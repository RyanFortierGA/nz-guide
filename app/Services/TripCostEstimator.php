<?php

namespace App\Services;

use App\Models\Location;
use App\Models\Trip;
use App\Models\TripBlock;

class TripCostEstimator
{
    /**
     * Ballpark NZD figures — all line amounts are per person.
     *
     * @return array{
     *   currency: string,
     *   party_size: int,
     *   trip_nights: int,
     *   nights_away: int,
     *   auckland_nights: int,
     *   total: int,
     *   per_person: int,
     *   party_total: int,
     *   lines: list<array{key:string,label:string,detail:?string,amount:int,category:string}>,
     *   disclaimer: string
     * }
     */
    public function forTrip(Trip $trip): array
    {
        $trip->loadMissing(['locations', 'blocks']);

        $party = max(1, (int) ($trip->party_size ?: 2));
        $tripNights = max(0, $trip->dayCount() - 1);
        $lines = [];

        $nightsAway = 0;
        foreach ($trip->locations as $location) {
            $nights = $this->nightsFor($location);
            if ($location->category !== 'local') {
                $nightsAway += $nights;
            }
        }
        $nightsAway = min($nightsAway, $tripNights);
        $aucklandNights = max(0, $tripNights - $nightsAway);

        if ($trip->include_auckland_stay && $aucklandNights > 0) {
            $rate = (int) ($trip->auckland_airbnb_night ?: 180);
            $shared = $aucklandNights * $rate;
            $lines[] = [
                'key' => 'auckland-stay',
                'label' => 'Auckland base stay (Airbnb-ish)',
                'detail' => "{$aucklandNights} night".($aucklandNights === 1 ? '' : 's')." × \${$rate} split {$party} ways",
                'amount' => $this->share($shared, $party),
                'category' => 'stay',
            ];
        }

        foreach ($trip->locations as $location) {
            foreach ($this->locationLines($location, $party) as $line) {
                $lines[] = $line;
            }
        }

        foreach ($trip->blocks as $block) {
            $amount = $this->blockAmountPp($block);
            if ($amount <= 0) {
                continue;
            }
            $lines[] = [
                'key' => 'block-'.$block->id,
                'label' => $block->typeLabel().': '.$block->title,
                'detail' => $block->day_index ? 'Day '.$block->day_index.' · per person' : 'Unscheduled · per person',
                'amount' => $amount,
                'category' => $block->type,
            ];
        }

        $perPerson = array_sum(array_column($lines, 'amount'));

        return [
            'currency' => 'NZD',
            'party_size' => $party,
            'trip_nights' => $tripNights,
            'nights_away' => $nightsAway,
            'auckland_nights' => $aucklandNights,
            'total' => $perPerson,
            'per_person' => $perPerson,
            'party_total' => $perPerson * $party,
            'lines' => $lines,
            'disclaimer' => 'All figures are per person. Shared stays/fuel are split across the party. Rough planning numbers only — check flights and Airbnbs before booking.',
        ];
    }

    /**
     * @return array{nights:int,party_size:int,total:int,per_person:int,party_total:int,blurb:string,lines:list<array{label:string,detail:?string,amount:int}>}
     */
    public function forLocationAddOn(Location $location, int $partySize = 2, ?int $nights = null): array
    {
        $party = max(1, $partySize);
        $resolvedNights = $nights ?? $this->suggestedNights($location);
        $lines = $this->locationLines($location, $party, $resolvedNights);
        $perPerson = array_sum(array_column($lines, 'amount'));

        $blurb = match ($location->category) {
            'flying' => "Tacking on {$location->name} for ~{$resolvedNights} nights",
            'weekend' => "A weekend at {$location->name}",
            default => "A day around {$location->name}",
        };

        return [
            'nights' => $resolvedNights,
            'party_size' => $party,
            'total' => $perPerson,
            'per_person' => $perPerson,
            'party_total' => $perPerson * $party,
            'blurb' => $blurb,
            'lines' => array_map(fn ($l) => [
                'label' => $l['label'],
                'detail' => $l['detail'],
                'amount' => $l['amount'],
            ], $lines),
        ];
    }

    /**
     * @return list<array{key:string,label:string,detail:?string,amount:int,category:string}>
     */
    private function locationLines(Location $location, int $party, ?int $nightsOverride = null): array
    {
        $nights = $nightsOverride ?? $this->nightsFor($location);
        $short = explode(',', $location->name)[0];

        if ($location->category === 'local') {
            $nights = 0;
            $days = 1;
        } else {
            $days = max(1, $nights);
        }

        $flight = (int) ($location->cost_flight_pp ?? $this->defaultFlight($location));
        $airbnb = (int) ($location->cost_airbnb_night ?? $this->defaultAirbnb($location));
        $dayPp = (int) ($location->cost_day_pp ?? $this->defaultDaySpend($location));
        $transport = (int) ($location->cost_transport ?? $this->defaultTransport($location));

        $lines = [];

        if ($flight > 0) {
            $lines[] = [
                'key' => 'flight-'.$location->id,
                'label' => "Flights · {$short}",
                'detail' => "~\${$flight} return each",
                'amount' => $flight,
                'category' => 'flight',
            ];
        }

        if ($nights > 0 && $airbnb > 0) {
            $shared = $nights * $airbnb;
            $lines[] = [
                'key' => 'stay-'.$location->id,
                'label' => "Stay · {$short}",
                'detail' => "{$nights} night".($nights === 1 ? '' : 's')." × \${$airbnb} split {$party} ways",
                'amount' => $this->share($shared, $party),
                'category' => 'stay',
            ];
        }

        if ($dayPp > 0) {
            $lines[] = [
                'key' => 'day-'.$location->id,
                'label' => "Days out · {$short}",
                'detail' => "{$days} day".($days === 1 ? '' : 's')." × \${$dayPp}",
                'amount' => $days * $dayPp,
                'category' => 'daily',
            ];
        }

        if ($transport > 0) {
            $lines[] = [
                'key' => 'transport-'.$location->id,
                'label' => "Getting there · {$short}",
                'detail' => ($location->mode === 'ferry' ? 'Ferry ballpark' : 'Fuel / parking ballpark')." split {$party} ways",
                'amount' => $this->share($transport, $party),
                'category' => 'transport',
            ];
        }

        return $lines;
    }

    private function share(int $sharedTotal, int $party): int
    {
        return (int) round($sharedTotal / max(1, $party));
    }

    private function nightsFor(Location $location): int
    {
        $pivotNights = $location->relationLoaded('pivot') || isset($location->pivot)
            ? ($location->pivot->nights ?? null)
            : null;

        if ($pivotNights !== null && $pivotNights !== '') {
            return max(0, (int) $pivotNights);
        }

        return $this->suggestedNights($location);
    }

    private function suggestedNights(Location $location): int
    {
        if ($location->cost_suggested_nights !== null) {
            return (int) $location->cost_suggested_nights;
        }

        return match ($location->category) {
            'flying' => 3,
            'weekend' => 1,
            default => 0,
        };
    }

    private function blockAmountPp(TripBlock $block): int
    {
        return match ($block->type) {
            'meal' => 45,
            'hangout' => 18,
            'find' => 25,
            default => 0,
        };
    }

    private function defaultFlight(Location $location): int
    {
        if ($location->category !== 'flying') {
            return 0;
        }

        return match ($location->slug) {
            'queenstown' => 280,
            'brisbane' => 420,
            'sydney' => 380,
            'fiji' => 650,
            default => 400,
        };
    }

    private function defaultAirbnb(Location $location): int
    {
        return match ($location->category) {
            'flying' => match ($location->slug) {
                'sydney' => 260,
                'fiji' => 280,
                'queenstown' => 220,
                'brisbane' => 170,
                default => 200,
            },
            'weekend' => 150,
            default => 0,
        };
    }

    private function defaultDaySpend(Location $location): int
    {
        return match ($location->category) {
            'flying' => 110,
            'weekend' => 85,
            default => 55,
        };
    }

    private function defaultTransport(Location $location): int
    {
        return match ($location->mode) {
            'car' => $location->category === 'weekend' ? 90 : 40,
            'ferry' => 60,
            'plane' => 0,
            default => 15,
        };
    }
}

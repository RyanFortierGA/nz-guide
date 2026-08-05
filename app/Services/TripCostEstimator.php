<?php

namespace App\Services;

use App\Models\Location;
use App\Models\Trip;
use App\Models\TripBlock;

class TripCostEstimator
{
    /**
     * Ballpark NZD totals for friends/family planning — not live quotes.
     *
     * @return array{
     *   currency: string,
     *   party_size: int,
     *   trip_nights: int,
     *   nights_away: int,
     *   auckland_nights: int,
     *   total: int,
     *   per_person: int,
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
            $amount = $aucklandNights * $rate;
            $lines[] = [
                'key' => 'auckland-stay',
                'label' => 'Auckland base stay (Airbnb-ish)',
                'detail' => "{$aucklandNights} night".($aucklandNights === 1 ? '' : 's')." × \${$rate}",
                'amount' => $amount,
                'category' => 'stay',
            ];
        }

        foreach ($trip->locations as $location) {
            foreach ($this->locationLines($location, $party) as $line) {
                $lines[] = $line;
            }
        }

        foreach ($trip->blocks as $block) {
            $amount = $this->blockAmount($block, $party);
            if ($amount <= 0) {
                continue;
            }
            $lines[] = [
                'key' => 'block-'.$block->id,
                'label' => $block->typeLabel().': '.$block->title,
                'detail' => $block->day_index ? 'Day '.$block->day_index : 'Unscheduled',
                'amount' => $amount,
                'category' => $block->type,
            ];
        }

        $total = array_sum(array_column($lines, 'amount'));

        return [
            'currency' => 'NZD',
            'party_size' => $party,
            'trip_nights' => $tripNights,
            'nights_away' => $nightsAway,
            'auckland_nights' => $aucklandNights,
            'total' => $total,
            'per_person' => (int) round($total / $party),
            'lines' => $lines,
            'disclaimer' => 'Rough planning numbers only — flights and Airbnbs swing a lot by season. Use the links to sanity-check before booking.',
        ];
    }

    /**
     * @return array{nights:int,party_size:int,total:int,per_person:int,blurb:string,lines:list<array{label:string,detail:?string,amount:int}>}
     */
    public function forLocationAddOn(Location $location, int $partySize = 2, ?int $nights = null): array
    {
        $party = max(1, $partySize);
        $resolvedNights = $nights ?? $this->suggestedNights($location);
        $lines = $this->locationLines($location, $party, $resolvedNights);
        $total = array_sum(array_column($lines, 'amount'));

        $blurb = match ($location->category) {
            'flying' => "Tacking on {$location->name} for ~{$resolvedNights} nights",
            'weekend' => "A weekend at {$location->name}",
            default => "A day around {$location->name}",
        };

        return [
            'nights' => $resolvedNights,
            'party_size' => $party,
            'total' => $total,
            'per_person' => (int) round($total / max(1, $party)),
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
                'detail' => "~\${$flight} return × {$party} people",
                'amount' => $flight * $party,
                'category' => 'flight',
            ];
        }

        if ($nights > 0 && $airbnb > 0) {
            $lines[] = [
                'key' => 'stay-'.$location->id,
                'label' => "Stay · {$short}",
                'detail' => "{$nights} night".($nights === 1 ? '' : 's')." × \${$airbnb} (Airbnb ballpark)",
                'amount' => $nights * $airbnb,
                'category' => 'stay',
            ];
        }

        if ($dayPp > 0) {
            $lines[] = [
                'key' => 'day-'.$location->id,
                'label' => "Days out · {$short}",
                'detail' => "{$days} day".($days === 1 ? '' : 's')." × \${$dayPp}/person",
                'amount' => $days * $dayPp * $party,
                'category' => 'daily',
            ];
        }

        if ($transport > 0) {
            $lines[] = [
                'key' => 'transport-'.$location->id,
                'label' => "Getting there · {$short}",
                'detail' => $location->mode === 'ferry' ? 'Ferry tickets ballpark' : 'Fuel / parking ballpark',
                'amount' => $transport,
                'category' => 'transport',
            ];
        }

        return $lines;
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

    private function blockAmount(TripBlock $block, int $party): int
    {
        return match ($block->type) {
            'meal' => 45 * $party,
            'hangout' => 18 * $party,
            'find' => 25 * $party,
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

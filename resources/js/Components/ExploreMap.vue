<script setup>
import { ICONS } from '@/icons';
import L from 'leaflet';
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps({
    /** Currently visible destinations (filtered) */
    locations: { type: Array, required: true },
    /** Full set — kept for marker cache; defaults to locations */
    allLocations: { type: Array, default: null },
    home: { type: Object, required: true },
    categoryColors: { type: Object, required: true },
    focusId: { type: [Number, String], default: null },
});

const emit = defineEmits(['select']);

const mapEl = ref(null);
let map = null;
const markers = {};
let subLayer = null;
const subByParent = {};
const SUB_ZOOM_THRESHOLD = 9;

function catalog() {
    return props.allLocations?.length ? props.allLocations : props.locations;
}

function pinIcon(d) {
    const c = props.categoryColors[d.category];
    return L.divIcon({
        className: 'dest-pin-icon',
        html: `<div class="pin-wrap">
            <div class="pin-label"><span style="color:${c}">${ICONS[d.mode] || ''}</span><span>${d.name.split(',')[0]}</span></div>
            <div class="pin-tail"></div>
            <div class="pin-dot" style="background:${c}"></div>
          </div>`,
        iconSize: [140, 44],
        iconAnchor: [70, 44],
    });
}

function popupHtml(d) {
    return `<div style="width:200px">
    <div style="width:100%;height:100px;border-radius:10px;background-size:cover;background-position:center;margin-bottom:8px;background-image:url('${d.image_url}')"></div>
    <h4 style="font-size:14px;font-weight:600;margin:0 0 4px">${d.name}</h4>
    <p style="font-size:11px;color:#787f88;margin:0;display:flex;align-items:center;gap:5px">${ICONS[d.mode] || ''}<span>${d.travel_time} from home</span></p>
  </div>`;
}

function fitToVisible() {
    if (!map) return;
    const visible = props.locations;
    const points = visible.map((d) => [d.lat, d.lng]).concat([[props.home.lat, props.home.lng]]);
    if (points.length === 1) {
        map.setView(points[0], 11);
        return;
    }
    map.fitBounds(L.latLngBounds(points), { padding: [50, 50], maxZoom: 12 });
}

function syncVisibility() {
    if (!map) return;
    const visibleIds = new Set(props.locations.map((d) => d.id));

    Object.entries(markers).forEach(([id, marker]) => {
        const show = visibleIds.has(Number(id)) || visibleIds.has(id);
        if (show) {
            if (!map.hasLayer(marker)) marker.addTo(map);
        } else if (map.hasLayer(marker)) {
            map.removeLayer(marker);
        }
    });

    // Rebuild sub-layer from visible parents only
    if (subLayer) {
        subLayer.clearLayers();
        props.locations.forEach((parent) => {
            (subByParent[parent.id] || []).forEach((dot) => dot.addTo(subLayer));
        });
    }

    updateSubVisibility();
    fitToVisible();
}

function updateSubVisibility() {
    if (!map || !subLayer) return;
    if (map.getZoom() >= SUB_ZOOM_THRESHOLD) {
        if (!map.hasLayer(subLayer)) subLayer.addTo(map);
    } else if (map.hasLayer(subLayer)) {
        map.removeLayer(subLayer);
    }
}

function initMap() {
    map = L.map(mapEl.value, { scrollWheelZoom: true, zoomControl: true }).setView([-36.85, 174.76], 10);
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap contributors &copy; CARTO',
        maxZoom: 18,
        subdomains: 'abcd',
    }).addTo(map);

    const homeIcon = L.divIcon({
        className: 'dest-pin-icon',
        html: `<div class="home-wrap"><div class="home-marker"></div><div class="home-label">Home</div></div>`,
        iconSize: [72, 48],
        iconAnchor: [36, 18],
    });
    L.marker([props.home.lat, props.home.lng], { icon: homeIcon })
        .addTo(map)
        .bindPopup(`<b>Home</b><div style="font-size:10px;color:#787f88;margin-top:3px">${props.home.name}</div>`);

    catalog().forEach((d) => {
        const marker = L.marker([d.lat, d.lng], { icon: pinIcon(d) });
        marker.bindPopup(popupHtml(d), { closeButton: true });
        marker.on('click', () => emit('select', d));
        markers[d.id] = marker;
    });

    subLayer = L.layerGroup();
    catalog().forEach((parent) => {
        subByParent[parent.id] = [];
        (parent.sub_locations || []).forEach((s) => {
            const c = props.categoryColors[parent.category];
            const marker = L.marker([s.lat, s.lng], {
                icon: L.divIcon({
                    className: 'sub-pin-icon',
                    html: `<div class="pin-wrap"><div class="sub-pin-label" style="border-left:3px solid ${c}">${s.name}</div></div>`,
                    iconSize: [120, 28],
                    iconAnchor: [60, 14],
                }),
                zIndexOffset: 200,
                riseOnHover: true,
            });
            const mapsUrl = s.maps_url
                || `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(`${s.name}@${s.lat},${s.lng}`)}`;
            const photo = s.image_url
                ? `<div style="height:80px;border-radius:8px;background-size:cover;background-position:center;margin-bottom:6px;background-image:url('${s.image_url}')"></div>`
                : '';
            marker.bindPopup(
                `<div style="width:180px">${photo}<b>${s.name}</b><div style="font-size:10px;color:#787f88;margin:3px 0 6px">near ${parent.name}</div><a href="${mapsUrl}" target="_blank" rel="noopener" style="font-size:11px;font-weight:600">Open in Maps ›</a></div>`,
            );
            marker.on('click', (e) => {
                L.DomEvent.stopPropagation(e);
            });
            subByParent[parent.id].push(marker);
        });
    });

    map.on('zoomend', updateSubVisibility);
    syncVisibility();
}

function flyTo(location) {
    if (!map || !location) return;
    map.flyTo([location.lat, location.lng], Math.max(map.getZoom(), SUB_ZOOM_THRESHOLD + 1), {
        duration: 0.7,
    });
    markers[location.id]?.openPopup();
}

watch(() => props.locations, syncVisibility, { deep: true });
watch(
    () => props.focusId,
    (id) => {
        const loc = catalog().find((l) => l.id === id);
        if (loc) flyTo(loc);
    },
);

onMounted(() => {
    initMap();
    setTimeout(() => map?.invalidateSize(), 100);
});

onBeforeUnmount(() => {
    map?.remove();
    map = null;
});

defineExpose({ flyTo });
</script>

<template>
    <div class="relative h-full min-h-[420px] w-full">
        <div ref="mapEl" class="absolute inset-0 bg-[#eef3f6]" />
        <div class="absolute right-4 top-4 z-[500] rounded-[14px] border border-[var(--line)] bg-white px-4 py-3 text-[11.5px] text-[#333] shadow-[0_10px_24px_-14px_rgba(0,0,0,.3)]">
            <div class="mb-2 text-[10px] font-bold uppercase tracking-[0.06em]">Getting there</div>
            <div class="my-1 flex items-center gap-2"><span class="inline-block h-2.5 w-2.5 rounded-full" style="background:var(--flying)" />Flying</div>
            <div class="my-1 flex items-center gap-2"><span class="inline-block h-2.5 w-2.5 rounded-full" style="background:var(--weekend)" />Weekend drive</div>
            <div class="my-1 flex items-center gap-2"><span class="inline-block h-2.5 w-2.5 rounded-full" style="background:var(--local)" />In Auckland</div>
            <div class="my-1 flex items-center gap-2"><span class="inline-block h-2.5 w-2.5 rounded-full bg-[var(--ink)]" />Home</div>
            <p class="mt-2 max-w-[150px] border-t border-[var(--line)] pt-2 text-[10.5px] leading-snug text-[var(--muted)]">
                Zoom in on a pin to see nearby sub-spots.
            </p>
        </div>
    </div>
</template>

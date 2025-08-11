<script setup lang="ts">
import { ref, onMounted, watch, onBeforeUnmount } from 'vue'
import { Loader2 } from 'lucide-vue-next'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import 'leaflet-geosearch/dist/geosearch.css'
import { OpenStreetMapProvider, GeoSearchControl } from 'leaflet-geosearch'
// Marker cluster plugin (no TS types provided)
import 'leaflet.markercluster'
import 'leaflet.markercluster/dist/MarkerCluster.css'
import 'leaflet.markercluster/dist/MarkerCluster.Default.css'

// (Saved locations feature removed)

// Define props and emits
const props = withDefaults(defineProps<{
  latitude: number | null
  longitude: number | null
  mapHeight?: string
  address?: string | null
  nearbyMarkers?: { id: number; name: string; code?: string; latitude: number; longitude: number }[]
}>(), {
  mapHeight: '350px',
  address: null,
  nearbyMarkers: () => [],
})

const emit = defineEmits<{
  (e: 'update:latitude', value: number | null): void
  (e: 'update:longitude', value: number | null): void
  (e: 'update:address', value: string | null): void
}>()

// Component refs and state
const mapContainer = ref<HTMLElement | null>(null)
// const isSearching = ref(false) // removed: no longer used
const isLoadingAddress = ref(false)
const currentAddress = ref<string | null>(null)
// removed saved locations state
const isEditingAddress = ref(false)
const editedAddress = ref('')
let map: L.Map | null = null
let marker: L.Marker | null = null
let searchControl: any = null
let clusterLayer: any = null
const hasAutoLocated = ref(false)

// Helpers to validate coordinates safely
function isValidLat(val: unknown): val is number {
  return typeof val === 'number' && Number.isFinite(val) && val >= -90 && val <= 90
}
function isValidLng(val: unknown): val is number {
  return typeof val === 'number' && Number.isFinite(val) && val >= -180 && val <= 180
}
function hasValidCoords(lat: unknown, lng: unknown): lat is number & unknown {
  // Use both validators; this signature helps TS narrow but is mainly for runtime safety
  return isValidLat(lat) && isValidLng(lng)
}

// removed saved locations persistence

// Watch for prop changes to update the marker
watch(() => [props.latitude, props.longitude], async ([newLat, newLng]) => {
  if (!map) return
  if (hasValidCoords(newLat, newLng)) {
    const position: L.LatLngExpression = [newLat as number, newLng as number]
    if (marker) {
      marker.setLatLng(position)
    } else {
      createMarker(position)
    }
    map.setView(position, 15)
  } else {
    // Clear existing marker if coordinates are cleared
    if (marker) {
      marker.remove()
      marker = null
    }
    currentAddress.value = null
    if (!hasAutoLocated.value) {
      const ok = await tryAutoLocate()
      if (ok) hasAutoLocated.value = true
    }
  }
})

// Watch for address changes
watch(() => currentAddress.value, (newAddress) => {
  if (newAddress !== undefined) {
    editedAddress.value = newAddress || ''
    emit('update:address', newAddress)
  }
})

// Keep internal address in sync if parent provides one
watch(() => props.address, (val) => {
  if (val && val !== currentAddress.value) {
    currentAddress.value = val
    editedAddress.value = val
  }
})

// Initialize the map
onMounted(() => {
  if (!mapContainer.value) return

  // Create map instance
  // Base layers
  const osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
  })
  const cartoLight = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
    attribution: '&copy; OpenStreetMap contributors & CARTO'
  })
  const cartoDark = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
    attribution: '&copy; OpenStreetMap contributors & CARTO'
  })

  map = L.map(mapContainer.value, {
    center: [props.latitude ?? 0, props.longitude ?? 0],
    zoom: (props.latitude !== null && props.longitude !== null) ? 15 : 3,
    layers: [cartoLight],
  })

  // Layers control
  const baseLayers = {
    'Carto Light': cartoLight,
    'Carto Dark': cartoDark,
    'OSM Standard': osm,
  }
  L.control.layers(baseLayers, {}, { position: 'topleft' }).addTo(map)

  // Set up search provider
  const provider = new OpenStreetMapProvider()

  // Add search control
  searchControl = GeoSearchControl({
    provider: provider,
    style: 'bar',
    showMarker: false,
    showPopup: false,
    autoClose: true,
    retainZoomLevel: false,
    animateZoom: true,
    keepResult: true,
    searchLabel: 'Search for location'
  })

  map.addControl(searchControl)

  // Create a marker if we have initial coordinates
  if (hasValidCoords(props.latitude, props.longitude)) {
    createMarker([props.latitude as number, props.longitude as number])
  }

  // If no coordinates provided, try to auto-locate the user (once)
  const attemptAutoLocate = async () => {
    if (hasAutoLocated.value) return
    if (props.latitude === null || props.longitude === null) {
      const ok = await tryAutoLocate()
      if (ok) {
        hasAutoLocated.value = true
      }
    }
  }

  // Add nearby markers in a cluster group (if provided)
  if (props.nearbyMarkers && props.nearbyMarkers.length > 0) {
  // @ts-expect-error: L.markerClusterGroup is provided by plugin without types
    clusterLayer = L.markerClusterGroup()
    props.nearbyMarkers.forEach((m) => {
      const marker = L.marker([m.latitude, m.longitude])
        .bindPopup(`<div class=\"text-sm\"><div class=\"font-medium\">${m.name}</div><div class=\"text-xs text-muted-foreground\">${m.code ?? ''}</div></div>`)
      clusterLayer.addLayer(marker)
    })
    clusterLayer.addTo(map)
  }

  // Try auto locate first, then if still no position and we have clusters, fit to bounds
  ;(async () => {
    await attemptAutoLocate()
  if (!hasAutoLocated.value && !hasValidCoords(props.latitude, props.longitude) && clusterLayer) {
      const bounds = clusterLayer.getBounds()
      if (bounds && bounds.isValid()) {
        map!.fitBounds(bounds.pad(0.1))
      }
    }
  })()

  // Handle search results
  map.on('geosearch/showlocation', async (event: any) => {
    const { location } = event
    if (location) {
      updateMarkerPosition([location.y, location.x])
      emit('update:latitude', location.y)
      emit('update:longitude', location.x)
  // removed isSearching

      // Get address from search result if available
      if (location.label) {
        currentAddress.value = location.label
      } else {
        // Otherwise get it from reverse geocoding
        const address = await reverseGeocode(location.y, location.x)
        currentAddress.value = address
      }
    }
  })

  // Handle map clicks
  map.on('click', async (event: L.LeafletMouseEvent) => {
    const position: L.LatLngExpression = [event.latlng.lat, event.latlng.lng]
    updateMarkerPosition(position)
    emit('update:latitude', event.latlng.lat)
    emit('update:longitude', event.latlng.lng)

    // Get address for the clicked location
    const address = await reverseGeocode(event.latlng.lat, event.latlng.lng)
    currentAddress.value = address
  })

  // Add zoom control with home button
  L.control.zoom({
    position: 'bottomright'
  }).addTo(map)

  // Add scale control
  L.control.scale({ position: 'bottomleft', imperial: true, metric: true }).addTo(map)

  // Add custom reset view control
  const resetViewControl = L.Control.extend({
    options: {
      position: 'bottomright'
    },
    onAdd: function() {
      const container = L.DomUtil.create('div', 'leaflet-control leaflet-control-custom')
      container.style.backgroundColor = 'var(--background)'
      container.style.width = '30px'
      container.style.height = '30px'
      container.style.cursor = 'pointer'
      container.style.border = '2px solid rgba(0,0,0,0.2)'
      container.style.borderRadius = '4px'
      container.style.display = 'flex'
      container.style.alignItems = 'center'
      container.style.justifyContent = 'center'
      container.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M3 12a9 9 0 1 0 18 0 9 9 0 0 0-18 0z"></path>
          <path d="M12 8v8"></path>
          <path d="M8 12h8"></path>
        </svg>
      `
      container.title = 'Reset view'

      container.onclick = function() {
        if (map) {
          map.setView([0, 0], 2)
        }
        return false
      }

      return container
    }
  })
  new resetViewControl().addTo(map)

  // Add fullscreen control (custom)
  const FullscreenControl = L.Control.extend({
    options: { position: 'bottomright' },
    onAdd: function () {
      const container = L.DomUtil.create('div', 'leaflet-control leaflet-control-custom')
      container.style.backgroundColor = 'var(--background)'
      container.style.width = '30px'
      container.style.height = '30px'
      container.style.cursor = 'pointer'
      container.style.border = '2px solid rgba(0,0,0,0.2)'
      container.style.borderRadius = '4px'
      container.style.display = 'flex'
      container.style.alignItems = 'center'
      container.style.justifyContent = 'center'
      container.title = 'Toggle fullscreen'

      const setIcon = (isFs: boolean) => {
        container.innerHTML = isFs
          ? `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h6v6"/><path d="M9 21H3v-6"/><path d="M21 3l-7 7"/><path d="M3 21l7-7"/></svg>`
          : `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 3H3v6"/><path d="M15 21h6v-6"/><path d="M3 9l7-7"/><path d="M21 15l-7 7"/></svg>`
      }

      setIcon(!!document.fullscreenElement)

      container.onclick = function () {
        const el = mapContainer.value as HTMLElement
        if (!document.fullscreenElement) {
          el.requestFullscreen?.()
        } else {
          document.exitFullscreen?.()
        }
        // icon will update on next event
        return false
      }

      document.addEventListener('fullscreenchange', () => setIcon(!!document.fullscreenElement))
      return container
    }
  })
  new FullscreenControl().addTo(map)
})

onBeforeUnmount(() => {
  if (map) {
    map.remove()
    map = null
  }
})

// Create a marker at the specified position
function createMarker(position: L.LatLngExpression) {
  if (!map) return

  // Create a custom icon with pulsating effect
  const customIcon = L.divIcon({
    className: 'custom-marker',
    html: `
      <div class="marker-container">
        <div class="marker-pulse"></div>
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="36" viewBox="0 0 24 36" class="marker-icon">
          <path fill="currentColor" class="text-primary" d="M12 0C5.4 0 0 5.4 0 12c0 7.2 12 24 12 24s12-16.8 12-24c0-6.6-5.4-12-12-12zm0 18c-3.3 0-6-2.7-6-6s2.7-6 6-6 6 2.7 6 6-2.7 6-6 6z"/>
        </svg>
      </div>
    `,
    iconSize: [40, 40],
    iconAnchor: [20, 40],
    popupAnchor: [0, -40]
  })

  // Add the marker to the map with animation
  marker = L.marker(position, {
    icon: customIcon,
    draggable: true
  }).addTo(map)

  // Add a tooltip to indicate draggable
  marker.bindTooltip("Drag to adjust position", {
    direction: 'top',
    offset: [0, -30],
    opacity: 0.8,
    className: 'marker-tooltip'
  })

  // Show tooltip once when created, then on mouseover
  setTimeout(() => marker?.openTooltip(), 1000)
  setTimeout(() => marker?.closeTooltip(), 4000)

  // Add drag events
  marker.on('dragstart', () => {
    if (marker && marker.getTooltip()) {
      marker.closeTooltip()
    }
  })

  marker.on('dragend', async () => {
    const newPos = marker?.getLatLng()
    if (newPos) {
      emit('update:latitude', newPos.lat)
      emit('update:longitude', newPos.lng)

      // Get address for the new position
      const address = await reverseGeocode(newPos.lat, newPos.lng)
      currentAddress.value = address
    }
  })
}

// Update the marker position
function updateMarkerPosition(position: L.LatLngExpression) {
  if (marker) {
    marker.setLatLng(position)
  } else {
    createMarker(position)
  }

  if (map) {
    map.setView(position, map.getZoom() < 10 ? 13 : map.getZoom())
  }
}

// removed saved locations methods

// Clear current selection
function clearSelection() {
  if (marker) {
    marker.remove()
    marker = null
  }
  emit('update:latitude', null)
  emit('update:longitude', null)
  currentAddress.value = null
}

// Copy helpers
async function copyToClipboard(text: string) {
  try {
    await navigator.clipboard.writeText(text)
  } catch (e) {
    console.error('Failed to copy', e)
  }
}

function openInGoogleMaps() {
  if (props.latitude != null && props.longitude != null) {
    const url = `https://maps.google.com/?q=${props.latitude},${props.longitude}`
    window.open(url, '_blank')
  }
}

// Reverse geocode a lat/lng to get address
async function reverseGeocode(lat: number, lng: number): Promise<string | null> {
  isLoadingAddress.value = true
  try {
    // Add a randomized delay to avoid rate limiting (max 1 request per second)
    const randomDelay = Math.floor(Math.random() * 500) + 500; // 500-1000ms delay
    await new Promise(resolve => setTimeout(resolve, randomDelay));

    const response = await fetch(
      `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`,
      {
        headers: {
          'User-Agent': 'MoBoards Location Picker (https://mo-boards.com)'
        }
      }
    )

    if (!response.ok) {
      if (response.status === 429) {
        // Rate limited
        console.warn('Rate limited by geocoding service, waiting before retry');
        await new Promise(resolve => setTimeout(resolve, 2000)); // Wait 2 seconds
        return await reverseGeocode(lat, lng); // Retry
      }
      throw new Error(`Failed to fetch address: ${response.statusText}`);
    }

    const data = await response.json()
    if (data && data.display_name) {
      return data.display_name
    }

    // Fallback to coordinates if no address found
    return `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
  } catch (error) {
    console.error('Error reverse geocoding:', error)
    // Return coordinates as fallback
    return `${lat.toFixed(6)}, ${lng.toFixed(6)} (Unable to get address)`;
  } finally {
    isLoadingAddress.value = false
  }
}

// Save manually edited address
async function saveEditedAddress() {
  if (editedAddress.value.trim()) {
    const address = editedAddress.value.trim();
    currentAddress.value = address;
    isEditingAddress.value = false;

    // Try to geocode the address to get coordinates
    try {
      if (map && searchControl && searchControl.searchElement) {
        // This is a bit hacky but uses the existing search control
        // First clear any existing results
        searchControl.searchElement.resetInput();

        // Set the value and trigger a search
        const searchInput = searchControl.searchElement.input;
        if (searchInput) {
          searchInput.value = address;
          searchControl.searchElement.autoSearch();
          // The search results will be handled by the geosearch/showlocation event listener
        }
      }
    } catch (error) {
      console.error('Error geocoding manually entered address:', error);
    }
  }
}

// Cancel address editing
function cancelEditAddress() {
  editedAddress.value = currentAddress.value || ''
  isEditingAddress.value = false
}

// removed manual geolocation helper
// Try to get the user's current location and set marker/address
async function tryAutoLocate(): Promise<boolean> {
  return await new Promise<boolean>((resolve) => {
    if (!('geolocation' in navigator)) {
      return resolve(false)
    }
    navigator.geolocation.getCurrentPosition(
      async (pos) => {
        const { latitude, longitude } = pos.coords
        updateMarkerPosition([latitude, longitude])
        emit('update:latitude', latitude)
        emit('update:longitude', longitude)
        try {
          const addr = await reverseGeocode(latitude, longitude)
          currentAddress.value = addr
        } catch {
          // ignore address failure
        }
        resolve(true)
      },
      () => resolve(false),
      { enableHighAccuracy: true, timeout: 8000, maximumAge: 60000 }
    )
  })
}
</script>

<template>
  <div class="w-full">
    <!-- Map Controls removed -->

    <!-- Map Container -->
    <div
      ref="mapContainer"
      class="w-full rounded-md border border-border bg-muted/20 shadow-sm relative overflow-hidden"
      :style="{ height: mapHeight }"
    ></div>

    <!-- Address Display -->
    <div v-if="currentAddress || isLoadingAddress" class="mt-6 text-sm">
      <div v-if="isLoadingAddress" class="text-muted-foreground flex items-center">
        <Loader2 class="mr-1 h-3 w-3 animate-spin" /> Loading address...
      </div>
      <div v-else-if="currentAddress" class="text-foreground">
        <div class="font-medium text-xs uppercase tracking-wide text-muted-foreground mb-1">
          Address
          <button
            type="button"
            @click="isEditingAddress = !isEditingAddress"
            class="ml-1 inline-flex items-center text-xs text-primary hover:text-primary/80"
            title="Edit address"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="12"
              height="12"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            >
              <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
            </svg>
          </button>
        </div>
        <div v-if="!isEditingAddress" class="text-sm cursor-pointer hover:text-primary" @click="isEditingAddress = true">
          {{ currentAddress }}
        </div>
        <div v-else class="flex flex-col sm:flex-row gap-2">
          <Input
            v-model="editedAddress"
            class="text-sm py-1 h-8 flex-1"
            placeholder="Enter address manually"
            @keydown.enter="saveEditedAddress"
            @keydown.esc="cancelEditAddress"
          />
          <div class="flex space-x-1 justify-end">
            <Button type="button" size="sm" class="h-8 px-2" @click="saveEditedAddress">Save</Button>
            <Button type="button" size="sm" variant="ghost" class="h-8 px-2" @click="cancelEditAddress">Cancel</Button>
          </div>
        </div>
      </div>
    </div>

    <!-- Coordinates Display -->
    <div class="mt-6 flex flex-wrap items-center gap-2">
  <Button size="sm" variant="outline" class="h-8" :disabled="latitude === null || longitude === null" @click="openInGoogleMaps">Open in Google Maps</Button>
      <Button size="sm" variant="outline" class="h-8" :disabled="latitude === null || longitude === null" @click="copyToClipboard(`${latitude}, ${longitude}`)">Copy Coords</Button>
      <Button size="sm" variant="ghost" class="h-8" @click="clearSelection">Clear Pin</Button>
      <div class="text-xs text-muted-foreground" v-if="currentAddress">
        <Button size="sm" variant="ghost" class="h-8 px-2" @click="copyToClipboard(currentAddress!)">Copy Address</Button>
      </div>
    </div>
  </div>
</template>

<style>
/* Custom marker styles */
.custom-marker {
  background: transparent;
  border: none;
  width: 40px !important;
  height: 40px !important;
  margin-top: -40px !important;
  margin-left: -20px !important;
}

.marker-container {
  position: relative;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.marker-icon {
  position: absolute;
  top: 0;
  left: 8px;
  z-index: 10;
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
  color: hsl(var(--primary));
}

.marker-pulse {
  position: absolute;
  width: 20px;
  height: 20px;
  left: 10px;
  top: 12px;
  background-color: hsla(var(--primary), 0.4);
  border-radius: 50%;
  z-index: 5;
  animation: pulse 1.5s ease-in-out infinite;
}

@keyframes pulse {
  0% {
    transform: scale(0.5);
    opacity: 1;
  }
  70% {
    transform: scale(2);
    opacity: 0;
  }
  100% {
    transform: scale(0.5);
    opacity: 0;
  }
}

/* Override leaflet and leaflet-geosearch styles to match our theme */
.leaflet-control-geosearch form {
  border-radius: 0.375rem;
  background-color: var(--background);
  border-color: var(--border);
  color: var(--foreground);
}

.leaflet-control-geosearch form input {
  color: var(--foreground);
}

.leaflet-control-geosearch form button {
  background-color: hsl(var(--primary));
  color: hsl(var(--primary-foreground));
}

.leaflet-control-geosearch form button:hover {
  background-color: hsl(var(--primary) / 0.9);
}

.leaflet-geosearch-bar .results>* {
  background-color: var(--background);
  color: var(--foreground);
  border-color: var(--border);
}

.leaflet-geosearch-bar .results>*:hover,
.leaflet-geosearch-bar .results>*.active {
  background-color: var(--accent);
}

.leaflet-control-zoom a {
  background-color: var(--background) !important;
  color: var(--foreground) !important;
  border-color: var(--border) !important;
}

.leaflet-control-zoom a:hover {
  background-color: var(--accent) !important;
}

.marker-tooltip {
  background-color: var(--background) !important;
  border: 1px solid var(--border) !important;
  color: var(--foreground) !important;
  font-size: 12px !important;
  padding: 4px 8px !important;
}

/* Dark mode adjustments for the map */
.dark .leaflet-tile {
  filter: brightness(0.8) invert(1) contrast(0.9) hue-rotate(200deg) saturate(0.7) !important;
}

.dark .leaflet-container {
  background: #1a1b26 !important;
}

/* Custom control styles */
.leaflet-control-custom {
  color: var(--foreground) !important;
}

.leaflet-control-custom:hover {
  background-color: var(--accent) !important;
}

/* Mobile responsiveness */
@media (max-width: 640px) {
  .leaflet-control-geosearch {
    width: calc(100% - 20px) !important;
    max-width: none !important;
    margin-left: 10px !important;
    margin-right: 10px !important;
  }

  .leaflet-control-geosearch form {
    width: 100% !important;
    box-sizing: border-box;
  }

  .leaflet-control-geosearch form input {
    width: calc(100% - 30px) !important;
    box-sizing: border-box;
  }

  .leaflet-control-zoom {
    margin-bottom: 50px !important; /* Give space for search bar at bottom */
  }
}
</style>

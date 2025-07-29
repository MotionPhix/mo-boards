<script setup lang="ts">
import { ref, onMounted, watch, onBeforeUnmount } from 'vue'
import { Loader2 } from 'lucide-vue-next'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import 'leaflet-geosearch/dist/geosearch.css'
import { OpenStreetMapProvider, GeoSearchControl } from 'leaflet-geosearch'

// Define types
interface SavedLocation {
  id: string;
  name: string;
  latitude: number;
  longitude: number;
  address?: string;
}

// Define props and emits
const props = withDefaults(defineProps<{
  latitude: number | null
  longitude: number | null
  mapHeight?: string
}>(), {
  mapHeight: '350px'
})

const emit = defineEmits<{
  (e: 'update:latitude', value: number | null): void
  (e: 'update:longitude', value: number | null): void
}>()

// Component refs and state
const mapContainer = ref<HTMLElement | null>(null)
const searchInput = ref<HTMLInputElement | null>(null)
const isSearching = ref(false)
const isLocating = ref(false)
const isLoadingAddress = ref(false)
const currentAddress = ref<string | null>(null)
const savedLocations = ref<SavedLocation[]>([])
const showSaveDialog = ref(false)
const showSavedLocationsDropdown = ref(false)
const locationName = ref('')
const isEditingAddress = ref(false)
const editedAddress = ref('')
let map: L.Map | null = null
let marker: L.Marker | null = null
let searchControl: any = null

// Load saved locations from localStorage
onMounted(() => {
  const storedLocations = localStorage.getItem('savedMapLocations')
  if (storedLocations) {
    try {
      savedLocations.value = JSON.parse(storedLocations)
    } catch (e) {
      console.error('Failed to parse saved locations', e)
      savedLocations.value = []
    }
  }
})

// Watch for changes to saved locations to persist them
watch(savedLocations, (newLocations) => {
  localStorage.setItem('savedMapLocations', JSON.stringify(newLocations))
}, { deep: true })

// Watch for prop changes to update the marker
watch(() => [props.latitude, props.longitude], ([newLat, newLng]) => {
  if (map && newLat !== null && newLng !== null) {
    const position: L.LatLngExpression = [newLat, newLng]
    
    if (marker) {
      marker.setLatLng(position)
    } else {
      createMarker(position)
    }
    
    map.setView(position, 15)
  }
})

// Watch for address changes
watch(() => currentAddress.value, (newAddress) => {
  if (newAddress) {
    editedAddress.value = newAddress
  }
})

// Initialize the map
onMounted(() => {
  if (!mapContainer.value) return

  // Create map instance
  map = L.map(mapContainer.value, {
    center: [props.latitude || 0, props.longitude || 0],
    zoom: props.latitude && props.longitude ? 15 : 2,
    layers: [
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
      })
    ]
  })

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
  if (props.latitude && props.longitude) {
    createMarker([props.latitude, props.longitude])
  }

  // Handle search results
  map.on('geosearch/showlocation', async (event: any) => {
    const { location } = event
    if (location) {
      updateMarkerPosition([location.y, location.x])
      emit('update:latitude', location.y)
      emit('update:longitude', location.x)
      isSearching.value = false
      
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

// Save current location to favorites
function saveCurrentLocation() {
  if (props.latitude === null || props.longitude === null || !locationName.value.trim()) {
    return
  }
  
  const newLocation: SavedLocation = {
    id: Date.now().toString(),
    name: locationName.value.trim(),
    latitude: props.latitude,
    longitude: props.longitude,
    address: currentAddress.value || undefined
  }
  
  savedLocations.value.push(newLocation)
  localStorage.setItem('savedMapLocations', JSON.stringify(savedLocations.value))
  
  // Reset the dialog
  locationName.value = ''
  showSaveDialog.value = false
}

// Load a saved location
function loadSavedLocation(location: SavedLocation) {
  if (map) {
    const position: L.LatLngExpression = [location.latitude, location.longitude]
    updateMarkerPosition(position)
    emit('update:latitude', location.latitude)
    emit('update:longitude', location.longitude)
    currentAddress.value = location.address || null
    
    // Close the dropdown after selection
    showSavedLocationsDropdown.value = false
  }
}

// Delete a saved location
function deleteSavedLocation(id: string, event: Event) {
  event.stopPropagation() // Prevent triggering the parent click
  savedLocations.value = savedLocations.value.filter(location => location.id !== id)
  localStorage.setItem('savedMapLocations', JSON.stringify(savedLocations.value))
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

// Get current location from browser
function getCurrentLocation() {
  if (!navigator.geolocation) {
    console.error('Geolocation is not supported by this browser.')
    return
  }
  
  isLocating.value = true
  
  navigator.geolocation.getCurrentPosition(
    async (position) => {
      const { latitude, longitude } = position.coords
      
      if (map) {
        const latlng: L.LatLngExpression = [latitude, longitude]
        updateMarkerPosition(latlng)
        map.setView(latlng, 15)
        emit('update:latitude', latitude)
        emit('update:longitude', longitude)
        
        // Get address for the location
        const address = await reverseGeocode(latitude, longitude)
        currentAddress.value = address
      }
      
      isLocating.value = false
    },
    (error) => {
      console.error('Error getting current location:', error)
      isLocating.value = false
    },
    {
      enableHighAccuracy: true,
      timeout: 5000,
      maximumAge: 0
    }
  )
}
</script>

<template>
  <div class="w-full">
    <!-- Map Controls -->
    <div class="flex flex-wrap gap-2 mb-2">
      <Button
        type="button"
        size="sm"
        variant="outline"
        class="flex-1 text-xs"
        @click="getCurrentLocation"
        :disabled="isLocating"
      >
        <span v-if="isLocating">
          <Loader2 class="mr-1 h-3 w-3 animate-spin inline-block" />
          Locating...
        </span>
        <span v-else>
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="14"
            height="14"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            class="mr-1 inline-block"
          >
            <circle cx="12" cy="12" r="10" />
            <circle cx="12" cy="12" r="1" />
            <line x1="12" y1="2" x2="12" y2="4" />
            <line x1="12" y1="20" x2="12" y2="22" />
            <line x1="2" y1="12" x2="4" y2="12" />
            <line x1="20" y1="12" x2="22" y2="12" />
          </svg>
          Use My Location
        </span>
      </Button>
      
      <div class="relative flex-1">
        <Button
          type="button"
          size="sm"
          variant="outline"
          class="w-full text-xs"
          @click="showSavedLocationsDropdown = !showSavedLocationsDropdown"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="14"
            height="14"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            class="mr-1 inline-block"
          >
            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
            <circle cx="12" cy="10" r="3"></circle>
          </svg>
          Saved Locations
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
            class="ml-1 inline-block"
          >
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
        </Button>
        
        <!-- Saved Locations Dropdown -->
        <div
          v-if="showSavedLocationsDropdown"
          class="absolute z-10 mt-1 w-full bg-background border border-border rounded-md shadow-lg py-1 text-sm"
        >
          <div
            v-if="savedLocations.length === 0"
            class="px-3 py-2 text-muted-foreground text-center"
          >
            No saved locations
          </div>
          <div
            v-for="location in savedLocations"
            :key="location.id"
            class="px-3 py-2 hover:bg-accent cursor-pointer flex justify-between items-center"
            @click="loadSavedLocation(location)"
          >
            <div>
              <div class="font-medium">{{ location.name }}</div>
              <div class="text-xs text-muted-foreground truncate max-w-[200px]">
                {{ location.address || `${location.latitude.toFixed(4)}, ${location.longitude.toFixed(4)}` }}
              </div>
              <div class="text-xs text-primary-foreground/70 mt-1">
                <span class="bg-primary/10 rounded px-1 py-0.5 mr-1">{{ location.latitude.toFixed(4) }}</span>
                <span class="bg-primary/10 rounded px-1 py-0.5">{{ location.longitude.toFixed(4) }}</span>
              </div>
            </div>
            <button
              @click="deleteSavedLocation(location.id, $event)"
              class="text-muted-foreground hover:text-destructive p-1"
              title="Delete this location"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="14"
                height="14"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <path d="M3 6h18"></path>
                <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                <line x1="10" y1="11" x2="10" y2="17"></line>
                <line x1="14" y1="11" x2="14" y2="17"></line>
              </svg>
            </button>
          </div>
        </div>
      </div>
      
      <Button
        type="button"
        size="sm"
        variant="outline"
        class="flex-none text-xs"
        @click="showSaveDialog = !showSaveDialog"
        :disabled="!latitude || !longitude"
        title="Save current location"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          width="14"
          height="14"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        >
          <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
          <polyline points="17 21 17 13 7 13 7 21"></polyline>
          <polyline points="7 3 7 8 15 8"></polyline>
        </svg>
      </Button>
    </div>
    
    <!-- Save Location Dialog -->
    <div v-if="showSaveDialog" class="mb-2 p-3 border border-border rounded-md bg-muted/10">
      <h3 class="text-sm font-medium mb-2">Save Current Location</h3>
      <div class="space-y-2">
        <Input
          v-model="locationName"
          placeholder="Location name"
          class="text-sm"
        />
        <div class="flex justify-end space-x-2">
          <Button
            type="button"
            size="sm"
            variant="ghost"
            @click="showSaveDialog = false"
          >
            Cancel
          </Button>
          <Button
            type="button"
            size="sm"
            @click="saveCurrentLocation"
            :disabled="!locationName.trim()"
          >
            Save
          </Button>
        </div>
      </div>
    </div>
    
    <!-- Map Container -->
    <div
      ref="mapContainer"
      class="w-full rounded-md border border-border bg-muted/20 shadow-sm relative overflow-hidden"
      :style="{ height: mapHeight }"
    ></div>
    
    <!-- Search Box (Note: This is for UI purposes only, actual search is handled by Leaflet GeoSearch) -->
    <div class="relative mt-2">
      <Input
        type="text"
        ref="searchInput"
        placeholder="Search for location..."
        class="pr-8"
        disabled
      />
      <div class="absolute right-2 top-1/2 -translate-y-1/2 text-xs text-muted-foreground">
        Use search in map
      </div>
    </div>
    
    <!-- Address Display -->
    <div v-if="currentAddress || isLoadingAddress" class="mt-2 text-sm">
      <div v-if="isLoadingAddress" class="text-muted-foreground flex items-center">
        <Loader2 class="mr-1 h-3 w-3 animate-spin" /> Loading address...
      </div>
      <div v-else-if="currentAddress" class="text-foreground">
        <div class="font-medium text-xs uppercase tracking-wide text-muted-foreground mb-1">
          Address
          <button 
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
            <Button size="sm" class="h-8 px-2" @click="saveEditedAddress">Save</Button>
            <Button size="sm" variant="ghost" class="h-8 px-2" @click="cancelEditAddress">Cancel</Button>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Coordinates Display -->
    <div class="flex items-center justify-between mt-2 text-sm text-muted-foreground">
      <span>Latitude: {{ latitude?.toFixed(6) || 'Not set' }}</span>
      <span>Longitude: {{ longitude?.toFixed(6) || 'Not set' }}</span>
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

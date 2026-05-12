<template>
  <div class="space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-display font-bold">💰 Soumettre des prix</h2>
        <p class="text-gray-500 text-sm mt-1">Tu es en jeu ? Renseigne les prix HDV en temps réel pour la communauté</p>
      </div>
      <div class="flex items-center gap-2 text-xs text-gray-600">
        <kbd class="px-2 py-1 bg-gray-800 rounded">Tab</kbd> suivant
        <kbd class="px-2 py-1 bg-gray-800 rounded">Enter</kbd> ajouter
        <kbd class="px-2 py-1 bg-gray-800 rounded">Ctrl+S</kbd> envoyer tout
      </div>
    </div>

    <!-- Sélecteur serveur -->
    <div class="flex gap-2">
      <button v-for="srv in servers" :key="srv.id"
              @click="selectedServer = srv.id"
              :class="selectedServer === srv.id
                ? 'bg-amber-500 text-gray-900 border-amber-500'
                : 'bg-gray-900 text-gray-400 border-gray-700 hover:border-gray-500'"
              class="px-5 py-2.5 rounded-xl border text-sm font-semibold transition-all">
        {{ srv.label }}
      </button>
    </div>

    <!-- Formulaire saisie rapide -->
    <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6">
      <h3 class="text-sm font-semibold text-gray-400 mb-4">Saisie rapide</h3>

      <div class="flex gap-3 items-end">
        <!-- Recherche item avec autocomplete -->
        <div class="flex-1 relative">
          <label class="block text-xs text-gray-500 mb-1.5">Item</label>
          <input ref="itemInput"
                 v-model="searchQuery"
                 @input="searchItems"
                 @keydown.enter.prevent="selectFirstResult"
                 @keydown.escape="showDropdown = false"
                 @keydown.tab.prevent="selectFirstResult"
                 placeholder="Rechercher un item (ex: Fer, Bronze...)"
                 class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white placeholder-gray-600 focus:border-amber-500 focus:outline-none" />

          <!-- Dropdown autocomplete -->
          <div v-if="showDropdown && searchResults.length" 
               class="absolute top-full left-0 right-0 mt-1 bg-gray-800 border border-gray-700 rounded-xl overflow-hidden z-20 shadow-xl">
            <button v-for="item in searchResults.slice(0, 8)" :key="item.id"
                    @click="selectItem(item)"
                    class="w-full flex items-center gap-3 px-4 py-3 hover:bg-gray-700 transition-colors text-left">
              <img :src="item.image_url" class="h-8 w-8 rounded-lg bg-gray-900 object-contain" @error="onImgError" />
              <div>
                <div class="text-white text-sm font-medium">{{ item.name }}</div>
                <div class="text-xs text-gray-500 capitalize">{{ item.type }} • Niv. {{ item.level }}</div>
              </div>
              <div class="ml-auto text-xs text-amber-400">
                {{ item.price ? formatKamas(item.price) : '—' }}
              </div>
            </button>
          </div>
        </div>

        <!-- Prix x1 -->
        <div class="w-44">
          <label class="block text-xs text-gray-500 mb-1.5">Prix × 1 (kamas)</label>
          <input ref="priceInput"
                 v-model="currentPrice"
                 @keydown.enter.prevent="addToQueue"
                 type="number" min="1"
                 placeholder="Ex: 250"
                 class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white placeholder-gray-600 focus:border-amber-500 focus:outline-none text-right font-mono" />
        </div>

        <!-- Bouton ajouter -->
        <button @click="addToQueue"
                :disabled="!selectedItem || !currentPrice"
                class="px-5 py-3 bg-amber-500 hover:bg-amber-400 disabled:opacity-40 text-gray-900 font-bold rounded-xl transition-colors whitespace-nowrap">
          + Ajouter
        </button>
      </div>

      <!-- Item sélectionné affiché -->
      <div v-if="selectedItem" class="mt-3 flex items-center gap-3 text-sm">
        <img :src="selectedItem.image_url" class="h-7 w-7 rounded-lg bg-gray-800 object-contain" @error="onImgError" />
        <span class="text-amber-400 font-semibold">{{ selectedItem.name }}</span>
        <span class="text-gray-500">sélectionné</span>
      </div>
    </div>

    <!-- File d'attente -->
    <div v-if="queue.length > 0" class="bg-gray-900 border border-gray-800 rounded-3xl p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-semibold text-gray-400">
          📋 File d'envoi ({{ queue.length }} item{{ queue.length > 1 ? 's' : '' }})
        </h3>
        <div class="flex gap-2">
          <button @click="queue = []" class="text-xs text-gray-500 hover:text-red-400 transition-colors px-3 py-1 rounded-lg hover:bg-red-500/10">
            Tout vider
          </button>
          <button @click="submitAll"
                  :disabled="submitting"
                  class="px-5 py-2 bg-amber-500 hover:bg-amber-400 disabled:opacity-50 text-gray-900 text-sm font-bold rounded-xl transition-colors flex items-center gap-2">
            <span v-if="submitting" class="animate-spin h-3 w-3 rounded-full border-2 border-gray-900 border-t-transparent"></span>
            {{ submitting ? 'Envoi...' : '🚀 Envoyer tout' }}
            <kbd class="text-xs opacity-70 ml-1">Ctrl+S</kbd>
          </button>
        </div>
      </div>

      <div class="space-y-2">
        <div v-for="(entry, i) in queue" :key="i"
             class="flex items-center gap-4 bg-gray-800/50 rounded-xl px-4 py-3">
          <img :src="entry.item.image_url" class="h-9 w-9 rounded-lg bg-gray-900 object-contain" @error="onImgError" />
          <div class="flex-1">
            <span class="font-medium text-white">{{ entry.item.name }}</span>
            <span class="text-gray-600 text-sm ml-2 capitalize">{{ entry.item.type }}</span>
          </div>
          <!-- Prix éditable inline -->
          <div class="flex items-center gap-2">
            <input v-model="entry.price_1" type="number"
                   class="w-32 bg-gray-900 border border-gray-700 rounded-lg px-3 py-1.5 text-amber-400 font-mono text-sm text-right focus:border-amber-500 focus:outline-none" />
            <span class="text-xs text-gray-600">kamas</span>
          </div>
          <button @click="queue.splice(i, 1)" class="text-gray-600 hover:text-red-400 transition-colors ml-2 text-lg leading-none">×</button>
        </div>
      </div>
    </div>

    <!-- Résultat envoi -->
    <div v-if="submitResult" class="bg-gray-900 border rounded-2xl p-5 flex items-center gap-4"
         :class="submitResult.success ? 'border-emerald-500/30' : 'border-red-500/30'">
      <span class="text-3xl">{{ submitResult.success ? '✅' : '❌' }}</span>
      <div>
        <p class="font-semibold text-white">{{ submitResult.message }}</p>
        <p class="text-xs text-gray-500 mt-0.5">Merci de contribuer aux prix en temps réel ! 🎮</p>
      </div>
      <button @click="submitResult = null" class="ml-auto text-gray-500 hover:text-white text-xl leading-none">×</button>
    </div>

    <!-- Conseils -->
    <div class="bg-gray-900/50 border border-gray-800 rounded-2xl p-5">
      <h3 class="text-sm font-semibold text-gray-500 mb-3">💡 Comment contribuer efficacement</h3>
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs text-gray-600">
        <div class="flex gap-2">
          <span class="text-amber-500">1.</span>
          <span>Ouvre l'HDV en jeu et cherche un item</span>
        </div>
        <div class="flex gap-2">
          <span class="text-amber-500">2.</span>
          <span>Note le prix × 1 affiché dans l'HDV</span>
        </div>
        <div class="flex gap-2">
          <span class="text-amber-500">3.</span>
          <span>Saisis-le ici et envoie (Ctrl+S) — 10 secondes maxi</span>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  server: { type: String, default: 'draconiros' }
})

const servers = [
  { id: 'draconiros', label: '🐉 Draconiros' },
  { id: 'ombre',      label: '🌑 Ombre' },
  { id: 'hellmina',   label: '⚡ Hellmina' },
  { id: 'orukam',     label: '🔥 Orukam' },
]

const selectedServer  = ref(props.server)
const searchQuery     = ref('')
const searchResults   = ref([])
const showDropdown    = ref(false)
const selectedItem    = ref(null)
const currentPrice    = ref('')
const queue           = ref([])
const submitting      = ref(false)
const submitResult    = ref(null)
const allItems        = ref([])
const itemInput       = ref(null)
const priceInput      = ref(null)

let searchTimeout

async function loadAllItems() {
  const res = await fetch(`/api/v1/items?server=${selectedServer.value}`)
  allItems.value = await res.json()
}

function searchItems() {
  clearTimeout(searchTimeout)
  if (!searchQuery.value.trim()) {
    showDropdown.value = false
    return
  }
  searchTimeout = setTimeout(() => {
    const q = searchQuery.value.toLowerCase()
    searchResults.value = allItems.value.filter(i =>
      i.name.toLowerCase().includes(q)
    )
    showDropdown.value = searchResults.value.length > 0
  }, 150)
}

function selectFirstResult() {
  if (searchResults.value.length) {
    selectItem(searchResults.value[0])
  }
}

function selectItem(item) {
  selectedItem.value = item
  searchQuery.value  = item.name
  showDropdown.value = false
  // Focus sur le champ prix
  setTimeout(() => priceInput.value?.focus(), 50)
}

function addToQueue() {
  if (!selectedItem.value || !currentPrice.value) return

  // Éviter les doublons
  const exists = queue.value.find(e => e.item.id === selectedItem.value.id)
  if (exists) {
    exists.price_1 = parseInt(currentPrice.value)
  } else {
    queue.value.push({
      item:    selectedItem.value,
      price_1: parseInt(currentPrice.value),
    })
  }

  // Reset pour saisie suivante
  selectedItem.value = null
  searchQuery.value  = ''
  currentPrice.value = ''
  searchResults.value = []
  setTimeout(() => itemInput.value?.focus(), 50)
}

async function submitAll() {
  if (!queue.value.length) return
  submitting.value  = true
  submitResult.value = null

  try {
    const res = await fetch('/api/v1/prices/bulk', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
      },
      body: JSON.stringify({
        server: selectedServer.value,
        prices: queue.value.map(e => ({
          item_id: e.item.id,
          price_1: parseInt(e.price_1),
        })),
      }),
    })
    submitResult.value = await res.json()
    if (submitResult.value.success) {
      queue.value = []
      await loadAllItems() // Refresh prix affichés
    }
  } catch (e) {
    submitResult.value = { success: false, message: 'Erreur réseau: ' + e.message }
  } finally {
    submitting.value = false
  }
}

// Raccourci clavier Ctrl+S
function onKeydown(e) {
  if ((e.ctrlKey || e.metaKey) && e.key === 's') {
    e.preventDefault()
    submitAll()
  }
}

function onImgError(e) {
  e.target.src = `data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='64' height='64'%3E%3Crect width='64' height='64' rx='12' fill='%231f2937'/%3E%3Ctext x='50%25' y='55%25' font-size='28' text-anchor='middle' dominant-baseline='middle' fill='%236b7280'%3E%3F%3C/text%3E%3C/svg%3E`
  e.target.onerror = null
}

function formatKamas(amount) {
  if (!amount) return '—'
  if (amount >= 1_000_000) return (amount / 1_000_000).toFixed(1) + ' MK'
  if (amount >= 1_000) return (amount / 1_000).toFixed(0) + ' K'
  return amount + ' K'
}

onMounted(() => {
  loadAllItems()
  window.addEventListener('keydown', onKeydown)
})
onUnmounted(() => {
  window.removeEventListener('keydown', onKeydown)
})
</script>

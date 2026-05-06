<template>
  <div class="min-h-screen bg-gray-950 text-white font-body">
    <!-- Header -->
    <header class="border-b border-gray-800 bg-gray-950/80 backdrop-blur sticky top-0 z-40">
      <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="h-9 w-9 rounded-xl bg-gradient-to-br from-amber-400 to-orange-600 flex items-center justify-center text-lg">⚔️</div>
          <span class="text-xl font-display font-bold text-white">DofusValue</span>
          <span class="text-xs px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-400 font-semibold">BETA</span>
        </div>
        <!-- Server selector -->
        <select v-model="server" @change="loadAll"
                class="bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-300 focus:border-amber-500 focus:outline-none">
          <option value="draconiros">🐉 Draconiros</option>
          <option value="ombre">🌑 Ombre</option>
          <option value="hellmina">⚡ Hellmina</option>
        </select>
      </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 py-8">
      <!-- Tabs -->
      <div class="flex gap-2 mb-8 bg-gray-900 rounded-2xl p-1.5 w-fit">
        <button v-for="tab in tabs" :key="tab.id"
                @click="activeTab = tab.id"
                :class="activeTab === tab.id
                  ? 'bg-amber-500 text-gray-900 shadow-lg shadow-amber-500/30'
                  : 'text-gray-400 hover:text-white'"
                class="px-5 py-2.5 rounded-xl text-sm font-semibold transition-all">
          {{ tab.icon }} {{ tab.label }}
        </button>
      </div>

      <!-- TRENDING TAB -->
      <div v-if="activeTab === 'trending'">
        <div class="mb-6 flex items-center justify-between">
          <h2 class="text-2xl font-display font-bold">🔥 Tendances du marché</h2>
          <span class="text-xs text-gray-500">MAJ toutes les 5 min</span>
        </div>

        <div v-if="loadingTrending" class="flex items-center justify-center h-48">
          <div class="animate-spin h-8 w-8 rounded-full border-2 border-amber-500 border-t-transparent"></div>
        </div>

        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <ItemCard v-for="item in trending" :key="item.id" :item="item" />
        </div>
      </div>

      <!-- ITEMS TAB -->
      <div v-if="activeTab === 'items'">
        <div class="mb-6 flex flex-col sm:flex-row gap-4">
          <input v-model="search" @input="debouncedSearch"
                 placeholder="Rechercher un item..."
                 class="flex-1 bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:border-amber-500 focus:outline-none" />
          <select v-model="filterType" @change="loadItems"
                  class="bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 text-gray-300 focus:border-amber-500 focus:outline-none">
            <option value="">Tous les types</option>
            <option value="ressource">Ressources</option>
            <option value="parchemin">Parchemins</option>
            <option value="consommable">Consommables</option>
            <option value="anneau">Anneaux</option>
            <option value="cape">Capes</option>
            <option value="ceinture">Ceintures</option>
          </select>
        </div>

        <div v-if="loadingItems" class="flex items-center justify-center h-48">
          <div class="animate-spin h-8 w-8 rounded-full border-2 border-amber-500 border-t-transparent"></div>
        </div>

        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <ItemCard v-for="item in items" :key="item.id" :item="item" @click="openItem(item)" />
        </div>
      </div>

      <!-- CRAFT TAB -->
      <div v-if="activeTab === 'craft'">
        <div class="mb-6">
          <h2 class="text-2xl font-display font-bold mb-2">⚗️ Opportunités de craft</h2>
          <p class="text-gray-500 text-sm">Items plus rentables à crafter qu'à acheter à l'HDV</p>
        </div>

        <div v-if="loadingCraft" class="flex items-center justify-center h-48">
          <div class="animate-spin h-8 w-8 rounded-full border-2 border-amber-500 border-t-transparent"></div>
        </div>

        <div v-else-if="craftOps.length === 0" class="text-center py-16 text-gray-600">
          <div class="text-5xl mb-4">⚗️</div>
          <p>Aucune opportunité de craft pour le moment</p>
        </div>

        <div v-else class="space-y-3">
          <div v-for="item in craftOps" :key="item.id"
               class="bg-gray-900 border border-gray-800 rounded-2xl p-5 hover:border-amber-500/40 transition-all">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-4">
                <img :src="item.image_url" :alt="item.name"
                     class="h-12 w-12 rounded-xl bg-gray-800 object-contain"
                     @error="$event.target.src='https://via.placeholder.com/48'" />
                <div>
                  <h3 class="font-semibold text-white">{{ item.name }}</h3>
                  <p class="text-xs text-gray-500 capitalize">{{ item.type }} • Niv. {{ item.level }}</p>
                </div>
              </div>
              <div class="text-right">
                <div class="text-2xl font-display font-bold"
                     :class="item.craft.margin > 0 ? 'text-emerald-400' : 'text-red-400'">
                  {{ item.craft.margin > 0 ? '+' : '' }}{{ formatKamas(item.craft.margin) }}
                </div>
                <div class="text-xs text-gray-500">
                  Craft: {{ formatKamas(item.craft.craft_cost) }} → Vente: {{ formatKamas(item.craft.sell_price) }}
                </div>
                <span class="inline-block mt-1 px-2 py-0.5 rounded-full text-xs font-bold"
                      :class="item.craft.margin_percent > 0 ? 'bg-emerald-500/20 text-emerald-400' : 'bg-red-500/20 text-red-400'">
                  {{ item.craft.margin_percent > 0 ? '+' : '' }}{{ item.craft.margin_percent }}%
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import ItemCard from '../components/ItemCard.vue'

const server = ref('draconiros')
const activeTab = ref('trending')
const search = ref('')
const filterType = ref('')

const trending = ref([])
const items = ref([])
const craftOps = ref([])

const loadingTrending = ref(false)
const loadingItems = ref(false)
const loadingCraft = ref(false)

const tabs = [
  { id: 'trending', icon: '🔥', label: 'Tendances' },
  { id: 'items',    icon: '📦', label: 'Items' },
  { id: 'craft',    icon: '⚗️', label: 'Craft' },
]

async function loadTrending() {
  loadingTrending.value = true
  try {
    const res = await fetch(`/api/v1/items/trending?server=${server.value}`)
    trending.value = await res.json()
  } finally {
    loadingTrending.value = false
  }
}

async function loadItems() {
  loadingItems.value = true
  try {
    const params = new URLSearchParams({ server: server.value })
    if (search.value) params.set('q', search.value)
    if (filterType.value) params.set('type', filterType.value)
    const res = await fetch(`/api/v1/items?${params}`)
    items.value = await res.json()
  } finally {
    loadingItems.value = false
  }
}

async function loadCraft() {
  loadingCraft.value = true
  try {
    const res = await fetch(`/api/v1/items/craft?server=${server.value}`)
    craftOps.value = await res.json()
  } finally {
    loadingCraft.value = false
  }
}

function loadAll() {
  loadTrending()
  loadItems()
  loadCraft()
}

let searchTimeout
function debouncedSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => loadItems(), 400)
}

function formatKamas(amount) {
  if (!amount) return '0 K'
  if (amount >= 1_000_000) return (amount / 1_000_000).toFixed(1) + ' MK'
  if (amount >= 1_000) return (amount / 1_000).toFixed(0) + ' K'
  return amount + ' K'
}

function openItem(item) {
  window.location.href = `/items/${item.slug}`
}

onMounted(() => loadAll())
</script>

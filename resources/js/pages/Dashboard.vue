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
        <!-- Server selector + Auth -->
        <div class="flex items-center gap-3">
          <select v-model="server" @change="loadAll"
                  class="bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-300 focus:border-amber-500 focus:outline-none">
            <option value="draconiros">🐉 Draconiros</option>
            <option value="ombre">🌑 Ombre</option>
            <option value="hellmina">⚡ Hellmina</option>
            <option value="orukam">🔥 Orukam</option>
          </select>
          <a v-if="!authUser" href="/login"
             class="px-4 py-2 bg-amber-500 hover:bg-amber-400 text-gray-900 text-sm font-bold rounded-lg transition-colors">
            Connexion
          </a>
          <div v-else class="flex items-center gap-2">
            <span class="text-xs text-gray-400">👤 {{ authUser }}</span>
            <form method="POST" action="/logout" class="inline">
              <input type="hidden" name="_token" :value="csrfToken" />
              <button type="submit" class="text-xs text-gray-500 hover:text-white transition-colors">Déconnexion</button>
            </form>
          </div>
        </div>
      </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 py-8">
      <!-- Tabs -->
      <div class="flex flex-wrap gap-2 mb-8 bg-gray-900 rounded-2xl p-1.5 w-fit">
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
          <span class="text-xs text-gray-500">MAJ toutes les heures</span>
        </div>
        <div v-if="loadingTrending" class="flex items-center justify-center h-48">
          <div class="animate-spin h-8 w-8 rounded-full border-2 border-amber-500 border-t-transparent"></div>
        </div>
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <ItemCard v-for="item in trending" :key="item.id" :item="item" @click="openItem(item)" />
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
          <p class="text-gray-500 text-sm">Items plus rentables à crafter qu'à acheter</p>
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
               class="bg-gray-900 border border-gray-800 rounded-2xl p-5 hover:border-amber-500/40 transition-all cursor-pointer"
               @click="openItem(item)">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-4">
                <img :src="item.image_url" :alt="item.name" class="h-12 w-12 rounded-xl bg-gray-800 object-contain" @error="onImgError" />
                <div>
                  <h3 class="font-semibold text-white">{{ item.name }}</h3>
                  <p class="text-xs text-gray-500 capitalize">{{ item.type }} • Niv. {{ item.level }}</p>
                </div>
              </div>
              <div class="text-right">
                <div class="text-2xl font-display font-bold"
                     :class="item.craft?.margin > 0 ? 'text-emerald-400' : 'text-red-400'">
                  {{ item.craft?.margin > 0 ? '+' : '' }}{{ formatKamas(item.craft?.margin) }}
                </div>
                <span class="inline-block mt-1 px-2 py-0.5 rounded-full text-xs font-bold"
                      :class="item.craft?.margin_percent > 0 ? 'bg-emerald-500/20 text-emerald-400' : 'bg-red-500/20 text-red-400'">
                  {{ item.craft?.margin_percent > 0 ? '+' : '' }}{{ item.craft?.margin_percent }}%
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- COMPARATEUR TAB -->
      <div v-if="activeTab === 'compare'">
        <div class="mb-6">
          <h2 class="text-2xl font-display font-bold mb-2">⚖️ Comparateur de serveurs</h2>
          <p class="text-gray-500 text-sm">Comparez les prix entre Draconiros, Ombre, Hellmina et Orukam</p>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-gray-800">
                <th class="text-left py-3 px-4 text-gray-400 font-semibold">Item</th>
                <th class="py-3 px-4 text-amber-400 font-semibold">🐉 Draconiros</th>
                <th class="py-3 px-4 text-gray-400 font-semibold">🌑 Ombre</th>
                <th class="py-3 px-4 text-yellow-400 font-semibold">⚡ Hellmina</th>
                <th class="py-3 px-4 text-orange-400 font-semibold">🔥 Orukam</th>
                <th class="py-3 px-4 text-emerald-400 font-semibold">Meilleur</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loadingCompare" class="text-center">
                <td colspan="6" class="py-8">
                  <div class="animate-spin h-6 w-6 rounded-full border-2 border-amber-500 border-t-transparent mx-auto"></div>
                </td>
              </tr>
              <tr v-for="row in compareData" :key="row.item.id"
                  class="border-b border-gray-800/50 hover:bg-gray-900/50 transition-colors">
                <td class="py-3 px-4">
                  <div class="flex items-center gap-3">
                    <img :src="row.item.image_url" class="h-8 w-8 rounded-lg bg-gray-800 object-contain" @error="onImgError" />
                    <span class="font-medium text-white">{{ row.item.name }}</span>
                  </div>
                </td>
                <td v-for="srv in ['draconiros','ombre','hellmina','orukam']" :key="srv"
                    class="py-3 px-4 text-center"
                    :class="row.best === srv ? 'text-emerald-400 font-bold' : 'text-gray-400'">
                  {{ row.prices[srv] ? formatKamas(row.prices[srv]) : '—' }}
                </td>
                <td class="py-3 px-4 text-center">
                  <span class="px-2 py-1 rounded-lg text-xs font-bold bg-emerald-500/20 text-emerald-400 capitalize">
                    {{ row.best || '—' }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ALERTES TAB -->
      <div v-if="activeTab === 'alerts'">
        <div class="mb-6 flex items-center justify-between">
          <h2 class="text-2xl font-display font-bold">🔔 Alertes de prix</h2>
          <button @click="showAlertForm = !showAlertForm"
                  class="px-4 py-2 bg-amber-500 hover:bg-amber-400 text-gray-900 rounded-xl text-sm font-bold transition-colors">
            + Nouvelle alerte
          </button>
        </div>

        <!-- Formulaire nouvelle alerte -->
        <div v-if="showAlertForm" class="bg-gray-900 border border-amber-500/30 rounded-2xl p-6 mb-6">
          <h3 class="font-semibold mb-4 text-amber-400">Créer une alerte</h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="text-xs text-gray-400 mb-1 block">Item</label>
              <select v-model="alertForm.item_id" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-white focus:border-amber-500 focus:outline-none">
                <option value="">Sélectionner un item</option>
                <option v-for="item in items" :key="item.id" :value="item.id">{{ item.name }}</option>
              </select>
            </div>
            <div>
              <label class="text-xs text-gray-400 mb-1 block">Serveur</label>
              <select v-model="alertForm.server" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-white focus:border-amber-500 focus:outline-none">
                <option value="draconiros">🐉 Draconiros</option>
                <option value="ombre">🌑 Ombre</option>
                <option value="hellmina">⚡ Hellmina</option>
                <option value="orukam">🔥 Orukam</option>
              </select>
            </div>
            <div>
              <label class="text-xs text-gray-400 mb-1 block">Prix seuil (kamas)</label>
              <input v-model="alertForm.threshold_price" type="number" placeholder="Ex: 50000"
                     class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-white focus:border-amber-500 focus:outline-none" />
            </div>
            <div>
              <label class="text-xs text-gray-400 mb-1 block">Condition</label>
              <select v-model="alertForm.direction" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-white focus:border-amber-500 focus:outline-none">
                <option value="below">En dessous du seuil</option>
                <option value="above">Au dessus du seuil</option>
              </select>
            </div>
            <div class="sm:col-span-2">
              <label class="text-xs text-gray-400 mb-1 block">Email (optionnel)</label>
              <input v-model="alertForm.email" type="email" placeholder="ton@email.com"
                     class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-white focus:border-amber-500 focus:outline-none" />
            </div>
          </div>
          <div class="flex gap-3 mt-4">
            <button @click="createAlert"
                    class="px-6 py-2 bg-amber-500 hover:bg-amber-400 text-gray-900 rounded-xl text-sm font-bold transition-colors">
              Créer l'alerte
            </button>
            <button @click="showAlertForm = false"
                    class="px-6 py-2 bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-xl text-sm font-semibold transition-colors">
              Annuler
            </button>
          </div>
        </div>

        <!-- Liste alertes -->
        <div v-if="loadingAlerts" class="flex items-center justify-center h-48">
          <div class="animate-spin h-8 w-8 rounded-full border-2 border-amber-500 border-t-transparent"></div>
        </div>
        <div v-else-if="alerts.length === 0" class="text-center py-16 text-gray-600">
          <div class="text-5xl mb-4">🔔</div>
          <p>Aucune alerte active</p>
        </div>
        <div v-else class="space-y-3">
          <div v-for="alert in alerts" :key="alert.id"
               class="bg-gray-900 border border-gray-800 rounded-2xl p-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
              <img :src="alert.item.image_url" class="h-10 w-10 rounded-xl bg-gray-800 object-contain" @error="onImgError" />
              <div>
                <div class="font-semibold text-white">{{ alert.item.name }}</div>
                <div class="text-xs text-gray-500 capitalize">
                  {{ alert.server }} • {{ alert.direction === 'below' ? '↓' : '↑' }} {{ formatKamas(alert.threshold_price) }}
                </div>
              </div>
            </div>
            <button @click="deleteAlert(alert.id)"
                    class="text-red-400 hover:text-red-300 text-sm px-3 py-1 rounded-lg hover:bg-red-500/10 transition-colors">
              Supprimer
            </button>
          </div>
        </div>
      </div>

    <!-- SUBMIT TAB -->
      <div v-if="activeTab === 'submit'">
        <PriceSubmit :server="server" />
      </div>

    <!-- IMPORT TAB -->
      <div v-if="activeTab === 'import'">
        <CsvImport />
      </div>

    </div>

    <!-- Modal détail item -->
    <ItemDetail v-if="selectedItem" :item="selectedItem" :server="server" @close="selectedItem = null" />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import ItemCard from '../components/ItemCard.vue'
import ItemDetail from '../components/ItemDetail.vue'
import CsvImport from '../components/CsvImport.vue'
import PriceSubmit from '../components/PriceSubmit.vue'

const server       = ref('draconiros')
const activeTab    = ref('trending')
const search       = ref('')
const filterType   = ref('')
const selectedItem = ref(null)
const showAlertForm = ref(false)
const authUser     = ref(window.__AUTH_USER__ || null)
const csrfToken    = ref(document.querySelector('meta[name="csrf-token"]')?.content || '')

const trending     = ref([])
const items        = ref([])
const craftOps     = ref([])
const compareData  = ref([])
const alerts       = ref([])

const loadingTrending = ref(false)
const loadingItems    = ref(false)
const loadingCraft    = ref(false)
const loadingCompare  = ref(false)
const loadingAlerts   = ref(false)

const alertForm = ref({
  item_id: '', server: 'draconiros', threshold_price: '', direction: 'below', email: ''
})

const tabs = [
  { id: 'trending', icon: '🔥', label: 'Tendances' },
  { id: 'items',    icon: '📦', label: 'Items' },
  { id: 'craft',    icon: '⚗️', label: 'Craft' },
  { id: 'compare',  icon: '⚖️', label: 'Comparer' },
  { id: 'alerts',   icon: '🔔', label: 'Alertes' },
  { id: 'submit',   icon: '💰', label: 'Soumettre prix' },
  { id: 'import',   icon: '📥', label: 'Import CSV' },
]

async function loadTrending() {
  loadingTrending.value = true
  try {
    const res = await fetch(`/api/v1/items/trending?server=${server.value}`)
    trending.value = await res.json()
  } finally { loadingTrending.value = false }
}

async function loadItems() {
  loadingItems.value = true
  try {
    const params = new URLSearchParams({ server: server.value })
    if (search.value) params.set('q', search.value)
    if (filterType.value) params.set('type', filterType.value)
    const res = await fetch(`/api/v1/items?${params}`)
    items.value = await res.json()
  } finally { loadingItems.value = false }
}

async function loadCraft() {
  loadingCraft.value = true
  try {
    const res = await fetch(`/api/v1/items/craft?server=${server.value}`)
    craftOps.value = await res.json()
  } finally { loadingCraft.value = false }
}

async function loadCompare() {
  loadingCompare.value = true
  try {
    const servers = ['draconiros', 'ombre', 'hellmina', 'orukam']
    const allItems = await (await fetch('/api/v1/items?server=draconiros')).json()

    const rows = await Promise.all(allItems.slice(0, 15).map(async (item) => {
      const prices = {}
      await Promise.all(servers.map(async (srv) => {
        const res = await fetch(`/api/v1/items/${item.slug}?server=${srv}`)
        const data = await res.json()
        prices[srv] = data.price || null
      }))

      // Trouver le serveur le moins cher
      const validPrices = Object.entries(prices).filter(([, v]) => v)
      const best = validPrices.sort(([, a], [, b]) => a - b)[0]?.[0] || null

      return { item, prices, best }
    }))

    compareData.value = rows
  } finally { loadingCompare.value = false }
}

async function loadAlerts() {
  loadingAlerts.value = true
  try {
    const res = await fetch('/api/v1/alerts')
    alerts.value = await res.json()
  } finally { loadingAlerts.value = false }
}

async function createAlert() {
  if (!alertForm.value.item_id || !alertForm.value.threshold_price) return

  await fetch('/api/v1/alerts', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
    body: JSON.stringify(alertForm.value)
  })

  showAlertForm.value = false
  alertForm.value = { item_id: '', server: 'draconiros', threshold_price: '', direction: 'below', email: '' }
  loadAlerts()
}

async function deleteAlert(id) {
  await fetch(`/api/v1/alerts/${id}`, {
    method: 'DELETE',
    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content }
  })
  loadAlerts()
}

function loadAll() {
  loadTrending()
  loadItems()
  loadCraft()
  loadCompare()
  loadAlerts()
}

let searchTimeout
function debouncedSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => loadItems(), 400)
}

function formatKamas(amount) {
  if (!amount) return '—'
  if (amount >= 1_000_000) return (amount / 1_000_000).toFixed(1) + ' MK'
  if (amount >= 1_000) return (amount / 1_000).toFixed(0) + ' K'
  return amount + ' K'
}

function openItem(item) {
  selectedItem.value = item
}

function onImgError(event) {
  const svg = `data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='64' height='64'%3E%3Crect width='64' height='64' rx='12' fill='%231f2937'/%3E%3Ctext x='50%25' y='55%25' font-size='28' text-anchor='middle' dominant-baseline='middle' fill='%236b7280'%3E%3F%3C/text%3E%3C/svg%3E`
  event.target.src = svg
  event.target.onerror = null
}

onMounted(() => loadAll())
</script>

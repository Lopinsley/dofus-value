<template>
  <!-- Backdrop -->
  <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm"
       @click.self="$emit('close')">
    <div class="bg-gray-900 border border-gray-700 rounded-3xl w-full max-w-2xl max-h-[90vh] overflow-y-auto shadow-2xl">

      <!-- Header -->
      <div class="flex items-center justify-between p-6 border-b border-gray-800">
        <div class="flex items-center gap-4">
          <img :src="item.image_url" :alt="item.name"
               class="h-14 w-14 rounded-xl bg-gray-800 object-contain"
               @error="onImgError" />
          <div>
            <h2 class="text-xl font-display font-bold text-white">{{ item.name }}</h2>
            <p class="text-sm text-gray-500 capitalize">{{ item.type }} • Niveau {{ item.level }} • {{ serverLabel }}</p>
          </div>
        </div>
        <button @click="$emit('close')" class="text-gray-500 hover:text-white transition-colors text-2xl leading-none">✕</button>
      </div>

      <!-- Prix actuels -->
      <div class="grid grid-cols-3 gap-4 p-6 border-b border-gray-800">
        <div class="bg-gray-800/50 rounded-2xl p-4 text-center">
          <div class="text-xs text-gray-500 mb-1">Prix x1</div>
          <div class="text-xl font-display font-bold text-amber-400">{{ formatKamas(detail?.price) }}</div>
          <div class="text-xs mt-1" :class="trendClass">{{ trendLabel }}</div>
        </div>
        <div class="bg-gray-800/50 rounded-2xl p-4 text-center">
          <div class="text-xs text-gray-500 mb-1">Prix x10</div>
          <div class="text-lg font-display font-bold text-gray-300">{{ formatKamas(detail?.price_x10) }}</div>
        </div>
        <div class="bg-gray-800/50 rounded-2xl p-4 text-center">
          <div class="text-xs text-gray-500 mb-1">Prix x100</div>
          <div class="text-lg font-display font-bold text-gray-300">{{ formatKamas(detail?.price_x100) }}</div>
        </div>
      </div>

      <!-- Graphique historique -->
      <div class="p-6 border-b border-gray-800">
        <h3 class="text-sm font-semibold text-gray-400 mb-4">📈 Historique des prix (7 jours)</h3>
        <div v-if="loadingDetail" class="flex items-center justify-center h-32">
          <div class="animate-spin h-6 w-6 rounded-full border-2 border-amber-500 border-t-transparent"></div>
        </div>
        <div v-else-if="chartData.length > 0" class="relative h-40">
          <!-- SVG chart simple -->
          <svg class="w-full h-full" viewBox="0 0 400 120" preserveAspectRatio="none">
            <!-- Grid lines -->
            <line v-for="i in 4" :key="i"
                  x1="0" :y1="i * 30" x2="400" :y2="i * 30"
                  stroke="#374151" stroke-width="0.5" />
            <!-- Price line -->
            <polyline :points="chartPoints"
                      fill="none" stroke="#f59e0b" stroke-width="2.5"
                      stroke-linecap="round" stroke-linejoin="round" />
            <!-- Area fill -->
            <polygon :points="chartFill" fill="url(#goldGradient)" opacity="0.3" />
            <defs>
              <linearGradient id="goldGradient" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#f59e0b" />
                <stop offset="100%" stop-color="#f59e0b" stop-opacity="0" />
              </linearGradient>
            </defs>
            <!-- Data points -->
            <circle v-for="(pt, i) in chartPointsArr" :key="i"
                    :cx="pt.x" :cy="pt.y" r="3" fill="#f59e0b" />
          </svg>
          <!-- Labels dates -->
          <div class="flex justify-between text-xs text-gray-600 mt-1">
            <span v-for="(d, i) in chartLabels" :key="i">{{ d }}</span>
          </div>
        </div>
        <p v-else class="text-gray-600 text-sm text-center py-8">Pas assez de données pour afficher le graphique</p>
      </div>

      <!-- Alerte rapide -->
      <div class="p-6">
        <h3 class="text-sm font-semibold text-gray-400 mb-3">🔔 Créer une alerte rapide</h3>
        <div class="flex gap-3">
          <input v-model="alertPrice" type="number" placeholder="Prix seuil en kamas"
                 class="flex-1 bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-white text-sm focus:border-amber-500 focus:outline-none" />
          <select v-model="alertDirection"
                  class="bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-gray-300 text-sm focus:border-amber-500 focus:outline-none">
            <option value="below">↓ En dessous</option>
            <option value="above">↑ Au dessus</option>
          </select>
          <button @click="createQuickAlert"
                  class="px-4 py-2 bg-amber-500 hover:bg-amber-400 text-gray-900 rounded-xl text-sm font-bold transition-colors">
            ➕
          </button>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'

const props = defineProps({
  item:   { type: Object, required: true },
  server: { type: String, default: 'draconiros' }
})

const emit = defineEmits(['close'])

const detail       = ref(null)
const loadingDetail = ref(false)
const alertPrice   = ref('')
const alertDirection = ref('below')

const servers = { draconiros: '🐉 Draconiros', ombre: '🌑 Ombre', hellmina: '⚡ Hellmina', orukam: '🔥 Orukam' }
const serverLabel = computed(() => servers[props.server] || props.server)

const trendLabel = computed(() => {
  if (!detail.value?.variation) return ''
  const v = detail.value.variation
  return v > 0 ? `↑ +${v}%` : v < 0 ? `↓ ${v}%` : '→ stable'
})
const trendClass = computed(() => {
  const v = detail.value?.variation || 0
  return v > 0 ? 'text-emerald-400' : v < 0 ? 'text-red-400' : 'text-gray-500'
})

const chartData = computed(() => detail.value?.history || [])

const chartPointsArr = computed(() => {
  if (!chartData.value.length) return []
  const prices = chartData.value.map(d => d.price_1)
  const min    = Math.min(...prices)
  const max    = Math.max(...prices) || min + 1
  const W = 400, H = 110

  return prices.map((p, i) => ({
    x: (i / (prices.length - 1 || 1)) * W,
    y: H - ((p - min) / (max - min || 1)) * H
  }))
})

const chartPoints = computed(() =>
  chartPointsArr.value.map(p => `${p.x},${p.y}`).join(' ')
)

const chartFill = computed(() => {
  if (!chartPointsArr.value.length) return ''
  const pts = chartPointsArr.value
  const last = pts[pts.length - 1]
  return `${pts.map(p => `${p.x},${p.y}`).join(' ')} ${last.x},120 0,120`
})

const chartLabels = computed(() => {
  return chartData.value
    .filter((_, i) => i === 0 || i === Math.floor(chartData.value.length / 2) || i === chartData.value.length - 1)
    .map(d => new Date(d.date).toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit' }))
})

async function loadDetail() {
  loadingDetail.value = true
  try {
    const res = await fetch(`/api/v1/items/${props.item.slug}?server=${props.server}`)
    detail.value = await res.json()
  } finally {
    loadingDetail.value = false
  }
}

async function createQuickAlert() {
  if (!alertPrice.value) return
  await fetch('/api/v1/alerts', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
    body: JSON.stringify({
      item_id:         props.item.id,
      server:          props.server,
      threshold_price: parseInt(alertPrice.value),
      direction:       alertDirection.value,
    })
  })
  alertPrice.value = ''
  alert('✅ Alerte créée !')
}

function onImgError(event) {
  const svg = `data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='64' height='64'%3E%3Crect width='64' height='64' rx='12' fill='%231f2937'/%3E%3Ctext x='50%25' y='55%25' font-size='28' text-anchor='middle' dominant-baseline='middle' fill='%236b7280'%3E%3F%3C/text%3E%3C/svg%3E`
  event.target.src = svg
  event.target.onerror = null
}

function formatKamas(amount) {
  if (!amount) return '—'
  if (amount >= 1_000_000) return (amount / 1_000_000).toFixed(1) + ' MK'
  if (amount >= 1_000) return (amount / 1_000).toFixed(0) + ' K'
  return amount + ' K'
}

onMounted(() => loadDetail())
</script>

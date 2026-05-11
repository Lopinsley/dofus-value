<template>
  <div @click="$emit('click', item)"
       class="group relative bg-gray-900 border border-gray-800 rounded-2xl p-4 hover:border-amber-500/50 hover:-translate-y-1 transition-all duration-300 cursor-pointer overflow-hidden">
    <!-- Trend glow background -->
    <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity"
         :class="{
           'bg-gradient-to-br from-emerald-500/5 to-transparent': item.trend === 'up',
           'bg-gradient-to-br from-red-500/5 to-transparent': item.trend === 'down',
           'bg-gradient-to-br from-amber-500/5 to-transparent': item.trend === 'stable',
         }">
    </div>

    <div class="relative">
      <!-- Image + Badge -->
      <div class="relative mb-3">
        <img :src="item.image_url" :alt="item.name"
             class="h-16 w-16 mx-auto rounded-xl bg-gray-800 object-contain"
             @error="onImgError($event)" />
        <!-- Trend badge -->
        <span class="absolute -top-1 -right-1 h-6 w-6 rounded-full flex items-center justify-center text-xs font-bold shadow-lg"
              :class="{
                'bg-emerald-500 text-white': item.trend === 'up',
                'bg-red-500 text-white': item.trend === 'down',
                'bg-gray-700 text-gray-300': item.trend === 'stable',
              }">
          {{ item.trend === 'up' ? '↑' : item.trend === 'down' ? '↓' : '→' }}
        </span>
      </div>

      <!-- Name -->
      <h3 class="text-sm font-semibold text-white text-center truncate mb-1">{{ item.name }}</h3>
      <p class="text-xs text-gray-500 text-center capitalize mb-3">{{ item.type }} • Niv. {{ item.level }}</p>

      <!-- Price -->
      <div class="bg-gray-800/50 rounded-xl p-3 text-center">
        <div class="text-lg font-display font-bold text-amber-400">
          {{ formatKamas(item.price) }}
        </div>
        <div class="text-xs text-gray-500 mt-0.5">
          x10: {{ formatKamas(item.price_x10) }}
        </div>
      </div>

      <!-- Variation -->
      <div v-if="item.variation !== 0" class="mt-2 text-center">
        <span class="text-xs font-bold"
              :class="item.variation > 0 ? 'text-emerald-400' : 'text-red-400'">
          {{ item.variation > 0 ? '+' : '' }}{{ item.variation }}%
        </span>
        <span class="text-xs text-gray-600 ml-1">24h</span>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  item: { type: Object, required: true }
})

defineEmits(['click'])

function onImgError(event) {
  // Remplace l'img par un SVG inline — aucune requête externe
  const svg = `data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='64' height='64' viewBox='0 0 64 64'%3E%3Crect width='64' height='64' rx='12' fill='%231f2937'/%3E%3Ctext x='50%25' y='55%25' font-size='28' text-anchor='middle' dominant-baseline='middle' fill='%236b7280'%3E%3F%3C/text%3E%3C/svg%3E`
  event.target.src = svg
}

function formatKamas(amount) {
  if (!amount) return '—'
  if (amount >= 1_000_000) return (amount / 1_000_000).toFixed(1) + ' MK'
  if (amount >= 1_000) return (amount / 1_000).toFixed(0) + 'K'
  return amount + ' K'
}
</script>

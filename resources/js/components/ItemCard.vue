<template>
  <Link :href="`/items/${item.slug}`" class="card-hover flex items-center gap-3 group">
    <!-- Image -->
    <img
      :src="item.image_url || '/img/placeholder-item.png'"
      :alt="item.name"
      class="item-image shrink-0"
    />

    <!-- Info -->
    <div class="flex-1 min-w-0">
      <p class="text-sm font-medium text-white truncate group-hover:text-orange-300 transition-colors">
        {{ item.name }}
      </p>
      <p class="text-xs text-gray-500">Niv. {{ item.level }} · {{ item.category }}</p>
    </div>

    <!-- Prix + Tendance -->
    <div class="text-right shrink-0">
      <p class="kamas text-sm">{{ formattedPrice }}</p>
      <span :class="badgeClass">
        {{ trendIcon }} {{ Math.abs(change).toFixed(1) }}%
      </span>
    </div>
  </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
  item: Object,
  change: Number,
  trend: {
    type: String,
    default: 'stable',
  },
})

const formattedPrice = computed(() => {
  const price = props.item.latest_price
  if (!price) return '?'
  if (price >= 1_000_000) return (price / 1_000_000).toFixed(1) + 'M K'
  if (price >= 1_000) return (price / 1_000).toFixed(0) + 'K K'
  return price.toLocaleString() + ' K'
})

const trendIcon = computed(() => ({
  up: '↑',
  down: '↓',
  stable: '→',
}[props.trend] ?? '→'))

const badgeClass = computed(() => ({
  up: 'badge-up',
  down: 'badge-down',
  stable: 'badge-stable',
}[props.trend] ?? 'badge-stable'))
</script>

<template>
  <AppLayout>
    <div class="max-w-7xl mx-auto px-4 py-8">

      <!-- Hero -->
      <div class="mb-10">
        <h1 class="text-3xl font-bold text-white mb-2">
          📊 Marché HDV — <span class="text-orange-400">Dofus Unity</span>
        </h1>
        <p class="text-gray-400">Suivez les prix en temps réel, trouvez les meilleures opportunités de craft.</p>
      </div>

      <!-- Stats globales -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10">
        <div class="card">
          <div class="stat-label">Items trackés</div>
          <div class="stat-value">{{ stats.total_items?.toLocaleString() ?? '—' }}</div>
        </div>
        <div class="card">
          <div class="stat-label">Prix enregistrés</div>
          <div class="stat-value">{{ stats.total_prices?.toLocaleString() ?? '—' }}</div>
        </div>
        <div class="card">
          <div class="stat-label">En hausse (7j)</div>
          <div class="stat-value text-green-400">{{ stats.trending_up ?? '—' }}</div>
        </div>
        <div class="card">
          <div class="stat-label">En baisse (7j)</div>
          <div class="stat-value text-red-400">{{ stats.trending_down ?? '—' }}</div>
        </div>
      </div>

      <div class="grid lg:grid-cols-3 gap-8">

        <!-- Trending UP -->
        <div>
          <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
            <span class="text-green-400">↑</span> Meilleures hausses
          </h2>
          <div class="space-y-2">
            <ItemCard
              v-for="entry in trending.rising"
              :key="entry.item.id"
              :item="entry.item"
              :change="entry.change_percent"
              trend="up"
            />
            <p v-if="!trending.rising?.length" class="text-gray-600 text-sm text-center py-4">
              Aucune donnée disponible
            </p>
          </div>
        </div>

        <!-- Trending DOWN -->
        <div>
          <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
            <span class="text-red-400">↓</span> Plus fortes baisses
          </h2>
          <div class="space-y-2">
            <ItemCard
              v-for="entry in trending.falling"
              :key="entry.item.id"
              :item="entry.item"
              :change="entry.change_percent"
              trend="down"
            />
            <p v-if="!trending.falling?.length" class="text-gray-600 text-sm text-center py-4">
              Aucune donnée disponible
            </p>
          </div>
        </div>

        <!-- Best Craft -->
        <div>
          <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
            <span class="text-yellow-400">⚒️</span> Craft rentable
          </h2>
          <div class="space-y-2">
            <div
              v-for="opp in craftOpportunities"
              :key="opp.item.id"
              class="card-hover flex items-center gap-3"
            >
              <img :src="opp.item.image_url" :alt="opp.item.name" class="item-image" />
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-white truncate">{{ opp.item.name }}</p>
                <p class="text-xs text-gray-500">
                  Craft: <span class="kamas text-xs">{{ formatKamas(opp.craft_cost) }}</span>
                </p>
              </div>
              <div class="text-right shrink-0">
                <p class="text-sm font-bold text-green-400">+{{ opp.margin }}%</p>
                <p class="text-xs text-gray-500">marge</p>
              </div>
            </div>
            <p v-if="!craftOpportunities?.length" class="text-gray-600 text-sm text-center py-4">
              Aucune donnée disponible
            </p>
          </div>
        </div>

      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/components/AppLayout.vue'
import ItemCard from '@/components/ItemCard.vue'

const props = defineProps({
  stats: Object,
  trending: Object,
  craftOpportunities: Array,
})

const formatKamas = (val) => {
  if (!val) return '?'
  if (val >= 1_000_000) return (val / 1_000_000).toFixed(1) + 'M'
  if (val >= 1_000) return (val / 1_000).toFixed(0) + 'K'
  return val.toLocaleString()
}
</script>

<template>
  <div class="chart-container">
    <canvas ref="chartCanvas"></canvas>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import {
  Chart,
  LineController,
  LineElement,
  PointElement,
  LinearScale,
  CategoryScale,
  Tooltip,
  Filler,
} from 'chart.js'

Chart.register(
  LineController,
  LineElement,
  PointElement,
  LinearScale,
  CategoryScale,
  Tooltip,
  Filler
)

const props = defineProps({
  labels: Array,
  prices: Array,
  trend: {
    type: String,
    default: 'stable',
  },
})

const chartCanvas = ref(null)
let chartInstance = null

const trendColor = computed(() => ({
  up: '#22c55e',
  down: '#ef4444',
  stable: '#3b82f6',
}[props.trend] ?? '#3b82f6'))

function buildChart() {
  if (chartInstance) chartInstance.destroy()

  const color = trendColor.value

  chartInstance = new Chart(chartCanvas.value, {
    type: 'line',
    data: {
      labels: props.labels,
      datasets: [{
        label: 'Prix (Kamas)',
        data: props.prices,
        borderColor: color,
        backgroundColor: `${color}15`,
        borderWidth: 2,
        pointRadius: 3,
        pointHoverRadius: 6,
        pointBackgroundColor: color,
        fill: true,
        tension: 0.4,
      }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: {
        intersect: false,
        mode: 'index',
      },
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: '#1f2937',
          borderColor: '#374151',
          borderWidth: 1,
          titleColor: '#9ca3af',
          bodyColor: '#ffffff',
          callbacks: {
            label: (ctx) => {
              const val = ctx.raw
              if (val >= 1_000_000) return ` ${(val / 1_000_000).toFixed(2)}M K`
              if (val >= 1_000) return ` ${(val / 1_000).toFixed(0)}K K`
              return ` ${val?.toLocaleString()} K`
            },
          },
        },
      },
      scales: {
        x: {
          grid: { color: '#1f2937' },
          ticks: {
            color: '#6b7280',
            maxTicksLimit: 8,
            font: { size: 11 },
          },
        },
        y: {
          grid: { color: '#1f2937' },
          ticks: {
            color: '#6b7280',
            font: { size: 11 },
            callback: (val) => {
              if (val >= 1_000_000) return (val / 1_000_000).toFixed(1) + 'M'
              if (val >= 1_000) return (val / 1_000).toFixed(0) + 'K'
              return val
            },
          },
        },
      },
    },
  })
}

onMounted(buildChart)
watch(() => [props.labels, props.prices], buildChart)
</script>

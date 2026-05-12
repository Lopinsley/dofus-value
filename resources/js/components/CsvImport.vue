<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-display font-bold">📥 Import CSV</h2>
        <p class="text-gray-500 text-sm mt-1">Importe tes prix HDV manuellement depuis un fichier CSV</p>
      </div>
      <a href="/api/v1/import/template" download
         class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-xl text-sm font-semibold transition-colors flex items-center gap-2">
        📄 Télécharger le modèle CSV
      </a>
    </div>

    <!-- Zone upload -->
    <div class="bg-gray-900 border-2 border-dashed rounded-3xl p-12 text-center transition-colors"
         :class="dragOver ? 'border-amber-500 bg-amber-500/5' : 'border-gray-700'"
         @dragover.prevent="dragOver = true"
         @dragleave="dragOver = false"
         @drop.prevent="onDrop">

      <div class="text-5xl mb-4">📂</div>
      <p class="text-gray-300 font-semibold mb-2">Glisse ton fichier CSV ici</p>
      <p class="text-gray-600 text-sm mb-6">ou clique pour sélectionner</p>

      <input ref="fileInput" type="file" accept=".csv,.txt" class="hidden" @change="onFileSelect" />
      <button @click="$refs.fileInput.click()"
              class="px-6 py-2.5 bg-amber-500 hover:bg-amber-400 text-gray-900 font-bold rounded-xl transition-colors">
        Choisir un fichier
      </button>

      <p v-if="selectedFile" class="mt-4 text-sm text-amber-400">
        📎 {{ selectedFile.name }} ({{ (selectedFile.size / 1024).toFixed(1) }} KB)
      </p>
    </div>

    <!-- Bouton import -->
    <div v-if="selectedFile" class="flex justify-center">
      <button @click="importCsv" :disabled="importing"
              class="px-8 py-3 bg-amber-500 hover:bg-amber-400 disabled:opacity-50 text-gray-900 font-bold rounded-xl transition-colors flex items-center gap-2">
        <span v-if="importing" class="animate-spin h-4 w-4 rounded-full border-2 border-gray-900 border-t-transparent"></span>
        {{ importing ? 'Import en cours...' : '🚀 Lancer l\'import' }}
      </button>
    </div>

    <!-- Résultats -->
    <div v-if="result" class="bg-gray-900 border rounded-2xl p-6"
         :class="result.errors?.length ? 'border-yellow-500/30' : 'border-emerald-500/30'">
      <div class="flex items-center gap-3 mb-4">
        <span class="text-2xl">{{ result.errors?.length ? '⚠️' : '✅' }}</span>
        <div>
          <p class="font-semibold text-white">{{ result.message }}</p>
          <p class="text-xs text-gray-500">{{ result.imported }} prix importés</p>
        </div>
      </div>
      <div v-if="result.errors?.length" class="space-y-1">
        <p class="text-xs font-semibold text-yellow-400 mb-2">Erreurs :</p>
        <p v-for="err in result.errors" :key="err" class="text-xs text-gray-500 font-mono">{{ err }}</p>
      </div>
    </div>

    <!-- Format CSV -->
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
      <h3 class="font-semibold text-gray-300 mb-3">📋 Format attendu</h3>
      <div class="bg-gray-950 rounded-xl p-4 font-mono text-xs text-gray-400 overflow-x-auto">
        <div class="text-amber-400">item_slug,server,price_1,price_10,price_100,date</div>
        <div>fer,draconiros,250,2375,22500,2025-05-11</div>
        <div>bronze,orukam,380,3610,34200,2025-05-11</div>
        <div>kobalte,hellmina,950,9025,85500,2025-05-11</div>
      </div>
      <p class="text-xs text-gray-600 mt-3">
        Serveurs valides : draconiros, ombre, hellmina, orukam<br>
        Les colonnes price_10, price_100 et date sont optionnelles
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const dragOver     = ref(false)
const selectedFile = ref(null)
const importing    = ref(false)
const result       = ref(null)

function onDrop(e) {
  dragOver.value = false
  const file = e.dataTransfer.files[0]
  if (file && (file.name.endsWith('.csv') || file.name.endsWith('.txt'))) {
    selectedFile.value = file
    result.value = null
  }
}

function onFileSelect(e) {
  selectedFile.value = e.target.files[0]
  result.value = null
}

async function importCsv() {
  if (!selectedFile.value) return
  importing.value = true
  result.value = null

  const formData = new FormData()
  formData.append('file', selectedFile.value)

  try {
    const res = await fetch('/api/v1/import/csv', {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
      body: formData,
    })
    result.value = await res.json()
    if (res.ok) selectedFile.value = null
  } catch (e) {
    result.value = { message: 'Erreur lors de l\'import', errors: [e.message], imported: 0 }
  } finally {
    importing.value = false
  }
}
</script>

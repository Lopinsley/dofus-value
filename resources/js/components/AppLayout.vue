<template>
  <div class="min-h-screen bg-gray-950">
    <!-- Navbar -->
    <nav class="border-b border-gray-800 bg-gray-900/80 backdrop-blur-sm sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <!-- Logo -->
          <Link href="/" class="flex items-center gap-3">
            <div class="w-8 h-8 bg-orange-500 rounded-lg flex items-center justify-center">
              <span class="text-white font-bold text-sm">DV</span>
            </div>
            <span class="font-bold text-white text-lg">DofusValue</span>
            <span class="text-xs text-orange-400 font-medium bg-orange-500/10 px-2 py-0.5 rounded-full border border-orange-500/20">
              Unity
            </span>
          </Link>

          <!-- Nav links -->
          <div class="hidden md:flex items-center gap-1">
            <Link href="/" :class="isActive('/') ? 'nav-link-active' : 'nav-link'">
              Dashboard
            </Link>
            <Link href="/items" :class="isActive('/items') ? 'nav-link-active' : 'nav-link'">
              Items
            </Link>
            <Link href="/craft" :class="isActive('/craft') ? 'nav-link-active' : 'nav-link'">
              Craft
            </Link>
            <Link href="/trending" :class="isActive('/trending') ? 'nav-link-active' : 'nav-link'">
              Tendances
            </Link>
          </div>

          <!-- Search + Status -->
          <div class="flex items-center gap-3">
            <!-- Live indicator -->
            <div class="hidden sm:flex items-center gap-2 text-xs text-gray-500">
              <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse-subtle"></span>
              <span>Mis à jour il y a {{ lastUpdate }}</span>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <!-- Page content -->
    <main>
      <slot />
    </main>

    <!-- Footer -->
    <footer class="border-t border-gray-800 mt-16 py-8">
      <div class="max-w-7xl mx-auto px-4 text-center text-sm text-gray-600">
        <p>DofusValue — Tracker HDV non-officiel pour Dofus Unity</p>
        <p class="mt-1">Pas affilié à Ankama Games</p>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()

const isActive = (path) => page.url === path || page.url.startsWith(path + '/')

const lastUpdate = computed(() => {
  // TODO: fetch from store
  return '5min'
})
</script>

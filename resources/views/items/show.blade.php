<!DOCTYPE html>
<html lang="fr" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $item->name }} — DofusValue</title>
    <meta property="og:title" content="{{ $item->name }} — DofusValue">
    <meta property="og:description" content="Prix HDV de {{ $item->name }} sur {{ ucfirst($server) }} — DofusValue">
    <meta property="og:image" content="{{ $item->image_url }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-950 text-white font-body">

    <!-- Header -->
    <header class="border-b border-gray-800 bg-gray-950/80 backdrop-blur sticky top-0 z-40">
        <div class="max-w-5xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="/" class="flex items-center gap-3">
                <div class="h-8 w-8 rounded-xl bg-gradient-to-br from-amber-400 to-orange-600 flex items-center justify-center">⚔️</div>
                <span class="text-lg font-display font-bold text-white">DofusValue</span>
            </a>
            <div class="flex items-center gap-3">
                <select onchange="window.location.href='/items/{{ $item->slug }}?server='+this.value"
                        class="bg-gray-800 border border-gray-700 rounded-lg px-3 py-1.5 text-sm text-gray-300 focus:border-amber-500 focus:outline-none">
                    @foreach(['draconiros' => '🐉 Draconiros', 'ombre' => '🌑 Ombre', 'hellmina' => '⚡ Hellmina', 'orukam' => '🔥 Orukam'] as $srv => $label)
                        <option value="{{ $srv }}" {{ $server === $srv ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @auth
                    <span class="text-xs text-gray-500">{{ auth()->user()->name }}</span>
                    <form method="POST" action="/logout" class="inline">@csrf
                        <button class="text-xs text-gray-500 hover:text-white transition-colors">Déconnexion</button>
                    </form>
                @else
                    <a href="/login" class="text-xs text-amber-400 hover:text-amber-300">Connexion</a>
                @endauth
            </div>
        </div>
    </header>

    <div class="max-w-5xl mx-auto px-4 py-8">
        <!-- Breadcrumb -->
        <nav class="text-sm text-gray-500 mb-6">
            <a href="/" class="hover:text-gray-300 transition-colors">Dashboard</a>
            <span class="mx-2">›</span>
            <span class="text-white">{{ $item->name }}</span>
        </nav>

        <!-- Item header -->
        <div class="bg-gray-900 border border-gray-800 rounded-3xl p-8 mb-6">
            <div class="flex items-start gap-6">
                <img src="{{ $item->image_url }}" alt="{{ $item->name }}"
                     class="h-24 w-24 rounded-2xl bg-gray-800 object-contain"
                     onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'96\' height=\'96\'%3E%3Crect width=\'96\' height=\'96\' rx=\'16\' fill=\'%231f2937\'/%3E%3Ctext x=\'50%25\' y=\'55%25\' font-size=\'40\' text-anchor=\'middle\' dominant-baseline=\'middle\' fill=\'%236b7280\'%3E%3F%3C/text%3E%3C/svg%3E'" />
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <h1 class="text-3xl font-display font-bold text-white">{{ $item->name }}</h1>
                        @if($variation !== null)
                            <span class="px-3 py-1 rounded-full text-sm font-bold {{ $variation > 0 ? 'bg-emerald-500/20 text-emerald-400' : ($variation < 0 ? 'bg-red-500/20 text-red-400' : 'bg-gray-800 text-gray-400') }}">
                                {{ $variation > 0 ? '↑ +' : ($variation < 0 ? '↓ ' : '→ ') }}{{ $variation }}%
                            </span>
                        @endif
                    </div>
                    <p class="text-gray-500 capitalize mb-4">{{ $item->category }} • Niveau {{ $item->level }}</p>

                    <!-- Prix grid -->
                    <div class="grid grid-cols-3 gap-4">
                        <div class="bg-gray-800/50 rounded-2xl p-4 text-center">
                            <div class="text-xs text-gray-500 mb-1">Prix × 1</div>
                            <div class="text-2xl font-display font-bold text-amber-400">
                                {{ $latest ? number_format($latest->price_1, 0, ',', ' ') : '—' }}
                            </div>
                            <div class="text-xs text-gray-600 mt-1">kamas</div>
                        </div>
                        <div class="bg-gray-800/50 rounded-2xl p-4 text-center">
                            <div class="text-xs text-gray-500 mb-1">Prix × 10</div>
                            <div class="text-xl font-display font-bold text-gray-300">
                                {{ $latest ? number_format($latest->price_10, 0, ',', ' ') : '—' }}
                            </div>
                            <div class="text-xs text-gray-600 mt-1">kamas</div>
                        </div>
                        <div class="bg-gray-800/50 rounded-2xl p-4 text-center">
                            <div class="text-xs text-gray-500 mb-1">Prix × 100</div>
                            <div class="text-xl font-display font-bold text-gray-300">
                                {{ $latest ? number_format($latest->price_100, 0, ',', ' ') : '—' }}
                            </div>
                            <div class="text-xs text-gray-600 mt-1">kamas</div>
                        </div>
                    </div>
                </div>

                <!-- Share button -->
                <button onclick="navigator.clipboard.writeText(window.location.href); this.textContent='✅ Copié !'; setTimeout(() => this.textContent='🔗 Partager', 2000)"
                        class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-xl text-sm font-semibold transition-colors whitespace-nowrap">
                    🔗 Partager
                </button>
            </div>
        </div>

        <!-- Graphique historique -->
        <div class="bg-gray-900 border border-gray-800 rounded-3xl p-8 mb-6">
            <h2 class="text-lg font-display font-bold text-white mb-6">📈 Historique des prix (7 derniers jours)</h2>

            @if($history->count() > 1)
                @php
                    $prices = $history->pluck('price_1')->toArray();
                    $min = min($prices);
                    $max = max($prices) ?: $min + 1;
                    $W = 800; $H = 200;
                    $points = collect($prices)->map(function($p, $i) use ($prices, $min, $max, $W, $H) {
                        return [
                            'x' => ($i / (count($prices) - 1)) * $W,
                            'y' => $H - (($p - $min) / ($max - $min ?: 1)) * ($H - 20) - 10
                        ];
                    });
                    $polyline = $points->map(fn($p) => "{$p['x']},{$p['y']}")->join(' ');
                    $fill = $polyline . " {$W},{$H} 0,{$H}";
                @endphp
                <div class="relative">
                    <svg viewBox="0 0 {{ $W }} {{ $H }}" class="w-full" style="height: 200px">
                        <defs>
                            <linearGradient id="grad" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#f59e0b" stop-opacity="0.4"/>
                                <stop offset="100%" stop-color="#f59e0b" stop-opacity="0"/>
                            </linearGradient>
                        </defs>
                        @for($i = 1; $i <= 4; $i++)
                            <line x1="0" y1="{{ $i * ($H / 4) }}" x2="{{ $W }}" y2="{{ $i * ($H / 4) }}"
                                  stroke="#374151" stroke-width="0.5"/>
                        @endfor
                        <polygon points="{{ $fill }}" fill="url(#grad)" />
                        <polyline points="{{ $polyline }}" fill="none" stroke="#f59e0b" stroke-width="3"
                                  stroke-linecap="round" stroke-linejoin="round"/>
                        @foreach($points as $i => $pt)
                            <circle cx="{{ $pt['x'] }}" cy="{{ $pt['y'] }}" r="4" fill="#f59e0b"/>
                        @endforeach
                    </svg>
                    <div class="flex justify-between text-xs text-gray-600 mt-2">
                        @foreach($history as $i => $h)
                            @if($i === 0 || $i === intdiv($history->count(), 2) || $i === $history->count() - 1)
                                <span>{{ \Carbon\Carbon::parse($h['date'])->format('d/m') }}</span>
                            @endif
                        @endforeach
                    </div>
                </div>
            @else
                <div class="text-center py-12 text-gray-600">
                    <div class="text-4xl mb-3">📊</div>
                    <p>Pas assez de données pour afficher le graphique</p>
                    <p class="text-xs mt-1">Lance <code class="text-amber-500">php artisan dofus:scrape --history</code></p>
                </div>
            @endif
        </div>

        <!-- Alerte rapide -->
        <div class="bg-gray-900 border border-gray-800 rounded-3xl p-8">
            <h2 class="text-lg font-display font-bold text-white mb-4">🔔 Créer une alerte de prix</h2>

            @auth
                <form method="POST" action="/alerts" class="flex flex-wrap gap-3 items-end">
                    @csrf
                    <input type="hidden" name="item_id" value="{{ $item->id }}">
                    <input type="hidden" name="server" value="{{ $server }}">
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Prix seuil (kamas)</label>
                        <input type="number" name="threshold_price" required placeholder="Ex: 50000"
                               class="bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-white focus:border-amber-500 focus:outline-none w-48">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Condition</label>
                        <select name="direction" class="bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-gray-300 focus:border-amber-500 focus:outline-none">
                            <option value="below">↓ En dessous</option>
                            <option value="above">↑ Au dessus</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Email (optionnel)</label>
                        <input type="email" name="email" placeholder="notif@email.com"
                               class="bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-white focus:border-amber-500 focus:outline-none w-48">
                    </div>
                    <button type="submit"
                            class="px-6 py-2.5 bg-amber-500 hover:bg-amber-400 text-gray-900 font-bold rounded-xl transition-colors">
                        Créer l'alerte
                    </button>
                </form>
            @else
                <div class="text-center py-6 text-gray-500">
                    <p>Tu dois être connecté pour créer des alertes</p>
                    <a href="/login" class="inline-block mt-3 px-6 py-2 bg-amber-500 hover:bg-amber-400 text-gray-900 font-bold rounded-xl transition-colors">
                        Se connecter
                    </a>
                </div>
            @endauth
        </div>
    </div>

</body>
</html>

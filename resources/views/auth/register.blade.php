<!DOCTYPE html>
<html lang="fr" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription — DofusValue</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-950 flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="inline-flex items-center gap-3 mb-2">
                <div class="h-12 w-12 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-600 flex items-center justify-center text-2xl">⚔️</div>
                <span class="text-3xl font-display font-bold text-white">DofusValue</span>
            </div>
            <p class="text-gray-500 text-sm">Crée ton compte pour sauvegarder tes alertes</p>
        </div>

        <div class="bg-gray-900 border border-gray-800 rounded-3xl p-8">
            <h1 class="text-xl font-display font-bold text-white mb-6">Créer un compte</h1>

            @if ($errors->any())
                <div class="mb-4 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-sm">
                    <ul class="space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="/register" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1.5">Pseudo</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white placeholder-gray-600 focus:border-amber-500 focus:outline-none transition-colors"
                           placeholder="TonPseudoDofus">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1.5">Adresse email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white placeholder-gray-600 focus:border-amber-500 focus:outline-none transition-colors"
                           placeholder="ton@email.com">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1.5">Mot de passe</label>
                    <input type="password" name="password" required
                           class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white placeholder-gray-600 focus:border-amber-500 focus:outline-none transition-colors"
                           placeholder="Minimum 8 caractères">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1.5">Confirmer le mot de passe</label>
                    <input type="password" name="password_confirmation" required
                           class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white placeholder-gray-600 focus:border-amber-500 focus:outline-none transition-colors"
                           placeholder="Même mot de passe">
                </div>
                <button type="submit"
                        class="w-full py-3 bg-amber-500 hover:bg-amber-400 text-gray-900 font-bold rounded-xl transition-colors">
                    Créer mon compte 🎮
                </button>
            </form>

            <p class="text-center text-sm text-gray-500 mt-6">
                Déjà un compte ?
                <a href="/login" class="text-amber-400 hover:text-amber-300 font-semibold">Se connecter</a>
            </p>
        </div>

        <p class="text-center mt-4">
            <a href="/" class="text-gray-600 hover:text-gray-400 text-sm transition-colors">← Retour au dashboard</a>
        </p>
    </div>

</body>
</html>

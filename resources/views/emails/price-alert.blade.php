@component('mail::message')
# 🔔 Alerte de prix déclenchée !

Ton alerte pour **{{ $alert->item->name }}** sur **{{ ucfirst($alert->server) }}** s'est déclenchée.

| | |
|---|---|
| **Item** | {{ $alert->item->name }} |
| **Serveur** | {{ ucfirst($alert->server) }} |
| **Prix actuel** | {{ number_format($currentPrice, 0, ',', ' ') }} kamas |
| **Seuil configuré** | {{ $alert->direction === 'below' ? '↓ En dessous de' : '↑ Au dessus de' }} {{ number_format($alert->threshold_price, 0, ',', ' ') }} kamas |

@component('mail::button', ['url' => config('app.url') . '/items/' . $alert->item->slug . '?server=' . $alert->server, 'color' => 'primary'])
Voir l'item →
@endcomponent

Bonne chasse ! ⚔️

— DofusValue
@endcomponent

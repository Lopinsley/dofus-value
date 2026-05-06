# 📊 DofusValue

> Real-time market analytics & price tracker for Dofus Unity

![DofusValue Banner](https://img.shields.io/badge/Dofus-Unity-orange?style=for-the-badge)
![Laravel](https://img.shields.io/badge/Laravel-12-red?style=for-the-badge&logo=laravel)
![Vue.js](https://img.shields.io/badge/Vue.js-3-green?style=for-the-badge&logo=vue.js)
![License](https://img.shields.io/badge/License-MIT-blue?style=for-the-badge)

---

## 🎯 Concept

DofusValue est un tracker de prix de l'Hôtel de Vente (HDV) pour Dofus Unity.  
Visualisez les tendances de marché, recevez des alertes de prix, et identifiez les meilleures opportunités de craft et de revente.

**Aucun équivalent sérieux n'existe sur ce segment.** C'est le moment.

---

## ✨ Fonctionnalités

### MVP (v1.0)
- 📈 **Historique des prix** — Courbes par item sur 7/30/90 jours
- 🔍 **Recherche intelligente** — Auto-complétion par nom, catégorie, niveau
- 🏷️ **Calculateur de craft** — Rentabilité matières premières vs prix de vente
- 📱 **Responsive design** — Desktop first, mobile friendly

### v1.1 (Post-MVP)
- 🔔 **Alertes prix** — Notification quand un item passe sous X kamas
- 📊 **Dashboard personnalisé** — Portfolio de tes items suivis
- 🤖 **Score tendance IA** — Buy/Sell signal basé sur historique

### v2.0 (Premium)
- 🌍 **Multi-serveurs** — Comparaison entre serveurs Dofus
- 📤 **Export CSV/JSON** — Données brutes pour les analystes
- 🔗 **API publique** — Pour les développeurs tiers

---

## 🛠️ Stack Technique

| Couche | Technologie | Raison |
|--------|------------|--------|
| Backend | Laravel 12 | Robuste, API REST native, Jobs/Queue |
| Frontend | Vue.js 3 + Inertia.js | SPA sans complexité, SSR |
| Charts | Chart.js 4 | Léger, flexible |
| Cache | Redis | Prix en temps réel |
| BDD | MySQL 8 | Historique des prix |
| Queue | Laravel Horizon | Jobs de scraping |
| CSS | Tailwind CSS 3 | Rapide, cohérent |
| Deploy | Docker + GitHub Actions | CI/CD automatisé |

---

## 🚀 Installation

### Prérequis
- PHP 8.3+
- Node.js 20+
- MySQL 8+
- Redis 7+
- Docker (optionnel)

### Dev local

```bash
# Cloner le repo
git clone https://github.com/Lopinsley/dofus-value.git
cd dofus-value

# Installer les dépendances
composer install
npm install

# Configuration
cp .env.example .env
php artisan key:generate

# Base de données
php artisan migrate --seed

# Lancer (dev)
php artisan serve &
npm run dev &
php artisan queue:work
```

### Docker

```bash
docker-compose up -d
docker-compose exec app php artisan migrate --seed
```

---

## 📁 Structure du Projet

```
dofus-value/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/          # API REST (items, prices, alerts)
│   │   │   └── Web/          # Pages Inertia.js
│   ├── Models/               # Item, Price, PriceAlert, User
│   ├── Services/
│   │   ├── DofusApiService   # Wrapper API Ankama
│   │   └── PriceAnalyzer     # Calcul tendances
│   └── Jobs/
│       └── ScrapePrices      # Job de scraping périodique
├── resources/
│   ├── js/
│   │   ├── Pages/            # Vue pages (Dashboard, Item, Search)
│   │   ├── Components/       # PriceChart, ItemCard, AlertForm
│   │   └── stores/           # Pinia stores
│   └── css/
│       └── app.css           # Tailwind
├── database/
│   ├── migrations/           # Items, prices, alerts
│   └── seeders/              # Items Dofus Unity (seed initial)
└── docker/
    ├── Dockerfile
    └── docker-compose.yml
```

---

## 🗺️ Roadmap

- [x] Structure projet & repo
- [ ] Scraper API Ankama / données HDV
- [ ] Migrations & modèles (Item, Price, PriceAlert)
- [ ] API REST endpoints
- [ ] Dashboard Vue.js avec Chart.js
- [ ] Calculateur de craft
- [ ] Système d'alertes (email/Discord)
- [ ] CI/CD GitHub Actions
- [ ] Deploy production

---

## 🤝 Contribution

Projet LHMI — usage interne et communauté Dofus.  
Issues et PRs bienvenus.

---

## 📄 Licence

MIT — © 2026 LHMI

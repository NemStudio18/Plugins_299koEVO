# 📈 Module LightStats — Comprenez votre trafic sans tracker intrusif

LightStats enregistre chaque visite côté serveur (sans cookie, sans bannière) et vous affiche des graphiques propres dans l’admin. Une solution idéale pour garder un œil sur vos audiences tout en restant conforme et ultra léger.

## 🌟 Ce que le module apporte
- 🔍 Collecte instantanée : page visitée, date, referer, user-agent, détection bot.
- 🪶 Stockage local (JSON) par jour/mois/année : aucune base externe, aucune dépendance.
- 📊 Dashboard admin avec graphiques Chart.js.
- 🔌 Hooks simples (`endFrontHead`, `adminHead`) pour tout site 299Ko.

## ⚙️ Vous personnalisez
- Libellé du module, emplacement dans le menu.
- Possibilité d’enrichir la fonction `isBot` ou le parsing des referers.
- Exploitation des fichiers JSON (scripts maison, export, etc.).

## 🔒 Pensé pour rester fiable
- Pas de cookies ni d’identifiant persistent : conformité privacy-friendly.
- Droits restreints sur `DATA_PLUGIN/lightstats/logs`.
- Possibilité d’ajouter un filtrage IP ou proxy pour votre infra.

## 🚀 Idéal pour
- Sites qui veulent un suivi simple sans Google Analytics.
- Plateformes internes ou intranets ne pouvant pas installer de trackers externes.
- Projets orientés privacy-by-design.

---

## LightStats — Documentation du module

### 1. Présentation
- **Slug** : `lightstats`
- **Version** : 2.0.0
- **Entrée admin** : `/admin/lightstats`
- **Libs** : `lib/` contient les helpers de lecture/agrégation.

### 2. Fonctionnalités
- Hook `endFrontHead` → `lightstatsAddVisitor` (log JSON `YYYY/MM/DD.json`).
- Hook `adminHead` → `lightstatsAddScript` charge Chart.js.
- Interface admin (`LightStatsAdminController#home`) pour visualiser visites & bots.
- Fonction `isBot` (liste basique) que vous pouvez enrichir.

### 3. Configuration (`param/config.json`)

| Clé | Description | Défaut |
| --- | --- | --- |
| `priority` | Position dans le menu | `2` |
| `label` | Nom affiché | `LightStats` |

### 4. Routes (`param/routes.php`)

| Méthode | URI | Action |
| --- | --- | --- |
| GET/POST | `/admin/lightstats` | `LightStatsAdminController#home` |

### 5. Hooks (`param/hooks.json`)
- `endFrontHead` → `lightstatsAddVisitor`
- `adminHead` → `lightstatsAddScript`

### 6. Sécurité & conformité
- Les logs contiennent IP et user-agent : informer les utilisateurs et prévoir une purge.
- Créez les dossiers avec permissions restreintes (750) et bloquez l’accès HTTP.
- Si vous êtes derrière un proxy, adaptez `lightstatsAddVisitor` (X-Forwarded-For).
- Ajouter un mécanisme d’anonymisation IP si besoin (hash, troncature).

### 7. Tests rapides
- Activer le plugin, visiter le site, vérifier l’apparition de `logs/YYYY/MM/DD.json`.
- Ouvrir `/admin/lightstats` pour voir les graphiques Chart.js.
- Simuler un user-agent `Googlebot` → `isBot()` doit retourner `true`.

### 8. Références
- `lightstats/lightstats.php`
- `controllers/LightStatsAdminController.php`
- `param/config.json`, `param/routes.php`, `param/hooks.json`


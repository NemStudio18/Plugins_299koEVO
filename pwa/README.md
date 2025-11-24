# 📱 Module PWA — Transformez votre site en application installable

Offrez à vos visiteurs une expérience mobile digne d’une app native : icônes sur l’écran d’accueil, lancement plein écran, service worker pour l’offline et notifications Web Push. Le module PWA orchestre manifest, service worker et gestion des abonnements depuis l’admin.

## 🌟 Ce que le module apporte
- 📦 Manifest dynamique (nom, icônes, couleurs, orientation) servi via `/pwa/manifest.json`.
- ⚙️ Service worker prêt à l’emploi (`/pwa/sw.js`) + script client `pwa.js`.
- 🔔 API Web Push complète : génération des clés VAPID, abonnement/désabonnement, envoi ciblé.
- 🪄 Bouton d’installation affiché automatiquement (`endFrontBody`) + scripts admin pour tester.

## ⚙️ Vous personnalisez
- Libellé, description, couleurs (`backgroundColor`, `themeColor`), orientation.
- Icônes 192/512 px (dossier `plugin/pwa/icons/`).
- Notifications : titre, message, segment d’abonnés.

## 🔒 Pensé pour rester fiable
- Clés VAPID stockées hors config (`DATA_PLUGIN/pwa/vapid_keys.json`).
- Gestion robuste d’OpenSSL (Windows friendly) pour générer les clés.
- Contrôlez les permissions sur `DATA_PLUGIN/pwa/` (vapid_keys + subscriptions).
- Ajoutez une politique de rate-limit sur `/pwa/subscribe`/`unsubscribe` si nécessaire.

## 🚀 Idéal pour
- Sites médias souhaitant réengager via notifications push.
- Applications métier souhaitant une expérience offline rapide.
- Projets cherchant à respecter le modèle PWA (manifest + service worker) sans effort.

---

## PWA — Documentation du module

### 1. Présentation
- **Slug** : `pwa`
- **Version** : 1.0
- **Entrées** : `/pwa/*` (API publique), `/admin/pwa`
- **Assets** : `pwa.js`, `sw.js`, `icons/`, `template/install-button.tpl`

### 2. Fonctionnalités
- Génération/stockage des clés VAPID (`pwaGenerateVapidKeys` → `DATA_PLUGIN/pwa/vapid_keys.json`).
- Manifest dynamique selon `param/config.json`.
- Service worker + script client pour installation + push.
- API REST pour public key, subscribe/unsubscribe, sw, manifest.
- Admin : génération de clés, envoi de notifications, configuration couleurs/icônes.

### 3. Configuration (`param/config.json`)

| Clé | Description | Défaut |
| --- | --- | --- |
| `priority` | Position menu | `5` |
| `label` | Nom du module | `PWA` |
| `description` | Texte admin | `Progressive Web App` |
| `backgroundColor` / `themeColor` | Couleurs manifest | `#ffffff` / `#000000` |
| `orientation` | `portrait` / `landscape` | `portrait` |
| `icon192`, `icon512` | Icônes | `plugin/pwa/icons/icon-192.png`, `icon-512.png` |

### 4. Routes publiques (`param/routes.php`)
- `GET /pwa/public-key` → retourne la clé VAPID publique.
- `POST /pwa/subscribe` / `POST /pwa/unsubscribe`.
- `GET /pwa/sw.js`, `GET /pwa/manifest.json`.

### 5. Routes admin
- `GET|POST /admin/pwa` → `PwaAdminController#home`.
- `POST /admin/pwa/send-notification` → envoi Web Push.

### 6. Hooks
- `endFrontHead` → `pwaEndFrontHead` (manifest + meta).
- `endAdminHead` → `pwaEndAdminHead` (chargement `pwa.js` côté admin).
- `endFrontBody` → `pwaEndFrontBody` (bouton d’installation + traduction).

### 7. Sécurité
- Permissions strictes sur `DATA_PLUGIN/pwa/` (clés + subscriptions).
- Ajouter un rate-limit/captcha sur les endpoints d’abonnement.
- Purger les abonnements expirés pour éviter les erreurs lors des envois.
- Vérifier/mettre à jour OpenSSL côté serveur pour la génération des clés.

### 8. Tests rapides
- Générer les clés VAPID → vérifier `DATA_PLUGIN/pwa/vapid_keys.json`.
- Installer la PWA sur Chrome/Android (icône + mode offline).
- S’abonner aux notifications, envoyer un push depuis l’admin → vérifier la réception.

### 9. Références
- `pwa/pwa.php`
- `controllers/PwaAdminController.php`
- `Pwa/Controllers/PwaController.php`
- `pwa.js`, `sw.js`, `template/install-button.tpl`
- `param/config.json`, `param/routes.php`, `param/hooks.json`


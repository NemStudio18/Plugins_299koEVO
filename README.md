# 📚 Suite plugins 299koEVO — Panorama & documentation

Ce dépôt héberge **exclusivement** les plugins officiellement compatibles avec **299koEVO** (branche EVO uniquement). Utilisez-le comme référence lorsque vous développez ou déployez sur cette version du CMS.

Tous les plugins disponibles dans `Plugins_299koEVO/` disposent d’une fiche dédiée (`docs/<slug>/README.md`) avec :
- la promesse du module ;
- une documentation structurée (config, routes, sécurité, tests, références).

Consultez ce document pour avoir une vision d’ensemble et naviguer rapidement vers la fiche souhaitée.

## Vue d’ensemble

| Plugin | Description rapide | Entrée publique | Entrée admin | Documentation |
| --- | --- | --- | --- | --- |
| Antispam | CAPTCHA texte / icône / Google reCAPTCHA | — | `/admin/antispam` | `docs/antispam/README.md` |
| Blog | Articles, catégories, commentaires, RSS | `/blog` | `/admin/blog` | `docs/blog/README.md` |
| Contact | Formulaire de contact + historique des adresses | `/contact` | `/admin/contact` | `docs/contact/README.md` |
| Docs | Wiki avec catégories, versions, shortcode | `/docs` | `/admin/docs` | `docs/docs/README.md` |
| Dons | Collecte PayPal / Stripe + dashboard | `/dons` | `/admin/dons` | `docs/dons/README.md` |
| FAQ | Questions/réponses, votes, soumission publique | `/faq` | `/admin/faq` | `docs/faq/README.md` |
| Galerie | Galerie photo, resize auto, catégories | `/galerie` | `/admin/galerie` | `docs/galerie/README.md` |
| Guestbook | Livre d’or avec modération et likes | `/guestbook` | `/admin/guestbook` | `docs/guestbook/README.md` |
| HighLight | Coloration syntaxique (Highlight.js) | — | `/admin/highlight` | `docs/highlight/README.md` |
| LightStats | Tracking serveur privacy-friendly | (hook) | `/admin/lightstats` | `docs/lightstats/README.md` |
| MDEditor | Éditeur Markdown EasyMDE | — | — | `docs/mdeditor/README.md` |
| Newsletter | Abonnements + modale front + campagnes | `/newsletter` | `/admin/newsletter` | `docs/newsletter/README.md` |
| PWA | Manifest, service worker, notifications push | `/pwa/*` | `/admin/pwa` | `docs/pwa/README.md` |
| SEO | Google Analytics + liens sociaux | — | `/admin/seo` | `docs/seo/README.md` |
| Sondage | Création et vote de sondages | `/sondage` | `/admin/sondage` | `docs/sondage/README.md` |
| TinyMCE | Éditeur WYSIWYG complet | — | — | `docs/tinymce/README.md` |

> Les modules Contact, FAQ, Guestbook, Dons… utilisent `antispam`. Activez-le avant toute mise en prod de formulaire public.

## Checklist sécurité transversale

**Entrées & stockage**
- `filter_input`, `filter_var`, cast strict pour tous les `$_POST`/`$_GET`.
- `htmlspecialchars` / échappement systématique dans les templates.
- Préférer PDO + requêtes préparées si vous ajoutez une couche SQL.

**Accès & sessions**
- Token CSRF (`[a:token]`) sur toutes les routes admin sensibles.
- HTTPS obligatoire + cookies `HttpOnly`, `Secure`, `SameSite=Strict`.
- Purger régulièrement les logs contenant IP/emails (`lightstats`, `dons`…).

**Uploads & fichiers**
- Whitelist d’extensions (galerie, tinymce) + redimensionnement côté serveur.
- Dossiers `DATA_PLUGIN` / `UPLOAD` hors webroot ou protégés (`.htaccess`, ACL).

## Mettre à jour la documentation

1. Ajouter/modifier votre plugin dans `Plugins_299koEVO/`.
2. Créer `docs/<slug>/README.md` en suivant la structure (pitch marketing + doc technique).
3. Mise à jour de ce fichier pour référencer le nouveau module.

## Navigation rapide

```
docs/
 ├── antispam/
 ├── blog/
 ├── contact/
 ├── …
 └── tinymce/
```

Chaque dossier contient un README autoportant, idéal pour les audits, la QA et le support. Bonne exploration !


/**
 * LightStats Analytics — Tracker Léger & Gestionnaire de Consentement
 */
(function() {
    'use strict';

    const CONSENT_KEY = 'vb_consent';
    const VISITOR_COOKIE = 'vb_vid';
    const SESSION_COOKIE = 'vb_sid';
    const VISITOR_DAYS = 7;
    const SESSION_MINUTES = 30;

    // Utilitaires Cookies
    function getCookie(name) {
        const matches = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + '=([^;]*)'));
        return matches ? decodeURIComponent(matches[1]) : null;
    }

    function setCookie(name, value, minutes) {
        let expires = '';
        if (minutes) {
            const date = new Date();
            date.setTime(date.getTime() + (minutes * 60 * 1000));
            expires = '; expires=' + date.toUTCString();
        }
        document.cookie = name + '=' + encodeURIComponent(value) + expires + '; path=/; SameSite=Lax';
    }

    function deleteCookie(name) {
        document.cookie = name + '=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT; SameSite=Lax';
    }

    function generateUuid() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            const r = Math.random() * 16 | 0;
            const v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }

    // Contexte serveur injecté dynamiquement dans la réponse HTML (sans cookie vb_req)
    const serverCtx = (typeof window.__VB_CTX__ === 'object' && window.__VB_CTX__ !== null) ? window.__VB_CTX__ : {};
    const requestId = (typeof serverCtx.reqId === 'string' && serverCtx.reqId.length > 0) ? serverCtx.reqId : null;
    const isCnilExempt = Boolean(serverCtx.cnilExempt);
    let sessionId = (typeof serverCtx.sid === 'string' && serverCtx.sid.length > 0) ? serverCtx.sid : null;

    // Gestion du consentement & visitor_id
    const currentConsent = isCnilExempt ? null : localStorage.getItem(CONSENT_KEY);
    let visitorId = null;

    if (isCnilExempt) {
        deleteCookie(VISITOR_COOKIE);
    } else if (currentConsent === 'accepted') {
        visitorId = getCookie(VISITOR_COOKIE);
        if (!visitorId) {
            visitorId = generateUuid();
            setCookie(VISITOR_COOKIE, visitorId, VISITOR_DAYS * 24 * 60);
        } else {
            // Prolonger la validité du cookie visiteur
            setCookie(VISITOR_COOKIE, visitorId, VISITOR_DAYS * 24 * 60);
        }
    } else if (currentConsent === 'refused') {
        deleteCookie(VISITOR_COOKIE);
    }

    // API Publique VBAnalytics
    window.VBAnalytics = {
        getVisitorId: () => visitorId,
        getSessionId: () => sessionId,
        getRequestId: () => requestId,
        getConsent: () => localStorage.getItem(CONSENT_KEY),
        trackEvent: function(module, eventType, eventData, existingEventId) {
            // Génération d'un event_id stable réutilisé lors des éventuelles réémissions
            const eventId = existingEventId || ('evt_' + generateUuid().replace(/-/g, ''));
            const payload = {
                event_id: eventId,
                request_id: requestId,
                session_id: sessionId,
                visitor_id: visitorId,
                module: module || 'other',
                event_type: eventType,
                page: window.location.pathname + window.location.search,
                event_data: eventData || {}
            };

            const jsonStr = JSON.stringify(payload);
            let sent = false;
            if (navigator.sendBeacon) {
                try {
                    const blob = new Blob([jsonStr], { type: 'application/json' });
                    sent = navigator.sendBeacon('/lightstats/api/event', blob);
                } catch (e) {
                    sent = false;
                }
            }
            if (!sent) {
                fetch('/lightstats/api/event', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: jsonStr,
                    keepalive: true
                }).catch(() => {});
            }
            return eventId;
        },
        openConsentModal: function() {
            showConsentBanner(true);
        },
        revokeConsent: function() {
            localStorage.setItem(CONSENT_KEY, 'refused');
            deleteCookie(VISITOR_COOKIE);
            visitorId = null;
        }
    };

    // Injection du bandeau discret de consentement
    function showConsentBanner(force = false) {
        if (isCnilExempt && !force) {
            return;
        }
        if (!force && localStorage.getItem(CONSENT_KEY)) {
            return;
        }

        let existing = document.getElementById('vb-privacy-toast');
        if (existing) {
            existing.style.display = 'flex';
            return;
        }

        const toast = document.createElement('div');
        toast.id = 'vb-privacy-toast';
        toast.innerHTML = `
            <div class="vb-privacy-content">
                <div class="vb-privacy-header">
                    <span class="vb-privacy-icon"><i class="fa-solid fa-shield-halved"></i></span>
                    <strong>Respect de votre vie privée</strong>
                </div>
                <p class="vb-privacy-text">
                    Nous mesurons l'usage du site pour améliorer ses fonctionnalités locales. Avec votre accord, un identifiant pseudonyme nous permet d'analyser les parcours récurrents. Aucune donnée n'est revendue.
                </p>
                <div class="vb-privacy-actions">
                    <button id="vb-privacy-refuse" class="vb-btn vb-btn-refuse" type="button">Tout refuser</button>
                    <button id="vb-privacy-accept" class="vb-btn vb-btn-accept" type="button">Tout accepter</button>
                </div>
            </div>
        `;

        // Styles CSS intégrés pour être 100% autonome
        const style = document.createElement('style');
        style.textContent = `
            #vb-privacy-toast {
                position: fixed;
                bottom: 24px;
                right: 24px;
                max-width: 440px;
                width: calc(100% - 48px);
                background: #ffffff;
                color: #1e293b;
                border: 1px solid #e2e8f0;
                border-radius: 14px;
                padding: 20px;
                box-shadow: 0 16px 36px rgba(0, 0, 0, 0.18);
                z-index: 999999;
                font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                animation: vbSlideUp 0.35s ease-out;
            }
            body.theme-dark #vb-privacy-toast,
            [data-theme="dark"] #vb-privacy-toast {
                background: #18181b;
                color: #f4f4f5;
                border: 1px solid #3f3f46;
                box-shadow: 0 16px 36px rgba(0, 0, 0, 0.55);
            }
            /* Thème Ebene : Ébénisterie royale et or */
            body.theme-ebene #vb-privacy-toast {
                background: #1c110a;
                color: #fdfaf6;
                border: 1px solid rgba(212, 175, 55, 0.48);
                box-shadow: 0 16px 36px rgba(0, 0, 0, 0.8), 0 0 24px rgba(212, 175, 55, 0.15);
            }
            body.theme-ebene .vb-privacy-header {
                color: #f9e295;
            }
            body.theme-ebene .vb-privacy-icon {
                color: #d4af37;
            }
            body.theme-ebene .vb-privacy-text {
                color: #d8c7b4;
            }
            body.theme-ebene .vb-btn-refuse {
                background: #24150c;
                color: #fdfaf6;
                border: 1px solid rgba(212, 175, 55, 0.3);
            }
            body.theme-ebene .vb-btn-refuse:hover {
                border-color: #d4af37;
                color: #f9e295;
            }
            body.theme-ebene .vb-btn-accept {
                background: linear-gradient(135deg, #d4af37 0%, #a07817 100%);
                color: #120904;
                font-weight: 700;
                box-shadow: 0 4px 14px rgba(212, 175, 55, 0.4);
            }
            body.theme-ebene .vb-btn-accept:hover {
                background: linear-gradient(135deg, #f9e295 0%, #d4af37 100%);
                box-shadow: 0 6px 18px rgba(212, 175, 55, 0.6);
            }

            /* Thème Ignis : Braise et flamme */
            body.theme-ignis #vb-privacy-toast {
                background: #0f172a;
                color: #f8fafc;
                border: 1px solid rgba(249, 115, 22, 0.4);
                box-shadow: 0 16px 36px rgba(0, 0, 0, 0.7), 0 0 20px rgba(249, 115, 22, 0.15);
            }
            body.theme-ignis .vb-privacy-header {
                color: #fb923c;
            }
            body.theme-ignis .vb-privacy-text {
                color: #cbd5e1;
            }
            body.theme-ignis .vb-btn-refuse {
                background: #1e293b;
                color: #f8fafc;
                border: 1px solid rgba(255, 255, 255, 0.15);
            }
            body.theme-ignis .vb-btn-accept {
                background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
                color: #ffffff;
                box-shadow: 0 4px 14px rgba(249, 115, 22, 0.4);
            }

            /* Thème Végétal : Forêt et chlorophylle */
            body.theme-vegetal #vb-privacy-toast {
                background: #ffffff;
                color: #14532d;
                border: 1px solid rgba(34, 197, 94, 0.35);
                box-shadow: 0 16px 36px rgba(20, 83, 45, 0.12);
            }
            body.theme-vegetal .vb-privacy-header {
                color: #15803d;
            }
            body.theme-vegetal .vb-privacy-text {
                color: #374151;
            }
            body.theme-vegetal .vb-btn-refuse {
                background: #f0fdf4;
                color: #166534;
                border: 1px solid #bbf7d0;
            }
            body.theme-vegetal .vb-btn-accept {
                background: #16a34a;
                color: #ffffff;
                box-shadow: 0 4px 14px rgba(22, 163, 74, 0.3);
            }

            /* Thème DesktopOS : Verre dépoli macOS */
            body.theme-desktop-os #vb-privacy-toast {
                background: rgba(15, 23, 42, 0.85);
                backdrop-filter: blur(20px) saturate(180%);
                -webkit-backdrop-filter: blur(20px) saturate(180%);
                color: #f8fafc;
                border: 1px solid rgba(255, 255, 255, 0.16);
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.45);
            }
            @keyframes vbSlideUp {
                from { transform: translateY(30px); opacity: 0; }
                to { transform: translateY(0); opacity: 1; }
            }
            .vb-privacy-header {
                display: flex;
                align-items: center;
                gap: 10px;
                margin-bottom: 8px;
                font-size: 15px;
                font-weight: 700;
                color: var(--evo-accent, #2563eb);
            }
            body.theme-dark .vb-privacy-header {
                color: var(--evo-accent, #60a5fa);
            }
            .vb-privacy-text {
                font-size: 13px;
                line-height: 1.5;
                color: var(--evo-text-muted, #64748b);
                margin: 0 0 16px 0;
            }
            body.theme-dark .vb-privacy-text {
                color: var(--evo-text-muted, #a1a1aa);
            }
            .vb-privacy-actions {
                display: flex;
                gap: 10px;
                justify-content: flex-end;
            }
            .vb-btn {
                padding: 8px 16px;
                border-radius: 999px;
                font-size: 13px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.2s ease;
                border: none;
            }
            .vb-btn-refuse {
                background: var(--evo-surface-alt, #f1f5f9);
                color: var(--evo-text, #334155);
                border: 1px solid var(--evo-border, #cbd5e1);
            }
            .vb-btn-refuse:hover {
                border-color: var(--evo-accent, #2563eb);
                color: var(--evo-text, #334155);
            }
            body.theme-dark .vb-btn-refuse {
                background: var(--evo-surface-alt, #27272a);
                color: var(--evo-text, #f4f4f5);
                border: 1px solid var(--evo-border, #3f3f46);
            }
            .vb-btn-accept {
                background: var(--evo-accent, #2563eb);
                color: var(--evo-accent-text, #ffffff);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            }
            .vb-btn-accept:hover {
                background: var(--evo-accent-hover, #1d4ed8);
                transform: translateY(-1px);
            }
            @media (max-width: 600px) {
                #vb-privacy-toast {
                    bottom: 12px;
                    right: 12px;
                    width: calc(100% - 24px);
                    padding: 16px;
                }
                .vb-privacy-actions {
                    flex-direction: column;
                }
                .vb-btn {
                    width: 100%;
                    text-align: center;
                }
            }
        `;

        document.head.appendChild(style);
        document.body.appendChild(toast);

        // Actions boutons
        document.getElementById('vb-privacy-refuse').addEventListener('click', function() {
            localStorage.setItem(CONSENT_KEY, 'refused');
            deleteCookie(VISITOR_COOKIE);
            visitorId = null;
            toast.style.display = 'none';
        });

        document.getElementById('vb-privacy-accept').addEventListener('click', function() {
            localStorage.setItem(CONSENT_KEY, 'accepted');
            visitorId = getCookie(VISITOR_COOKIE);
            if (!visitorId) {
                visitorId = generateUuid();
                setCookie(VISITOR_COOKIE, visitorId, VISITOR_DAYS * 24 * 60);
            }
            toast.style.display = 'none';
        });
    }

    // Certification Active View JS (garantit l'exécution côté client par un vrai navigateur)
    function certifyPageView() {
        if (window.location.pathname.indexOf('/admin') === 0) {
            return;
        }
        // Protection impérative : Une certification n'est émise que si un request_id valide est fourni par le serveur
        if (!requestId) {
            return;
        }
        try {
            window.VBAnalytics.trackEvent('page', 'view_certified', {
                title: document.title,
                screen: (window.screen ? (window.screen.width + 'x' + window.screen.height) : ''),
                referrer: document.referrer || ''
            });
        } catch (e) {}
    }

    // Démarrage au chargement du DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            showConsentBanner();
            certifyPageView();
        });
    } else {
        showConsentBanner();
        certifyPageView();
    }
})();

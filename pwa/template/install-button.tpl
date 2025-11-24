<!-- Bouton flottant PWA Install -->
<button id="pwa-install-button" class="pwa-install-button" aria-label="{{ installLabel }}">
    <i class="fa-solid fa-download"></i>
    <span>{{ installLabel }}</span>
</button>

<style>
.pwa-install-button {
    position: fixed;
    bottom: 2rem;
    left: 2rem;
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: #ffffff;
    border: none;
    border-radius: 50px;
    padding: 1rem 1.5rem;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 4px 15px rgba(0, 123, 255, 0.4);
    z-index: 999;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    transition: all 0.3s ease;
}

.pwa-install-button:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0, 123, 255, 0.6);
}

.pwa-install-button:active {
    transform: scale(0.98);
}

.pwa-install-button i {
    font-size: 1.2rem;
}

.pwa-install-button span {
    display: inline-block;
}

@media (max-width: 768px) {
    .pwa-install-button {
        bottom: 1rem;
        left: 1rem;
        padding: 0.85rem 1.25rem;
        font-size: 0.9rem;
    }
    
    .pwa-install-button span {
        display: none;
    }
    
    .pwa-install-button i {
        font-size: 1.3rem;
    }
}

/* Ajuster la position si newsletter est présent (newsletter à droite, PWA à gauche) */
.newsletter-floating-btn ~ .pwa-install-button {
    /* Newsletter est à droite, PWA à gauche - pas de conflit */
}
</style>

<script>
(function() {
    'use strict';

    let publicKey = null;
    let registration = null;
    let deferredPrompt = null;

    // Initialiser le PWA
    function init() {
        // Toujours initialiser le prompt d'installation, même sans service worker
        showInstallPrompt();
        
        if ('serviceWorker' in navigator) {
            // Toujours enregistrer le service worker, même sans Push API
            loadPublicKey().then(() => {
                registerServiceWorker();
            }).catch(() => {
                // Si la clé publique n'est pas disponible, enregistrer quand même le service worker
                registerServiceWorker();
            });
        } else {
            console.warn('PWA: Service Worker non supporté - le bouton d\'installation sera toujours disponible pour iOS/Firefox');
        }
    }

    // Attendre l'événement beforeinstallprompt pour proposer le prompt natif
    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        deferredPrompt = event;
    });

    // Charger la clé publique VAPID
    function loadPublicKey() {
        return fetch('/pwa/public-key')
            .then(response => response.text())
            .then(key => {
                publicKey = key.trim();
                return key;
            })
            .catch(error => {
                console.error('PWA: Erreur lors du chargement de la clé publique', error);
            });
    }

    // Enregistrer le Service Worker
    function registerServiceWorker() {
        navigator.serviceWorker.register('/pwa/sw.js')
            .then(reg => {
                registration = reg;
                console.log('PWA: Service Worker enregistré');
                
                // Vérifier si l'utilisateur est déjà abonné
                checkSubscription();
            })
            .catch(error => {
                console.error('PWA: Erreur lors de l\'enregistrement du Service Worker', error);
            });
    }

    // Vérifier l'abonnement actuel
    function checkSubscription() {
        if (!registration) return;

        registration.pushManager.getSubscription()
            .then(subscription => {
                if (subscription) {
                    console.log('PWA: Déjà abonné aux notifications');
                }
            })
            .catch(error => {
                console.error('PWA: Erreur lors de la vérification de l\'abonnement', error);
            });
    }

    // Détecter le navigateur et la plateforme
    function detectPlatform() {
        const ua = navigator.userAgent || navigator.vendor || window.opera;
        const isIOS = /iPad|iPhone|iPod/.test(ua) && !window.MSStream;
        const isAndroid = /android/i.test(ua);
        // Chrome detection - plus robuste
        const isChrome = /Chrome/.test(ua) && !/Edge|Edg|OPR/.test(ua);
        const isFirefox = /Firefox/.test(ua);
        const isSafari = /^((?!chrome|android).)*safari/i.test(ua) && !isChrome;
        const isEdge = /Edge|Edg/.test(ua);
        
        return {
            isIOS: isIOS,
            isAndroid: isAndroid,
            isChrome: isChrome,
            isFirefox: isFirefox,
            isSafari: isSafari,
            isEdge: isEdge,
            isMobile: isIOS || isAndroid
        };
    }
    
    // Vérifier si l'app est déjà installée
    function isAppInstalled() {
        // Chrome/Edge/Opera
        if (window.matchMedia('(display-mode: standalone)').matches) {
            return true;
        }
        // iOS Safari
        if (window.navigator.standalone === true) {
            return true;
        }
        // Autres indicateurs
        if (window.matchMedia('(display-mode: fullscreen)').matches) {
            return true;
        }
        return false;
    }
    
    // Afficher le prompt d'installation
    function showInstallPrompt() {
        const installButton = document.getElementById('pwa-install-button');
        if (!installButton) {
            console.warn('PWA: Install button not found in DOM');
            return;
        }
        
        console.log('PWA: Bouton trouvé, initialisation...');
        
        // Masquer le bouton par défaut, il sera affiché si nécessaire
        installButton.style.display = 'none';
        installButton.dataset.listenerAdded = '';
        
        // Si l'app est déjà installée, masquer le bouton
        if (isAppInstalled()) {
            console.log('PWA: App déjà installée, masquage du bouton');
            return;
        }
        
        const platform = detectPlatform();
        
        // Fonction pour gérer le clic sur le bouton
        function setupButtonClick() {
            if (installButton.dataset.listenerAdded) {
                return; // Déjà configuré
            }
            
            installButton.addEventListener('click', () => {
                // Si on a un deferredPrompt (Chrome/Edge), l'utiliser
                if (deferredPrompt) {
                    deferredPrompt.prompt();
                    deferredPrompt.userChoice.then((choiceResult) => {
                        if (choiceResult.outcome === 'accepted') {
                            console.log('PWA: Installation acceptée');
                            installButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i><span>Installation...</span>';
                            setTimeout(() => {
                                installButton.style.display = 'none';
                            }, 2000);
                        } else {
                            console.log('PWA: Installation refusée');
                        }
                        deferredPrompt = null;
                    });
                }
                // Sinon, afficher des instructions selon le navigateur
                else if (platform.isIOS && platform.isSafari) {
                    alert('Pour installer cette application sur iOS:\n\n1. Appuyez sur le bouton de partage (□↑)\n2. Sélectionnez "Sur l\'écran d\'accueil"\n3. Appuyez sur "Ajouter"');
                }
                else if (platform.isFirefox) {
                    alert('Pour installer cette application sur Firefox:\n\n1. Cliquez sur le menu (☰)\n2. Sélectionnez "Installer" ou "Installer le site"\n3. Confirmez l\'installation');
                }
                else {
                    // Pour les autres navigateurs, afficher des instructions génériques
                    alert('Pour installer cette application:\n\n1. Utilisez le menu de votre navigateur\n2. Cherchez l\'option "Installer" ou "Ajouter à l\'écran d\'accueil"\n3. Suivez les instructions affichées');
                }
            });
            installButton.dataset.listenerAdded = 'true';
        }
        
        // Pour iOS Safari - toujours afficher avec instructions
        if (platform.isIOS && platform.isSafari) {
            installButton.style.display = 'flex';
            setupButtonClick();
        }
        // Pour Firefox - toujours afficher avec instructions
        else if (platform.isFirefox) {
            installButton.style.display = 'flex';
            setupButtonClick();
        }
        // Pour Chrome/Edge/Opera (desktop et Android) - utiliser beforeinstallprompt
        else if (platform.isChrome || platform.isEdge || (!platform.isFirefox && !platform.isSafari && !platform.isIOS)) {
            if (deferredPrompt) {
                installButton.style.display = 'flex';
                setupButtonClick();
            }
            // Afficher le bouton après un court délai même si beforeinstallprompt n'est pas encore déclenché
            // (certains navigateurs peuvent prendre du temps)
            setTimeout(() => {
                if (!isAppInstalled() && installButton.style.display === 'none') {
                    // Afficher quand même le bouton, il sera fonctionnel si beforeinstallprompt arrive
                    installButton.style.display = 'flex';
                    setupButtonClick();
                }
            }, 1000);
        }
        // Pour autres navigateurs - afficher quand même avec instructions génériques
        else {
            installButton.style.display = 'flex';
            setupButtonClick();
        }
    }

    // Initialiser automatiquement quand le DOM est prêt
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>


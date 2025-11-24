/**
 * Script client PWA pour gérer l'installation et les notifications push
 */
(function() {
    'use strict';

    let publicKey = null;
    let registration = null;

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

    // Demander la permission et s'abonner
    function subscribe() {
        if (!registration || !publicKey) {
            console.error('PWA: Service Worker ou clé publique non disponible');
            return Promise.reject('Service Worker ou clé publique non disponible');
        }

        return registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(publicKey)
        })
        .then(subscription => {
            // Envoyer l'abonnement au serveur
            return fetch('/pwa/subscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    endpoint: subscription.endpoint,
                    keys: {
                        p256dh: arrayBufferToBase64(subscription.getKey('p256dh')),
                        auth: arrayBufferToBase64(subscription.getKey('auth'))
                    }
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('PWA: Abonnement réussi', data);
                return subscription;
            });
        })
        .catch(error => {
            console.error('PWA: Erreur lors de l\'abonnement', error);
            throw error;
        });
    }

    // Se désabonner
    function unsubscribe() {
        if (!registration) {
            return Promise.reject('Service Worker non disponible');
        }

        return registration.pushManager.getSubscription()
            .then(subscription => {
                if (subscription) {
                    // Notifier le serveur
                    return fetch('/pwa/unsubscribe', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            endpoint: subscription.endpoint
                        })
                    })
                    .then(() => {
                        return subscription.unsubscribe();
                    })
                    .then(() => {
                        console.log('PWA: Désabonnement réussi');
                    });
                }
            })
            .catch(error => {
                console.error('PWA: Erreur lors du désabonnement', error);
                throw error;
            });
    }

    // Convertir la clé publique base64url en Uint8Array
    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding)
            .replace(/\-/g, '+')
            .replace(/_/g, '/');

        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    // Convertir ArrayBuffer en base64
    function arrayBufferToBase64(buffer) {
        const bytes = new Uint8Array(buffer);
        let binary = '';
        for (let i = 0; i < bytes.byteLength; i++) {
            binary += String.fromCharCode(bytes[i]);
        }
        return window.btoa(binary);
    }

    // Détecter le navigateur et la plateforme
    function detectPlatform() {
        const ua = navigator.userAgent || navigator.vendor || window.opera;
        const isIOS = /iPad|iPhone|iPod/.test(ua) && !window.MSStream;
        const isAndroid = /android/i.test(ua);
        const isChrome = /Chrome/.test(ua) && /Google Inc/.test(navigator.vendor);
        const isFirefox = /Firefox/.test(ua);
        const isSafari = /^((?!chrome|android).)*safari/i.test(ua);
        const isEdge = /Edge/.test(ua);
        
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
            console.warn('PWA: Install button not found in DOM - vérifiez que le hook endFrontBody est bien appelé');
            // Réessayer après un court délai au cas où le DOM n'est pas encore prêt
            setTimeout(() => {
                const retryButton = document.getElementById('pwa-install-button');
                if (retryButton) {
                    console.log('PWA: Bouton trouvé après délai');
                    showInstallPrompt();
                } else {
                    console.error('PWA: Bouton toujours introuvable après délai');
                }
            }, 500);
            return;
        }
        
        console.log('PWA: Bouton trouvé, initialisation...');
        
        // Masquer le bouton par défaut, il sera affiché si nécessaire
        installButton.style.display = 'none';
        
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
                if (window.deferredPrompt) {
                    window.deferredPrompt.prompt();
                    window.deferredPrompt.userChoice.then((choiceResult) => {
                        if (choiceResult.outcome === 'accepted') {
                            console.log('PWA: Installation acceptée');
                            installButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i><span>Installation...</span>';
                            setTimeout(() => {
                                installButton.style.display = 'none';
                            }, 2000);
                        } else {
                            console.log('PWA: Installation refusée');
                        }
                        window.deferredPrompt = null;
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
                    // Pour les autres navigateurs, essayer quand même
                    console.log('PWA: Installation requested but no prompt available');
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
            // Écouter l'événement beforeinstallprompt
            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                window.deferredPrompt = e;
                
                // Afficher le bouton d'installation
                installButton.style.display = 'flex';
                setupButtonClick();
            });
            
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

    // API publique
    window.PWA = {
        init: init,
        subscribe: subscribe,
        unsubscribe: unsubscribe,
        showInstallPrompt: showInstallPrompt
    };

    // Initialiser automatiquement quand le DOM est prêt
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Écouter le prompt d'installation
    showInstallPrompt();
})();


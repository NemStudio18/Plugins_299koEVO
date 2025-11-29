<!-- Bouton flottant Newsletter -->
<button id="newsletter-floating-btn" class="newsletter-floating-btn" onclick="openNewsletterModal()" aria-label="{{ Lang.newsletter.subscribe }}">
    <i class="fa-regular fa-envelope"></i>
    <span>{{ Lang.newsletter.subscribe }}</span>
</button>

    <!-- Modal Newsletter -->
    <div id="newsletter-modal" class="newsletter-modal" style="display: none;">
        <div class="newsletter-modal-overlay" onclick="closeNewsletterModal()"></div>
        <div class="newsletter-modal-content">
            <div class="newsletter-modal-header">
                <h3>{{ Lang.newsletter.name }}</h3>
                <button class="newsletter-modal-close" onclick="closeNewsletterModal()" aria-label="{{ Lang.cancel }}">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="newsletter-modal-body">
                {{ SHOW.displayMsg }}
                <form method="post" action="{{ subscribeUrl }}" id="newsletter-form">
                    {{ SHOW.tokenField }}
                    <label for="newsletter-email">{{ Lang.newsletter.email }}</label>
                    <input type="email" name="email" id="newsletter-email" placeholder="{{ Lang.newsletter.email-placeholder }}" required />
                    
                    <div class="newsletter-rgpd">
                        <label class="newsletter-rgpd-label">
                            <input type="checkbox" name="rgpd_accept" id="newsletter-rgpd" value="1" required />
                            <span>{{ Lang.newsletter.rgpd-accept }}</span>
                        </label>
                        <small class="newsletter-rgpd-text">{{ Lang.newsletter.rgpd-text }}</small>
                    </div>
                    
                    <button type="submit" class="button">{{ Lang.newsletter.subscribe }}</button>
                </form>
            </div>
        </div>
    </div>

    <style>
    .newsletter-floating-btn {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        background: linear-gradient(135deg, #6b3fa0 0%, #8b5fc0 100%);
        color: #ffffff;
        border: none;
        border-radius: 50px;
        padding: 1rem 1.5rem;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(107, 63, 160, 0.4);
        z-index: 999;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        transition: all 0.3s ease;
    }

    .newsletter-floating-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(107, 63, 160, 0.6);
    }

    .newsletter-floating-btn i {
        font-size: 1.2rem;
    }

    .newsletter-floating-btn span {
        display: inline-block;
    }

    @media (max-width: 768px) {
        .newsletter-floating-btn {
            bottom: 1rem;
            right: 1rem;
            padding: 0.85rem 1.25rem;
            font-size: 0.9rem;
        }
        .newsletter-floating-btn span {
            display: none;
        }
    }

    .newsletter-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 10000;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .newsletter-modal-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(4px);
    }

    .newsletter-modal-content {
        position: relative;
        background: #ffffff;
        border-radius: 12px;
        max-width: 500px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        z-index: 10001;
        animation: modalFadeIn 0.3s ease;
    }

    @keyframes modalFadeIn {
        from {
            opacity: 0;
            transform: scale(0.9) translateY(-20px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    .newsletter-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem;
        border-bottom: 2px solid #e0e0e0;
    }

    .newsletter-modal-header h3 {
        margin: 0;
        color: #1a3a5f;
        font-size: 1.5rem;
    }

    .newsletter-modal-close {
        background: transparent;
        border: none;
        font-size: 1.5rem;
        color: #666;
        cursor: pointer;
        padding: 0.5rem;
        transition: color 0.3s ease;
    }

    .newsletter-modal-close:hover {
        color: #1a3a5f;
    }

    .newsletter-modal-body {
        padding: 1.5rem;
    }

    .newsletter-rgpd {
        margin: 1.5rem 0;
        padding: 1rem;
        background: #f8f9fa;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
    }

    .newsletter-rgpd-label {
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
        cursor: pointer;
        font-weight: 500;
        margin-bottom: 0.5rem;
    }

    .newsletter-rgpd-label input[type="checkbox"] {
        margin-top: 0.2rem;
        flex-shrink: 0;
    }

    .newsletter-rgpd-text {
        display: block;
        margin-left: 1.75rem;
        font-size: 0.85rem;
        line-height: 1.5;
        color: #555;
    }

    .newsletter-modal-body label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: #1a3a5f;
    }

    .newsletter-modal-body input[type="email"] {
        width: 100%;
        padding: 0.75rem;
        border: 2px solid #e0e0e0;
        border-radius: 6px;
        font-size: 1rem;
        margin-bottom: 1rem;
        transition: border-color 0.3s ease;
    }

    .newsletter-modal-body input[type="email"]:focus {
        outline: none;
        border-color: #6b3fa0;
    }

    .newsletter-modal-body .button {
        width: 100%;
        padding: 0.85rem;
        font-size: 1rem;
        margin-top: 1rem;
    }
    </style>

    <script>
    function openNewsletterModal() {
        document.getElementById('newsletter-modal').style.display = 'flex';
        document.getElementById('newsletter-email').focus();
        document.body.style.overflow = 'hidden';
    }

    function closeNewsletterModal() {
        document.getElementById('newsletter-modal').style.display = 'none';
        document.body.style.overflow = '';
    }

    // Fermer la modal en cliquant sur l'overlay
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('newsletter-modal');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal.querySelector('.newsletter-modal-overlay')) {
                    closeNewsletterModal();
                }
            });
        }

        // Fermer avec la touche Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal && modal.style.display === 'flex') {
                closeNewsletterModal();
            }
        });
    });
    </script>


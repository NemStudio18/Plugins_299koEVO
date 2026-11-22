<link rel="stylesheet" href="/plugin/ProRdv/public/css/prordv.css">

<div class="prordv-confirm-wrapper">
    <div class="prordv-card" style="text-align:center;">
        {% if res.success %}
            <div style="width:64px;height:64px;background:var(--evo-success-soft, rgba(16,185,129,0.12));color:var(--prordv-success);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:2rem;">
                <i class="fa-solid fa-check"></i>
            </div>
            <h1 style="font-size:1.5rem;font-weight:800;color:var(--prordv-text);margin:0 0 10px;">
                {% if res.statut == 'confirme' %}
                    Rendez-vous Confirmé !
                {% else %}
                    Demande Transmise au Commerçant
                {% endif %}
            </h1>
            <p style="color:var(--prordv-text);font-size:1rem;line-height:1.6;margin:0 0 20px;">
                {% if res.already %}
                    {{ res.message }}
                {% else %}
                    Votre rendez-vous pour <strong>{{ res.presta }}</strong> chez <strong>{{ res.commerce }}</strong> est bien enregistré pour le <strong>{{ res.quand }}</strong>.
                {% endif %}
            </p>
            <div class="prordv-alert-info" style="margin-bottom:24px;text-align:center;">
                <i class="fa-solid fa-circle-info"></i> Un récapitulatif complet vous a été adressé par email.
            </div>
        {% else %}
            <div style="width:64px;height:64px;background:var(--evo-danger-soft, rgba(239,68,68,0.12));color:var(--prordv-danger);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:2rem;">
                <i class="fa-solid fa-xmark"></i>
            </div>
            <h1 style="font-size:1.5rem;font-weight:800;color:var(--prordv-text);margin:0 0 10px;">Lien non valide ou expiré</h1>
            <p style="color:var(--prordv-danger);font-size:1rem;margin:0 0 24px;">{{ res.error }}</p>
        {% endif %}

        <a href="/" class="btn" style="display:inline-block;background:var(--prordv-primary);color:var(--evo-accent-text, #fff);padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:600;">
            Retour à l'accueil
        </a>
    </div>
</div>

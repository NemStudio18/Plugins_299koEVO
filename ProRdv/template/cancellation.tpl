<link rel="stylesheet" href="/plugin/ProRdv/public/css/prordv.css">

<div class="prordv-confirm-wrapper">
    <div class="prordv-card" style="text-align:center;">
        <div style="width:64px;height:64px;background:var(--evo-danger-soft, rgba(239,68,68,0.12));color:var(--prordv-danger);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:2rem;">
            <i class="fa-solid fa-calendar-xmark"></i>
        </div>
        <h1 style="font-size:1.5rem;font-weight:800;color:var(--prordv-text);margin:0 0 10px;">Annulation de Rendez-vous</h1>
        <p style="color:var(--prordv-text);font-size:1rem;line-height:1.6;margin:0 0 24px;">
            {{ res.message|default(res.error) }}
        </p>
        <a href="/" class="btn" style="display:inline-block;background:var(--prordv-primary);color:var(--evo-accent-text, #fff);padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:600;">
            Retour à l'accueil
        </a>
    </div>
</div>

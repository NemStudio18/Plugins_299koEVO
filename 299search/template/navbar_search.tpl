<div class="search299-wrapper" id="search299Wrapper">
    <form action="/search" method="GET" class="search299-pill-form" role="search" id="search299Form">
        <button type="submit" class="search299-icon-btn" aria-label="Rechercher sur le site">
            <i class="fa-solid fa-magnifying-glass"></i>
        </button>
        <input 
            type="text" 
            name="q" 
            id="search299Input"
            class="search299-input" 
            placeholder="Rechercher sur le site…" 
            value="{{q}}" 
            autocomplete="off"
            aria-label="Rechercher sur tout le site"
        />
    </form>
</div>
<script>
(function() {
    var form  = document.getElementById('search299Form');
    var input = document.getElementById('search299Input');
    if (form && input) {
        // Clic sur la loupe sans texte : focus le champ de saisie
        form.addEventListener('submit', function(e) {
            if (!input.value.trim()) {
                e.preventDefault();
                input.focus();
            }
        });

        // Touche Échap : vide et referme le champ
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                input.value = '';
                input.blur();
            }
        });
    }
})();
</script>

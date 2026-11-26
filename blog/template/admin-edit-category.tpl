<section>
    <header>{{Lang.blog.categories.editCategory}}</header>
    <label for="category-list-edit-label">{{Lang.blog.categories.categoryName}}</label>
    <input type="text" value="{{category.label}}" id="category-list-edit-label" name="category-list-edit-label"/>
    <label for="category-list-edit-parentId">{{Lang.blog.categories.categoryParent}}</label>
    {{categoriesManager.outputAsSelect(category.parentId, category.id, "category-list-edit-parentId")}}
    
    <div style="margin: 1.25rem 0 1rem; border-top: 1px solid var(--evo-border, #e2e8f0); padding-top: 0.75rem;">
        <label style="display: block; font-weight: 600; margin-bottom: 0.25rem;"><i class="fa-solid fa-user-lock"></i> Restrictions d'accès (Groupes VIP)</label>
        <p class="font-small color-muted" style="margin-bottom: 0.6rem;">Les articles de cette catégorie hériteront automatiquement de ces restrictions.</p>
        <div style="display: flex; flex-direction: column; gap: 0.4rem; max-height: 160px; overflow-y: auto;">
            {% for group in userGroups %}
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.9rem;">
                    <input type="checkbox" class="cat-group-checkbox" value="{{ group.getSlug() }}" {% if in_array(group.getSlug(), categoryAllowedGroups) %}checked{% endif %} />
                    <span><strong>{{ group.getName() }}</strong> <span class="color-muted font-small">({{ group.getSlug() }})</span></span>
                </label>
            {% endfor %}
        </div>
    </div>

    <button onclick="BlogEditSaveCategory()">{{Lang.blog.categories.editCategory}}</button>
</section>
<script>
    async function BlogEditSaveCategory() {
        let url = '{ ROUTER.generate("admin-blog-save-category", ["id" => category.id]) }';
        let allowedGroups = Array.from(document.querySelectorAll('.cat-group-checkbox:checked')).map(cb => cb.value);
        let data = {
            label: document.querySelector('#category-list-edit-label').value,
            parentId: document.querySelector('#category-list-edit-parentId').value,
            allowedGroups: allowedGroups,
            token: '{{token}}'
        };
        try {
            let response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
            if (response.status === 202) {
                Toastify({
                    text: "{{Lang.core-item-edited}}",
                    className: "success"		
                }).showToast();
                // Refresh list
                Fancybox.close();
                Fancybox.show([
                    {
                        src: "{{categoriesManager.getAjaxDisplayListUrl()}}",
                        type: "ajax",
                    },
                ]);
            } else {
                Toastify({
                    text: "{{Lang.core-item-not-edited}}",
                    className: "error"		
                }).showToast();
            }
        } catch (err) {
            console.error(err);
            alert("Error: " + err.message);
        }
    };
</script>
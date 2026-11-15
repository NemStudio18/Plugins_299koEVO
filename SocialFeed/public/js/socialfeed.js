/**
 * SocialFeed - Client-side Interactive Logic
 */
document.addEventListener('DOMContentLoaded', () => {

    const feedContainer = document.getElementById('social-feed-container');
    const publishForm = document.getElementById('social-publish-form');
    const mediaFileInput = document.getElementById('media-file-input');
    const mediaPreview = document.getElementById('media-preview-container');
    const mediaPreviewImg = document.getElementById('media-preview-img');
    const btnRemoveMedia = document.getElementById('btn-remove-media');
    const btnToggleMediaUrl = document.getElementById('btn-toggle-media-url');
    const mediaUrlWrap = document.getElementById('media-url-wrap');

    // 1. GESTION DES MÉDIAS (PHOTO / URL)
    if (mediaFileInput) {
        mediaFileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (re) => {
                    mediaPreviewImg.src = re.target.result;
                    mediaPreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    }

    if (btnRemoveMedia) {
        btnRemoveMedia.addEventListener('click', () => {
            if (mediaFileInput) mediaFileInput.value = '';
            const urlInput = document.getElementById('media-url-input');
            if (urlInput) urlInput.value = '';
            mediaPreviewImg.src = '';
            mediaPreview.style.display = 'none';
        });
    }

    if (btnToggleMediaUrl) {
        btnToggleMediaUrl.addEventListener('click', () => {
            if (mediaUrlWrap) {
                mediaUrlWrap.style.display = mediaUrlWrap.style.display === 'none' ? 'block' : 'none';
            }
        });
    }

    // 2. PUBLICATION D'UN POST EN AJAX
    if (publishForm) {
        publishForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btnSubmit = document.getElementById('btn-submit-post');
            const originalText = btnSubmit.innerHTML;
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Publication...';

            const formData = new FormData(publishForm);
            try {
                const res = await fetch('/social/publish', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();

                if (data.status === 'success') {
                    publishForm.reset();
                    if (mediaPreview) mediaPreview.style.display = 'none';
                    if (mediaUrlWrap) mediaUrlWrap.style.display = 'none';

                    // Recharger les posts en tête de fil
                    reloadFeed();
                } else {
                    alert(data.message || 'Erreur lors de la publication.');
                }
            } catch (err) {
                console.error(err);
                alert('Erreur réseau.');
            } finally {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = originalText;
            }
        });
    }

    // 3. ACTIONS GLOBALES DÉLÉGUÉES (LIKES, COMMENTAIRES, SUPPRESSION)
    document.addEventListener('click', async (e) => {
        // A. LIKE
        const btnLike = e.target.closest('.btn-like');
        if (btnLike) {
            e.preventDefault();
            const postId = btnLike.dataset.postId;
            try {
                const res = await fetch(`/social/post/${postId}/like`, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                if (data.status === 'success') {
                    btnLike.classList.toggle('liked', data.has_liked);
                    const icon = btnLike.querySelector('i');
                    if (icon) {
                        icon.className = (data.has_liked ? 'fa-solid' : 'fa-regular') + ' fa-heart heart-icon';
                    }
                    const countSpan = btnLike.querySelector('.likes-count');
                    if (countSpan) countSpan.textContent = data.likes_count;
                } else if (data.message) {
                    alert(data.message);
                }
            } catch (err) {
                console.error(err);
            }
            return;
        }

        // B. TOGGLE COMMENTAIRES
        const btnComments = e.target.closest('.btn-toggle-comments');
        if (btnComments) {
            e.preventDefault();
            const postId = btnComments.dataset.postId;
            const commentsSection = document.getElementById(`comments-section-${postId}`);
            if (commentsSection) {
                const isHidden = commentsSection.style.display === 'none';
                commentsSection.style.display = isHidden ? 'block' : 'none';
                if (isHidden) {
                    loadComments(postId);
                }
            }
            return;
        }

        // C. SUPPRESSION POST
        const btnDelete = e.target.closest('.btn-delete-post');
        if (btnDelete) {
            e.preventDefault();
            const postId = btnDelete.dataset.postId;
            if (confirm('Voulez-vous vraiment supprimer ce message ?')) {
                const token = document.querySelector('input[name="token"]')?.value || '';
                const formData = new FormData();
                formData.append('token', token);

                try {
                    const res = await fetch(`/social/post/${postId}/delete`, {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const data = await res.json();
                    if (data.status === 'success') {
                        const card = document.getElementById(`post-${postId}`);
                        if (card) {
                            card.style.opacity = '0';
                            setTimeout(() => card.remove(), 250);
                        }
                    } else {
                        alert(data.message || 'Impossible de supprimer.');
                    }
                } catch (err) {
                    console.error(err);
                }
            }
            return;
        }

        // D. FILTRES D'ONGLETS / SOURCES
        const chip = e.target.closest('.source-chip');
        if (chip) {
            document.querySelectorAll('.source-chip').forEach(c => c.classList.remove('active'));
            chip.classList.add('active');
            const filter = chip.dataset.filter;
            feedContainer.dataset.filter = filter;
            feedContainer.dataset.page = '1';
            reloadFeed(filter);
        }
    });

    // 4. CHARGEMENT ET ENVOI DE COMMENTAIRES
    async function loadComments(postId) {
        const listContainer = document.getElementById(`comments-list-${postId}`);
        if (!listContainer) return;
        listContainer.innerHTML = '<span class="loading-hint"><i class="fa-solid fa-spinner fa-spin"></i> Chargement...</span>';

        try {
            const res = await fetch(`/social/post/${postId}/comments`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            if (data.status === 'success') {
                if (data.comments.length === 0) {
                    listContainer.innerHTML = '<span class="empty-hint">Aucun commentaire pour le moment.</span>';
                } else {
                    listContainer.innerHTML = data.comments.map(c => `
                        <div class="comment-item" id="comment-${c.id}">
                            <img src="${c.author_avatar}" class="user-avatar-sm" alt="${c.author_name}" />
                            <div class="comment-bubble">
                                <span class="comment-author">${c.author_name}</span>
                                <span class="comment-body">${c.content}</span>
                                <time class="post-time">${c.time_ago}</time>
                            </div>
                        </div>
                    `).join('');
                }
            }
        } catch (err) {
            listContainer.innerHTML = '<span class="error-hint">Erreur de chargement.</span>';
        }
    }

    document.addEventListener('submit', async (e) => {
        const commentForm = e.target.closest('.comment-add-form');
        if (commentForm) {
            e.preventDefault();
            const postId = commentForm.dataset.postId;
            const input = commentForm.querySelector('.comment-input');
            const content = input.value.trim();
            if (!content) return;

            const token = document.querySelector('input[name="token"]')?.value || '';
            const formData = new FormData();
            formData.append('content', content);
            formData.append('token', token);

            try {
                const res = await fetch(`/social/post/${postId}/comment`, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                if (data.status === 'success') {
                    input.value = '';
                    loadComments(postId);
                    // Incrémenter le compteur visuel
                    const postCard = document.getElementById(`post-${postId}`);
                    const countSpan = postCard?.querySelector('.comments-count');
                    if (countSpan) {
                        countSpan.textContent = parseInt(countSpan.textContent || '0', 10) + 1;
                    }
                } else {
                    alert(data.message || 'Erreur lors de l\'envoi.');
                }
            } catch (err) {
                console.error(err);
            }
        }
    });

    // 5. RECHARGEMENT DU FLUX (FILTRE / REFRESH)
    async function reloadFeed(filter = null) {
        if (!feedContainer) return;
        const currentFilter = filter !== null ? filter : (feedContainer.dataset.filter || 'all');
        const currentScope = feedContainer.dataset.scope || 'all';

        feedContainer.style.opacity = '0.5';
        try {
            const res = await fetch(`/social/feed?page=1&filter=${encodeURIComponent(currentFilter)}&scope=${encodeURIComponent(currentScope)}&render=1`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const html = await res.text();
            feedContainer.innerHTML = html;
        } catch (err) {
            console.error(err);
        } finally {
            feedContainer.style.opacity = '1';
        }
    }
});

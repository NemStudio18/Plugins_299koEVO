/**
 * SocialFeed - Client-side Interactive Logic (Facebook / X / Bluesky Style)
 */
document.addEventListener('DOMContentLoaded', () => {

    const feedContainer = document.getElementById('social-feed-container');
    const publishForm = document.getElementById('social-publish-form');
    const postTextarea = document.getElementById('social-post-content');
    const mediaFileInput = document.getElementById('media-file-input');
    const mediaPreview = document.getElementById('media-preview-container');
    const mediaPreviewImg = document.getElementById('media-preview-img');
    const mediaPreviewVideo = document.getElementById('media-preview-video');
    const btnRemoveMedia = document.getElementById('btn-remove-media');
    const btnToggleMediaUrl = document.getElementById('btn-toggle-media-url');
    const mediaUrlWrap = document.getElementById('media-url-wrap');
    const mediaUrlInput = document.getElementById('media-url-input');

    // Tiroirs Emojis & GIFs
    const btnToggleEmoji = document.getElementById('btn-toggle-emoji');
    const emojiDrawer = document.getElementById('emoji-drawer');
    const btnCloseEmoji = document.getElementById('btn-close-emoji');

    const btnToggleGif = document.getElementById('btn-toggle-gif');
    const gifDrawer = document.getElementById('gif-drawer');
    const btnCloseGif = document.getElementById('btn-close-gif');

    // 1. TEXTAREA AUTO-EXPAND (Comme X / Facebook)
    if (postTextarea) {
        postTextarea.addEventListener('input', () => {
            postTextarea.style.height = 'auto';
            postTextarea.style.height = Math.max(80, postTextarea.scrollHeight) + 'px';
        });
    }

    // FORMATAGE DU TEXTE (GRAS, ITALIQUE, SOULIGNÉ)
    document.addEventListener('click', (e) => {
        const btnFormat = e.target.closest('.btn-format');
        if (btnFormat && postTextarea) {
            e.preventDefault();
            const format = btnFormat.dataset.format;
            const start = postTextarea.selectionStart || 0;
            const end = postTextarea.selectionEnd || 0;
            const selectedText = postTextarea.value.substring(start, end) || 'texte';
            let replacement = selectedText;

            if (format === 'bold') replacement = `**${selectedText}**`;
            else if (format === 'italic') replacement = `*${selectedText}*`;
            else if (format === 'underline') replacement = `<u>${selectedText}</u>`;

            const text = postTextarea.value;
            postTextarea.value = text.substring(0, start) + replacement + text.substring(end);
            postTextarea.selectionStart = start;
            postTextarea.selectionEnd = start + replacement.length;
            postTextarea.focus();
            postTextarea.dispatchEvent(new Event('input'));
        }
    });

    // ABONNEMENT / DÉSABONNEMENT DEPUIS LA PAGE PROFIL
    const btnProfileFollow = document.getElementById('btn-toggle-profile-follow');
    if (btnProfileFollow) {
        btnProfileFollow.addEventListener('click', async () => {
            const targetUserId = btnProfileFollow.dataset.userId;
            btnProfileFollow.disabled = true;
            try {
                const res = await fetch(`/social/user/${targetUserId}/follow`, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                if (data.status === 'success') {
                    btnProfileFollow.classList.toggle('following', data.following);
                    const countEl = document.getElementById('followers-count-val');
                    if (countEl) {
                        const current = parseInt(countEl.textContent || '0', 10);
                        countEl.textContent = data.following ? current + 1 : Math.max(0, current - 1);
                    }
                } else if (data.message) {
                    alert(data.message);
                }
            } catch (err) {
                console.error(err);
            } finally {
                btnProfileFollow.disabled = false;
            }
        });
    }

    // 2. GESTION DES TIROIRS EMOJIS & GIFS
    if (btnToggleEmoji && emojiDrawer) {
        btnToggleEmoji.addEventListener('click', (e) => {
            e.stopPropagation();
            if (gifDrawer) gifDrawer.style.display = 'none';
            emojiDrawer.style.display = emojiDrawer.style.display === 'none' ? 'block' : 'none';
        });
    }

    if (btnCloseEmoji && emojiDrawer) {
        btnCloseEmoji.addEventListener('click', () => {
            emojiDrawer.style.display = 'none';
        });
    }

    // Insertion emoji à la position du curseur
    document.addEventListener('click', (e) => {
        const emojiBtn = e.target.closest('.emoji-item');
        if (emojiBtn && postTextarea) {
            const emoji = emojiBtn.dataset.emoji;
            const start = postTextarea.selectionStart || 0;
            const end = postTextarea.selectionEnd || 0;
            const text = postTextarea.value;
            postTextarea.value = text.substring(0, start) + emoji + text.substring(end);
            postTextarea.selectionStart = postTextarea.selectionEnd = start + emoji.length;
            postTextarea.focus();
            postTextarea.dispatchEvent(new Event('input'));
        }
    });

    if (btnToggleGif && gifDrawer) {
        btnToggleGif.addEventListener('click', (e) => {
            e.stopPropagation();
            if (emojiDrawer) emojiDrawer.style.display = 'none';
            gifDrawer.style.display = gifDrawer.style.display === 'none' ? 'block' : 'none';
        });
    }

    if (btnCloseGif && gifDrawer) {
        btnCloseGif.addEventListener('click', () => {
            gifDrawer.style.display = 'none';
        });
    }

    // Sélection d'un GIF
    document.addEventListener('click', (e) => {
        const gifBtn = e.target.closest('.gif-item');
        if (gifBtn) {
            const gifUrl = gifBtn.dataset.gifUrl;
            if (mediaUrlInput) mediaUrlInput.value = gifUrl;
            if (mediaPreview && mediaPreviewImg) {
                mediaPreviewImg.src = gifUrl;
                mediaPreviewImg.style.display = 'block';
                if (mediaPreviewVideo) mediaPreviewVideo.style.display = 'none';
                mediaPreview.style.display = 'block';
            }
            if (gifDrawer) gifDrawer.style.display = 'none';
        }
    });

    // 3. GESTION DES MÉDIAS (PHOTO / VIDÉO / REEL)
    if (mediaFileInput) {
        mediaFileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                const isVideo = file.type.startsWith('video/');
                const reader = new FileReader();

                reader.onload = (re) => {
                    if (isVideo && mediaPreviewVideo) {
                        mediaPreviewVideo.src = re.target.result;
                        mediaPreviewVideo.style.display = 'block';
                        if (mediaPreviewImg) mediaPreviewImg.style.display = 'none';
                    } else if (mediaPreviewImg) {
                        mediaPreviewImg.src = re.target.result;
                        mediaPreviewImg.style.display = 'block';
                        if (mediaPreviewVideo) mediaPreviewVideo.style.display = 'none';
                    }
                    if (mediaPreview) mediaPreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    }

    if (btnRemoveMedia) {
        btnRemoveMedia.addEventListener('click', () => {
            if (mediaFileInput) mediaFileInput.value = '';
            if (mediaUrlInput) mediaUrlInput.value = '';
            if (mediaPreviewImg) {
                mediaPreviewImg.src = '';
                mediaPreviewImg.style.display = 'none';
            }
            if (mediaPreviewVideo) {
                mediaPreviewVideo.src = '';
                mediaPreviewVideo.style.display = 'none';
            }
            if (mediaPreview) mediaPreview.style.display = 'none';
        });
    }

    if (btnToggleMediaUrl) {
        btnToggleMediaUrl.addEventListener('click', () => {
            if (mediaUrlWrap) {
                mediaUrlWrap.style.display = mediaUrlWrap.style.display === 'none' ? 'block' : 'none';
                if (mediaUrlWrap.style.display === 'block' && mediaUrlInput) {
                    mediaUrlInput.focus();
                }
            }
        });
    }

    // Prévisualisation instantanée lien vidéo/image externe
    if (mediaUrlInput) {
        mediaUrlInput.addEventListener('change', () => {
            const val = mediaUrlInput.value.trim();
            if (val && mediaPreview) {
                if (val.match(/\.(mp4|webm|mov)(\?.*)?$/i) && mediaPreviewVideo) {
                    mediaPreviewVideo.src = val;
                    mediaPreviewVideo.style.display = 'block';
                    if (mediaPreviewImg) mediaPreviewImg.style.display = 'none';
                } else if (mediaPreviewImg) {
                    mediaPreviewImg.src = val;
                    mediaPreviewImg.style.display = 'block';
                    if (mediaPreviewVideo) mediaPreviewVideo.style.display = 'none';
                }
                mediaPreview.style.display = 'block';
            }
        });
    }

    // 4. GESTION DES MENUS DROPDOWN & TIROIRS
    document.addEventListener('click', (e) => {
        const btnOptions = e.target.closest('.btn-post-options');
        const isInsideDropdown = e.target.closest('.dropdown-menu');

        // Clic sur le bouton 3-points (...)
        if (btnOptions) {
            e.preventDefault();
            const menu = btnOptions.parentElement ? btnOptions.parentElement.querySelector('.dropdown-menu') : null;
            if (menu) {
                const isOpen = menu.classList.contains('show');
                document.querySelectorAll('.dropdown-menu.show').forEach(m => m.classList.remove('show'));
                if (!isOpen) {
                    menu.classList.add('show');
                }
            }
            return;
        }

        // Clic en dehors du menu déroulant : fermer
        if (!isInsideDropdown) {
            document.querySelectorAll('.dropdown-menu.show').forEach(m => m.classList.remove('show'));
        }

        // Tiroir Emojis
        const isEmojiToggle = e.target.closest('#btn-toggle-emoji');
        const isInsideEmoji = e.target.closest('#emoji-drawer');
        if (!isEmojiToggle && !isInsideEmoji && emojiDrawer) {
            emojiDrawer.style.display = 'none';
        }

        // Tiroir GIFs
        const isGifToggle = e.target.closest('#btn-toggle-gif');
        const isInsideGif = e.target.closest('#gif-drawer');
        if (!isGifToggle && !isInsideGif && gifDrawer) {
            gifDrawer.style.display = 'none';
        }
    });

    // 5. PUBLICATION D'UN POST EN AJAX
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
                    if (postTextarea) postTextarea.style.height = 'auto';
                    if (mediaPreview) mediaPreview.style.display = 'none';
                    if (mediaUrlWrap) mediaUrlWrap.style.display = 'none';
                    if (emojiDrawer) emojiDrawer.style.display = 'none';
                    if (gifDrawer) gifDrawer.style.display = 'none';

                    // Recharger le flux
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

    // TOAST NOTIFICATION FLOTTANTE
    function showToast(message) {
        let toast = document.getElementById('sf-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'sf-toast';
            toast.className = 'sf-toast';
            document.body.appendChild(toast);
        }
        toast.textContent = message;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 3000);
    }

    // 6. ACTIONS GLOBALES (LIKES, COMMENTAIRES, PARTAGE, SUPPRESSION)
    document.addEventListener('click', async (e) => {
        // A. PARTAGE D'UN POST
        const btnShare = e.target.closest('.btn-share');
        if (btnShare) {
            e.preventDefault();
            const postId = btnShare.dataset.postId;
            const pathUrl = btnShare.dataset.url || `/social#post-${postId}`;
            const fullUrl = pathUrl.startsWith('http') ? pathUrl : window.location.origin + pathUrl;

            if (navigator.share) {
                try {
                    await navigator.share({
                        title: 'Publication Déclic',
                        url: fullUrl
                    });
                    fetch(`/social/post/${postId}/share`, { method: 'POST' });
                } catch (err) {}
            } else {
                navigator.clipboard.writeText(fullUrl).then(() => {
                    showToast('Lien de la publication copié dans le presse-papier ! 📋');
                    fetch(`/social/post/${postId}/share`, { method: 'POST' });
                }).catch(() => {
                    prompt('Copiez ce lien :', fullUrl);
                });
            }
            return;
        }

        // B. COPIER LE LIEN DEPUIS LE MENU (...)
        const btnCopyLink = e.target.closest('.btn-copy-link');
        if (btnCopyLink) {
            e.preventDefault();
            const pathUrl = btnCopyLink.dataset.url || window.location.href;
            const fullUrl = pathUrl.startsWith('http') ? pathUrl : window.location.origin + pathUrl;
            navigator.clipboard.writeText(fullUrl).then(() => {
                showToast('Lien copié dans le presse-papier ! 📋');
            });
            document.querySelectorAll('.dropdown-menu.show').forEach(m => m.classList.remove('show'));
            return;
        }

        // C. SIGNALER UN POST
        const btnReport = e.target.closest('.btn-report-post');
        if (btnReport) {
            e.preventDefault();
            alert('Merci, cette publication a été signalée à l\'équipe de modération.');
            document.querySelectorAll('.dropdown-menu.show').forEach(m => m.classList.remove('show'));
            return;
        }

        // D. LIKE
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
            if (feedContainer) {
                feedContainer.dataset.filter = filter;
                feedContainer.dataset.page = '1';
            }
            reloadFeed(filter);
        }
    });

    // 7. COMMENTAIRES (CHARGEMENT & AJOUT)
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
                    listContainer.innerHTML = '<span class="empty-hint" style="color:var(--sf-text-muted);font-size:0.85rem;">Soyez le premier à commenter !</span>';
                } else {
                    listContainer.innerHTML = data.comments.map(c => `
                        <div class="comment-item" id="comment-${c.id}">
                            <img src="${c.author_avatar}" class="user-avatar-sm" alt="${c.author_name}" />
                            <div class="comment-bubble">
                                <span class="comment-author">${c.author_name}</span>
                                <span class="comment-body">${c.content}</span>
                                <time class="post-time" style="display:block;margin-top:2px;">${c.time_ago}</time>
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

    // 8. RECHARGEMENT DU FLUX
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

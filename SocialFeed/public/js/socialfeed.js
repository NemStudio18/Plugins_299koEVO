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

    // COMPOSER REPLIABLE (EXPAND ON CLICK / FOCUS - MASQUE BIU ET OUTILS PAR DÉFAUT)
    const publishCard = document.getElementById('social-publish-card');

    function expandComposer() {
        if (publishCard && publishCard.classList.contains('is-collapsed')) {
            publishCard.classList.remove('is-collapsed');
            publishCard.classList.add('is-expanded');
            if (postTextarea) {
                postTextarea.style.height = 'auto';
                postTextarea.style.height = Math.max(90, postTextarea.scrollHeight) + 'px';
            }
        }
    }

    function collapseComposerIfEmpty() {
        if (!publishCard || !publishCard.classList.contains('is-expanded')) return;
        const hasText = postTextarea && postTextarea.value.trim().length > 0;
        const hasFile = mediaFileInput && mediaFileInput.files && mediaFileInput.files.length > 0;
        const hasUrl = mediaUrlInput && mediaUrlInput.value.trim().length > 0;
        const isEmojiOpen = emojiDrawer && emojiDrawer.style.display !== 'none';
        const isGifOpen = gifDrawer && gifDrawer.style.display !== 'none';

        if (!hasText && !hasFile && !hasUrl && !isEmojiOpen && !isGifOpen) {
            publishCard.classList.remove('is-expanded');
            publishCard.classList.add('is-collapsed');
            if (postTextarea) {
                postTextarea.style.height = '';
            }
        }
    }

    if (postTextarea) {
        postTextarea.addEventListener('focus', expandComposer);
        postTextarea.addEventListener('click', expandComposer);
    }
    if (publishCard) {
        publishCard.addEventListener('click', (e) => {
            if (e.target.closest('.social-publish-form') || e.target.closest('.publish-header-user')) {
                expandComposer();
            }
        });
    }

    // Clic en dehors du compositeur : replier si vide
    document.addEventListener('click', (e) => {
        if (publishCard && !publishCard.contains(e.target)) {
            const camModal = document.getElementById('camera-recorder-modal');
            if (camModal && camModal.style.display !== 'none') return;
            collapseComposerIfEmpty();
        }
    });

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

    // 3B. ENREGISTREUR CAMÉRA & RÉELS VIDÉO EN DIRECT
    const btnCameraRecord = document.getElementById('btn-camera-record');
    const cameraModal = document.getElementById('camera-recorder-modal');
    const btnCloseCamera = document.getElementById('btn-close-camera');
    const streamVideo = document.getElementById('camera-stream-video');
    const recordedVideo = document.getElementById('camera-recorded-video');
    const recordingStatus = document.getElementById('camera-recording-status');
    const recordTimer = document.getElementById('camera-record-timer');
    const controlsIdle = document.getElementById('camera-controls-idle');
    const controlsRecording = document.getElementById('camera-controls-recording');
    const controlsPreview = document.getElementById('camera-controls-preview');
    const btnStartRecord = document.getElementById('btn-start-record');
    const btnStopRecord = document.getElementById('btn-stop-record');
    const btnRetryRecord = document.getElementById('btn-retry-record');
    const btnUseRecordedVideo = document.getElementById('btn-use-recorded-video');
    const mobileVideoCapture = document.getElementById('mobile-video-capture');

    let cameraStream = null;
    let cameraRecorder = null;
    let recordedChunks = [];
    let recordTimerInterval = null;
    let recordSeconds = 0;
    let recordedBlob = null;

    function formatRecordTime(sec) {
        const m = Math.floor(sec / 60).toString().padStart(2, '0');
        const s = (sec % 60).toString().padStart(2, '0');
        return `${m}:${s}`;
    }

    async function openCamera() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            if (mobileVideoCapture) mobileVideoCapture.click();
            return;
        }

        try {
            cameraStream = await navigator.mediaDevices.getUserMedia({
                video: { width: { ideal: 1280 }, height: { ideal: 720 }, facingMode: 'user' },
                audio: true
            });
            if (streamVideo) {
                streamVideo.srcObject = cameraStream;
                streamVideo.style.display = 'block';
            }
            if (recordedVideo) {
                recordedVideo.style.display = 'none';
                recordedVideo.src = '';
            }
            if (recordingStatus) recordingStatus.style.display = 'none';
            if (controlsIdle) controlsIdle.style.display = 'flex';
            if (controlsRecording) controlsRecording.style.display = 'none';
            if (controlsPreview) controlsPreview.style.display = 'none';
            if (cameraModal) cameraModal.style.display = 'flex';
        } catch (err) {
            console.warn('Accès caméra refusé ou indisponible, fallback mobile:', err);
            if (mobileVideoCapture) mobileVideoCapture.click();
        }
    }

    function startCameraRecording() {
        if (!cameraStream) return;
        recordedChunks = [];
        let mimeType = 'video/webm;codecs=vp8,opus';
        if (!window.MediaRecorder || !MediaRecorder.isTypeSupported(mimeType)) {
            mimeType = '';
        }

        const options = mimeType ? { mimeType } : {};
        try {
            cameraRecorder = new MediaRecorder(cameraStream, options);
        } catch (e) {
            cameraRecorder = new MediaRecorder(cameraStream);
        }

        cameraRecorder.ondataavailable = (e) => {
            if (e.data && e.data.size > 0) {
                recordedChunks.push(e.data);
            }
        };

        cameraRecorder.onstop = () => {
            recordedBlob = new Blob(recordedChunks, { type: cameraRecorder.mimeType || 'video/webm' });
            if (streamVideo) streamVideo.style.display = 'none';
            if (recordedVideo) {
                recordedVideo.src = URL.createObjectURL(recordedBlob);
                recordedVideo.style.display = 'block';
                recordedVideo.play().catch(() => {});
            }
            if (controlsRecording) controlsRecording.style.display = 'none';
            if (controlsPreview) controlsPreview.style.display = 'flex';
            if (recordingStatus) recordingStatus.style.display = 'none';
        };

        cameraRecorder.start(100);
        if (controlsIdle) controlsIdle.style.display = 'none';
        if (controlsRecording) controlsRecording.style.display = 'flex';
        if (recordingStatus) recordingStatus.style.display = 'flex';

        recordSeconds = 0;
        if (recordTimer) recordTimer.textContent = '00:00';
        clearInterval(recordTimerInterval);
        recordTimerInterval = setInterval(() => {
            recordSeconds++;
            if (recordTimer) recordTimer.textContent = formatRecordTime(recordSeconds);
        }, 1000);
    }

    function stopCameraRecording() {
        if (cameraRecorder && cameraRecorder.state !== 'inactive') {
            cameraRecorder.stop();
        }
        clearInterval(recordTimerInterval);
    }

    function closeCameraModal() {
        if (cameraRecorder && cameraRecorder.state !== 'inactive') {
            cameraRecorder.stop();
        }
        clearInterval(recordTimerInterval);
        if (cameraStream) {
            cameraStream.getTracks().forEach(t => t.stop());
            cameraStream = null;
        }
        if (cameraModal) cameraModal.style.display = 'none';
    }

    function applyRecordedVideo() {
        if (!recordedBlob) return;
        const ext = recordedBlob.type.includes('mp4') ? 'mp4' : 'webm';
        const file = new File([recordedBlob], `video_reel_${Date.now()}.${ext}`, {
            type: recordedBlob.type || 'video/webm'
        });

        const dt = new DataTransfer();
        dt.items.add(file);
        if (mediaFileInput) mediaFileInput.files = dt.files;

        if (mediaPreviewVideo) {
            mediaPreviewVideo.src = URL.createObjectURL(file);
            mediaPreviewVideo.style.display = 'block';
            if (mediaPreviewImg) mediaPreviewImg.style.display = 'none';
        }
        if (mediaPreview) mediaPreview.style.display = 'block';

        expandComposer();
        if (postTextarea) postTextarea.focus();
        closeCameraModal();
    }

    if (btnCameraRecord) btnCameraRecord.addEventListener('click', openCamera);
    if (btnCloseCamera) btnCloseCamera.addEventListener('click', closeCameraModal);
    if (btnStartRecord) btnStartRecord.addEventListener('click', startCameraRecording);
    if (btnStopRecord) btnStopRecord.addEventListener('click', stopCameraRecording);
    if (btnRetryRecord) btnRetryRecord.addEventListener('click', openCamera);
    if (btnUseRecordedVideo) btnUseRecordedVideo.addEventListener('click', applyRecordedVideo);

    if (mobileVideoCapture) {
        mobileVideoCapture.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                const dt = new DataTransfer();
                dt.items.add(file);
                if (mediaFileInput) mediaFileInput.files = dt.files;
                if (mediaPreviewVideo) {
                    mediaPreviewVideo.src = URL.createObjectURL(file);
                    mediaPreviewVideo.style.display = 'block';
                    if (mediaPreviewImg) mediaPreviewImg.style.display = 'none';
                }
                if (mediaPreview) mediaPreview.style.display = 'block';
                expandComposer();
                if (postTextarea) postTextarea.focus();
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

        // D. RÉACTIONS STYLE FACEBOOK (POST)
        const btnReact = e.target.closest('.btn-react');
        if (btnReact) {
            e.preventDefault();
            const reaction = btnReact.dataset.reaction || 'like';
            const container = btnReact.closest('.reaction-container');
            const postId = container?.dataset.postId;
            const btnLike = container?.querySelector('.btn-like');
            if (postId && btnLike) {
                applyPostReaction(postId, reaction, btnLike);
            }
            return;
        }

        const btnLike = e.target.closest('.btn-like');
        if (btnLike) {
            e.preventDefault();
            const postId = btnLike.dataset.postId;
            const currentReaction = btnLike.dataset.currentReaction;
            const reaction = currentReaction || 'like';
            applyPostReaction(postId, reaction, btnLike);
            return;
        }

        // RÉACTIONS STYLE FACEBOOK (COMMENTAIRE)
        const btnCommentReact = e.target.closest('.btn-comment-react');
        if (btnCommentReact) {
            e.preventDefault();
            const commentId = btnCommentReact.dataset.commentId;
            const reaction = btnCommentReact.dataset.reaction || 'like';
            const wrap = btnCommentReact.closest('.comment-reaction-wrap');
            const btnCmtLike = wrap?.querySelector('.btn-comment-like');
            if (commentId && btnCmtLike) {
                applyCommentReaction(commentId, reaction, btnCmtLike);
            }
            return;
        }

        const btnCommentLike = e.target.closest('.btn-comment-like');
        if (btnCommentLike) {
            e.preventDefault();
            const commentId = btnCommentLike.dataset.commentId;
            const reaction = btnCommentLike.dataset.reaction || 'like';
            applyCommentReaction(commentId, reaction, btnCommentLike);
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
            return;
        }

        // E. MENU PARTAGE DÉROULANT
        const shareBtn = e.target.closest('.btn-share-toggle');
        if (shareBtn) {
            e.stopPropagation();
            const dropdown = shareBtn.nextElementSibling;
            document.querySelectorAll('.share-menu-dropdown.show').forEach(d => {
                if (d !== dropdown) d.classList.remove('show');
            });
            if (dropdown) dropdown.classList.toggle('show');
            return;
        } else if (!e.target.closest('.share-menu-dropdown')) {
            document.querySelectorAll('.share-menu-dropdown.show').forEach(d => d.classList.remove('show'));
        }

        // F. COPIER LE LIEN DU POST
        const copyBtn = e.target.closest('.btn-copy-link');
        if (copyBtn) {
            const url = copyBtn.dataset.url || window.location.href;
            navigator.clipboard.writeText(url).then(() => {
                copyBtn.innerHTML = '<i class="fa-solid fa-check text-emerald"></i> <span>Lien copié !</span>';
                setTimeout(() => {
                    copyBtn.innerHTML = '<i class="fa-solid fa-link text-blue"></i> <span>Copier le lien</span>';
                }, 2000);
            });
            return;
        }

        // G. REPOSTER SUR MON MUR
        const repostBtn = e.target.closest('.btn-repost-wall');
        if (repostBtn) {
            const author = repostBtn.dataset.author || '';
            const content = repostBtn.dataset.content || '';
            const media = repostBtn.dataset.media || '';
            const token = document.querySelector('input[name="token"]')?.value || window.socialCsrfToken || '';
            
            const repostContent = `RT @${author} : ${content}`;
            const formData = new FormData();
            formData.append('content', repostContent);
            formData.append('token', token);
            if (media) formData.append('media_url', media);

            try {
                const res = await fetch('/social/publish', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                if (data.status === 'success') {
                    alert('Publication partagée sur votre mur !');
                    reloadFeed();
                } else {
                    alert(data.message || 'Erreur lors du partage.');
                }
            } catch (err) {
                console.error(err);
            }
            return;
        }

        // H. RÉPONDRE À UN COMMENTAIRE
        const replyBtn = e.target.closest('.btn-reply-comment');
        if (replyBtn) {
            const postId = replyBtn.dataset.postId;
            const commentId = replyBtn.dataset.commentId;
            const author = replyBtn.dataset.author;
            const form = document.querySelector(`.comment-add-form[data-post-id="${postId}"]`);
            const indicator = document.getElementById(`reply-indicator-${postId}`);
            if (form) {
                form.dataset.parentId = commentId;
                const input = form.querySelector('.comment-input');
                if (input) {
                    input.focus();
                    if (!input.value.startsWith(`@${author} `)) {
                        input.value = `@${author} ` + input.value;
                    }
                }
                if (indicator) {
                    const textSpan = indicator.querySelector('.reply-target-text');
                    if (textSpan) textSpan.textContent = `Réponse à @${author}`;
                    indicator.style.display = 'flex';
                }
            }
            return;
        }

        // I. ANNULER LA RÉPONSE
        const cancelReplyBtn = e.target.closest('.btn-cancel-reply');
        if (cancelReplyBtn) {
            const indicator = cancelReplyBtn.closest('.comment-reply-indicator');
            const section = cancelReplyBtn.closest('.card-comments-section');
            const form = section?.querySelector('.comment-add-form');
            if (form) {
                delete form.dataset.parentId;
            }
            if (indicator) {
                indicator.style.display = 'none';
            }
            return;
        }
    });

    // GESTION TEMPORISÉE DU SURVOL ET TOUCHER DES RÉACTIONS (ANTI-DISPARITION)
    let reactionLeaveTimers = new Map();

    document.addEventListener('mouseover', (e) => {
        const wrap = e.target.closest('.reaction-container, .comment-reaction-wrap');
        if (wrap) {
            const timer = reactionLeaveTimers.get(wrap);
            if (timer) {
                clearTimeout(timer);
                reactionLeaveTimers.delete(wrap);
            }
            wrap.classList.add('is-open');
        }
    });

    document.addEventListener('mouseout', (e) => {
        const wrap = e.target.closest('.reaction-container, .comment-reaction-wrap');
        if (wrap) {
            if (wrap.contains(e.relatedTarget)) return;

            // Délais de grâce confortable de 500ms avant de fermer
            const timer = setTimeout(() => {
                wrap.classList.remove('is-open');
                reactionLeaveTimers.delete(wrap);
            }, 500);
            reactionLeaveTimers.set(wrap, timer);
        }
    });

    // HELPER RÉACTIONS STYLE FACEBOOK
    const REACTION_CONFIG = {
        'like': { emoji: '👍', label: 'J\'aime', color: '#1d9bf0' },
        'love': { emoji: '❤️', label: 'J\'adore', color: '#f91880' },
        'haha': { emoji: '😂', label: 'Haha', color: '#f59e0b' },
        'sad': { emoji: '😢', label: 'Triste', color: '#eab308' },
        'angry': { emoji: '😡', label: 'Grr', color: '#ef4444' }
    };

    async function applyPostReaction(postId, reaction, btnLike) {
        btnLike.closest('.reaction-container')?.classList.remove('is-open');
        const formData = new FormData();
        formData.append('reaction', reaction);
        const token = document.querySelector('input[name="token"]')?.value || window.socialCsrfToken || '';
        formData.append('token', token);

        try {
            const res = await fetch(`/social/post/${postId}/like`, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            if (data.status === 'success') {
                btnLike.classList.toggle('liked', data.has_liked);
                btnLike.dataset.currentReaction = data.reaction || '';
                
                const countSpan = btnLike.querySelector('.likes-count');
                if (countSpan) countSpan.textContent = data.likes_count;

                if (data.has_liked && data.reaction && REACTION_CONFIG[data.reaction]) {
                    const cfg = REACTION_CONFIG[data.reaction];
                    btnLike.innerHTML = `<span class="reaction-emoji">${cfg.emoji}</span> <span class="like-label" style="color:${cfg.color}">${cfg.label}</span> <span class="count likes-count">${data.likes_count}</span>`;
                } else {
                    btnLike.innerHTML = `<i class="fa-regular fa-thumbs-up"></i> <span class="like-label">J'aime</span> <span class="count likes-count">${data.likes_count}</span>`;
                }
            } else if (data.message) {
                alert(data.message);
            }
        } catch (err) {
            console.error(err);
        }
    }

    async function applyCommentReaction(commentId, reaction, btnCommentLike) {
        btnCommentLike.closest('.comment-reaction-wrap')?.classList.remove('is-open');
        const formData = new FormData();
        formData.append('reaction', reaction);
        const token = document.querySelector('input[name="token"]')?.value || window.socialCsrfToken || '';
        formData.append('token', token);

        try {
            const res = await fetch(`/social/comment/${commentId}/like`, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            if (data.status === 'success') {
                btnCommentLike.classList.toggle('liked', data.has_liked);
                btnCommentLike.dataset.reaction = data.reaction || '';
                const countStr = data.likes_count > 0 ? ` ${data.likes_count}` : '';

                if (data.has_liked && data.reaction && REACTION_CONFIG[data.reaction]) {
                    btnCommentLike.innerHTML = `${REACTION_CONFIG[data.reaction].emoji}<span class="comment-likes-count">${countStr}</span>`;
                } else {
                    btnCommentLike.innerHTML = `<i class="fa-regular fa-thumbs-up"></i><span class="comment-likes-count">${countStr}</span>`;
                }
            } else if (data.message) {
                alert(data.message);
            }
        } catch (err) {
            console.error(err);
        }
    }

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
                    listContainer.innerHTML = data.comments.map(c => {
                        const reactionEmoji = c.user_reaction && REACTION_CONFIG[c.user_reaction] 
                            ? REACTION_CONFIG[c.user_reaction].emoji 
                            : (c.has_liked ? '👍' : '<i class="fa-regular fa-thumbs-up"></i>');
                        const countStr = c.likes_count > 0 ? ` ${c.likes_count}` : '';

                        return `
                        <div class="comment-item ${c.parent_id ? 'comment-reply' : ''}" id="comment-${c.id}">
                            <img src="${c.author_avatar}" class="user-avatar-sm" alt="${c.author_name}" />
                            <div class="comment-bubble">
                                <span class="comment-author">${c.author_name}</span>
                                <span class="comment-body">${c.content}</span>
                                <div class="comment-footer-actions">
                                    <time class="post-time">${c.time_ago}</time>
                                    <div class="comment-reaction-wrap">
                                        <button type="button" class="btn-comment-like ${c.has_liked ? 'liked' : ''}" data-comment-id="${c.id}" data-reaction="${c.user_reaction || 'like'}">
                                            ${reactionEmoji}<span class="comment-likes-count">${countStr}</span>
                                        </button>
                                        <div class="comment-reactions-popover">
                                            <button type="button" class="btn-comment-react" data-comment-id="${c.id}" data-reaction="like" title="J'aime">👍</button>
                                            <button type="button" class="btn-comment-react" data-comment-id="${c.id}" data-reaction="love" title="J'adore">❤️</button>
                                            <button type="button" class="btn-comment-react" data-comment-id="${c.id}" data-reaction="haha" title="Haha">😂</button>
                                            <button type="button" class="btn-comment-react" data-comment-id="${c.id}" data-reaction="sad" title="Triste">😢</button>
                                            <button type="button" class="btn-comment-react" data-comment-id="${c.id}" data-reaction="angry" title="Grr">😡</button>
                                        </div>
                                    </div>
                                    <button type="button" class="btn-reply-comment" data-post-id="${postId}" data-comment-id="${c.id}" data-author="${c.author_name}">Répondre</button>
                                </div>
                            </div>
                        </div>
                    `;}).join('');
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

            const token = commentForm.querySelector('input[name="token"]')?.value
                || document.querySelector('input[name="token"]')?.value
                || window.socialCsrfToken
                || '';
            const formData = new FormData();
            formData.append('content', content);
            formData.append('token', token);
            if (commentForm.dataset.parentId) {
                formData.append('parent_id', commentForm.dataset.parentId);
            }

            const submitBtn = commentForm.querySelector('.btn-comment-submit');
            if (submitBtn) submitBtn.disabled = true;

            try {
                const res = await fetch(`/social/post/${postId}/comment`, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                if (data.status === 'success') {
                    input.value = '';
                    delete commentForm.dataset.parentId;
                    const indicator = document.getElementById(`reply-indicator-${postId}`);
                    if (indicator) indicator.style.display = 'none';
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
            } finally {
                if (submitBtn) submitBtn.disabled = false;
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

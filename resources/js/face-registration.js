import * as faceapi from '@vladmandic/face-api';

const MODEL_URL = '/models/face-api';
const MAX_CAPTURE_DIMENSION = 720;
const DETECTION_INTERVAL_MS = 220;
const SMART_POSE_LABELS = [
    'Tampak Depan',
    'Agak Kiri',
    'Agak Kanan',
    'Berkacamata',
    'Ekspresi Senyum',
];

let modelsPromise = null;
let modelsLoaded = false;

/**
 * Muat bobot model ringan (@vladmandic/face-api) satu kali dari public/models/face-api.
 */
async function ensureModelsLoaded(onStatusChange) {
    if (modelsLoaded) {
        return true;
    }

    if (!modelsPromise) {
        if (typeof onStatusChange === 'function') {
            onStatusChange('Memuat model AI wajah ringan...', 'warning');
        }

        modelsPromise = Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
            faceapi.nets.faceLandmark68TinyNet.loadFromUri(MODEL_URL),
            faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
        ])
            .then(() => {
                modelsLoaded = true;
                return true;
            })
            .catch((err) => {
                modelsPromise = null;
                throw err;
            });
    }

    return modelsPromise;
}

/**
 * Hitung dimensi baru dengan menjaga rasio aspek dan batas sisi terpanjang.
 */
function resolveScaledSize(width, height, maxDim = MAX_CAPTURE_DIMENSION) {
    if (!width || !height) {
        return { width: 640, height: 480 };
    }

    if (width <= maxDim && height <= maxDim) {
        return { width, height };
    }

    const ratio = Math.min(maxDim / width, maxDim / height);
    return {
        width: Math.round(width * ratio),
        height: Math.round(height * ratio),
    };
}

/**
 * Gambar kotak deteksi wajah dan titik landmark pada canvas overlay.
 */
function drawDetectionOverlay(overlayCanvas, sourceWidth, sourceHeight, detections) {
    if (!overlayCanvas) return;

    overlayCanvas.width = sourceWidth;
    overlayCanvas.height = sourceHeight;

    const ctx = overlayCanvas.getContext('2d');
    ctx.clearRect(0, 0, sourceWidth, sourceHeight);

    if (!Array.isArray(detections) || detections.length === 0) {
        return;
    }

    const isSingle = detections.length === 1;
    const strokeColor = isSingle ? '#16A34A' : '#DC2626';

    detections.forEach((det) => {
        const box = det.detection ? det.detection.box : det.box;
        if (!box) return;

        ctx.strokeStyle = strokeColor;
        ctx.lineWidth = Math.max(2, Math.round(sourceWidth / 220));
        ctx.strokeRect(box.x, box.y, box.width, box.height);

        if (det.landmarks && typeof det.landmarks.positions !== 'undefined') {
            ctx.fillStyle = isSingle ? '#38BDF8' : '#FCA5A5';
            det.landmarks.positions.forEach((pt) => {
                ctx.beginPath();
                ctx.arc(pt.x, pt.y, 1.8, 0, Math.PI * 2);
                ctx.fill();
            });
        }
    });
}

function initFaceRegistrationPage() {
    const appRoot = document.getElementById('pendaftaranMukaApp');
    if (!appRoot) return;

    const canEdit = appRoot.dataset.canEdit === '1';
    const canDelete = appRoot.dataset.canDelete === '1';

    // Elemen Modal Daftar Wajah
    const modalRegisterId = 'modalRegisterFace';
    const formRegister = document.getElementById('formRegisterFace');
    const btnOpenRegisterFace = document.getElementById('btnOpenRegisterFace');
    const btnSubmitRegisterFace = document.getElementById('btnSubmitRegisterFace');
    const inputFotoBase64 = document.getElementById('register_foto_base64');
    const inputDescriptor = document.getElementById('register_face_descriptor');
    const inputConfidence = document.getElementById('register_confidence');
    const inputLabel = document.getElementById('register_label');

    // Tab Mode Kamera vs Unggah Foto
    const tabCamera = document.getElementById('tabModeCamera');
    const tabUpload = document.getElementById('tabModeUpload');
    const panelCameraActions = document.getElementById('panelCameraActions');
    const panelUploadActions = document.getElementById('panelUploadActions');

    // Elemen Stage Kamera & Pratinjau
    const videoEl = document.getElementById('faceCameraVideo');
    const overlayCanvas = document.getElementById('faceOverlayCanvas');
    const snapshotImg = document.getElementById('faceSnapshotImg');
    const stagePlaceholder = document.getElementById('faceStagePlaceholder');
    const stagePlaceholderText = document.getElementById('faceStagePlaceholderText');
    const stageBadge = document.getElementById('faceStageBadge');
    const stageBadgeText = document.getElementById('faceStageBadgeText');

    const btnStartCamera = document.getElementById('btnStartFaceCamera');
    const btnCaptureCamera = document.getElementById('btnCaptureFaceCamera');
    const btnRetakeCamera = document.getElementById('btnRetakeFaceCamera');
    const cameraInsecureWarning = document.getElementById('faceCameraInsecureAlert');

    const uploadArea = document.getElementById('faceUploadDropzone');
    const uploadFileInput = document.getElementById('faceUploadInput');
    const detectionFeedback = document.getElementById('faceDetectionFeedback');

    // Elemen Modal Kelola Sampel
    const modalManageId = 'modalManageFaces';
    const manageBody = document.getElementById('manageFacesListContainer');
    const manageEmpty = document.getElementById('manageFacesEmpty');
    const manageHeaderName = document.getElementById('manageFacesMuridNama');
    const manageHeaderMeta = document.getElementById('manageFacesMuridMeta');
    const manageCountBadge = document.getElementById('manageFacesCountBadge');
    const manageAvatarImg = document.getElementById('manageFacesAvatarImg');
    const manageAvatarInitials = document.getElementById('manageFacesInitials');
    const manageAvatarWrap = document.getElementById('manageFacesAvatarWrap');
    const btnAddSampleFromManage = document.getElementById('btnAddSampleFromManage');
    const formToggleFaceProfile = document.getElementById('formToggleFaceProfile');

    let currentMode = 'camera';
    let mediaStream = null;
    let detectionTimer = null;
    let isDetectingFrame = false;
    let isCaptured = false;
    let activeManageMurid = null;

    const detectorOptions = new faceapi.TinyFaceDetectorOptions({
        inputSize: 320,
        scoreThreshold: 0.5,
    });

    function setStageBadge(text, variant = 'neutral') {
        if (!stageBadge || !stageBadgeText) return;
        stageBadge.classList.remove('is-success', 'is-warning', 'is-danger');
        if (variant === 'success') stageBadge.classList.add('is-success');
        if (variant === 'warning') stageBadge.classList.add('is-warning');
        if (variant === 'danger') stageBadge.classList.add('is-danger');
        stageBadgeText.textContent = text;
    }

    function setFeedback(text, type = 'muted') {
        if (!detectionFeedback) return;
        detectionFeedback.textContent = text;
        detectionFeedback.classList.remove('text-slate-500', 'text-emerald-600', 'text-amber-600', 'text-danger');
        if (type === 'success') detectionFeedback.classList.add('text-emerald-600');
        else if (type === 'warning') detectionFeedback.classList.add('text-amber-600');
        else if (type === 'danger') detectionFeedback.classList.add('text-danger');
        else detectionFeedback.classList.add('text-slate-500');
    }

    function clearExtractedPayload() {
        isCaptured = false;
        if (inputFotoBase64) inputFotoBase64.value = '';
        if (inputDescriptor) inputDescriptor.value = '';
        if (inputConfidence) inputConfidence.value = '';
        if (btnSubmitRegisterFace) btnSubmitRegisterFace.disabled = true;
    }

    function stopDetectionLoop() {
        if (detectionTimer) {
            clearInterval(detectionTimer);
            detectionTimer = null;
        }
        isDetectingFrame = false;
    }

    function stopCameraStream() {
        stopDetectionLoop();
        if (mediaStream) {
            mediaStream.getTracks().forEach((track) => track.stop());
            mediaStream = null;
        }
        if (videoEl) {
            videoEl.srcObject = null;
            videoEl.classList.add('hidden');
        }
        if (overlayCanvas) {
            const ctx = overlayCanvas.getContext('2d');
            ctx.clearRect(0, 0, overlayCanvas.width || 1, overlayCanvas.height || 1);
            overlayCanvas.classList.remove('is-mirrored');
        }
    }

    function resetStageVisuals(placeholderText) {
        clearExtractedPayload();
        stopCameraStream();
        if (snapshotImg) {
            snapshotImg.src = '';
            snapshotImg.classList.add('hidden');
        }
        if (stagePlaceholder) {
            stagePlaceholder.classList.remove('hidden');
        }
        if (stagePlaceholderText && placeholderText) {
            stagePlaceholderText.textContent = placeholderText;
        }
        if (btnCaptureCamera) btnCaptureCamera.disabled = true;
        if (btnRetakeCamera) btnRetakeCamera.classList.add('hidden');
        if (btnStartCamera) btnStartCamera.classList.remove('hidden');
    }

    function switchMode(mode) {
        currentMode = mode;

        if (tabCamera && tabUpload) {
            const isCam = mode === 'camera';
            tabCamera.classList.toggle('active', isCam);
            tabCamera.setAttribute('aria-selected', isCam ? 'true' : 'false');
            tabUpload.classList.toggle('active', !isCam);
            tabUpload.setAttribute('aria-selected', !isCam ? 'true' : 'false');
        }

        if (panelCameraActions) panelCameraActions.classList.toggle('hidden', mode !== 'camera');
        if (panelUploadActions) panelUploadActions.classList.toggle('hidden', mode !== 'upload');

        if (mode === 'camera') {
            resetStageVisuals('Klik "Aktifkan Kamera" untuk mulai memindai wajah.');
            setStageBadge('Kamera Siap', 'neutral');
            setFeedback('Arahkan 1 wajah murid ke kamera lalu ambil foto.', 'muted');
            startCamera();
        } else {
            resetStageVisuals('Pilih atau seret berkas foto wajah murid pada area unggah di bawah.');
            if (cameraInsecureWarning) cameraInsecureWarning.classList.add('hidden');
            setStageBadge('Mode Unggah Foto', 'neutral');
            setFeedback('Unggah foto berwajah jelas (JPG, PNG, WebP).', 'muted');
            if (uploadFileInput) uploadFileInput.value = '';
        }
    }

    async function startCamera() {
        if (currentMode !== 'camera') return;

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            if (cameraInsecureWarning) {
                cameraInsecureWarning.classList.remove('hidden');
            }
            setStageBadge('Kamera Tidak Tersedia', 'warning');
            setFeedback('Browser memblokir akses kamera (butuh HTTPS/localhost). Gunakan tab Unggah Foto.', 'warning');
            return;
        }

        if (cameraInsecureWarning) {
            cameraInsecureWarning.classList.add('hidden');
        }

        resetStageVisuals('Menghubungkan kamera dan memuat model AI...');
        setStageBadge('Menyiapkan AI & Kamera...', 'warning');

        try {
            await ensureModelsLoaded((msg, variant) => setStageBadge(msg, variant));

            mediaStream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'user',
                    width: { ideal: 640 },
                    height: { ideal: 480 },
                },
                audio: false,
            });

            if (currentMode !== 'camera') {
                stopCameraStream();
                return;
            }

            if (videoEl) {
                videoEl.srcObject = mediaStream;
                videoEl.classList.remove('hidden');
                videoEl.classList.add('is-mirrored');
                await videoEl.play();
            }

            if (overlayCanvas) {
                overlayCanvas.classList.add('is-mirrored');
            }
            if (stagePlaceholder) stagePlaceholder.classList.add('hidden');
            if (btnStartCamera) btnStartCamera.classList.add('hidden');
            if (btnCaptureCamera) btnCaptureCamera.disabled = true;

            setStageBadge('Mencari Wajah...', 'neutral');
            setFeedback('Posisikan tepat 1 wajah murid di depan kamera.', 'muted');

            startLiveDetectionLoop();
        } catch (err) {
            stopCameraStream();
            if (cameraInsecureWarning) {
                cameraInsecureWarning.classList.remove('hidden');
            }
            setStageBadge('Gagal Membuka Kamera', 'danger');
            setFeedback('Kamera tidak ditemukan atau izin ditolak. Silakan beralih ke tab Unggah Foto.', 'danger');
        }
    }

    function startLiveDetectionLoop() {
        stopDetectionLoop();

        detectionTimer = setInterval(async () => {
            if (isDetectingFrame || isCaptured || currentMode !== 'camera' || !videoEl || videoEl.readyState < 2) {
                return;
            }

            isDetectingFrame = true;
            try {
                const detections = await faceapi
                    .detectAllFaces(videoEl, detectorOptions)
                    .withFaceLandmarks(true);

                const vw = videoEl.videoWidth || 640;
                const vh = videoEl.videoHeight || 480;
                drawDetectionOverlay(overlayCanvas, vw, vh, detections);

                if (detections.length === 0) {
                    if (btnCaptureCamera) btnCaptureCamera.disabled = true;
                    setStageBadge('Wajah Tidak Terdeteksi', 'warning');
                } else if (detections.length > 1) {
                    if (btnCaptureCamera) btnCaptureCamera.disabled = true;
                    setStageBadge(`Terdeteksi ${detections.length} Wajah (Harus 1)`, 'danger');
                } else {
                    const scorePct = Math.round((detections[0].detection.score || 0) * 100);
                    if (btnCaptureCamera) btnCaptureCamera.disabled = false;
                    setStageBadge(`1 Wajah Terdeteksi • ${scorePct}%`, 'success');
                }
            } catch (err) {
                console.error('Live detection error:', err);
            } finally {
                isDetectingFrame = false;
            }
        }, DETECTION_INTERVAL_MS);
    }

    async function captureFromCamera() {
        if (!videoEl || videoEl.readyState < 2) return;

        if (btnCaptureCamera) btnCaptureCamera.disabled = true;
        setStageBadge('Mengekstrak Vektor Wajah...', 'warning');
        setFeedback('Sedang mengekstrak 128 fitur wajah secara lokal di browser...', 'muted');

        try {
            await ensureModelsLoaded();

            const rawW = videoEl.videoWidth || 640;
            const rawH = videoEl.videoHeight || 480;
            const { width, height } = resolveScaledSize(rawW, rawH);

            const captureCanvas = document.createElement('canvas');
            captureCanvas.width = width;
            captureCanvas.height = height;

            const ctx = captureCanvas.getContext('2d');
            // Mirror horizontal agar foto tersimpan sesuai pratinjau cermin yang dilihat pengguna
            ctx.translate(width, 0);
            ctx.scale(-1, 1);
            ctx.drawImage(videoEl, 0, 0, width, height);
            ctx.setTransform(1, 0, 0, 1, 0, 0);

            const detections = await faceapi
                .detectAllFaces(captureCanvas, detectorOptions)
                .withFaceLandmarks(true)
                .withFaceDescriptors();

            if (detections.length !== 1) {
                setStageBadge(
                    detections.length === 0 ? 'Wajah Gagal Terbaca Saat Jepret' : `Terdeteksi ${detections.length} Wajah`,
                    'danger'
                );
                setFeedback('Pastikan hanya ada 1 wajah jelas dan tidak bergerak saat mengambil foto.', 'danger');
                if (btnCaptureCamera) btnCaptureCamera.disabled = false;
                return;
            }

            applyValidDetectionResult(captureCanvas, detections[0], true);
        } catch (err) {
            console.error('Capture error:', err);
            setStageBadge('Gagal Memproses Wajah', 'danger');
            setFeedback('Terjadi kesalahan saat mengekstrak fitur wajah. Coba ambil ulang.', 'danger');
            if (btnCaptureCamera) btnCaptureCamera.disabled = false;
        }
    }

    async function processUploadedFile(file) {
        if (!file) return;

        const allowedTypes = ['image/png', 'image/jpeg', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            setStageBadge('Format Berkas Tidak Didukung', 'danger');
            setFeedback('Pilih berkas gambar PNG, JPG, atau WebP.', 'danger');
            clearExtractedPayload();
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            setStageBadge('Berkas Terlalu Besar', 'danger');
            setFeedback('Ukuran berkas awal maksimal 5MB.', 'danger');
            clearExtractedPayload();
            return;
        }

        clearExtractedPayload();
        setStageBadge('Memuat AI & Memindai Foto...', 'warning');
        setFeedback('Mendeteksi wajah dan mengekstrak descriptor dari foto...', 'muted');

        try {
            await ensureModelsLoaded((msg, variant) => setStageBadge(msg, variant));

            const dataUrl = await new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = (ev) => resolve(ev.target.result);
                reader.onerror = reject;
                reader.readAsDataURL(file);
            });

            const img = await new Promise((resolve, reject) => {
                const imageEl = new Image();
                imageEl.onload = () => resolve(imageEl);
                imageEl.onerror = reject;
                imageEl.src = dataUrl;
            });

            const { width, height } = resolveScaledSize(img.naturalWidth || img.width, img.naturalHeight || img.height);
            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;
            canvas.getContext('2d').drawImage(img, 0, 0, width, height);

            // Tampilkan pratinjau langsung pada stage
            const base64Jpeg = canvas.toDataURL('image/jpeg', 0.88);
            if (snapshotImg) {
                snapshotImg.src = base64Jpeg;
                snapshotImg.classList.remove('hidden');
            }
            if (stagePlaceholder) stagePlaceholder.classList.add('hidden');

            const detections = await faceapi
                .detectAllFaces(canvas, detectorOptions)
                .withFaceLandmarks(true)
                .withFaceDescriptors();

            if (detections.length === 0) {
                drawDetectionOverlay(overlayCanvas, width, height, []);
                setStageBadge('Wajah Tidak Terdeteksi', 'danger');
                setFeedback('Tidak ditemukan wajah yang jelas pada foto ini. Gunakan foto close-up yang menghadap depan.', 'danger');
                return;
            }

            if (detections.length > 1) {
                drawDetectionOverlay(overlayCanvas, width, height, detections);
                setStageBadge(`Terdeteksi ${detections.length} Wajah (Harus 1)`, 'danger');
                setFeedback('Foto berisi lebih dari 1 orang. Unggah foto individu murid saja.', 'danger');
                return;
            }

            applyValidDetectionResult(canvas, detections[0], false, base64Jpeg);
        } catch (err) {
            console.error('Upload face extraction error:', err);
            setStageBadge('Gagal Memindai Foto', 'danger');
            setFeedback('Terjadi kesalahan saat membaca foto. Pastikan file gambar tidak rusak.', 'danger');
        }
    }

    function applyValidDetectionResult(canvas, detectionResult, fromCamera = false, existingBase64 = null) {
        const descriptorArray = Array.from(detectionResult.descriptor || []).map((num) => Number(num.toFixed(6)));
        if (descriptorArray.length !== 128) {
            setStageBadge('Vektor Wajah Tidak Valid', 'danger');
            setFeedback('Gagal menghasilkan 128 fitur wajah. Silakan coba lagi.', 'danger');
            return;
        }

        const base64Jpeg = existingBase64 || canvas.toDataURL('image/jpeg', 0.88);
        const confidence = Number((detectionResult.detection?.score || 0).toFixed(4));
        const confidencePct = Math.round(confidence * 100);

        if (fromCamera) {
            stopCameraStream();
            if (snapshotImg) {
                snapshotImg.src = base64Jpeg;
                snapshotImg.classList.remove('hidden');
            }
            if (stagePlaceholder) stagePlaceholder.classList.add('hidden');
            if (btnRetakeCamera) btnRetakeCamera.classList.remove('hidden');
            if (btnCaptureCamera) btnCaptureCamera.classList.add('hidden');
        }

        if (overlayCanvas) {
            overlayCanvas.classList.remove('is-mirrored');
            drawDetectionOverlay(overlayCanvas, canvas.width, canvas.height, [detectionResult]);
        }

        isCaptured = true;
        if (inputFotoBase64) inputFotoBase64.value = base64Jpeg;
        if (inputDescriptor) inputDescriptor.value = JSON.stringify(descriptorArray);
        if (inputConfidence) inputConfidence.value = String(confidence);
        if (btnSubmitRegisterFace) btnSubmitRegisterFace.disabled = false;

        setStageBadge(`Wajah Valid • Skor ${confidencePct}%`, 'success');
        setFeedback('Vektor 128-dimensi berhasil diekstrak. Klik "Simpan Sampel Wajah" untuk mendaftarkan.', 'success');
    }

    function openRegisterModalForMurid(muridId = null, existingCount = 0) {
        clearExtractedPayload();
        if (btnCaptureCamera) btnCaptureCamera.classList.remove('hidden');
        if (btnRetakeCamera) btnRetakeCamera.classList.add('hidden');

        const muridSelect = window.SearchableSelect?.getInstance('register_murid_id');
        if (muridSelect) {
            if (muridId) {
                muridSelect.setValue(String(muridId), '', '', false);
            } else {
                muridSelect.clear(false);
            }
        }

        if (inputLabel) {
            const suggestedLabel = SMART_POSE_LABELS[Math.min(existingCount, SMART_POSE_LABELS.length - 1)] || 'Tampak Depan';
            inputLabel.value = suggestedLabel;
        }

        window.InforaModals.open(modalRegisterId);
        // Mulai mode default sesuai dukungan kamera/HTTPS
        const prefersCamera = Boolean(window.isSecureContext && navigator.mediaDevices?.getUserMedia);
        switchMode(prefersCamera ? 'camera' : 'upload');
    }

    function renderManageSamples(data) {
        if (manageHeaderName) manageHeaderName.textContent = data.nama_lengkap || '-';
        if (manageHeaderMeta) {
            const idPart = data.nisn && data.nisn !== '-' ? `NISN: ${data.nisn}` : (data.nis && data.nis !== '-' ? `NIS: ${data.nis}` : 'Tanpa NISN');
            manageHeaderMeta.textContent = `${data.kelas_nama || 'Tanpa Kelas'} • ${idPart}`;
        }
        if (manageCountBadge) {
            manageCountBadge.textContent = `${data.active_count} Aktif (${data.total_count}/${data.max_samples} Sampel)`;
        }

        if (data.foto_url) {
            if (manageAvatarImg) {
                manageAvatarImg.src = data.foto_url;
                manageAvatarImg.classList.remove('hidden');
            }
            if (manageAvatarInitials) manageAvatarInitials.classList.add('hidden');
            if (manageAvatarWrap) manageAvatarWrap.classList.add('has-image');
        } else {
            if (manageAvatarImg) {
                manageAvatarImg.src = '';
                manageAvatarImg.classList.add('hidden');
            }
            if (manageAvatarInitials) {
                manageAvatarInitials.textContent = (data.nama_lengkap || 'MD').substring(0, 2).toUpperCase();
                manageAvatarInitials.classList.remove('hidden');
            }
            if (manageAvatarWrap) manageAvatarWrap.classList.remove('has-image');
        }

        if (btnAddSampleFromManage) {
            const reachedLimit = (data.total_count || 0) >= (data.max_samples || 5);
            btnAddSampleFromManage.classList.toggle('hidden', reachedLimit);
        }

        if (!manageBody) return;
        manageBody.innerHTML = '';

        const profiles = Array.isArray(data.profiles) ? data.profiles : [];
        if (manageEmpty) {
            manageEmpty.classList.toggle('hidden', profiles.length > 0);
        }

        profiles.forEach((profile) => {
            const card = document.createElement('div');
            card.className = `face-sample-card ${profile.is_active ? '' : 'is-inactive'}`;

            const media = document.createElement('div');
            media.className = 'face-sample-media';

            const img = document.createElement('img');
            img.className = 'face-sample-img';
            img.src = profile.foto_url || '';
            img.alt = profile.label || 'Sampel Wajah';
            media.appendChild(img);

            const body = document.createElement('div');
            body.className = 'face-sample-body';

            const topRow = document.createElement('div');
            topRow.className = 'flex items-center justify-between gap-2';

            const labelBadge = document.createElement('span');
            labelBadge.className = 'badge badge-primary';
            labelBadge.textContent = profile.label || 'Tampak Depan';

            const statusBadge = document.createElement('span');
            statusBadge.className = `badge ${profile.is_active ? 'badge-success' : 'badge-neutral'}`;
            statusBadge.textContent = profile.is_active ? 'Aktif' : 'Nonaktif';

            topRow.appendChild(labelBadge);
            topRow.appendChild(statusBadge);

            const metaText = document.createElement('div');
            metaText.className = 'text-xs text-slate-500';
            metaText.textContent = `Skor: ${profile.confidence_percent} • ${profile.created_at_formatted}`;

            const actions = document.createElement('div');
            actions.className = 'face-sample-actions';

            if (canEdit) {
                const toggleBtn = document.createElement('button');
                toggleBtn.type = 'button';
                toggleBtn.className = 'btn-secondary text-xs';
                toggleBtn.textContent = profile.is_active ? 'Nonaktifkan' : 'Aktifkan';
                toggleBtn.addEventListener('click', () => {
                    if (!formToggleFaceProfile) return;
                    formToggleFaceProfile.action = profile.toggle_url;
                    formToggleFaceProfile.submit();
                });
                actions.appendChild(toggleBtn);
            }

            if (canDelete) {
                const delBtn = document.createElement('button');
                delBtn.type = 'button';
                delBtn.className = 'btn-delete text-xs';
                delBtn.textContent = 'Hapus';
                delBtn.addEventListener('click', () => {
                    const deleteForm = document.getElementById('formDeleteFaceProfile');
                    if (deleteForm) deleteForm.action = profile.delete_url;
                    const titleEl = document.getElementById('modalDeleteFaceProfileTitle');
                    const msgEl = document.getElementById('deleteFaceProfileMessage');
                    const badgeEl = document.getElementById('deleteFaceProfileBadge');
                    const itemEl = document.getElementById('deleteFaceProfileTitle');

                    if (titleEl) titleEl.textContent = 'Hapus Sampel Wajah?';
                    if (msgEl) msgEl.textContent = 'Sampel foto dan vektor wajah berikut akan dihapus permanen dari penyimpanan.';
                    if (badgeEl) badgeEl.textContent = profile.label || 'Sampel Wajah';
                    if (itemEl) itemEl.textContent = `${data.nama_lengkap} (${profile.created_at_formatted})`;

                    window.InforaModals.close(modalManageId);
                    window.InforaModals.open('modalDeleteFaceProfile');
                });
                actions.appendChild(delBtn);
            }

            body.appendChild(topRow);
            body.appendChild(metaText);
            if (canEdit || canDelete) {
                body.appendChild(actions);
            }

            card.appendChild(media);
            card.appendChild(body);
            manageBody.appendChild(card);
        });
    }

    // Registrasi Modal & Event Listeners
    if (window.InforaModals) {
        window.InforaModals.setup(modalRegisterId, { cancelBtnId: 'btnCancelRegisterFace' });
        window.InforaModals.setup(modalManageId, { cancelBtnId: 'btnCloseManageFaces' });
        window.InforaModals.setup('modalDeleteFaceProfile', { cancelBtnId: 'btnCancelDeleteFaceProfile' });

        window.InforaModals.onClose(modalRegisterId, () => {
            stopCameraStream();
            clearExtractedPayload();
        });
    }

    if (btnOpenRegisterFace) {
        btnOpenRegisterFace.addEventListener('click', () => {
            openRegisterModalForMurid(null, 0);
        });
    }

    document.querySelectorAll('.btn-open-register-face').forEach((btn) => {
        btn.addEventListener('click', () => {
            const muridId = btn.dataset.muridId || null;
            const sampleCount = parseInt(btn.dataset.sampleCount || '0', 10);
            openRegisterModalForMurid(muridId, sampleCount);
        });
    });

    document.querySelectorAll('.btn-open-manage-faces').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const url = btn.dataset.url;
            if (!url) return;

            try {
                const response = await fetch(url, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                if (!response.ok) {
                    throw new Error('Gagal memuat sampel wajah.');
                }
                const data = await response.json();
                activeManageMurid = data;
                renderManageSamples(data);
                window.InforaModals.open(modalManageId);
            } catch (err) {
                console.error(err);
                if (window.showToast) {
                    window.showToast('Gagal memuat daftar sampel wajah murid.');
                }
            }
        });
    });

    if (btnAddSampleFromManage) {
        btnAddSampleFromManage.addEventListener('click', () => {
            if (!activeManageMurid) return;
            const targetId = activeManageMurid.id;
            const targetCount = activeManageMurid.total_count || 0;
            window.InforaModals.close(modalManageId);
            openRegisterModalForMurid(targetId, targetCount);
        });
    }

    if (tabCamera) {
        tabCamera.addEventListener('click', () => switchMode('camera'));
    }

    if (tabUpload) {
        tabUpload.addEventListener('click', () => switchMode('upload'));
    }

    if (btnStartCamera) {
        btnStartCamera.addEventListener('click', () => startCamera());
    }

    if (btnCaptureCamera) {
        btnCaptureCamera.addEventListener('click', () => captureFromCamera());
    }

    if (btnRetakeCamera) {
        btnRetakeCamera.addEventListener('click', () => {
            if (btnCaptureCamera) btnCaptureCamera.classList.remove('hidden');
            if (btnRetakeCamera) btnRetakeCamera.classList.add('hidden');
            startCamera();
        });
    }

    if (uploadArea && uploadFileInput) {
        uploadArea.addEventListener('click', () => uploadFileInput.click());
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('is-dragover');
        });
        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('is-dragover');
        });
        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('is-dragover');
            const droppedFile = e.dataTransfer?.files?.[0];
            if (droppedFile) {
                processUploadedFile(droppedFile);
            }
        });
        uploadFileInput.addEventListener('change', () => {
            const chosenFile = uploadFileInput.files?.[0];
            if (chosenFile) {
                processUploadedFile(chosenFile);
            }
        });
    }

    if (formRegister) {
        formRegister.addEventListener('submit', (e) => {
            if (!inputFotoBase64?.value || !inputDescriptor?.value) {
                e.preventDefault();
                e.stopImmediatePropagation();
                if (window.InforaProgress) {
                    window.InforaProgress.hide();
                }
                setStageBadge('Wajah Belum Diambil', 'danger');
                setFeedback('Ambil foto dari kamera atau unggah foto berwajah jelas terlebih dahulu.', 'danger');
            }
        }, true);
    }

    // Hangatkan model ringan di latar belakang setelah halaman selesai render
    const warmup = () => {
        ensureModelsLoaded().catch(() => {
            // Abaikan kegagalan preload awal; akan dicoba ulang saat modal dibuka
        });
    };
    if ('requestIdleCallback' in window) {
        window.requestIdleCallback(warmup, { timeout: 2500 });
    } else {
        setTimeout(warmup, 800);
    }

    // Reopen modal otomatis jika terjadi validasi gagal dari backend
    if (window.hasValidationErrors && window.InforaModals) {
        window.InforaModals.reopenOnError({
            create: modalRegisterId,
        });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initFaceRegistrationPage);
} else {
    initFaceRegistrationPage();
}

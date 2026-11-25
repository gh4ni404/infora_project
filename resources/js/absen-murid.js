import * as faceapi from '@vladmandic/face-api';

const MODEL_URL = '/models/face-api';
const SCAN_DELAY_MS = 260;
const RECOGNITION_COOLDOWN_MS = 15000;
const SNAPSHOT_MAX_DIM = 420;

let modelsPromise = null;
let modelsLoaded = false;

async function ensureModelsLoaded() {
    if (modelsLoaded) {
        return true;
    }

    if (!modelsPromise) {
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

class VoiceGreeter {
    constructor() {
        this.enabled = true;
        this.audioCtx = null;
        this.selectedVoice = null;
        this.loadIndonesianVoice();

        if ('speechSynthesis' in window && typeof window.speechSynthesis.addEventListener === 'function') {
            window.speechSynthesis.addEventListener('voiceschanged', () => this.loadIndonesianVoice());
        }
    }

    loadIndonesianVoice() {
        if (!('speechSynthesis' in window)) return;
        const voices = window.speechSynthesis.getVoices() || [];
        this.selectedVoice =
            voices.find((v) => v.lang && v.lang.toLowerCase() === 'id-id') ||
            voices.find((v) => v.lang && v.lang.toLowerCase().startsWith('id')) ||
            voices.find((v) => v.name && v.name.toLowerCase().includes('indonesia')) ||
            null;
    }

    unlock() {
        try {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (AudioCtx && !this.audioCtx) {
                this.audioCtx = new AudioCtx();
            }
            if (this.audioCtx && this.audioCtx.state === 'suspended') {
                this.audioCtx.resume();
            }
        } catch (_) {
            // AudioContext opsional
        }
        this.loadIndonesianVoice();
    }

    playChime(variant = 'success') {
        if (!this.enabled) return;

        try {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (!AudioCtx) return;
            if (!this.audioCtx) {
                this.audioCtx = new AudioCtx();
            }
            if (this.audioCtx.state === 'suspended') {
                this.audioCtx.resume();
            }

            const now = this.audioCtx.currentTime;
            const freqs = variant === 'success' ? [523.25, 659.25] : [440, 440];

            freqs.forEach((freq, idx) => {
                const osc = this.audioCtx.createOscillator();
                const gain = this.audioCtx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(freq, now + idx * 0.11);

                gain.gain.setValueAtTime(0.001, now + idx * 0.11);
                gain.gain.exponentialRampToValueAtTime(0.14, now + idx * 0.11 + 0.02);
                gain.gain.exponentialRampToValueAtTime(0.001, now + idx * 0.11 + 0.24);

                osc.connect(gain);
                gain.connect(this.audioCtx.destination);
                osc.start(now + idx * 0.11);
                osc.stop(now + idx * 0.11 + 0.25);
            });
        } catch (_) {
            // Abaikan bila audio diblokir browser
        }
    }

    speak(text, chimeVariant = 'success') {
        if (!this.enabled) return;

        this.playChime(chimeVariant);

        if (!('speechSynthesis' in window)) return;
        this.loadIndonesianVoice();

        try {
            window.speechSynthesis.cancel();
            const utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = 'id-ID';
            if (this.selectedVoice) {
                utterance.voice = this.selectedVoice;
            }
            utterance.rate = 0.98;
            utterance.pitch = 1.02;
            utterance.volume = 1;
            window.speechSynthesis.speak(utterance);
        } catch (_) {
            // Abaikan error speechSynthesis
        }
    }
}

function drawKioskOverlay(canvas, videoWidth, videoHeight, results) {
    if (!canvas) return;
    canvas.width = videoWidth;
    canvas.height = videoHeight;

    const ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, videoWidth, videoHeight);

    results.forEach(({ box, label, color }) => {
        if (!box) return;
        // Video di-mirror CSS scaleX(-1), jadi koordinat X dibalik agar kotak presisi & teks tidak terbalik
        const mirroredX = videoWidth - box.x - box.width;

        ctx.strokeStyle = color;
        ctx.lineWidth = Math.max(2.5, Math.round(videoWidth / 220));
        ctx.strokeRect(mirroredX, box.y, box.width, box.height);

        if (label) {
            ctx.font = '600 13px "Plus Jakarta Sans", system-ui, sans-serif';
            const paddingX = 8;
            const textMetrics = ctx.measureText(label);
            const pillWidth = textMetrics.width + paddingX * 2;
            const pillHeight = 24;
            const pillY = Math.max(4, box.y - pillHeight - 4);

            ctx.fillStyle = color;
            ctx.beginPath();
            ctx.roundRect(mirroredX, pillY, pillWidth, pillHeight, 6);
            ctx.fill();

            ctx.fillStyle = '#FFFFFF';
            ctx.fillText(label, mirroredX + paddingX, pillY + 16);
        }
    });
}

function captureSnapshotBase64(videoEl) {
    const rawW = videoEl.videoWidth || 640;
    const rawH = videoEl.videoHeight || 480;
    const ratio = Math.min(1, SNAPSHOT_MAX_DIM / Math.max(rawW, rawH));
    const width = Math.round(rawW * ratio);
    const height = Math.round(rawH * ratio);

    const canvas = document.createElement('canvas');
    canvas.width = width;
    canvas.height = height;

    const ctx = canvas.getContext('2d');
    ctx.translate(width, 0);
    ctx.scale(-1, 1);
    ctx.drawImage(videoEl, 0, 0, width, height);

    return canvas.toDataURL('image/jpeg', 0.82);
}

function initAbsenMuridKiosk() {
    const appRoot = document.getElementById('absenMuridApp');
    if (!appRoot) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const descriptorsUrl = appRoot.dataset.descriptorsUrl;
    const storeUrl = appRoot.dataset.storeUrl;
    const schoolId = appRoot.dataset.schoolId;
    const timezone = appRoot.dataset.timezone || 'Asia/Makassar';
    const isTodayView = appRoot.dataset.isToday === '1';
    const canCreate = appRoot.dataset.canCreate === '1';

    // Elemen Kiosk Kamera
    const videoEl = document.getElementById('kioskCameraVideo');
    const overlayCanvas = document.getElementById('kioskOverlayCanvas');
    const stagePlaceholder = document.getElementById('kioskStagePlaceholder');
    const stageBadge = document.getElementById('kioskStageBadge');
    const stageBadgeText = document.getElementById('kioskStageBadgeText');
    const liveClockEl = document.getElementById('kioskLiveClock');
    const insecureAlert = document.getElementById('kioskCameraInsecureAlert');

    const btnStartKiosk = document.getElementById('btnStartKioskCamera');
    const btnStopKiosk = document.getElementById('btnStopKioskCamera');
    const btnRefreshDescriptors = document.getElementById('btnRefreshDescriptors');
    const btnToggleVoice = document.getElementById('btnToggleVoice');
    const btnTestVoice = document.getElementById('btnTestVoice');
    const voiceToggleLabel = document.getElementById('voiceToggleLabel');
    const iconVoiceOn = document.getElementById('iconVoiceOn');
    const iconVoiceOff = document.getElementById('iconVoiceOff');

    const tabModeMasuk = document.getElementById('tabKioskMasuk');
    const tabModePulang = document.getElementById('tabKioskPulang');
    const selectKioskKelas = document.getElementById('kioskFilterKelasId');
    const inputBatasMasuk = document.getElementById('kioskJamBatasMasuk');

    // Elemen Kartu Sambutan & Feed
    const welcomeCard = document.getElementById('kioskWelcomeCard');
    const welcomeHeadline = document.getElementById('kioskWelcomeHeadline');
    const welcomeName = document.getElementById('kioskWelcomeName');
    const welcomeMeta = document.getElementById('kioskWelcomeMeta');
    const welcomeTime = document.getElementById('kioskWelcomeTime');
    const welcomeStatusBadge = document.getElementById('kioskWelcomeStatusBadge');
    const welcomeScoreBadge = document.getElementById('kioskWelcomeScoreBadge');
    const welcomeAvatarWrap = document.getElementById('kioskWelcomeAvatarWrap');
    const welcomeAvatarImg = document.getElementById('kioskWelcomeAvatarImg');
    const welcomeInitials = document.getElementById('kioskWelcomeInitials');
    const recentFeedList = document.getElementById('kioskRecentFeed');
    const recentFeedEmpty = document.getElementById('kioskRecentFeedEmpty');

    // Elemen KPI
    const kpiHadirEl = document.getElementById('kpiHadirValue');
    const kpiTerlambatEl = document.getElementById('kpiTerlambatValue');
    const kpiBelumEl = document.getElementById('kpiBelumAbsenValue');

    const greeter = new VoiceGreeter();
    const detectorOptions = new faceapi.TinyFaceDetectorOptions({
        inputSize: 320,
        scoreThreshold: 0.5,
    });

    let currentMode = 'masuk';
    let mediaStream = null;
    let isRunning = false;
    let scanTimeout = null;
    let faceMatcher = null;
    let studentsById = new Map();
    const lastRecognizedAt = new Map();
    const inFlightMuridIds = new Set();

    // Jam digital live WITA
    function updateClock() {
        if (!liveClockEl) return;
        try {
            const formatted = new Intl.DateTimeFormat('id-ID', {
                timeZone: timezone,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false,
            }).format(new Date());
            liveClockEl.textContent = `${formatted} WITA`;
        } catch (_) {
            liveClockEl.textContent = new Date().toLocaleTimeString('id-ID');
        }
    }
    updateClock();
    setInterval(updateClock, 1000);

    function setStageBadge(text, variant = 'neutral') {
        if (!stageBadge || !stageBadgeText) return;
        stageBadge.classList.remove('is-success', 'is-warning', 'is-danger');
        if (variant === 'success') stageBadge.classList.add('is-success');
        if (variant === 'warning') stageBadge.classList.add('is-warning');
        if (variant === 'danger') stageBadge.classList.add('is-danger');
        stageBadgeText.textContent = text;
    }

    async function loadFaceDescriptors() {
        if (!descriptorsUrl) return 0;

        setStageBadge('Memuat Data Wajah...', 'warning');
        const url = new URL(descriptorsUrl, window.location.origin);
        if (schoolId) url.searchParams.set('school_id', schoolId);
        if (selectKioskKelas?.value) url.searchParams.set('kelas_id', selectKioskKelas.value);

        const res = await fetch(url.toString(), {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        if (!res.ok) {
            throw new Error('Gagal mengambil daftar descriptor wajah.');
        }

        const payload = await res.json();
        const students = Array.isArray(payload.students) ? payload.students : [];
        const threshold = Number(payload.threshold || 0.48);

        studentsById = new Map();
        const labeledDescriptors = [];

        students.forEach((student) => {
            studentsById.set(String(student.murid_id), student);

            const floatArrays = (student.descriptors || [])
                .map((d) => (Array.isArray(d.descriptor) && d.descriptor.length === 128 ? new Float32Array(d.descriptor) : null))
                .filter(Boolean);

            if (floatArrays.length > 0) {
                labeledDescriptors.push(new faceapi.LabeledFaceDescriptors(String(student.murid_id), floatArrays));
            }
        });

        faceMatcher = labeledDescriptors.length > 0
            ? new faceapi.FaceMatcher(labeledDescriptors, threshold)
            : null;

        return students.length;
    }

    function updateWelcomeCard(attendance, greetingText, scoreText, isAlready = false) {
        if (!welcomeCard) return;

        welcomeCard.classList.remove('is-recognized');
        void welcomeCard.offsetWidth;
        welcomeCard.classList.add('is-recognized');

        if (welcomeHeadline) {
            welcomeHeadline.textContent = greetingText;
        }
        if (welcomeName) {
            welcomeName.textContent = attendance.nama_lengkap || '-';
        }
        if (welcomeMeta) {
            welcomeMeta.textContent = `${attendance.kelas_nama || 'Tanpa Kelas'} • NISN: ${attendance.nisn || '-'}`;
        }
        if (welcomeTime) {
            const jam = currentMode === 'pulang'
                ? (attendance.jam_pulang || attendance.jam_masuk || '-')
                : (attendance.jam_masuk || '-');
            welcomeTime.textContent = isAlready ? `Tercatat sebelumnya pukul ${jam}` : `Tercatat pukul ${jam} WITA`;
        }
        if (welcomeStatusBadge) {
            const status = (attendance.status || 'hadir').toLowerCase();
            const labelMap = {
                hadir: currentMode === 'pulang' ? 'Pulang' : 'Hadir',
                terlambat: 'Terlambat',
            };
            welcomeStatusBadge.textContent = isAlready
                ? (currentMode === 'pulang' ? 'Sudah Pulang' : 'Sudah Absen')
                : (labelMap[status] || status.toUpperCase());
            welcomeStatusBadge.className = `badge ${status === 'terlambat' ? 'badge-amber' : 'badge-success'}`;
        }
        if (welcomeScoreBadge) {
            welcomeScoreBadge.textContent = `Kecocokan ${scoreText || attendance.match_score_percent || '-'}`;
        }

        if (attendance.foto_url) {
            if (welcomeAvatarImg) {
                welcomeAvatarImg.src = attendance.foto_url;
                welcomeAvatarImg.classList.remove('hidden');
            }
            if (welcomeInitials) welcomeInitials.classList.add('hidden');
            if (welcomeAvatarWrap) welcomeAvatarWrap.classList.add('has-image');
        } else {
            if (welcomeAvatarImg) {
                welcomeAvatarImg.src = '';
                welcomeAvatarImg.classList.add('hidden');
            }
            if (welcomeInitials) {
                welcomeInitials.textContent = (attendance.nama_lengkap || 'MD').substring(0, 2).toUpperCase();
                welcomeInitials.classList.remove('hidden');
            }
            if (welcomeAvatarWrap) welcomeAvatarWrap.classList.remove('has-image');
        }
    }

    function prependFeedItem(attendance) {
        if (!recentFeedList) return;
        if (recentFeedEmpty) recentFeedEmpty.classList.add('hidden');

        const existingEl = recentFeedList.querySelector(`[data-attendance-id="${attendance.id}"]`);
        if (existingEl) {
            existingEl.remove();
        }

        const item = document.createElement('div');
        item.className = 'kiosk-feed-item';
        item.dataset.attendanceId = String(attendance.id);

        const left = document.createElement('div');
        left.className = 'min-w-0';

        const nameEl = document.createElement('div');
        nameEl.className = 'font-semibold text-sm text-slate-900 truncate';
        nameEl.textContent = attendance.nama_lengkap || '-';

        const subEl = document.createElement('div');
        subEl.className = 'text-xs text-slate-500';
        const timeStr = attendance.jam_pulang
            ? `Masuk ${attendance.jam_masuk} • Pulang ${attendance.jam_pulang}`
            : `Masuk ${attendance.jam_masuk || '-'}`;
        subEl.textContent = `${attendance.kelas_nama || 'Tanpa Kelas'} • ${timeStr}`;

        left.appendChild(nameEl);
        left.appendChild(subEl);

        const badge = document.createElement('span');
        badge.className = `badge ${attendance.status === 'terlambat' ? 'badge-amber' : 'badge-success'} shrink-0`;
        badge.textContent = attendance.jam_pulang ? 'Pulang' : (attendance.status === 'terlambat' ? 'Terlambat' : 'Hadir');

        item.appendChild(left);
        item.appendChild(badge);
        recentFeedList.prepend(item);

        // Pertahankan maksimal 6 item terbaru di feed
        const items = recentFeedList.querySelectorAll('.kiosk-feed-item');
        if (items.length > 6) {
            items[items.length - 1].remove();
        }
    }

    function updateKpiDisplay(stats) {
        if (!stats || !isTodayView) return;
        if (kpiHadirEl) kpiHadirEl.textContent = Number(stats.hadir_hari_ini || 0).toLocaleString('id-ID');
        if (kpiTerlambatEl) kpiTerlambatEl.textContent = Number(stats.terlambat_hari_ini || 0).toLocaleString('id-ID');
        if (kpiBelumEl) kpiBelumEl.textContent = Number(stats.belum_absen || 0).toLocaleString('id-ID');
    }

    async function handleRecognizedStudent(student, matchScore, faceProfileId) {
        const key = String(student.murid_id);
        const nowTs = Date.now();
        const prevTs = lastRecognizedAt.get(key) || 0;

        if (nowTs - prevTs < RECOGNITION_COOLDOWN_MS || inFlightMuridIds.has(key)) {
            return;
        }

        const scorePercent = `${Math.round(matchScore * 100)}%`;

        // Jika secara lokal sudah tercatat untuk mode ini, beri feedback tanpa memanggil server berulang
        if (currentMode === 'masuk' && student.today_jam_masuk) {
            lastRecognizedAt.set(key, nowTs);
            const infoGreeting = `Selamat datang, ${student.nama_lengkap}`;
            updateWelcomeCard(
                {
                    nama_lengkap: student.nama_lengkap,
                    nisn: student.nisn,
                    kelas_nama: student.kelas_nama,
                    foto_url: student.foto_url,
                    jam_masuk: student.today_jam_masuk,
                    status: student.today_status || 'hadir',
                },
                infoGreeting,
                scorePercent,
                true
            );
            greeter.speak(`${student.nama_lengkap}, sudah tercatat hadir.`, 'info');
            setStageBadge(`${student.nama_lengkap} (Sudah Absen)`, 'success');
            return;
        }

        if (currentMode === 'pulang' && student.today_jam_pulang) {
            lastRecognizedAt.set(key, nowTs);
            updateWelcomeCard(
                {
                    nama_lengkap: student.nama_lengkap,
                    nisn: student.nisn,
                    kelas_nama: student.kelas_nama,
                    foto_url: student.foto_url,
                    jam_pulang: student.today_jam_pulang,
                    status: student.today_status || 'hadir',
                },
                `Sampai jumpa, ${student.nama_lengkap}`,
                scorePercent,
                true
            );
            greeter.speak(`${student.nama_lengkap}, sudah tercatat pulang.`, 'info');
            return;
        }

        if (!canCreate) {
            lastRecognizedAt.set(key, nowTs);
            setStageBadge('Tidak Ada Izin Mencatat Absen', 'danger');
            return;
        }

        inFlightMuridIds.add(key);
        lastRecognizedAt.set(key, nowTs);

        try {
            const fotoBase64 = videoEl ? captureSnapshotBase64(videoEl) : null;
            const res = await fetch(storeUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    school_id: schoolId,
                    murid_id: student.murid_id,
                    face_profile_id: faceProfileId || null,
                    mode: currentMode,
                    match_score: Number(matchScore.toFixed(4)),
                    jam_batas_masuk: inputBatasMasuk?.value || null,
                    foto_base64: fotoBase64,
                }),
            });

            const data = await res.json();

            if (!res.ok) {
                setStageBadge(data.message || 'Gagal mencatat absensi', 'warning');
                if (window.showToast && data.message) {
                    window.showToast(data.message);
                }
                return;
            }

            if (data.attendance) {
                if (data.attendance.jam_masuk) student.today_jam_masuk = data.attendance.jam_masuk;
                if (data.attendance.jam_pulang) student.today_jam_pulang = data.attendance.jam_pulang;
                if (data.attendance.status) student.today_status = data.attendance.status;

                const spokenText = currentMode === 'pulang'
                    ? `Sampai jumpa, ${student.nama_lengkap}`
                    : `Selamat datang, ${student.nama_lengkap}`;

                updateWelcomeCard(
                    data.attendance,
                    data.greeting || `${spokenText}!`,
                    scorePercent,
                    Boolean(data.already_recorded)
                );
                prependFeedItem(data.attendance);
                updateKpiDisplay(data.stats);
                greeter.speak(spokenText, data.already_recorded ? 'info' : 'success');
                setStageBadge(data.greeting || spokenText, 'success');
            }
        } catch (err) {
            console.error('Error recording attendance:', err);
            lastRecognizedAt.delete(key);
            setStageBadge('Koneksi ke server terganggu', 'danger');
        } finally {
            inFlightMuridIds.delete(key);
        }
    }

    async function runScanCycle() {
        if (!isRunning || !videoEl || videoEl.readyState < 2) {
            return;
        }

        try {
            const detections = await faceapi
                .detectAllFaces(videoEl, detectorOptions)
                .withFaceLandmarks(true)
                .withFaceDescriptors();

            const vw = videoEl.videoWidth || 640;
            const vh = videoEl.videoHeight || 480;

            if (detections.length === 0) {
                drawKioskOverlay(overlayCanvas, vw, vh, []);
                setStageBadge(
                    faceMatcher ? 'Kamera Aktif • Menunggu Wajah...' : 'Belum Ada Wajah Terdaftar',
                    faceMatcher ? 'neutral' : 'warning'
                );
            } else {
                const overlayBoxes = [];

                detections.forEach((det) => {
                    if (!faceMatcher) {
                        overlayBoxes.push({
                            box: det.detection.box,
                            label: 'Belum Ada Data Wajah',
                            color: '#D97706',
                        });
                        return;
                    }

                    const bestMatch = faceMatcher.findBestMatch(det.descriptor);
                    if (bestMatch.label !== 'unknown') {
                        const student = studentsById.get(bestMatch.label);
                        const matchScore = Math.max(0, Math.min(1, 1 - bestMatch.distance));
                        const pct = Math.round(matchScore * 100);

                        if (student) {
                            overlayBoxes.push({
                                box: det.detection.box,
                                label: `${student.nama_lengkap} (${pct}%)`,
                                color: '#16A34A',
                            });

                            const firstDescriptor = student.descriptors?.[0]?.face_profile_id || null;
                            handleRecognizedStudent(student, matchScore, firstDescriptor);
                            return;
                        }
                    }

                    overlayBoxes.push({
                        box: det.detection.box,
                        label: 'Wajah Tidak Dikenal',
                        color: '#D97706',
                    });
                    setStageBadge('Wajah Belum Terdaftar / Tidak Dikenal', 'warning');
                });

                drawKioskOverlay(overlayCanvas, vw, vh, overlayBoxes);
            }
        } catch (err) {
            console.error('Kiosk scan error:', err);
        }

        if (isRunning) {
            scanTimeout = setTimeout(runScanCycle, SCAN_DELAY_MS);
        }
    }

    async function startKioskCamera() {
        greeter.unlock();

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            if (insecureAlert) insecureAlert.classList.remove('hidden');
            setStageBadge('Kamera Memerlukan HTTPS / localhost', 'danger');
            return;
        }

        if (insecureAlert) insecureAlert.classList.add('hidden');
        if (btnStartKiosk) btnStartKiosk.disabled = true;
        setStageBadge('Memuat Model AI & Data Wajah...', 'warning');

        try {
            const [, studentCount] = await Promise.all([
                ensureModelsLoaded(),
                loadFaceDescriptors(),
            ]);

            mediaStream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'user',
                    width: { ideal: 640 },
                    height: { ideal: 480 },
                },
                audio: false,
            });

            if (videoEl) {
                videoEl.srcObject = mediaStream;
                videoEl.classList.remove('hidden');
                videoEl.classList.add('is-mirrored');
                await videoEl.play();
            }

            if (stagePlaceholder) stagePlaceholder.classList.add('hidden');
            if (btnStartKiosk) btnStartKiosk.classList.add('hidden');
            if (btnStopKiosk) btnStopKiosk.classList.remove('hidden');

            isRunning = true;
            setStageBadge(
                studentCount > 0 ? `Siap Memindai (${studentCount} Murid)` : 'Belum Ada Wajah Murid Terdaftar',
                studentCount > 0 ? 'success' : 'warning'
            );

            runScanCycle();
        } catch (err) {
            console.error('Start kiosk camera failed:', err);
            stopKioskCamera();
            if (insecureAlert) insecureAlert.classList.remove('hidden');
            setStageBadge('Gagal Membuka Kamera', 'danger');
        } finally {
            if (btnStartKiosk) btnStartKiosk.disabled = false;
        }
    }

    function stopKioskCamera() {
        isRunning = false;
        if (scanTimeout) {
            clearTimeout(scanTimeout);
            scanTimeout = null;
        }
        if (mediaStream) {
            mediaStream.getTracks().forEach((track) => track.stop());
            mediaStream = null;
        }
        if (videoEl) {
            videoEl.srcObject = null;
            videoEl.classList.add('hidden');
        }
        if (overlayCanvas) {
            overlayCanvas.getContext('2d').clearRect(0, 0, overlayCanvas.width || 1, overlayCanvas.height || 1);
        }
        if (stagePlaceholder) stagePlaceholder.classList.remove('hidden');
        if (btnStartKiosk) btnStartKiosk.classList.remove('hidden');
        if (btnStopKiosk) btnStopKiosk.classList.add('hidden');
        setStageBadge('Kamera Nonaktif', 'neutral');
    }

    function switchMode(mode) {
        currentMode = mode;
        if (tabModeMasuk && tabModePulang) {
            const isMasuk = mode === 'masuk';
            tabModeMasuk.classList.toggle('active', isMasuk);
            tabModeMasuk.setAttribute('aria-selected', isMasuk ? 'true' : 'false');
            tabModePulang.classList.toggle('active', !isMasuk);
            tabModePulang.setAttribute('aria-selected', !isMasuk ? 'true' : 'false');
        }
    }

    // Bind Controls
    if (btnStartKiosk) btnStartKiosk.addEventListener('click', startKioskCamera);
    if (btnStopKiosk) btnStopKiosk.addEventListener('click', stopKioskCamera);

    if (btnRefreshDescriptors) {
        btnRefreshDescriptors.addEventListener('click', async () => {
            try {
                const count = await loadFaceDescriptors();
                setStageBadge(
                    count > 0 ? `Data Diperbarui (${count} Murid)` : 'Belum Ada Wajah Murid Terdaftar',
                    count > 0 ? 'success' : 'warning'
                );
                if (window.showToast) {
                    window.showToast(`Memuat ${count} data wajah murid aktif.`);
                }
            } catch (err) {
                console.error(err);
            }
        });
    }

    if (selectKioskKelas) {
        selectKioskKelas.addEventListener('change', () => {
            loadFaceDescriptors().catch(console.error);
        });
    }

    if (tabModeMasuk) tabModeMasuk.addEventListener('click', () => switchMode('masuk'));
    if (tabModePulang) tabModePulang.addEventListener('click', () => switchMode('pulang'));

    if (btnToggleVoice) {
        btnToggleVoice.addEventListener('click', () => {
            greeter.unlock();
            greeter.enabled = !greeter.enabled;
            if (voiceToggleLabel) {
                voiceToggleLabel.textContent = greeter.enabled ? 'Suara Aktif' : 'Suara Mati';
            }
            if (iconVoiceOn) iconVoiceOn.classList.toggle('hidden', !greeter.enabled);
            if (iconVoiceOff) iconVoiceOff.classList.toggle('hidden', greeter.enabled);
            btnToggleVoice.classList.toggle('active', greeter.enabled);

            if (greeter.enabled) {
                greeter.speak('Suara sambutan absensi diaktifkan.');
            } else if ('speechSynthesis' in window) {
                window.speechSynthesis.cancel();
            }
        });
    }

    if (btnTestVoice) {
        btnTestVoice.addEventListener('click', () => {
            greeter.unlock();
            if (!greeter.enabled) {
                greeter.enabled = true;
                if (voiceToggleLabel) voiceToggleLabel.textContent = 'Suara Aktif';
                if (iconVoiceOn) iconVoiceOn.classList.remove('hidden');
                if (iconVoiceOff) iconVoiceOff.classList.add('hidden');
            }
            greeter.speak('Selamat datang, Muhammad Raditya.');
        });
    }

    // Modal Hapus Log Absensi
    if (window.InforaModals) {
        window.InforaModals.setup('modalDeleteAbsensi', { cancelBtnId: 'btnCancelDeleteAbsensi' });
        window.InforaModals.bindDeleteButtons('.btn-open-delete-absensi', 'modalDeleteAbsensi', function (modal, btn) {
            const titleEl = document.getElementById('modalDeleteAbsensiTitle');
            const msgEl = document.getElementById('deleteAbsensiMessage');
            const badgeEl = document.getElementById('deleteAbsensiBadge');
            const itemEl = document.getElementById('deleteAbsensiTitle');

            if (titleEl) titleEl.textContent = 'Hapus Catatan Absensi?';
            if (msgEl) msgEl.textContent = 'Apakah Anda yakin ingin menghapus log kehadiran murid berikut?';
            if (badgeEl) badgeEl.textContent = btn.dataset.tanggal || 'Absensi';
            if (itemEl) itemEl.textContent = btn.dataset.nama || 'Murid';
        });
    }

    window.addEventListener('beforeunload', stopKioskCamera);

    // Preload model ringan di background saat halaman selesai dimuat
    const warmup = () => {
        ensureModelsLoaded().catch(() => {});
    };
    if ('requestIdleCallback' in window) {
        window.requestIdleCallback(warmup, { timeout: 2500 });
    } else {
        setTimeout(warmup, 800);
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAbsenMuridKiosk);
} else {
    initAbsenMuridKiosk();
}

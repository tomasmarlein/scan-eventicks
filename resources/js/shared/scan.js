import { BrowserMultiFormatReader } from "@zxing/browser";

const video    = document.getElementById("preview");
const torchBtn = document.getElementById("torchBtn");
const soundBtn = document.getElementById("soundBtn");
const statusEl = document.getElementById("status");
const retryBtn = document.getElementById("retryBtn");
const qrField  = document.getElementById("qrField");
const form     = document.getElementById("resultForm");
const page     = document.getElementById("page-scan");

const feedback = document.getElementById("scanFeedback");
const feedbackTitle = document.getElementById("scanFeedbackTitle");
const feedbackMessage = document.getElementById("scanFeedbackMessage");
const feedbackName = document.getElementById("scanFeedbackName");
const feedbackTicket = document.getElementById("scanFeedbackTicket");
const feedbackOrder = document.getElementById("scanFeedbackOrder");
const feedbackSymbol = document.getElementById("scanFeedbackSymbol");

const feedbackMs = Number.parseInt(page?.dataset?.feedbackMs || "1400", 10);
const recentScanTtlMs = Math.max(feedbackMs + 1200, 3500);
const soundFiles = { success: "/sounds/success.mp3", error: "/sounds/error.mp3" };
const AudioContextClass = window.AudioContext || window.webkitAudioContext;

let reader = null;
let controls = null;
let currentStream = null;
let currentVideoTrack = null;
let torchOn = false;
let submitting = false;
let feedbackTimer = null;
let resumeTimer = null;
let restartTimer = null;
let lifecycleId = 0;
let isStarting = false;
let restartWhenReady = false;
let audioContext = null;
let soundLoadPromise = null;
let soundMuted = false;
const recentScans = new Map();

if (!video || !form || !qrField) {
    throw new Error("Scanner markup ontbreekt op deze pagina.");
}

function getAudioContext() {
    if (!AudioContextClass) return null;

    if (!audioContext) {
        try {
            audioContext = new AudioContextClass();
            audioContext.addEventListener("statechange", updateSoundButton);
        } catch {
            return null;
        }
    }

    return audioContext;
}

function updateSoundButton() {
    if (!soundBtn) return;

    const active = !soundMuted && audioContext?.state === "running";
    const label = active ? "Geluid uitschakelen" : "Geluid inschakelen";
    soundBtn.classList.toggle("is-active", active);
    soundBtn.setAttribute("aria-pressed", String(active));
    soundBtn.setAttribute("aria-label", label);
    soundBtn.title = label;
}

function loadSounds() {
    const context = getAudioContext();
    if (!context) return Promise.resolve(null);

    if (!soundLoadPromise) {
        soundLoadPromise = Promise.all(Object.entries(soundFiles).map(async ([status, url]) => {
            const response = await fetch(url);
            if (!response.ok) throw new Error(`Geluid ${status} kon niet geladen worden.`);
            return [status, await context.decodeAudioData(await response.arrayBuffer())];
        })).then(entries => Object.fromEntries(entries)).catch(error => {
            soundLoadPromise = null;
            throw error;
        });
    }

    return soundLoadPromise;
}

async function enableSound() {
    if (soundMuted) return;

    const context = getAudioContext();
    if (!context) return;

    try {
        await context.resume();
        await loadSounds();
    } catch (error) {
        console.warn("Scangeluid kon niet worden ingeschakeld:", error);
    }

    updateSoundButton();
}

async function playSound(status) {
    if (soundMuted) return;

    const key = status === "success" ? "success" : "error";
    const context = getAudioContext();

    if (context?.state === "running") {
        try {
            const buffers = await loadSounds();
            const source = context.createBufferSource();
            const volume = context.createGain();
            source.buffer = buffers[key];
            volume.gain.value = 0.8;
            source.connect(volume);
            volume.connect(context.destination);
            source.start();
            return;
        } catch (error) {
            console.warn("Scangeluid kon niet worden afgespeeld:", error);
        }
    }

    try {
        const audio = new Audio(soundFiles[key]);
        audio.volume = 0.8;
        await audio.play();
    } catch {
        updateSoundButton();
    }
}

function setStatus(message, state) {
    if (statusEl) {
        statusEl.textContent = message;
    }

    if (state && page) {
        page.dataset.state = state;
    }

    retryBtn?.classList.toggle("d-none", state !== "error" || page?.dataset.cameraActive === "true");
}

function pruneRecentScans(now = Date.now()) {
    for (const [value, expiresAt] of recentScans.entries()) {
        if (expiresAt <= now) {
            recentScans.delete(value);
        }
    }
}

function isRecentDuplicate(value) {
    const now = Date.now();
    pruneRecentScans(now);

    if (recentScans.has(value)) {
        return true;
    }

    recentScans.set(value, now + recentScanTtlMs);
    return false;
}

function clearState() {
    torchOn = false;
    currentVideoTrack = null;
    currentStream = null;

    if (torchBtn) {
        torchBtn.disabled = true;
        torchBtn.classList.remove("is-active");
        torchBtn.setAttribute("aria-pressed", "false");
        torchBtn.setAttribute("aria-label", "Zaklamp inschakelen");
    }
}

function setTorchButtonState(track) {
    if (!torchBtn) {
        return;
    }

    try {
        const caps = track?.getCapabilities?.();
        torchBtn.disabled = !caps?.torch;
        torchBtn.classList.toggle("is-active", torchOn);
        torchBtn.setAttribute("aria-pressed", String(torchOn));
        torchBtn.setAttribute("aria-label", torchOn ? "Zaklamp uitschakelen" : "Zaklamp inschakelen");
    } catch {
        torchBtn.disabled = true;
    }
}

async function applyTorch(on) {
    if (!currentVideoTrack) {
        return;
    }

    try {
        await currentVideoTrack.applyConstraints({ advanced: [{ torch: Boolean(on) }] });
        torchOn = Boolean(on);
        setTorchButtonState(currentVideoTrack);
    } catch (error) {
        console.warn("Torch niet beschikbaar of geweigerd:", error);
        if (torchBtn) {
            torchBtn.disabled = true;
        }
    }
}

function stopDecoderAndStream() {
    try {
        controls?.stop?.();
    } catch (error) {
        console.debug("ZXing controls stop issue", error);
    }

    controls = null;

    try {
        currentStream?.getTracks?.().forEach(track => track.stop());
    } catch (error) {
        console.debug("Stream stop issue", error);
    }

    try {
        video.pause?.();
        video.srcObject = null;
    } catch (error) {
        console.debug("Video detach issue", error);
    }

    clearState();
}

function stop() {
    lifecycleId += 1;
    restartWhenReady = false;
    window.clearTimeout(restartTimer);
    stopDecoderAndStream();
    if (page) page.dataset.cameraActive = "false";
    setStatus("Camera hervat automatisch wanneer je terugkeert.", "starting");
}

function scheduleRestart() {
    window.clearTimeout(restartTimer);
    if (!document.hidden) {
        restartTimer = window.setTimeout(() => void start(), 3000);
    }
}

function finishInterruptedStart() {
    isStarting = false;
    if (restartWhenReady && !document.hidden) {
        restartWhenReady = false;
        void start();
    }
}

async function start() {
    if (document.hidden) return;
    if (isStarting) {
        restartWhenReady = true;
        return;
    }

    const attempt = ++lifecycleId;
    isStarting = true;
    window.clearTimeout(restartTimer);
    stopDecoderAndStream();
    if (page) page.dataset.cameraActive = "false";

    if (!navigator.mediaDevices?.getUserMedia) {
        setStatus("Deze browser ondersteunt geen cameratoegang.", "error");
        return;
    }

    video.setAttribute("playsinline", "");
    video.muted = true;
    video.autoplay = true;

    if (!reader) {
        reader = new BrowserMultiFormatReader();
    }

    setStatus("Camera starten…", "starting");

    try {
        const nextControls = await reader.decodeFromConstraints({
            audio: false,
            video: { facingMode: { ideal: "environment" } },
        }, video, (result, error) => {
            if (result) {
                void handleScanResult(result.getText());
            }

            if (error && error?.name !== "NotFoundException") {
                console.debug(error);
            }
        });

        if (attempt !== lifecycleId || document.hidden) {
            nextControls.stop();
            finishInterruptedStart();
            return;
        }

        controls = nextControls;

        currentStream = video.srcObject;
        currentVideoTrack = currentStream?.getVideoTracks?.()[0] || null;

        await video.play().catch(() => {});
        if (attempt !== lifecycleId || document.hidden) {
            finishInterruptedStart();
            return;
        }
        isStarting = false;

        torchOn = false;
        setTorchButtonState(currentVideoTrack);

        if (page) page.dataset.cameraActive = "true";
        setStatus("Klaar om te scannen", "active");
        currentVideoTrack?.addEventListener("ended", () => {
            if (attempt === lifecycleId) {
                if (page) page.dataset.cameraActive = "false";
                setStatus("Camera opnieuw verbinden…", "starting");
                scheduleRestart();
            }
        }, { once: true });
    } catch (error) {
        if (attempt !== lifecycleId) {
            finishInterruptedStart();
            return;
        }
        isStarting = false;
        console.error("Camera start error:", error?.name, error?.message);

        setStatus(
            error?.name === "NotReadableError"
                ? "Camera is bezet door een andere app of tab. Sluit die en probeer opnieuw."
                : error?.name === "NotAllowedError"
                    ? "Toegang geweigerd. Controleer de camera-toestemming in de browser."
                    : "Kon camera niet starten. Controleer de cameratoegang en probeer opnieuw.",
            "error"
        );
        if (!["NotAllowedError", "SecurityError", "NotFoundError"].includes(error?.name)) {
            scheduleRestart();
        }
    }
}

async function handleScanResult(rawValue) {
    const value = String(rawValue || "").trim();

    if (!value || submitting || isRecentDuplicate(value)) {
        return;
    }

    submitting = true;
    qrField.value = value;
    setStatus("Ticket controleren…", "processing");

    try {
        const response = await fetch(form.action, {
            method: "POST",
            body: new FormData(form),
            credentials: "same-origin",
            headers: {
                "Accept": "application/json",
                "X-Requested-With": "XMLHttpRequest",
            },
        });

        if (!response.ok) {
            throw new Error(`Scan request failed with status ${response.status}`);
        }

        const payload = await response.json();
        showFeedback(payload.scan || {
            status: payload.status || "error",
            message: payload.message || "Scan verwerkt.",
        });
    } catch (error) {
        console.error("Scan submit error:", error);
        showFeedback({
            status: "error",
            message: "Scan kon niet verzonden worden. Controleer de verbinding en probeer opnieuw.",
            orderline: {
                name: null,
                unique_qr_id: value,
                ticket: { name: null, price: null },
            },
            order_ref: null,
        });
    } finally {
        window.clearTimeout(resumeTimer);
        resumeTimer = window.setTimeout(() => {
            submitting = false;
            if (page?.dataset.cameraActive === "true") {
                setStatus("Klaar om te scannen", "active");
            }
        }, feedbackMs);
    }
}

function showFeedback(scan) {
    const status = scan?.status || "error";
    const title = status === "success"
        ? "Geldig ticket"
        : status === "warning"
            ? "Let op"
            : "Ongeldig ticket";

    if (feedback) {
        feedback.dataset.status = status;
        feedback.classList.add("is-visible");
    }

    if (feedbackTitle) {
        feedbackTitle.textContent = title;
    }

    if (feedbackSymbol) {
        feedbackSymbol.textContent = status === "success" ? "✓" : "!";
    }

    setStatus(title, status);

    if (feedbackMessage) {
        feedbackMessage.textContent = scan?.message || "Scan verwerkt.";
    }

    if (feedbackName) {
        feedbackName.textContent = scan?.orderline?.name ? `Naam: ${scan.orderline.name}` : "";
    }

    if (feedbackTicket) {
        feedbackTicket.textContent = scan?.orderline?.ticket?.name ? `Ticket: ${scan.orderline.ticket.name}` : "";
    }

    if (feedbackOrder) {
        feedbackOrder.textContent = scan?.order_ref ? `Order: ${scan.order_ref}` : "";
    }

    playFeedback(status);

    window.clearTimeout(feedbackTimer);
    feedbackTimer = window.setTimeout(() => {
        feedback?.classList.remove("is-visible");
    }, feedbackMs);
}

function playFeedback(status) {
    const success = status === "success";
    const pattern = success ? [40, 30, 40] : [80, 50, 80];

    try {
        if ("vibrate" in navigator) {
            navigator.vibrate(pattern);
        }
    } catch {
        // Ignore unsupported haptics.
    }

    void playSound(status);
}

retryBtn?.addEventListener("click", () => void start());
torchBtn?.addEventListener("click", () => void applyTorch(!torchOn));
soundBtn?.addEventListener("click", () => {
    if (audioContext?.state === "running" && !soundMuted) {
        soundMuted = true;
        updateSoundButton();
        return;
    }

    soundMuted = false;
    void enableSound();
});
document.addEventListener("pointerdown", event => {
    if (!soundBtn?.contains(event.target)) void enableSound();
}, { once: true });
document.addEventListener("keydown", event => {
    if (!soundBtn?.contains(event.target)) void enableSound();
}, { once: true });

void loadSounds().then(() => {
    void audioContext?.resume().then(updateSoundButton).catch(() => {});
}).catch(error => console.warn("Scangeluiden konden niet vooraf geladen worden:", error));

window.addEventListener("pagehide", stop);
window.addEventListener("pageshow", event => {
    if (event.persisted) void start();
});

document.addEventListener("visibilitychange", () => {
    if (document.hidden) {
        stop();
        return;
    }

    void start();
});

navigator.mediaDevices?.addEventListener?.("devicechange", () => {
    if (page?.dataset.cameraActive !== "true" && !isStarting) void start();
});

void start();

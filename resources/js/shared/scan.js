import { BrowserMultiFormatReader } from "@zxing/browser";

const video    = document.getElementById("preview");
const select   = document.getElementById("cameraSelect");
const startBtn = document.getElementById("startBtn");
const stopBtn  = document.getElementById("stopBtn");
const torchBtn = document.getElementById("torchBtn");
const statusEl = document.getElementById("status");
const qrField  = document.getElementById("qrField");
const form     = document.getElementById("resultForm");
const page     = document.getElementById("page-scan");

const feedback = document.getElementById("scanFeedback");
const feedbackTitle = document.getElementById("scanFeedbackTitle");
const feedbackMessage = document.getElementById("scanFeedbackMessage");
const feedbackName = document.getElementById("scanFeedbackName");
const feedbackTicket = document.getElementById("scanFeedbackTicket");
const feedbackOrder = document.getElementById("scanFeedbackOrder");

const feedbackMs = Number.parseInt(page?.dataset?.feedbackMs || "1400", 10);
const recentScanTtlMs = Math.max(feedbackMs + 1200, 3500);

let reader = null;
let controls = null;
let currentStream = null;
let currentVideoTrack = null;
let torchOn = false;
let submitting = false;
let feedbackTimer = null;
let resumeTimer = null;
const recentScans = new Map();

if (!video || !form || !qrField) {
    throw new Error("Scanner markup ontbreekt op deze pagina.");
}

function setStatus(message) {
    if (statusEl) {
        statusEl.textContent = message;
    }
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

async function listCameras() {
    if (!select || !navigator.mediaDevices?.enumerateDevices) {
        return;
    }

    const devices = await navigator.mediaDevices.enumerateDevices();
    const videos = devices.filter(device => device.kind === "videoinput");
    const selectedDevice = select.value;

    select.innerHTML = "";

    videos.forEach((device, index) => {
        const option = document.createElement("option");
        option.value = device.deviceId || "";
        option.textContent = device.label || `Camera ${index + 1}`;
        select.appendChild(option);
    });

    if ([...select.options].some(option => option.value === selectedDevice)) {
        select.value = selectedDevice;
        return;
    }

    const preferred = [...select.options].find(option => /back|rear|environment/i.test(option.textContent));

    if (preferred) {
        select.value = preferred.value;
    }
}

function stopDecoderAndStream() {
    window.clearTimeout(resumeTimer);

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
    stopDecoderAndStream();

    if (startBtn) {
        startBtn.disabled = false;
    }

    if (stopBtn) {
        stopBtn.disabled = true;
    }

    setStatus("Status: gestopt.");
}

async function start() {
    stopDecoderAndStream();

    if (!navigator.mediaDevices?.getUserMedia) {
        setStatus("Camera wordt niet ondersteund door deze browser.");
        return;
    }

    video.setAttribute("playsinline", "");
    video.muted = true;
    video.autoplay = true;

    if (!reader) {
        reader = new BrowserMultiFormatReader();
    }

    const deviceId = select?.value || undefined;
    setStatus("Status: camera starten...");

    try {
        controls = await reader.decodeFromVideoDevice(deviceId, video, (result, error) => {
            if (result) {
                void handleScanResult(result.getText());
            }

            if (error && error?.name !== "NotFoundException") {
                console.debug(error);
            }
        });

        currentStream = video.srcObject;
        currentVideoTrack = currentStream?.getVideoTracks?.()[0] || null;

        await video.play().catch(() => {});
        await listCameras();

        torchOn = false;
        setTorchButtonState(currentVideoTrack);

        if (startBtn) {
            startBtn.disabled = true;
        }

        if (stopBtn) {
            stopBtn.disabled = false;
        }

        setStatus("Status: scannen… richt de QR naar de camera.");
    } catch (error) {
        console.error("Camera start error:", error?.name, error?.message);

        setStatus(
            error?.name === "NotReadableError"
                ? "Camera is bezet door een andere app of tab. Sluit die en probeer opnieuw."
                : error?.name === "NotAllowedError"
                    ? "Toegang geweigerd. Controleer de camera-toestemming in de browser."
                    : "Kon camera niet starten. Gebruik HTTPS en geef camera-toestemming."
        );

        if (startBtn) {
            startBtn.disabled = false;
        }

        if (stopBtn) {
            stopBtn.disabled = true;
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
    setStatus("Status: ticket verwerken...");

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
            setStatus("Status: scannen… richt de QR naar de camera.");
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

    if (feedbackMessage) {
        feedbackMessage.textContent = scan?.message || "Scan verwerkt.";
    }

    if (feedbackName) {
        feedbackName.textContent = `Naam: ${scan?.orderline?.name || "-"}`;
    }

    if (feedbackTicket) {
        feedbackTicket.textContent = `Ticket: ${scan?.orderline?.ticket?.name || "-"}`;
    }

    if (feedbackOrder) {
        feedbackOrder.textContent = `Order: ${scan?.order_ref || scan?.orderline?.unique_qr_id || "-"}`;
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
    const soundFile = success ? "/sounds/success.mp3" : "/sounds/error.mp3";

    try {
        if ("vibrate" in navigator) {
            navigator.vibrate(pattern);
        }
    } catch {
        // Ignore unsupported haptics.
    }

    try {
        const audio = new Audio(soundFile);
        audio.volume = 0.8;
        audio.play().catch(() => {});
    } catch {
        // Ignore blocked autoplay/audio errors.
    }
}

startBtn?.addEventListener("click", () => void start());
stopBtn?.addEventListener("click", stop);
torchBtn?.addEventListener("click", () => void applyTorch(!torchOn));
select?.addEventListener("change", () => void start());

window.addEventListener("pagehide", stop);

document.addEventListener("visibilitychange", () => {
    if (document.hidden) {
        stop();
        return;
    }

    resumeTimer = window.setTimeout(() => void start(), 150);
});

(async () => {
    try {
        await listCameras();
        await start();
    } catch (error) {
        console.error(error);
        setStatus("Kon camera niet starten. Geef camera-toestemming en gebruik HTTPS.");

        if (startBtn) {
            startBtn.disabled = false;
        }

        if (stopBtn) {
            stopBtn.disabled = true;
        }
    }
})();

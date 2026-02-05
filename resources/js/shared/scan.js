import { BrowserMultiFormatReader } from "@zxing/browser";

const video    = document.getElementById("preview");
const select   = document.getElementById("cameraSelect");
const startBtn = document.getElementById("startBtn");
const stopBtn  = document.getElementById("stopBtn");
const torchBtn = document.getElementById("torchBtn");
const statusEl = document.getElementById("status");
const qrField  = document.getElementById("qrField");
const form     = document.getElementById("resultForm");

let reader = null;
let controls = null;
let lastValue = null;
let submitting = false;
let currentStream = null;
let currentVideoTrack = null;
let torchOn = false;

// ---------- Helpers
function setStatus(msg) {
    if (statusEl) statusEl.textContent = msg;
}

function clearState() {
    lastValue = null;
    torchOn = false;
    currentVideoTrack = null;
    currentStream = null;
    if (torchBtn) torchBtn.disabled = true;
}

function setTorchButtonState(track) {
    try {
        const caps = track?.getCapabilities?.();
        const hasTorch = !!caps?.torch;
        torchBtn.disabled = !hasTorch;
        // eventueel andere UI state hier
    } catch {
        torchBtn.disabled = true;
    }
}

async function applyTorch(on) {
    if (!currentVideoTrack) return;
    try {
        await currentVideoTrack.applyConstraints({ advanced: [{ torch: !!on }] });
        torchOn = !!on;
        setTorchButtonState(currentVideoTrack);
    } catch (e) {
        console.warn("Torch niet beschikbaar of geweigerd:", e);
        torchBtn.disabled = true;
    }
}

async function listCameras() {
    const devices = await navigator.mediaDevices.enumerateDevices();
    const videos  = devices.filter(d => d.kind === 'videoinput');

    select.innerHTML = "";
    videos.forEach((d, i) => {
        const opt = document.createElement("option");
        opt.value = d.deviceId || "";                 // mogelijk lege deviceId vooraf
        opt.textContent = d.label || `Camera ${i+1}`; // labels komen na toestemming
        select.appendChild(opt);
    });

    const prefer = [...select.options].find(o => /back|rear|environment/i.test(o.textContent));
    if (prefer) select.value = prefer.value;
}

function stopDecoderAndStream() {
    try {
        if (controls) {
            controls.stop();
            controls = null;
        }
    } catch (e) {
        console.debug("ZXing controls stop issue", e);
    }
    try {
        if (currentStream) {
            currentStream.getTracks().forEach(t => t.stop());
        }
    } catch (e) {
        console.debug("Stream stop issue", e);
    }
    try {
        if (video) {
            video.pause?.();
            video.srcObject = null;
            // playsinline/autoplay blijven staan
        }
    } catch (e) {
        console.debug("Video detach issue", e);
    }
    clearState();
}

function stop() {
    stopDecoderAndStream();
    if (startBtn) startBtn.disabled = false;
    if (stopBtn)  stopBtn.disabled  = true;
    setStatus("Status: gestopt.");
}

// Netjes opruimen voordat we de pagina verlaten of navigeren
function navigateAwayCleanup() {
    stop();
    reader = null; // force volledige re-init bij terugkeer
}

// ---------- Start flow
async function start() {
    // reset submit-state bij een nieuwe scan-sessie
    submitting = false;
    lastValue  = null;

    // stop eerst alles (idempotent)
    stop();

    video.setAttribute('playsinline','');
    video.muted = true;
    video.autoplay = true;

    if (!reader) reader = new BrowserMultiFormatReader();

    // wanneer select nog geen geldige deviceId heeft, laat ZXing zelf kiezen
    const deviceId = select?.value || undefined;
    setStatus("Status: camera starten...");

    try {
        controls = await reader.decodeFromVideoDevice(deviceId, video, (result, err) => {
            if (result) {
                const text = result.getText();
                if (text && text !== lastValue && !submitting) {
                    lastValue = text;
                    setStatus(`QR gevonden: ${text}`);
                    submitting = true;
                    qrField.value = text;

                    // vóór navigeren: alles vrijgeven
                    navigateAwayCleanup();
                    form.submit();
                }
            }
            if (err && err?.name !== "NotFoundException") {
                console.debug(err);
            }
        });

        // Wacht tot stream hangt, dan expliciet starten (Android)
        currentStream     = video.srcObject;
        currentVideoTrack = currentStream?.getVideoTracks?.()[0] || null;
        await video.play().catch(() => {}); // voorkom unhandled promise

        // nu labels opnieuw vullen (na permissie zijn ze zichtbaar)
        await listCameras();

        torchOn = false;
        setTorchButtonState(currentVideoTrack);

        startBtn.disabled = true;
        stopBtn.disabled  = false;
        setStatus("Status: scannen… richt de QR naar de camera.");
    } catch (e) {
        console.error('start() error:', e?.name, e?.message);
        setStatus(
            e?.name === 'NotReadableError'
            ? 'Camera is bezet door een andere app/tab. Sluit die en probeer opnieuw.'
            : e?.name === 'NotAllowedError'
              ? 'Toegang geweigerd. Controleer camera-toestemming in de browser.'
              : 'Kon camera’s niet starten. Toestemming gegeven en via HTTPS?'
        );
        startBtn.disabled = false;
        stopBtn.disabled  = true;
    }
}

// ---------- Events & lifecycle
startBtn?.addEventListener("click", start);
stopBtn?.addEventListener("click", stop);
torchBtn?.addEventListener("click", async () => {
    if (!currentVideoTrack || torchBtn.disabled) return;
    await applyTorch(!torchOn);
});

// Auto-init bij eerste load
(async () => {
    try {
        await listCameras();
        await start();
    } catch (e) {
        console.error(e);
        setStatus("Kon camera’s niet starten. Toestemming gegeven en via HTTPS?");
        if (startBtn) startBtn.disabled = false;
        if (stopBtn)  stopBtn.disabled  = true;
    }
})();

// Volledige cleanup wanneer je weggaat (ook SPA → andere route, of form submit)
window.addEventListener('pagehide', navigateAwayCleanup);

// Terugkeer (ook via bfcache): forceer re-init
window.addEventListener('pageshow', async (e) => {
    // Android: even laten “landen”
    setTimeout(async () => {
        if (!submitting) {
            await listCameras();
            await start();
        }
    }, 50);
});

document.addEventListener('visibilitychange', async () => {
    if (document.hidden) {
        stop();
    } else {
        if (!submitting) {
            await listCameras();
            await start();
        }
    }
});

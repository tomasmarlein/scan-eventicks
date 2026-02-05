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

async function listCameras() {
    // Labels van camera's zijn pas zichtbaar NA toestemming; we vragen alvast permissie.
    try {
        await navigator.mediaDevices.getUserMedia({ video: true });
    } catch (e) {
        console.warn("Camera-toestemming niet vooraf verleend:", e);
    }

    const devices = await BrowserMultiFormatReader.listVideoInputDevices();
    select.innerHTML = "";
    devices.forEach((d, i) => {
        const opt = document.createElement("option");
        opt.value = d.deviceId;
        opt.textContent = d.label || `Camera ${i + 1}`;
        select.appendChild(opt);
    });

    // Kies automatisch achtercamera indien herkenbaar
    const prefer = [...select.options].find(o => /back|rear|environment/i.test(o.textContent));
    if (prefer) select.value = prefer.value;
}

function setTorchButtonState(track) {
    try {
        const caps = track?.getCapabilities?.();
        const hasTorch = !!caps?.torch;
        torchBtn.disabled = !hasTorch;
        torchBtn.textContent = hasTorch && torchOn ? "🔦" : "🔦";
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

async function start() {
    if (controls) return;

    reader = new BrowserMultiFormatReader();
    const deviceId = select.value || undefined;
    statusEl.textContent = "Status: camera starten...";

    controls = await reader.decodeFromVideoDevice(deviceId, video, (result, err) => {
        if (result) {
            const text = result.getText();
            if (text && text !== lastValue && !submitting) {
                lastValue = text;
                statusEl.textContent = `QR gevonden: ${text}`;
                submitting = true;
                qrField.value = text;
                form.submit();
            }
        }
        if (err && err?.name !== "NotFoundException") {
            console.debug(err);
        }
    });

    // Bewaar track voor torch
    currentStream     = video.srcObject;
    currentVideoTrack = currentStream?.getVideoTracks?.()[0] || null;
    torchOn = false;
    setTorchButtonState(currentVideoTrack);

    startBtn.disabled = true;
    stopBtn.disabled  = false;
    statusEl.textContent = "Status: scannen… richt de QR naar de camera.";
}

function stop() {
    if (controls) {
        controls.stop();
        controls = null;
    }
    if (currentStream) {
        currentStream.getTracks().forEach(t => t.stop());
        currentStream = null;
        currentVideoTrack = null;
    }
    torchOn = false;
    torchBtn.disabled = true;

    startBtn.disabled = false;
    stopBtn.disabled = true;
    statusEl.textContent = "Status: gestopt.";
}

startBtn?.addEventListener("click", start);
stopBtn?.addEventListener("click", stop);
torchBtn?.addEventListener("click", async () => {
    if (!currentVideoTrack || torchBtn.disabled) return;
    await applyTorch(!torchOn);
});

// ✅ Auto-start bij laden van de pagina
(async () => {
    try {
        await listCameras();     // 1) devices ophalen + select vullen
        await start();           // 2) direct starten met gekozen camera
    } catch (e) {
        console.error(e);
        statusEl.textContent = "Kon camera’s niet starten. Toestemming gegeven en via HTTPS?";
        // Optioneel: enable de start-knop zodat de user het alsnog kan proberen
        startBtn.disabled = false;
        stopBtn.disabled = false;
    }
})();

document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        stop();
    } else {
        // alleen auto-herstarten als de gebruiker niet net aan het submitten is
        if (!submitting) start();
    }
});

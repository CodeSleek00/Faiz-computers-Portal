<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Live Attendance Camera</title>
    <style>
        body{ background:#111827; color:white; font-family:Arial; padding:30px; text-align:center; }
        video{ width:min(900px, 95vw); border-radius:18px; margin-top:18px; border:1px solid #334155; }
        .row{ display:flex; gap:10px; justify-content:center; flex-wrap:wrap; margin-top:16px; }
        button,a.btn{ padding:12px 16px; border-radius:10px; border:none; background:#2563eb; color:white; cursor:pointer; text-decoration:none; display:inline-block; }
        a.btn.secondary{ background:#334155; }
        .status{ margin-top:14px; color:#cbd5e1; }
        .ok{ color:#86efac; }
        .err{ color:#fca5a5; }
    </style>
</head>
<body>

<h2 style="margin:0;">Live Attendance</h2>
<div class="status" id="status">Click start, show face to camera.</div>

<video id="video" autoplay playsinline></video>

<div class="row">
    <button id="startBtn" onclick="startAttendance()">Start Attendance Camera</button>
    <a class="btn secondary" href="index.php">Back</a>
    <a class="btn secondary" href="attendance_dashboard.php">Dashboard</a>
</div>

<script>
const video = document.getElementById('video');
const statusEl = document.getElementById('status');
let streamRef = null;
let timerRef = null;
let locked = false;
let errorStreak = 0;
let modelsReady = false;

function setStatus(text, cls){
    statusEl.className = 'status ' + (cls || '');
    statusEl.textContent = text;
}

async function startAttendance(){
    if (timerRef) return;

    setStatus('Starting camera...', '');
    streamRef = await navigator.mediaDevices.getUserMedia({ video:true });
    video.srcObject = streamRef;

    // Poll every 1s for recognition.
    timerRef = setInterval(captureAndRecognize, 1000);
    setStatus('Camera started. Looking for a known face...', '');
}

async function loadFaceModels(){
    if (modelsReady) return;
    await new Promise((resolve, reject) => {
        const s = document.createElement('script');
        s.src = 'https://unpkg.com/face-api.js@0.22.2/dist/face-api.min.js';
        s.onload = resolve;
        s.onerror = reject;
        document.head.appendChild(s);
    });
    const MODEL_URL = 'https://justadudewhohacks.github.io/face-api.js/models';
    await faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL);
    await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
    await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
    modelsReady = true;
}

async function captureAndRecognize(){
    if (locked) return;
    if (!video.videoWidth || !video.videoHeight) return;

    locked = true;
    try {
        await loadFaceModels();

        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0);
        const det = await faceapi
            .detectSingleFace(canvas)
            .withFaceLandmarks()
            .withFaceDescriptor();
        if (!det || !det.descriptor) {
            setStatus('No face detected. Try again...', '');
            errorStreak = 0;
            return;
        }
        const embedding = Array.from(det.descriptor);

        const resp = await fetch('recognize_frame.php', {
            method:'POST',
            headers:{ 'Content-Type':'application/json' },
            body: JSON.stringify({ embedding })
        });
        const recText = await resp.text();
        if (!resp.ok) {
            errorStreak++;
            setStatus(`recognize_frame.php failed (HTTP ${resp.status}). Check server/PHP logs.`, 'err');
            if (errorStreak >= 3) {
                // Stop spamming the server if it's failing.
                cleanup();
            }
            return;
        }

        let rec = null;
        try {
            rec = JSON.parse(recText);
        } catch {
            setStatus('Server error (non-JSON): ' + recText.slice(0, 120), 'err');
            return;
        }

        if (rec.ok && rec.recognized) {
            errorStreak = 0;
            setStatus(`Recognized: ${rec.student_id} (${rec.table_name}). Marking attendance...`, '');

            const markText = await fetch('api/attendance_api.php', {
                method:'POST',
                headers:{ 'Content-Type':'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    student_id: rec.student_id,
                    table_name: rec.table_name
                })
            }).then(r => r.text());

            let mark = null;
            try {
                mark = JSON.parse(markText);
            } catch {
                setStatus('Attendance API error (non-JSON): ' + markText.slice(0, 120), 'err');
                return;
            }

            if (mark && mark.ok) {
                setStatus('Attendance marked. Opening dashboard...', 'ok');
                cleanup();
                window.location.href = 'attendance_dashboard.php';
                return;
            }

            setStatus('Attendance API failed. Check server logs.', 'err');
        } else if (rec && rec.ok && !rec.recognized) {
            errorStreak = 0;
            // Show underlying reason to debug (missing encodings/deps/etc.)
            if (rec.result && rec.result !== 'UNKNOWN' && rec.result !== 'NOFACE') {
                setStatus(`Not ready: ${rec.result}`, 'err');
            }
        }
    } catch (e) {
        setStatus('Error while recognizing: ' + (e && e.message ? e.message : 'unknown'), 'err');
    } finally {
        locked = false;
    }
}

function cleanup(){
    if (timerRef) clearInterval(timerRef);
    timerRef = null;
    if (streamRef) streamRef.getTracks().forEach(t => t.stop());
    streamRef = null;
}

window.addEventListener('beforeunload', cleanup);
</script>

</body>
</html>

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

async function captureAndRecognize(){
    if (locked) return;
    if (!video.videoWidth || !video.videoHeight) return;

    locked = true;
    try {
        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0);
        const image = canvas.toDataURL('image/jpeg');

        const rec = await fetch('recognize_frame.php', {
            method:'POST',
            headers:{ 'Content-Type':'application/json' },
            body: JSON.stringify({ image })
        }).then(r => r.json());

        if (rec.ok && rec.recognized) {
            setStatus(`Recognized: ${rec.student_id} (${rec.table_name}). Marking attendance...`, '');

            const mark = await fetch('api/attendance_api.php', {
                method:'POST',
                headers:{ 'Content-Type':'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    student_id: rec.student_id,
                    table_name: rec.table_name
                })
            }).then(r => r.json().catch(() => null));

            if (mark && mark.ok) {
                setStatus('Attendance marked. Opening dashboard...', 'ok');
                cleanup();
                window.location.href = 'attendance_dashboard.php';
                return;
            }

            setStatus('Attendance API failed. Check server logs.', 'err');
        } else if (rec && rec.ok && !rec.recognized) {
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

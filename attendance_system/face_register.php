<?php
$id = $_GET['id'];
$table = $_GET['table'];
$id_col = $_GET['id_col'] ?? 'id';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Face Registration</title>

    <style>
        body{
            background:#111827;
            color:white;
            text-align:center;
            font-family:Arial;
        }

        video{
            width:700px;
            border-radius:20px;
            margin-top:30px;
        }

        button{
            margin-top:20px;
            padding:15px 30px;
            border:none;
            border-radius:10px;
            background:#2563eb;
            color:white;
            font-size:18px;
            cursor:pointer;
        }
    </style>
</head>
<body>

<h1>Face Registration</h1>

<video id="video" autoplay></video>

<br>

<button onclick="startCapture()">
Start Face Capture
</button>

<script>

const video = document.getElementById('video');
let streamRef = null;
let embeddingRef = null;

navigator.mediaDevices.getUserMedia({ video:true })
.then(stream => {
    streamRef = stream;
    video.srcObject = stream;
});

async function loadFaceModels(){
    if (window.faceapi) return;
    await new Promise((resolve, reject) => {
        const s = document.createElement('script');
        s.src = 'https://unpkg.com/face-api.js@0.22.2/dist/face-api.min.js';
        s.onload = resolve;
        s.onerror = reject;
        document.head.appendChild(s);
    });

    // Load models from public CDN (weights are fetched by browser)
    const MODEL_URL = 'https://justadudewhohacks.github.io/face-api.js/models';
    await faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL);
    await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
    await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
}

function startCapture(){

    let count = 0;
    let modelsReady = false;

    const interval = setInterval(() => {

        count++;

        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;

        const ctx = canvas.getContext('2d');
        ctx.drawImage(video,0,0);

        const image = canvas.toDataURL('image/jpeg');

        const request = (async () => {
            if (!modelsReady) {
                await loadFaceModels();
                modelsReady = true;
            }

            let embedding = null;
            if (count >= 15) {
                const det = await faceapi
                    .detectSingleFace(canvas)
                    .withFaceLandmarks()
                    .withFaceDescriptor();
                if (det && det.descriptor) {
                    embedding = Array.from(det.descriptor);
                }
            }

            return fetch('save_capture.php', {
                method:'POST',
                headers:{ 'Content-Type':'application/json' },
                body:JSON.stringify({
                    image:image,
                    count:count,
                    student_id:'<?= $id ?>',
                    table_name:'<?= $table ?>',
                    id_col:'<?= $id_col ?>',
                    embedding: embedding
                })
            });
        })();

        if(count >= 15){
            clearInterval(interval);
            request
                .then(r => r.json().catch(() => null))
                .then((res) => {
                    if (streamRef) {
                        streamRef.getTracks().forEach(t => t.stop());
                    }
                    if (res && res.ok === false && res.error) {
                        alert(res.error + (res.detail ? (": " + res.detail) : ""));
                    }
                    // After capture+training, open live attendance camera
                    window.location.href = 'attendance_live.php';
                })
                .catch(() => {
                    if (streamRef) {
                        streamRef.getTracks().forEach(t => t.stop());
                    }
                    window.location.href = 'attendance_live.php';
                });
        }

    },1000);
}

</script>

</body>
</html>

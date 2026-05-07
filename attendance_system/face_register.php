<?php
$id = $_GET['id'];
$table = $_GET['table'];
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

navigator.mediaDevices.getUserMedia({ video:true })
.then(stream => {
    video.srcObject = stream;
});

function startCapture(){

    let count = 0;

    const interval = setInterval(() => {

        count++;

        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;

        const ctx = canvas.getContext('2d');
        ctx.drawImage(video,0,0);

        const image = canvas.toDataURL('image/jpeg');

        fetch('save_capture.php', {
            method:'POST',
            headers:{
                'Content-Type':'application/json'
            },
            body:JSON.stringify({
                image:image,
                count:count,
                student_id:'<?= $id ?>',
                table_name:'<?= $table ?>'
            })
        });

        if(count >= 15){
            clearInterval(interval);
            alert('Face Registration Complete');
        }

    },1000);
}

</script>

</body>
</html>
document.addEventListener('DOMContentLoaded', function(){
const items = document.querySelectorAll('.playlist-item');
const player = document.getElementById('video-player');
const qList = document.getElementById('quality-list');
const titleEl = document.getElementById('video-title');
const descEl = document.getElementById('video-desc');
const downloadLink = document.getElementById('download-link');
const forwardBtn = document.getElementById('btn-forward');


items.forEach(it => it.addEventListener('click', async ()=>{
const id = it.dataset.id;
// fetch video file list
const res = await fetch('fetch_video_files.php?video_id='+id);
if (!res.ok) return alert('Failed to load video');
const data = await res.json();
titleEl.textContent = it.textContent;
descEl.textContent = data.description || '';


qList.innerHTML = '';
// build quality buttons
data.files.forEach((f, idx) =>{
const btn = document.createElement('button');
btn.textContent = f.quality;
btn.dataset.src = f.file_path;
btn.addEventListener('click', ()=>{
setSource(f.file_path, f.mime);
});
qList.appendChild(btn);
if (idx===0){ // auto select first
setSource(f.file_path, f.mime);
}
});


downloadLink.href = 'download.php?file='+encodeURIComponent(data.files[0].file_path);
downloadLink.setAttribute('download', data.title + '.mp4');
}));


function setSource(src, mime){
// preserve current time
const cur = player.currentTime || 0;
player.pause();
player.src = sr
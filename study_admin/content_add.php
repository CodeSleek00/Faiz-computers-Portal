<?php
$pageTitle='Add Study Content';
require_once __DIR__ . '/includes/header.php';

$topics=$conn->query("SELECT t.id,t.topic_name,c.course_name FROM study_topics t JOIN study_courses c ON c.id=t.course_id WHERE t.status='active' AND c.status='active' ORDER BY c.course_name,t.sort_order,t.topic_name");
$selectedTopic=(int)($_GET['topic_id'] ?? $_POST['topic_id'] ?? 0);

if($_SERVER['REQUEST_METHOD']==='POST'){
    $topicId=post_int('topic_id');
    $title=post_string('title');
    $type=post_string('content_type');
    $description=post_string('description');
    $body=$_POST['content_body'] ?? '';
    $order=post_int('sort_order');
    $free=isset($_POST['is_free'])?1:0;
    $status=post_string('status','active');

    $allowedTypes=['video','notes','pdf','document','practical'];
    if($topicId<=0 || $title==='' || !in_array($type,$allowedTypes,true)){
        flash('error','Topic, title and valid content type are required.');
    }else{
        try{
            $fileUrl=null;$videoUrl=null;$thumbUrl=null;
            $videoExt=['mp4','webm','mov','m4v'];
            $fileExt=['pdf','doc','docx','odt','txt','zip','html','htm','css','js','py','xlsx','xls','ppt','pptx'];
            $imageExt=['jpg','jpeg','png','webp'];

            if($type==='video'){
                if(empty($_FILES['video_file']['name'])) throw new RuntimeException('Select a video file.');
                $videoUrl=save_upload($_FILES['video_file'],'videos',$videoExt,500*1024*1024);
            }elseif($type==='pdf'){
                if(!empty($_FILES['content_file']['name'])) $fileUrl=save_upload($_FILES['content_file'],'pdf',['pdf'],25*1024*1024);
            }elseif($type==='notes'){
                if(!empty($_FILES['content_file']['name'])) $fileUrl=save_upload($_FILES['content_file'],'notes',['html','htm','pdf','doc','docx','odt','txt'],25*1024*1024);
            }else{
                if(!empty($_FILES['content_file']['name'])) $fileUrl=save_upload($_FILES['content_file'],$type==='practical'?'practical':'documents',$fileExt,25*1024*1024);
            }

            if(!empty($_FILES['thumbnail']['name'])){
                $thumbUrl=save_upload($_FILES['thumbnail'],'thumbnails',$imageExt,5*1024*1024);
            }

            $stmt=$conn->prepare("INSERT INTO study_contents (topic_id,title,content_type,description,content_body,file_url,video_url,thumbnail_url,sort_order,is_free,status) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param('isssssssiis',$topicId,$title,$type,$description,$body,$fileUrl,$videoUrl,$thumbUrl,$order,$free,$status);
            $stmt->execute();$id=$stmt->insert_id;$stmt->close();

            flash('success','Content created successfully.');
            redirect('content_edit.php?id='.$id);
        }catch(Throwable $e){
            flash('error',$e->getMessage());
        }
    }
}
?>
<div class="card">
<form method="post" enctype="multipart/form-data">
<div class="form-grid">
<div class="full"><label>Topic *</label>
<select name="topic_id" required>
<option value="">Select topic</option>
<?php while($t=$topics->fetch_assoc()): ?>
<option value="<?= (int)$t['id'] ?>" <?= $selectedTopic===(int)$t['id']?'selected':'' ?>><?= e($t['course_name'].' → '.$t['topic_name']) ?></option>
<?php endwhile; ?>
</select></div>

<div><label>Title *</label><input name="title" required></div>

<div><label>Content Type *</label>
<select name="content_type" id="content_type" onchange="toggleFields()">
<option value="video">Video</option>
<option value="notes">Notes</option>
<option value="pdf">PDF</option>
<option value="document">Document</option>
<option value="practical">Practical</option>
</select></div>

<div><label>Sort Order</label><input type="number" name="sort_order" value="0"></div>

<div><label>Status</label><select name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>

<div class="full"><label>Description</label><textarea name="description"></textarea></div>

<div class="full" id="bodyBox">
<label>Notes / HTML / Rich Content (optional)</label>
<textarea name="content_body" placeholder="For notes you can write HTML content here."></textarea>
</div>

<div class="full" id="videoBox">
<label>Video File (MP4/WebM/MOV/M4V)</label>
<input type="file" name="video_file" accept=".mp4,.webm,.mov,.m4v">
</div>

<div class="full" id="fileBox">
<label>Study File</label>
<input type="file" name="content_file">
</div>

<div class="full">
<label>Thumbnail (optional)</label>
<input type="file" name="thumbnail" accept=".jpg,.jpeg,.png,.webp">
</div>

<div><label><input type="checkbox" name="is_free" value="1" style="width:auto"> Free content</label></div>
</div>
<br><button class="btn orange">Create Content</button>
<a class="btn light" href="contents.php<?= $selectedTopic?'?topic_id='.$selectedTopic:'' ?>">Cancel</a>
</form>
</div>
<script>
function toggleFields(){
 const t=document.getElementById('content_type').value;
 document.getElementById('videoBox').style.display=t==='video'?'block':'none';
 document.getElementById('fileBox').style.display=t==='video'?'none':'block';
 document.getElementById('bodyBox').style.display=(t==='notes'||t==='practical')?'block':'none';
}
toggleFields();
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

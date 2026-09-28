<?php
$id=(int)($_GET['id'] ?? 0);
$pageTitle='Edit Study Content';
require_once __DIR__ . '/includes/header.php';

$stmt=$conn->prepare("SELECT * FROM study_contents WHERE id=?");
$stmt->bind_param('i',$id);$stmt->execute();$row=$stmt->get_result()->fetch_assoc();$stmt->close();
if(!$row){flash('error','Content not found.');redirect('contents.php');}

$topics=$conn->query("SELECT t.id,t.topic_name,c.course_name FROM study_topics t JOIN study_courses c ON c.id=t.course_id ORDER BY c.course_name,t.sort_order,t.topic_name");

if($_SERVER['REQUEST_METHOD']==='POST'){
    $topicId=post_int('topic_id');$title=post_string('title');$type=post_string('content_type');
    $description=post_string('description');$body=$_POST['content_body'] ?? '';
    $order=post_int('sort_order');$free=isset($_POST['is_free'])?1:0;$status=post_string('status','active');

    try{
        $fileUrl=$row['file_url'];$videoUrl=$row['video_url'];$thumbUrl=$row['thumbnail_url'];

        $videoExt=['mp4','webm','mov','m4v'];
        $fileExt=['pdf','doc','docx','odt','txt','zip','html','htm','css','js','py','xlsx','xls','ppt','pptx'];
        $imageExt=['jpg','jpeg','png','webp'];

        if(!empty($_FILES['video_file']['name'])){
            $new=save_upload($_FILES['video_file'],'videos',$videoExt,500*1024*1024);
            delete_relative_file($videoUrl); $videoUrl=$new;
        }
        if(!empty($_FILES['content_file']['name'])){
            $folder=$type==='pdf'?'pdf':($type==='notes'?'notes':($type==='practical'?'practical':'documents'));
            $new=save_upload($_FILES['content_file'],$folder,$type==='pdf'?['pdf']:($type==='notes'?['html','htm','pdf','doc','docx','odt','txt']:$fileExt),25*1024*1024);
            delete_relative_file($fileUrl); $fileUrl=$new;
        }
        if(!empty($_FILES['thumbnail']['name'])){
            $new=save_upload($_FILES['thumbnail'],'thumbnails',$imageExt,5*1024*1024);
            delete_relative_file($thumbUrl); $thumbUrl=$new;
        }

        $stmt=$conn->prepare("UPDATE study_contents SET topic_id=?,title=?,content_type=?,description=?,content_body=?,file_url=?,video_url=?,thumbnail_url=?,sort_order=?,is_free=?,status=? WHERE id=?");
        $stmt->bind_param('isssssssiisi',$topicId,$title,$type,$description,$body,$fileUrl,$videoUrl,$thumbUrl,$order,$free,$status,$id);
        $stmt->execute();$stmt->close();

        flash('success','Content updated.');
        redirect('content_edit.php?id='.$id);
    }catch(Throwable $e){flash('error',$e->getMessage());}

    $row=array_merge($row,['topic_id'=>$topicId,'title'=>$title,'content_type'=>$type,'description'=>$description,'content_body'=>$body,'sort_order'=>$order,'is_free'=>$free,'status'=>$status,'file_url'=>$fileUrl,'video_url'=>$videoUrl,'thumbnail_url'=>$thumbUrl]);
}
?>
<div class="card">
<form method="post" enctype="multipart/form-data">
<div class="form-grid">
<div class="full"><label>Topic</label><select name="topic_id" required>
<?php while($t=$topics->fetch_assoc()): ?>
<option value="<?= (int)$t['id'] ?>" <?= (int)$row['topic_id']===(int)$t['id']?'selected':'' ?>><?= e($t['course_name'].' → '.$t['topic_name']) ?></option>
<?php endwhile; ?></select></div>
<div><label>Title</label><input name="title" value="<?= e($row['title']) ?>" required></div>
<div><label>Content Type</label><select name="content_type">
<?php foreach(['video','notes','pdf','document','practical'] as $t): ?>
<option value="<?= $t ?>" <?= $row['content_type']===$t?'selected':'' ?>><?= ucfirst($t) ?></option>
<?php endforeach; ?></select></div>
<div><label>Sort Order</label><input type="number" name="sort_order" value="<?= (int)$row['sort_order'] ?>"></div>
<div><label>Status</label><select name="status"><option value="active" <?= $row['status']==='active'?'selected':'' ?>>Active</option><option value="inactive" <?= $row['status']==='inactive'?'selected':'' ?>>Inactive</option></select></div>
<div class="full"><label>Description</label><textarea name="description"><?= e($row['description']) ?></textarea></div>
<div class="full"><label>Notes / HTML / Rich Content</label><textarea name="content_body"><?= e($row['content_body']) ?></textarea></div>

<div class="full">
<label>Current Video</label>
<?php if($row['video_url']): ?><a class="btn small" target="_blank" href="<?= e($row['video_url']) ?>">Open current video</a><?php else: ?><span class="muted">No video</span><?php endif; ?>
<input type="file" name="video_file" accept=".mp4,.webm,.mov,.m4v">
</div>

<div class="full">
<label>Current Study File</label>
<?php if($row['file_url']): ?><a class="btn small" target="_blank" href="<?= e($row['file_url']) ?>">Open current file</a><?php else: ?><span class="muted">No file</span><?php endif; ?>
<input type="file" name="content_file">
</div>

<div class="full">
<label>Current Thumbnail</label>
<?php if($row['thumbnail_url']): ?><a class="btn small" target="_blank" href="<?= e($row['thumbnail_url']) ?>">Open thumbnail</a><?php else: ?><span class="muted">No thumbnail</span><?php endif; ?>
<input type="file" name="thumbnail" accept=".jpg,.jpeg,.png,.webp">
</div>

<div><label><input type="checkbox" name="is_free" value="1" style="width:auto" <?= (int)$row['is_free']===1?'checked':'' ?>> Free content</label></div>
</div>
<br><button class="btn orange">Save Changes</button>
<a class="btn light" href="contents.php?topic_id=<?= (int)$row['topic_id'] ?>">Back</a>
</form>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

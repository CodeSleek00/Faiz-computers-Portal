<?php
$contentId=(int)($_GET['content_id'] ?? 0);
$pageTitle='Content Attachments';
require_once __DIR__ . '/includes/header.php';

$stmt=$conn->prepare("SELECT id,title,content_type FROM study_contents WHERE id=?");
$stmt->bind_param('i',$contentId);$stmt->execute();$content=$stmt->get_result()->fetch_assoc();$stmt->close();
if(!$content){flash('error','Content not found.');redirect('contents.php');}

if($_SERVER['REQUEST_METHOD']==='POST'){
    if(post_string('action')==='delete'){
        $id=post_int('id');
        $stmt=$conn->prepare("SELECT file_url FROM study_content_attachments WHERE id=? AND content_id=?");
        $stmt->bind_param('ii',$id,$contentId);$stmt->execute();$old=$stmt->get_result()->fetch_assoc();$stmt->close();
        $stmt=$conn->prepare("DELETE FROM study_content_attachments WHERE id=? AND content_id=?");
        $stmt->bind_param('ii',$id,$contentId);$stmt->execute();$stmt->close();
        if($old) delete_relative_file($old['file_url']);
        flash('success','Attachment deleted.');
        redirect('attachments.php?content_id='.$contentId);
    }
    if(post_string('action')==='add'){
        try{
            $name=post_string('attachment_name');
            $type=post_string('attachment_type','other');
            $order=post_int('sort_order');
            $exts=['pdf','doc','docx','odt','txt','zip','jpg','jpeg','png','webp','html','htm','css','js','py','xlsx','xls','ppt','pptx'];
            if($name==='' || empty($_FILES['attachment']['name'])) throw new RuntimeException('Name and file are required.');
            $url=save_upload($_FILES['attachment'],'attachments',$exts,50*1024*1024);
            $size=(int)$_FILES['attachment']['size'];
            $stmt=$conn->prepare("INSERT INTO study_content_attachments (content_id,attachment_name,attachment_type,file_url,file_size,sort_order) VALUES (?,?,?,?,?,?)");
            $stmt->bind_param('isssii',$contentId,$name,$type,$url,$size,$order);
            $stmt->execute();$stmt->close();
            flash('success','Attachment added.');
        }catch(Throwable $e){flash('error',$e->getMessage());}
        redirect('attachments.php?content_id='.$contentId);
    }
}
$rows=$conn->query("SELECT * FROM study_content_attachments WHERE content_id=".$contentId." ORDER BY sort_order,id");
?>
<div class="card">
<h2><?= e($content['title']) ?></h2>
<p class="muted">Type: <?= e($content['content_type']) ?></p>
<form method="post" enctype="multipart/form-data">
<input type="hidden" name="action" value="add">
<div class="form-grid">
<div><label>Attachment Name *</label><input name="attachment_name" required></div>
<div><label>Type</label><select name="attachment_type">
<?php foreach(['pdf','document','zip','image','code','other'] as $t): ?><option value="<?= $t ?>"><?= ucfirst($t) ?></option><?php endforeach; ?>
</select></div>
<div><label>Sort Order</label><input type="number" name="sort_order" value="0"></div>
<div><label>File *</label><input type="file" name="attachment" required></div>
</div><br>
<button class="btn orange">Add Attachment</button>
</form>
</div>

<div class="card"><div class="table-wrap"><table>
<thead><tr><th>ID</th><th>Name</th><th>Type</th><th>Size</th><th>File</th><th>Action</th></tr></thead>
<tbody>
<?php while($r=$rows->fetch_assoc()): ?>
<tr><td><?= (int)$r['id'] ?></td><td><?= e($r['attachment_name']) ?></td><td><?= e($r['attachment_type']) ?></td><td><?= number_format(((int)$r['file_size'])/1024,1) ?> KB</td><td><a class="btn small" target="_blank" href="<?= e($r['file_url']) ?>">Open</a></td><td>
<form method="post" onsubmit="return confirm('Delete attachment?')">
<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
<button class="btn small danger">Delete</button>
</form></td></tr>
<?php endwhile; ?>
</tbody></table></div></div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

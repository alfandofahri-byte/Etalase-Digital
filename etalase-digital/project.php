<?php
require 'config.php';
login_required();

$role=$_SESSION['user']['role'];
if($role==='siswa'){
    $allowed=['siswa'];
}elseif($role==='admin'){
    $allowed=['admin'];
}else{
    $allowed=['super_admin'];
}
if(!in_array($role,$allowed,true)) go_dashboard();

$action=$_GET['action']??'add';
$id=(int)($_GET['id']??0);
$error='';

if($action==='delete'){
    if($role==='siswa'){
        $stmt=$pdo->prepare("SELECT gambar FROM projects WHERE id=? AND user_id=?");
        $stmt->execute([$id,$_SESSION['user']['id']]);
    }else{
        $stmt=$pdo->prepare("SELECT gambar FROM projects WHERE id=?");
        $stmt->execute([$id]);
    }
    $p=$stmt->fetch();
    if($p){
        $del=$pdo->prepare("DELETE FROM projects WHERE id=?");
        $del->execute([$id]);
        if($p['gambar']!=='default-project.svg'){
            $file=__DIR__.'/uploads/projects/'.$p['gambar'];
            if(is_file($file)) unlink($file);
        }
    }
    go_dashboard();
}

$project=null;
if($action==='edit'){
    $stmt=$pdo->prepare("SELECT * FROM projects WHERE id=?");
    $stmt->execute([$id]);
    $project=$stmt->fetch();
    if(!$project) exit('Project tidak ditemukan.');
    if($role==='siswa' && $project['user_id']!=$_SESSION['user']['id']) exit('Kamu hanya boleh mengedit project milik sendiri.');
}

$students=[];
if($role!=='siswa'){
    $students=$pdo->query("SELECT id,nama FROM users WHERE role='siswa' ORDER BY nama")->fetchAll();
}

if($_SERVER['REQUEST_METHOD']==='POST'){
    $user_id=$role==='siswa' ? (int)$_SESSION['user']['id'] : (int)($_POST['user_id']??0);
    $nama=trim($_POST['nama_project']??'');
    $deskripsi=trim($_POST['deskripsi']??'');
    $link=trim($_POST['link_project']??'');
    $demo=trim($_POST['demo']??'');
    $file=$_FILES['gambar']??null;

    if(!$user_id || $nama==='' || $deskripsi==='' || $link===''){
        $error='Semua field wajib diisi kecuali demo.';
    }elseif(!filter_var($link,FILTER_VALIDATE_URL) || ($demo!=='' && !filter_var($demo,FILTER_VALIDATE_URL))){
        $error='Link harus berupa URL yang valid.';
    }else{
        $filename=$project['gambar']??'';
        if($action==='add'){
            if(!$file || $file['error']!==UPLOAD_ERR_OK){
                $error='Gambar wajib diupload.';
            }
        }

        if($error===''){
            if($file && $file['error']===UPLOAD_ERR_OK){
                $allowed=['image/jpeg','image/png','image/webp'];
                $mime=mime_content_type($file['tmp_name']);
                if($file['size']>2*1024*1024){
                    $error='Ukuran gambar maksimal 2 MB.';
                }elseif(!in_array($mime,$allowed,true)){
                    $error='Gambar harus JPG, PNG, atau WEBP.';
                }else{
                    $ext=strtolower(pathinfo($file['name'],PATHINFO_EXTENSION));
                    $filename=uniqid('project_',true).'.'.$ext;
                    if(!move_uploaded_file($file['tmp_name'],__DIR__.'/uploads/projects/'.$filename)){
                        $error='Upload gambar gagal.';
                    }elseif($action==='edit' && $project['gambar']!=='default-project.svg'){
                        $old=__DIR__.'/uploads/projects/'.$project['gambar'];
                        if(is_file($old)) unlink($old);
                    }
                }
            }
        }

        if($error===''){
            if($action==='add'){
                $stmt=$pdo->prepare("INSERT INTO projects(user_id,nama_project,deskripsi,gambar,link_project,demo) VALUES(?,?,?,?,?,?)");
                $stmt->execute([$user_id,$nama,$deskripsi,$filename,$link,$demo?:null]);
            }else{
                if($role==='siswa'){
                    $stmt=$pdo->prepare("UPDATE projects SET nama_project=?,deskripsi=?,gambar=?,link_project=?,demo=? WHERE id=? AND user_id=?");
                    $stmt->execute([$nama,$deskripsi,$filename,$link,$demo?:null,$id,$_SESSION['user']['id']]);
                }else{
                    $stmt=$pdo->prepare("UPDATE projects SET user_id=?,nama_project=?,deskripsi=?,gambar=?,link_project=?,demo=? WHERE id=?");
                    $stmt->execute([$user_id,$nama,$deskripsi,$filename,$link,$demo?:null,$id]);
                }
            }
            go_dashboard();
        }
    }
}

$title=$action==='add'?'Tambah Project':'Edit Project';
require 'header.php';
?>
<section class="layout">
<div class="container">
<div class="row justify-content-center">
<div class="col-lg-8">
<div class="form-box">
<h1 class="fw-bold mb-4"><?= $title ?></h1>
<?php if($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data">
<?php if($role!=='siswa'): ?>
<div class="mb-3"><label class="form-label">Pemilik Project</label><select name="user_id" class="form-select" required><option value="">Pilih siswa</option><?php foreach($students as $s): ?><option value="<?= $s['id'] ?>" <?= isset($project['user_id'])&&$project['user_id']==$s['id']?'selected':'' ?>><?= e($s['nama']) ?></option><?php endforeach; ?></select></div>
<?php endif; ?>
<div class="mb-3"><label class="form-label">Nama Project</label><input name="nama_project" class="form-control" maxlength="150" value="<?= e($project['nama_project']??'') ?>" required></div>
<div class="mb-3"><label class="form-label">Deskripsi</label><textarea name="deskripsi" class="form-control" rows="6" required><?= e($project['deskripsi']??'') ?></textarea></div>
<?php if($project): ?><div class="mb-3"><label class="form-label">Gambar Saat Ini</label><br><img src="uploads/projects/<?= e($project['gambar']) ?>" style="width:220px;height:140px;object-fit:cover;border-radius:12px" alt=""></div><?php endif; ?>
<div class="mb-3"><label class="form-label">Gambar <?= $project?'(opsional jika tidak ingin mengganti)':'' ?></label><input type="file" name="gambar" class="form-control" accept=".jpg,.jpeg,.png,.webp" <?= $project?'':'required' ?>></div>
<div class="mb-3"><label class="form-label">Link Pendukung</label><input type="url" name="link_project" class="form-control" value="<?= e($project['link_project']??'') ?>" placeholder="https://github.com/..." required></div>
<div class="mb-4"><label class="form-label">Demo <span class="text-secondary">(opsional)</span></label><input type="url" name="demo" class="form-control" value="<?= e($project['demo']??'') ?>" placeholder="https://..."></div>
<button class="btn btn-primary">Simpan</button>
<a href="dashboard.php" class="btn btn-outline-secondary">Batal</a>
</form>
</div>
</div>
</div>
</div>
</section>
<?php require 'footer.php'; ?>

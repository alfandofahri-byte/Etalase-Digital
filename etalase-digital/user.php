<?php
require 'config.php';
login_required();

$role=$_SESSION['user']['role'];
$action=$_GET['action']??'add';
$id=(int)($_GET['id']??0);

if($role==='admin'){
    if($action!=='edit') go_dashboard();
    $stmt=$pdo->prepare("SELECT * FROM users WHERE id=? AND role='siswa'");
    $stmt->execute([$id]);
    $user=$stmt->fetch();
    if(!$user) exit('Siswa tidak ditemukan.');
}elseif($role==='super_admin'){
    $user=null;
    if($action==='edit'){
        $stmt=$pdo->prepare("SELECT * FROM users WHERE id=?");
        $stmt->execute([$id]);
        $user=$stmt->fetch();
        if(!$user) exit('User tidak ditemukan.');
    }
    if($action==='delete'){
        if($id!=$_SESSION['user']['id']){
            $stmt=$pdo->prepare("DELETE FROM users WHERE id=?");
            $stmt->execute([$id]);
        }
        go_dashboard();
    }
}else{
    go_dashboard();
}

$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $nama=trim($_POST['nama']??'');
    $email=trim($_POST['email']??'');
    $password=$_POST['password']??'';
    $new_role=$_POST['role']??'siswa';

    if($nama==='' || !filter_var($email,FILTER_VALIDATE_EMAIL)){
        $error='Nama dan email harus valid.';
    }elseif($action==='add' && strlen($password)<6){
        $error='Password minimal 6 karakter.';
    }elseif($role==='super_admin' && !in_array($new_role,['super_admin','admin','siswa'],true)){
        $error='Role tidak valid.';
    }else{
        $check=$pdo->prepare("SELECT id FROM users WHERE email=? AND id!=?");
        $check->execute([$email,$id]);
        if($check->fetch()){
            $error='Email sudah digunakan.';
        }else{
            if($action==='add'){
                $hash=password_hash($password,PASSWORD_DEFAULT);
                $stmt=$pdo->prepare("INSERT INTO users(nama,email,password,role) VALUES(?,?,?,?)");
                $stmt->execute([$nama,$email,$hash,$role==='super_admin'?$new_role:'siswa']);
            }else{
                if($role==='admin'){
                    $stmt=$pdo->prepare("UPDATE users SET nama=?,email=? WHERE id=? AND role='siswa'");
                    $stmt->execute([$nama,$email,$id]);
                }else{
                    if($password!==''){
                        $hash=password_hash($password,PASSWORD_DEFAULT);
                        $stmt=$pdo->prepare("UPDATE users SET nama=?,email=?,password=?,role=? WHERE id=?");
                        $stmt->execute([$nama,$email,$hash,$new_role,$id]);
                    }else{
                        $stmt=$pdo->prepare("UPDATE users SET nama=?,email=?,role=? WHERE id=?");
                        $stmt->execute([$nama,$email,$new_role,$id]);
                    }
                    if($id==$_SESSION['user']['id']){
                        $_SESSION['user']['nama']=$nama;
                        $_SESSION['user']['email']=$email;
                        $_SESSION['user']['role']=$new_role;
                    }
                }
            }
            go_dashboard();
        }
    }
}

$title=$action==='add'?'Tambah User':'Edit User';
require 'header.php';
?>
<section class="layout">
<div class="container">
<div class="row justify-content-center"><div class="col-lg-7"><div class="form-box">
<h1 class="fw-bold mb-4"><?= $title ?></h1>
<?php if($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
<form method="post">
<div class="mb-3"><label class="form-label">Nama</label><input name="nama" class="form-control" value="<?= e($user['nama']??'') ?>" required></div>
<div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= e($user['email']??'') ?>" required></div>
<?php if($role==='super_admin'): ?>
<div class="mb-3"><label class="form-label">Role</label><select name="role" class="form-select">
<option value="siswa" <?= ($user['role']??'')==='siswa'?'selected':'' ?>>Siswa</option>
<option value="admin" <?= ($user['role']??'')==='admin'?'selected':'' ?>>Admin</option>
<option value="super_admin" <?= ($user['role']??'')==='super_admin'?'selected':'' ?>>Super Admin</option>
</select></div>
<?php endif; ?>
<div class="mb-4"><label class="form-label">Password <?= $action==='edit'?'(kosongkan jika tidak diubah)':'' ?></label><input type="password" name="password" class="form-control" <?= $action==='add'?'required':'' ?>></div>
<button class="btn btn-primary">Simpan</button>
<a href="dashboard.php" class="btn btn-outline-secondary">Batal</a>
</form>
</div></div></div>
</div>
</section>
<?php require 'footer.php'; ?>

<?php require __DIR__ . '/../shares/header.php'; ?>

<div class="auth-container" style="max-width: 800px; margin: 20px auto;">
    <div class="auth-card">
        <h2 class="auth-title">Hồ Sơ Cá Nhân</h2>
        
        <div style="display: flex; gap: 30px; flex-wrap: wrap;">
            <!-- Bên trái: Avatar -->
            <div style="flex: 1; min-width: 200px; text-align: center;">
                <div class="mb-3">
                    <img src="<?= e(base_url(avatar_url($user['avatar']))) ?>" alt="Avatar" style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 2px solid #ee4d2d; display: block; margin: 0 auto;">
                </div>
                <form action="<?= e(url('profile', 'uploadAvatar')) ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
                    <input type="file" name="avatar" accept="image/*" required style="font-size: 12px; margin-bottom: 10px;">
                    <button type="submit" class="btn btn-outline" style="padding: 5px 15px; font-size: 12px;">Đổi ảnh</button>
                </form>
            </div>

            <!-- Bên phải: Form thông tin -->
            <div style="flex: 2; min-width: 300px;">
                <form action="<?= e(url('profile', 'update')) ?>" method="POST">
                    <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
                    
                    <div class="form-group">
                        <label>Email (Không thể thay đổi)</label>
                        <input type="text" class="form-control" value="<?= e($user['email']) ?>" disabled style="background: #f5f5f5;">
                    </div>

                    <div class="form-group">
                        <label>Họ và tên</label>
                        <input type="text" name="name" class="form-control" value="<?= e($user['name']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Số điện thoại</label>
                        <input type="text" name="phone" class="form-control" value="<?= e($user['phone'] ?? '') ?>" placeholder="Chưa cập nhật">
                    </div>

                    <div class="form-group">
                        <label>Địa chỉ</label>
                        <textarea name="address" class="form-control" rows="3" placeholder="Chưa cập nhật"><?= e($user['address'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">Cập nhật thông tin</button>
                </form>

                <hr style="margin: 30px 0; border: 0; border-top: 1px solid #eee;">
                
                <h3 style="font-size: 18px; margin-bottom: 15px;">Đổi mật khẩu</h3>
                <form action="<?= e(url('profile', 'changePassword')) ?>" method="POST">
                    <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
                    
                    <div class="form-group">
                        <input type="password" name="current_password" class="form-control" placeholder="Mật khẩu hiện tại" required>
                    </div>
                    <div class="form-group">
                        <input type="password" name="new_password" class="form-control" placeholder="Mật khẩu mới" required>
                    </div>
                    <div class="form-group">
                        <input type="password" name="new_password_confirm" class="form-control" placeholder="Xác nhận mật khẩu mới" required>
                    </div>
                    
                    <button type="submit" class="btn btn-outline" style="width: 100%; border-color: #ee4d2d; color: #ee4d2d;">Xác nhận đổi mật khẩu</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-weight: 600; color: #555; font-size: 14px; }
</style>

<?php require __DIR__ . '/../shares/footer.php'; ?>

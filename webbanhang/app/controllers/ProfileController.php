<?php
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../core/helpers.php';

class ProfileController
{
    private UserModel $userModel;

    public function __construct(private PDO $pdo)
    {
        $this->userModel = new UserModel($pdo);
        $this->requireLogin();
    }

    private function requireLogin(): void
    {
        if (empty($_SESSION['user'])) {
            $_SESSION['flash_error'] = 'Vui lòng đăng nhập để tiếp tục.';
            redirect('index.php?c=auth&a=login');
        }
    }

    // ============================
    // XEM HỒ SƠ
    // ============================
    public function index(): void
    {
        $user = $this->userModel->findById((int)$_SESSION['user']['id']);
        $pageTitle = 'Hồ Sơ Cá Nhân';
        require __DIR__ . '/../views/profile/index.php';
    }

    // ============================
    // CẬP NHẬT HỒ SƠ
    // ============================
    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?c=profile&a=index');
        }
        csrf_check();

        $name    = trim($_POST['name'] ?? '');
        $phone   = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $errors  = [];

        if (mb_strlen($name) < 2) $errors[] = 'Họ tên phải ít nhất 2 ký tự.';

        if (!$errors) {
            $userId = (int)$_SESSION['user']['id'];
            $this->userModel->updateProfile($userId, compact('name', 'phone', 'address'));
            // Cập nhật session
            $_SESSION['user']['name'] = $name;
            $_SESSION['flash_success'] = 'Cập nhật hồ sơ thành công!';
            redirect('index.php?c=profile&a=index');
        }

        $user = $this->userModel->findById((int)$_SESSION['user']['id']);
        $pageTitle = 'Hồ Sơ Cá Nhân';
        require __DIR__ . '/../views/profile/index.php';
    }

    // ============================
    // ĐỔI MẬT KHẨU
    // ============================
    public function changePassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?c=profile&a=index');
        }
        csrf_check();

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword     = $_POST['new_password'] ?? '';
        $confirm         = $_POST['new_password_confirm'] ?? '';
        $errors          = [];
        $userId          = (int)$_SESSION['user']['id'];

        $user = $this->userModel->findById($userId);

        if (!$this->userModel->verifyPassword($user, $currentPassword)) {
            $errors[] = 'Mật khẩu hiện tại không đúng.';
        }
        if (mb_strlen($newPassword) < 6) $errors[] = 'Mật khẩu mới ít nhất 6 ký tự.';
        if ($newPassword !== $confirm)   $errors[] = 'Xác nhận mật khẩu không khớp.';

        if (!$errors) {
            $this->userModel->updatePassword($userId, $newPassword);
            $_SESSION['flash_success'] = 'Đổi mật khẩu thành công!';
        } else {
            $_SESSION['flash_error'] = implode('<br>', $errors);
        }

        redirect('index.php?c=profile&a=index');
    }

    // ============================
    // TẢI LÊN ẢNH ĐẠI DIỆN
    // ============================
    public function uploadAvatar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?c=profile&a=index');
        }
        csrf_check();

        $userId = (int)$_SESSION['user']['id'];
        $errors = [];

        if (empty($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Vui lòng chọn file ảnh.';
        } else {
            $file     = $_FILES['avatar'];
            $maxSize  = 2 * 1024 * 1024; // 2MB
            $allowed  = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $finfo    = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);

            if ($file['size'] > $maxSize) {
                $errors[] = 'File quá lớn. Tối đa 2MB.';
            } elseif (!in_array($mimeType, $allowed)) {
                $errors[] = 'Chỉ chấp nhận ảnh JPG, PNG, GIF, WEBP.';
            }

            if (!$errors) {
                $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = 'avatar_' . $userId . '_' . time() . '.' . strtolower($ext);
                $uploadDir = __DIR__ . '/../../public/uploads/avatars/';

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                // Xóa avatar cũ nếu có
                $oldUser = $this->userModel->findById($userId);
                if ($oldUser['avatar'] && file_exists(__DIR__ . '/../../' . $oldUser['avatar'])) {
                    unlink(__DIR__ . '/../../' . $oldUser['avatar']);
                }

                $destPath = $uploadDir . $filename;
                if (move_uploaded_file($file['tmp_name'], $destPath)) {
                    $dbPath = 'public/uploads/avatars/' . $filename;
                    $this->userModel->updateAvatar($userId, $dbPath);
                    $_SESSION['user']['avatar'] = $dbPath;
                    $_SESSION['flash_success'] = 'Cập nhật ảnh đại diện thành công!';
                } else {
                    $errors[] = 'Không thể lưu file. Kiểm tra quyền thư mục.';
                }
            }
        }

        if ($errors) {
            $_SESSION['flash_error'] = implode('<br>', $errors);
        }

        redirect('index.php?c=profile&a=index');
    }
}

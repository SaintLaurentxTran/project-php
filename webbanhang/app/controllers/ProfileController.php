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
            redirect(url('auth', 'login'));
        }
    }

    // ============================
    // XEM HỒ SƠ
    // ============================
    public function index(): void
    {
        // 1. Lấy dữ liệu mới nhất từ database
        $user = $this->userModel->findById((int)$_SESSION['user']['id']);
        
        // 2. Ép Session phải ghi đè lại dữ liệu vừa bốc từ Database lên
        if ($user) {
            $_SESSION['user'] = [
                'id'      => $user['id'],
                'name'    => $user['name'],
                'email'   => $user['email'],
                'phone'   => $user['phone'] ?? '',
                'address' => $user['address'] ?? '',
                'avatar'  => $user['avatar'] ?? '',
                'role'    => $user['role'] ?? 'user'
            ];
        }

        $pageTitle = 'Hồ Sơ Cá Nhân';
        require __DIR__ . '/../views/profile/index.php';
    }

    // ============================
    // CẬP NHẬT HỒ SƠ
    // ============================
    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(url('profile', 'index'));
        }
        
        csrf_check();

        $name    = trim($_POST['name'] ?? '');
        $phone   = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $errors  = [];

        if (mb_strlen($name) < 2) {
            $errors[] = 'Họ tên phải ít nhất 2 ký tự.';
        }

        if (!$errors) {
            $userId = (int)$_SESSION['user']['id'];

            $this->userModel->updateProfile($userId, $name, $phone, $address);

            $_SESSION['user']['name']    = $name;
            $_SESSION['user']['phone']   = $phone;
            $_SESSION['user']['address'] = $address;
            
            $_SESSION['flash_success'] = 'Cập nhật hồ sơ thành công!';
            redirect(url('profile', 'index'));
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
            redirect(url('profile', 'index'));
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

        redirect(url('profile', 'index'));
    }

    // ============================
    // TẢI LÊN ẢNH ĐẠI DIỆN
    // ============================
    public function uploadAvatar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(url('profile', 'index'));
        }

        csrf_check();

        $userId = (int)$_SESSION['user']['id'];

        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['avatar']['tmp_name'];
            $fileName    = $_FILES['avatar']['name'];
            
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

            if (!in_array($fileExtension, $allowedExtensions, true)) {
                $_SESSION['flash_error'] = 'Định dạng ảnh không hợp lệ. Chỉ chấp nhận JPG, PNG, GIF.';
                redirect(url('profile', 'index'));
            }

            $avatarName = 'avatar_' . $userId . '_' . time() . '.' . $fileExtension;

            $uploadFileDir = __DIR__ . '/../../public/uploads/avatars/';
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }

            $destPath = $uploadFileDir . $avatarName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                
                $this->userModel->updateAvatar($userId, $avatarName);

                $_SESSION['user']['avatar'] = $avatarName;

                $_SESSION['flash_success'] = 'Cập nhật ảnh đại diện thành công!';
            } else {
                $_SESSION['flash_error'] = 'Không thể lưu tập tin ảnh đại diện lên máy chủ.';
            }
        } else {
            $_SESSION['flash_error'] = 'Vui lòng chọn một tập tin ảnh hợp lệ.';
        }

        redirect(url('profile', 'index'));
    }
}

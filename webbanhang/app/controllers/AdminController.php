<?php
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../core/helpers.php';

class AdminController
{
    private UserModel $userModel;

    public function __construct(private PDO $pdo)
    {
        $this->userModel = new UserModel($pdo);
        $this->requireAdmin();
    }

    private function requireAdmin(): void
    {
        if (empty($_SESSION['user'])) {
            $_SESSION['flash_error'] = 'Vui lòng đăng nhập.';
            redirect('index.php?c=auth&a=login');
        }
        if ($_SESSION['user']['role'] !== 'admin') {
            http_response_code(403);
            $pageTitle = 'Không có quyền truy cập';
            require __DIR__ . '/../views/errors/403.php';
            exit;
        }
    }

    // ============================
    // DANH SÁCH NGƯỜI DÙNG
    // ============================
    public function users(): void
    {
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $search  = trim($_GET['q'] ?? '');
        $result  = $this->userModel->paginate($page, 15, $search);
        $pageTitle = 'Quản Lý Người Dùng';
        require __DIR__ . '/../views/admin/users.php';
    }

    // ============================
    // KHÓA / MỞ KHÓA TÀI KHOẢN
    // ============================
    public function toggleActive(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?c=admin&a=users');
        }
        csrf_check();

        $userId = (int)($_POST['user_id'] ?? 0);
        $status = (int)($_POST['status'] ?? 0); // 0 = khóa, 1 = mở

        // Không cho khóa chính mình
        if ($userId === (int)$_SESSION['user']['id']) {
            $_SESSION['flash_error'] = 'Không thể tự khóa tài khoản của mình.';
            redirect('index.php?c=admin&a=users');
        }

        $this->userModel->setActive($userId, $status);
        $msg = $status ? 'Đã mở khóa tài khoản.' : 'Đã khóa tài khoản.';
        $_SESSION['flash_success'] = $msg;
        redirect('index.php?c=admin&a=users');
    }

    // ============================
    // THAY ĐỔI VAI TRÒ
    // ============================
    public function changeRole(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?c=admin&a=users');
        }
        csrf_check();

        $userId = (int)($_POST['user_id'] ?? 0);
        $role   = in_array($_POST['role'] ?? '', ['admin', 'user']) ? $_POST['role'] : 'user';

        if ($userId === (int)$_SESSION['user']['id']) {
            $_SESSION['flash_error'] = 'Không thể thay đổi vai trò của chính mình.';
            redirect('index.php?c=admin&a=users');
        }

        $this->userModel->setRole($userId, $role);
        $_SESSION['flash_success'] = 'Đã cập nhật vai trò người dùng.';
        redirect('index.php?c=admin&a=users');
    }

    // ============================
    // XÓA TÀI KHOẢN
    // ============================
    public function deleteUser(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?c=admin&a=users');
        }
        csrf_check();

        $userId = (int)($_POST['user_id'] ?? 0);

        if ($userId === (int)$_SESSION['user']['id']) {
            $_SESSION['flash_error'] = 'Không thể xóa tài khoản của chính mình.';
            redirect('index.php?c=admin&a=users');
        }

        $this->userModel->delete($userId);
        $_SESSION['flash_success'] = 'Đã xóa tài khoản người dùng.';
        redirect('index.php?c=admin&a=users');
    }
}

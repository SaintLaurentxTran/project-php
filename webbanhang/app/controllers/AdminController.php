<?php
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/OrderModel.php';
require_once __DIR__ . '/../core/helpers.php';

class AdminController
{
    private UserModel $userModel;
    private OrderModel $orderModel;

    public function __construct(private PDO $pdo)
    {
        $this->userModel = new UserModel($pdo);
        $this->orderModel = new OrderModel($pdo);
        $this->requireAdmin();
    }

    private function requireAdmin(): void
    {
        if (empty($_SESSION['user'])) {
            $_SESSION['flash_error'] = 'Vui lòng đăng nhập.';
            redirect(url('auth', 'login'));
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

    public function orders(): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $search = trim($_GET['q'] ?? '');
        $status = trim($_GET['status'] ?? '');
        $allowedStatuses = ['', 'pending', 'confirmed', 'shipping', 'completed', 'canceled'];
        if (!in_array($status, $allowedStatuses, true)) {
            $status = '';
        }

        $result = $this->orderModel->paginate($page, 15, $search, $status);
        $stats = $this->orderModel->adminStats();
        $statusLabels = $this->orderStatusLabels();
        $pageTitle = 'Quan Ly Don Hang';
        require __DIR__ . '/../views/admin/orders.php';
    }

    public function orderDetail(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $order = $this->orderModel->findWithUser($id);
        if (!$order) {
            http_response_code(404);
            exit('Order not found');
        }

        $items = $this->orderModel->items($id);
        $statusLabels = $this->orderStatusLabels();
        $pageTitle = 'Chi Tiet Don Hang';
        require __DIR__ . '/../views/admin/order_detail.php';
    }

    public function updateOrderStatus(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(url('admin', 'orders'));
        }
        csrf_check();

        $id = (int)($_POST['order_id'] ?? 0);
        $status = $_POST['status'] ?? 'pending';

        try {
            $this->orderModel->updateStatus($id, $status);
            $_SESSION['flash_success'] = 'Đã cập nhật trạng thái đơn hàng.';
        } catch (Throwable $e) {
            $_SESSION['flash_error'] = 'Không thể cập nhật trạng thái đơn hàng.';
        }

        $back = $_POST['back'] ?? url('admin', 'orders');
        redirect($back);
    }

    private function orderStatusLabels(): array
    {
        return [
            'pending' => 'Chờ xử lý',
            'confirmed' => 'Đã xác nhận',
            'shipping' => 'Đang giao',
            'completed' => 'Hoàn thành',
            'canceled' => 'Đã hủy',
        ];
    }

    // ============================
    // KHÓA / MỞ KHÓA TÀI KHOẢN
    // ============================
    public function toggleActive(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(url('admin', 'users'));
        }
        csrf_check();

        $userId = (int)($_POST['user_id'] ?? 0);
        $status = (int)($_POST['status'] ?? 0); // 0 = khóa, 1 = mở

        // Không cho khóa chính mình
        if ($userId === (int)$_SESSION['user']['id']) {
            $_SESSION['flash_error'] = 'Không thể tự khóa tài khoản của mình.';
            redirect(url('admin', 'users'));
        }

        $this->userModel->setActive($userId, $status);
        $msg = $status ? 'Đã mở khóa tài khoản.' : 'Đã khóa tài khoản.';
        $_SESSION['flash_success'] = $msg;
        redirect(url('admin', 'users'));
    }

    // ============================
    // THAY ĐỔI VAI TRÒ
    // ============================
    public function changeRole(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(url('admin', 'users'));
        }
        csrf_check();

        $userId = (int)($_POST['user_id'] ?? 0);
        $role   = in_array($_POST['role'] ?? '', ['admin', 'user']) ? $_POST['role'] : 'user';

        if ($userId === (int)$_SESSION['user']['id']) {
            $_SESSION['flash_error'] = 'Không thể thay đổi vai trò của chính mình.';
            redirect(url('admin', 'users'));
        }

        $this->userModel->setRole($userId, $role);
        $_SESSION['flash_success'] = 'Đã cập nhật vai trò người dùng.';
        redirect(url('admin', 'users'));
    }

    public function verifyUserEmail(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(url('admin', 'users'));
        }
        csrf_check();

        $userId = (int)($_POST['user_id'] ?? 0);
        $this->userModel->verifyEmail($userId);
        $this->userModel->markEmailVerificationUsedByUserId($userId);

        $_SESSION['flash_success'] = 'Đã xác thực email người dùng.';
        redirect(url('admin', 'users'));
    }

    public function unverifyUserEmail(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(url('admin', 'users'));
        }
        csrf_check();

        $userId = (int)($_POST['user_id'] ?? 0);
        $this->userModel->unverifyEmail($userId);

        $_SESSION['flash_success'] = 'Đã bỏ xác thực email người dùng.';
        redirect(url('admin', 'users'));
    }

    // ============================
    // XÓA TÀI KHOẢN
    // ============================
    public function deleteUser(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(url('admin', 'users'));
        }
        csrf_check();

        $userId = (int)($_POST['user_id'] ?? 0);

        if ($userId === (int)$_SESSION['user']['id']) {
            $_SESSION['flash_error'] = 'Không thể xóa tài khoản của chính mình.';
            redirect(url('admin', 'users'));
        }

        $this->userModel->delete($userId);
        $_SESSION['flash_success'] = 'Đã xóa tài khoản người dùng.';
        redirect(url('admin', 'users'));
    }
}

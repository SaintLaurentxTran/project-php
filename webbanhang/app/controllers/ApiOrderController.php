<?php
require_once __DIR__ . '/../models/OrderModel.php';
require_once __DIR__ . '/../core/JwtMiddleware.php'; // Đảm bảo import Middleware

class ApiOrderController {
    private OrderModel $orderModel;

    public function __construct(private PDO $pdo) {
        $this->orderModel = new OrderModel($this->pdo);
    }

    private function jsonResponse(bool $success, string $message, int $statusCode = 200, array $data = []): void {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data'    => $data
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * 1. API ĐẶT HÀNG (POST /api/orders)
     */
    public function create(): void {
        // 🔥 VÁ LỖ HỔNG 1: Lấy User trực tiếp từ mã Token, không dùng $_SESSION của Web truyền thống
        $currentUser = JwtMiddleware::getAuthenticatedUser();

        if (empty($_SESSION['cart'])) {
            require_once __DIR__ . '/../models/CartModel.php';
            $cartItems = (new CartModel($this->pdo))->getByUserId((int)$currentUser['id']);
            $_SESSION['cart'] = [];
            foreach ($cartItems as $item) {
                $_SESSION['cart'][(int)$item['id']] = [
                    'id' => (int)$item['id'],
                    'name' => $item['name'],
                    'price' => (int)$item['price'],
                    'image' => $item['image'],
                    'qty' => (int)$item['qty'],
                    'city' => $item['city'] ?? ''
                ];
            }
        }

        if (empty($_SESSION['cart'])) {
            $this->jsonResponse(false, 'Không thể đặt hàng vì giỏ hàng của bạn đang trống.', 400);
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $customer = [
            'user_id' => (int)$currentUser['id'], // Sử dụng ID bóc tách an toàn từ Token
            'name'    => $input['customer_name'] ?? ($currentUser['name'] ?? ''),
            'phone'   => $input['customer_phone'] ?? '',
            'address' => $input['customer_address'] ?? ''
        ];

        if (empty($customer['name']) || empty($customer['phone']) || empty($customer['address'])) {
            $this->jsonResponse(false, 'Vui lòng điền đầy đủ tên, số điện thoại và địa chỉ giao hàng.', 400);
        }

        $shippingFee  = isset($input['shipping_fee']) ? (int)$input['shipping_fee'] : 0;
        $voucher      = isset($input['voucher_amount']) ? (int)$input['voucher_amount'] : 0;
        $coinsUsed    = isset($input['coins_used']) ? (int)$input['coins_used'] : 0;
        $paymentMethod= $input['payment_method'] ?? 'COD';

        try {
            $orderId = $this->orderModel->createOrder(
                $customer,
                $_SESSION['cart'],
                $shippingFee,
                $voucher,
                $coinsUsed,
                $paymentMethod
            );

            // Làm trống giỏ hàng
            $userId = (int)$currentUser['id'];
            require_once __DIR__ . '/../models/CartModel.php';
            (new CartModel($this->pdo))->clearCart($userId);
            $_SESSION['cart'] = []; 

            $this->jsonResponse(true, 'Đặt hàng thành công!', 201, ['order_id' => $orderId]);
        } catch (Throwable $e) {
            $this->jsonResponse(false, 'Đặt hàng thất bại: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 2. API XEM DANH SÁCH ĐƠN HÀNG (GET /api/orders)
     * 🔥 VÁ LỖ HỔNG IDOR: Phân luồng dữ liệu Admin / User dựa vào Token
     */
    public function list(): void {
        $currentUser = JwtMiddleware::getAuthenticatedUser(); // Kiểm tra đăng nhập qua Token

        $page    = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['perPage']) ? (int)$_GET['perPage'] : 10;
        $search  = $_GET['search'] ?? '';
        $status  = $_GET['status'] ?? '';

        // Phân quyền tải dữ liệu: Nếu là admin -> truyền null (lấy hết), nếu là user -> truyền ID của họ
        $userIdFilter = (($currentUser['role'] ?? '') === 'admin') ? null : (int)$currentUser['id'];

        // Gọi hàm paginate đã sửa đổi ở bước trước
        $data = $this->orderModel->paginate($page, $perPage, $search, $status, $userIdFilter);
        
        $this->jsonResponse(true, 'Tải danh sách đơn hàng thành công.', 200, $data);
    }

    /**
     * 3. API XEM CHI TIẾT ĐƠN HÀNG (GET /api/orders/detail)
     */
    public function show(): void {
        // 🔥 VÁ LỖ HỔNG: Lấy user trực tiếp từ Token chứ không lấy qua $_GET['current_user'] nhằm tránh hacker sửa đổi URL trên Postman
        $currentUser = JwtMiddleware::getAuthenticatedUser(); 
        
        $orderId = $_GET['id'] ?? null;
        $order = $this->orderModel->findWithUser((int)$orderId);

        if (!$order) {
            $this->jsonResponse(false, 'Đơn hàng không tồn tại.', 404);
        }

        // Kiểm tra phân quyền: Không cho phép User thường xem đơn hàng của người khác
        if (($currentUser['role'] ?? '') !== 'admin' && (int)$order['user_id'] !== (int)$currentUser['id']) {
            $this->jsonResponse(false, 'Forbidden: Bạn không có quyền xem đơn hàng của người dùng khác.', 403);
        }

        $this->jsonResponse(true, 'Tải chi tiết đơn hàng thành công.', 200, ['order' => $order]);
    }

    /**
     * 4. API HỦY ĐƠN HÀNG (POST /api/orders/cancel)
     */
    public function cancel(): void {
        $currentUser = JwtMiddleware::getAuthenticatedUser(); // Xác thực Token người dùng

        $input   = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $orderId = isset($input['order_id']) ? (int)$input['order_id'] : 0;
        $order   = $this->orderModel->find($orderId);

        if (!$order) {
            $this->jsonResponse(false, 'Đơn hàng không tồn tại.', 404);
        }

        // 🔥 VÁ LỖ HỔNG: Không cho phép User thường thực hiện bấm hủy đơn hàng của người khác
        if (($currentUser['role'] ?? '') !== 'admin' && (int)$order['user_id'] !== (int)$currentUser['id']) {
            $this->jsonResponse(false, 'Forbidden: Bạn không có quyền hủy đơn hàng của người khác.', 403);
        }

        if ($order['status'] !== 'pending') {
            $this->jsonResponse(false, 'Đơn hàng đã được xử lý hoặc vận chuyển, không thể hủy.', 400);
        }

        $this->orderModel->updateStatus($orderId, 'canceled');
        $this->jsonResponse(true, 'Hủy đơn hàng thành công.', 200, ['order_id' => $orderId, 'status' => 'canceled']);
    }

    /**
     * 5. API CẬP NHẬT TRẠNG THÁI ĐƠN HÀNG (PUT /api/orders) - CHỈ DÀNH CHO ADMIN
     */
    public function updateStatus(): void {
        // 🔥 VÁ LỖ HỔNG CHẶN QUYỀN: Bắt buộc phải có quyền Admin mới cho chạy qua rào cản này
        JwtMiddleware::requireAdmin(); 

        $input   = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $orderId = isset($input['order_id']) ? (int)$input['order_id'] : 0;
        $status  = $input['status'] ?? '';

        $order = $this->orderModel->find($orderId);
        if (!$order) {
            $this->jsonResponse(false, 'Đơn hàng không tồn tại.', 404);
        }

        try {
            $this->orderModel->updateStatus($orderId, $status);
            $this->jsonResponse(true, 'Cập nhật trạng thái đơn hàng thành công.', 200, [
                'order_id' => $orderId,
                'status'   => $status
            ]);
        } catch (InvalidArgumentException $e) {
            $this->jsonResponse(false, $e->getMessage(), 400);
        }
    }
}

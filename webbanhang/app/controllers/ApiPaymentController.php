<?php
require_once __DIR__ . '/../models/OrderModel.php';

class ApiPaymentController {
    private OrderModel $orderModel;

    public function __construct(private PDO $pdo) {
        $this->orderModel = new OrderModel($this->pdo);
    }

    /**
     * Helper gửi phản hồi JSON nhanh
     */
    private function jsonResponse(bool $success, string $message, array $data = [], int $statusCode = 200): void {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
        echo json_encode(array_merge([
            'success' => $success,
            'message' => $message
        ], $data), JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * API 1: Xử lý Tạo / Thực hiện thanh toán cho Đơn hàng
     * ROUTE: POST /api/payment
     */
    public function processPayment() {
        // Đọc dữ liệu JSON từ Body gửi lên
        $input = json_decode(file_get_contents('php://input'), true);
        
        $orderId = isset($input['order_id']) ? (int)$input['order_id'] : 0;
        $paymentMethod = trim($input['payment_method'] ?? '');

        if ($orderId <= 0 || empty($paymentMethod)) {
            $this->jsonResponse(false, 'Thiếu thông tin đơn hàng hoặc phương thức thanh toán.', [], 400);
        }

        // Tìm kiếm đơn hàng
        $order = $this->orderModel->find($orderId);
        if (!$order) {
            $this->jsonResponse(false, 'Đơn hàng không tồn tại.', [], 404);
        }

        // NGHIỆP VỤ: Không cho thanh toán lại đơn hàng đã thanh toán
        $currentPaymentStatus = $order['payment_status'] ?? 'unpaid';
        if ($currentPaymentStatus === 'paid') {
            $this->jsonResponse(false, 'Đơn hàng này đã được thanh toán trước đó. Không thể thanh toán lại!', [], 400);
        }

        // Xử lý logic theo từng Phương thức thanh toán
        switch ($paymentMethod) {
            case 'COD': // Thanh toán khi nhận hàng
                // Đơn COD thì trạng thái thanh toán vẫn là unpaid, cập nhật phương thức là COD và trạng thái 'confirmed'
                $this->orderModel->updateOrderAndPaymentStatus($orderId, 'confirmed', 'unpaid', 'COD');
                
                $this->jsonResponse(true, 'Đặt hàng COD thành công! Đơn hàng đã được xác nhận.', [
                    'order_id' => $orderId,
                    'payment_method' => 'COD',
                    'order_status' => 'confirmed',
                    'payment_status' => 'unpaid'
                ]);
                break;

            case 'BANK_TRANSFER': // Chuyển khoản ngân hàng (Mô phỏng)
            case 'E_WALLET':     // Ví điện tử (Mô phỏng)
                // Cập nhật đơn hàng sang "confirmed", trạng thái thanh toán "paid" và đồng bộ phương thức mới
                $this->orderModel->updateOrderAndPaymentStatus($orderId, 'confirmed', 'paid', $paymentMethod);

                $this->jsonResponse(true, 'Thanh toán trực tuyến mô phỏng thành công!', [
                    'order_id' => $orderId,
                    'payment_method' => $paymentMethod,
                    'order_status' => 'confirmed',
                    'payment_status' => 'paid',
                    'transaction_code' => 'TXMOCK' . time() . rand(10, 99)
                ]);
                break;

            default:
                $this->jsonResponse(false, 'Phương thức thanh toán không được hỗ trợ. Chọn (COD, BANK_TRANSFER, E_WALLET)', [], 400);
                break;
        }
    }

    /**
     * API 2: Cập nhật thủ công trạng thái thanh toán của Đơn hàng (Thường dành cho Admin)
     * ROUTE: PUT /api/payment
     */
    public function updateStatus() {
        $input = json_decode(file_get_contents('php://input'), true);

        $orderId = isset($input['order_id']) ? (int)$input['order_id'] : 0;
        $paymentStatus = trim($input['payment_status'] ?? ''); // 'paid' hoặc 'unpaid'

        if ($orderId <= 0 || empty($paymentStatus)) {
            $this->jsonResponse(false, 'Thiếu dữ liệu order_id hoặc payment_status', [], 400);
        }

        if (!in_array($paymentStatus, ['paid', 'unpaid'])) {
            $this->jsonResponse(false, 'Trạng thái thanh toán không hợp lệ (Chỉ nhận: paid, unpaid)', [], 400);
        }

        $order = $this->orderModel->find($orderId);
        if (!$order) {
            $this->jsonResponse(false, 'Đơn hàng không tồn tại.', [], 404);
        }

        // Thực hiện cập nhật trạng thái thanh toán và giữ lại phương thức cũ hoặc cập nhật theo yêu cầu
        $this->orderModel->updatePaymentStatus($orderId, $paymentStatus);

        $this->jsonResponse(true, 'Cập nhật trạng thái thanh toán thành công!', [
            'order_id' => $orderId,
            'payment_status' => $paymentStatus
        ]);
    }
}
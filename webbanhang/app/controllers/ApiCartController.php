<?php
require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../models/CartModel.php';
require_once __DIR__ . '/../core/JwtMiddleware.php';

class ApiCartController {
    private ProductModel $productModel;
    private CartModel $cartModel;

    public function __construct(private PDO $pdo) {
        $this->productModel = new ProductModel($this->pdo);
        $this->cartModel = new CartModel($this->pdo);
    }

    /**
     * Trả về phản hồi JSON chuẩn hóa và dừng kịch bản
     */
    private function jsonResponse(bool $success, string $message, array $data = [], int $statusCode = 200): void {
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
     * Trích xuất thông tin người dùng đang đăng nhập thông qua Session
     */
    private function getLoggedInUserId(): ?int {
        if (!empty($_SESSION['user']['id'])) {
            return (int)$_SESSION['user']['id'];
        }

        $currentUser = JwtMiddleware::getAuthenticatedUser();
        if (empty($currentUser['id'])) {
            return null;
        }

        $_SESSION['user'] = [
            'id' => (int)$currentUser['id'],
            'email' => $currentUser['email'] ?? '',
            'role' => $currentUser['role'] ?? 'user',
            'name' => $currentUser['name'] ?? ''
        ];

        $_SESSION['cart'] = [];
        foreach ($this->cartModel->getByUserId((int)$currentUser['id']) as $item) {
            $_SESSION['cart'][(int)$item['id']] = [
                'id' => (int)$item['id'],
                'name' => $item['name'],
                'price' => (int)$item['price'],
                'image' => $item['image'],
                'qty' => (int)$item['qty'],
                'city' => $item['city'] ?? ''
            ];
        }

        return (int)$currentUser['id'];
    }

    /**
     * API 1 & 6: XEM GIỎ HÀNG & TÍNH TỔNG TIỀN (GET /api/cart)
     */
    public function index(): void {
        $userId = $this->getLoggedInUserId();
        $items = [];

        if ($userId) {
            // Lấy dữ liệu thuần từ database
            $items = $this->cartModel->getByUserId($userId);
        } else {
            // Lấy dữ liệu từ session đối với khách vãng lai
            $sessionCart = $_SESSION['cart'] ?? [];
            foreach ($sessionCart as $item) {
                $items[] = [
                    'id'    => (int)$item['id'],
                    'name'  => $item['name'],
                    'price' => (int)$item['price'],
                    'image' => $item['image'],
                    'qty'   => (int)$item['qty'],
                    'city'  => $item['city']
                ];
            }
        }

        // Tính toán tổng số lượng và tổng số tiền của giỏ hàng
        $totalItems = 0;
        $totalAmount = 0;

        foreach ($items as $item) {
            $totalItems  += (int)$item['qty'];
            $totalAmount += (int)$item['price'] * (int)$item['qty'];
        }

        $this->jsonResponse(true, 'Tải dữ liệu giỏ hàng thành công', [
            'items'        => $items,
            'total_items'  => $totalItems,
            'total_amount' => $totalAmount
        ], 200);
    }

    /**
     * API 2: THÊM SẢN PHẨM VÀO GIỎ HÀNG (POST /api/cart)
     */
    public function add(): void {
        // Hỗ trợ cả dữ liệu raw JSON và x-www-form-urlencoded
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $productId = (int)($input['product_id'] ?? 0);
        $qty       = (int)($input['qty'] ?? 1);

        // RÀO CẢN 1: Kiểm tra số lượng hợp lệ
        if ($qty <= 0) {
            $this->jsonResponse(false, 'Số lượng sản phẩm thêm vào giỏ phải lớn hơn 0', [], 400);
        }

        // RÀO CẢN 2: Kiểm tra sản phẩm có tồn tại hay không
        $product = $this->productModel->find($productId);
        if (!$product) {
            $this->jsonResponse(false, 'Sản phẩm không tồn tại trên hệ thống', [], 404);
        }

        $userId = $this->getLoggedInUserId();

        if ($userId) {
            // Lưu dữ liệu vào database nếu đã đăng nhập
            $this->cartModel->addOrIncrement($userId, $productId, $qty);
        } else {
            // Lưu dữ liệu tạm vào session nếu chưa đăng nhập
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }

            if (!isset($_SESSION['cart'][$productId])) {
                $_SESSION['cart'][$productId] = [
                    'id'    => (int)$product['id'],
                    'name'  => $product['name'],
                    'price' => (int)$product['price'],
                    'image' => $product['thumb_url'],
                    'qty'   => 0,
                    'city'  => $product['city']
                ];
            }
            $_SESSION['cart'][$productId]['qty'] += $qty;
        }

        $this->jsonResponse(true, 'Đã thêm sản phẩm vào giỏ hàng thành công', [], 201);
    }

    /**
     * API: CẬP NHẬT SỐ LƯỢNG SẢN PHẨM TRONG GIỎ HÀNG (PUT /api/cart)
     */
    public function updateQty(): void {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $productId = isset($input['product_id']) ? (int)$input['product_id'] : 0;
        $qty       = isset($input['qty']) ? (int)$input['qty'] : 0;

        // 1. Rào cản số lượng
        if ($qty <= 0) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Số lượng cập nhật phải lớn hơn 0'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // 2. Rào cản kiểm tra sản phẩm tồn tại trên hệ thống
        $product = $this->productModel->find($productId);
        if (!$product) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Sản phẩm không tồn tại trên hệ thống'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // 3. RÀO CẢN QUYẾT ĐỊNH: Kiểm tra sản phẩm có trong GIỎ HÀNG hiện tại không
        // Vì index.php đã đồng bộ DB sang $_SESSION['cart'] ở đầu trang, ta có thể check chung qua Session:
        if (!isset($_SESSION['cart'][$productId])) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(422); // 422 Unprocessable Entity hoặc 404
            echo json_encode([
                'success' => false,
                'message' => 'Sản phẩm này hiện không có trong giỏ hàng của bạn, không thể cập nhật số lượng.'
            ], JSON_UNESCAPED_UNICODE);
            exit; // 🌟 Bắt buộc phải dừng chương trình tại đây!
        }

        // 4. Nếu vượt qua tất cả các rào cản trên -> Tiến hành cập nhật dữ liệu
        $userId = $_SESSION['user']['id'] ?? null;

        if ($userId) {
            // Cập nhật Database
            $this->cartModel->updateQuantity($userId, $productId, $qty);
        }
        
        // Luôn luôn cập nhật Session để đảm bảo dữ liệu đồng bộ tức thì
        $_SESSION['cart'][$productId]['qty'] = $qty;

        // 5. Trả về phản hồi thành công đích thực
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Cập nhật số lượng sản phẩm thành công',
            'data'    => [
                'product_id' => $productId,
                'new_qty'    => $qty
            ]
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * API: XÓA MỘT SẢN PHẨM KHỎI GIỎ HÀNG (DELETE /api/cart/{id})
     */
    public function remove(): void {
        // Lấy ID sản phẩm đã được file index.php bóc tách tự động đưa vào $_GET['id']
        $productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        // 1. Kiểm tra mã ID truyền vào có hợp lệ hay không
        if ($productId <= 0) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(400); // Bad Request
            echo json_encode([
                'success' => false,
                'message' => 'Mã sản phẩm không hợp lệ',
                'data'    => []
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // 2. 🔥 RÀO CẢN QUYẾT ĐỊNH: Kiểm tra sản phẩm có thực sự tồn tại trong giỏ hàng hiện tại không
        if (!isset($_SESSION['cart'][$productId])) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(404); // Not Found
            echo json_encode([
                'success' => false,
                'message' => 'Sản phẩm này không có trong giỏ hàng của bạn, không thể xóa.',
                'data'    => []
            ], JSON_UNESCAPED_UNICODE);
            exit; // 🌟 Bắt buộc dừng tại đây nếu sản phẩm không có trong giỏ!
        }

        // 3. Nếu tìm thấy sản phẩm trong giỏ -> Tiến hành xóa dữ liệu
        $userId = $_SESSION['user']['id'] ?? null;

        if ($userId) {
            // Xóa dòng tương ứng trong Database của thành viên
            $this->cartModel->removeProduct($userId, $productId);
        }

        // Luôn luôn xóa trong bộ nhớ tạm Session để đồng bộ giao diện hiển thị
        unset($_SESSION['cart'][$productId]);

        // 4. Phản hồi thành công đích thực
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(200); // OK
        echo json_encode([
            'success' => true,
            'message' => 'Đã loại bỏ sản phẩm khỏi giỏ hàng thành công.',
            'data'    => [
                'removed_product_id' => $productId
            ]
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * API: XÓA TOÀN BỘ GIỎ HÀNG (DELETE /api/cart/clear)
     */
    public function clear(): void {
        // 1. Kiểm tra giỏ hàng hiện tại có sản phẩm nào không trước khi xóa
        if (empty($_SESSION['cart'])) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(400); // Bad Request
            echo json_encode([
                'success' => false,
                'message' => 'Giỏ hàng của bạn đã trống sẵn rồi, không cần dọn dẹp thêm.',
                'data'    => []
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $userId = $_SESSION['user']['id'] ?? null;

        if ($userId) {
            // NẾU ĐÃ ĐĂNG NHẬP: Xóa sạch toàn bộ các dòng sản phẩm của User này trong Database
            $this->cartModel->clearCart($userId);
        }

        // Luôn luôn làm rỗng mảng Session giỏ hàng để đồng bộ bộ nhớ tạm
        $_SESSION['cart'] = [];

        // 2. Phản hồi kết quả xóa sạch thành công
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(200); // OK
        echo json_encode([
            'success' => true,
            'message' => 'Đã xóa toàn bộ sản phẩm khỏi giỏ hàng thành công.',
            'data'    => [
                'items' => [],
                'total_items' => 0,
                'total_amount' => 0
            ]
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * API: TÍNH TỔNG TIỀN VÀ SỐ LƯỢNG GIỎ HÀNG (GET /api/cart/total)
     */
    public function total(): void {
        $totalItems = 0;
        $totalAmount = 0;

        // Tính toán dựa trên mảng giỏ hàng Session hiện tại (đã được index.php đồng bộ tự động)
        if (!empty($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $item) {
                $qty = (int)$item['qty'];
                $price = (int)$item['price'];
                
                $totalItems += $qty;
                $totalAmount += ($price * $qty);
            }
        }

        // Trả về cấu trúc JSON gọn nhẹ, tối ưu tốc độ xử lý cho Frontend
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(200); // OK
        echo json_encode([
            'success' => true,
            'message' => 'Tính toán tổng tiền giỏ hàng thành công.',
            'data'    => [
                'total_items'  => $totalItems,
                'total_amount' => $totalAmount,
                'currency'     => 'VND'
            ]
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

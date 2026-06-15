<?php
require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../models/CartModel.php'; // 🌟 Đảm bảo đã require CartModel

class CartController {
    private ProductModel $productModel;
    private CartModel $cartModel;

    public function __construct(private PDO $pdo) {
        $this->productModel = new ProductModel($this->pdo);
        $this->cartModel = new CartModel($this->pdo); // Khởi tạo CartModel ở đây
    }

    private function &cartRef(): array {
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
        return $_SESSION['cart'];
    }

    // Lấy ID người dùng nếu họ đã đăng nhập thành công
    private function getLoggedInUserId(): ?int {
        return $_SESSION['user']['id'] ?? null;
    }

    /**
     * 1. XEM GIỎ HÀNG TRÊN WEB
     */
    public function index() {
        $pageTitle = "ShopeeFake - Giỏ hàng";
        $userId = $this->getLoggedInUserId();

        // 🌟 Nếu đã đăng nhập: Lấy giỏ hàng từ DATABASE đè lên Session để View hiển thị
        if ($userId) {
            $cartItems = $this->cartModel->getByUserId($userId);
            
            // Format lại mảng để giữ nguyên cấu trúc cũ cho file View index.php không bị lỗi
            $_SESSION['cart'] = [];
            foreach ($cartItems as $item) {
                $_SESSION['cart'][$item['id']] = [
                    'id'    => $item['id'],
                    'name'  => $item['name'],
                    'price' => $item['price'],
                    'image' => $item['image'],
                    'qty'   => $item['qty'],
                    'city'  => $item['city']
                ];
            }
        }

        require __DIR__ . '/../views/cart/index.php';
    }

    /**
     * 2. THÊM SẢN PHẨM VÀO GIỎ HÀNG
     */
    public function add() {
        $id = (int)($_POST['id'] ?? 0);
        $qty = max(1, (int)($_POST['qty'] ?? 1));

        $p = $this->productModel->find($id);
        if (!$p) {
            http_response_code(404);
            exit("Product not found");
        }

        $userId = $this->getLoggedInUserId();

        if ($userId) {
            // 🌟 NẾU ĐÃ ĐĂNG NHẬP: Ghi trực tiếp xuống DATABASE bảng cart
            $this->cartModel->addOrIncrement($userId, $id, $qty);
        } else {
            // NẾU CHƯA ĐĂNG NHẬP (KHÁCH): Lưu tạm vào Session như cũ
            $cart = &$this->cartRef();
            if (!isset($cart[$id])) {
                $cart[$id] = [
                    'id' => $p['id'], 'name' => $p['name'], 'price' => (int)$p['price'],
                    'image' => $p['thumb_url'], 'qty' => 0, 'city' => $p['city']
                ];
            }
            $cart[$id]['qty'] += $qty;
        }

        redirect(url('cart', 'index'));
    }

    /**
     * 3. CẬP NHẬT SỐ LƯỢNG SẢN PHẨM
     */
    public function updateQty() {
        $id = (int)($_POST['id'] ?? 0);
        $qty = max(1, (int)($_POST['qty'] ?? 1));
        
        $userId = $this->getLoggedInUserId();

        if ($userId) {
            // 🌟 Cập nhật số lượng dưới DB
            $this->cartModel->updateQuantity($userId, $id, $qty);
        } else {
            $cart = &$this->cartRef();
            if (isset($cart[$id])) $cart[$id]['qty'] = $qty;
        }

        redirect(url('cart', 'index'));
    }

    /**
     * 4. XÓA MỘT SẢN PHẨM KHỎI GIỎ HÀNG
     */
    public function remove() {
        $id = (int)($_POST['id'] ?? 0);
        $userId = $this->getLoggedInUserId();

        if ($userId) {
            // 🌟 Xóa dòng dữ liệu dưới DB
            $this->cartModel->removeProduct($userId, $id);
        }
        
        // Luôn xóa trong session để đồng bộ
        $cart = &$this->cartRef();
        unset($cart[$id]);

        redirect(url('cart', 'index'));
    }

    /**
     * 5. XÓA TOÀN BỘ GIỎ HÀNG
     */
    public function clear() {
        $userId = $this->getLoggedInUserId();

        if ($userId) {
            // 🌟 Xóa toàn bộ giỏ hàng của user dưới DB
            $this->cartModel->clearCart($userId);
        }

        $_SESSION['cart'] = [];
        redirect(url('cart', 'index'));
    }
}
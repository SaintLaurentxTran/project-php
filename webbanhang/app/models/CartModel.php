<?php
class CartModel {
    public function __construct(private PDO $pdo) {}

    // 1. Lấy danh sách sản phẩm trong giỏ của User từ DB
    public function getByUserId(int $userId): array {
        $st = $this->pdo->prepare("
            SELECT c.product_id AS id, p.name, p.price, p.thumb_url AS image, c.qty, p.city
            FROM cart c
            JOIN products p ON c.product_id = p.id
            WHERE c.user_id = ?
        ");
        $st->execute([$userId]);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    // 2. Thêm hoặc tăng số lượng sản phẩm
    public function addOrIncrement(int $userId, int $productId, int $qty): void {
        $st = $this->pdo->prepare("
            INSERT INTO cart (user_id, product_id, qty, created_at) 
            VALUES (?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE qty = qty + ?
        ");
        $st->execute([$userId, $productId, $qty, $qty]);
    }

    // 3. Cập nhật cứng số lượng sản phẩm (Dùng cho API update Qty)
    public function updateQuantity(int $userId, int $productId, int $qty): void {
        $st = $this->pdo->prepare("UPDATE cart SET qty = ? WHERE user_id = ? AND product_id = ?");
        $st->execute([$qty, $userId, $productId]);
    }

    // 4. Xóa một sản phẩm khỏi giỏ
    public function removeProduct(int $userId, int $productId): void {
        $st = $this->pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        $st->execute([$userId, $productId]);
    }

    // 5. Xóa sạch giỏ hàng của User
    public function clearCart(int $userId): void {
        $st = $this->pdo->prepare("DELETE FROM cart WHERE user_id = ?");
        $st->execute([$userId]);
    }
}
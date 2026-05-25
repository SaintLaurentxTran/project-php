<?php
class OrderModel {
  public function __construct(private PDO $pdo) {}

  public function createOrder(array $customer, array $cart, int $shippingFee, int $voucher, int $coinsUsed, string $paymentMethod): int {
    $this->pdo->beginTransaction();
    try {
      $totalItems = 0;
      foreach ($cart as $row) {
        $totalItems += $row['price'] * $row['qty'];
      }
      $total = $totalItems + $shippingFee - $voucher - $coinsUsed;

      $st = $this->pdo->prepare("
        INSERT INTO orders(order_code, customer_name, customer_phone, customer_address, payment_method, shipping_fee, voucher_amount, coins_used, total_amount, created_at)
        VALUES(?,?,?,?,?,?,?,?,?,NOW())
      ");
      $orderCode = 'SF' . time() . rand(100,999);
      $st->execute([
        $orderCode,
        $customer['name'],
        $customer['phone'],
        $customer['address'],
        $paymentMethod,
        $shippingFee,
        $voucher,
        $coinsUsed,
        $total
      ]);
      $orderId = (int)$this->pdo->lastInsertId();

      $itemSt = $this->pdo->prepare("
        INSERT INTO order_items(order_id, product_id, product_name, price, qty)
        VALUES(?,?,?,?,?)
      ");

      $stockSt = $this->pdo->prepare("UPDATE products SET stock = GREATEST(stock - ?, 0) WHERE id=?");

      foreach ($cart as $row) {
        $itemSt->execute([$orderId, $row['id'], $row['name'], $row['price'], $row['qty']]);
        $stockSt->execute([$row['qty'], $row['id']]);
      }

      $this->pdo->commit();
      return $orderId;
    } catch (Throwable $e) {
      $this->pdo->rollBack();
      throw $e;
    }
  }

  public function find(int $id): ?array {
    $st = $this->pdo->prepare("SELECT * FROM orders WHERE id=?");
    $st->execute([$id]);
    $row = $st->fetch();
    return $row ?: null;
  }

  public function items(int $orderId): array {
    $st = $this->pdo->prepare("SELECT * FROM order_items WHERE order_id=?");
    $st->execute([$orderId]);
    return $st->fetchAll();
  }
}
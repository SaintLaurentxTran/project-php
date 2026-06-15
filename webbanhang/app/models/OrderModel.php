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
        INSERT INTO orders(user_id, order_code, customer_name, customer_phone, customer_address, payment_method, shipping_fee, voucher_amount, coins_used, total_amount, status, created_at, updated_at)
        VALUES(?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())
      ");
      $orderCode = 'SF' . time() . rand(100,999);
      $st->execute([
        $customer['user_id'] ?? null,
        $orderCode,
        $customer['name'],
        $customer['phone'],
        $customer['address'],
        $paymentMethod,
        $shippingFee,
        $voucher,
        $coinsUsed,
        $total,
        'pending'
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

  public function paginate(int $page = 1, int $perPage = 15, string $search = '', string $status = '', ?int $userId = null): array {
    $page = max(1, $page);
    $offset = ($page - 1) * $perPage;
    $where = [];
    $params = [];

    if ($search !== '') {
      $where[] = "(o.order_code LIKE ? OR o.customer_name LIKE ? OR o.customer_phone LIKE ?)";
      $like = '%' . $search . '%';
      array_push($params, $like, $like, $like);
    }

    if ($status !== '') {
      $where[] = "o.status = ?";
      $params[] = $status;
    }

    // 🔥 THÊM ĐIỀU KIỆN LỌC THEO USER ID NẾU CÓ TRUYỀN VÀO (Dành cho tài khoản quyền 'user')
    if ($userId !== null) {
      $where[] = "o.user_id = ?";
      $params[] = $userId;
    }

    $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $countSt = $this->pdo->prepare("SELECT COUNT(*) FROM orders o {$whereSql}");
    $countSt->execute($params);
    $total = (int)$countSt->fetchColumn();

    $st = $this->pdo->prepare("
      SELECT o.*, u.email AS user_email
      FROM orders o
      LEFT JOIN users u ON u.id = o.user_id
      {$whereSql}
      ORDER BY o.created_at DESC, o.id DESC
      LIMIT {$perPage} OFFSET {$offset}
    ");
    $st->execute($params);

    return [
      'orders' => $st->fetchAll(),
      'total' => $total,
      'page' => $page,
      'perPage' => $perPage,
      'totalPages' => max(1, (int)ceil($total / $perPage)),
    ];
  }

  public function adminStats(): array {
    $totalOrders = (int)$this->pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $totalRevenue = (int)$this->pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status <> 'canceled'")->fetchColumn();
    $pendingOrders = (int)$this->pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();

    return [
      'totalOrders' => $totalOrders,
      'totalRevenue' => $totalRevenue,
      'pendingOrders' => $pendingOrders,
    ];
  }

  public function findWithUser(int $id): ?array {
    $st = $this->pdo->prepare("
      SELECT o.*, u.email AS user_email
      FROM orders o
      LEFT JOIN users u ON u.id = o.user_id
      WHERE o.id = ?
    ");
    $st->execute([$id]);
    $row = $st->fetch();
    return $row ?: null;
  }

  public function updateStatus(int $id, string $status): void {
    $allowed = ['pending', 'confirmed', 'shipping', 'completed', 'canceled'];
    if (!in_array($status, $allowed, true)) {
      throw new InvalidArgumentException('Invalid order status.');
    }

    $st = $this->pdo->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
    $st->execute([$status, $id]);
  }

  public function items(int $orderId): array {
    $st = $this->pdo->prepare("SELECT * FROM order_items WHERE order_id=?");
    $st->execute([$orderId]);
    return $st->fetchAll();
  }

  // Cập nhật trạng thái thanh toán của đơn hàng
  public function updatePaymentStatus(int $orderId, string $paymentStatus): void {
      $st = $this->pdo->prepare("UPDATE orders SET payment_status = ?, updated_at = NOW() WHERE id = ?");
      $st->execute([$paymentStatus, $orderId]);
  }

  /**
   * 🔥 SỬA ĐỔI: Hỗ trợ cập nhật cả Trạng thái đơn, Trạng thái thanh toán VÀ Phương thức thanh toán mới
   */
  public function updateOrderAndPaymentStatus(int $orderId, string $orderStatus, string $paymentStatus, string $paymentMethod = ''): void {
      if (!empty($paymentMethod)) {
          // Nếu có truyền phương thức thanh toán mới, thực hiện cập nhật toàn bộ 3 trường
          $st = $this->pdo->prepare("UPDATE orders SET status = ?, payment_status = ?, payment_method = ?, updated_at = NOW() WHERE id = ?");
          $st->execute([$orderStatus, $paymentStatus, $paymentMethod, $orderId]);
      } else {
          // Nếu không truyền phương thức thanh toán, chỉ cập nhật 2 trạng thái cũ
          $st = $this->pdo->prepare("UPDATE orders SET status = ?, payment_status = ?, updated_at = NOW() WHERE id = ?");
          $st->execute([$orderStatus, $paymentStatus, $orderId]);
      }
  }
}
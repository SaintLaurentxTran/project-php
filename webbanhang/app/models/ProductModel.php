<?php
class ProductModel {
  public function __construct(private PDO $pdo) {}

  public function latest(int $limit = 24): array {
    $st = $this->pdo->prepare("
      SELECT p.*, c.name AS category_name
      FROM products p
      JOIN categories c ON c.id = p.category_id
      ORDER BY p.created_at DESC
      LIMIT ?
    ");
    $st->bindValue(1, $limit, PDO::PARAM_INT);
    $st->execute();
    return $st->fetchAll();
  }

  public function flashSale(int $limit = 10): array {
    $st = $this->pdo->prepare("
      SELECT p.*, c.name AS category_name
      FROM products p
      JOIN categories c ON c.id = p.category_id
      WHERE p.is_flash_sale = 1
      ORDER BY p.discount_percent DESC, p.created_at DESC
      LIMIT ?
    ");
    $st->bindValue(1, $limit, PDO::PARAM_INT);
    $st->execute();
    return $st->fetchAll();
  }

  public function paginate(int $page, int $perPage, array $filters = []): array {
    $where = [];
    $params = [];

    if (!empty($filters['q'])) {
      $where[] = "(p.name LIKE ? OR p.description LIKE ?)";
      $params[] = '%' . $filters['q'] . '%';
      $params[] = '%' . $filters['q'] . '%';
    }
    if (!empty($filters['category_id'])) {
      $where[] = "p.category_id = ?";
      $params[] = (int)$filters['category_id'];
    }

    $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

    $countSt = $this->pdo->prepare("SELECT COUNT(*) AS cnt FROM products p {$whereSql}");
    $countSt->execute($params);
    $total = (int)$countSt->fetch()['cnt'];

    $offset = ($page - 1) * $perPage;

    $sql = "
      SELECT p.*, c.name AS category_name
      FROM products p
      JOIN categories c ON c.id = p.category_id
      {$whereSql}
      ORDER BY p.created_at DESC
      LIMIT ? OFFSET ?
    ";
    $st = $this->pdo->prepare($sql);
    
    // Bind các tham số search (nếu có)
    foreach ($params as $index => $value) {
        $st->bindValue($index + 1, $value);
    }
    // Bind tham số phân trang dưới dạng INT
    $st->bindValue(count($params) + 1, $perPage, PDO::PARAM_INT);
    $st->bindValue(count($params) + 2, $offset, PDO::PARAM_INT);
    
    $st->execute();
    $items = $st->fetchAll();

    return [
      'items' => $items,
      'total' => $total,
      'page' => $page,
      'perPage' => $perPage,
      'totalPages' => max(1, (int)ceil($total / $perPage)),
    ];
  }

  public function find(int $id): ?array {
    $st = $this->pdo->prepare("
      SELECT p.*, c.name AS category_name
      FROM products p
      JOIN categories c ON c.id = p.category_id
      WHERE p.id = ?
    ");
    $st->execute([$id]);
    $row = $st->fetch();
    return $row ?: null;
  }

  public function gallery(int $productId): array {
    $st = $this->pdo->prepare("SELECT * FROM product_images WHERE product_id=? ORDER BY sort_order ASC, id ASC");
    $st->execute([$productId]);
    return $st->fetchAll();
  }

  // THÊM: Lưu đường dẫn ảnh phụ vào bảng product_images
  public function addImageToGallery(int $productId, string $imageUrl, int $sortOrder = 0): void {
    $st = $this->pdo->prepare("INSERT INTO product_images(product_id, image_url, sort_order) VALUES(?,?,?)");
    $st->execute([$productId, $imageUrl, $sortOrder]);
  }

  // CẬP NHẬT: Thêm trường thumb_url vào câu lệnh SQL INSERT để lưu ảnh đã upload
  public function create(array $data): int {
    $st = $this->pdo->prepare("
      INSERT INTO products(category_id, name, price, old_price, discount_percent, stock, city, description, is_flash_sale, thumb_url, created_at)
      VALUES(?,?,?,?,?,?,?,?,?,?,NOW())
    ");
    $st->execute([
      (int)$data['category_id'],
      $data['name'],
      (int)$data['price'],
      $data['old_price'] !== '' ? (int)$data['old_price'] : null,
      $data['discount_percent'] !== '' ? (int)$data['discount_percent'] : 0,
      (int)$data['stock'],
      $data['city'],
      $data['description'],
      !empty($data['is_flash_sale']) ? 1 : 0,
      $data['thumb_url'] // Nhận chuỗi đường dẫn cục bộ (ví dụ: uploads/1716...png)
    ]);
    return (int)$this->pdo->lastInsertId();
  }

  // CẬP NHẬT: Thêm trường thumb_url vào câu lệnh SQL UPDATE
  public function update(int $id, array $data): void {
    $st = $this->pdo->prepare("
      UPDATE products
      SET category_id=?, name=?, price=?, old_price=?, discount_percent=?, stock=?, city=?, description=?, is_flash_sale=?, thumb_url=?
      WHERE id=?
    ");
    $st->execute([
      (int)$data['category_id'],
      $data['name'],
      (int)$data['price'],
      $data['old_price'] !== '' ? (int)$data['old_price'] : null,
      $data['discount_percent'] !== '' ? (int)$data['discount_percent'] : 0,
      (int)$data['stock'],
      $data['city'],
      $data['description'],
      !empty($data['is_flash_sale']) ? 1 : 0,
      $data['thumb_url'],
      $id
    ]);
  }

  public function delete(int $id): void {
    $this->pdo->prepare("DELETE FROM product_images WHERE product_id=?")->execute([$id]);
    $this->pdo->prepare("DELETE FROM products WHERE id=?")->execute([$id]);
  }
}
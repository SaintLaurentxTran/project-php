<?php
class ProductModel {
  private PDO $pdo;

  public function __construct() {
    $cfg = require __DIR__ . '/../config/database.php';
    $dsn = "mysql:host={$cfg['host']};dbname={$cfg['dbname']};charset={$cfg['charset']}";
    $this->pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
  }

  public function all(int $limit = 10): array {
    $sql = "SELECT p.*, c.name AS category_name
            FROM products p
            JOIN categories c ON c.id = p.category_id
            ORDER BY p.id DESC
            LIMIT :limit";
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
  }

  public function find(int $id): ?array {
    $stmt = $this->pdo->prepare(
      "SELECT p.*, c.name AS category_name
       FROM products p
       JOIN categories c ON c.id = p.category_id
       WHERE p.id = ?"
    );
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
  }

  public function create(array $data): int {
    $stmt = $this->pdo->prepare(
      "INSERT INTO products(category_id, name, price, description, image)
       VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->execute([
      (int)$data['category_id'],
      trim($data['name']),
      (float)$data['price'],
      $data['description'] ?? null,
      $data['image'] ?? null
    ]);
    return (int)$this->pdo->lastInsertId();
  }

  public function update(int $id, array $data): bool {
    $stmt = $this->pdo->prepare(
      "UPDATE products
       SET category_id = ?, name = ?, price = ?, description = ?, image = ?
       WHERE id = ?"
    );
    return $stmt->execute([
      (int)$data['category_id'],
      trim($data['name']),
      (float)$data['price'],
      $data['description'] ?? null,
      $data['image'] ?? null,
      $id
    ]);
  }

  public function delete(int $id): bool {
    $stmt = $this->pdo->prepare("DELETE FROM products WHERE id = ?");
    return $stmt->execute([$id]);
  }
}
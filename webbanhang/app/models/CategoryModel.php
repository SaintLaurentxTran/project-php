<?php
class CategoryModel {
  public function __construct(private PDO $pdo) {}

  public function all(): array {
    return $this->pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
  }

  public function paginate(int $page, int $perPage, string $search = ''): array {
    $whereSql = '';
    $params = [];

    if ($search !== '') {
      $whereSql = 'WHERE c.name LIKE ? OR c.icon LIKE ?';
      $params[] = '%' . $search . '%';
      $params[] = '%' . $search . '%';
    }

    $countSt = $this->pdo->prepare("SELECT COUNT(*) AS cnt FROM categories c {$whereSql}");
    $countSt->execute($params);
    $total = (int)$countSt->fetch()['cnt'];

    $offset = ($page - 1) * $perPage;
    $st = $this->pdo->prepare("
      SELECT c.*, COUNT(p.id) AS product_count
      FROM categories c
      LEFT JOIN products p ON p.category_id = c.id
      {$whereSql}
      GROUP BY c.id, c.name, c.icon
      ORDER BY c.name ASC
      LIMIT ? OFFSET ?
    ");

    foreach ($params as $index => $value) {
      $st->bindValue($index + 1, $value);
    }
    $st->bindValue(count($params) + 1, $perPage, PDO::PARAM_INT);
    $st->bindValue(count($params) + 2, $offset, PDO::PARAM_INT);
    $st->execute();

    return [
      'items' => $st->fetchAll(),
      'total' => $total,
      'page' => $page,
      'perPage' => $perPage,
      'totalPages' => max(1, (int)ceil($total / $perPage)),
    ];
  }

  public function find(int $id): ?array {
    $st = $this->pdo->prepare("SELECT * FROM categories WHERE id=?");
    $st->execute([$id]);
    $row = $st->fetch();
    return $row ?: null;
  }

  public function create(array $data): int {
    $st = $this->pdo->prepare("INSERT INTO categories(name, icon) VALUES(?, ?)");
    $st->execute([$data['name'], $data['icon'] ?: null]);
    return (int)$this->pdo->lastInsertId();
  }

  public function update(int $id, array $data): void {
    $st = $this->pdo->prepare("UPDATE categories SET name=?, icon=? WHERE id=?");
    $st->execute([$data['name'], $data['icon'] ?: null, $id]);
  }

  public function productCount(int $id): int {
    $st = $this->pdo->prepare("SELECT COUNT(*) AS cnt FROM products WHERE category_id=?");
    $st->execute([$id]);
    return (int)$st->fetch()['cnt'];
  }

  public function delete(int $id): void {
    $st = $this->pdo->prepare("DELETE FROM categories WHERE id=?");
    $st->execute([$id]);
  }
}

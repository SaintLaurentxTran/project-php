<?php
class CategoryModel {
  public function __construct(private PDO $pdo) {}

  public function all(): array {
    return $this->pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
  }

  public function find(int $id): ?array {
    $st = $this->pdo->prepare("SELECT * FROM categories WHERE id=?");
    $st->execute([$id]);
    $row = $st->fetch();
    return $row ?: null;
  }
}
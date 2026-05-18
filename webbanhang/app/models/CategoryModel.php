<?php
class CategoryModel {
  private PDO $pdo;

  public function __construct() {
    $cfg = require __DIR__ . '/../config/database.php';
    $dsn = "mysql:host={$cfg['host']};dbname={$cfg['dbname']};charset={$cfg['charset']}";
    $this->pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
  }

  public function all(): array {
    return $this->pdo->query("SELECT * FROM categories ORDER BY id DESC")->fetchAll();
  }
}
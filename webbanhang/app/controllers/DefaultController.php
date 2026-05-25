<?php
require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../models/CategoryModel.php';

class DefaultController {
  public function __construct(private PDO $pdo) {}

  public function home() {
    $productModel = new ProductModel($this->pdo);
    $catModel = new CategoryModel($this->pdo);

    $flash = $productModel->flashSale(10);
    $latest = $productModel->latest(24);
    $categories = $catModel->all();

    $pageTitle = "ShopeeFake - Trang Chủ";
    require __DIR__ . '/../views/home.php';
  }

  public function search() {
    $productModel = new ProductModel($this->pdo);
    $catModel = new CategoryModel($this->pdo);

    $q = trim($_GET['q'] ?? '');
    $category_id = (int)($_GET['category_id'] ?? 0);
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 20;

    $filters = [
      'q' => $q,
      'category_id' => $category_id ?: null,
    ];

    $result = $productModel->paginate($page, $perPage, $filters);
    $categories = $catModel->all();

    $pageTitle = "ShopeeFake - Tìm kiếm";
    require __DIR__ . '/../views/search.php';
  }
}
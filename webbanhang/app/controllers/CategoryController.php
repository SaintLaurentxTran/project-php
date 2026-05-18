<?php
require_once __DIR__ . '/DefaultController.php';
require_once __DIR__ . '/../models/CategoryModel.php';

class CategoryController extends DefaultController {
  public function index() {
    $model = new CategoryModel();
    $categories = $model->all();
    // Tạm in nhanh, bạn có thể làm view riêng sau
    echo "<pre>"; print_r($categories); echo "</pre>";
  }
}
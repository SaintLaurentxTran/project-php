<?php
require_once __DIR__ . '/DefaultController.php';
require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../models/CategoryModel.php';

class ProductController extends DefaultController {

  private function handleUpload(?array $file): ?string {
    if (!$file || empty($file['name'])) return null;
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) return null;

    // check type cơ bản
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime = mime_content_type($file['tmp_name']);
    if (!isset($allowed[$mime])) {
      exit("File không hợp lệ. Chỉ cho phép JPG/PNG/WEBP.");
    }

    // tên file mới
    $ext = $allowed[$mime];
    $newName = 'p_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

    $uploadDir = __DIR__ . '/../../public/uploads/';
    if (!is_dir($uploadDir)) {
      mkdir($uploadDir, 0777, true);
    }

    $dest = $uploadDir . $newName;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
      exit("Upload thất bại.");
    }

    return $newName; // lưu vào DB
  }

  private function deleteImageFile(?string $imageName): void {
    if (!$imageName) return;
    $path = __DIR__ . '/../../public/uploads/' . $imageName;
    if (is_file($path)) @unlink($path);
  }

  public function index() {
    $model = new ProductModel();
    $products = $model->all(10); // chỉ 10 sản phẩm
    $this->view('product/list', compact('products'));
  }

  public function add() {
    $catModel = new CategoryModel();
    $categories = $catModel->all();
    $this->view('product/add', compact('categories'));
  }

  public function store() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->redirect('index.php?controller=product&action=index');

    $imageName = $this->handleUpload($_FILES['image'] ?? null);

    $data = [
      'category_id' => $_POST['category_id'] ?? '',
      'name' => $_POST['name'] ?? '',
      'price' => $_POST['price'] ?? 0,
      'description' => $_POST['description'] ?? '',
      'image' => $imageName,
    ];

    if (!$data['category_id'] || trim($data['name']) === '') {
      exit("Thiếu dữ liệu: category hoặc name");
    }

    $model = new ProductModel();
    $id = $model->create($data);

    $_SESSION['flash'] = "Đã thêm sản phẩm (#$id).";
    $this->redirect("index.php?controller=product&action=index");
  }

  public function edit() {
    $id = (int)($_GET['id'] ?? 0);

    $model = new ProductModel();
    $product = $model->find($id);
    if (!$product) { http_response_code(404); exit("Not found"); }

    $catModel = new CategoryModel();
    $categories = $catModel->all();

    $this->view('product/edit', compact('product', 'categories'));
  }

  public function update() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->redirect('index.php?controller=product&action=index');

    $id = (int)($_POST['id'] ?? 0);

    $model = new ProductModel();
    $old = $model->find($id);
    if (!$old) { http_response_code(404); exit("Not found"); }

    $newImage = $this->handleUpload($_FILES['image'] ?? null);

    // nếu upload ảnh mới => xóa ảnh cũ
    $finalImage = $old['image'] ?? null;
    if ($newImage) {
      $this->deleteImageFile($finalImage);
      $finalImage = $newImage;
    }

    $data = [
      'category_id' => $_POST['category_id'] ?? '',
      'name' => $_POST['name'] ?? '',
      'price' => $_POST['price'] ?? 0,
      'description' => $_POST['description'] ?? '',
      'image' => $finalImage,
    ];

    $model->update($id, $data);

    $_SESSION['flash'] = "Đã cập nhật sản phẩm (#$id).";
    $this->redirect("index.php?controller=product&action=index");
  }

  public function delete() {
    $id = (int)($_GET['id'] ?? 0);

    $model = new ProductModel();
    $old = $model->find($id);
    if ($old && !empty($old['image'])) {
      $this->deleteImageFile($old['image']);
    }

    $model->delete($id);

    $_SESSION['flash'] = "Đã xóa sản phẩm (#$id).";
    $this->redirect("index.php?controller=product&action=index");
  }

  public function show() {
    $id = (int)($_GET['id'] ?? 0);
    $model = new ProductModel();
    $product = $model->find($id);
    if (!$product) { http_response_code(404); exit("Not found"); }
    $this->view('product/show', compact('product'));
  }
}
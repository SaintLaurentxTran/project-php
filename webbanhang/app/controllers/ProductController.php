<?php
require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../models/CategoryModel.php';

class ProductController {
  public function __construct(private PDO $pdo) {}

  public function show() {
    $id = (int)($_GET['id'] ?? 0);
    $model = new ProductModel($this->pdo);
    $product = $model->find($id);
    if (!$product) {
      http_response_code(404);
      exit("Product not found");
    }
    $gallery = $model->gallery($id);
    $pageTitle = "ShopeeFake - " . $product['name'];
    require __DIR__ . '/../views/product/show.php';
  }

  public function list() {
    $model = new ProductModel($this->pdo);
    $catModel = new CategoryModel($this->pdo);
    $page = max(1, (int)($_GET['page'] ?? 1));
    $result = $model->paginate($page, 20, []);
    $categories = $catModel->all();
    $pageTitle = "ShopeeFake - Danh sách sản phẩm";
    require __DIR__ . '/../views/product/list.php';
  }

  // 1. HIỂN THỊ FORM THÊM MỚI SẢN PHẨM
  public function add() {
    $catModel = new CategoryModel($this->pdo);
    $categories = $catModel->all();
    $pageTitle = "Thêm sản phẩm mới";
    require __DIR__ . '/../views/product/add.php';
  }

  // 2. XỬ LÝ LƯU SẢN PHẨM MỚI VÀ UPLOAD FILE
  public function store() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        exit("Invalid request method");
    }

    $model = new ProductModel($this->pdo);
    $data = $_POST;
    
    // Mặc định ảnh đại diện trống
    $data['image'] = ''; 

    // Xử lý upload ảnh đại diện (input name="image")
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $data['image'] = $this->handleFileUpload($_FILES['image']);
    }

    // Lưu thông tin cơ bản của sản phẩm và lấy ID vừa tạo
    $productId = $model->create($data);

    // Xử lý upload nhiều ảnh phụ cho album slider nếu có (input name="gallery[]" multiple)
    if (isset($_FILES['gallery']) && is_array($_FILES['gallery']['name'])) {
        $files = $_FILES['gallery'];
        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $singleFile = [
                    'name'     => $files['name'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error'    => $files['error'][$i],
                    'size'     => $files['size'][$i],
                ];
                $galleryPath = $this->handleFileUpload($singleFile);
                if ($galleryPath) {
                    $model->addImageToGallery($productId, $galleryPath, $i);
                }
            }
        }
    }

    // Chuyển hướng về danh sách sau khi thêm thành công
    header("Location: index.php?c=product&a=list");
    exit();
  }

  // 3. HIỂN THỊ FORM CHỈNH SỬA SẢN PHẨM
  public function edit() {
    $id = (int)($_GET['id'] ?? 0);
    $model = new ProductModel($this->pdo);
    $product = $model->find($id);
    if (!$product) {
        exit("Product not found");
    }
    $catModel = new CategoryModel($this->pdo);
    $categories = $catModel->all();
    $pageTitle = "Sửa sản phẩm - " . $product['name'];
    require __DIR__ . '/../views/product/edit.php';
  }

  // 4. XỬ LÝ CẬP NHẬT SẢN PHẨM VÀ FILE ẢNH MỚI
  public function update() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        exit("Invalid request method");
    }

    $id = (int)($_GET['id'] ?? 0);
    $model = new ProductModel($this->pdo);
    $data = $_POST;

    // Giữ lại đường dẫn ảnh cũ nếu không chọn file ảnh mới
    $data['image'] = $_POST['old_image'] ?? '';

    // Nếu người dùng chọn file ảnh mới, tiến hành ghi đè
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $newImagePath = $this->handleFileUpload($_FILES['image']);
        if ($newImagePath) {
            $data['image'] = $newImagePath;
        }
    }

    $model->update($id, $data);

    header("Location: index.php?c=product&a=list");
    exit();
  }

  /**
   * Hàm helper dùng chung xử lý upload file vật lý vào thư mục ổ cứng Laragon
   */
  private function handleFileUpload(array $file): string {
    $fileName = $file['name'];
    $fileTmpPath = $file['tmp_name'];
    
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    // Đặt tên file ngẫu nhiên bằng hash md5 + thời gian để không bao giờ bị trùng lặp ảnh
    $newFileName = time() . '_' . md5(uniqid($fileName, true)) . '.' . $fileExtension;

    // Thư mục lưu trữ: Ngoài thư mục gốc, tạo một thư mục tên là `uploads` ngang hàng index.php
    $uploadFileDir = __DIR__ . '/../../uploads/';
    
    if (!is_dir($uploadFileDir)) {
        mkdir($uploadFileDir, 0755, true);
    }

    $dest_path = $uploadFileDir . $newFileName;

    if (move_uploaded_file($fileTmpPath, $dest_path)) {
        // Chuỗi trả về lưu DB có dạng: uploads/171654321_abcde.jpg
        return 'uploads/' . $newFileName;
    }

    return '';
  }
}
<?php
require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../models/CategoryModel.php';

class SellerController {
  
  public function __construct(private PDO $pdo) {
    requireAdmin();
  }

  public function products() {
    $model = new ProductModel($this->pdo);
    $catModel = new CategoryModel($this->pdo);
    
    $page = max(1, (int)($_GET['page'] ?? 1));
    $result = $model->paginate($page, 20, []);
    $categories = $catModel->all();
    
    $pageTitle = "Quản lý sản phẩm - Nhà bán hàng";
    require __DIR__ . '/../views/seller/products.php'; 
  }

  public function add() {
    $catModel = new CategoryModel($this->pdo);
    $categories = $catModel->all();
    $pageTitle = "Thêm sản phẩm mới";
    require __DIR__ . '/../views/product/add.php';
  }

  /**
   * SỬA: XỬ LÝ LƯU SẢN PHẨM MỚI KHI CHỌN NHIỀU ẢNH CÙNG LÚC
   */
  public function store() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        exit("Invalid request method");
    }

    $model = new ProductModel($this->pdo);
    $data = $_POST;
    $data['thumb_url'] = ''; 

    $uploadedImages = [];

    // 1. Duyệt mảng dữ liệu nhiều tệp tin gửi lên từ trường dữ liệu thumb_url[]
    if (isset($_FILES['thumb_url']) && is_array($_FILES['thumb_url']['name'])) {
        $files = $_FILES['thumb_url'];
        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $singleFile = [
                    'name'     => $files['name'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error'    => $files['error'][$i],
                    'size'     => $files['size'][$i],
                ];
                $path = $this->uploadFileToServer($singleFile);
                if ($path) {
                    $uploadedImages[] = $path;
                }
            }
        }
    }

    // Lấy tấm ảnh đầu tiên làm ảnh đại diện chính của sản phẩm
    if (!empty($uploadedImages)) {
        $data['thumb_url'] = $uploadedImages[0];
    }

    // Lưu thông tin cơ bản sản phẩm vào Database
    $productId = $model->create($data);

    // 2. Nếu người dùng chọn nhiều ảnh, tự động đẩy các tấm ảnh từ thứ 2 trở đi vào bộ sưu tập ảnh phụ
    if (count($uploadedImages) > 1) {
        for ($i = 1; $i < count($uploadedImages); $i++) {
            $model->addImageToGallery($productId, $uploadedImages[$i], $i);
        }
    }

    // 3. Xử lý thêm album ảnh phụ bổ sung (nếu có chọn ở trường gallery[] phía dưới)
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
                $galleryPath = $this->uploadFileToServer($singleFile);
                if ($galleryPath) {
                    $model->addImageToGallery($productId, $galleryPath, $i + 10);
                }
            }
        }
    }

    require __DIR__ . '/../views/seller/create_success.php';
    exit();
  }

  public function edit() {
    $id = (int)($_GET['id'] ?? 0);
    $model = new ProductModel($this->pdo);
    $product = $model->find($id);
    
    if (!$product) {
        http_response_code(404);
        exit("Product not found");
    }
    
    $catModel = new CategoryModel($this->pdo);
    $categories = $catModel->all();
    $pageTitle = "Sửa sản phẩm - " . $product['name'];
    require __DIR__ . '/../views/product/edit.php';
  }

  /**
   * SỬA: XỬ LÝ CẬP NHẬT SẢN PHẨM KHI CHỌN NHIỀU ẢNH CÙNG LÚC
   */
  public function update() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        exit("Invalid request method");
    }

    $id = (int)($_GET['id'] ?? 0);
    $model = new ProductModel($this->pdo);
    $data = $_POST;

    $data['thumb_url'] = $_POST['old_thumb_url'] ?? '';
    $uploadedImages = [];

    // Duyệt danh sách ảnh mới tải lên
    if (isset($_FILES['thumb_url']) && is_array($_FILES['thumb_url']['name'])) {
        $files = $_FILES['thumb_url'];
        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $singleFile = [
                    'name'     => $files['name'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error'    => $files['error'][$i],
                    'size'     => $files['size'][$i],
                ];
                $path = $this->uploadFileToServer($singleFile);
                if ($path) {
                    $uploadedImages[] = $path;
                }
            }
        }
    }

    // Nếu có chọn loạt ảnh mới, cập nhật lại ảnh đại diện chính bằng ảnh đầu tiên của loạt ảnh mới
    if (!empty($uploadedImages)) {
        $data['thumb_url'] = $uploadedImages[0];
        
        // Đẩy toàn bộ các ảnh mới còn lại vào album ảnh phụ chi tiết
        for ($i = 1; $i < count($uploadedImages); $i++) {
            $model->addImageToGallery($id, $uploadedImages[$i], $i);
        }
    }

    $model->update($id, $data);

    require __DIR__ . '/../views/seller/update_success.php';
    exit();
  }

  public function delete() {
    $id = (int)($_GET['id'] ?? 0);
    $model = new ProductModel($this->pdo);
    $model->delete($id);

    require __DIR__ . '/../views/seller/delete_success.php';
    exit();
  }

  private function uploadFileToServer(array $file): string {
    $fileName = $file['name'];
    $fileTmpPath = $file['tmp_name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    $newFileName = time() . '_' . md5(uniqid($fileName, true)) . '.' . $fileExtension;
    $uploadFileDir = __DIR__ . '/../../uploads/';
    
    if (!is_dir($uploadFileDir)) {
        mkdir($uploadFileDir, 0755, true);
    }

    if (move_uploaded_file($fileTmpPath, $uploadFileDir . $newFileName)) {
        return 'uploads/' . $newFileName;
    }
    return '';
  }
}
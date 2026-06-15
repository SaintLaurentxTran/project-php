<?php
require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../models/CategoryModel.php';

class ApiProductController {
    
    private ProductModel $productModel;

    public function __construct(private PDO $pdo) {
        $this->productModel = new ProductModel($this->pdo);
    }

    /**
     * Hàm Helper dùng chung để xuất dữ liệu dạng JSON chuẩn API
     */
    private function sendJson(bool $success, string $message, mixed $data = null, int $statusCode = 200): void {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data'    => $data
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * 1. API LẤY DANH SÁCH SẢN PHẨM
     * GET /api/products
     */
    public function list(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendJson(false, 'Phương thức HTTP không hợp lệ', null, 405);
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = max(1, (int)($_GET['limit'] ?? 20));
        
        $search = trim($_GET['search'] ?? $_GET['q'] ?? $_GET['search_name'] ?? '');
        $categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
        $sortBy = trim($_GET['sort_by'] ?? ''); 
        $minPrice = isset($_GET['min_price']) ? (int)$_GET['min_price'] : null;
        $maxPrice = isset($_GET['max_price']) ? (int)$_GET['max_price'] : null;

        $filters = [
            'q'      => $search,
            'category_id' => $categoryId,
            'sort_by'     => $sortBy,
            'min_price'   => $minPrice,
            'max_price'   => $maxPrice
        ];

        $result = $this->productModel->paginate($page, $limit, $filters);
        $this->sendJson(true, 'Lấy danh sách sản phẩm thành công', $result);
    }

    /**
     * 2. API LẤY CHI TIẾT SẢN PHẨM
     * GET /api/products?id={id}
     */
    public function show(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendJson(false, 'Phương thức HTTP không hợp lệ', null, 405);
        }

        $id = (int)($_GET['id'] ?? 0);
        $product = $this->productModel->find($id);

        if (!$product) {
            $this->sendJson(false, 'Sản phẩm không tồn tại', null, 404);
        }

        $product['gallery'] = $this->productModel->gallery($id) ?: [];
        $this->sendJson(true, 'Lấy chi tiết sản phẩm thành công', $product);
    }

    /**
     * 3. API THÊM SẢN PHẨM MỚI
     * POST /api/products
     */
    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJson(false, 'Phương thức HTTP không hợp lệ', null, 405);
        }

        $rawJson = file_get_contents('php://input');
        $data = json_decode(trim($rawJson), true); 

        if (!is_array($data)) {
            $this->sendJson(false, 'Dữ liệu không đúng định dạng JSON hoặc Body bị trống.', null, 400);
        }

        $name            = $data['name'] ?? null;
        $categoryId      = $data['category_id'] ?? null;
        $price           = $data['price'] ?? null;
        $discountPercent = $data['discount_percent'] ?? 0;
        $stock           = $data['stock'] ?? 0;
        $city            = $data['city'] ?? '';
        $description     = $data['description'] ?? '';
        $isFlashSale     = $data['is_flash_sale'] ?? 0;
        $thumbUrl        = $data['thumb_url'] ?? null;

        // ==========================================================
        // 🔥 HỆ THỐNG VALIDATION DỮ LIỆU ĐẦU VÀO (MỚI)
        // ==========================================================
        
        // Yêu cầu 1: Tên sản phẩm không được rỗng
        if (empty($name) || empty(trim($name))) {
            $this->sendJson(false, 'Tên sản phẩm không được để trống.', null, 400);
        }

        // Yêu cầu 2: Giá phải là số và lớn hơn 0
        if ($price === null || !is_numeric($price) || (float)$price <= 0) {
            $this->sendJson(false, 'Giá sản phẩm phải là số và lớn hơn 0.', null, 400);
        }

        // Yêu cầu 3: Danh mục sản phẩm phải hợp lệ (Tồn tại trong DB)
        if (empty($categoryId) || !is_numeric($categoryId)) {
            $this->sendJson(false, 'Danh mục sản phẩm không được để trống và phải là số.', null, 400);
        } else {
            // Khởi tạo CategoryModel (giả định có hàm find() hoặc check tương tự)
            $categoryModel = new CategoryModel($this->pdo);
            // Nếu hệ thống của bạn dùng hàm khác, hãy thay thế bằng hàm kiểm tra danh mục tương ứng
            if (method_exists($categoryModel, 'find') && !$categoryModel->find((int)$categoryId)) {
                $this->sendJson(false, 'Danh mục sản phẩm không hợp lệ (Không tồn tại trong hệ thống).', null, 400);
            }
        }

        // Yêu cầu 4: Hình ảnh sản phẩm nếu có phải đúng định dạng
        if (!empty($thumbUrl)) {
            $fileExtension = strtolower(pathinfo($thumbUrl, PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($fileExtension, $allowedExtensions)) {
                $this->sendJson(false, 'Đường dẫn hình ảnh không hợp lệ. Chỉ chấp nhận các định dạng: ' . implode(', ', $allowedExtensions), null, 400);
            }
        }

        // --- ĐOẠN CODE LƯU VÀO DB PHÍA DƯỚI GIỮ NGUYÊN ---
        $productData = [
            'category_id'      => (int)$categoryId,
            'name'             => trim($name),
            'price'            => (float)$price,
            'discount_percent' => (int)$discountPercent,
            'stock'            => (int)$stock,
            'city'             => trim($city),
            'description'      => trim($description),
            'is_flash_sale'    => !empty($isFlashSale) ? 1 : 0,
            'thumb_url'        => $thumbUrl
        ];

        try {
            $newProductId = $this->productModel->create($productData);
            if ($newProductId > 0) {
                $this->sendJson(true, 'Thêm sản phẩm mới thành công!', ['id' => $newProductId, 'name' => $name], 201);
            }
        } catch (Exception $e) {
            $this->sendJson(false, 'Lỗi hệ thống database: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * 4. API CẬP NHẬT SẢN PHẨM
     * PUT /api/products?id={id}
     */
    public function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            $this->sendJson(false, 'Phương thức HTTP không hợp lệ. Vui lòng sử dụng PUT.', null, 405);
        }

        $id = (int)($_GET['id'] ?? 0);
        $product = $this->productModel->find($id);

        if (!$product) {
            $this->sendJson(false, "Sản phẩm có ID = {$id} không tồn tại để cập nhật", null, 404);
        }

        $inputData = json_decode(file_get_contents('php://input'), true);
        
        if (!is_array($inputData)) {
            $this->sendJson(false, 'Dữ liệu sửa không đúng định dạng JSON hoặc Body bị trống.', null, 400);
        }

        // Gộp dữ liệu mới đè lên dữ liệu cũ (đảm bảo các trường không gửi lên vẫn có giá trị)
        $updateData = array_merge($product, $inputData);

        // ==========================================================
        // 🔥 HỆ THỐNG VALIDATION DỮ LIỆU KHI CẬP NHẬT (MỚI)
        // ==========================================================

        // Yêu cầu 1: Tên sản phẩm không được rỗng
        if (isset($inputData['name']) && empty(trim($inputData['name']))) {
            $this->sendJson(false, 'Tên sản phẩm không được để trống.', null, 400);
        }

        // Yêu cầu 2: Giá phải là số và lớn hơn 0
        if (isset($inputData['price']) && (!is_numeric($inputData['price']) || (float)$inputData['price'] <= 0)) {
            $this->sendJson(false, 'Giá sản phẩm phải là số và lớn hơn 0.', null, 400);
        }

        // Yêu cầu 3: Danh mục sản phẩm sửa phải hợp lệ
        if (isset($inputData['category_id'])) {
            if (!is_numeric($inputData['category_id'])) {
                $this->sendJson(false, 'Danh mục sản phẩm phải là số.', null, 400);
            }
            $categoryModel = new CategoryModel($this->pdo);
            if (method_exists($categoryModel, 'find') && !$categoryModel->find((int)$inputData['category_id'])) {
                $this->sendJson(false, 'Danh mục sản phẩm sửa đổi không tồn tại trong hệ thống.', null, 400);
            }
        }

        // Yêu cầu 4: Hình ảnh sản phẩm nếu có phải đúng định dạng
        if (!empty($inputData['thumb_url'])) {
            $fileExtension = strtolower(pathinfo($inputData['thumb_url'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($fileExtension, $allowedExtensions)) {
                $this->sendJson(false, 'Đường dẫn hình ảnh không hợp lệ. Chỉ chấp nhận định dạng ảnh phổ biến.', null, 400);
            }
        }

        try {
            $this->productModel->update($id, $updateData);
            $this->sendJson(true, 'Cập nhật sản phẩm thành công', $this->productModel->find($id));
        } catch (Exception $e) {
            $this->sendJson(false, 'Lỗi hệ thống database: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * 5. API XÓA SẢN PHẨM
     * DELETE /api/products?id={id}
     */
    public function destroy(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            $this->sendJson(false, 'Phương thức HTTP không hợp lệ', null, 405);
        }

        $id = (int)($_GET['id'] ?? 0);
        $product = $this->productModel->find($id);

        if (!$product) {
            $this->sendJson(false, 'Sản phẩm không tồn tại hoặc đã bị xóa trước đó', null, 404);
        }

        $this->productModel->delete($id);
        $this->sendJson(true, 'Xóa sản phẩm thành công', ['id' => $id]);
    }
}
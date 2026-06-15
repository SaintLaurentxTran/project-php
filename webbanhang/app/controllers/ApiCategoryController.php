<?php
require_once __DIR__ . '/../models/CategoryModel.php';

class ApiCategoryController {
    private CategoryModel $categoryModel;

    public function __construct(private PDO $pdo) {
        $this->categoryModel = new CategoryModel($this->pdo);
    }

    /**
     * Hàm Helper xuất dữ liệu JSON sạch sẽ
     */
    private function sendJson(bool $success, string $message, mixed $data = null, int $statusCode = 200): void {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data'    => $data
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * 1. API LẤY DANH SÁCH DANH MỤC (CÓ PHÂN TRANG & TÌM KIẾM)
     * GET /api/categories
     */
    public function list(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendJson(false, 'Phương thức HTTP không hợp lệ', null, 405);
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = max(1, (int)($_GET['limit'] ?? $_GET['per_page'] ?? 20));
        $search = trim($_GET['search'] ?? $_GET['q'] ?? $_GET['search_name'] ?? '');

        // Gọi hàm paginate có sẵn trong Model của bạn
        $result = $this->categoryModel->paginate($page, $perPage, $search);
        $this->sendJson(true, 'Lấy danh sách danh mục thành công', $result);
    }

    /**
     * 2. API LẤY CHI TIẾT DANH MỤC
     * GET /api/categories?id={id}
     */
    public function show(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendJson(false, 'Phương thức HTTP không hợp lệ', null, 405);
        }

        $id = (int)($_GET['id'] ?? 0);
        $category = $this->categoryModel->find($id);

        if (!$category) {
            $this->sendJson(false, 'Danh mục không tồn tại', null, 404);
        }

        // Đính kèm thêm số lượng sản phẩm hiện có của danh mục này cho Frontend tiện hiển thị
        $category['product_count'] = $this->categoryModel->productCount($id);

        $this->sendJson(true, 'Lấy chi tiết danh mục thành công', $category);
    }

    /**
     * 3. API THÊM DANH MỤC MỚI
     * POST /api/categories
     */
    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJson(false, 'Phương thức HTTP không hợp lệ', null, 405);
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!is_array($data)) {
            $this->sendJson(false, 'Dữ liệu không hợp lệ hoặc body trống', null, 400);
        }

        // Validation: Tên danh mục bắt buộc nhập
        if (empty($data['name']) || empty(trim($data['name']))) {
            $this->sendJson(false, 'Tên danh mục không được để trống.', null, 400);
        }

        $insertData = [
            'name' => trim($data['name']),
            'icon' => $data['icon'] ?? null
        ];

        try {
            $newId = $this->categoryModel->create($insertData);
            if ($newId > 0) {
                $this->sendJson(true, 'Thêm danh mục mới thành công!', [
                    'id'   => $newId,
                    'name' => $insertData['name'],
                    'icon' => $insertData['icon']
                ], 201);
            }
        } catch (Exception $e) {
            $this->sendJson(false, 'Lỗi hệ thống: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * 4. API SỬA DANH MỤC
     * PUT /api/categories?id={id}
     */
    public function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            $this->sendJson(false, 'Phương thức HTTP không hợp lệ. Vui lòng dùng PUT.', null, 405);
        }

        $id = (int)($_GET['id'] ?? 0);
        $category = $this->categoryModel->find($id);

        if (!$category) {
            $this->sendJson(false, "Danh mục có ID = {$id} không tồn tại để chỉnh sửa", null, 404);
        }

        $inputData = json_decode(file_get_contents('php://input'), true);
        if (!is_array($inputData)) {
            $this->sendJson(false, 'Dữ liệu sửa không hợp lệ hoặc body trống', null, 400);
        }

        // Validation nếu Client muốn sửa trường 'name' thành rỗng
        if (isset($inputData['name']) && empty(trim($inputData['name']))) {
            $this->sendJson(false, 'Tên danh mục không được để trống.', null, 400);
        }

        // Gộp dữ liệu cũ và mới (giữ lại trường cũ nếu Postman không truyền lên)
        $updateData = array_merge($category, $inputData);

        try {
            $this->categoryModel->update($id, $updateData);
            $this->sendJson(true, 'Cập nhật danh mục thành công', $this->categoryModel->find($id));
        } catch (Exception $e) {
            $this->sendJson(false, 'Lỗi hệ thống: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * 5. API XÓA DANH MỤC (KIỂM TRA CHẶN RÀNG BUỘC)
     * DELETE /api/categories?id={id}
     */
    public function destroy(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            $this->sendJson(false, 'Phương thức HTTP không hợp lệ', null, 405);
        }

        $id = (int)($_GET['id'] ?? 0);
        $category = $this->categoryModel->find($id);

        if (!$category) {
            $this->sendJson(false, 'Danh mục không tồn tại hoặc đã bị xóa trước đó.', null, 404);
        }

        // 🔥 THỰC HIỆN ĐÚNG YÊU CẦU: Sử dụng hàm productCount của bạn để check liên kết
        $productCount = $this->categoryModel->productCount($id);
        if ($productCount > 0) {
            $this->sendJson(false, "Không thể xóa! Danh mục này hiện đang có {$productCount} sản phẩm liên kết.", null, 400);
        }

        try {
            $this->categoryModel->delete($id);
            $this->sendJson(true, 'Xóa danh mục thành công.', ['id' => $id]);
        } catch (Exception $e) {
            $this->sendJson(false, 'Lỗi hệ thống database: ' . $e->getMessage(), null, 500);
        }
    }
}
<?php
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../core/JwtMiddleware.php';

class ApiAuthController {
    private UserModel $userModel;

    public function __construct(private PDO $pdo) {
        $this->userModel = new UserModel($this->pdo);
    }

    // Helper chuẩn hóa phản hồi dữ liệu JSON nhanh
    private function jsonResponse(bool $success, string $message, array $data = [], int $statusCode = 200): void {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
        echo json_encode(array_merge([
            'success' => $success,
            'message' => $message
        ], $data), JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Đọc và xử lý JSON raw body truyền từ Postman
    private function getJsonInput(): array {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }

    /**
     * API 1: Đăng ký tài khoản (POST /api/auth/register)
     */
    public function register(): void {
        $input = $this->getJsonInput();
        $name     = trim($input['name'] ?? '');
        $email    = trim($input['email'] ?? '');
        $password = $input['password'] ?? '';

        if (mb_strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($password) < 6) {
            $this->jsonResponse(false, 'Dữ liệu đầu vào không hợp lệ. Họ tên từ 2 ký tự, Email chuẩn, Mật khẩu từ 6 ký tự.', [], 400);
        }

        if ($this->userModel->findByEmail($email)) {
            $this->jsonResponse(false, 'Email này đã tồn tại trên hệ thống.', [], 409);
        }

        // Tạo tài khoản (Sử dụng password_hash đã tích hợp trong UserModel)
        $userId = $this->userModel->create($name, $email, $password);

        // Kích hoạt luôn tài khoản cho môi trường API đơn giản (Bỏ qua bước OTP web)
        $this->userModel->verifyEmail($userId); 

        $this->jsonResponse(true, 'Đăng ký tài khoản API thành công!', ['user_id' => $userId], 21);
    }

    /**
     * API 2: Đăng nhập nhận JWT Token (POST /api/auth/login)
     */
    public function login(): void {
        $input = $this->getJsonInput();
        $email    = trim($input['email'] ?? '');
        $password = $input['password'] ?? '';

        $user = $this->userModel->findByEmail($email);

        // Sử dụng password_verify đã thiết lập trong Model của bạn để đối chiếu
        if (!$user || !$this->userModel->verifyPassword($user, $password)) {
            $this->jsonResponse(false, 'Tài khoản hoặc mật khẩu không chính xác.', [], 401);
        }

        if (!(int)$user['is_active']) {
            $this->jsonResponse(false, 'Tài khoản đang bị vô hiệu hóa.', [], 403);
        }

        // Sinh chuỗi JWT Token chữ ký số điện tử bảo mật
        $token = JwtMiddleware::generateToken($user);

        $this->jsonResponse(true, 'Đăng nhập thành công!', [
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user' => [
                'id'    => $user['id'],
                'name'  => $user['name'],
                'email' => $user['email'],
                'role'  => $user['role']
            ]
        ]);
    }

    /**
     * API 3: Xem thông tin cá nhân hiện tại (GET /api/auth/me)
     */
    public function me(): void {
        $userId = JwtMiddleware::getAuthenticatedUser();
        if (!$userId) {
            $this->jsonResponse(false, 'Lỗi xác thực! Token không hợp lệ hoặc đã hết hạn.', [], 401);
        }

        $user = $this->userModel->findById($userId);
        unset($user['password'], $user['remember_token']); // Giấu thông tin nhạy cảm

        $this->jsonResponse(true, 'Lấy thông tin tài khoản thành công.', ['user' => $user]);
    }

    /**
     * API 4: Cập nhật hồ sơ cá nhân (PUT /api/auth/profile)
     */
    public function updateProfile(): void {
        $userId = JwtMiddleware::getAuthenticatedUser();
        if (!$userId) {
            $this->jsonResponse(false, 'Lỗi xác thực! Token không hợp lệ hoặc đã hết hạn.', [], 401);
        }

        $input = $this->getJsonInput();
        $name    = trim($input['name'] ?? '');
        $phone   = trim($input['phone'] ?? '');
        $address = trim($input['address'] ?? '');

        if (mb_strlen($name) < 2) {
            $this->jsonResponse(false, 'Tên người dùng không được để trống và phải lớn hơn 2 ký tự.', [], 400);
        }

        $this->userModel->updateProfile($userId, [
            'name'    => $name,
            'phone'   => $phone ?: null,
            'address' => $address ?: null
        ]);

        $this->jsonResponse(true, 'Cập nhật thông tin tài khoản thành công!');
    }

    /**
     * API 5: Đổi mật khẩu (PUT /api/auth/change-password)
     */
    public function changePassword(): void {
        $userId = JwtMiddleware::getAuthenticatedUser();
        if (!$userId) {
            $this->jsonResponse(false, 'Lỗi xác thực! Token không hợp lệ hoặc đã hết hạn.', [], 401);
        }

        $input = $this->getJsonInput();
        $oldPassword = $input['current_password'] ?? '';
        $newPassword = $input['new_password'] ?? '';

        if (mb_strlen($newPassword) < 6) {
            $this->jsonResponse(false, 'Mật khẩu mới phải đạt tối thiểu từ 6 ký tự.', [], 400);
        }

        $user = $this->userModel->findById($userId);
        if (!$this->userModel->verifyPassword($user, $oldPassword)) {
            $this->jsonResponse(false, 'Mật khẩu hiện tại không khớp.', [], 400);
        }

        $this->userModel->updatePassword($userId, $newPassword);
        $this->jsonResponse(true, 'Thay đổi mật khẩu tài khoản thành công!');
    }

    /**
     * API 6: Quên mật khẩu cơ bản / mô phỏng (POST /api/auth/forgot-password)
     */
    public function forgotPassword(): void {
        $input = $this->getJsonInput();
        $email = trim($input['email'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->jsonResponse(false, 'Cú pháp Email không đúng định dạng.', [], 400);
        }

        $user = $this->userModel->findByEmail($email);
        
        if ($user) {
            // Tạo chuỗi mã đặt lại mật khẩu ngẫu nhiên mô phỏng
            $mockToken = bin2hex(random_bytes(16));
            $this->userModel->createPasswordReset($email, $mockToken);
            
            // Thay vì gửi email thực, ta trả về dữ liệu mẫu để nhà phát triển dễ test nhanh
            $this->jsonResponse(true, 'Hệ thống đã nhận yêu cầu. Hướng dẫn đặt lại mật khẩu đã được xử lý thành công!', [
                'demo_simulation' => [
                    'message' => 'Mô phỏng gửi link token thành công ra file email_log.txt',
                    'reset_token_key' => $mockToken,
                    'suggested_next_action' => 'Sử dụng Token này cấu hình cho API đặt lại mật khẩu mới.'
                ]
            ]);
        }

        // Để bảo mật, không báo cụ thể Email có tồn tại hay không
        $this->jsonResponse(true, 'Nếu email tồn tại trên hệ thống, bạn sẽ nhận được chỉ dẫn đặt lại mật khẩu.');
    }
}
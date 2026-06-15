<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

class JwtMiddleware {
    // Khóa bí mật dùng chung để mã hóa và giải mã Token
    private static string $secretKey = "a7f9b2d4e8c156f3a9e2d7b4c6a8f1e0"; 

    // =========================================================================
    // 1. Hàm tạo mã Token khi Đăng nhập thành công
    // =========================================================================
    public static function generateToken(array $user): string {
        $payload = [
            'iss' => 'shopeefake_api',
            'aud' => 'shopeefake_users',
            'iat' => time(),
            'exp' => time() + (60 * 60 * 24), // Hết hạn sau 24 giờ
            'data' => [
                'id'    => (int)$user['id'],
                'email' => $user['email'],
                'role'  => $user['role'] 
            ]
        ];
        return JWT::encode($payload, self::$secretKey, 'HS256');
    }

    // =========================================================================
    // 2. Hàm xác thực Đăng nhập - Tự động xuất lỗi 401 Unauthorized nếu sai/thiếu Token
    // =========================================================================
    public static function getAuthenticatedUser(): array {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

        header('Content-Type: application/json; charset=utf-8');

        if (empty($authHeader) || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized: Token không được cung cấp hoặc sai định dạng.']);
            exit;
        }

        try {
            $jwt = $matches[1];
            $decoded = JWT::decode($jwt, new Key(self::$secretKey, 'HS256'));
            
            // Ép kiểu mảng sâu để tránh lỗi đọc Object dạng mảng
            $decodedArray = json_decode(json_encode($decoded), true);

            // Kiểm tra cấu trúc key 'data' 
            if (isset($decodedArray['data'])) {
                return $decodedArray['data'];
            }
            return $decodedArray;

        } catch (ExpiredException $e) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized: Token đã hết hạn sử dụng.']);
            exit;
        } catch (Throwable $e) {
            http_response_code(401);
            echo json_encode([
                'success' => false, 
                'message' => 'Unauthorized: Token không hợp lệ.', 
                'debug_error' => $e->getMessage()
            ]);
            exit;
        }
    }

    // =========================================================================
    // 3. Hàm kiểm tra quyền Admin - Tự động xuất lỗi 403 Forbidden nếu sai quyền
    // =========================================================================
    public static function requireAdmin(): array {
        // Bước 1: Lấy thông tin user (đã qua bộ lọc 401)
        $user = self::getAuthenticatedUser(); 

        // 🔥 ĐÃ GỠ BỎ ĐOẠN ĐANG DỪNG DEBUG ĐỂ HỆ THỐNG KIỂM TRA PHÂN QUYỀN THỰC TẾ:
        // Bước 2: Kiểm tra thuộc tính role có đúng là admin hay không
        if (($user['role'] ?? '') !== 'admin') {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(403); 
            echo json_encode([
                'success' => false, 
                'message' => 'Forbidden: Bạn không có quyền truy cập vào tài nguyên này.'
            ]);
            exit;
        }

        return $user;
    }
}
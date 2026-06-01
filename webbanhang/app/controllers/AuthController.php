<?php
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../core/helpers.php';

class AuthController
{
    private UserModel $userModel;

    public function __construct(private PDO $pdo)
    {
        $this->userModel = new UserModel($pdo);
    }

    // ============================
    // ĐĂNG KÝ
    // ============================
    public function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_check();

            $name     = trim($_POST['name'] ?? '');
            $email    = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm  = $_POST['password_confirm'] ?? '';
            $errors   = [];

            if (mb_strlen($name) < 2)      $errors[] = 'Họ tên phải ít nhất 2 ký tự.';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email không hợp lệ.';
            if (mb_strlen($password) < 6)  $errors[] = 'Mật khẩu ít nhất 6 ký tự.';
            if ($password !== $confirm)     $errors[] = 'Xác nhận mật khẩu không khớp.';
            if ($this->userModel->findByEmail($email)) $errors[] = 'Email đã được sử dụng.';

            if (!$errors) {
                $userId = $this->userModel->create($name, $email, $password);
                $token  = bin2hex(random_bytes(32));
                $this->userModel->createEmailVerification($userId, $token);

                // Gửi email xác thực (giả lập - in ra link)
                $verifyUrl = base_url("index.php?c=auth&a=verifyEmail&token={$token}");
                $this->sendVerificationEmail($email, $name, $verifyUrl);

                $_SESSION['flash_success'] = "Đăng ký thành công! Vui lòng kiểm tra email để xác thực tài khoản.";
                redirect('index.php?c=auth&a=login');
            }

            $pageTitle = 'Đăng Ký';
            require __DIR__ . '/../views/auth/register.php';
            return;
        }

        $errors = [];
        $pageTitle = 'Đăng Ký';
        require __DIR__ . '/../views/auth/register.php';
    }

    // ============================
    // ĐĂNG NHẬP
    // ============================
    public function login(): void
    {
        if (!empty($_SESSION['user'])) {
            redirect('index.php');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_check();

            $email    = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $remember = !empty($_POST['remember']);
            $errors   = [];

            $user = $this->userModel->findByEmail($email);

            if (!$user || !$this->userModel->verifyPassword($user, $password)) {
                $errors[] = 'Email hoặc mật khẩu không đúng.';
            } elseif (!$user['is_active']) {
                $errors[] = 'Tài khoản chưa được kích hoạt. Vui lòng xác thực email hoặc liên hệ quản trị viên.';
            }

            if (!$errors) {
                $_SESSION['user'] = [
                    'id'     => $user['id'],
                    'name'   => $user['name'],
                    'email'  => $user['email'],
                    'role'   => $user['role'],
                    'avatar' => $user['avatar'],
                ];

                // Remember Me
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $this->userModel->setRememberToken($user['id'], $token);
                    setcookie('remember_token', $token, time() + 60 * 60 * 24 * 30, '/', '', false, true);
                }

                $_SESSION['flash_success'] = "Chào mừng, {$user['name']}!";
                redirect('index.php');
            }

            $pageTitle = 'Đăng Nhập';
            require __DIR__ . '/../views/auth/login.php';
            return;
        }

        $errors = [];
        $pageTitle = 'Đăng Nhập';
        require __DIR__ . '/../views/auth/login.php';
    }

    // ============================
    // ĐĂNG XUẤT
    // ============================
    public function logout(): void
    {
        if (!empty($_SESSION['user'])) {
            $this->userModel->clearRememberToken($_SESSION['user']['id']);
        }
        setcookie('remember_token', '', time() - 3600, '/');
        session_destroy();
        redirect('index.php?c=auth&a=login');
    }

    // ============================
    // XÁC THỰC EMAIL
    // ============================
    public function verifyEmail(): void
    {
        $token = $_GET['token'] ?? '';
        $record = $this->userModel->findEmailVerification($token);

        if (!$record) {
            $_SESSION['flash_error'] = 'Link xác thực không hợp lệ hoặc đã hết hạn.';
            redirect('index.php?c=auth&a=login');
        }

        $this->userModel->verifyEmail((int)$record['user_id']);
        $this->userModel->markEmailVerificationUsed($token);

        $_SESSION['flash_success'] = 'Xác thực email thành công! Bạn có thể đăng nhập.';
        redirect('index.php?c=auth&a=login');
    }

    // ============================
    // QUÊN MẬT KHẨU
    // ============================
    public function forgotPassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_check();

            $email = trim($_POST['email'] ?? '');
            $errors = [];

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Email không hợp lệ.';
            }

            if (!$errors) {
                $user = $this->userModel->findByEmail($email);
                if ($user) {
                    $token = bin2hex(random_bytes(32));
                    $this->userModel->createPasswordReset($email, $token);
                    $resetUrl = base_url("index.php?c=auth&a=resetPassword&token={$token}");
                    $this->sendResetEmail($email, $user['name'], $resetUrl);
                }
                // Luôn hiện thông báo thành công (bảo mật - không tiết lộ email tồn tại)
                $_SESSION['flash_success'] = 'Nếu email tồn tại, chúng tôi đã gửi hướng dẫn đặt lại mật khẩu.';
                redirect('index.php?c=auth&a=forgotPassword');
            }

            $pageTitle = 'Quên Mật Khẩu';
            require __DIR__ . '/../views/auth/forgot_password.php';
            return;
        }

        $errors = [];
        $pageTitle = 'Quên Mật Khẩu';
        require __DIR__ . '/../views/auth/forgot_password.php';
    }

    // ============================
    // ĐẶT LẠI MẬT KHẨU
    // ============================
    public function resetPassword(): void
    {
        $token = $_GET['token'] ?? '';
        $record = $this->userModel->findPasswordReset($token);

        if (!$record) {
            $_SESSION['flash_error'] = 'Link đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.';
            redirect('index.php?c=auth&a=forgotPassword');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_check();

            $password = $_POST['password'] ?? '';
            $confirm  = $_POST['password_confirm'] ?? '';
            $errors   = [];

            if (mb_strlen($password) < 6) $errors[] = 'Mật khẩu ít nhất 6 ký tự.';
            if ($password !== $confirm)   $errors[] = 'Xác nhận mật khẩu không khớp.';

            if (!$errors) {
                $user = $this->userModel->findByEmail($record['email']);
                if ($user) {
                    $this->userModel->updatePassword((int)$user['id'], $password);
                    $this->userModel->markPasswordResetUsed($token);
                    $_SESSION['flash_success'] = 'Đặt lại mật khẩu thành công! Vui lòng đăng nhập.';
                    redirect('index.php?c=auth&a=login');
                }
            }

            $pageTitle = 'Đặt Lại Mật Khẩu';
            require __DIR__ . '/../views/auth/reset_password.php';
            return;
        }

        $errors = [];
        $pageTitle = 'Đặt Lại Mật Khẩu';
        require __DIR__ . '/../views/auth/reset_password.php';
    }

    // ============================
    // HÀM GỬI EMAIL (giả lập - ghi ra file log)
    // Trong production: thay bằng PHPMailer / SMTP
    // ============================
    private function sendVerificationEmail(string $email, string $name, string $url): void
    {
        $subject = 'Xác thực tài khoản ShopeeFake';
        $body = "Xin chào {$name},\n\nVui lòng click vào link sau để xác thực tài khoản:\n{$url}\n\nLink hết hạn sau 24 giờ.\n\nShopeeFake Team";

        // Ghi log thay vì gửi thật (môi trường development Laragon)
        $logFile = __DIR__ . '/../../email_log.txt';
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] VERIFY TO: {$email}\nURL: {$url}\n\n", FILE_APPEND);

        // Bỏ comment dòng dưới khi có SMTP thật:
        // mail($email, $subject, $body, "From: noreply@shopeefake.com\r\nContent-Type: text/plain; charset=UTF-8");
    }

    private function sendResetEmail(string $email, string $name, string $url): void
    {
        $subject = 'Đặt lại mật khẩu ShopeeFake';
        $body = "Xin chào {$name},\n\nVui lòng click vào link sau để đặt lại mật khẩu:\n{$url}\n\nLink hết hạn sau 1 giờ.\n\nShopeeFake Team";

        $logFile = __DIR__ . '/../../email_log.txt';
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] RESET TO: {$email}\nURL: {$url}\n\n", FILE_APPEND);

        // mail($email, $subject, $body, "From: noreply@shopeefake.com\r\nContent-Type: text/plain; charset=UTF-8");
    }
}

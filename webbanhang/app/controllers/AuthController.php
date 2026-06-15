<?php
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/CartModel.php'; 
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
            
            // 🔥 ĐỒNG BỘ JSON: Đọc dữ liệu nếu Postman gửi dạng JSON raw
            $contentType = $_SERVER["CONTENT_TYPE"] ?? '';
            $isApi = str_contains($_SERVER['REQUEST_URI'], '/api/');
            if (stripos($contentType, 'application/json') !== false) {
                $rawJson = file_get_contents('php://input');
                $jsonData = json_decode($rawJson, true) ?? [];
                $_POST = array_merge($_POST, $jsonData);
            }

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
                $otp = $this->generateOtp();
                $this->userModel->createEmailOtp($userId, $this->hashOtp($otp));

                $this->sendOtpEmail($email, $name, $otp);
                $_SESSION['pending_verification_email'] = $email;

                // 🔥 Xử lý phản hồi nếu là API gửi từ Postman
                if ($isApi) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode([
                        'success' => true,
                        'message' => 'Đăng ký thành công! Vui lòng kiểm tra mã OTP trong hệ thống.',
                        'email' => $email
                    ]);
                    return;
                }

                $_SESSION['flash_success'] = "Đăng ký thành công! Vui lòng nhập mã OTP đã gửi đến email.";
                redirect(url('auth', 'verifyOtp', ['email' => $email]));
            }

            // 🔥 Trả về lỗi định dạng JSON nếu gọi qua API từ Postman
            if ($isApi) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(400);
                echo json_encode(['success' => false, 'errors' => $errors]);
                return;
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
    // ĐĂNG NHẬP (TÍCH HỢP JWT)
    // ============================
    public function login(): void
    {
        if (!empty($_SESSION['user']) && !str_contains($_SERVER['REQUEST_URI'], '/api/')) {
            redirect(url());
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            // ĐỒNG BỘ JSON: Đọc dữ liệu nếu Postman gửi dạng JSON raw
            $contentType = $_SERVER["CONTENT_TYPE"] ?? '';
            $isApi = str_contains($_SERVER['REQUEST_URI'], '/api/');
            if (stripos($contentType, 'application/json') !== false) {
                $rawJson = file_get_contents('php://input');
                $jsonData = json_decode($rawJson, true) ?? [];
                $_POST = array_merge($_POST, $jsonData);
            }

            csrf_check();

            $email    = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $remember = !empty($_POST['remember']);
            $errors   = [];

            $user = $this->userModel->findByEmail($email);

            if (!$user || !$this->userModel->verifyPassword($user, $password)) {
                $errors[] = 'Email hoặc mật khẩu không đúng.';
            } elseif (empty($user['email_verified_at'])) {
                // (Giữ nguyên logic OTP cũ của bạn)
                $otp = $this->generateOtp();
                $this->userModel->createEmailOtp((int)$user['id'], $this->hashOtp($otp));
                $this->sendOtpEmail($user['email'], $user['name'], $otp);
                $_SESSION['pending_verification_email'] = $user['email'];

                if ($isApi) {
                    header('Content-Type: application/json; charset=utf-8');
                    http_response_code(401);
                    echo json_encode(['success' => false, 'message' => 'Tài khoản chưa kích hoạt. Vui lòng xác thực OTP.']);
                    return;
                }

                $_SESSION['flash_error'] = 'Tài khoản chưa kích hoạt. Vui lòng nhập mã OTP vừa gửi đến email.';
                redirect(url('auth', 'verifyOtp', ['email' => $user['email']]));
            } elseif (!$user['is_active']) {
                $errors[] = 'Tài khoản của bạn đang bị khóa. Vui lòng liên hệ quản trị viên.';
            }

            if (!$errors) {
                // Tạo mảng thông tin User tiêu chuẩn
                $userData = [
                    'id'     => (int)$user['id'],
                    'name'   => $user['name'],
                    'email'  => $user['email'],
                    'role'   => $user['role'],
                    'avatar' => $user['avatar'],
                ];

                // Lưu vào Session cho người dùng duyệt Web truyền thống
                $_SESSION['user'] = $userData;

                // ĐỒNG BỘ GIỎ HÀNG TỪ SESSION XUỐNG DATABASE
                if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
                    $cartModel = new CartModel($this->pdo);
                    foreach ($_SESSION['cart'] as $productId => $item) {
                        $cartModel->addOrIncrement((int)$user['id'], (int)$productId, (int)$item['qty']);
                    }
                    unset($_SESSION['cart']);
                }

                // Remember Me
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $this->userModel->setRememberToken($user['id'], $token);
                    setcookie('remember_token', $token, time() + 60 * 60 * 24 * 30, '/', '', false, true);
                }

                // Remember Me
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $this->userModel->setRememberToken($user['id'], $token);
                    setcookie('remember_token', $token, time() + 60 * 60 * 24 * 30, '/', '', false, true);
                }

                // 🔥 ĐÃ SỬA: ĐỒNG BỘ HÓA GỌI QUA MIDDLEWARE ĐỂ TẠO TOKEN ĐỒNG NHẤT
                if ($isApi) {
                    // Gọi trực tiếp hàm tạo Token của Middleware để dùng chung cấu trúc 'data' và chung $secretKey
                    $jwt = JwtMiddleware::generateToken($userData);

                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode([
                        'success'      => true,
                        'message'      => 'Đăng nhập thành công!',
                        'access_token' => $jwt,
                        'token_type'   => 'Bearer',
                        'expires_in'   => 86400, // 24 giờ khớp với Middleware
                        'user'         => $userData
                    ]);
                    return;
                }

                $_SESSION['flash_success'] = "Chào mừng, {$user['name']}!";
                redirect(url());

                $_SESSION['flash_success'] = "Chào mừng, {$user['name']}!";
                redirect(url());
            }

            // Trả về lỗi định dạng JSON nếu gọi đăng nhập thất bại qua API bằng Postman
            if ($isApi) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(400);
                echo json_encode(['success' => false, 'errors' => $errors]);
                return;
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
    // LẤY THÔNG TIN NGƯỜI DÙNG HIỆN TẠI (API ME)
    // ============================
    public function me(): void
    {
        // Chỉ cho phép truy cập qua phương thức GET
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            header('HTTP/1.1 405 Method Not Allowed');
            exit;
        }

        // Gọi hàm kiểm tra và lấy thông tin user từ JWT Token
        $user = getJwtUser();

        // Trả về thông tin user dưới dạng JSON
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'message' => 'Lấy thông tin người dùng thành công.',
            'user' => $user
        ]);
        return;
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
        
        unset($_SESSION['user']);
        unset($_SESSION['pending_verification_email']);
        
        $_SESSION['cart'] = []; 
        
        redirect(url('auth', 'login'));
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
            redirect(url('auth', 'login'));
        }

        $this->userModel->verifyEmail((int)$record['user_id']);
        $this->userModel->markEmailVerificationUsed($token);

        $_SESSION['flash_success'] = 'Xác thực email thành công! Bạn có thể đăng nhập.';
        redirect(url('auth', 'login'));
    }

    public function verifyOtp(): void
    {
        $email = trim($_GET['email'] ?? $_POST['email'] ?? $_SESSION['pending_verification_email'] ?? '');
        $record = $email !== '' ? $this->userModel->findPendingEmailVerificationByEmail($email) : null;

        if (!$record) {
            $_SESSION['flash_error'] = 'Mã OTP không hợp lệ hoặc đã hết hạn. Vui lòng đăng ký/đăng nhập lại để nhận mã mới.';
            redirect(url('auth', 'login'));
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_check();
            $otp = $this->otpFromPost();
            $errors = [];

            if (!preg_match('/^\d{6}$/', $otp)) {
                $errors[] = 'Vui lòng nhập đầy đủ 6 chữ số OTP.';
            } elseif (!hash_equals((string)$record['token'], $this->hashOtp($otp))) {
                $errors[] = 'Mã OTP không đúng. Vui lòng kiểm tra lại email của bạn.';
            }

            if (!$errors) {
                $this->userModel->verifyEmail((int)$record['user_id']);
                $this->userModel->markEmailVerificationUsedByUserId((int)$record['user_id']);
                unset($_SESSION['pending_verification_email']);

                $_SESSION['flash_success'] = 'Xác thực OTP thành công! Bạn có thể đăng nhập.';
                redirect(url('auth', 'login'));
            }
        } else {
            $errors = [];
        }

        $_SESSION['pending_verification_email'] = $email;
        $expiresAt = strtotime($record['expires_at']);
        $remainingSeconds = max(0, $expiresAt - time());
        $pageTitle = 'Xác thực OTP';
        require __DIR__ . '/../views/auth/verify_otp.php';
    }

    public function resendOtp(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(url('auth', 'login'));
        }
        csrf_check();

        $email = trim($_POST['email'] ?? $_SESSION['pending_verification_email'] ?? '');
        $user = $email !== '' ? $this->userModel->findByEmail($email) : null;

        if (!$user || (int)$user['is_active'] === 1 || !empty($user['email_verified_at'])) {
            $_SESSION['flash_error'] = 'Không thể gửi lại OTP cho tài khoản này.';
            redirect(url('auth', 'login'));
        }

        $otp = $this->generateOtp();
        $this->userModel->createEmailOtp((int)$user['id'], $this->hashOtp($otp));
        $this->sendOtpEmail($user['email'], $user['name'], $otp);
        $_SESSION['pending_verification_email'] = $user['email'];
        $_SESSION['flash_success'] = 'Đã gửi lại mã OTP mới. Vui lòng kiểm tra email của bạn.';
        redirect(url('auth', 'verifyOtp', ['email' => $user['email']]));
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
                    $resetUrl = $this->absoluteUrl(url('auth', 'resetPassword', ['token' => $token]));
                    $this->sendPasswordResetLink($email, $user['name'], $resetUrl);
                }
                $_SESSION['flash_success'] = 'Nếu email tồn tại, chúng tôi đã gửi hướng dẫn đặt lại mật khẩu.';
                redirect(url('auth', 'forgotPassword'));
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
            redirect(url('auth', 'forgotPassword'));
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
                    redirect(url('auth', 'login'));
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

    private function generateOtp(): string
    {
        return (string)random_int(100000, 999999);
    }

    private function hashOtp(string $otp): string
    {
        return hash('sha256', $otp);
    }

    private function otpFromPost(): string
    {
        if (isset($_POST['otp']) && is_array($_POST['otp'])) {
            return preg_replace('/\D+/', '', implode('', $_POST['otp']));
        }
        return preg_replace('/\D+/', '', (string)($_POST['otp'] ?? ''));
    }

    private function sendOtpEmail(string $email, string $name, string $otp): void
    {
        $logFile = __DIR__ . '/../../email_log.txt';
        $subject = 'Mã OTP xác thực tài khoản ShopeeFake';
        $body = "Xin chào {$name},\n\nMã OTP xác thực tài khoản ShopeeFake của bạn là: {$otp}\n\nMã có hiệu lực trong 5 phút.\n\nShopeeFake Team";
        $from = $this->mailFromAddress();
        $headers = [
            "From: {$from}",
            "Reply-To: {$from}",
            'Content-Type: text/plain; charset=UTF-8',
        ];

        $mailTransportReady = $this->mailTransportReady();
        $mailError = '';
        $sent = false;

        if ($mailTransportReady) {
            $sent = $this->sendViaSmtp($email, $name, $subject, $body, $mailError);

            if (!$sent) {
                ini_set('sendmail_from', $from);
                $sent = @mail($email, $subject, $body, implode("\r\n", $headers), "-f{$from}");
                if (!$sent && $mailError === '') {
                    $mailError = 'PHP mail() returned false.';
                }
            }
        }

        file_put_contents(
            $logFile,
            "[" . date('Y-m-d H:i:s') . "] OTP TO: {$email}\nMAIL_STATUS: " . ($sent ? 'SENT' : ($mailTransportReady ? 'FAILED' : 'SMTP_NOT_CONFIGURED')) . ($mailError ? "\nMAIL_ERROR: {$mailError}" : '') . "\nOTP: {$otp}\n{$body}\n\n",
            FILE_APPEND
        );
    }

    private function sendViaSmtp(string $email, string $name, string $subject, string $body, string &$error = ''): bool
    {
        $config = $this->sendmailConfig(ini_get('sendmail_path') ?: '');
        if ($config === null) {
            $error = 'sendmail.ini not found.';
            return false;
        }

        $phpmailerClass = 'C:/laragon/etc/php/pear/PHPMailer/class.phpmailer.php';
        $smtpClass = 'C:/laragon/etc/php/pear/PHPMailer/class.smtp.php';
        if (!is_file($phpmailerClass) || !is_file($smtpClass)) {
            $error = 'PHPMailer class files not found.';
            return false;
        }

        require_once $phpmailerClass;
        require_once $smtpClass;

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = trim((string)($config['smtp_server'] ?? ''));
            $mail->Port = (int)($config['smtp_port'] ?? 587);
            $mail->SMTPSecure = strtolower((string)($config['smtp_ssl'] ?? 'tls'));
            $mail->SMTPAuth = true;
            $mail->Username = trim((string)($config['auth_username'] ?? ''));
            $mail->Password = preg_replace('/\s+/', '', (string)($config['auth_password'] ?? ''));
            $mail->CharSet = 'UTF-8';
            $mail->isHTML(false);

            $from = $this->mailFromAddress();
            $mail->setFrom($from, 'ShopeeFake');
            $mail->addAddress($email, $name);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->send();
            return true;
        } catch (Throwable $e) {
            $error = $mail->ErrorInfo ?: $e->getMessage();
            return false;
        }
    }

    private function mailTransportReady(): bool
    {
        $sendmailPath = ini_get('sendmail_path') ?: '';
        $config = $this->sendmailConfig($sendmailPath);
        if ($config === null) {
            return true;
        }

        $server = trim((string)($config['smtp_server'] ?? ''));
        $requiresAuth = stripos($server, 'gmail.com') !== false;
        if (!$requiresAuth) {
            return true;
        }

        return trim((string)($config['auth_username'] ?? '')) !== ''
            && trim((string)($config['auth_password'] ?? '')) !== '';
    }

    private function mailFromAddress(): string
    {
        $config = $this->sendmailConfig(ini_get('sendmail_path') ?: '');
        $username = trim((string)($config['auth_username'] ?? ''));

        return filter_var($username, FILTER_VALIDATE_EMAIL) ? $username : 'noreply@shopeefake.local';
    }

    private function absoluteUrl(string $path): string
    {
        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8080';
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['SERVER_PORT'] ?? '') === '443');
        $scheme = $https ? 'https' : 'http';

        return $scheme . '://' . $host . '/' . ltrim($path, '/');
    }

    private function sendmailConfig(string $sendmailPath): ?array
    {
        if ($sendmailPath === '') {
            return null;
        }

        if (!preg_match('/^(.+?sendmail\.exe)/i', $sendmailPath, $matches)) {
            return null;
        }

        $iniPath = dirname(str_replace('/', DIRECTORY_SEPARATOR, $matches[1])) . DIRECTORY_SEPARATOR . 'sendmail.ini';
        if (!is_file($iniPath)) {
            return null;
        }

        $config = parse_ini_file($iniPath);
        return $config ?: null;
    }

    private function sendVerificationEmail(string $email, string $name, string $url): void
    {
        $subject = 'Xác thực tài khoản ShopeeFake';
        $body = "Xin chào {$name},\n\nVui lòng click vào link sau để xác thực tài khoản:\n{$url}\n\nLink hết hạn sau 24 giờ.\n\nShopeeFake Team";

        $logFile = __DIR__ . '/../..';
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] VERIFY TO: {$email}\nURL: {$url}\n\n", FILE_APPEND);
    }

    private function sendPasswordResetLink(string $email, string $name, string $url): void
    {
        $logFile = __DIR__ . '/../../email_log.txt';
        $subject = 'Đặt lại mật khẩu ShopeeFake';
        $body = "Xin chào {$name},\n\nBạn vừa yêu cầu đặt lại mật khẩu tài khoản ShopeeFake.\n\nVui lòng bấm vào link sau để tạo mật khẩu mới:\n{$url}\n\nLink có hiệu lực trong 1 giờ. Nếu bạn không yêu cầu, vui lòng bỏ qua email này.\n\nShopeeFake Team";
        $from = $this->mailFromAddress();
        $headers = [
            "From: {$from}",
            "Reply-To: {$from}",
            'Content-Type: text/plain; charset=UTF-8',
        ];

        $mailTransportReady = $this->mailTransportReady();
        $mailError = '';
        $sent = false;

        if ($mailTransportReady) {
            $sent = $this->sendViaSmtp($email, $name, $subject, $body, $mailError);

            if (!$sent) {
                ini_set('sendmail_from', $from);
                $sent = @mail($email, $subject, $body, implode("\r\n", $headers), "-f{$from}");
                if (!$sent && $mailError === '') {
                    $mailError = 'PHP mail() returned false.';
                }
            }
        }

        file_put_contents(
            $logFile,
            "[" . date('Y-m-d H:i:s') . "] RESET TO: {$email}\nMAIL_STATUS: " . ($sent ? 'SENT' : ($mailTransportReady ? 'FAILED' : 'SMTP_NOT_CONFIGURED')) . ($mailError ? "\nMAIL_ERROR: {$mailError}" : '') . "\nURL: {$url}\n{$body}\n\n",
            FILE_APPEND
        );
    }

    // ==========================================
    // API CẬP NHẬT HỒ SƠ CÁ NHÂN VÀ ĐẠI DIỆN
    // ==========================================
    public function updateProfile(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!isset($_FILES['avatar'])) {
            echo json_encode([
                'success' => false, 
                'message' => 'Backend KHÔNG nhận được key avatar từ Postman.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode([
                'success' => false,
                'message' => 'File lên tới server nhưng bị lỗi hệ thống. Mã lỗi (Error Code): ' . $_FILES['avatar']['error']
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $currentUser = JwtMiddleware::getAuthenticatedUser();
        $userId = (int)$currentUser['id'];

        $name    = trim($_POST['name'] ?? '');
        $phone   = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if (empty($name)) {
            echo json_encode([
                'success' => false, 
                'message' => 'Tên không được để trống.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $avatarName = null;
        $fileTmpPath = $_FILES['avatar']['tmp_name'];
        $fileName    = $_FILES['avatar']['name'];
        
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($fileExtension, $allowedExtensions, true)) {
            echo json_encode([
                'success' => false, 
                'message' => 'Định dạng ảnh không hợp lệ. Chỉ chấp nhận JPG, PNG, GIF.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $avatarName = 'avatar_' . $userId . '_' . time() . '.' . $fileExtension;

        $uploadFileDir = __DIR__ . '/../../public/uploads/avatars/';
        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0755, true);
        }

        $destPath = $uploadFileDir . $avatarName;

        if (!move_uploaded_file($fileTmpPath, $destPath)) {
            echo json_encode([
                'success' => false, 
                'message' => 'Không thể lưu tập tin ảnh đại diện.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // 5. CẬP NHẬT THÔNG TIN VÀO CƠ SỞ DỮ LIỆU
        try {
            $this->userModel->updateProfile($userId, $name, $phone, $address);

            if ($avatarName !== null) {
                $this->userModel->updateAvatar($userId, $avatarName);
            }

            echo json_encode([
                'success' => true,
                'message' => 'Cập nhật hồ sơ cá nhân và ảnh đại diện thành công!'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lưu DB: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    // =========================================================================
    // 🔑 API 1: ĐỔI MẬT KHẨU (YÊU CẦU TOKEN ĐĂNG NHẬP)
    // =========================================================================
    public function apiChangePassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('HTTP/1.1 405 Method Not Allowed');
            exit;
        }

        // 1. Xác thực người dùng qua JWT Token
        $userPayload = getJwtUser(); 
        $userId = (int)$userPayload['id'];

        // 2. Đọc dữ liệu JSON thô từ Postman
        $rawJson = file_get_contents('php://input');
        $data = json_decode($rawJson, true) ?? [];

        $oldPassword = $data['old_password'] ?? '';
        $newPassword = $data['new_password'] ?? '';
        $confirmPassword = $data['password_confirm'] ?? '';
        $errors = [];

        // 3. Kiểm tra dữ liệu hợp lệ
        if (empty($oldPassword) || empty($newPassword)) {
            $errors[] = 'Vui lòng điền đầy đủ mật khẩu cũ và mật khẩu mới.';
        }
        if (mb_strlen($newPassword) < 6) {
            $errors[] = 'Mật khẩu mới phải từ 6 ký tự trở lên.';
        }
        if ($newPassword !== $confirmPassword) {
            $errors[] = 'Xác nhận mật khẩu mới không trùng khớp.';
        }

        // 4. Kiểm tra mật khẩu cũ có đúng không
        if (empty($errors)) {
            $dbUser = $this->userModel->findByEmail($userPayload['email']);
            // password_verify kiểm tra chuỗi mật khẩu thô so với chuỗi băm trong DB
            if (!$dbUser || !password_verify($oldPassword, $dbUser['password'])) {
                $errors[] = 'Mật khẩu cũ không chính xác.';
            }
        }

        header('Content-Type: application/json; charset=utf-8');
        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => $errors]);
            return;
        }

        // 5. Tiến hành đổi mật khẩu (Hàm updatePassword tự mã hóa bằng password_hash)
        $this->userModel->updatePassword($userId, $newPassword);

        echo json_encode([
            'success' => true,
            'message' => 'Thay đổi mật khẩu thành công!'
        ]);
    }

    // =========================================================================
    // 📩 API 2: QUÊN MẬT KHẨU (MÔ PHỎNG / KHÔNG CẦN TOKEN ĐĂNG NHẬP)
    // =========================================================================
    public function apiForgotPassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('HTTP/1.1 405 Method Not Allowed');
            exit;
        }

        $rawJson = file_get_contents('php://input');
        $data = json_decode($rawJson, true) ?? [];
        $email = trim($data['email'] ?? '');

        header('Content-Type: application/json; charset=utf-8');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Email không đúng định dạng.']);
            return;
        }

        $user = $this->userModel->findByEmail($email);
        
        // Tạo token khôi phục ngẫu nhiên (32 ký tự)
        $token = bin2hex(random_bytes(16));
        
        if ($user) {
            // Lưu token vào DB (nếu có bảng) hoặc bạn có thể mô phỏng trả về luôn
            // Ở đây gọi hàm model để lưu thông tin reset
            $this->userModel->createPasswordReset($email, $token);
        }

        // Vì đây là API mô phỏng/cơ bản, ta trả về token ngay trong JSON để dễ làm bài tập và test Postman 
        // thay vì bắt buộc người dùng mở email_log.txt lên lấy.
        echo json_encode([
            'success' => true,
            'message' => 'Yêu cầu khôi phục mật khẩu thành công. Hãy dùng mã token dưới đây để đặt lại mật khẩu mới.',
            'simulated_token' => $token,
            'note' => 'Trong thực tế, token này sẽ được đính kèm vào link gửi trực tiếp tới email người dùng.'
        ]);
    }

    // =========================================================================
    // 🔄 API 3: ĐẶT LẠI MẬT KHẨU MỚI TỪ TOKEN (KHÔNG CẦN TOKEN ĐĂNG NHẬP)
    // =========================================================================
    public function apiResetPassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('HTTP/1.1 405 Method Not Allowed');
            exit;
        }

        $rawJson = file_get_contents('php://input');
        $data = json_decode($rawJson, true) ?? [];

        $token = trim($data['token'] ?? '');
        $newPassword = $data['password'] ?? '';
        $confirmPassword = $data['password_confirm'] ?? '';
        $errors = [];

        if (empty($token)) $errors[] = 'Mã Token khôi phục mật khẩu là bắt buộc.';
        if (mb_strlen($newPassword) < 6) $errors[] = 'Mật khẩu mới phải từ 6 ký tự trở lên.';
        if ($newPassword !== $confirmPassword) $errors[] = 'Xác nhận mật khẩu không trùng khớp.';

        // Xác thực Token xem có tồn tại hoặc hết hạn hay không
        $resetRecord = null;
        if (empty($errors)) {
            $resetRecord = $this->userModel->findPasswordReset($token);
            if (!$resetRecord) {
                $errors[] = 'Mã Token không hợp lệ hoặc đã hết hạn (Hiệu lực 1 giờ).';
            }
        }

        header('Content-Type: application/json; charset=utf-8');
        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => $errors]);
            return;
        }

        // Lấy thông tin user dựa vào email từ bản ghi token
        $user = $this->userModel->findByEmail($resetRecord['email']);
        if ($user) {
            // Tiến hành cập nhật mật khẩu mới (Mã hóa bằng password_hash)
            $this->userModel->updatePassword((int)$user['id'], $newPassword);
            // Xóa mã token này đi để tránh dùng lại lần 2
            $this->userModel->markPasswordResetUsed($token);
        }

        echo json_encode([
            'success' => true,
            'message' => 'Đặt lại mật khẩu mới thành công! Bạn có thể sử dụng mật khẩu này để đăng nhập.'
        ]);
    }
}
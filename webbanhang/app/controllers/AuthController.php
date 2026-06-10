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
                $otp = $this->generateOtp();
                $this->userModel->createEmailOtp($userId, $this->hashOtp($otp));

                $this->sendOtpEmail($email, $name, $otp);
                $_SESSION['pending_verification_email'] = $email;

                $_SESSION['flash_success'] = "Dang ky thanh cong! Vui long nhap ma OTP da gui den email.";
                redirect(url('auth', 'verifyOtp', ['email' => $email]));
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
            redirect(url());
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
            } elseif (empty($user['email_verified_at'])) {
                $otp = $this->generateOtp();
                $this->userModel->createEmailOtp((int)$user['id'], $this->hashOtp($otp));
                $this->sendOtpEmail($user['email'], $user['name'], $otp);
                $_SESSION['pending_verification_email'] = $user['email'];
                $_SESSION['flash_error'] = 'Tai khoan chua kich hoat. Vui long nhap ma OTP vua gui den email.';
                redirect(url('auth', 'verifyOtp', ['email' => $user['email']]));
            } elseif (!$user['is_active']) {
                $errors[] = 'Tai khoan cua ban dang bi khoa. Vui long lien he quan tri vien.';
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
                redirect(url());
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
            $_SESSION['flash_error'] = 'Ma OTP khong hop le hoac da het han. Vui long dang ky/dang nhap lai de nhan ma moi.';
            redirect(url('auth', 'login'));
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_check();
            $otp = $this->otpFromPost();
            $errors = [];

            if (!preg_match('/^\d{6}$/', $otp)) {
                $errors[] = 'Vui long nhap day du 6 chu so OTP.';
            } elseif (!hash_equals((string)$record['token'], $this->hashOtp($otp))) {
                $errors[] = 'Ma OTP khong dung. Vui long kiem tra lai email cua ban.';
            }

            if (!$errors) {
                $this->userModel->verifyEmail((int)$record['user_id']);
                $this->userModel->markEmailVerificationUsedByUserId((int)$record['user_id']);
                unset($_SESSION['pending_verification_email']);

                $_SESSION['flash_success'] = 'Xac thuc OTP thanh cong! Ban co the dang nhap.';
                redirect(url('auth', 'login'));
            }
        } else {
            $errors = [];
        }

        $_SESSION['pending_verification_email'] = $email;
        $expiresAt = strtotime($record['expires_at']);
        $remainingSeconds = max(0, $expiresAt - time());
        $pageTitle = 'Xac thuc OTP';
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
            $_SESSION['flash_error'] = 'Khong the gui lai OTP cho tai khoan nay.';
            redirect(url('auth', 'login'));
        }

        $otp = $this->generateOtp();
        $this->userModel->createEmailOtp((int)$user['id'], $this->hashOtp($otp));
        $this->sendOtpEmail($user['email'], $user['name'], $otp);
        $_SESSION['pending_verification_email'] = $user['email'];
        $_SESSION['flash_success'] = 'Da gui lai ma OTP moi. Vui long kiem tra email cua ban.';
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
                    $resetUrl = url('auth', 'resetPassword', ['token' => $token]);
                    $this->sendResetEmail($email, $user['name'], $resetUrl);
                }
                // Luôn hiện thông báo thành công (bảo mật - không tiết lộ email tồn tại)
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

    // ============================
    // HÀM GỬI EMAIL (giả lập - ghi ra file log)
    // Trong production: thay bằng PHPMailer / SMTP
    // ============================
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

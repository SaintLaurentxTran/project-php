<?php

class UserModel
{
    public function __construct(private PDO $pdo) {}

    // Tìm user theo email
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    // Tìm user theo ID
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    // Tạo user mới (chờ xác thực email)
    public function create(string $name, string $email, string $password): int
    {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $this->pdo->prepare(
            "INSERT INTO users (name, email, password, role, is_active, created_at, updated_at)
             VALUES (?, ?, ?, 'user', 0, NOW(), NOW())"
        );
        $stmt->execute([$name, $email, $hash]);
        return (int)$this->pdo->lastInsertId();
    }

    // Xác thực email
    public function verifyEmail(int $userId): void
    {
        $this->pdo->prepare(
            "UPDATE users SET is_active=1, email_verified_at=NOW(), updated_at=NOW() WHERE id=?"
        )->execute([$userId]);
    }

    // Cập nhật mật khẩu
    public function updatePassword(int $userId, string $newPassword): void
    {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        $this->pdo->prepare(
            "UPDATE users SET password=?, updated_at=NOW() WHERE id=?"
        )->execute([$hash, $userId]);
    }

    // Kiểm tra mật khẩu, đồng thời nâng cấp dữ liệu cũ về bcrypt nếu cần
    public function verifyPassword(array $user, string $plainPassword): bool
    {
        $storedPassword = (string)($user['password'] ?? '');

        if ($storedPassword === '') {
            return false;
        }

        if (password_verify($plainPassword, $storedPassword)) {
            return true;
        }

        $legacyMd5 = strtolower($storedPassword);
        if (strlen($legacyMd5) === 32 && ctype_xdigit($legacyMd5) && hash_equals($legacyMd5, md5($plainPassword))) {
            $this->updatePassword((int)$user['id'], $plainPassword);
            return true;
        }

        if (hash_equals($storedPassword, $plainPassword)) {
            $this->updatePassword((int)$user['id'], $plainPassword);
            return true;
        }

        return false;
    }

    // Cập nhật thông tin hồ sơ
    public function updateProfile(int $userId, array $data): void
    {
        $this->pdo->prepare(
            "UPDATE users SET name=?, phone=?, address=?, updated_at=NOW() WHERE id=?"
        )->execute([$data['name'], $data['phone'] ?? null, $data['address'] ?? null, $userId]);
    }

    // Cập nhật avatar
    public function updateAvatar(int $userId, string $avatarPath): void
    {
        $this->pdo->prepare(
            "UPDATE users SET avatar=?, updated_at=NOW() WHERE id=?"
        )->execute([$avatarPath, $userId]);
    }

    // Lưu remember token
    public function setRememberToken(int $userId, string $token): void
    {
        $this->pdo->prepare(
            "UPDATE users SET remember_token=? WHERE id=?"
        )->execute([$token, $userId]);
    }

    // Xóa remember token
    public function clearRememberToken(int $userId): void
    {
        $this->pdo->prepare(
            "UPDATE users SET remember_token=NULL WHERE id=?"
        )->execute([$userId]);
    }

    // ====== ADMIN: quản lý người dùng ======

    public function paginate(int $page = 1, int $perPage = 15, string $search = ''): array
    {
        $offset = ($page - 1) * $perPage;
        $like = "%{$search}%";

        $total = $this->pdo->prepare(
            "SELECT COUNT(*) FROM users WHERE name LIKE ? OR email LIKE ?"
        );
        $total->execute([$like, $like]);
        $totalCount = (int)$total->fetchColumn();

        $stmt = $this->pdo->prepare(
            "SELECT id, name, email, role, is_active, avatar, phone, email_verified_at, created_at
             FROM users WHERE name LIKE ? OR email LIKE ?
             ORDER BY id DESC LIMIT ? OFFSET ?"
        );
        $stmt->bindValue(1, $like, PDO::PARAM_STR);
        $stmt->bindValue(2, $like, PDO::PARAM_STR);
        $stmt->bindValue(3, $perPage, PDO::PARAM_INT);
        $stmt->bindValue(4, $offset, PDO::PARAM_INT);
        $stmt->execute();
        $users = $stmt->fetchAll();

        return [
            'users'      => $users,
            'total'      => $totalCount,
            'page'       => $page,
            'perPage'    => $perPage,
            'totalPages' => (int)ceil($totalCount / $perPage),
        ];
    }

    public function setActive(int $userId, int $status): void
    {
        $this->pdo->prepare(
            "UPDATE users SET is_active=?, updated_at=NOW() WHERE id=?"
        )->execute([$status, $userId]);
    }

    public function setRole(int $userId, string $role): void
    {
        $this->pdo->prepare(
            "UPDATE users SET role=?, updated_at=NOW() WHERE id=?"
        )->execute([$role, $userId]);
    }

    public function delete(int $userId): void
    {
        $this->pdo->prepare("DELETE FROM users WHERE id=?")->execute([$userId]);
    }

    // ====== Password Reset ======

    public function createPasswordReset(string $email, string $token): void
    {
        // Xóa token cũ
        $this->pdo->prepare("DELETE FROM password_resets WHERE email=?")->execute([$email]);
        // Tạo mới, hết hạn sau 1 giờ
        $this->pdo->prepare(
            "INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))"
        )->execute([$email, $token]);
    }

    public function findPasswordReset(string $token): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM password_resets WHERE token=? AND used=0 AND expires_at > NOW() LIMIT 1"
        );
        $stmt->execute([$token]);
        return $stmt->fetch() ?: null;
    }

    public function markPasswordResetUsed(string $token): void
    {
        $this->pdo->prepare(
            "UPDATE password_resets SET used=1 WHERE token=?"
        )->execute([$token]);
    }

    // ====== Email Verification ======

    public function createEmailVerification(int $userId, string $token): void
    {
        $this->pdo->prepare("DELETE FROM email_verifications WHERE user_id=?")->execute([$userId]);
        $this->pdo->prepare(
            "INSERT INTO email_verifications (user_id, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 24 HOUR))"
        )->execute([$userId, $token]);
    }

    public function findEmailVerification(string $token): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM email_verifications WHERE token=? AND used=0 AND expires_at > NOW() LIMIT 1"
        );
        $stmt->execute([$token]);
        return $stmt->fetch() ?: null;
    }

    public function markEmailVerificationUsed(string $token): void
    {
        $this->pdo->prepare(
            "UPDATE email_verifications SET used=1 WHERE token=?"
        )->execute([$token]);
    }
}

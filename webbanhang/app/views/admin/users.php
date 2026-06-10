<?php require __DIR__ . '/../shares/header.php'; ?>

<div class="admin-page">
  <div class="admin-header">
    <h1>⚙ Quản Lý Người Dùng</h1>
    <div class="admin-actions">
      <a href="<?= e(url('admin', 'orders')) ?>" class="btn btn-outline">Don hang</a>
      <a href="<?= e(url()) ?>" class="btn btn-outline">← Về Trang Chủ</a>
    </div>
  </div>

  <!-- Thống kê -->
  <div class="admin-stats">
    <div class="stat-card">
      <div class="stat-num"><?= $result['total'] ?></div>
      <div class="stat-label">Tổng người dùng</div>
    </div>
  </div>

  <!-- Tìm kiếm -->
  <form method="GET" action="<?= e(url('admin', 'users')) ?>" class="admin-search-form">
    <input type="text" name="q" class="form-control" style="display:inline-block;width:300px"
           value="<?= e($_GET['q'] ?? '') ?>" placeholder="Tìm theo tên hoặc email...">
    <button type="submit" class="btn btn-primary">Tìm Kiếm</button>
    <?php if (!empty($_GET['q'])): ?>
      <a href="<?= e(url('admin', 'users')) ?>" class="btn btn-outline">Xóa Lọc</a>
    <?php endif; ?>
  </form>

  <!-- Bảng người dùng -->
  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Ảnh</th>
          <th>Tên</th>
          <th>Email</th>
          <th>Vai Trò</th>
          <th>Trạng Thái</th>
          <th>Email Xác Thực</th>
          <th>Ngày Tạo</th>
          <th>Hành Động</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($result['users'] as $u): ?>
        <tr class="<?= !$u['is_active'] ? 'row-locked' : '' ?>">
          <td><?= $u['id'] ?></td>
          <td>
            <img class="table-avatar" src="<?= e(base_url(avatar_url($u['avatar']))) ?>" alt="">
          </td>
          <td><?= e($u['name']) ?></td>
          <td><?= e($u['email']) ?></td>
          <td>
            <?php if ($u['role'] === 'admin'): ?>
              <span class="role-badge role-admin">Admin</span>
            <?php else: ?>
              <span class="role-badge role-user">User</span>
            <?php endif; ?>
          </td>
          <td>
            <?php if ($u['is_active']): ?>
              <span class="status-badge status-active">Hoạt động</span>
            <?php else: ?>
              <span class="status-badge status-locked">Bị khóa</span>
            <?php endif; ?>
          </td>
          <td>
            <?php if ($u['email_verified_at']): ?>
              <span class="badge-verified">Da xac thuc</span>
              <form method="POST" action="<?= e(url('admin', 'unverifyUserEmail')) ?>" class="inline-form"
                    onsubmit="return confirm('Bo xac thuc email nguoi dung nay?')">
                <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
                <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                <button type="submit" class="btn btn-warning btn-sm" title="Bo xac thuc email">
                  <span class="material-symbols-outlined">mark_email_unread</span>
                </button>
              </form>
            <?php else: ?>
              <span class="badge-unverified">Chua xac thuc</span>
              <form method="POST" action="<?= e(url('admin', 'verifyUserEmail')) ?>" class="inline-form"
                    onsubmit="return confirm('Xac thuc email nguoi dung nay?')">
                <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
                <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                <button type="submit" class="btn btn-success btn-sm" title="Xac thuc email">
                  <span class="material-symbols-outlined">mark_email_read</span>
                </button>
              </form>
            <?php endif; ?>
          </td>
          <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
          <td class="action-cell">
            <?php if ($u['id'] !== (int)$_SESSION['user']['id']): ?>
            <!-- Khóa / Mở khóa -->
            <form method="POST" action="<?= e(url('admin', 'toggleActive')) ?>" class="inline-form"
                  onsubmit="return confirm('Bạn chắc chắn?')">
              <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
              <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
              <input type="hidden" name="status" value="<?= $u['is_active'] ? 0 : 1 ?>">
              <button type="submit" class="btn <?= $u['is_active'] ? 'btn-warning' : 'btn-success' ?> btn-sm"
                      title="<?= $u['is_active'] ? 'Khóa tài khoản' : 'Mở khóa tài khoản' ?>">
                <span class="material-symbols-outlined"><?= $u['is_active'] ? 'lock' : 'lock_open' ?></span>
              </button>
            </form>

            <!-- Đổi vai trò -->
            <form method="POST" action="<?= e(url('admin', 'changeRole')) ?>" class="inline-form"
                  onsubmit="return confirm('Thay đổi vai trò?')">
              <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
              <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
              <input type="hidden" name="role" value="<?= $u['role'] === 'admin' ? 'user' : 'admin' ?>">
              <button type="submit" class="btn btn-info btn-sm"
                      title="Chuyển thành <?= $u['role'] === 'admin' ? 'User' : 'Admin' ?>">
                <span class="material-symbols-outlined">swap_horiz</span>
                <?= $u['role'] === 'admin' ? '→User' : '→Admin' ?>
              </button>
            </form>

            <!-- Xóa -->
            <form method="POST" action="<?= e(url('admin', 'deleteUser')) ?>" class="inline-form"
                  onsubmit="return confirm('XÓA tài khoản này? Không thể hoàn tác!')">
              <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
              <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
              <button type="submit" class="btn btn-danger btn-sm" title="Xóa tài khoản">
                <span class="material-symbols-outlined">delete</span>
              </button>
            </form>
            <?php else: ?>
              <span class="text-muted">— Tài khoản của bạn</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($result['users'])): ?>
          <tr><td colspan="9" style="text-align:center;padding:30px;color:#999">Không tìm thấy người dùng nào.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Phân trang -->
  <?php if ($result['totalPages'] > 1): ?>
  <div class="pagination">
    <?php for ($i = 1; $i <= $result['totalPages']; $i++): ?>
      <a href="<?= e(url('admin', 'users', ['page' => $i, 'q' => $_GET['q'] ?? ''])) ?>"
         class="page-btn <?= $i === $result['page'] ? 'active' : '' ?>">
        <?= $i ?>
      </a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../shares/footer.php'; ?>

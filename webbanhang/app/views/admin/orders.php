<?php require __DIR__ . '/../shares/header.php'; ?>

<div class="admin-page">
  <div class="admin-header">
    <h1><span class="material-symbols-outlined">receipt_long</span> Quan Ly Don Hang</h1>
    <div class="admin-actions">
      <a href="index.php?c=admin&a=users" class="btn btn-outline">Nguoi dung</a>
      <a href="index.php" class="btn btn-outline">Ve trang chu</a>
    </div>
  </div>

  <div class="admin-stats">
    <div class="stat-card">
      <div class="stat-num"><?= (int)$stats['totalOrders'] ?></div>
      <div class="stat-label">Tong don hang</div>
    </div>
    <div class="stat-card">
      <div class="stat-num"><?= (int)$stats['pendingOrders'] ?></div>
      <div class="stat-label">Cho xu ly</div>
    </div>
    <div class="stat-card">
      <div class="stat-num"><?= money_vnd($stats['totalRevenue']) ?></div>
      <div class="stat-label">Doanh thu</div>
    </div>
  </div>

  <form method="GET" action="index.php" class="admin-search-form">
    <input type="hidden" name="c" value="admin">
    <input type="hidden" name="a" value="orders">
    <input type="text" name="q" class="form-control admin-search-input"
           value="<?= e($_GET['q'] ?? '') ?>" placeholder="Tim ma don, ten khach, so dien thoai...">
    <select name="status" class="form-control admin-select">
      <option value="">Tat ca trang thai</option>
      <?php foreach ($statusLabels as $key => $label): ?>
        <option value="<?= e($key) ?>" <?= ($status ?? '') === $key ? 'selected' : '' ?>><?= e($label) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-primary">Tim kiem</button>
    <?php if (!empty($_GET['q']) || !empty($_GET['status'])): ?>
      <a href="index.php?c=admin&a=orders" class="btn btn-outline">Xoa loc</a>
    <?php endif; ?>
  </form>

  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Ma don</th>
          <th>Khach hang</th>
          <th>Dien thoai</th>
          <th>Tong tien</th>
          <th>Thanh toan</th>
          <th>Trang thai</th>
          <th>Ngay tao</th>
          <th>Hanh dong</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($result['orders'] as $order): ?>
          <tr>
            <td class="bold"><?= e($order['order_code']) ?></td>
            <td>
              <div class="bold"><?= e($order['customer_name']) ?></div>
              <?php if (!empty($order['user_email'])): ?>
                <div class="text-muted"><?= e($order['user_email']) ?></div>
              <?php endif; ?>
            </td>
            <td><?= e($order['customer_phone']) ?></td>
            <td class="bold primary"><?= money_vnd($order['total_amount']) ?></td>
            <td><?= e($order['payment_method']) ?></td>
            <td>
              <form method="POST" action="index.php?c=admin&a=updateOrderStatus" class="status-form">
                <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
                <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
                <input type="hidden" name="back" value="index.php?c=admin&a=orders&page=<?= (int)$result['page'] ?>&q=<?= urlencode($_GET['q'] ?? '') ?>&status=<?= urlencode($_GET['status'] ?? '') ?>">
                <select name="status" class="form-control status-select" onchange="this.form.submit()">
                  <?php foreach ($statusLabels as $key => $label): ?>
                    <option value="<?= e($key) ?>" <?= $order['status'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                  <?php endforeach; ?>
                </select>
              </form>
            </td>
            <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
            <td>
              <a class="btn btn-info btn-sm" href="index.php?c=admin&a=orderDetail&id=<?= (int)$order['id'] ?>">
                <span class="material-symbols-outlined">visibility</span> Xem
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($result['orders'])): ?>
          <tr><td colspan="8" style="text-align:center;padding:30px;color:#999">Chua co don hang nao.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <?php if ($result['totalPages'] > 1): ?>
    <div class="pagination">
      <?php for ($i = 1; $i <= $result['totalPages']; $i++): ?>
        <a href="index.php?c=admin&a=orders&page=<?= $i ?>&q=<?= urlencode($_GET['q'] ?? '') ?>&status=<?= urlencode($_GET['status'] ?? '') ?>"
           class="page-btn <?= $i === $result['page'] ? 'active' : '' ?>">
          <?= $i ?>
        </a>
      <?php endfor; ?>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../shares/footer.php'; ?>

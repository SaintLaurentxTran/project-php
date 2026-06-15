<?php require __DIR__ . '/../shares/header.php'; ?>

<div class="admin-page">
  <div class="admin-header">
    <h1><span class="material-symbols-outlined">receipt_long</span> Quan Ly Don Hang</h1>
    <div class="admin-actions">
      <a href="<?= e(url('admin', 'users')) ?>" class="btn btn-outline">Người dùng</a>
      <a href="<?= e(url()) ?>" class="btn btn-outline">Ve trang chu</a>
    </div>
  </div>

  <div class="admin-stats">
    <div class="stat-card">
      <div class="stat-num"><?= (int)$stats['totalOrders'] ?></div>
      <div class="stat-label">Tổng đơn hàng</div>
    </div>
    <div class="stat-card">
      <div class="stat-num"><?= (int)$stats['pendingOrders'] ?></div>
      <div class="stat-label">Chờ xử lý</div>
    </div>
    <div class="stat-card">
      <div class="stat-num"><?= money_vnd($stats['totalRevenue']) ?></div>
      <div class="stat-label">Doanh thu</div>
    </div>
  </div>

  <form method="GET" action="<?= e(url('admin', 'orders')) ?>" class="admin-search-form">
    <input type="text" name="q" class="form-control admin-search-input"
           value="<?= e($_GET['q'] ?? '') ?>" placeholder="Tìm mã đơn, tên khách, số điện thoại...">
    <select name="status" class="form-control admin-select">
      <option value="">Tat ca trang thai</option>
      <?php foreach ($statusLabels as $key => $label): ?>
        <option value="<?= e($key) ?>" <?= ($status ?? '') === $key ? 'selected' : '' ?>><?= e($label) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-primary">Tìm kiếm</button>
    <?php if (!empty($_GET['q']) || !empty($_GET['status'])): ?>
      <a href="<?= e(url('admin', 'orders')) ?>" class="btn btn-outline">Xóa lọc</a>
    <?php endif; ?>
  </form>

  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Mã đơn</th>
          <th>Khách hàng</th>
          <th>Điện thoại</th>
          <th>Tổng tiền</th>
          <th>Thanh toán</th>
          <th>Trạng thái</th>
          <th>Ngày tạo</th>
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
            
            <td>
              <span class="bold" style="text-transform: uppercase; display: block; margin-bottom: 2px;"><?= e($order['payment_method']) ?></span>
              <?php 
                // Đồng bộ dữ liệu nếu database trả về trống (null) hoặc chưa đồng bộ cột
                $checkStatus = isset($order['payment_status']) ? trim($order['payment_status']) : 'unpaid';
                if ($checkStatus === 'paid'): 
              ?>
                <span style="color: #2ec4b6; font-size: 13px; font-weight: bold; display: inline-block;">● Đã thanh toán</span>
              <?php else: ?>
                <span style="color: #e71d36; font-size: 13px; font-weight: bold; display: inline-block;">● Chưa thanh toán</span>
              <?php endif; ?>
            </td>
            
            <td>
              <form method="POST" action="<?= e(url('admin', 'updateOrderStatus')) ?>" class="status-form">
                <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
                <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
                <input type="hidden" name="back" value="<?= e(url('admin', 'orders', ['page' => (int)$result['page'], 'q' => $_GET['q'] ?? '', 'status' => $_GET['status'] ?? ''])) ?>">
                <select name="status" class="form-control status-select" onchange="this.form.submit()" style="padding: 4px 8px; border-radius: 4px;">
                  <?php foreach ($statusLabels as $key => $label): ?>
                    <option value="<?= e($key) ?>" <?= $order['status'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                  <?php endforeach; ?>
                </select>
              </form>
            </td>
            
            <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
            
            <td>
              <a class="btn btn-info btn-sm" href="<?= e(url('admin', 'orderDetail', ['id' => (int)$order['id']])) ?>">
                <span class="material-symbols-outlined" style="font-size: 16px; vertical-align: middle;">visibility</span> Xem
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($result['orders'])): ?>
          <tr><td colspan="8" style="text-align:center;padding:30px;color:#999">Chưa có đơn hàng nào.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <?php if ($result['totalPages'] > 1): ?>
    <div class="pagination">
      <?php for ($i = 1; $i <= $result['totalPages']; $i++): ?>
        <a href="<?= e(url('admin', 'orders', ['page' => $i, 'q' => $_GET['q'] ?? '', 'status' => $_GET['status'] ?? ''])) ?>"
           class="page-btn <?= $i === $result['page'] ? 'active' : '' ?>">
          <?= $i ?>
        </a>
      <?php endfor; ?>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../shares/footer.php'; ?>
<?php require __DIR__ . '/../shares/header.php'; ?>

<div class="admin-page">
  <div class="admin-header">
    <h1><span class="material-symbols-outlined">receipt_long</span> Don hang <?= e($order['order_code']) ?></h1>
    <a href="index.php?c=admin&a=orders" class="btn btn-outline">Ve danh sach</a>
  </div>

  <div class="order-detail-grid">
    <section class="profile-section">
      <h2 class="section-title">Thong tin don hang</h2>
      <div class="detail-row"><span>Ma don</span><strong><?= e($order['order_code']) ?></strong></div>
      <div class="detail-row"><span>Trang thai</span><strong><?= e($statusLabels[$order['status']] ?? $order['status']) ?></strong></div>
      <div class="detail-row"><span>Thanh toan</span><strong><?= e($order['payment_method']) ?></strong></div>
      <div class="detail-row"><span>Ngay tao</span><strong><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></strong></div>
      <?php if (!empty($order['user_email'])): ?>
        <div class="detail-row"><span>Tai khoan</span><strong><?= e($order['user_email']) ?></strong></div>
      <?php endif; ?>

      <form method="POST" action="index.php?c=admin&a=updateOrderStatus" class="order-status-box">
        <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
        <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
        <input type="hidden" name="back" value="index.php?c=admin&a=orderDetail&id=<?= (int)$order['id'] ?>">
        <label for="status">Cap nhat trang thai</label>
        <div class="status-update-row">
          <select id="status" name="status" class="form-control">
            <?php foreach ($statusLabels as $key => $label): ?>
              <option value="<?= e($key) ?>" <?= $order['status'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
          </select>
          <button type="submit" class="btn btn-primary">Luu</button>
        </div>
      </form>
    </section>

    <section class="profile-section">
      <h2 class="section-title">Nguoi nhan</h2>
      <div class="detail-row"><span>Ho ten</span><strong><?= e($order['customer_name']) ?></strong></div>
      <div class="detail-row"><span>Dien thoai</span><strong><?= e($order['customer_phone']) ?></strong></div>
      <div class="detail-address"><?= e($order['customer_address']) ?></div>
    </section>
  </div>

  <div class="admin-table-wrap mt-24">
    <table class="admin-table">
      <thead>
        <tr>
          <th>San pham</th>
          <th>Don gia</th>
          <th>So luong</th>
          <th>Thanh tien</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $item): ?>
          <tr>
            <td class="bold"><?= e($item['product_name']) ?></td>
            <td><?= money_vnd($item['price']) ?></td>
            <td><?= (int)$item['qty'] ?></td>
            <td class="bold"><?= money_vnd((int)$item['price'] * (int)$item['qty']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <section class="profile-section order-total-box">
    <div class="detail-row"><span>Phi van chuyen</span><strong><?= money_vnd($order['shipping_fee']) ?></strong></div>
    <div class="detail-row"><span>Voucher</span><strong>-<?= money_vnd($order['voucher_amount']) ?></strong></div>
    <div class="detail-row"><span>Shopee xu</span><strong>-<?= money_vnd($order['coins_used']) ?></strong></div>
    <div class="detail-row total"><span>Tong thanh toan</span><strong><?= money_vnd($order['total_amount']) ?></strong></div>
  </section>
</div>

<?php require __DIR__ . '/../shares/footer.php'; ?>

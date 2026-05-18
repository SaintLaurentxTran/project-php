<?php include 'app/views/shared/header.php'; ?>
<h1>Danh sách sản phẩm</h1>
<a href="/webbanhang/Product/add" class="btn btn-success mb-2">Thêm sản phẩm mới</a>
<ul class="list-group">
<?php foreach ($products as $product): ?>
<li class="list-group-item">
<h2> <a href="/webbanhang/Product/show/<?php echo $product->id; ?>" >
<?php echo htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'); ?>
</a>
</h2>
<p><?php echo htmlspecialchars($product->description, ENT_QUOTES, 'UTF-
8'); ?></p>
<p>Giá: <?php echo htmlspecialchars($product->price, ENT_QUOTES, 'UTF-8');
?></p>
<p><strong>Danh mục:</strong> <?php echo htmlspecialchars($product->category_name, ENT_QUOTES, 'UTF-8'); ?></p>
<a href="/webbanhang/Product/edit/<?php echo $product->id; ?>" class="btn
btn-warning">Sửa</a>
<a href="/webbanhang/Product/delete/<?php echo $product->id; ?>"
class="btn btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm
này?');">Xóa</a>
</li>
<?php endforeach; ?>
</ul>
<?php include 'app/views/shared/footer.php'; ?>
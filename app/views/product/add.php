<!DOCTYPE html>
<html>
<head>
<title>Thêm sản phẩm</title>
<script>
function validateForm() {
let name = document.getElementById('name').value;
let price = document.getElementById('price').value;
let errors = [];
if (name.length < 10 || name.length > 100) {
errors.push('Tên sản phẩm phải có từ 10 đến 100 ký tự.');
}
if (price <= 0 || isNaN(price)) {
errors.push('Giá phải là một số dương lớn hơn 0.');
}
if (errors.length > 0) {
alert(errors.join('\n'));
return false;
}
return true;
}
</script>
</head>
<body>
<h1>Thêm sản phẩm mới</h1>
<?php if (!empty($errors)): ?>
<ul>
<?php foreach ($errors as $error): ?>
<li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
<?php endforeach; ?>
</ul>
<?php endif; ?>
<form method="POST" action="/project1/Product/add" onsubmit="return
validateForm();">
<label for="name">Tên sản phẩm:</label>
<input type="text" id="name" name="name" required><br><br>
<label for="description">Mô tả:</label>
<textarea id="description" name="description" required></textarea><br><br>
<label for="price">Giá:</label>
<input type="number" id="price" name="price" step="0.01" required><br><br>
<button type="submit">Thêm sản phẩm</button>
</form>
<a href="/project1/Product/list">Quay lại danh sách sản phẩm</a>
</body>
</html>
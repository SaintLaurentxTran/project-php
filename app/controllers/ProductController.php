<?php
require_once 'app/models/ProductModel.php';
class ProductController
{
private $products = [];
private $allowedImageTypes = [
'image/jpeg' => 'jpg',
'image/png' => 'png',
'image/gif' => 'gif',
'image/webp' => 'webp'
];

public function __construct()
{
// Giả sử chúng ta lưu trữ sản phẩm trong session để giữ lại khi làm mới trang
session_start();
if (isset($_SESSION['products'])) {
$this->products = $_SESSION['products'];
}
}
private function ensureUploadDirectory()
{
$uploadDir = dirname(__DIR__, 2) . '/public/uploads';
if (!is_dir($uploadDir)) {
mkdir($uploadDir, 0755, true);
}
return $uploadDir;
}
private function handleUploadedImages($fieldName, &$errors)
{
$uploadedImages = [];
if (empty($_FILES[$fieldName]) || empty($_FILES[$fieldName]['name'][0])) {
return $uploadedImages;
}
$uploadDir = $this->ensureUploadDirectory();
$fileData = $_FILES[$fieldName];
foreach ($fileData['name'] as $index => $originalName) {
$errorCode = $fileData['error'][$index];
if ($errorCode === UPLOAD_ERR_NO_FILE) {
continue;
}
if ($errorCode !== UPLOAD_ERR_OK) {
$errors[] = 'Tải ảnh thất bại. Vui lòng thử lại.';
continue;
}
$tmpName = $fileData['tmp_name'][$index];
$imageInfo = getimagesize($tmpName);
if ($imageInfo === false || !isset($this->allowedImageTypes[$imageInfo['mime']])) {
$errors[] = 'Vui lòng chọn tệp hình ảnh hợp lệ (JPG, PNG, GIF, WEBP).';
continue;
}
$extension = $this->allowedImageTypes[$imageInfo['mime']];
$fileName = uniqid('product_', true) . '.' . $extension;
$destination = $uploadDir . '/' . $fileName;
if (!move_uploaded_file($tmpName, $destination)) {
$errors[] = 'Không thể lưu hình ảnh. Vui lòng thử lại.';
continue;
}
$uploadedImages[] = 'public/uploads/' . $fileName;
}
return $uploadedImages;
}
private function deleteImages($imagePaths)
{
$baseDir = dirname(__DIR__, 2) . '/';
$uploadDir = $this->ensureUploadDirectory();
$uploadDirReal = realpath($uploadDir);
if ($uploadDirReal === false) {
return;
}
foreach ($imagePaths as $imagePath) {
if (!is_string($imagePath)) {
continue;
}
$safePath = ltrim($imagePath, '/');
if (strpos($safePath, 'public/uploads/') !== 0) {
continue;
}
$fullPath = $baseDir . $safePath;
$realPath = realpath($fullPath);
if ($realPath === false) {
continue;
}
if (strpos($realPath, $uploadDirReal) !== 0) {
continue;
}
if (is_file($realPath)) {
unlink($realPath);
}
}
}
public function index()
{
$this->list();
}
public function list()
{
// Hiển thị danh sách sản phẩm
$products = $this->products;
include 'app/views/product/list.php';
}
public function add()
{
$errors = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
$name = $_POST['name'];
$description = $_POST['description'];
$price = $_POST['price'];
// Kiểm tra tên sản phẩm
if (empty($name)) {
$errors[] = 'Tên sản phẩm là bắt buộc.';
} elseif (strlen($name) < 10 || strlen($name) > 100) {
$errors[] = 'Tên sản phẩm phải có từ 10 đến 100 ký tự.';
}
// Kiểm tra giá
if (!is_numeric($price) || $price <= 0) {
$errors[] = 'Giá phải là một số dương lớn hơn 0.';
}
if (empty($errors)) {
$uploadedImages = $this->handleUploadedImages('images', $errors);
if (!empty($errors) && !empty($uploadedImages)) {
$this->deleteImages($uploadedImages);
}
}
if (empty($errors)) {
$id = count($this->products) + 1;
$product = new ProductModel($id, $name, $description, $price, $uploadedImages ?? []);
$this->products[] = $product;

$_SESSION['products'] = $this->products;
header('Location: /project1/Product/list');
exit();
}
}
include 'app/views/product/add.php';
}
public function edit($id)
{
    $errors = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
foreach ($this->products as $key => $product) {
if ($product->getID() == $id) {
$name = $_POST['name'] ?? '';
$description = $_POST['description'] ?? '';
$price = $_POST['price'] ?? '';
if (empty($name)) {
$errors[] = 'Tên sản phẩm là bắt buộc.';
} elseif (strlen($name) < 10 || strlen($name) > 100) {
$errors[] = 'Tên sản phẩm phải có từ 10 đến 100 ký tự.';
}
if (!is_numeric($price) || $price <= 0) {
$errors[] = 'Giá phải là một số dương lớn hơn 0.';
}
if (empty($errors)) {
    $removeImages = $_POST['remove_images'] ?? [];
    if (!is_array($removeImages)) {
    $removeImages = [];
    }
    $currentImages = $this->products[$key]->getImages();
    $removeImages = array_values(array_filter($removeImages, function ($imagePath) use ($currentImages) {
    if (!is_string($imagePath)) {
    return false;
    }
    $safePath = ltrim($imagePath, '/');
    if (strpos($safePath, 'public/uploads/') !== 0) {
    return false;
    }
    return in_array($imagePath, $currentImages, true);
    }));
    $remainingImages = array_values(array_diff($currentImages, $removeImages));
    $uploadedImages = $this->handleUploadedImages('images', $errors);
    if (!empty($errors) && !empty($uploadedImages)) {
    $this->deleteImages($uploadedImages);
    }
    if (empty($errors)) {
    $this->products[$key]->setName($name);
    $this->products[$key]->setDescription($description);
    $this->products[$key]->setPrice($price);
    $this->products[$key]->setImages(array_merge($remainingImages, $uploadedImages));
    $this->deleteImages($removeImages);
    $_SESSION['products'] = $this->products;
    header('Location: /project1/Product/list');
    exit();
    }
}
break;
}
}
}
foreach ($this->products as $product) {
if ($product->getID() == $id) {
include 'app/views/product/edit.php';
return;
}
}
die('Product not found');
}
public function delete($id)
{
foreach ($this->products as $key => $product) {
if ($product->getID() == $id) {
    $this->deleteImages($product->getImages());
unset($this->products[$key]);
break;
}
}
$this->products = array_values($this->products);

$_SESSION['products'] = $this->products;
header('Location: /project1/Product/list');
exit();
}
}
?>

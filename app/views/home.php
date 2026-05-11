<?php
$pageTitle = 'SnackHub - Trang chủ';
$activePage = 'home';
$showHero = true;
include 'app/views/layout/header.php';
$basePath = '/project1';
?>
<div class="container">
    <section class="mb-4">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="info-card h-100">
                    <h3>Giao diện chuyên nghiệp</h3>
                    <p>Thiết kế hiện đại với Bootstrap, tối ưu hiển thị trên mọi màn hình.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-card h-100">
                    <h3>Quản lý sản phẩm</h3>
                    <p>Thêm, sửa và xóa sản phẩm nhanh chóng với form rõ ràng, dễ dùng.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-card h-100">
                    <h3>Trải nghiệm mượt mà</h3>
                    <p>Thành phần điều hướng, banner và thẻ sản phẩm được đồng bộ đẹp mắt.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="text-center">
        <a href="<?php echo $basePath; ?>/Product/list" class="btn btn-primary btn-lg me-2 mb-2">Xem sản phẩm</a>
        <a href="<?php echo $basePath; ?>/Product/add" class="btn btn-outline-primary btn-lg mb-2">Thêm sản phẩm mới</a>
    </section>
</div>
<?php include 'app/views/layout/footer.php'; ?>

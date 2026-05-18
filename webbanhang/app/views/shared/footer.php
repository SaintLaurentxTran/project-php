        </div>
    </div>

    <footer class="bg-dark text-white mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="mb-3">
                        <i class="fas fa-info-circle"></i> Về Hệ Thống
                    </h5>
                    <p>Hệ thống quản lý sản phẩm hiện đại, cung cấp các tính năng quản lý sản phẩm toàn diện.</p>
                </div>
                <div class="col-md-6">
                    <h5 class="mb-3">
                        <i class="fas fa-link"></i> Liên Kết Nhanh
                    </h5>
                    <ul class="list-unstyled">
                        <li><a href="/webbanhang/" class="text-light">Trang Chủ</a></li>
                        <li><a href="/webbanhang/Product/" class="text-light">Sản Phẩm</a></li>
                        <li><a href="/webbanhang/Product/add" class="text-light">Thêm Mới</a></li>
                    </ul>
                </div>
            </div>
            <hr class="bg-secondary">
            <div class="text-center">
                <p class="mb-0">&copy; 2024 Hệ Thống Quản Lý Sản Phẩm. All rights reserved.</p>
                <small>Developed with <i class="fas fa-heart text-danger"></i> by Developer Team</small>
            </div>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Add active class to current navigation item
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname;
            const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
            navLinks.forEach(link => {
                if (link.getAttribute('href') === currentPath) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>

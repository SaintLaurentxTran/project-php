<?php
class DefaultController {
  protected function view($viewPath, $data = []) {
    extract($data);
    require __DIR__ . '/../views/shares/header.php';
    require __DIR__ . '/../views/' . $viewPath . '.php';
    require __DIR__ . '/../views/shares/footer.php';
  }

  protected function redirect($url) {
    header("Location: $url");
    exit;
  }
}
<?php
require_once __DIR__ . '/../models/ProductModel.php';

class CartController {
  public function __construct(private PDO $pdo) {}

  private function &cartRef(): array {
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    return $_SESSION['cart'];
  }

  public function index() {
    $pageTitle = "ShopeeFake - Giỏ hàng";
    require __DIR__ . '/../views/cart/index.php';
  }

  public function add() {
    $id = (int)($_POST['id'] ?? 0);
    $qty = max(1, (int)($_POST['qty'] ?? 1));

    $productModel = new ProductModel($this->pdo);
    $p = $productModel->find($id);
    if (!$p) {
      http_response_code(404);
      exit("Product not found");
    }

    $cart = &$this->cartRef();
    if (!isset($cart[$id])) {
      $cart[$id] = [
        'id' => $p['id'],
        'name' => $p['name'],
        'price' => (int)$p['price'],
        'image' => $p['thumb_url'],
        'qty' => 0,
        'city' => $p['city']
      ];
    }
    $cart[$id]['qty'] += $qty;

    redirect(url('cart', 'index'));
  }

  public function updateQty() {
    $id = (int)($_POST['id'] ?? 0);
    $qty = max(1, (int)($_POST['qty'] ?? 1));
    $cart = &$this->cartRef();
    if (isset($cart[$id])) $cart[$id]['qty'] = $qty;

    redirect(url('cart', 'index'));
  }

  public function remove() {
    $id = (int)($_POST['id'] ?? 0);
    $cart = &$this->cartRef();
    unset($cart[$id]);

    redirect(url('cart', 'index'));
  }

  public function clear() {
    $_SESSION['cart'] = [];
    redirect(url('cart', 'index'));
  }
}

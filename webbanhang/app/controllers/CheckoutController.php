<?php
require_once __DIR__ . '/../models/OrderModel.php';

class CheckoutController {
  public function __construct(private PDO $pdo) {}

  private function getCartItems(): array {
    return array_values($_SESSION['cart'] ?? []);
  }

  public function index() {
    $cartItems = $this->getCartItems();
    if (count($cartItems) === 0) {
      header("Location: index.php?c=cart&a=index");
      return;
    }
    $pageTitle = "ShopeeFake - Thanh toán";
    require __DIR__ . '/../views/checkout/index.php';
  }

  public function placeOrder() {
    $cartItems = $this->getCartItems();
    if (count($cartItems) === 0) {
      header("Location: index.php?c=cart&a=index");
      return;
    }

    $customer = [
      'name' => trim($_POST['name'] ?? 'Nguyễn Văn A'),
      'phone' => trim($_POST['phone'] ?? '090 000 0000'),
      'address' => trim($_POST['address'] ?? '123 Đường Lê Lợi, Quận 1, TP. Hồ Chí Minh')
    ];

    $payment = $_POST['payment_method'] ?? 'shopeepay';
    $shippingFee = 32000;
    $voucher = 50000;
    $coinsUsed = !empty($_POST['use_coins']) ? 20000 : 0;

    $orderModel = new OrderModel($this->pdo);
    $orderId = $orderModel->createOrder($customer, $cartItems, $shippingFee, $voucher, $coinsUsed, $payment);

    // clear cart after successful order
    $_SESSION['cart'] = [];

    header("Location: index.php?c=checkout&a=success&id=" . $orderId);
  }

  public function success() {
    $id = (int)($_GET['id'] ?? 0);
    $orderModel = new OrderModel($this->pdo);
    $order = $orderModel->find($id);
    if (!$order) {
      http_response_code(404);
      exit("Order not found");
    }
    $items = $orderModel->items($id);

    $pageTitle = "ShopeeFake - Thanh toán thành công";
    require __DIR__ . '/../views/checkout/success.php';
  }
}
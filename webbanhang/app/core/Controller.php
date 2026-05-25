<?php

class Controller
{
    protected function view(string $viewPath, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../views/shares/header.php';
        require __DIR__ . '/../views/' . $viewPath . '.php';
        require __DIR__ . '/../views/shares/footer.php';
    }

    protected function viewOnly(string $viewPath, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../views/' . $viewPath . '.php';
    }
}
<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/CategoryModel.php';

class CategoryController extends Controller
{
    public function index(): void
    {
        $model = new CategoryModel();
        $this->view('product/list', [
            'categories' => $model->all(),
        ]);
    }
}
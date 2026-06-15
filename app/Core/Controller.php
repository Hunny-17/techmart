<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Controller - base class cho mọi controller
 *
 * Cung cấp shortcut: view, json, redirect, validate
 */
abstract class Controller
{
    /**
     * Render view (tự động chọn layout theo path controller)
     */
    protected function view(string $view, array $data = [], ?string $layout = 'customer'): void
    {
        View::render($view, $data, $layout);
    }

    protected function json(mixed $data, int $status = 200): void
    {
        View::json($data, $status);
    }

    protected function redirect(string $path): never
    {
        redirect($path);
    }

    /**
     * Validate input. Nếu fails sẽ redirect back với errors + old input
     */
    protected function validate(array $data, array $rules): array
    {
        $v = new Validator($data, $rules);
        if ($v->fails()) {
            Flash::set('error', 'Vui lòng kiểm tra lại thông tin nhập.');
            Session::set('_errors', $v->errors());
            Session::set('_old',    $data);
            $back = $_SERVER['HTTP_REFERER'] ?? '/';
            $this->redirect($back);
        }
        return $data;
    }
}

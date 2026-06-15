<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Flash;
use App\Core\Session;
use App\Models\Order;
use App\Models\User;
use App\Models\Wishlist;

final class ProfileController extends Controller
{
    public function edit(): void
    {
        $user = (new User())->find((int)Auth::id());
        if ($user === null) {
            Auth::logout();
            $this->redirect('/login');
        }

        unset($user['password_hash']);

        $userId = (int)Auth::id();
        $order  = new Order();
        $stats  = [
            'order_count'    => $order->count(['user_id' => $userId]),
            'total_spent'    => $order->totalSpentByUser($userId),
            'total_savings'  => $order->totalSavingsByUser($userId),
            'wishlist_count' => (new Wishlist())->countByUser($userId),
        ];

        $this->view('profile/edit', [
            'title' => 'Tài khoản của tôi',
            'user'  => $user,
            'stats' => $stats,
        ]);
    }

    public function update(): void
    {
        Csrf::verify();

        $data = $this->validate($_POST, [
            'full_name' => ['required', 'max:100'],
            'phone' => ['max:20'],
            'address' => ['max:1000'],
        ]);

        $userModel = new User();
        $id = (int)Auth::id();
        $userModel->updateProfile($id, $data);

        $fresh = $userModel->find($id);
        if ($fresh !== null) {
            unset($fresh['password_hash']);
            Session::set('user', $fresh);
        }

        Flash::set('success', 'Cập nhật thông tin tài khoản thành công.');
        $this->redirect('/profile');
    }

    public function updatePassword(): void
    {
        Csrf::verify();

        $data = $this->validate($_POST, [
            'current_password' => ['required', 'min:6'],
            'password' => ['required', 'min:6', 'max:100'],
            'password_confirmation' => ['required', 'min:6'],
        ]);

        if ($data['password'] !== $data['password_confirmation']) {
            Flash::set('error', 'Mật khẩu xác nhận không khớp.');
            $this->redirect('/profile');
        }

        $userModel = new User();
        $user = $userModel->find((int)Auth::id());
        if ($user === null || !password_verify($data['current_password'], $user['password_hash'])) {
            Flash::set('error', 'Mật khẩu hiện tại không đúng.');
            $this->redirect('/profile');
        }

        $userModel->updatePassword((int)$user['id'], $data['password']);
        Flash::set('success', 'Đổi mật khẩu thành công.');
        $this->redirect('/profile');
    }
}

<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Flash;
use App\Core\Session;
use App\Models\User;
use App\Services\EmailVerificationNotifier;
use App\Services\LoginRateLimiter;
use App\Services\PasswordResetNotifier;

final class AuthController extends Controller
{
    public function loginForm(): void
    {
        if (Auth::check()) {
            $this->redirect(Auth::isAdmin() ? '/admin' : '/');
        }

        $this->view('auth/login', ['title' => 'Đăng nhập']);
    }

    public function login(): void
    {
        Csrf::verify();

        $data = $this->validate($_POST, [
            'email' => ['required', 'email'],
            'password' => ['required', 'min:6'],
        ]);

        // Rate limiting: chặn brute-force theo cặp IP + email.
        $ip      = (string)($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
        $limiter = new LoginRateLimiter();

        if ($limiter->tooManyAttempts($ip, $data['email'])) {
            $seconds = $limiter->availableIn($ip, $data['email']);
            $minutes = (int)ceil($seconds / 60);
            Flash::set('error', "Quá nhiều lần đăng nhập sai. Vui lòng thử lại sau {$minutes} phút.");
            $this->redirect('/login');
        }

        $ok = Auth::attempt($data['email'], $data['password']);
        $limiter->recordAttempt($ip, $data['email'], $ok);

        if (!$ok) {
            if (!Session::has('_flash')) {
                Flash::set('error', 'Email hoặc mật khẩu không đúng.');
            }
            $this->redirect('/login');
        }

        Flash::set('success', 'Đăng nhập thành công.');
        $this->redirect(Auth::isAdmin() ? '/admin' : '/');
    }

    public function registerForm(): void
    {
        if (Auth::check()) {
            $this->redirect('/');
        }

        $this->view('auth/register', ['title' => 'Đăng ký']);
    }

    public function forgotPasswordForm(): void
    {
        if (Auth::check()) {
            $this->redirect(Auth::isAdmin() ? '/admin' : '/');
        }

        $this->view('auth/forgot-password', ['title' => 'Quên mật khẩu']);
    }

    public function sendPasswordResetLink(): void
    {
        Csrf::verify();

        $data = $this->validate($_POST, [
            'email' => ['required', 'email', 'max:150'],
        ]);

        $userModel = new User();
        $user = $userModel->findByEmail($data['email']);

        if ($user === null || ($user['status'] ?? '') === 'locked') {
            Flash::set('success', 'Nếu email tồn tại trong hệ thống, TechMart sẽ gửi liên kết đặt lại mật khẩu.');
            $this->redirect('/forgot-password');
        }

        $retryAfter = $userModel->passwordResetRetryAfter($user);
        if ($retryAfter > 0) {
            Flash::set('warning', 'Vui lòng chờ ' . $retryAfter . ' giây trước khi gửi lại email đặt mật khẩu.');
            $this->redirect('/forgot-password');
        }

        $token = $userModel->createPasswordResetToken((int)$user['id']);
        $sent = (new PasswordResetNotifier())->send($user, $token);

        if ($sent) {
            Flash::set('success', 'TechMart đã gửi liên kết đặt lại mật khẩu. Vui lòng kiểm tra email.');
        } else {
            Flash::set('warning', 'Chưa gửi được email đặt lại mật khẩu. Vui lòng thử lại sau.');
        }

        $this->redirect('/forgot-password');
    }

    public function resetPasswordForm(): void
    {
        if (Auth::check()) {
            $this->redirect(Auth::isAdmin() ? '/admin' : '/');
        }

        $token = trim((string)($_GET['token'] ?? ''));
        $user = $token !== '' ? (new User())->findByPasswordResetToken($token) : null;
        if ($user === null) {
            Flash::set('error', 'Liên kết đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.');
            $this->redirect('/forgot-password');
        }

        $this->view('auth/reset-password', [
            'title' => 'Đặt lại mật khẩu',
            'token' => $token,
            'email' => $user['email'] ?? '',
        ]);
    }

    public function resetPassword(): void
    {
        Csrf::verify();

        $data = $this->validate($_POST, [
            'token' => ['required'],
            'password' => ['required', 'min:6', 'max:100'],
            'password_confirmation' => ['required', 'min:6', 'max:100'],
        ]);

        if ((string)$data['password'] !== (string)$data['password_confirmation']) {
            Flash::set('error', 'Mật khẩu nhập lại không khớp.');
            Session::set('_errors', ['password_confirmation' => 'Mật khẩu nhập lại không khớp']);
            $this->redirect('/reset-password?token=' . urlencode((string)$data['token']));
        }

        $userModel = new User();
        $user = $userModel->findByPasswordResetToken((string)$data['token']);
        if ($user === null) {
            Flash::set('error', 'Liên kết đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.');
            $this->redirect('/forgot-password');
        }

        $userModel->resetPassword((int)$user['id'], (string)$data['password']);
        Flash::set('success', 'Mật khẩu đã được cập nhật. Bạn có thể đăng nhập bằng mật khẩu mới.');
        $this->redirect('/login');
    }

    public function register(): void
    {
        Csrf::verify();

        // Honeypot: user thật để trống field này; nếu có giá trị → bot đang điền form tự động.
        // Giả vờ thành công để bot không biết bị detect và không thử cách khác.
        if (($_POST['website'] ?? '') !== '') {
            Flash::set('success', 'Đăng ký thành công. Vui lòng kiểm tra email để xác thực tài khoản.');
            $this->redirect('/login');
        }

        $data = $this->validate($_POST, [
            'full_name' => ['required', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'password' => ['required', 'min:6', 'max:100'],
            'phone' => ['max:20'],
        ]);

        $userModel = new User();
        if ($userModel->emailExists($data['email'])) {
            Flash::set('error', 'Email này đã được sử dụng.');
            $this->redirect('/register');
        }

        $userId = $userModel->register([
            'username' => $data['email'],
            'email' => $data['email'],
            'password' => $data['password'],
            'full_name' => $data['full_name'],
            'phone' => $data['phone'] ?? '',
            'address' => $data['address'] ?? '',
        ]);

        $createdUser = $userModel->find($userId);
        $token = $userModel->createEmailVerificationToken($userId);
        $sent = $createdUser !== null && (new EmailVerificationNotifier())->send($createdUser, $token);

        if ($sent) {
            Flash::set('success', 'Đăng ký thành công. Vui lòng kiểm tra email để xác thực tài khoản.');
        } else {
            Flash::set('warning', 'Đăng ký thành công nhưng email xác thực chưa gửi được. Vui lòng liên hệ quản trị viên.');
        }

        $this->redirect('/login');
    }

    public function verifyEmail(): void
    {
        $token = trim((string)($_GET['token'] ?? ''));
        if ($token === '') {
            Flash::set('error', 'Link xác thực email không hợp lệ.');
            $this->redirect('/login');
        }

        $userModel = new User();
        $user = $userModel->findByVerificationToken($token);
        if ($user === null) {
            if ($userModel->hasExpiredVerificationToken($token)) {
                Flash::set('error', 'Link xác thực đã hết hạn (24 giờ). Vui lòng đăng nhập và yêu cầu gửi lại email xác thực.');
            } else {
                Flash::set('error', 'Link xác thực email không hợp lệ hoặc đã được sử dụng.');
            }
            $this->redirect('/login');
        }

        $userModel->markEmailVerified((int)$user['id']);
        Flash::set('success', 'Email đã được xác thực. Bạn có thể đăng nhập.');
        $this->redirect('/login');
    }

    public function logout(): void
    {
        Csrf::verify();
        Auth::logout();
        Flash::set('success', 'Đã đăng xuất.');
        $this->redirect('/');
    }
}

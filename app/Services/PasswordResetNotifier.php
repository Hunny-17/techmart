<?php
declare(strict_types=1);

namespace App\Services;

final class PasswordResetNotifier
{
    public function __construct(private readonly Mailer $mailer = new Mailer())
    {
    }

    public function send(array $user, string $token): bool
    {
        $email = (string)($user['email'] ?? '');
        if ($email === '') {
            return false;
        }

        $name = (string)($user['full_name'] ?? 'quý khách');
        $resetUrl = function_exists('url') ? \url('/reset-password?token=' . urlencode($token)) : '#';
        $subject = 'TechMart đặt lại mật khẩu';

        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safeUrl = htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8');
        $html = '<div style="font-family:Arial,sans-serif;color:#111827;line-height:1.55">'
            . '<h2 style="margin:0 0 12px;color:#0d6efd">Đặt lại mật khẩu TechMart</h2>'
            . '<p>Xin chào <strong>' . $safeName . '</strong>,</p>'
            . '<p>TechMart vừa nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.</p>'
            . '<p><a href="' . $safeUrl . '" style="display:inline-block;background:#0d6efd;color:#fff;text-decoration:none;padding:10px 14px;border-radius:8px">Đặt lại mật khẩu</a></p>'
            . '<p style="color:#6b7280;font-size:13px">Liên kết này có hiệu lực trong 1 giờ và chỉ sử dụng được một lần.</p>'
            . '<p style="color:#6b7280;font-size:13px">Nếu nút không hoạt động, hãy copy liên kết này vào trình duyệt:<br>'
            . '<span style="word-break:break-all">' . $safeUrl . '</span></p>'
            . '<p style="color:#6b7280;font-size:13px">Nếu bạn không yêu cầu đặt lại mật khẩu, hãy bỏ qua email này.</p>'
            . '</div>';

        $text = "Xin chào {$name},\n"
            . "TechMart nhận được yêu cầu đặt lại mật khẩu.\n"
            . "Liên kết đặt lại mật khẩu có hiệu lực trong 1 giờ:\n"
            . "{$resetUrl}\n";

        return $this->mailer->send($email, $subject, $html, $text);
    }
}

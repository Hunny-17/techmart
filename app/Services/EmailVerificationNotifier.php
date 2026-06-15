<?php
declare(strict_types=1);

namespace App\Services;

final class EmailVerificationNotifier
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
        $verifyUrl = function_exists('url') ? \url('/verify-email?token=' . urlencode($token)) : '#';
        $subject = 'TechMart xác thực email đăng ký';

        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safeUrl = htmlspecialchars($verifyUrl, ENT_QUOTES, 'UTF-8');
        $html = '<div style="font-family:Arial,sans-serif;color:#111827;line-height:1.55">'
            . '<h2 style="margin:0 0 12px;color:#0d6efd">Xác thực email TechMart</h2>'
            . '<p>Xin chào <strong>' . $safeName . '</strong>,</p>'
            . '<p>Cảm ơn bạn đã đăng ký tài khoản TechMart. Vui lòng bấm nút bên dưới để xác thực email.</p>'
            . '<p><a href="' . $safeUrl . '" style="display:inline-block;background:#0d6efd;color:#fff;text-decoration:none;padding:10px 14px;border-radius:8px">Xác thực email</a></p>'
            . '<p style="color:#6b7280;font-size:13px">Nếu nút không hoạt động, hãy copy link này vào trình duyệt:<br>'
            . '<span style="word-break:break-all">' . $safeUrl . '</span></p>'
            . '<p style="color:#6b7280;font-size:13px">Nếu bạn không đăng ký tài khoản TechMart, vui lòng bỏ qua email này.</p>'
            . '</div>';

        $text = "Xin chào {$name},\n"
            . "Vui lòng xác thực email TechMart bằng link sau:\n"
            . "{$verifyUrl}\n";

        return $this->mailer->send($email, $subject, $html, $text);
    }
}

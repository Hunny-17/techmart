<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\App;

final class Mailer
{
    private ?string $lastLogFile = null;

    public function lastLogFile(): ?string
    {
        return $this->lastLogFile;
    }

    public function send(string $to, string $subject, string $html, ?string $text = null): bool
    {
        $this->lastLogFile = null;
        $to = trim($to);
        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $driver = App::$config['mail']['driver'] ?? 'log';
        return match ($driver) {
            'smtp'  => $this->sendWithSmtp($to, $subject, $html, $text),
            'mail'  => $this->sendWithPhpMail($to, $subject, $html, $text),
            'brevo' => $this->sendWithBrevo($to, $subject, $html, $text),
            default => $this->writeToLog($to, $subject, $html, $text),
        };
    }

    private function sendWithBrevo(string $to, string $subject, string $html, ?string $text): bool
    {
        $apiKey = App::$config['mail']['api_key'] ?? '';
        if ($apiKey === '') {
            error_log('Brevo: MAIL_API_KEY chưa được cấu hình.');
            return false;
        }

        $plain = $text ?? trim(strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $html)));
        $payload = json_encode([
            'sender'      => ['name' => $this->fromName(), 'email' => $this->fromEmail()],
            'to'          => [['email' => $to]],
            'subject'     => $subject,
            'htmlContent' => $html,
            'textContent' => $plain,
        ]);

        $ch = curl_init('https://api.brevo.com/v3/smtp/email');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'api-key: ' . $apiKey,
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            CURLOPT_TIMEOUT        => 15,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError !== '') {
            error_log('Brevo curl error: ' . $curlError);
            return false;
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            error_log('Brevo API error ' . $httpCode . ': ' . $response);
            return false;
        }

        return true;
    }

    private function sendWithPhpMail(string $to, string $subject, string $html, ?string $text): bool
    {
        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $this->formatAddress($this->fromEmail(), $this->fromName()),
        ];

        return mail($to, $this->encodeHeader($subject), $html, implode("\r\n", $headers));
    }

    private function sendWithSmtp(string $to, string $subject, string $html, ?string $text): bool
    {
        $cfg = App::$config['mail'] ?? [];
        $host = (string)($cfg['host'] ?? '');
        $port = (int)($cfg['port'] ?? 587);
        $encryption = strtolower((string)($cfg['encryption'] ?? 'tls'));
        $username = (string)($cfg['username'] ?? '');
        $password = (string)($cfg['password'] ?? '');
        $timeout = (int)($cfg['timeout'] ?? 15);

        if ($host === '' || $username === '' || $password === '') {
            return false;
        }

        $remote = ($encryption === 'ssl' ? 'ssl://' : '') . $host . ':' . $port;
        $socket = @stream_socket_client($remote, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT);
        if (!is_resource($socket)) {
            error_log("SMTP connect failed: {$errno} {$errstr}");
            return false;
        }

        stream_set_timeout($socket, $timeout);

        try {
            $this->expect($socket, [220]);
            $serverName = $_SERVER['SERVER_NAME'] ?? 'localhost';
            $this->command($socket, "EHLO {$serverName}", [250]);

            if ($encryption === 'tls') {
                $this->command($socket, 'STARTTLS', [220]);
                if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    throw new \RuntimeException('SMTP STARTTLS failed.');
                }
                $this->command($socket, "EHLO {$serverName}", [250]);
            }

            $this->command($socket, 'AUTH LOGIN', [334]);
            $this->command($socket, base64_encode($username), [334]);
            $this->command($socket, base64_encode($password), [235]);

            $fromEmail = $this->fromEmail();
            $this->command($socket, 'MAIL FROM:<' . $fromEmail . '>', [250]);
            $this->command($socket, 'RCPT TO:<' . $to . '>', [250, 251]);
            $this->command($socket, 'DATA', [354]);

            $message = $this->buildMimeMessage($to, $subject, $html, $text);
            fwrite($socket, $this->dotStuff($message) . "\r\n.\r\n");
            $this->expect($socket, [250]);
            $this->command($socket, 'QUIT', [221]);
            fclose($socket);

            return true;
        } catch (\Throwable $e) {
            error_log('SMTP send failed: ' . $e->getMessage());
            if (is_resource($socket)) {
                fwrite($socket, "QUIT\r\n");
                fclose($socket);
            }

            return false;
        }
    }

    private function buildMimeMessage(string $to, string $subject, string $html, ?string $text): string
    {
        $boundary = '=_TechMart_' . bin2hex(random_bytes(12));
        $plain = $text ?? trim(strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $html)));

        $headers = [
            'Date: ' . date(DATE_RFC2822),
            'From: ' . $this->formatAddress($this->fromEmail(), $this->fromName()),
            'To: <' . $to . '>',
            'Subject: ' . $this->encodeHeader($subject),
            'MIME-Version: 1.0',
            'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
        ];

        return implode("\r\n", $headers)
            . "\r\n\r\n--{$boundary}\r\n"
            . "Content-Type: text/plain; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: 8bit\r\n\r\n"
            . $plain
            . "\r\n\r\n--{$boundary}\r\n"
            . "Content-Type: text/html; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: 8bit\r\n\r\n"
            . $html
            . "\r\n\r\n--{$boundary}--";
    }

    private function writeToLog(string $to, string $subject, string $html, ?string $text): bool
    {
        $dir = App::$config['mail']['log_path'] ?? dirname(__DIR__, 2) . '/storage/mail';
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            return false;
        }

        $safeTo = preg_replace('/[^a-z0-9_.-]+/i', '_', $to) ?: 'recipient';
        $filename = sprintf('%s/%s_%s.html', rtrim($dir, '/\\'), date('Ymd_His'), $safeTo);
        $plain = $text ?? trim(strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $html)));
        $content = $this->wrapLogContent($to, $subject, $html, $plain);

        $written = file_put_contents($filename, $content) !== false;
        if ($written) {
            $this->lastLogFile = $filename;
        }

        return $written;
    }

    private function command(mixed $socket, string $command, array $expected): string
    {
        fwrite($socket, $command . "\r\n");
        return $this->expect($socket, $expected);
    }

    private function expect(mixed $socket, array $expected): string
    {
        $response = '';
        while (($line = fgets($socket, 515)) !== false) {
            $response .= $line;
            if (strlen($line) >= 4 && $line[3] === ' ') {
                break;
            }
        }

        $code = (int)substr($response, 0, 3);
        if (!in_array($code, $expected, true)) {
            throw new \RuntimeException('Unexpected SMTP response: ' . trim($response));
        }

        return $response;
    }

    private function dotStuff(string $message): string
    {
        $normalized = str_replace(["\r\n", "\r"], "\n", $message);
        $lines = explode("\n", $normalized);
        $lines = array_map(static fn(string $line): string => str_starts_with($line, '.') ? '.' . $line : $line, $lines);

        return implode("\r\n", $lines);
    }

    private function encodeHeader(string $value): string
    {
        return '=?UTF-8?B?' . base64_encode($value) . '?=';
    }

    private function formatAddress(string $email, string $name): string
    {
        return $this->encodeHeader($name) . ' <' . $email . '>';
    }

    private function fromEmail(): string
    {
        return App::$config['mail']['from_email'] ?? 'no-reply@techmart.test';
    }

    private function fromName(): string
    {
        return App::$config['mail']['from_name'] ?? 'TechMart';
    }

    private function wrapLogContent(string $to, string $subject, string $html, string $text): string
    {
        $sentAt = date('d/m/Y H:i:s');
        return '<!DOCTYPE html><html lang="vi"><head><meta charset="UTF-8">'
            . '<meta name="viewport" content="width=device-width, initial-scale=1.0">'
            . '<title>' . htmlspecialchars($subject, ENT_QUOTES, 'UTF-8') . '</title>'
            . '<style>body{font-family:Arial,sans-serif;background:#f6f7fb;padding:24px;color:#111827}'
            . '.mail{max-width:760px;margin:auto;background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:24px}'
            . '.meta{font-size:13px;color:#6b7280;border-bottom:1px solid #e5e7eb;padding-bottom:12px;margin-bottom:18px}'
            . 'pre{white-space:pre-wrap;background:#f9fafb;border:1px solid #e5e7eb;padding:12px;border-radius:8px}</style>'
            . '</head><body><main class="mail">'
            . '<div class="meta"><strong>Mail log demo</strong><br>'
            . 'To: ' . htmlspecialchars($to, ENT_QUOTES, 'UTF-8') . '<br>'
            . 'Subject: ' . htmlspecialchars($subject, ENT_QUOTES, 'UTF-8') . '<br>'
            . 'Created at: ' . htmlspecialchars($sentAt, ENT_QUOTES, 'UTF-8') . '</div>'
            . $html
            . '<hr><div class="meta">Plain text fallback</div><pre>'
            . htmlspecialchars($text, ENT_QUOTES, 'UTF-8') . '</pre>'
            . '</main></body></html>';
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

final class SmtpMailer
{
    /**
     * Envia uma mensagem simples em HTML usando SMTP autenticado.
     */
    public function send(string $toEmail, string $toName, string $subject, string $html): void
    {
        $host = (string) env('SMTP_HOST', '');
        $port = (int) env('SMTP_PORT', 465);
        $username = (string) env('SMTP_USERNAME', '');
        $password = (string) env('SMTP_PASSWORD', '');
        $encryption = text_lower((string) env('SMTP_ENCRYPTION', 'ssl'));
        $fromEmail = (string) env('MAIL_FROM_ADDRESS', $username);
        $fromName = (string) env('MAIL_FROM_NAME', env('APP_NAME', 'TipsForMe'));

        if ($host === '' || $username === '' || $password === '' || $fromEmail === '') {
            throw new RuntimeException('SMTP configuration is incomplete.');
        }

        $transport = $encryption === 'ssl' ? 'ssl://' : '';
        $socket = @stream_socket_client(
            $transport . $host . ':' . $port,
            $errorNumber,
            $errorMessage,
            20,
            STREAM_CLIENT_CONNECT
        );

        if (!is_resource($socket)) {
            throw new RuntimeException('SMTP connection failed: ' . $errorMessage . ' (' . $errorNumber . ')');
        }

        stream_set_timeout($socket, 20);

        try {
            $this->expect($socket, [220]);
            $this->command($socket, 'EHLO ' . $this->hostname(), [250]);

            if ($encryption === 'tls') {
                $this->command($socket, 'STARTTLS', [220]);

                if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    throw new RuntimeException('Unable to enable SMTP TLS encryption.');
                }

                $this->command($socket, 'EHLO ' . $this->hostname(), [250]);
            }

            $this->command($socket, 'AUTH LOGIN', [334]);
            $this->command($socket, base64_encode($username), [334]);
            $this->command($socket, base64_encode($password), [235]);
            $this->command($socket, 'MAIL FROM:<' . $fromEmail . '>', [250]);
            $this->command($socket, 'RCPT TO:<' . $toEmail . '>', [250, 251]);
            $this->command($socket, 'DATA', [354]);

            $headers = [
                'Date: ' . date(DATE_RFC2822),
                'From: ' . $this->encodedName($fromName) . ' <' . $fromEmail . '>',
                'To: ' . $this->encodedName($toName) . ' <' . $toEmail . '>',
                'Subject: ' . $this->encodedName($subject),
                'Message-ID: <' . bin2hex(random_bytes(12)) . '@' . $this->hostname() . '>',
                'MIME-Version: 1.0',
                'Content-Type: text/html; charset=UTF-8',
                'Content-Transfer-Encoding: base64',
            ];

            $body = chunk_split(base64_encode($html), 76, "\r\n");
            $message = implode("\r\n", $headers) . "\r\n\r\n" . $body;
            $message = preg_replace('/(?m)^\./', '..', $message) ?? $message;

            fwrite($socket, $message . "\r\n.\r\n");
            $this->expect($socket, [250]);
            $this->command($socket, 'QUIT', [221]);
        } finally {
            fclose($socket);
        }
    }

    private function command($socket, string $command, array $expectedCodes): void
    {
        fwrite($socket, $command . "\r\n");
        $this->expect($socket, $expectedCodes);
    }

    private function expect($socket, array $expectedCodes): string
    {
        $response = '';

        while (($line = fgets($socket, 515)) !== false) {
            $response .= $line;

            if (strlen($line) >= 4 && $line[3] === ' ') {
                break;
            }
        }

        $code = (int) substr($response, 0, 3);

        if (!in_array($code, $expectedCodes, true)) {
            throw new RuntimeException('Unexpected SMTP response: ' . trim($response));
        }

        return $response;
    }

    private function hostname(): string
    {
        return preg_replace('/[^a-zA-Z0-9.-]/', '', gethostname() ?: 'localhost') ?: 'localhost';
    }

    private function encodedName(string $value): string
    {
        return '=?UTF-8?B?' . base64_encode($value) . '?=';
    }
}

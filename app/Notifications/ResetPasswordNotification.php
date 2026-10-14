<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(public string $token)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);
        $name = e($notifiable->name);
        $safeUrl = e($url);
        $minutes = (int) config('auth.passwords.users.expire', 60);

        return (new MailMessage)
            ->subject('Đặt lại mật khẩu Pawora')
            ->view([
                'html' => new HtmlString('<div style="max-width:600px;margin:auto;padding:32px;font-family:Arial,sans-serif;color:#1d1d1f;line-height:1.6">'
                    .'<p style="color:#0066cc;font-size:12px;font-weight:700;letter-spacing:.12em">PAWORA</p>'
                    .'<h1 style="margin:12px 0 16px;font-size:32px;line-height:1.15">Đặt lại mật khẩu</h1>'
                    .'<p>Xin chào '.$name.',</p><p>Pawora nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.</p>'
                    .'<p style="margin:28px 0"><a href="'.$safeUrl.'" style="display:inline-block;padding:13px 20px;border-radius:10px;background:#0066cc;color:#fff;text-decoration:none;font-weight:700">Đặt lại mật khẩu</a></p>'
                    .'<p style="color:#5f5f63">Đường dẫn có hiệu lực trong '.$minutes.' phút và chỉ dùng được một lần. Nếu bạn không gửi yêu cầu này, hãy bỏ qua email.</p>'
                    .'</div>'),
                'raw' => "Xin chào {$notifiable->name},\n\nDùng đường dẫn sau để đặt lại mật khẩu Pawora:\n{$url}\n\nĐường dẫn có hiệu lực trong {$minutes} phút và chỉ dùng được một lần.",
            ]);
    }
}

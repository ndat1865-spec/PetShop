<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminLoginCodeNotification extends Notification
{
    use Queueable;

    public function __construct(public string $code) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Mã xác thực đăng nhập Pawora Ops')
            ->greeting('Xin chào '.$notifiable->name.',')
            ->line('Dùng mã sau để hoàn tất đăng nhập khu vực quản trị:')
            ->line($this->code)
            ->line('Mã có hiệu lực trong 10 phút. Nếu bạn không đăng nhập, hãy đổi mật khẩu và báo cho quản trị viên.');
    }
}

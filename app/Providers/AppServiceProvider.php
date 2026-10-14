<?php

namespace App\Providers;

use App\Models\ContactMessage;
use App\Models\ProductReview;
use App\Models\ChatConversation;
use App\Mail\Transport\BrevoTransport;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Mail::extend('brevo', fn () => new BrevoTransport(
            config('services.brevo.key'),
            (bool) config('services.brevo.ssl_verify', true),
        ));

        View::composer('layouts.admin', function ($view) {
            $view->with([
                'adminUnansweredReviewCount' => ProductReview::approved()->where('awaiting_shop_reply', true)->count(),
                'adminNewContactCount' => ContactMessage::where('status', 'new')->count(),
                'adminUnreadChatCount' => Schema::hasTable('chat_conversations') ? ChatConversation::where('admin_unread_count', '>', 0)->count() : 0,
            ]);
        });
    }
}

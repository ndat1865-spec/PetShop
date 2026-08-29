<?php

namespace App\Providers;

use App\Models\ContactMessage;
use App\Models\ProductReview;
use App\Models\ChatConversation;
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
        View::composer('layouts.admin', function ($view) {
            $view->with([
                'adminPendingReviewCount' => ProductReview::where('status', 'pending')->count(),
                'adminNewContactCount' => ContactMessage::where('status', 'new')->count(),
                'adminUnreadChatCount' => Schema::hasTable('chat_conversations') ? ChatConversation::where('admin_unread_count', '>', 0)->count() : 0,
            ]);
        });
    }
}

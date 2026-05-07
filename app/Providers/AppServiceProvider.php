<?php

namespace App\Providers;

use App\Models\Attachment;
use App\Models\CalendarEvent;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Observers\UserObserver;
use App\Models\Comment;
use App\Models\Structure;
use App\Policies\AttachmentPolicy;
use App\Policies\CalendarEventPolicy;
use App\Policies\CommentPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\TaskPolicy;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        Gate::policy(CalendarEvent::class, CalendarEventPolicy::class);
        Gate::policy(Attachment::class, AttachmentPolicy::class);
        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(Task::class, TaskPolicy::class);
        Gate::policy(Comment::class, CommentPolicy::class);

        User::observe(UserObserver::class);

        // Morph map for backward compatibility with status_histories entity_type
        Relation::morphMap([
            'User' => User::class,
            'Structure' => Structure::class,
        ]);

        // Breadcrumb View Composer - inject breadcrumbs to all views
        view()->composer('*', function ($view) {
            $breadcrumbs = app(\App\Services\BreadcrumbService::class)->getBreadcrumbs();
            $view->with('breadcrumbs', $breadcrumbs);
        });
    }
}

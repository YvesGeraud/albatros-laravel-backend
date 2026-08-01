<?php

namespace App\Providers;

use App\Models\Combo;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;
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
        Relation::enforceMorphMap([
            'product' => Product::class,
            'combo' => Combo::class,
            // Sanctum's HasApiTokens trait uses a morphMany relation
            // internally (tokens()) — enforceMorphMap blocks any model not
            // listed here from participating in ANY morph relation, so User
            // needs an entry even though nothing else in the app treats it
            // polymorphically.
            'user' => User::class,
        ]);
    }
}

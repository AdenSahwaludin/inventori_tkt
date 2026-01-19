<?php

namespace App\Providers;

use App\Models\BarangRusak;
use App\Models\MasterBarang;
use App\Models\TransaksiBarang;
use App\Models\TransaksiKeluar;
use App\Models\UnitBarang;
use App\Observers\BarangRusakObserver;
use App\Observers\MasterBarangObserver;
use App\Observers\TransaksiBarangObserver;
use App\Observers\TransaksiKeluarObserver;
use App\Observers\UnitBarangObserver;
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
        // Register Observers
        MasterBarang::observe(MasterBarangObserver::class);
        TransaksiBarang::observe(TransaksiBarangObserver::class);
        TransaksiKeluar::observe(TransaksiKeluarObserver::class);
        UnitBarang::observe(UnitBarangObserver::class);
        BarangRusak::observe(BarangRusakObserver::class);
    }
}

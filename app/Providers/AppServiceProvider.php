<?php

namespace App\Providers;

use App\Models\AbsensiKegiatan;
use App\Models\AbsensiPuasa;
use App\Models\Pelanggaran;
use App\Models\PenilaianKeseharian;
use App\Models\Santri;
use App\Policies\AbsensiKegiatanPolicy;
use App\Policies\AbsensiPuasaPolicy;
use App\Policies\PelanggaranPolicy;
use App\Policies\PenilaianKeseharianPolicy;
use App\Policies\SantriPolicy;
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
        // Register policies
        Gate::policy(Santri::class, SantriPolicy::class);
        Gate::policy(AbsensiKegiatan::class, \App\Policies\AbsensiPolicy::class);
        Gate::policy(AbsensiPuasa::class, \App\Policies\AbsensiPolicy::class);
        Gate::policy(Pelanggaran::class, PelanggaranPolicy::class);
        Gate::policy(PenilaianKeseharian::class, PenilaianKeseharianPolicy::class);
    }
}

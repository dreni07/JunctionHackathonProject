<?php

namespace App\Providers;

use App\Services\DocumentOcrService;
use App\Services\OcrService;
use App\Services\QdrantService;
use App\Services\VectorStore;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(QdrantService::class, function (): QdrantService {
            return new QdrantService(
                endpoint: config('qdrant.endpoint'),
                apiKey: config('qdrant.api_key'),
                defaultCollection: config('qdrant.collection'),
                vectorSize: config('qdrant.vector_size'),
                timeout: config('qdrant.timeout'),
            );
        });

        $this->app->singleton(VectorStore::class);

        $this->app->singleton(OcrService::class, function (): OcrService {
            return new OcrService(
                binary: config('ocr.binary'),
                defaultLanguage: config('ocr.language'),
                timeout: config('ocr.timeout'),
                preprocess: config('ocr.preprocess'),
                imagemagickBinary: config('ocr.imagemagick_binary'),
            );
        });

        $this->app->singleton(DocumentOcrService::class, function (): DocumentOcrService {
            return new DocumentOcrService(
                ocr: $this->app->make(OcrService::class),
                pdftoppmBinary: config('ocr.pdftoppm_binary'),
                dpi: config('ocr.document_dpi'),
                timeout: config('ocr.document_timeout'),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}

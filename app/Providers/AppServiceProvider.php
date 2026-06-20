<?php

namespace App\Providers;

use App\Agent\AgentService;
use App\Agent\Tool;
use App\Agent\Tools\DbQueryTool;
use App\Agent\Tools\DocumentSearchTool;
use App\Agent\Tools\FileSearchTool;
use App\Agent\Tools\WebSearchTool;
use App\Models\User;
use App\Observers\OperationalChangeObserver;
use App\Services\DocumentIndexer;
use App\Services\DocumentOcrService;
use App\Services\EmbeddingService;
use App\Services\FileSearchService;
use App\Services\GroqService;
use App\Services\OcrService;
use App\Services\OpenAiChatService;
use App\Services\PdfTextExtractor;
use App\Services\QdrantService;
use App\Services\SpeechToTextService;
use App\Services\TextToSpeechService;
use App\Services\VectorStore;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PdfTextExtractor::class, function (): PdfTextExtractor {
            return new PdfTextExtractor(
                documentOcr: $this->app->make(DocumentOcrService::class),
                pdftotextBinary: (string) config('pdf.pdftotext_binary'),
                timeout: (int) config('pdf.timeout'),
            );
        });

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

        $this->app->singleton(GroqService::class, function (): GroqService {
            return new GroqService(
                apiKey: config('services.groq.api_key'),
                baseUrl: config('services.groq.base_url'),
                model: config('services.groq.model'),
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

        $this->app->singleton(EmbeddingService::class, function (): EmbeddingService {
            return new EmbeddingService(
                apiKey: config('services.gemini.api_key'),
                baseUrl: config('services.gemini.base_url'),
                model: config('services.gemini.embedding_model'),
                dimensions: (int) config('qdrant.vector_size'),
            );
        });

        $this->app->singleton(DocumentIndexer::class, function (): DocumentIndexer {
            return new DocumentIndexer(
                embeddings: $this->app->make(EmbeddingService::class),
                qdrant: $this->app->make(QdrantService::class),
            );
        });

        $this->app->singleton(FileSearchService::class, function (): FileSearchService {
            return new FileSearchService(rootPath: base_path('docs'));
        });

        $this->app->singleton(OpenAiChatService::class, function (): OpenAiChatService {
            return new OpenAiChatService(
                apiKey: (string) config('services.openai.api_key'),
                baseUrl: (string) config('services.openai.base_url'),
                model: (string) config('services.openai.model'),
            );
        });

        $this->app->singleton(SpeechToTextService::class, function (): SpeechToTextService {
            return new SpeechToTextService(
                apiKey: (string) config('services.openai.api_key'),
                baseUrl: (string) config('services.openai.base_url'),
                model: (string) config('services.openai.stt.model'),
            );
        });

        $this->app->singleton(TextToSpeechService::class, function (): TextToSpeechService {
            return new TextToSpeechService(
                apiKey: (string) config('services.openai.api_key'),
                baseUrl: (string) config('services.openai.base_url'),
                model: (string) config('services.openai.tts.model'),
                voice: (string) config('services.openai.tts.voice'),
                format: (string) config('services.openai.tts.format'),
            );
        });

        $this->app->singleton(AgentService::class, function (): AgentService {
            /** @var list<Tool> $tools */
            $tools = [
                new DbQueryTool,
                new DocumentSearchTool(
                    $this->app->make(EmbeddingService::class),
                    $this->app->make(QdrantService::class),
                ),
                // Searches the whole docs library. To scope an agent to specific
                // collections, pass them as the second argument, e.g.
                // new FileSearchTool($svc, ['spaces', 'policies']).
                new FileSearchTool($this->app->make(FileSearchService::class)),
                new WebSearchTool,
            ];

            $map = [];
            foreach ($tools as $tool) {
                $map[$tool->name()] = $tool;
            }

            return new AgentService($this->app->make(GroqService::class), $map);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->registerPermissionGates();
        $this->registerOperationalSyncObservers();
    }

    protected function registerOperationalSyncObservers(): void
    {
        $observer = OperationalChangeObserver::class;

        foreach (config('operational-sync.models', []) as $modelClass) {
            $modelClass::observe($observer);
        }
    }

    /**
     * Make every permission name usable as a Gate ability, so routes can use
     * ->can('events.manage') and views can use @can. A user is granted an
     * ability when any of their roles include that permission.
     *
     * Returning null (not false) when the permission is absent lets any
     * explicit policies still run.
     */
    protected function registerPermissionGates(): void
    {
        Gate::before(function (User $user, string $ability): ?bool {
            return $user->hasPermissionTo($ability) ? true : null;
        });
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

        Password::defaults(fn (): Password => Password::min(8));
    }
}

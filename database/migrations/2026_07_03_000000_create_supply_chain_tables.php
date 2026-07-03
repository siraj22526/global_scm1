<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Countries
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->char('iso2', 2)->unique();
            $table->char('iso3', 3)->unique();
            $table->string('name', 100);
            $table->string('official_name', 150);
            $table->string('capital', 100);
            $table->string('region', 60);
            $table->json('languages')->nullable();
            $table->string('flag_url', 255)->nullable();
            $table->decimal('latitude', 9, 6);
            $table->decimal('longitude', 9, 6);
            $table->timestamps();
        });

        // 2. Currencies
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained('countries')->onDelete('cascade');
            $table->char('code', 3);
            $table->string('name', 100);
            $table->string('symbol', 20)->nullable();
            $table->timestamps();
        });

        // 3. Ports
        Schema::create('ports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained('countries')->onDelete('cascade');
            $table->string('name', 150);
            $table->string('wpi_code', 20)->nullable();
            $table->decimal('latitude', 9, 6);
            $table->decimal('longitude', 9, 6);
            $table->string('harbor_size', 20)->nullable();
            $table->timestamps();

            $table->index('country_id', 'idx_ports_country');
            $table->index('name', 'idx_ports_name');
        });

        // 4. Economic Indicators
        Schema::create('economic_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained('countries')->onDelete('cascade');
            $table->string('indicator', 30); // gdp, inflation, population, export, import
            $table->smallInteger('year');
            $table->decimal('value', 20, 4)->nullable();
            $table->timestamp('fetched_at')->useCurrent();
            $table->timestamps();

            $table->unique(['country_id', 'indicator', 'year']);
            $table->index(['country_id', 'indicator', 'year'], 'idx_indicators_search');
        });

        // 5. Weather Snapshots
        Schema::create('weather_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained('countries')->onDelete('cascade');
            $table->decimal('temperature_c', 5, 2);
            $table->decimal('precipitation_mm', 6, 2);
            $table->decimal('wind_speed_kmh', 6, 2);
            $table->tinyInteger('storm_risk'); // 0 to 100
            $table->timestamp('recorded_at');
            $table->timestamps();
        });

        // 6. Currency Rates
        Schema::create('currency_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('currency_id')->constrained('currencies')->onDelete('cascade');
            $table->decimal('rate_to_usd', 18, 8);
            $table->date('rate_date');
            $table->timestamps();

            $table->unique(['currency_id', 'rate_date']);
        });

        // 7. News Cache
        Schema::create('news_cache', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->nullable()->constrained('countries')->onDelete('cascade');
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->string('url', 500)->unique();
            $table->string('category', 30); // logistics, trade, shipping, economy
            $table->timestamp('published_at');
            $table->timestamps();

            // Adding a fulltext index if database driver supports it, else raw queries will work
            // Since we are running SQLite/MySQL, fulltext might act differently, but we'll use normal matching or fulltext conditional.
        });

        // 8. Sentiment Results
        Schema::create('sentiment_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('news_id')->unique()->constrained('news_cache')->onDelete('cascade');
            $table->smallInteger('positive_score');
            $table->smallInteger('negative_score');
            $table->string('label'); // positive, neutral, negative
            $table->timestamps();
        });

        // 9. Lexicon Tables
        Schema::create('positive_words', function (Blueprint $table) {
            $table->id();
            $table->string('word', 50)->unique();
            $table->timestamps();
        });

        Schema::create('negative_words', function (Blueprint $table) {
            $table->id();
            $table->string('word', 50)->unique();
            $table->timestamps();
        });

        // 10. Risk Scores
        Schema::create('risk_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained('countries')->onDelete('cascade');
            $table->decimal('total_score', 5, 2); // 0 to 100
            $table->string('level'); // low, medium, high
            $table->timestamp('calculated_at');
            $table->timestamps();

            $table->index(['country_id', 'calculated_at'], 'idx_risk_trend');
        });

        // 11. Risk Score Components
        Schema::create('risk_score_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('risk_score_id')->constrained('risk_scores')->onDelete('cascade');
            $table->string('component', 30); // weather, inflation, currency, news
            $table->decimal('raw_value', 10, 4);
            $table->decimal('normalized', 5, 2);
            $table->decimal('weight', 4, 3);
            $table->timestamps();
        });

        // 12. Risk Weights
        Schema::create('risk_weights', function (Blueprint $table) {
            $table->id();
            $table->string('component', 30)->unique();
            $table->decimal('weight', 4, 3);
            $table->timestamps();
        });

        // 13. Watchlists
        Schema::create('watchlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('country_id')->constrained('countries')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['user_id', 'country_id']);
        });

        // 14. Articles
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title', 255);
            $table->string('slug', 255)->unique();
            $table->text('body');
            $table->string('status', 30)->default('draft'); // draft, published
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
        Schema::dropIfExists('watchlists');
        Schema::dropIfExists('risk_score_components');
        Schema::dropIfExists('risk_weights');
        Schema::dropIfExists('risk_scores');
        Schema::dropIfExists('negative_words');
        Schema::dropIfExists('positive_words');
        Schema::dropIfExists('sentiment_results');
        Schema::dropIfExists('news_cache');
        Schema::dropIfExists('currency_rates');
        Schema::dropIfExists('weather_snapshots');
        Schema::dropIfExists('economic_indicators');
        Schema::dropIfExists('ports');
        Schema::dropIfExists('currencies');
        Schema::dropIfExists('countries');
    }
};

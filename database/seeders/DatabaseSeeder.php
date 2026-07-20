<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Country;
use App\Models\Currency;
use App\Models\RiskWeight;
use App\Models\PositiveWord;
use App\Models\NegativeWord;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Users
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'SCM Administrator',
                'password' => Hash::make('admin12345'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'user@gmail.com'],
            [
                'name' => 'SCM Regular User',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'is_active' => true,
            ]
        );

        // 2. Seed 20 Countries and their currencies
        $countriesData = [
            [
                'iso2' => 'DE', 'iso3' => 'DEU', 'name' => 'Germany', 'official_name' => 'Federal Republic of Germany',
                'capital' => 'Berlin', 'region' => 'Europe', 'languages' => ['de' => 'German'],
                'flag_url' => 'https://flagcdn.com/w320/de.png', 'latitude' => 52.520008, 'longitude' => 13.404954,
                'currency' => ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€']
            ],
            [
                'iso2' => 'CN', 'iso3' => 'CHN', 'name' => 'China', 'official_name' => "People's Republic of China",
                'capital' => 'Beijing', 'region' => 'Asia', 'languages' => ['zh' => 'Chinese'],
                'flag_url' => 'https://flagcdn.com/w320/cn.png', 'latitude' => 39.9042, 'longitude' => 116.4074,
                'currency' => ['code' => 'CNY', 'name' => 'Renminbi', 'symbol' => '¥']
            ],
            [
                'iso2' => 'ID', 'iso3' => 'IDN', 'name' => 'Indonesia', 'official_name' => 'Republic of Indonesia',
                'capital' => 'Jakarta', 'region' => 'Asia', 'languages' => ['id' => 'Indonesian'],
                'flag_url' => 'https://flagcdn.com/w320/id.png', 'latitude' => -6.2088, 'longitude' => 106.8456,
                'currency' => ['code' => 'IDR', 'name' => 'Indonesian Rupiah', 'symbol' => 'Rp']
            ],
            [
                'iso2' => 'AU', 'iso3' => 'AUS', 'name' => 'Australia', 'official_name' => 'Commonwealth of Australia',
                'capital' => 'Canberra', 'region' => 'Oceania', 'languages' => ['en' => 'English'],
                'flag_url' => 'https://flagcdn.com/w320/au.png', 'latitude' => -35.2809, 'longitude' => 149.1300,
                'currency' => ['code' => 'AUD', 'name' => 'Australian Dollar', 'symbol' => '$']
            ],
            [
                'iso2' => 'US', 'iso3' => 'USA', 'name' => 'United States', 'official_name' => 'United States of America',
                'capital' => 'Washington D.C.', 'region' => 'Americas', 'languages' => ['en' => 'English'],
                'flag_url' => 'https://flagcdn.com/w320/us.png', 'latitude' => 38.8951, 'longitude' => -77.0364,
                'currency' => ['code' => 'USD', 'name' => 'United States Dollar', 'symbol' => '$']
            ],
            [
                'iso2' => 'GB', 'iso3' => 'GBR', 'name' => 'United Kingdom', 'official_name' => 'United Kingdom of Great Britain and Northern Ireland',
                'capital' => 'London', 'region' => 'Europe', 'languages' => ['en' => 'English'],
                'flag_url' => 'https://flagcdn.com/w320/gb.png', 'latitude' => 51.5074, 'longitude' => -0.1278,
                'currency' => ['code' => 'GBP', 'name' => 'British Pound', 'symbol' => '£']
            ],
            [
                'iso2' => 'JP', 'iso3' => 'JPN', 'name' => 'Japan', 'official_name' => 'Japan',
                'capital' => 'Tokyo', 'region' => 'Asia', 'languages' => ['ja' => 'Japanese'],
                'flag_url' => 'https://flagcdn.com/w320/jp.png', 'latitude' => 35.6762, 'longitude' => 139.6503,
                'currency' => ['code' => 'JPY', 'name' => 'Japanese Yen', 'symbol' => '¥']
            ],
            [
                'iso2' => 'IN', 'iso3' => 'IND', 'name' => 'India', 'official_name' => 'Republic of India',
                'capital' => 'New Delhi', 'region' => 'Asia', 'languages' => ['hi' => 'Hindi', 'en' => 'English'],
                'flag_url' => 'https://flagcdn.com/w320/in.png', 'latitude' => 28.6139, 'longitude' => 77.2090,
                'currency' => ['code' => 'INR', 'name' => 'Indian Rupee', 'symbol' => '₹']
            ],
            [
                'iso2' => 'SG', 'iso3' => 'SGP', 'name' => 'Singapore', 'official_name' => 'Republic of Singapore',
                'capital' => 'Singapore', 'region' => 'Asia', 'languages' => ['en' => 'English', 'ms' => 'Malay', 'zh' => 'Chinese', 'ta' => 'Tamil'],
                'flag_url' => 'https://flagcdn.com/w320/sg.png', 'latitude' => 1.3521, 'longitude' => 103.8198,
                'currency' => ['code' => 'SGD', 'name' => 'Singapore Dollar', 'symbol' => '$']
            ],
            [
                'iso2' => 'BR', 'iso3' => 'BRA', 'name' => 'Brazil', 'official_name' => 'Federative Republic of Brazil',
                'capital' => 'Brasilia', 'region' => 'Americas', 'languages' => ['pt' => 'Portuguese'],
                'flag_url' => 'https://flagcdn.com/w320/br.png', 'latitude' => -15.7938, 'longitude' => -47.8828,
                'currency' => ['code' => 'BRL', 'name' => 'Brazilian Real', 'symbol' => 'R$']
            ],
            [
                'iso2' => 'CA', 'iso3' => 'CAN', 'name' => 'Canada', 'official_name' => 'Canada',
                'capital' => 'Ottawa', 'region' => 'Americas', 'languages' => ['en' => 'English', 'fr' => 'French'],
                'flag_url' => 'https://flagcdn.com/w320/ca.png', 'latitude' => 45.4215, 'longitude' => -75.6972,
                'currency' => ['code' => 'CAD', 'name' => 'Canadian Dollar', 'symbol' => '$']
            ],
            [
                'iso2' => 'FR', 'iso3' => 'FRA', 'name' => 'France', 'official_name' => 'French Republic',
                'capital' => 'Paris', 'region' => 'Europe', 'languages' => ['fr' => 'French'],
                'flag_url' => 'https://flagcdn.com/w320/fr.png', 'latitude' => 48.8566, 'longitude' => 2.3522,
                'currency' => ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€']
            ],
            [
                'iso2' => 'IT', 'iso3' => 'ITA', 'name' => 'Italy', 'official_name' => 'Italian Republic',
                'capital' => 'Rome', 'region' => 'Europe', 'languages' => ['it' => 'Italian'],
                'flag_url' => 'https://flagcdn.com/w320/it.png', 'latitude' => 41.9028, 'longitude' => 12.4964,
                'currency' => ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€']
            ],
            [
                'iso2' => 'RU', 'iso3' => 'RUS', 'name' => 'Russia', 'official_name' => 'Russian Federation',
                'capital' => 'Moscow', 'region' => 'Europe', 'languages' => ['ru' => 'Russian'],
                'flag_url' => 'https://flagcdn.com/w320/ru.png', 'latitude' => 55.7558, 'longitude' => 37.6173,
                'currency' => ['code' => 'RUB', 'name' => 'Russian Ruble', 'symbol' => '₽']
            ],
            [
                'iso2' => 'KR', 'iso3' => 'KOR', 'name' => 'South Korea', 'official_name' => 'Republic of Korea',
                'capital' => 'Seoul', 'region' => 'Asia', 'languages' => ['ko' => 'Korean'],
                'flag_url' => 'https://flagcdn.com/w320/kr.png', 'latitude' => 37.5665, 'longitude' => 126.9780,
                'currency' => ['code' => 'KRW', 'name' => 'South Korean Won', 'symbol' => '₩']
            ],
            [
                'iso2' => 'ZA', 'iso3' => 'ZAF', 'name' => 'South Africa', 'official_name' => 'Republic of South Africa',
                'capital' => 'Pretoria', 'region' => 'Africa', 'languages' => ['en' => 'English', 'af' => 'Afrikaans'],
                'flag_url' => 'https://flagcdn.com/w320/za.png', 'latitude' => -25.7479, 'longitude' => 28.1878,
                'currency' => ['code' => 'ZAR', 'name' => 'South African Rand', 'symbol' => 'R']
            ],
            [
                'iso2' => 'MX', 'iso3' => 'MEX', 'name' => 'Mexico', 'official_name' => 'United Mexican States',
                'capital' => 'Mexico City', 'region' => 'Americas', 'languages' => ['es' => 'Spanish'],
                'flag_url' => 'https://flagcdn.com/w320/mx.png', 'latitude' => 19.4326, 'longitude' => -99.1332,
                'currency' => ['code' => 'MXN', 'name' => 'Mexican Peso', 'symbol' => '$']
            ],
            [
                'iso2' => 'NL', 'iso3' => 'NLD', 'name' => 'Netherlands', 'official_name' => 'Kingdom of the Netherlands',
                'capital' => 'Amsterdam', 'region' => 'Europe', 'languages' => ['nl' => 'Dutch'],
                'flag_url' => 'https://flagcdn.com/w320/nl.png', 'latitude' => 52.3676, 'longitude' => 4.9041,
                'currency' => ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€']
            ],
            [
                'iso2' => 'SA', 'iso3' => 'SAU', 'name' => 'Saudi Arabia', 'official_name' => 'Kingdom of Saudi Arabia',
                'capital' => 'Riyadh', 'region' => 'Asia', 'languages' => ['ar' => 'Arabic'],
                'flag_url' => 'https://flagcdn.com/w320/sa.png', 'latitude' => 24.7136, 'longitude' => 46.6753,
                'currency' => ['code' => 'SAR', 'name' => 'Saudi Riyal', 'symbol' => 'ر.س']
            ],
            [
                'iso2' => 'TR', 'iso3' => 'TUR', 'name' => 'Turkey', 'official_name' => 'Republic of Turkey',
                'capital' => 'Ankara', 'region' => 'Asia', 'languages' => ['tr' => 'Turkish'],
                'flag_url' => 'https://flagcdn.com/w320/tr.png', 'latitude' => 39.9334, 'longitude' => 32.8597,
                'currency' => ['code' => 'TRY', 'name' => 'Turkish Lira', 'symbol' => '₺']
            ]
        ];

        foreach ($countriesData as $c) {
            $country = Country::updateOrCreate(
                ['iso2' => $c['iso2']],
                [
                    'iso3' => $c['iso3'],
                    'name' => $c['name'],
                    'official_name' => $c['official_name'],
                    'capital' => $c['capital'],
                    'region' => $c['region'],
                    'languages' => $c['languages'],
                    'flag_url' => $c['flag_url'],
                    'latitude' => $c['latitude'],
                    'longitude' => $c['longitude']
                ]
            );

            Currency::updateOrCreate(
                ['country_id' => $country->id],
                [
                    'code' => $c['currency']['code'],
                    'name' => $c['currency']['name'],
                    'symbol' => $c['currency']['symbol']
                ]
            );
        }

        // 3. Seed Risk Weights
        $weights = [
            'weather' => 0.30,
            'news' => 0.40,
            'inflation' => 0.20,
            'currency' => 0.10
        ];

        foreach ($weights as $comp => $val) {
            RiskWeight::updateOrCreate(
                ['component' => $comp],
                ['weight' => $val]
            );
        }

        // 4. Seed Lexicon Words
        $positiveWords = ['growth', 'increase', 'profit', 'stable', 'improve', 'success', 'positive', 'boom', 'recovery', 'gain', 'expand', 'rise', 'boost'];
        $negativeWords = ['war', 'crisis', 'inflation', 'delay', 'disaster', 'decrease', 'drop', 'loss', 'decline', 'strike', 'shortage', 'conflict', 'risk', 'fail', 'plunge', 'recession'];

        foreach ($positiveWords as $word) {
            PositiveWord::updateOrCreate(['word' => $word]);
        }

        foreach ($negativeWords as $word) {
            NegativeWord::updateOrCreate(['word' => $word]);
        }
    }
}

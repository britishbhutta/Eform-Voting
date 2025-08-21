<?php
 
namespace Database\Seeders;
 
use Illuminate\Database\Seeder;
use App\Models\Tariff;
 
class TariffSeeder extends Seeder
{
    public function run(): void
    {
        $features = json_encode([
            'Event Creation',
            'Generate A Link For Voting',
            'E-mails Of Voting Participants For Download In .CSV Format'
        ]);
 
        Tariff::create([
            'title' => 'Tariff Small',
            'description' => 'Up To 500 Voters',
            'note' => 'Suitable For Online Streams',
            'features' => $features,
            'price_cents' => 1900,
        ]);
 
        Tariff::create([
            'title' => 'Tariff Standard',
            'description' => '500 - 5000 Voters',
            'note' => 'Suitable For Sports Matches',
            'features' => $features,
            'price_cents' => 2900,
        ]);
 
        Tariff::create([
            'title' => 'Tariff Medium',
            'description' => '5000 - 10000 Voters',
            'note' => 'Suitable For Concerts, Festivals',
            'features' => $features,
            'price_cents' => 3900,
        ]);
 
        Tariff::create([
            'title' => 'Tariff Extra',
            'description' => 'more than 10000 voters',
            'note' => 'suitable for programs on television',
            'features' => $features,
            'price_cents' => 4900,
        ]);
    }
}
 
 
<?php

namespace Database\Seeders;

use App\Models\Region;
use App\Models\City;
use App\Models\Organization;
use Illuminate\Database\Seeder;

class TajikistanLocationsSeeder extends Seeder
{
    public function run(): void
    {
        $regionsData = [
            'Вилояти Суғд' => ['Хуҷанд', 'Истаравшан', 'Панҷакент', 'Исфара', 'Конибодом', 'Гулистон', 'Бӯстон', 'Истиқлол'],
            'Вилояти Хатлон' => ['Бохтар', 'Кӯлоб', 'Норак', 'Левакант', 'Данғара', 'Панҷ'],
            'ВМКБ' => ['Хоруғ', 'Ишкошим', 'Мурғоб', 'Дарвоз'],
            'РРП' => ['Душанбе', 'Турсунзода', 'Ваҳдат', 'Ҳисор', 'Рӯдакӣ', 'Варзоб'],
        ];

        foreach ($regionsData as $nameRu => $cities) {
            $region = Region::firstOrCreate(['name_ru' => $nameRu], ['name_tj' => $nameRu]);

            foreach ($cities as $cityName) {
                $city = City::firstOrCreate(
                    ['region_id' => $region->id, 'name_ru' => $cityName],
                    ['name_tj' => $cityName]
                );

                // Создаем разнообразные организации для каждого города
                $this->createOrganizationsForCity($city->id, $cityName);
            }
        }
    }

    private function createOrganizationsForCity(int $cityId, string $cityName): void
    {
        $templates = [
            ['name' => "Бонки Эсхата, филиали шаҳри {$cityName}", 'type' => 'Бонк'],
            ['name' => "Бонки Амону, филиали {$cityName}", 'type' => 'Бонк'],
            ['name' => "Мактаби миёнаи умумтаълимии №1 шаҳри {$cityName}", 'type' => 'Мактаб'],
            ['name' => "Лицейи №5 шаҳри {$cityName}", 'type' => 'Мактаб'],
            ['name' => "Идораи маорифи шаҳри {$cityName}", 'type' => 'Вазорат / Идора'],
            ['name' => "Ширкати \"{$cityName} Телеком\"", 'type' => 'Ширкат'],
        ];

        foreach ($templates as $org) {
            Organization::firstOrCreate([
                'city_id' => $cityId,
                'name' => $org['name'],
                'type' => $org['type']
            ]);
        }
    }
}
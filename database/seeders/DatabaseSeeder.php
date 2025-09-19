<?php

namespace Database\Seeders;

use App\Models\Reminder_type;
use App\Models\Reservation_statuse;
use App\Models\Restaurant;
use App\Models\Restaurant_chain;
use App\Models\RestaurantChain;
use App\Models\RestaurantSchedule;
use App\Models\Review;
use App\Models\Role;
use App\Models\Table;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            ReservationStatusSeeder::class,
            ReminderTypeSeeder::class,
        ]);

        // Получаем роли из базы данных
        $guestRole = Role::where('name', 'guest')->first();
        $userRole = Role::where('name', 'user')->first();
        $adminRestaurantRole = Role::where('name', 'admin_restaurant')->first();
        $adminChainRole = Role::where('name', 'admin_chain')->first();
        $globalAdminRole = Role::where('name', 'superadmin')->first();


        //Создаем 1 глобального админа
        $globalAdmin = User::factory()->create([
            'email' => 'admin@admin.com',
        ]);
        $globalAdmin->roles()->attach($globalAdminRole);

        // Создаем 3 гостя
        $guestUsers = User::factory(3)->unverified()->create();
        foreach ($guestUsers as $user) {
            $user->roles()->attach($guestRole);
        }

        //  Создаем 30 обычных пользователей
        $regularUsers = User::factory(30)->create();
        foreach ($regularUsers as $user) {
            $user->roles()->attach($userRole);
        }

        //  Создаем 3 заблокированных пользователя
        $blockedUsers = User::factory(3)->blocked()->create();
        foreach ($blockedUsers as $user) {
            $user->roles()->attach($userRole);
        }

        $allRestaurants = collect();

        // Создаем 2 сети ресторанов
        RestaurantChain::factory(2)->create()->each(function ($chain) use ($adminChainRole, $adminRestaurantRole, &$allRestaurants) {
            // Создаем суперадмина для сети
            $superAdmin = User::factory()->create([
                'email' => 'superadmin@' . strtolower(str_replace(' ', '', $chain->name)) . '.com',
            ]);
            $superAdmin->roles()->attach($adminChainRole);
            $chain->superAdmins()->attach($superAdmin);

            // Создаем 5 ресторанов в каждой сети
            $chainRestaurants = Restaurant::factory(5)->create(['restaurant_chain_id' => $chain->id]);

            $chainRestaurants->each(function ($restaurant) use ($adminRestaurantRole) {
                // Создаем админа для каждого ресторана
                $admin = User::factory()->create([
                    'email' => 'admin@' . strtolower(str_replace(' ', '', $restaurant->name)) . '.com',
                ]);
                $admin->roles()->attach($adminRestaurantRole);
                $restaurant->administrators()->attach($admin);
            });
            $allRestaurants = $allRestaurants->merge($chainRestaurants);
        });

        // Создаем 10 ресторанов без сети
        $standaloneRestaurants = Restaurant::factory(10)->create();
        $standaloneRestaurants->each(function ($restaurant) use ($adminRestaurantRole) {
            // Создаем админа для каждого ресторана
            $admin = User::factory()->create([
                'email' => 'admin@' . strtolower(str_replace([' ', ','], '', $restaurant->name)) . '.com',
            ]);
            $admin->roles()->attach($adminRestaurantRole);
            $restaurant->administrators()->attach($admin);
        });
        $allRestaurants = $allRestaurants->merge($standaloneRestaurants);

        // Добавляем отзывы, расписание и столики для КАЖДОГО ресторана
        $allRestaurants->each(function ($restaurant) use ($regularUsers) {
            // Добавляем 2 отзыва от случайных обычных пользователей
            $usersForReview = $regularUsers->random(2)->unique('id');
            foreach ($usersForReview as $user) {
                Review::factory()->create([
                    'user_id' => $user->id,
                    'restaurant_id' => $restaurant->id,
                ]);
            }

            // Заполняем расписание на 31 декабря и 1 января
            $year = Carbon::now()->year;
            RestaurantSchedule::create([
                'restaurant_id' => $restaurant->id,
                'date' => "{$year}-12-31",
                'opens_at' => '10:00:00',
                'closes_at' => '18:00:00',
            ]);
            RestaurantSchedule::create([
                'restaurant_id' => $restaurant->id,
                'date' => ($year + 1) . "-01-01",
                'opens_at' => '10:00:00',
                'closes_at' => '18:00:00',
            ]);

            for ($i = 1; $i <= 10; $i++) {
                Table::factory()->create([
                    'restaurant_id' => $restaurant->id,
                    'number' => $i,
                ]);
            }
        });
    }
}

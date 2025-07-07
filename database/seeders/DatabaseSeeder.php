<?php

namespace Database\Seeders;

use App\Models\Reminder_type;
use App\Models\Reservation_statuse;
use App\Models\Restaurant;
use App\Models\Restaurant_chain;
use App\Models\Role;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Создаем роли
        $roles = Role::factory()->createMany([
            ['name' => 'user'],
            ['name' => 'admin'],
            ['name' => 'superadmin'],
        ]);

        // 2. Создаем пользователей
        $users = User::factory(30)->create();
        $blockedUsers = User::factory(3)->blocked()->create();
        $allUsers = $users->merge($blockedUsers);

        // 3. Назначаем роли пользователям
        $userRole = Role::where('name', 'user')->first();
        foreach ($allUsers as $user) {
            UserRole::create([
                'user_id' => $user->id,
                'role_id' => $userRole->id
            ]);
        }

        // 4. Создаем статусы бронирования
        $statuses = ['pending', 'Confirmed', 'Modified', 'Cancelled', 'Completed', 'No-show'];
        foreach ($statuses as $status) {
            ReservationStatus::create(['name' => $status]);
        }

        // 5. Создаем типы напоминаний
        $reminders = [
            ['name' => '30min', 'minutes_before' => 30, 'is_default' => false],
            ['name' => '1h', 'minutes_before' => 60, 'is_default' => true],
            ['name' => '2h', 'minutes_before' => 120, 'is_default' => false],
            ['name' => '3h', 'minutes_before' => 180, 'is_default' => false],
            ['name' => '6h', 'minutes_before' => 360, 'is_default' => false],
            ['name' => '12h', 'minutes_before' => 720, 'is_default' => false],
            ['name' => '24h', 'minutes_before' => 1440, 'is_default' => false],
            ['name' => '2d', 'minutes_before' => 2880, 'is_default' => false],
        ];

        foreach ($reminders as $reminder) {
            ReminderType::create($reminder);
        }

        // 6. Создаем сети ресторанов
        $chains = RestaurantChain::factory(2)->create();

        // 7. Для каждой сети создаем 5 ресторанов
        $restaurants = collect();
        $chainAdmins = collect();

        foreach ($chains as $chain) {
            $chainRestaurants = Restaurant::factory(5)->create([
                'restaurant_chain_id' => $chain->id
            ]);

            $restaurants = $restaurants->merge($chainRestaurants);

            // Создаем суперадмина для сети
            $superAdmin = User::factory()->create();
            UserRole::create([
                'user_id' => $superAdmin->id,
                'role_id' => Role::where('name', 'superadmin')->first()->id
            ]);

            ChainSuperAdmin::create([
                'user_id' => $superAdmin->id,
                'restaurant_chain_id' => $chain->id
            ]);

            // Создаем админов для ресторанов сети
            foreach ($chainRestaurants as $restaurant) {
                $admin = User::factory()->create();
                UserRole::create([
                    'user_id' => $admin->id,
                    'role_id' => Role::where('name', 'admin')->first()->id
                ]);

                RestaurantAdmin::create([
                    'user_id' => $admin->id,
                    'restaurant_id' => $restaurant->id
                ]);

                $chainAdmins->push($admin);
            }
        }

        // 8. Создаем 10 независимых ресторанов
        $independentRestaurants = Restaurant::factory(10)->create();
        $independentAdmins = collect();

        foreach ($independentRestaurants as $restaurant) {
            $admin = User::factory()->create();
            UserRole::create([
                'user_id' => $admin->id,
                'role_id' => Role::where('name', 'admin')->first()->id
            ]);

            RestaurantAdmin::create([
                'user_id' => $admin->id,
                'restaurant_id' => $restaurant->id
            ]);

            $independentAdmins->push($admin);
        }

        $allRestaurants = $restaurants->merge($independentRestaurants);

        // 9. Создаем отзывы для ресторанов
        foreach ($allRestaurants as $restaurant) {
            Review::factory(2)->create([
                'restaurant_id' => $restaurant->id,
                'user_id' => $users->random()->id
            ]);
        }

        // 10. Создаем расписания на особые даты
        foreach ($allRestaurants as $restaurant) {
            RestaurantSchedule::create([
                'restaurant_id' => $restaurant->id,
                'date' => '2024-12-31',
                'opens_at' => '10:00',
                'closes_at' => '18:00',
                'is_closed' => false,
                'description' => 'New Year Eve Special Hours'
            ]);

            RestaurantSchedule::create([
                'restaurant_id' => $restaurant->id,
                'date' => '2025-01-01',
                'opens_at' => '10:00',
                'closes_at' => '18:00',
                'is_closed' => false,
                'description' => 'New Year Day Special Hours'
            ]);
        }

        // 11. Создаем столики для ресторанов
        foreach ($allRestaurants as $restaurant) {
            Table::factory(10)->create([
                'restaurant_id' => $restaurant->id
            ]);
        }
    }
}

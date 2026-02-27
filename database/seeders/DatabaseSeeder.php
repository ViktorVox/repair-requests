<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ServiceRequest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Создаем диспетчера
        User::factory()->create([
            'name' => 'Dispatcher',
            'email' => 'dispatcher@test.com',
            'password' => Hash::make('password'),
            'role' => 'dispatcher',
        ]);

        // 2. Создаем двух мастеров
        $master1 = User::factory()->create([
            'name' => 'Master One',
            'email' => 'master1@test.com',
            'password' => Hash::make('password'),
            'role' => 'master',
        ]);

        $master2 = User::factory()->create([
            'name' => 'Master Two',
            'email' => 'master2@test.com',
            'password' => Hash::make('password'),
            'role' => 'master',
        ]);

        // 3. Создаем 5 тестовых заявок
        ServiceRequest::create([
            'client_name' => 'Иван Иванов',
            'phone' => '+79991234567',
            'address' => 'ул. Ленина, д. 10, кв. 5',
            'problem_text' => 'Не морозит холодильник.',
            'status' => 'new',
            'assigned_to' => null,
        ]);

        ServiceRequest::create([
            'client_name' => 'Петр Петров',
            'phone' => '+79997654321',
            'address' => 'пр. Мира, д. 42',
            'problem_text' => 'Стиральная машина протекает снизу.',
            'status' => 'assigned',
            'assigned_to' => $master1->id,
        ]);

        ServiceRequest::create([
            'client_name' => 'Анна Смирнова',
            'phone' => '+79990001122',
            'address' => 'ул. Пушкина, д. 15',
            'problem_text' => 'Микроволновка искрит при включении.',
            'status' => 'in_progress',
            'assigned_to' => $master2->id,
        ]);

        ServiceRequest::create([
            'client_name' => 'Елена Соколова',
            'phone' => '+79993334455',
            'address' => 'ул. Гагарина, д. 8',
            'problem_text' => 'Телевизор не включается, индикатор не горит.',
            'status' => 'done',
            'assigned_to' => $master1->id,
        ]);

        ServiceRequest::create([
            'client_name' => 'Дмитрий Волков',
            'phone' => '+79995556677',
            'address' => 'ул. Садовая, д. 3',
            'problem_text' => 'Сломался замок на входной двери.',
            'status' => 'canceled',
            'assigned_to' => null,
        ]);
    }
}
<?php

namespace Tests\Feature;

use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceRequestTest extends TestCase
{
    // Этот трейт будет очищать базу после каждого теста, чтобы они не мешали друг другу
    use RefreshDatabase;

    /**
     * Тест 1: Гость может создать заявку
     */
    public function test_guest_can_create_service_request(): void
    {
        $response = $this->post('/requests', [
            'client_name' => 'Иван Тестовый',
            'phone' => '+79991234567',
            'address' => 'ул. Тестовая, 1',
            'problem_text' => 'Сломался тестовый стенд',
        ]);

        // Проверяем, что нас редиректнуло обратно на главную
        $response->assertRedirect('/');
        
        // Проверяем, что в сессии есть сообщение об успехе
        $response->assertSessionHas('success');

        // Проверяем, что запись реально появилась в базе данных со статусом 'new'
        $this->assertDatabaseHas('service_requests', [
            'client_name' => 'Иван Тестовый',
            'status' => 'new',
        ]);
    }

    /**
     * Тест 2: Мастер может взять новую заявку в работу
     */
    public function test_master_can_take_request_in_work(): void
    {
        // Создаем тестового мастера
        $master = User::factory()->create([
            'role' => 'master'
        ]);

        // Создаем тестовую новую заявку
        $request = ServiceRequest::create([
            'client_name' => 'Клиент',
            'phone' => '123',
            'address' => 'Адрес',
            'problem_text' => 'Проблема',
            'status' => 'new'
        ]);

        // Авторизуемся как мастер и шлем POST запрос на взятие заявки
        $response = $this->actingAs($master)->post("/requests/{$request->id}/take");

        // Проверяем, что ошибок нет
        $response->assertSessionHasNoErrors();

        // Проверяем, что в базе статус изменился на assigned и привязался ID мастера
        $this->assertDatabaseHas('service_requests', [
            'id' => $request->id,
            'status' => 'assigned',
            'assigned_to' => $master->id,
        ]);
    }
}
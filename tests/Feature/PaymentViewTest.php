<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_see_users_and_their_ids_on_payment_page()
    {
        // Создаем тестового пользователя и авторизуемся
        $user = User::factory()->create();
        $this->actingAs($user);

        // Создаем нескольких дополнительных пользователей для отображения в дашборде
        $otherUsers = User::factory()->count(3)->create();

        // Посещаем страницу payment
        $response = $this->get(route('payment'));

        // Проверяем успешный статус ответа
        $response->assertStatus(200);

        // Проверяем, что страница содержит нужные элементы
        // Проверяем, что отображаются все пользователи (включая текущего)
        $allUsers = User::all();

        foreach ($allUsers as $user) {
            $response->assertSee($user->name, false); // если есть имя пользователя
            $response->assertSee((string) $user->id, false); // обязательно преобразуем ID в строку
        }

    }

    public function test_payment_route_exists_and_is_accessible()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/payment');

        // Проверяем, что маршрут существует и доступен
        $response->assertOk();

        // Или проверяем через именованный маршрут
        $response = $this->get(route('payment'));
        $response->assertOk();
    }

    public function test_user_comes_to_window_page()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('window', ['user' => $user->id]));

        // Проверяем успешный статус ответа
        $response->assertStatus(200);
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginSystemTest extends TestCase
{
    use RefreshDatabase; // Добавьте этот trait для очистки БД между тестами

    // ======================
    // SUCCESSFUL LOGIN TESTS
    // ======================

    public function test_user_can_login_with_valid_credentials()
    {
        // Given: Существующий пользователь
        $user = $this->createTestUser();

        // When: Пользователь отправляет правильные данные
        $response = $this->postLoginRequest($user->username, 'password123');

        // Then: Логин успешен
        $this->assertLoginSucceeded($response, $user);
    }

    // ======================
    // FAILED LOGIN TESTS
    // ======================

    public function test_user_cannot_login_with_invalid_password()
    {
        // Given: Пользователь с правильным паролем
        $user = $this->createUserWithPassword('correctpassword');

        // When: Пользователь отправляет неправильный пароль
        $response = $this->postLoginRequest($user->username, 'wrongpassword');

        // Then: Логин должен провалиться
        $this->assertLoginFailed($response);
        $this->assertErrorDisplayed($response, 'Username or password is wrong!');
    }

    public function test_user_cannot_login_with_non_existent_username()
    {
        // When: Попытка входа с несуществующим username
        $response = $this->postLoginRequest('nonexistent', 'anypassword');

        // Then: Логин должен провалиться
        $this->assertLoginFailed($response);
        $this->assertErrorDisplayed($response, 'Username or password is wrong!');
    }

    // ======================
    // VALIDATION TESTS
    // ======================

    public function test_login_requires_username_and_password()
    {
        // When: Пустой username
        $response = $this->postLoginRequest('', 'password123');

        // Then: Ошибка валидации username
        $response->assertSessionHasErrors(['username']);

        // When: Пустой password
        $response = $this->postLoginRequest('testuser', '');

        // Then: Ошибка валидации password
        $response->assertSessionHasErrors(['password']);
    }

    // ======================
    // SECURITY TESTS
    // ======================

    public function test_login_form_is_protected_from_xss()
    {
        // Given: Существующий пользователь
        $this->createTestUser();

        // When: Попытка XSS инъекции
        $response = $this->postLoginRequest('<script>alert("xss")</script>', 'password123');

        // Then: XSS должен быть заблокирован
        $this->assertLoginFailed($response);
        $response->assertDontSee('<script>', false);
    }

    public function test_session_is_regenerated_after_login()
    {
        // Given: Существующий пользователь
        $user = $this->createTestUser();
        
        // Получаем текущую сессию
        $this->post('/'); // Простой запрос для инициализации сессии
        $oldSessionId = session()->getId();

        // When: Пользователь логинится
        $this->postLoginRequest($user->username, 'password123');

        // Then: ID сессии должен измениться
        $this->assertNotEquals($oldSessionId, session()->getId());
    }

    public function test_user_is_redirected_to_intended_url_after_login()
    {
        // Given: Существующий пользователь
        $user = $this->createTestUser();

        // Когда: Пользователь пытается получить доступ к защищенной странице
        $this->get('/dashboards/');

        // И затем логинится
        $response = $this->postLoginRequest($user->username, 'password123');

        // Тогда: Должен быть перенаправлен на /dashboards/
        $response->assertRedirect('/dashboards/');
    }

    // ======================
    // HELPER METHODS
    // ======================

    /**
     * Создает тестового пользователя
     */
    private function createTestUser(): User
    {
        return User::create([
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'card' => '8600 1234 5678 9012',
            'balance' => 1000000,
            'profits' => 0,
            'expenses' => 0,
        ]);
    }

    /**
     * Создает пользователя с указанным паролем
     */
    private function createUserWithPassword(string $password): User
    {
        return User::create([
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => Hash::make($password),
            'card' => '8600 1234 5678 9012',
            'balance' => 1000000,
            'profits' => 0,
            'expenses' => 0,
        ]);
    }

    /**
     * Отправляет запрос на логин
     */
    private function postLoginRequest(string $username, string $password)
    {
        return $this->post(route('loginsystem'), [
            'username' => $username,
            'password' => $password,
        ]);
    }

    /**
     * Проверяет успешный логин
     */
    private function assertLoginSucceeded($response, User $user)
    {
        // Проверяем аутентификацию
        $this->assertAuthenticated('web');
        $this->assertAuthenticatedAs($user);

        // Проверяем ID пользователя
        $this->assertEquals($user->id, auth()->id());

        // Проверяем редирект
        $response->assertRedirect('/dashboards/');

        // Проверяем доступ к dashboard
        $this->get('/dashboards/')->assertOk();
    }

    /**
     * Проверяет провальный логин
     */
    private function assertLoginFailed($response)
    {
        $this->assertGuest('web');
    }

    /**
     * Проверяет отображение ошибки
     */
    private function assertErrorDisplayed($response, string $errorMessage)
    {
        $response->assertSee($errorMessage, false);
    }
}
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegisterSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_with_valid_data()
    {
        // Arrange
        $userData = [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123', // Правильное поле для confirmed
        ];

        // Act
        $response = $this->post(route('registersystem'), $userData);

        // Assert
        // Сначала просто проверьте, что есть редирект
        $response->assertStatus(302);

        // Проверяем аутентификацию
        $this->assertAuthenticated();

        // Проверяем, что пользователь создан в базе данных
        $this->assertDatabaseHas('users', [
            'username' => 'testuser',
            'email' => 'test@example.com',
        ]);

        // Проверяем конкретные поля
        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals(1000000, $user->balance);
        $this->assertEquals(0, $user->profits);
        $this->assertEquals(0, $user->expenses);

        // Проверяем формат номера карты
        $this->assertStringStartsWith('8600', $user->card);

        // Проверяем, что пароль захеширован
        $this->assertTrue(Hash::check('password123', $user->password));

        // Дополнительно: проверяем длину карты
        $this->assertEquals(19, strlen($user->card)); // 16 цифр + 4 пробела
    }

    public function test_registration_requires_all_fields()
    {
        // Act
        $response = $this->post(route('registersystem'), []);

        // Assert
        $response->assertSessionHasErrors(['username', 'email', 'password']);
    }

    public function test_username_must_be_unique()
    {
        // Arrange
        User::create([
            'username' => 'existinguser',
            'email' => 'existing@example.com',
            'password' => bcrypt('password123'),
            'card' => '8600 1111 2222 3333',
            'balance' => 1000000,
        ]);

        $userData = [
            'username' => 'existinguser', // Тот же username
            'email' => 'new@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // Act
        $response = $this->post(route('registersystem'), $userData);

        // Assert
        $response->assertSessionHasErrors(['username']);
    }

    public function test_email_must_be_unique()
    {
        // Arrange
        User::create([
            'username' => 'existinguser',
            'email' => 'existing@example.com',
            'password' => bcrypt('password123'),
            'card' => '8600 1111 2222 3333',
            'balance' => 1000000,
        ]);

        $userData = [
            'username' => 'newuser',
            'email' => 'existing@example.com', // Тот же email
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // Act
        $response = $this->post(route('registersystem'), $userData);

        // Assert
        $response->assertSessionHasErrors(['email']);
    }

    public function test_password_must_be_confirmed()
    {
        // Arrange
        $userData = [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'differentpassword',
        ];

        // Act
        $response = $this->post(route('registersystem'), $userData);

        // Assert
        $response->assertSessionHasErrors(['password']);
    }

    public function test_password_minimum_length()
    {
        // Arrange
        $userData = [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => '123', // Слишком короткий
            'password_confirmation' => '123',
        ];

        // Act
        $response = $this->post(route('registersystem'), $userData);

        // Assert
        $response->assertSessionHasErrors(['password']);
    }

    // tests/Feature/RegisterSystemTest.php
    public function test_user_is_logged_in_after_registration()
    {
        // Arrange
        $userData = [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // Act
        $response = $this->post(route('registersystem'), $userData);

        $this->assertDatabaseHas('users', [
            'username' => 'testuser',
            'email' => 'test@example.com',
        ]);
        // Assert
        // Убедитесь, что пользователь аутентифицирован

        // Проверьте редирект
        $response->assertRedirect('/dashboards/');
        $this->assertAuthenticated();

    }

    public function test_generated_card_number_has_correct_format()
    {
        $userData = [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // Act
        $response = $this->post(route('registersystem'), $userData);
        $response->assertStatus(302);

        // Assert
        $user = User::first();
        $this->assertNotNull($user);

        // Проверяем формат: 8600 XXXX XXXX XXXX
        // Правильный синтаксис регулярного выражения
        $this->assertMatchesRegularExpression('/^8600 \d{4} \d{4} \d{4}$/', $user->card);

        // Проверяем длину (должно быть 19 символов: 16 цифр + 3 пробела)
        $this->assertEquals(19, strlen($user->card));

        // Дополнительная проверка: все символы должны быть цифрами или пробелами
        $this->assertMatchesRegularExpression('/^[0-9 ]+$/', $user->card);
    }

    public function test_new_user_gets_initial_balance_of_one_million()
    {
        // Arrange
        $userData = [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // Act
        $response = $this->post(route('registersystem'), $userData);
        $response->assertStatus(302);

        // Assert
        $user = User::first();
        $this->assertNotNull($user);
        $this->assertEquals(1000000, $user->balance);
    }

    public function test_debug_registration_flow()
    {
        // Arrange
        $userData = [
            'username' => 'debuguser',
            'email' => 'debug@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // Act
        $response = $this->post(route('registersystem'), $userData);

        // Debug: посмотрим, что происходит
        if ($response->isRedirect()) {
            $redirectUrl = $response->headers->get('Location');
            echo "\nRedirect URL: ".$redirectUrl;
        }

        // Проверяем состояние
        $this->assertAuthenticated();

        // Если есть ошибки валидации, покажем их
        if (session()->has('errors')) {
            echo "\nValidation errors: ";
            print_r(session('errors')->all());
        }

    }
}

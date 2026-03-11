<?php

namespace Tests\Feature;

use Tests\TestCase;

class LoginViewTest extends TestCase
{
    
    /**
     * Проверяет доступность страницы логина
     */
    public function test_login_page_is_accessible()
    {
        // Act
        $response = $this->getLoginPage();

        // Assert
        $this->assertPageLoadsSuccessfully($response);
    }

    /**
     * Проверяет отображение ошибок валидации на странице логина
     */
    public function test_login_page_shows_validation_errors()
    {
        // Arrange
        $this->simulateFailedLoginAttempt();

        // Act
        $response = $this->getLoginPage();

        // Assert
        $this->assertValidationErrorsDisplayed($response);
    }

    /**
     * Проверяет производительность загрузки страницы логина
     */
    public function test_login_page_loads_within_acceptable_time()
    {
        // Act & Assert
        $this->assertPageLoadsQuickly();
    }

    // ======================
    // HELPER METHODS
    // ======================

    /**
     * Открывает страницу логина
     */
    private function getLoginPage()
    {
        return $this->get('/login');
    }

    /**
     * Проверяет успешную загрузку страницы
     */
    private function assertPageLoadsSuccessfully($response)
    {
        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    /**
     * Симулирует неудачную попытку входа
     */
    private function simulateFailedLoginAttempt()
    {
        session()->flash('errors', collect([
            'username' => ['The username field is required.'],
            'password' => ['The password field is required.'],
        ]));
    }

    /**
     * Проверяет отображение ошибок валидации
     */
    private function assertValidationErrorsDisplayed($response)
    {
        $response->assertSee('The username field is required.', false);
        $response->assertSee('The password field is required.', false);
    }

    /**
     * Проверяет что страница загружается быстро
     */
    private function assertPageLoadsQuickly()
    {
        $maxLoadTimeMs = 500;

        $startTime = microtime(true);
        $response = $this->getLoginPage();
        $endTime = microtime(true);

        $loadTimeMs = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);
        $this->assertLessThan(
            $maxLoadTimeMs,
            $loadTimeMs,
            "Login page should load within {$maxLoadTimeMs}ms, but took {$loadTimeMs}ms"
        );
    }
}

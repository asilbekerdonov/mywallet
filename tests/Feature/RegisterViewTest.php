<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_page_is_accessible()
    {
        // Act
        $response = $this->get('/register');

        // Assert
        $response->assertStatus(200);
        $response->assertViewIs('auth.register');
    }

    public function test_register_page_has_correct_route_name()
    {
        // Act & Assert
        $this->get(route('register'))
            ->assertStatus(200);
    }
}

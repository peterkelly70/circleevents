<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSee('CircleEvents');
    }

    public function test_the_install_page_can_be_rendered(): void
    {
        $response = $this->get('/install');

        $response
            ->assertOk()
            ->assertSee('Install CircleEvents')
            ->assertSee('tools/lockdown.sh');
    }
}

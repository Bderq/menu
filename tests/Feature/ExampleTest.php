<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_redirects_to_default_store_without_tracking(): void
    {
        $this->get('/')->assertRedirect('/gorukle');

        $this->assertDatabaseCount('visitors', 0);
        $this->assertDatabaseCount('visits', 0);
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_root_redirects_to_default_store(): void
    {
        $this->get('/')->assertRedirect('/gorukle');
    }
}

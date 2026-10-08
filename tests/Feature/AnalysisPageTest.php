<?php

use App\Models\User;

it('renders the analysis dashboard and summary cards', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/analysis');

    $response->assertOk();
    $response->assertViewIs('analysis.index');
    $response->assertSee('Сводка');
    $response->assertSee('Всего');
    $response->assertSee('На подписании');
    $response->assertSee('Подписано');
    $response->assertSee('Отклонено');
    $response->assertSee('mainChart');
    $response->assertSee('userChart');
    $response->assertSee('statusChart');
});

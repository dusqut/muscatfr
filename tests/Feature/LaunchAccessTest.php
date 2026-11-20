<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LaunchAccessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'cache.default' => 'array',
            'launch.enabled' => true,
            'launch.password_hash' => Hash::make('test-developer-password'),
        ]);
    }

    public function test_visitors_see_the_launch_page_instead_of_the_storefront(): void
    {
        $this->get('/en')
            ->assertOk()
            ->assertViewIs('storefront.launch')
            ->assertSee('A new scent of Muscat is on its way.')
            ->assertDontSee('Find the scent that feels like');
    }

    public function test_root_route_shows_the_launch_page_before_its_locale_redirect(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertViewIs('storefront.launch');
    }

    public function test_arabic_visitors_receive_the_arabic_launch_page(): void
    {
        $this->get('/ar')
            ->assertOk()
            ->assertSee('<html lang="ar" dir="rtl">', false)
            ->assertSee('عطرٌ جديد من مسقط يقترب.');
    }

    public function test_developer_password_grants_session_access_to_requested_locale(): void
    {
        $this->get('/ar');

        $this->post('/launch-access/ar', ['password' => 'test-developer-password'])
            ->assertRedirect('/ar');

        $this->get('/ar')
            ->assertOk()
            ->assertSee('<html lang="ar" dir="rtl">', false)
            ->assertSee('اكتشفوا عطراً يشبه', false);
    }

    public function test_invalid_password_does_not_grant_access(): void
    {
        $this->post('/launch-access/en', ['password' => 'incorrect'])
            ->assertRedirect(route('launch.access', ['locale' => 'en']))
            ->assertSessionHasErrors('password');

        $this->get('/en')
            ->assertViewIs('storefront.launch');
    }

    public function test_missing_password_hash_fails_closed(): void
    {
        config(['launch.password_hash' => null]);

        $this->post('/launch-access/en', ['password' => 'anything'])
            ->assertServiceUnavailable();
    }

    public function test_password_attempts_are_rate_limited(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post('/launch-access/en', ['password' => 'incorrect'])
                ->assertRedirect();
        }

        $this->post('/launch-access/en', ['password' => 'incorrect'])
            ->assertTooManyRequests();
    }
}

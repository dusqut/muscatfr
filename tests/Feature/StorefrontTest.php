<?php

namespace Tests\Feature;

use Tests\TestCase;

class StorefrontTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['launch.enabled' => false]);
    }

    public function test_homepage_redirects_to_the_english_storefront(): void
    {
        $this->get('/')
            ->assertRedirect(route('store.home', ['locale' => 'en']));
    }

    public function test_english_storefront_renders_with_search_metadata(): void
    {
        $this->get('/en')
            ->assertOk()
            ->assertSee('<html lang="en" dir="ltr">', false)
            ->assertSee('Find the scent that feels like', false)
            ->assertSee('rel="canonical"', false)
            ->assertSee('hreflang="ar"', false);
    }

    public function test_arabic_storefront_renders_right_to_left_with_language_switch(): void
    {
        $this->get('/ar')
            ->assertOk()
            ->assertSee('<html lang="ar" dir="rtl">', false)
            ->assertSee('اكتشفوا عطراً يشبه', false)
            ->assertSee('href="http://localhost/en"', false);
    }

    public function test_unsupported_locales_are_not_storefront_routes(): void
    {
        $this->get('/fr')->assertNotFound();
    }
}

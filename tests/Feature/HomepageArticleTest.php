<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageArticleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_publish_and_edit_an_article_in_the_existing_homepage_form(): void
    {
        $this->actingAs($this->userWithRole('admin'));
        $form = $this->get(route('admin.homepage.edit'));
        $form->assertOk()->assertSee('Homepage SEO Article')->assertSee('tinymce.init', false);
        $data = $form->viewData('content')->except(['home_hero_image'])->all();

        $data['home_seo_title'] = 'A guide to hotspot billing';
        $data['home_seo_body'] = '<h2>Introduction</h2><p>Manage <strong>WiFi subscriptions</strong> across Kenya.</p><ul><li>Flexible plans</li></ul><p><a href="/services">View features</a></p>';
        $this->put(route('admin.homepage.update'), $data)->assertSessionHasNoErrors()->assertRedirect();

        $this->get('/')
            ->assertOk()
            ->assertSee('A guide to hotspot billing')
            ->assertSee('<h3>Introduction</h3>', false)
            ->assertSee('<strong>WiFi subscriptions</strong>', false)
            ->assertSee('<a href="/services">View features</a>', false)
            ->assertSeeInOrder(['id="testimonials"', 'id="homepage-article"', '<footer'], false);

        $saved = $this->get(route('admin.homepage.edit'))->viewData('content');
        $this->assertStringContainsString('<h3>Introduction</h3>', $saved['home_seo_body']);
        $this->assertSame($data['home_hero_title'], Setting::valueFor('home_hero_title'));

        $data['home_seo_body'] = '<p>Updated guide.</p>';
        $this->put(route('admin.homepage.update'), $data)->assertSessionHasNoErrors();
        $this->get('/')->assertSee('Updated guide.')->assertDontSee('WiFi subscriptions');
    }

    public function test_empty_content_hides_the_section_and_clearing_it_unpublishes_the_article(): void
    {
        $this->get('/')->assertOk()->assertDontSee('id="homepage-article"', false);
        Setting::putValue('home_seo_title', 'Existing guide');
        Setting::putValue('home_seo_body', '<p>Published text.</p>');
        $this->actingAs($this->userWithRole('admin'));
        $data = $this->get(route('admin.homepage.edit'))->viewData('content')->except(['home_hero_image'])->all();
        $data['home_seo_title'] = '';
        $data['home_seo_body'] = '<p>&nbsp;<br></p>';

        $this->put(route('admin.homepage.update'), $data)->assertSessionHasNoErrors();
        $this->get('/')->assertDontSee('id="homepage-article"', false)->assertDontSee('Published text.');
    }

    public function test_customer_and_guest_cannot_publish_homepage_content(): void
    {
        $this->get(route('admin.homepage.edit'))->assertRedirect(route('login'));
        $this->put(route('admin.homepage.update'), [])->assertRedirect(route('login'));
        $this->actingAs($this->userWithRole('customer'));
        $this->get(route('admin.homepage.edit'))->assertForbidden();
        $this->put(route('admin.homepage.update'), [])->assertForbidden();
        $this->assertDatabaseMissing('settings', ['key' => 'home_seo_body']);
    }

    public function test_unsafe_markup_is_removed_before_saving_and_rendering(): void
    {
        $this->actingAs($this->userWithRole('admin'));
        $data = $this->get(route('admin.homepage.edit'))->viewData('content')->except(['home_hero_image'])->all();
        $data['home_seo_body'] = '<script>alert(1)</script><p onclick="alert(1)" style="position:fixed">Safe content <a href="javascript:alert(1)">link</a></p><iframe src="https://example.com"></iframe>';

        $this->put(route('admin.homepage.update'), $data)->assertSessionHasNoErrors();
        $this->assertSame('<p>Safe content <a>link</a></p>', Setting::valueFor('home_seo_body'));

        // Sanitize legacy or directly inserted settings on output as well.
        Setting::putValue('home_seo_body', '<p>Still safe</p><script>alert(123456)</script>');
        $this->get('/')->assertSee('<p>Still safe</p>', false)->assertDontSee('alert(123456)', false);
    }

    public function test_invalid_article_does_not_partially_save_homepage_settings(): void
    {
        $this->actingAs($this->userWithRole('admin'));
        $data = $this->get(route('admin.homepage.edit'))->viewData('content')->except(['home_hero_image'])->all();
        $data['home_seo_title'] = '';
        $data['home_seo_body'] = '<p>A guide needs a title.</p>';
        $this->put(route('admin.homepage.update'), $data)->assertSessionHasErrors('home_seo_title');
        $this->assertDatabaseCount('settings', 0);

        $data['home_seo_title'] = 'Long article';
        $data['home_seo_body'] = '<p>'.str_repeat('界', 21000).'</p>';
        $this->put(route('admin.homepage.update'), $data)->assertSessionHasErrors('home_seo_body');
        $this->assertDatabaseCount('settings', 0);
    }

    private function userWithRole(string $role): User
    {
        $role = Role::create(['name' => $role, 'label' => ucfirst($role)]);

        return User::factory()->create(['role_id' => $role->id]);
    }
}

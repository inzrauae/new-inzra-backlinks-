<?php

namespace Tests\Feature\Cart;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_guest_is_redirected_to_login_when_adding_to_cart(): void
    {
        $product = Product::factory()->create();

        $response = $this->get("/cart/add/{$product->slug}");

        $response->assertRedirect('/login');
    }

    public function test_a_guests_intended_cart_addition_survives_the_login_redirect(): void
    {
        $product = Product::factory()->create();
        $user = User::factory()->create();

        $this->get("/cart/add/{$product->slug}?target_url=https%3A%2F%2Fexample.com&anchor_text=hello");

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect();
        $location = $response->headers->get('Location');
        $this->assertStringContainsString("/cart/add/{$product->slug}?", $location);
        parse_str(parse_url($location, PHP_URL_QUERY), $query);
        $this->assertSame('https://example.com', $query['target_url']);
        $this->assertSame('hello', $query['anchor_text']);
    }

    public function test_a_logged_in_user_can_add_a_product_to_their_cart(): void
    {
        $product = Product::factory()->create(['name' => 'Guest Post on a DA70 Site']);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get("/cart/add/{$product->slug}?target_url=https%3A%2F%2Fexample.com&anchor_text=best+seo&target_country=Sri+Lanka&quantity=2");

        $response->assertRedirect(route('cart.index'));

        $cartResponse = $this->get('/cart');
        $cartResponse->assertOk();
        $cartResponse->assertSee('Guest Post on a DA70 Site');
        $cartResponse->assertSee('https://example.com');
        $cartResponse->assertSee('best seo');
        $cartResponse->assertSee('Sri Lanka');
    }

    public function test_adding_to_cart_via_ajax_returns_json_with_the_new_count(): void
    {
        $product = Product::factory()->create(['name' => 'Guest Post Placement']);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson("/cart/add/{$product->slug}");

        $response->assertOk()->assertJson([
            'message' => 'Guest Post Placement added to your cart.',
            'count' => 1,
        ]);
    }

    public function test_adding_a_second_product_keeps_both_lines_in_the_cart(): void
    {
        $productA = Product::factory()->create(['name' => 'Product A']);
        $productB = Product::factory()->create(['name' => 'Product B']);
        $user = User::factory()->create();

        $this->actingAs($user)->get("/cart/add/{$productA->slug}");
        $this->actingAs($user)->get("/cart/add/{$productB->slug}");

        $response = $this->actingAs($user)->get('/cart');

        $response->assertSee('Product A');
        $response->assertSee('Product B');
    }

    public function test_a_user_can_update_the_quantity_of_a_cart_line(): void
    {
        $product = Product::factory()->create(['price' => 10]);
        $user = User::factory()->create();

        $this->actingAs($user)->get("/cart/add/{$product->slug}");
        $lineId = array_key_first(session('cart'));

        $response = $this->actingAs($user)->patch("/cart/{$lineId}", ['quantity' => 3]);

        $response->assertRedirect(route('cart.index'));
        $this->assertSame(3, session('cart')[$lineId]['quantity']);
    }

    public function test_a_user_can_remove_a_cart_line(): void
    {
        $product = Product::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user)->get("/cart/add/{$product->slug}");
        $lineId = array_key_first(session('cart'));

        $response = $this->actingAs($user)->delete("/cart/{$lineId}");

        $response->assertRedirect(route('cart.index'));
        $this->assertArrayNotHasKey($lineId, session('cart', []));
    }
}

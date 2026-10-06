<?php

namespace Tests\Feature;

use App\Livewire\Auth\Login;
use App\Livewire\User\Profile as UserProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class LoginPasswordVisibilityAndProfileThemeTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $kasir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Utama',
            'username' => 'adminutama',
            'code' => 'ADM-001',
            'email' => 'admin@brewisland.com',
            'password' => Hash::make('password123'),
            'role' => 'Admin',
        ]);

        $this->kasir = User::create([
            'name' => 'Kasir Satu',
            'username' => 'kasirsatu',
            'code' => 'KAS-001',
            'email' => 'kasir@brewisland.com',
            'password' => Hash::make('kasirpass123'),
            'role' => 'Kasir',
        ]);
    }

    /**
     * 1. Halaman login menampilkan input password dan tombol lihat password yang benar.
     */
    public function test_01_login_page_renders_password_toggle_elements(): void
    {
        $response = $this->get('/login');
        $response->assertOk();
        $response->assertSee('id="togglePassword"', false);
        $response->assertSee('id="password"', false);
        $response->assertSee('id="eyeIcon"', false);
        $response->assertSee('toggleLoginPassword', false);
        $response->assertSee('showPassword', false);
    }

    /**
     * 2. Otentikasi login tetap bekerja normal setelah perbaikan lihat password.
     */
    public function test_02_login_authentication_works_correctly(): void
    {
        Livewire::test(Login::class)
            ->set('login_id', 'adminutama')
            ->set('password', 'password123')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($this->admin);
    }

    /**
     * 3. Login dengan kredensial salah tetap menampilkan error yang sesuai.
     */
    public function test_03_login_with_invalid_credentials_returns_error(): void
    {
        Livewire::test(Login::class)
            ->set('login_id', 'adminutama')
            ->set('password', 'salahpass')
            ->call('login')
            ->assertHasErrors(['loginError']);

        $this->assertGuest();
    }

    /**
     * 4. Halaman profile dapat diakses oleh user terotentikasi.
     */
    public function test_04_profile_page_can_be_accessed(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('user.profile'));
        $response->assertOk();
        $response->assertSee('Profil Saya');
        $response->assertSee('Admin Utama');
        $response->assertSee('admin@brewisland.com');
        $response->assertSee('ADM-001');
    }

    /**
     * 5. Component profile memuat data user login dengan benar.
     */
    public function test_05_profile_component_mounts_user_data(): void
    {
        $this->actingAs($this->kasir);

        Livewire::test(UserProfile::class)
            ->assertSet('name', 'Kasir Satu')
            ->assertSet('email', 'kasir@brewisland.com')
            ->assertSet('username', 'kasirsatu')
            ->assertSet('code', 'KAS-001')
            ->assertSet('role', 'Kasir');
    }

    /**
     * 6. User dapat memperbarui nama dan email profilnya.
     */
    public function test_06_user_can_update_profile_name_and_email(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(UserProfile::class)
            ->set('name', 'Admin Terupdate')
            ->set('email', 'admin.new@brewisland.com')
            ->call('updateProfile')
            ->assertHasNoErrors()
            ->assertSee('Data profil Anda berhasil diperbarui.');

        $fresh = $this->admin->fresh();
        $this->assertEquals('Admin Terupdate', $fresh->name);
        $this->assertEquals('admin.new@brewisland.com', $fresh->email);
    }

    /**
     * 7. Validasi email duplikat pada update profil.
     */
    public function test_07_profile_update_rejects_duplicate_email(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(UserProfile::class)
            ->set('name', 'Admin Utama')
            ->set('email', 'kasir@brewisland.com') // already used by kasir
            ->call('updateProfile')
            ->assertHasErrors(['email']);

        $this->assertEquals('admin@brewisland.com', $this->admin->fresh()->email);
    }

    /**
     * 8. Update profil tidak mengubah role user.
     */
    public function test_08_profile_update_does_not_modify_user_role(): void
    {
        $this->actingAs($this->kasir);

        Livewire::test(UserProfile::class)
            ->set('name', 'Kasir Berganti Nama')
            ->call('updateProfile')
            ->assertHasNoErrors();

        $this->assertEquals('Kasir', $this->kasir->fresh()->role);
    }

    /**
     * 9. Ganti password membutuhkan password saat ini yang valid.
     */
    public function test_09_change_password_validates_current_password(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(UserProfile::class)
            ->set('current_password', 'passwordsalah')
            ->set('new_password', 'newsecret123')
            ->set('new_password_confirmation', 'newsecret123')
            ->call('updatePassword')
            ->assertHasErrors(['current_password']);

        // Password in DB must still be password123
        $this->assertTrue(Hash::check('password123', $this->admin->fresh()->password));
    }

    /**
     * 10. Ganti password berhasil dengan kredensial yang valid dan dapat digunakan untuk login.
     */
    public function test_10_change_password_succeeds_and_allows_login_with_new_password(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(UserProfile::class)
            ->set('current_password', 'password123')
            ->set('new_password', 'newsecret123')
            ->set('new_password_confirmation', 'newsecret123')
            ->call('updatePassword')
            ->assertHasNoErrors()
            ->assertSee('Password Anda berhasil diperbarui.');

        $this->assertTrue(Hash::check('newsecret123', $this->admin->fresh()->password));

        // Test login with the newly created password
        $this->app['auth']->logout();

        Livewire::test(Login::class)
            ->set('login_id', 'adminutama')
            ->set('password', 'newsecret123')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.dashboard'));
    }

    /**
     * 11. File logo baru logoBrewIsland.png tersedia di folder public.
     */
    public function test_11_new_logo_asset_exists_in_public_directory(): void
    {
        $logoPath = public_path('logoBrewIsland.png');
        $this->assertFileExists($logoPath);
        $this->assertGreaterThan(0, filesize($logoPath));
    }

    /**
     * 12. Halaman aplikasi dan login memuat logo baru dan CSS kustom tema #13295C.
     */
    public function test_12_layouts_render_logo_and_custom_theme_stylesheet(): void
    {
        $loginRes = $this->get('/login');
        $loginRes->assertOk();
        $loginRes->assertSee('logoBrewIsland.png');
        $loginRes->assertSee('custom.css');

        $this->actingAs($this->admin);
        $appRes = $this->get(route('admin.dashboard'));
        $appRes->assertOk();
        $appRes->assertSee('logoBrewIsland.png');
        $appRes->assertSee('custom.css');
        $appRes->assertSee(route('user.profile'));

        // Verify custom.css contains the primary color #13295C
        $cssPath = public_path('!template-stisla/dist/assets/css/custom.css');
        $this->assertFileExists($cssPath);
        $this->assertStringContainsString('#13295C', file_get_contents($cssPath));
    }
}

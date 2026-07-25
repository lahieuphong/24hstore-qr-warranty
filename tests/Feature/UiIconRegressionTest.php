<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class UiIconRegressionTest extends TestCase
{
    use RefreshDatabase;

    private const LEGACY_UI_GLYPHS = '/[↗⌕↕✕↻×☰☷★☆⚙✓✔✗✘⚠ℹ👁🔍🔄➕➖]/u';

    public function test_admin_pages_render_lucide_blade_icons(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $menuIcon = trim(Blade::render('<x-lucide-menu class="size-5" aria-hidden="true" />'));
        $plusIcon = trim(Blade::render('<x-lucide-plus class="size-4" aria-hidden="true" />'));

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee($menuIcon, false)
            ->assertSee($plusIcon, false);
    }

    public function test_application_views_only_use_lucide_components_for_inline_ui_icons(): void
    {
        $violations = [];
        $hasLucideComponent = false;

        foreach ($this->uiSourceFiles() as $path => $source) {
            $hasLucideComponent = $hasLucideComponent || str_contains($source, '<x-lucide-');

            if (preg_match('/<\s*svg\b/i', $source) === 1) {
                $violations[] = $path.': contains an inline <svg> element';
            }

            if (preg_match(self::LEGACY_UI_GLYPHS, $source, $matches) === 1) {
                $violations[] = $path.': contains legacy UI glyph '.$matches[0];
            }

            if (preg_match('/>\s*\+\s*</u', $source) === 1) {
                $violations[] = $path.': contains a standalone + UI glyph';
            }
        }

        $this->assertTrue($hasLucideComponent, 'No Lucide Blade component was found in application views.');
        $this->assertSame([], $violations, "Application Blade icon regressions:\n".implode("\n", $violations));
    }

    public function test_application_views_do_not_use_native_select_controls(): void
    {
        $violations = [];

        foreach ($this->uiSourceFiles() as $path => $source) {
            if (! str_ends_with($path, '.blade.php')) {
                continue;
            }

            if (preg_match('/<\s*select\b/i', $source) === 1) {
                $violations[] = $path;
            }
        }

        $this->assertSame(
            [],
            $violations,
            "Native select controls found in application views:\n".implode("\n", $violations),
        );
    }

    public function test_custom_dropdown_uses_one_entangled_value_path(): void
    {
        $source = File::get(resource_path('views/components/admin-select.blade.php'));

        $this->assertStringContainsString('selected: $wire.$entangle', $source);
        $this->assertStringNotContainsString('$wire.$set(', $source);
    }

    /** @return array<string, string> */
    private function uiSourceFiles(): array
    {
        $sources = [];

        foreach (File::allFiles(resource_path()) as $file) {
            if (! preg_match('/\.(?:blade\.php|css|js)$/', $file->getFilename())) {
                continue;
            }

            $sources[$file->getRelativePathname()] = File::get($file->getPathname());
        }

        return $sources;
    }
}

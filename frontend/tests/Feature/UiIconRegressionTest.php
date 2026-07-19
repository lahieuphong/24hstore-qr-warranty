<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UiIconRegressionTest extends TestCase
{
    private const LEGACY_UI_GLYPHS = '/[↗⌕↕✕↻×☰☷★☆⚙✓✔✗✘⚠ℹ👁🔍🔄➕➖]/u';

    public function test_public_pages_render_lucide_blade_icons(): void
    {
        $token = '33333333-3333-4333-8333-333333333333';
        Http::fake(['http://backend.test/*' => Http::response([], 404)]);

        $qrIcon = trim(Blade::render('<x-lucide-qr-code class="size-6" aria-hidden="true" />'));
        $notFoundIcon = trim(Blade::render('<x-lucide-search-x class="size-8" aria-hidden="true" />'));

        $this->get('/bao-hanh/'.$token)
            ->assertOk()
            ->assertSee($qrIcon, false)
            ->assertSee($notFoundIcon, false);
    }

    public function test_frontend_blade_views_only_use_lucide_components_for_inline_ui_icons(): void
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

        $this->assertTrue($hasLucideComponent, 'No Lucide Blade component was found in frontend views.');
        $this->assertSame([], $violations, "Frontend Blade icon regressions:\n".implode("\n", $violations));
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

<?php
/**
 * PortalAssets Unit Tests: inline config scripts cannot be closed or broken out of by data values.
 */

declare(strict_types=1);

namespace Readscope\Tests\Unit;

use Readscope\Support\PortalAssets;
use Readscope\Tests\TestCase;

class PortalAssetsTest extends TestCase
{
    private const HOSTILE = "</script><img src=x onerror=alert(1)> \"quoted\" 'single' a&b";

    public function test_hostile_values_never_appear_raw_in_inline_script(): void
    {
        $script = PortalAssets::inlineConfigScript('readscopeLookupConfig', [
            'name'   => self::HOSTILE,
            'nested' => ['x' => self::HOSTILE],
        ]);

        $this->assertStringStartsWith('var readscopeLookupConfig = {', $script);
        $this->assertStringNotContainsString('</script>', strtolower($script));
        $this->assertStringNotContainsString('<img', $script);
        $this->assertStringNotContainsString('<', $script);
        $this->assertStringNotContainsString('>', $script);
        $this->assertStringNotContainsString('&', $script);
        $this->assertStringNotContainsString("'", $script);

        // Only the JSON string delimiters are raw double quotes: 2 per key/value pair, none from the data.
        $json    = substr($script, strlen('var readscopeLookupConfig = '), -1);
        $decoded = json_decode($json, true);
        $this->assertSame(self::HOSTILE, $decoded['name'], 'Escaped output must round-trip to the original value.');
        $this->assertSame(self::HOSTILE, $decoded['nested']['x']);
    }

    public function test_invalid_global_name_emits_nothing(): void
    {
        foreach (['', '1abc', 'a-b', 'a b', 'x;alert(1)', "x\nalert(1)", 'x=1;//'] as $bad) {
            $this->assertSame('', PortalAssets::inlineConfigScript($bad, ['a' => 1]), "Name '{$bad}' must be rejected.");
        }
        $this->assertTrue('' !== PortalAssets::inlineConfigScript('$cfg_1', ['a' => 1]));
    }

    public function test_unencodable_data_falls_back_to_empty_object(): void
    {
        $script = PortalAssets::inlineConfigScript('readscopeWizardConfig', ['bad' => "\xB1\x31"]);
        $this->assertSame('var readscopeWizardConfig = {};', $script);
    }

    public function test_enqueue_hands_escaped_json_to_wp_add_inline_script(): void
    {
        $GLOBALS['readscope_test_assets']['inline'] = [];
        PortalAssets::enqueue('lookup', ['msg' => self::HOSTILE]);

        $inline = $GLOBALS['readscope_test_assets']['inline']['readscope-lookup'][0] ?? '';
        $this->assertTrue('' !== $inline);
        $this->assertStringNotContainsString('</script>', strtolower($inline));
        $this->assertStringNotContainsString('<img', $inline);
    }
}

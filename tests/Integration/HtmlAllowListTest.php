<?php
/**
 * The allow-list used by Html::print() keeps every tag the booking panel and search markup rely on.
 */

declare(strict_types=1);

namespace Readscope\Tests\Integration;

use Readscope\Support\Html;
use Readscope\Tests\TestCase;

class HtmlAllowListTest extends TestCase
{
    public function test_payment_method_fieldset_and_legend_are_allowed(): void
    {
        $allowed = Html::allowed();

        $this->assertTrue(isset($allowed['fieldset']), 'fieldset must survive Html::print');
        $this->assertTrue(isset($allowed['legend']), 'legend must survive Html::print');
    }

    public function test_every_tag_in_the_booking_panel_template_is_allowed(): void
    {
        $allowed = Html::allowed();
        $source  = (string) file_get_contents(dirname(__DIR__, 2) . '/templates/booking-panel.php');

        preg_match_all('/<([a-z][a-z0-9]*)[\s>\/]/i', $source, $m);
        $missing = [];
        foreach (array_unique(array_map('strtolower', $m[1])) as $tag) {
            if (in_array($tag, ['php', 'script', 'style', 'svg', 'path', 'circle', 'rect', 'g', 'line', 'polyline'], true)) {
                continue;
            }
            if (!isset($allowed[$tag])) {
                $missing[] = $tag;
            }
        }

        $this->assertEquals([], $missing, 'tags missing from Html::allowed(): ' . implode(', ', $missing));
    }
}

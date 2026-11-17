<?php
/**
 * Email output escaping: the body is filtered with the email allowlist at output time.
 */

declare(strict_types=1);

namespace Readscope\Tests\Unit;

use Readscope\Services\EmailService;
use Readscope\Support\Html;
use Readscope\Tests\TestCase;

class EmailHtmlTest extends TestCase
{
    private const BUTTON = '<a style="color:#fff;padding:12px;border-radius:6px" href="https://example.com">Open</a>';
    private const TABLE  = '<table cellpadding="8" cellspacing="0" border="0" width="100%"><tr><td align="center" valign="top" bgcolor="#f8fafc" colspan="2">Cell</td></tr></table>';

    public function test_scripts_and_event_handlers_are_stripped(): void
    {
        $dirty = '<p onclick="steal()">Hello</p><script>alert(1)</script>'
            . '<img src="x.png" onerror="alert(1)" alt="pic">'
            . '<a href="javascript:alert(1)">bad</a><iframe src="https://evil.example"></iframe>'
            . '<form action="https://evil.example"><input name="q"></form>';

        $clean = Html::emailBody($dirty);

        $this->assertStringNotContainsString('<script', strtolower($clean));
        $this->assertStringNotContainsString('onclick', strtolower($clean));
        $this->assertStringNotContainsString('onerror', strtolower($clean));
        $this->assertStringNotContainsString('javascript:', strtolower($clean));
        $this->assertStringNotContainsString('<iframe', strtolower($clean));
        $this->assertStringNotContainsString('<form', strtolower($clean));
        $this->assertStringNotContainsString('<input', strtolower($clean));
        $this->assertStringContainsString('Hello', $clean);
        $this->assertStringContainsString('alt="pic"', $clean);
    }

    public function test_email_button_styles_and_table_attributes_survive(): void
    {
        $clean = Html::emailBody(self::BUTTON . self::TABLE);

        $this->assertStringContainsString('href="https://example.com"', $clean);
        foreach (['color:#fff', 'padding:12px', 'border-radius:6px'] as $declaration) {
            $this->assertStringContainsString($declaration, str_replace(' ', '', $clean));
        }
        foreach (['cellpadding="8"', 'cellspacing="0"', 'border="0"', 'width="100%"', 'align="center"', 'valign="top"', 'bgcolor="#f8fafc"', 'colspan="2"'] as $attribute) {
            $this->assertStringContainsString($attribute, $clean);
        }
    }

    public function test_extra_css_properties_do_not_leak_outside_the_email_filter(): void
    {
        Html::emailBody(self::BUTTON);

        $after = apply_filters('safe_style_css', ['color']);
        $this->assertSame(['color'], $after, 'The safe_style_css filter must be removed after the email body is filtered.');
    }

    public function test_base_layout_filters_the_body_at_output(): void
    {
        $service = new EmailService();
        $html = $service->renderTemplate('base-layout.php', [
            'emailHeading'     => 'Heading',
            'emailBodyContent' => '<p style="line-height:1.5">Body</p><script>alert(1)</script>' . self::BUTTON,
            'siteName'         => 'Site',
            'primaryColor'     => '#0d9488',
            'isTest'           => false,
        ]);

        $content = substr($html, (int) strpos($html, 'readscope-email-content'));
        $this->assertStringNotContainsString('<script', strtolower($content));
        $this->assertStringContainsString('Body', $content);
        $this->assertStringContainsString('border-radius:6px', str_replace(' ', '', $content));
        $this->assertStringContainsString('line-height:1.5', str_replace(' ', '', $content));
        $this->assertSame(['color'], apply_filters('safe_style_css', ['color']));
    }

    public function test_test_email_only_accepts_real_email_types(): void
    {
        $service = new EmailService();

        foreach (['booking_confirmation', 'base_layout', '../../wp-config', 'nope', ''] as $type) {
            $this->assertFalse($service->sendTestEmail('qa@example.com', $type), "Type '{$type}' must be rejected.");
        }

        foreach (EmailService::EMAIL_TYPES as $type) {
            $file = dirname(__DIR__, 2) . '/templates/emails/' . str_replace('_', '-', $type) . '.php';
            $this->assertTrue(is_file($file), "Template for '{$type}' must exist.");
        }
        $this->assertTrue($service->sendTestEmail('qa@example.com', 'customer_verify_email'));
    }
}

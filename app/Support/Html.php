<?php

declare(strict_types=1);

namespace Readscope\Support;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Prints markup that Readscope Booking builds from its own templates (search bar, listing cards).
 *
 * The markup is run through wp_kses() with a list that includes the form controls these templates use,
 * which wp_kses_post() would strip. Cards set `--trv-*` CSS custom properties in `style`; those are
 * kept, everything else in `style` follows WordPress' own rules.
 *
 * @package Readscope\Support
 */
final class Html
{
    /**
     * Print markup after filtering it.
     *
     * @param string $html Markup built by the plugin.
     * @return void
     */
    public static function print(string $html): void
    {
        add_filter('safecss_filter_attr_allow_css', [self::class, 'allowCustomProperty'], 10, 2);
        echo wp_kses($html, self::allowed());
        remove_filter('safecss_filter_attr_allow_css', [self::class, 'allowCustomProperty'], 10);
    }

    /**
     * Let `--trv-*` custom properties with a plain value through the style attribute.
     *
     * @param bool   $allow         Whether WordPress already allows the declaration.
     * @param string $cssTestString The declaration, for example `--trv-grid-cols: 3`.
     * @return bool
     */
    public static function allowCustomProperty($allow, $cssTestString): bool
    {
        if ($allow) {
            return true;
        }

        return (bool) preg_match('/^--trv-[a-z0-9-]+\s*:\s*[#a-zA-Z0-9.,%\s()-]+$/', (string) $cssTestString);
    }

    /**
     * Allowed tags and attributes.
     *
     * @return array<string, array<string, bool>>
     */
    public static function allowed(): array
    {
        $global = [
            'class'            => true,
            'id'               => true,
            'style'            => true,
            'title'            => true,
            'role'             => true,
            'lang'             => true,
            'dir'              => true,
            'hidden'           => true,
            'tabindex'         => true,
            'data-*'           => true,
            'aria-label'       => true,
            'aria-labelledby'  => true,
            'aria-describedby' => true,
            'aria-hidden'      => true,
            'aria-expanded'    => true,
            'aria-haspopup'    => true,
            'aria-controls'    => true,
            'aria-live'        => true,
            'aria-current'     => true,
            'aria-selected'    => true,
            'aria-pressed'     => true,
            'aria-disabled'    => true,
            'aria-required'    => true,
            'aria-invalid'     => true,
            'aria-atomic'      => true,
        ];

        $tags = [];
        foreach (['div', 'span', 'section', 'nav', 'main', 'article', 'header', 'footer', 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'ol', 'li', 'strong', 'b', 'em', 'i', 'small', 'del', 'ins', 'br', 'figure', 'figcaption', 'time'] as $tag) {
            $tags[$tag] = $global;
        }

        $tags['time']     += ['datetime' => true];
        $tags['a']         = $global + ['href' => true, 'rel' => true, 'target' => true, 'download' => true];
        $tags['img']       = $global + ['src' => true, 'srcset' => true, 'sizes' => true, 'alt' => true, 'width' => true, 'height' => true, 'loading' => true, 'decoding' => true];
        $tags['form']      = $global + ['action' => true, 'method' => true, 'role' => true, 'novalidate' => true, 'autocomplete' => true];
        $tags['label']     = $global + ['for' => true];
        $tags['input']     = $global + [
            'type' => true, 'name' => true, 'value' => true, 'placeholder' => true, 'min' => true, 'max' => true,
            'step' => true, 'checked' => true, 'disabled' => true, 'required' => true, 'readonly' => true,
            'autocomplete' => true, 'inputmode' => true, 'maxlength' => true, 'minlength' => true, 'pattern' => true, 'list' => true,
        ];
        $tags['select']    = $global + ['name' => true, 'multiple' => true, 'required' => true, 'disabled' => true, 'autocomplete' => true];
        $tags['option']    = $global + ['value' => true, 'selected' => true, 'disabled' => true];
        $tags['optgroup']  = $global + ['label' => true, 'disabled' => true];
        $tags['button']    = $global + ['type' => true, 'name' => true, 'value' => true, 'disabled' => true];
        $tags['textarea']  = $global + ['name' => true, 'rows' => true, 'cols' => true, 'placeholder' => true, 'required' => true, 'disabled' => true, 'maxlength' => true];
        $tags['datalist']  = $global;
        $tags['fieldset']  = $global + ['disabled' => true, 'name' => true, 'form' => true];
        $tags['legend']    = $global;

        return $tags;
    }

    /**
     * wp_kses() allowlist for the body of an HTML email.
     *
     * Mail clients need inline `style` attributes and old-school table attributes, which wp_kses_post()
     * would partly strip. Scripts, event handlers, forms and embeds are not in the list.
     *
     * @return array<string, array<string, bool>>
     */
    public static function emailAllowedHtml(): array
    {
        $global = [
            'style'  => true,
            'class'  => true,
            'id'     => true,
            'title'  => true,
            'role'   => true,
            'dir'    => true,
            'lang'   => true,
            'align'  => true,
            'width'  => true,
            'height' => true,
        ];
        $cell = [
            'valign'  => true,
            'bgcolor' => true,
            'colspan' => true,
            'rowspan' => true,
            'scope'   => true,
            'nowrap'  => true,
        ];

        $tags = [];
        foreach (['p', 'div', 'span', 'strong', 'b', 'em', 'i', 'u', 'br', 'hr', 'h1', 'h2', 'h3', 'h4', 'ul', 'ol', 'li', 'blockquote', 'small', 'center', 'thead', 'tbody', 'tfoot'] as $tag) {
            $tags[$tag] = $global;
        }

        $tags['a']     = $global + ['href' => true, 'target' => true, 'rel' => true];
        $tags['img']   = $global + ['src' => true, 'alt' => true, 'border' => true];
        $tags['table'] = $global + ['cellpadding' => true, 'cellspacing' => true, 'border' => true, 'bgcolor' => true, 'valign' => true];
        $tags['tr']    = $global + ['valign' => true, 'bgcolor' => true];
        $tags['th']    = $global + $cell;
        $tags['td']    = $global + $cell;

        return $tags;
    }

    /**
     * Inline CSS properties email templates use that WordPress' default `safe_style_css` list leaves out.
     *
     * Hooked on `safe_style_css` only while an email body is filtered, then removed again.
     *
     * @param mixed $properties Allowed CSS property names.
     * @return array<int, string>
     */
    public static function emailSafeCss($properties): array
    {
        $properties = is_array($properties) ? $properties : [];

        return array_values(array_unique(array_merge($properties, [
            'display',
            'border-radius',
            'line-height',
            'text-decoration',
            'text-transform',
            'letter-spacing',
            'max-width',
            'min-width',
            'border-collapse',
            'border-spacing',
            'vertical-align',
            'word-break',
            'word-wrap',
            'overflow-wrap',
            'white-space',
            'box-shadow',
            'opacity',
            'table-layout',
        ])));
    }

    /**
     * Filter an email body for output: wp_kses() with the email allowlist and the email CSS properties.
     *
     * @param string $html Email body built from the plugin's email templates.
     * @return string
     */
    public static function emailBody(string $html): string
    {
        add_filter('safe_style_css', [self::class, 'emailSafeCss']);
        $clean = wp_kses($html, self::emailAllowedHtml());
        remove_filter('safe_style_css', [self::class, 'emailSafeCss']);

        return $clean;
    }
}

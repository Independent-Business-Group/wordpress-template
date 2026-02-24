<?php
/**
 * @var $module
 * @var $settings
 * @var $id
 */

global $wp_embed;

$settings = apply_filters('labb_heading_' . esc_attr($id) . '_settings', $settings);

list($animate_class, $animation_attr) = labb_get_animation_atts($settings->text_animation);

$output = '<div class="labb-heading labb-' . esc_attr($settings->style) . ' labb-align' . esc_attr($settings->align) . ' ' . esc_attr($animate_class) . '" ' . esc_attr($animation_attr) . '>';

if ($settings->style == 'style2' && !empty($settings->subtitle)):

    $output .= '<div class="labb-subtitle">' . esc_html($settings->subtitle) . '</div>';

endif;

$output .= '<' . esc_html($settings->title_tag) . ' class="labb-title">' . wp_kses_post($settings->heading) . '</' . esc_html($settings->title_tag) . '>';

if ($settings->style != 'style3' && !empty($settings->short_text)):

    $output .= '<p class="labb-text">' . wp_kses_post(wpautop($wp_embed->autoembed(do_shortcode($settings->short_text)))) . '</p>';

endif;

$output .= '</div><!-- .labb-heading -->';

echo apply_filters('labb_heading_output', $output, $settings);
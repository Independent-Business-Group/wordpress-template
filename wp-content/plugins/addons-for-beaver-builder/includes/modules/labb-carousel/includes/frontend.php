<?php
/**
 * @var $module
 * @var $settings
 * @var $id
 */

global $wp_embed;

$settings = apply_filters('labb_carousel_' . esc_attr($id) . '_settings', $settings);

$elements = $settings->elements;

$carousel_settings = [
    'arrows' => ('yes' === $settings->arrows),
    'dots' => ('yes' === $settings->dots),
    'autoplay' => ('yes' === $settings->autoplay),
    'autoplay_speed' => absint($settings->autoplay_speed),
    'animation_speed' => absint($settings->animation_speed),
    'pause_on_hover' => ('yes' === $settings->pause_on_hover),
];

$responsive_settings = [
    'display_columns' => $settings->display_columns,
    'scroll_columns' => $settings->scroll_columns,
    'gutter' => esc_attr($settings->gutter),
    'tablet_width' => $settings->tablet_width,
    'tablet_display_columns' => $settings->tablet_display_columns,
    'tablet_scroll_columns' => $settings->tablet_scroll_columns,
    'tablet_gutter' => esc_attr($settings->tablet_gutter),
    'mobile_width' => $settings->mobile_width,
    'mobile_display_columns' => $settings->mobile_display_columns,
    'mobile_scroll_columns' => $settings->mobile_scroll_columns,
    'mobile_gutter' => esc_attr($settings->mobile_gutter),

];

$carousel_settings = array_merge($carousel_settings, $responsive_settings);

if (!empty($elements)) :

    $output = '<div id="labb-carousel-' . esc_attr($id) . '" class="labb-carousel labb-container" data-settings=\'' . esc_attr(wp_json_encode($carousel_settings)) . '\'>';

    foreach ($elements as $element) :

        if (!is_object($element))
            continue;

        $child_output = '<div class="labb-carousel-item">';

        $child_output .= wpautop($wp_embed->autoembed(do_shortcode(wp_kses_post($element->element_content))));

        $child_output .= '</div><!-- .labb-carousel-item -->';

        $output .= apply_filters('labb_carousel_item_output', $child_output, $element, $settings);

    endforeach;

    $output .= '</div><!-- .labb-carousel -->';

    echo apply_filters('labb_carousel_output', $output, $settings);

endif;

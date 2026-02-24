<?php
/**
 * @var $module
 * @var $settings
 * @var $id
 */

global $wp_embed;

$settings = apply_filters('labb_services_' . esc_attr($id) . '_settings', $settings);

$output = '<div class="labb-services labb-' . esc_attr($settings->style) . ' labb-grid-container ' . esc_attr(labb_get_grid_classes($settings)) . '">';

foreach ($settings->services as $service):

    if (!is_object($service))
        continue;

    list($animate_class, $animation_attr) = labb_get_animation_atts($service->service_animation);

    $child_output = '<div class="labb-grid-item labb-service-wrapper">';

    $child_output .= '<div class="labb-service ' . esc_attr($animate_class) . '" ' . esc_attr($animation_attr) . '>';

    if ($service->icon_type == 'icon_image') :

        if (!empty($service->icon_image)):

            $icon_image = absint($service->icon_image);

            $child_output .= '<div class="labb-image-wrapper">';

            $image_html = wp_get_attachment_image($icon_image, 'full', false, array('class' => 'labb-image full'));

            $child_output .= $image_html;

            $child_output .= '</div>';

        endif;

    elseif ($service->icon_type == 'icon') :

        $child_output .= '<div class="labb-icon-wrapper">';

        $child_output .= '<span class="' . esc_attr($service->font_icon) . '"></span>';

        $child_output .= '</div>';

    endif;

    $child_output .= '<div class="labb-service-text">';

    $child_output .= '<' . esc_html($settings->title_tag) . ' class="labb-title">' . esc_html($service->service_title) . '</' . esc_html($settings->title_tag) . '>';

    $child_output .= '<div class="labb-service-details">' . wpautop($wp_embed->autoembed(do_shortcode(wp_kses_post($service->service_excerpt)))) . '</div>';

    $child_output .= '</div><!-- .labb-service-text -->';

    $child_output .= '</div><!-- .labb-service -->';

    $child_output .= '</div><!-- .labb-service-wrapper -->';

    $output .= apply_filters('labb_service_item_output', $child_output, $service, $settings);

endforeach;

$output .= '</div><!-- .labb-services -->';

$output .= '<div class="labb-clear"></div>';

echo apply_filters('labb_services_output', $output, $settings);
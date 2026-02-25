<?php
/**
 * @var $module
 * @var $settings
 * @var $id
 */

$settings = apply_filters('labb_pricing_table_' . esc_attr($id) . '_settings', $settings);

if (empty($settings->pricing_plans))
    return;

$output = '<div class="labb-pricing-table labb-grid-container ' . esc_attr(labb_get_grid_classes($settings)) . '">';

foreach ($settings->pricing_plans as $pricing_plan) :

    if (!is_object($pricing_plan))
        continue;

    $pricing_title = esc_html($pricing_plan->pricing_title);
    $tagline = esc_html($pricing_plan->tagline);
    $price_tag = htmlspecialchars_decode(wp_kses_post($pricing_plan->price_tag));
    $pricing_img = absint($pricing_plan->pricing_image);
    $pricing_url = (empty($pricing_plan->pricing_url)) ? '#' : esc_url($pricing_plan->pricing_url);
    $pricing_button_text = esc_html($pricing_plan->button_text);
    $new_window = ('yes' === $pricing_plan->new_window);
    $highlight = ('yes' === $pricing_plan->highlight);

    $price_tag = (empty($price_tag)) ? '' : $price_tag;

    list($animate_class, $animation_attr) = labb_get_animation_atts($pricing_plan->pricing_animation);

    $child_output = '<div class="labb-grid-item labb-pricing-plan ' . ($highlight ? ' labb-highlight' : '') . ' ' . esc_attr($animate_class) . '" ' . esc_attr($animation_attr) . '>';

    $child_output .= '<div class="labb-top-header">';

    if (!empty($tagline))
        $child_output .= '<p class="labb-tagline center">' . $tagline . '</p>';

    $child_output .= '<' . esc_html($settings->plan_name_tag) . ' class="labb-plan-name labb-center">' . $pricing_title . '</' . esc_html($settings->plan_name_tag) . '>';

    if (!empty($pricing_img)) :
        $child_output .= wp_get_attachment_image($pricing_img, 'full', false, array('class' => 'labb-image full', 'alt' => $pricing_title));
    endif;

    $child_output .= '</div>';

    $child_output .= '<' . esc_html($settings->plan_price_tag) . ' class="labb-plan-price labb-plan-header labb-center">';

    $child_output .= '<span class="labb-text">' . wp_kses_post($price_tag) . '</span>';

    $child_output .= '</' . esc_html($settings->plan_price_tag) . '>';

    $child_output .= '<div class="labb-plan-details">';

    $child_output .= do_shortcode(wp_kses_post($pricing_plan->pricing_content));

    $child_output .= '</div><!-- .labb-plan-details -->';

    $child_output .= '<div class="labb-purchase">';

    $child_output .= '<a class="labb-button default" href="' . esc_url($pricing_url) . '"' . ($new_window ? ' target="_blank"' : '') . '>' . $pricing_button_text . '</a>';

    $child_output .= '</div>';

    $child_output .= '</div><!-- .labb-pricing-plan -->';

    $output .= apply_filters('labb_pricing_plan_output', $child_output, $pricing_plan, $settings);

endforeach;

$output .= '</div><!-- .labb-pricing-table -->';

$output .= '<div class="labb-clear"></div>';

echo apply_filters('labb_pricing_table_output', $output, $settings);
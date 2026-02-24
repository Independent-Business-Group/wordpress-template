<?php
/**
 * @var $module
 * @var $settings
 * @var $id
 */

$settings = apply_filters('labb_posts_grid_' . esc_attr($id) . '_settings', $settings);

// Use the processed post selector query to find posts.
$loop = FLBuilderLoop::query($settings);

// Loop through the posts and do something with them.
if ($loop->have_posts()) :

    $chosen_taxonomy = esc_attr($settings->taxonomy_filter);

    $taxonomies[] = $chosen_taxonomy;

    $target = ($settings->post_link_new_window == 'yes') ? ' target="_blank"' : '';

    $output = '<div class="labb-portfolio-wrap labb-gapless-grid">';

    if (!empty($settings->heading) || $settings->filterable == 'yes'):

        $header_class = (trim($settings->heading) === '') ? ' labb-no-heading' : '';

        $grid_header = '<div class="labb-portfolio-header ' . esc_attr($header_class) . '">';

        if (!empty($settings->heading)) :
            $grid_header .= '<' . esc_html($settings->heading_tag) . ' class="labb-heading">' . wp_kses_post($settings->heading) . '</' . esc_html($settings->heading_tag) . '>';
        endif;

        if ($settings->filterable == 'yes')
            $grid_header .= labb_get_taxonomy_terms_filter($loop->query, $taxonomies);

        $grid_header .= '</div>';

        $output .= apply_filters('labb_posts_grid_header', $grid_header, $settings);

    endif;

    $output .= '<div id="labb-portfolio-' . esc_attr(uniqid()) . '" class="labb-portfolio js-isotope labb-' . esc_attr($settings->layout_mode) . ' labb-grid-container ' . esc_attr(labb_get_grid_classes($settings)) . '" data-isotope-options=\'{ "itemSelector": ".labb-portfolio-item", "layoutMode": "' . esc_attr($settings->layout_mode) . '"}\'>';

    $current_page = get_queried_object_id();

    while ($loop->have_posts()) : $loop->the_post();

        $post_id = get_the_ID();

        if ($post_id === $current_page)
            continue; // skip current page since we can run into infinite loop when users choose All option in build query

        $style = '';
        foreach ($taxonomies as $taxonomy) {
            $terms = get_the_terms($post_id, $taxonomy);
            if (!empty($terms) && !is_wp_error($terms)) {
                foreach ($terms as $term) {
                    $style .= ' term-' . esc_attr($term->term_id);
                }
            }
        }

        $entry_output = '<div data-id="id-' . esc_attr($post_id) . '" class="labb-grid-item labb-portfolio-item ' . $style . '">';

        $entry_output .= '<article id="post-' . esc_attr($post_id) . '" class="' . esc_attr(join(' ', get_post_class('', $post_id))) . '">';

        if ($thumbnail_exists = has_post_thumbnail()):

            $entry_image = '<div class="labb-project-image">';

            $thumbnail_html = $module->get_img(get_the_ID());

            $entry_image .= apply_filters('labb_posts_grid_thumbnail_html', $thumbnail_html, $post_id, $settings);

            if (($settings->display_title_on_thumbnail == 'yes') || ($settings->display_taxonomy_on_thumbnail == 'yes')):

                $image_info = '<div class="labb-image-info">';

                $image_info .= '<div class="labb-entry-info">';

                if ($settings->display_title_on_thumbnail == 'yes'):

                    $image_info .= '<' . esc_html($settings->thumbnail_info_title_tag) . ' class="labb-post-title"><a href="' . esc_url(get_permalink()) . '" title="' . esc_attr(get_the_title()) . '" rel="bookmark"' . $target . '>' . esc_html(get_the_title()) . '</a></' . esc_html($settings->thumbnail_info_title_tag) . '>';

                endif;

                if ($settings->display_taxonomy_on_thumbnail == 'yes'):

                    $image_info .= labb_get_info_for_taxonomies($taxonomies);

                endif;

                $image_info .= '</div>';

                $image_info .= '</div><!-- .labb-image-info -->';

                $entry_image .= apply_filters('labb_posts_grid_image_info', $image_info, $post_id, $settings);

            endif;

            $entry_image .= '</div>';

            $entry_output .= apply_filters('labb_posts_grid_entry_image', $entry_image, $post_id, $settings);

        endif;

        if (($settings->display_title == 'yes') || ($settings->display_summary == 'yes')) :

            $entry_text = '<div class="labb-entry-text-wrap ' . ($thumbnail_exists ? '' : ' nothumbnail') . '">';

            if ($settings->display_title == 'yes') :

                $entry_title = '<' . esc_html($settings->entry_title_tag) . ' class="entry-title"><a href="' . esc_url(get_permalink()) . '" title="' . esc_attr(get_the_title()) . '" rel="bookmark"' . $target . '>' . esc_html(get_the_title()) . '</a></' . esc_html($settings->entry_title_tag) . '>';

                $entry_text .= apply_filters('labb_posts_grid_entry_title', $entry_title, $post_id, $settings);

            endif;

            if (($settings->display_post_date == 'yes') || ($settings->display_author == 'yes') || ($settings->display_taxonomy == 'yes')) :

                $entry_meta = '<div class="labb-entry-meta">';

                if ($settings->display_author == 'yes'):

                    $entry_meta .= labb_entry_author();

                endif;

                if ($settings->display_post_date == 'yes'):

                    $entry_meta .= labb_entry_published();

                endif;

                if ($settings->display_taxonomy == 'yes'):

                    $entry_meta .= labb_get_info_for_taxonomies($taxonomies);

                endif;

                $entry_meta .= '</div>';

                $entry_text .= apply_filters('labb_posts_grid_entry_meta', $entry_meta, $post_id, $settings);

            endif;

            if ($settings->display_summary == 'yes') :

                $excerpt = '<div class="entry-summary">';

                $excerpt .= wp_kses_post(get_the_excerpt());

                $excerpt .= '</div>';

                $entry_text .= apply_filters('labb_posts_grid_entry_excerpt', $excerpt, $post_id, $settings);

            endif;

            if ($settings->display_read_more == 'yes') :

                $read_more_text = esc_html($settings->read_more_text);

                $read_more = '<div class="labb-read-more">';

                $read_more .= '<a href="' . esc_url(get_the_permalink()) . '"' . $target . '>' . $read_more_text . '</a>';

                $read_more .= '</div>';

                $entry_text .= apply_filters('labb_posts_grid_read_more_link', $read_more, $post_id, $settings);

            endif;

            $entry_text .= '</div>';

            $entry_output .= apply_filters('labb_posts_grid_entry_text', $entry_text, $post_id, $settings);

        endif;

        $entry_output .= '</article><!-- .hentry -->';

        $entry_output .= '</div>';

        $output .= apply_filters('labb_posts_grid_entry_output', $entry_output, $post_id, $settings);

    endwhile;

    wp_reset_postdata();

    $output .= '</div><!-- .labb-portfolio -->';

    $output .= '</div><!-- .labb-portfolio-wrap -->';

    echo apply_filters('labb_posts_grid_output', $output, $settings);

endif;
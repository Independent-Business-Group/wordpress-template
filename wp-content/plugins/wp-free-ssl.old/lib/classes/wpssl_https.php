<?php


if( ! class_exists( 'WPSSL_HTTPS' ) ) {
    
    class WPSSL_HTTPS {

        public static function init() {
            
            if(get_option( 'wppssl_ssl_activated' ) == '1' ) {
                /* 301 redirection */
                add_action('wp_loaded', __CLASS__ . '::force_ssl', 20);

                /* Fix mixed content */
                if (is_admin()) {
                    add_action("admin_init", __CLASS__ . "::start_buffer", 100);
                }
                else {
                    add_action("init", __CLASS__ . "::start_buffer");
                }

                add_action("shutdown", __CLASS__ . "::end_buffer", 999);
            }
        }


        public static function force_ssl() {
            /* Force SSL for javascript */
            add_action('wp_print_scripts', __CLASS__ . '::force_ssl_with_javascript');
            /* Force SSL wordpress redirect */
            add_action('wp', __CLASS__ . '::wpRedirectToHttps', 40, 3);
        }

        public static function force_ssl_with_javascript() {
            $script = '<script>';
            $script .= 'if (document.location.protocol != "https:") {';
            $script .= 'document.location = document.URL.replace(/^http:/i, "https:");';
            $script .= '}';
            $script .= '</script>';

            echo $script;
        }


        public static function wpRedirectToHttps() {
            if ( ! is_ssl() ) {
                $redirect_url = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                wp_redirect($redirect_url, 301);
                exit;
            }
        }


        public static function filter_buffer($buffer) {

            if (substr($buffer, 0, 5) == "<?xml") return $buffer;

            $home = str_replace("https://", "http://", get_option('home'));
            $home_no_www = str_replace("://www.", "://", $home);
            $home_yes_www = str_replace("://", "://www.", $home_no_www);

            /* for the escaped version, we only replace the home_url, not it's www or non www counterpart, as it is most likely not used */
            $escaped_home = str_replace("/", "\/", $home);

            $search_array = array(
                $home_yes_www,
                $home_no_www,
                $escaped_home,
                "src='http://",
                'src="http://',
            );

            $ssl_array = str_replace(array("http://", "http:\/\/"), array("https://", "https:\/\/"), $search_array);
            /* now replace these links*/
            $buffer = str_replace($search_array, $ssl_array, $buffer);

            /* replace all http links except hyperlinks*/
            /* all tags with src attr are already fixed by str_replace*/
            $pattern = array(
                '/url\([\'"]?\K(http:\/\/)(?=[^)]+)/i',
                '/<link [^>]*?href=[\'"]\K(http:\/\/)(?=[^\'"]+)/i',
                '/<meta property="og:image" [^>]*?content=[\'"]\K(http:\/\/)(?=[^\'"]+)/i',
                '/<form [^>]*?action=[\'"]\K(http:\/\/)(?=[^\'"]+)/i',
            );

            $buffer = preg_replace($pattern, 'https://', $buffer);

            /* handle multiple images in srcset */
            $buffer = preg_replace_callback('/<img[^\>]*[^\>\S]+srcset=[\'"]\K((?:[^"\'\s,]+\s*(?:\s+\d+[wx])(?:,\s*)?)+)["\']/', __CLASS__ . '::replace_src_set', $buffer);

            $buffer = str_replace("<body", '<body data-rsssl=1', $buffer);

            return $buffer;
        }

        /**
         * Function to start buffer
         *
         * @since 1.0
         * @static
         */
        public static function start_buffer() {
            ob_start(__CLASS__ . '::filter_buffer');
        }

        /**
         * Function to end buffer
         *
         * @since 1.0
         * @static
         */
        public static function end_buffer() {
            if (ob_get_length()) ob_end_flush();
        }

        /**
         * Function to replace http to https
         *
         * @since 1.0
         * @static
         */
        public static function replace_src_set($matches) {
            return str_replace("http://", "https://", $matches[0]);
        }

    }

    /**
    * Calling init function and activate hooks and filters.
    */
    WPSSL_HTTPS::init();
}
<?php

namespace AgoLab\Maintenance;

defined( 'ABSPATH' ) || exit;

class Frontend {

    public static function init(): void {
        add_action( 'template_redirect', [ __CLASS__, 'maybe_show_maintenance' ] );
        add_action( 'admin_bar_menu', [ __CLASS__, 'admin_bar_indicator' ], 100 );
    }

    public static function maybe_show_maintenance(): void {
        $settings = Plugin::get_settings();

        // Admin preview: shows the page whatever the enabled state, from the link on the settings screen.
        $is_preview = isset( $_GET['agomaintenance_preview'], $_GET['_wpnonce'] )
            && current_user_can( 'manage_options' )
            && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'agomaintenance_preview' );

        if ( ! $is_preview ) {
            if ( empty( $settings['enabled'] ) ) {
                return;
            }

            // Anyone who can work in wp-admin keeps seeing the site.
            if ( is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
                return;
            }

            $whitelist = array_filter( array_map( 'trim', explode( "\n", (string) $settings['ip_whitelist'] ) ) );
            $ip        = self::get_client_ip();
            if ( '' !== $ip && in_array( $ip, $whitelist, true ) ) {
                return;
            }

            if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
                return;
            }

            if ( self::is_login_shortcut() ) {
                return;
            }
        }

        if ( 'maintenance' === $settings['mode'] ) {
            status_header( 503 );
            header( 'Retry-After: 3600' );
        } else {
            status_header( 200 );
        }
        header( 'Content-Type: text/html; charset=utf-8' );
        nocache_headers();
        header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0', true );
        header( 'Pragma: no-cache', true );

        self::register_assets( $settings );
        Template::render( $settings );
        exit;
    }

    /**
     * Registers the page stylesheet and countdown script. The page is standalone
     * (it does not run wp_head), so Template prints them with wp_print_styles()
     * and wp_print_scripts().
     *
     * @param array<string, mixed> $settings
     */
    private static function register_assets( array $settings ): void {
        wp_register_style( 'agomaintenance-page', AGOMAINTENANCE_URL . 'assets/css/maintenance.css', [], AGOMAINTENANCE_VERSION );

        $overlay = max( 0, min( 100, intval( $settings['overlay_opacity'] ) ) ) / 100;
        $css     = ':root{--ago-bg:' . sanitize_hex_color( $settings['bg_color'] ) . ';--ago-text:' . sanitize_hex_color( $settings['text_color'] ) . ';--ago-overlay:' . $overlay . ';}';

        if ( ! empty( $settings['bg_image_url'] ) ) {
            $css .= 'body.ago-has-bg{background-image:url("' . esc_url( $settings['bg_image_url'] ) . '");}';
        }
        wp_add_inline_style( 'agomaintenance-page', $css );

        if ( '' !== self::countdown_target( $settings ) ) {
            wp_register_script( 'agomaintenance-countdown', AGOMAINTENANCE_URL . 'assets/js/countdown.js', [], AGOMAINTENANCE_VERSION, false );
        }
    }

    /**
     * Countdown target as a Unix timestamp in milliseconds, read in the site
     * timezone, or an empty string when there is no valid date.
     *
     * @param array<string, mixed> $settings
     */
    public static function countdown_target( array $settings ): string {
        $value = trim( (string) $settings['countdown_datetime'] );
        if ( '' === $value ) {
            return '';
        }

        try {
            $date = new \DateTimeImmutable( $value, wp_timezone() );
        } catch ( \Exception $e ) {
            return '';
        }

        return (string) ( $date->getTimestamp() * 1000 );
    }

    public static function admin_bar_indicator( \WP_Admin_Bar $bar ): void {
        $settings = Plugin::get_settings();
        if ( empty( $settings['enabled'] ) || ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $label = 'maintenance' === $settings['mode']
            ? __( 'Maintenance ON', 'ago-maintenance' )
            : __( 'Coming Soon ON', 'ago-maintenance' );

        $bar->add_node( [
            'id'    => 'agomaintenance-indicator',
            'title' => '<span style="background:#d63638;color:#fff;padding:2px 8px;border-radius:3px;font-size:12px">' . esc_html( $label ) . '</span>',
            'href'  => admin_url( 'admin.php?page=agomaintenance' ),
        ] );
    }

    /**
     * Whether the request is one of the WordPress shortcuts to the login screen
     * (/admin, /login, /wp-admin, /dashboard), which core redirects later on
     * template_redirect. Only the exact path counts: the query string and
     * longer paths that merely contain those words keep the maintenance page.
     */
    private static function is_login_shortcut(): bool {
        if ( empty( $_SERVER['REQUEST_URI'] ) ) {
            return false;
        }

        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- only the path is kept and it is sanitized below.
        $path = wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH );
        if ( ! is_string( $path ) ) {
            return false;
        }
        $path = sanitize_text_field( rawurldecode( $path ) );

        $home = (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH );
        if ( '' !== $home && 0 === strpos( $path, $home ) ) {
            $path = substr( $path, strlen( $home ) );
        }

        return in_array( strtolower( trim( $path, '/' ) ), [ 'admin', 'login', 'wp-admin', 'dashboard', 'wp-login.php' ], true );
    }

    /**
     * The address the request comes from. Only REMOTE_ADDR is read: the
     * forwarding headers are set by the client and anyone could send one with
     * an address from the list.
     */
    private static function get_client_ip(): string {
        if ( empty( $_SERVER['REMOTE_ADDR'] ) ) {
            return '';
        }

        $ip = filter_var( sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ), FILTER_VALIDATE_IP );

        return false === $ip ? '' : $ip;
    }
}

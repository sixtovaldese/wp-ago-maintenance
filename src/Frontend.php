<?php

namespace AgoLab\Maintenance;

defined( 'ABSPATH' ) || exit;

class Frontend {

    public static function init(): void {
        add_action( 'template_redirect', [ __CLASS__, 'maybe_show_maintenance' ] );
        add_action( 'admin_bar_menu', [ __CLASS__, 'admin_bar_indicator' ], 100 );
    }

    public static function maybe_show_maintenance(): void {
        $settings = get_option( 'agomaintenance_settings', [] );

        // Admin preview mode: show the page regardless of enabled state.
        $is_preview = ! empty( $_GET['agomaintenance_preview'] ) && current_user_can( 'manage_options' );

        if ( ! $is_preview && empty( $settings['enabled'] ) ) {
            return;
        }

        // Skip for any logged-in user with manage_options. Admins NEVER see maintenance.
        if ( ! $is_preview && current_user_can( 'manage_options' ) ) {
            return;
        }
        // Also skip for any logged-in editor/author/etc, anyone who can access wp-admin.
        if ( ! $is_preview && is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
            return;
        }

        // Skip for whitelisted IPs.
        $ip        = self::get_client_ip();
        $whitelist = array_filter( array_map( 'trim', explode( "\n", $settings['ip_whitelist'] ?? '' ) ) );
        if ( in_array( $ip, $whitelist, true ) ) {
            return;
        }

        // Skip for login page, admin, and any URL that looks like an admin/login route.
        if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
            return;
        }
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        if ( preg_match( '#/(wp-admin|wp-login\.php|admin|login)(/|$|\?)#i', $request_uri ) ) {
            return;
        }

        $mode = $settings['mode'] ?? 'maintenance';
        if ( 'maintenance' === $mode ) {
            status_header( 503 );
            header( 'Retry-After: 3600' );
        } else {
            status_header( 200 );
        }
        header( 'Content-Type: text/html; charset=utf-8' );
        nocache_headers();
        header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0', true );
        header( 'Pragma: no-cache', true );

        Template::render( wp_parse_args( $settings, Plugin::DEFAULTS ) );
        exit;
    }

    public static function admin_bar_indicator( \WP_Admin_Bar $bar ): void {
        $settings = get_option( 'agomaintenance_settings', [] );
        if ( empty( $settings['enabled'] ) ) {
            return;
        }

        $mode  = $settings['mode'] ?? 'maintenance';
        $label = 'maintenance' === $mode
            ? __( 'Maintenance ON', 'ago-maintenance' )
            : __( 'Coming Soon ON', 'ago-maintenance' );

        $bar->add_node( [
            'id'    => 'agomaintenance-indicator',
            'title' => '<span style="background:#d63638;color:#fff;padding:2px 8px;border-radius:3px;font-size:12px">' . esc_html( $label ) . '</span>',
            'href'  => admin_url( 'admin.php?page=agomaintenance' ),
        ] );
    }

    private static function get_client_ip(): string {
        foreach ( [ 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR' ] as $key ) {
            if ( ! empty( $_SERVER[ $key ] ) ) {
                $ip = $_SERVER[ $key ];
                if ( str_contains( $ip, ',' ) ) {
                    $ip = trim( explode( ',', $ip )[0] );
                }
                return $ip;
            }
        }
        return '';
    }
}

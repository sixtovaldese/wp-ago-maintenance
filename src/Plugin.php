<?php

namespace AgoLab\Maintenance;

defined( 'ABSPATH' ) || exit;

class Plugin {

    private static ?self $instance = null;

    /** Default settings. */
    public const DEFAULTS = [
        'enabled'            => false,
        'mode'               => 'maintenance',
        'title'              => 'Under Maintenance',
        'message'            => 'We are performing scheduled maintenance. We will be back shortly.',
        'bg_color'           => '#1d2327',
        'text_color'         => '#ffffff',
        'countdown_datetime' => '',
        'ip_whitelist'       => '',
        'logo_url'           => '',
        'bg_image_url'       => '',
        'overlay_opacity'    => 60,
    ];

    public static function instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'init', [ $this, 'load_textdomain' ] );
        add_action( 'admin_menu', [ $this, 'register_admin_menu' ] );
        add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        // Frontend interception + admin bar indicator.
        Frontend::init();
    }

    /* ───── Textdomain ───── */

    public function load_textdomain(): void {
        load_plugin_textdomain( 'ago-maintenance', false, dirname( plugin_basename( AGOMAINTENANCE_FILE ) ) . '/languages' );
    }

    /* ───── Admin menu (smart pattern) ───── */

    public function register_admin_menu(): void {
        if ( empty( $GLOBALS['admin_page_hooks']['agolab-tools'] ) ) {
            add_menu_page(
                __( 'aGo Tools', 'ago-maintenance' ),
                __( 'aGo Tools', 'ago-maintenance' ),
                'manage_options',
                'agolab-tools',
                '__return_null',
                'dashicons-hammer',
                81
            );
        }

        add_submenu_page(
            'agolab-tools',
            __( 'aGo Maintenance', 'ago-maintenance' ),
            __( 'Maintenance', 'ago-maintenance' ),
            'manage_options',
            'agomaintenance',
            [ Admin\Page::class, 'render' ]
        );

        remove_submenu_page( 'agolab-tools', 'agolab-tools' );
    }

    /* ───── REST routes ───── */

    public function register_rest_routes(): void {
        register_rest_route( 'agomaintenance/v1', '/settings', [
            [
                'methods'             => 'GET',
                'callback'            => [ $this, 'handle_get_settings' ],
                'permission_callback' => function () {
                    return current_user_can( 'manage_options' );
                },
            ],
            [
                'methods'             => 'POST',
                'callback'            => [ $this, 'handle_save_settings' ],
                'permission_callback' => function () {
                    return current_user_can( 'manage_options' );
                },
            ],
        ] );
    }

    public function handle_get_settings(): \WP_REST_Response {
        return new \WP_REST_Response( self::get_settings() );
    }

    public function handle_save_settings( \WP_REST_Request $request ): \WP_REST_Response {
        $input    = $request->get_json_params();
        $defaults = self::DEFAULTS;
        $settings = [];

        $settings['enabled']            = ! empty( $input['enabled'] );
        $settings['mode']               = in_array( $input['mode'] ?? '', [ 'maintenance', 'coming_soon' ], true )
                                            ? $input['mode'] : $defaults['mode'];
        $settings['title']              = sanitize_text_field( $input['title'] ?? $defaults['title'] );
        $settings['message']            = sanitize_textarea_field( $input['message'] ?? $defaults['message'] );
        $settings['bg_color']           = sanitize_hex_color( $input['bg_color'] ?? $defaults['bg_color'] ) ?: $defaults['bg_color'];
        $settings['text_color']         = sanitize_hex_color( $input['text_color'] ?? $defaults['text_color'] ) ?: $defaults['text_color'];
        $settings['countdown_datetime'] = sanitize_text_field( $input['countdown_datetime'] ?? '' );
        $settings['ip_whitelist']       = sanitize_textarea_field( $input['ip_whitelist'] ?? '' );
        $settings['logo_url']           = esc_url_raw( $input['logo_url'] ?? '' );
        $settings['bg_image_url']       = esc_url_raw( $input['bg_image_url'] ?? '' );
        $settings['overlay_opacity']    = max( 0, min( 100, intval( $input['overlay_opacity'] ?? 60 ) ) );

        update_option( 'agomaintenance_settings', $settings );

        return new \WP_REST_Response( [ 'saved' => true, 'settings' => $settings ] );
    }

    /* ───── Assets ───── */

    public function enqueue_assets( string $hook ): void {
        if ( ! str_ends_with( $hook, '_page_agomaintenance' ) ) {
            return;
        }

        wp_enqueue_media();

        wp_enqueue_style(
            'agomaintenance-admin',
            AGOMAINTENANCE_URL . 'assets/css/admin.css',
            [],
            AGOMAINTENANCE_VERSION
        );

        wp_enqueue_script(
            'agomaintenance-admin',
            AGOMAINTENANCE_URL . 'assets/js/admin.js',
            [],
            AGOMAINTENANCE_VERSION,
            true
        );

        wp_localize_script( 'agomaintenance-admin', 'agomaintenanceData', [
            'restUrl'  => rest_url( 'agomaintenance/v1' ),
            'nonce'    => wp_create_nonce( 'wp_rest' ),
            'settings' => self::get_settings(),
            'siteUrl'  => home_url( '/' ),
        ] );
    }

    /* ───── Helpers ───── */

    /** @return array<string, mixed> */
    public static function get_settings(): array {
        $saved = get_option( 'agomaintenance_settings', [] );
        return wp_parse_args( $saved, self::DEFAULTS );
    }
}

<?php
/**
 * Smoke tests for aGo Maintenance.
 *
 * Covers what the WordPress.org review and the August audit found: the IP
 * whitelist trusted forwarding headers, the countdown read the date in the
 * visitor timezone, the page printed its stylesheet and script outside the
 * WordPress loaders, and the preview link carried no nonce.
 *
 * Run with the plugin deployed:
 *   docker compose run --rm wpcli eval-file \
 *     //var/www/html/wp-content/plugins/ago-maintenance/tests/smoke.php
 */

use AgoLab\Maintenance\Frontend;
use AgoLab\Maintenance\Plugin;
use AgoLab\Maintenance\Template;

class AgoMaintenanceSmoke {

    public static int $ok = 0;
    public static int $fail = 0;

    public static function check( string $name, bool $cond, string $detail = '' ): void {
        if ( $cond ) {
            self::$ok++;
            echo "  ok    {$name}\n";
        } else {
            self::$fail++;
            echo "  FALLA {$name}" . ( $detail ? " ({$detail})" : '' ) . "\n";
        }
    }

    public static function run(): void {
        $saved_option = get_option( 'agomaintenance_settings', null );
        $saved_server = $_SERVER;
        $saved_tz     = get_option( 'timezone_string' );

        try {
            self::client_ip();
            self::countdown();
            self::page_assets();
            self::preview_nonce();
            self::http();
        } finally {
            $_SERVER = $saved_server;
            update_option( 'timezone_string', $saved_tz );
            null === $saved_option ? delete_option( 'agomaintenance_settings' ) : update_option( 'agomaintenance_settings', $saved_option );
        }

        echo "\n" . self::$ok . ' ok, ' . self::$fail . " fallas\n";
        if ( self::$fail ) {
            exit( 1 );
        }
    }

    private static function client_ip(): void {
        $m = new ReflectionMethod( Frontend::class, 'get_client_ip' );
        $m->setAccessible( true );

        $_SERVER['REMOTE_ADDR']          = '203.0.113.7';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.1';
        $_SERVER['HTTP_CF_CONNECTING_IP'] = '198.51.100.2';
        self::check( 'la IP sale de REMOTE_ADDR, no de las cabeceras', '203.0.113.7' === $m->invoke( null ), (string) $m->invoke( null ) );

        $_SERVER['REMOTE_ADDR'] = 'no-es-ip';
        self::check( 'REMOTE_ADDR invalido da cadena vacia', '' === $m->invoke( null ) );
    }

    private static function countdown(): void {
        update_option( 'timezone_string', 'America/Santiago' );
        $target   = Frontend::countdown_target( [ 'countdown_datetime' => '2026-12-31T23:00' ] );
        $expected = ( new DateTimeImmutable( '2026-12-31T23:00', new DateTimeZone( 'America/Santiago' ) ) )->getTimestamp() * 1000;
        self::check( 'cuenta regresiva en la zona del sitio', (string) $expected === $target, $target );
        self::check( 'fecha vacia no muestra cuenta regresiva', '' === Frontend::countdown_target( [ 'countdown_datetime' => '' ] ) );
        self::check( 'fecha invalida no muestra cuenta regresiva', '' === Frontend::countdown_target( [ 'countdown_datetime' => 'manana' ] ) );
    }

    private static function page_assets(): void {
        $settings = wp_parse_args(
            [
                'countdown_datetime' => '2030-01-01T10:00',
                'bg_image_url'       => 'https://example.com/fondo.jpg',
                'bg_color'           => '#112233',
            ],
            Plugin::defaults()
        );

        $m = new ReflectionMethod( Frontend::class, 'register_assets' );
        $m->setAccessible( true );
        $m->invoke( null, $settings );

        ob_start();
        Template::render( $settings );
        $html = ob_get_clean();

        self::check( 'la hoja de estilos sale por wp_print_styles', (bool) preg_match( "#<link[^>]+id='agomaintenance-page-css'#", $html ) );
        self::check( 'el CSS en linea lleva el color y el fondo', str_contains( $html, '--ago-bg:#112233' ) && str_contains( $html, 'fondo.jpg' ) );
        self::check( 'el script de la cuenta regresiva sale por wp_print_scripts', (bool) preg_match( "#<script[^>]+id=[\"']agomaintenance-countdown-js[\"']#", $html ) );
        self::check( 'no queda script en linea en la plantilla', ! preg_match( '#<script>\s*\(function#', $html ) );
        self::check( 'el destino va en milisegundos', str_contains( $html, 'data-target="' . Frontend::countdown_target( $settings ) . '"' ) );
    }

    private static function preview_nonce(): void {
        $admin = get_users( [ 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ] );
        wp_set_current_user( (int) $admin[0] );
        $ok = wp_create_nonce( 'agomaintenance_preview' );
        self::check( 'el nonce de vista previa valida para el administrador', false !== wp_verify_nonce( $ok, 'agomaintenance_preview' ) );
        self::check( 'un nonce inventado no valida', false === wp_verify_nonce( 'abc123', 'agomaintenance_preview' ) );
        wp_set_current_user( 0 );
    }

    private static function http(): void {
        update_option( 'agomaintenance_settings', wp_parse_args( [ 'enabled' => true, 'mode' => 'maintenance', 'ip_whitelist' => "198.51.100.9\n" ], Plugin::defaults() ) );

        // The query string keeps the request out of the nginx page cache, which
        // still holds the home page as it was before maintenance was enabled.
        $host = wp_parse_url( home_url(), PHP_URL_HOST );
        $get  = static function ( array $headers = [] ) use ( $host ) {
            return wp_remote_get( 'http://nginx/?agomaintenance_smoke=' . wp_rand(), [
                'headers'     => array_merge( [ 'Host' => $host, 'X-Forwarded-Proto' => 'https' ], $headers ),
                'redirection' => 0,
                'timeout'     => 15,
            ] );
        };

        $r = $get();
        if ( is_wp_error( $r ) ) {
            self::check( 'la portada responde', false, $r->get_error_message() );
            return;
        }
        self::check( 'visitante anonimo recibe 503', 503 === wp_remote_retrieve_response_code( $r ), (string) wp_remote_retrieve_response_code( $r ) );
        self::check( 'con Retry-After', '' !== wp_remote_retrieve_header( $r, 'retry-after' ) );

        $r = $get( [ 'X-Forwarded-For' => '198.51.100.9', 'CF-Connecting-IP' => '198.51.100.9', 'X-Real-IP' => '198.51.100.9' ] );
        self::check( 'una IP de la lista en cabeceras no salta el mantenimiento', 503 === wp_remote_retrieve_response_code( $r ), (string) wp_remote_retrieve_response_code( $r ) );

        foreach ( [ '/?agomaintenance_smoke=1&x=/login/', '/?agomaintenance_smoke=2&r=/wp-admin', '/sample-page/admin/?agomaintenance_smoke=3' ] as $path ) {
            $r = wp_remote_get( 'http://nginx' . $path, [ 'headers' => [ 'Host' => $host, 'X-Forwarded-Proto' => 'https' ], 'redirection' => 0, 'timeout' => 15 ] );
            self::check( "{$path} no salta el mantenimiento", 503 === wp_remote_retrieve_response_code( $r ), (string) wp_remote_retrieve_response_code( $r ) );
        }

        $r = wp_remote_get( 'http://nginx/login/?agomaintenance_smoke=4', [ 'headers' => [ 'Host' => $host, 'X-Forwarded-Proto' => 'https' ], 'redirection' => 0, 'timeout' => 15 ] );
        self::check( '/login/ sigue llevando al acceso', 503 !== wp_remote_retrieve_response_code( $r ), (string) wp_remote_retrieve_response_code( $r ) );

        $r = $get();
        $body = wp_remote_retrieve_body( $r );
        self::check( 'la pagina carga maintenance.css', str_contains( $body, 'assets/css/maintenance.css' ) );

        $r = wp_remote_get( 'http://nginx/?agomaintenance_preview=1&_wpnonce=abc', [ 'headers' => [ 'Host' => $host, 'X-Forwarded-Proto' => 'https' ], 'redirection' => 0, 'timeout' => 15 ] );
        self::check( 'vista previa sin sesion no cambia nada (sigue 503)', 503 === wp_remote_retrieve_response_code( $r ) );

        update_option( 'agomaintenance_settings', wp_parse_args( [ 'enabled' => false ], Plugin::defaults() ) );
        $r = $get();
        self::check( 'desactivado, la portada no da 503', 503 !== wp_remote_retrieve_response_code( $r ), (string) wp_remote_retrieve_response_code( $r ) );
    }
}

AgoMaintenanceSmoke::run();

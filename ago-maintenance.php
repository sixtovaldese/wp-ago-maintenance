<?php
/**
 * Plugin Name: aGo Maintenance
 * Plugin URI:  https://ago.cl/herramientas/
 * Description: One-click maintenance mode and coming soon page with countdown timer, IP whitelist, admin bypass, and SEO-friendly headers.
 * Version:     1.0.0
 * Requires PHP: 8.1
 * Author:      aGo Lab
 * Author URI:  https://ago.cl/
 * License:     GPL-2.0-or-later
 * Text Domain: ago-maintenance
 */

defined( 'ABSPATH' ) || exit;

define( 'AGO_MAINTENANCE_VERSION', '1.0.0' );
define( 'AGO_MAINTENANCE_FILE', __FILE__ );
define( 'AGO_MAINTENANCE_PATH', plugin_dir_path( __FILE__ ) );
define( 'AGO_MAINTENANCE_URL', plugin_dir_url( __FILE__ ) );

// PSR-4 Autoloader
spl_autoload_register( function ( string $class ): void {
    $prefix = 'AgoLab\\Maintenance\\';
    if ( strncmp( $class, $prefix, strlen( $prefix ) ) !== 0 ) {
        return;
    }
    $relative = substr( $class, strlen( $prefix ) );
    $file     = AGO_MAINTENANCE_PATH . 'src/' . str_replace( '\\', '/', $relative ) . '.php';
    if ( file_exists( $file ) ) {
        require_once $file;
    }
} );

// Boot
add_action( 'plugins_loaded', [ AgoLab\Maintenance\Plugin::class, 'instance' ] );

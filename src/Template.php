<?php

namespace AgoLab\Maintenance;

defined( 'ABSPATH' ) || exit;

class Template {

    /**
     * Render the standalone maintenance/coming-soon page.
     *
     * @param array<string, mixed> $settings
     */
    public static function render( array $settings ): void {
        $title     = $settings['title'];
        $message   = $settings['message'];
        $mode      = $settings['mode'];
        $user_logo = $settings['logo_url'];
        $bg_image  = $settings['bg_image_url'];
        $countdown = Frontend::countdown_target( $settings );

        ?><!DOCTYPE html>
<html lang="<?php echo esc_attr( get_locale() ); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo esc_html( $title ); ?></title>
    <?php if ( 'maintenance' === $mode ) : ?>
    <meta name="robots" content="noindex, nofollow">
    <?php endif; ?>
    <?php wp_print_styles( 'agomaintenance-page' ); ?>
</head>
<body<?php if ( $bg_image ) : ?> class="ago-has-bg"<?php endif; ?>>
    <div class="ago-maint-wrap">
        <div class="ago-maint-content">
            <?php if ( $user_logo ) : ?>
                <img src="<?php echo esc_url( $user_logo ); ?>" alt="" class="ago-maint-userlogo">
            <?php endif; ?>

            <h1><?php echo esc_html( $title ); ?></h1>
            <p class="ago-maint-message"><?php echo nl2br( esc_html( $message ) ); ?></p>

            <?php if ( '' !== $countdown ) : ?>
            <div id="ago-countdown" class="ago-maint-countdown" data-target="<?php echo esc_attr( $countdown ); ?>">
                <div class="ago-countdown-unit"><span id="ago-cd-days">--</span><small><?php esc_html_e( 'Days', 'ago-maintenance' ); ?></small></div>
                <div class="ago-countdown-unit"><span id="ago-cd-hours">--</span><small><?php esc_html_e( 'Hours', 'ago-maintenance' ); ?></small></div>
                <div class="ago-countdown-unit"><span id="ago-cd-minutes">--</span><small><?php esc_html_e( 'Minutes', 'ago-maintenance' ); ?></small></div>
                <div class="ago-countdown-unit"><span id="ago-cd-seconds">--</span><small><?php esc_html_e( 'Seconds', 'ago-maintenance' ); ?></small></div>
            </div>
            <?php wp_print_scripts( 'agomaintenance-countdown' ); ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
        <?php
    }
}

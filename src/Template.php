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
        $title         = $settings['title'];
        $message       = $settings['message'];
        $bg_color      = $settings['bg_color'];
        $text_color    = $settings['text_color'];
        $countdown     = $settings['countdown_datetime'];
        $mode          = $settings['mode'] ?? 'maintenance';
        $user_logo     = $settings['logo_url'] ?? '';
        $bg_image      = $settings['bg_image_url'] ?? '';
        $overlay       = max( 0, min( 100, intval( $settings['overlay_opacity'] ?? 60 ) ) ) / 100;
        $ago_logo_url  = AGO_MAINTENANCE_URL . 'assets/img/agolab.webp';
        $css_url       = AGO_MAINTENANCE_URL . 'assets/css/maintenance.css';

        ?><!DOCTYPE html>
<html lang="<?php echo esc_attr( get_locale() ); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo esc_html( $title ); ?></title>
    <?php if ( 'maintenance' === $mode ) : ?>
    <meta name="robots" content="noindex, nofollow">
    <?php endif; ?>
    <?php // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet -- Standalone maintenance template renders without wp_head(). ?>
    <link rel="stylesheet" href="<?php echo esc_url( $css_url ); ?>">
    <style>
        :root {
            --ago-bg: <?php echo esc_attr( $bg_color ); ?>;
            --ago-text: <?php echo esc_attr( $text_color ); ?>;
            --ago-overlay: <?php echo esc_attr( (string) $overlay ); ?>;
        }
        <?php if ( $bg_image ) : ?>
        body.ago-has-bg {
            background-image: url('<?php echo esc_url( $bg_image ); ?>');
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
        body.ago-has-bg::before {
            content: '';
            position: fixed;
            inset: 0;
            background: <?php echo esc_attr( $bg_color ); ?>;
            opacity: var(--ago-overlay);
            z-index: 0;
            pointer-events: none;
        }
        body.ago-has-bg .ago-maint-wrap { position: relative; z-index: 1; }
        <?php endif; ?>
    </style>
</head>
<body class="<?php echo $bg_image ? 'ago-has-bg' : ''; ?>">
    <div class="ago-maint-wrap">
        <div class="ago-maint-content">
            <?php if ( $user_logo ) : ?>
                <img src="<?php echo esc_url( $user_logo ); ?>" alt="" class="ago-maint-userlogo">
            <?php endif; ?>

            <h1><?php echo esc_html( $title ); ?></h1>
            <p class="ago-maint-message"><?php echo nl2br( esc_html( $message ) ); ?></p>

            <?php if ( ! empty( $countdown ) ) : ?>
            <div id="ago-countdown" class="ago-maint-countdown" data-target="<?php echo esc_attr( $countdown ); ?>">
                <div class="ago-countdown-unit"><span id="ago-cd-days">--</span><small><?php esc_html_e( 'Days', 'ago-maintenance' ); ?></small></div>
                <div class="ago-countdown-unit"><span id="ago-cd-hours">--</span><small><?php esc_html_e( 'Hours', 'ago-maintenance' ); ?></small></div>
                <div class="ago-countdown-unit"><span id="ago-cd-minutes">--</span><small><?php esc_html_e( 'Minutes', 'ago-maintenance' ); ?></small></div>
                <div class="ago-countdown-unit"><span id="ago-cd-seconds">--</span><small><?php esc_html_e( 'Seconds', 'ago-maintenance' ); ?></small></div>
            </div>
            <script>
            (function () {
                var el = document.getElementById('ago-countdown');
                if (!el) return;
                var target = new Date(el.getAttribute('data-target')).getTime();
                if (isNaN(target)) { el.style.display = 'none'; return; }
                var d = document.getElementById('ago-cd-days'),
                    h = document.getElementById('ago-cd-hours'),
                    m = document.getElementById('ago-cd-minutes'),
                    s = document.getElementById('ago-cd-seconds');
                function tick() {
                    var diff = target - Date.now();
                    if (diff <= 0) { d.textContent=h.textContent=m.textContent=s.textContent='0'; return; }
                    d.textContent = Math.floor(diff/86400000);
                    h.textContent = Math.floor((diff%86400000)/3600000);
                    m.textContent = Math.floor((diff%3600000)/60000);
                    s.textContent = Math.floor((diff%60000)/1000);
                }
                tick(); setInterval(tick, 1000);
            })();
            </script>
            <?php endif; ?>
        </div>

        <footer class="ago-maint-credit">
            <?php
            echo wp_kses_post(
                sprintf(
                    /* translators: 1: heart icon, 2: aGo Lab link */
                    __( 'Developed with %1$s by %2$s', 'ago-maintenance' ),
                    '<span aria-hidden="true">&#10084;</span>',
                    '<a href="' . esc_url( 'https://ago.cl' ) . '" target="_blank" rel="noopener"><img src="' . esc_url( $ago_logo_url ) . '" alt="aGo Lab"><span>aGo Lab</span></a>'
                )
            );
            ?>
        </footer>
    </div>
</body>
</html>
        <?php
    }
}

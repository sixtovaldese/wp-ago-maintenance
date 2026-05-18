<?php

namespace AgoLab\Maintenance\Admin;

use AgoLab\Maintenance\Plugin;

defined( 'ABSPATH' ) || exit;

class Page {

    public static function render(): void {
        ?>
        <div class="wrap">
            <h1>
                <img src="<?php echo esc_url( AGO_MAINTENANCE_URL . 'assets/img/agolab.webp' ); ?>" alt="aGo Lab" style="height:28px;width:auto;vertical-align:middle;margin-right:8px">
                <?php esc_html_e( 'aGo Maintenance', 'ago-maintenance' ); ?>
                <span style="font-size:12px;color:#999;margin-left:8px">v<?php echo esc_html( AGO_MAINTENANCE_VERSION ); ?></span>
            </h1>

            <div class="ago-layout">
                <div class="ago-main">

                    <!-- Master Toggle -->
                    <div class="card ago-card ago-master-toggle-card">
                        <div class="ago-master-toggle">
                            <div>
                                <h2 style="margin:0"><?php esc_html_e( 'Maintenance Mode', 'ago-maintenance' ); ?></h2>
                                <p class="ago-toggle-desc" id="ago-master-status">
                                    <?php esc_html_e( 'Toggle to enable or disable maintenance mode.', 'ago-maintenance' ); ?>
                                </p>
                            </div>
                            <label class="ago-switch ago-switch-lg">
                                <input type="checkbox" id="ago-enabled" data-key="enabled">
                                <span class="ago-slider"></span>
                            </label>
                        </div>
                        <div class="ago-admin-bypass-note">
                            <p class="ago-toggle-desc">
                                <strong><?php esc_html_e( 'How to verify:', 'ago-maintenance' ); ?></strong>
                                <?php esc_html_e( 'Administrators and any logged-in user with editing access never see the maintenance page, by design. To verify how it looks, open your site in a private/incognito window, or click the "Preview" button below.', 'ago-maintenance' ); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Settings -->
                    <div class="card ago-card">
                        <h2><?php esc_html_e( 'Settings', 'ago-maintenance' ); ?></h2>

                        <div id="ago-maint-status" style="display:none"></div>

                        <!-- Mode -->
                        <div class="ago-section">
                            <h3><?php esc_html_e( 'Mode', 'ago-maintenance' ); ?></h3>
                            <div class="ago-mode-selector">
                                <label class="ago-mode-option">
                                    <input type="radio" name="ago_mode" value="maintenance" data-key="mode">
                                    <span class="ago-mode-box">
                                        <strong><?php esc_html_e( 'Maintenance', 'ago-maintenance' ); ?></strong>
                                        <span><?php esc_html_e( 'HTTP 503 + Retry-After, SEO friendly for temporary downtime', 'ago-maintenance' ); ?></span>
                                    </span>
                                </label>
                                <label class="ago-mode-option">
                                    <input type="radio" name="ago_mode" value="coming_soon" data-key="mode">
                                    <span class="ago-mode-box">
                                        <strong><?php esc_html_e( 'Coming Soon', 'ago-maintenance' ); ?></strong>
                                        <span><?php esc_html_e( 'HTTP 200, For new sites that are not yet launched', 'ago-maintenance' ); ?></span>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="ago-section">
                            <h3><?php esc_html_e( 'Content', 'ago-maintenance' ); ?></h3>

                            <div class="ago-field">
                                <label for="ago-title"><?php esc_html_e( 'Title', 'ago-maintenance' ); ?></label>
                                <input type="text" id="ago-title" data-key="title" class="regular-text" placeholder="Under Maintenance">
                            </div>

                            <div class="ago-field">
                                <label for="ago-message"><?php esc_html_e( 'Message', 'ago-maintenance' ); ?></label>
                                <textarea id="ago-message" data-key="message" rows="4" class="large-text" placeholder="We are performing scheduled maintenance. We will be back shortly."></textarea>
                            </div>
                        </div>

                        <!-- Appearance -->
                        <div class="ago-section">
                            <h3><?php esc_html_e( 'Appearance', 'ago-maintenance' ); ?></h3>

                            <!-- Your logo -->
                            <div class="ago-field">
                                <label><?php esc_html_e( 'Your Logo', 'ago-maintenance' ); ?></label>
                                <p class="ago-toggle-desc"><?php esc_html_e( 'Shown large at the top of the page. PNG or SVG with transparent background recommended.', 'ago-maintenance' ); ?></p>
                                <div class="ago-media-row">
                                    <div class="ago-media-preview" id="ago-logo-preview">
                                        <img src="" alt="" style="display:none">
                                        <span class="ago-media-empty"><?php esc_html_e( 'No image selected', 'ago-maintenance' ); ?></span>
                                    </div>
                                    <div class="ago-media-buttons">
                                        <button type="button" class="button ago-media-pick" data-target="logo_url"><?php esc_html_e( 'Select image', 'ago-maintenance' ); ?></button>
                                        <button type="button" class="button-link ago-media-clear" data-target="logo_url"><?php esc_html_e( 'Remove', 'ago-maintenance' ); ?></button>
                                    </div>
                                </div>
                                <input type="hidden" id="ago-logo-url" data-key="logo_url" value="">
                            </div>

                            <!-- Background image -->
                            <div class="ago-field">
                                <label><?php esc_html_e( 'Background Image', 'ago-maintenance' ); ?></label>
                                <p class="ago-toggle-desc"><?php esc_html_e( 'Covers the entire screen. A dark overlay is applied on top so text stays readable.', 'ago-maintenance' ); ?></p>
                                <div class="ago-media-row">
                                    <div class="ago-media-preview ago-media-preview-bg" id="ago-bg-preview">
                                        <img src="" alt="" style="display:none">
                                        <span class="ago-media-empty"><?php esc_html_e( 'No image selected', 'ago-maintenance' ); ?></span>
                                    </div>
                                    <div class="ago-media-buttons">
                                        <button type="button" class="button ago-media-pick" data-target="bg_image_url"><?php esc_html_e( 'Select image', 'ago-maintenance' ); ?></button>
                                        <button type="button" class="button-link ago-media-clear" data-target="bg_image_url"><?php esc_html_e( 'Remove', 'ago-maintenance' ); ?></button>
                                    </div>
                                </div>
                                <input type="hidden" id="ago-bg-image-url" data-key="bg_image_url" value="">
                            </div>

                            <div class="ago-field">
                                <label for="ago-overlay-opacity"><?php esc_html_e( 'Overlay Darkness', 'ago-maintenance' ); ?> <span id="ago-overlay-value" style="color:#888">60%</span></label>
                                <input type="range" id="ago-overlay-opacity" data-key="overlay_opacity" min="0" max="100" step="5" value="60" style="width:280px;vertical-align:middle">
                                <p class="ago-toggle-desc"><?php esc_html_e( 'How dark the overlay over your background image is. 0% = no overlay, 100% = solid color.', 'ago-maintenance' ); ?></p>
                            </div>

                            <div class="ago-field-row">
                                <div class="ago-field">
                                    <label for="ago-bg-color"><?php esc_html_e( 'Background Color', 'ago-maintenance' ); ?></label>
                                    <input type="color" id="ago-bg-color" data-key="bg_color" value="#1d2327">
                                    <p class="ago-toggle-desc" style="margin-top:4px"><?php esc_html_e( 'Used when no background image is selected, and as the overlay color.', 'ago-maintenance' ); ?></p>
                                </div>
                                <div class="ago-field">
                                    <label for="ago-text-color"><?php esc_html_e( 'Text Color', 'ago-maintenance' ); ?></label>
                                    <input type="color" id="ago-text-color" data-key="text_color" value="#ffffff">
                                </div>
                            </div>
                        </div>

                        <!-- Countdown -->
                        <div class="ago-section">
                            <h3><?php esc_html_e( 'Countdown Timer', 'ago-maintenance' ); ?></h3>
                            <p class="ago-toggle-desc"><?php esc_html_e( 'Set a target date/time to show a countdown on the maintenance page. Leave empty to hide.', 'ago-maintenance' ); ?></p>
                            <div class="ago-field">
                                <label for="ago-countdown"><?php esc_html_e( 'Target Date & Time', 'ago-maintenance' ); ?></label>
                                <input type="datetime-local" id="ago-countdown" data-key="countdown_datetime">
                            </div>
                        </div>

                        <!-- IP Whitelist -->
                        <div class="ago-section">
                            <h3><?php esc_html_e( 'IP Whitelist', 'ago-maintenance' ); ?></h3>
                            <p class="ago-toggle-desc"><?php esc_html_e( 'One IP address per line. These IPs will bypass maintenance mode.', 'ago-maintenance' ); ?></p>
                            <div class="ago-field">
                                <textarea id="ago-whitelist" data-key="ip_whitelist" rows="4" class="large-text" placeholder="192.168.1.1&#10;10.0.0.1"></textarea>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="ago-actions">
                            <button id="ago-save-btn" class="button button-primary" type="button">
                                <?php esc_html_e( 'Save Settings', 'ago-maintenance' ); ?>
                            </button>
                            <button id="ago-preview-btn" class="button" type="button">
                                <?php esc_html_e( 'Preview', 'ago-maintenance' ); ?>
                            </button>
                        </div>
                    </div>

                </div>

                <!-- SIDEBAR -->
                <div class="ago-sidebar">

                    <!-- About -->
                    <div class="card ago-card">
                        <h3><?php esc_html_e( 'About', 'ago-maintenance' ); ?></h3>
                        <p style="font-size:13px;color:#666">
                            <?php esc_html_e( 'One-click maintenance mode and coming soon page. Admins bypass automatically.', 'ago-maintenance' ); ?>
                        </p>
                        <ul class="ago-features">
                            <li><?php esc_html_e( 'Toggle maintenance on/off instantly', 'ago-maintenance' ); ?></li>
                            <li><?php esc_html_e( 'Maintenance (503) or Coming Soon (200) mode', 'ago-maintenance' ); ?></li>
                            <li><?php esc_html_e( 'Countdown timer with target date', 'ago-maintenance' ); ?></li>
                            <li><?php esc_html_e( 'IP whitelist bypass', 'ago-maintenance' ); ?></li>
                            <li><?php esc_html_e( 'Admin bar indicator when active', 'ago-maintenance' ); ?></li>
                            <li><?php esc_html_e( 'Custom colors, title and message', 'ago-maintenance' ); ?></li>
                        </ul>
                    </div>

                    <!-- Donation -->
                    <div class="card ago-card ago-donation">
                        <h3><?php esc_html_e( 'Support Open Source', 'ago-maintenance' ); ?></h3>
                        <p style="font-size:13px;color:#666">
                            <?php esc_html_e( 'If this plugin saves you time, consider supporting our open-source work.', 'ago-maintenance' ); ?>
                        </p>
                        <div class="ago-donation-amounts">
                            <a href="https://paypal.me/sixtovaldes/3" class="ago-amount" target="_blank" rel="noopener">$3</a>
                            <a href="https://paypal.me/sixtovaldes/5" class="ago-amount" target="_blank" rel="noopener">$5</a>
                            <a href="https://paypal.me/sixtovaldes/10" class="ago-amount" target="_blank" rel="noopener">$10</a>
                        </div>
                        <a href="https://paypal.me/sixtovaldes" class="ago-coffee-btn" target="_blank" rel="noopener">
                            <span class="dashicons dashicons-coffee" style="margin-right:6px"></span>
                            <?php esc_html_e( 'Buy us a coffee', 'ago-maintenance' ); ?>
                        </a>
                        <p class="ago-donation-note">
                            <?php esc_html_e( 'Voluntary donation. Thank you!', 'ago-maintenance' ); ?>
                        </p>
                    </div>

                    <!-- Footer with logo -->
                    <div class="ago-footer">
                        <a href="https://ago.cl" target="_blank" rel="noopener" class="ago-footer-logo">
                            <img src="<?php echo esc_url( AGO_MAINTENANCE_URL . 'assets/img/agolab.webp' ); ?>" alt="aGo Lab" style="height:40px;width:auto">
                        </a>
                        <p>
                            <?php
                            echo wp_kses_post(
                                sprintf(
                                    /* translators: 1: heart icon HTML, 2: aGo Lab link HTML */
                                    __( 'Developed with %1$s by %2$s', 'ago-maintenance' ),
                                    '<span style="color:#e25555">&#10084;</span>',
                                    '<a href="https://ago.cl" target="_blank" rel="noopener"><strong>aGo Lab</strong></a>'
                                )
                            );
                            ?>
                        </p>
                        <p style="font-size:11px;color:#999">
                            <?php esc_html_e( 'Building tools for the web, one plugin at a time.', 'ago-maintenance' ); ?>
                        </p>
                    </div>

                </div>
            </div>

        </div>
        <?php
    }
}

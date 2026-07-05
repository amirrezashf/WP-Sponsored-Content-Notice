<?php
/**
 * Plugin Name:       WP Sponsored Content Notice
 * Plugin URI:        https://github.com/amirrezashf/WP-Sponsored-Content-Notice
 * Description:       Displays a sponsored content notice before and after selected WordPress posts from a target category, with a per-post disable option.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Amirreza Shayesteh Far
 * Author URI:        https://github.com/amirrezashf
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wp-sponsored-content-notice
 * Domain Path:       /languages
 *
 * @package WPSponsoredContentNotice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_Sponsored_Content_Notice' ) ) {
	/**
	 * Main plugin class.
	 */
	final class WP_Sponsored_Content_Notice {

		/**
		 * Plugin version.
		 *
		 * @var string
		 */
		private const VERSION = '1.0.0';

		/**
		 * Default target category ID.
		 *
		 * @var int
		 */
		private const DEFAULT_CATEGORY_ID = 1951;

		/**
		 * Meta key for disabling notice per post.
		 *
		 * @var string
		 */
		private const META_DISABLE_NOTICE = '_wp_scn_disable_notice';

		/**
		 * Frontend style handle.
		 *
		 * @var string
		 */
		private const STYLE_HANDLE = 'wp-sponsored-content-notice';

		/**
		 * Nonce action.
		 *
		 * @var string
		 */
		private const NONCE_ACTION = 'wp_scn_save_metabox';

		/**
		 * Nonce field.
		 *
		 * @var string
		 */
		private const NONCE_FIELD = 'wp_scn_metabox_nonce';

		/**
		 * Singleton instance.
		 *
		 * @var self|null
		 */
		private static $instance = null;

		/**
		 * Get singleton instance.
		 *
		 * @return self
		 */
		public static function instance(): self {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor.
		 */
		private function __construct() {
			add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_styles' ) );
			add_filter( 'the_content', array( $this, 'append_notice_to_content' ), 20 );
			add_action( 'add_meta_boxes_post', array( $this, 'register_metabox' ) );
			add_action( 'save_post_post', array( $this, 'save_metabox' ) );
		}

		/**
		 * Load plugin translations.
		 *
		 * @return void
		 */
		public function load_textdomain(): void {
			load_plugin_textdomain(
				'wp-sponsored-content-notice',
				false,
				dirname( plugin_basename( __FILE__ ) ) . '/languages'
			);
		}

		/**
		 * Get target category ID.
		 *
		 * @return int
		 */
		private function get_target_category_id(): int {
			/**
			 * Filters target category ID for sponsored content notice.
			 *
			 * @param int $category_id Target category ID.
			 */
			return absint( apply_filters( 'wp_scn_target_category_id', self::DEFAULT_CATEGORY_ID ) );
		}

		/**
		 * Check whether notice should be displayed for a post.
		 *
		 * @param int $post_id Post ID.
		 *
		 * @return bool
		 */
		private function should_show_notice( int $post_id ): bool {
			$post_id = absint( $post_id );

			if ( ! $post_id ) {
				return false;
			}

			if ( 'post' !== get_post_type( $post_id ) ) {
				return false;
			}

			$category_id = $this->get_target_category_id();

			if ( ! $category_id || ! has_term( $category_id, 'category', $post_id ) ) {
				return false;
			}

			$is_disabled = 'yes' === get_post_meta( $post_id, self::META_DISABLE_NOTICE, true );

			if ( $is_disabled ) {
				return false;
			}

			/**
			 * Filters whether the notice should be displayed for a specific post.
			 *
			 * @param bool $should_show Whether notice should be shown.
			 * @param int  $post_id     Post ID.
			 */
			return (bool) apply_filters( 'wp_scn_should_show_notice', true, $post_id );
		}

		/**
		 * Enqueue frontend inline styles only on eligible single posts.
		 *
		 * @return void
		 */
		public function enqueue_frontend_styles(): void {
			if ( is_admin() || ! is_singular( 'post' ) ) {
				return;
			}

			$post_id = get_queried_object_id();

			if ( ! $post_id || ! $this->should_show_notice( $post_id ) ) {
				return;
			}

			wp_register_style(
				self::STYLE_HANDLE,
				false,
				array(),
				self::VERSION
			);

			wp_enqueue_style( self::STYLE_HANDLE );

			wp_add_inline_style(
				self::STYLE_HANDLE,
				$this->get_frontend_css()
			);
		}

		/**
		 * Append notice before and after post content.
		 *
		 * @param string $content Post content.
		 *
		 * @return string
		 */
		public function append_notice_to_content( string $content ): string {
			if ( is_admin() ) {
				return $content;
			}

			if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
				return $content;
			}

			$post_id = get_the_ID();

			if ( ! $post_id || ! $this->should_show_notice( $post_id ) ) {
				return $content;
			}

			$notice_html = $this->get_notice_html();

			$top_notice = sprintf(
				'<div class="wp-scn-wrap wp-scn-wrap-top">%s</div>',
				$notice_html
			);

			$bottom_notice = sprintf(
				'<div class="wp-scn-wrap wp-scn-wrap-bottom">%s</div>',
				$notice_html
			);

			return $top_notice . $content . $bottom_notice;
		}

		/**
		 * Get notice title.
		 *
		 * @return string
		 */
		private function get_notice_title(): string {
			/**
			 * Filters sponsored content notice title.
			 *
			 * @param string $title Notice title.
			 */
			return (string) apply_filters(
				'wp_scn_notice_title',
				__( 'هشدار محتوای تبلیغاتی', 'wp-sponsored-content-notice' )
			);
		}

		/**
		 * Get desktop notice text.
		 *
		 * @return string
		 */
		private function get_desktop_notice_text(): string {
			/**
			 * Filters sponsored content desktop notice text.
			 *
			 * @param string $text Desktop notice text.
			 */
			return (string) apply_filters(
				'wp_scn_desktop_notice_text',
				__( 'این مطلب صرفاً جنبه تبلیغاتی دارد، به محتوای تحریریه‌ای این رسانه مستقیماً مرتبط نیست و مسئولیت آن بر عهده سفارش‌دهنده تبلیغ است.', 'wp-sponsored-content-notice' )
			);
		}

		/**
		 * Get mobile notice text.
		 *
		 * @return string
		 */
		private function get_mobile_notice_text(): string {
			/**
			 * Filters sponsored content mobile notice text.
			 *
			 * @param string $text Mobile notice text.
			 */
			return (string) apply_filters(
				'wp_scn_mobile_notice_text',
				__( 'این مطلب تبلیغاتی است و مسئولیت آن بر عهده سفارش‌دهنده تبلیغ است.', 'wp-sponsored-content-notice' )
			);
		}

		/**
		 * Get notice HTML.
		 *
		 * @return string
		 */
		private function get_notice_html(): string {
			ob_start();
			?>
			<div class="wp-scn-box" role="note" aria-live="polite">
				<div class="wp-scn-inner">
					<span class="wp-scn-icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" focusable="false">
							<path d="M12 8V13" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
							<circle cx="12" cy="16.5" r="1" fill="currentColor"></circle>
							<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"></path>
						</svg>
					</span>

					<div class="wp-scn-content">
						<div class="wp-scn-title">
							<?php echo esc_html( $this->get_notice_title() ); ?>
						</div>

						<div class="wp-scn-text wp-scn-text-desktop">
							<?php echo esc_html( $this->get_desktop_notice_text() ); ?>
						</div>

						<div class="wp-scn-text wp-scn-text-mobile">
							<?php echo esc_html( $this->get_mobile_notice_text() ); ?>
						</div>
					</div>
				</div>
			</div>
			<?php

			return (string) ob_get_clean();
		}

		/**
		 * Register post metabox.
		 *
		 * @return void
		 */
		public function register_metabox(): void {
			add_meta_box(
				'wp_scn_metabox',
				esc_html__( 'تنظیمات هشدار محتوای تبلیغاتی', 'wp-sponsored-content-notice' ),
				array( $this, 'render_metabox' ),
				'post',
				'side',
				'default'
			);
		}

		/**
		 * Render post metabox.
		 *
		 * @param WP_Post $post Post object.
		 *
		 * @return void
		 */
		public function render_metabox( WP_Post $post ): void {
			wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );

			$is_in_category = has_term( $this->get_target_category_id(), 'category', $post->ID );
			$is_disabled    = 'yes' === get_post_meta( $post->ID, self::META_DISABLE_NOTICE, true );
			?>
			<div class="wp-scn-admin-box">
				<?php if ( $is_in_category ) : ?>
					<p style="margin:0 0 10px; line-height:1.8;">
						<?php echo esc_html__( 'این نوشته داخل دسته‌بندی هدف قرار دارد و هشدار محتوای تبلیغاتی به‌صورت خودکار در ابتدا و انتهای محتوا نمایش داده می‌شود.', 'wp-sponsored-content-notice' ); ?>
					</p>

					<label style="display:flex; align-items:flex-start; gap:8px; line-height:1.8;">
						<input type="checkbox" name="wp_scn_disable_notice" value="1" <?php checked( $is_disabled ); ?> />
						<span>
							<?php echo esc_html__( 'نمایش هشدار محتوای تبلیغاتی برای این نوشته غیرفعال شود', 'wp-sponsored-content-notice' ); ?>
						</span>
					</label>

					<p style="margin:10px 0 0; color:#646970; font-size:12px; line-height:1.8;">
						<?php echo esc_html__( 'این گزینه فقط برای نوشته‌های دسته‌بندی هدف کاربرد دارد.', 'wp-sponsored-content-notice' ); ?>
					</p>
				<?php else : ?>
					<p style="margin:0; color:#646970; line-height:1.9;">
						<?php echo esc_html__( 'این نوشته در دسته‌بندی هدف قرار ندارد؛ بنابراین هشدار محتوای تبلیغاتی برای آن اعمال نمی‌شود.', 'wp-sponsored-content-notice' ); ?>
					</p>
				<?php endif; ?>
			</div>
			<?php
		}

		/**
		 * Save metabox data.
		 *
		 * @param int $post_id Post ID.
		 *
		 * @return void
		 */
		public function save_metabox( int $post_id ): void {
			$post_id = absint( $post_id );

			if ( ! $post_id ) {
				return;
			}

			$nonce = isset( $_POST[ self::NONCE_FIELD ] ) ? sanitize_text_field( wp_unslash( $_POST[ self::NONCE_FIELD ] ) ) : '';

			if ( ! $nonce || ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
				return;
			}

			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
				return;
			}

			if ( 'post' !== get_post_type( $post_id ) ) {
				return;
			}

			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}

			$is_in_category = has_term( $this->get_target_category_id(), 'category', $post_id );

			if ( ! $is_in_category ) {
				delete_post_meta( $post_id, self::META_DISABLE_NOTICE );
				return;
			}

			$is_disabled = isset( $_POST['wp_scn_disable_notice'] );

			if ( $is_disabled ) {
				update_post_meta( $post_id, self::META_DISABLE_NOTICE, 'yes' );
				return;
			}

			delete_post_meta( $post_id, self::META_DISABLE_NOTICE );
		}

		/**
		 * Get frontend CSS.
		 *
		 * @return string
		 */
		private function get_frontend_css(): string {
			return '
.wp-scn-wrap {
	direction: rtl;
	margin: 22px 0;
}

.wp-scn-box {
	background:
		linear-gradient(180deg, rgba(255, 255, 255, 0.78), rgba(255, 255, 255, 0.92)),
		linear-gradient(135deg, #fff7ed 0%, #fffbeb 100%);
	border: 1px solid rgba(245, 158, 11, 0.22);
	border-radius: 14px;
	box-shadow:
		0 10px 30px rgba(15, 23, 42, 0.05),
		inset 0 1px 0 rgba(255, 255, 255, 0.75);
	box-sizing: border-box;
	overflow: hidden;
	padding: 16px 18px;
	position: relative;
	width: 100%;
}

.wp-scn-box::before {
	background: linear-gradient(180deg, #f59e0b 0%, #f97316 100%);
	content: "";
	height: 100%;
	position: absolute;
	right: 0;
	top: 0;
	width: 4px;
}

.wp-scn-inner {
	align-items: flex-start;
	box-sizing: border-box;
	display: flex;
	gap: 12px;
	width: 100%;
}

.wp-scn-icon {
	align-items: center;
	background: linear-gradient(135deg, #fff3cd 0%, #ffe7ba 100%);
	border-radius: 12px;
	box-shadow: inset 0 0 0 1px rgba(245, 158, 11, 0.16);
	color: #b45309;
	display: inline-flex;
	flex: 0 0 auto;
	height: 42px;
	justify-content: center;
	width: 42px;
}

.wp-scn-icon svg {
	display: block;
	height: 20px;
	width: 20px;
}

.wp-scn-content {
	flex: 1 1 auto;
	min-width: 0;
}

.wp-scn-title {
	color: #9a3412;
	font-size: 13px;
	font-weight: 700;
	line-height: 1.7;
	margin: 0 0 5px;
	padding: 0;
}

.wp-scn-text {
	color: #7c2d12;
	font-size: 14px;
	font-weight: 700;
	line-height: 2;
	margin: 0;
	padding: 0;
}

.wp-scn-text-mobile {
	display: none;
}

@media (max-width: 767px) {
	.wp-scn-wrap {
		margin: 10px 0;
	}

	.wp-scn-box {
		background: #fffaf0;
		border-color: rgba(245, 158, 11, 0.14);
		border-radius: 9px;
		box-shadow: 0 4px 12px rgba(15, 23, 42, 0.035);
		padding: 8px 10px;
	}

	.wp-scn-box::before {
		width: 3px;
	}

	.wp-scn-inner {
		align-items: center;
		gap: 7px;
	}

	.wp-scn-icon {
		background: #fff1d6;
		border-radius: 7px;
		height: 24px;
		width: 24px;
	}

	.wp-scn-icon svg {
		height: 13px;
		width: 13px;
	}

	.wp-scn-title,
	.wp-scn-text-desktop {
		display: none;
	}

	.wp-scn-text-mobile {
		color: #7c2d12;
		display: block;
		font-size: 11px;
		font-weight: 600;
		line-height: 1.7;
	}
}

@media (max-width: 420px) {
	.wp-scn-wrap {
		margin: 9px 0;
	}

	.wp-scn-box {
		border-radius: 8px;
		padding: 7px 9px;
	}

	.wp-scn-inner {
		gap: 8px;
	}

	.wp-scn-icon {
		border-radius: 7px;
		height: 22px;
		width: 22px;
	}

	.wp-scn-icon svg {
		height: 12px;
		width: 12px;
	}

	.wp-scn-text-mobile {
		font-size: 14px !important;
		line-height: 1.65;
	}
}
';
		}
	}
}

WP_Sponsored_Content_Notice::instance();

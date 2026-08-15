<?php
/**
 * Admin settings for the Support Button plugin.
 *
 * Owns the Settings page, option registration, and sanitising. Nothing
 * here renders on the front end.
 *
 * @package DPSupportButton
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DP_Support_Settings {

	const GROUP = 'dpsb_group';
	const PAGE  = 'discreet-support-button';

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_init', array( $this, 'register' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin' ) );
		add_filter(
			'plugin_action_links_' . plugin_basename( DPSB_DIR . 'discreet-support-button.php' ),
			array( $this, 'settings_link' )
		);
	}

	/**
	 * Read the stored option merged with defaults.
	 *
	 * @return array
	 */
	public static function get() {
		return wp_parse_args(
			(array) get_option( DPSB_OPTION, array() ),
			array(
				'enabled' => 0,
				'title'   => '',
				'methods' => array(),
			)
		);
	}

	public function add_menu() {
		add_options_page(
			__( 'Discreet Support Button', 'discreet-support-button' ),
			__( 'Support button', 'discreet-support-button' ),
			'manage_options',
			self::PAGE,
			array( $this, 'render_page' )
		);
	}

	public function register() {
		register_setting(
			self::GROUP,
			DPSB_OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize' ),
				'default'           => array(),
			)
		);
	}

	/**
	 * Add a Settings link on the Plugins screen.
	 *
	 * @param array $links Existing action links.
	 * @return array
	 */
	public function settings_link( $links ) {
		$url  = admin_url( 'options-general.php?page=' . self::PAGE );
		$link = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'discreet-support-button' ) . '</a>';
		array_unshift( $links, $link );
		return $links;
	}

	/**
	 * Clean the submitted settings before they are stored.
	 *
	 * @param array $input Raw submitted values.
	 * @return array
	 */
	public function sanitize( $input ) {
		$out            = array();
		$out['enabled'] = ( ! empty( $input['enabled'] ) ) ? 1 : 0;
		$out['title']   = isset( $input['title'] ) ? sanitize_text_field( $input['title'] ) : '';
		$out['methods'] = array();

		if ( ! empty( $input['methods'] ) && is_array( $input['methods'] ) ) {
			foreach ( $input['methods'] as $method ) {
				$label = isset( $method['label'] ) ? sanitize_text_field( $method['label'] ) : '';
				$url   = isset( $method['url'] ) ? esc_url_raw( trim( $method['url'] ) ) : '';
				if ( '' === $label && '' === $url ) {
					continue;
				}
				$out['methods'][] = array(
					'label' => $label,
					'url'   => $url,
				);
			}
		}

		return $out;
	}

	public function enqueue_admin( $hook ) {
		if ( 'settings_page_' . self::PAGE !== $hook ) {
			return;
		}
		wp_enqueue_style( 'dpsb-admin', DPSB_URL . 'assets/css/admin.css', array(), DPSB_VERSION );
		wp_enqueue_script( 'dpsb-admin', DPSB_URL . 'assets/js/admin.js', array(), DPSB_VERSION, true );
	}

	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$opt     = self::get();
		$methods = ! empty( $opt['methods'] ) ? $opt['methods'] : array(
			array(
				'label' => '',
				'url'   => '',
			),
		);
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Discreet Support Button', 'discreet-support-button' ); ?></h1>
			<form method="post" action="options.php">
				<?php settings_fields( self::GROUP ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Show the button', 'discreet-support-button' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( DPSB_OPTION ); ?>[enabled]" value="1" <?php checked( $opt['enabled'], 1 ); ?> />
								<?php esc_html_e( 'Display the support button on the site', 'discreet-support-button' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="dpsb-title"><?php esc_html_e( 'Button title', 'discreet-support-button' ); ?></label>
						</th>
						<td>
							<input type="text" id="dpsb-title" class="regular-text" name="<?php echo esc_attr( DPSB_OPTION ); ?>[title]" value="<?php echo esc_attr( $opt['title'] ); ?>" placeholder="<?php esc_attr_e( 'Support this site', 'discreet-support-button' ); ?>" />
							<p class="description"><?php esc_html_e( 'The text shown on the closed button.', 'discreet-support-button' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Donation methods', 'discreet-support-button' ); ?></th>
						<td>
							<div id="dpsb-methods">
								<?php foreach ( $methods as $i => $method ) : ?>
									<?php $this->render_row( (int) $i, $method ); ?>
								<?php endforeach; ?>
							</div>
							<p>
								<button type="button" class="button" id="dpsb-add"><?php esc_html_e( 'Add method', 'discreet-support-button' ); ?></button>
							</p>
							<p class="description"><?php esc_html_e( 'Each method is a labelled link, for example "Buy me a coffee" pointing to your Ko-fi page. Add as many as you like.', 'discreet-support-button' ); ?></p>

							<script type="text/html" id="dpsb-row-template">
								<?php
								$this->render_row(
									0,
									array(
										'label' => '',
										'url'   => '',
									),
									true
								);
								?>
							</script>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render one label plus url row.
	 *
	 * @param int   $index       Row index.
	 * @param array $method      Row values.
	 * @param bool  $is_template Whether this is the JS clone template.
	 */
	private function render_row( $index, $method, $is_template = false ) {
		$idx  = $is_template ? '__i__' : $index;
		$name = DPSB_OPTION . '[methods][' . $idx . ']';
		?>
		<div class="dpsb-row">
			<input type="text" name="<?php echo esc_attr( $name ); ?>[label]" value="<?php echo esc_attr( $method['label'] ); ?>" placeholder="<?php esc_attr_e( 'Label, e.g. Buy me a coffee', 'discreet-support-button' ); ?>" />
			<input type="url" name="<?php echo esc_attr( $name ); ?>[url]" value="<?php echo esc_attr( $method['url'] ); ?>" placeholder="https://ko-fi.com/yourname" />
			<button type="button" class="button dpsb-remove" aria-label="<?php esc_attr_e( 'Remove this method', 'discreet-support-button' ); ?>">&times;</button>
		</div>
		<?php
	}
}

<?php
/**
 * Front-end rendering for the Support Button plugin.
 *
 * @package DPSupportButton
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DP_Support_Render {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'wp_footer', array( $this, 'render' ) );
	}

	/**
	 * The widget shows only when it is enabled and has at least one method.
	 *
	 * @return bool
	 */
	private function is_active() {
		$opt = DP_Support_Settings::get();
		return ! empty( $opt['enabled'] ) && ! empty( $opt['methods'] );
	}

	public function enqueue() {
		if ( ! $this->is_active() ) {
			return;
		}
		wp_enqueue_style( 'dpsb', DPSB_URL . 'assets/css/support.css', array(), DPSB_VERSION );
		wp_enqueue_script( 'dpsb', DPSB_URL . 'assets/js/support.js', array(), DPSB_VERSION, true );
	}

	public function render() {
		if ( ! $this->is_active() ) {
			return;
		}

		$opt   = DP_Support_Settings::get();
		$title = '' !== $opt['title'] ? $opt['title'] : __( 'Support this site', 'discreet-support-button' );
		?>
<div class="dp-support" data-dp-support>
	<button type="button" class="dp-support__toggle" aria-expanded="false" aria-controls="dp-support-panel">
		<span class="dp-support__label"><?php echo esc_html( $title ); ?></span>
	</button>
	<div class="dp-support__panel" id="dp-support-panel" role="group" aria-label="<?php echo esc_attr( $title ); ?>" hidden>
		<?php
		foreach ( $opt['methods'] as $method ) :
			if ( empty( $method['url'] ) ) {
				continue;
			}
			$label = '' !== $method['label'] ? $method['label'] : $method['url'];
			?>
		<a class="dp-support__link" href="<?php echo esc_url( $method['url'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $label ); ?></a>
			<?php
		endforeach;
		?>
	</div>
</div>
		<?php
	}
}

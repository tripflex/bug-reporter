<?php
/**
 * sMyles WordPress Plugin Updater
 *
 * Author:       Myles McNamara
 * Author URI:   http://plugins.smyl.es
 * License:      GPL 3+
 * Version:      @@version
 * Last Updated: @@timestamp
 */

if ( ! class_exists( 'sMyles_Bug_Report' ) ) {

	class sMyles_Bug_Report extends WP_Job_Manager_Field_Editor {

		private $version = '@@version';

		function __construct() {

			if ( ! defined( 'SMYLES_BUG_REPORT_DIR' ) ) {
				define( 'SMYLES_BUG_REPORT_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
			}

			if ( ! defined( 'SMYLES_BUG_REPORT_URL' ) ) {
				define( 'SMYLES_BUG_REPORT_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
			}

			add_action( 'wp_ajax_smyles_submit_bug', array( $this, 'submit_bug' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );

			$this->enable_debug();
		}

		function get_version(){
			return $this->version;
		}

		public function enable_debug(){

			// Set debug display false if not defined as default is true
			if ( ! defined( 'WP_DEBUG_DISPLAY' ) ){
				define( 'WP_DEBUG_DISPLAY', false );
			}

			if( ! defined('WP_DEBUG_LOG') || WP_DEBUG_LOG == false ) define( 'WP_DEBUG_LOG', true );
			if( ! defined('WP_DEBUG') || WP_DEBUG == false ) define( 'WP_DEBUG', true );

			wp_debug_mode();

		}

		public function admin_assets() {

			wp_enqueue_style( 'smyles-bug-report', SMYLES_BUG_REPORT_URL . '/assets/css/core.min.css' );
			wp_enqueue_style( 'smyles-bug-report-vendor', SMYLES_BUG_REPORT_URL . '/assets/css/vendor.min.css' );
			wp_enqueue_script( 'smyles-bug-report', SMYLES_BUG_REPORT_URL . '/assets/js/core.min.js', array( 'jquery' ), $this->version, true );
			wp_enqueue_script( 'smyles-bug-report-vendor', SMYLES_BUG_REPORT_URL . '/assets/js/vendor.min.js', array( 'jquery' ), $this->version, true );

			// JS Translation Vars
			$translation_array = array(
				'submit_success' => __( 'Bug Report submitted succesfully!  Thank You!' ),
				'submit_error' => __( 'There was an error submitting your bug report, please try again later.' )
			);

			wp_localize_script( 'smyles-bug-report', 'smyles_bug_report', $translation_array );
		}

		public function server_details(){
			$indices = array(
				'PHP_SELF',
				'GATEWAY_INTERFACE',
				'SERVER_ADDR',
				'SERVER_NAME',
				'SERVER_SOFTWARE',
				'SERVER_PROTOCOL',
				'REQUEST_METHOD',
				'REQUEST_TIME',
				'DOCUMENT_ROOT',
				'HTTP_ACCEPT',
				'HTTP_ACCEPT_CHARSET',
				'HTTP_ACCEPT_ENCODING',
				'HTTP_ACCEPT_LANGUAGE',
				'HTTP_CONNECTION',
				'HTTP_HOST',
				'HTTP_REFERER',
				'HTTP_USER_AGENT',
				'HTTPS',
				'REMOTE_ADDR',
				'REMOTE_HOST',
				'REMOTE_PORT',
				'REMOTE_USER',
				'REDIRECT_REMOTE_USER',
				'SCRIPT_FILENAME',
				'SERVER_ADMIN',
				'SERVER_PORT',
				'SERVER_SIGNATURE',
				'PATH_TRANSLATED',
				'SCRIPT_NAME',
				'REQUEST_URI',
				'PHP_AUTH_DIGEST',
				'PHP_AUTH_USER',
				'PHP_AUTH_PW',
				'AUTH_TYPE',
				'PATH_INFO',
				'ORIG_PATH_INFO'
			);

			echo '<table cellpadding="10">' ;
			foreach ($indices as $arg) {
			    if (isset($_SERVER[$arg])) {
			        echo '<tr><td>'.$arg.'</td><td>' . $_SERVER[$arg] . '</td></tr>' ;
			    }
			    else {
			        echo '<tr><td>'.$arg.'</td><td>-</td></tr>' ;
			    }
			}
			echo '</table>' ;
		}

		public function submit_bug() {

			check_ajax_referer( 'smyles_submit_bug', 'nonce' );

			$email          = sanitize_email( $_POST[ 'email' ] );
			$description    = sanitize_text_field( $_POST[ 'description' ] );
			$details        = sanitize_text_field( $_POST[ 'details' ] );
			$active_plugins = get_option( 'active_plugins' );

			if( ! is_email( $email ) ){

				$response['status'] = 'error';
				$response['error'] = __( 'Invalid Email' );

			} else {

				ob_start();

				$headers[] = 'Content-Type: text/html; charset=UTF-8';
				$attachments = array();

				if( defined( 'WP_CONTENT_DIR' ) ){
					if( file_exists( WP_CONTENT_DIR . '/debug.log') ){
						$attachments[] = WP_CONTENT_DIR . '/debug.log';
					}
				} elseif( defined( 'ABSPATH' ) ) {
					if( is_dir( ABSPATH . '/wp-content' ) ){
						$attachments[] = ABSPATH . '/wp-content/debug.log';
					}
				}

				echo '<strong>From:</strong> ' . $email . "<br />";
				echo '<strong>Description:</strong> ' . $description . "<br />";
				echo '<strong>Details:</strong> ' . "<br />" . $details . "<br /><br />";
				echo '<strong>Active Plugins:</strong><br />';
				echo '<ul>';

				foreach ( $active_plugins as $key => $value ) {
					$string = explode( '/', $value ); // Folder name will be displayed
					echo '<li>' . $string[ 0 ] . '</li>';
				}

				echo '</ul>';

				echo '<br /><br /><strong>Server Details:</strong><br />';

				$this->server_details();

				$message = ob_get_clean();

				$prod_id = str_replace( ' ', '', parent::PROD_ID );
				preg_match_all( '#([A-Z]+)#', $prod_id, $prod_id_only_uppercase );

				if ( wp_mail( 'myles@smyl.es', '[ ' . implode( '', $prod_id_only_uppercase[ 0 ] ) . ' ' . parent::VERSION . 'BUG ] ' . $description, $message, $headers, $attachments ) ) {

					$response[ 'status' ] = 'success';

				} else {

					$response[ 'status' ] = 'error';

				}

			}

			ob_clean();

			echo json_encode( $response );

			die();

		}

		public static function output_html() {

			ob_start();
			?>

			<div id="smyles-report-bug">
				<?php wp_nonce_field( 'smyles_submit_bug', 'smyles-bug-nonce' ); ?>
				<div id="smyles-bug-toggle" data-toggle="tooltip" data-placement="left" title="<?php _e( 'Report a Bug' ); ?>">
					<i class="fa fa-lg fa-bug"></i>
			</div>
			<h4 id="smyles-bug-header"><?php _e( 'Report a Bug' ); ?></h4>
			<div id="smyles-bug-content">
				<div id="smyles-bug-alert" class="alert"></div>
				<div id="smyles-bug-spin" class="smyles-spin-wrapper"><div class="smyles-spinner"><i class="fa fa-circle-o-notch fa-3x fa-spin"></i></div></div>
				<form id="smyles-bug-form" role="form">
					<div class="form-group">
						<label for="smyles-bug-email"><?php _e( 'Email Address' ); ?></label>
						<input type="email" class="form-control" id="smyles-bug-email" placeholder="<?php _e( 'Your Email' ); ?>" required>
					</div>
					<div class="form-group">
						<label for="smyles-bug-description"><?php _e( 'Bug Description' ); ?></label>
						<input type="text" class="form-control" id="smyles-bug-description" placeholder="<?php _e( 'Short Bug Description ...' ); ?>" required>
					</div>
					<div class="form-group">
						<label for="smyles-bug-details"><?php _e( 'Bug Details' ); ?></label>
						<textarea class="form-control" id="smyles-bug-details" rows="3" placeholder="<?php _e( 'Please describe the bug, how to replicate the bug, and any other details you can add.  Links to screenshots would be helpful as well.' ); ?>" required></textarea>
					</div>
					<button id="smyles-bug-reset" type="reset" class="button button-default">Reset</button>
					<a href="#" id="smyles-bug-submit" class="button button-primary">Submit</a>
				</form>
			</div>
		</div>

			<?php
			ob_end_flush();
		}

	}
}

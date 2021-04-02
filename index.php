<?php
/*
Plugin Name: WP Tárhely Teszt
Plugin URI: https://tarhelylista.hu
Description: Szerver és WordPress sebesség teszt
Author: Viszt Péter
Version: 1.0.2
WC requires at least: 3.0.0
WC tested up to: 3.7.0
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WP_Tarhely_Teszt {
  public $benchmark_tool = null;
  private $_server_benchmark_results;
  private $_wp_benchmark_results;
  protected static $_instance = null;
  public static $version;

	//Get main instance
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

  //Construct
	public function __construct() {
    self::$version = '1.0.2';

    //Benchmark helper
    require_once( plugin_dir_path( __FILE__ ) . '/includes/class-benchmark.php' );
    $this->benchmark_tool = new WP_Tarhely_Teszt_Benchmark();

    //Plugin loaded
		add_action( 'plugins_loaded', array( $this, 'init' ) );

    //WP benchmark ajax
    add_action( 'wp_ajax_wp_hosting_test', array( $this, 'run_wp_hosting_test' ) );
    add_action( 'wp_ajax_wp_hosting_test_save', array( $this, 'run_wp_hosting_test_save' ) );
    add_action( 'wp_ajax_wp_hosting_test_delete', array( $this, 'run_wp_hosting_test_delete' ) );
    add_action( 'wp_ajax_wp_hosting_test_edit', array( $this, 'run_wp_hosting_test_edit' ) );

    //Admin JS
		add_action( 'admin_init', array( $this, 'admin_js' ) );

  }

  public function init() {
    add_action('admin_menu', array( $this, 'create_menu' ));
  }

  //Add Admin CSS & JS
  public function admin_js() {
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
    wp_enqueue_script( 'wp_hosting_test', plugins_url( '/assets/admin'.$suffix.'.js',__FILE__ ), array('jquery'), WP_Tarhely_Teszt::$version, TRUE );
    wp_enqueue_style( 'wp_hosting_test', plugins_url( '/assets/admin.css',__FILE__ ), array(), WP_Tarhely_Teszt::$version );
  }

	//Create submenu in Tools
	public function create_menu() {
    $hook = add_management_page( 'Tárhely teszt', 'Tárhely teszt', 'install_plugins', 'wp_tarhelylista_teszt', array( $this, 'generate_page_content' ) );
    add_action( "load-$hook", array( $this, 'process_page_submit' ) );
	}

  function generate_page_content() {
    include( dirname( __FILE__ ) . '/includes/views/html-benchmark.php' );
  }

  //Runs for server benchmark
  public function process_page_submit() {
    if ( ! empty( $_POST['wp_tarhelylista_teszt_server_run'] ) ) {
      check_admin_referer( 'run_test', 'wp_tarhelylista_teszt_nonce' );
      $this->_server_benchmark_results = $this->benchmark_tool->server_benchmark();
      $existing_results = get_option('wp_tarhely_teszt_results_server');
      if(!$existing_results) $existing_results = array();
      $existing_results[current_time('timestamp')] = array(
        'result' => $this->_server_benchmark_results['benchmark']['total']
      );
      update_option('wp_tarhely_teszt_results_server', $existing_results);
      add_action( 'admin_notices', array( $this, 'display_notice' ) );
    }
  }

  //Trigger wp benchmark via ajax
  public function run_wp_hosting_test() {
    check_ajax_referer( 'run_test', 'wp_tarhelylista_teszt_nonce' );
    $results = $this->benchmark_tool->wordpress_benchmark();
    wp_send_json_success($results);
  }

  //Runs when wp benchmark finished
  public function run_wp_hosting_test_save() {
    check_ajax_referer( 'run_test', 'wp_tarhelylista_teszt_nonce' );

    //Find existing results
    $existing_results = get_option('wp_tarhely_teszt_results_wp');
    if(!$existing_results) $existing_results = array();

    //Append new data
    $existing_results[current_time('timestamp')] = array(
      'result' => sanitize_text_field($_POST['total'])
    );
    update_option('wp_tarhely_teszt_results_wp', $existing_results);

    //Return response
    wp_send_json_success($existing_results);
  }

  //Runs when result needs to be deleted
  public function run_wp_hosting_test_delete() {
    check_ajax_referer( 'run_test', 'wp_tarhelylista_teszt_nonce' );

    //Identify sample
    $meta_key = 'wp_tarhely_teszt_results_wp';
    if($_POST['type'] == 'server') $meta_key = 'wp_tarhely_teszt_results_server';

    //Find existing results
    $existing_results = get_option($meta_key);

    //Remove
    unset($existing_results[sanitize_text_field($_POST['index'])]);

    //Save
    update_option($meta_key, $existing_results);

    //Return response
    wp_send_json_success($existing_results);
  }

  //Runs when comment is made for a result
  public function run_wp_hosting_test_edit() {
    check_ajax_referer( 'run_test', 'wp_tarhelylista_teszt_nonce' );

    //Identify sample
    $meta_key = 'wp_tarhely_teszt_results_wp';
    if($_POST['type'] == 'server') $meta_key = 'wp_tarhely_teszt_results_server';

    //Find existing results
    $existing_results = get_option($meta_key);

    //Save note
    $existing_results[sanitize_text_field($_POST['index'])]['note'] = sanitize_text_field($_POST['note']);

    //Save
    update_option($meta_key, $existing_results);

    //Return response
    wp_send_json_success($existing_results);
  }

  public function display_notice() {
    ?>
		<div class="notice notice-success is-dismissible">
			<p>A teszt sikeresen lefutott, a lenti táblázatban látod a részletes eredményeket.</p>
		</div>
		<?php
  }

}

function WP_Tarhely_Teszt() {
  return WP_Tarhely_Teszt::instance();
}
$GLOBALS['wp_tarhely_teszt'] = WP_Tarhely_Teszt();

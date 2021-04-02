<?php

/**
 * PHP Script to benchmark PHP and MySQL-Server
 * http://odan.github.io/benchmark-php/
 *
 * inspired by / thanks to:
 * - www.php-benchmark-script.com  (Alessandro Torrisi)
 * - www.webdesign-informatik.de
 *
 * @author odan
 * @license MIT
 */

/* Modified version for the wp-tarhelylista-test wordpress plugin */

// -----------------------------------------------------------------------------
// Setup
// -----------------------------------------------------------------------------
set_time_limit(120); // 2 minutes

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_Tarhelylista_Teszt_Benchmark', false ) ) :

	class WP_Tarhelylista_Teszt_Benchmark {
		public $test_limit_1 = 500000;
		public $test_limit_2 = 10000000;
		public $test_limit_3 = 500;

    public function server_benchmark() {

      //Setup Wordpress database details
      $settings = array();
      $settings['db.host'] = DB_HOST;
      $settings['db.user'] = DB_USER;
      $settings['db.pw'] = DB_PASSWORD;
      $settings['db.name'] = DB_NAME;

      $result = array();
      $result['version'] = '1.4';
      $result['sysinfo']['time'] = date('Y-m-d H:i:s');
      $result['sysinfo']['php_version'] = PHP_VERSION;
      $result['sysinfo']['platform'] = PHP_OS;
      $result['sysinfo']['server_name'] = $_SERVER['SERVER_NAME'];
      $result['sysinfo']['server_addr'] = $_SERVER['SERVER_ADDR'];
      $result['sysinfo']['xdebug'] = in_array('xdebug', get_loaded_extensions());

      $timeStart = microtime(true);

      $this->test_math($result, $this->test_limit_1);
      $this->test_string($result, $this->test_limit_1);
      $this->test_loops($result, $this->test_limit_2);
      $this->test_ifelse($result, $this->test_limit_2);

      $result['benchmark']['calculation_total'] = $this->timer_diff($timeStart);

      if (isset($settings['db.host'])) {
        $this->test_mysql($result, $settings);
      }

      $result['benchmark']['total'] = $this->timer_diff($timeStart);

      return $result;
    }

    public function test_math(&$result, $count) {
      $timeStart = microtime(true);

      $mathFunctions = array("abs", "acos", "asin", "atan", "bindec", "floor", "exp", "sin", "tan", "pi", "is_finite", "is_nan", "sqrt");
      for ($i = 0; $i < $count; $i++) {
        foreach ($mathFunctions as $function) {
          call_user_func_array($function, array($i));
        }
      }
      $result['benchmark']['math'] = $this->timer_diff($timeStart);
    }

    public function test_string(&$result, $count) {
      $timeStart = microtime(true);
      $stringFunctions = array("addslashes", "chunk_split", "metaphone", "strip_tags", "md5", "sha1", "strtoupper", "strtolower", "strrev", "strlen", "soundex", "ord");

      $string = 'the quick brown fox jumps over the lazy dog';
      for ($i = 0; $i < $count; $i++) {
        foreach ($stringFunctions as $function) {
          call_user_func_array($function, array($string));
        }
      }
      $result['benchmark']['string'] = $this->timer_diff($timeStart);
    }

    public function test_loops(&$result, $count) {
      $timeStart = microtime(true);
      for ($i = 0; $i < $count; ++$i) {

      }

      $i = 0;
      while ($i < $count) {
        ++$i;
      }

      $result['benchmark']['loops'] = $this->timer_diff($timeStart);
    }

    public function test_ifelse(&$result, $count) {
      $timeStart = microtime(true);
      for ($i = 0; $i < $count; $i++) {
        if ($i == -1) {

        } elseif ($i == -2) {

        } else {
          if ($i == -3) {

          }
        }
      }
      $result['benchmark']['ifelse'] = $this->timer_diff($timeStart);
    }

    public function test_mysql(&$result, $settings) {
      $timeStart = microtime(true);

      $link = mysqli_connect($settings['db.host'], $settings['db.user'], $settings['db.pw']);
      $result['benchmark']['mysql_connect'] = $this->timer_diff($timeStart);

      mysqli_select_db($link, $settings['db.name']);
      $result['benchmark']['mysql_select_db'] = $this->timer_diff($timeStart);

      $dbResult = mysqli_query($link, 'SELECT VERSION() as version;');
      $arr_row = mysqli_fetch_array($dbResult);
      $result['sysinfo']['mysql_version'] = $arr_row['version'];
      $result['benchmark']['mysql_query_version'] = $this->timer_diff($timeStart);

      $query = "SELECT BENCHMARK($this->test_limit_2, AES_ENCRYPT('hello', UNHEX('F3229A0B371ED2D9441B830D21A390C3')));";
      mysqli_query($link, $query);
      $result['benchmark']['mysql_query_benchmark'] = $this->timer_diff($timeStart);

      mysqli_close($link);

      $result['benchmark']['mysql_total'] = $this->timer_diff($timeStart);

      return $result;
    }

    public function wordpress_benchmark(){

      //start timing wordpress mysql functions
      $time_start = microtime(true);

      //Create some random posts, update, read and delete them
      $count = $this->test_limit_3;
      for($x=0; $x<$count;$x++){

        //Create random content
        $content = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.';

        //insert
        $post_id = wp_insert_post(array('post_title'=>'wp_tarhelylista_teszt_'.$x, 'post_content'=> $content));

        //update
        $post_id = wp_update_post(array(
          'ID'           => $post_id,
          'post_title'   => 'wp_tarhelylista_teszt_update_'.$x,
          'post_content' => $content.'. Updated.',
        ));

        //Get the post
        $post = get_post($post_id);

        //Delete the post
        $deleted = wp_delete_post($post->ID, true);
      }

      //Test WPDB speed by creating, reading, updateding and deleting in the options table
      global $wpdb;
      $table = $wpdb->prefix . 'options';
      $optionname = 'wp_tarhelylista_teszt_';
      $count = $this->test_limit_3;
      for($x=0; $x<$count;$x++){
        //insert
        $data = array('option_name' => $optionname . $x, 'option_value' => wp_generate_password(100));
        $wpdb->insert($table, $data);

        //select
        $select = "SELECT option_value FROM $table WHERE option_name='$optionname" . $x . "'";
        $wpdb->get_var($select);

        //update
        $data = array('option_value' => wp_generate_password(100));
        $where =  array('option_name' => $optionname . $x);
        $wpdb->update($table, $data, $where);

        //delete
        $where = array('option_name' => $optionname.$x);
        $wpdb->delete($table,$where);
      }

      //Sum up the results
      $time = $this->timer_diff($time_start);
      $queries = ($count * 2 * 8) / $time;
      return array('time'=>$time, 'operations'=>$queries);
    }

    public function timer_diff($timeStart) {
      return number_format(microtime(true) - $timeStart, 3);
    }

  }

endif;

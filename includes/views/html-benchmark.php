<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap wp-hosting-test">
  <h1>Tárhely Teszt</h1>
  <p class="wp-hosting-test-subtitle">Ez a bővítmény ellenőrizni fogja különböző műveletek futtatását a szervereden és meghatároz egy futási időt. Kattints a gombra a teszt futtatásához. A teszt akár egy percig is eltarhat, légy türelemmel.</p>
  <form method="post">
    <?php wp_nonce_field( 'run_test', 'wp_tarhelylista_teszt_nonce' ); ?>
    <div style="display:flex">
      <?php submit_button( 'Szerver teszt futtatása', 'primary', 'wp_tarhelylista_teszt_server_run' ); ?>
      <div style="width:20px"></div>
      <?php submit_button( 'Wordpress teszt futtatása', 'primary', 'wp_tarhelylista_teszt_wp_run' ); ?>
    </div>
  </form>

  <ul class="wp-hosting-test-status" style="display:none">
    <li><span class="spinner is-active"></span> <span>Teszt 1</span></li>
    <li><span class="spinner is-active"></span> Teszt 2</li>
    <li><span class="spinner is-active"></span> Teszt 3</li>
    <li><span class="spinner is-active"></span> Teszt 4</li>
    <li><span class="spinner is-active"></span> Teszt 5</li>
  </ul>

  <?php if($this->_server_benchmark_results): ?>
  <table class="widefat fixed" cellspacing="0">
    <thead>
      <tr>
        <th>Teszt</th>
        <th>Futási idő (másodperc)</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>Math</td>
        <td><?php echo $this->_server_benchmark_results['benchmark']['math']; ?></td>
      </tr>
      <tr>
        <td>String Manipulation</td>
        <td><?php echo $this->_server_benchmark_results['benchmark']['string']; ?></td>
      </tr>
      <tr>
        <td>Loops</td>
        <td><?php echo $this->_server_benchmark_results['benchmark']['loops']; ?></td>
      </tr>
      <tr>
        <td>Conditionals</td>
        <td><?php echo $this->_server_benchmark_results['benchmark']['ifelse']; ?></td>
      </tr>
      <tr>
        <td>Mysql Connect</td>
        <td><?php echo $this->_server_benchmark_results['benchmark']['mysql_connect']; ?></td>
      </tr>
      <tr>
        <td>Mysql Select Database</td>
        <td><?php echo $this->_server_benchmark_results['benchmark']['mysql_select_db']; ?></td>
      </tr>
      <tr>
        <td>Mysql Query Version</td>
        <td><?php echo $this->_server_benchmark_results['benchmark']['mysql_query_version']; ?></td>
      </tr>
      <tr>
        <td>Mysql Query Benchmark</td>
        <td><?php echo $this->_server_benchmark_results['benchmark']['mysql_query_benchmark']; ?></td>
      </tr>
    </tbody>
    <tfoot>
      <tr>
        <th>Futási idő összesen (másodperc)</th>
        <th><?php echo $this->_server_benchmark_results['benchmark']['total']; ?></th>
      </tr>
    </tfoot>
  </table>
  <?php endif; ?>

  <?php if(get_option('wp_tarhely_teszt_results_server') || get_option('wp_tarhely_teszt_results_wp')): ?>
    <?php if($server_results = get_option('wp_tarhely_teszt_results_server')): ?>
    <table class="widefat results" style="margin: 0 0 20px 0" cellspacing="0">
      <thead>
        <tr>
          <th><strong>Szerver teszt eredmények</strong></th>
          <th>Futási idő(másodperc)</th>
          <th>Megjegyzés</th>
          <th></th>
        </tr>
      </thead>
      <tfoot>
          <?php foreach ($server_results as $time => $result): ?>
            <tr>
              <td><?php echo date_i18n(get_option('date_format').' '.get_option('time_format'), $time); ?></td>
              <td><?php echo esc_html($result['result']); ?></td>
              <td class="note"><?php if(isset($result['note'])) { echo esc_html($result['note']); }; ?></td>
              <td>
                <a href="#" class="delete-result" data-type="server" data-index="<?php echo esc_attr($time); ?>"><span class="dashicons dashicons-dismiss"></span></a>
                <a href="#" class="add-note" data-type="server" data-index="<?php echo esc_attr($time); ?>"><span class="dashicons dashicons-edit"></span></a>
              </td>
            </tr>
          <?php endforeach; ?>
      </tfoot>
    </table>
    <?php endif; ?>

    <?php if($wp_results = get_option('wp_tarhely_teszt_results_wp')): ?>
    <table class="widefat results" cellspacing="0">
      <thead>
        <tr>
          <th><strong>WordPress teszt eredmények</strong></th>
          <th>Futási idő(másodperc)</th>
          <th>Megjegyzés</th>
          <th></th>
        </tr>
      </thead>
      <tfoot>
        <?php foreach ($wp_results as $time => $result): ?>
          <tr>
            <td><?php echo date_i18n(get_option('date_format').' '.get_option('time_format'), $time); ?></td>
            <td><?php echo esc_html($result['result']); ?></td>
            <td class="note"><?php if(isset($result['note'])) { echo esc_html($result['note']); }; ?></td>
            <td>
              <a href="#" class="delete-result" data-type="wp" data-index="<?php echo esc_attr($time); ?>"><span class="dashicons dashicons-dismiss"></span></a>
              <a href="#" class="add-note" data-type="wp" data-index="<?php echo esc_attr($time); ?>"><span class="dashicons dashicons-edit"></span></a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tfoot>
    </table>
    <?php endif; ?>
  <?php endif; ?>
</div>

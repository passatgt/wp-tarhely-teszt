jQuery(document).ready(function($) {

  var benchmark_runs = 0;
  var results = [];

  $('#wp_tarhelylista_teszt_server_run').click(function(){
    $('.wp-hosting-test').addClass('loading');
  });

  $('#wp_tarhelylista_teszt_wp_run').click(function(){
    $('.wp-hosting-test').addClass('loading');

    //Reset data
    benchmark_runs = 0;
    results = [];

    $('.wp-hosting-test-status').show();

    //Start benchmark loop
    run_benchmark()

    return false;
  });

  function run_benchmark() {
    var nonce = $('#wp_tarhelylista_teszt_nonce').val();
    $.post(ajaxurl, {'action':'wp_hosting_test', 'wp_tarhelylista_teszt_nonce': nonce}, function(response) {

      //Loading indicator
      $('.wp-hosting-test-status li').eq(benchmark_runs).addClass('done');

      //Count runs
      benchmark_runs++;

      //Save results
      if(response.success) {
        results.push(response.data);
      }

      //Start again or end
      if(benchmark_runs == 5) {
        benchmark_runs = 0;
        save_benchmark_result();
      } else {
        run_benchmark();
      }
    }).fail(function(response) {
      $('.wp-hosting-test-status').hide();
      alert('Error: ' + response.responseText);
    });
  }

  function save_benchmark_result() {
    var nonce = $('#wp_tarhelylista_teszt_nonce').val();
    var total = 0;
    results.forEach(function(result){
      total += parseFloat(result.time);
    });

    $.post(ajaxurl, {'action':'wp_hosting_test_save', 'wp_tarhelylista_teszt_nonce': nonce, 'total': total.toFixed(2)}, function(response) {
      window.location.reload();
      $('.wp-hosting-test-status').hide();
    });
  }

  $('.wp-hosting-test .delete-result').click(function(){
    var nonce = $('#wp_tarhelylista_teszt_nonce').val();
    var index = $(this).data('index');
    var type = $(this).data('type');
    var $link = $(this);
    var $row = $(this).parents('tr');

    $.post(ajaxurl, {'action':'wp_hosting_test_delete', 'wp_tarhelylista_teszt_nonce': nonce, 'index': index, 'type': type}, function(response) {
      $row.slideUp();
    });

    return false;
  });

  $('.wp-hosting-test .add-note').click(function(){
    var nonce = $('#wp_tarhelylista_teszt_nonce').val();
    var index = $(this).data('index');
    var type = $(this).data('type');
    var $link = $(this);
    var $row = $(this).parents('tr');

    var note = prompt('Megjegyzés hozzáadása');
    if(note !== null) {
      $.post(ajaxurl, {'action':'wp_hosting_test_edit', 'wp_tarhelylista_teszt_nonce': nonce, 'index': index, 'type': type, 'note': note}, function(response) {
        $row.find('.note').text(note);
      });
    }

    return false;
  });

});

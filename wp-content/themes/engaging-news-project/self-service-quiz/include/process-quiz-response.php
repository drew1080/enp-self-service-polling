<?php
include('../../../../../wp-config.php');
global $wpdb;

if(isset($_POST['input-id'])) {
  
  $date = date('Y-m-d H:i:s');
  $guid = $_POST['input-guid'];
  $quiz_type = $_POST['quiz-type'];
  $response_id = 0;
  $preview_response = $_POST['preview'] ? 1 : 0;
  
  $quiz = $wpdb->get_row("
    SELECT * FROM enp_quiz 
    WHERE guid = '" . $guid . "' ");

  if ( $quiz_type == 'multiple-choice' ) {
    $response_id = processMCResponse($date, $quiz->ID, $preview_response, $wpdb);
  } else {
    $response_id = processSliderResponse($date, $quiz->ID, $preview_response, $wpdb);
  }
  
  if ( !$quiz->locked && !$preview_response ) {
    lockQuiz($quiz->ID, $wpdb);
  }
  
  header("Location: " . get_site_url() . "/quiz-answer/?response_id=" . $response_id . "&guid=" . $guid);
}

function lockQuiz($quiz_id, $wpdb) {
  $wpdb->update( 
  	'enp_quiz', 
  	array( 
  		'locked' => 1
  	), 
  	array( 'ID' => $quiz_id ), 
  	array( 
  		'%d'	// value1
  	), 
  	array( '%d' ) 
  );
}

function processMCResponse($date, $quiz_id, $preview_response, $wpdb) {
  $quiz_answer_id = $_POST['mc-radio-answers'];
  $quiz_answer_value = $_POST['option-radio-id-' . $quiz_answer_id];
  $is_correct = 0;
      
  $correct_option_id = $wpdb->get_var("
    SELECT value FROM enp_quiz_options
    WHERE field = 'correct_option' AND
    quiz_id = " . $quiz_id);
    
  $correct_option_value = $wpdb->get_var("
    SELECT value FROM enp_quiz_options
    WHERE field = 'answer_option' AND
    id = " . $correct_option_id);
  
  if ( $correct_option_id == $quiz_answer_id ) {
    $is_correct = 1;
  }

  $wpdb->insert( 'enp_quiz_responses', 
  array( 'quiz_id' => $quiz_id , 'quiz_option_id' => $quiz_answer_id, 'quiz_option_value' => $quiz_answer_value, 
    'correct_option_id' => $correct_option_id, 'correct_option_value' => $correct_option_value, 
    'is_correct' => $is_correct, 'ip_address' => $_SERVER['REMOTE_ADDR'], 'response_datetime' => $date, 
    'preview_response' => $preview_response ));
    
  return $wpdb->insert_id;
}

function processSliderResponse($date, $quiz_id, $preview_response, $wpdb) {
  $is_correct = 0;
  
  $slider_value = $_POST['slider-value'];
  $slider_high_answer = $_POST['slider-high-answer'];
  $slider_low_answer = $_POST['slider-low-answer'];
  $correct_option_value = $slider_low_answer . ' to ' . $slider_high_answer;
  
  //TODO more scenario's
  if ( $slider_value <= $slider_high_answer && $slider_value >= $slider_low_answer ) {
    $is_correct = 1;
  }

  $wpdb->insert( 'enp_quiz_responses', 
  array( 'quiz_id' => $quiz_id , 'quiz_option_id' => -2, 'quiz_option_value' => $slider_value, 
    'correct_option_id' => -2, 'correct_option_value' => $correct_option_value, 
    'is_correct' => $is_correct, 'ip_address' => $_SERVER['REMOTE_ADDR'], 'response_datetime' => $date, 
    'preview_response' => $preview_response ));
    
  return $wpdb->insert_id;
}
?>
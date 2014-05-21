<?php

	add_shortcode('list_quizzes', 'list_quizzes_handler');

	function list_quizzes_handler($atts, $content=null, $code="") {
    global $wpdb;
    $user_ID = get_current_user_id(); 

    if ( $user_ID && $_GET["delete_guid"] ) {
      $wpdb->delete( 'enp_quiz', array( 'guid' => $_GET["delete_guid"] ) );
      $quiz_notifications =  "
        <div class='bootstrap'>
          <div class='alert alert-success alert-dismissable'>
            <span class='glyphicon glyphicon-info-sign'></span> Quiz successfully deleted.
            <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
          </div>
          <div class='clear'></div>
        </div>";
    }
    ?>
    <?php
    if ( $user_ID ) {
       echo $quiz_notifications;
       $quizzes= $wpdb->get_results( 
         "
         SELECT * 
         FROM enp_quiz
         WHERE user_id = " . $user_ID . "
         ORDER BY last_modified_datetime DESC"
       );
    ?>
      <h1>My Quizzes</h1>
      <div class="bootstrap">
        <p><a href="configure-quiz/" class="btn btn-primary btn-sm active" role="button">New quiz</a></p>
        <?php
        if ( $quizzes ) {
        ?>
        <div class="panel panel-info">
          <!-- Default panel contents -->
          <div class="panel-heading">My Quizzes</div>
          <div class='table-responsive'>
            <table class='table'>
              <thead><tr>
                <th>#</th>
                <th>Title</th>
                <th>Type</th>
                <th>Unique Views</th>
                <th>Correct Responses</th>
                <th>Percentage Answering</th>
                <th></th>
                <th></th>
              </tr></thead>
          <?php
          foreach ( $quizzes as $quiz )
          {
            $wpdb->get_var( 
              "
              SELECT ip_address
              FROM enp_quiz_responses   
              WHERE 
              #preview_response = false AND 
              " . $ignored_ip_sql . "
              quiz_id = " . $quiz->ID . 
              " GROUP BY ip_address"
            );

            $unique_view_count = $wpdb->num_rows;
          
            $correct_response_count = $wpdb->get_var( 
              "
              SELECT COUNT(*) 
              FROM enp_quiz_responses
              WHERE 
              #preview_response = false AND 
              " . $ignored_ip_sql . "
              is_correct = 1 AND quiz_id = " . $quiz->ID
            );
          
            $quiz_total_view_count = $wpdb->get_var( 
              "
              SELECT COUNT(*) 
              FROM enp_quiz_responses
              WHERE 
              #preview_response = false AND 
              " . $ignored_ip_sql . "
              correct_option_value = 'quiz-viewed-by-user' AND quiz_id = " . $quiz->ID
            );
            
            $wpdb->get_var( 
              "
              SELECT ip_address
              FROM enp_quiz_responses   
              WHERE 
              #preview_response = false AND 
              " . $ignored_ip_sql . "
              correct_option_value != 'quiz-viewed-by-user' 
              AND quiz_id = " . $quiz->ID . 
              " GROUP BY ip_address"
            );

            $unique_answer_count = $wpdb->num_rows;
            
            $percent_answering = $unique_answer_count > 0 ? 
              ROUND($unique_view_count/$unique_answer_count*100, 2) : 0;
            ?>
            <tr>
              <td><?php echo $quiz->ID; ?></td>
              <td><a href="view-quiz?guid=<?php echo $quiz->guid ?>"><?php echo esc_attr($quiz->title); ?></a></td>
              <td><?php echo $quiz->quiz_type == "slider" ? "Slider" : "Multiple Choice"; ?></td>
              <td><a href="quiz-report/?guid=<?php echo $quiz->guid ?>" class="btn btn-warning btn-xs active" role="button"><?php echo $unique_view_count; ?></a></td>
              <td><a href="quiz-report/?guid=<?php echo $quiz->guid ?>" class="btn btn-warning btn-xs active" role="button"><?php echo $correct_response_count; ?></a></td>
              <td><a href="quiz-report/?guid=<?php echo $quiz->guid ?>" class="btn btn-warning btn-xs active" role="button"><?php echo $percent_answering; ?>%</a></td>
              <?php //if ( !$quiz->locked ) { ?>
                <td><a href="configure-quiz/?edit_guid=<?php echo $quiz->guid ?>" class="btn btn-info btn-xs active" role="button">Edit</a></td>
              <?php //} else { ?>
                <!-- <td>Locked -->
                  <!-- <span class="glyphicon glyphicon-ban-circle" data-toggle="tooltip" data-placement="top" title="This quiz is locked from editing."></span> -->
                <!-- </td> -->
              <?php //} ?>
              <td><a href="list-quizzes/?delete_guid=<?php echo $quiz->guid ?>" onclick="return confirm('Are you sure you want to delete this quiz?')" class="btn btn-danger btn-xs active" role="button">Delete</a></td>
            </tr>
            <?php
          }  
      
          echo "</table>";
          echo "</div>";
          echo "</div>";
        }
        else
        {
          ?>
          <p>Welcome!  Click <i><a href="configure-quiz/">New quiz</a></i> to get started!</p>
          <p>Once you click to start, the tool will ask you a few simple questions to help you create your first quiz!</p>
          <?php
        }
        ?>
        </div>
    
        <?php
        } else {
          the_content();
        ?>
          <!-- <p>Please login or <a href="/wp-login.php?action=register">register</a> to start creating quizzes!</p> -->
        <?php } 
    
		return '';
		
	}
  
	add_shortcode('configure_quiz', 'configure_quiz_handler');

	function configure_quiz_handler($atts, $content=null, $code="") {
    $user_ID = get_current_user_id(); 
    
    if ( $user_ID ) { ?>
      
  		<?php get_template_part('self-service-quiz/quiz-form', 'page'); ?>      
    <?php
    } else {
    ?>
      <p>Please login to start creating quizzes!</p>
    <?php }
  }
  
	add_shortcode('view_quiz', 'view_quiz_handler');

	function view_quiz_handler($atts, $content=null, $code="") {
    global $wpdb;
    $user_ID = get_current_user_id(); 
    
    if ( $user_ID ) {
      
    get_template_part('includes/breadcrumbs', 'page');
    if ( $_GET["quiz_updated"] ) {
      if ( $_GET["quiz_updated"] == 1 ) {
        $quiz_notifications =  "
          <div class='bootstrap'>
            <div class='alert alert-success alert-dismissable'>
              <span class='glyphicon glyphicon-info-sign'></span> Quiz successfully updated.
              <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
            </div>
            <div class='clear'></div>
          </div>";
      }
    }
    
    if ( $_GET["guid"] ) {
      $quiz = $wpdb->get_row("
        SELECT * FROM enp_quiz 
        WHERE guid = '" . $_GET["guid"] . "' ");
        
      $quiz_created_date = new DateTime($quiz->create_datetime);
      
      if ( $quiz->quiz_type == "multiple-choice" ) {
        $correct_answer = $wpdb->get_var("
          SELECT poa.value 'correct_answer'
          FROM enp_quiz_options poa
          INNER JOIN enp_quiz_options po ON po.value = poa.ID
          INNER JOIN enp_quiz p ON p.ID = po.quiz_id
          WHERE po.field = 'correct_option' AND p.guid = '" . $_GET["guid"] . "' ");
        
      } else {
        $slider_answers = $wpdb->get_row("
          SELECT qo_high.value 'high_answer', qo_low.value 'low_answer'
          FROM enp_quiz_options qo
          INNER JOIN enp_quiz_options qo_high ON qo_high.quiz_id = qo.quiz_id AND qo_high.field = 'slider_high_answer'
          INNER JOIN enp_quiz_options qo_low ON qo_low.quiz_id = qo.quiz_id AND qo_low.field = 'slider_low_answer'
          WHERE qo.quiz_id = " . $quiz->ID . "
          GROUP BY qo.quiz_id" );
        
        if ( $slider_answers->high_answer == $slider_answers->low_answer ) {
          $correct_answer = $slider_answers->low_answer;
        } else {
          $correct_answer = $slider_answers->low_answer . ' to ' . $slider_answers->high_answer;
        }
      }
      
      $quiz_display_width = $wpdb->get_var("
        SELECT value FROM enp_quiz_options
        WHERE field = 'quiz_display_width' AND quiz_id = " . $quiz->ID);
        
      $quiz_display_height = $wpdb->get_var("
        SELECT value FROM enp_quiz_options
        WHERE field = 'quiz_display_height' AND quiz_id = " . $quiz->ID);
    }
    
    echo $quiz_notifications;

    $iframe_url = get_site_url() . '/iframe-quiz/?guid=' . $_GET["guid"];
    ?>

    <h1>Quiz: <b><?php echo esc_attr($quiz->title); ?></b></h1>
    <?php 
    // Removing lock feature...remove permanently after more feedback
    //if ( !$quiz->locked ) {
    if ( true ) {
    ?>
      <span class="bootstrap top-edit-button"><a href="configure-quiz/?edit_guid=<?php echo $_GET["guid"] ?>" class="btn btn-info active" role="button">Edit Quiz</a></span>
    <?php } else { ?>
      <span class="bootstrap top-edit-button"><div class="alert alert-warning">Quiz locked from editing.</div></span>
    <?php } ?>
    <h4>Created <?php echo $quiz_created_date->format('m.d.Y'); ?></h4>
    <!-- <span class="bootstrap"><hr></span> -->
    <!-- <h3>Preview Quiz</h3>
    <span class="bootstrap"><hr></span> -->
    <div class="bootstrap">
      <div class="panel panel-info">
        <div class="panel-heading">Preview Quiz</div>
        <div class="panel-body preview-quiz">
          <?php //get_template_part('self-service-quiz/quiz-display', 'page'); ?>
          <?php echo '<iframe frameBorder="0" height="' . $quiz_display_height
           . '" width="' . $quiz_display_width . '" src="' . $iframe_url . '&amp;preview=true"></iframe>';  ?>
          <div class="form-group">
            <div class="clear"></div>
          </div>
          <div class="well"><span><b>Correct Answer</b>: <i><?php echo $correct_answer ?></i></span></div>
          <div class="well">
            <h4>Styling Suggestions</h4>
            <span><b>Scrolling</b>: If the quiz has scroll bars, consider changing the quiz content or adjusting the height and width from the edit page.</span>
            <?php 
            if ( $quiz->quiz_type == "slider" ) {
            ?>
            <br/>
            <span><b>Slider labels</b>: If the quiz slider labels are overlapping, consider changing the quiz labels or adjusting the width from the edit page.</span>
            <?php } ?>
          </div>
        </div>
      </div>
      <div class="clear"></div>
      <div class="panel panel-info">
        <div class="panel-heading">iframe Markup</div>
        <div class="panel-body">
          <p>Copy and paste this markup into your target website.  <a href="<?php echo $iframe_url ?>&amp;preview=true" target="_blank">Preview iframe</a>.</p>
    	    <div class="form-group">
            <textarea class="form-control" id="quiz-iframe-code" rows="5"><?php echo '<iframe frameBorder="0" height="' . $quiz_display_height . '" width="' . $quiz_display_width . '" src="' . $iframe_url . '"></iframe>' ?></textarea>
          </div>
          <div class="clear"></div>
        </div>
      </div>
	    <div class="form-group">
        <p>
          <?php //if ( !$quiz->locked ) { ?>
            <a href="configure-quiz/?edit_guid=<?php echo $_GET["guid"] ?>" class="btn btn-info btn-sm active" role="button">Edit Quiz</a> | 
          <?php //} ?>
          <a href="list-quizzes/?delete_guid=<?php echo $_GET["guid"] ?>" onclick="return confirm('Are you sure you want to delete this quiz?')" class="btn btn-danger btn-sm  active" role="button">Delete Quiz</a>  | <a href="quiz-report/?guid=<?php echo $_GET["guid"] ?>" class="btn btn-primary btn-sm active" role="button">Quiz Report</a></p>
        <p><a href="configure-quiz" class="btn btn-info btn-xs active" role="button">New Quiz</a> | <a href="list-quizzes/" class="btn btn-primary btn-xs active" role="button">Back to Quizzes</a></p>
      </div>
    </div>
    
    <?php
    } else {
    ?>
      <p>Please login to start creating quizzes!</p>
    <?php }
  }
  
	add_shortcode('iframe_quiz', 'iframe_quiz_handler');

	function iframe_quiz_handler($atts, $content=null, $code="") {
    global $wpdb;
    // TODO do we not need the whole page-iframe-quiz, with the simplified header?
    get_template_part('self-service-quiz/quiz-display', 'page');
  }
  
	add_shortcode('quiz_answer', 'quiz_answer_handler');

	function quiz_answer_handler($atts, $content=null, $code="") {
    global $wpdb;
    // TODO do we not need the whole page-quiz-answer, with the simplified header?
      $quiz = $wpdb->get_row("
        SELECT * FROM enp_quiz 
        WHERE guid = '" . $_GET["guid"] . "' ");
  
      $quiz_background_color = $wpdb->get_var("
        SELECT value FROM enp_quiz_options
        WHERE field = 'quiz_background_color' AND quiz_id = " . $quiz->ID);
    
      $quiz_text_color = $wpdb->get_var("
        SELECT value FROM enp_quiz_options
        WHERE field = 'quiz_text_color' AND quiz_id = " . $quiz->ID);
    
      $quiz_display_border = $wpdb->get_var("
        SELECT value FROM enp_quiz_options
        WHERE field = 'quiz_display_border' AND quiz_id = " . $quiz->ID);
  
      $quiz_display_width = $wpdb->get_var("
        SELECT value FROM enp_quiz_options
        WHERE field = 'quiz_display_width' AND quiz_id = " . $quiz->ID);
    
      $quiz_display_padding = $wpdb->get_var("
        SELECT value FROM enp_quiz_options
        WHERE field = 'quiz_display_padding' AND quiz_id = " . $quiz->ID);
    
      $quiz_show_title = $wpdb->get_var("
        SELECT value FROM enp_quiz_options
        WHERE field = 'quiz_show_title' AND quiz_id = " . $quiz->ID);
    ?>

  <div style="background:<?php echo $quiz_background_color ;?>;color:<?php echo $quiz_text_color ;?>;width:<?php echo $quiz_display_width ;?>;padding:<?php echo $quiz_display_padding ;?>;border:<?php echo $quiz_display_border ;?>;" class="bootstrap quiz-answer">
      <?php 
      $quiz_response = $wpdb->get_row("SELECT * FROM enp_quiz_responses WHERE ID = " . $_GET["response_id"] ); 

      $exact_value = false;
      $display_answer = $quiz_response->correct_option_value;
  
      if ( $quiz->quiz_type == "multiple-choice" ) {
        $mc_options = $wpdb->get_row("
          SELECT correct.value 'correct_answer_message', incorrect.value 'incorrect_answer_message'
          FROM enp_quiz_options po
          LEFT OUTER JOIN enp_quiz_options correct ON correct.field = 'correct_answer_message' AND po.quiz_id = correct.quiz_id
          LEFT OUTER JOIN enp_quiz_options incorrect ON incorrect.field = 'incorrect_answer_message' AND po.quiz_id = incorrect.quiz_id
          WHERE po.quiz_id = " . $quiz->ID . "
          GROUP BY po.quiz_id;");
      } else if ( $quiz->quiz_type == "slider" ) {
      
        $answer_array = explode(' to ', $quiz_response->correct_option_value);
      
        if ( $answer_array[0] == $answer_array[1] ) {
          $exact_value = true;
          $display_answer = $answer_array[0];
        }
      
        $wpdb->query('SET OPTION SQL_BIG_SELECTS = 1');
        $slider_options = $wpdb->get_row("
          SELECT po_high_answer.value 'slider_high_answer', po_low_answer.value 'slider_low_answer', po_correct_answer.value 'slider_correct_answer', po_correct_message.value 'correct_answer_message', po_incorrect_message.value 'incorrect_answer_message'
          FROM enp_quiz_options po
          LEFT OUTER JOIN enp_quiz_options po_high_answer ON po_high_answer.field = 'slider_high_answer' AND po.quiz_id = po_high_answer.quiz_id
          LEFT OUTER JOIN enp_quiz_options po_low_answer ON po_low_answer.field = 'slider_low_answer' AND po.quiz_id = po_low_answer.quiz_id
          LEFT OUTER JOIN enp_quiz_options po_correct_answer ON po_correct_answer.field = 'slider_correct_answer' AND po.quiz_id = po_correct_answer.quiz_id
          LEFT OUTER JOIN enp_quiz_options po_correct_message ON po_correct_message.field = 'correct_answer_message' AND po.quiz_id = po_correct_message.quiz_id
          LEFT OUTER JOIN enp_quiz_options po_incorrect_message ON po_incorrect_message.field = 'incorrect_answer_message' AND po.quiz_id = po_incorrect_message.quiz_id
          WHERE po.quiz_id = " . $quiz->ID . "
          GROUP BY po.quiz_id;");
      } else {
        $exact_value = true;
      }
      ?>
      <div class="col-sm-12">
          <?php 
          $is_correct = $quiz_response->is_correct;
          $correct_option_id = $quiz_response->correct_option_id; 
          $quiz_response_option_value = $quiz_response->quiz_option_value;
          $question_text = esc_attr($quiz->question);
        
          if ( $quiz->quiz_type == "multiple-choice" ) {
            $correct_answer_message = $mc_options->correct_answer_message; 
            $incorrect_answer_message = $mc_options->incorrect_answer_message;
          
            if ( $is_correct ) {
              $correct_answer_message = str_replace('[user_answer]', $display_answer, $correct_answer_message);
              $correct_answer_message = str_replace('[correct_value]', $display_answer, $correct_answer_message);
            } else {
              $incorrect_answer_message = str_replace('[user_answer]', $quiz_response_option_value, $incorrect_answer_message);
              $incorrect_answer_message = str_replace('[correct_value]', $display_answer, $incorrect_answer_message);
            }
          } else if ( $quiz->quiz_type == "slider" ) {
            if ( $is_correct ) {
              $correct_answer_message = $slider_options->correct_answer_message;
    
              $correct_answer_message = str_replace('[user_answer]',$quiz_response_option_value, $correct_answer_message);
              $correct_answer_message = str_replace('[lower_range]', $slider_options->slider_low_answer, $correct_answer_message);
              $correct_answer_message = str_replace('[upper_range]', $slider_options->slider_high_answer, $correct_answer_message);
              $correct_answer_message = str_replace('[correct_value]', $slider_options->slider_correct_answer, $correct_answer_message);
            } else {
              $incorrect_answer_message = $slider_options->incorrect_answer_message;
            
              $incorrect_answer_message = str_replace('[user_answer]', $quiz_response_option_value, $incorrect_answer_message);
              $incorrect_answer_message = str_replace('[lower_range]', $slider_options->slider_low_answer, $incorrect_answer_message);
              $incorrect_answer_message = str_replace('[upper_range]', $slider_options->slider_high_answer, $incorrect_answer_message);
              $incorrect_answer_message = str_replace('[correct_value]', $slider_options->slider_correct_answer, $incorrect_answer_message);
            }
          }
        
        
          include(locate_template('self-service-quiz/quiz-answer.php')); 
          ?>
        
          <p>Thanks for taking our quiz!  <a href="<?php echo get_site_url() . '/iframe-quiz/?guid=' . $_GET["guid"];?>" class="btn btn-info btn-xs">Return to question</a></p>
        </div>
        <div class="form-group iframe-credits">
          <div class="col-sm-12">
            <p>Built by the <a href="<?php echo get_site_url() ?>">Engaging News Project</a></p>
          </div>
        </div><?php
  }
  
	add_shortcode('quiz_report', 'quiz_report_handler');

	function quiz_report_handler($atts, $content=null, $code="") {
    global $wpdb;
    if ( $_GET["message"] && $_GET["message"] == "responses_deleted") {
      $quiz_notifications =  "
        <div class='bootstrap'>
          <div class='alert alert-success alert-dismissable'>
            <span class='glyphicon glyphicon-info-sign'></span> Quiz responses successfully deleted.
            <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
          </div>
          <div class='clear'></div>
        </div>";
    }
    
    echo $quiz_notifications;
    ?>
    
		<?php get_template_part('includes/breadcrumbs', 'page'); ?>
    <?php
    $user_ID = get_current_user_id(); 
    
    if ( $user_ID ) {
    ?>
    <?php
    
    $quiz = $wpdb->get_row("
      SELECT * FROM enp_quiz 
      WHERE guid = '" . $_GET["guid"] . "' ");
    
    $ignored_ip_list = $wpdb->get_var("
      SELECT value
      FROM enp_quiz_options
      WHERE field = 'report_ignored_ip_addresses' AND
      quiz_id = " . $quiz->ID);
      
    //echo "1: " . $ignored_ip_list . "<br>";
      
    $ignored_ip_sql = "('" . $ignored_ip_list . "";
    
    //echo "2: " . $ignored_ip_sql . "<br>";
    
    $ignored_ip_sql = str_replace(",", "','",$ignored_ip_sql);
    
    //echo "3: " . $ignored_ip_sql . "<br>";
    
    $ignored_ip_sql .= "')";
    
    //echo "4: " . $ignored_ip_sql;
    
    $ignored_ip_sql = " ip_address NOT IN " . $ignored_ip_sql . " AND ";
      
    $mc_answers = $wpdb->get_results("
      SELECT * FROM enp_quiz_options
      WHERE field = 'answer_option' AND quiz_id = " . $quiz->ID . 
      " ORDER BY `display_order`");
      
    $correct_answer_info = $wpdb->get_row("
      SELECT * FROM enp_quiz_options
      WHERE field = 'correct_option' AND
      quiz_id = " . $quiz->ID);
      
    $correct_answer_id = $correct_answer_info->ID;
    $correct_answer_value = $correct_answer_info->value;
    
    $quiz_response_count = $wpdb->get_var( 
      "
      SELECT COUNT(*) 
      FROM enp_quiz_responses
      WHERE 
      #preview_response = false AND
      " . $ignored_ip_sql . "
      correct_option_id != '-1' AND quiz_id = " . $quiz->ID
    );
    
    // USE this to get the current correct answer count 
    // WHERE is_correct = 1 AND quiz_option_id = " . $correct_answer_value . " AND quiz_id = " . $quiz->ID
    $correct_response_count = $wpdb->get_var( 
      "
      SELECT COUNT(*) 
      FROM enp_quiz_responses
      WHERE 
      #preview_response = false AND
      " . $ignored_ip_sql . "
      is_correct = 1 AND quiz_id = " . $quiz->ID
    );
  
    $quiz_total_view_count = $wpdb->get_var( 
      "
      SELECT COUNT(*) 
      FROM enp_quiz_responses
      WHERE 
      #preview_response = false AND
      " . $ignored_ip_sql . "
      correct_option_value = 'quiz-viewed-by-user' AND quiz_id = " . $quiz->ID
    );
    
    $wpdb->get_var( 
      "
      SELECT ip_address
      FROM enp_quiz_responses   
      WHERE 
      #preview_response = false AND
      " . $ignored_ip_sql . "
      quiz_id = " . $quiz->ID . 
      " GROUP BY ip_address"
    );

    $unique_view_count = $wpdb->num_rows;
    
    $wpdb->get_var( 
      "
      SELECT ip_address
      FROM enp_quiz_responses   
      WHERE
      #preview_response = false AND
      " . $ignored_ip_sql . "
      correct_option_value != 'quiz-viewed-by-user' 
      AND quiz_id = " . $quiz->ID . 
      " GROUP BY ip_address"
    );

    $unique_answer_count = $wpdb->num_rows;
    
    if ( $quiz->quiz_type == "slider") {
      $slider_options = $wpdb->get_row("
        SELECT po_high_answer.value 'slider_high_answer', po_low_answer.value 'slider_low_answer', 
          po_correct_answer.value 'slider_correct_answer'
        FROM enp_quiz_options po
        LEFT OUTER JOIN enp_quiz_options po_high_answer ON po_high_answer.field = 'slider_high_answer' AND po.quiz_id = po_high_answer.quiz_id
        LEFT OUTER JOIN enp_quiz_options po_low_answer ON po_low_answer.field = 'slider_low_answer' AND po.quiz_id = po_low_answer.quiz_id
        LEFT OUTER JOIN enp_quiz_options po_correct_answer ON po_correct_answer.field = 'slider_correct_answer' AND po.quiz_id = po_correct_answer.quiz_id
        WHERE po.quiz_id = " . $quiz->ID . "
        GROUP BY po.quiz_id;");
        
      $count_answering_above = $wpdb->get_var( 
        "SELECT COUNT(*) 
        FROM `enp_quiz_responses` 
        WHERE 
        #preview_response = false AND
        " . $ignored_ip_sql . "
        correct_option_id != '-1' 
        AND quiz_option_value > " . $slider_options->slider_high_answer . " 
        AND `quiz_id` = " . $quiz->ID
      );
      
      $count_answering_below = $wpdb->get_var( 
        "SELECT COUNT(*) 
        FROM `enp_quiz_responses` 
        WHERE 
        #preview_response = false AND
        " . $ignored_ip_sql . "
        correct_option_id != '-1' 
        AND quiz_option_value < " . $slider_options->slider_low_answer . " AND `quiz_id` = " . $quiz->ID
      );
      
      
      // $exact_match_count = $wpdb->get_var( 
//         "SELECT COUNT(*) 
//         FROM `enp_quiz_responses` 
//         WHERE 
//         #preview_response = false AND
//         " . $ignored_ip_sql . "
//         correct_option_id != '-1' 
//         AND quiz_option_value = " . $slider_options->slider_correct_answer . " AND `quiz_id` = " . $quiz->ID
//       );
      
       
      if ($quiz_response_count > 0) {
          $percentage_answering_above = 
            ROUND($count_answering_above/$quiz_response_count*100, 2);
          $percentage_answering_below = 
            ROUND($count_answering_below/$quiz_response_count*100, 2);
      }
    }
    ?>
    <h1>Quiz Report: <b><?php echo esc_attr($quiz->title); ?></b></h1>
    <br>
    <div class="bootstrap">
        <div class="panel panel-info">
          <!-- Default panel contents -->
          <div class="panel-heading">Quiz Preview</div>
          <div class="panel-body preview-quiz">
            <?php 
            $quiz_display_width = $wpdb->get_var("
              SELECT value FROM enp_quiz_options
              WHERE field = 'quiz_display_width' AND quiz_id = " . $quiz->ID);
    
            $quiz_display_height = $wpdb->get_var("
              SELECT value FROM enp_quiz_options
              WHERE field = 'quiz_display_height' AND quiz_id = " . $quiz->ID);
        
            $iframe_url = get_site_url() . '/iframe-quiz/?guid=' . $_GET["guid"];
      
            echo '<iframe frameBorder="0" height="' . $quiz_display_height . '" width="' . $quiz_display_width . '" src="' . $iframe_url . '&amp;preview=true"></iframe>';  
            ?>
          </div>
        </div>
    </div>
    <?php if ( $quiz_response_count > 0 ) {  ?>
    <div id="<?php echo $quiz->quiz_type == "multiple-choice" ? "quiz-mc-answer-pie-graph" : "quiz-slider-answer-pie-graph" ; ?>"></div>
    <?php if ( $quiz->quiz_type == "multiple-choice") { ?>
    <?php } ?>
    <?php //include(locate_template('self-service-quiz/quiz-detailed-responses.php')); ?>
    <div class="bootstrap">
      <div class="panel panel-info">
        <!-- Default panel contents -->
        <div class="panel-heading">Quiz statistics</div>
        <div class="input-group">
          <span class="input-group-addon">Total responses: </span>
          <label class="form-control"><?php echo $quiz_response_count; ?></label>
        </div>
        <div class="input-group">
          <span class="input-group-addon">Incorrect responses: </span>
          <label class="form-control"><?php echo $quiz_response_count-$correct_response_count; ?></label>
        </div>
        <div class="input-group">
          <span class="input-group-addon">Correct responses: </span>
          <label class="form-control"><?php echo $correct_response_count; ?></label>
        </div>
        <?php //if ($quiz->quiz_type == "slider") { ?>
        <!-- <div class="input-group">
          <span class="input-group-addon">Exact matches: </span>
          <label class="form-control"><?php //echo $exact_match_count; ?></label>
          <input type="hidden" id="exact-matches" value="<?php //echo $exact_match_count ?>">
        </div> -->
        <?php //}?>
        <div class="input-group">
          <span class="input-group-addon">Percentage correct: </span>
          <label class="form-control"><?php echo ROUND($correct_response_count/$quiz_response_count*100, 2); ?>%</label>
          <input type="hidden" id="percentage-correct" value="<?php echo ROUND($correct_response_count/$quiz_response_count*100, 2); ?>">
        </div>
        <?php if ($quiz->quiz_type == "slider") { ?>
        <!-- <div class="input-group">
          <span class="input-group-addon">Percentage exact: </span>
          <label class="form-control"><?php echo ROUND($exact_match_count/$quiz_response_count*100, 2); ?>%</label>
          <input type="hidden" id="percentage-exact" value="<?php echo ROUND($exact_match_count/$quiz_response_count*100, 2); ?>">
        </div> -->
        <div class="input-group">
          <span class="input-group-addon">Percentage answering above: </span>
          <label class="form-control"><?php echo $percentage_answering_above; ?>%</label>
          <input type="hidden" id="percentage-answering-above" value="<?php echo $percentage_answering_above ?>">
        </div>
        <div class="input-group">
          <span class="input-group-addon">Percentage answering below: </span>
          <label class="form-control"><?php echo $percentage_answering_below; ?>%</label>
          <input type="hidden" id="percentage-answering-below" value="<?php echo $percentage_answering_below ?>">
        </div>
        <?php }?>
        <div class="input-group">
          <span class="input-group-addon">Total views: </span>
          <label class="form-control"><?php echo $quiz_total_view_count; ?></label>
        </div>
        <div class="input-group">
          <span class="input-group-addon">Unique views: </span>
          <label class="form-control"><?php echo $unique_view_count; ?></label>
        </div>
        <div class="input-group">
          <span class="input-group-addon">Percentage answering: </span>
          <label class="form-control"><?php echo ROUND($unique_view_count/$unique_answer_count*100, 2); ?>%</label>
        </div>
      </div>
    </div>
    
    <?php if ( $quiz->quiz_type == "multiple-choice") { ?>
    <div class="bootstrap">
      <div class="panel panel-info">
        <!-- Default panel contents -->
        <div class="panel-heading">Response Detail</div>
          <div class='table-responsive'>
            <table class='table'>
              <thead><tr>
                <!-- <th>ID</th> -->
                <th>Answer</th>
                <th># Responded</th>
                <th>Display Order</th>
                <!-- <th>% Selected</th> -->
              </tr></thead>
              <?php
              foreach ( $mc_answers as $mc_answer ) { 
                $quiz_responses[$mc_answer->ID] = $wpdb->get_var( 
                  "SELECT COUNT(*) 
                  FROM enp_quiz_responses
                  WHERE 
                  #preview_response = false AND
                  " . $ignored_ip_sql . "
                  quiz_option_id = " . $mc_answer->ID . "
                  AND quiz_id = " . $quiz->ID
                );
                ?>
                <tr class="<?php echo $correct_answer_id == $mc_answer->ID ? "correct" : ""; ?>">
                  <!-- <td><?php //echo $mc_answer->ID ?></td> -->
                  <td><input type="hidden" class="form-control quiz-responses-option" id="<?php echo $mc_answer->ID ?>" value="<?php echo $mc_answer->value ?>"><?php echo $mc_answer->value ?></td>
                  <td><input type="hidden" class="form-control quiz-responses-option-count" id="quiz-responses-option-count-<?php echo $mc_answer->ID ?>" value="<?php echo $quiz_responses[$mc_answer->ID] ?>"><?php echo $quiz_responses[$mc_answer->ID] ?></td>
                  <td><?php echo $mc_answer->display_order ?></td>
                  <!-- <td><?php// echo ROUND($quiz_responses[$mc_answer->ID]/$quiz_response_count*100, 2) ?>%</td> -->
                </tr>
                <?php
              }
              ?>
            </table>
          </div>
          
        </div>
    </div><!-- END Bootstrap -->
    <?php } ?>
    
    <?php } else { ?>
    <div class="bootstrap">
        <div class="panel panel-info">
          <!-- Default panel contents -->
          <div class="panel-heading">Quiz Report</div>
          <div class="panel-body preview-quiz">
            <p>No responses for this quiz just yet!</p>
          </div>
        </div>
    </div>
    <?php } ?>
    
    <div class="bootstrap">
        <div class="panel panel-info">
          <!-- Default panel contents -->
          <div class="panel-heading">Quiz Report Options</div>
          <div class="panel-body">
            <form id="quiz-report-form" class="form-horizontal" role="form" method="post" action="<?php echo get_stylesheet_directory_uri(); ?>/self-service-quiz/include/process-quiz-report-form.php">
              <input type="hidden" name="input-id" id="input-id" value="<?php echo $quiz->ID; ?>">
    		  <input type="hidden" name="input-guid" id="input-guid" value="<?php echo $quiz->guid; ?>">
              
              <!-- BEGIN QUIZ QUESTION -->
	          <div class="form-group">
	            <label for="input-question" class="col-sm-3">Ignored IP Addresses <span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" data-placement="top" title="Specify a comma separated list of IP Addresses to exclude form this report"></span></label>
	            <div class="col-sm-9">
                  <textarea class="form-control" rows="2" name="input-report-ip-addresses" id="input-report-ip-addresses" placeholder="Enter IP addresses to ignore (comma separated)"><?php echo esc_attr($ignored_ip_list); ?></textarea>
	            </div>
	          </div>
              
              <div class="form-group">
  	            <div class="col-sm-12">
                    <button id="add-my-ip" class="btn btn-sm btn-primary add-my-ip">Add my current IP Address</button>
  	            </div>
              </div>
              <!-- END QUIZ QUESTION -->
              
	          <div class="form-group">
                <div class="col-sm-3">
                </div>
	            <div class="col-sm-9">
	              <button type="submit" class="btn btn-primary">Update</button>
	            </div>
	          </div>
          </div>
        </div>
    </div>
    
    <div class="bootstrap"><p><a href="view-quiz?guid=<?php echo $quiz->guid ?>" class="btn btn-primary btn-xs active">View Quiz</a> | <?php if ( $quiz_response_count > 0 ) {  ?> <a href="<?php echo get_stylesheet_directory_uri(); ?>/self-service-quiz/include/process-quiz-delete-responses.php?guid=<?php echo $quiz->guid ?>" class="btn btn-danger btn-xs active delete-responses-button" role="button">Delete Responses</a> | <?php }  ?><a href="list-quizzes/" class="btn btn-primary btn-xs active" role="button">Back to Quizzes</a></p></div>
    <?php
    } else {
    ?>
      <p>Please login to start creating quizzes!</p>
    <?php }
  }
	
<?php if (have_posts()) while (have_posts()) : the_post(); ?>
    <article class="entry post clearfix">
        <h1 class="main_title"><?php the_title(); ?></h1>

        <div class="post-content clearfix">
            <?php
            if ($_GET["add_question"] != 1 && $_GET["edit_guid"]) {
                $add_question = false;
                $new_quiz = false;
                $first_question = false;
            } elseif ($_GET["add_question"] == 1 && $_GET["edit_guid"]) {
                $add_question = true;
                $new_quiz = true;
                $first_question = false;
            } elseif ($_GET["add_question"] == 1 && !$_GET["edit_guid"]) {
                $add_question = true;
                $new_quiz = true;
                $first_question = false;
            } elseif (!$_GET["edit_guid"]) {
                $add_question = true;
                $new_quiz = true;
                $first_question = true;
            } else {
//                $add_question = false;
//                $new_quiz = true;
            }
            if ($_GET["edit_guid"]) {
                $quiz = $wpdb->get_row("SELECT * FROM enp_quiz WHERE guid = '" . $_GET["edit_guid"] . "'");
//                $first_question = false;
            } elseif ($_GET["parent_guid"]) {
                $parent_quiz = $wpdb->get_row("SELECT * FROM enp_quiz WHERE guid = '" . $_GET["parent_guid"] . "'");
//                $first_question = true;
//                $new_quiz = true;
            }
            if ($_GET["curr_quiz_id"]) {
                $curr_quiz_id = $_GET["curr_quiz_id"];
                $parent_guid = $_GET["parent_guid"];
                $parent_title = $parent_quiz->title;
            } else {
                $curr_quiz_id = $quiz->ID;
                $parent_guid = $quiz->guid;
                $parent_title = '';
            }
            if ($quiz) {
                $question_text = esc_attr($quiz->question);
            } else {
                $question_text = "Enter Quiz Question";
            }
            // Removing lock feature...remove permanently after more feedback
            //if ( !$quiz->locked ) {
            if (true) {
                ?>
                <div class="entry_content bootstrap <?php echo $quiz ? "edit_quiz" : "new_quiz" ?>">
                    <?php if ($quiz->locked) { ?>
                        <div class='bootstrap'>
                            <div class='alert alert-warning alert-dismissable'>
                                <span class='glyphicon glyphicon-warning-sign'></span> Quiz has received responses.
                                Editing could cause inconsistent reports.
                                <button type='button' class='close' data-dismiss='alert'
                                        aria-hidden='true'>&times;</button>
                            </div>
                            <div class='clear'></div>
                        </div>
                    <?php } ?>
                    <form id="quiz-form" class="form-horizontal" role="form" method="post"
                          action="<?php echo get_stylesheet_directory_uri(); ?>/self-service-quiz/include/process-quiz-form.php">
                        <input type="hidden" name="input-id" id="input-id" value="<?php echo $quiz->ID; ?>">
                        <input type="hidden" name="input-guid" id="input-guid" value="<?php echo $quiz->guid; ?>">
                        <?php include(locate_template('self-service-quiz/quiz-form-options.php')); ?>
                        <div class="panel panel-info quiz-answers-panel">
                            <div class="panel-heading">Quiz Answers</div>
                            <div class="panel-body" id="quiz-answers">
                                <?php include(locate_template('self-service-quiz/quiz-form-mc-options.php')); ?>
                                <?php include(locate_template('self-service-quiz/quiz-form-slider-options.php')); ?>
                            </div>
                        </div>
                        <?php include(locate_template('self-service-quiz/quiz-form-styling-options.php')); ?>
                        <div class="panel panel-info aanswer-settings">
                            <div class="panel-heading">Advanced Answer Settings - Optional</div>
                            <div class="panel-body" id="quiz-answers">
                                <?php include(locate_template('self-service-quiz/quiz-form-aanswer-options.php')); ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <input type="hidden" name="quiz-new-question" id="quiz-new-question" value=""><!--||KVB-->
                                <input type="hidden" name="curr-quiz-id" id="curr-quiz-id" value=""><!--||KVB-->
                                <input type="hidden" name="parent-guid" id="parent-guid" value=""><!--||KVB-->
                                <input type="hidden" name="parent-title" id="parent-title" value=""><!--||KVB-->
                                <?php if ($new_quiz) {
                                    echo "<button id=\"addQuestionSubmit\" class=\"btn btn-primary\">Add Another Question to Quiz</button>"; // ||KVB
                                } ?>
                                <button type="submit" id="questionSubmit"
                                        class="btn btn-primary"><?php /* echo $quiz ? "Update Quiz" : "Create Quiz"; */ ?><?php echo $quiz ? "Finish Updating Quiz" : "Finish Creating Quiz"; // ||KVB ?></button>
                                <script type="text/javascript">
                                    (function ($) {
                                        $('#addQuestionSubmit').click(function (e) {
                                            e.preventDefault();
                                            <?php if ( $add_question == true ) {
                                                echo "$('#quiz-new-question').val('updateQuizAddQuestion');";
                                                echo "$('#curr-quiz-id').val('".$curr_quiz_id."');";
                                                echo "$('#parent-guid').val('".$parent_guid."');";
                                                echo "$('#parent-title').val('".$parent_title."');";
                                            } else {
                                                echo "$('#quiz-new-question').val('newQuizAddQuestion_shouldNotHappen');";
                                                echo "$('#curr-quiz-id').val('".$curr_quiz_id."');";
                                                echo "$('#parent-guid').val('".$parent_guid."');";
                                                echo "$('#parent-title').val('".$parent_title."');";
                                            }?>
                                            console.log($('#quiz-new-question').val()); // remove console.log ||KVB
                                            $('#quiz-form').submit();
                                            return false;
                                        });
                                        $('#questionSubmit').click(function (e) {
                                            e.preventDefault();
                                            <?php if ( $add_question == true ) {
        							    	    echo "$('#quiz-new-question').val('finishQuiz');";
        								    	echo "$('#curr-quiz-id').val('".$curr_quiz_id."');";
                                                echo "$('#parent-guid').val('".$parent_guid."');";
                                                echo "$('#parent-title').val('".$parent_title."');";
                                            } else {
                                                echo "$('#quiz-new-question').val('finishQuiz');";
        								    	echo "$('#curr-quiz-id').val('".$curr_quiz_id."');";
                                                echo "$('#parent-guid').val('".$parent_guid."');";
                                                echo "$('#parent-title').val('".$parent_title."');";
                                            } ?>
                                            //$('#curr-quiz-id').val('');
                                            console.log('quiz-new-question = ' + $('#quiz-new-question').val()); // remove console.log ||KVB
                                            console.log('curr-quiz-id = ' + $('#curr-quiz-id').val()); // remove console.log ||KVB
                                            console.log('parent-guid = ' + $('#parent-guid').val()); // remove console.log ||KVB
                                            console.log('parent-title = ' + $('#parent-title').val()); // remove console.log ||KVB
                                            $('#quiz-form').submit();
                                            return false;
                                        });
                                    })(jQuery);
                                </script>
                                <!--||KVB-->
                                <?php if ($quiz) { ?>
                                    <a href="view-quiz?guid=<?php echo $quiz->guid ?>" class="btn btn-warning"
                                       role="button">Cancel</a>
                                <?php } ?>
                            </div>
                        </div>
                    </form>
                    <a href="create-a-quiz/" class="btn btn-primary btn-xs active" role="button">Back to Quizzes</a>
                    <?php wp_link_pages(array('before' => '<p><strong>' . esc_attr__('Pages', 'Trim') . ':</strong> ', 'after' => '</p>', 'next_or_number' => 'number')); ?>
                </div> <!-- end .entry_content -->

            <?php } else { ?>
                <p>This quiz is locked for editing, as responses have been received.</p>
                <div class="bootstrap">
                    <div class="form-group">
                        <p>
                            <a href="view-quiz?guid=<?php echo $quiz->guid ?>" class="btn btn-info btn-sm active"
                               role="button">View Quiz</a>

                        <p><a href="configure-quiz" class="btn btn-info btn-xs active" role="button">New Quiz</a> | <a
                                href="create-a-quiz/" class="btn btn-primary btn-xs active" role="button">Back to
                                Quizzes</a></p>
                    </div>
                </div>
            <?php } ?>
        </div>
        <!-- end .post-content -->
    </article> <!-- end .post -->
<?php endwhile; // end of the loop. ?>
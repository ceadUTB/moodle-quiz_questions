<?php

/**
 * @package   quiz_questionsreport
 * @copyright 2018 AncaSystems
 * @license   https://github.com/AncaSystems/moodle-questionsreport/blob/master/LICENSE Apache 2.0
 */

defined('MOODLE_INTERNAL') || die();


class quiz_questions_report extends quiz_default_report{
  protected $course;
  protected $quiz;
  protected $questions;
  protected $cm;
  protected $context;

  public function display($quiz, $cm, $course) {

    $this->course = $course;
    $this->quiz = $quiz;
    $this->cm = $cm;

    $this->context = context_module::instance($cm->id);
    require_capability('mod/quiz:grade', $this->context);
    $viewCap  = has_capability('quiz/questionsreport:view', $this->context);


    if (!quiz_has_questions($quiz->id)) {
      $this->print_header_and_tabs($cm, $course, $quiz, 'Questions report');
      echo quiz_no_questions_message($quiz, $cm, $this->context);
      return true;
    }

    $this->questions = quiz_report_get_significant_questions($quiz);

    $this->print_header_and_tabs($cm, $course, $quiz, 'Questions report');

    

    return true;
  }
}

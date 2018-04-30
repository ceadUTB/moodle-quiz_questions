<?php

/**
 * @package   quiz_questions_report
 * @copyright 2018 AncaSystems
 * @license   https://github.com/AncaSystems/moodle-questions_report/blob/master/LICENSE Apache 2.0
 */

defined('MOODLE_INTERNAL') || die();


class quiz_questions_report extends quiz_default_report {
  protected $course;
  protected $quiz;
  protected $questions;
  protected $cm;
  protected $context;

  public function display ($cm, $course, $quiz){
    global $CFG, $DB, $PAGE;

    $this->course = $course;
    $this->quiz = $quiz;
    $this->cm = $cm;

    $this->context = context_module::instance($cm->id);
    require_capability('mod/quiz:grade', $this->context);

    $this->questions = quiz_report_get_significant_questions($quiz);

    print_r($this->questions);
  }
}

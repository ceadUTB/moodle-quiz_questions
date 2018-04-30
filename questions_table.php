<?php

/**
 * @package   quiz_questionsreport
 * @copyright 2018 AncaSystems
 * @license   https://github.com/AncaSystems/moodle-questionsreport/blob/master/LICENSE Apache 2.0
 */

defined('MOODLE_INTERNAL') || die();


require_once($CFG->libdir.'/tablelib.php');

class quiz_questions_table extends flexible_table {
  /** @var object the quiz settings. */
  protected $quiz;

  /** @var integer the quiz course_module id. */
  protected $cmid;
  function __construct(){
    parent::__construct('mod-quiz-report-questions-report');
  }

  /**
   * Set up the columns and headers and other properties of the table and then
   * call flexible_table::setup() method.
   *
   * @param object $quiz the quiz settings
   * @param int $cmid the quiz course_module id
   * @param moodle_url $reporturl the URL to redisplay this report.
   * @param int $s number of attempts included in the statistics.
   */

  public function question_setup($quiz,$cmid,$reporturl,$s){
    $this->quiz = $quiz;
    $this->cmid = $cmid;

    parent::setup();
  }
}

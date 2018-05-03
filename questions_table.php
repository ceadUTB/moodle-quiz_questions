<?php
/**
 * @package   quiz_questionsreport
 * @copyright 2018 AncaSystems
 * @license   https://github.com/AncaSystems/moodle-questionsreport/blob/master/LICENSE Apache 2.0
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/tablelib.php');

class quiz_questions_table extends flexible_table{
  protected $quiz;
  protected $cmid;

  public function __construct()  {
    parent::__construct('mod-quiz-report-questions-report');
  }


  public function questions_setup($quiz, $cmid, $reporturl){
    $this->quiz = $quiz;
    $this->cmid = $cmid;

    $columns = array('name','times', 'right','rightpercent', 'wrong', 'wrongpercent');
    $headers = array(get_string('question_name','quiz_questions'),
                                     get_string('question_times','quiz_questions'),
                                     get_string('question_right','quiz_questions'),
                                     get_string('question_rightpercent','quiz_questions'),
                                     get_string('question_wrong','quiz_questions'),
                                     get_string('question_wrongpercent','quiz_questions'));


    $this->define_columns($columns);
    $this->define_headers($headers);
    $this->sortable(false);

    $this->define_baseurl($reporturl->out());

    $this->collapsible(true);

    parent::setup();
  }

  protected function col_name($QuestionReport){
    $name = $QuestionReport->get_name();
    if ($this->is_downloading()) {
      return $name;
    }
    return $name;
  }

  protected function col_times($QuestionReport){
    $times = $QuestionReport->get_times();
    if ($this->is_downloading()) {
      return $times;
    }
    return $times;
  }

  protected function col_right($QuestionReport){
    $right = $QuestionReport->get_right();
    if ($this->is_downloading()) {
      return $right;
    }
    return $right;
  }

  protected function col_rightpercent($QuestionReport){
    $rightpercent = $QuestionReport->RightPercent() . '%';
    if ($this->is_downloading()) {
      return $rightpercent;
    }
    return $rightpercent;
  }

  protected function col_wrong($QuestionReport){
    $wrong = $QuestionReport->get_wrong();
    if ($this->is_downloading()) {
      return $wrong;
    }
    return $wrong;
  }

  protected function col_wrongpercent($QuestionReport){
    $wrongpercent = $QuestionReport->WrongPercent() . '%';
    if ($this->is_downloading()) {
      return $wrongpercent;
    }
    return $wrongpercent;
  }
}

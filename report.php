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
  protected $students;
  protected $questionresponse = array();
  protected $questionReport = array();

  public function display($quiz, $cm, $course) {
    global $DB;

    $this->course = $course;
    $this->quiz = $quiz;
    $this->cm = $cm;

    $this->context = context_module::instance($cm->id);
    require_capability('mod/quiz:grade', $this->context);
    $viewCap  = has_capability('quiz/questionsreport:view', $this->context);


    if ($viewCap) {
      if (!quiz_has_questions($quiz->id)) {
        $this->print_header_and_tabs($cm, $course, $quiz, 'Questions report');
        echo quiz_no_questions_message($quiz, $cm, $this->context);
        return true;
      }
      // Quiz Questions
      $this->questions = quiz_report_get_significant_questions($quiz);

      $this->print_header_and_tabs($cm, $course, $quiz, 'Questions report');

      // get all students
      $this->students = get_role_users(5, $this->context, true);

      foreach ($this->questions as $question) {
        foreach ($this->students as $student) {
          $response = $DB->get_records_sql('SELECT qas.state AS response, q.name AS question_name FROM {question_attempt_steps} qas
            JOIN {question_attempts} qa ON qa.id = qas.questionattemptid
            JOIN {question} q ON q.id = '.$question->id.'
            JOIN {question_usages} qu ON qu.contextid ='.$this->context->id.'
            WHERE qas.userid = '. $student->id .' AND (qas.state = "gradedwrong" OR qas.state = "gradedright") ORDER BY q.id');

            if (sizeof($response)>0) {
                foreach ($response as $data) {
                  if (sizeof($this->questionresponse[$data->question_name])>0 ) {
                    array_push($this->questionresponse[$data->question_name], $data->response );
                  }else{
                    $this->questionresponse[$data->question_name] = array();
                    array_push($this->questionresponse[$data->question_name], $data->response );
                  }
                }
            }
        }

      }

      foreach ($this->questionresponse as $questionname => $questiondata) {
        array_push($this->questionReport, $this->QuestionReport($questiondata,$questionname));
      }

      return true;
    }

    return false;
  }


  private function QuestionReport($questiondata, string $name){
    $QuestionReport = new \quiz_questions\questionreport($name,sizeof($questiondata));
    foreach ($questiondata as $data) {
      if ($data == "gradedwrong") {
        $QuestionReport->wrongplusplus();
      }else if($data == "gradedright"){
        $QuestionReport->rightplusplus();
      }
    }

    return $QuestionReport;
  }

  protected function output_question_report_data(){
    global $OUTPUT;
  }
}

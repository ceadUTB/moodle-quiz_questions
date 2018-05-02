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

    $download = optional_param('download','',PARAM_BOOL);

    $options = array('id' => $cm->id, 'mode'=>'questions');
    $reporturl = new moodle_url('/mod/quiz/report.php', $options);

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

      echo $this->output_question_report_data($this->questionReport,$options);
      return true;
    }


    redirect(new moodle_url('/course/view.php',array('id'=> $course->id)));
    return true;
  }


  private function QuestionReport($questiondata, $name){
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

  protected function output_question_report_data($QuestionsReports,$options){
    $content = html_writer::tag('h3',get_string('questions', 'quiz_questions'));
    $content.= html_writer::start_tag('div', array('class' => 'row'));
    $content.= html_writer::start_tag('table',array('class'=>'table table-bordered'));
    $content.= html_writer::start_tag('tr');
    $content.= html_writer::tag('th', get_string('question_name','quiz_questions'));
    $content.= html_writer::tag('th', get_string('question_times','quiz_questions') . '(<i class="fa fa-clock-o times-color" aria-hidden="true"></i>)');
    $content.= html_writer::tag('th', get_string('question_right','quiz_questions') . '(<i class="fa fa-check right-color" aria-hidden="true"></i>)');
    $content.= html_writer::tag('th', get_string('question_rightpercent','quiz_questions') . '(<span class="percent-color"><b>%</b></span>)');
    $content.= html_writer::tag('th', get_string('question_wrong','quiz_questions') . '(<i class="fa fa-times wrong-color" aria-hidden="true"></i>)');
    $content.= html_writer::tag('th', get_string('question_wrongpercent','quiz_questions') . '(<span class="percent-color"><b>%</b></span>)');
    $content.= html_writer::end_tag('tr');
    foreach ($QuestionsReports as $QuestionReport) {
      $content.= html_writer::start_tag('tr');
      $content.= html_writer::tag('td', $QuestionReport->get_name());
      $content.= html_writer::tag('td', $QuestionReport->get_times());
      $content.= html_writer::tag('td', $QuestionReport->get_right());
      $content.= html_writer::tag('td', $QuestionReport->RightPercent() . '%');
      $content.= html_writer::tag('td', $QuestionReport->get_wrong());
      $content.= html_writer::tag('td', $QuestionReport->WrongPercent() . '%');
      $content.= html_writer::end_tag('tr');
    }
    $content.= html_writer::end_tag('table');
    $content.= html_writer::end_tag('div');

    // $content.= html_writer::start_tag('div', array('class'=>'row text-center'));
    // $content.= html_writer::start_tag('div', array('class'=>'span6 offset3'));
    // // Form to export
    // $content.= html_writer::start_tag('form', array('class' => 'form-inline', 'method' => 'get', 'action' => new moodle_url('/mod/quiz/report.php')));
    // $content.= '<input type="number" value="'.$options['id'].'" name="id" class="hidden">';
    // $content.= '<input type="text" value="'.$options['mode'].'" name="mode" class="hidden">';
    // $content.= '<input type="text" value="true" name="mode" class="hidden">';
    // $content.= html_writer::start_tag('select', array('name' => 'type'));
    // $content.= html_writer::tag('option', 'Excel / XML', array('value'=>'xmls'));
    // $content.= html_writer::end_tag('select');
    // $content.= '<input value="'.get_string('export', 'quiz_questions').'" type="submit">';
    // $content.= html_writer::end_tag('form');
    // $content.= html_writer::end_tag('div');
    // $content.= html_writer::end_tag('div');

    return $content;
  }
}

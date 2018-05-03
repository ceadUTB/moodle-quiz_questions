<?php

/**
 * @package   quiz_questionsreport
 * @copyright 2018 AncaSystems
 * @license   https://github.com/AncaSystems/moodle-questionsreport/blob/master/LICENSE Apache 2.0
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/report/questions/questions_table.php');

class quiz_questions_report extends quiz_default_report{
  protected $course;
  protected $quiz;
  protected $questions;
  protected $cm;
  protected $context;
  protected $students;
  protected $questionresponse = array();
  protected $questionReport = array();
  protected $table;

  public function display($quiz, $cm, $course) {
    global $DB;

     $this->context = context_module::instance($cm->id);
     $download = optional_param('download', '', PARAM_ALPHA);
     require_capability('mod/quiz:grade', $this->context);
     $viewCap  = has_capability('quiz/questionsreport:view', $this->context);

    if ($viewCap) {
      $this->table = new quiz_questions_table();

      $this->course = $course;
      $this->quiz = $quiz;
      $this->cm = $cm;

      $options = array('id' => $cm->id, 'mode'=>'questions');
      $reporturl = new moodle_url('/mod/quiz/report.php', $options);

      // Table setup
      $this->table->questions_setup($quiz, $cm->id, $reporturl);

      $courseshortname = format_string($course->shortname,true,
                                        array('context' => context_course::instance($course->id)));

      $filename = quiz_report_download_filename(get_string('questions:componentname','quiz_questions'),$courseshortname,$quiz->name);

      $this->table->is_downloading($download,$filename,
                                   get_string('questions:componentname', 'quiz_questions'));

      if (!quiz_has_questions($quiz->id)) {
         $this->print_header_and_tabs($cm, $course, $quiz, 'Questions report');
         echo quiz_no_questions_message($quiz, $cm, $this->context);
         return true;
       }
       // Quiz Questions
      $this->questions = quiz_report_get_significant_questions($quiz);

      // get all students
      $this->students = get_role_users(5, $this->context, true);

      foreach ($this->questions as $question) {
        foreach ($this->students as $student) {
          $response = $DB->get_records_sql('SELECT qas.state as response, q.name AS question_name FROM {question_attempt_steps} qas
            JOIN {question_attempts} qa ON qa.id = qas.questionattemptid
            JOIN {question} q ON q.id = '.$question->id.'
            JOIN {question_usages} qu ON qu.contextid ='.$this->context->id.'
            WHERE qas.userid = '. $student->id .' AND qas.state IN ("gradedwrong", "gradedright") GROUP BY qas.state ORDER BY q.id ');

            if (sizeof($response)>0) {
                foreach ($response as $data) {
                  if (array_key_exists($data->question_name,$this->questionresponse)) {
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

      if ($this->table->is_downloading()) {
        $this->download_questions_report_table($this->questionReport);
        $this->table->export_class_instance()->finish_document();
      }else{
          if (!$this->table->is_downloading()) {
             $this->print_header_and_tabs($cm, $course, $quiz, 'Questions report');
          }
         echo $this->output_question_report_data($this->questionReport,$quiz);
         echo $this->everything_download_options($reporturl);

      }
     }


  }

  /**
  * Model translate Function
  */
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

  /**
  * Table Function
  */
  protected function output_question_report_data($QuestionsReports){
    global $OUTPUT;
    $questioninfotable = new html_table();
    $questioninfotable->aling = array('center','center');
    $questioninfotable->width = '60%';
    $questioninfotable->attributes['class'] = 'table table-bordered titlesleft';

    $questioninfotable->head = array(get_string('question_name','quiz_questions'),
                                     get_string('question_times','quiz_questions') . '(<i class="fa fa-clock-o times-color" aria-hidden="true"></i>)',
                                     get_string('question_right','quiz_questions') . '(<i class="fa fa-check right-color" aria-hidden="true"></i>)',
                                     get_string('question_rightpercent','quiz_questions') . '(<span class="percent-color"><b>%</b></span>)',
                                     get_string('question_wrong','quiz_questions') . '(<i class="fa fa-times wrong-color" aria-hidden="true"></i>)',
                                     get_string('question_wrongpercent','quiz_questions') . '(<span class="percent-color"><b>%</b></span>)');

    $questioninfotable->data = array();
    foreach ($QuestionsReports as $QuestionReport) {
      $datumfromtable = $this->table->format_row($QuestionReport);
      $questioninfotable->data[] = $datumfromtable;
    }
    echo $OUTPUT->heading(get_string('questions:componentname', 'quiz_questions'), 3);
    echo html_writer::table($questioninfotable);
  }

  /**
  * Download button
  */
  protected function everything_download_options(moodle_url $reporturl){
    global $OUTPUT;
    return $OUTPUT->download_dataformat_selector(get_string('export', 'quiz_questions'),$reporturl->out_omit_querystring(), 'download', $reporturl->params());
  }


  /**
  * Download Function
  */
  protected function download_questions_report_table($QuestionsReports){
    if ($this->table->is_downloading() == 'html') {
      echo $this->output_question_report_data($this->questionReport,$quiz);
      return;
    }

    $exportclass = $this->table->export_class_instance();
    $exportclass->start_table(get_string('questions:componentname', 'quiz_questions'));
    $exportclass->output_headers($this->table->headers);

    foreach ($QuestionsReports as $QuestionReport) {
      $row = array();
      foreach ($this->table->format_row($QuestionReport) as $heading => $value) {
        $row[] = $value;
      }
      $exportclass->add_data($row);
    }

    $exportclass->finish_table();
  }
}

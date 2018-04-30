<?php

/**
 * @package   quiz_questionsreport
 * @copyright 2018 AncaSystems
 * @license   https://github.com/AncaSystems/moodle-questionsreport/blob/master/LICENSE Apache 2.0
 */

 defined('MOODLE_INTERNAL') || die();


function xmldb_quiz_questions_install(){
  global $DB;


  $record = new stdClass();
  $record->name = 'questions';
  $record->displayorder = 8500;
  $record->capability  = 'quiz/questionsreport:view';

  $DB->insert_record('quiz_reports', $record);
}

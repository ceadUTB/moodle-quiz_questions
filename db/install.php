<?php

/**
 * @package   quiz_questions_report
 * @copyright 2018 AncaSystems
 * @license   https://github.com/AncaSystems/moodle-questions_report/blob/master/LICENSE Apache 2.0
 */

 defined('MOODLE_INTERNAL') || die();


function xmldb_quiz_questions_report_install(){
  global $DB;

  $record = new stdClass();
  $record->name = 'questions_report';
  $record->displayorder = '8500';

  $DB->insert_record('quiz_reports', $record);
}

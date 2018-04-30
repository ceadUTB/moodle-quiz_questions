<?php

/**
 * @package   quiz_questions_report
 * @copyright 2018 AncaSystems
 * @license   https://github.com/AncaSystems/moodle-questions_report/blob/master/LICENSE Apache 2.0
 */

  defined('MOODLE_INTERNAL') || die();


  $capabilities = array(
      // Is the user allowed to see the student's real names while grading?
      'quiz/grading:viewstudentnames' => array(
          'captype' => 'read',
          'contextlevel' => CONTEXT_MODULE,
          'legacy' => array(
              'teacher' => CAP_ALLOW,
              'editingteacher' => CAP_ALLOW
          ),
          'clonepermissionsfrom' =>  'mod/quiz:viewreports'
      ),

      // Is the user allowed to see the student's idnumber while grading?
      'quiz/grading:viewidnumber' => array(
          'captype' => 'read',
          'contextlevel' => CONTEXT_MODULE,
          'legacy' => array(
              'teacher' => CAP_ALLOW,
              'editingteacher' => CAP_ALLOW
          ),
          'clonepermissionsfrom' =>  'mod/quiz:viewreports'
      )
  );

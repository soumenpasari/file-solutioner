<?php
/**
 * @author soumen pasari
 * @package file-solutioner
 * @license soumen-pasari 2019-2020 MIT open source
 */
/**
 * ERROR REPORTING
 */
ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(E_ALL);

 require_once('autoload.php');

  /**
   * creating auto date folder create class's object
   */
  $autoFol = new FileSolutioner();
  $header = ['title','content','date'];
  $data = ['bodytitle','body content',date('Y-m-d')];
  ProcessTimeCalculator::startTimer();
  $createFile = $autoFol
                  ->folder(date('Y'))
                  ->isChild(date('M'))
                  ->isChild(date('d'))
                  ->isChild('live')
                  ->file('fileName.csv','csv')
                  ->addData($data)
                  ->addData($data)
                  ->create();
  ResponseCreator::$response['totalTime'] = ProcessTimeCalculator::endTimer();
  echo '<pre>';
  print_r(ResponseCreator::$response);

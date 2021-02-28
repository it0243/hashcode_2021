<?php

include 'Reader.php';
include 'Writer.php';
include 'Solver.php';
include 'Scorer.php';

$FILES = [
  'a.txt',
  'b.txt',
  'c.txt',
  'd.txt',
  'e.txt',
  'f.txt',
];

$total_score = 0;

foreach ($FILES as $filename) {
  $reader = new Reader($filename);
  $data = $reader->read();

  $solver = new Solver($data);
  $solution = $solver->solve();

  $plan = $solution['plan'];
  $output = $solution['output'];

  $scorer = new Scorer($data, $plan);
  $file_score = $scorer->calculate();

  $total_score += $file_score;
  d("$filename: " . n($file_score));

  $writer = new Writer($filename, $output);
  $writer->write();
}

d("TOTAL: " . n($total_score));

function n($number) {
  return number_format($number, 0, '', ',');
}

function d($output) {
  if (is_string($output)) {
    echo $output . "\n";
  } else {
    print_r($output);
  }
}

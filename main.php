<?php

include 'Solver.php';
include 'Solver2.php';
include 'Reader.php';
include 'Writer.php';

$files = [
  'a_example.in',
  'b_little_bit_of_everything.in',
  'c_many_ingredients.in',
  'd_many_pizzas.in',
  'e_many_teams.in'
];

$total_score = 0;

foreach ($files as $filename) {
  $reader = new Reader($filename);
  $data = $reader->read();

  $solver = new Solver($data);
  $solution = $solver->solve();

  $file_score = $solution['score'];
  $output = $solution['output'];

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

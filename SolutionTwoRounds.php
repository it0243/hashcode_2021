<?php

$FILES = [
  // 'a.txt',
  // 'b.txt',
  'c.txt',
  'd.txt',
  'e.txt',
  'f.txt',
];

$total_score = 0;
foreach ($FILES as $file) {
  $f = fopen($file, "r");

  list($D, $I, $S, $V, $F) = explode(' ', trim(fgets($f)));

  // Read all Streets
  $streets = [];
  $intersections = [];
  for ($i = 0; $i < $S; $i++) {
    list($start_int, $end_int, $street_name, $travel_time) = explode(' ', trim(fgets($f)));
    $weight = $streets_counts[$street_name] ?? 0;
    $streets[$street_name] = [
      'start' => $start_int,
      'end'   => $end_int,
      'name'  => $street_name,
      'time'  => $travel_time,
    ];
    $intersections[$end_int][] = $street_name;
  }

  // Read all cars
  $cars = [];
  for ($i = 0; $i < $V; $i++) {
    $car_desc = explode(' ', trim(fgets($f)));
    $streets_count = array_shift($car_desc);
    $car_streets = $car_desc;
    $time = 0;
    foreach ($car_streets as $ind => $car_street) {
      $time += $ind > 0 ? $streets[$car_street]['time'] : 0;
    }
    if ($time <= $D) {
      $cars[] = [
        'streets_count'  => $streets_count,
        'streets' => $car_streets,
        // 'first_streets' => [$car_streets[0]],
        'time'  => $time,
      ];
    }
  }

  fclose($f);


  /// SOLVING

  $file_score = 0;
  $output = '';


  $active_cars = $cars;

  $all_streets = array_merge(...array_column($cars, 'streets'));
  $streets_counts = array_count_values($all_streets);


  $plan = [];
  foreach ($intersections as $key => $incoming_streets) {
    foreach ($incoming_streets as $ind => $name) {
      $weight = $streets_counts[$name] ?? 0;
      if (!$weight) {
        unset($incoming_streets[$ind]);
      }
    }
    if (count($incoming_streets)) {
      $plan[$key] = [];
      foreach ($incoming_streets as $name) {
        $weight = 1;
        $plan[$key][$name] = $weight;
      }
    }
  }

  /// SCORING 1

  $lights = get_lights($D, $plan);
  $traffic = [];
  $queues = [];
  $cars_ended = 0;
  $intersections_jam = [];

  $second = 0;
  $traffic[$second] = [];
  $current_lights = $lights[$second];
  // d("second: $second");
  // d($current_lights);
  foreach ($active_cars as $car_key => $car) {
    $car_str_index = 0;
    $car_str = $car['streets'][$car_str_index];
    $street = $streets[$car_str];
    $car_pos = $street['time'];
    $car_go = (in_array($car_str, $current_lights) && empty($queues[$car_str]));
    $car_ended = false;
    // d("$car_key: $car_str pos:$car_pos go:$car_go ended:$car_ended");
    $queues[$car_str][$car_key] = $car_key;
    isset($intersections_jam[$street['end']][$second][$car_str]) ? $intersections_jam[$street['end']][$second][$car_str]++ : $intersections_jam[$street['end']][$second][$car_str] = 1;
    $traffic[$second][$car_key] = ['car_str' => $car_str, 'car_str_index' => $car_str_index, 'car_pos' => $car_pos, 'car_go' => $car_go];
  }

  for ($second = 1; $second <= $D; $second++) {
    $traffic[$second] = [];
    $current_lights = $lights[$second];
    // d("second: $second");
    // d($current_lights);
    foreach ($active_cars as $car_key => $car) {
      $previous = $traffic[$second - 1][$car_key];
      $prev_car_str = $previous['car_str'];
      $prev_car_str_index = $previous['car_str_index'];
      $prev_street = $streets[$prev_car_str];
      $prev_car_pos = $previous['car_pos'];
      $end_of_prev_str = ($prev_car_pos == $prev_street['time']);
      $car_str_index = $prev_car_str_index;
      //change street
      if ($previous['car_go'] && $end_of_prev_str) {
        $car_str_index++;
        $car_pos = 1;
        unset($queues[$prev_car_str][$car_key]);
      } elseif ($previous['car_go'] && !$end_of_prev_str) {
        $car_pos = $prev_car_pos + 1;
      } else {
        $car_pos = $prev_car_pos;
      }
      $car_str = $car['streets'][$car_str_index];
      $street = $streets[$car_str];
      $car_go = ($car_pos < $street['time']) || (in_array($car_str, $current_lights) && (empty($queues[$car_str]) || array_key_first($queues[$car_str]) == $car_key));
      $car_ended = ($car_str == $car['streets'][$car['streets_count'] - 1]) && $car_pos == $street['time'];
      // d("$car_key: $car_str pos:$car_pos go:$car_go ended:$car_ended");
      if ($car_ended) {
        $car_score = $F + ($D - $second);
        $cars[$car_key]['score'] = $car_score;
        // $file_score += $car_score;
        unset($active_cars[$car_key]);
        unset($queues[$car_str][$car_key]);
        $cars_ended++;
      } else {
        $traffic[$second][$car_key] = ['car_str' => $car_str, 'car_str_index' => $car_str_index, 'car_pos' => $car_pos, 'car_go' => $car_go];
        if ($car_pos == $street['time']) {
          $queues[$car_str][$car_key] = $car_key;
          isset($intersections_jam[$street['end']][$second][$car_str]) ? $intersections_jam[$street['end']][$second][$car_str]++ : $intersections_jam[$street['end']][$second][$car_str] = 1;
        }
      }
    }
  }
  // d($intersections_jam);

  $optimized_plan = get_optimized_plan($intersections_jam);
  // d($optimized_plan);

  $output = count($optimized_plan) . PHP_EOL . $output;
  foreach ($optimized_plan as $intersection => $incoming_streets) {
    $output .= $intersection . PHP_EOL;
    $output .= count($incoming_streets) . PHP_EOL;
    foreach ($incoming_streets as $name => $weight) {
      $output .= "$name $weight" . PHP_EOL;
    }
  }

  // SCORING 2

  $plan = $optimized_plan;
  $lights = get_lights($D, $plan);
  $traffic = [];
  $queues = [];
  $cars_ended = 0;
  // $intersections_jam = [];
  $active_cars = $cars;

  $second = 0;
  $traffic[$second] = [];
  $current_lights = $lights[$second];
  // d("second: $second");
  // d($current_lights);
  foreach ($active_cars as $car_key => $car) {
    $car_str_index = 0;
    $car_str = $car['streets'][$car_str_index];
    $street = $streets[$car_str];
    $car_pos = $street['time'];
    $car_go = (in_array($car_str, $current_lights) && empty($queues[$car_str]));
    $car_ended = false;
    // d("$car_key: $car_str pos:$car_pos go:$car_go ended:$car_ended");
    $queues[$car_str][$car_key] = $car_key;
    // isset($intersections_jam[$street['end']][$second][$car_str]) ? $intersections_jam[$street['end']][$second][$car_str]++ : $intersections_jam[$street['end']][$second][$car_str] = 1;
    $traffic[$second][$car_key] = ['car_str' => $car_str, 'car_str_index' => $car_str_index, 'car_pos' => $car_pos, 'car_go' => $car_go];
  }

  for ($second = 1; $second <= $D; $second++) {
    $traffic[$second] = [];
    $current_lights = $lights[$second];
    // d("second: $second");
    // d($current_lights);
    foreach ($active_cars as $car_key => $car) {
      $previous = $traffic[$second - 1][$car_key];
      $prev_car_str = $previous['car_str'];
      $prev_car_str_index = $previous['car_str_index'];
      $prev_street = $streets[$prev_car_str];
      $prev_car_pos = $previous['car_pos'];
      $end_of_prev_str = ($prev_car_pos == $prev_street['time']);
      $car_str_index = $prev_car_str_index;
      //change street
      if ($previous['car_go'] && $end_of_prev_str) {
        $car_str_index++;
        $car_pos = 1;
        unset($queues[$prev_car_str][$car_key]);
      } elseif ($previous['car_go'] && !$end_of_prev_str) {
        $car_pos = $prev_car_pos + 1;
      } else {
        $car_pos = $prev_car_pos;
      }
      $car_str = $car['streets'][$car_str_index];
      $street = $streets[$car_str];
      $car_go = ($car_pos < $street['time']) || (in_array($car_str, $current_lights) && (empty($queues[$car_str]) || array_key_first($queues[$car_str]) == $car_key));
      $car_ended = ($car_str == $car['streets'][$car['streets_count'] - 1]) && $car_pos == $street['time'];
      // d("$car_key: $car_str pos:$car_pos go:$car_go ended:$car_ended");
      if ($car_ended) {
        $car_score = $F + ($D - $second);
        $cars[$car_key]['score'] = $car_score;
        $file_score += $car_score;
        unset($active_cars[$car_key]);
        unset($queues[$car_str][$car_key]);
        $cars_ended++;
      } else {
        $traffic[$second][$car_key] = ['car_str' => $car_str, 'car_str_index' => $car_str_index, 'car_pos' => $car_pos, 'car_go' => $car_go];
        if ($car_pos == $street['time']) {
          $queues[$car_str][$car_key] = $car_key;
          // isset($intersections_jam[$street['end']][$second][$car_str]) ? $intersections_jam[$street['end']][$second][$car_str]++ : $intersections_jam[$street['end']][$second][$car_str] = 1;
        }
      }
    }
  }



  $total_score += $file_score;
  d("$file: " . n($file_score) . " cars ended: $cars_ended/" . count($cars));

  $out_file = pathinfo($file, PATHINFO_FILENAME) . '.out';
  $out = fopen($out_file, 'w');
  fputs($out, $output);
  fclose($out);
}

d("TOTAL: " . n($total_score));

function get_lights($D, $intersection_plans)
{
  $timespans = [];
  foreach ($intersection_plans as $plan_key => $plan) {
    $timespan = [];
    $index = 0;
    while ($index <= $D) {
      foreach ($plan as $key => $value) {
        array_push($timespan, ...array_fill($index, $value, $key));
        $index += $value;
      }
    }
    $timespans[] = $timespan;
  }
  $result = [];
  for ($i = 0; $i <= $D; $i++) {
    $result[$i] = array_column($timespans, $i);
  }
  return $result;
}

function get_optimized_plan($intersections_jam)
{
  $optimized_plan = [];
  foreach ($intersections_jam as $intersection => $times) {
    $streets_jam = [];
    foreach ($times as $time => $streets) {
      $maxVal = max($streets);
      $maxKey = array_search($maxVal, $streets);
      $streets_jam[$time] = $maxKey;
    }
    $plan = get_intersection_plan($streets_jam);
    $optimized_plan[$intersection] = $plan;
  }
  return $optimized_plan;
}

function get_intersection_plan($streets_jam)
{
  $result = [];
  $prev_value = array('value' => null, 'amount' => null);
  foreach ($streets_jam as $val) {
    if ($prev_value['value'] != $val) {
      unset($prev_value);
      $prev_value = array('value' => $val, 'amount' => 0);
      $result[] = &$prev_value;
    }
    $prev_value['amount']++;
  }

  $plan = [];
  foreach ($result as $key => $pair) {
    $val = $pair['value'];
    if (!isset($plan[$val])) {
      $plan[$val] = $pair['amount'];
    }
  }
  array_normalize($plan, 5);
  return $plan;
}


function array_normalize(&$a, $scale_max)
{
  $scale = max($a) / $scale_max;
  array_walk($a, 'scale_func', $scale);
}

function scale_func(&$n, $key, $scale)
{
  $n = ceil($n / $scale);
}


function n($number)
{
  return number_format($number, 0, '', ',');
}

function d($output)
{
  if (!is_array($output)) {
    print_r($output . PHP_EOL);
  } else {
    print_r($output);
  }
}

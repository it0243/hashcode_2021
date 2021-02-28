<?php

class Solver {

  protected $data;

  public function __construct($data) {
    $this->data  = $data;
  }

  public function solve() {
    $cars = $this->data['cars'];
    $intersections = $this->data['intersections'];
    $V = $this->data['V'];

    usort($cars, function ($a, $b) {
      return $a['time'] > $b['time'];
    });

    // found after euristics
    // F:
    $BEST_CARS_PERCENTAGE = 50;
    $ALL_STREETS_WEIGHT = 20;
    $BEST_STREETS_WEIGHT = 1;

    $best_cars = array_slice($cars, 0, $V/$BEST_CARS_PERCENTAGE);
    $best_streets = array_merge(...array_column($best_cars, 'streets'));
    $best_streets_counts = array_count_values($best_streets);
    $all_streets = array_merge(...array_column($cars, 'streets'));
    $streets_counts = array_count_values($all_streets);

    $plan = [];
    $output = '';
    foreach ($intersections as $key => $incoming_streets) {
      foreach ($incoming_streets as $ind => $name) {
        $weight = $streets_counts[$name] ?? 0;
        if (!$weight) {
          unset($incoming_streets[$ind]);
        }
      }
      if (count($incoming_streets)) {
        usort($incoming_streets, function ($a, $b) use ($streets_counts, $best_streets_counts, $ALL_STREETS_WEIGHT, $BEST_STREETS_WEIGHT) {
          $best_weight_a = $best_streets_counts[$a] ?? 0;
          $weight_a = ceil($streets_counts[$a] / $ALL_STREETS_WEIGHT) + ceil($best_weight_a / $BEST_STREETS_WEIGHT);
          $best_weight_b = $best_streets_counts[$b] ?? 0;
          $weight_b = ceil($streets_counts[$b] / $ALL_STREETS_WEIGHT) + ceil($best_weight_b / $BEST_STREETS_WEIGHT);
          return $weight_a < $weight_b;
        });
        $plan[$key] = [];
        $output .= $key . PHP_EOL;
        $output .= count($incoming_streets) . PHP_EOL;
        foreach ($incoming_streets as $name) {
          $best_weight = $best_streets_counts[$name] ?? 0;
          $weight = ceil($streets_counts[$name] / $ALL_STREETS_WEIGHT) + ceil($best_weight / $BEST_STREETS_WEIGHT);
          $weight = max(1, $weight);
          // $weight = 1;
          $output .= "$name $weight" . PHP_EOL;
          $plan[$key][$name] = $weight;
        }
      }
    }
    $output = count($plan) . PHP_EOL . $output;

    return ['plan' =>$plan, 'output' => $output];
  }

}

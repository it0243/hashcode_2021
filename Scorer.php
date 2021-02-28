<?php

class Scorer {

  protected $data;
  protected $plan;

  public function __construct($data, $plan) {
    $this->data  = $data;
    $this->plan  = $plan;
  }

  /**
   * Calculates the score of the current plan, by simulating the traffic
   * through the duration of the problem.
   */
  public function calculate() {
    $score = 0;

    $plan = $this->plan;
    $cars = $this->data['cars'];
    $streets = $this->data['streets'];
    $D = $this->data['D'];
    $F = $this->data['F'];

    $active_cars = $cars;

    $lights = static::get_lights($D, $plan);
    $traffic = [];
    $queues = [];
    $cars_ended = 0;

    $second = 0;
    $traffic[$second] = [];
    $current_lights = $lights[$second];
    foreach ($active_cars as $car_key => $car) {
      $car_str_index = 0;
      $car_str = $car['streets'][$car_str_index];
      $street = $streets[$car_str];
      $car_pos = $street['time'];
      $car_go = (in_array($car_str, $current_lights) && empty($queues[$car_str]));
      $car_ended = false;
      $queues[$car_str][$car_key] = $car_key;
      $traffic[$second][$car_key] = ['car_str' => $car_str,
        'car_str_index' => $car_str_index, 'car_pos' => $car_pos, 'car_go' => $car_go];
    }

    for ($second = 1; $second <= $D; $second++) {
      $traffic[$second] = [];
      $current_lights = $lights[$second];
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
        $car_go = ($car_pos < $street['time']) || (in_array($car_str, $current_lights)
          && (empty($queues[$car_str]) || array_key_first($queues[$car_str]) == $car_key));
        $car_ended = ($car_str == $car['streets'][$car['streets_count'] - 1]) && $car_pos == $street['time'];
        if ($car_ended) {
          $car_score = $F + ($D - $second);
          $cars[$car_key]['score'] = $car_score;
          $score += $car_score;
          unset($active_cars[$car_key]);
          unset($queues[$car_str][$car_key]);
          $cars_ended++;
        } else {
          $traffic[$second][$car_key] = ['car_str' => $car_str,
            'car_str_index' => $car_str_index, 'car_pos' => $car_pos, 'car_go' => $car_go];
          if ($car_pos == $street['time']) {
            $queues[$car_str][$car_key] = $car_key;
          }
        }
      }
    }
    return $score;
  }

  /**
   * Calculates the lights state (the streets with green lights) for the
   * timespan of the problem for each intersection.
   */
  static function get_lights($D, $intersection_plans) {
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

}

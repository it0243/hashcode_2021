<?php

class Solver2 {

  protected $M;
  protected $T2;
  protected $T3;
  protected $T4;
  protected $pizzas;

  public function __construct($data) {
    $this->M  = $data['M'];
    $this->T2 = $data['T2'];
    $this->T3 = $data['T3'];
    $this->T4 = $data['T4'];
    $this->pizzas = $data['pizzas'];
  }

  public function solve() {
    $deliveries = [];
    $file_score = 0;
    $deliveries_count = 0;
    $available_teams_map = [
      4 => $this->T4,
      3 => $this->T3,
      2 => $this->T2,
    ];
    $available_pizzas = $this->pizzas;
    usort($available_pizzas, function ($a, $b) {
      return $a['count'] < $b['count'];
    });

    // iterate for all team sizes starting from larger
    for ($team_size = 4; $team_size >= 2; $team_size--) {
      $available_teams = $available_teams_map[$team_size];
      // 1. there should be more available pizzas than the team size (delivery count of pizzas)
      // 2. also do not leave one pizza out, aka if available pizzas are 5, choose 2+3 instead of 4+1, as the one left will remain unused
      // 3. the available teams for a specific team size should be > 0
      while (count($available_pizzas) >= $team_size && count($available_pizzas) - $team_size <> 1 && $available_teams > 0) {
        $available_teams--;
        $delivery_pizzas = [];
        $delivery_ingredients = [];
        for ($i = 0; $i < $team_size; $i++) {
          $pizza = array_shift($available_pizzas);
          $delivery_pizzas[] = $pizza['id'];
          $delivery_ingredients = array_unique(array_merge($delivery_ingredients, $pizza['ingredients']));
        }
        // score of the delivery is the square of unique ingredients
        $delivery_score = pow(count($delivery_ingredients), 2);
        $size = count($delivery_pizzas);
        $deliveries[$deliveries_count] = ['id' => $deliveries_count, 'pizzas' => $delivery_pizzas, 'size' => $size, 'score' => $delivery_score];
        $deliveries_count++;
      }
    }

    static::optimizeScore($deliveries);

    $output = "$deliveries_count\n";
    foreach ($deliveries as $delivery) {
      $size = $delivery['size'];
      $pizzas_arr = implode(' ', $delivery['pizzas']);
      $output .= "$size $pizzas_arr\n";
      $file_score += $delivery['score'];
    }

    return ['score' => $file_score, 'output' => $output];

  }

  static function optimizeScore(&$deliveries) {
    $score = 0;
    $deliveries_count = count($deliveries);
    foreach ($deliveries as $delivery) {
      $score += $delivery['score'];
    }
    $max_tries = 1000;
    $tries = 0;
    for ($i=0; $i < $deliveries_count && $tries < $max_tries; $i++) {
      for ($j = 0; $j <> $i && $j < $deliveries_count; $j++) {
        $a = &$deliveries[$i];
        $b = &$deliveries[$j];
        $a_pizzas = &$a['pizzas'];
        $b_pizzas = &$b['pizzas'];
        $max_index = min($deliveries[$i]['size'], $deliveries[$j]['size']);
        for ($pos=0; $pos < $max_index; $pos++) {
          $temp = $a_pizzas[$pos];
          $a_pizzas[$pos] = $b_pizzas[$pos];
          $b_pizzas[$pos] = $temp;
          $tries++;
        }
      }
    }
  }

  static function swap_array_values(&$a, &$b, $position) {
    $temp = $a[$position];
    $a[$position] = $b[$position];
    $b[$position] = $temp;
  }

}

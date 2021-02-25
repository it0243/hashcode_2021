<?php

class Reader {

  protected $filename;

  public function __construct($filename) {
    $this->filename = $filename;
  }

  public function read() {
    // Open file
    $f = fopen($this->filename, "r");
    // Read first line (parameters)
    list($M, $T2, $T3, $T4) = explode(' ', trim(fgets($f)));

    // Read all available pizzas
    $pizzas = [];
    for ($i = 0; $i < $M; $i++) {
      // Get ingredients as an array
      $pizza_description = explode(' ', trim(fgets($f)));
      // Remove the first element which is the number of ingredients
      $ingredient_count = array_shift($pizza_description);
      $pizzas[$i] = [
        'id' => $i,
        'ingredients' => $pizza_description,
        'count' => $ingredient_count
      ];
    }
    fclose($f);

    $data = ['M' => $M, 'T2' => $T2, 'T3' => $T3, 'T4' => $T4, 'pizzas' => $pizzas];
    return $data;
  }
}

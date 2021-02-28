<?php

class Reader {

  protected $filename;

  public function __construct($filename) {
    $this->filename = $filename;
  }

  public function read() {
    $f = fopen($this->filename, "r");

    // Read 1st line parameters
    list($D, $I, $S, $V, $F) = explode(' ', trim(fgets($f)));

    // Read streets
    $streets = [];
    $intersections = [];
    for ($i = 0; $i < $S; $i++) {
      list($start, $end, $street_name, $time) = explode(' ', trim(fgets($f)));
      $streets[$street_name] = [
        'start' => $start,
        'end'   => $end,
        'name'  => $street_name,
        'time'  => $time,
      ];
      $intersections[$end][] = $street_name;
    }

    // Read cars
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
          'time'  => $time,
        ];
      }
    }

    fclose($f);

    $data = ['D' => $D, 'I' => $I, 'S' => $S, 'V' => $V, 'F' => $F, 'intersections' => $intersections, 'streets' => $streets, 'cars' => $cars];
    return $data;
  }
}

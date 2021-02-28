# Google Hash Code 2021 Problem in PHP

[Problem statement](https://hashcodejudge.withgoogle.com/download/blob/AMIfv94X6tBYG-yBohlbYh13va4qVOgVrUYeBg3D-EzqvupLro-MQ1-zYFBxlC7Zi91osWW_LLazfyN-WJ7aF0fjzvRW1lwxv5Djf4ta9KElHlq3rPnzojHT7HYUvt8Ymi_yU1hYg0zU8ydfciGo8GY32puuPgkX4R3JbBgkxE7VbuuRCc2YJAe9iJ5L__MLnkvttuFC7iDnHLJq7wbDQ9YgfMbnhNQs2l4K1mFm4VW9xWAxioky7uiCeYb-Res1w4VUH82KbacphAEtTzjpo7GAws8Jp0MRxnM1lypbKc1Mua6_AulffwE)

## Solution

It sorts and assign weights to intersection streets based on some criteria, such as:
* number of cars crossing the streets
* number of good cars (with smaller routes) crossing the streets, etc.
Some parameters of the solution have been adjusted after iterations in order
to optimize the score.

It contains a score calculator that simulates the problem through the timespan.
It has a deviation of about 0,1% compared to the official google score calculator.

The score calculator has been used to adjust the values of the optimization parameters.

The solver has also been modified per input file manually in order to achieve the
best submission in the Hashcode contest for each file.
These manual adjustments are not being reflected in the current file, which shows
just one version of the submitted solutions.
It could have been parameterized to cover all submissions and input files.

## Time
* With score calculator executes in 160 seconds.
* Without score calculator executes in 0.5 seconds.

## Scores

### Current implementation
* A – An example:             1,001 points
* B – By the ocean:       4,566,362 points
* C – Checkmate:          1,298,544 points
* D – Daily commute:      1,435,972 points
* E – Etoile:               681,772 points
* F – Forever jammed:     1,397,066 points
* TOTAL:                  9,380,717 points

### Best submissions
* A – An example:             2,002 points
* B – By the ocean:       4,566,537 points
* C – Checkmate:          1,298,723 points
* D – Daily commute:      1,588,095 points
* E – Etoile:               707,570 points
* F – Forever jammed:     1,397,066 points
* TOTAL:                  9,559,993 points

## Execution instructions

```
php main.php
```

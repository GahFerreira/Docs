<?php

const pi = 3.14159;

$r = (float) readline();
echo 'A=' . number_format($r * $r * pi, 4, '.', '') . PHP_EOL;
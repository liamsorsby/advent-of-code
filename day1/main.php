<?php

$file = file('input.txt');

foreach ($file as $row) {
    foreach ($file as $row2) {
        foreach ($file as $row3) {
            if (((int) $row + (int) $row2 + (int) $row3) === 2020) {
                echo sprintf("%s + %s + %s \n", (int)$row, (int)$row2, (int)$row3);
                echo (int) $row * (int) $row2 * (int) $row3;
                echo "\n";
                exit;
            }
        }
    }
}


<?php
namespace App\Traits;
trait CurrentDateTime{
    public $months = [
        1,
        2,
        3,
        4,
        5,
        6,
        7,
        8,
        9,
        10,
        11,
        12
    ];
    public function getCurrentMonthAndYear(){
        return [
            'year'=> now()->year,
            'month'=> now()->month
        ];
    }

    public function getCurrentMonth(){
        return now()->month;
    }

    public function getCurrentYear(){
        return now()->year;
    }
}

<?php

namespace App\Services;

class Bit2Scorer
{
    protected static array $groups = ['TH','GH','TN','EH','LF','KB','VB','LG','SE'];

    protected static array $questionGroups = [
        1=>'TH',2=>'EH',3=>'VB',4=>'GH',5=>'LF',6=>'LG',7=>'TN',8=>'KB',9=>'SE',
        10=>'TH',11=>'EH',12=>'VB',13=>'GH',14=>'LF',15=>'LG',16=>'TN',17=>'KB',18=>'SE',
        19=>'TH',20=>'EH',21=>'VB',22=>'GH',23=>'LF',24=>'LG',25=>'TN',26=>'KB',27=>'SE',
        28=>'TH',29=>'EH',30=>'VB',31=>'GH',32=>'LF',33=>'LG',34=>'TN',35=>'KB',36=>'SE',
        37=>'TH',38=>'EH',39=>'VB',40=>'GH',41=>'LF',42=>'LG',43=>'TN',44=>'KB',45=>'SE',
        46=>'TH',47=>'EH',48=>'VB',49=>'GH',50=>'LF',51=>'LG',52=>'TN',53=>'KB',54=>'SE',
        55=>'TH',56=>'EH',57=>'VB',58=>'GH',59=>'LF',60=>'LG',61=>'TN',62=>'KB',63=>'SE',
        64=>'TH',65=>'EH',66=>'VB',67=>'GH',68=>'LF',69=>'LG',70=>'TN',71=>'KB',72=>'SE',
        73=>'TH',74=>'EH',75=>'VB',76=>'GH',77=>'LF',78=>'LG',79=>'TN',80=>'KB',81=>'SE',
    ];

    protected static array $maleNormTable = [
        ['percentile' => 100, 'TH' => 43, 'GH' => 45, 'TN' => 45, 'EH' => 44, 'LF' => 45, 'KB' => 42, 'VB' => 45, 'LG' => 44, 'SE' => 45],
        ['percentile' => 95, 'TH' => 27, 'GH' => 41, 'TN' => 36, 'EH' => 38, 'LF' => 39, 'KB' => 34, 'VB' => 34, 'LG' => 37, 'SE' => 42],
        ['percentile' => 90, 'TH' => 24, 'GH' => 38, 'TN' => 34, 'EH' => 35, 'LF' => 36, 'KB' => 31, 'VB' => 31, 'LG' => 35, 'SE' => 41],
        ['percentile' => 85, 'TH' => 22, 'GH' => 37, 'TN' => 31, 'EH' => 34, 'LF' => 34, 'KB' => 29, 'VB' => 29, 'LG' => 32, 'SE' => 39],
        ['percentile' => 80, 'TH' => 20, 'GH' => 36, 'TN' => 28, 'EH' => 32, 'LF' => 32, 'KB' => 28, 'VB' => 27, 'LG' => 31, 'SE' => 38],
        ['percentile' => 75, 'TH' => null, 'GH' => 35, 'TN' => 26, 'EH' => 30, 'LF' => 31, 'KB' => 27, 'VB' => 26, 'LG' => 29, 'SE' => 37],
        ['percentile' => 70, 'TH' => 19, 'GH' => 33, 'TN' => 25, 'EH' => 29, 'LF' => 29, 'KB' => 26, 'VB' => 25, 'LG' => 27, 'SE' => 36],
        ['percentile' => 65, 'TH' => 18, 'GH' => 32, 'TN' => 24, 'EH' => 28, 'LF' => 28, 'KB' => 25, 'VB' => null, 'LG' => 26, 'SE' => null],
        ['percentile' => 60, 'TH' => 17, 'GH' => 31, 'TN' => 23, 'EH' => 27, 'LF' => 27, 'KB' => 24, 'VB' => 24, 'LG' => 25, 'SE' => 35],
        ['percentile' => 55, 'TH' => 16, 'GH' => 29, 'TN' => 22, 'EH' => 26, 'LF' => 26, 'KB' => null, 'VB' => 23, 'LG' => 24, 'SE' => 34],
        ['percentile' => 50, 'TH' => 15, 'GH' => 28, 'TN' => 21, 'EH' => null, 'LF' => 25, 'KB' => 23, 'VB' => 22, 'LG' => 23, 'SE' => 33],
        ['percentile' => 45, 'TH' => 14, 'GH' => 27, 'TN' => 20, 'EH' => 25, 'LF' => 24, 'KB' => null, 'VB' => 21, 'LG' => 22, 'SE' => 32],
        ['percentile' => 40, 'TH' => 13, 'GH' => null, 'TN' => 19, 'EH' => 24, 'LF' => 23, 'KB' => 22, 'VB' => 20, 'LG' => 21, 'SE' => 31],
        ['percentile' => 35, 'TH' => 12, 'GH' => 26, 'TN' => 18, 'EH' => 23, 'LF' => 22, 'KB' => 21, 'VB' => 19, 'LG' => 20, 'SE' => 30],
        ['percentile' => 30, 'TH' => null, 'GH' => 24, 'TN' => 17, 'EH' => 22, 'LF' => 21, 'KB' => 20, 'VB' => 18, 'LG' => 18, 'SE' => 29],
        ['percentile' => 25, 'TH' => 11, 'GH' => 23, 'TN' => 16, 'EH' => 21, 'LF' => 20, 'KB' => 19, 'VB' => 16, 'LG' => 17, 'SE' => 27],
        ['percentile' => 20, 'TH' => 10, 'GH' => 21, 'TN' => 15, 'EH' => 20, 'LF' => 18, 'KB' => 18, 'VB' => 15, 'LG' => 16, 'SE' => 26],
        ['percentile' => 15, 'TH' => 9,  'GH' => 20, 'TN' => 14, 'EH' => 19, 'LF' => 17, 'KB' => 17, 'VB' => 13, 'LG' => 15, 'SE' => 24],
        ['percentile' => 10, 'TH' => null, 'GH' => 17, 'TN' => 12, 'EH' => 17, 'LF' => 16, 'KB' => 15, 'VB' => 11, 'LG' => 14, 'SE' => 22],
        ['percentile' => 5,  'TH' => null, 'GH' => 15, 'TN' => 11, 'EH' => 14, 'LF' => 14, 'KB' => 13, 'VB' => 9,  'LG' => 12, 'SE' => 18],
        ['percentile' => 0,  'TH' => null, 'GH' => 9,  'TN' => 9,  'EH' => 9,  'LF' => 9,  'KB' => 9,  'VB' => null, 'LG' => 9,  'SE' => 10],
    ];

    protected static array $femaleNormTable = self::$maleNormTable;

    protected static function normTable(?string $sex): array
    {
        return $sex === 'f' ? self::$femaleNormTable : self::$maleNormTable;
    }

    public static function score(array $answers, ?string $sex): array
    {
        $totals = array_fill_keys(self::$groups, 0);
        foreach ($answers as $entry) {
            $num = $entry['number'] ?? null;
            $ans = $entry['answer'] ?? null;
            if ($num === null || $ans === null) {
                continue;
            }
            $group = self::$questionGroups[$num] ?? null;
            if ($group) {
                $totals[$group] += (int)$ans;
            }
        }

        $percentiles = [];
        $table = self::normTable($sex);
        foreach ($totals as $group => $raw) {
            $percent = null;
            foreach ($table as $row) {
                $threshold = $row[$group] ?? null;
                if ($threshold !== null && $raw >= $threshold) {
                    $percent = $row['percentile'];
                    break;
                }
            }
            $percentiles[$group] = $percent;
        }

        return [
            'group_totals' => $totals,
            'group_percentiles' => $percentiles,
            'answers' => $answers,
        ];
    }
}


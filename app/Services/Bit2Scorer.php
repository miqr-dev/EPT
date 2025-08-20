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

    // Norm tables mapping raw sums to percentile ranks.
    // Actual values to be filled once official tables are available.
    protected static array $normTables = [
        'male' => [],
        'female' => [],
    ];

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
        $sexKey = strtolower($sex ?? '') === 'f' ? 'female' : 'male';
        $table = self::$normTables[$sexKey] ?? [];
        foreach ($totals as $group => $raw) {
            $percentiles[$group] = $table[$group][$raw] ?? null;
        }

        return [
            'group_totals' => $totals,
            'group_percentiles' => $percentiles,
            'answers' => $answers,
        ];
    }
}


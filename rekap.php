<?php

foreach (glob('*.html') as $filename) {
    rekap($filename);
}

function rekap($filename)
{
    echo $filename . PHP_EOL;
    echo "====================" . PHP_EOL;

    $html = file_get_contents($filename);

    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML($html);
    libxml_clear_errors();

    $xpath = new DOMXPath($doc);

    $accounts = $xpath->query('//span[contains(@class, "_ap3a _aaco _aacw _aacx _aad7 _aade")]');
    $comments = $xpath->query('//div[contains(@class, "x9f619 xjbqb8w x78zum5 x168nmei x13lgxp2 x5pf9jr xo71vjh x1uhb9sk x1plvlek xryxfnj x1c4vz4f x2lah0s xdt5ytf xqjyukv x1cy8zhl x1oa3qoh x1nhvcw1")]');

    $duplicates = [];
    $valid = [];
    $totals = [];
    foreach ($accounts as $key => $account) {
        if (!$account->nodeValue || !$comments->item($key)) {
            continue;
        }

        $comment = $comments->item($key)->nodeValue;
        preg_match_all('/\d+/', $comment, $matches);
        $vote = str_replace('0', '', (string) implode('', $matches[0]));

        $accountName = trim($account->nodeValue);
        $voteNumber = (int) $vote;

        if (isset($valid[$accountName])) {
            $duplicates[$accountName] ??= $valid[$accountName];
            $duplicates[$accountName] .= "|$voteNumber";
        } else if ($accountName && $voteNumber) {
            if ($voteNumber > 3 || $voteNumber < 1) {
                $comment = preg_replace('/[\s+\r\n]/', '', trim($comment));
                echo "    $accountName: $comment" . PHP_EOL;
            } else {
                $valid[$accountName] = $voteNumber;

                $totals[$voteNumber] ??= 0;
                $totals[$voteNumber]++;
            }
        }
    }

    if (!empty($duplicates)) {
        echo "Duplicates: " . PHP_EOL;
        foreach ($duplicates as $accountName => $votes) {
            echo "    $accountName: $votes" . PHP_EOL;
        }
    }

    echo "Summary: " . PHP_EOL;
    foreach ($totals as $number => $total) {
        echo "    $number: $total" . PHP_EOL;
    }

    echo PHP_EOL;
}

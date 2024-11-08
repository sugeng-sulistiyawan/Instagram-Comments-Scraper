<?php

foreach (glob('*.json') as $filename) {
    rekap($filename);
}

function rekap($filename)
{
    echo $filename . PHP_EOL;
    echo "====================" . PHP_EOL;

    $json = file_get_contents($filename);
    $data = json_decode($json, true);

    $invalids = [];
    $duplicates = [];
    $bots = [];
    $valid = [];
    $totals = [];
    foreach ($data as $key => $d) {

        $username = $d['username'];
        $comment = $d['text'];
        $timestamp = $d['timestamp'];
        $isBot = $d['is_bot'] ?? false;
        $utcDateTime = new DateTime($timestamp, new DateTimeZone('UTC'));
        $utcPlus7DateTime = $utcDateTime->setTimezone(new DateTimeZone('Asia/Jakarta'));
        $date = $utcPlus7DateTime->format('Y-m-d H:i:s');

        if ($date > '2024-11-10 09:00:00') {
            continue;
        }

        // echo "{$date} {$username}: {$comment} | UTC {$timestamp}" . PHP_EOL;

        preg_match_all('/\d+/', $comment, $matches);
        $vote = strtr((string) implode('', $matches[0]), [
            '0' => '',
            '33' => '3',
        ]);
        $voteNumber = (int) $vote;

        if (isset($valid[$username])) {
            $duplicates[$username] ??= $valid[$username];
            $duplicates[$username] .= "|$voteNumber";
        } else if ($username && $voteNumber) {
            if ($voteNumber > 3 || $voteNumber < 1) {
                $comment = preg_replace('/[\s+\r\n]/', '', trim($comment));
                $invalids[] = "$username: $comment";
            } else {
                $valid[$username] = $voteNumber;

                $totals[$voteNumber] ??= 0;
                $totals[$voteNumber]++;
            }
        }

        // if ($isBot) {
        //     $bots[] = $username;
        // }
    }

    if (!empty($invalids)) {
        echo "Invalids: " . PHP_EOL;
        foreach ($invalids as $invalid) {
            echo "    $invalid" . PHP_EOL;
        }
    }

    if (!empty($bots)) {
        echo "Bots: " . PHP_EOL;
        foreach ($bots as $bot) {
            echo "    $bot" . PHP_EOL;
        }
    }

    if (!empty($duplicates)) {
        echo "Duplicates: " . PHP_EOL;
        foreach ($duplicates as $username => $votes) {
            echo "    $username: $votes" . PHP_EOL;
        }
    }

    ksort($totals);

    echo "Summary: " . PHP_EOL;
    foreach ($totals as $number => $total) {
        echo "    $number: $total" . PHP_EOL;
    }

    echo PHP_EOL;
}

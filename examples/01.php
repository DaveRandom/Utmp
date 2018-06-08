<?php declare(strict_types=1);

namespace DaveRandom\Utmp;

require __DIR__ . '/../vendor/autoload.php';

$data = \file_get_contents('/var/run/utmp');

try {
    $records = Record::parsePackedRecordSet($data);
} catch (UnpackFailedException $e) {
    exit("Parse failed");
}

foreach ($records as $i => $record) {
    $timeStr = \sprintf("%d %06d", $record->sessionTime->seconds, $record->sessionTime->microseconds);
    $time = \DateTime::createFromFormat('U u', $timeStr);

    $ipAddress = \substr($record->ipAddress, 4) === "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00"
        ? \inet_ntop(\substr($record->ipAddress, 0, 4))
        : \inet_ntop($record->ipAddress);

    echo "Record {$i}\n";
    echo "  Type: " . Types::parseValue($record->type) . "\n";
    echo "  PID:     {$record->pid}\n";
    echo "  Line:    {$record->line}\n";
    echo "  ID:      {$record->id}\n";
    echo "  User:    {$record->user}\n";
    echo "  Host:    {$record->host}\n";
    echo "  Exit Status:\n";
    echo "      Term: {$record->exitStatus->terminationStatus}:\n";
    echo "      Exit: {$record->exitStatus->exitStatus}:\n";
    echo "  Session: {$record->sessionId}\n";
    echo "  Time:    {$time->format('Y-m-d H:i:s')}\n";
    echo "  IP Addr: {$ipAddress}\n";
    echo "\n";
}


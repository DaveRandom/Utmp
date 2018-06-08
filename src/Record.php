<?php declare(strict_types=1);

namespace DaveRandom\Utmp;

final class Record
{
    private const SIZE = 384;

    private const UT_LINESIZE = 32;
    private const UT_NAMESIZE = 32;
    private const UT_HOSTSIZE = 256;

    /*
     * This map should me a more readable/understandable way to write the unpack spec
     *
     * All int specifiers use machine byte order (?)
     */
    private const FORMAT = [
        'type'    => 's',   // signed (?) short, 2 bytes
        'allign'  => 'x2',  // alignment, 2 bytes
        'pid'     => 'I',   // pid_t is based on unsigned int, 4 bytes
        'line'    => 'Z' . self::UT_LINESIZE, // 32 bytes
        'id'      => 'Z4',  // 4 bytes
        'user'    => 'Z' . self::UT_NAMESIZE, // 32 bytes
        'host'    => 'Z' . self::UT_HOSTSIZE, // 256 bytes
        'exit'    => 's2',  // 2 short ints, 4 bytes
        'session' => 'l',   // signed int32, 4 bytes
        'time'    => 'l2',  // 2 x signed int32, 8 bytes
        'ipaddr'  => 'a16', // 16 bytes
        'unused'  => 'a20', // 20 bytes
    ];

    /** @var int */
    public $type;
    /** @var int */
    public $pid;
    /** @var string */
    public $line;
    /** @var string */
    public $id;
    /** @var string */
    public $user;
    /** @var string */
    public $host;
    /** @var ExitStatus */
    public $exitStatus;
    /** @var int */
    public $sessionId;
    /** @var TimeValue */
    public $sessionTime;
    /** @var int[] */
    public $ipAddress;

    /*
     * Convert the format array to a string that unpack() understands
     */
    private static function getUnpackString(): string
    {
        $result = [];

        foreach (self::FORMAT as $name => $spec) {
            $result[] = "{$spec}{$name}";
        }

        return \implode('/', $result);
    }

    /**
     * @throws UnpackFailedException
     */
    public static function createFromPackedData(string $data, int $offset = 0): self
    {
        if (false === $fields = \unpack(self::getUnpackString(), $data, $offset)) {
            throw new UnpackFailedException();
        }

        $result = new self;

        $result->type = $fields['type'];

        $result->pid = $fields['pid'];
        $result->line = $fields['line'];

        $result->id = $fields['id'];
        $result->user = $fields['user'];
        $result->host = $fields['host'];

        $result->exitStatus = new ExitStatus;
        $result->exitStatus->terminationStatus = $fields['exit1'];
        $result->exitStatus->exitStatus = $fields['exit2'];

        $result->sessionId = $fields['session'];

        $result->sessionTime = new TimeValue;
        $result->sessionTime->seconds = $fields['time1'];
        $result->sessionTime->microseconds = $fields['time2'];

        $result->ipAddress = $fields['ipaddr'];

        return $result;
    }

    /**
     * @return Record[]
     * @throws UnpackFailedException
     */
    public static function parsePackedRecordSet(string $data): array
    {
        $result = [];

        for ($pos = 0, $length = \strlen($data); $pos < $length; $pos += self::SIZE) {
            $result[] = self::createFromPackedData($data, $pos);
        }

        return $result;
    }
}

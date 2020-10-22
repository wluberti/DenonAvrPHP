<?php declare(strict_types=1);

/*
 * @Author: Wouter Luberti
 * @Copyright: MIT
 *
 * Full Denon protocol list can be found on:
 *    http://assets.eu.denon.com/DocumentMaster/DE/AVR2113CI_1913_PROTOCOL_V02.pdf
 */

class DenonAvr
{
    /** @var string */
    private $ipAddress;
    /** @var int */
    private $portNumber;
    /** @var int */
    private $timeout;
    /** @var int */
    private $errorNumber;
    /** @var string */
    private $errorString;
    /** File Pointer */
    private $socket;

    public function __construct(
        string $ipAddress,
        int $portNumber = 23,
        int $timeout = 3
    ) {
        $this->ipAddress = $ipAddress;
        $this->portNumber = $portNumber;
        $this->timeout = $timeout;
    }

    public function execute(string $command)
    {
        $preparedCommand = sprintf("%s\r", $command);

        $this->connect();

        fwrite($this->socket, $preparedCommand);
        echo 'Wrote:' . $preparedCommand . PHP_EOL;

        $this->close();
    }

    public function volume(int $volume)
    {
        if ($volume >= 0 && $volume <= 98) {
            $this->execute(sprintf('MV%s', $volume));
        }
    }

    public function powerOff()
    {
        $this->execute('PWSTANDBY');
    }

    public function powerOn()
    {
        // $this->execute('PWON');
        $this->execute('ZMON');
    }

    public function changeInput(string $case)
    {
        $validCases = [
            'CD',
            'BD',
            'CABSAT',
            'MPLAY',
            'AUX1',
        ];

        if (in_array(strtoupper($case), $validCases)){
            $this->execute(sprintf('SI%s', $case));
        } else {
            throw new Exception(sprintf(
                'No input found for: %s' . PHP_EOL,
                $case
            ));
        }
    }

    public function startUp(string $case = 'CD')
    {
        $this->powerOn();
        sleep(3);
        $this->changeInput($case);
        sleep(1);
        $this->volume(50);
    }

    private function connect()
    {
        try {
            $this->socket = fsockopen($this->ipAddress, $this->portNumber, $this->errorNumber, $this->errorString, $this->timeout);
            // echo 'Connected...' . PHP_EOL;
        } catch (Exception $exception) {
            throw new Exception(sprintf(
                'Could not connect to "%s:%s". Error: %s',
                $this->ipAddress,
                $this->portNumber,
                $exception->getMessage()
            ));
        }
    }

    private function close()
    {
        fclose($this->socket);
        // echo 'Disconnected...' . PHP_EOL;
    }
}

<?php

namespace Bitrix\Calendar\Core\Queue\Impl;

use Bitrix\Calendar\Core\Queue\Interfaces;

/**
 * The ConsumerPollingTrait is a common implementation of the polling algorithm for a Consumer
 */
trait ConsumerPollingTrait
{
    /**
     * Polling interval in milliseconds
     * @var integer
     */
    protected int $pollingInterval = 1000;

    /**
     * Set polling interval in milliseconds.
     */
    public function setPollingInterval(int $msec): self
    {
        $this->pollingInterval = $msec;
        return $this;
    }

    /**
     * Get polling interval in milliseconds.
     */
    public function getPollingInterval(): int
    {
        return $this->pollingInterval;
    }

    public function receive(int $timeout = 0): ?Interfaces\Message
    {
        $timeout *= 1000; // from milliseconds to microseconds
        $startAt = microtime(true);

        while(true)
        {
            $message = $this->receiveNoWait();

            if($message)
			{
                return $message;
            }

            if($timeout)
			{

                $timeSpent = microtime(true) - $startAt;
                $timeSpent *= 1000000; // from seconds to microseconds
                $timeLeft = $timeout - $timeSpent;

                // No time left to wait
                if($timeLeft <= 0)
				{
                    return null;
                }

                // We pay attention not to wait too long to go over the timeout limit
                $sleep = min($timeLeft, $this->pollingInterval * 1000);

            }
			else
			{
                $sleep = $this->pollingInterval * 1000;
            }

            usleep((int)$sleep);
        }
    }
}
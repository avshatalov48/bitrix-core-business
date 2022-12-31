<?php

namespace Bitrix\Calendar\Core\Queue\Impl;

/**
 * The ConsumerVisibilityTimeoutTrait implements message visibility timeout for a Consumer
 * supporting an acknowledgement mechanism
 */
trait ConsumerVisibilityTimeoutTrait
{
    /**
     * @var int|null
     */
    private ?int $visibilityTimeout = null;

    public function getVisibilityTimeout(): ?int
    {
        return $this->visibilityTimeout;
    }

    /**
     * The duration (in seconds) that the received messages are hidden from subsequent retrieve
     * requests after being retrieved by a ReceiveMessage request.
     */
    public function setVisibilityTimeout(int $visibilityTimeout = null): void
    {
        $this->visibilityTimeout = $visibilityTimeout;
    }
}

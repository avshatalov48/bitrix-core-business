<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Transport;

use Bitrix\Main\ArgumentException;
use Bitrix\Sender\Message;

/**
 * Class Transport
 * @package Bitrix\Sender\Transport
 */
class Adapter implements iBase, iLimitation
{
	/** @var  iBase $transport Transport. */
	protected $transport;

	/** @var  Adapter[] $list List. */
	protected static $list;

	/** @var  boolean $startResult Start result. */
	protected $startResult = null;

	/** @var  boolean $isEnded Is ended. */
	protected $isEnded = false;

	/** @var  iLimiter[] $limiters Limiters. */
	protected $limiters = null;

	/** @var  integer $sendCount Count of send. */
	protected $sendCount = 0;

	/**
	 * Get instance.
	 *
	 * @param string $code Code.
	 *
	 * @return Adapter
	 */
	public static function getInstance($code)
	{
		return isset(self::$list[$code]) ? self::$list[$code] : self::create($code);
	}

	/**
	 * Create.
	 *
	 * @param string $code Code.
	 * @return static
	 * @throws ArgumentException
	 */
	public static function create($code)
	{
		$transport = Factory::getTransport($code);
		/** @var IBase $transport Transport. */
		if (!$transport)
		{
			throw new ArgumentException($code);
		}

		return new static($transport);
	}

	/**
	 * Transport constructor.
	 *
	 * @param iBase $transport Transport.
	 */
	public function __construct(iBase $transport)
	{
		$this->transport = $transport;
	}

	/**
	 * Get name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->transport->getName();
	}

	/**
	 * Get code.
	 *
	 * @return string
	 */
	public function getCode()
	{
		return $this->transport->getCode();
	}

	/**
	 * Get supported recipient types.
	 *
	 * @return integer[]
	 */
	public function getSupportedRecipientTypes()
	{
		return $this->transport->getSupportedRecipientTypes();
	}

	/**
	 * Set count of send.
	 *
	 * @param integer $sendCount Count of send.
	 * @return $this
	 */
	public function setSendCount($sendCount)
	{
		$this->sendCount = $sendCount;
		return $this;
	}

	/**
	 * Get count of send.
	 *
	 * @return integer
	 */
	public function getSendCount()
	{
		return $this->sendCount;
	}

	/**
	 * Load configuration.
	 *
	 * @return Message\Configuration
	 */
	public function loadConfiguration()
	{
		return $this->transport->loadConfiguration();
	}

	/**
	 * Save configuration.
	 *
	 * @param Message\Configuration $configuration Configuration.
	 */
	public function saveConfiguration(Message\Configuration $configuration)
	{
		$this->transport->saveConfiguration($configuration);
	}

	/**
	 * Start.
	 */
	public function start()
	{
		if ($this->startResult !== null)
		{
			return $this->startResult;
		}

		$startResult = $this->transport->start();
		if ($startResult === null)
		{
			$startResult = true;
		}
		$this->startResult = $startResult;

		return $this->startResult;
	}

	/**
	 * Send message.
	 *
	 * @param Message\Adapter $message Message.
	 *
	 * @return bool
	 */
	public function send(Message\Adapter $message)
	{
		$this->start();
		return $this->transport->send($message);
	}

	/**
	 * End.
	 */
	public function end()
	{
		if ($this->isEnded)
		{
			return;
		}

		$this->transport->end();
		$this->isEnded = true;
	}

	/**
	 * Destroy.
	 */
	public function __destroy()
	{
		$this->end();
	}

	/**
	 * Get send duration in seconds.
	 *
	 * @param Adapter|null $message Message.
	 * @return integer
	 */
	public function getDuration($message = null)
	{
		if (!($this->transport instanceof iDuration))
		{
			return 0;
		}

		return $this->transport->getDuration($message);
	}

	/**
	 * Has limiters.
	 *
	 * @return bool
	 */
	public function hasLimiters()
	{
		return count($this->getLimiters()) > 0;
	}

	/**
	 * Get limiters.
	 *
	 * @param Message\iBase $message Message.
	 * @return iLimiter[]
	 */
	public function getLimiters(Message\iBase $message = null)
	{
		if ($this->limiters === null)
		{
			if (!($this->transport instanceof iLimitation))
			{
				$this->limiters = array();
			}
			else
			{
				$this->limiters = $this->transport->getLimiters($message);
			}
		}

		return $this->limiters;
	}

	/**
	 * Check limit exceeding.
	 *
	 * @param Message\iBase $message Message.
	 * @return bool
	 */
	public function isLimitsExceeded(Message\iBase $message = null)
	{
		foreach ($this->getLimiters($message) as $limiter)
		{
			if ($limiter->getCurrent() < $limiter->getLimit())
			{
				continue;
			}

			return true;
		}

		return false;
	}
}
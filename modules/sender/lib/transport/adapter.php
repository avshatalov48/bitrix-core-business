<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Transport;

use Bitrix\Main\ArgumentException;
use Bitrix\Sender\Consent\AbstractConsentMessageBuilder;
use Bitrix\Sender\Consent\ConsentMessageBuilderFactory as ConsentFactory;
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


	/** @var  boolean $isStarted Is started. */
	protected $isStarted = false;

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
	public function getConsentMaxRequests()
	{
		if($this->isConsentSupported())
		{
			return $this->transport->getConsentMaxRequests();
		}
		return null;
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
		if (!$this->isStarted && $this->getSendCount())
		{
			\Bitrix\Sender\Log::stat('sending_started', $this->transport->getCode(), $message->getId());
		}
		$this->start();
		$this->isStarted = true;

		$result = $this->transport->send($message);
		\Bitrix\Sender\Log::stat('item_sent', $message->getId(), $result ? 'Y' : 'N');
		return $result;
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
	 * check if consent supported by transport and check is consent need
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	public function isConsentAvailable()
	{
		return $this->isConsentSupported();
	}

	/**
	 * check if consent messaging supported by this transport
	 * @return bool
	 */
	public function isConsentSupported()
	{
		return 	$this->transport instanceof iConsent;
	}

	/**
	 * Send consent message to contact
	 *
	 * @param array $data
	 *
	 * @return bool|null
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	public function sendConsent($message, $data) : ?bool
	{
		if(!$this->isConsentAvailable())
		{
			return false;
		}

		$builder = ConsentFactory::getConsentBuilder($this->transport::CODE)->setFields($data);
		return $this->sendConsentByBuilder($message, $builder);
	}

	/**
	 * Send Test Consent Message
	 * @param $data
	 *
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	public function sendTestConsent($message, $data): bool
	{
		if(!$this->isConsentSupported())
		{
			return false;
		}
		$builder = ConsentFactory::getTestMessageConsentBuilder($this->transport::CODE)->setFields($data);
		return $this->sendConsentByBuilder($message, $builder);
	}

	protected function sendConsentByBuilder(Message\Adapter $message,  AbstractConsentMessageBuilder $builder): bool
	{
		return $builder? $this->transport->sendConsent($message, $builder) : false;
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

	/**
	 * Check limit exceeding and returns iLimiter.
	 *
	 * @param Message\iBase|null $message Message.
	 * @return iLimiter|null
	 */
	public function getExceededLimiter(Message\iBase $message = null)
	{
		foreach ($this->getLimiters($message) as $limiter)
		{
			if ($limiter->getCurrent() < $limiter->getLimit())
			{
				continue;
			}

			return $limiter;
		}

		return null;
	}
}
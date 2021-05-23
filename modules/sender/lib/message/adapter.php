<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Message;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\SiteTable;
use Bitrix\Sender\Integration;
use Bitrix\Sender\Transport;

/**
 * Class Adapter
 * @package Bitrix\Sender\Message
 */
class Adapter implements iBase
{
	/** @var  static[] $list List. */
	protected static $list;

	/** @var iBase $message Message. */
	protected $message;

	/** @var  Tester $tester tester. */
	protected $tester;

	/** @var Configuration $configuration Configuration. */
	protected $configuration;

	/** @var Transport\Adapter $transport Transport. */
	protected $transport;

	/** @var array $fields Fields. */
	protected $fields = array();

	/** @var string|null $siteId Site ID. */
	protected $siteId = null;

	/** @var array|null $siteData Site data. */
	protected $siteData = null;

	/** @var string $recipientCode Recipient code. */
	protected $recipientCode;

	/** @var string $recipientId Recipient ID. */
	protected $recipientId;

	/** @var string $recipientType Recipient type. */
	protected $recipientType;

	/** @var [] $recipientData Recipient data. */
	protected $recipientData;

	/** @var Tracker $readTracker Read tracker. */
	protected $readTracker;

	/** @var Tracker $clickTracker Click tracker. */
	protected $clickTracker;

	/** @var Tracker $unsubTracker Unsubscribe tracker. */
	protected $unsubTracker;

	/**
	 * Get instance.
	 *
	 * @param string $code Message code.
	 * @return static
	 * @throws ArgumentException
	 */
	public static function getInstance($code)
	{
		return isset(self::$list[$code]) ? self::$list[$code] : self::create($code);
	}

	/**
	 * Create instance.
	 *
	 * @param string $code Code.
	 * @return static
	 * @throws ArgumentException
	 */
	public static function create($code)
	{
		$message = Factory::getMessage($code);
		if (!$message)
		{
			throw new ArgumentException($code);
		}

		return new static($message);
	}

	/**
	 * Message constructor.
	 *
	 * @param iBase $message Message.
	 */
	public function __construct(iBase $message)
	{
		$this->message = $message;
		$this->loadConfiguration();

		$this->readTracker = new Tracker(Tracker::TYPE_READ);
		$this->clickTracker = new Tracker(Tracker::TYPE_CLICK);
		$this->unsubTracker = new Tracker(Tracker::TYPE_UNSUB);
	}

	/**
	 * Get name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->message->getName();
	}

	/**
	 * Get transport.
	 *
	 * @return Transport\Adapter
	 */
	public function getTransport()
	{
		if ($this->transport)
		{
			return $this->transport;
		}

		$transportCode = $this->configuration->get('TRANSPORT_CODE') ?: current($this->message->getSupportedTransports());
		//$transportConfigId = $this->configuration->get('TRANSPORT_CONFIGURATION_ID');
		$this->transport = Transport\Adapter::create($transportCode);
		$this->transport->saveConfiguration($this->getConfiguration());
		$this->transport->loadConfiguration();

		return $this->transport;
	}

	/**
	 * Set transport.
	 *
	 * @param Transport\Adapter $transport Transport.
	 * @return void
	 */
	public function setTransport(Transport\Adapter $transport)
	{
		$this->transport = $transport;
	}

	/**
	 * Get code.
	 *
	 * @return string
	 */
	public function getCode()
	{
		return $this->message->getCode();
	}

	/**
	 * Get ID.
	 *
	 * @return string
	 */
	public function getId()
	{
		return $this->configuration->getId();
	}

	/**
	 * Get supported transports.
	 *
	 * @return array
	 */
	public function getSupportedTransports()
	{
		return $this->message->getSupportedTransports();
	}

	/**
	 * Get supported recipient types.
	 *
	 * @return integer[]
	 */
	public function getSupportedRecipientTypes()
	{
		return $this->getTransport()->getSupportedRecipientTypes();
	}

	/**
	 * Is support testing.
	 *
	 * @return Tester
	 */
	public function getTester()
	{
		if (!$this->tester)
		{
			$this->tester = new Tester($this);
		}

		return $this->tester;
	}

	/**
	 * Load configuration.
	 *
	 * @param string|null $id ID.
	 * @return Configuration
	 */
	public function loadConfiguration($id = null)
	{
		$this->configuration = $this->message->loadConfiguration($id);
		return $this->configuration;
		/*
		if (!$this->configuration)
		{
			$this->configuration = $this->message->loadConfiguration($id);
		}

		return $this->configuration;
		*/
	}

	/**
	 * Set configuration data.
	 *
	 * @param array $data Data.
	 * @return void
	 */
	public function setConfigurationData(array $data)
	{
		foreach ($data as $key => $value)
		{
			$this->configuration->set($key, $value);
		}
	}

	/**
	 * Get configuration.
	 *
	 * @return Configuration
	 */
	public function getConfiguration()
	{
		return $this->configuration;
	}

	/**
	 * Save configuration.
	 *
	 * @param Configuration $configuration Configuration.
	 * @return Result
	 */
	public function saveConfiguration(Configuration $configuration)
	{
		$result = $this->message->saveConfiguration($configuration);
		if ($result === null)
		{
			$result = new Result();
		}

		return $result;
	}

	/**
	 * Copy configuration.
	 *
	 * @param integer|string|null $id ID.
	 * @return Result|null
	 */
	public function copyConfiguration($id)
	{
		$result = $this->message->copyConfiguration($id);
		if ($result === null)
		{
			$result = new Result();
		}

		return $result;
	}

	/**
	 * Get field.
	 *
	 * @param string $key Key.
	 * @return mixed|string|null
	 */
	public function getField($key)
	{
		return isset($this->fields[$key]) ? $this->fields[$key] : null;
	}

	/**
	 * Get fields.
	 *
	 * @return array
	 */
	public function getFields()
	{
		return $this->fields;
	}

	/**
	 * Set fields.
	 *
	 * @param array $fields Fields.
	 * @return void
	 */
	public function setFields(array $fields)
	{
		$this->fields = $fields;
	}

	/**
	 * Replace fields in content.
	 *
	 * @param string $content Content.
	 * @param string $replaceChar Replace char.
	 * @return string
	 */
	public function replaceFields($content = "", $replaceChar = '#')
	{
		$from = array();
		$to = array();
		foreach ($this->getFields() as $code => $value)
		{
			$from[] = "$replaceChar$code$replaceChar";
			$to[] = (string) $value;
		}

		return Integration\Sender\Mail\TransportMail::replaceTemplate(str_replace($from, $to, $content));
	}
	/**
	 * Get to.
	 *
	 * @return string
	 */
	public function getTo()
	{
		return $this->recipientCode;
	}

	/**
	 * Get recipient code.
	 *
	 * @return string
	 */
	public function getRecipientCode()
	{
		return $this->recipientCode;
	}

	/**
	 * Set recipient code.
	 *
	 * @param string $code Code.
	 * @return void
	 */
	public function setRecipientCode($code)
	{
		$this->recipientCode = $code;
	}

	/**
	 * Get recipient ID.
	 *
	 * @return string
	 */
	public function getRecipientId()
	{
		return $this->recipientId;
	}

	/**
	 * Set recipient ID.
	 *
	 * @param string $id Recipient ID.
	 * @return void
	 */
	public function setRecipientId($id)
	{
		$this->recipientId = $id;
	}

	/**
	 * Get recipient type.
	 *
	 * @return string
	 */
	public function getRecipientType()
	{
		return $this->recipientType;
	}

	/**
	 * Set recipient type.
	 *
	 * @param string $type Type.
	 * @return void
	 */
	public function setRecipientType($type)
	{
		$this->recipientType = $type;
	}

	/**
	 * Get recipient data.
	 *
	 * @return array
	 */
	public function getRecipientData()
	{
		return $this->recipientData;
	}

	/**
	 * Set recipient data.
	 *
	 * @param array $data Data.
	 * @return void
	 */
	public function setRecipientData(array $data)
	{
		$this->recipientData = $data;
	}

	/**
	 * Get read tracker.
	 *
	 * @return Tracker
	 */
	public function getReadTracker()
	{
		return $this->readTracker;
	}

	/**
	 * Get click tracker.
	 *
	 * @return Tracker
	 */
	public function getClickTracker()
	{
		return $this->clickTracker;
	}

	/**
	 * Get unsub tracker.
	 *
	 * @return Tracker
	 */
	public function getUnsubTracker()
	{
		return $this->unsubTracker;
	}

	/**
	 * Send.
	 *
	 * @return bool
	 */
	public function send()
	{
		if (!$this->getTransport())
		{
			return false;
		}

		return $this->getTransport()->send($this);
	}

	/**
	 * Get send duration.
	 *
	 * @return integer
	 */
	public function getSendDuration()
	{
		if (!$this->getTransport())
		{
			return 0;
		}

		return $this->getTransport()->getDuration($this);
	}

	/**
	 * Set site ID.
	 *
	 * @param string $id ID.
	 * @return void
	 */
	public function setSiteId($id = null)
	{
		$this->siteId = $id;
	}

	/**
	 * Get site ID.
	 *
	 * @return string
	 */
	public function getSiteId()
	{
		$siteData = $this->getSiteData($this->siteId);
		return isset($siteData['LID']) ? $siteData['LID'] : SITE_ID;
	}

	/**
	 * Get charset.
	 *
	 * @return string
	 */
	public function getCharset()
	{
		$siteData = $this->getSiteData($this->siteId);
		return isset($siteData['CHARSET']) ? $siteData['CHARSET'] : SITE_CHARSET;
	}

	/**
	 * Get site ID.
	 *
	 * @return string
	 */
	public function getSiteName()
	{
		$siteData = $this->getSiteData($this->siteId);
		return isset($siteData['SITE_NAME']) ? $siteData['SITE_NAME'] : SITE_ID;
	}

	/**
	 * Get site server name.
	 *
	 * @return string
	 */
	public function getSiteServerName()
	{
		$siteData = $this->getSiteData($this->siteId);
		return isset($siteData['SERVER_NAME']) ? $siteData['SERVER_NAME'] : null;
	}

	/**
	 * Get site data.
	 * @param int $id Id.
	 * @return array
	 * @throws ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function getSiteData($id)
	{
		if ($this->siteData !== null)
		{
			$this->siteData;
		}

		$this->siteData = array();
		$siteDb = SiteTable::getList(array(
			'select'=>array('LID', 'SERVER_NAME', 'NAME', 'CHARSET'=>'CULTURE.CHARSET'),
			'filter' => array('=LID' => $id ?: SITE_ID)
		));
		if($site = $siteDb->fetch())
		{
			$site['SITE_NAME'] = $site['NAME'];
			unset($site['NAME']);
			$this->siteData = $site;
		}

		return $this->siteData;
	}

	/**
	 * Is ads.
	 *
	 * @return bool
	 */
	public function isAds()
	{
		return $this->message instanceof iAds;
	}

	/**
	 * Is ads.
	 *
	 * @return bool
	 */
	public function isMarketing()
	{
		return $this->message instanceof iMarketing;
	}

	/**
	 * Is mailing.
	 *
	 * @return bool
	 */
	public function isMailing()
	{
		return $this->message instanceof iMailable;
	}

	/**
	 * Is return customer.
	 *
	 * @return bool
	 */
	public function isReturnCustomer()
	{
		return $this->message instanceof iReturnCustomer;
	}

	/**
	 * Return true if is hidden.
	 *
	 * @return bool
	 */
	public function isHidden()
	{
		return ($this->message instanceof iHideable && $this->message->isHidden());
	}

	/**
	 * Is available.
	 *
	 * @return bool
	 */
	public function isAvailable()
	{
		if ($this->message instanceof iAds)
		{
			return Integration\Bitrix24\Service::isAdAvailable();
		}
		elseif ($this->message instanceof iReturnCustomer)
		{
			return Integration\Bitrix24\Service::isRcAvailable();
		}
		else
		{
			switch ($this->getCode())
			{
				case iBase::CODE_MAIL:
					return Integration\Bitrix24\Service::isEmailAvailable();

				default:
					return Integration\Bitrix24\Service::isMailingsAvailable();
			}
		}
	}

	/**
	 * Return true if it has statistics.
	 *
	 * @return bool
	 */
	public function hasStatistics()
	{
		switch ($this->getCode())
		{
			case iBase::CODE_MAIL:
				return true;

			default:
				return false;
		}
	}

	/**
	 *  Check value of audio field and prepare it for DB
	 * @param string $optionCode Field code.
	 * @param string $newValue New field value.
	 * @return bool|string
	 */
	public function getAudioValue($optionCode, $newValue)
	{
		if ($this->message instanceof iAudible)
		{
			return $this->message->getAudioValue($optionCode, $newValue);
		}
		return $newValue;
	}

	public function onBeforeStart()
	{
		if ($this->message instanceof iBeforeAfter)
		{
			return $this->message->onBeforeStart();
		}
		return new \Bitrix\Main\Result();
	}

	public function onAfterEnd()
	{
		if ($this->message instanceof iBeforeAfter)
		{
			return $this->message->onAfterEnd();

		}
		return new \Bitrix\Main\Result();
	}

	/**
	 * @inheritDoc
	 */
	public function getEntityCode()
	{
		return $this->message->getEntityCode();
	}
}
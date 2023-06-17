<?php

namespace Bitrix\Pull\Push\Message;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Text\Encoding;
use Bitrix\Pull\Push\Service\BaseService;

abstract class BaseMessage
{
	protected array $deviceTokens = [];
	protected ?string $text = null;
	protected $category;
	protected $badge;
	protected string $sound = "default";
	protected int $expiryValue = 7200;

	protected $customIdentifier;
	protected $title;
	public $customProperties = [];

	public function addRecipient($sDeviceToken)
	{
		$this->deviceTokens[] = $sDeviceToken;
	}

	public function getRecipient($nRecipient = 0)
	{
		if (!isset($this->deviceTokens[$nRecipient]))
		{
			throw new ArgumentException(
				"No recipient at index '{$nRecipient}'"
			);
		}

		return $this->deviceTokens[$nRecipient];
	}

	public function getRecipients()
	{
		return $this->deviceTokens;
	}

	public function setText($sText)
	{
		$this->text = str_replace("\n", " ", $sText);
	}

	public function getText()
	{
		return $this->text;
	}

	public function setTitle(string $sTitle): void
	{
		$this->title = $sTitle;
	}

	public function getTitle()
	{
		return $this->title;
	}

	public function setBadge(int $nBadge): void
	{
		$this->badge = $nBadge;
	}

	public function getBadge()
	{
		return $this->badge;
	}

	public function setSound($sSound = 'default')
	{
		$this->sound = $sSound;
	}

	public function getSound()
	{
		return $this->sound;
	}

	public function setCustomProperty($sName, $mValue)
	{
		$this->customProperties[trim($sName)] = $mValue;
	}

	public function getCustomProperty($sName)
	{
		if (!array_key_exists($sName, $this->customProperties))
		{
			throw new ArgumentException(
				"No property exists with the specified name '{$sName}'."
			);
		}

		return $this->customProperties[$sName];
	}

	public function setExpiry(int $nExpiryValue)
	{
		$this->expiryValue = $nExpiryValue;
	}

	public function getExpiry()
	{
		return $this->expiryValue;
	}

	public function setCustomIdentifier($mCustomIdentifier)
	{
		$this->customIdentifier = $mCustomIdentifier;
	}

	public function getCustomIdentifier()
	{
		return $this->customIdentifier;
	}

	abstract function getBatch();

	/**
	 * @return mixed
	 */
	public function getCategory()
	{
		return $this->category;
	}

	/**
	 * @param mixed $category
	 */
	public function setCategory($category)
	{
		$this->category = $category;
	}

	public function setFromArray(array $messageArray): BaseMessage
	{
		if (is_string($messageArray["TITLE"]) && $messageArray["TITLE"] != "")
		{
			$title = Encoding::convertEncoding($messageArray["TITLE"], SITE_CHARSET, "utf-8");
			$this->setTitle($title);
		}

		$this->setSound('');
		if (is_string($messageArray["MESSAGE"]) && $messageArray["MESSAGE"] != "")
		{
			$text = Encoding::convertEncoding($messageArray["MESSAGE"], SITE_CHARSET, "utf-8");
			$this->setText($text);

			if (is_string($messageArray["SOUND"]) && $messageArray["SOUND"] != "")
			{
				$this->setSound($messageArray["SOUND"]);
			}
		}

		if (isset($messageArray["CATEGORY"]))
		{
			$this->setCategory($messageArray["CATEGORY"]);
		}

		if (array_key_exists("EXPIRY", $messageArray))
		{
			$expiry = (int)$messageArray["EXPIRY"];
			$this->setExpiry($expiry >= 0 ? $expiry : BaseService::DEFAULT_EXPIRY);
		}

		if (isset($messageArray["PARAMS"]))
		{
			$this->setCustomProperty(
				'params',
				(is_array($messageArray["PARAMS"]) ? json_encode($messageArray["PARAMS"]) : $messageArray["PARAMS"])
			);
		}

		if (is_array($messageArray["ADVANCED_PARAMS"]))
		{
			$messageArray["ADVANCED_PARAMS"] = Encoding::convertEncoding($messageArray["ADVANCED_PARAMS"], SITE_CHARSET, "utf-8");
			if (array_key_exists("senderMessage", $messageArray["ADVANCED_PARAMS"]))
			{
				$this->setText("");
			}

			foreach ($messageArray["ADVANCED_PARAMS"] as $param => $value)
			{
				$this->setCustomProperty($param, $value);
			}
		}

		$badge = (int)($messageArray["BADGE"] ?? 0);
		if ($badge >= 0)
		{
			$this->setBadge($badge);
		}
		return $this;
	}
}

<?php
namespace Bitrix\MessageService\Sender;

use Bitrix\Main\Text\Emoji;
use Bitrix\MessageService\MessageStatus;
use Bitrix\MessageService\MessageType;
use Bitrix\MessageService\Providers;

abstract class Base
{
	protected Providers\Informant $informant;
	protected Providers\Initiator $initiator;
	protected Providers\Sender $sender;

	protected int $socketTimeout = 10;
	protected int $streamTimeout = 30;

	/**
	 * @param int $socketTimeout
	 * @return Base
	 */
	public function setSocketTimeout(int $socketTimeout): Base
	{
		$this->socketTimeout = $socketTimeout;
		return $this;
	}

	/**
	 * @param int $streamTimeout
	 * @return Base
	 */
	public function setStreamTimeout(int $streamTimeout): Base
	{
		$this->streamTimeout = $streamTimeout;
		return $this;
	}

	/**
	 * @return bool
	 */
	public static function isSupported()
	{
		return true;
	}

	/** @deprecated  */
	public static function className()
	{
		return get_called_class();
	}

	public function isConfigurable()
	{
		return false;
	}

	public function getType()
	{
		return MessageType::SMS;
	}

	abstract public function getId();

	public function getExternalId()
	{
		return $this->getType().':'.$this->getId();
	}

	/**
	 * @return string
	 */
	abstract public function getName();

	/**
	 * @return string
	 */
	abstract public function getShortName();

	/**
	 * Check can use state of provider.
	 * @return bool
	 */
	abstract public function canUse();

	abstract public function getFromList();

	/**
	 * Get default From.
	 * @return null|string
	 */
	public function getDefaultFrom()
	{
		$fromList = $this->getFromList();
		$from = isset($fromList[0]) ? $fromList[0]['id'] : null;
		//Try to find alphanumeric from
		foreach ($fromList as $item)
		{
			if (!preg_match('#^[0-9]+$#', $item['id']))
			{
				$from = $item['id'];
				break;
			}
		}
		return $from;
	}

	/**
	 * @return mixed|null
	 */
	public function getFirstFromList()
	{
		$fromList = $this->getFromList();
		if (!is_array($fromList))
		{
			return null;
		}

		foreach ($fromList as $item)
		{
			if (isset($item['id']) && $item['id'])
			{
				return $item['id'];
			}
		}

		return null;
	}

	/**
	 * @param string $from
	 * @return bool
	 */
	public function isCorrectFrom($from)
	{
		$fromList = $this->getFromList();
		foreach ($fromList as $item)
		{
			if ($from === $item['id'])
			{
				return true;
			}
		}
		return false;
	}

	/**
	 * @param array $messageFieldsFields
	 * @return Result\SendMessage Send operation result.
	 */
	abstract public function sendMessage(array $messageFieldsFields);

	/**
	 * Converts service status to internal status
	 * @see \Bitrix\MessageService\MessageStatus
	 * @param mixed $serviceStatus
	 * @return int
	 */
	public static function resolveStatus($serviceStatus)
	{
		return MessageStatus::UNKNOWN;
	}

	public function getManageUrl()
	{
		return $this->isConfigurable() ? '/crm/configs/sms/?sender='.$this->getId() : '';
	}

	/**
	 * Prepares text for message body.
	 */
	public function prepareMessageBodyForSave(string $text): string
	{
		return Emoji::encode($text);
	}

	protected function prepareMessageBodyForSend(string $text): string
	{
		return Emoji::decode($text);
	}
}
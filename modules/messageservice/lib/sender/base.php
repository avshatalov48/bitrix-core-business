<?php
namespace Bitrix\MessageService\Sender;

use Bitrix\MessageService\MessageType;

abstract class Base
{
	/**
	 * @return bool
	 */
	public static function isSupported()
	{
		return true;
	}

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
	 * @return null|int
	 */
	public static function resolveStatus($serviceStatus)
	{
		return null;
	}
}
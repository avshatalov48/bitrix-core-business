<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Integration\Im;

use Bitrix\Main\Loader;
use Bitrix\Sender\Security;
use Bitrix\Sender\Entity;

/**
 * Class Notification
 * @package Bitrix\Sender\Integration\Im
 */
class Notification
{
	/** @var array $to */
	protected $to = array();

	/** @var string $message */
	protected $message;

	/**
	 * Can use.
	 *
	 * @return bool|null
	 */
	public static function canUse()
	{
		if (!Loader::includeModule('im'))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Create.
	 *
	 * @return static
	 */
	public static function create()
	{
		return new static();
	}

	/**
	 * Set to.
	 *
	 * @param integer[] $list List.
	 * @return $this
	 */
	public function setTo(array $list)
	{
		$this->to = $list;
		return $this;
	}

	/**
	 * Add to.
	 *
	 * @param integer $userId User ID.
	 * @return $this
	 */
	public function addTo($userId)
	{
		$this->to[] = $userId;
		$this->to = array_unique($this->to);

		return $this;
	}

	/**
	 * Add all authors.
	 *
	 * @return $this
	 */
	public function toAllAuthors()
	{
		$list = Entity\Letter::getList(array(
			'select' => array('CREATED_BY'),
			'filter' => array(
				'=MESSAGE_CODE',
				'!=CREATED_BY' => null
			),
			'group' => array('CREATED_BY'),
			'cache' => array('ttl' => 3600),
		));
		foreach ($list as $item)
		{
			$this->addTo($item['CREATED_BY']);
		}

		return $this;
	}

	/**
	 * Add current user.
	 *
	 * @return $this
	 */
	public function toCurrentUser()
	{
		$this->addTo(Security\User::current()->getId());
		return $this;
	}

	/**
	 * With message.
	 *
	 * @param string $message Text.
	 * @return $this
	 */
	public function withMessage($message)
	{
		$this->message = $message;
		return $this;
	}

	/**
	 * Send.
	 *
	 * @return void
	 */
	public function send()
	{
		if (!static::canUse())
		{
			return;
		}

		if (count($this->to) === 0 || !$this->message)
		{
			return;
		}

		foreach ($this->to as $userId)
		{
			$fields = array(
				"TO_USER_ID" => $userId,
				"FROM_USER_ID" => 0,
				"NOTIFY_TYPE" => IM_NOTIFY_SYSTEM,
				"NOTIFY_MODULE" => "sender",
				//"NOTIFY_EVENT" => $imNotifyEvent,
				//"NOTIFY_TAG" => $notifyTag,
				"NOTIFY_MESSAGE" => $this->message
			);
			\CIMNotify::Add($fields);
		}
	}
}
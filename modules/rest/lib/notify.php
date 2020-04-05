<?php
namespace Bitrix\Rest;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;

class Notify
{
	const NOTIFY_IM = 'im';
	const NOTIFY_BOT = 'bot';

	protected $type;
	protected $userList = array();

	/**
	 * @var INotify
	 */
	protected $notifier = null;

	public function __construct($notifyType, array $userList)
	{
		$this->setType($notifyType);
		$this->setUserList($userList);
	}

	public function send($clientId, $token, $method, $message)
	{
		foreach($this->userList as $userId)
		{
			$this->getNotifier()->send($clientId, $userId, $token, $method, $message);
		}
	}

	public function setType($notifyType)
	{
		if($notifyType == static::NOTIFY_IM || $notifyType == static::NOTIFY_BOT)
		{
			$this->type = $notifyType;
		}
		else
		{
			throw new ArgumentException('Wrong notify type', 'type');
		}
	}

	public function getType()
	{
		return $this->type;
	}

	public function setUserList(array $userList)
	{
		if(count($userList) > 0)
		{
			$this->userList = $userList;
		}
		else
		{
			throw new ArgumentNullException('userList');
		}
	}

	public function getUserList()
	{
		return $this->userList;
	}

	/**
	 * @return INotify
	 */
	protected function getNotifier()
	{
		if(!$this->notifier)
		{
			switch($this->type)
			{
				case static::NOTIFY_IM:
					$this->notifier = new NotifyIm();
					break;
				case static::NOTIFY_BOT:
					$this->notifier = new NotifyIm();
					break;
			}
		}

		return $this->notifier;
	}
}
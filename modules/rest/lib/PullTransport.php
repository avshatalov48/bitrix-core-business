<?php

namespace Bitrix\Rest;

use Bitrix\Bitrix24\CurrentUser;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Pull\Event;

class PullTransport implements MessageTransportInterface
{
	/**
	 * Users who receive messages
	 * @var array user id
	 */
	private array $recipients;
	private string $moduleId;
	private int $expiry;

	/**
	 * @throws LoaderException
	 * @throws \Exception
	 */
	public function __construct(array $userIds = null, int $expiry = 0)
	{
		if (!Loader::includeModule('pull'))
		{
			throw new \Exception('Module "pull" not installed');
		}

		if (is_null($userIds))
		{
			$this->recipients = [CurrentUser::get()->getId()];
		}
		else
		{
			$this->recipients = $userIds;
		}
		$this->moduleId = 'rest';
		$this->expiry = $expiry;
	}

	public function send(string $method, array $parameters): bool
	{
		return Event::add($this->recipients, [
				'module_id' => $this->moduleId,
				'command' => $method,
				'params' => $parameters,
				'expiry' => $this->expiry,
			]
		);
	}
}
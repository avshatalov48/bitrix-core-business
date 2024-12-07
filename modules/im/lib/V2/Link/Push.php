<?php

namespace Bitrix\Im\V2\Link;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Rest\RestAdapter;
use Bitrix\Main\Application;

class Push
{
	use ContextCustomer;

	protected const MODULE_ID = 'im';

	protected array $events;
	private static Push $instance;
	private bool $isJobPlanned = false;

	private function __construct()
	{
	}

	public static function getInstance(): self
	{
		if (!isset(self::$instance))
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function sendFull(LinkRestConvertible $link, string $eventName, array $endpoint): void
	{
		$this->send((new RestAdapter($link))->toRestFormat(), $eventName, $endpoint);
	}

	public function sendIdOnly(LinkRestConvertible $link, string $eventName, array $endpoint): void
	{
		$this->send($link->toRestFormatIdOnly(), $eventName, $endpoint);
	}

	protected function send(array $params, string $eventName, array $endpoint): void
	{
		$pull = [
			'module_id' => self::MODULE_ID,
			'command' => $eventName,
			'params' => $params,
			'extra' => \Bitrix\Im\Common::getPullExtra(),
		];
		$event = ['params' => $pull];
		if (isset($endpoint['CHAT_ID']))
		{
			$chat = Chat::getInstance((int)$endpoint['CHAT_ID']);
			if ($chat->getType() === Chat::IM_TYPE_COMMENT)
			{
				$event['tag'] = 'IM_PUBLIC_COMMENT_' . $chat->getParentChatId();
			}
			else
			{
				$event['recipient'] = $chat->getRelations()->getUserIds();
			}
		}
		elseif (isset($endpoint['RECIPIENT']))
		{
			$event['recipient'] = $endpoint['RECIPIENT'];
		}
		else
		{
			return;
		}

		$this->events[] = $event;

		$this->deferRun();
	}

	protected function push(): void
	{
		if (!\Bitrix\Main\Loader::includeModule('pull'))
		{
			return;
		}
		foreach ($this->events as $event)
		{
			if (isset($event['recipient']))
			{
				\Bitrix\Pull\Event::add($event['recipient'], $event['params']);
			}
			if (isset($event['tag']))
			{
				\CPullWatch::AddToStack($event['tag'], $event['params']);
			}
		}
		$this->isJobPlanned = false;
		$this->events = [];
	}

	protected function deferRun(): void
	{
		if (!$this->isJobPlanned)
		{
			Application::getInstance()->addBackgroundJob(function () {
				$this->push();
			});
			$this->isJobPlanned = true;
		}
	}
}
<?php

namespace Bitrix\Im\V2\Link;

use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Rest\RestAdapter;
use Bitrix\Main\Application;

class Push
{
	use ContextCustomer;

	protected const MODULE_ID = 'im';

	protected array $events;
	protected array $recipientByChatId;
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
		if (isset($endpoint['CHAT_ID']))
		{
			$recipient = $this->getRecipientByChatId((int)$endpoint['CHAT_ID']);
		}
		elseif (isset($endpoint['RECIPIENT']))
		{
			$recipient = $endpoint['RECIPIENT'];
		}
		else
		{
			return;
		}

		$this->events[] = [
			'recipient' => $recipient,
			'params' => [
				'module_id' => self::MODULE_ID,
				'command' => $eventName,
				'params' => $params,
				'extra' => \Bitrix\Im\Common::getPullExtra(),
			],
		];

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
			\Bitrix\Pull\Event::add($event['recipient'], $event['params']);
		}
		$this->isJobPlanned = false;
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

	protected function getRecipientByChatId(int $chatId): array
	{
		if (isset($this->recipientByChatId[$chatId]))
		{
			return $this->recipientByChatId[$chatId];
		}

		$relations = \Bitrix\Im\Chat::getRelation($chatId, ['SELECT' => ['ID', 'USER_ID'], 'WITHOUT_COUNTERS' => 'Y']);
		$this->recipientByChatId[$chatId] = array_keys($relations);

		return $this->recipientByChatId[$chatId];
	}
}
<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Anchor\DI;

use Bitrix\Im\V2\Anchor\AnchorProvider;
use Bitrix\Im\V2\Anchor\AnchorService;
use Bitrix\Im\V2\Anchor\Push\PushService;
use Bitrix\Im\V2\Anchor\ReadService;
use Bitrix\Im\V2\Message;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\NotImplementedException;

final class AnchorContainer
{
	private static ?self $instance = null;

	public static function getInstance(): self
	{
		if (self::$instance === null)
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function getAnchorService(Message $message): AnchorService
	{
		return $this->get(
			static fn (): AnchorService => new AnchorService($message),
			'im.anchor.service.' . $message->getId()
		);
	}

	public function getReadService(): ReadService
	{
		return $this->get(ReadService::class, 'im.anchor.read.service');
	}

	public function getAnchorProvider(): AnchorProvider
	{
		return $this->get(AnchorProvider::class, 'im.anchor.provider');
	}

	public function getPushService(): PushService
	{
		return $this->get(PushService::class, 'im.anchor.push.service');
	}

	private function get(string|callable $constructor, string $id, array $args = []): object
	{
		$locator = ServiceLocator::getInstance();

		if (!$locator->has($id))
		{
			$config = [
				'constructorParams' => $args,
				is_callable($constructor) ? 'constructor' : 'className' => $constructor,
			];

			$locator->addInstanceLazy($id, $config);
		}

		return $locator->get($id);
	}


	private function __construct()
	{
	}

	/**
	 * @throws NotImplementedException
	 */
	public function __clone()
	{
		throw new NotImplementedException();
	}

	/**
	 * @throws NotImplementedException
	 */
	public function __serialize(): array
	{
		throw new NotImplementedException();
	}
}
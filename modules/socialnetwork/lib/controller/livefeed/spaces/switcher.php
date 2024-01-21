<?php

namespace Bitrix\Socialnetwork\Controller\Livefeed\Spaces;

use Bitrix\Main\Engine\AutoWire\BinderArgumentException;
use Bitrix\Main\Engine\AutoWire\ExactParameter;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Socialnetwork\Integration\Pull\PushService;
use Bitrix\Socialnetwork\Internals\EventService\Push\PushEventDictionary;
use Bitrix\Socialnetwork\Space\List\Item\Space;
use Bitrix\Socialnetwork\Space\Toolbar\Switcher\Factory\SwitcherFactory;
use Bitrix\Socialnetwork\Space\Toolbar\Switcher\SwitcherInterface;

class Switcher extends Controller
{
	private int $userId;

	/**
	 * @throws BinderArgumentException
	 */
	public function getAutoWiredParameters(): array
	{
		return [
			new ExactParameter(
				SwitcherInterface::class,
				'switcher',
				fn (string $className, array $switcher): SwitcherInterface =>
				SwitcherFactory::get($switcher['type'], $this->userId, $switcher['spaceId'])
			),
			new ExactParameter(
				Space::class,
				'space',
				fn (string $className, int $space): Space => (new Space())->setId($space)
			)
		];
	}

	/**
	 * @restMethod socialnetwork.api.livefeed.spaces.switcher.pin
	 */
	public function pinAction(SwitcherInterface $switcher, Space $space): ?array
	{
		$result = $switcher->switch();
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());
			return null;
		}

		$this->sendPush(
			PushEventDictionary::EVENT_WORKGROUP_PIN_CHANGED,
			$space->getId(),
			$switcher->isEnabled() ? 'pin' : 'unpin'
		);

		return [
			'mode' => $result->getData()['value'],
			'message' => $result->getData()['message'],
		];
	}

	/**
	 * @restMethod socialnetwork.api.livefeed.spaces.switcher.follow
	 */
	public function followAction(SwitcherInterface $switcher, Space $space): ?array
	{
		$result = $switcher->switch();
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());
			return null;
		}

		$this->sendPush(PushEventDictionary::EVENT_WORKGROUP_SUBSCRIBE_CHANGED, $space->getId());

		return [
			'mode' => $result->getData()['value'],
			'message' => $result->getData()['message'],
		];
	}

	/**
	 * @restMethod socialnetwork.api.livefeed.spaces.switcher.track
	 */
	public function trackAction(SwitcherInterface $switcher, Space $space): ?array
	{
		$result = $switcher->switch();
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());
			return null;
		}

		return [
			'mode' => $result->getData()['value'],
		];
	}

	protected function init(): void
	{
		parent::init();
		$this->userId = CurrentUser::get()->getId();
	}

	private function sendPush(string $command, int $spaceId, string $action = ''): void
	{
		$parameters = [
			'GROUP_ID' => $spaceId,
			'USER_ID' => $this->userId,
		];
		if (!empty($action))
		{
			$parameters['ACTION'] = $action;
		}

		PushService::addEvent(
			[$this->userId],
			[
				'module_id' => 'socialnetwork',
				'command' => $command,
				'params' => $parameters,
			]
		);
	}
}
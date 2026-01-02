<?php

namespace Bitrix\Im\V2\Pull;

use Bitrix\Im\V2\Pull\Dto\Diff;
use Bitrix\Im\V2\Pull\Dto\Group;
use Bitrix\Im\V2\Result;
use Bitrix\Im\V2\Service\Locator;

abstract class BaseEvent implements Event
{
	protected int $expiry;
	protected Sender $sender;
	protected string $moduleId = 'im';
	protected array $basePullParams;
	private array $wrappingParameters;

	public function __construct()
	{
		$this->sender = Locator::getPullSender();
	}

	final public function send(): Result
	{
		return $this->sender->send($this);
	}

	final public function getBase(): array
	{
		return $this->wrapPullParams($this->getBasePullParams());
	}

	/**
	 * @return Group[]
	 */
	final public function getPullByUsers(): array
	{
		$pullByUsers = [];
		$pullParamsByUsers = $this->getPullParamsByUsers();

		foreach ($pullParamsByUsers as $group)
		{
			$pullByUsers[] = new Group($group->getRecipients(), $this->wrapPullParams($group->getParams()));
		}

		return $pullByUsers;
	}

	final public function getMobilePushByUsers(): array
	{
		if (!$this->shouldSendMobilePush())
		{
			return [];
		}

		return []; // TODO: Implement getMobilePushByUsers() method.
	}

	/**
	 * @return Group[]
	 */
	private function getPullParamsByUsers(): array
	{
		$recipients = $this->getRecipients();
		$basePull = $this->getBasePullParams();
		$skippedUserIds = $this->getSkippedUserIds();
		if (!empty($skippedUserIds))
		{
			$recipients = array_diff($recipients, $skippedUserIds);
		}

		$pullParamsByUsers = [];
		foreach ($recipients as $userId)
		{
			$diff = $this->getDiffByUser($userId);
			$key = $diff->getKey();
			$pullParamsByUsers[$key] ??= new Group(params: array_merge($basePull, $diff->params));
			$pullParamsByUsers[$key]->addUser($userId);
		}

		return $pullParamsByUsers;
	}

	/**
	 * @return int[]
	 */
	abstract protected function getRecipients(): array;

	protected function getDiffByUser(int $userId): Diff
	{
		return new Diff($userId);
	}

	final protected function getBasePullParams(): array
	{
		$this->basePullParams ??= $this->getBasePullParamsInternal();

		return $this->basePullParams;
	}

	abstract protected function getBasePullParamsInternal(): array;

	abstract protected function getType(): EventType;

	private function wrapPullParams(array $pullParams): array
	{
		$wrappingParameters = $this->getWrappingParameters();
		$wrappingParameters['params'] = $pullParams;

		return $wrappingParameters;
	}

	private function getWrappingParameters(): array
	{
		if (isset($this->wrappingParameters))
		{
			return $this->wrappingParameters;
		}

		$this->wrappingParameters = [
			'module_id' => $this->moduleId,
			'command' => $this->getType()->value,
			'extra' => \Bitrix\Im\Common::getPullExtra()
		];

		if (isset($this->expiry))
		{
			$this->wrappingParameters['expiry'] = $this->expiry;
		}

		$skippedUserIds = $this->getSkippedUserIds();
		if (!empty($skippedUserIds))
		{
			$this->wrappingParameters['skip_users'] = $skippedUserIds;
		}

		return $this->wrappingParameters;
	}

	protected function getSkippedUserIds(): array
	{
		return [];
	}

	public function isGlobal(): bool
	{
		return false;
	}

	public function shouldSendToOnlySpecificRecipients(): bool
	{
		return false;
	}

	public function shouldSendMobilePush(): bool
	{
		return false;
	}

	public function shouldSendImmediately(): bool
	{
		return false;
	}
}

<?php

namespace Bitrix\Socialnetwork\Internals\Space;

use Bitrix\Socialnetwork\Integration\Tasks;
use Bitrix\Socialnetwork\Integration\Calendar;
use Bitrix\Socialnetwork\Integration\SocialNetwork\WorkGroup;
use Bitrix\Socialnetwork\Integration\SocialNetwork\LiveFeed;
use Bitrix\Socialnetwork\Internals\Space\Counter\Dictionary;
use Bitrix\Socialnetwork\Internals\Space\Counter\ProviderCollection;
use Bitrix\Socialnetwork\Internals\Space\Counter\PushSender;
use Bitrix\Socialnetwork\Space\List\Invitation\InvitationManager;
use Bitrix\Socialnetwork\Space\List\Query\Builder;

class Counter
{
	private static array $instance = [];

	public static function getInstance(int $userId): self
	{
		if (!array_key_exists($userId, self::$instance))
		{
			self::$instance[$userId] = new self(
				$userId,
				new ProviderCollection(...[
					new Tasks\CounterProvider($userId),
					new Calendar\CounterProvider($userId),
					new WorkGroup\CounterProvider($userId),
					LiveFeed\CounterFactory::getLiveFeedCounterProvider($userId),
				])
			);
		}

		return self::$instance[$userId];
	}

	/**
	 * Returns the sum of all available mertics from each connected provider, see ProviderCollection above
	 * for the selected space
	 * $spaceId = 0 considered to be the "Common Space"
	 * @param int $spaceId
	 * @return int
	 */
	public function getTotal(int $spaceId = 0): int
	{
		$total = 0;

		foreach ($this->providerCollection as $provider)
		{
			$total += $provider->getTotal($spaceId);
		}

		return $total;
	}

	/**
	 * Returns the sum of mertics from each connected provider in case the metric exists in provider
	 * $spaceId = 0 considered to be the "Common Space"
	 * @param int $spaceId
	 * @param array $metrics
	 * @return int
	 */
	public function getValue(int $spaceId = 0, array $metrics = []): int
	{
		if (empty($metrics))
		{
			return $this->getTotal($spaceId);
		}

		$value = 0;

		foreach ($this->providerCollection as $provider)
		{
			$value += $provider->getValue($spaceId, $metrics);
		}

		return $value;
	}

	/**
	 * Returns a list of all available mertics from each connected provider
	 * @return array
	 */
	public function getAvailableMetrics(): array
	{
		$availableMetrics = [];

		foreach ($this->providerCollection as $provider)
		{
			$availableMetrics = array_merge($availableMetrics, $provider->getAvailableMetrics());
		}

		return $availableMetrics;
	}

	/**
	 * Returns total counters for each metric
	 * Ex.
	 * total: 2,
	 * spaces: [{
	 * 		id:1,
	 *		total: 2,
	 * 		metrics: {countersTasksTotal: 2, countersCalendarTotal: 0, countersWorkGroupRequestTotal: 0, countersLiveFeedTotal: 0}
	 * },{....}]
	 * @return array
	 */
	public function getMemberSpaceCounters(): array
	{
		$total = 0;
		$spacesTotal = $this->getMemberSpacesTotal();
		$invitationsTotal = $this->getInvitationsTotal();
		$total += $invitationsTotal;
		$total += $spacesTotal['total'];

		return [
			'userId' => $this->userId,
			'total' => $total,
			'spaces' => $spacesTotal['spaces'],
			'invitations' => $invitationsTotal,
		];
	}

	private function __construct(private int $userId, private ProviderCollection $providerCollection)
	{
		$this->recount();
	}

	/**
	 * Updates the spaces total counter displayed in the main left menu;
	 * The spaces total counter is the summ of all user's spaces counters
	 * @return void
	 */
	public function recount()
	{
		$memberCounters = $this->getMemberSpaceCounters();
		$value = $memberCounters['total'];

		if (!$this->isSameValueCached($value))
		{
			\CUserCounter::Set(
				$this->userId,
				Dictionary::LEFT_MENU_SPACES,
				$value,
				'**',
				'',
				true
			);
		}

		// push data to the client
		(new PushSender())->createPush(
			[$this->userId],
			PushSender::COMMAND_USER_SPACES,
			$memberCounters
		);
	}

	private function isSameValueCached(int $value): bool
	{
		global $CACHE_MANAGER;

		$cache = $CACHE_MANAGER->Get('user_counter' . $this->userId);
		if (!$cache)
		{
			return false;
		}

		foreach ($cache as $item)
		{
			if (
				$item['CODE'] === Dictionary::LEFT_MENU_SPACES
				&& $item['SITE_ID'] === '**'
				&& (int)$item['CNT'] === $value
			)
			{
				return true;
			}
		}

		return false;
	}

	private function getUserSpaces(int $userId): array
	{
		$userSpaces = (new Builder($userId))
			->addModeFilter(\Bitrix\Socialnetwork\Space\List\Dictionary::FILTER_MODES['my'])
			->build()
			->exec()
			->fetchAll();

		// append common space
		$userSpaces[] = ['ID' => 0];

		return $userSpaces;
	}

	private function getMemberSpacesTotal(): array
	{
		$total = 0;
		$spaces = [];

		foreach ($this->getUserSpaces($this->userId) as $space)
		{
			$spaceId = (int)$space['ID'];
			$spaceTotal = $this->getTotal($spaceId);
			$total += $spaceTotal;

			$spaceCounters = [
				'id' => $spaceId,
				'total' => $spaceTotal,
			];

			foreach ($this->getAvailableMetrics() as $metric)
			{
				$spaceCounters['metrics'][$metric] = $this->getValue($spaceId, [$metric]);
			}

			$spaces[] = $spaceCounters;
		}

		return [
			'total' => $total,
			'spaces' => $spaces,
		];
	}

	private function getInvitationsTotal(): int
	{
		$total = 0;

		$invitations = (new InvitationManager($this->userId))->getInvitations()->toArray();
		foreach ($invitations as $invitation)
		{
			$total += $this->getValue($invitation->getSpaceId(), [Dictionary::COUNTERS_WORKGROUP_REQUEST_OUT]);
		}

		return $total;
	}
}
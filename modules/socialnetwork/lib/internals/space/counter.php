<?php

namespace Bitrix\Socialnetwork\Internals\Space;

use Bitrix\Socialnetwork\Integration\Tasks;
use Bitrix\Socialnetwork\Integration\Calendar;
use Bitrix\Socialnetwork\Integration\SocialNetwork\WorkGroup;
use Bitrix\Socialnetwork\Integration\SocialNetwork\LiveFeed;
use Bitrix\Socialnetwork\Internals\Space\Counter\Cache;
use Bitrix\Socialnetwork\Internals\Space\Counter\Dictionary;
use Bitrix\Socialnetwork\Internals\Space\Counter\ProviderCollection;

class Counter
{
	private static array $instance = [];
	private Cache $cache;

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
					new LiveFeed\CounterProvider($userId),
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
		$this->cache = new Cache($this->userId);
		$this->updateLeftMenuCounter();
	}

	public function updateLeftMenuCounter(): void
	{
		$memberCounters = $this->getMemberSpaceCounters();
		$value = $memberCounters['total'];
		$code = Dictionary::LEFT_MENU_SPACES;

		if (!$this->cache->isSameLeftMenuTotal($code, $value))
		{
			\CUserCounter::Set(
				$this->userId,
				$code,
				$value,
				'**',
				'',
				false
			);
		}
	}

	private function getMemberSpacesTotal(): array
	{
		$total = 0;
		$spaces = [];

		$userSpaces = $this->cache->getUserSpaceIds();

		foreach ($userSpaces as $spaceId)
		{
			$spaceTotal = $this->getTotal($spaceId);

			if ($spaceTotal === 0)
			{
				continue;
			}

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

		$invitations = $this->cache->getUserInvitationIds();

		foreach ($invitations as $invitationId)
		{
			$total += $this->getValue($invitationId, [Dictionary::COUNTERS_WORKGROUP_REQUEST_OUT]);
		}

		return $total;
	}
}
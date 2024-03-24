<?php

namespace Bitrix\MessageService\Providers\Edna;

use Bitrix\MessageService\Providers;
use Bitrix\MessageService\Internal\Entity\ChannelTable;

class Initiator extends Providers\Base\Initiator
{
	protected Providers\OptionManager $optionManager;
	protected Providers\SupportChecker $supportChecker;
	protected EdnaRu $utils;
	protected Providers\CacheManager $cacheManager;
	protected string $providerId;

	protected string $channelType = '';

	public function __construct(
		Providers\OptionManager $optionManager,
		Providers\SupportChecker $supportChecker,
		EdnaRu $utils,
		string $providerId
	)
	{
		$this->optionManager = $optionManager;
		$this->supportChecker = $supportChecker;
		$this->utils = $utils;
		$this->providerId = $providerId;
		$this->cacheManager = new Providers\CacheManager($this->providerId);
	}

	/**
	 * @return string
	 */
	public function getChannelType(): string
	{
		return $this->channelType;
	}

	/**
	 * @return array<array{id: int, name: string, channelPhone: string}>
	 */
	public function getFromList(): array
	{
		if (!$this->supportChecker->canUse())
		{
			return [];
		}

		// load from cache
		$cachedChannels = $this->cacheManager->getValue(Providers\CacheManager::CHANNEL_CACHE_ENTITY_ID);
		if (!empty($cachedChannels))
		{
			return $cachedChannels;
		}

		$fromList = [];

		// load from db
		$res = ChannelTable::getChannelsByType($this->providerId, $this->getChannelType());
		while ($channel = $res->fetch())
		{
			$fromList[] = [
				'id' => (int)$channel['EXTERNAL_ID'],
				'name' => $channel['NAME'],
				'channelPhone' => $channel['ADDITIONAL_PARAMS']['channelAttribute'] ?? '',
			];
		}

		if (empty($fromList))
		{
			// get channels from provider
			//$fromList = $this->utils->updateSavedChannelList($this->getChannelType());
			\Bitrix\Main\Application::getInstance()->addBackgroundJob([$this->utils, 'updateSavedChannelList'], [$this->getChannelType()]);
		}

		// update cache
		$this->cacheManager->setValue(Providers\CacheManager::CHANNEL_CACHE_ENTITY_ID, $fromList);

		return $fromList;
	}

	public function isCorrectFrom($from): bool
	{
		$fromList = $this->getFromList();
		foreach ($fromList as $item)
		{
			if ((int)$from === $item['id'])
			{
				return true;
			}
		}
		return false;
	}
}
<?php

namespace Bitrix\Im\V2\TariffLimit;

use Bitrix\Bitrix24\Feature;
use Bitrix\Im\V2\Chat;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;

class Limit
{
	private const DAYS_LIMIT_HISTORY_VARIABLE = 'im_history_days_limit';
	private const HISTORY_LIMIT_ID = 'im_history_limit';

	private static ?self $instance;

	private function __construct()
	{
	}

	public static function getInstance(): static
	{
		if (!isset(static::$instance))
		{
			static::$instance = new static();
		}

		return static::$instance;
	}

	public static function onLicenseHasChanged(): void
	{
		if (Loader::includeModule('pull'))
		{
			\CPullStack::AddShared([
				'module_id' => 'im',
				'command' => 'changeTariff',
				'params' => [
					'tariffRestrictions' => self::getInstance()->getRestrictions(),
				],
				'extra' => \Bitrix\Im\Common::getPullExtra()
			]);
		}
	}

	public function getRestrictions(): array
	{
		return [
			'fullChatHistory' => [
				'isAvailable' => !$this->hasRestrictions(),
				'limitDays' => $this->getLimitDays(),
			],
		];
	}

	protected function getLimitDays(): ?int
	{
		if (!Loader::includeModule('bitrix24'))
		{
			return null;
		}

		if (Feature::isFeatureEnabled(self::HISTORY_LIMIT_ID))
		{
			return null;
		}

		$limitDays = Feature::getVariable(self::DAYS_LIMIT_HISTORY_VARIABLE);

		return $limitDays === null ? null : (int)$limitDays;
	}

	public function hasAccessByDate(DateFilterable $item, DateTime $date): bool
	{
		if (!$this->shouldFilterByDate($item))
		{
			return true;
		}

		return $this->getLimitDate()->getTimestamp() <= $date->getTimestamp();
	}

	public function hasRestrictions(): bool
	{
		return $this->getLimitDays() !== null;
	}

	public function shouldFilterByDate(DateFilterable $item): bool
	{
		if (!$this->hasRestrictions())
		{
			return false;
		}

		$chatId = $item->getRelatedChatId();

		if (!$chatId)
		{
			return false;
		}

		$chat = Chat::getInstance($chatId);

		return !$chat instanceof Chat\ChannelChat && !$chat instanceof Chat\CommentChat && !$chat instanceof Chat\CollabChat;
	}

	public function getLimitDate(): DateTime
	{
		Loader::requireModule('bitrix24');
		$days = $this->getLimitDays();

		return (new DateTime())->add("-{$days} days");
	}
}

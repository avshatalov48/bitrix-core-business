<?php

namespace Bitrix\MessageService\Restriction;

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\Date;
use Bitrix\MessageService\Internal\Entity\RestrictionTable;
use Bitrix\MessageService\Message;

class RestrictionManager
{
	private const OPTION_NAME = 'network_restrictions_enable';

	/** @var Base[] */
	private array $restrictions;
	private Base $notPassedRestriction;

	public static function canUse(): bool
	{
		return Option::get('messageservice', self::OPTION_NAME, 'N') === 'Y';
	}

	public static function enableRestrictions(): void
	{
		Option::set('messageservice', self::OPTION_NAME, 'Y');
	}

	public static function disableRestrictions(): void
	{
		Option::set('messageservice', self::OPTION_NAME, 'N');
	}

	public function __construct(Message $message)
	{
		$this->restrictions = $this->registerRestrictions($message);
	}

	public function isCanSendMessage(): bool
	{
		if (empty($this->restrictions) || !self::canUse())
		{
			return true;
		}

		try
		{
			$this->lockRestrictions();

			$codes = array_keys($this->restrictions);
			$query = RestrictionTable::query()
				->setSelect([
					'CODE',
					'COUNTER',
					'ADDITIONAL_PARAMS'
				])
				->whereIn('CODE', $codes)
				->where('DATE_CREATE', new Date())
				->setLimit(count($this->restrictions))
			;

			foreach($query->exec() as $row)
			{
				$code = $row['CODE'];

				$this->restrictions[$code]
					->setCounter($row['COUNTER'])
					->setAdditionalParams($row['ADDITIONAL_PARAMS'])
				;
			}

			if (!$this->checkRestrictions())
			{
				return false;
			}

			$connection = Application::getConnection();
			$connection->startTransaction();

			foreach($this->restrictions as $restriction)
			{
				if (!$restriction->increase())
				{
					$this->notPassedRestriction = $restriction;
					$connection->rollbackTransaction();

					return false;
				}
			}
			$connection->commitTransaction();

			return true;
		}
		finally
		{
			$this->unlockRestrictions();
			if (isset($this->notPassedRestriction))
			{
				$this->notPassedRestriction->log();
			}
		}
	}

	/**
	 * @return array<string, Base>
	 */
	private function registerRestrictions(Message $message): array
	{
		//TODO Put it in configs
		/** @var Base[] $restrictions */
		$restrictions = [
			new SmsPerUser($message),
			new SmsPerPhone($message),
			new PhonePerUser($message),
			new UserPerPhone($message),
			new IpPerUser($message),
			new IpPerPhone($message),
			new SmsPerIp($message),
		];

		$result = [];
		foreach($restrictions as $restriction)
		{
			if ($restriction->canUse())
			{
				$result[$restriction->getEntityId()] = $restriction;
			}
		}

		return $result;
	}

	private function lockRestrictions(): void
	{
		foreach($this->restrictions as $restriction)
		{
			$restriction->lock();
		}
	}

	private function unlockRestrictions(): void
	{
		foreach($this->restrictions as $restriction)
		{
			$restriction->unlock();
		}
	}

	private function checkRestrictions(): bool
	{
		foreach($this->restrictions as $restriction)
		{
			if (!$restriction->isCanSend())
			{
				$this->notPassedRestriction = $restriction;
				return false;
			}
		}

		return true;
	}
}

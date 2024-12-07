<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage tasks
 * @copyright 2001-2023 Bitrix
 */

namespace Bitrix\Socialnetwork\Update;

use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\LogTable;
use Bitrix\Main\Config\Option;

class FeedGarbageCollector
{
	private static $processing = false;

	public static function execute()
	{
		if (self::$processing)
		{
			return self::getAgentName();
		}

		self::$processing = true;

		$agent = new self();
		$res = $agent->run();

		self::$processing = false;

		return $res;
	}

	/**
	 * @return string
	 */
	private static function getAgentName(): string
	{
		return self::class . "::execute();";
	}

	private function __construct()
	{

	}

	/**
	 * @return string
	 * @throws \Bitrix\Main\LoaderException
	 */
	private function run(): string
	{
		$types = $this->getTypes();
		if (empty($types))
		{
			return '';
		}

		$logIds = $this->getLogIds($types);
		if (empty($logIds))
		{
			return '';
		}

		foreach ($logIds as $logId)
		{
			\CSocNetLog::Delete($logId);
		}

		return self::getAgentName();
	}

	/**
	 * @param array $types
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function getLogIds(array $types): array
	{
		$logIds = LogTable::getList([
			'select' => ['ID'],
			'filter' => [
				'@ENTITY_TYPE' => $types,
			],
			'limit' => $this->getLimit(),
		])->fetchAll();
		if (empty($logIds))
		{
			return [];
		}

		return array_column($logIds, 'ID');
	}

	/**
	 * @return array
	 * @throws \Bitrix\Main\LoaderException
	 */
	private function getTypes(): array
	{
		if (!Loader::includeModule('crm'))
		{
			return [];
		}

		return [
			SONET_CRM_LEAD_ENTITY,
			SONET_CRM_CONTACT_ENTITY,
			SONET_CRM_COMPANY_ENTITY,
			SONET_CRM_DEAL_ENTITY,
			SONET_CRM_ACTIVITY_ENTITY,
			SONET_CRM_INVOICE_ENTITY,
			SONET_CRM_ORDER_ENTITY,
			SONET_CRM_SUSPENDED_LEAD_ENTITY,
			SONET_SUSPENDED_CRM_CONTACT_ENTITY,
			SONET_SUSPENDED_CRM_COMPANY_ENTITY,
			SONET_CRM_SUSPENDED_DEAL_ENTITY,
			SONET_CRM_SUSPENDED_ACTIVITY_ENTITY,
		];
	}

	private function getLimit(): int
	{
		return (int)Option::get('socialnetwork', 'FeedGarbageCollectorAgentLimit', 20);
	}
}

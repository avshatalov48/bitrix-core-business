<?php

namespace Bitrix\Im\Call;

use Bitrix\Im\Call\Integration\EntityFactory;
use Bitrix\Im\Chat;
use Bitrix\Im\Common;
use Bitrix\Im\Model\CallTable;
use Bitrix\Im\Model\CallUserTable;
use Bitrix\Im\User;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Event;

class CallLog
{
	const TYPE_NOW = 'now';
	const TYPE_ALL = 'all';
	const TYPE_SEARCH = 'search';

	public static function getTypes()
	{
		return [
			self::TYPE_ALL,
			self::TYPE_NOW,
			self::TYPE_SEARCH
		];
	}
	public static function get($params = [])
	{
		$filterType = in_array($params['TYPE'], self::getTypes(), true)? $params['TYPE']: self::TYPE_ALL;
		$filterCallId = $params['TYPE'] === self::TYPE_SEARCH? intval($params['CALL_ID']): 0;

		$select = [
			'ID', 'UUID', 'TYPE', 'INITIATOR_ID', 'PROVIDER', 'STATE', 'LOG_URL',
			'ENTITY_TYPE', 'ENTITY_ID',
			'START_DATE', 'END_DATE',
			'CHAT_TITLE' => 'CHAT.TITLE'
		];

		$runtime = [
			new \Bitrix\Main\Entity\ReferenceField(
				'CHAT',
				\Bitrix\Im\Model\ChatTable::class,
				[
					"=ref.ID" => "this.CHAT_ID",
				],
				["join_type"=>"LEFT"]
			)
		];

		$filter = [];

		if ($filterType == self::TYPE_SEARCH)
		{
			$filter = [
				'=ID' => $filterCallId
			];
		}
		else
		{
			if ($filterType == self::TYPE_NOW)
			{
				$filter = [
					'!=STATE' => \Bitrix\Im\Call\Call::STATE_FINISHED
				];
			}

			if ($params['LAST_ID'])
			{
				$filter['<ID'] = $params['LAST_ID'];
			}
		}

		$result = \Bitrix\Im\Model\CallTable::getList([
			'select' => $select,
			'runtime' => $runtime,
			'filter' => $filter,
			'order' => ['ID' => 'DESC'],
			'limit' => '50'
		]);

		$list = [];
		while($row = $result->fetch())
		{
			$call = $row;

			if (!$call['CHAT_TITLE'])
			{
				$call['CHAT_TITLE'] = User::getInstance($call['INITIATOR_ID'])->getLastName(false);

				if ($call['ENTITY_TYPE'] === 'chat' && !Common::isChatId($call['ENTITY_ID']))
				{
					$call['CHAT_TITLE'] .= ' -> '.User::getInstance($call['ENTITY_ID'])->getLastName(false);
				}
				else
				{
					$call['CHAT_TITLE'] .= ' -> '.ucfirst($call['ENTITY_TYPE']);
				}
			}

			$call['DURATION'] = 0;
			$startDate = $call['START_DATE'] instanceof DateTime? $call['START_DATE']->getTimestamp(): 0;
			$endDate = $call['END_DATE'] instanceof DateTime? $call['END_DATE']->getTimestamp(): (new DateTime())->getTimestamp();

			$call['DURATION'] = $endDate - $startDate;
			$call['DURATION_TEXT'] = \CModule::includeModule('voximplant') ? \CVoxImplantHistory::convertDurationToText($call['DURATION']) : $call['DURATION'];

			$call['CONNECTIONS'] = [];

			$list[$row['ID']] = $call;
		}

		$result = \Bitrix\Im\Model\CallUserTable::getList([
			'select' => ['CALL_ID', 'USER_ID', 'STATE', 'LAST_SEEN'],
			'filter' => [
				'=CALL_ID' => array_keys($list)
			],
		]);

		while($row = $result->fetch())
		{
			$connection = $row;

			$connection['INITIATOR'] = $list[$row['CALL_ID']]['INITIATOR_ID'] === $row['USER_ID'];
			$connection['USER_NAME'] = User::getInstance($row['USER_ID'])->getFullName(false);
			$connection['USER_AVATAR'] = User::getInstance($row['USER_ID'])->getAvatar();
			$connection['USER_COLOR'] = User::getInstance($row['USER_ID'])->getColor();

			if (defined('IM_CALL_LOG_PATH'))
			{
				$connection['LOG_URL'] = IM_CALL_LOG_PATH."{$row['CALL_ID']}-{$row['USER_ID']}.txt";
			}
			if ($list[$row['CALL_ID']]['PROVIDER'] === Call::PROVIDER_VOXIMPLANT && defined('IM_CALL_STAT_URL'))
			{
				$connection['STAT_URL'] = str_replace(
					[
						'#callId#',
						'#userId#',
						'#tsFrom#',
						'#tsTo#',
					],
					[
						$row['CALL_ID'],
						$row['USER_ID'],
						$list[$row['CALL_ID']]['START_DATE']->getTimestamp() * 1000,
						$list[$row['CALL_ID']]['END_DATE']
							? $list[$row['CALL_ID']]['END_DATE']->getTimestamp() * 1000
							: ($list[$row['CALL_ID']]['START_DATE']->getTimestamp() + 7200) * 1000
						,
					],
					IM_CALL_STAT_URL
				);
			}

			unset($connection['CALL_ID']);

			$list[$row['CALL_ID']]['CONNECTIONS'][] = $connection;
		}

		return $list;
	}
}



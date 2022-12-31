<?php

namespace Bitrix\Socialnetwork\Update;

use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Update\Stepper;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\Component\WorkgroupList;
use Bitrix\Socialnetwork\WorkgroupPinTable;
use Bitrix\Socialnetwork\WorkgroupTable;
use Bitrix\Tasks\Internals\Project\UserOption\UserOptionTypeDictionary;
use Bitrix\Tasks\Internals\Task\ProjectUserOptionTable;

Loc::loadMessages(__FILE__);

final class WorkgroupTasksPinMigration extends Stepper
{
	protected static $moduleId = 'socialnetwork';

	public function execute(array &$result)
	{
		if (
			!(
				Loader::includeModule(self::$moduleId)
				&& Loader::includeModule('tasks')
				&& Option::get('socialnetwork', 'needWorkgroupTaskPinMigration', 'Y') === 'Y'
			)
		)
		{
			return false;
		}

		$return = false;

		$params = Option::get('socialnetwork', 'workgrouptaskspinmigration');
		$params = ($params !== '' ? @unserialize($params, [ 'allowed_classes' => false ]) : []);
		$params = (is_array($params) ? $params : []);

		if (empty($params))
		{
			$params = [
				'lastId' => 0,
				'number' => 0,
				'count' => $this->getCount(),
			];
		}

		if ($params['count'] > 0)
		{
			$result['title'] = Loc::getMessage('FUPD_WORKGROUP_TASKS_PIN_MIGRATION_TITLE');
			$result['progress'] = 1;
			$result['steps'] = '';
			$result['count'] = $params['count'];

			$res = (new \Bitrix\Main\Entity\Query(ProjectUserOptionTable::getEntity()))
				->registerRuntimeField(
					new ReferenceField(
						'PROJECT',
						WorkgroupTable::getEntity(),
						[ '=this.PROJECT_ID' => 'ref.ID' ],
						[ 'join_type' => 'INNER' ]
					)
				)
				->addFilter('>ID', $params['lastId'])
				->addFilter('=OPTION_CODE', UserOptionTypeDictionary::OPTION_PINNED)
				->addSelect('ID')
				->addSelect('PROJECT_ID')
				->addSelect('USER_ID')
				->addSelect('PROJECT.SCRUM_MASTER_ID')
				->setOffset(0)
				->setLimit(50)
				->exec();

			$found = false;
			while ($userOptionItem = $res->fetchObject())
			{
				$groupId = $userOptionItem->get('PROJECT_ID');
				$userId = $userOptionItem->get('USER_ID');
				$projectItem = $userOptionItem->get('PROJECT');
				$context = (
					(int)$projectItem->get('SCRUM_MASTER_ID') > 0
						? WorkgroupList::MODE_TASKS_SCRUM
						: WorkgroupList::MODE_TASKS_PROJECT
				);

				$params['number']++;
				$params['lastId'] = $userOptionItem->getId();

				if (WorkgroupPinTable::getList([
					'filter' => [
						'=GROUP_ID' => $groupId,
						'=USER_ID' => $userId,
						'=CONTEXT' => $context,
					],
				])->fetchObject())
				{
					continue;
				}

				WorkgroupPinTable::add([
					'GROUP_ID' => $groupId,
					'USER_ID' => $userId,
					'CONTEXT' => $context,
				]);

				$found = true;
			}

			if ($found)
			{
				Option::set('socialnetwork', 'workgrouptaskspinmigration', serialize($params));
				$return = true;
			}

			$result['progress'] = (int)($params['number'] * 100 / $params['count']);
			$result['steps'] = $params['number'];

			if ($found === false)
			{
				Option::delete('socialnetwork', [ 'name' => 'workgrouptaskspinmigration' ]);
				Option::set('socialnetwork', 'needWorkgroupTaskPinMigration', 'N');
			}
		}

		return $return;
	}

	private function getCount(): int
	{
		return (new \Bitrix\Main\Entity\Query(ProjectUserOptionTable::getEntity()))
			->addFilter('=OPTION_CODE', UserOptionTypeDictionary::OPTION_PINNED)
			->addSelect('ID')
			->countTotal(true)
			->exec()
			->getCount();
	}
}

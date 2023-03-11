<?php

namespace Bitrix\Socialnetwork\Filter;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Socialnetwork\Component\WorkgroupList;
use Bitrix\Socialnetwork\Internals\Counter\CounterFilter;
use Bitrix\Socialnetwork\Helper;

class WorkgroupDataProvider extends \Bitrix\Main\Filter\EntityDataProvider
{
	/** @var WorkgroupSettings|null */
	protected ?WorkgroupSettings $settings;
	protected ?array $additionalParams;

	public function __construct(WorkgroupSettings $settings, ?array $additionalParams = null)
	{
		$this->settings = $settings;
		$this->additionalParams = $additionalParams;
	}

	/**
	 * Get Settings
	 * @return WorkgroupSettings
	 */
	public function getSettings(): ?WorkgroupSettings
	{
		return $this->settings;
	}

	/**
	 * Get specified entity field caption.
	 * @param string $fieldID Field ID.
	 * @return string
	 */
	protected function getFieldName($fieldID): string
	{
		switch ($fieldID)
		{
			case 'OWNER':
				$name = (
					ModuleManager::isModuleInstalled('intranet')
						? Loc::getMessage("SOCIALNETWORK_WORKGROUP_FILTER_{$fieldID}_INTRANET")
						: Loc::getMessage("SOCIALNETWORK_WORKGROUP_FILTER_{$fieldID}")
				);
				break;
			default:
				$name = Loc::getMessage("SOCIALNETWORK_WORKGROUP_FILTER_{$fieldID}");
		}

		if ($name === null)
		{
			$name = $fieldID;
		}

		return $name;
	}

	public function prepareFieldData($fieldID): ?array
	{
		$result = null;

		if ($fieldID === 'ROLE')
		{
/*
			$roles = [
				UserToGroupTable::ROLE_OWNER => Loc::getMessage('SOCIALNETWORK_USERTOGROUP_FILTER_ROLE_OWNER'),
				UserToGroupTable::ROLE_MODERATOR => Loc::getMessage('SOCIALNETWORK_USERTOGROUP_FILTER_ROLE_MODERATOR'),
				UserToGroupTable::ROLE_USER => Loc::getMessage('SOCIALNETWORK_USERTOGROUP_FILTER_ROLE_USER'),
				UserToGroupTable::ROLE_REQUEST => Loc::getMessage('SOCIALNETWORK_USERTOGROUP_FILTER_ROLE_REQUEST'),
			];
			$result = [
				'params' => ['multiple' => 'Y'],
				'items' => $roles
			];
*/
		}
		elseif ($fieldID === 'INITIATED_BY_TYPE')
		{
/*
			$result = [
				'params' => ['multiple' => 'N'],
				'items' => [
					UserToGroupTable::INITIATED_BY_USER => Loc::getMessage('SOCIALNETWORK_USERTOGROUP_FILTER_INITIATED_BY_USER'),
					UserToGroupTable::INITIATED_BY_GROUP => Loc::getMessage('SOCIALNETWORK_USERTOGROUP_FILTER_INITIATED_BY_GROUP'),
				]
			];
*/
		}
		elseif ($fieldID === 'OWNER')
		{
			return [
				'params' => [
					'apiVersion' => '3',
					'context' => 'SONET_GROUP_LIST_FILTER_OWNER',
					'multiple' => 'N',
					'contextCode' => 'U',
					'enableAll' => 'N',
					'enableSonetgroups' => 'N',
					'allowEmailInvitation' => 'N',
					'allowSearchEmailUsers' => 'N',
					'departmentSelectDisable' => 'Y',
				],
			];
		}
		elseif ($fieldID === 'MEMBER')
		{
			return [
				'params' => [
					'apiVersion' => '3',
					'context' => 'SONET_GROUP_LIST_FILTER_MEMBER',
					'multiple' => 'N',
					'contextCode' => 'U',
					'enableAll' => 'N',
					'enableSonetgroups' => 'N',
					'allowEmailInvitation' => 'N',
					'allowSearchEmailUsers' => 'N',
					'departmentSelectDisable' => 'Y',
				],
			];
		}
		elseif (in_array($fieldID, [ 'FAVORITES', 'EXTRANET' ], true))
		{
			return [
				'items' => [
					'' => Loc::getMessage('SOCIALNETWORK_WORKGROUP_FILTER_LIST_VALUE_NOT_SPECIFIED'),
					'Y' => Loc::getMessage('SOCIALNETWORK_WORKGROUP_FILTER_LIST_VALUE_Y'),
				],
			];
		}
		elseif ($fieldID === 'COUNTERS')
		{
			$items = [];

			if (
				is_array($this->additionalParams)
				&& isset($this->additionalParams['MODE'])
				&& in_array($this->additionalParams['MODE'], WorkgroupList::getTasksModeList(), true)
			)
			{
				if ($this->additionalParams['MODE'] === WorkgroupList::MODE_TASKS_SCRUM)
				{
					$items = [
						'NEW_COMMENTS' => Loc::getMessage('SOCIALNETWORK_WORKGROUP_FILTER_COUNTERS_LIST_VALUE_NEW_COMMENTS'),
						'PROJECT_NEW_COMMENTS' => Loc::getMessage('SOCIALNETWORK_WORKGROUP_FILTER_COUNTERS_LIST_VALUE_PROJECT_NEW_COMMENTS'),
					];
				}
				elseif ($this->additionalParams['MODE'] === WorkgroupList::MODE_TASKS_PROJECT)
				{
					$items = [
						'EXPIRED' => Loc::getMessage('SOCIALNETWORK_WORKGROUP_FILTER_COUNTERS_LIST_VALUE_EXPIRED'),
						'NEW_COMMENTS' => Loc::getMessage('SOCIALNETWORK_WORKGROUP_FILTER_COUNTERS_LIST_VALUE_NEW_COMMENTS'),
						'PROJECT_EXPIRED' => Loc::getMessage('SOCIALNETWORK_WORKGROUP_FILTER_COUNTERS_LIST_VALUE_PROJECT_EXPIRED'),
						'PROJECT_NEW_COMMENTS' => Loc::getMessage('SOCIALNETWORK_WORKGROUP_FILTER_COUNTERS_LIST_VALUE_PROJECT_NEW_COMMENTS'),
					];
				}
			}

			return [
				'items' => $items,
			];
		}
		elseif ($fieldID === 'COMMON_COUNTERS')
		{
			$items = [];

			if (
				is_array($this->additionalParams)
				&& isset($this->additionalParams['MODE'])
				&& $this->additionalParams['MODE'] === WorkgroupList::MODE_COMMON
			)
			{
				$items = [
					CounterFilter::VALUE_LIVEFEED => Loc::getMessage('SOCIALNETWORK_WORKGROUP_FILTER_COUNTERS_LIST_VALUE_LIVEFEED'),
					CounterFilter::VALUE_TASKS => Loc::getMessage('SOCIALNETWORK_WORKGROUP_FILTER_COUNTERS_LIST_VALUE_TASKS'),
				];
			}

			return [
				'items' => $items,
			];
		}

		return $result;
	}

	/**
	 * Prepare field list.
	 * @return \Bitrix\Main\Filter\Field[]
	 */
	public function prepareFields(): array
	{
		$result = [];

		$fieldsList = [
			'ID' => [
				'options' => [ 'type' => 'number' ],
			],
			'NAME' => [
				'options' => [ 'default' => true ],
			],
			'OWNER' => [
				'options' => [ 'type' => 'dest_selector', 'partial' => true ],
			],
			'MEMBER' => [
				'options' => [ 'type' => 'dest_selector', 'partial' => true ],
			],
			'TAG' => [
				'options' => [],
			],
			'VISIBLE' => [
				'options' => [ 'type' => 'checkbox' ],
			],
			'OPENED' => [
				'options' => [ 'type' => 'checkbox' ],
			],
			'CLOSED' => [
				'conditionMethod' => '\Bitrix\Socialnetwork\Filter\WorkgroupDataProvider::getClosedAvailability',
				'options' => [ 'type' => 'checkbox' ],
			],
			'PROJECT' => [
				'conditionMethod' => '\Bitrix\Main\Filter\UserDataProvider::getProjectAvailability',
				'options' => [ 'type' => 'checkbox' ],
			],
			'SCRUM' => [
				'conditionMethod' => '\Bitrix\Main\Filter\UserDataProvider::getScrumAvailability',
				'options' => [ 'type' => 'checkbox' ],
			],
			'PROJECT_DATE' => [
				'conditionMethod' => '\Bitrix\Main\Filter\UserDataProvider::getProjectAvailability',
				'options' => [ 'type' => 'date' ]
			],
			'EXTRANET' => [
				'conditionMethod' => '\Bitrix\Main\Filter\UserDataProvider::getExtranetAvailability',
				'options' => [ 'type' => 'list', 'partial' => true ],
			],
			'LANDING' => [
				'conditionMethod' => '\Bitrix\Main\Filter\UserDataProvider::getLandingAvailability',
				'options' => [ 'type' => 'checkbox' ],
			],
			'FAVORITES' => [
				'options' => [ 'type' => 'list', 'partial' => true ],
			],
			'COUNTERS' => [
				'options' => [ 'type' => 'list', 'partial' => true ],
			],
		];

		//todo oh
		if (
			is_array($this->additionalParams)
			&& isset($this->additionalParams['MODE'])
			&& in_array($this->additionalParams['MODE'], WorkgroupList::getTasksModeList(), true)
		)
		{
			$fieldsList['COMMON_COUNTERS'] = [
				'options' => [ 'type' => 'list', 'partial' => true ],
			];
		}

		foreach ($fieldsList as $column => $field)
		{
			$whiteListPassed = true;

			if (
				!empty($field['conditionMethod'])
				&& is_callable($field['conditionMethod'])
			)
			{
				$whiteListPassed = call_user_func_array($field['conditionMethod'], []);
			}

			if ($whiteListPassed)
			{
				if (
					is_array($this->additionalParams)
					&& isset($this->additionalParams['MODE'])
					&& in_array($column, ['SCRUM', 'PROJECT'], true)
					&& in_array($this->additionalParams['MODE'], WorkgroupList::getTasksModeList(), true)
				)
				{
					continue;
				}

				if (
					$column === 'COUNTERS'
					&& (
						!is_array($this->additionalParams)
						|| !isset($this->additionalParams['MODE'])
						|| !in_array($this->additionalParams['MODE'], WorkgroupList::getTasksModeList(), true)
					)
				)
				{
					continue;
				}

				if (
					$column === 'COMMON_COUNTERS'
					&& (
						!isset($this->additionalParams['MODE'], $this->additionalParams['CONTEXT_USER_ID'])
						|| !is_array($this->additionalParams)
						|| $this->additionalParams['MODE'] !== WorkgroupList::MODE_COMMON
						|| $this->additionalParams['CONTEXT_USER_ID'] !== Helper\User::getCurrentUserId()
					)
				)
				{
					continue;
				}

				$result[$column] = $this->createField(
					$column,
					(!empty($field['options']) ? $field['options'] : [])
				);
			}
		}

		return $result;
	}

	public static function getClosedAvailability(): bool
	{
		return (Option::get('socialnetwork', 'work_with_closed_groups', 'N') !== 'Y');
	}

	public static function getProjectAvailability(): bool
	{
		return (
			ModuleManager::isModuleInstalled('intranet')
			&& ModuleManager::isModuleInstalled('tasks')
		);
	}

	public static function getScrumAvailability(): bool
	{
		return (
			ModuleManager::isModuleInstalled('intranet')
			&& ModuleManager::isModuleInstalled('tasks')
		);
	}

	public static function getLandingAvailability(): bool
	{
		$extranetSiteId = Option::get('extranet', 'extranet_site');
		$extranetSiteId = ($extranetSiteId && ModuleManager::isModuleInstalled('extranet') ?  $extranetSiteId : '');

		return (
			SITE_ID !== $extranetSiteId
			&& ModuleManager::isModuleInstalled('landing')
		);
	}

}

<?php

namespace Bitrix\Socialnetwork\Filter;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Socialnetwork\UserToGroupTable;

class UserToGroupDataProvider extends \Bitrix\Main\Filter\EntityDataProvider
{
	/** @var UserToGroupSettings|null */
	protected $settings;

	public function __construct(UserToGroupSettings $settings)
	{
		$this->settings = $settings;
	}

	/**
	 * Get Settings
	 * @return UserToGroupSettings
	 */
	public function getSettings(): ?UserToGroupSettings
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
		$name = Loc::getMessage("SOCIALNETWORK_USERTOGROUP_FILTER_{$fieldID}");

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
		}
		elseif ($fieldID === 'INITIATED_BY_TYPE')
		{
			$result = [
				'params' => ['multiple' => 'N'],
				'items' => [
					UserToGroupTable::INITIATED_BY_USER => Loc::getMessage('SOCIALNETWORK_USERTOGROUP_FILTER_INITIATED_BY_USER'),
					UserToGroupTable::INITIATED_BY_GROUP => Loc::getMessage('SOCIALNETWORK_USERTOGROUP_FILTER_INITIATED_BY_GROUP'),
				]
			];
		}
		elseif (
			$fieldID === 'DEPARTMENT'
			|| $fieldID === 'DEPARTMENT_FLAT'
		)
		{
			return [
				'params' => [
					'apiVersion' => 3,
					'context' => 'USERTOGROUP_LIST_FILTER_DEPARTMENT',
					'multiple' => 'N',
					'contextCode' => 'DR',
					'enableDepartments' => 'Y',
					'departmentFlatEnable' => ($fieldID === 'DEPARTMENT_FLAT' ? 'Y' : 'N'),
					'enableAll' => 'N',
					'enableUsers' => 'N',
					'enableSonetgroups' => 'N',
					'allowEmailInvitation' => 'N',
					'allowSearchEmailUsers' => 'N',
					'departmentSelectDisable' => 'N',
					'isNumeric' => 'N',
				],
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
			'ID' => [],
			'NAME' => [],
			'LAST_NAME' => [
				'options' => [ 'default' => true ],
			],
			'FIRED' => [
				'conditionMethod' => '\Bitrix\Main\Filter\UserDataProvider::getFiredAvailability',
				'options' => [ 'type' => 'checkbox' ]
			],
			'EXTRANET' => [
				'conditionMethod' => '\Bitrix\Main\Filter\UserDataProvider::getExtranetAvailability',
				'options' => [ 'type' => 'checkbox' ],
			],
			'ROLE' => [
				'options' => [ 'default' => true, 'type' => 'list', 'partial' => true ],
			],
			'INITIATED_BY_TYPE' => [
				'options' => [ 'type' => 'list', 'partial' => true ],
			],
			'EMAIL' => [],
			'DEPARTMENT' => [
				'options' => [ 'default' => true, 'type' => 'dest_selector', 'partial' => true ],
			],
			'DEPARTMENT_FLAT' => [
				'options' => [ 'type' => 'dest_selector', 'partial' => true ],
			],
			'AUTO_MEMBER' => [
				'conditionMethod' => '\Bitrix\Socialnetwork\Filter\UserToGroupDataProvider::getAutoMemberAvailability',
				'options' => [ 'type' => 'checkbox' ]
			],

		];

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
				$result[$column] = $this->createField(
					$column,
					(!empty($field['options']) ? $field['options'] : [])
				);
			}
		}

		return $result;
	}

	public static function getAutoMemberAvailability(): bool
	{
		static $result = null;

		if ($result === null)
		{
			$result = (
				ModuleManager::isModuleInstalled('intranet')
				&& (
					!Loader::includeModule('extranet')
					|| !\CExtranet::isExtranetSite()
				)
			);
		}

		return $result;
	}

}

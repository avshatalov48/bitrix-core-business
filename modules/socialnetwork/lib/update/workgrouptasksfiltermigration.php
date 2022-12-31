<?php

namespace Bitrix\Socialnetwork\Update;

use Bitrix\Main\Update\Stepper;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\Helper;

Loc::loadMessages(__FILE__);

abstract class WorkgroupTasksFilterMigration extends Stepper
{
	protected static $moduleId = 'socialnetwork';
	protected static $filterOptionCategory = 'main.ui.filter';
	protected static $sourceFilterOptionName = '';
	protected static $targetFilterOptionName = '';
	protected static $stepperNeedOptionName = '';
	protected static $stepperDataOptionName = '';

	public function execute(array &$result)
	{
		global $DB;

		if (
			!(
				static::$sourceFilterOptionName !== ''
				&& static::$targetFilterOptionName !== ''
				&& static::$stepperNeedOptionName !== ''
				&& static::$stepperDataOptionName !== ''
				&& Loader::includeModule(static::$moduleId)
				&& Loader::includeModule('tasks')
				&& Option::get('socialnetwork', static::$stepperNeedOptionName, 'Y') === 'Y'
			)
		)
		{
			return false;
		}

		$return = false;

		$params = Option::get('socialnetwork', static::$stepperDataOptionName);

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
			$result['title'] = Loc::getMessage('FUPD_WORKGROUP_TASKS_FILTER_MIGRATION_TITLE');
			$result['progress'] = 1;
			$result['steps'] = '';
			$result['count'] = $params['count'];

			$strSql = "
				SELECT ID, USER_ID, COMMON, VALUE
				FROM b_user_option
				WHERE 
					ID > " . (int)$params['lastId'] . "
					AND CATEGORY = '" . $DB->ForSql(static::$filterOptionCategory) . "'
					AND NAME = '" . $DB->ForSql(static::$sourceFilterOptionName) . "'
				ORDER BY ID ASC	
				";
			$res = $DB->Query($strSql);

			$found = false;
			while ($userOptionFields = $res->fetch())
			{
				$userId = (int)$userOptionFields['USER_ID'];
				$common = ($userOptionFields['COMMON'] === 'Y');
				$value = $userOptionFields['VALUE'];

				$params['number']++;
				$params['lastId'] = $userOptionFields['ID'];

				$data = @unserialize($value, [ 'allowed_classes' => false ]);
				if (!is_array($data))
				{
					continue;
				}

				$data = $this->migrate($data, $userId);

				\CUserOptions::SetOption(
					static::$filterOptionCategory,
					static::$targetFilterOptionName,
					$data,
					$common,
					$userId
				);

				$found = true;
			}

			if ($found)
			{
				Option::set('socialnetwork', static::$stepperDataOptionName, serialize($params));
				$return = true;
			}

			$result['progress'] = (int)($params['number'] * 100 / $params['count']);
			$result['steps'] = $params['number'];

			if ($found === false)
			{
				Option::delete('socialnetwork', [ 'name' => static::$stepperDataOptionName ]);
				Option::set('socialnetwork', static::$stepperNeedOptionName, 'N');
			}
		}

		return $return;
	}

	protected function getCount(): int
	{
		$counter = 0;

		$res = \CUserOptions::getList(
			[],
			[
				'CATEGORY' => static::$filterOptionCategory,
				'NAME' => static::$sourceFilterOptionName,
			]
		);

		while ($res->fetch())
		{
			$counter++;
		}

		return $counter;
	}

	protected function migrate(array $data = [], int $userId = 0): array
	{
		$newData = [];

		foreach ($data as $key => $value)
		{
			if ($key === 'default_presets')
			{
				$newData[$key] = $this->migratePresets($value, $userId);
			}
			elseif ($key === 'filters')
			{
				$newData[$key] = $this->migrateFilters($value);
			}
			elseif ($key === 'default')
			{
				$newData[$key] = $this->migratePresetCode($value);
			}
			else
			{
				$newData[$key] = $value;
			}
		}

		return $newData;
	}

	protected function migratePresets(array $presetsData = [], int $userId = 0): array
	{
		static $isExtranetInstalled = null;

		if ($isExtranetInstalled === null)
		{
			$isExtranetInstalled = Loader::includeModule('extranet');
		}

		$newPresetsData = [];
		$modelPresetData = [];

		if ($userId > 0)
		{
			$extranetSiteId = '';

			if (
				$isExtranetInstalled
				&& !\CExtranet::isIntranetUser(SITE_ID, $userId)
			)
			{
				$extranetSiteId = \CExtranet::getExtranetSiteID();
			}

			$modelPresetData = \Bitrix\Socialnetwork\Integration\Main\UIFilter\Workgroup::getFilterPresetList([
				'currentUserId' => $userId,
				'extranetSiteId' => $extranetSiteId,
			]);
		}

		foreach ($presetsData as $presetCode => $preset)
		{
			$newPresetCode = $this->migratePresetCode($presetCode);
			$newPresetsData[$newPresetCode] = $this->migratePreset($preset);
		}

		return $this->mergeData($modelPresetData, $newPresetsData);
	}

	protected function migratePresetCode(string $presetCode = ''): string
	{
		$newPresetCode = $presetCode;

		switch ($presetCode)
		{
			case 'my':
				$newPresetCode = 'my';
				break;
			case 'active_project':
				$newPresetCode = 'active';
				break;
			case 'inactive_project':
				$newPresetCode = 'archive';
				break;
			default:
		}

		return $newPresetCode;
	}

	protected function migratePreset(array $presetData = []): array
	{
		$newData = [];

		foreach ($presetData as $key => $value)
		{
			if ($key === 'fields')
			{
				$newData[$key] = $this->migrateFields($value);
			}
			elseif ($key !== 'default')
			{
				$newData[$key] = $value;
			}
		}

		return $newData;
	}

	protected function migrateFilters(array $filtersData = []): array
	{
		$newFiltersData = [];

		foreach ($filtersData as $filterCode => $filter)
		{
			$newFilterCode = $this->migratePresetCode($filterCode);
			$newFiltersData[$newFilterCode] = $this->migrateFilter($filter);
		}

		return $newFiltersData;
	}

	protected function migrateFilter(array $filterData = []): array
	{
		$newData = [];

		foreach ($filterData as $key => $value)
		{
			if ($key === 'fields')
			{
				$newData[$key] = $this->migrateFields($value);
			}
			elseif ($key === 'filter_rows')
			{
				$newData[$key] = $this->migrateFilterRows((string)$value);
			}
			else
			{
				$newData[$key] = $value;
			}
		}

		return $newData;
	}

	protected function migrateFilterRows(string $value = ''): string
	{
		$newFilterRowsList = [];

		$filterRowsList = explode(',', $value);
		foreach ($filterRowsList as $filterRow)
		{
			switch ($filterRow)
			{
				case 'MEMBER_ID':
					$newFilterRowsList[] = 'MEMBER';
					break;
				case 'OWNER_ID':
					$newFilterRowsList[] = 'OWNER';
					break;
				case 'TAGS':
					$newFilterRowsList[] = 'TAG';
					break;
				case 'TYPE':
					$newFilterRowsList[] = 'VISIBLE';
					$newFilterRowsList[] = 'OPENED';
					$newFilterRowsList[] = 'PROJECT';
					$newFilterRowsList[] = 'EXTRANET';
					$newFilterRowsList[] = 'LANDING';
					break;
				default:
					$newFilterRowsList[] = $filterRow;
			}
		}

		return implode(',', $newFilterRowsList);
	}

	protected function migrateFields(array $fieldsData = []): array
	{
		$newData = [];

		foreach ($fieldsData as $key => $value)
		{
			$this->migrateFieldValue($value, $key, $newData);
		}

		return $newData;
	}

	protected function migrateFieldKey(string $key = ''): string
	{
		$newKey = $key;

		switch ($key)
		{
			case 'MEMBER_ID':
				$newKey = 'MEMBER';
				break;
			case 'MEMBER_ID_label':
				$newKey = 'MEMBER_label';
				break;
			case 'OWNER_ID':
				$newKey = 'OWNER';
				break;
			case 'OWNER_ID_label':
				$newKey = 'OWNER_label';
				break;
			case 'TAGS':
				$newKey = 'TAG';
				break;
			case 'TAGS_label':
				$newKey = 'TAG_label';
				break;
			default:
		}

		return $newKey;
	}

	protected function migrateFieldValue($value, string $key = '', array &$newData = []): void
	{
		switch ($key)
		{
			case 'MEMBER_ID':
			case 'OWNER_ID':
				$newData[$this->migrateFieldKey($key)] = (!empty($value) ? 'U' . $value : '');
				break;
			case 'TYPE':
				$this->migrateTypeField((string)$value, $newData);
				break;
			default:
				$newData[$this->migrateFieldKey($key)] = $value;
		}
	}

	protected function migrateTypeField(string $projectType, array &$newData = []): void
	{
		static $typesList = null;

		if ($typesList === null)
		{
			$typesList = Helper\Workgroup::getTypes();
		}

		if (isset($typesList[$projectType]))
		{
			if (isset($typesList[$projectType]['VISIBLE']))
			{
				$newData['VISIBLE'] = $typesList[$projectType]['VISIBLE'];
			}
			if (isset($typesList[$projectType]['OPENED']))
			{
				$newData['OPENED'] = $typesList[$projectType]['OPENED'];
			}
			if (isset($typesList[$projectType]['PROJECT']))
			{
				$newData['PROJECT'] = $typesList[$projectType]['PROJECT'];
			}
			if (isset($typesList[$projectType]['EXTERNAL']))
			{
				$newData['EXTRANET'] = $typesList[$projectType]['EXTERNAL'];
			}
			if (isset($typesList[$projectType]['LANDING']))
			{
				$newData['LANDING'] = $typesList[$projectType]['LANDING'];
			}
		}
	}

	protected function mergeData($modelPresetData, $newPresetsData)
	{
		foreach ($modelPresetData as $code => $modelPreset)
		{
			if (!isset($newPresetsData[$code]))
			{
				$newPresetsData[$code] = $modelPreset;
			}
		}

		return $newPresetsData;
	}
}

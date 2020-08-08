<?
namespace Bitrix\Socialnetwork\Integration\Main\UISelector;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Socialnetwork\UserToGroupTable;
use Bitrix\Socialnetwork\WorkgroupTable;

class SonetGroups extends \Bitrix\Main\UI\Selector\EntityBase
{
	public static function getFeatureOperations($feature = false)
	{
		$result = [];
		if (!$feature)
		{
			return $result;
		}

		switch($feature)
		{
			case 'blog':
				$result = [ 'premoderate_post', 'moderate_post', 'write_post', 'full_post' ];
				break;
			default:
				$result = [];
		}

		return $result;
	}

	public function getData($params = array())
	{
		$result = array(
			'ITEMS' => array(),
			'ITEMS_LAST' => array(),
			'ITEMS_HIDDEN' => array(),
			'ADDITIONAL_INFO' => array(
				'GROUPS_LIST' => array(
					'sonetgroups' => array(
						'TITLE' => Loc::getMessage('MAIN_UI_SELECTOR_TITLE_SONETGROUPS'),
						'TYPE_LIST' => array(Handler::ENTITY_TYPE_SONETGROUPS),
						'DESC_LESS_MODE' => 'Y',
						'SORT' => 20
					)
				),
				'SORT_SELECTED' => 300
			)
		);

		$entityType = Handler::ENTITY_TYPE_SONETGROUPS;

		$options = (!empty($params['options']) ? $params['options'] : array());

		$lastItems = (!empty($params['lastItems']) ? $params['lastItems'] : array());
		$selectedItems = (!empty($params['selectedItems']) ? $params['selectedItems'] : array());

		$limitReached = false;

		$filter = array(
			'limit' => 100,
			'useProjects' => (!empty($options['enableProjects']) ? $options['enableProjects'] : 'N')
		);

		if (!empty($options['siteId']))
		{
			$filter['siteId'] = $options['siteId'];
		}

		if (
			!empty($options['landing'])
			&& ModuleManager::isModuleInstalled('landing')
		)
		{
			$filter['landing'] = $options['landing'];
		}

		$sonetGroupsList = \Bitrix\Socialnetwork\ComponentHelper::getSonetGroupAvailable($filter, $limitReached);

		if (
			!empty($options['enableProjects'])
			&& $options['enableProjects'] == 'Y'
		)
		{
			$sonetGroupsList = $sonetGroupsList['SONETGROUPS'];
		}

		$result['ADDITIONAL_INFO']['LIMITED'] = ($limitReached ? 'Y' : 'N');

		$lastSonetGroupsList = array();
		if(!empty($lastItems[$entityType]))
		{
			$result["ITEMS_LAST"] = array_values($lastItems[$entityType]);
			foreach ($lastItems[$entityType] as $value)
			{
				$lastSonetGroupsList[] = str_replace('SG', '', $value);
			}
		}

		$selectedSonetGroupsList = array();
		if(!empty($selectedItems[Handler::ENTITY_TYPE_SONETGROUPS]))
		{
			foreach ($selectedItems[Handler::ENTITY_TYPE_SONETGROUPS] as $value)
			{
				$selectedSonetGroupsList[] = str_replace('SG', '', $value);
			}
		}

		if (!empty($lastSonetGroupsList))
		{
			$filter = array(
				'id' => $lastSonetGroupsList,
				'useProjects' => (!empty($options['enableProjects']) ? $options['enableProjects'] : 'N')
			);

			if (!empty($options['feature']))
			{
				$feature = $options['feature'];
				if (trim($feature) <> '')
				{
					$operations = self::getFeatureOperations($feature);
					if (!empty($operations))
					{
						$filter['features'] = [ $feature, $operations ];
					}
				}
			}

			if (
				!empty($options['landing'])
				&& ModuleManager::isModuleInstalled('landing')
			)
			{
				$filter['landing'] = $options['landing'];
			}
			if (!empty($options['siteId']))
			{
				$filter['site_id'] = $options['siteId'];
			}

			$sonetGroupsAdditionalList = \CSocNetLogDestination::getSocnetGroup($filter);
			if (!empty($sonetGroupsAdditionalList))
			{
				if (
					!empty($options['enableProjects'])
					&& $options['enableProjects'] == 'Y'
				)
				{
					$sonetGroupsAdditionalList = $sonetGroupsAdditionalList['SONETGROUPS'];
				}

				$sonetGroupsList = array_merge($sonetGroupsList, $sonetGroupsAdditionalList);
			}
		}

		if (!empty($selectedSonetGroupsList))
		{
			$filter = array(
				'id' => $selectedSonetGroupsList,
				'useProjects' => (!empty($options['enableProjects']) ? $options['enableProjects'] : 'N')
			);

			if (!empty($options['feature']))
			{
				$feature = $options['feature'];
				if (trim($feature) <> '')
				{
					$operations = self::getFeatureOperations($feature);
					if (!empty($operations))
					{
						$filter['features'] = [ $feature, $operations ];
					}
				}
			}

			if (
				!empty($options['landing'])
				&& ModuleManager::isModuleInstalled('landing')
			)
			{
				$filter['landing'] = $options['landing'];
			}
			if (!empty($options['siteId']))
			{
				$filter['site_id'] = $options['siteId'];
			}

			$sonetGroupsAdditionalList = \CSocNetLogDestination::getSocnetGroup($filter);
			if (!empty($sonetGroupsAdditionalList))
			{
				if (
					!empty($options['enableProjects'])
					&& $options['enableProjects'] == 'Y'
				)
				{
					$sonetGroupsAdditionalList = $sonetGroupsAdditionalList['SONETGROUPS'];
				}

				$sonetGroupsList = array_merge($sonetGroupsList, $sonetGroupsAdditionalList);
			}
		}

		if (!empty($selectedItems[Handler::ENTITY_TYPE_SONETGROUPS]))
		{
			$hiddenItemsList = array_diff($selectedItems[Handler::ENTITY_TYPE_SONETGROUPS], array_keys($sonetGroupsList));
			$hiddenItemsList = array_map(function($code) { return preg_replace('/^SG(\d+)$/', '$1', $code); }, $hiddenItemsList);

			if (!empty($hiddenItemsList))
			{
				$filter = array(
					"@ID" => $hiddenItemsList
				);

				if (
					!empty($options['enableProjects'])
					&& $options['enableProjects'] == 'Y'
				)
				{
					$filter['PROJECT'] = 'N';
				}

				$isCurrentUserModuleAdmin = \CSocNetUser::isCurrentUserModuleAdmin();
				$res = \Bitrix\Socialnetwork\WorkgroupTable::getList(array(
					'filter' => $filter,
					'select' => array("ID", "NAME", "DESCRIPTION", "OPENED", "VISIBLE")
				));

				$extranetGroupsIdList = \Bitrix\Socialnetwork\ComponentHelper::getExtranetSonetGroupIdList();

				while($groupFields = $res->fetch())
				{
					if (
						(
							$groupFields['OPENED'] == "Y"
							|| $isCurrentUserModuleAdmin
						)
						&& !Handler::isExtranetUser()
					)
					{
						$sonetGroupsList['SG'.$groupFields["ID"]] = array(
							"id" => 'SG'.$groupFields["ID"],
							"entityId" => $groupFields["ID"],
							"name" => $groupFields["NAME"],
							"desc" => $groupFields["DESCRIPTION"],
							"isExtranet" => (in_array($groupFields["ID"], $extranetGroupsIdList) ? 'Y' : 'N')
						);
					}
					elseif (
						$groupFields['VISIBLE'] == "Y"
						&& !Handler::isExtranetUser()
					)
					{
						$sonetGroupsList['SG'.$groupFields["ID"]] = array(
							"id" => 'SG'.$groupFields["ID"],
							"entityId" => $groupFields["ID"],
							"name" => $groupFields["NAME"],
							"desc" => $groupFields["DESCRIPTION"],
							"isExtranet" => (in_array($groupFields["ID"], $extranetGroupsIdList) ? 'Y' : 'N'),
							"selectable" => 'N'
						);
					}
					else
					{
						$result['ITEMS_HIDDEN'][] = 'SG'.$groupFields["ID"];
					}
				}
			}
		}

		$result['ITEMS'] = $sonetGroupsList;

		return $result;
	}

	public function getTabList($params = array())
	{
		$options = (!empty($params['options']) ? $params['options'] : array());

		return array(
			array(
				'id' => 'sonetgroups',
				'name' => Loc::getMessage('MAIN_UI_SELECTOR_TAB_SONETGROUPS'),
				'sort' => 30
			)
		);
	}

	public function search($params = array())
	{
		$result = array(
			'ITEMS' => array(),
			'ADDITIONAL_INFO' => array()
		);

		$entityOptions = (!empty($params['options']) ? $params['options'] : array());
		$requestFields = (!empty($params['requestFields']) ? $params['requestFields'] : array());

		if (
			!empty($entityOptions['additionalData'])
			&& !empty($entityOptions['additionalData']['LIMITED'])
			&& $entityOptions['additionalData']['LIMITED'] = 'Y'
		)
		{
			$filter = array(
				"SEARCH" => $requestFields['searchString'],
				"LANDING" => (!empty($entityOptions['landing']) && ModuleManager::isModuleInstalled('landing') && $entityOptions['landing'] == 'Y' ? 'Y' : 'N')
			);

			if (!empty($entityOptions['feature']))
			{
				$feature = $entityOptions['feature'];
				if (trim($feature) <> '')
				{
					$operations = self::getFeatureOperations($feature);
					if (!empty($operations))
					{
						$filter['FEATURES'] = [ $feature, $operations ];
					}
				}
			}

			if (!empty($entityOptions['siteId']))
			{
				$filter['SITE_ID'] = $entityOptions['siteId'];
			}
			$result["ITEMS"] = \CSocNetLogDestination::searchSonetGroups($filter);
		}

		return $result;
	}

	public function getItemName($itemCode = '')
	{
		return \Bitrix\Socialnetwork\Integration\Main\UISelector\SonetGroups::getWorkgroupName($itemCode);
	}

	public static function getWorkgroupName($itemCode = '')
	{
		global $USER;

		$result = '';

		$entityId = (
			preg_match('/^SG(\d+)$/i', $itemCode, $matches)
			&& intval($matches[1]) > 0
				? intval($matches[1])
				: 0
		);

		if ($entityId  > 0)
		{
			$res = WorkgroupTable::getList(array(
				'filter' => array(
					'=ID' => $entityId
				),
				'select' => array('VISIBLE', 'NAME')
			));
			if (
				($workgroupFields = $res->fetch())
				&& ($workgroupFields["VISIBLE"] == "Y")
				|| (\CSocNetUser::isCurrentUserModuleAdmin(SITE_ID, false))
			)
			{
				$result = $workgroupFields['NAME'];
			}

			if (empty($result))
			{
				$res = UserToGroupTable::getList(
					array(
						'filter' => array(
							'=GROUP_ID' => $entityId,
							'=USER_ID' => $USER->getId()
						),
						'select' => array(
							'GROUP_ID',
							'GROUP_NAME' => 'GROUP.NAME'
						)
					)
				);
				if ($relationFields = $res->fetch())
				{
					$result = $relationFields['GROUP_NAME'];
				}
			}
		}

		return $result;
	}


}
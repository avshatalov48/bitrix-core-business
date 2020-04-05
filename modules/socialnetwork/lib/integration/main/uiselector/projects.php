<?
namespace Bitrix\Socialnetwork\Integration\Main\UISelector;

use Bitrix\Main\Localization\Loc;

class Projects extends \Bitrix\Main\UI\Selector\EntityBase
{
	public function getData($params = array())
	{
		$result = array(
			'ITEMS' => array(),
			'ITEMS_LAST' => array(),
			'ITEMS_HIDDEN' => array(),
			'ADDITIONAL_INFO' => array(
				'SORT_SELECTED' => 300
			)
		);

		$entityType = Handler::ENTITY_TYPE_PROJECTS;

		$options = (!empty($params['options']) ? $params['options'] : array());

		$lastItems = (!empty($params['lastItems']) ? $params['lastItems'] : array());
		$selectedItems = (!empty($params['selectedItems']) ? $params['selectedItems'] : array());

		$limitReached = false;
		$projectsList = \Bitrix\Socialnetwork\ComponentHelper::getSonetGroupAvailable(array(
			'limit' => 100,
			'useProjects' => 'Y'
		), $limitReached);

		$projectsList = $projectsList['PROJECTS'];

		$result['ADDITIONAL_INFO']['LIMITED'] = ($limitReached ? 'Y' : 'N');

		$lastProjectsList = array();
		if(!empty($lastItems[$entityType]))
		{
			$result["ITEMS_LAST"] = array_values($lastItems[$entityType]);
			foreach ($lastItems[$entityType] as $value)
			{
				$lastProjectsList[] = str_replace('SG', '', $value);
			}
		}

		$selectedProjectsList = array();
		if(!empty($selectedItems[Handler::ENTITY_TYPE_SONETGROUPS]))
		{
			foreach ($selectedItems[Handler::ENTITY_TYPE_SONETGROUPS] as $value)
			{
				$selectedProjectsList[] = str_replace('SG', '', $value);
			}
		}

		if (!empty($lastProjectsList))
		{
			$filter = array(
				'features' => array("blog", array("premoderate_post", "moderate_post", "write_post", "full_post")),
				'id' => $lastProjectsList,
				'useProjects' => 'Y'
			);
			if (!empty($options['siteId']))
			{
				$filter['site_id'] = $options['siteId'];
			}
			$projectsAdditionalList = \CSocNetLogDestination::getSocnetGroup($filter);
			if (!empty($projectsAdditionalList))
			{
				$projectsAdditionalList = $projectsAdditionalList['PROJECTS'];
				$projectsList = array_merge($projectsList, $projectsAdditionalList);
			}
		}
		if (!empty($selectedProjectsList))
		{
			// available to post
			$filter = array(
				'features' => array("blog", array("premoderate_post", "moderate_post", "write_post", "full_post")),
				'id' => $selectedProjectsList,
				'useProjects' => 'Y'
			);
			if (!empty($options['siteId']))
			{
				$filter['site_id'] = $options['siteId'];
			}

			$projectsAdditionalList = \CSocNetLogDestination::getSocnetGroup($filter);
			if (!empty($projectsAdditionalList))
			{
				$projectsAdditionalList = $projectsAdditionalList['PROJECTS'];
				$projectsList = array_merge($projectsList, $projectsAdditionalList);
			}
		}

		if (!empty($selectedItems[Handler::ENTITY_TYPE_SONETGROUPS]))
		{
			$hiddenItemsList = array_diff($selectedItems[Handler::ENTITY_TYPE_SONETGROUPS], array_keys($projectsList));
			$hiddenItemsList = array_map(function($code) { return preg_replace('/^SG(\d+)$/', '$1', $code); }, $hiddenItemsList);

			if (!empty($hiddenItemsList))
			{
				$isCurrentUserModuleAdmin = \CSocNetUser::isCurrentUserModuleAdmin();
				$res = \Bitrix\Socialnetwork\WorkgroupTable::getList(array(
					'filter' => array(
						"@ID" => $hiddenItemsList,
						'PROJECT' => 'Y'
					),
					'select' => array("ID", "NAME", "DESCRIPTION", "OPENED")
				));

				$extranetGroupsIdList = \Bitrix\Socialnetwork\ComponentHelper::getExtranetSonetGroupIdList();

				while($groupFields = $res->fetch())
				{
					if (
						$groupFields['OPENED'] == "Y"
						|| $isCurrentUserModuleAdmin
					)
					{
						$projectsList['SG'.$groupFields["ID"]] = array(
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
						$projectsList['SG'.$groupFields["ID"]] = array(
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

		$result['ITEMS'] = $projectsList;

		return $result;
	}

	public function getTabList($params = array())
	{
		$options = (!empty($params['options']) ? $params['options'] : array());

		return array(
			array(
				'id' => 'projects',
				'name' => Loc::getMessage('MAIN_UI_SELECTOR_TAB_PROJECTS'),
				'sort' => 29
			)
		);
	}

	public function getItemName($itemCode = '')
	{
		return \Bitrix\Socialnetwork\Integration\Main\UISelector\SonetGroups::getWorkgroupName($itemCode);
	}
}
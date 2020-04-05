<?
/**
 * @access private
 */

namespace Bitrix\Photogallery\Integration\SocialNetwork;
class Group
{
	public static function onSocNetGroupDelete($groupId)
	{
		$iblockIdList = array();
		$res = \CIBlock::getList(array(), array("ACTIVE" => "Y", "CODE"=>"group_photogallery%"));
		while($iblock = $res->fetch())
		{
			$iblockIdList[] = $iblock["ID"];
		}

		if (empty($iblockIdList))
		{
			return true;
		}

		$res = \CIBlockSection::getList(
			array(),
			array(
				"IBLOCK_ID" => $iblockIdList,
				"SOCNET_GROUP_ID" => $groupId
			),
			false,
			array('ID', 'LEFT_MARGIN', 'RIGHT_MARGIN')
		);
		while ($section = $res->fetch())
		{
			@set_time_limit(1000);

			$treeSectionIdList = $treeElementIdList = array();

			$pseudoComponentParams = array(
				'IS_SOCNET' => 'Y',
				'USER_ALIAS' => 'group_'.$groupId
			);

			$pseudoComponentResult = array(
				'SECTION' => $section
			);

			foreach(getModuleEvents("photogallery", "OnBeforeSectionDrop", true) as $event)
			{
				executeModuleEventEx($event, array($section['ID'], $pseudoComponentParams, $pseudoComponentResult, &$treeSectionIdList, &$treeElementIdList));
			}

			if (\CIBlockSection::delete($section['ID'], false))
			{
				$eventFields = array(
					"ID" => $section['ID'],
					"SECTIONS_IN_TREE" => $treeSectionIdList,
					"ELEMENTS_IN_TREE" => $treeElementIdList
				);
				foreach(getModuleEvents("photogallery", "OnAfterSectionDrop", true) as $event)
				{
					executeModuleEventEx($event, array($section['ID'], $eventFields, $pseudoComponentParams, $pseudoComponentResult));
				}
			}
		}

		return true;
	}
}
?>
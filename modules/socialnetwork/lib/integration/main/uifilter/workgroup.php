<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2017 Bitrix
 */
namespace Bitrix\Socialnetwork\Integration\Main\UIFilter;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Workgroup
{
	public static function getFilterPresetList($params)
	{
		$result = array();

		$currentUserId = (!empty($params['currentUserId']) ? intval($params['currentUserId']) : false);

		if (Option::get("socialnetwork", "work_with_closed_groups", "N") != "Y")
		{
			$result['active'] = array(
				'name' => Loc::getMessage('SONET_C36_T_FILTER_PRESET_ACTIVE'),
				'fields' => array(
					'CLOSED' => 'N'
				),
				'default' => true
			);
		}

		if ($currentUserId)
		{
			$userLabel = '';
			$renderPartsUser = new \Bitrix\Socialnetwork\Livefeed\RenderParts\User(array('skipLink' => true));
			if ($renderData = $renderPartsUser->getData($currentUserId))
			{
				$userLabel = $renderData['name'];
			}

			$result['my'] = array(
				'name' => Loc::getMessage('SONET_C36_T_FILTER_PRESET_MY'),
				'fields' => array(
					'MEMBER' => 'U'.$currentUserId,
					'MEMBER_label' => $userLabel,
				)
			);
			$result['favorites'] = array(
				'name' => Loc::getMessage('SONET_C36_T_FILTER_PRESET_FAVORITES'),
				'fields' => array(
					'FAVORITES' => 'Y'
				)
			);
		}

		if (
			!empty($params['extranetSiteId'])
			&& SITE_ID != $params['extranetSiteId']
		)
		{
			$result['extranet'] = array(
				'name' => Loc::getMessage('SONET_C36_T_FILTER_PRESET_EXTRANET'),
				'fields' => array(
					'EXTRANET' => 'Y'
				)
			);
		}

		if (Option::get("socialnetwork", "work_with_closed_groups", "N") != "Y")
		{
			$result['archive'] = array(
				'name' => Loc::getMessage('SONET_C36_T_FILTER_PRESET_ARCHIVE'),
				'fields' => array(
					'CLOSED' => 'Y'
				)
			);
		}

		return $result;
	}
}

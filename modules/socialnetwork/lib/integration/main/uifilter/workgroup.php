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
	public static function getFilterPresetList($params): array
	{
		$result = [];

		$currentUserId = (int)($params['currentUserId'] ?? 0);

		if (Option::get('socialnetwork', 'work_with_closed_groups', 'N') !== 'Y')
		{
			$result['active'] = [
				'name' => Loc::getMessage('SONET_C36_T_FILTER_PRESET_ACTIVE'),
				'fields' => [
					'CLOSED' => 'N',
				],
				'default' => true,
			];
		}

		if ($currentUserId > 0)
		{
			$userLabel = '';
			$renderPartsUser = new \Bitrix\Socialnetwork\Livefeed\RenderParts\User([ 'skipLink' => true ]);
			if ($renderData = $renderPartsUser->getData($currentUserId))
			{
				$userLabel = $renderData['name'];
			}

			$result['my'] = [
				'name' => Loc::getMessage('SONET_C36_T_FILTER_PRESET_MY'),
				'fields' => [
					'MEMBER' => 'U' . $currentUserId,
					'MEMBER_label' => $userLabel,
				],
			];
			$result['favorites'] = [
				'name' => Loc::getMessage('SONET_C36_T_FILTER_PRESET_FAVORITES'),
				'fields' => [
					'FAVORITES' => 'Y',
				],
			];
		}

		if (
			!empty($params['extranetSiteId'])
			&& SITE_ID !== $params['extranetSiteId']
		)
		{
			$result['extranet'] = [
				'name' => Loc::getMessage('SONET_C36_T_FILTER_PRESET_EXTRANET'),
				'fields' => [
					'EXTRANET' => 'Y',
				],
			];
		}

		if (Option::get('socialnetwork', 'work_with_closed_groups', 'N') !== 'Y')
		{
			$result['archive'] = [
				'name' => Loc::getMessage('SONET_C36_T_FILTER_PRESET_ARCHIVE'),
				'fields' => [
					'CLOSED' => 'Y',
				],
			];
		}

		return $result;
	}
}

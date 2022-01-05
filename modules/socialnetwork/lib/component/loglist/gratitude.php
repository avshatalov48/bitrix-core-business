<?php

namespace Bitrix\Socialnetwork\Component\LogList;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

class Gratitude
{
	protected $component;

	public function __construct($params)
	{
		if (!empty($params['component']))
		{
			$this->component = $params['component'];
		}
	}

	public static function getGratitudesIblockData(array $params = []): array
	{
		$result = [
			'BADGES_DATA' => [],
			'ELEMENT_ID_LIST' => [],
			'GRAT_VALUE' => ''
		];

		$userId = (!empty($params['userId']) && (int)$params['userId'] > 0 ? (int)$params['userId'] : 0);
		if ($userId <= 0)
		{
			return $result;
		}

		if (!Loader::includeModule('iblock'))
		{
			return $result;
		}

		$honourIblockId = self::getGratitudesIblockId();
		$filter = [
			'IBLOCK_ID' => $honourIblockId,
			'ACTIVE' => 'Y',
			'=PROPERTY_USERS' => $userId,
		];

		$gratCode = (!empty($params['gratCode']) ? $params['gratCode'] : false);
		if ($gratCode)
		{
			$res = \CIBlockPropertyEnum::getList(
				[],
				[
					'IBLOCK_ID' => $honourIblockId,
					'CODE' => 'GRATITUDE',
					'XML_ID' => $gratCode
				]
			);
			if ($enumFields = $res->fetch())
			{
				$filter['PROPERTY_GRATITUDE'] = $enumFields['ID'];
				$result['GRAT_VALUE'] = $enumFields['VALUE'];
			}
		}

		$iblockElementsIdList = [];
		$badgesData = [];

		$res = \CIBlockElement::getList(
			[],
			$filter,
			false,
			false,
			[ 'ID', 'PROPERTY_GRATITUDE' ]
		);
		while($iblockElementFields = $res->fetch())
		{
			$badgeEnumId = $iblockElementFields['PROPERTY_GRATITUDE_ENUM_ID'];
			if (!isset($badgesData[$badgeEnumId]))
			{
				$badgesData[$badgeEnumId] = [
					'NAME' => $iblockElementFields['PROPERTY_GRATITUDE_VALUE'],
					'COUNT' => 0,
					'ID' => [],
				];
			}
			$badgesData[$badgeEnumId]['ID'][] = (int)$iblockElementFields['ID'];
			$iblockElementsIdList[] = $iblockElementFields['ID'];
		}

		$result['BADGES_DATA'] = $badgesData;
		$result['ELEMENT_ID_LIST'] = $iblockElementsIdList;

		return $result;
	}

	public static function getGratitudesIblockId()
	{
		return \Bitrix\Socialnetwork\Helper\Gratitude::getIblockId();
	}

	public static function getGratitudesBlogData(array $params = []): array
	{
		global $CACHE_MANAGER;

		$result = [
			'POST_ID_LIST' => [],
			'AUTHOR_ID_LIST' => [],
			'ELEMENT_ID_LIST' => [],
		];

		$iblockElementsIdList = (!empty($params['iblockElementsIdList']) && is_array($params['iblockElementsIdList']) ? $params['iblockElementsIdList'] : []);
		if (empty($iblockElementsIdList))
		{
			return $result;
		}

		if (!Loader::includeModule('blog'))
		{
			return $result;
		}

		$authorsIdList = [];

		$res = \Bitrix\Blog\PostTable::getList([
			'filter' => [
				'@UF_GRATITUDE' => $iblockElementsIdList
			],
			'select' => ['ID', 'AUTHOR_ID', 'UF_GRATITUDE']
		]);

		$iblockElementsIdList = [];
		while($postFields = $res->fetch())
		{
			$postIdList[] = $postFields['ID'];
			$authorsIdList[] = $postFields['AUTHOR_ID'];
			$iblockElementsIdList[] = $postFields['UF_GRATITUDE'];

			if (defined('BX_COMP_MANAGED_CACHE'))
			{
				$CACHE_MANAGER->registerTag('blog_post_' . $postFields['ID']);
				$CACHE_MANAGER->registerTag('USER_CARD_' . (int)($postFields['AUTHOR_ID'] / TAGGED_user_card_size));
			}
		}

		$result['POST_ID_LIST'] = $postIdList;
		$result['AUTHOR_ID_LIST'] = array_unique($authorsIdList);
		$result['ELEMENT_ID_LIST'] = $iblockElementsIdList;

		return $result;
	}

	/**
	 * Prepares filter for the gratUserId gratitudes list
	 *
	 * @param array $result component result.
	 * @return void
	 */
	public function prepareGratPostFilter(&$result): void
	{
		global $APPLICATION;

		$request = Util::getRequest();

		$result['GRAT_POST_FILTER'] = [];
		$result['RETURN_EMPTY_LIST'] = false;

		$userId = (int)$request->get('gratUserId');
		$gratCode = $request->get('gratCode');

		if (
			$userId
			&& ModuleManager::isModuleInstalled('intranet')
		)
		{
			$res = \CUser::getById($userId);
			$gratUserName = '';
			if ($userFields = $res->fetch())
			{
				$gratUserName = \CUser::formatName(\CSite::getNameFormat(false), $userFields, true);
			}

			$result['RETURN_EMPTY_LIST'] = true;
			$filterParams = [
				'userId' => $userId
			];

			if ($gratCode <> '')
			{
				$filterParams['gratCode'] = $gratCode;
			}

			$gratitudesData = self::getGratitudesIblockData($filterParams);
			$iblockElementsIdList = $gratitudesData['ELEMENT_ID_LIST'];
			$gratValue = '';

			if ($gratitudesData['GRAT_VALUE'] <> '')
			{
				$gratValue = $gratitudesData['GRAT_VALUE'];
			}

			$postIdList = [];
			if (!empty($iblockElementsIdList))
			{
				$gratitudesData = self::getGratitudesBlogData([
					'iblockElementsIdList' => $iblockElementsIdList,
				]);
				$postIdList = $gratitudesData['POST_ID_LIST'];
			}

			if (!empty($postIdList))
			{
				$result['GRAT_POST_FILTER'] = $postIdList;
				$result['RETURN_EMPTY_LIST'] = false;
			}

			if ($gratUserName <> '')
			{
				$APPLICATION->setTitle(Loc::getMessage($gratValue <> '' ? 'SONET_LOG_LIST_TITLE_GRAT2' : 'SONET_LOG_LIST_TITLE_GRAT', [
					'#USER_NAME#' => $gratUserName,
					'#GRAT_NAME#' => $gratValue,
				]));
			}
		}
	}
}

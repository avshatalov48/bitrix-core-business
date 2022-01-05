<?php

namespace Bitrix\Socialnetwork\Component\LogList;

use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;

class ParamPhotogallery
{
	protected $component;

	public function __construct($params)
	{
		if (!empty($params['component']))
		{
			$this->component = $params['component'];
		}
	}

	public function getComponent()
	{
		return $this->component;
	}

	public function preparePhotogalleryParams(&$componentParams): void
	{
		if (!ModuleManager::isModuleInstalled('photogallery'))
		{
			return;
		}

		Util::checkEmptyParamInteger($componentParams, 'PHOTO_COUNT', 6);
		Util::checkEmptyParamInteger($componentParams, 'PHOTO_THUMBNAIL_SIZE', 48);

		$folderUsers = $this->getComponent()->getPathInstance()->getFolderUsersValue();
		$folderWorkgroups = $this->getComponent()->getPathInstance()->getFolderWorkgroupsValue();

		Util::checkEmptyParamString($componentParams, 'PHOTO_USER_IBLOCK_TYPE', 'photos');

		if (
			(
				!isset($componentParams['PHOTO_USER_IBLOCK_ID'])
				|| (int)$componentParams['PHOTO_USER_IBLOCK_ID'] <= 0
			)
			&& Loader::includeModule('iblock')
		)
		{
			$res = \CIBlock::getList(
				[],
				[
					'SITE_ID' => SITE_ID,
					'=CODE' => 'user_photogallery'
				]
			);
			if ($iblockFields = $res->fetch())
			{
				$componentParams['PHOTO_USER_IBLOCK_ID'] = $iblockFields['ID'];
			}
		}

		if (
			(
				!isset($componentParams['PHOTO_FORUM_ID'])
				|| (int)$componentParams['PHOTO_FORUM_ID'] <= 0
			)
			&& Loader::includeModule('forum')
		)
		{
			$res = \CForumNew::getListEx(
				[],
				[
					'SITE_ID' => SITE_ID,
					'XML_ID' => 'PHOTOGALLERY_COMMENTS'
				]
			);
			if ($forumFields = $res->fetch())
			{
				$componentParams['PHOTO_FORUM_ID'] = $forumFields['ID'];
			}
		}

		Util::checkEmptyParamString($componentParams, 'PATH_TO_USER_PHOTO', $folderUsers.'user/#user_id#/photo/');
		Util::checkEmptyParamString($componentParams, 'PATH_TO_GROUP_PHOTO', $folderWorkgroups.'group/#group_id#/photo/');
		Util::checkEmptyParamString($componentParams, 'PATH_TO_USER_PHOTO_SECTION', $folderUsers.'user/#user_id#/photo/album/#section_id#/');
		Util::checkEmptyParamString($componentParams, 'PATH_TO_GROUP_PHOTO_SECTION', $folderWorkgroups.'group/#group_id#/photo/album/#section_id#/');
		Util::checkEmptyParamString($componentParams, 'PATH_TO_USER_PHOTO_ELEMENT', $folderUsers.'user/#user_id#/photo/photo/#section_id#/#element_id#/');
		Util::checkEmptyParamString($componentParams, 'PATH_TO_GROUP_PHOTO_ELEMENT', $folderWorkgroups.'group/#group_id#/photo/#section_id#/#element_id#/');
	}

	public function prepareParentPhotogalleryParams(&$componentParams): void
	{
		if (
			(
				(string)$componentParams['PHOTO_GROUP_IBLOCK_TYPE'] === ''
				|| (int)$componentParams['PHOTO_GROUP_IBLOCK_ID'] <= 0
			)
			&& Loader::includeModule('iblock'))
		{
			$ttl = 60*60*24;
			$cacheId = 'sonet_group_photo_iblock_'.SITE_ID;
			$cacheDir = '/bitrix/sonet_group_photo_iblock';
			$cache = new \CPHPCache;

			if($cache->initCache($ttl, $cacheId, $cacheDir))
			{
				$cacheData = $cache->getVars();
				$componentParams['PHOTO_GROUP_IBLOCK_TYPE'] = $cacheData['PHOTO_GROUP_IBLOCK_TYPE'];
				$componentParams['PHOTO_GROUP_IBLOCK_ID'] = $cacheData['PHOTO_GROUP_IBLOCK_ID'];
				unset($cacheData);
			}
			else
			{
				$res = \CIBlockType::getById('photos');
				if ($IBlockTypeFields = $res->fetch())
				{
					$resIBlock = \CIBlock::getList(
						[ 'SORT' => 'ASC' ],
						[
							'IBLOCK_TYPE' => $IBlockTypeFields['ID'],
							'CODE' => [ 'group_photogallery', 'group_photogallery_'.SITE_ID ],
							'ACTIVE' => 'Y',
							'SITE_ID' => SITE_ID
						]
					);
					if ($IBlockFields = $resIBlock->fetch())
					{
						$componentParams['PHOTO_GROUP_IBLOCK_TYPE'] = $IBlockFields['IBLOCK_TYPE_ID'];
						$componentParams['PHOTO_GROUP_IBLOCK_ID'] = $IBlockFields['ID'];
					}
				}

				if ($cache->startDataCache())
				{
					$cache->endDataCache([
						'PHOTO_GROUP_IBLOCK_TYPE' => $componentParams['PHOTO_GROUP_IBLOCK_TYPE'],
						'PHOTO_GROUP_IBLOCK_ID' => $componentParams['PHOTO_GROUP_IBLOCK_ID']
					]);
				}
			}
			unset($cache);
		}
	}
}

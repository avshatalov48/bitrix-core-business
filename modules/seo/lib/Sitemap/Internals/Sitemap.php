<?php

namespace Bitrix\Seo\Sitemap\Internals;

use \Bitrix\Main\Entity;
use \Bitrix\Main\Localization\Loc;
use Bitrix\Seo\Sitemap\Job;

Loc::loadMessages(__FILE__);

class SitemapTable extends Entity\DataManager
{
	const SETTINGS_DEFAULT_FILE_MASK = '*.php,*.html';

	const ACTIVE = 'Y';
	const INACTIVE = 'N';

	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_seo_sitemap';
	}

	public static function getMap()
	{
		$fieldsMap = [
			'ID' => [
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			],
			'TIMESTAMP_X' => [
				'data_type' => 'datetime'
			],
			'SITE_ID' => [
				'data_type' => 'string',
				'required' => true,
			],
			'ACTIVE' => [
				'data_type' => 'boolean',
				'values' => [self::INACTIVE, self::ACTIVE]
			],
			'NAME' => [
				'data_type' => 'string',
				'required' => true,
				'title' => Loc::getMessage('SITEMAP_NAME_TITLE'),
			],
			'DATE_RUN' => [
				'data_type' => 'datetime',
			],
			'SETTINGS' => [
				'data_type' => 'text',
			],
		];

		return $fieldsMap;
	}

	protected static function compileMask($mask)
	{
		if($mask <> '')
		{
			$arMask = preg_split("/[\s,;]+/", $mask);

			foreach ($arMask as $key => $subMask)
			{
				if($subMask <> '')
				{
					$arMask[$key] = str_replace(
						array("___ALL___", "___ONE___"),
						array(".*?", "."),
						preg_quote(str_replace(
							array("*", "?"),
							array("___ALL___", "___ONE___"),
							$subMask
						))
					);
				}
				else
				{
					unset($arMask[$key]);
				}
			}

			return "/^(".implode('|', $arMask).")$/i".BX_UTF_PCRE_MODIFIER;
		}
		else
		{
			return "/.*/i".BX_UTF_PCRE_MODIFIER;
		}
	}

	/**
	 * Gets settings from admin page and returns filtered version
	 * @param $arSettings
	 * @return array
	 */
	public static function prepareSettings($arSettings)
	{
		if(is_array($arSettings))
		{
			$arSettings['FILE_MASK_REGEXP'] = self::compileMask($arSettings['FILE_MASK']);

			if(!isset($arSettings['DIR']) || !is_array($arSettings['DIR']))
			{
				$arSettings['DIR'] = array();
			}

			if(isset($arSettings['FILE']) && is_array($arSettings['FILE']))
			{
				ksort($arSettings['FILE'], SORT_STRING);

				foreach($arSettings['FILE'] as $file => $value)
				{
					$pos = mb_strrpos($file, '/');
					$parentDir = $pos > 0? mb_substr($file, 0, $pos) : '/';

					if(isset($arSettings['DIR'][$parentDir]) && $arSettings['DIR'][$parentDir] == $value)
					{
						unset($arSettings['FILE'][$file]);
					}
				}
			}

			if(isset($arSettings['DIR']) && is_array($arSettings['DIR']))
			{
				krsort($arSettings['DIR'], SORT_STRING);

				foreach($arSettings['DIR'] as $dir => $value)
				{
					if($dir != '/')
					{
						$pos = mb_strrpos($dir, '/');
						$parentDir = mb_substr($dir, 0, $pos);

						if($parentDir == '')
							$parentDir = '/';

						if(isset($arSettings['DIR'][$parentDir]) && $arSettings['DIR'][$parentDir] == $value)
						{
							unset($arSettings['DIR'][$dir]);
						}
					}
				}

				$arSettings['DIR'] = array_reverse($arSettings['DIR']);
			}

			if(isset($arSettings['IBLOCK_ACTIVE']) && is_array($arSettings['IBLOCK_ACTIVE']))
			{
				foreach($arSettings['IBLOCK_ACTIVE'] as $iblockId => $value)
				{
					if ($value == 'N')
					{
						unset($arSettings['IBLOCK_LIST'][$iblockId]);
						unset($arSettings['IBLOCK_SECTION'][$iblockId]);
						unset($arSettings['IBLOCK_ELEMENT'][$iblockId]);
						unset($arSettings['IBLOCK_SECTION_SECTION'][$iblockId]);
						unset($arSettings['IBLOCK_SECTION_ELEMENT'][$iblockId]);
					}
				}
			}

			if(isset($arSettings['FORUM_ACTIVE']) && is_array($arSettings['FORUM_ACTIVE']))
			{
				foreach($arSettings['FORUM_ACTIVE'] as $forumId => $value)
				{
					if ($value == 'N')
					{
						unset($arSettings['FORUM_TOPIC'][$forumId]);
					}
				}
			}
		}

		return $arSettings;
	}

	/**
	 * Delete sitemap and all related settings
	 * @param int $id
	 * @return void
	 * @throws \Exception
	 */
	public static function fullDelete(int $id): void
	{
		$delRes = self::delete($id);
		if ($delRes->isSuccess())
		{
			RuntimeTable::clearByPid($id);
			ForumTable::clearBySitemap($id);
			IblockTable::clearBySitemap($id);
			Job::clearBySitemap($id);
		}
	}
}

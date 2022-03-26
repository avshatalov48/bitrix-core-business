<?php
namespace Bitrix\Landing\Site\Update;

use Bitrix\Landing\Folder;
use Bitrix\Landing\Landing;
use Bitrix\Landing\Site;
use Bitrix\Landing\Syspage;
use Bitrix\Landing\Template;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class ChatSales extends Update
{
	/**
	 * This updater expects only this code.
	 */
	private const ONLY_CODES = ['store-chats-dark'];

	/**
	 * Creates catalog's folder if not exists. If exists, return it id.
	 * @param int $siteId Site id.
	 * @return int|null
	 */
	private static function createFolder(int $siteId): ?int
	{
		$catalogFolderId = null;

		$folders = Site::getFolders($siteId);
		foreach ($folders as $folder)
		{
			if ($folder['CODE'] === 'catalog')
			{
				$catalogFolderId = $folder['ID'];
			}
		}

		if (!$catalogFolderId)
		{
			$res = Site::addFolder($siteId, [
				'TITLE' => Loc::getMessage('LANDING_SITE_UPDATE_CHAT_SALE_FOLDER_NAME'),
				'CODE' => 'catalog'
			]);
			if (!$res->isSuccess())
			{
				return false;
			}
			$catalogFolderId = $res->getId();
		}

		return $catalogFolderId;
	}

	/**
	 * Creates page by code if not exists. Returns page's id.
	 * @param int $siteId Site id.
	 * @param int $catalogFolderId Folder id.
	 * @param string $code Page code.
	 * @return int|null
	 */
	private static function createPageIfNotExists(int $siteId, int $catalogFolderId, string $code): ?int
	{
		$res = Landing::getList([
			'select' => [
				'ID'
			],
			'filter' => [
				'SITE_ID' => $siteId,
				'=TPL_CODE' => $code,
				'CHECK_PERMISSIONS' => 'N'
			]
		]);
		if ($row  = $res->fetch())
		{
			return $row['ID'];
		}
		else
		{
			$res = Landing::addByTemplate($siteId, $code, [
				'FOLDER_ID' => $catalogFolderId,
				'SITE_TYPE' => 'STORE'
			]);
			return $res->getId();
		}
	}

	private static function setEmptyTemplateToLanding($landingId): bool
	{
		static $emptyId = null;
		if (!$emptyId)
		{
			$res = Template::getList([
				'select' => [
					'ID', 'XML_ID'
				],
				'filter' => [
					'XML_ID' => 'empty'
				]
			]);
			if ($row = $res->fetch())
			{
				$emptyId = $row['ID'];
			}
			else
			{
				return false;
			}
		}

		$res = Landing::update($landingId, [
			'TPL_ID' => $emptyId
		]);
		return $res->isSuccess();
	}

	/**
	 * Entry point. Returns true on success.
	 * @param int $siteId Site id.
	 * @return bool
	 */
	public static function update(int $siteId): bool
	{
		$site = self::getId($siteId);

		if (!$site || !in_array($site['TPL_CODE'], self::ONLY_CODES))
		{
			return true;
		}

		$catalogFolderId = self::createFolder($siteId);
		if (!$catalogFolderId)
		{
			return false;
		}

		if ($pageId = self::createPageIfNotExists($siteId, $catalogFolderId, 'store-chats-dark/catalog_order'))
		{
			if (!self::setEmptyTemplateToLanding($pageId))
			{
				return false;
			}
			$landing = Landing::createInstance($pageId);
			if (!$landing->publication())
			{
				return false;
			}
		}
		else
		{
			return false;
		}

		if ($pageId = self::createPageIfNotExists($siteId, $catalogFolderId, 'store-chats-dark/catalog'))
		{
			if (!self::setEmptyTemplateToLanding($pageId))
			{
				return false;
			}
			$landing = Landing::createInstance($pageId);
			if (!$landing->publication())
			{
				return false;
			}
			Syspage::set($siteId, 'catalog', $pageId);
			Folder::update($catalogFolderId, [
				'INDEX_ID' => $pageId
			]);
		}
		else
		{
			return false;
		}

		return true;
	}
}

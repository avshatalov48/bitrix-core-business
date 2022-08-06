<?php
namespace Bitrix\Landing\Site\Update;

use Bitrix\Landing\Folder;
use Bitrix\Landing\Landing;
use Bitrix\Landing\Site;
use Bitrix\Landing\Syspage;
use Bitrix\Landing\Template;
use Bitrix\Landing\TemplateRef;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class ChatSales extends Update
{
	/**
	 * This updater expects only this code.
	 */
	private const ONLY_CODES = [
		'store-chats-dark',
		'store-chats-light',
		'store-chats',
	];

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
			if (
				$folder['CODE'] === 'catalog'
				|| $folder['CODE'] === 'katalog'
			)
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
		// find or create
		$res = Landing::getList([
			'select' => [
				'ID'
			],
			'filter' => [
				'DELETED' => 'N',
				'SITE_ID' => $siteId,
				'=TPL_CODE' => $code,
				'CHECK_PERMISSIONS' => 'N'
			]
		]);
		if ($row  = $res->fetch())
		{
			$pageId = $row['ID'];
			return $pageId;
		}

		$res = Landing::addByTemplate($siteId, $code, [
			'FOLDER_ID' => $catalogFolderId,
			'SITE_TYPE' => 'STORE'
		]);
		$pageId = $res->getId();

		if (
			$pageId
			&& Landing::createInstance($pageId)->publication()
		)
		{
			return $pageId;
		}

		return null;
	}

	private static function setTemplateToLanding(int $landingId, string $templateName, array $templateReferences): bool
	{
		$resTemplate = Template::getList([
			'select' => [
				'ID', 'XML_ID'
			],
			'filter' => [
				'XML_ID' => $templateName
			]
		]);
		if ($template = $resTemplate->fetch())
		{
			$resUpdate = Landing::update($landingId, [
				'TPL_ID' => $template['ID']
			]);

			if ($resUpdate->isSuccess())
			{
				TemplateRef::setForLanding($landingId, $templateReferences);

				return true;
			}
		}

		return false;
	}

	private static function setIndexToFolder(int $folderId, int $indexId): void
	{
		$landing = Landing::createInstance($indexId);
		if ($landing)
		{
			Folder::update($folderId, [
				'INDEX_ID' => $indexId
			]);
		}
	}

	/**
	 * Entry point. Returns true on success.
	 * @param int $siteId Site id.
	 * @return bool
	 */
	public static function update(int $siteId): bool
	{
		$site = self::getId($siteId);

		if (!$site || !in_array($site['TPL_CODE'], self::ONLY_CODES, true))
		{
			return true;
		}

		$catalogFolderId = self::createFolder($siteId);
		if (!$catalogFolderId)
		{
			return false;
		}

		if (
			!($orderId = self::createPageIfNotExists($siteId, $catalogFolderId, 'store-chats-dark/catalog_order'))
			|| !($detailId = self::createPageIfNotExists($siteId, $catalogFolderId, 'store-chats-dark/catalog_detail'))
			|| !($catalogId = self::createPageIfNotExists($siteId, $catalogFolderId, 'store-chats-dark/catalog'))
			|| !($headerId = self::createPageIfNotExists($siteId, $catalogFolderId, 'store-chats-dark/catalog_header'))
			|| !($footerId = self::createPageIfNotExists($siteId, $catalogFolderId, 'store-chats-dark/catalog_footer'))
		)
		{
			return false;
		}

		Syspage::set($siteId, 'catalog', $catalogId);
		self::setIndexToFolder($catalogFolderId, $catalogId);
		self::setTemplateToLanding($catalogId, 'header_footer', [1 => $headerId, 2 => $footerId]);
		self::setTemplateToLanding($detailId, 'header_footer', [1 => $headerId, 2 => $footerId]);
		self::setTemplateToLanding($orderId, 'header_footer', [1 => $headerId, 2 => $footerId]);

		return true;
	}
}

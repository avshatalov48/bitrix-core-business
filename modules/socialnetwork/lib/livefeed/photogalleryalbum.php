<?php

namespace Bitrix\Socialnetwork\Livefeed;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Iblock\SectionTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Socialnetwork\LogTable;

Loc::loadMessages(__FILE__);

final class PhotogalleryAlbum extends Provider
{
	public const PROVIDER_ID = 'PHOTO_ALBUM';
	public const CONTENT_TYPE_ID = 'PHOTO_ALBUM';

	public static function getId(): string
	{
		return static::PROVIDER_ID;
	}

	public function getEventId(): array
	{
		return [ 'photo' ];
	}

	public function getType(): string
	{
		return Provider::TYPE_POST;
	}

	public function getCommentProvider(): Provider
	{
		return new LogComment();
	}

	public function initSourceFields()
	{
		static $cache = [];

		$sectionId = $this->entityId;

		if ($sectionId <= 0)
		{
			return;
		}

		$albumFields = [];

		if (isset($cache[$sectionId]))
		{
			$albumFields = $cache[$sectionId];
		}
		elseif (Loader::includeModule('iblock'))
		{
			$res = SectionTable::getList([
				'filter' => [
					'=ID' => $sectionId
				],
				'select' => [ 'ID', 'NAME' ]
			]);
			if ($sectionFields = $res->fetch())
			{
				$logId = false;

				$res = LogTable::getList([
					'filter' => [
						'SOURCE_ID' => $sectionId,
						'@EVENT_ID' => $this->getEventId(),
					],
					'select' => [ 'ID', 'URL' ]
				]);
				if ($logEntryFields = $res->fetch())
				{
					$logId = (int)$logEntryFields['ID'];
				}

				if ($logId)
				{
					$res = \CSocNetLog::getList(
						[],
						[
							'=ID' => $logId
						],
						false,
						false,
						[ 'ID', 'EVENT_ID', 'URL' ],
						[
							"CHECK_RIGHTS" => "Y",
							"USE_FOLLOW" => "N",
							"USE_SUBSCRIBE" => "N"
						]
					);
					if ($logFields = $res->fetch())
					{
						$albumFields = array_merge($sectionFields, [
							'LOG_ID' => $logFields['ID'],
							'LOG_EVENT_ID' => $logFields['EVENT_ID'],
							'URL' => $logFields['URL']
						]);
					}
				}
			}

			$cache[$sectionId] = $albumFields;
		}

		if (empty($albumFields))
		{
			return;
		}

		$this->setLogId($albumFields['LOG_ID']);
		$this->setSourceFields($albumFields);

		$title = $albumFields['NAME'];
		$this->setSourceDescription($title);
		$this->setSourceTitle($title);
	}

	public function getPinnedTitle(): string
	{
		$result = '';

		if (empty($this->sourceFields))
		{
			$this->initSourceFields();
		}

		$albumFields = $this->getSourceFields();
		if (empty($albumFields))
		{
			return $result;
		}

		return (string)Loc::getMessage('SONET_LIVEFEED_PHOTOGALLERY_ALBUM_PINNED_TITLE', [
			'#TITLE#' => $albumFields['NAME']
		]);
	}

	public function getPinnedDescription(): string
	{
		return '';
	}

	public static function canRead($params): bool
	{
		return true;
	}

	protected function getPermissions(array $post): string
	{
		return self::PERMISSION_READ;
	}

	public function getLiveFeedUrl()
	{
		$pathToPhoto = '';

		if (
			($message = $this->getSourceFields())
			&& !empty($message)
		)
		{
			$pathToPhoto = str_replace(
				"#GROUPS_PATH#",
				Option::get('socialnetwork', 'workgroups_page', '/workgroups/', $this->getSiteId()),
				$message['URL']
			);
		}

		return $pathToPhoto;
	}

}
<?php
namespace Bitrix\Socialnetwork\Livefeed;

use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Socialnetwork\LogTable;

Loc::loadMessages(__FILE__);

final class PhotogalleryPhoto extends Provider
{
	public const PROVIDER_ID = 'PHOTO_PHOTO';
	public const CONTENT_TYPE_ID = 'PHOTO_PHOTO';

	protected static $iblockElementClass = ElementTable::class;
	protected static $logTableClass = LogTable::class;
	protected static $logClass = \CSocNetLog::class;

	public static function getId(): string
	{
		return static::PROVIDER_ID;
	}

	public function getEventId()
	{
		return [ 'photo_photo' ];
	}

	public function getType()
	{
		return Provider::TYPE_POST;
	}

	public function getCommentProvider()
	{
		return new ForumPost();
	}

	public function initSourceFields()
	{
		static $cache = [];

		$elementId = (int)$this->entityId;

		if ($elementId <= 0)
		{
			return;
		}

		$photoFields = [];

		if (isset($cache[$elementId]))
		{
			$photoFields = $cache[$elementId];
		}
		elseif (Loader::includeModule('iblock'))
		{
			$res = self::$iblockElementClass::getList([
				'filter' => [
					'=ID' => $elementId
				],
				'select' => [ 'ID', 'NAME' ]
			]);
			if ($element = $res->fetch())
			{
				$logId = false;

				$res = self::$logTableClass::getList([
					'filter' => [
						'SOURCE_ID' => $elementId,
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
					$res = self::$logClass::getList(
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
						$photoFields = array_merge($element, [
							'LOG_ID' => $logFields['ID'],
							'LOG_EVENT_ID' => $logFields['EVENT_ID'],
							'URL' => $logFields['URL']
						]);
					}
				}
			}

			$cache[$elementId] = $photoFields;
		}

		if (empty($photoFields))
		{
			return;
		}

		$this->setLogId($photoFields['LOG_ID']);
		$this->setSourceFields($photoFields);

		$title = $photoFields['NAME'];
		$this->setSourceDescription($title);
		$this->setSourceTitle($title);
	}

	public function getPinnedTitle()
	{
		$result = '';

		if (empty($this->sourceFields))
		{
			$this->initSourceFields();
		}

		$photoFields = $this->getSourceFields();
		if (empty($photoFields))
		{
			return $result;
		}

		$result = Loc::getMessage('SONET_LIVEFEED_PHOTOGALLERY_PHOTO_PINNED_TITLE', [
			'#TITLE#' => $photoFields['NAME']
		]);

		return $result;
	}

	public function getPinnedDescription()
	{
		return '';
	}

	public static function canRead($params)
	{
		return true;
	}

	protected function getPermissions(array $post)
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
<?php
namespace Bitrix\Socialnetwork\Livefeed;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Forum\MessageTable;

Loc::loadMessages(__FILE__);

final class ForumPost extends Provider
{
	const PROVIDER_ID = 'FORUM_POST';
	const TYPE = 'comment';
	const CONTENT_TYPE_ID = 'FORUM_POST';

	public static function getId()
	{
		return static::PROVIDER_ID;
	}

	public function getEventId()
	{
		return array('forum', 'tasks_comment', 'calendar_comment');
	}

	public function getType()
	{
		return static::TYPE;
	}

	public function initSourceFields()
	{
		$messageId = $this->entityId;

		if (
			$messageId > 0
			&& Loader::includeModule('forum')
		)
		{
			$res = MessageTable::getList(array(
				'filter' => array(
					'=ID' => $messageId
				),
				'select' => array('ID', 'POST_MESSAGE')
			));
			if ($message = $res->fetch())
			{
				$this->setSourceFields($message);
				$this->setSourceDescription($message['POST_MESSAGE']);
				$this->setSourceTitle('');
				$this->setSourceAttachedDiskObjects($this->getAttachedDiskObjects($messageId));
				$this->setSourceDiskObjects($this->getDiskObjects($messageId, $this->cloneDiskObjects));
			}
		}
	}

	protected function getAttachedDiskObjects($clone = false)
	{
		global $USER_FIELD_MANAGER;
		static $cache = array();

		$messageId = $this->entityId;

		$result = array();
		$cacheKey = $messageId.$clone;

		if (isset($cache[$cacheKey]))
		{
			$result = $cache[$cacheKey];
		}
		else
		{
			$messageUF = $USER_FIELD_MANAGER->getUserFields("FORUM_MESSAGE", $messageId, LANGUAGE_ID);
			if (
				!empty($messageUF['UF_FORUM_MES_URL_PRV'])
				&& !empty($messageUF['UF_FORUM_MES_URL_PRV']['VALUE'])
				&& is_array($messageUF['UF_FORUM_MES_URL_PRV']['VALUE'])
			)
			{
				if ($clone)
				{
					$this->attachedDiskObjectsCloned = self::cloneUfValues($messageUF['UF_FORUM_MES_URL_PRV']['VALUE']);
					$result = $cache[$cacheKey] = array_values($this->attachedDiskObjectsCloned);
				}
				else
				{
					$result = $cache[$cacheKey] = $messageUF['UF_FORUM_MES_URL_PRV']['VALUE'];
				}
			}
		}

		return $result;
	}

	public static function canRead($params)
	{
		return true;
	}

	protected function getPermissions(array $post)
	{
		$result = self::PERMISSION_READ;

		return $result;
	}

	public function getLiveFeedUrl()
	{
		return '';
	}
}
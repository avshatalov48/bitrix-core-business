<?php
namespace Bitrix\Socialnetwork\Livefeed;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UserTable;
use Bitrix\Socialnetwork\LogTable;

Loc::loadMessages(__FILE__);

class IntranetNewUser extends Provider
{
	const PROVIDER_ID = 'INTRANET_NEW_USER';
	const CONTENT_TYPE_ID = 'INTRANET_NEW_USER';

	public static function getId()
	{
		return static::PROVIDER_ID;
	}

	public function getEventId()
	{
		return array('intranet_new_user');
	}

	public function getType()
	{
		return Provider::TYPE_POST;
	}

	public function getCommentProvider()
	{
		$provider = new \Bitrix\Socialnetwork\Livefeed\LogComment();
		return $provider;
	}

	public function initSourceFields()
	{
		$ratingEntityId = $this->getEntityId();
		$userId = 0;

		if (!empty($ratingEntityId))
		{
			$res = LogTable::getList(array(
				'filter' => array(
					'@EVENT_ID' => $this->getEventId(),
					'=RATING_ENTITY_ID' => $ratingEntityId
				),
				'select' => array('ID', 'ENTITY_ID')
			));
			if ($logEntry = $res->fetch())
			{
				$this->setLogId($logEntry['ID']);
				$userId = $logEntry['ENTITY_ID'];
			}
		}

		if ($userId > 0)
		{
			$res = UserTable::getList(array(
				'filter' => array(
					'=ID' => $userId
				)
			));
			if ($user = $res->fetch())
			{
				$this->setSourceFields($user);

				$userName = \CUser::formatName(
					\CSite::getNameFormat(),
					$user,
					true,
					false
				);
				$user['FULL_NAME'] = $userName;

				$this->setSourceFields(array_merge($user, array('LOG_ENTRY' => $logEntry)));
				$this->setSourceTitle(Loc::getMessage('SONET_LIVEFEED_INTRANET_NEW_USER_TITLE', array(
					'#USER_NAME#' => $userName
				)));
			}
		}
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
		$pathToLogEntry = '';

		$logId = $this->getLogId();
		if ($logId)
		{
			$pathToLogEntry = Option::get('socialnetwork', 'log_entry_page', '', $this->getSiteId());
			if (!empty($pathToLogEntry))
			{
				$pathToLogEntry = \CComponentEngine::makePathFromTemplate($pathToLogEntry, array("log_id" => $logId));
//				$pathToLogEntry .= (strpos($pathToLogEntry, '?') === false ? '?' : '&').'commentId='.$this->getEntityId().'#com'.$this->getEntityId();
			}
		}
		return $pathToLogEntry;
	}
}
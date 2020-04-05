<?php
namespace Bitrix\Socialnetwork\Livefeed;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UserTable;
use Bitrix\Socialnetwork\LogTable;

final class Bitrix24NewUser extends IntranetNewUser
{
	const PROVIDER_ID = 'BITRIX24_NEW_USER';
	const CONTENT_TYPE_ID = 'BITRIX24_NEW_USER';

	public function getEventId()
	{
		return array('bitrix24_new_user');
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
				$this->setSourceTitle(Loc::getMessage('SONET_LIVEFEED_BITRIX24_NEW_USER_TITLE', array(
					'#USER_NAME#' => $userName
				)));
			}
		}
	}


}
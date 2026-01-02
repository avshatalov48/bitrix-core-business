<?php

namespace Bitrix\Vote\Integration\Im;

use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;
use Bitrix\Vote\Attach;
use Bitrix\Vote\Attachment\ImMessageConnector;
use Bitrix\Vote\Attachment\Manager;
use Bitrix\Vote\EO_Attach;

class ImVoteEventHandler
{
	public static function onDeleteByEntityId(?int $messageId, array $messageFields): void
	{
		if (
			$messageId <= 0
			|| empty($messageFields['PARAMS']['COMPONENT_ID'])
			|| !Loader::includeModule('im')
			|| $messageFields['PARAMS']['COMPONENT_ID'] !== ImVote::MESSAGE_COMPONENT_ID
		)
		{
			return;
		}

		$attaches = Manager::loadFromEntity([
			'=MODULE_ID' => 'im',
			'=ENTITY_TYPE' => ImMessageConnector::className(),
			'=ENTITY_ID' => $messageId,
		]);

		foreach ($attaches as $attach)
		{
			$attach->delete();
		}
	}

	public static function onImMessageAdd(int $messageId, array $messageFields): void
	{
		if (
			$messageId <= 0
			|| empty($messageFields['PARAMS']['COMPONENT_ID'])
			|| $messageFields['PARAMS']['COMPONENT_ID'] !== ImVote::MESSAGE_COMPONENT_ID
			|| empty($messageFields['PARAMS']['COMPONENT_PARAMS'][ImVote::MESSAGE_COMPONENT_PARAM_VOTE_ID])
		)
		{
			return;
		}

		for ($try = 1; $try <= 2; $try++)
		{
			$result = (new EO_Attach())
				->setModuleId('im')
				->setObjectId($messageFields['PARAMS']['COMPONENT_PARAMS'][ImVote::MESSAGE_COMPONENT_PARAM_VOTE_ID])
				->setEntityId($messageId)
				->setEntityType(ImMessageConnector::className())
				->setCreatedBy($messageFields['AUTHOR_ID'] ?? null)
				->setCreateTime(new DateTime())
				->setUid(Attach::generateUid())
				->save()
			;
			if ($result->isSuccess())
			{
				break;
			}
		}
	}
}
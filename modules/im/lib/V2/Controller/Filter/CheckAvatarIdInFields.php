<?php

namespace Bitrix\Im\V2\Controller\Filter;

use Bitrix\Disk\Driver;
use Bitrix\Disk\File;
use Bitrix\Disk\Security\DiskSecurityContext;
use Bitrix\Im\V2\Chat;
use Bitrix\Main\Engine\ActionFilter\Base;
use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\File\Image;

class CheckAvatarIdInFields extends Base
{
	public function onBeforeAction(Event $event)
	{
		$fields = $this->getAction()->getArguments()['fields'];
		$avatarId = $fields['avatar'] ?? null;
		if (!is_numeric($avatarId))
		{
			return null;
		}

		$avatarResult = \CFile::GetByID($avatarId);
		$avatar = (isset($avatarResult) && $avatarResult) ? $avatarResult->Fetch() : null;
		$info = (new Image($_SERVER["DOCUMENT_ROOT"] . $avatar['SRC']))->getInfo();
		if (!$info)
		{
			$this->addError(new Error(
				'Wrong file type',
				Chat\ChatError::WRONG_PARAMETER
			));
			return new EventResult(EventResult::ERROR, null, null, $this);
		}

		$currentUser = $this->getAction()->getCurrentUser();
		$userId = isset($currentUser) ? $currentUser->getId() : null;
		$securityContext = new DiskSecurityContext((int)$userId);
		$parameters = [
			'filter' => ['FILE_ID' => $avatarId],
			'with' => ['CREATE_USER']
		];
		$parameters = Driver::getInstance()->getRightsManager()->addRightsCheck($securityContext, $parameters, ['ID', 'CREATED_BY']);

		$fileCollection = File::getModelList($parameters);
		if (!$fileCollection)
		{
			$this->addError(new Error(
				'File is not accessible',
				Chat\ChatError::WRONG_PARAMETER
			));
			return new EventResult(EventResult::ERROR, null, null, $this);
		}

		return null;
	}
}

<?php

namespace Bitrix\Im\Controller;

use Bitrix\Im\Alias;
use Bitrix\Im\User;
use Bitrix\Im\Model\AliasTable;
use Bitrix\Main\Context;
use Bitrix\Main\Engine\Action;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Engine\JsonController;
use Bitrix\Main\Engine\JsonPayload;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use Bitrix\Im\Call\Conference as ConferenceClass;

class Conference extends JsonController
{
	protected function processBeforeAction(Action $action): bool
	{
		if (!Loader::includeModule('im'))
		{
			$this->addError(new Error("Module IM is not installed"));

			return false;
		}

		if (User::getInstance()->isExtranet())
		{
			$this->addError(new Error("You dont have access to this action"));

			return false;
		}

		return true;
	}

	public function prepareAction(JsonPayload $payload)
	{
		$result = [];
		$result['ALIAS_DATA'] = Alias::addUnique(
			[
				"ENTITY_TYPE" => Alias::ENTITY_TYPE_VIDEOCONF,
				"ENTITY_ID" => 0
			]
		);
		$result['DEFAULT_TITLE'] = \CIMChat::getNextConferenceDefaultTitle();

		return $result;
	}

	public function createAction(JsonPayload $payload)
	{
		$fields = $payload->getData()['fields'];
		$fields = array_change_key_case($fields, CASE_UPPER);

		$fields['ID'] = (int)$fields['ID'];
		$editMode = $fields['ID'] > 0;

		$result = null;
		if ($editMode)
		{
			$conference = ConferenceClass::getById($fields['ID']);

			if (!$conference)
			{
				$this->addError(new Error(Loc::getMessage('IM_CONFERENCE_EDIT_ERROR_WRONG_ID')));

				return null;
			}

			if (!$conference->canUserEdit(CurrentUser::get()->getId()))
			{
				$this->addError(new Error(Loc::getMessage('IM_CONFERENCE_EDIT_ERROR_CANT_EDIT')));

				return null;
			}

			$result = $conference->update($fields);
		}
		else
		{
			$aliasData = $payload->getData()['aliasData'];
			if (
				!$this->checkRequirements() ||
				!Alias::getByIdAndCode($aliasData['ID'], $aliasData['ALIAS'])
			)
			{
				$this->addError(new Error(Loc::getMessage('IM_CONFERENCE_EDIT_CREATION_ERROR')));

				return null;
			}
			$fields['ALIAS_DATA'] = $aliasData;

			$result = ConferenceClass::add($fields);
		}

		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return null;
		}

		if ($editMode)
		{
			return $result;
		}

		return [
			'CHAT_ID' => $result->getData()['CHAT_ID']
		];
	}

	protected function checkRequirements(): bool
	{
		return Loader::includeModule('pull') &&
			   \CPullOptions::GetPublishWebEnabled() &&
			   \Bitrix\Im\Call\Call::isCallServerEnabled() === true;
	}
}
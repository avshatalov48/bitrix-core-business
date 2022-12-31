<?php

namespace Bitrix\Im\Controller;

use Bitrix\Im\Alias;
use Bitrix\Im\User;
use Bitrix\Main\Engine\Action;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Engine\JsonController;
use Bitrix\Main\Engine\JsonPayload;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
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

	public function prepareAction(JsonPayload $payload): array
	{
		$result = [];
		$result['ALIAS_DATA'] = Alias::addUnique([
			"ENTITY_TYPE" => Alias::ENTITY_TYPE_VIDEOCONF,
			"ENTITY_ID" => 0
		]);
		$result['DEFAULT_TITLE'] = \CIMChat::getNextConferenceDefaultTitle();

		return $result;
	}

	public function createAction(JsonPayload $payload)
	{
		if (!isset($payload->getData()['fields']))
		{
			$this->addError(new Error(Loc::getMessage('IM_CONFERENCE_EDIT_CREATION_ERROR')));

			return null;
		}

		$fields = $payload->getData()['fields'];
		$fields = array_change_key_case($fields, CASE_UPPER);

		$fields['ID'] = isset($fields['ID']) ? (int)$fields['ID'] : 0;
		if ($fields['ID'] > 0)
		{
			$updatingResult = $this->updateConference($fields);
			if (!$updatingResult->isSuccess())
			{
				$this->addErrors($updatingResult->getErrors());

				return null;
			}

			return $updatingResult;
		}

		$addingResult = $this->createConference($payload, $fields);
		if (!$addingResult->isSuccess())
		{
			$this->addErrors($addingResult->getErrors());

			return null;
		}

		$aliasData = $addingResult->getData()['ALIAS_DATA'];

		return [
			'CHAT_ID' => $addingResult->getData()['CHAT_ID'],
			'ALIAS' => $aliasData['ALIAS'],
			'LINK' => $aliasData['LINK']
		];
	}

	private function updateConference(array $fields): Result
	{
		$updatingResult = new Result();
		$conference = ConferenceClass::getById($fields['ID']);

		if (!$conference)
		{
			return $updatingResult->addError(new Error(Loc::getMessage('IM_CONFERENCE_EDIT_ERROR_WRONG_ID')));
		}

		if (!$conference->canUserEdit(CurrentUser::get()->getId()))
		{
			return $updatingResult->addError(new Error(Loc::getMessage('IM_CONFERENCE_EDIT_ERROR_CANT_EDIT')));
		}

		return $conference->update($fields);
	}

	private function createConference(JsonPayload $payload, array $fields): Result
	{
		$addingResult = new Result();

		// link was created before
		if (isset($payload->getData()['aliasData']))
		{
			$fields['ALIAS_DATA'] = $payload->getData()['aliasData'];
		}

		return ConferenceClass::add($fields);
	}
}
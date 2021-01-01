<?

use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Engine\JsonController;
use Bitrix\Main\Engine\JsonPayload;
use Bitrix\Main\Loader;
use Bitrix\Im\Call\Conference;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)die();

class ImConferenceEditController extends JsonController
{
	public function submitFormAction(JsonPayload $payload)
	{
		$fields = $payload->getData()['fields'];
		if (!Loader::includeModule('im'))
		{
			$this->addError(new Error(Loc::getMessage('IM_CONFERENCE_EDIT_ERROR_IM_NOT_INSTALLED')));

			return null;
		}

		$fields = array_change_key_case($fields, CASE_UPPER);

		$fields['ID'] = (int)$fields['ID'];
		$editMode = $fields['ID'] > 0;

		$result = null;
		if ($editMode)
		{
			$conference = Conference::getById($fields['ID']);

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
			$result = Conference::add($fields);
		}

		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return null;
		}

		return $result;
	}
}
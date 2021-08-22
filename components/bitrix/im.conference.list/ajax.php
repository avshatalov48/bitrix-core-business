<?

use Bitrix\Im\Model\ConferenceTable;
use Bitrix\Main\Engine\Action;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Im\Call\Conference;
use Bitrix\Main\Error;
use Bitrix\Main\Engine\Controller;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)die();

class ImConferenceListController extends Controller
{
	protected function processBeforeAction(Action $action): bool
	{
		if (!Loader::includeModule('im'))
		{
			$this->addError(new Error("Module IM is not installed"));

			return false;
		}

		if (\Bitrix\Im\User::getInstance()->isExtranet())
		{
			$this->addError(new Error("You dont have access to this action"));

			return false;
		}

		return true;
	}

	public function deleteConferenceAction($conferenceId = null)
	{
		if (!Loader::includeModule('im'))
		{
			$this->addError(new Error(Loc::getMessage('IM_CONFERENCE_LIST_ERROR_IM_NOT_INSTALLED')));

			return null;
		}

		$conference = Conference::getById($conferenceId);
		if (!$conference)
		{
			$this->addError(new Error(Loc::getMessage('IM_CONFERENCE_LIST_ERROR_WRONG_ID')));

			return null;
		}

		if (!$conference->canUserDelete(CurrentUser::get()->getId()))
		{
			$this->addError(new Error(Loc::getMessage('IM_CONFERENCE_LIST_ERROR_CANT_DELETE')));

			return null;
		}

		$deletionResult = $conference->delete();
		if (!$deletionResult->isSuccess())
		{
			$this->addErrors($deletionResult->getErrors());

			return null;
		}

		$queryResult = ConferenceTable::getList(
			[
				'select' => ['COUNT'],
				'runtime' => [new ExpressionField('COUNT', 'COUNT(*)')]
			]
		)->fetchAll();
		$conferenceCount = (int)$queryResult[0]['COUNT'];

		return [
			'LAST_ROW' => $conferenceCount === 0
		];
	}

	public function getAllowedOperationsAction($conferenceId = null)
	{
		$currentUserId = CurrentUser::get()->getId();

		$conference = Conference::getById($conferenceId);
		if (!$conference)
		{
			$this->addError(new Error(Loc::getMessage('IM_CONFERENCE_LIST_ERROR_WRONG_ID')));

			return null;
		}

		return [
			'edit' => $conference->canUserEdit($currentUserId),
			'delete' => $conference->canUserDelete($currentUserId)
		];
	}
}
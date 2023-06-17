<?php

namespace Bitrix\Iblock\Controller\Property\Action;

use Bitrix\Main\Engine\Action;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use CApplicationException;
use CIBlockProperty;
use CIBlockRights;
use CMain;

/**
 * Delete property action.
 */
final class DeleteAction extends Action
{
	/**
	 * Handler.
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	public function run(int $id): bool
	{
		global $APPLICATION;

		/**
		 * @var CMain $APPLICATION
		 */

		$fields = CIBlockProperty::GetByID($id)->Fetch();
		if (empty($fields))
		{
			$this->errorCollection->setError(
				new Error(Loc::getMessage('IBLOCK_CONTROLLER_PROPERTY_ACTION_DELETE_ERROR_NOT_FOUND'))
			);

			return false;
		}

		$iblockId = (int)$fields['IBLOCK_ID'];
		if (!$this->checkWritePermissions($iblockId))
		{
			$this->errorCollection->setError(
				new Error(Loc::getMessage('IBLOCK_CONTROLLER_PROPERTY_ACTION_DELETE_ERROR_ACCESS_DENIED'))
			);

			return false;
		}

		$result = CIBlockProperty::Delete($id) !== false;
		if (!$result)
		{
			$ex = $APPLICATION->GetException();
			if ($ex instanceof CApplicationException)
			{
				$this->errorCollection->setError(
					new Error($ex->GetString())
				);
			}
		}

		return $result;
	}

	/**
	 * Check rights.
	 *
	 * @param int $iblockId
	 *
	 * @return bool
	 */
	private function checkWritePermissions(int $iblockId): bool
	{
		return CIBlockRights::UserHasRightTo($iblockId, $iblockId, 'iblock_edit');
	}
}

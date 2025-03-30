<?php

namespace Bitrix\Iblock\Grid\Row\Actions\Item;

use Bitrix\Iblock\Grid\Access\IblockRightsChecker;
use Bitrix\Main\AccessDeniedException;
use Bitrix\Main\Application;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\Error;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use CIBlockSection;
use CUtil;

final class DeleteSectionItem extends BaseItem
{
	private IblockRightsChecker $rights;

	public static function getId(): ?string
	{
		return 'delete_section';
	}

	public function __construct(IblockRightsChecker $rights)
	{
		$this->rights = $rights;
	}

	protected function getText(): string
	{
		return Loc::getMessage('IBLOCK_GRID_ROW_ACTIONS_DELETE_SECTION_NAME');
	}

	public function getControl(array $rawFields): ?array
	{
		$id = (int)($rawFields['ID'] ?? 0);
		if ($id <= 0)
		{
			return null;
		}

		$actionId = self::getId();
		$data = CUtil::PhpToJSObject([
			'id' => $id,
		]);
		$confirmMessage = \CUtil::JSEscape(
			Loc::getMessage('IBLOCK_GRID_ROW_ACTIONS_DELETE_SECTION_CONFIRM_MESSAGE')
		);

		$confirmButtonMessage = \CUtil::JSEscape(
			Loc::getMessage('IBLOCK_GRID_ROW_ACTIONS_DELETE_SECTION_CONFIRM_BUTTON')
		);
		$backButtonMessage = \CUtil::JSEscape(
			Loc::getMessage('IBLOCK_GRID_ROW_ACTIONS_DELETE_SECTION_BACK_BUTTON')
		);

		$this->onclick = "IblockGridInstance.sendSmallPopupWithConfirm('{$actionId}', {$data}, '{$confirmMessage}', '{$confirmButtonMessage}', '{$backButtonMessage}')";

		return parent::getControl($rawFields);
	}

	public function processRequest(HttpRequest $request): ?Result
	{
		global $APPLICATION;

		/**
		 * @var CMain $APPLICATION
		 */

		$id = $request->getPost('id');
		if (empty($id) || !is_numeric($id))
		{
			return null;
		}
		$id = (int)$id;

		if (!$this->rights->canDeleteSection($id))
		{
			throw new AccessDeniedException('Cannot delete element');
		}

		$result = new Result();

		$conn = Application::getConnection();
		$conn->startTransaction();
		try
		{
			$updateResult = CIBlockSection::Delete($id);
			if (!$updateResult)
			{
				$ex = $APPLICATION->GetException();
				$message =
					$ex
						? $ex->getString()
						: Loc::getMessage('IBLOCK_GRID_ROW_ACTION_DELETE_SECTION_INTERNAL_ERROR')
				;
				$result->addError(new Error($message));
				unset(
					$message,
					$ex,
				);
			}
		}
		catch (SqlQueryException)
		{
			$result->addError(new Error(Loc::getMessage('IBLOCK_GRID_ROW_ACTION_DELETE_SECTION_INTERNAL_ERROR')));
		}

		if ($result->isSuccess())
		{
			$conn->commitTransaction();
		}
		else
		{
			$conn->rollbackTransaction();
		}

		return $result;
	}
}

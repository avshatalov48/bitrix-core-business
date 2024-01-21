<?php

namespace Bitrix\Iblock\Grid\Row\Actions\Item;

use Bitrix\Main\AccessDeniedException;
use Bitrix\Main\Error;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use CIBlockElement;
use CUtil;

final class ClearCounterItem extends BaseItem
{
	public static function getId(): ?string
	{
		return 'clear_counter';
	}

	protected function getText(): string
	{
		return Loc::getMessage('IBLOCK_GRID_ROW_ACTIONS_CLEAR_COUNTER_NAME');
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

		$this->onclick = "IblockGridInstance.sendRowAction('{$actionId}', {$data})";

		return parent::getControl($rawFields);
	}

	public function processRequest(HttpRequest $request): ?Result
	{
		$id = $request->getPost('id');
		if (empty($id) || !is_numeric($id))
		{
			return null;
		}
		$id = (int)$id;

		if (!$this->getIblockRightsChecker()->canEditElement($id))
		{
			throw new AccessDeniedException('Cannot edit element');
		}

		$result = new Result();

		$entity = new CIBlockElement();
		$updateResult = $entity->Update($id, [
			'SHOW_COUNTER' => false,
			'SHOW_COUNTER_START' => false,
		]);
		if (!$updateResult)
		{
			$message = $entity->getLastError() ?: 'Cannot update element';
			$result->addError(new Error($message));
		}

		return $result;
	}
}

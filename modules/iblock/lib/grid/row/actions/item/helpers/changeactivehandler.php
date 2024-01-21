<?php

namespace Bitrix\Iblock\Grid\Row\Actions\Item\Helpers;

use Bitrix\Iblock\Grid\Access\IblockRightsChecker;
use Bitrix\Main\AccessDeniedException;
use Bitrix\Main\Error;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Result;
use CIBlockElement;

trait ChangeActiveHandler
{
	abstract protected function getSetActiveValue(): string;

	abstract protected function getIblockRightsChecker(): IblockRightsChecker;

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
			throw new AccessDeniedException('Cant edit element');
		}

		$result = new Result();

		$entity = new CIBlockElement();
		$updateResult = $entity->Update($id, [
			'ACTIVE' => $this->getSetActiveValue(),
		]);
		if (!$updateResult)
		{
			$message = $entity->getLastError() ?: 'Cant update element';
			$result->addError(new Error($message));
		}

		return $result;
	}
}

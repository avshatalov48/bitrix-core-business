<?php

namespace Bitrix\Catalog\Controller;

use Bitrix\Main\Error;

trait GetAction
{
	abstract protected function get($id);

	abstract protected function addErrorEntityNotExists(): void;

	abstract protected function getServiceItemName(): string;

	/**
	 * @param $id
	 * @return array|null
	 */
	public function getAction($id): ?array
	{
		$row = $this->get($id);
		if (!is_array($row))
		{
			$this->addErrorEntityNotExists();

			return null;
		}

		return [
			$this->getServiceItemName() => $row,
		];
	}
}

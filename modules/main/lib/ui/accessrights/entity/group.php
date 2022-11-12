<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage tasks
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Main\UI\AccessRights\Entity;


use Bitrix\Main\Access\AccessCode;
use Bitrix\Main\GroupTable;

class Group extends EntityBase
{
	private static $modelsCache = [];

	public function getType(): string
	{
		return AccessCode::TYPE_GROUP;
	}

	public function getName(): string
	{
		if ($this->model)
		{
			return $this->model->getName();
		}
		return '';
	}

	public function getUrl(): string
	{
		return '';
	}

	public function getAvatar(int $width = 58, int $height = 58): ?string
	{
		return '';
	}

	protected function loadModel()
	{
		if (!$this->model)
		{
			if (array_key_exists($this->id, self::$modelsCache))
			{
				$this->model = self::$modelsCache[$this->id];
			}
			else
			{
				$this->model = GroupTable::getList([
					'select' => [
						'ID',
						'NAME',
					],
					'filter' => [
						'=ID' => $this->id,
					],
					'limit' => 1,
				])->fetchObject();

				self::$modelsCache[$this->id] = $this->model;
			}
		}
	}
}

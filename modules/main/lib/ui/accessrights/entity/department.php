<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage tasks
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Main\UI\AccessRights\Entity;


use Bitrix\Main\Access\AccessCode;

class Department extends EntityBase
{

	public function getType(): string
	{
		return AccessCode::TYPE_DEPARTMENT;
	}

	public function getName(): string
	{
		if ($this->model)
		{
			return $this->model['NAME'];
		}
		return '';
	}

	public function getUrl(): string
	{
		return '/company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT='. $this->getId();
	}

	public function getAvatar(int $width = 58, int $height = 58): ?string
	{
		return '';
	}

	protected function loadModel()
	{
		if (!$this->model)
		{
			$structure = \CIntranetUtils::GetStructure();
			if (array_key_exists($this->getId(), $structure['DATA']))
			{
				$this->model = $structure['DATA'][$this->getId()];
			}
		}

		return $this->model;
	}
}
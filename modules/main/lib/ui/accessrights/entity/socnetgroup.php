<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage tasks
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Main\UI\AccessRights\Entity;


use Bitrix\Main\Access\AccessCode;
use Bitrix\Socialnetwork\WorkgroupTable;

class SocnetGroup extends EntityBase
{

	public function getType(): string
	{
		return AccessCode::TYPE_SOCNETGROUP;
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
		$groupUrlTemplate = \COption::GetOptionString("socialnetwork", "group_path_template", "/workgroups/group/#group_id#/", SITE_ID);
		return str_replace(array("#group_id#", "#GROUP_ID#"), $this->getId(), $groupUrlTemplate);
	}

	public function getAvatar(int $width = 58, int $height = 58): ?string
	{
		if ($this->model)
		{
			$arFile = \CFile::GetFileArray($this->model->getImageId());
			if(is_array($arFile))
			{
				return $arFile['SRC'];
			}
		}
		return '';
	}

	protected function loadModel()
	{
		if (!$this->model)
		{
			$this->model = WorkgroupTable::getById($this->id)->fetchObject();
		}
	}
}
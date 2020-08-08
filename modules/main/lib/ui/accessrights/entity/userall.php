<?php


namespace Bitrix\Main\UI\AccessRights\Entity;


use Bitrix\Main\Access\AccessCode;
use Bitrix\Main\Localization\Loc;

class UserAll extends EntityBase
{

	public function getType(): string
	{
		return AccessCode::TYPE_USER;
	}

	public function getName(): string
	{
		return Loc::getMessage('MAIN_UI_SELECTOR_ACCESSRIGHT_TOALL_INTRANET');
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
		return null;
	}
}
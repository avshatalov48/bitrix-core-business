<?php
namespace Bitrix\UI\Avatar\Mask\Owner;

use Bitrix\UI\Avatar;
use Bitrix\Main;

class System extends DefaultOwner
{
	public function getId()
	{
		return 'bitrix';
	}

	public function getDefaultAccess(): array
	{
		return [Main\Access\AccessCode::ACCESS_EMPLOYEE . '0', 'UA'];
	}
}
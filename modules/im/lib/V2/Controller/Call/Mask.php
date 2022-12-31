<?php

namespace Bitrix\Im\V2\Controller\Call;

use Bitrix\Main\Engine\Action;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Loader;
use Bitrix\UI\InfoHelper;

class Mask extends Controller
{
	/**
	 * @restMethod im.v2.call.mask.get
	 */
	public function getAction()
	{
		return [
			'masks' => \Bitrix\Im\Call\Mask::get(),
		];
	}
}
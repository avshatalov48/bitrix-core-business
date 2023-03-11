<?php

namespace Bitrix\Im\V2\Controller\Call;

use Bitrix\Main\Engine\Controller;

class Mask extends Controller
{
	/**
	 * @restMethod im.v2.Call.Mask.get
	 */
	public function getAction()
	{
		return [
			'masks' => \Bitrix\Im\Call\Mask::get(),
		];
	}
}
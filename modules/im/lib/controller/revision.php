<?php

namespace Bitrix\Im\Controller;

class Revision extends \Bitrix\Main\Engine\Controller
{
	/**
	 * @restMethod im.revision.get
	 *
	 * @return array
	 */
	public function getAction()
	{
		$result = \Bitrix\Im\Revision::get();

		$result = array_merge($result, [
			'im_revision_mobile' => \Bitrix\Im\Revision::getMobile(),
		]);

		return $result;
	}
}
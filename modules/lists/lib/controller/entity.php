<?php
namespace Bitrix\Lists\Controller;

use Bitrix\Lists\Service\Param;
use Bitrix\Main\Engine\Controller;

class Entity extends Controller
{
	protected function getParamFromRequest()
	{
		$request = $this->getRequest();
		$post = $request->getPostList()->toArray();

		return new Param($post);
	}
}
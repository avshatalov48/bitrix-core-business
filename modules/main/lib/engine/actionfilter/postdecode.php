<?php

namespace Bitrix\Main\Engine\ActionFilter;

use Bitrix\Main\Event;
use Bitrix\Main\Web\PostDecodeFilter;

final class PostDecode extends Base
{
	public function onBeforeAction(Event $event)
	{
		$httpRequest = $this->getAction()->getController()->getRequest();
		if ($httpRequest && $httpRequest->isPost())
		{
			\CUtil::jSPostUnescape();
			$httpRequest->addFilter(new PostDecodeFilter);
		}
	}
}
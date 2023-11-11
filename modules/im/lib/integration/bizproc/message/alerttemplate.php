<?php

namespace Bitrix\Im\Integration\Bizproc\Message;

class AlertTemplate extends PlainTemplate
{
	protected const DEFAULT_BORDER_COLOR = '#f4433e';

	public function buildMessage(array $messageFields): array
	{
		$attach = new \CIMMessageParamAttach(0, static::DEFAULT_BORDER_COLOR);

		$attach->SetDescription($this->buildDescriptionText());

		$attach->AddUser([
			'NAME' => $this->messageText,
			'AVATAR' => '/bitrix/js/im/images/robot/warning.svg',
		]);

		$attach->AddMessage($this->buildRobotText());

		$messageFields['ATTACH'] = $attach;

		return $messageFields;
	}
}

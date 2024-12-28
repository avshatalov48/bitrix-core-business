<?php

namespace Bitrix\Main\Grid\Row\Action\Control;

use Bitrix\Main\Grid\Row\Action\Action;
use Bitrix\Main\Web\Json;
use Stringable;

class SendRowActionOnclick implements Stringable
{
	public function __construct(
		private Action $action,
		private array $payload = [],
		private ?string $gridId = null,
	)
	{}

	public function __toString(): string
	{
		$id = htmlspecialcharsbx($this->action->getId());
		$payload = Json::encode($this->payload);

		if (empty($this->gridId))
		{
			return sprintf(
				'BX.Main.gridManager.data[0].instance.sendRowAction("%s", %s)',
				$id,
				$payload,
			);
		}

		return sprintf(
			'BX.Main.gridManager.getInstanceById("%s").sendRowAction("%s", %s)',
			htmlspecialcharsbx($this->gridId),
			$id,
			$payload,
		);
	}
}

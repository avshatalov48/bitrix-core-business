<?php

namespace Bitrix\Mail\Grid\MailboxSettingsGrid\Row\Action;

use Bitrix\Mail\Grid\MailboxSettingsGrid\Settings\MailboxSettings;
use Bitrix\Main\Grid\Row\Action\BaseAction;
use Bitrix\Main\Web\Json;

abstract class JsGridAction extends BaseAction
{
	private string $actionId;
	private string $extensionName;
	private string $gridId;

	public function __construct(MailboxSettings $settings)
	{
		$this->actionId = $this->getActionId();
		$this->extensionName = $settings->getExtensionName();
		$this->gridId = $settings->getID();
	}

	abstract protected function isEnabled(array $rawFields): bool;
	abstract public function getActionId(): string;
	abstract protected function getActionParams(array $rawFields): array;

	protected function getActionOptions(): array
	{
		return [];
	}

	public function getControl(array $rawFields): ?array
	{
		$extension = $this->extensionName;
		$gridId = $this->gridId;
		$actionOptions = $this->getActionOptions();
		$actionParams = $this->getActionParams($rawFields);
		$params = Json::encode([
			'actionId' => $this->actionId,
			'options' => $actionOptions,
			'params' => $actionParams,
		]);

		$this->onclick = sprintf("BX.%s.GridManager.getInstance('%s').runAction(%s)", $extension, $gridId, $params);

		$control = parent::getControl($rawFields);

		if (isset($control) && !$this->isEnabled($rawFields))
		{
			$disabledClass = 'menu-popup-item-disabled';
			$control['className'] =
				isset($control['className'])
					? $control['className'] . ' ' . $disabledClass
					: $disabledClass
			;

			unset($control['ONCLICK']);
		}

		return $control;
	}
}
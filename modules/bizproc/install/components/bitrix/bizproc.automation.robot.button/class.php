<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\UI\Buttons\Icon;
use Bitrix\UI\Buttons\Tag;

use Bitrix\UI\Counter\CounterStyle;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

use Bitrix\Bizproc\Internal\Service\LatestRobots\LatestRobotCounterService;

Main\UI\Extension::load(['ui.alerts', 'ui.buttons', 'main.core']);

class BizprocAutomationRobotButtonComponent extends \Bitrix\Bizproc\Automation\Component\Base
{
	protected function getDocumentType(): ?array
	{
		return is_array($this->arParams['DOCUMENT_TYPE'] ?? null) ? $this->arParams['DOCUMENT_TYPE'] : null;
	}

	protected function getUrl(): string
	{
		return $this->arParams['URL'] ?? '';
	}

	protected function getOnClick(): string
	{
		return $this->arParams['ON_CLICK'] ?? '';
	}

	protected function getButtonTag(): string
	{
		return $this->arParams['TAG'] ?? Tag::BUTTON;
	}

	protected function getButtonText(): string
	{
		return $this->arParams['TEXT'] ?? Loc::getMessage('BIZPROC_ROBOT_BUTTON_TITLE');
	}

	protected function getCounter(): int
	{
		return $this->arParams['COUNTER'] ?? 0;
	}

	protected function getCounterStyle(): CounterStyle
	{
		return $this->arParams['COUNTER_STYLE'] ?? CounterStyle::FILLED_SUCCESS;
	}

	protected function getIcon(): string
	{
		return $this->arParams['ICON'] ?? Icon::ROBOTS;
	}

	protected function getCustomClasses(): array
	{
		return $this->arParams['CUSTOM_CLASSES'] ?? [];
	}

	public function executeComponent(): void
	{
		if (!Main\Loader::includeModule('bizproc'))
		{
			ShowError(Loc::getMessage('BIZPROC_MODULE_NOT_INSTALLED'));
			return;
		}

		$latestRobotCounterService = new LatestRobotCounterService();
		$buttonData = $latestRobotCounterService->getButtonData(
			$this->getDocumentType(),
		);

		$this->arResult = [
			'BUTTON_ID' => $this->getEditAreaId('robot_button'),
			'TAG' => $this->getButtonTag(),
			'TEXT' => $this->getButtonText(),
			'COUNTER' => $buttonData->getCounter(),
			'URL' => $this->getUrl(),
			'ON_CLICK' => $this->getOnClick(),
			'ICON' => $this->getIcon(),
			'COUNTER_STYLE' => $this->getCounterStyle(),
			'CUSTOM_CLASSES' => $this->getCustomClasses(),
			'AVAILABLE_NEW_ROBOTS' => $buttonData->getAvailableNewRobotIds(),
		];

		$this->includeComponentTemplate();
	}
}
<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\UI\Toolbar\Facade\Toolbar;

class ReportVisualConstructorBoardHeader extends CBitrixComponent
{
	public function executeComponent()
	{
		$this->arResult['BOARD_ID'] = $this->arParams['BOARD_ID'];
		$this->arResult['REPORTS_CATEGORIES'] = $this->arParams['REPORTS_CATEGORIES'];
		$this->arResult['FILTER'] = $this->arParams['FILTER'];
		$this->arResult['WITH_ADD_BUTTON'] = !$this->arParams['DEFAULT_BOARD'];
		$this->arResult['IS_FRAME_MODE'] = $this->isFrameMode();
		$this->arResult['BOARD_BUTTONS'] = $this->arParams['BOARD_BUTTONS'];

		$this->configureToolbar();

		$this->includeComponentTemplate();
	}

	private function isFrameMode()
	{
		$isFrame = \Bitrix\Main\Application::getInstance()->getContext()->getRequest()->get('IFRAME');
		return $isFrame === "Y";
	}

	private function configureToolbar(): void
	{
		if (!\Bitrix\Main\Loader::includeModule('ui'))
		{
			return;
		}

		if ($this->isFrameMode())
		{
			/*
			// Very likely that this code is dead. This component is rendered only in two situations:
			// 1. On a separate page, where bitrix:report.visualconstructor.board.base is included as a top-level component
			// 2. On an SPA-like page, where bitrix:report.analytics.base is a top-level component
			// In the second case, output buffer for header and toolbar is never rendered, buttons rendered differently.
			// And in the first case, that page is never opened in a slider.
			// I leave it here just in case I've missed something. Feel free to remove after some time if there were
			// no issues found.

			ob_start();
			foreach ($this->arResult['BOARD_BUTTONS'] as $button)
			{
				if ($button instanceof \Bitrix\Report\VisualConstructor\BoardButton)
				{
					$button->flush();
				}
			}

			Toolbar::addRightCustomHtml(ob_get_clean());
			*/
		}
		else
		{
			$filter = $this->arResult['FILTER'] ?? null;
			if ($filter instanceof \Bitrix\Report\VisualConstructor\Helper\Filter)
			{
				Toolbar::addFilter($filter->getFilterParameters());
			}

			if ($this->arResult['WITH_ADD_BUTTON'])
			{
				ob_start();

				global $APPLICATION;
				$APPLICATION->IncludeComponent(
					'bitrix:report.visualconstructor.board.controls',
					'',
					[
						'BOARD_ID' => $this->arResult['BOARD_ID'],
						'REPORTS_CATEGORIES' => $this->arResult['REPORTS_CATEGORIES']
					],
					$this,
					[]
				);

				Toolbar::addRightCustomHtml(ob_get_clean());
			}
		}
	}
}

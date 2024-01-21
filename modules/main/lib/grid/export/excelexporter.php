<?php

namespace Bitrix\Main\Grid\Export;

use Bitrix\Main\Context;
use Bitrix\Main\Grid\Component\ComponentParams;
use Bitrix\Main\Grid\Grid;
use Bitrix\Main\Grid\Settings;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Uri;
use Bitrix\UI\Buttons\BaseButton;

final class ExcelExporter
{
	public const REQUEST_PARAM_NAME = 'mode';
	public const REQUEST_PARAM_VALUE = 'excel';

	public function isExportRequest(?HttpRequest $request = null): bool
	{
		$request ??= Context::getCurrent()->getRequest();

		return $request->get(self::REQUEST_PARAM_NAME) === self::REQUEST_PARAM_VALUE;
	}

	public function getControl(?HttpRequest $request = null): BaseButton
	{
		/**
		 * @var HttpRequest $request
		 */
		$request ??= Context::getCurrent()->getRequest();

		$uri = new Uri($request->getRequestUri());
		$uri->addParams([
			self::REQUEST_PARAM_NAME => self::REQUEST_PARAM_VALUE,
			// for disable composite
            'ncc' => 1,
		]);

		$button = new BaseButton();
		$button->setLink((string)$uri);
		$button->setText(Loc::getMessage('MAIN_GRID_EXPORT_EXCEL_BUTTON_TEXT'));

		return $button;
	}

	public function process(Grid $grid, string $fileName = 'export'): void
	{
		global $APPLICATION;

		/**
		 * @var \CMain $APPLICATION
		 */

		$APPLICATION->RestartBuffer();

		$grid->getSettings()->setMode(Settings::MODE_EXCEL);

		try
		{
			ob_start();
			$APPLICATION->IncludeComponent(
				'bitrix:main.ui.grid',
				'excel',
				ComponentParams::get($grid)
			);
			$content = ob_get_contents();
		}
		finally
		{
			ob_end_clean();
		}

		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: filename="' . $fileName . '.xls"');

		echo $content;

		die();
	}
}

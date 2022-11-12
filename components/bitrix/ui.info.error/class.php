<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage UI
 * @copyright 2001-2022 Bitrix
 */

use Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use Bitrix\UI\Toolbar\Facade\Toolbar;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

class InfoError extends CBitrixComponent
{
	public function onPrepareComponentParams($params)
	{
		$params['IS_HTML'] = $params['IS_HTML'] ?? 'N';

		return parent::onPrepareComponentParams($params);
	}

	public function executeComponent()
	{
		Toolbar::deleteFavoriteStar();

		$this->fillResult();

		$this->includeComponentTemplate();
	}

	private function fillResult()
	{
		$title = $this->arParams['~TITLE'] ?? Loc::getMessage('UI_INFO_ERROR_COMPONENT_TITLE_DEFAULT');
		$this->arResult = [
			'TITLE' => $this->arParams['IS_HTML'] !== 'Y' ? htmlspecialcharsbx($title) : $title,
			'DESCRIPTION' => $this->getDescription(),
		];
	}

	private function getDescription(): string
	{
		if (isset($this->arParams['~DESCRIPTION']))
		{
			return
				$this->arParams['IS_HTML'] !== 'Y'
					? htmlspecialcharsbx($this->arParams['~DESCRIPTION'])
					: $this->arParams['~DESCRIPTION']
			;
		}

		$isB24 = Loader::includeModule('bitrix24');
		$moreLink = false;
		$moreTitle = Loc::getMessage('UI_INFO_ERROR_COMPONENT_HELPER_LINK_TITLE');
		$helperCode = (int)($this->arParams['HELPER_CODE'] ?? 0);
		$courseId = (int)($this->arParams['COURSE_ID'] ?? 0);
		$lessonId = (int)($this->arParams['LESSON_ID'] ?? 0);
		if ($helperCode > 0 && $isB24)
		{
			$moreLink = "<a onclick=\"top.BX.Helper.show('redirect=detail&code={$helperCode}')\" style=\"cursor: pointer; \">{$moreTitle}</a>";
		}
		elseif ($courseId > 0 && $lessonId > 0)
		{
			$instructionLink = 'https://dev.1c-bitrix.ru/learning/course/index.php';
			if (!in_array($this->getLanguageId(), ["ru", "ua"]))
			{
				$instructionLink = "https://training.bitrix24.com/support/training/course/index.php";
			}

			$moreLink = "<a href=\"{$instructionLink}?COURSE_ID={$courseId}&LESSON_ID={$lessonId}\" target=\"_blank\">{$moreTitle}</a>";
		}

		if ($isB24)
		{
			return
				!$moreLink
					? Loc::getMessage('UI_INFO_ERROR_COMPONENT_DESCRIPTION_B24_DEFAULT')
					: Loc::getMessage(
						'UI_INFO_ERROR_COMPONENT_DESCRIPTION_B24_DEFAULT_WITH_HELPER_LINK',
						['#HELPER_LINK#' => $moreLink]
					)
			;
		}

		return
			!$moreLink
				? Loc::getMessage('UI_INFO_ERROR_COMPONENT_DESCRIPTION_DEFAULT')
				: Loc::getMessage(
				'UI_INFO_ERROR_COMPONENT_DESCRIPTION_DEFAULT_WITH_HELPER_LINK',
				['#HELPER_LINK#' => $moreLink]
			)
		;
	}
}

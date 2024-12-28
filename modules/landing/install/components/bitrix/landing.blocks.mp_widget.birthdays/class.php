<?php

use Bitrix\Landing\Mainpage;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

CBitrixComponent::includeComponentClass('bitrix:landing.blocks.mp_widget.base');

class LandingBlocksMainpageWidgetBirthdays extends LandingBlocksMainpageWidgetBase
{
	private const USER_AMOUNT = 6;

	private const WIDGET_CSS_VAR_PROPERTIES = [
		'COLOR_BG' => '--widget-color-bg',
		'COLOR_BG_V2' => '--widget-color-bg-v2',
		'COLOR_USER_BORDER' => '--widget-color-user-border',
		'COLOR_NAME' => '--widget-color-name',
		'COLOR_NAME_V2' => '--widget-color-name-v2',
		'COLOR_WORK_POSITION' => '--widget-color-work-position',
		'COLOR_WORK_POSITION_V2' => '--widget-color-work-position-v2',
		'COLOR_DATE' => '--widget-color-date',
		'COLOR_DATE_V2' => '--widget-color-date-v2',
	];

	/**
	 * Base executable method.
	 * @return void
	 */
	public function executeComponent(): void
	{
		$this->checkParam('TITLE', Loc::getMessage('LANDING_WIDGET_BIRTHDAYS_CLASS__TITLE_DEFAULT_VALUE'));
		$this->checkParam('COLOR_BG', 'hsla(40, 100%, 50%, 0.15)');
		$this->checkParam('COLOR_BG_V2', 'var(--primary-opacity-0_5)');
		$this->checkParam('COLOR_HEADERS', '#E89B06');
		$this->checkParam('COLOR_USER_BORDER', '#ffa900');
		$this->checkParam('COLOR_NAME', '#525c69');
		$this->checkParam('COLOR_NAME_V2', '#ffffff');
		$this->checkParam('COLOR_WORK_POSITION', '#959ca4');
		$this->checkParam('COLOR_WORK_POSITION_V2', 'hsla(0, 0%, 100%, 0.65)');
		$this->checkParam('COLOR_DATE', 'var(--primary)');
		$this->checkParam('COLOR_BG_BORDER', 'var(--primary)');
		$this->checkParam('COLOR_DATE_V2', '#f7a70b');

		foreach (self::WIDGET_CSS_VAR_PROPERTIES as $property => $cssVar)
		{
			$this->addCssVarProperty($property, $cssVar);
		}

		$this->getData();

		parent::executeComponent();
	}

	protected function getData(): void
	{
		$this->arResult['SHOW_EMPTY_STATE'] = false;
		if (Mainpage\Manager::isUseDemoData())
		{
			$usersData = $this->getDemoData();
		}
		else
		{
			$usersData = $this->getRealData();
			if (count($usersData) === 0)
			{
				$this->arResult['SHOW_EMPTY_STATE'] = true;
			}
		}

		$this->arResult['USERS'] = $usersData;
		$this->arResult['TITLE'] = $this->arParams['TITLE'];
	}

	protected function getRealData(): array
	{
		if (!Loader::includeModule('intranet'))
		{
			// todo: show error
			return [];
		}

		$users = [];
		$component = new CBitrixComponent();
		if ($component->InitComponent('bitrix:intranet.structure.birthday.nearest'))
		{
			$params = [
				'NUM_USERS' => self::USER_AMOUNT,
			];
			ob_start();
			$result = $component->IncludeComponent('not_exist_template', $params, null, true);
			ob_get_clean();
			$users = $result['USERS'];
		}

		foreach ($users as $code => $user)
		{
			$users[$code]['PERSONAL_PHOTO'] = [
				'FILE_ID' => $user['PERSONAL_PHOTO'],
				'IMG' => CFile::ResizeImageGet(
					$user['PERSONAL_PHOTO'],
					["width" => 175, "height" => 175],
					BX_RESIZE_IMAGE_EXACT,
					true
				),
			];
			$users[$code]['PERSONAL_PHOTO_PATH'] = $users[$code]['PERSONAL_PHOTO']['IMG']['src'];
			if ($user['PERSONAL_BIRTHDAY'][4] === '-' || $user['PERSONAL_BIRTHDAY'][4] === '/')
			{
				$users[$code]['PERSONAL_BIRTHDAY'] = substr($user['PERSONAL_BIRTHDAY'], 5 , 5);
			}
			else
			{
				$users[$code]['PERSONAL_BIRTHDAY'] = substr($user['PERSONAL_BIRTHDAY'], 0 , 5);
			}
		}

		return $users;
	}

	protected function getDemoData(): array
	{
		return [
			[
				'NAME' => Loc::getMessage('LANDING_WIDGET_BIRTHDAYS_CLASS_DEMO_DATA_NAME_1'),
				'PERSONAL_BIRTHDAY' => $this->convertDateFormat('10.09.2000', 'dm', 'd.m.Y'),
				'PERSONAL_PHOTO_PATH' => 'https://cdn.bitrix24.site/bitrix/images/landing/widget/birthdays/1.png',
				'WORK_POSITION' => Loc::getMessage('LANDING_WIDGET_BIRTHDAYS_CLASS_DEMO_DATA_WORK_POSITION_1'),
			],
			[
				'NAME' => Loc::getMessage('LANDING_WIDGET_BIRTHDAYS_CLASS_DEMO_DATA_NAME_2'),
				'PERSONAL_BIRTHDAY' => $this->convertDateFormat('10.09.2000', 'dm', 'd.m.Y'),
				'PERSONAL_PHOTO_PATH' => 'https://cdn.bitrix24.site/bitrix/images/landing/widget/birthdays/2.png',
				'WORK_POSITION' => Loc::getMessage('LANDING_WIDGET_BIRTHDAYS_CLASS_DEMO_DATA_WORK_POSITION_2'),
			],
			[
				'NAME' => Loc::getMessage('LANDING_WIDGET_BIRTHDAYS_CLASS_DEMO_DATA_NAME_3'),
				'PERSONAL_BIRTHDAY' => $this->convertDateFormat('12.09.2000', 'dm', 'd.m.Y'),
				'PERSONAL_PHOTO_PATH' => 'https://cdn.bitrix24.site/bitrix/images/landing/widget/birthdays/3.png',
				'WORK_POSITION' => Loc::getMessage('LANDING_WIDGET_BIRTHDAYS_CLASS_DEMO_DATA_WORK_POSITION_3'),
			],
			[
				'NAME' => Loc::getMessage('LANDING_WIDGET_BIRTHDAYS_CLASS_DEMO_DATA_NAME_4'),
				'PERSONAL_BIRTHDAY' => $this->convertDateFormat('12.09.2000', 'dm', 'd.m.Y'),
				'PERSONAL_PHOTO_PATH' => 'https://cdn.bitrix24.site/bitrix/images/landing/widget/birthdays/4.png',
				'WORK_POSITION' => Loc::getMessage('LANDING_WIDGET_BIRTHDAYS_CLASS_DEMO_DATA_WORK_POSITION_4'),
			],
			[
				'NAME' => Loc::getMessage('LANDING_WIDGET_BIRTHDAYS_CLASS_DEMO_DATA_NAME_5'),
				'PERSONAL_BIRTHDAY' => $this->convertDateFormat('14.09.2000', 'dm', 'd.m.Y'),
				'PERSONAL_PHOTO_PATH' => 'https://cdn.bitrix24.site/bitrix/images/landing/widget/birthdays/5.png',
				'WORK_POSITION' => Loc::getMessage('LANDING_WIDGET_BIRTHDAYS_CLASS_DEMO_DATA_WORK_POSITION_5'),
			],
		];
	}
}
<?php

use Bitrix\Landing\Mainpage;
use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

\CBitrixComponent::includeComponentClass('bitrix:landing.blocks.mp_widget.base');

class LandingBlocksMainpageWidgetNewEmployees extends LandingBlocksMainpageWidgetBase
{
	private const USER_AMOUNT = 6;
	private const WIDGET_CSS_VAR_PROPERTIES = [
		'COLOR_HEADERS_V2' => '--widget-color-h-v2',
	];

	/**
	 * Base executable method.
	 * @return void
	 */
	public function executeComponent(): void
	{
		$this->checkParam('TITLE', Loc::getMessage('LANDING_WIDGET_NEW_EMPLOYEES_CLASS__TITLE_DEFAULT_VALUE'));
		$this->checkParam('COLOR_HEADERS_V2', 'var(--primary)');

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

	protected function getDemoData(): array
	{
		return [
			[
				'ID' => '1',
				'NAME' => Loc::getMessage('LANDING_WIDGET_NEW_EMPLOYEES_CLASS_NAME_1'),
				'LAST_NAME' => '',
				'SECOND_NAME' => '',
				'DATE_REGISTER' => $this->convertDateFormat('15.07.2018 15:00:00', 'dmy'),
				'PERSONAL_PHOTO_PATH' => 'https://cdn.bitrix24.site/bitrix/images/landing/widget/new_employees/1.png',
				'WORK_POSITION' => Loc::getMessage('LANDING_WIDGET_NEW_EMPLOYEES_CLASS_WORK_POSITION_1'),
			],
			[
				'ID' => '2',
				'NAME' => Loc::getMessage('LANDING_WIDGET_NEW_EMPLOYEES_CLASS_NAME_2'),
				'LAST_NAME' => '',
				'SECOND_NAME' => '',
				'DATE_REGISTER' => $this->convertDateFormat('22.03.2020 15:00:00', 'dmy'),
				'PERSONAL_PHOTO_PATH' => 'https://cdn.bitrix24.site/bitrix/images/landing/widget/new_employees/2.png',
				'WORK_POSITION' => Loc::getMessage('LANDING_WIDGET_NEW_EMPLOYEES_CLASS_WORK_POSITION_2'),
			],
			[
				'ID' => '3',
				'NAME' => Loc::getMessage('LANDING_WIDGET_NEW_EMPLOYEES_CLASS_NAME_3'),
				'LAST_NAME' => '',
				'SECOND_NAME' => '',
				'DATE_REGISTER' => $this->convertDateFormat('05.11.2019 15:00:00', 'dmy'),
				'PERSONAL_PHOTO_PATH' => 'https://cdn.bitrix24.site/bitrix/images/landing/widget/new_employees/3.png',
				'WORK_POSITION' => Loc::getMessage('LANDING_WIDGET_NEW_EMPLOYEES_CLASS_WORK_POSITION_3'),
			],
			[
				'ID' => '4',
				'NAME' => Loc::getMessage('LANDING_WIDGET_NEW_EMPLOYEES_CLASS_NAME_4'),
				'LAST_NAME' => '',
				'SECOND_NAME' => '',
				'DATE_REGISTER' => $this->convertDateFormat('18.01.2021 15:00:00', 'dmy'),
				'PERSONAL_PHOTO_PATH' => 'https://cdn.bitrix24.site/bitrix/images/landing/widget/new_employees/4.png',
				'WORK_POSITION' => Loc::getMessage('LANDING_WIDGET_NEW_EMPLOYEES_CLASS_WORK_POSITION_4'),
			],
			[
				'ID' => '5',
				'NAME' => Loc::getMessage('LANDING_WIDGET_NEW_EMPLOYEES_CLASS_NAME_5'),
				'LAST_NAME' => '',
				'SECOND_NAME' => '',
				'DATE_REGISTER' => $this->convertDateFormat('29.08.2017 15:00:00', 'dmy'),
				'PERSONAL_PHOTO_PATH' => 'https://cdn.bitrix24.site/bitrix/images/landing/widget/new_employees/5.png',
				'WORK_POSITION' => Loc::getMessage('LANDING_WIDGET_NEW_EMPLOYEES_CLASS_WORK_POSITION_5'),
			],
			[
				'ID' => '6',
				'NAME' => Loc::getMessage('LANDING_WIDGET_NEW_EMPLOYEES_CLASS_NAME_6'),
				'LAST_NAME' => '',
				'SECOND_NAME' => '',
				'DATE_REGISTER' => $this->convertDateFormat('12.06.2022 15:00:00', 'dmy'),
				'PERSONAL_PHOTO_PATH' => 'https://cdn.bitrix24.site/bitrix/images/landing/widget/new_employees/6.png',
				'WORK_POSITION' => Loc::getMessage('LANDING_WIDGET_NEW_EMPLOYEES_CLASS_WORK_POSITION_6'),
			],
		];
	}

	protected function getRealData(): array
	{
		$arRequiredFields = [
			'ID',
			'NAME',
			'LAST_NAME',
			'SECOND_NAME',
			'DATE_REGISTER',
			'PERSONAL_PHOTO',
			'WORK_POSITION',
		];
		$arNavParams = [
			'nTopCount' => self::USER_AMOUNT,
		];
		$arFilter = [
			'ACTIVE' => 'Y',
			'!EXTERNAL_AUTH_ID' => \Bitrix\Main\UserTable::getExternalUserTypes(),
			'!UF_DEPARTMENT' => false,
		];
		$dbUsers = CUser::getList(
			'DATE_REGISTER',
			'DESC',
			$arFilter,
			[
				'SELECT' => $arRequiredFields,
				'NAV_PARAMS' => $arNavParams,
				'FIELDS' => $arRequiredFields,
			]
		);

		$users = [];
		$count = 0;
		while ($arUser = $dbUsers->GetNext())
		{
			$users[$count]['ID'] = $arUser['ID'];
			$users[$count]['NAME'] = $arUser['NAME'];
			$users[$count]['LAST_NAME'] = $arUser['LAST_NAME'];
			$users[$count]['SECOND_NAME'] = $arUser['SECOND_NAME'];
			$users[$count]['DATE_REGISTER'] = substr($arUser['DATE_REGISTER'], 0, 10);
			$users[$count]['PERSONAL_PHOTO'] = $arUser['PERSONAL_PHOTO'];
			$resizeFile = \CFile::ResizeImageGet(
				$arUser['PERSONAL_PHOTO'],
				["width" => 52, "height" => 52],
				BX_RESIZE_IMAGE_EXACT
			);
			$users[$count]['PERSONAL_PHOTO_PATH'] = $resizeFile['src'];
			$users[$count]['WORK_POSITION'] = $arUser['WORK_POSITION'];

			$count++;
		}

		return $users;
	}
}
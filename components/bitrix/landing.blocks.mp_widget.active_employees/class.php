<?php

use Bitrix\Intranet\UStat\UStat;
use Bitrix\Landing\Mainpage;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

CBitrixComponent::includeComponentClass('bitrix:landing.blocks.mp_widget.base');

class LandingBlocksMainpageWidgetActiveEmployees extends LandingBlocksMainpageWidgetBase
{
	private const WIDGET_CSS_VAR_PROPERTIES = [
		'COLOR_SUBTITLE' => '--widget-color-subtitle',
		'COLOR_SUBTITLE_V2' => '--widget-color-subtitle-v2',
		'COLOR_DIAGRAM_MAIN' => '--widget-color-diagram-main',
		'COLOR_DIAGRAMS' => '--widget-color-diagrams',
		'COLOR_BORDER_LINE' => '--widget-color-border-line',
		'COLOR_DIAGRAM_TITLE' => '--widget-color-diagram-title',
		'COLOR_DIAGRAM_TITLE_V2' => '--widget-color-diagram-title-v2',
		'COLOR_DIAGRAM_TEXT' => '--widget-color-diagram-text',
	];

	private const USER_COUNT_LIMIT = 6;

	/**
	 * Base executable method.
	 * @return void
	 */
	public function executeComponent(): void
	{
		$this->initializeParams();
		$this->getData();
		parent::executeComponent();
	}

	protected function initializeParams(): void
	{
		$this->checkParam('TITLE', Loc::getMessage('LANDING_WIDGET_ACTIVE_EMPLOYEES_CLASS_TITLE_DEFAULT_VALUE'));
		$this->checkParam('COLOR_SUBTITLE', '#000000');
		$this->checkParam('COLOR_SUBTITLE_V2', '#ffffff');
		$this->checkParam('COLOR_TEXT', '#adadad');
		$this->checkParam('COLOR_DIAGRAM_MAIN', '#55d0e0');
		$this->checkParam('COLOR_BORDER_LINE', 'hsla(212, 8%, 61%, 0.15)');
		$this->checkParam('COLOR_DIAGRAM_TITLE', '#333333');
		$this->checkParam('COLOR_DIAGRAM_TITLE_V2', '#ffffff');
		$this->checkParam('COLOR_DIAGRAM_TEXT', '#58616e');
		$this->checkParam('COLOR_HEADERS_V2', '#ffffff');

		foreach (self::WIDGET_CSS_VAR_PROPERTIES as $property => $cssVar)
		{
			$this->addCssVarProperty($property, $cssVar);
		}
	}

	protected function getData(): void
	{
		$this->arResult['SHOW_EMPTY_STATE'] = false;

		if (Mainpage\Manager::isUseDemoData())
		{
			$demoData = $this->getDemoData();
			$usersData = $demoData['USERS'];
			$generalActivity = $demoData['GENERAL_ACTIVITY'];
		}
		else
		{
			if (Loader::includeModule('intranet'))
			{
				$realData = $this->getRealData();
			}
			$usersData = $realData['USERS'] ?? [];
			if (count($usersData) === 0)
			{
				$this->arResult['SHOW_EMPTY_STATE'] = true;
			}
			if (isset($realData['GENERAL_ACTIVITY']) && $realData['GENERAL_ACTIVITY'] !== '')
			{
				$generalActivity = $realData['GENERAL_ACTIVITY'];
			}
			else
			{
				$generalActivity = 0;
			}
		}

		$this->arResult['USERS'] = $usersData;
		$this->arResult['GENERAL_ACTIVITY'] = $generalActivity;
		$this->arResult['TITLE'] = $this->arParams['TITLE'];
	}

	protected function getDemoData(): array
	{
		return [
			'USERS' => [
				[
					'NAME' => Loc::getMessage('LANDING_WIDGET_ACTIVE_EMPLOYEES_CLASS_DEMO_DATA_NAME_1'),
					'PERSONAL_PHOTO_PATH' => 'https://cdn.bitrix24.site/bitrix/images/landing/widget/active_employees/1.png',
					'WORK_POSITION' => Loc::getMessage(
						'LANDING_WIDGET_ACTIVE_EMPLOYEES_CLASS_DEMO_DATA_WORK_POSITION_1'
					),
					'ACTIVITY' => 254,
				],
				[
					'NAME' => Loc::getMessage('LANDING_WIDGET_ACTIVE_EMPLOYEES_CLASS_DEMO_DATA_NAME_2'),
					'PERSONAL_PHOTO_PATH' => 'https://cdn.bitrix24.site/bitrix/images/landing/widget/active_employees/2.png',
					'WORK_POSITION' => Loc::getMessage(
						'LANDING_WIDGET_ACTIVE_EMPLOYEES_CLASS_DEMO_DATA_WORK_POSITION_2'
					),
					'ACTIVITY' => 212,
				],
				[
					'NAME' => Loc::getMessage('LANDING_WIDGET_ACTIVE_EMPLOYEES_CLASS_DEMO_DATA_NAME_3'),
					'PERSONAL_PHOTO_PATH' => 'https://cdn.bitrix24.site/bitrix/images/landing/widget/active_employees/3.png',
					'WORK_POSITION' => Loc::getMessage(
						'LANDING_WIDGET_ACTIVE_EMPLOYEES_CLASS_DEMO_DATA_WORK_POSITION_2'
					),
					'ACTIVITY' => 190,
				],
				[
					'NAME' => Loc::getMessage('LANDING_WIDGET_ACTIVE_EMPLOYEES_CLASS_DEMO_DATA_NAME_4'),
					'PERSONAL_PHOTO_PATH' => 'https://cdn.bitrix24.site/bitrix/images/landing/widget/active_employees/4.png',
					'WORK_POSITION' => Loc::getMessage(
						'LANDING_WIDGET_ACTIVE_EMPLOYEES_CLASS_DEMO_DATA_WORK_POSITION_1'
					),
					'ACTIVITY' => 120,
				],
				[
					'NAME' => Loc::getMessage('LANDING_WIDGET_ACTIVE_EMPLOYEES_CLASS_DEMO_DATA_NAME_5'),
					'PERSONAL_PHOTO_PATH' => 'https://cdn.bitrix24.site/bitrix/images/landing/widget/active_employees/5.png',
					'WORK_POSITION' => Loc::getMessage(
						'LANDING_WIDGET_ACTIVE_EMPLOYEES_CLASS_DEMO_DATA_WORK_POSITION_3'
					),
					'ACTIVITY' => 98,
				],
				[
					'NAME' => Loc::getMessage('LANDING_WIDGET_ACTIVE_EMPLOYEES_CLASS_DEMO_DATA_NAME_6'),
					'PERSONAL_PHOTO_PATH' => 'https://cdn.bitrix24.site/bitrix/images/landing/widget/active_employees/6.png',
					'WORK_POSITION' => Loc::getMessage(
						'LANDING_WIDGET_ACTIVE_EMPLOYEES_CLASS_DEMO_DATA_WORK_POSITION_4'
					),
					'ACTIVITY' => 63,
				],
			],
			'GENERAL_ACTIVITY' => 87,
		];
	}

	protected function getRealData(): array
	{
		$period = $this->arParams['PERIOD'] ?? 'month';
		if ($period === 'day')
		{
			$period = '';
		}
		$dataApi = UStat::getUserRatingApi($period, self::USER_COUNT_LIMIT);
		$users = $dataApi['users'];
		$generalActivity = $dataApi['involvement'] ?? 0;
		$prepareUsers = [];
		foreach ($users as $user)
		{
			$prepareUser = [];
			$prepareUser['ID'] = $user['user_id'];
			$prepareUser['NAME'] = $user['name'] . ' ' . $user['last_name'];
			$prepareUser['WORK_POSITION'] = $user['work_position'];
			$prepareUser['PERSONAL_PHOTO_PATH'] = $user['avatar'];
			$prepareUser['ACTIVITY'] = $user['activity'];
			$prepareUsers[] = $prepareUser;
		}

		return [
			'USERS' => $prepareUsers,
			'GENERAL_ACTIVITY' => $generalActivity,
		];
	}
}

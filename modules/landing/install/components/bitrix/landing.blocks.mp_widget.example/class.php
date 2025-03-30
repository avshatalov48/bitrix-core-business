<?php

use Bitrix\Landing\Mainpage;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

CBitrixComponent::includeComponentClass('bitrix:landing.blocks.mp_widget.base');

class LandingBlocksMainpageWidgetExample extends LandingBlocksMainpageWidgetBase
{
	/**
	 * Base executable method.
	 * @return void
	 */
	public function executeComponent(): void
	{
		$this->checkParam('TITLE', Loc::getMessage('LANDING_WIDGET_EXAMPLE_CLASS__TITLE_DEFAULT_VALUE'));
		$this->checkParam('USER_AMOUNT', 3);

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
				'NUM_USERS' => (int)$this->arParams['USER_AMOUNT'],
			];
			ob_start();
			$result = $component->IncludeComponent('not_exist_template', $params, null, true);
			ob_get_clean();
			$users = $result['USERS'];
		}

		foreach ($users as $code => $user)
		{
			if ($user['PERSONAL_BIRTHDAY'][4] === '-' || $user['PERSONAL_BIRTHDAY'][4] === '/')
			{
				$users[$code]['PERSONAL_BIRTHDAY'] = substr($user['PERSONAL_BIRTHDAY'], 5, 5);
			}
			else
			{
				$users[$code]['PERSONAL_BIRTHDAY'] = substr($user['PERSONAL_BIRTHDAY'], 0, 5);
			}
		}

		return $users;
	}

	protected function getDemoData(): array
	{
		return [
			[
				'NAME' => Loc::getMessage('LANDING_WIDGET_EXAMPLE_CLASS_DEMO_DATA_NAME_1'),
				'PERSONAL_BIRTHDAY' => $this->convertDateFormat('10.09.2000', 'dm', 'd.m.Y'),
				'PERSONAL_PHOTO_PATH' => 'https://cdn.bitrix24.site/bitrix/images/landing/widget/birthdays/1.png',
				'WORK_POSITION' => Loc::getMessage('LANDING_WIDGET_EXAMPLE_CLASS_DEMO_DATA_WORK_POSITION_1'),
			],
			[
				'NAME' => Loc::getMessage('LANDING_WIDGET_EXAMPLE_CLASS_DEMO_DATA_NAME_2'),
				'PERSONAL_BIRTHDAY' => $this->convertDateFormat('10.09.2000', 'dm', 'd.m.Y'),
				'PERSONAL_PHOTO_PATH' => 'https://cdn.bitrix24.site/bitrix/images/landing/widget/birthdays/2.png',
				'WORK_POSITION' => Loc::getMessage('LANDING_WIDGET_EXAMPLE_CLASS_DEMO_DATA_WORK_POSITION_2'),
			],
			[
				'NAME' => Loc::getMessage('LANDING_WIDGET_EXAMPLE_CLASS_DEMO_DATA_NAME_3'),
				'PERSONAL_BIRTHDAY' => $this->convertDateFormat('12.09.2000', 'dm', 'd.m.Y'),
				'PERSONAL_PHOTO_PATH' => 'https://cdn.bitrix24.site/bitrix/images/landing/widget/birthdays/3.png',
				'WORK_POSITION' => Loc::getMessage('LANDING_WIDGET_EXAMPLE_CLASS_DEMO_DATA_WORK_POSITION_3'),
			],
			[
				'NAME' => Loc::getMessage('LANDING_WIDGET_EXAMPLE_CLASS_DEMO_DATA_NAME_4'),
				'PERSONAL_BIRTHDAY' => $this->convertDateFormat('12.09.2000', 'dm', 'd.m.Y'),
				'PERSONAL_PHOTO_PATH' => 'https://cdn.bitrix24.site/bitrix/images/landing/widget/birthdays/4.png',
				'WORK_POSITION' => Loc::getMessage('LANDING_WIDGET_EXAMPLE_CLASS_DEMO_DATA_WORK_POSITION_4'),
			],
			[
				'NAME' => Loc::getMessage('LANDING_WIDGET_EXAMPLE_CLASS_DEMO_DATA_NAME_5'),
				'PERSONAL_BIRTHDAY' => $this->convertDateFormat('14.09.2000', 'dm', 'd.m.Y'),
				'PERSONAL_PHOTO_PATH' => 'https://cdn.bitrix24.site/bitrix/images/landing/widget/birthdays/5.png',
				'WORK_POSITION' => Loc::getMessage('LANDING_WIDGET_EXAMPLE_CLASS_DEMO_DATA_WORK_POSITION_5'),
			],
		];
	}
}
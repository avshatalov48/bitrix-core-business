<?php
IncludeModuleLangFile(__FILE__);

class CBitrixCloudMobile
{
	/**
	 * Builds menu
	 *
	 * @return void
	 *
	 * RegisterModuleDependences(
	 * 	"mobileapp",
	 * 	"OnBeforeAdminMobileMenuBuild",
	 * 	"bitrixcloud",
	 * 	"CBitrixCloudMobile",
	 * 	"OnBeforeAdminMobileMenuBuild"
	 * );
	 */
	public static function OnBeforeAdminMobileMenuBuild()
	{
		global $USER;

		if ($USER->CanDoOperation('bitrixcloud_monitoring'))
		{
			CAdminMobileMenu::addItem([
				'text' => GetMessage('BCL_MON_MOB_INSPECTOR'),
				'type' => 'section',
				'sort' => 300,
				'items' => [
					[
						'text' => GetMessage('BCL_MON_MOB_MENU_IPAGE'),
						'data-url' => '/bitrix/admin/mobile/bitrixcloud_monitoring_ipage.php',
						'data-pageid' => 'bitrix_cloud_monitoring_info',
						'push-param' => 'bc'
					],
					[
						'text' => GetMessage('BCL_MON_MOB_MENU_PUSH'),
						'data-url' => '/bitrix/admin/mobile/bitrixcloud_monitoring_push.php',
						'data-pageid' => 'bitrix_cloud_monitoring_push',
					],
				],
			]);
		}
	}

	public static function getUserDevices($userId)
	{
		$arResult = [];

		if (CModule::IncludeModule('pull'))
		{
			$dbres = \Bitrix\Pull\Model\PushTable::getList([
				'filter' => [
					'USER_ID' => $userId,
				],
			]);
			while ($arDb = $dbres->fetch())
			{
				if ($arDb['DEVICE_TYPE'] == 'APPLE')
				{
					CModule::IncludeModule('mobileapp');
					CMobile::Init();

					$protocol = 2;
				}
				else
				{
					$protocol = 3;
				}

				$arResult[] = $arDb['DEVICE_TOKEN'] . ':' . $protocol . ':BitrixAdmin';
			}
		}

		return $arResult;
	}
}

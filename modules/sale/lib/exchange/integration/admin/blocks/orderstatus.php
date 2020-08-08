<?php


namespace Bitrix\Sale\Exchange\Integration\Admin\Blocks;


use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Order;

class OrderStatus extends \Bitrix\Sale\Helpers\Admin\Blocks\OrderStatus
{
	protected static function renderCreatorLink($data)
	{
		return '<a href="/bitrix/admin/user_edit.php?lang='.LANGUAGE_ID.'&ID='. $data["CREATOR_USER_ID"].'" target="_blank">'.htmlspecialcharsbx($data["CREATOR_USER_NAME"]).'</a>';
	}

	protected static function renderUserCanceledLink($data)
	{
		return '<a href="/bitrix/admin/user_edit.php?lang='.LANGUAGE_ID.'&ID='.$data["EMP_CANCELED_ID"].'" target="_blank">'.htmlspecialcharsbx($data["EMP_CANCELED_NAME"]).'</a>';
	}

	protected static function getJsObjName()
	{
		return 'BX.Sale.Admin.Integration.OrderEditPage';
	}
}
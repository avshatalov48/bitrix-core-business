<?php


namespace Bitrix\Sale\Exchange\Integration\Admin\Blocks;


class OrderAdditional extends \Bitrix\Sale\Helpers\Admin\Blocks\OrderAdditional
{
	protected static function renderResponsibleLink($data)
	{
		return '<a href="/bitrix/admin/user_edit.php?lang='.LANGUAGE_ID.'&ID='. $data["RESPONSIBLE_ID"].'" id="order_additional_info_responsible" target="_blank">'.htmlspecialcharsbx($data['RESPONSIBLE']).'</a>';
	}
}
<?php
namespace Bitrix\Sale\Exchange\Integration\Admin\Blocks;


class OrderBuyer extends \Bitrix\Sale\Helpers\Admin\Blocks\OrderBuyer
{
	protected static function renderBuyerLink($data, $attr=[])
	{
		return parent::renderBuyerLink($data, array_merge($attr, ['target="_blank"']));
	}
}
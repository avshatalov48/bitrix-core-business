<?php


namespace Bitrix\Sale\Exchange\Integration\Admin\Blocks;


use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Exchange\Integration\EntityType;
use Bitrix\Sale\Exchange\Integration\Relation\Relation;
use Bitrix\Sale\Order;

Loc::loadMessages(__FILE__);

class OrderInfo extends \Bitrix\Sale\Helpers\Admin\Blocks\OrderInfo
{
	protected static function getOrderInfoBlock(Order $order)
	{
		$result = '';

		$relation = Relation::getByEntity(
			EntityType::ORDER, $order->getId(),
			\Bitrix\Sale\Exchange\Integration\CRM\EntityType::DEAL, '');

		if(isset($relation['DST_ENTITY_ID']) && $relation['DST_ENTITY_ID']>0)
		{
			$result = '<div class="adm-bus-orderinfoblock-content">
					<div class="adm-bus-orderinfoblock-content-block-customer">
						<ul class="adm-bus-orderinfoblock-content-customer-info">
							<li>
								<span class="adm-bus-orderinfoblock-content-customer-info-param">'.Loc::getMessage('SALE_ORDER_INFO_LINK').'</span>
								<span class="adm-bus-orderinfoblock-content-customer-info-value" id="order_info_buyer_name">'.$relation['DST_ENTITY_ID'].'</span>
							</li>
						</ul>
					</div>
				</div>';
		}

		return $result;
	}
}
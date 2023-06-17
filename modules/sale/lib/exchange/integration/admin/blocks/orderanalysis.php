<?php


namespace Bitrix\Sale\Exchange\Integration\Admin\Blocks;


use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Exchange\Integration\Admin\Factory;
use Bitrix\Sale\Exchange\Integration\Admin\ModeType;
use Bitrix\Sale\Exchange\Integration\Admin\Registry;

class OrderAnalysis extends \Bitrix\Sale\Helpers\Admin\Blocks\OrderAnalysis
{
	protected static function renderResponsibleLink($data)
	{
		return '<a class="adm-bus-orderdocs-threelist-block-responsible-name" href="/bitrix/admin/user_edit.php?ID='.$data['RESPONSIBLE_ID'].'" target="_blank">'.htmlspecialcharsbx($data['RESPONSIBLE']).'</a>';
	}

	protected static function renderDeliveryRequestView($data)
	{
		$id = $data['ID'];

		$url = Factory::create(ModeType::DEFAULT_TYPE)
			->setPageByType(Registry::SALE_DELIVERY_REQUEST_VIEW)
			->setFilterParams(false)
			->setField('ID', $id)
			->fill()
			->build();

		return '<a href="'.$url.'" class="adm-bus-orderdocs-threelist-block-title-link" target="_blank">'.
			Loc::getMessage('SALE_OANALYSIS_DELIVERY_REQUEST', array(
				'#REQUEST_ID#' => $id
			)).'</a>';
	}

	protected static function renderShipmentItemLink($item)
	{
		if (!isset($item['EDIT_PAGE_URL']))
		{
			return htmlspecialcharsEx($item['NAME']);
		}
		return
			'<a class="fwb" href="' . htmlspecialcharsbx($item['EDIT_PAGE_URL']) . '" target="_blank">'
			. htmlspecialcharsEx($item['NAME'])
			. '</a>'
		;
	}
}
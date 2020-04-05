<?php

namespace Bitrix\Sale\Exchange\OneC;


class ConverterDocumentInvoice extends ConverterDocumentOrder
{
	public static function normalizeExternalCode($xml)
	{
		static $sales = null;

		list($originatorId, $productXmlId) = explode("#", $xml, 2);
		if($productXmlId<>'')
		{
			if($sales === null)
				$sales = \CCrmExternalSaleHelper::PrepareListItems();

			if(isset($sales[$originatorId]))
			{
				$xml = $productXmlId;
			}
		}

		return parent::normalizeExternalCode($xml);
	}

	/**
	 * @param $id
	 * @return string
	 */
	static protected function getStatusNameById($id)
	{
		static $statuses;

		if($statuses === null)
		{
			while($status = \Bitrix\Crm\Invoice\InvoiceStatus::getList()->fetch())
			{
				$statuses[$status['STATUS_ID']] = $status['NAME'];
			}

			if(!is_array($statuses))
			{
				$statuses = array();
			}
		}
		return (isset($statuses[$id])?$statuses[$id]:'');
	}
}
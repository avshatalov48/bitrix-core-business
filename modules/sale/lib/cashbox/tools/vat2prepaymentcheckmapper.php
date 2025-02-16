<?php

namespace Bitrix\Sale\Cashbox\Tools;

use Bitrix\Sale\Cashbox;

class Vat2PrepaymentCheckMapper
{
	public function __construct(private readonly array $mapVatToCalcVat) {}

	public function getMap() : array
	{
		$prepaymentCheckList = [
			Cashbox\PrepaymentCheck::getType(),
			Cashbox\PrepaymentReturnCheck::getType(),
			Cashbox\PrepaymentReturnCashCheck::getType(),
			Cashbox\FullPrepaymentCheck::getType(),
			Cashbox\FullPrepaymentReturnCheck::getType(),
			Cashbox\FullPrepaymentReturnCashCheck::getType(),
		];

		$map = [];

		foreach ($this->mapVatToCalcVat as $vat => $calcVat)
		{
			$map[$vat] = array_fill_keys($prepaymentCheckList, $calcVat);
		}

		return $map;
	}

}
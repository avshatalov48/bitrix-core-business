<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Sale;

interface ICorrection
{
	/**
	 * @param CorrectionCheck $check
	 * @return Sale\Result
	 */
	public function printCorrectionImmediately(CorrectionCheck $check);

	/**
	 * @param CorrectionCheck $check
	 * @return array
	 */
	public function buildCorrectionCheckQuery(CorrectionCheck $check);

	/**
	 * @param CorrectionCheck $check
	 * @return Sale\Result
	 */
	public function checkCorrection(CorrectionCheck $check);
}


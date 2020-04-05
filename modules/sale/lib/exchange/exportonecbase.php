<?php
namespace Bitrix\Sale\Exchange;

use Bitrix\Sale\Exchange\OneC\DocumentBase;

abstract class ExportOneCBase extends ExportPattern
{
	const SHEM_VERSION_2_10 = '2.10';
	const SHEM_VERSION_3_1 = '3.1';

	public static function configuration()
	{
		ManagerExport::registerInstance(EntityType::ORDER, OneC\ExportSettings::getCurrent());
		ManagerExport::registerInstance(EntityType::SHIPMENT, OneC\ExportSettings::getCurrent());
		ManagerExport::registerInstance(EntityType::PAYMENT_CASH, OneC\ExportSettings::getCurrent());
		ManagerExport::registerInstance(EntityType::PAYMENT_CASH_LESS, OneC\ExportSettings::getCurrent());
		ManagerExport::registerInstance(EntityType::PAYMENT_CARD_TRANSACTION, OneC\ExportSettings::getCurrent());
		ManagerExport::registerInstance(EntityType::USER_PROFILE, OneC\ExportSettings::getCurrent());
	}

	/**
	 * @return string
	 */
	public function outputXmlCMLHeader()
	{
		return "<?xml version=\"1.0\" encoding=\"windows-1251\"?>\n".
			"<".
			DocumentBase::getLangByCodeField("COM_INFORMATION")." ".
			DocumentBase::getLangByCodeField("SHEM_VERSION")."=\"".$this->getShemVersion()."\" ".
			DocumentBase::getLangByCodeField("SHEM_DATE_CREATE")."=\"".date("Y-m-d")."T".date("G:i:s")."\" ".
			DocumentBase::getLangByCodeField("DATE_FORMAT")."=\"".
			DocumentBase::getLangByCodeField("DATE_FORMAT_DF")."=yyyy-MM-dd; ".
			DocumentBase::getLangByCodeField("DATE_FORMAT_DLF")."=DT\" ".
			DocumentBase::getLangByCodeField("DATE_FORMAT_DATETIME")."=\"".
			DocumentBase::getLangByCodeField("DATE_FORMAT_DF")."=".
			DocumentBase::getLangByCodeField("DATE_FORMAT_TIME")."; ".
			DocumentBase::getLangByCodeField("DATE_FORMAT_DLF")."=T\" ".
			DocumentBase::getLangByCodeField("DEL_DT")."=\"T\" ".
			DocumentBase::getLangByCodeField("FORM_SUMM")."=\"".
			DocumentBase::getLangByCodeField("FORM_CC")."=18; ".
			DocumentBase::getLangByCodeField("FORM_CDC")."=2; ".
			DocumentBase::getLangByCodeField("FORM_CRD")."=.\" ".
			DocumentBase::getLangByCodeField("FORM_QUANT")."=\"".
			DocumentBase::getLangByCodeField("FORM_CC")."=18; ".
			DocumentBase::getLangByCodeField("FORM_CDC")."=2; ".
			DocumentBase::getLangByCodeField("FORM_CRD")."=.\"".
			">\n";
	}

	/**
	 * @return string
	 */
	public function outputXmlCMLFooter()
	{
		return "</".DocumentBase::getLangByCodeField("COM_INFORMATION").">";
	}

	/**
	 * @return string
	 */
	abstract protected function getShemVersion();

	/**
	 * @return string
	 */
	public function getDirectionType()
	{
		return ManagerExport::getDirectionType();
	}
}
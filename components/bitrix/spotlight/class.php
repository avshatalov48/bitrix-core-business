<?

use Bitrix\Main\UI\Spotlight;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

class SpotlightComponent extends \CBitrixComponent
{
	protected $spotlight = null;

	protected function prepareParams()
	{
		$this->arParams["JS_OPTIONS"] =
			isset($this->arParams["JS_OPTIONS"]) && is_array($this->arParams["JS_OPTIONS"])
				? $this->arParams["JS_OPTIONS"]
				: array()
		;
	}

	protected function prepareData()
	{
		if (!isset($this->arParams["ID"]) || !mb_strlen($this->arParams["ID"]))
		{
			ShowError("Spotlight: 'ID' parameter is required.");
			return;
		}

		$this->spotlight = new Spotlight($this->arParams["ID"]);

		$params = array(
			"USER_TYPE" => "setUserType",
			"USER_TIMESPAN" => "setUserTimeSpan",
			"LIFETIME" => "setLifetime",
			"START_DATE" => "setStartDate",
			"END_DATE" => "setEndDate",
		);

		foreach ($params as $param => $setter)
		{
			if (isset($this->arParams[$param]))
			{
				$this->spotlight->$setter($this->arParams[$param]);
			}
		}

		$this->arResult["ID"] = $this->spotlight->getId();
		$this->arResult["IS_AVAILABLE"] = $this->spotlight->isAvailable();

		$defaultOptions = array(
			"autoSave" => true
		);

		$this->arResult["OPTIONS"] = array_merge(
			$defaultOptions,
			$this->arParams["JS_OPTIONS"],
			array(
				"id" => $this->spotlight->getId()
			)
		);

	}

	public function getSpotlight()
	{
		return $this->spotlight;
	}

	public function executeComponent()
	{
		$this->prepareParams();
		$this->prepareData();

		if ($this->getSpotlight())
		{
			$this->includeComponentTemplate();
		}
	}
}
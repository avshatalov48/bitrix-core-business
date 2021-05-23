<?

use Bitrix\Main\UI\Tour;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

class TourComponent extends \CBitrixComponent
{
	protected $tour = null;

	protected function prepareParams()
	{
		$this->arParams["JS_OPTIONS"] =
			isset($this->arParams["JS_OPTIONS"]) && is_array($this->arParams["JS_OPTIONS"])
				? $this->arParams["JS_OPTIONS"]
				: []
		;

		$this->arParams["AUTO_START"] =
			isset($this->arParams["AUTO_START"]) && in_array($this->arParams["AUTO_START"], ["N", false], true)
				? false
				: true
		;

		$this->arParams["AUTO_START_TIMEOUT"] =
			isset($this->arParams["AUTO_START_TIMEOUT"]) &&
			is_int($this->arParams["AUTO_START_TIMEOUT"]) &&
			$this->arParams["AUTO_START_TIMEOUT"] > 0
				? $this->arParams["AUTO_START_TIMEOUT"]
				: 0
		;
	}

	protected function prepareData()
	{
		if (!isset($this->arParams["ID"]) || !mb_strlen($this->arParams["ID"]))
		{
			ShowError("Tour: 'ID' parameter is required.");
			return;
		}

		$this->tour = new Tour($this->arParams["ID"]);

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
				$this->tour->$setter($this->arParams[$param]);
			}
		}

		$this->arResult["ID"] = $this->tour->getId();
		$this->arResult["IS_AVAILABLE"] = $this->tour->isAvailable();

		$defaultOptions = [
			"autoSave" => true
		];

		$this->arResult["OPTIONS"] = array_merge(
			$defaultOptions,
			$this->arParams["JS_OPTIONS"],
			[
				"id" => $this->tour->getId()
			]
		);

	}

	public function getTour()
	{
		return $this->tour;
	}

	public function executeComponent()
	{
		$this->prepareParams();
		$this->prepareData();

		if ($this->getTour())
		{
			$this->includeComponentTemplate();
		}
	}
}
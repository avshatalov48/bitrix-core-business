<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Localization\Loc;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;

class CSaleStoreChooseComponent extends CBitrixComponent
{
	const MAP_TYPE_YANDEX = 'yandex';
	const MAP_TYPE_GOOGLE = 'google';
	const MAP_TYPE_NONE = 'none';

	/**
	 * @param array $params
	 * @return bool
	 * @throws ArgumentNullException
	 * @throws ArgumentOutOfRangeException
	 */
	public function checkParams($params)
	{
		if(!isset($params["INDEX"]))
			throw new ArgumentNullException('params["INDEX"]');

		if(!isset($params["STORES_LIST"]) || !is_array($params["STORES_LIST"]) || count($params["STORES_LIST"]) <= 0 )
			throw new ArgumentNullException('params["STORES_LIST"]');

		if(isset($params["MAP_TYPE"])
			&& !in_array($params["MAP_TYPE"], array(self::MAP_TYPE_GOOGLE, self::MAP_TYPE_YANDEX, self::MAP_TYPE_NONE)
		))
		{
			$params["MAP_TYPE"] = self::MAP_TYPE_YANDEX;
		}

		return true;
	}

	/**
	 * @param array $params
	 * @return array
	 */
	public function onPrepareComponentParams($params)
	{
		$params = parent::onPrepareComponentParams($params);

		if (!isset($params["MAP"]))
			$params["MAP"] = array();

		if(!isset($params["MAP_TYPE"]))
			$params["MAP_TYPE"] = Option::get('sale', 'order_choose_comp_map_type', self::MAP_TYPE_YANDEX);

		if (!isset($params["MAP"]['CONTROLS']))
			$params["MAP"]['CONTROLS'] = array("TYPECONTROL");

		if (!isset($params["MAP"]['OPTIONS']))
			$params["MAP"]['OPTIONS'] = array("ENABLE_SCROLL_ZOOM", "ENABLE_DRAGGING");

		if(!isset($params["WIDTH"]))
			$params["WIDTH"] = 400;

		if(!isset($params["HEIGHT"]))
			$params["HEIGHT"] = 400;

		if(!isset($params["SELECTED_STORE"]))
		{
			$params["SELECTED_STORE"] = 0;
			if (isset($params["STORES_LIST"]) && is_array($params["STORES_LIST"]))
			{
				reset($params["STORES_LIST"]);
				$params["SELECTED_STORE"] = key($params["STORES_LIST"]);
			}
		}

		if(!isset($params["SHOW_MAP_TYPE_SETTINGS"]) || $params["SHOW_MAP_TYPE_SETTINGS"] != 'Y')
			$params["SHOW_MAP_TYPE_SETTINGS"] = 'N';

		return $params;
	}

	/**
	 * void
	 */
	public function executeComponent()
	{
		try
		{
			$this->checkParams($this->arParams);
		}
		catch(\Exception $e)
		{
			ShowError($e->getMessage());
			return;
		}

		$stores = array();
		$arStoreLocation = array();
		$scaleMapParamName = $this->getMapParamScaleName();

		if(strlen($scaleMapParamName) > 0)
			$arStoreLocation = array($scaleMapParamName => 11, "PLACEMARKS" => array());

		foreach($this->arParams["STORES_LIST"] as $storeId => $storeParams)
		{
			$stores[$storeParams["ID"]] = $storeParams;

			if (intval($storeParams["IMAGE_ID"]) > 0)
			{
				$arImage = CFile::GetFileArray($storeParams["IMAGE_ID"]);
				$imgValue = CFile::ShowImage($arImage, 115, 115, "border=0", "", false);
				$stores[$storeParams["ID"]]["IMAGE"] = $imgValue;
				$stores[$storeParams["ID"]]["IMAGE_URL"] = $arImage["SRC"];
			}

			if(!empty($arStoreLocation))
			{
				$latMapParamName = self::getMapParamLatName();
				$lonMapParamName = self::getMapParamLonName();

				if (floatval($arStoreLocation[$latMapParamName]) <= 0)
					$arStoreLocation[$latMapParamName] = $storeParams["GPS_N"];

				if (floatval($arStoreLocation[$lonMapParamName]) <= 0)
					$arStoreLocation[$lonMapParamName] = $storeParams["GPS_S"];


				$arLocationTmp = array();
				$arLocationTmp["ID"] = $storeParams["ID"];
				if (strlen($storeParams["GPS_N"]) > 0)
					$arLocationTmp["LAT"] = $storeParams["GPS_N"];
				if (strlen($storeParams["GPS_S"]) > 0)
					$arLocationTmp["LON"] = $storeParams["GPS_S"];
				if (strlen($storeParams["TITLE"]) > 0)
					$arLocationTmp["TEXT"] = htmlspecialcharsbx($storeParams["TITLE"]."\r\n".$storeParams["DESCRIPTION"]);

				$arStoreLocation["PLACEMARKS"][] = $arLocationTmp;
			}
		}

		if(!empty($arStoreLocation))
			$this->arResult["LOCATION"] = serialize($arStoreLocation);

		$this->arResult["SHOW_IMAGES"] = (isset($_REQUEST["showImages"]) && $_REQUEST["showImages"] == "Y");
		$this->arResult["STORES"] = $stores;
		$this->arResult["MAP_TYPES_LIST"] = array(
			self::MAP_TYPE_YANDEX => Loc::getMessage('SALE_SSC_MAP_YANDEX'),
			self::MAP_TYPE_GOOGLE => Loc::getMessage('SALE_SSC_MAP_GOOGLE'),
			self::MAP_TYPE_NONE => Loc::getMessage('SALE_SSC_MAP_NONE'),
		);
		$this->arResult['AJAX_URL'] = $this->getPath()."/ajax.php";

		$this->includeComponentTemplate();
	}

	/**
	 * @return string
	 */
	protected function getMapParamScaleName()
	{
		if($this->arParams['MAP_TYPE'] == self::MAP_TYPE_YANDEX)
			$result = 'yandex_scale';
		elseif($this->arParams['MAP_TYPE'] == self::MAP_TYPE_GOOGLE)
			$result = 'google_scale';
		else
			$result = '';

		return $result;
	}

	/**
	 * @return string
	 */
	protected function getMapParamLonName()
	{
		if($this->arParams['MAP_TYPE'] == self::MAP_TYPE_YANDEX)
			$result = 'yandex_lon';
		elseif($this->arParams['MAP_TYPE'] == self::MAP_TYPE_GOOGLE)
			$result = 'google_lon';
		else
			$result = '';

		return $result;
	}

	/**
	 * @return string
	 */
	protected function getMapParamLatName()
	{
		if($this->arParams['MAP_TYPE'] == self::MAP_TYPE_YANDEX)
			$result = 'yandex_lat';
		elseif($this->arParams['MAP_TYPE'] == self::MAP_TYPE_GOOGLE)
			$result = 'google_lat';
		else
			$result = '';

		return $result;
	}
}
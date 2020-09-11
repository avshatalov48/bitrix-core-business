<?
IncludeModuleLangFile(__FILE__);
/*
GEOIP_ADDR 		83.219.130.40
GEOIP_CONTINENT_CODE 	AS
GEOIP_COUNTRY_CODE 	RU
GEOIP_COUNTRY_NAME 	Russian Federation
GEOIP_REGION 		23
GEOIP_REGION_NAME 	Kaliningrad
GEOIP_CITY 		Kaliningrad
GEOIP_DMA_CODE 		0
GEOIP_AREA_CODE 	0
GEOIP_LATITUDE 		54.709999
GEOIP_LONGITUDE 	20.500000
*/
class CCityLookup_geoip_mod extends CCityLookup
{
	var $continent_code = false;
	var $latitude = false;
	var $longitude = false;

	public static function OnCityLookup($arDBRecord = false)
	{
		return new CCityLookup_geoip_mod($arDBRecord);
	}

	function __construct($arDBRecord = false)
	{
		parent::__construct($arDBRecord);
		if(!$arDBRecord)
		{
			if(array_key_exists("GEOIP_COUNTRY_CODE", $_SERVER) && mb_strlen($_SERVER["GEOIP_COUNTRY_CODE"]) == 2)
			{
				$this->is_installed = true;
				$this->country_code = $_SERVER["GEOIP_COUNTRY_CODE"];
			}
			$this->charset = "iso-8859-1";
		}
		else
		{
			if(array_key_exists("XCONT", $arDBRecord)) $this->continent_code = $arDBRecord["XCONT"];
			if(array_key_exists("XLAT", $arDBRecord)) $this->latitude = $arDBRecord["XLAT"];
			if(array_key_exists("XLON", $arDBRecord)) $this->longitude = $arDBRecord["XLON"];
		}
	}

	function ArrayForDB()
	{
		$ar = parent::ArrayForDB();
		if($this->continent_code) $ar["XCONT"] = $this->continent_code;
		if($this->latitude) $ar["XLAT"] = $this->latitude;
		if($this->longitude) $ar["XLON"] = $this->longitude;
		return $ar;
	}

	function GetFullInfo()
	{
		$ar = parent::GetFullInfo();
		$ar["CONTINENT"] = array(
			"TITLE" => GetMessage("STAT_CITY_GEOIP_MOD_CONTINENT"),
			"VALUE~" => $this->continent_code,
			"VALUE" => htmlspecialcharsbx($this->continent_code),
		);
		$ar["LONGITUDE"] = array(
			"TITLE" => GetMessage("STAT_CITY_GEOIP_MOD_LONGITUDE"),
			"VALUE~" => $this->longitude,
			"VALUE" => htmlspecialcharsbx($this->longitude),
		);
		$ar["LATITUDE"] = array(
			"TITLE" => GetMessage("STAT_CITY_GEOIP_MOD_LATITUDE"),
			"VALUE~" => $this->latitude,
			"VALUE" => htmlspecialcharsbx($this->latitude),
		);
		return $ar;
	}

	function GetDescription()
	{
		return array(
			"CLASS" => "CCityLookup_geoip_mod",
			"DESCRIPTION" => GetMessage("STAT_CITY_GEOIP_MOD_DESCR"),
			"IS_INSTALLED" => $this->is_installed,
			"CAN_LOOKUP_COUNTRY" => true,
			"CAN_LOOKUP_CITY" => array_key_exists("GEOIP_REGION", $_SERVER) && mb_strlen($_SERVER["GEOIP_REGION"]) == 2
					&& array_key_exists("GEOIP_CITY", $_SERVER) && $_SERVER["GEOIP_CITY"] <> '',
		);
	}

	function IsInstalled()
	{
		return $this->is_installed;
	}

	function Lookup()
	{
		if(array_key_exists("GEOIP_COUNTRY_NAME", $_SERVER) && $_SERVER["GEOIP_COUNTRY_NAME"] <> '')
		{
			$this->country_full_name = $_SERVER["GEOIP_COUNTRY_NAME"];
		}
		if(array_key_exists("GEOIP_REGION", $_SERVER) && mb_strlen($_SERVER["GEOIP_REGION"]) == 2)
		{
			$this->region_name = $_SERVER["GEOIP_REGION"];
			if(array_key_exists("GEOIP_CITY", $_SERVER) && $_SERVER["GEOIP_CITY"] <> '')
			{
				$this->city_name = $_SERVER["GEOIP_CITY"];
			}
		}
		//Extended information
		if(array_key_exists("GEOIP_CONTINENT_CODE", $_SERVER) && $_SERVER["GEOIP_CONTINENT_CODE"] <> '')
		{
			$this->continent_code = $_SERVER["GEOIP_CONTINENT_CODE"];
		}
		if(array_key_exists("GEOIP_LATITUDE", $_SERVER) && $_SERVER["GEOIP_LATITUDE"] <> '')
		{
			$this->latitude = $_SERVER["GEOIP_LATITUDE"];
		}
		if(array_key_exists("GEOIP_LONGITUDE", $_SERVER) && $_SERVER["GEOIP_LONGITUDE"] <> '')
		{
			$this->longitude = $_SERVER["GEOIP_LONGITUDE"];
		}
	}
}
?>

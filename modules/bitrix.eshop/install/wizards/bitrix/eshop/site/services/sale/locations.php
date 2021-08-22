<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if(!CModule::IncludeModule('sale'))
	return;

$dbSite = CSite::GetByID(WIZARD_SITE_ID);
if($arSite = $dbSite -> Fetch())
	$lang = $arSite["LANGUAGE_ID"];
if($lang == '')
	$lang = "ru";
$bRus = false;
if($lang == "ru")
	$bRus = true;

if($bRus || COption::GetOptionString("eshop", "wizard_installed", "N", WIZARD_SITE_ID) != "Y" || WIZARD_INSTALL_DEMO_DATA)
{
	$loc_file = $wizard->GetVar("locations_csv");


	$typeTableFreshEnough = false; 
	if($GLOBALS['DB']->Query("select DISPLAY_SORT from b_sale_loc_type WHERE 1=0", true))
		$typeTableFreshEnough = true;

	if($loc_file <> '')
	{
		define('LOC_STEP_LENGTH', 20);

		if(\Bitrix\Main\Config\Option::get('sale', 'sale_locationpro_migrated', '') == 'Y') // CSaleLocation::isLocationProMigrated()
		{
			require($_SERVER['DOCUMENT_ROOT'].WIZARD_SERVICE_RELATIVE_PATH."/locations/pro/file_map.php");

			$file_url = 		$_SERVER['DOCUMENT_ROOT'].WIZARD_SERVICE_RELATIVE_PATH."/locations/pro/bundles/".$LOCATION_FILE_MAP[$loc_file];
			$type_file_url = 	$_SERVER['DOCUMENT_ROOT'].WIZARD_SERVICE_RELATIVE_PATH."/locations/pro/type".($typeTableFreshEnough ? '.v2' : '').".csv";
			$service_file_url = $_SERVER['DOCUMENT_ROOT'].WIZARD_SERVICE_RELATIVE_PATH."/locations/pro/externalservice.csv";

			if (isset($LOCATION_FILE_MAP[$loc_file]) && file_exists($file_url))
			{
				if(!isset($_SESSION["LOC_IMPORT_DESC"]) || ($file_url != $_SESSION["LOC_IMPORT_DESC"]['FILE']))
				{
					$_SESSION["LOC_IMPORT_DESC"] = array(
						'POS' => 0,
						'FILE' => $file_url,
						'TYPE_FILE' => $type_file_url,
						'SERVICE_FILE' => $service_file_url,
						'TIME_LIMIT' => LOC_STEP_LENGTH,
						'STEP' => 'import'
					);
				}

				$done = \Bitrix\Sale\Location\Import\ImportProcess::importFile($_SESSION["LOC_IMPORT_DESC"]);
				
				if($done)
				{
					unset($_SESSION["LOC_IMPORT_DESC"]);
					// go farther to other steps
				}
				else
				{
					$this->repeatCurrentService = true; // go to the next iteration of the same step
				}
			}
		}
		else
		{
			// DEPRECATED old location branch

			$time_limit = ini_get('max_execution_time');
			if ($time_limit < LOC_STEP_LENGTH) set_time_limit(LOC_STEP_LENGTH + 5);

			$start_time = time();
			$finish_time = $start_time + LOC_STEP_LENGTH;

			if(in_array($loc_file, array("loc_ussr.csv", "loc_ua.csv", "loc_kz.csv")))
				$file_url = $_SERVER['DOCUMENT_ROOT'].WIZARD_SERVICE_RELATIVE_PATH."/locations/ru/".$loc_file;
			else
				$file_url = $_SERVER['DOCUMENT_ROOT'].WIZARD_SERVICE_RELATIVE_PATH."/locations/".$loc_file;

			if (file_exists($file_url))
			{
				$bFinish = true;

				$arSysLangs = Array();
				$db_lang = CLangAdmin::GetList('', '', array("ACTIVE" => "Y"));
				while ($arLang = $db_lang->Fetch())
				{
					$arSysLangs[$arLang["LID"]] = $arLang["LID"];
				}

				$arLocations = array();
				$bSync = true;

				$dbLocations = CSaleLocation::GetList(array(), array(), false, false, array("ID", "COUNTRY_ID", "REGION_ID", "CITY_ID"));
				while ($arLoc = $dbLocations->Fetch())
				{
					$arLocations[$arLoc["ID"]] = $arLoc;
				}

				if (count($arLocations) <= 0)
					$bSync = false;

				include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/csv_data.php");

				$csvFile = new CCSVData();
				$csvFile->LoadFile($file_url);
				$csvFile->SetFieldsType("R");
				$csvFile->SetFirstHeader(false);
				$csvFile->SetDelimiter(",");

				$arRes = $csvFile->Fetch();
				if (is_array($arRes) && count($arRes) > 0 && mb_strlen($arRes[0]) == 2)
				{
					$DefLang = $arRes[0];
					if (in_array($DefLang, $arSysLangs))
					{

						if (is_set($_SESSION["LOC_POS"]))
						{
							$csvFile->SetPos($_SESSION["LOC_POS"]);

							$CurCountryID = $_SESSION["CUR_COUNTRY_ID"];
							$CurRegionID = $_SESSION["CUR_REGION_ID"];
							$numCountries = $_SESSION["NUM_COUNTRIES"];
							$numRegiones = $_SESSION["NUM_REGIONES"];
							$numCities = $_SESSION["NUM_CITIES"];
							$numLocations = $_SESSION["NUM_LOCATIONS"];
						}
						else
						{
							$CurCountryID = 0;
							$CurRegionID = 0;
							$numCountries = 0;
							$numRegiones = 0;
							$numCities = 0;
							$numLocations = 0;
						}

						$tt = 0;
						while ($arRes = $csvFile->Fetch())
						{
							$type = mb_strtoupper($arRes[0]);
							$tt++;
							$arArrayTmp = array();
							foreach($arRes as $ind => $value)
							{
								if ($ind%2 && isset($arSysLangs[$value]))
								{
									$arArrayTmp[$value] = array(
											"LID" => $value,
											"NAME" => $arRes[$ind + 1]
										);

									if ($value == $DefLang)
									{
										$arArrayTmp["NAME"] = $arRes[$ind + 1];
									}
								}
							}

							//country
							if (is_array($arArrayTmp) && $arArrayTmp["NAME"] <> '')
							{
								if ($type == "S")
								{
									$CurRegionID = null;
									$arRegionList = Array();
									$CurCountryID = null;
									$arContList = array();
									$LLL = 0;
									if ($bSync)
									{
										$db_contList = CSaleLocation::GetList(
											Array(),
											Array(
												"COUNTRY_NAME" => $arArrayTmp["NAME"],
												"LID" => $DefLang
											)
										);
										if ($arContList = $db_contList->Fetch())
										{
											$LLL = intval($arContList["ID"]);
											$CurCountryID = intval($arContList["COUNTRY_ID"]);
										}
									}

									if (intval($CurCountryID) <= 0)
									{
										$CurCountryID = CSaleLocation::AddCountry($arArrayTmp);
										$CurCountryID = intval($CurCountryID);
										if ($CurCountryID>0)
										{
											$numCountries++;
											if(intval($LLL) <= 0)
											{
												$LLL = CSaleLocation::AddLocation(array("COUNTRY_ID" => $CurCountryID));
												if (intval($LLL)>0) $numLocations++;
											}
										}
									}
								}
								elseif ($type == "R") //region
								{
									$CurRegionID = null;
									$arRegionList = Array();
									$LLL = 0;
									if ($bSync)
									{
										$db_rengList = CSaleLocation::GetList(
											Array(),
											Array(
												"COUNTRY_ID" => $CurCountryID,
												"REGION_NAME"=>$arArrayTmp["NAME"],
												"LID" => $DefLang
											)
										);
										if ($arRegionList = $db_rengList->Fetch())
										{
											$LLL = $arRegionList["ID"];
											$CurRegionID = intval($arRegionList["REGION_ID"]);
										}
									}

									if (intval($CurRegionID) <= 0)
									{
										$CurRegionID = CSaleLocation::AddRegion($arArrayTmp);
										$CurRegionID = intval($CurRegionID);
										if ($CurRegionID > 0)
										{
											$numRegiones++;
											if (intval($LLL) <= 0)
											{
												$LLL = CSaleLocation::AddLocation(array("COUNTRY_ID" => $CurCountryID, "REGION_ID" => $CurRegionID));
												if (intval($LLL)>0) $numLocations++;
											}
										}
									}
								}
								elseif ($type == "T" && intval($CurCountryID)>0) //city
								{
									$city_id = 0;
									$LLL = 0;
									$arCityList = Array();

									if ($bSync)
									{
										$arFilter = Array(
												"COUNTRY_ID" => $CurCountryID,
												"CITY_NAME" => $arArrayTmp["NAME"],
												"LID" => $DefLang
											);
										if(intval($CurRegionID) > 0)
											$arFilter["REGION_ID"] = $CurRegionID;

										$db_cityList = CSaleLocation::GetList(
											Array(),
											$arFilter
										);
										if ($arCityList = $db_cityList->Fetch())
										{
											$LLL = $arCityList["ID"];
											$city_id = intval($arCityList["CITY_ID"]);
										}
									}

									if ($city_id <= 0)
									{
										$city_id = CSaleLocation::AddCity($arArrayTmp);
										$city_id = intval($city_id);
										if ($city_id > 0)
											$numCities++;
									}

									if ($city_id > 0)
									{
										if (intval($LLL) <= 0)
										{
											$LLL = CSaleLocation::AddLocation(
												array(
													"COUNTRY_ID" => $CurCountryID,
													"REGION_ID" => $CurRegionID,
													"CITY_ID" => $city_id
												));

											if (intval($LLL) > 0) $numLocations++;
										}
									}
								}
							}

							if($tt == 10)
							{
								$tt = 0;
								$cur_time = time();

								if ($cur_time >= $finish_time)
								{
									$cur_step = $csvFile->GetPos();
									$amount = $csvFile->iFileLength;

									$_SESSION["LOC_POS"] = $cur_step;
									$_SESSION["CUR_COUNTRY_ID"] = $CurCountryID;
									$_SESSION["CUR_REGION_ID"] = $CurRegionID;
									$_SESSION["NUM_COUNTRIES"] = $numCountries;
									$_SESSION["NUM_REGIONES"] = $numRegiones;
									$_SESSION["NUM_CITIES"] = $numCities;
									$_SESSION["NUM_LOCATIONS"] = $numLocations;

									$this->repeatCurrentService = true;

									$bFinish = false;
								}
							}
						}
					}
				}

				if ($bFinish)
					unset($_SESSION["LOC_POS"]);
				else
					return true;

				$time_limit = ini_get('max_execution_time');
				if ($time_limit < LOC_STEP_LENGTH) set_time_limit(LOC_STEP_LENGTH + 5);

				$start_time = time();
				$finish_time = $start_time + LOC_STEP_LENGTH;

				if ($loc_file == "loc_ussr.csv" && file_exists($_SERVER['DOCUMENT_ROOT'].WIZARD_SERVICE_RELATIVE_PATH."/locations/ru/zip_ussr.csv"))
				{
					$rsLocations = CSaleLocation::GetList(array(), array("LID" => 'ru'), false, false, array("ID", "CITY_NAME_LANG", "REGION_NAME_LANG"));
					$arLocationMap = array();
					while ($arLocation = $rsLocations->Fetch())
					{
						if($arLocation["CITY_NAME_LANG"] <> '')
						{
							if($arLocation["REGION_NAME_LANG"] <> '')
								$arLocationMap[$arLocation["CITY_NAME_LANG"]][$arLocation["REGION_NAME_LANG"]] = $arLocation["ID"];
							else
								$arLocationMap[$arLocation["CITY_NAME_LANG"]] = $arLocation["ID"];
						}
					}

					$DB->StartTransaction();

					include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/csv_data.php");

					$csvFile = new CCSVData();
					$csvFile->LoadFile($_SERVER['DOCUMENT_ROOT'].WIZARD_SERVICE_RELATIVE_PATH."/locations/ru/zip_ussr.csv");
					$csvFile->SetFieldsType("R");
					$csvFile->SetFirstHeader(false);
					$csvFile->SetDelimiter(";");

					if (is_set($_SESSION, 'ZIP_POS'))
					{
						$numZIP = $_SESSION["NUM_ZIP"];
						$csvFile->SetPos($_SESSION["ZIP_POS"]);
					}
					else
					{
						CSaleLocation::ClearAllLocationZIP();

						unset($_SESSION["NUM_ZIP"]);
						$numZIP = 0;
					}

					$bFinish = true;
					$arLocationsZIP = array();
					$tt = 0;
					$REGION = "";
					while ($arRes = $csvFile->Fetch())
					{
						$tt++;
						$CITY = $arRes[1];
						if($arRes[3] <> '')
							$REGION = $arRes[3];

						if (array_key_exists($CITY, $arLocationMap))
						{
							if($REGION <> '')
								$ID = $arLocationMap[$CITY][$REGION];
							else
								$ID = $arLocationMap[$CITY];
						}
						else
						{
							$ID = 0;
						}

						if ($ID)
						{
							CSaleLocation::AddLocationZIP($ID, $arRes[2]);

							$numZIP++;
						}

						if($tt == 10)
						{
							$tt = 0;

							$cur_time = time();
							if ($cur_time >= $finish_time)
							{
								$cur_step = $csvFile->GetPos();
								$amount = $csvFile->iFileLength;

								$_SESSION["ZIP_POS"] = $cur_step;
								$_SESSION["NUM_ZIP"] = $numZIP;

								$bFinish = false;

								$this->repeatCurrentService = true;

							}
						}
					}

					$DB->Commit();

					if ($bFinish)
						unset($_SESSION["ZIP_POS"]);
					else
						return true;
				}
			}

		}
	}
}
?>
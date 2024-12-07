<?php

use Bitrix\Main;
use Bitrix\Main\Loader;

IncludeModuleLangFile(__FILE__);

if (!Loader::includeModule('sale'))
{
	$arReturn['ERROR'] = GetMessage('SL_MODULE_SALE_NOT_INSTALLED');

	return $arReturn;
}

function getAllowedFiles(): array
{
	$region = \Bitrix\Main\Application::getInstance()->getLicense()->getRegion();
	$isBitrixSiteManagementOnly =
		!\Bitrix\Main\Loader::includeModule('bitrix24')
		&& !\Bitrix\Main\Loader::includeModule('intranet')
	;

	if ($region === 'ru' || $region === 'by' || $region === 'kz' || $isBitrixSiteManagementOnly)
	{
		return [
			'loc_ussr.csv',
			'loc_kz.csv',
			'loc_usa.csv',
			'loc_cntr.csv',
			'locations.csv',
		];
	}

	return [
		'locations.csv',
	];
}

function saleLocationLoadFile($arParams): array
{
	$arReturn = [
		'STEP' => false,
		'ERROR' => '',
		'MESSAGE' => '',
	];

	if (!defined('DLSERVER'))
	{
		define('DLSERVER', $arParams['DLSERVER']);
	}
	if (!defined('DLPORT'))
	{
		define('DLPORT', $arParams['DLPORT']);
	}
	if (!defined('DLPATH'))
	{
		define('DLPATH', $arParams['DLPATH']);
	}
	if (!defined('DLMETHOD'))
	{
		define('DLMETHOD', $arParams['DLMETHOD']);
	}
	if (!defined('DLZIPFILE'))
	{
		define('DLZIPFILE', $arParams['DLZIPFILE']);
	}

	if(isset($arParams['TMP_PATH']))
		$sTmpFilePath = $arParams['TMP_PATH'];
	else
		$sTmpFilePath = CTempFile::GetDirectoryName(12, 'sale');

	set_time_limit(600);

	$STEP = (int)($arParams['STEP'] ?? 0);
	$CSVFILE = (string)($arParams['CSVFILE'] ?? '');
	$LOADZIP = $arParams["LOADZIP"];

	if ($CSVFILE !== '' && !in_array($CSVFILE, getAllowedFiles(), true))
	{
		$arReturn['ERROR'] = GetMessage('SL_IMPORT_ERROR_FILES');
	}
	else
	{
		if ($STEP == 1 && ($CSVFILE == '' || $CSVFILE == 'locations.csv'))
		{
			if ($LOADZIP == 'Y') $STEP = 2;
			else $STEP = 3;
		}

		switch($STEP)
		{
			case 0:
				$arReturn['MESSAGE'] = GetMessage('SL_LOADER_LOADING');
				$arReturn['STEP'] = 1;
			break;

			case 1:
				$url = $arParams['DLSERVER']
					. (isset($arParams['DLPORT']) ? ':' . $arParams['DLPORT'] : '')
					. $arParams['DLPATH']
					. $CSVFILE
				;
				$http = new Main\Web\HttpClient();
				$http->setRedirect(true);
				$data =
					DLMETHOD === 'POST'
						? $http->post($url)
						: $http->get($url)
				;
				$data = (string)$data;

				if ($data !== '')
				{
					CheckDirPath($sTmpFilePath);
					$fp = fopen($sTmpFilePath.$CSVFILE, 'w');
					fwrite($fp, Main\Text\Encoding::convertEncoding($data, 'windows-1251', LANG_CHARSET));
					fclose($fp);

					$arReturn['MESSAGE'] = GetMessage('SL_LOADER_FILE_LOADED').' '.$CSVFILE;
					$arReturn['STEP'] = $LOADZIP == "Y" ? 2 : 3;
				}
				else
				{
					$arReturn['ERROR'] = GetMessage('SL_LOADER_FILE_ERROR').' '.$CSVFILE;
					$arReturn['RUN_ERROR'] = true;
				}

			break;

			case 2:
				$url = $arParams['DLSERVER']
					. (isset($arParams['DLPORT']) ? ':' . $arParams['DLPORT'] : '')
					. $arParams['DLPATH']
					. $CSVFILE
				;
				$http = new Main\Web\HttpClient();
				$http->setRedirect(true);
				$data =
					DLMETHOD === 'POST'
						? $http->post($url)
						: $http->get($url)
				;
				$data = (string)$data;

				if ($data !== '')
				{
					CheckDirPath($sTmpFilePath);
					$fp = fopen($sTmpFilePath.DLZIPFILE, 'w');
					fwrite($fp, Main\Text\Encoding::convertEncoding($data, 'windows-1251', LANG_CHARSET));
					fclose($fp);

					$arReturn['MESSAGE'] = GetMessage('SL_LOADER_FILE_LOADED').' '.DLZIPFILE;
					$arReturn['STEP'] = 3;
				}
				else
				{
					$arReturn['ERROR'] = GetMessage('SL_LOADER_FILE_ERROR').' '.DLZIPFILE;
					$arReturn['RUN_ERROR'] = true;
				}

			break;

			case 3:
				$arReturn['COMPLETE'] = true;
			break;
		}
	}

	return $arReturn;
}

function saleLocationImport($arParams): array
{
	global $DB;

	$arReturn = [
		'STEP' => false,
		'ERROR' => '',
		'AMOUNT' => 0,
		'POS' => 0,
		'MESSAGE' => '',
	];

	$step_length = (int)($arParams['STEP_LENGTH'] ?? 0);

	if ($step_length <= 0)
		$step_length = 10;

	define('ZIP_STEP_LENGTH', $step_length);
	define('LOC_STEP_LENGTH', $step_length);
	define('DLZIPFILE', $arParams["DLZIPFILE"]);

	$STEP = (int)($arParams['STEP'] ?? 0);
	$CSVFILE = (string)($arParams['CSVFILE'] ?? '');
	$LOADZIP = $arParams["LOADZIP"];
	$bSync = $arParams["SYNC"] == "Y";

	if(isset($arParams['TMP_PATH']))
		$sTmpFilePath = $arParams['TMP_PATH'];
	else
		$sTmpFilePath = CTempFile::GetDirectoryName(12, 'sale');


	if ($CSVFILE !== '' && !in_array($CSVFILE, getAllowedFiles(), true))
	{
		//echo GetMessage('SL_IMPORT_ERROR_FILES');
		$arReturn['ERROR'] = GetMessage('SL_IMPORT_ERROR_FILES');
	}
	else
	{
		if ($STEP == 1 && $CSVFILE == '')
		{
			if ($LOADZIP == 'Y') $STEP = 2;
			else $STEP = 3;
		}

		switch($STEP)
		{
			case 0:
				$arReturn['MESSAGE'] = GetMessage('WSL_IMPORT_FILES_LOADING');
				$arReturn['STEP'] = 1;
			break;

			case 1:

				$time_limit = ini_get('max_execution_time');
				if ($time_limit < LOC_STEP_LENGTH) set_time_limit(LOC_STEP_LENGTH + 5);

				$start_time = time();
				$finish_time = $start_time + LOC_STEP_LENGTH;

				$file_url = $sTmpFilePath.$CSVFILE;

				if (!file_exists($file_url))
				{
					$arReturn['ERROR'] = GetMessage('SL_IMPORT_ERROR_NO_LOC_FILE');
					break;
				}

				$bFinish = true;

				$arSysLangs = Array();
				$db_lang = CLangAdmin::GetList("sort", "asc", array("ACTIVE" => "Y"));
				while ($arLang = $db_lang->Fetch())
				{
					$arSysLangs[$arLang["LID"]] = $arLang["LID"];
				}

				$arLocations = array();

				if (!$bSync)
				{
					if (!is_set($_SESSION["LOC_POS"]))
					{
						CSaleLocation::DeleteAll();
					}
				}
				else
				{
					$dbLocations = CSaleLocation::GetList(
														array(),
														array(),
														false,
														false,
														array("ID", "COUNTRY_ID", "REGION_ID", "CITY_ID"));

					while ($arLoc = $dbLocations->Fetch())
					{
						$arLocations[$arLoc["ID"]] = $arLoc;
					}
				}

				if (empty($arLocations))
				{
					$bSync = false;
				}

				include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/csv_data.php");

				$strWarning = '';

				$csvFile = new CCSVData();
				$csvFile->LoadFile($file_url);
				$csvFile->SetFieldsType("R");
				$csvFile->SetFirstHeader(false);
				$csvFile->SetDelimiter(",");

				$arRes = $csvFile->Fetch();
				if (!is_array($arRes) || count($arRes)<=0 || mb_strlen($arRes[0]) != 2)
				{
					$strWarning .= GetMessage('SL_IMPORT_ERROR_WRONG_LOC_FILE')."<br />";
				}

				if ($strWarning == '')
				{
					$DefLang = $arRes[0];
					if (!in_array($DefLang, $arSysLangs))
					{
						$strWarning .= GetMessage('SL_IMPORT_ERROR_NO_LANG')."<br />";
					}
				}

				if ($strWarning <> '')
				{
					$arReturn['ERROR'] = $strWarning."<br />";
					break;
				}


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

							$bFinish = false;

							//echo "<script>Import(1, {AMOUNT:".CUtil::JSEscape($amount).",POS:".CUtil::JSEscape($cur_step)."})</script>";

							$arReturn['STEP'] = 1;
							$arReturn['AMOUNT'] = $amount;
							$arReturn['POS'] = $cur_step;
							break;
						}
					}
				}


				if ($bFinish)
				{
					unset($_SESSION["LOC_POS"]);

					$strOK = GetMessage('SL_IMPORT_LOC_STATS').'<br />';
					$strOK = str_replace('#NUMCOUNTRIES#', intval($numCountries), $strOK);
					$strOK = str_replace('#NUMREGIONES#', intval($numRegiones), $strOK);
					$strOK = str_replace('#NUMCITIES#', intval($numCities), $strOK);
					$strOK = str_replace('#NUMLOCATIONS#', intval($numLocations), $strOK);

					$arReturn['MESSAGE'] = $strOK;
					$arReturn['STEP'] = $LOADZIP == "Y" ? 2 : 3;
					//echo '<script>Import('.($LOADZIP == "Y" ? 2 : 3).')</script>';
				}

			break;

			case 2:
				$time_limit = ini_get('max_execution_time');
				if ($time_limit < ZIP_STEP_LENGTH) set_time_limit(ZIP_STEP_LENGTH + 5);

				$start_time = time();
				$finish_time = $start_time + ZIP_STEP_LENGTH;

				if ($LOADZIP == "Y" && file_exists($sTmpFilePath.DLZIPFILE))
				{
					$rsLocations = CSaleLocation::GetList(
													array(),
													array("LID" => 'ru'),
													false,
													false,
													array("ID", "CITY_NAME_LANG", "REGION_NAME_LANG"));
					$arLocationMap = array();
					while ($arLocation = $rsLocations->Fetch())
					{
						if($arLocation["REGION_NAME_LANG"] <> '')
						{
							if ($arLocation["CITY_NAME_LANG"] <> '')
								$arLocationMap[$arLocation["CITY_NAME_LANG"]][$arLocation["REGION_NAME_LANG"]] = $arLocation["ID"];
						}
						else
							$arLocationMap[$arLocation["CITY_NAME_LANG"]] = $arLocation["ID"];
					}

					$DB->StartTransaction();

					include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/csv_data.php");

					$csvFile = new CCSVData();
					$csvFile->LoadFile($sTmpFilePath.DLZIPFILE);
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

								$arReturn['STEP'] = 2;
								$arReturn['AMOUNT'] = $amount;
								$arReturn['POS'] = $cur_step;
								break;
							}
						}
					}

					$DB->Commit();

					if ($bFinish)
					{
						unset($_SESSION["ZIP_POS"]);

						$numCity = CSaleLocation::_GetZIPImportStats();

						$strOK = GetMessage('SL_IMPORT_ZIP_STATS');
						$strOK = str_replace('#NUMZIP#', intval($numZIP), $strOK);
						$strOK = str_replace('#NUMCITIES#', intval($numCity["CITY_CNT"]), $strOK);

						$arReturn['MESSAGE'] = $strOK;
						$arReturn['STEP'] = 3;
						$arReturn['PB_REMOVE'] = true;
						break;
					}
				}
				else
				{
					$arReturn['ERROR'] = GetMessage('SL_IMPORT_ERROR_NO_ZIP_FILE').'<br>';
					$arReturn['STEP'] = 3;
					break;
				}

			break;

			case 3:
				$arReturn['COMPLETE'] = true;
			break;
		}
	}
	return $arReturn;
}

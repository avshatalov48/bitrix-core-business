<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$instance = \Bitrix\Main\Application::getInstance();
$context = $instance->getContext();
$request = $context->getRequest();

if(!is_array($arParams["GROUP_PERMISSIONS"]))
	$arParams["GROUP_PERMISSIONS"] = array(1);
if(empty($arParams["SITE_LIST"]))
	$arParams["SITE_LIST"] = "";

$arParams["USE_ZIP"] = $arParams["USE_ZIP"]!="N";
$arParams["EXPORT_PAYED_ORDERS"] = (($arParams["EXPORT_PAYED_ORDERS"]=="Y")? true : false);
$arParams["EXPORT_ALLOW_DELIVERY_ORDERS"] = (($arParams["EXPORT_ALLOW_DELIVERY_ORDERS"]=="Y") ? true : false);
$arParams["CHANGE_STATUS_FROM_1C"] = (($arParams["CHANGE_STATUS_FROM_1C"]=="Y") ? true : false);

$arParams["REPLACE_CURRENCY"] = htmlspecialcharsEx($arParams["REPLACE_CURRENCY"]);
if ($arParams["USE_TEMP_DIR"] !== "Y" && $arParams["USE_TEMP_DIR"] !== "N")
	$arParams["USE_TEMP_DIR"] = (defined("BX24_HOST_NAME")? "Y": "N");
if(!isset($arParams["INTERVAL"]))
	$arParams["INTERVAL"] = COption::GetOptionString("sale", "1C_INTERVAL", 30);
else
	$arParams["INTERVAL"] = intval($arParams["INTERVAL"]);

$arParams["FILE_SIZE_LIMIT"] = intval($arParams["FILE_SIZE_LIMIT"]);
if($arParams["FILE_SIZE_LIMIT"] < 1)
	$arParams["FILE_SIZE_LIMIT"] = 200*1024; //200KB

if($arParams["INTERVAL"] <= 0)
	@set_time_limit(0);

$bUSER_HAVE_ACCESS = false;
if(isset($GLOBALS["USER"]) && is_object($GLOBALS["USER"]))
{
	$arUserGroupArray = $GLOBALS["USER"]->GetUserGroupArray();
	foreach($arParams["GROUP_PERMISSIONS"] as $PERM)
	{
		if(in_array($PERM, $arUserGroupArray))
		{
			$bUSER_HAVE_ACCESS = true;
			break;
		}
	}
}

$bDesignMode = $GLOBALS["APPLICATION"]->GetShowIncludeAreas() && is_object($GLOBALS["USER"]) && $GLOBALS["USER"]->IsAdmin();
if(!$bDesignMode)
{
	$APPLICATION->RestartBuffer();
	header("Pragma: no-cache");
}

$bCrmMode = isset($arParams["CRM_MODE"]) && ($arParams["CRM_MODE"] == "Y");
$bExportFromCrm = isset($arParams["EXPORT_FROM_CRM"]) && ($arParams["EXPORT_FROM_CRM"] == "Y");

$gzCompressionSupported = (($_GET["mode"] == "query" || $_POST["mode"] == "query") && $bCrmMode
	&& isset($arParams["GZ_COMPRESSION_SUPPORTED"]) && $arParams["GZ_COMPRESSION_SUPPORTED"] && function_exists("gzcompress"));

$lid = ($bCrmMode && !empty($arParams["LID"]) ? $arParams["LID"] : null);

ob_start();

$curPage = mb_substr($APPLICATION->GetCurPage(), 0, 22);

if(empty($_REQUEST["sessid"]) && !empty($_POST["sessid"]))
{
	$_REQUEST["sessid"] = $_POST["sessid"];
}

if($_REQUEST["sessid"] <> '' && COption::GetOptionString("sale", "secure_1c_exchange", "N") != "Y")
{
	COption::SetOptionString("sale", "secure_1c_exchange", "Y");
}
if(!(CModule::IncludeModule('sale') && CModule::IncludeModule('catalog')))
{
	echo "failure\n".GetMessage("CC_BSC1_ERROR_MODULE");
	return;
}

if($_GET["mode"] == "checkauth" && $USER->IsAuthorized())
{
	if(
		(COption::GetOptionString("main", "use_session_id_ttl", "N") == "Y")
		&& (COption::GetOptionInt("main", "session_id_ttl", 0) > 0)
		&& !defined("BX_SESSION_ID_CHANGE")
	)
	{
		echo "failure\n",GetMessage("CC_BSC1_ERROR_SESSION_ID_CHANGE");
	}
	else
	{
		echo "success\n";
		echo session_name()."\n";
		echo session_id() ."\n";
		echo bitrix_sessid_get()."\n";

		COption::SetOptionString("sale", "export_session_name_".$curPage, session_name());
		COption::SetOptionString("sale", "export_session_id_".$curPage, session_id());
	}
}
elseif(!$USER->IsAuthorized())
{
	echo "failure\n".GetMessage("CC_BSC1_ERROR_AUTHORIZE");
}
elseif(COption::GetOptionString("sale", "secure_1c_exchange", "N") == "Y" && !check_bitrix_sessid())
{
	echo "failure\n".GetMessage("CC_BSC1_ERROR_SOURCE_CHECK");
}
elseif(!$bUSER_HAVE_ACCESS)
{
	echo "failure\n".GetMessage("CC_BSC1_PERMISSION_DENIED");
}
else
{
	if ($arParams["USE_TEMP_DIR"] === "Y")
		$DIR_NAME = $_SESSION["BX_CML2_EXPORT"]["TEMP_DIR"];
	else
		$DIR_NAME = $_SERVER["DOCUMENT_ROOT"]."/".COption::GetOptionString("main", "upload_dir", "upload")."/1c_exchange/";

	$ABS_FILE_NAME = false;
	$WORK_DIR_NAME = false;

	if(isset($_GET["filename"]) && $_GET["filename"] <> '' && $DIR_NAME <> '')
	{
		//This check for 1c server on linux
		$filename = preg_replace("#^(/tmp/|upload/1c/webdata)#", "", $_GET["filename"]);
		$filename = trim(str_replace("\\", "/", trim($filename)), "/");

		$io = CBXVirtualIo::GetInstance();
		$bBadFile = HasScriptExtension($filename)
			|| IsFileUnsafe($filename)
			|| !$io->ValidatePathString("/".$filename)
		;

		if(!$bBadFile)
		{
			$filename = trim(str_replace("\\", "/", trim($filename)), "/");

			$FILE_NAME = rel2abs($DIR_NAME, "/".$filename);
			if((mb_strlen($FILE_NAME) > 1) && ($FILE_NAME === "/".$filename))
			{
				$ABS_FILE_NAME = $DIR_NAME.$FILE_NAME;
				$WORK_DIR_NAME = mb_substr($ABS_FILE_NAME, 0, mb_strrpos($ABS_FILE_NAME, "/") + 1);
			}
		}
	}


	if($_GET["mode"]=="init")
	{
		if ($arParams["USE_TEMP_DIR"] === "Y")
		{
			$DIR_NAME = CTempFile::GetDirectoryName(6, "1c_exchange");
		}
		else
		{
			$DIR_NAME = $_SERVER["DOCUMENT_ROOT"]."/".COption::GetOptionString("main", "upload_dir", "upload")."/1c_exchange/";
			DeleteDirFilesEx(mb_substr($DIR_NAME, mb_strlen($_SERVER["DOCUMENT_ROOT"])));
		}

		CheckDirPath($DIR_NAME);
		if(!is_dir($DIR_NAME))
		{
			echo "failure\n".GetMessage("CC_BSC1_ERROR_INIT");
		}
		else
		{
			$ht_name = $DIR_NAME.".htaccess";
			if(!file_exists($ht_name))
			{
				$fp = fopen($ht_name, "w");
				if($fp)
				{
					fwrite($fp, "Deny from All");
					fclose($fp);
					@chmod($ht_name, BX_FILE_PERMISSIONS);
				}
			}

			$_SESSION["BX_CML2_EXPORT"]["zip"] = $arParams["USE_ZIP"] && function_exists("zip_open");
			if($arParams["USE_TEMP_DIR"] === "Y")
				$_SESSION["BX_CML2_EXPORT"]["TEMP_DIR"] = $DIR_NAME;

			echo "zip=".($_SESSION["BX_CML2_EXPORT"]["zip"]? "yes": "no")."\n";
			echo "file_limit=".$arParams["FILE_SIZE_LIMIT"]."\n";

			if($_GET["version"] <> '')
			{
				echo bitrix_sessid_get()."\n";
				echo "version=2.09";
				$_SESSION["BX_CML2_EXPORT"]["version"] = $_GET["version"];
			}
		}
		$_SESSION["BX_CML2_EXPORT"]["cmlVersion"] = doubleval($request->get('cmlVersion'));
	}
	elseif($_GET["mode"] == "query" || $_POST["mode"] == "query")
	{
	    \Bitrix\Sale\Exchange\ManagerExport::deleteLoggingDate();

		$arFilter = Array();
		$nTopCount = false;

		if(\Bitrix\Sale\Exchange\ManagerExport::isCRMCompatibility())
		{
			$export = new ExportOneCCRM();
		}
		else
		{
			$export =  new CSaleExport();
		}

		if (!$bCrmMode)
		{
			$arFilter["UPDATED_1C"] = "N";
			$arFilter["!EXTERNAL_ORDER"] = "Y";
			if($arParams["EXPORT_PAYED_ORDERS"])
				$arFilter["PAYED"] = "Y";
			if($arParams["EXPORT_ALLOW_DELIVERY_ORDERS"])
				$arFilter["ALLOW_DELIVERY"] = "Y";
			if($arParams["EXPORT_FINAL_ORDERS"] <> '')
			{
				$bNextExport = false;
				$arStatusToExport = [];
				if (\Bitrix\Sale\Exchange\ManagerExport::isCRMCompatibility())
				{
					$statusList = CCrmStatus::GetStatus('INVOICE_STATUS');
					foreach ($statusList as $statusId => $status)
					{
						if($status['STATUS_ID'] == $arParams["EXPORT_FINAL_ORDERS"])
						{
							$bNextExport = true;
						}

						if($bNextExport)
						{
							$arStatusToExport[] = $status['STATUS_ID'];
						}
					}
				}
				else
				{
					$dbStatus = CSaleStatus::GetList(Array("SORT" => "ASC"), Array("LID" => LANGUAGE_ID));
					while ($arStatus = $dbStatus->Fetch())
					{
						if($arStatus["ID"] == $arParams["EXPORT_FINAL_ORDERS"])
							$bNextExport = true;
						if($bNextExport)
							$arStatusToExport[] = $arStatus["ID"];
					}
				}

				$arFilter["STATUS_ID"] = $arStatusToExport;
			}
			if($arParams["SITE_LIST"])
				$arFilter["LID"] = $arParams["SITE_LIST"];

			if(COption::GetOptionString("sale", "last_export_time_committed_".$curPage, "") <> '')
				$arFilter[">=DATE_UPDATE"] = ConvertTimeStamp(COption::GetOptionString("sale", "last_export_time_committed_".$curPage, ""), "FULL");
			COption::SetOptionString("sale", "last_export_time_".$curPage, time());
		}
		else
		{
			$arParams["ORDER_ID"] = intval($arParams["ORDER_ID"]);
			if ($arParams["ORDER_ID"] > 0)
				$arFilter["ID"] = $arParams["ORDER_ID"];

			$arParams["MODIFICATION_LABEL"] = intval($arParams["MODIFICATION_LABEL"]);
			if ($arParams["MODIFICATION_LABEL"] > 0)
			{
				$arParams["MODIFICATION_LABEL"] += ($arParams["ZZZ"] - date("Z"));

				$arFilter[">DATE_UPDATE"] = ConvertTimeStamp($arParams["MODIFICATION_LABEL"], "FULL");
			}

			$arParams["IMPORT_SIZE"] = intval($arParams["IMPORT_SIZE"]);
			if ($arParams["IMPORT_SIZE"] > 0)
				$nTopCount = $arParams["IMPORT_SIZE"];

			$arParams["REPLACE_CURRENCY"] = '';
			if($_SESSION["BX_CML2_EXPORT"]["version"] <> '' && intval($arParams["INTERVAL"]) <= 0)
				$arParams["INTERVAL"] = 30;

			$export::setLanguage('en');
		}

		if($_SESSION["BX_CML2_EXPORT"]["version"] == '')
			$arParams["INTERVAL"] = 0;

		$options = array();

		if ($bExportFromCrm)
		{
			$options['EXPORT_FROM_CRM'] = "Y";
		}

		if ($lid)
		{
			$options['LID'] = $lid;
		}

		CTimeZone::Disable();

		if($_SESSION["BX_CML2_EXPORT"]["cmlVersion"] >= doubleval(\Bitrix\Sale\Exchange\ExportOneCBase::SHEM_VERSION_2_10))
		{
			//region schema Documents or Document.Subordinate
		    $r = $export->export(array(
					'filter'=>$arFilter,
					'limit'=>$arParams["INTERVAL"])
			);
		    echo $r->getData()[0];
			//endregion
        }
        else
        {
            $arResultStat = $export::ExportOrders2Xml(
                $arFilter, $nTopCount, $arParams["REPLACE_CURRENCY"], $bCrmMode, $arParams["INTERVAL"],
                $_SESSION["BX_CML2_EXPORT"]["version"], $options
            );
        }

		CTimeZone::Enable();

		if (!$bCrmMode)
		{
			$time = intval($_SESSION["BX_CML2_EXPORT"][$export::getOrderPrefix()]);
			if($time>0)
				COption::SetOptionString("sale", "last_export_time_".$curPage, $time);
		}
		else
		{
			$crmSiteUrl = "";
			if(isset($_POST["CRM_SITE_URL"]) && !empty($_POST["CRM_SITE_URL"]))
			{
				$crmSiteUrl = $_POST["CRM_SITE_URL"];
			}
			elseif(isset($_GET["CRM_SITE_URL"]) && !empty($_GET["CRM_SITE_URL"]))
			{
				$crmSiteUrl = $_GET["CRM_SITE_URL"];
			}
			if($crmSiteUrl <> '')
			{
				$opt = COption::GetOptionString("sale", "~crm_integration", "");
				$opt = unserialize($opt, ['allowed_classes' => false]);
				if (!is_array($opt))
					$opt = array();
				if (!array_key_exists($crmSiteUrl, $opt))
					$opt[$crmSiteUrl] = array();

				$opt[$crmSiteUrl]["DATE"] = time();
				if (intval($arResultStat["ORDERS"]) > 0)
				{
					$opt[$crmSiteUrl]["TOTAL_ORDERS"] = $opt[$crmSiteUrl]["TOTAL_ORDERS"] + $arResultStat["ORDERS"];
					$opt[$crmSiteUrl]["TOTAL_CONTACTS"] = $opt[$crmSiteUrl]["TOTAL_CONTACTS"] + $arResultStat["CONTACTS"];
					$opt[$crmSiteUrl]["TOTAL_COMPANIES"] = $opt[$crmSiteUrl]["TOTAL_COMPANIES"] + $arResultStat["COMPANIES"];
					$opt[$crmSiteUrl]["NUM_ORDERS"] = $arResultStat["ORDERS"];
					$opt[$crmSiteUrl]["NUM_CONTACTS"] = $arResultStat["CONTACTS"];
					$opt[$crmSiteUrl]["NUM_COMPANIES"] = $arResultStat["COMPANIES"];
				}
				COption::SetOptionString("sale", "~crm_integration", serialize($opt));
			}
		}
	}
	elseif($_GET["mode"]=="success")
	{
		if($_COOKIE[COption::GetOptionString("sale", "export_session_name_".$curPage, "")] == COption::GetOptionString("sale", "export_session_id_".$curPage, ""))
		{
			$_SESSION["BX_CML2_EXPORT"][CSaleExport::getOrderPrefix()] = 0;

			COption::SetOptionString("sale", "last_export_time_committed_".$curPage, COption::GetOptionString("sale", "last_export_time_".$curPage, ""));
			global $CACHE_MANAGER;
			$CACHE_MANAGER->Clean("sale_orders"); // for real-time orders
			echo "success\n";
		}
		else
			echo "error\n";
	}
	elseif($_GET["mode"] == "file" && $_SESSION["BX_CML2_EXPORT"]["version"] == '')// old version
	{
		if($ABS_FILE_NAME)
		{
			if(function_exists("file_get_contents"))
				$DATA = file_get_contents("php://input");
			elseif(isset($GLOBALS["HTTP_RAW_POST_DATA"]))
				$DATA = &$GLOBALS["HTTP_RAW_POST_DATA"];
			else
				$DATA = false;

			if(isset($DATA) && $DATA !== false)
			{
				CheckDirPath($ABS_FILE_NAME);
				if($fp = fopen($ABS_FILE_NAME, "ab"))
				{
					$result = fwrite($fp, $DATA);
					if($result === strlen($DATA))
					{
						if($_SESSION["BX_CML2_EXPORT"]["zip"])
							$_SESSION["BX_CML2_EXPORT"]["zip"] = $ABS_FILE_NAME;
						//echo "success\n";
					}
					else
					{
						echo "failure\n".GetMessage("CC_BSC1_ERROR_FILE_WRITE", array("#FILE_NAME#"=>$FILE_NAME));
					}
					fclose($fp);
				}
				else
				{
					echo "failure\n".GetMessage("CC_BSC1_ERROR_FILE_OPEN", array("#FILE_NAME#"=>$FILE_NAME));
				}
			}
			else
			{
				echo "failure\n".GetMessage("CC_BSC1_ERROR_HTTP_READ");
			}
		}

		if($_SESSION["BX_CML2_EXPORT"]["zip"] <> '')
		{
			$file_name = $_SESSION["BX_CML2_EXPORT"]["zip"];
			$dir_name = mb_substr($file_name, 0, mb_strrpos($file_name, "/") + 1);

			if (mb_strlen($dir_name) <= mb_strlen($_SERVER["DOCUMENT_ROOT"]))
			{
				return false;
			}

			/** @var IBXArchive $oArchiver */
			$oArchiver = CBXArchive::GetArchive($file_name, "ZIP");
			if ($oArchiver instanceof IBXArchive)
			{
				$rArchiver = $oArchiver->Unpack($dir_name);

				if ($rArchiver)
				{
					$ABS_FILE_NAME = current($rArchiver)['filename'];
				}
				else
				{
					echo "error\n".GetMessage("CC_BSC1_UNZIP_ERROR");
				}
			}
			else
			{
				return false;
			}
		}

		$new_file_name = $ABS_FILE_NAME;

		if(filesize($new_file_name)>0)
		{
			$position = false;
			$loader = new CSaleOrderLoader;
			$loader->arParams = $arParams;

			$o = new CXMLFileStream;
			$o->registerElementHandler("/".GetMessage("CC_BSC1_COM_INFO"), array($loader, "elementHandler"));
			$o->registerNodeHandler("/".GetMessage("CC_BSC1_COM_INFO")."/".GetMessage("CC_BSC1_DOCUMENT"), function (CDataXML $xmlObject) use ($o, $loader)
			{
				$loader->nodeHandlerDefaultModuleOneC($xmlObject);
			});

			$o->setPosition(false);

			if ($o->openFile($new_file_name))
				while($o->findNext());

			echo "success";
			if($loader->strError <> '')
				echo $loader->strError;
			echo "\n";
		}
		else
		{
			echo "failure\n".GetMessage("CC_BSC1_EMPTY_CML");
		}

	}
	elseif($_GET["mode"] == "file")// new version
	{
		if($ABS_FILE_NAME)
		{
			if(function_exists("file_get_contents"))
				$DATA = file_get_contents("php://input");
			elseif(isset($GLOBALS["HTTP_RAW_POST_DATA"]))
				$DATA = &$GLOBALS["HTTP_RAW_POST_DATA"];
			else
				$DATA = false;

			if(isset($DATA) && $DATA !== false)
			{
				CheckDirPath($ABS_FILE_NAME);
				if($fp = fopen($ABS_FILE_NAME, "ab"))
				{
					$result = fwrite($fp, $DATA);
					if($result === strlen($DATA))
					{
						if($_SESSION["BX_CML2_EXPORT"]["zip"])
							$_SESSION["BX_CML2_EXPORT"]["zip"] = $ABS_FILE_NAME;
						echo "success\n";
					}
					else
					{
						echo "failure\n".GetMessage("CC_BSC1_ERROR_FILE_WRITE", array("#FILE_NAME#"=>$FILE_NAME));
					}
					fclose($fp);
				}
				else
				{
					echo "failure\n".GetMessage("CC_BSC1_ERROR_FILE_OPEN", array("#FILE_NAME#"=>$FILE_NAME));
				}
			}
			else
			{
				echo "failure\n".GetMessage("CC_BSC1_ERROR_HTTP_READ");
			}
		}
	}
	elseif($_GET["mode"] == "import" && $_SESSION["BX_CML2_EXPORT"]["zip"] && mb_strlen($_SESSION["BX_CML2_EXPORT"]["zip"]) > 1)
	{
		if(!array_key_exists("last_zip_entry", $_SESSION["BX_CML2_EXPORT"]))
			$_SESSION["BX_CML2_EXPORT"]["last_zip_entry"] = -1;

		$result = CSaleExport::safetyUnZip($_SESSION["BX_CML2_EXPORT"]["zip"], $_SESSION["BX_CML2_EXPORT"]["last_zip_entry"]);
		if($result===false)
		{
			echo "failure\n".GetMessage("CC_BSC1_ZIP_ERROR");
		}
		elseif($result===true)
		{
			$_SESSION["BX_CML2_EXPORT"]["zip"] = false;
			echo "progress\n".GetMessage("CC_BSC1_ZIP_DONE");

		}
		else
		{
			$_SESSION["BX_CML2_EXPORT"]["last_zip_entry"] = $result;
			echo "progress\n".GetMessage("CC_BSC1_ZIP_PROGRESS");
		}
	}
	elseif($_GET["mode"] == "import" && $ABS_FILE_NAME)
	{
		if(file_exists($ABS_FILE_NAME) && filesize($ABS_FILE_NAME)>0)
		{
		    \Bitrix\Sale\Exchange\ManagerImport::deleteLoggingDate();

			if(!is_array($_SESSION["BX_CML2_EXPORT"]) || !array_key_exists("last_xml_entry", $_SESSION["BX_CML2_EXPORT"]))
				$_SESSION["BX_CML2_EXPORT"]["last_xml_entry"] = "";

			$position = false;
			$startTime = time();

			$loader = new CSaleOrderLoader;
			$loader->arParams = $arParams;
			$loader->bNewVersion = true;
			$loader->crmCompatibleMode = $bExportFromCrm;
			$startTime = time();

			$o = new CXMLFileStream;

			$o->registerElementHandler("/".GetMessage("CC_BSC1_COM_INFO"), array($loader, "elementHandler"));

			//region schema Documents or Document.Subordinate
			if($_SESSION["BX_CML2_EXPORT"]["cmlVersion"] >= doubleval(\Bitrix\Sale\Exchange\ExportOneCBase::SHEM_VERSION_2_10))
			{
				$o->registerNodeHandler("/".GetMessage("CC_BSC1_COM_INFO")."/".GetMessage("CC_BSC1_DOCUMENT"), function (CDataXML $xmlObject) use ($o, $loader)
				{
					\Bitrix\Sale\Exchange\ImportOneCSubordinateSale::configuration();
					$loader->importer = \Bitrix\Sale\Exchange\ImportOneCSubordinateSale::getInstance();
					$loader->nodeHandler($xmlObject, $o);
				});
			}
			else
			{
				$o->registerNodeHandler("/".GetMessage("CC_BSC1_COM_INFO")."/".GetMessage("CC_BSC1_DOCUMENT"), function (CDataXML $xmlObject) use ($o, $loader)
				{
					if(\Bitrix\Sale\Exchange\ManagerExport::isCRMCompatibility())
					{
						$loader->nodeHandlerDefaultModuleOneCCRM($xmlObject);
					}
					else
                    {
						$loader->nodeHandlerDefaultModuleOneC($xmlObject);
                    }
				});
			}
			//endregion
			//region schema Contragents
			if(\Bitrix\Sale\Exchange\ManagerExport::isSaleB24Mode() || \Bitrix\Sale\Exchange\ManagerExport::isB24SaleMode())
            {
				CModule::IncludeModule('CRM');
				$o->registerNodeHandler("/".GetMessage("CC_BSC1_COM_INFO")."/".GetMessage("CC_BSC1_AGENTS")."/".GetMessage("CC_BSC1_AGENT"), function (CDataXML $xmlObject) use ($o, $loader)
				{
					\Bitrix\Sale\Exchange\ImportOneCContragentCRM::configuration();
					$loader->importer = new \Bitrix\Sale\Exchange\ImportOneCContragentCRM();
					$loader->nodeHandler($xmlObject, $o);
				});
            }
            else
            {
				$o->registerNodeHandler("/".GetMessage("CC_BSC1_COM_INFO")."/".GetMessage("CC_BSC1_AGENTS")."/".GetMessage("CC_BSC1_AGENT"), function (CDataXML $xmlObject) use ($o, $loader)
				{
					\Bitrix\Sale\Exchange\ImportOneCContragent::configuration();
					$loader->importer = new \Bitrix\Sale\Exchange\ImportOneCContragent();
					$loader->nodeHandler($xmlObject, $o);
				});
            }
			//endregion
			//region schema Package.CRM or Package.Sale
            /*
             * если устнавливется дистр Б24, то обмен идет счетами
             * если устнавливется дистр БУС, то обмен идет заказами
             * если на БУС вводиться ключ редакции Б24+БУС, то выполняется обмен заказми (т.к. модуль CRM не установлен)
             * если на Б24 вводиться ключ редакции Б24+БУС, то выполняется обмен счетами (т.к. модуль CRM установлен)
             * если на БУС запускается мастер +Б24, анализирем опцию IS_SALE_CRM_SITE_MASTER_FINISH и продолжаем ипортировать заказы
             * если на Б24 запускается мастер +БУС, анализирем опцию IS_SALE_BSM_SITE_MASTER_FINISH и продолжаем ипортировать счета,
             *     а для импорта заказов используем отдельный модуль на rest
             * */
			//B24 -> +BUS.wizard
            if(\Bitrix\Sale\Exchange\ManagerExport::isB24SaleMode())
            {
				CModule::IncludeModule('CRM');
				$o->registerNodeHandler("/".GetMessage("CC_BSC1_COM_INFO")."/".GetMessage("CC_BSC1_CONTAINER"), function (CDataXML $xmlObject) use ($o, $loader)
				{
					\Bitrix\Sale\Exchange\ImportOneCPackageCRM::configuration();
					$loader->importer = \Bitrix\Sale\Exchange\ImportOneCPackageCRM::getInstance();
					$loader->nodeHandler($xmlObject, $o);
				});
            }
			//BUS -> +B24.wizard
            elseif(\Bitrix\Sale\Exchange\ManagerExport::isSaleB24Mode())
			{
				$o->registerNodeHandler("/".GetMessage("CC_BSC1_COM_INFO")."/".GetMessage("CC_BSC1_CONTAINER"), function (CDataXML $xmlObject) use ($o, $loader)
				{
					\Bitrix\Sale\Exchange\ImportOneCPackageSale::configuration();
					$loader->importer = \Bitrix\Sale\Exchange\ImportOneCPackageSale::getInstance();
					$loader->nodeHandler($xmlObject, $o);
				});
			}
			elseif(\Bitrix\Sale\Exchange\ManagerExport::isB24Mode())
			{
				$o->registerNodeHandler("/".GetMessage("CC_BSC1_COM_INFO")."/".GetMessage("CC_BSC1_CONTAINER"), function (CDataXML $xmlObject) use ($o, $loader)
				{
					\Bitrix\Sale\Exchange\ImportOneCPackageCRM::configuration();
					$loader->importer = \Bitrix\Sale\Exchange\ImportOneCPackageCRM::getInstance();
					$loader->nodeHandler($xmlObject, $o);
				});
			}
			else
			{
				$o->registerNodeHandler("/".GetMessage("CC_BSC1_COM_INFO")."/".GetMessage("CC_BSC1_CONTAINER"), function (CDataXML $xmlObject) use ($o, $loader)
				{
					\Bitrix\Sale\Exchange\ImportOneCPackageSale::configuration();
					$loader->importer = \Bitrix\Sale\Exchange\ImportOneCPackageSale::getInstance();
					$loader->nodeHandler($xmlObject, $o);
				});
			}
			//endregion

			$o->setPosition($_SESSION["BX_CML2_EXPORT"]["last_xml_entry"]);
			if ($o->openFile($ABS_FILE_NAME))
			{
				while($o->findNext())
				{
					if($arParams["INTERVAL"] > 0)
					{
						$_SESSION["BX_CML2_EXPORT"]["last_xml_entry"] = $o->getPosition();
						if(time()-$startTime > $arParams["INTERVAL"])
						{
							break;
						}
					}
				}
			}

			if(!$o->endOfFile())
				echo "progress";
			else
			{
				$_SESSION["BX_CML2_EXPORT"]["last_xml_entry"] = "";
				$loader->clearSessionData();
				echo "success";
			}
			if($loader->strError <> '')
				echo $loader->strError;
			echo "\n";
		}
		else
		{
			echo "failure\n".GetMessage("CC_BSC1_EMPTY_CML");
		}
	}
	elseif($_GET["mode"] == "info")
	{
		?><<?="?"?>xml version="1.0" encoding="windows-1251"<?="?"?>>
		<<?=GetMessage("CC_BSC1_DI_GENERAL")?>>
			<<?=GetMessage("CC_BSC1_DI_STATUSES")?>>
			<?
			if(\Bitrix\Sale\Exchange\ManagerExport::isCRMCompatibility())
			{
				$dbStatus = \Bitrix\Crm\Invoice\InvoiceStatus::getList(array('order'=>array("SORT" => "ASC")));
                while ($arStatus = $dbStatus->Fetch())
				{
					?>
					<<?=GetMessage("CC_BSC1_DI_ELEMENT")?>>
					<<?=GetMessage("CC_BSC1_DI_ID")?>><?=$arStatus["STATUS_ID"]?></<?=GetMessage("CC_BSC1_DI_ID")?>>
					<<?=GetMessage("CC_BSC1_DI_NAME")?>><?=htmlspecialcharsbx($arStatus["NAME"])?></<?=GetMessage("CC_BSC1_DI_NAME")?>>
					</<?=GetMessage("CC_BSC1_DI_ELEMENT")?>>
					<?
                }
			}
			else
			{
				$dbStatus = CSaleStatus::GetList(array("SORT" => "ASC"), array("LID" => LANGUAGE_ID), false, false, array("ID", "NAME"));
				while ($arStatus = $dbStatus->Fetch())
				{
					?>
					<<?=GetMessage("CC_BSC1_DI_ELEMENT")?>>
					<<?=GetMessage("CC_BSC1_DI_ID")?>><?=$arStatus["ID"]?></<?=GetMessage("CC_BSC1_DI_ID")?>>
					<<?=GetMessage("CC_BSC1_DI_NAME")?>><?=htmlspecialcharsbx($arStatus["NAME"])?></<?=GetMessage("CC_BSC1_DI_NAME")?>>
					</<?=GetMessage("CC_BSC1_DI_ELEMENT")?>>
					<?
				}
			}
			?>
			</<?=GetMessage("CC_BSC1_DI_STATUSES")?>>
			<<?=GetMessage("CC_BSC1_DI_PS")?>>
			<?
			$dbPS = CSalePaySystem::GetList(array("SORT" => "ASC"), array("ACTIVE" => "Y"), false, false, array('ID', 'NAME', 'ACTIVE', 'SORT', 'DESCRIPTION', 'IS_CASH'));
			while ($arPS = $dbPS->Fetch())
			{
				if(\Bitrix\Sale\Exchange\ManagerExport::isCRMCompatibility())
				    $typeId = \Bitrix\Sale\Exchange\Entity\PaymentInvoiceBase::resolveEntityTypeIdByCodeType($arPS["IS_CASH"]);
				else
			        $typeId = \Bitrix\Sale\Exchange\Entity\PaymentImport::resolveEntityTypeIdByCodeType($arPS["IS_CASH"]);

				$typeName = \Bitrix\Sale\Exchange\EntityType::getDescription($typeId);
			    ?>
				<<?=GetMessage("CC_BSC1_DI_ELEMENT")?>>
					<<?=GetMessage("CC_BSC1_DI_ID")?>><?=$arPS["ID"]?></<?=GetMessage("CC_BSC1_DI_ID")?>>
					<<?=GetMessage("CC_BSC1_DI_NAME")?>><?=htmlspecialcharsbx($arPS["NAME"])?></<?=GetMessage("CC_BSC1_DI_NAME")?>>
					<<?=GetMessage("CC_BSC1_DI_IS_CASH")?>><?=htmlspecialcharsbx($typeName)?></<?=GetMessage("CC_BSC1_DI_IS_CASH")?>>
				</<?=GetMessage("CC_BSC1_DI_ELEMENT")?>>
				<?
			}
			?>
			</<?=GetMessage("CC_BSC1_DI_PS")?>>
			<<?=GetMessage("CC_BSC1_DI_DS")?>>
            <?
            $deliveryList = \Bitrix\Sale\Delivery\Services\Manager::getActiveList(true);
            foreach($deliveryList as $delivery)
            {
                ?>
                <<?=GetMessage("CC_BSC1_DI_ELEMENT")?>>
                <<?=GetMessage("CC_BSC1_DI_ID")?>><?=$delivery["ID"]?></<?=GetMessage("CC_BSC1_DI_ID")?>>
                <<?=GetMessage("CC_BSC1_DI_NAME")?>><?=htmlspecialcharsbx($delivery["NAME"])?></<?=GetMessage("CC_BSC1_DI_NAME")?>>
                </<?=GetMessage("CC_BSC1_DI_ELEMENT")?>>
                <?
            }
            ?>
            </<?=GetMessage("CC_BSC1_DI_DS")?>>
		</<?=GetMessage("CC_BSC1_DI_GENERAL")?>><?
	}
	else
	{
		echo "failure\n".GetMessage("CC_BSC1_ERROR_UNKNOWN_COMMAND");
	}
}

$contents = ob_get_contents();
ob_end_clean();

if(!$bDesignMode)
{
	if (!$bCrmMode)
	{
		if(mb_strtoupper(LANG_CHARSET) != "WINDOWS-1251")
			$contents = \Bitrix\Main\Text\Encoding::convertEncoding($contents, LANG_CHARSET, "windows-1251");
	}

	if ($gzCompressionSupported)
	{
		$contents = gzcompress($contents);

		header("Content-Type: application/octet-stream");
		header("Content-Length: " . strlen($contents));
	}
	else
	{
		$str = strlen($contents);
		if(in_array($_GET["mode"], array("query", "info")) || in_array($_POST["mode"], array("query", "info")))
		{
			header("Content-Type: application/xml; charset=windows-1251");
			header("Content-Length: ".$str);
		}
		else
		{
			header("Content-Type: text/html; charset=windows-1251");
		}
	}

	echo $contents;
	die();
}
else
{
	$this->IncludeComponentLang(".parameters.php");
	$arStatuses = Array("" => GetMessage("CP_BCI1_NO"));

	$dbStatus = CSaleStatus::GetList(Array("SORT" => "ASC"), Array("LID" => LANGUAGE_ID));
	while ($arStatus = $dbStatus->GetNext())
	{
		$arStatuses[$arStatus["ID"]] = "[".$arStatus["ID"]."] ".$arStatus["NAME"];
	}

	?><table class="data-table">
	<tr><td><?echo GetMessage("CP_BCI1_SITE_LIST")?></td><td><?echo $arParams["SITE_LIST"]?></td></tr>
	<tr><td><?echo GetMessage("CP_BCI1_EXPORT_PAYED_ORDERS")?></td><td><?echo $arParams["EXPORT_PAYED_ORDERS"]? GetMessage("MAIN_YES"): GetMessage("MAIN_NO")?></td></tr>
	<tr><td><?echo GetMessage("CP_BCI1_EXPORT_ALLOW_DELIVERY_ORDERS")?></td><td><?echo $arParams["EXPORT_ALLOW_DELIVERY_ORDERS"]? GetMessage("MAIN_YES"): GetMessage("MAIN_NO")?></td></tr>
	<tr><td><?echo GetMessage("CP_BCI1_CHANGE_STATUS_FROM_1C")?></td><td><?echo $arParams["CHANGE_STATUS_FROM_1C"]? GetMessage("MAIN_YES"): GetMessage("MAIN_NO")?></td></tr>
	<tr><td><?echo GetMessage("CP_BCI1_EXPORT_FINAL_ORDERS")?></td><td><?echo $arStatuses[$arParams["EXPORT_FINAL_ORDERS"]]?></td></tr>
	<tr><td><?echo GetMessage("CP_BCI1_FINAL_STATUS_ON_DELIVERY")?></td><td><?echo $arStatuses[$arParams["FINAL_STATUS_ON_DELIVERY"]]?></td></tr>
	<tr><td><?echo GetMessage("CP_BCI1_REPLACE_CURRENCY")?></td><td><?echo $arParams["REPLACE_CURRENCY"]?></td></tr>
	<tr><td><?echo GetMessage("CP_BCI1_USE_ZIP")?></td><td><?echo $arParams["USE_ZIP"]? GetMessage("MAIN_YES"): GetMessage("MAIN_NO")?></td></tr>
	</table>
	<?
}
?>

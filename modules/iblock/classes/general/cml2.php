<?php
use Bitrix\Main;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Loader;
use Bitrix\Iblock;
use Bitrix\Catalog;
use Bitrix\Catalog\Product;

IncludeModuleLangFile(__FILE__);

class CIBlockCMLImport
{
	public const IBLOCK_CACHE_FREEZE = 'D';
	public const IBLOCK_CACHE_FINAL = 'F';
	public const IBLOCK_CACHE_HIT = 'H';
	public const IBLOCK_CACHE_NORMAL = 'N';

	var $LAST_ERROR = "";
	/** @var bool|array */
	var $next_step = false;
	var $files_dir = false;
	var $use_offers = true;
	var $force_offers = false;
	var $use_iblock_type_id = false;
	var $use_crc = true;

	var $preview = false;
	var $detail = false;
	var $iblock_resize = false;

	/** @var CIBlockXMLFile $_xml_file */
	var $_xml_file = null;

	var $bCatalog = false;
	var $isCatalogIblock = false;
	var $PROPERTY_MAP = array();
	var $SECTION_MAP = array();
	var $PRICES_MAP = array();
	var $arProperties = array();
	var $arSectionCache = array();
	var $arElementCache = array();
	var $arEnumCache = array();
	var $arCurrencyCache = array();
	var $arTaxCache = array();
	var $mess = array();

	var $arTempFiles = array();
	var $arFileDescriptionsMap = array();
	var $arElementFilesId = array();
	var $arElementFiles = array();
	var $arLinkedProps = false;

	var $translit_on_add = false;
	var $translit_on_update = false;
	var $translit_params = array();
	var $skip_root_section = false;
	var $disable_change_price_name = false;

	protected $activeStores = array();

	protected $iblockCacheMode = self::IBLOCK_CACHE_NORMAL;

	private $bitrix24mode = null;

	protected $currentUserId = null;

	protected $currencyIncluded = null;

	protected array $productSizes = [
		'WIDTH',
		'LENGTH',
		'HEIGHT',
	];

	function InitEx(&$next_step, $params)
	{
		$defaultParams = array(
			"files_dir" => false,
			"use_crc" => true,
			"preview" => false,
			"detail" => false,
			"use_offers" => false,
			"force_offers" => false,
			"use_iblock_type_id" => false,
			"table_name" => "b_xml_tree",
			"translit_on_add" => false,
			"translit_on_update" => false,
			"translit_params" => array(
				"max_len" => 255,
				"change_case" => 'L', // 'L' - toLower, 'U' - toUpper, false - do not change
				"replace_space" => '-',
				"replace_other" => '-',
				"delete_repeat_replace" => true,
			),
			"skip_root_section" => false,
			"disable_change_price_name" => false,
			"iblock_cache_mode" => self::IBLOCK_CACHE_NORMAL
		);
		foreach($defaultParams as $key => $value)
			if(!array_key_exists($key, $params))
				$params[$key] = $value;

		$this->Init($next_step,
			$params["files_dir"],
			$params["use_crc"],
			$params["preview"],
			$params["detail"],
			$params["use_offers"],
			$params["use_iblock_type_id"],
			$params["table_name"]
		);

		if($params["translit_on_add"])
			$this->translit_on_add = $params["translit_params"];
		if($params["translit_on_update"])
			$this->translit_on_update = $params["translit_params"];
		if ($params["disable_change_price_name"])
			$this->disable_change_price_name = $params["disable_change_price_name"];

		$this->skip_root_section = ($params["skip_root_section"] === true);
		$this->force_offers = ($params["force_offers"] === true);

		if (
			$params['iblock_cache_mode'] == self::IBLOCK_CACHE_FREEZE
			|| $params['iblock_cache_mode'] == self::IBLOCK_CACHE_FINAL
			|| $params['iblock_cache_mode'] == self::IBLOCK_CACHE_HIT
			|| $params['iblock_cache_mode'] == self::IBLOCK_CACHE_NORMAL
		)
		{
			$this->iblockCacheMode = $params['iblock_cache_mode'];
		}
	}

	function Init(&$next_step, $files_dir = false, $use_crc = true, $preview = false, $detail = false, $use_offers = false, $use_iblock_type_id = false, $table_name = "b_xml_tree")
	{
		global $USER;
		$this->currentUserId = (isset($USER) && $USER instanceof CUser ? (int)$USER->GetID() : 0);

		$this->next_step = &$next_step;
		$this->files_dir = $files_dir;
		$this->use_offers = $use_offers;
		$this->use_iblock_type_id = $use_iblock_type_id;
		$this->use_crc = $use_crc;

		$this->_xml_file = new CIBlockXMLFile($table_name);

		if(!is_array($preview) && $preview)
			$this->iblock_resize = true;

		if(is_array($preview) && count($preview)==2)
			$this->preview = $preview;
		else
			$this->preview = false;

		if(is_array($detail) && count($detail)==2)
			$this->detail = $detail;
		else
			$this->detail = false;

		$this->bCatalog = Loader::includeModule('catalog');
		$this->currencyIncluded = Loader::includeModule('currency');
		$this->bitrix24mode = ModuleManager::isModuleInstalled('bitrix24');
		$this->arProperties = array();
		$this->PROPERTY_MAP = array();
		if (isset($this->next_step["IBLOCK_ID"]) && $this->next_step["IBLOCK_ID"] > 0)
		{
			$obProperty = new CIBlockProperty;
			$rsProperties = $obProperty->GetList(array(), array("IBLOCK_ID"=>$this->next_step["IBLOCK_ID"], "ACTIVE"=>"Y"));
			while($arProperty = $rsProperties->Fetch())
			{
				$this->PROPERTY_MAP[$arProperty["XML_ID"]] = $arProperty["ID"];
				$this->arProperties[$arProperty["ID"]] = $arProperty;
			}

			if ($this->bCatalog)
			{
				$catalogsIterator = \Bitrix\Catalog\CatalogIblockTable::getList(array(
					'select' => array('IBLOCK_ID'),
					'filter' => array('=IBLOCK_ID' => $this->next_step["IBLOCK_ID"])
				));
				if ($catalogData = $catalogsIterator->fetch())
					$this->isCatalogIblock = true;
				unset($catalogData, $catalogsIterator);
			}
		}

		if (isset($this->next_step['lang']) && $this->next_step['lang'])
		{
			$this->mess = Main\Localization\Loc::loadLanguageFile(__FILE__, $this->next_step['lang']);
		}

		$this->arTempFiles = array();
		$this->arLinkedProps = false;
	}

	public static function CheckIfFileIsCML($file_name)
	{
		if(file_exists($file_name) && is_file($file_name))
		{
			$fp = fopen($file_name, "rb");
			if(is_resource($fp))
			{
				$header = fread($fp, 1024);
				fclose($fp);

				if(preg_match("/<"."\\?XML[^>]{1,}encoding=[\"']([^>\"']{1,})[\"'][^>]{0,}\\?".">/i", $header, $matches))
				{
					if(strtoupper($matches[1]) !== strtoupper(LANG_CHARSET))
						$header = Main\Text\Encoding::convertEncoding($header, $matches[1], LANG_CHARSET);
				}

				foreach(array(LANGUAGE_ID, "en", "ru") as $lang)
				{
					$mess = Main\Localization\Loc::loadLanguageFile(__FILE__, $lang);
					if(strpos($header, "<".$mess["IBLOCK_XML2_COMMERCE_INFO"]) !== false)
						return $lang;
				}
			}
		}
		return false;
	}

	public function isTemporaryTablesExist(): bool
	{
		return $this->_xml_file->IsExistTemporaryTable();
	}

	public function isTemporaryTablesStructureCorrect(): bool
	{
		return $this->_xml_file->isTableStructureCorrect();
	}

	public function truncateTemporaryTables(): bool
	{
		return $this->_xml_file->truncateTemporaryTables();
	}

	function DropTemporaryTables()
	{
		return $this->_xml_file->DropTemporaryTables();
	}

	function CreateTemporaryTables()
	{
		return $this->_xml_file->CreateTemporaryTables();
	}

	function initializeTemporaryTables()
	{
		return $this->_xml_file->initializeTemporaryTables();
	}

	function IndexTemporaryTables()
	{
		return $this->_xml_file->IndexTemporaryTables();
	}

	function ReadXMLToDatabase($fp, &$NS, $time_limit=0, $read_size = 1024)
	{
		return $this->_xml_file->ReadXMLToDatabase($fp, $NS, $time_limit, $read_size);
	}

	public function GetRoot()
	{
		return $this->_xml_file->GetRoot();
	}

	function StartSession($sess_id)
	{
		return $this->_xml_file->StartSession($sess_id);
	}

	function GetSessionRoot()
	{
		return $this->_xml_file->GetSessionRoot();
	}

	function EndSession()
	{
		return $this->_xml_file->EndSession();
	}

	public function GetFilePosition()
	{
		return $this->_xml_file->GetFilePosition();
	}

	function CleanTempFiles()
	{
		foreach($this->arTempFiles as $file)
		{
			if (file_exists($file))
				unlink($file);
		}
		$this->arTempFiles = array();
	}

	function MakeFileArray($file, $fields = array())
	{
		if(is_array($file))
		{
			if(
				array_key_exists($this->mess["IBLOCK_XML2_BX_URL"], $file)
				&& $file[$this->mess["IBLOCK_XML2_BX_URL"]] <> ''
			)
			{
				if (Loader::includeModule('clouds'))
				{
					$bucket = CCloudStorage::FindBucketByFile($file[$this->mess["IBLOCK_XML2_BX_URL"]]);
					if(is_object($bucket) && $bucket->READ_ONLY === "Y")
					{
						return array(
							"name" => $file[$this->mess["IBLOCK_XML2_BX_ORIGINAL_NAME"]],
							"description" => $file[$this->mess["IBLOCK_XML2_DESCRIPTION"]],
							"tmp_name" => $file[$this->mess["IBLOCK_XML2_BX_URL"]],
							"file_size" => $file[$this->mess["IBLOCK_XML2_BX_FILE_SIZE"]],
							"width" => $file[$this->mess["IBLOCK_XML2_BX_FILE_WIDTH"]],
							"height" => $file[$this->mess["IBLOCK_XML2_BX_FILE_HEIGHT"]],
							"type" => $file[$this->mess["IBLOCK_XML2_BX_FILE_CONTENT_TYPE"]],
							"content" => "", //Fake field in order to avoid warning
							"bucket" => $bucket,
						);
					}
				}
				return CFile::MakeFileArray($this->URLEncode($file[$this->mess["IBLOCK_XML2_BX_URL"]])); //Download from the cloud
			}
		}
		else
		{
			if ($file <> '')
			{
				$external_id = md5($file);
				if (is_file($this->files_dir.$file))
					return CFile::MakeFileArray($this->files_dir.$file, false, false, $external_id);
				$fileId = $this->CheckFileByName($external_id, $fields);
				if ($fileId > 0)
					return CFile::MakeFileArray($fileId);
			}
		}

		return array("tmp_name"=>"", "del"=>"Y");
	}

	function URLEncode($str)
	{
		$strEncodedURL = '';
		$arUrlComponents = preg_split("#(://|/|\\?|=|&)#", $str, -1, PREG_SPLIT_DELIM_CAPTURE);
		foreach($arUrlComponents as $i => $part_of_url)
		{
			if($i % 2)
				$strEncodedURL .= $part_of_url;
			else
				$strEncodedURL .= urlencode($part_of_url);
		}
		return $strEncodedURL;
	}

	function CheckFileByName($file, $fields = null)
	{
		$external_id = $file;
		$fileName = bx_basename($file);
		if (!empty($fields) && $fileName != "")
		{
			if (empty($this->arElementFiles))
			{
				$this->arElementFiles = array();
				$ID = array();
				foreach ($this->arElementFilesId as $fileId)
				{
					foreach($fileId as $value)
						$ID[$value] = $value;
				}
				$rsFile = CFile::GetList(array(), array(
					"@ID" => implode(",", $ID),
				));
				while ($arFile = $rsFile->Fetch())
				{
					$arFile["~ORIGINAL_NAME"] = preg_replace("/(\\.resize[0-9]+\\.)/", ".", $arFile["ORIGINAL_NAME"]);
					$this->arElementFiles[$arFile["ID"]] = $arFile;
				}
			}

			foreach ($fields as $fieldId)
			{
				if (isset($this->arElementFilesId[$fieldId]))
				{
					foreach ($this->arElementFilesId[$fieldId] as $fileId)
					{
						if (isset($this->arElementFiles[$fileId]))
						{
							if ($this->arElementFiles[$fileId]["EXTERNAL_ID"] === $external_id)
								return $fileId;
							if ($this->arElementFiles[$fileId]["~ORIGINAL_NAME"] === $fileName)
								return $fileId;
						}
					}
				}
			}
		}
		return false;
	}

	function ResizePicture($file, $resize, $primaryField, $secondaryField = "")
	{
		static $errorFile = array("tmp_name"=>"", "del"=>"Y");
		$external_id = md5($file);
		if($file == '')
		{
			return $errorFile;
		}

		if(file_exists($this->files_dir.$file) && is_file($this->files_dir.$file))
		{
			$file = $this->files_dir.$file;
		}
		elseif(file_exists($file) && is_file($file))
		{
		}
		elseif(($fileId = $this->CheckFileByName($external_id, array($primaryField))) > 0)
		{
			return CFile::MakeFileArray($fileId);
		}
		elseif($secondaryField && ($fileId = $this->CheckFileByName($external_id, array($secondaryField))) > 0)
		{
			$storedFile = CFile::MakeFileArray($fileId);
			if (isset($storedFile['tmp_name']))
			{
				$tempFile = CTempFile::GetFileName(bx_basename($storedFile["tmp_name"]));
				CheckDirPath($tempFile);
				if (copy($storedFile["tmp_name"], $tempFile))
				{
					$storedFile["tmp_name"] = $tempFile;
					return $storedFile;
				}
				else
				{
					return $errorFile;
				}
			}
			else
			{
				return $errorFile;
			}
		}
		else
		{
			return $errorFile;
		}

		if(!is_array($resize) || !preg_match("#(\\.)([^./\\\\]+?)$#", $file))
		{
			$arFile = CFile::MakeFileArray($file, false, false, $external_id);
			if($arFile && $this->iblock_resize)
				$arFile["COPY_FILE"] = "Y";
			return $arFile;
		}

		$i = 1;
		while(file_exists(preg_replace("#(\\.)([^./\\\\]+?)$#", ".resize".$i.".\\2", $file)))
			$i++;
		$new_file = preg_replace("#(\\.)([^./\\\\]+?)$#", ".resize".$i.".\\2", $file);

		if (!CFile::ResizeImageFile($file, $new_file, array("width"=>$resize[0], "height"=>$resize[1])))
			return CFile::MakeFileArray($file, false, false, $external_id);

		$this->arTempFiles[] = $new_file;

		return CFile::MakeFileArray($new_file, false, false, $external_id);
	}

	function GetIBlockByXML_ID($XML_ID)
	{
		if($XML_ID <> '')
		{
			$obIBlock = new CIBlock;
			$rsIBlock = $obIBlock->GetList(array(), array("XML_ID"=>$XML_ID, "CHECK_PERMISSIONS" => "N"));
			if($arIBlock = $rsIBlock->Fetch())
				return $arIBlock["ID"];
			else
				return false;
		}
		return false;
	}

	function GetSectionByXML_ID($IBLOCK_ID, $XML_ID)
	{
		if (!isset($this->arSectionCache[$IBLOCK_ID]))
			$this->arSectionCache[$IBLOCK_ID] = array();
		if (!isset($this->arSectionCache[$IBLOCK_ID][$XML_ID]))
		{
			$rsSection = CIBlockSection::GetList(array(), array("IBLOCK_ID"=>$IBLOCK_ID, "EXTERNAL_ID"=>$XML_ID), false, array('ID'));
			if($arSection = $rsSection->Fetch())
				$this->arSectionCache[$IBLOCK_ID][$XML_ID] = $arSection["ID"];
			else
				$this->arSectionCache[$IBLOCK_ID][$XML_ID] = false;
			unset($rsSection);
			unset($arSection);
		}
		return $this->arSectionCache[$IBLOCK_ID][$XML_ID];
	}

	function GetElementByXML_ID($IBLOCK_ID, $XML_ID)
	{
		if($XML_ID == '')
			return false;
		if (!isset($this->arElementCache[$IBLOCK_ID]))
			$this->arElementCache[$IBLOCK_ID] = array();
		if (!isset($this->arElementCache[$IBLOCK_ID][$XML_ID]))
		{
			$obElement = new CIBlockElement;
			$rsElement = $obElement->GetList(
					array('ID' => 'ASC'),
					array("=XML_ID" => $XML_ID, "IBLOCK_ID" => $IBLOCK_ID),
					false, false,
					array("ID", "XML_ID")
			);
			if($arElement = $rsElement->Fetch())
				$this->arElementCache[$IBLOCK_ID][$XML_ID] = $arElement["ID"];
			else
				$this->arElementCache[$IBLOCK_ID][$XML_ID] = false;
		}

		return $this->arElementCache[$IBLOCK_ID][$XML_ID];
	}

	function GetEnumByXML_ID($PROP_ID, $XML_ID)
	{
		if($XML_ID == '')
			return "";

		if(!isset($this->arEnumCache[$PROP_ID]))
			$this->arEnumCache[$PROP_ID] = array();

		if(!isset($this->arEnumCache[$PROP_ID][$XML_ID]))
		{
			$rsEnum = CIBlockPropertyEnum::GetList(
					array(),
					array("EXTERNAL_ID" => $XML_ID, "PROPERTY_ID" => $PROP_ID)
			);
			if($arEnum = $rsEnum->Fetch())
				$this->arEnumCache[$PROP_ID][$XML_ID] = $arEnum["ID"];
			else
				$this->arEnumCache[$PROP_ID][$XML_ID] = false;
		}

		return $this->arEnumCache[$PROP_ID][$XML_ID];
	}

	function GetSectionEnumByXML_ID($FIELD_ID, $XML_ID)
	{
		if($XML_ID == '')
			return "";

		$cacheId = "E".$FIELD_ID;
		if(!isset($this->arEnumCache[$cacheId]))
			$this->arEnumCache[$cacheId] = array();

		if(!isset($this->arEnumCache[$cacheId][$XML_ID]))
		{
			$obEnum = new CUserFieldEnum;
			$rsEnum = $obEnum->GetList(array(), array(
				"USER_FIELD_ID" => $FIELD_ID,
				"XML_ID" => $XML_ID,
			));
			if($arEnum = $rsEnum->Fetch())
				$this->arEnumCache[$cacheId][$XML_ID] = $arEnum["ID"];
			else
				$this->arEnumCache[$cacheId][$XML_ID] = false;
		}

		if ($this->arEnumCache[$cacheId][$XML_ID])
			return $this->arEnumCache[$cacheId][$XML_ID];
		else
			return $XML_ID;
	}

	function GetPropertyByXML_ID($IBLOCK_ID, $XML_ID)
	{
		$obProperty = new CIBlockProperty;
		$rsProperty = $obProperty->GetList(array(), array("IBLOCK_ID"=>$IBLOCK_ID, "XML_ID"=>$XML_ID));
		if($arProperty = $rsProperty->Fetch())
			return $arProperty["ID"];
		else
			return false;
	}

	function CheckProperty($IBLOCK_ID, $code, $xml_name)
	{
		$obProperty = new CIBlockProperty;
		$rsProperty = $obProperty->GetList(array(), array("IBLOCK_ID"=>$IBLOCK_ID, "XML_ID"=>$code));
		$dbProperty = $rsProperty->Fetch();
		if(!$dbProperty)
		{
			$arProperty = array(
				"IBLOCK_ID" => $IBLOCK_ID,
				"NAME" => is_array($xml_name)? $xml_name["NAME"]: $xml_name,
				"CODE" => $code,
				"XML_ID" => $code,
				"MULTIPLE" => "N",
				"PROPERTY_TYPE" => "S",
				"ACTIVE" => "Y",
			);
			if(is_array($xml_name))
			{
				foreach($xml_name as $name => $value)
					$arProperty[$name] = $value;
			}
			$ID = $obProperty->Add($arProperty);
			if(!$ID)
				return $obProperty->LAST_ERROR;
		}
		elseif (is_array($xml_name))
		{
			$update = array();
			foreach($xml_name as $name => $value)
			{
				if (($name != "NAME") && ($dbProperty[$name] != $value))
				{
					$update[$name] = $value;
				}
			}
			if ($update)
			{
				$obProperty->Update($dbProperty["ID"], $update);
			}
		}
		return true;
	}

	function CheckTax($title, $rate)
	{
		if ($rate === null)
		{
			$taxName = GetMessage('IBLOCK_NO_VAT_TITLE');
		}
		else
		{
			$taxName = $title . " " . $rate . "%";
		}

		if (isset($this->arTaxCache[$taxName]))
		{
			return $this->arTaxCache[$taxName];
		}

		$vatParams = [
			'select' => ['ID'],
		];

		if ($rate === null)
		{
			$vatParams['filter'] = [
				'=EXCLUDE_VAT' => 'Y',
			];
		}
		else
		{
			$vatParams['filter'] = [
				'=NAME' => $taxName,
				'=EXCLUDE_VAT' => 'N',
				'=RATE' => $rate,
			];
		}

		$getResult = \Bitrix\Catalog\Model\Vat::getList($vatParams)->fetch();
		if (isset($getResult['ID']))
		{
			$vatId = $getResult['ID'];
			$this->arTaxCache[$taxName] = $vatId;
			return $vatId;
		}

		$addParams = [
			'ACTIVE' => 'Y',
			'NAME' => $taxName,
		];

		if ($rate === null)
		{
			$addParams['EXCLUDE_VAT'] = 'Y';
		}
		else
		{
			$addParams['RATE'] = $rate;
		}

		$this->arTaxCache[$taxName] = \Bitrix\Catalog\Model\Vat::add($addParams)->getId();

		return $this->arTaxCache[$taxName];
	}

	function CheckCurrency($currency)
	{
		global $CML2_CURRENCY;

		if($currency==$this->mess["IBLOCK_XML2_RUB"])
		{
			$currency="RUB";
		}
		elseif(!preg_match("/^[a-zA-Z]+$/", $currency))
		{
			if(
				is_array($CML2_CURRENCY)
				&& isset($CML2_CURRENCY[$currency])
				&& is_string($CML2_CURRENCY[$currency])
				&& preg_match("/^[a-zA-Z0-9]+$/", $CML2_CURRENCY[$currency])
			)
			{
				$currency = $CML2_CURRENCY[$currency];
			}
			else
			{
				$currency="RUB";
				$this->LAST_ERROR = GetMessage("IBLOCK_XML2_CURRENCY_ERROR");
			}
		}

		if(!isset($this->arCurrencyCache[$currency]))
		{
			if($this->bCatalog && $this->currencyIncluded)
			{
				CCurrency::Add(array(
					"CURRENCY" => $currency,
				));
			}
			$this->arCurrencyCache[$currency] = true;
		}

		return $currency;
	}

	function CheckIBlockType($ID)
	{
		$obType = new CIBlockType;
		$rsType = $obType->GetByID($ID);
		if($arType = $rsType->Fetch())
		{
			return $arType["ID"];
		}
		else
		{
			$rsType = $obType->GetByID("1c_catalog");
			if($arType = $rsType->Fetch())
			{
				return $arType["ID"];
			}
			else
			{
				$result = $obType->Add(array(
					"ID" => "1c_catalog",
					"SECTIONS" => "Y",
					"LANG" => array(
						"ru" => array(
							"NAME" => GetMessage("IBLOCK_XML2_CATALOG_NAME"),
							"SECTION_NAME" => GetMessage("IBLOCK_XML2_CATALOG_SECTION_NAME"),
							"ELEMENT_NAME" => GetMessage("IBLOCK_XML2_CATALOG_ELEMENT_NAME"),
						),
					),
				));
				if($result)
					return $result;
				else
					return false;
			}
		}
	}

	function CheckSites($arSite)
	{
		$arResult = array();
		if(!is_array($arSite))
			$arSite = array($arSite);
		foreach($arSite as $site_id)
		{
			if($site_id <> '')
			{
				$rsSite = CSite::GetByID($site_id);
				if($rsSite->Fetch())
					$arResult[] = $site_id;
			}
		}
		if(!defined("ADMIN_SECTION"))
		{
			$rsSite = CSite::GetByID(SITE_ID);
			if($rsSite->Fetch())
				$arResult[] = SITE_ID;
		}
		if(count($arResult)<1)
			$arResult[] = CSite::GetDefSite();
		return $arResult;
	}

	function ImportMetaData($xml_root_id, $IBLOCK_TYPE, $IBLOCK_LID, $bUpdateIBlock = true)
	{
		global $APPLICATION;

		$rs = $this->_xml_file->GetList(
			array("ID" => "asc"),
			array("ID" => $xml_root_id),
			array("ID", "NAME", "ATTRIBUTES")
		);
		$ar = $rs->Fetch();

		if ($ar)
		{
			foreach(array(LANGUAGE_ID, "en", "ru") as $lang)
			{
				$mess = Main\Localization\Loc::loadLanguageFile(__FILE__, $lang);
				if($ar["NAME"] === $mess["IBLOCK_XML2_COMMERCE_INFO"])
				{
					$this->mess = $mess;
					$this->next_step["lang"] = $lang;
				}
			}
			$xml_root_id = $ar["ID"];
		}

		if($ar && ($ar["ATTRIBUTES"] <> ''))
		{
			$info = unserialize($ar["ATTRIBUTES"], ['allowed_classes' => false]);
			if(is_array($info) && array_key_exists($this->mess["IBLOCK_XML2_SUM_FORMAT"], $info))
			{
				if(preg_match("#".$this->mess["IBLOCK_XML2_SUM_FORMAT_DELIM"]."=(.);{0,1}#", $info[$this->mess["IBLOCK_XML2_SUM_FORMAT"]], $match))
				{
					$this->next_step["sdp"] = $match[1];
				}
			}
		}

		$meta_data_xml_id = false;
		$XML_ELEMENTS_PARENT = false;
		$XML_SECTIONS_PARENT = false;
		$XML_PROPERTIES_PARENT = false;
		$XML_SECTIONS_PROPERTIES_PARENT = false;
		$XML_PRICES_PARENT = false;
		$XML_STORES_PARENT = false;
		$XML_BASE_UNITS_PARENT = false;
		$XML_SECTION_PROPERTIES = false;
		$arIBlock = array();

		$this->next_step["bOffer"] = false;
		$rs = $this->_xml_file->GetList(
			array(),
			array("PARENT_ID" => $xml_root_id, "NAME" => $this->mess["IBLOCK_XML2_CATALOG"]),
			array("ID", "ATTRIBUTES")
		);
		$ar = $rs->Fetch();
		if(!$ar)
		{
			$rs = $this->_xml_file->GetList(
				array(),
				array("PARENT_ID" => $xml_root_id, "NAME" => $this->mess["IBLOCK_XML2_OFFER_LIST"]),
				array("ID", "ATTRIBUTES")
			);
			$ar = $rs->Fetch();
			$this->next_step["bOffer"] = true;
		}
		if(!$ar)
		{
			$rs = $this->_xml_file->GetList(
				array(),
				array("PARENT_ID" => $xml_root_id, "NAME" => $this->mess["IBLOCK_XML2_OFFERS_CHANGE"]),
				array("ID", "ATTRIBUTES")
			);
			$ar = $rs->Fetch();
			$this->next_step["bOffer"] = true;
			$this->next_step["bUpdateOnly"] = true;
			$bUpdateIBlock = false;
		}

		if ($this->next_step["bOffer"] && !$this->bCatalog)
			return GetMessage('IBLOCK_XML2_MODULE_CATALOG_IS_ABSENT');

		if($ar)
		{
			if($ar["ATTRIBUTES"] <> '')
			{
				$attrs = unserialize($ar["ATTRIBUTES"], ['allowed_classes' => false]);
				if (is_array($attrs))
				{
					if (isset($attrs[$this->mess["IBLOCK_XML2_UPDATE_ONLY"]]))
					{
						$this->next_step["bUpdateOnly"] = $attrs[$this->mess["IBLOCK_XML2_UPDATE_ONLY"]] === "true"
							|| (int)$attrs[$this->mess["IBLOCK_XML2_UPDATE_ONLY"]] > 0
						;
					}
				}
			}

			$rs = $this->_xml_file->GetList(
				array("ID" => "asc"),
				array("PARENT_ID" => $ar["ID"])
			);
			while($ar = $rs->Fetch())
			{
				if(isset($ar["VALUE_CLOB"]))
					$ar["VALUE"] = $ar["VALUE_CLOB"];

				$trueValue = $ar['VALUE'] === 'true' || (int)$ar['VALUE'] > 0;

				if($ar["NAME"] == $this->mess["IBLOCK_XML2_ID"])
					$arIBlock["XML_ID"] = ($this->use_iblock_type_id? $IBLOCK_TYPE."-": "").$ar["VALUE"];
				elseif($ar["NAME"] == $this->mess["IBLOCK_XML2_CATALOG_ID"])
					$arIBlock["CATALOG_XML_ID"] = ($this->use_iblock_type_id? $IBLOCK_TYPE."-": "").$ar["VALUE"];
				elseif($ar["NAME"] == $this->mess["IBLOCK_XML2_NAME"])
					$arIBlock["NAME"] = $ar["VALUE"];
				elseif($ar["NAME"] == $this->mess["IBLOCK_XML2_DESCRIPTION"])
				{
					$arIBlock["DESCRIPTION"] = $ar["VALUE"];
					$arIBlock["DESCRIPTION_TYPE"] = "html";
				}
				elseif($ar["NAME"] == $this->mess["IBLOCK_XML2_POSITIONS"] || $ar["NAME"] == $this->mess["IBLOCK_XML2_OFFERS"])
					$XML_ELEMENTS_PARENT = $ar["ID"];
				elseif($ar["NAME"] == $this->mess["IBLOCK_XML2_PRICE_TYPES"])
					$XML_PRICES_PARENT = $ar["ID"];
				elseif($ar["NAME"] == $this->mess["IBLOCK_XML2_STORES"])
					$XML_STORES_PARENT = $ar["ID"];
				elseif($ar["NAME"] == $this->mess["IBLOCK_XML2_BASE_UNITS"])
					$XML_BASE_UNITS_PARENT = $ar["ID"];
				elseif($ar["NAME"] == $this->mess["IBLOCK_XML2_METADATA_ID"])
					$meta_data_xml_id = $ar["VALUE"];
				elseif($ar["NAME"] == $this->mess["IBLOCK_XML2_UPDATE_ONLY"])
					$this->next_step["bUpdateOnly"] = $trueValue;
				elseif($ar["NAME"] == $this->mess["IBLOCK_XML2_BX_CODE"])
					$arIBlock["CODE"] = $ar["VALUE"];
				elseif($ar["NAME"] == $this->mess["IBLOCK_XML2_BX_API_CODE"])
					$arIBlock["API_CODE"] = $ar["VALUE"];
				elseif($ar["NAME"] == $this->mess["IBLOCK_XML2_BX_SORT"])
					$arIBlock["SORT"] = $ar["VALUE"];
				elseif($ar["NAME"] == $this->mess["IBLOCK_XML2_BX_LIST_URL"])
					$arIBlock["LIST_PAGE_URL"] = $ar["VALUE"];
				elseif($ar["NAME"] == $this->mess["IBLOCK_XML2_BX_DETAIL_URL"])
					$arIBlock["DETAIL_PAGE_URL"] = $ar["VALUE"];
				elseif($ar["NAME"] == $this->mess["IBLOCK_XML2_BX_SECTION_URL"])
					$arIBlock["SECTION_PAGE_URL"] = $ar["VALUE"];
				elseif($ar["NAME"] == $this->mess["IBLOCK_XML2_BX_CANONICAL_URL"])
					$arIBlock["CANONICAL_PAGE_URL"] = $ar["VALUE"];
				elseif($ar["NAME"] == $this->mess["IBLOCK_XML2_BX_INDEX_ELEMENTS"])
					$arIBlock["INDEX_ELEMENT"] = $trueValue ? "Y": "N";
				elseif($ar["NAME"] == $this->mess["IBLOCK_XML2_BX_INDEX_SECTIONS"])
					$arIBlock["INDEX_SECTION"] = $trueValue ? "Y": "N";
				elseif($ar["NAME"] == $this->mess["IBLOCK_XML2_BX_SECTIONS_NAME"])
					$arIBlock["SECTIONS_NAME"] = $ar["VALUE"];
				elseif($ar["NAME"] == $this->mess["IBLOCK_XML2_BX_SECTION_NAME"])
					$arIBlock["SECTION_NAME"] = $ar["VALUE"];
				elseif($ar["NAME"] == $this->mess["IBLOCK_XML2_BX_ELEMENTS_NAME"])
					$arIBlock["ELEMENTS_NAME"] = $ar["VALUE"];
				elseif($ar["NAME"] == $this->mess["IBLOCK_XML2_BX_ELEMENT_NAME"])
					$arIBlock["ELEMENT_NAME"] = $ar["VALUE"];
				elseif($ar["NAME"] == $this->mess["IBLOCK_XML2_BX_PICTURE"])
				{
					if($ar["VALUE"] <> '')
						$arIBlock["PICTURE"] = $this->MakeFileArray($ar["VALUE"]);
					else
						$arIBlock["PICTURE"] = $this->MakeFileArray($this->_xml_file->GetAllChildrenArray($ar["ID"]));
				}
				elseif($ar["NAME"] == $this->mess["IBLOCK_XML2_BX_WORKFLOW"])
					$arIBlock["WORKFLOW"] = ($ar["VALUE"]=="true") || intval($ar["VALUE"])? "Y": "N";
				elseif($ar["NAME"] == $this->mess["IBLOCK_XML2_INHERITED_TEMPLATES"])
				{
					$arIBlock["IPROPERTY_TEMPLATES"] = array();
					$arTemplates = $this->_xml_file->GetAllChildrenArray($ar["ID"]);
					foreach($arTemplates as $TEMPLATE)
					{
						$id = $TEMPLATE[$this->mess["IBLOCK_XML2_ID"]];
						$template = $TEMPLATE[$this->mess["IBLOCK_XML2_VALUE"]];
						if($id <> '' && $template <> '')
							$arIBlock["IPROPERTY_TEMPLATES"][$id] = $template;
					}
				}
				elseif($ar["NAME"] == $this->mess["IBLOCK_XML2_LABELS"])
				{
					$arLabels = $this->_xml_file->GetAllChildrenArray($ar["ID"]);
					foreach($arLabels as $arLabel)
					{
						$id = $arLabel[$this->mess["IBLOCK_XML2_ID"]];
						$label = $arLabel[$this->mess["IBLOCK_XML2_VALUE"]];
						if($id <> '' && $label <> '')
							$arIBlock[$id] = $label;
					}
				}
			}
			if($this->next_step["bOffer"] && !$this->use_offers)
			{
				if (isset($arIBlock["CATALOG_XML_ID"]) && $arIBlock["CATALOG_XML_ID"] !== '')
				{
					$arIBlock["XML_ID"] = $arIBlock["CATALOG_XML_ID"];
					$this->next_step["bUpdateOnly"] = true;
				}
			}

			$obIBlock = new CIBlock;
			$rsIBlocks = $obIBlock->GetList(array(), array("XML_ID"=>$arIBlock["XML_ID"]));
			$ar = $rsIBlocks->Fetch();

			//Also check for non bitrix xml file
			if(!$ar && !array_key_exists("CODE", $arIBlock))
			{
				if($this->next_step["bOffer"] && $this->use_offers)
					$rsIBlocks = $obIBlock->GetList(array(), array("XML_ID"=>"FUTURE-1C-OFFERS"));
				else
					$rsIBlocks = $obIBlock->GetList(array(), array("XML_ID"=>"FUTURE-1C-CATALOG"));
				$ar = $rsIBlocks->Fetch();
			}
			if($ar)
			{
				if($bUpdateIBlock && (!$this->next_step["bOffer"] || $this->use_offers))
				{
					if($obIBlock->Update($ar["ID"], $arIBlock))
						$arIBlock["ID"] = $ar["ID"];
					else
						return $obIBlock->LAST_ERROR;
				}
				else
				{
					$arIBlock["ID"] = $ar["ID"];
				}
			}
			else
			{
				$arIBlock["IBLOCK_TYPE_ID"] = $this->CheckIBlockType($IBLOCK_TYPE);
				if(!$arIBlock["IBLOCK_TYPE_ID"])
					return GetMessage("IBLOCK_XML2_TYPE_ADD_ERROR");
				$arIBlock["GROUP_ID"] = array(2=>"R");
				$arIBlock["LID"] = $this->CheckSites($IBLOCK_LID);
				$arIBlock["ACTIVE"] = "Y";
				$arIBlock["WORKFLOW"] = "N";
				if (
					$this->translit_on_add
					&& !array_key_exists("CODE", $arIBlock)
				)
				{
					$arIBlock["FIELDS"] = array(
						"CODE" => array( "DEFAULT_VALUE" => array(
							"TRANSLITERATION" => "Y",
							"TRANS_LEN" => $this->translit_on_add["max_len"],
							"TRANS_CASE" => $this->translit_on_add["change_case"],
							"TRANS_SPACE" => $this->translit_on_add["replace_space"],
							"TRANS_OTHER" => $this->translit_on_add["replace_other"],
							"TRANS_EAT" => $this->translit_on_add["delete_repeat_replace"]? "Y": "N",
						)),
						"SECTION_CODE" => array( "DEFAULT_VALUE" => array(
							"TRANSLITERATION" => "Y",
							"TRANS_LEN" => $this->translit_on_add["max_len"],
							"TRANS_CASE" => $this->translit_on_add["change_case"],
							"TRANS_SPACE" => $this->translit_on_add["replace_space"],
							"TRANS_OTHER" => $this->translit_on_add["replace_other"],
							"TRANS_EAT" => $this->translit_on_add["delete_repeat_replace"]? "Y": "N",
						)),
					);
				}
				$arIBlock["ID"] = $obIBlock->Add($arIBlock);
				if(!$arIBlock["ID"])
					return $obIBlock->LAST_ERROR;
			}

			//Make this catalog
			if($this->bCatalog && $this->next_step["bOffer"])
			{
				$obCatalog = new CCatalog();
				$intParentID = $this->GetIBlockByXML_ID($arIBlock["CATALOG_XML_ID"] ?? '');
				if (0 < intval($intParentID) && $this->use_offers)
				{
					$mxSKUProp = $obCatalog->LinkSKUIBlock($intParentID,$arIBlock["ID"]);
					if (!$mxSKUProp)
					{
						if ($ex = $APPLICATION->GetException())
						{
							$result = $ex->GetString();
							return $result;
						}
					}
					else
					{
						$rs = CCatalog::GetList(array(),array("IBLOCK_ID"=>$arIBlock["ID"]));
						if($arOffer = $rs->Fetch())
						{
							$boolFlag = $obCatalog->Update($arIBlock["ID"],array('PRODUCT_IBLOCK_ID' => $intParentID,'SKU_PROPERTY_ID' => $mxSKUProp));
						}
						else
						{
							$boolFlag = $obCatalog->Add(array("IBLOCK_ID"=>$arIBlock["ID"], "YANDEX_EXPORT"=>"N", "SUBSCRIPTION"=>"N",'PRODUCT_IBLOCK_ID' => $intParentID,'SKU_PROPERTY_ID' => $mxSKUProp));
						}
						if (!$boolFlag)
						{
							if ($ex = $APPLICATION->GetException())
							{
								$result = $ex->GetString();
								return $result;
							}
						}
					}
				}
				else
				{
					$rs = CCatalog::GetList(array(),array("IBLOCK_ID"=>$arIBlock["ID"]));
					if(!($rs->Fetch()))
					{
						$boolFlag = $obCatalog->Add(array("IBLOCK_ID"=>$arIBlock["ID"], "YANDEX_EXPORT"=>"N", "SUBSCRIPTION"=>"N"));
						if (!$boolFlag)
						{
							if ($ex = $APPLICATION->GetException())
							{
								$result = $ex->GetString();
								return $result;
							}
						}
					}
				}
			}

			//For non bitrix xml file
			//Check for mandatory properties and add them as necessary
			if(!array_key_exists("CODE", $arIBlock))
			{
				$arProperties = [];
				$arProperties['CML2_BAR_CODE'] = GetMessage('IBLOCK_XML2_BAR_CODE');
				$arProperties['CML2_ARTICLE'] = GetMessage('IBLOCK_XML2_ARTICLE');
				$arProperties['CML2_ATTRIBUTES'] = [
					'NAME' => GetMessage('IBLOCK_XML2_ATTRIBUTES'),
					'MULTIPLE' => 'Y',
					'WITH_DESCRIPTION' => 'Y',
					'MULTIPLE_CNT' => 1,
				];
				$arProperties['CML2_TRAITS'] = [
					'NAME' => GetMessage('IBLOCK_XML2_TRAITS'),
					'MULTIPLE' => 'Y',
					'WITH_DESCRIPTION' => 'Y',
					'MULTIPLE_CNT' => 1,
				];
				$arProperties['CML2_BASE_UNIT'] = [
					'NAME' => GetMessage('IBLOCK_XML2_BASE_UNIT_NAME'),
					'WITH_DESCRIPTION' => 'Y',
				];
				$arProperties['CML2_TAXES'] = [
					'NAME' => GetMessage('IBLOCK_XML2_TAXES'),
					'MULTIPLE' => 'Y',
					'WITH_DESCRIPTION' => 'Y',
					'MULTIPLE_CNT' => 1,
				];
				$arProperties[CIBlockPropertyTools::XML_MORE_PHOTO] = CIBlockPropertyTools::getPropertyDescription(
					CIBlockPropertyTools::CODE_MORE_PHOTO,
					['IBLOCK_ID' => $arIBlock['ID']]
				);
				$arProperties['CML2_FILES'] = [
					'NAME' => GetMessage('IBLOCK_XML2_FILES'),
					'MULTIPLE' => 'Y',
					'WITH_DESCRIPTION' => 'Y',
					'MULTIPLE_CNT' => 1,
					'PROPERTY_TYPE' => Iblock\PropertyTable::TYPE_FILE,
					'CODE' => 'FILES',
				];
				$arProperties['CML2_MANUFACTURER'] = [
					'NAME' => GetMessage('IBLOCK_XML2_PROP_MANUFACTURER'),
					'MULTIPLE' => 'N',
					'WITH_DESCRIPTION' => 'N',
					'MULTIPLE_CNT' => 1,
					'PROPERTY_TYPE' => Iblock\PropertyTable::TYPE_LIST,
				];

				foreach($arProperties as $k=>$v)
				{
					$result = $this->CheckProperty($arIBlock["ID"], $k, $v);
					if($result!==true)
						return $result;
				}
				//For offers make special property: link to catalog
				if (isset($arIBlock['CATALOG_XML_ID']) && $this->use_offers)
				{
					$this->CheckProperty(
						$arIBlock['ID'],
						CIBlockPropertyTools::XML_SKU_LINK,
						CIBlockPropertyTools::getPropertyDescription(
							CIBlockPropertyTools::CODE_SKU_LINK,
							[
								'IBLOCK_ID' => $arIBlock['ID'],
								'LINK_IBLOCK_ID' => $this->GetIBlockByXML_ID($arIBlock['CATALOG_XML_ID']),
							]
						)
					);
				}
			}

			$this->next_step["IBLOCK_ID"] = $arIBlock["ID"];
			$this->next_step["XML_ELEMENTS_PARENT"] = $XML_ELEMENTS_PARENT;
		}

		if($meta_data_xml_id)
		{
			$rs = $this->_xml_file->GetList(
				array(),
				array("PARENT_ID" => $xml_root_id, "NAME" => $this->mess["IBLOCK_XML2_METADATA"]),
				array("ID")
			);
			while($arMetadata = $rs->Fetch())
			{
				//Find referenced metadata
				$bMetaFound = false;
				$meta_roots = array();
				$rsMetaRoots = $this->_xml_file->GetList(
					array("ID" => "asc"),
					array("PARENT_ID" => $arMetadata["ID"])
				);
				while($arMeta = $rsMetaRoots->Fetch())
				{
					if(isset($arMeta["VALUE_CLOB"]))
						$arMeta["VALUE"] = $arMeta["VALUE_CLOB"];

					if($arMeta["NAME"] == $this->mess["IBLOCK_XML2_ID"] && $arMeta["VALUE"] == $meta_data_xml_id)
						$bMetaFound = true;

					$meta_roots[] = $arMeta;
				}

				//Get xml parents of the properties and sections
				if($bMetaFound)
				{
					foreach($meta_roots as $arMeta)
					{
						if($arMeta["NAME"] == $this->mess["IBLOCK_XML2_GROUPS"])
							$XML_SECTIONS_PARENT = $arMeta["ID"];
						elseif($arMeta["NAME"] == $this->mess["IBLOCK_XML2_PROPERTIES"])
							$XML_PROPERTIES_PARENT = $arMeta["ID"];
						elseif($arMeta["NAME"] == $this->mess["IBLOCK_XML2_GROUPS_PROPERTIES"])
							$XML_SECTIONS_PROPERTIES_PARENT = $arMeta["ID"];
						elseif($arMeta["NAME"] == $this->mess["IBLOCK_XML2_SECTION_PROPERTIES"])
							$XML_SECTION_PROPERTIES = $arMeta["ID"];
						elseif($arMeta["NAME"] == $this->mess["IBLOCK_XML2_PRICE_TYPES"])
							$XML_PRICES_PARENT = $arMeta["ID"];
						elseif($arMeta["NAME"] == $this->mess["IBLOCK_XML2_STORES"])
							$XML_STORES_PARENT = $arMeta["ID"];
						elseif($arMeta["NAME"] == $this->mess["IBLOCK_XML2_BASE_UNITS"])
							$XML_BASE_UNITS_PARENT = $arMeta["ID"];
					}
					break;
				}
			}
		}

		$iblockFields = CIBlock::GetFields($arIBlock["ID"]);
		$iblockFields["XML_IMPORT_START_TIME"] = array(
			"NAME" => "XML_IMPORT_START_TIME",
			"IS_REQUIRED" => "N",
			"DEFAULT_VALUE" => date("Y-m-d H:i:s"),
		);
		CIBlock::SetFields($arIBlock["ID"], $iblockFields);

		if($XML_PROPERTIES_PARENT)
		{
			$result = $this->ImportProperties($XML_PROPERTIES_PARENT, $arIBlock["ID"]);
			if($result!==true)
				return $result;
		}

		if($XML_SECTION_PROPERTIES)
		{
			$result = $this->ImportSectionProperties($XML_SECTION_PROPERTIES, $arIBlock["ID"]);
			if($result!==true)
				return $result;
		}

		if($XML_SECTIONS_PROPERTIES_PARENT)
		{
			$result = $this->ImportSectionsProperties($XML_SECTIONS_PROPERTIES_PARENT, $arIBlock["ID"]);
			if($result!==true)
				return $result;
		}

		if($XML_PRICES_PARENT)
		{
			if($this->bCatalog)
			{
				$result = $this->ImportPrices($XML_PRICES_PARENT, $arIBlock["ID"], $IBLOCK_LID);
				if($result!==true)
					return $result;
			}
		}

		if($XML_STORES_PARENT)
		{
			if($this->bCatalog)
			{
				$result = $this->ImportStores($XML_STORES_PARENT);
				if($result!==true)
					return $result;
			}
		}

		if($XML_BASE_UNITS_PARENT)
		{
			if($this->bCatalog)
			{
				$result = $this->ImportBaseUnits($XML_BASE_UNITS_PARENT);
				if($result!==true)
					return $result;
			}
		}

		$this->next_step["section_sort"] = 100;
		$this->next_step["XML_SECTIONS_PARENT"] = $XML_SECTIONS_PARENT;

		$rs = $this->_xml_file->GetList(
			array(),
			array("PARENT_ID" => $xml_root_id, "NAME" => $this->mess["IBLOCK_XML2_PRODUCTS_SETS"]),
			array("ID", "ATTRIBUTES")
		);
		$ar = $rs->Fetch();
		if ($ar)
		{
			$this->next_step["SETS"] = (int)$ar["ID"];
		}

		return true;
	}

	function ImportSections()
	{
		if($this->next_step["XML_SECTIONS_PARENT"])
		{
			$rs = $this->_xml_file->GetList(
				array("ID" => "asc"),
				array("PARENT_ID" => $this->next_step["XML_SECTIONS_PARENT"]),
				array("ID", "NAME", "VALUE")
			);
			$arID = array();
			while($ar = $rs->Fetch())
				$arID[] = $ar["ID"];

			if($this->skip_root_section && (count($arID) == 1))
			{
				$rs = $this->_xml_file->GetList(
					array("ID" => "asc"),
					array("PARENT_ID" => $arID[0]),
					array("ID", "NAME", "VALUE")
				);

				$XML_SECTIONS_PARENT = false;
				while($ar = $rs->Fetch())
					if($ar["NAME"] == $this->mess["IBLOCK_XML2_GROUPS"])
						$XML_SECTIONS_PARENT = $ar["ID"];

				$arID = array();
				if($XML_SECTIONS_PARENT > 0)
				{
					$rs = $this->_xml_file->GetList(
						array("ID" => "asc"),
						array("PARENT_ID" => $XML_SECTIONS_PARENT),
						array("ID", "NAME", "VALUE")
					);
					while($ar = $rs->Fetch())
						$arID[] = $ar["ID"];
				}
			}

			foreach($arID as $id)
			{
				$result = $this->ImportSection($id, $this->next_step["IBLOCK_ID"], false);
				if($result !== true)
					return $result;
			}
		}

		return true;
	}

	function DeactivateSections($action)
	{
		if(array_key_exists("bUpdateOnly", $this->next_step) && $this->next_step["bUpdateOnly"])
			return;
		if(!$this->next_step["XML_SECTIONS_PARENT"])
			return;

		if($action!="D" && $action!="A")
			return;

		$bDelete = $action=="D";

		//This will protect us from deactivating when next_step is lost
		$IBLOCK_ID = intval($this->next_step["IBLOCK_ID"]);
		if($IBLOCK_ID < 1)
			return;

		$arFilter = array(
			"IBLOCK_ID" => $IBLOCK_ID,
		);
		if(!$bDelete)
			$arFilter["ACTIVE"] = "Y";

		$obSection = new CIBlockSection;
		$rsSection = $obSection->GetList(array("ID"=>"asc"), $arFilter, false, ['ID', 'IBLOCK_ID']);

		while($arSection = $rsSection->Fetch())
		{
			$rs = $this->_xml_file->GetList(
				array(),
				array("PARENT_ID+0" => 0, "LEFT_MARGIN" => $arSection["ID"]),
				array("ID")
			);
			$ar = $rs->Fetch();
			if(!$ar)
			{
				if($bDelete)
				{
					$obSection->Delete($arSection["ID"]);
				}
				else
				{
					$obSection->Update($arSection["ID"], array("ACTIVE"=>"N"));
				}
			}
			else
			{
				$this->_xml_file->Delete($ar["ID"]);
			}
		}
		return;
	}

	function SectionsResort()
	{
		CIBlockSection::ReSort($this->next_step["IBLOCK_ID"]);
	}

	function ImportPrices($XML_PRICES_PARENT, $IBLOCK_ID, $IBLOCK_LID)
	{
		$price_sort = 0;
		$this->next_step["XML_PRICES_PARENT"] = $XML_PRICES_PARENT;

		$arLang = array();
		foreach($IBLOCK_LID as $site_id)
		{
			$rsSite = CSite::GetList("sort", "asc", array("ID" => $site_id));
			while ($site = $rsSite->Fetch())
				$arLang[$site["LANGUAGE_ID"]] = $site["LANGUAGE_ID"];
		}

		$arPrices = CCatalogGroup::GetListArray();

		if (!Catalog\Config\Feature::isMultiPriceTypesEnabled())
		{
			$prices_limit = 1 - count($arPrices);
		}
		else
		{
			$prices_limit = null;
		}

		$arXMLPrices = $this->_xml_file->GetAllChildrenArray($XML_PRICES_PARENT);
		$uniqPriceById = array();
		foreach($arXMLPrices as $arXMLPrice)
		{
			$PRICE_ID = $arXMLPrice[$this->mess["IBLOCK_XML2_ID"]];
			$PRICE_NAME = $arXMLPrice[$this->mess["IBLOCK_XML2_NAME"]];
			if (array_key_exists($PRICE_ID, $uniqPriceById))
				return GetMessage("IBLOCK_XML2_PRICE_DUP_ERROR");
			else
				$uniqPriceById[$PRICE_ID] = true;

			$found_id = 0;
			//Check for price by XML_ID
			if (isset($PRICE_ID) && $PRICE_ID != "")
			{
				foreach($arPrices as $i => $arPrice)
				{
					if ($PRICE_ID === $arPrice["XML_ID"])
					{
						$found_id = $arPrice["ID"];
						$arPrices[$i]["found"] = true;
						break;
					}
				}
			}
			//When lookup by it's name
			if (!$found_id)
			{
				foreach($arPrices as $arPrice)
				{
					if ($PRICE_NAME === $arPrice["NAME"] && !isset($arPrice["found"]))
					{
						$found_id = $arPrice["ID"];
						break;
					}
				}
			}
			//Add new price type
			if(!$found_id)
			{
				$price_sort += 100;
				$arPrice = array(
					"NAME" => $PRICE_NAME,
					"XML_ID" => $PRICE_ID,
					"SORT" => $price_sort,
					"USER_LANG" => array(),
					"USER_GROUP" => array(2),
					"USER_GROUP_BUY" => array(2),
				);
				foreach($arLang as $lang)
				{
					$arPrice["USER_LANG"][$lang] = $arXMLPrice[$this->mess["IBLOCK_XML2_NAME"]];
				}

				if(!isset($prices_limit) || $prices_limit > 0)
				{
					CCatalogGroup::Add($arPrice);
				}
				elseif (isset($prices_limit))
				{
					if ($this->bitrix24mode)
					{
						return GetMessage("IBLOCK_XML2_PRICE_SB_ADD_ERROR_B24");
					}
					else
					{
						return GetMessage("IBLOCK_XML2_PRICE_SB_ADD_ERROR");
					}
				}
			}
			//We can update XML_ID of the price
			elseif ($arPrices[$found_id]["XML_ID"] == '' && $PRICE_ID <> '')
			{
				CCatalogGroup::Update($found_id, array(
					"XML_ID" => $PRICE_ID,
				));
			}
			//We should update NAME of the price
			elseif ($arPrices[$found_id]["NAME"] !== $PRICE_NAME && !$this->disable_change_price_name)
			{
				CCatalogGroup::Update($found_id, array(
					"NAME" => $PRICE_NAME,
				));
			}

			if (isset($prices_limit))
				$prices_limit--;
		}
		return true;
	}

	function ImportBaseUnits($XML_BASE_UNITS_PARENT)
	{
		$arXMLBaseUnits = $this->_xml_file->GetAllChildrenArray($XML_BASE_UNITS_PARENT);
		foreach ($arXMLBaseUnits as $arXMLBaseUnit)
		{
			$arUnit = array(
				"CODE" => $arXMLBaseUnit[$this->mess["IBLOCK_XML2_CODE"]],
				"MEASURE_TITLE" => $arXMLBaseUnit[$this->mess["IBLOCK_XML2_FULL_NAME"]],
				"SYMBOL_RUS" => $arXMLBaseUnit[$this->mess["IBLOCK_XML2_SHORT_NAME"]],
				//"SYMBOL_INTL" => $arXMLBaseUnit[$this->mess["IBLOCK_XML2_SHORT_NAME"]],
				"SYMBOL_LETTER_INTL" => $arXMLBaseUnit[$this->mess["IBLOCK_XML2_INTL_SHORT_NAME"]],
			);

			$rsBaseUnit = CCatalogMeasure::GetList(array(), array("CODE" => $arUnit["CODE"]));
			$arIDUnit = $rsBaseUnit->Fetch();
			if (!$arIDUnit)
			{
				$ID = CCatalogMeasure::Add($arUnit);
				if (!$ID)
				{
					return GetMessage("IBLOCK_XML2_BASE_UNIT_ADD_ERROR", array("#CODE#" => $arUnit["CODE"]));
				}
			}
		}
		return true;
	}

	function ImportStores($XML_STORES_PARENT)
	{
		$error = '';
		$ID = 0;
		$arDBStores = $this->getStoreList();

		$arXMLStores = $this->_xml_file->GetAllChildrenArray($XML_STORES_PARENT);
		foreach($arXMLStores as $arXMLStore)
		{
			$arStore = array(
				"TITLE" => $arXMLStore[$this->mess["IBLOCK_XML2_NAME"]],
				"XML_ID" => $arXMLStore[$this->mess["IBLOCK_XML2_ID"]],
			);
			if(isset($arXMLStore[$this->mess["IBLOCK_XML2_STORE_ADDRESS"]]))
				$arStore["ADDRESS"] = $arXMLStore[$this->mess["IBLOCK_XML2_STORE_ADDRESS"]][$this->mess["IBLOCK_XML2_VIEW"]];
			if(isset($arXMLStore[$this->mess["IBLOCK_XML2_STORE_DESCRIPTION"]]))
				$arStore["DESCRIPTION"] = $arXMLStore[$this->mess["IBLOCK_XML2_STORE_DESCRIPTION"]];

			if(
				isset($arXMLStore[$this->mess["IBLOCK_XML2_STORE_CONTACTS"]])
				&& is_array($arXMLStore[$this->mess["IBLOCK_XML2_STORE_CONTACTS"]])
			)
			{
				$storeContact = array();
				foreach($arXMLStore[$this->mess["IBLOCK_XML2_STORE_CONTACTS"]] as $arContact)
				{
					if (
						is_array($arContact)
						&& (
							!isset($arContact[$this->mess["IBLOCK_XML2_TYPE"]])
							|| mb_substr($arContact[$this->mess["IBLOCK_XML2_TYPE"]], 0, mb_strlen($this->mess["IBLOCK_XML2_STORE_PHONE"])) === $this->mess["IBLOCK_XML2_STORE_PHONE"]
						)
					)
					{
						$storeContact[] = $arContact[$this->mess["IBLOCK_XML2_VALUE"]];
					}
				}

				if($storeContact)
					$arStore["PHONE"] = implode(", ", $storeContact);
			}

			if (!isset($arDBStores[$arStore["XML_ID"]]))
			{
				if ((count($arDBStores) < 1) || Catalog\Config\Feature::isMultiStoresEnabled())
					$arDBStores[$arStore["XML_ID"]] = $ID = CCatalogStore::Add($arStore);
				else
					$error = GetMessage("IBLOCK_XML2_MULTI_STORE_IMPORT_ERROR");
			}
			else
			{
				if ((count($arDBStores) <= 1) || Catalog\Config\Feature::isMultiStoresEnabled())
					$ID = CCatalogStore::Update($arDBStores[$arStore["XML_ID"]], $arStore);
				else
					$error = GetMessage("IBLOCK_XML2_MULTI_STORE_IMPORT_ERROR");
			}
		}

		if($error)
			return $error;
		if(!$ID)
			return false;
		return true;
	}

	function ImportStoresAmount($arElement, $elementID, &$counter)
	{
		$arFields = array();
		$arFields['PRODUCT_ID'] = $elementID;
		static $arStoreResult = false;
		if ($arStoreResult === false)
		{
			$arStoreResult = $this->getStoreList();
		}

		foreach($arElement as $xmlID => $amount)
		{
			if (isset($arStoreResult[$xmlID]))
			{
				$arFields['STORE_ID'] = $arStoreResult[$xmlID];
				$arFields['AMOUNT'] = $amount;
				$res = CCatalogStoreProduct::UpdateFromForm($arFields);
				if(!$res)
					$counter["ERR"]++;
			}
		}
		return true;
	}

	function ImportSectionsProperties($XML_PARENT, $IBLOCK_ID)
	{
		/** @var CMain $APPLICATION */
		global $APPLICATION;
		$obTypeManager = new CUserTypeEntity;
		$sort = 100;

		$rs = $this->_xml_file->GetList(
			array("ID" => "asc"),
			array("PARENT_ID" => $XML_PARENT),
			array("ID")
		);
		while($ar = $rs->Fetch())
		{
			$XML_ENUM_PARENT = array();
			$arField = array(
			);
			$rsP = $this->_xml_file->GetList(
				array("ID" => "asc"),
				array("PARENT_ID" => $ar["ID"])
			);
			while($arP = $rsP->Fetch())
			{
				if(isset($arP["VALUE_CLOB"]))
					$arP["VALUE"] = $arP["VALUE_CLOB"];

				if($arP["NAME"] == $this->mess["IBLOCK_XML2_ID"])
					$arField["XML_ID"] = $arP["VALUE"];
				elseif($arP["NAME"] == $this->mess["IBLOCK_XML2_NAME"])
					$arField["FIELD_NAME"] = $arP["VALUE"];
				elseif($arP["NAME"] == $this->mess["IBLOCK_XML2_SORT"])
					$arField["SORT"] = $arP["VALUE"];
				elseif($arP["NAME"] == $this->mess["IBLOCK_XML2_MULTIPLE"])
					$arField["MULTIPLE"] = ($arP["VALUE"]=="true") || intval($arP["VALUE"])? "Y": "N";
				elseif($arP["NAME"] == $this->mess["IBLOCK_XML2_BX_PROPERTY_TYPE"])
					$arField["USER_TYPE_ID"] = $arP["VALUE"];
				elseif($arP["NAME"] == $this->mess["IBLOCK_XML2_BX_IS_REQUIRED"])
					$arField["MANDATORY"] = ($arP["VALUE"]=="true") || intval($arP["VALUE"])? "Y": "N";
				elseif($arP["NAME"] == $this->mess["IBLOCK_XML2_BX_FILTER"])
					$arField["SHOW_FILTER"] = ($arP["VALUE"]=="true") || intval($arP["VALUE"])? "Y": "N";
				elseif($arP["NAME"] == $this->mess["IBLOCK_XML2_BX_SHOW_IN_LIST"])
					$arField["SHOW_IN_LIST"] = ($arP["VALUE"]=="true") || intval($arP["VALUE"])? "Y": "N";
				elseif($arP["NAME"] == $this->mess["IBLOCK_XML2_BX_EDIT_IN_LIST"])
					$arField["EDIT_IN_LIST"] = ($arP["VALUE"]=="true") || intval($arP["VALUE"])? "Y": "N";
				elseif($arP["NAME"] == $this->mess["IBLOCK_XML2_BX_SEARCH"])
					$arField["IS_SEARCHABLE"] = ($arP["VALUE"]=="true") || intval($arP["VALUE"])? "Y": "N";
				elseif($arP["NAME"] == $this->mess["IBLOCK_XML2_BX_SETTINGS"])
					$arField["SETTINGS"] = unserialize($arP["VALUE"], ['allowed_classes' => false]);
				elseif($arP["NAME"]==$this->mess["IBLOCK_XML2_CHOICE_VALUES"])
					$XML_ENUM_PARENT = $arP["ID"];
			}

			$rsUserFields = $obTypeManager->GetList(array(), array("ENTITY_ID"=> "IBLOCK_".$IBLOCK_ID."_SECTION", "XML_ID"=>$arField["XML_ID"]));
			$arDBField = $rsUserFields->Fetch();
			if(!$arDBField)
			{
				$rsUserFields = $obTypeManager->GetList(array(), array("ENTITY_ID"=> "IBLOCK_".$IBLOCK_ID."_SECTION", "FIELD_NAME"=>$arField["FIELD_NAME"]));
				$arDBField = $rsUserFields->Fetch();
			}

			if($arDBField)
			{
				$bChanged = false;
				foreach($arField as $key=>$value)
				{
					if($arDBField[$key] !== $value)
					{
						$bChanged = true;
						break;
					}
				}
				if(!$bChanged)
					$arField["ID"] = $arDBField["ID"];
				elseif($obTypeManager->Update($arDBField["ID"], $arField))
					$arField["ID"] = $arDBField["ID"];
				else
				{
					if($e = $APPLICATION->GetException())
						return GetMessage("IBLOCK_XML2_UF_ERROR", array(
							"#XML_ID#" => $arField["XML_ID"],
							"#ERROR_TEXT#" => $e->GetString(),
						));
					else
						return false;
				}
			}
			else
			{
				$arField["ENTITY_ID"] = "IBLOCK_".$IBLOCK_ID."_SECTION";
				if(!array_key_exists("SORT", $arField))
					$arField["SORT"] = $sort;
				$arField["ID"] = $obTypeManager->Add($arField);
				if(!$arField["ID"])
				{
					if($e = $APPLICATION->GetException())
						return GetMessage("IBLOCK_XML2_UF_ERROR", array(
							"#XML_ID#" => $arField["XML_ID"],
							"#ERROR_TEXT#" => $e->GetString(),
						));
					else
						return false;
				}
			}

			if ($XML_ENUM_PARENT)
			{
				$arEnumXmlNodes = array();
				$rsE = $this->_xml_file->GetList(
					array("ID" => "asc"),
					array("PARENT_ID" => $XML_ENUM_PARENT)
				);
				while($arE = $rsE->Fetch())
				{
					if(isset($arE["VALUE_CLOB"]))
						$arE["VALUE"] = $arE["VALUE_CLOB"];
					$arEnumXmlNodes[] = $arE;
				}

				if (!empty($arEnumXmlNodes))
				{
					$this->ImportSectionPropertyEnum($arField["ID"], $arEnumXmlNodes);
				}
			}

			$sort += 100;
		}

		return true;
	}

	function ImportProperties($XML_PROPERTIES_PARENT, $IBLOCK_ID)
	{
		$obProperty = new CIBlockProperty;
		$sort = 100;

		$arElementFields = array(
			"CML2_ACTIVE" => $this->mess["IBLOCK_XML2_BX_ACTIVE"],
			"CML2_CODE" => $this->mess["IBLOCK_XML2_SYMBOL_CODE"],
			"CML2_SORT" => $this->mess["IBLOCK_XML2_SORT"],
			"CML2_ACTIVE_FROM" => $this->mess["IBLOCK_XML2_START_TIME"],
			"CML2_ACTIVE_TO" => $this->mess["IBLOCK_XML2_END_TIME"],
			"CML2_PREVIEW_TEXT" => $this->mess["IBLOCK_XML2_ANONS"],
			"CML2_DETAIL_TEXT" => $this->mess["IBLOCK_XML2_DETAIL"],
			"CML2_PREVIEW_PICTURE" => $this->mess["IBLOCK_XML2_PREVIEW_PICTURE"],
		);

		$rs = $this->_xml_file->GetList(
			array("ID" => "asc"),
			array("PARENT_ID" => $XML_PROPERTIES_PARENT),
			array("ID")
		);
		while($ar = $rs->Fetch())
		{
			$XML_ENUM_PARENT = false;
			$isExternal = false;
			$arProperty = array(
			);
			$rsP = $this->_xml_file->GetList(
				array("ID" => "asc"),
				array("PARENT_ID" => $ar["ID"])
			);
			while($arP = $rsP->Fetch())
			{
				if(isset($arP["VALUE_CLOB"]))
					$arP["VALUE"] = $arP["VALUE_CLOB"];

				if($arP["NAME"]==$this->mess["IBLOCK_XML2_ID"])
				{
					$arProperty["XML_ID"] = $arP["VALUE"];
					if(array_key_exists($arProperty["XML_ID"], $arElementFields))
						break;
				}
				elseif($arP["NAME"]==$this->mess["IBLOCK_XML2_NAME"])
					$arProperty["NAME"] = $arP["VALUE"];
				elseif($arP["NAME"]==$this->mess["IBLOCK_XML2_MULTIPLE"])
					$arProperty["MULTIPLE"] = ($arP["VALUE"]=="true") || intval($arP["VALUE"])? "Y": "N";
				elseif($arP["NAME"]==$this->mess["IBLOCK_XML2_BX_SORT"])
					$arProperty["SORT"] = $arP["VALUE"];
				elseif($arP["NAME"]==$this->mess["IBLOCK_XML2_BX_CODE"])
					$arProperty["CODE"] = $arP["VALUE"];
				elseif($arP["NAME"]==$this->mess["IBLOCK_XML2_BX_DEFAULT_VALUE"])
					$arProperty["DEFAULT_VALUE"] = $arP["VALUE"];
				elseif($arP["NAME"]==$this->mess["IBLOCK_XML2_SERIALIZED"])
					$arProperty["SERIALIZED"] = ($arP["VALUE"]=="true") || intval($arP["VALUE"])? "Y": "N";
				elseif($arP["NAME"]==$this->mess["IBLOCK_XML2_BX_PROPERTY_TYPE"])
				{
					$arProperty["PROPERTY_TYPE"] = $arP["VALUE"];
					$arProperty["USER_TYPE"] = "";
				}
				elseif($arP["NAME"]==$this->mess["IBLOCK_XML2_BX_ROWS"])
					$arProperty["ROW_COUNT"] = $arP["VALUE"];
				elseif($arP["NAME"]==$this->mess["IBLOCK_XML2_BX_COLUMNS"])
					$arProperty["COL_COUNT"] = $arP["VALUE"];
				elseif($arP["NAME"]==$this->mess["IBLOCK_XML2_BX_LIST_TYPE"])
					$arProperty["LIST_TYPE"] = $arP["VALUE"];
				elseif($arP["NAME"]==$this->mess["IBLOCK_XML2_BX_FILE_EXT"])
					$arProperty["FILE_TYPE"] = $arP["VALUE"];
				elseif($arP["NAME"]==$this->mess["IBLOCK_XML2_BX_FIELDS_COUNT"])
					$arProperty["MULTIPLE_CNT"] = $arP["VALUE"];
				elseif($arP["NAME"]==$this->mess["IBLOCK_XML2_BX_USER_TYPE"])
					$arProperty["USER_TYPE"] = $arP["VALUE"];
				elseif($arP["NAME"]==$this->mess["IBLOCK_XML2_BX_WITH_DESCRIPTION"])
					$arProperty["WITH_DESCRIPTION"] = ($arP["VALUE"]=="true") || intval($arP["VALUE"])? "Y": "N";
				elseif($arP["NAME"]==$this->mess["IBLOCK_XML2_BX_SEARCH"])
					$arProperty["SEARCHABLE"] = ($arP["VALUE"]=="true") || intval($arP["VALUE"])? "Y": "N";
				elseif($arP["NAME"]==$this->mess["IBLOCK_XML2_BX_FILTER"])
					$arProperty["FILTRABLE"] = ($arP["VALUE"]=="true") || intval($arP["VALUE"])? "Y": "N";
				elseif($arP["NAME"]==$this->mess["IBLOCK_XML2_BX_LINKED_IBLOCK"])
					$arProperty["LINK_IBLOCK_ID"] = $this->GetIBlockByXML_ID($arP["VALUE"]);
				elseif($arP["NAME"]==$this->mess["IBLOCK_XML2_CHOICE_VALUES"])
					$XML_ENUM_PARENT = $arP["ID"];
				elseif($arP["NAME"]==$this->mess["IBLOCK_XML2_BX_IS_REQUIRED"])
					$arProperty["IS_REQUIRED"] = ($arP["VALUE"]=="true") || intval($arP["VALUE"])? "Y": "N";
				elseif($arP["NAME"]==$this->mess["IBLOCK_XML2_VALUES_TYPE"])
				{
					if(
						$arP["VALUE"] == $this->mess["IBLOCK_XML2_TYPE_LIST"]
						&& !$isExternal
					)
					{
						$arProperty["PROPERTY_TYPE"] = "L";
						$arProperty["USER_TYPE"] = "";
					}
					elseif($arP["VALUE"] == $this->mess["IBLOCK_XML2_TYPE_NUMBER"])
					{
						$arProperty["PROPERTY_TYPE"] = "N";
						$arProperty["USER_TYPE"] = "";
					}
					elseif($arP["VALUE"] == $this->mess["IBLOCK_XML2_TYPE_STRING"])
					{
						$arProperty["PROPERTY_TYPE"] = "S";
						$arProperty["USER_TYPE"] = "";
					}
					elseif($arP["VALUE"] == $this->mess["IBLOCK_XML2_USER_TYPE_DATE"])
					{
						$arProperty["PROPERTY_TYPE"] = "S";
						$arProperty["USER_TYPE"] = "Date";
					}
					elseif($arP["VALUE"] == $this->mess["IBLOCK_XML2_USER_TYPE_DATETIME"])
					{
						$arProperty["PROPERTY_TYPE"] = "S";
						$arProperty["USER_TYPE"] = "DateTime";
					}
				}
				elseif($arP["NAME"]==$this->mess["IBLOCK_XML2_VALUES_TYPES"])
				{
					//This property metadata contains information about it's type
					$rsTypes = $this->_xml_file->GetList(
						array("ID" => "asc"),
						array("PARENT_ID" => $arP["ID"]),
						array("ID", "LEFT_MARGIN", "RIGHT_MARGIN", "NAME")
					);
					$arType = $rsTypes->Fetch();
					//We'll process only properties with NOT composing types
					//composed types will be supported only as simple string properties
					if($arType && !$rsTypes->Fetch())
					{
						$rsType = $this->_xml_file->GetList(
							array("ID" => "asc"),
							array("PARENT_ID" => $arType["ID"]),
							array("ID", "LEFT_MARGIN", "RIGHT_MARGIN", "NAME", "VALUE")
						);
						while($arType = $rsType->Fetch())
						{
							if($arType["NAME"] == $this->mess["IBLOCK_XML2_TYPE"])
							{
								if($arType["VALUE"] == $this->mess["IBLOCK_XML2_TYPE_LIST"])
									$arProperty["PROPERTY_TYPE"] = "L";
								elseif($arType["VALUE"] == $this->mess["IBLOCK_XML2_TYPE_NUMBER"])
									$arProperty["PROPERTY_TYPE"] = "N";
							}
							elseif($arType["NAME"] == $this->mess["IBLOCK_XML2_CHOICE_VALUES"])
							{
								$XML_ENUM_PARENT = $arType["ID"];
							}
						}
					}
				}
				elseif($arP["NAME"]==$this->mess["IBLOCK_XML2_BX_USER_TYPE_SETTINGS"])
				{
					$arProperty["USER_TYPE_SETTINGS"] = unserialize($arP["VALUE"], ['allowed_classes' => false]);
				}
				elseif($arP["NAME"]==$this->mess["IBLOCK_XML2_EXTERNAL"])
				{
					$isExternal = ($arP["VALUE"]=="true") || intval($arP["VALUE"])? true: false;
					if ($isExternal)
					{
						$arProperty["PROPERTY_TYPE"] = "S";
						$arProperty["USER_TYPE"] = "directory";
					}
				}
				elseif($arP["NAME"]==$this->mess["IBLOCK_XML2_BX_PROPERTY_FEATURE_LIST"])
				{
					$arProperty["FEATURES"] = unserialize($arP["VALUE"], ['allowed_classes' => false]);
				}
			}

			if(array_key_exists($arProperty["XML_ID"], $arElementFields))
				continue;

			// Skip properties with no choice values
			// http://jabber.bx/view.php?id=30476
			$arEnumXmlNodes = array();
			if($XML_ENUM_PARENT)
			{
				$rsE = $this->_xml_file->GetList(
					array("ID" => "asc"),
					array("PARENT_ID" => $XML_ENUM_PARENT)
				);
				while($arE = $rsE->Fetch())
				{
					if(isset($arE["VALUE_CLOB"]))
						$arE["VALUE"] = $arE["VALUE_CLOB"];
					$arEnumXmlNodes[] = $arE;
				}

				if (empty($arEnumXmlNodes))
					continue;
			}

			if (isset($arProperty['SERIALIZED']))
			{
				if ($arProperty['SERIALIZED'] === 'Y')
				{
					$arProperty['DEFAULT_VALUE'] = unserialize($arProperty['DEFAULT_VALUE'], ['allowed_classes' => false]);
				}
				unset($arProperty['SERIALIZED']);
			}

			$rsProperty = $obProperty->GetList(
				array(),
				array(
					"IBLOCK_ID"=>$IBLOCK_ID,
					"XML_ID"=>$arProperty["XML_ID"],
				)
			);
			$arDBProperty = $rsProperty->Fetch();
			unset($rsProperty);
			if ($arDBProperty)
			{
				$arDBProperty['FEATURES'] = [];
				$featuresIterator = Iblock\PropertyFeatureTable::getList([
					'select' => [
						'ID',
						'MODULE_ID',
						'FEATURE_ID',
						'IS_ENABLED',
					],
					'filter' => [
						'=PROPERTY_ID' => (int)$arDBProperty['ID'],
					],
					'order' => [
						'ID' => 'ASC',
					],
				]);
				while ($featureRow = $featuresIterator->fetch())
				{
					unset($featureRow['ID']);
					$arDBProperty['FEATURES'][] = $featureRow;
				}
				unset($featureRow, $featuresIterator);

				$bChanged = false;
				foreach($arProperty as $key=>$value)
				{
					if($arDBProperty[$key] !== $value)
					{
						$bChanged = true;
						break;
					}
				}
				if(!$bChanged)
					$arProperty["ID"] = $arDBProperty["ID"];
				elseif($obProperty->Update($arDBProperty["ID"], $arProperty))
					$arProperty["ID"] = $arDBProperty["ID"];
				else
					return $obProperty->LAST_ERROR;
			}
			else
			{
				$arProperty["IBLOCK_ID"] = $IBLOCK_ID;
				$arProperty["ACTIVE"] = "Y";
				if(!array_key_exists("PROPERTY_TYPE", $arProperty))
					$arProperty["PROPERTY_TYPE"] = "S";
				if(!array_key_exists("SORT", $arProperty))
					$arProperty["SORT"] = $sort;
				if(!array_key_exists("CODE", $arProperty))
				{
					$arProperty["CODE"] = $this->safeTranslit($arProperty["NAME"]);
					if(preg_match('/^[0-9]/', $arProperty["CODE"]))
						$arProperty["CODE"] = '_'.$arProperty["CODE"];

					$rsProperty = $obProperty->GetList(array(), array("IBLOCK_ID"=>$IBLOCK_ID, "CODE"=>$arProperty["CODE"]));
					if($arDBProperty = $rsProperty->Fetch())
					{
						$suffix = 0;
						do {
							$suffix++;
							$rsProperty = $obProperty->GetList(array(), array("IBLOCK_ID"=>$IBLOCK_ID, "CODE"=>$arProperty["CODE"]."_".$suffix));
						} while ($rsProperty->Fetch());
						$arProperty["CODE"] .= '_'.$suffix;
					}
				}
				$arProperty["ID"] = $obProperty->Add($arProperty);
				if(!$arProperty["ID"])
					return $obProperty->LAST_ERROR;
			}

			if($XML_ENUM_PARENT)
			{
				if ($isExternal)
					$result = $this->ImportPropertyDirectory($arProperty, $arEnumXmlNodes);
				else
					$result = $this->ImportPropertyEnum($arProperty, $arEnumXmlNodes);

				if ($result !== true)
					return $result;
			}
			$sort += 100;
		}
		return true;
	}

	function ImportPropertyEnum($arProperty, $arEnumXmlNodes)
	{
		$arEnumMap = array();
		$arProperty["VALUES"] = array();
		$rsEnum = CIBlockProperty::GetPropertyEnum($arProperty["ID"]);
		while($arEnum = $rsEnum->Fetch())
		{
			$arProperty["VALUES"][$arEnum["ID"]] = $arEnum;
			$arEnumMap[$arEnum["XML_ID"]] = &$arProperty["VALUES"][$arEnum["ID"]];
		}

		$i = 0;
		foreach($arEnumXmlNodes as $arE)
		{
			if(
				$arE["NAME"] == $this->mess["IBLOCK_XML2_CHOICE"]
				|| $arE["NAME"] == $this->mess["IBLOCK_XML2_CHOICE_VALUE"]
			)
			{
				$arE = $this->_xml_file->GetAllChildrenArray($arE);
				if(isset($arE[$this->mess["IBLOCK_XML2_ID"]]))
				{
					$xml_id = $arE[$this->mess["IBLOCK_XML2_ID"]];
					if(!array_key_exists($xml_id, $arEnumMap))
					{
						$arProperty["VALUES"]["n".$i] = array();
						$arEnumMap[$xml_id] = &$arProperty["VALUES"]["n".$i];
						$i++;
					}
					$arEnumMap[$xml_id]["CML2_EXPORT_FLAG"] = true;
					$arEnumMap[$xml_id]["XML_ID"] = $xml_id;
					if(isset($arE[$this->mess["IBLOCK_XML2_VALUE"]]))
						$arEnumMap[$xml_id]["VALUE"] = $arE[$this->mess["IBLOCK_XML2_VALUE"]];
					if(isset($arE[$this->mess["IBLOCK_XML2_BY_DEFAULT"]]))
						$arEnumMap[$xml_id]["DEF"] = ($arE[$this->mess["IBLOCK_XML2_BY_DEFAULT"]]=="true") || intval($arE[$this->mess["IBLOCK_XML2_BY_DEFAULT"]])? "Y": "N";
					if(isset($arE[$this->mess["IBLOCK_XML2_SORT"]]))
						$arEnumMap[$xml_id]["SORT"] = intval($arE[$this->mess["IBLOCK_XML2_SORT"]]);
				}
			}
			elseif(
				$arE["NAME"] == $this->mess["IBLOCK_XML2_TYPE_LIST"]
			)
			{
				$arE = $this->_xml_file->GetAllChildrenArray($arE);
				if(isset($arE[$this->mess["IBLOCK_XML2_VALUE_ID"]]))
				{
					$xml_id = $arE[$this->mess["IBLOCK_XML2_VALUE_ID"]];
					if(!array_key_exists($xml_id, $arEnumMap))
					{
						$arProperty["VALUES"]["n".$i] = array();
						$arEnumMap[$xml_id] = &$arProperty["VALUES"]["n".$i];
						$i++;
					}
					$arEnumMap[$xml_id]["CML2_EXPORT_FLAG"] = true;
					$arEnumMap[$xml_id]["XML_ID"] = $xml_id;
					if(isset($arE[$this->mess["IBLOCK_XML2_VALUE"]]))
						$arEnumMap[$xml_id]["VALUE"] = $arE[$this->mess["IBLOCK_XML2_VALUE"]];
				}
			}
		}

		$bUpdateOnly = array_key_exists("bUpdateOnly", $this->next_step) && $this->next_step["bUpdateOnly"];
		$sort = 100;

		foreach($arProperty["VALUES"] as $id=>$arEnum)
		{
			if(!isset($arEnum["CML2_EXPORT_FLAG"]))
			{
				//Delete value only when full exchange happened
				if(!$bUpdateOnly)
					$arProperty["VALUES"][$id]["VALUE"] = "";
			}
			elseif(isset($arEnum["SORT"]))
			{
				if($arEnum["SORT"] > $sort)
					$sort = $arEnum["SORT"] + 100;
			}
			else
			{
				$arProperty["VALUES"][$id]["SORT"] = $sort;
				$sort += 100;
			}
		}

		$obProperty = new CIBlockProperty;
		$obProperty->UpdateEnum($arProperty["ID"], $arProperty["VALUES"], false);

		return true;
	}

	/**
	 * @param array $arProperty
	 * @param array $arEnumXmlNodes
	 * @return true|string
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	function ImportPropertyDirectory($arProperty, $arEnumXmlNodes)
	{
		if (!Loader::includeModule('highloadblock'))
			return true;

		$rsProperty = CIBlockProperty::GetList(array(), array("ID"=>$arProperty["ID"]));
		$arProperty = $rsProperty->Fetch();
		if (!$arProperty)
			return true;

		$tableName = 'b_'.strtolower($arProperty["CODE"]);
		if ($arProperty["USER_TYPE_SETTINGS"]["TABLE_NAME"] == '')
		{
			$hlblock = Bitrix\Highloadblock\HighloadBlockTable::getList(array(
				"filter" => array(
					"=TABLE_NAME" => $tableName,
				)))->fetch();
			if (!$hlblock)
			{
				$highBlockName = trim($arProperty["CODE"]);
				$highBlockName = preg_replace('/([^A-Za-z0-9]+)/', '', $highBlockName);
				$highBlockName = preg_replace('/(^[0-9]+)/', '', $highBlockName);
				if ($highBlockName === '')
				{
					return GetMessage('IBLOCK_XML2_HBLOCK_NAME_IS_INVALID');
				}

				$highBlockName = ucfirst($highBlockName);
				$data = [
					'NAME' => $highBlockName,
					'TABLE_NAME' => $tableName,
				];
				$result = Bitrix\Highloadblock\HighloadBlockTable::add($data);
				if (!$result->isSuccess())
				{
					$errors = implode('. ', $result->getErrorMessages());
					if ($errors === '')
					{
						$errors = GetMessage(
							'IBLOCK_XML2_HBLOCK_CREATE_ERROR_UNKNOWN',
							[
								'#ID#' => $arProperty['ID'],
								'#NAME#' => $arProperty['NAME'],
							]
						);
					}
					else
					{
						$errors = GetMessage(
							'IBLOCK_XML2_HBLOCK_CREATE_ERROR',
							[
								'#ID#' => $arProperty['ID'],
								'#NAME#' => $arProperty['NAME'],
								'#ERRORS#' => $errors,
							]
						);
					}

					return $errors;
				}

				$highBlockID = $result->getId();

				$arFieldsName = array(
					'UF_NAME' => array("Y", "string"),
					'UF_XML_ID' => array("Y", "string"),
					'UF_LINK' => array("N", "string"),
					'UF_DESCRIPTION' => array("N", "string"),
					'UF_FULL_DESCRIPTION' => array("N", "string"),
					'UF_SORT' => array("N", "integer"),
					'UF_FILE' => array("N", "file"),
					'UF_DEF' => array("N", "boolean"),
				);
				$obUserField = new CUserTypeEntity();
				$sort = 100;
				foreach($arFieldsName as $fieldName => $fieldValue)
				{
					$arUserField = array(
						"ENTITY_ID" => "HLBLOCK_".$highBlockID,
						"FIELD_NAME" => $fieldName,
						"USER_TYPE_ID" => $fieldValue[1],
						"XML_ID" => "",
						"SORT" => $sort,
						"MULTIPLE" => "N",
						"MANDATORY" => $fieldValue[0],
						"SHOW_FILTER" => "N",
						"SHOW_IN_LIST" => "Y",
						"EDIT_IN_LIST" => "Y",
						"IS_SEARCHABLE" => "N",
						"SETTINGS" => array(),
					);
					$obUserField->Add($arUserField);
					$sort += 100;
				}
			}

			$arProperty["USER_TYPE_SETTINGS"]["TABLE_NAME"] = $tableName;
			$obProperty = new CIBlockProperty;
			$obProperty->Update($arProperty["ID"], $arProperty);
		}

		$hlblock = Bitrix\Highloadblock\HighloadBlockTable::getList(array(
			"filter" => array(
				"=TABLE_NAME" => $arProperty["USER_TYPE_SETTINGS"]["TABLE_NAME"],
			)))->fetch();

		if (empty($hlblock))
		{
			return GetMessage(
				"IBLOCK_XML2_HBLOCK_ABSENT",
				array(
					"#ID#" => $arProperty["ID"],
					"#NAME#" => $arProperty["NAME"]
				)
			);
		}

		$entity = Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
		$entity_data_class = $entity->getDataClass();

		$arEnumMap = array();
		$rsData = $entity_data_class::getList(array(
			"select" => array("ID", "UF_NAME", "UF_XML_ID", "UF_SORT"),
		));
		while($arData = $rsData->fetch())
		{
			$arEnumMap[$arData["UF_XML_ID"]] = $arData;
		}

		$i = 0;
		foreach($arEnumXmlNodes as $arE)
		{
			if(
				$arE["NAME"] == $this->mess["IBLOCK_XML2_TYPE_LIST"]
			)
			{
				$arE = $this->_xml_file->GetAllChildrenArray($arE);
				if(
					isset($arE[$this->mess["IBLOCK_XML2_VALUE_ID"]])
					&& isset($arE[$this->mess["IBLOCK_XML2_VALUE"]])
				)
				{
					$xml_id = $arE[$this->mess["IBLOCK_XML2_VALUE_ID"]];
					$arFields = array(
						"UF_XML_ID" => $xml_id,
						"UF_NAME" => $arE[$this->mess["IBLOCK_XML2_VALUE"]],
					);
					if (isset($arE[$this->mess["IBLOCK_XML2_PICTURE"]]))
					{
						$arFields["UF_FILE"] = $this->MakeFileArray($arE[$this->mess["IBLOCK_XML2_PICTURE"]]);
					}

					if(!array_key_exists($xml_id, $arEnumMap))
					{
						$entity_data_class::add($arFields);
					}
					elseif ($arEnumMap[$xml_id]["UF_NAME"] !== $arFields['UF_NAME'])
					{
						$entity_data_class::update($arEnumMap[$xml_id]["ID"], $arFields);
					}
				}
			}
		}

		return true;
	}

	function ImportSectionPropertyEnum($FIELD_ID, $arEnumXmlNodes)
	{
		$arEnumMap = array();
		$arEnumValues = array();
		$obEnum = new CUserFieldEnum;
		$rsEnum = $obEnum->GetList(array(), array(
			"USER_FIELD_ID" => $FIELD_ID,
		));
		while($arEnum = $rsEnum->Fetch())
		{
			$arEnumValues[$arEnum["ID"]] = $arEnum;
			$arEnumMap[$arEnum["XML_ID"]] = &$arEnumValues[$arEnum["ID"]];
		}

		$i = 0;
		foreach($arEnumXmlNodes as $arE)
		{
			if($arE["NAME"] == $this->mess["IBLOCK_XML2_CHOICE"])
			{
				$arE = $this->_xml_file->GetAllChildrenArray($arE);
				if(isset($arE[$this->mess["IBLOCK_XML2_ID"]]))
				{
					$xml_id = $arE[$this->mess["IBLOCK_XML2_ID"]];
					if(!array_key_exists($xml_id, $arEnumMap))
					{
						$arEnumValues["n".$i] = array();
						$arEnumMap[$xml_id] = &$arEnumValues["n".$i];
						$i++;
					}
					$arEnumMap[$xml_id]["CML2_EXPORT_FLAG"] = true;
					$arEnumMap[$xml_id]["XML_ID"] = $xml_id;
					if(isset($arE[$this->mess["IBLOCK_XML2_VALUE"]]))
						$arEnumMap[$xml_id]["VALUE"] = $arE[$this->mess["IBLOCK_XML2_VALUE"]];
					if(isset($arE[$this->mess["IBLOCK_XML2_BY_DEFAULT"]]))
						$arEnumMap[$xml_id]["DEF"] = ($arE[$this->mess["IBLOCK_XML2_BY_DEFAULT"]]=="true") || intval($arE[$this->mess["IBLOCK_XML2_BY_DEFAULT"]])? "Y": "N";
					if(isset($arE[$this->mess["IBLOCK_XML2_SORT"]]))
						$arEnumMap[$xml_id]["SORT"] = intval($arE[$this->mess["IBLOCK_XML2_SORT"]]);
				}
			}
		}

		$bUpdateOnly = array_key_exists("bUpdateOnly", $this->next_step) && $this->next_step["bUpdateOnly"];
		$sort = 100;

		foreach($arEnumValues as $id=>$arEnum)
		{
			if(!isset($arEnum["CML2_EXPORT_FLAG"]))
			{
				//Delete value only when full exchange happened
				if(!$bUpdateOnly)
					$arEnumValues[$id]["VALUE"] = "";
			}
			elseif(isset($arEnum["SORT"]))
			{
				if($arEnum["SORT"] > $sort)
					$sort = $arEnum["SORT"] + 100;
			}
			else
			{
				$arEnumValues[$id]["SORT"] = $sort;
				$sort += 100;
			}
		}

		$obEnum = new CUserFieldEnum;
		$res = $obEnum->SetEnumValues($FIELD_ID, $arEnumValues);

		return true;
	}

	function ImportSectionProperties($XML_SECTION_PROPERTIES, $IBLOCK_ID, $SECTION_ID = 0)
	{
		if($SECTION_ID == 0)
		{
			CIBlockSectionPropertyLink::DeleteByIBlock($IBLOCK_ID);
			$ib = new CIBlock;
			$ib->Update($IBLOCK_ID, array("SECTION_PROPERTY" => "Y"));
		}

		$rs = $this->_xml_file->GetList(
			array("ID" => "asc"),
			array("PARENT_ID" => $XML_SECTION_PROPERTIES),
			array("ID")
		);
		while($ar = $rs->Fetch())
		{
			$iblockId = 0;
			$sectionId = 0;
			$arLink = array(
			);
			$rsP = $this->_xml_file->GetList(
				array("ID" => "asc"),
				array("PARENT_ID" => $ar["ID"])
			);
			while($arP = $rsP->Fetch())
			{
				if($arP["NAME"]==$this->mess["IBLOCK_XML2_ID"])
					$arLink["XML_ID"] = $arP["VALUE"];
				elseif($arP["NAME"]==$this->mess["IBLOCK_XML2_SMART_FILTER"])
					$arLink["SMART_FILTER"] = ($arP["VALUE"]=="true") || intval($arP["VALUE"])? "Y": "N";
				elseif($arP["NAME"]==$this->mess["IBLOCK_XML2_SMART_FILTER_DISPLAY_TYPE"])
					$arLink["DISPLAY_TYPE"] = $arP["VALUE"];
				elseif($arP["NAME"]==$this->mess["IBLOCK_XML2_SMART_FILTER_DISPLAY_EXPANDED"])
					$arLink["DISPLAY_EXPANDED"] = ($arP["VALUE"]=="true") || intval($arP["VALUE"])? "Y": "N";
				elseif($arP["NAME"]==$this->mess["IBLOCK_XML2_SMART_FILTER_HINT"])
					$arLink["FILTER_HINT"] = $arP["VALUE"];
				elseif($arP["NAME"]==$this->mess["IBLOCK_XML2_BX_LINKED_IBLOCK"])
					$iblockId = $this->GetIBlockByXML_ID($arP["VALUE"]);
				elseif($arP["NAME"]==$this->mess["IBLOCK_XML2_GROUP"] && $iblockId > 0)
					$sectionId = $this->GetSectionByXML_ID($iblockId, $arP["VALUE"]);
			}

			if ($iblockId > 0)
				$arLink["IBLOCK_ID"] = $iblockId;

			$rsProperty = CIBlockProperty::GetList(array(), array(
				"IBLOCK_ID" => $IBLOCK_ID,
				"XML_ID" => $arLink["XML_ID"],
				"CHECK_PERMISSIONS" => "N",
			));
			if($arDBProperty = $rsProperty->Fetch())
			{
				CIBlockSectionPropertyLink::Add($sectionId? $sectionId: $SECTION_ID, $arDBProperty["ID"], $arLink);
			}
		}
		return true;
	}

	function ReadCatalogData(&$SECTION_MAP, &$PRICES_MAP)
	{
		if(!is_array($SECTION_MAP))
		{
			$SECTION_MAP = array();
			$rsSections = CIBlockSection::GetList(array(), array("IBLOCK_ID"=>$this->next_step["IBLOCK_ID"]), false);
			while($ar = $rsSections->Fetch())
				$SECTION_MAP[$ar["XML_ID"]] = $ar["ID"];
			unset($rsSections);
		}
		$this->SECTION_MAP = $SECTION_MAP;

		if(!is_array($PRICES_MAP))
		{
			$arPrices = array();
			if($this->bCatalog)
			{
				$arPrices = CCatalogGroup::GetListArray();
			}

			$PRICES_MAP = array();
			if(isset($this->next_step["XML_PRICES_PARENT"]))
			{
				$rs = $this->_xml_file->GetList(
					array("ID" => "asc"),
					array("PARENT_ID" => $this->next_step["XML_PRICES_PARENT"])
				);
				while($arParent = $rs->Fetch())
				{
					if(isset($arParent["VALUE_CLOB"]))
						$arParent["VALUE"] = $arParent["VALUE_CLOB"];
					$arXMLPrice = $this->_xml_file->GetAllChildrenArray($arParent);
					$PRICE_ID =  $arXMLPrice[$this->mess["IBLOCK_XML2_ID"]];
					$PRICE_NAME =  $arXMLPrice[$this->mess["IBLOCK_XML2_NAME"]];
					$arPrice = array(
						"NAME" => $PRICE_NAME,
						"XML_ID" => $PRICE_ID,
						"CURRENCY" => $arXMLPrice[$this->mess["IBLOCK_XML2_CURRENCY"]] ?? '',
						"TAX_NAME" => $arXMLPrice[$this->mess["IBLOCK_XML2_TAX"]][$this->mess["IBLOCK_XML2_NAME"]] ?? '',
						"TAX_IN_SUM" => $arXMLPrice[$this->mess["IBLOCK_XML2_TAX"]][$this->mess["IBLOCK_XML2_IN_SUM"]] ?? '',
					);
					if($this->bCatalog)
					{
						$found_id = 0;
						//Check for price by XML_ID
						if (isset($PRICE_ID) && $PRICE_ID != "")
						{
							foreach($arPrices as $price)
							{
								if ($PRICE_ID === $price["XML_ID"])
								{
									$found_id = $price["ID"];
									break;
								}
							}
						}
						//When lookup by it's name
						if (!$found_id)
						{
							foreach($arPrices as $price)
							{
								if ($PRICE_NAME === $price["NAME"])
								{
									$found_id = $price["ID"];
									break;
								}
							}
						}

						if($found_id)
							$arPrice["ID"] = $found_id;
						else
							$arPrice["ID"] = 0;
					}
					else
					{
						$obProperty = new CIBlockProperty;
						$rsProperty = $obProperty->GetList(array(), array("IBLOCK_ID"=>$this->next_step["IBLOCK_ID"], "XML_ID"=>$arPrice["XML_ID"]));
						if($ar = $rsProperty->Fetch())
							$arPrice["ID"] = $ar["ID"];
						else
							$arPrice["ID"] = 0;
					}
					$PRICES_MAP[$PRICE_ID] = $arPrice;
				}
			}
			else
			{
				foreach($arPrices as $arPrice)
				{
					$arPrice['TAX_NAME'] = '';
					$arPrice['TAX_IN_SUM'] = '';
					$PRICES_MAP[$arPrice["XML_ID"]] = $arPrice;
				}
			}
		}
		$this->PRICES_MAP = $PRICES_MAP;
	}

	function GetElementCRC($arElement)
	{
		if(!is_array($arElement))
		{
			$parent_id = $arElement;
			$rsElement = $this->_xml_file->GetList(
				array("ID" => "asc"),
				array("PARENT_ID" => $parent_id)
			);
			$arElement = array();
			while($ar = $rsElement->Fetch())
				$arElement[] = array(
					$ar["NAME"],
					$ar["VALUE"],
					$ar["VALUE_CLOB"],
					$ar["ATTRIBUTES"],
				);
		}
		$c = crc32(print_r($arElement, true));
		if($c > 0x7FFFFFFF)
			$c = -(0xFFFFFFFF - $c + 1);
		return $c;
	}

	function CheckManufacturer($xml)
	{
		if (!$xml)
		{
			return "";
		}

		if (!isset($this->PROPERTY_MAP["CML2_MANUFACTURER"]))
		{
			return '';
		}

		$propertyId = $this->PROPERTY_MAP["CML2_MANUFACTURER"];
		$enumXmlId = $xml[$this->mess["IBLOCK_XML2_ID"]];
		$enumName = $xml[$this->mess["IBLOCK_XML2_NAME"]];
		if ($enumXmlId == '')
		{
			return "";
		}
		$enumValue = CIBlockPropertyEnum::GetList(array(), array(
			"PROPERTY_ID" => $propertyId,
			"XML_ID" => $enumXmlId,
		));
		$enum = $enumValue->Fetch();

		if ($enum)
		{
			if ($enum["VALUE"] !== $enumName)
			{
				CIBlockPropertyEnum::Update($enum["ID"], array(
					"VALUE" => $enumName,
				));
			}
			return $enum["ID"];
		}
		else
		{
			return CIBlockPropertyEnum::Add(array(
				"VALUE" => $enumName,
				"PROPERTY_ID" => $propertyId,
				"DEF" => "N",
				"XML_ID" => $enumXmlId,
			));
		}
	}

	/**
	 * @return bool
	 */
	public function GetTotalCountElementsForImport()
	{
		$result = true;
		if (isset($this->next_step["XML_ELEMENTS_PARENT"]) && $this->next_step["XML_ELEMENTS_PARENT"] > 0)
		{
			if (!isset($this->next_step["DONE"]))
				$this->next_step["DONE"] = [];
			if (!isset($this->next_step["DONE"]["ALL"]))
				$this->next_step["DONE"]["ALL"] = 0;
			if ($this->next_step["DONE"]["ALL"] <= 0)
			{
				if ($this->_xml_file->IsExistTemporaryTable())
				{
					$this->next_step["DONE"]["ALL"] = $this->_xml_file->GetCountItemsWithParent($this->next_step["XML_ELEMENTS_PARENT"]);
				}
				else
				{
					$this->LAST_ERROR = GetMessage("IBLOCK_XML2_TEMPORARY_TABLE_EXIST_ERROR");
					$result = false;
				}
			}
		}
		return $result;
	}

	/**
	 * @param int $start_time
	 * @param int $interval
	 * @return array
	 */
	public function ImportElements($start_time, $interval)
	{
		global $DB;
		$counter = array(
			"ADD" => 0,
			"UPD" => 0,
			"DEL" => 0,
			"DEA" => 0,
			"ERR" => 0,
			"CRC" => 0,
		);
		if($this->next_step["XML_ELEMENTS_PARENT"])
		{
			Iblock\PropertyIndex\Manager::enableDeferredIndexing();
			if ($this->bCatalog)
				Catalog\Product\Sku::enableDeferredCalculation();
			$this->activeStores = $this->getActiveStores();

			$obElement = new CIBlockElement();
			$obElement->CancelWFSetMove();
			$bWF = Loader::includeModule("workflow");
			$filter = [
				'PARENT_ID' => $this->next_step['XML_ELEMENTS_PARENT'],
			];
			if (isset($this->next_step['XML_LAST_ID']))
			{
				$filter['>ID'] = $this->next_step['XML_LAST_ID'];
			}
			$rsParents = $this->_xml_file->GetList(
				array("ID" => "asc"),
				$filter,
				array("ID", "LEFT_MARGIN", "RIGHT_MARGIN")
			);
			while($arParent = $rsParents->Fetch())
			{
				if (!$arParent["RIGHT_MARGIN"])
					continue;

				$counter["CRC"]++;

				$arXMLElement = $this->_xml_file->GetAllChildrenArray($arParent);
				$hashPosition = strrpos($arXMLElement[$this->mess["IBLOCK_XML2_ID"]], "#");

				if(!$this->next_step["bOffer"] && $this->use_offers)
				{
					if($hashPosition !== false)
					{
						$this->next_step["XML_LAST_ID"] = $arParent["ID"];
						continue;
					}
				}
				if(array_key_exists($this->mess["IBLOCK_XML2_STATUS"], $arXMLElement) && ($arXMLElement[$this->mess["IBLOCK_XML2_STATUS"]] == $this->mess["IBLOCK_XML2_DELETED"]))
				{
					$ID = $this->GetElementByXML_ID($this->next_step["IBLOCK_ID"], $arXMLElement[$this->mess["IBLOCK_XML2_ID"]]);
					if($ID && $obElement->Update($ID, array("ACTIVE"=>"N"), $bWF))
					{
						if($this->use_offers)
							$this->ChangeOffersStatus($ID, "N", $bWF);
						$counter["DEA"]++;
					}
					else
					{
						$counter["ERR"]++;
					}
				}
				elseif(array_key_exists($this->mess["IBLOCK_XML2_BX_TAGS"], $arXMLElement))
				{
					//This is our export file
					$ID = $this->ImportElement($arXMLElement, $counter, $bWF, $arParent);
				}
				else
				{
					$this->arFileDescriptionsMap = array();
					$this->arElementFilesId = array();
					$this->arElementFiles = array();
					//offers.xml
					if ($this->next_step["bOffer"])
					{
						if (!$this->use_offers)
						{
							//We have only one information block
							$ID = $this->ImportElementPrices($arXMLElement, $counter, $arParent);
						}
						elseif ($hashPosition === false && !$this->force_offers)
						{
							//We have separate offers iblock and there is element price
							$ID = $this->ImportElementPrices($arXMLElement, $counter, $arParent);
						}
						else
						{
							$xmlKeys = array_keys($arXMLElement);
							if ($xmlKeys == array($this->mess["IBLOCK_XML2_ID"], $this->mess["IBLOCK_XML2_PRICES"]))
							{
								//prices.xml
								$ID = $this->ImportElementPrices($arXMLElement, $counter, $arParent);
							}
							elseif ($xmlKeys == array($this->mess["IBLOCK_XML2_ID"], $this->mess["IBLOCK_XML2_RESTS"]))
							{
								//rests.xml
								$ID = $this->ImportElementPrices($arXMLElement, $counter, $arParent);
							}
							else
							{
								//It's an offer in offers iblock
								$ID = $this->ImportElement($arXMLElement, $counter, $bWF, $arParent);
							}
						}
					}
					//import.xml
					else
					{
						$ID = $this->ImportElement($arXMLElement, $counter, $bWF, $arParent);
					}
				}

				if($ID)
				{
					$ipropValues = new \Bitrix\Iblock\InheritedProperty\ElementValues($this->next_step["IBLOCK_ID"], $ID);
					$ipropValues->clearValues();

					$DB->Query("UPDATE b_iblock_element SET TIMESTAMP_X = ".$DB->CurrentTimeFunction()." WHERE ID=".$ID);
					$this->_xml_file->Add(array("PARENT_ID" => 0, "LEFT_MARGIN" => $ID));
				}

				$this->next_step["XML_LAST_ID"] = $arParent["ID"];

				if($interval > 0 && (time()-$start_time) > (2*$interval/3))
					break;
			}
			if ($this->bCatalog)
			{
				Catalog\Product\Sku::disableDeferredCalculation();
				Catalog\Product\Sku::calculate();
			}
			Iblock\PropertyIndex\Manager::disableDeferredIndexing();
			Iblock\PropertyIndex\Manager::runDeferredIndexing($this->next_step["IBLOCK_ID"]);
		}
		$this->CleanTempFiles();
		return $counter;
	}

	function ImportProductSets()
	{
		if ($this->bCatalog && isset($this->next_step["SETS"]) && $this->next_step["SETS"] > 0)
		{
			$rsParents = $this->_xml_file->GetList(
				array("ID" => "asc"),
				array("PARENT_ID" => $this->next_step["SETS"]),
				array("ID", "LEFT_MARGIN", "RIGHT_MARGIN")
			);
			while($arParent = $rsParents->Fetch())
			{
				$arXMLElement = $this->_xml_file->GetAllChildrenArray($arParent);
				if(isset($arXMLElement[$this->mess["IBLOCK_XML2_ID"]]))
				{
					$rsElement = CIBlockElement::GetList(
						array(),
						array("=XML_ID" => $arXMLElement[$this->mess["IBLOCK_XML2_ID"]], "IBLOCK_ID" => $this->next_step["IBLOCK_ID"]),
						false, false,
						array("ID", "IBLOCK_ID")
					);
					if ($arDBElement = $rsElement->Fetch())
					{
						CCatalogProductSet::deleteAllSetsByProduct($arDBElement["ID"], CCatalogProductSet::TYPE_GROUP);
						if (isset($arXMLElement[$this->mess["IBLOCK_XML2_PRODUCT_SET"]]))
						{
							$arFields = array(
								"ITEM_ID" => $arDBElement["ID"],
								"TYPE" => CCatalogProductSet::TYPE_GROUP,
								"ITEMS" => array(),
							);
							foreach ($arXMLElement[$this->mess["IBLOCK_XML2_PRODUCT_SET"]] as $xmlSet)
							{
								$arFields["ITEMS"][] = array(
									"ITEM_ID" => $this->GetElementByXML_ID($arDBElement["IBLOCK_ID"], $xmlSet[$this->mess["IBLOCK_XML2_VALUE"]]),
									"SORT" => intval($xmlSet[$this->mess["IBLOCK_XML2_SORT"]]),
									"QUANTITY" => intval($xmlSet[$this->mess["IBLOCK_XML2_AMOUNT"]]),
								);
							}
							$ps = new CCatalogProductSet;
							$ps->add($arFields);
						}
					}
				}
			}
		}
	}

	function ChangeOffersStatus($ELEMENT_ID, $STATUS = "Y", $bWF = true)
	{
		if($this->arLinkedProps === false)
		{
			$this->arLinkedProps = array();
			$obProperty = new CIBlockProperty;
			$rsProperty = $obProperty->GetList(array(), array("LINK_IBLOCK_ID"=>$this->next_step["IBLOCK_ID"], "XML_ID"=>"CML2_LINK"));
			while($arProperty = $rsProperty->Fetch())
				$this->arLinkedProps[] = $arProperty;
		}
		$obElement = new CIBlockElement;
		$obElement->CancelWFSetMove();
		$elementFields = array("ACTIVE"=>$STATUS);
		if ((string)\Bitrix\Main\Config\Option::get('iblock', 'change_user_by_group_active_modify') === 'Y')
		{
			$elementFields['MODIFIED_BY'] = $this->currentUserId;
		}
		foreach($this->arLinkedProps as $arProperty)
		{
			$rsElements = $obElement->GetList(
				Array("ID"=>"asc"),
				Array(
					"PROPERTY_".$arProperty["ID"] => $ELEMENT_ID,
					"IBLOCK_ID" => $arProperty["IBLOCK_ID"],
					"ACTIVE" => $STATUS=="Y"? "N": "Y",
				),
				false, false,
				Array("ID", "TMP_ID")
			);
			while($arElement = $rsElements->Fetch())
				$obElement->Update($arElement["ID"], $elementFields, $bWF);
		}
	}

	function safeTranslit($str)
	{
		$params = array(
			"max_len" => 50,
			"change_case" => 'U', // 'L' - toLower, 'U' - toUpper, false - do not change
			"replace_space" => '_',
			"replace_other" => '_',
			"delete_repeat_replace" => true,
		);

		$result = CUtil::translit($str, LANGUAGE_ID, $params);
		$result = preg_replace("/[^a-zA-Z0-9_]/", $params["replace_other"], $result);
		if ($params["delete_repeat_replace"])
			$result = preg_replace("/".preg_quote($params["replace_other"], "/")."+/", $params["replace_other"], $result);

		return $result;
	}

	/**
	 * @param string $str
	 * @return float
	 */
	public function ToFloat($str)
	{
		static $search = false;
		static $replace = false;
		if(!$search)
		{
			if (isset($this->next_step["sdp"]) && $this->next_step["sdp"] !== '')
			{
				$search = array("\xc2\xa0", "\xa0", " ", $this->next_step["sdp"], ",");
				$replace = array("", "", "", ".", ".");
			}
			else
			{
				$search = array("\xc2\xa0", "\xa0", " ", ",");
				$replace = array("", "", "", ".");
			}
		}

		return (float)str_replace($search, $replace, $str);
	}

	/**
	 * @param string $value
	 * @return float|string
	 */
	public function ToFloatEmpty($value)
	{
		return ($value === '' ? '' : $this->ToFloat($value));
	}

	/**
	 * @param string $str
	 * @return int
	 */
	public function ToInt($str)
	{
		static $search = false;
		static $replace = false;
		if(!$search)
		{
			if (isset($this->next_step["sdp"]) && $this->next_step["sdp"] !== '')
			{
				$search = array("\xa0", " ", $this->next_step["sdp"], ",");
				$replace = array("", "", ".", ".");
			}
			else
			{
				$search = array("\xa0", " ", ",");
				$replace = array("", "", ".");
			}
		}

		return (int)str_replace($search, $replace, $str);
	}

	function Unserialize($string)
	{
		$decoded_string = preg_replace_callback('/(s:\d+:")(.*?)(";)/s', array($this, "__unserialize_callback"), $string);
		return unserialize($decoded_string, ['allowed_classes' => false]);
	}

	function __unserialize_callback($match)
	{
		return 's:'.strlen($match[2]).':"'.$match[2].'";';
	}

	function convertBaseUnitFromXmlToPropertyValue($xmlValue)
	{
		static $cacheValue = array();
		static $cacheDescr = array();
		$xmlValue = (int)$xmlValue;
		if (!isset($cacheValue[$xmlValue]))
		{
			$cacheValue[$xmlValue] = $xmlValue;
			$cacheDescr[$xmlValue] = false;
			if ($xmlValue > 0 && Loader::includeModule('catalog'))
			{
				$rsBaseUnit = CCatalogMeasure::GetList(array(), array("CODE" => $xmlValue));
				$arIDUnit = $rsBaseUnit->Fetch();
				if ($arIDUnit)
				{
					$cacheValue[$xmlValue] = $arIDUnit["SYMBOL_RUS"];
					$cacheDescr[$xmlValue] = $arIDUnit["ID"];
				}
				else
				{
					$rsBaseUnit = CCatalogMeasure::GetList(array(), array("ID" => $xmlValue));
					$arIDUnit = $rsBaseUnit->Fetch();
					if ($arIDUnit)
					{
						$cacheValue[$xmlValue] = $arIDUnit["SYMBOL_RUS"];
						$cacheDescr[$xmlValue] = $arIDUnit["ID"];
					}
				}
			}
		}

		return array(
			"VALUE" => $cacheValue[$xmlValue],
			"DESCRIPTION" => $cacheDescr[$xmlValue],
		);
	}

	function CheckIfElementIsActive($arXMLElement)
	{
		$bActive = true; //by default
		if(isset($arXMLElement[$this->mess["IBLOCK_XML2_PROPERTIES_VALUES"]]))
		{
			foreach($arXMLElement[$this->mess["IBLOCK_XML2_PROPERTIES_VALUES"]] as $value)
			{
				if($value[$this->mess["IBLOCK_XML2_ID"]] === "CML2_ACTIVE")
				{
					if($value[$this->mess["IBLOCK_XML2_VALUE"]] === "false" || $value[$this->mess["IBLOCK_XML2_VALUE"]] === "0")
					{
						$bActive = false;
						break;
					}
				}
			}
		}
		return $bActive;
	}

	function ImportElement($arXMLElement, &$counter, $bWF, $arParent)
	{
		$arElement = array(
			"ACTIVE" => "Y",
			"PROPERTY_VALUES" => array(),
		);

		if(isset($arXMLElement[$this->mess["IBLOCK_XML2_VERSION"]]))
			$arElement["TMP_ID"] = $arXMLElement[$this->mess["IBLOCK_XML2_VERSION"]];
		else
			$arElement["TMP_ID"] = $this->GetElementCRC($arXMLElement);

		if(isset($arXMLElement[$this->mess["IBLOCK_XML2_ID_1C_SITE"]]))
			$arElement["XML_ID"] = $arXMLElement[$this->mess["IBLOCK_XML2_ID_1C_SITE"]];
		elseif(isset($arXMLElement[$this->mess["IBLOCK_XML2_ID"]]))
			$arElement["XML_ID"] = $arXMLElement[$this->mess["IBLOCK_XML2_ID"]];

		$obElement = new CIBlockElement;
		$obElement->CancelWFSetMove();
		$rsElement = $obElement->GetList(
			Array("ID"=>"asc"),
			Array("=XML_ID" => $arElement["XML_ID"], "IBLOCK_ID" => $this->next_step["IBLOCK_ID"]),
			false, false,
			Array("ID", "TMP_ID", "ACTIVE", "CODE", "PREVIEW_PICTURE", "DETAIL_PICTURE")
		);

		$bMatch = false;
		if($arDBElement = $rsElement->Fetch())
			$bMatch = ($arElement["TMP_ID"] == $arDBElement["TMP_ID"]);

		if($bMatch && $this->use_crc)
		{
			//Check Active flag in XML is not set to false
			if($this->CheckIfElementIsActive($arXMLElement))
			{
				//In case element is not active in database we have to activate it and its offers
				if($arDBElement["ACTIVE"] != "Y")
				{
					$obElement->Update($arDBElement["ID"], array("ACTIVE"=>"Y"), $bWF);
					$this->ChangeOffersStatus($arDBElement["ID"], "Y", $bWF);
					$counter["UPD"]++;
				}
			}
			$arElement["ID"] = $arDBElement["ID"];
		}
		elseif(isset($arXMLElement[$this->mess["IBLOCK_XML2_NAME"]]))
		{
			if($arDBElement)
			{
				if ($arDBElement["PREVIEW_PICTURE"] > 0)
					$this->arElementFilesId["PREVIEW_PICTURE"] = array($arDBElement["PREVIEW_PICTURE"]);
				if ($arDBElement["DETAIL_PICTURE"] > 0)
					$this->arElementFilesId["DETAIL_PICTURE"] = array($arDBElement["DETAIL_PICTURE"]);

				$rsProperties = $obElement->GetProperty($this->next_step["IBLOCK_ID"], $arDBElement["ID"], "sort", "asc");
				while($arProperty = $rsProperties->Fetch())
				{
					if(!array_key_exists($arProperty["ID"], $arElement["PROPERTY_VALUES"]))
						$arElement["PROPERTY_VALUES"][$arProperty["ID"]] = array(
							"bOld" => true,
						);

					$arElement["PROPERTY_VALUES"][$arProperty["ID"]][$arProperty['PROPERTY_VALUE_ID']] = array(
						"VALUE"=>$arProperty['VALUE'],
						"DESCRIPTION"=>$arProperty["DESCRIPTION"]
					);

					if($arProperty["PROPERTY_TYPE"] == "F" && $arProperty["VALUE"] > 0)
						$this->arElementFilesId[$arProperty["ID"]][] = $arProperty["VALUE"];
				}
			}

			if ($this->bCatalog && $this->next_step["bOffer"])
			{
				if (isset($this->PROPERTY_MAP["CML2_LINK"]))
				{
					$p = strpos($arXMLElement[$this->mess["IBLOCK_XML2_ID"]], "#");
					if ($p !== false)
					{
						$link_xml_id = substr($arXMLElement[$this->mess["IBLOCK_XML2_ID"]], 0, $p);
					}
					else
					{
						$link_xml_id = $arXMLElement[$this->mess["IBLOCK_XML2_ID"]];
					}

					$arElement["PROPERTY_VALUES"][$this->PROPERTY_MAP["CML2_LINK"]] = [
						"n0" => [
							"VALUE" => $this->GetElementByXML_ID(
								$this->arProperties[$this->PROPERTY_MAP["CML2_LINK"]]["LINK_IBLOCK_ID"],
								$link_xml_id
							),
							"DESCRIPTION" => false,
						],
					];
				}
			}

			if(isset($arXMLElement[$this->mess["IBLOCK_XML2_NAME"]]))
				$arElement["NAME"] = $arXMLElement[$this->mess["IBLOCK_XML2_NAME"]];

			if(isset($arXMLElement[$this->mess["IBLOCK_XML2_DELETE_MARK"]]))
			{
				$value = $arXMLElement[$this->mess["IBLOCK_XML2_DELETE_MARK"]];
				$arElement["ACTIVE"] = ($value=="true") || intval($value)? "N": "Y";
			}

			if(array_key_exists($this->mess["IBLOCK_XML2_BX_TAGS"], $arXMLElement))
				$arElement["TAGS"] = $arXMLElement[$this->mess["IBLOCK_XML2_BX_TAGS"]];

			if(array_key_exists($this->mess["IBLOCK_XML2_DESCRIPTION"], $arXMLElement))
			{
				if($arXMLElement[$this->mess["IBLOCK_XML2_DESCRIPTION"]] <> '')
					$arElement["DETAIL_TEXT"] = $arXMLElement[$this->mess["IBLOCK_XML2_DESCRIPTION"]];
				else
					$arElement["DETAIL_TEXT"] = "";

				if(preg_match('/<[a-zA-Z0-9]+.*?>/', $arElement["DETAIL_TEXT"]))
					$arElement["DETAIL_TEXT_TYPE"] = "html";
				else
					$arElement["DETAIL_TEXT_TYPE"] = "text";
			}

			if(array_key_exists($this->mess["IBLOCK_XML2_FULL_TITLE"], $arXMLElement))
			{
				if($arXMLElement[$this->mess["IBLOCK_XML2_FULL_TITLE"]] <> '')
					$arElement["PREVIEW_TEXT"] = $arXMLElement[$this->mess["IBLOCK_XML2_FULL_TITLE"]];
				else
					$arElement["PREVIEW_TEXT"] = "";

				if(preg_match('/<[a-zA-Z0-9]+.*?>/', $arElement["PREVIEW_TEXT"]))
					$arElement["PREVIEW_TEXT_TYPE"] = "html";
				else
					$arElement["PREVIEW_TEXT_TYPE"] = "text";
			}

			if(array_key_exists($this->mess["IBLOCK_XML2_INHERITED_TEMPLATES"], $arXMLElement))
			{
				$arElement["IPROPERTY_TEMPLATES"] = array();
				foreach($arXMLElement[$this->mess["IBLOCK_XML2_INHERITED_TEMPLATES"]] as $TEMPLATE)
				{
					$id = $TEMPLATE[$this->mess["IBLOCK_XML2_ID"]];
					$template = $TEMPLATE[$this->mess["IBLOCK_XML2_VALUE"]];
					if($id <> '' && $template <> '')
						$arElement["IPROPERTY_TEMPLATES"][$id] = $template;
				}
			}
			if (isset($this->PROPERTY_MAP["CML2_BAR_CODE"]))
			{
				if (array_key_exists($this->mess["IBLOCK_XML2_BAR_CODE2"], $arXMLElement))
				{
					$arElement["PROPERTY_VALUES"][$this->PROPERTY_MAP["CML2_BAR_CODE"]] = [
						"n0" => [
							"VALUE" => $arXMLElement[$this->mess["IBLOCK_XML2_BAR_CODE2"]],
							"DESCRIPTION" => false,
						],
					];
				}
				elseif (array_key_exists($this->mess["IBLOCK_XML2_BAR_CODE"], $arXMLElement))
				{
					$arElement["PROPERTY_VALUES"][$this->PROPERTY_MAP["CML2_BAR_CODE"]] = [
						"n0" => [
							"VALUE" => $arXMLElement[$this->mess["IBLOCK_XML2_BAR_CODE"]],
							"DESCRIPTION" => false,
						],
					];
				}
			}

			if (isset($this->PROPERTY_MAP["CML2_ARTICLE"]))
			{
				if (array_key_exists($this->mess["IBLOCK_XML2_ARTICLE"], $arXMLElement))
				{
					$arElement["PROPERTY_VALUES"][$this->PROPERTY_MAP["CML2_ARTICLE"]] = [
						"n0" => [
							"VALUE" => $arXMLElement[$this->mess["IBLOCK_XML2_ARTICLE"]],
							"DESCRIPTION" => false,
						],
					];
				}
			}

			if (
				array_key_exists($this->mess["IBLOCK_XML2_MANUFACTURER"], $arXMLElement)
				&& isset($this->PROPERTY_MAP["CML2_MANUFACTURER"])
				&& $this->PROPERTY_MAP["CML2_MANUFACTURER"] > 0
			)
			{
				$arElement["PROPERTY_VALUES"][$this->PROPERTY_MAP["CML2_MANUFACTURER"]] = array(
					"n0" => array(
						"VALUE" => $this->CheckManufacturer($arXMLElement[$this->mess["IBLOCK_XML2_MANUFACTURER"]]),
						"DESCRIPTION" => false,
					),
				);
			}

			if(array_key_exists($this->mess["IBLOCK_XML2_PICTURE"], $arXMLElement))
			{
				$rsFiles = $this->_xml_file->GetList(
					array("ID" => "asc"),
					array("PARENT_ID" => $arParent["ID"], "NAME" => $this->mess["IBLOCK_XML2_PICTURE"])
				);
				$arFile = $rsFiles->Fetch();
				if($arFile)
				{
					$description = "";
					if($arFile["ATTRIBUTES"] <> '')
					{
						$arAttributes = unserialize($arFile["ATTRIBUTES"], ['allowed_classes' => false]);
						if(is_array($arAttributes) && array_key_exists($this->mess["IBLOCK_XML2_DESCRIPTION"], $arAttributes))
						{
							$description = $arAttributes[$this->mess["IBLOCK_XML2_DESCRIPTION"]];
						}
					}

					if($arFile["VALUE"] <> '')
					{
						$arElement["DETAIL_PICTURE"] = $this->ResizePicture($arFile["VALUE"], $this->detail, "DETAIL_PICTURE", $this->PROPERTY_MAP["CML2_PICTURES"] ?? '');

						if(is_array($arElement["DETAIL_PICTURE"]))
						{
							$arElement["DETAIL_PICTURE"]["description"] = $description;
							$this->arFileDescriptionsMap[$arFile["VALUE"]][] = &$arElement["DETAIL_PICTURE"]["description"];
						}

						if(is_array($this->preview))
						{
							$arElement["PREVIEW_PICTURE"] = $this->ResizePicture($arFile["VALUE"], $this->preview, "PREVIEW_PICTURE");
							if(is_array($arElement["PREVIEW_PICTURE"]))
							{
								$arElement["PREVIEW_PICTURE"]["description"] = $description;
								$this->arFileDescriptionsMap[$arFile["VALUE"]][] = &$arElement["PREVIEW_PICTURE"]["description"];
							}
						}
					}
					else
					{
						$arElement["DETAIL_PICTURE"] = $this->MakeFileArray($this->_xml_file->GetAllChildrenArray($arFile["ID"]));

						if(is_array($arElement["DETAIL_PICTURE"]))
						{
							$arElement["DETAIL_PICTURE"]["description"] = $description;
						}
					}

					$prop_id = (int)($this->PROPERTY_MAP["CML2_PICTURES"] ?? 0);
					if($prop_id > 0)
					{
						$i = 1;
						while($arFile = $rsFiles->Fetch())
						{
							$description = "";
							if($arFile["ATTRIBUTES"] <> '')
							{
								$arAttributes = unserialize($arFile["ATTRIBUTES"], ['allowed_classes' => false]);
								if(is_array($arAttributes) && array_key_exists($this->mess["IBLOCK_XML2_DESCRIPTION"], $arAttributes))
								{
									$description = $arAttributes[$this->mess["IBLOCK_XML2_DESCRIPTION"]];
								}
							}

							if($arFile["VALUE"] <> '')
								$arPropFile = $this->ResizePicture($arFile["VALUE"], $this->detail, $this->PROPERTY_MAP["CML2_PICTURES"], "DETAIL_PICTURE");
							else
								$arPropFile = $this->MakeFileArray($this->_xml_file->GetAllChildrenArray($arFile["ID"]));

							if(is_array($arPropFile))
							{
								$arPropFile = array(
									"VALUE" => $arPropFile,
									"DESCRIPTION" => $description,
								);
							}
							$arElement["PROPERTY_VALUES"][$prop_id]["n".$i] = $arPropFile;
							if ($arFile["VALUE"] <> '')
								$this->arFileDescriptionsMap[$arFile["VALUE"]][] = &$arElement["PROPERTY_VALUES"][$prop_id]["n".$i]["DESCRIPTION"];
							$i++;
						}

						if(
							isset($arElement["PROPERTY_VALUES"][$prop_id])
							&& is_array($arElement["PROPERTY_VALUES"][$prop_id]))
						{
							foreach($arElement["PROPERTY_VALUES"][$prop_id] as $PROPERTY_VALUE_ID => $PROPERTY_VALUE)
							{
								if(!$PROPERTY_VALUE_ID)
									unset($arElement["PROPERTY_VALUES"][$prop_id][$PROPERTY_VALUE_ID]);
								elseif(strncmp($PROPERTY_VALUE_ID, "n", 1) !== 0)
									$arElement["PROPERTY_VALUES"][$prop_id][$PROPERTY_VALUE_ID] = array(
										"tmp_name" => "",
										"del" => "Y",
									);
							}
							unset($arElement["PROPERTY_VALUES"][$prop_id]["bOld"]);
						}
					}
				}
			}

			$cleanCml2FilesProperty = false;
			if(
				array_key_exists($this->mess["IBLOCK_XML2_FILE"], $arXMLElement)
				&& isset($this->PROPERTY_MAP["CML2_FILES"])
			)
			{
				$prop_id = $this->PROPERTY_MAP["CML2_FILES"];
				$rsFiles = $this->_xml_file->GetList(
					array("ID" => "asc"),
					array("PARENT_ID" => $arParent["ID"], "NAME" => $this->mess["IBLOCK_XML2_FILE"])
				);
				$i = 1;
				while($arFile = $rsFiles->Fetch())
				{

					if($arFile["VALUE"] <> '')
						$file = $this->MakeFileArray($arFile["VALUE"], array($prop_id));
					else
						$file = $this->MakeFileArray($this->_xml_file->GetAllChildrenArray($arFile["ID"]));

					$arElement["PROPERTY_VALUES"][$prop_id]["n".$i] = array(
						"VALUE" => $file,
						"DESCRIPTION" => $file["description"],
					);
					if($arFile["ATTRIBUTES"] <> '')
					{
						$desc = unserialize($arFile["ATTRIBUTES"], ['allowed_classes' => false]);
						if(is_array($desc) && array_key_exists($this->mess["IBLOCK_XML2_DESCRIPTION"], $desc))
						{
							$arElement["PROPERTY_VALUES"][$prop_id]["n".$i]["DESCRIPTION"] = $desc[$this->mess["IBLOCK_XML2_DESCRIPTION"]];
						}
					}
					$i++;
				}
				$cleanCml2FilesProperty = true;
			}

			if(array_key_exists($this->mess["IBLOCK_XML2_GROUPS"], $arXMLElement))
			{
				$arElement["IBLOCK_SECTION"] = array();
				if (
					!empty($arXMLElement[$this->mess["IBLOCK_XML2_GROUPS"]])
					&& is_array($arXMLElement[$this->mess["IBLOCK_XML2_GROUPS"]])
				)
				{
					foreach ($arXMLElement[$this->mess["IBLOCK_XML2_GROUPS"]] as $value)
					{
						if (array_key_exists($value, $this->SECTION_MAP))
							$arElement["IBLOCK_SECTION"][] = $this->SECTION_MAP[$value];
					}
				}
				if($arElement["IBLOCK_SECTION"])
					$arElement["IBLOCK_SECTION_ID"] = $arElement["IBLOCK_SECTION"][0];
			}

			if(array_key_exists($this->mess["IBLOCK_XML2_PRICES"], $arXMLElement))
			{//Collect price information for future use
				$arElement["PRICES"] = array();
				if (is_array($arXMLElement[$this->mess["IBLOCK_XML2_PRICES"]]))
				{
					foreach($arXMLElement[$this->mess["IBLOCK_XML2_PRICES"]] as $price)
					{
						if(isset($price[$this->mess["IBLOCK_XML2_PRICE_TYPE_ID"]]) && array_key_exists($price[$this->mess["IBLOCK_XML2_PRICE_TYPE_ID"]], $this->PRICES_MAP))
						{
							$price["PRICE"] = $this->PRICES_MAP[$price[$this->mess["IBLOCK_XML2_PRICE_TYPE_ID"]]];
							$arElement["PRICES"][] = $price;
						}
					}
				}

				$arElement["DISCOUNTS"] = array();
				if(isset($arXMLElement[$this->mess["IBLOCK_XML2_DISCOUNTS"]]))
				{
					foreach($arXMLElement[$this->mess["IBLOCK_XML2_DISCOUNTS"]] as $discount)
					{
						if(
							isset($discount[$this->mess["IBLOCK_XML2_DISCOUNT_CONDITION"]])
							&& $discount[$this->mess["IBLOCK_XML2_DISCOUNT_CONDITION"]]===$this->mess["IBLOCK_XML2_DISCOUNT_COND_VOLUME"]
						)
						{
							$discount_value = $this->ToInt($discount[$this->mess["IBLOCK_XML2_DISCOUNT_COND_VALUE"]]);
							$discount_percent = $this->ToFloat($discount[$this->mess["IBLOCK_XML2_DISCOUNT_COND_PERCENT"]]);
							if($discount_value > 0 && $discount_percent > 0)
								$arElement["DISCOUNTS"][$discount_value] = $discount_percent;
						}
					}
				}
			}

			if($this->bCatalog && array_key_exists($this->mess["IBLOCK_XML2_AMOUNT"], $arXMLElement))
			{
				$arElement["QUANTITY_RESERVED"] = 0;
				if($arDBElement)
				{
					$iterator = Catalog\Model\Product::getList([
						'select' => ['ID', 'QUANTITY_RESERVED'],
						'filter' => ['=ID' => $arDBElement['ID']]
					]);
					$arElementTmp = $iterator->fetch();
					if (isset($arElementTmp["QUANTITY_RESERVED"]))
						$arElement["QUANTITY_RESERVED"] = (float)$arElementTmp["QUANTITY_RESERVED"];
					unset($arElementTmp);
					unset($iterator);
				}
				$arElement["QUANTITY"] = $this->ToFloat($arXMLElement[$this->mess["IBLOCK_XML2_AMOUNT"]]) - $arElement["QUANTITY_RESERVED"];
			}

			if (
				isset($arXMLElement[$this->mess["IBLOCK_XML2_ITEM_ATTRIBUTES"]])
				&& isset($this->PROPERTY_MAP["CML2_ATTRIBUTES"])
			)
			{
				$arElement["PROPERTY_VALUES"][$this->PROPERTY_MAP["CML2_ATTRIBUTES"]] = array();
				$i = 0;
				foreach($arXMLElement[$this->mess["IBLOCK_XML2_ITEM_ATTRIBUTES"]] as $value)
				{
					$arElement["PROPERTY_VALUES"][$this->PROPERTY_MAP["CML2_ATTRIBUTES"]]["n".$i] = array(
						"VALUE" => $value[$this->mess["IBLOCK_XML2_VALUE"]],
						"DESCRIPTION" => $value[$this->mess["IBLOCK_XML2_NAME"]],
					);
					$i++;
				}
			}

			$i = 0;
			$weightKey = false;
			if (isset($arXMLElement[$this->mess["IBLOCK_XML2_TRAITS_VALUES"]]))
			{
				if (isset($this->PROPERTY_MAP["CML2_TRAITS"]))
				{
					$arElement["PROPERTY_VALUES"][$this->PROPERTY_MAP["CML2_TRAITS"]] = [];
				}
				foreach($arXMLElement[$this->mess["IBLOCK_XML2_TRAITS_VALUES"]] as $value)
				{
					if(
						!array_key_exists("PREVIEW_TEXT", $arElement)
						&& $value[$this->mess["IBLOCK_XML2_NAME"]] == $this->mess["IBLOCK_XML2_FULL_TITLE2"]
					)
					{
						$arElement["PREVIEW_TEXT"] = $value[$this->mess["IBLOCK_XML2_VALUE"]];
						if(strpos($arElement["PREVIEW_TEXT"], "<") !== false)
							$arElement["PREVIEW_TEXT_TYPE"] = "html";
						else
							$arElement["PREVIEW_TEXT_TYPE"] = "text";
					}
					elseif(
						$value[$this->mess["IBLOCK_XML2_NAME"]] == $this->mess["IBLOCK_XML2_HTML_DESCRIPTION"]
					)
					{
						if($value[$this->mess["IBLOCK_XML2_VALUE"]] <> '')
						{
							$arElement["DETAIL_TEXT"] = $value[$this->mess["IBLOCK_XML2_VALUE"]];
							$arElement["DETAIL_TEXT_TYPE"] = "html";
						}
					}
					elseif(
						$value[$this->mess["IBLOCK_XML2_NAME"]] == $this->mess["IBLOCK_XML2_FILE"]
					)
					{
						if($value[$this->mess["IBLOCK_XML2_VALUE"]] <> '')
						{
							$prop_id = $this->PROPERTY_MAP["CML2_FILES"] ?? 0;

							$j = 1;
							while (isset($arElement["PROPERTY_VALUES"][$prop_id]["n".$j]))
								$j++;

							$file = $this->MakeFileArray($value[$this->mess["IBLOCK_XML2_VALUE"]], array($prop_id));
							if (is_array($file))
							{
								$arElement["PROPERTY_VALUES"][$prop_id]["n".$j] = array(
									"VALUE" => $file,
									"DESCRIPTION" => "",
								);
								unset($arElement["PROPERTY_VALUES"][$prop_id]["bOld"]);
								$this->arFileDescriptionsMap[$value[$this->mess["IBLOCK_XML2_VALUE"]]][] = &$arElement["PROPERTY_VALUES"][$prop_id]["n".$j]["DESCRIPTION"];
								$cleanCml2FilesProperty = true;
							}
						}
					}
					elseif(
						$value[$this->mess["IBLOCK_XML2_NAME"]] == $this->mess["IBLOCK_XML2_FILE_DESCRIPTION"]
					)
					{
						if($value[$this->mess["IBLOCK_XML2_VALUE"]] <> '')
						{
							[$fileName, $description] = explode("#", $value[$this->mess["IBLOCK_XML2_VALUE"]]);
							if (isset($this->arFileDescriptionsMap[$fileName]))
							{
								foreach($this->arFileDescriptionsMap[$fileName] as $k => $tmp)
									$this->arFileDescriptionsMap[$fileName][$k] = $description;
							}
						}
					}
					else
					{
						if($value[$this->mess["IBLOCK_XML2_NAME"]] == $this->mess["IBLOCK_XML2_WEIGHT"])
						{
							$arElement["BASE_WEIGHT"] = $this->ToFloat($value[$this->mess["IBLOCK_XML2_VALUE"]])*1000;
							$weightKey = "n".$i;
						}
						if (isset($this->PROPERTY_MAP["CML2_TRAITS"]))
						{
							$arElement["PROPERTY_VALUES"][$this->PROPERTY_MAP["CML2_TRAITS"]]["n" . $i] = [
								"VALUE" => $value[$this->mess["IBLOCK_XML2_VALUE"]],
								"DESCRIPTION" => $value[$this->mess["IBLOCK_XML2_NAME"]],
							];
						}
						$i++;
					}
				}
			}

			if (isset($arXMLElement[$this->mess["IBLOCK_XML2_WEIGHT"]]))
			{
				if (isset($this->PROPERTY_MAP["CML2_TRAITS"]))
				{
					if ($weightKey === false)
					{
						if (!isset($arElement["PROPERTY_VALUES"][$this->PROPERTY_MAP["CML2_TRAITS"]]))
						{
							$arElement["PROPERTY_VALUES"][$this->PROPERTY_MAP["CML2_TRAITS"]] = [];
							$weightKey = "n0";
						}
						else // $weightKey === false && isset($arElement["PROPERTY_VALUES"][$this->PROPERTY_MAP["CML2_TRAITS"]])
						{
							$weightKey = "n" . $i;
						}
					}
					$arElement["PROPERTY_VALUES"][$this->PROPERTY_MAP["CML2_TRAITS"]][$weightKey] = [
						"VALUE" => $arXMLElement[$this->mess["IBLOCK_XML2_WEIGHT"]],
						"DESCRIPTION" => $this->mess["IBLOCK_XML2_WEIGHT"],
					];
				}
				$arElement["BASE_WEIGHT"] = $this->ToFloat($arXMLElement[$this->mess["IBLOCK_XML2_WEIGHT"]])*1000;
			}

			if ($this->bCatalog)
			{
				$arElement = array_merge(
					$arElement,
					$this->getProductFieldsFromXml($arXMLElement)
				);
			}

			if ($cleanCml2FilesProperty)
			{
				$prop_id = $this->PROPERTY_MAP["CML2_FILES"] ?? 0;
				if (
					isset($arElement["PROPERTY_VALUES"][$prop_id])
					&& is_array($arElement["PROPERTY_VALUES"][$prop_id])
				)
				{
					foreach($arElement["PROPERTY_VALUES"][$prop_id] as $PROPERTY_VALUE_ID => $PROPERTY_VALUE)
					{
						if(!$PROPERTY_VALUE_ID)
							unset($arElement["PROPERTY_VALUES"][$prop_id][$PROPERTY_VALUE_ID]);
						elseif(strncmp($PROPERTY_VALUE_ID, "n", 1) !== 0)
							$arElement["PROPERTY_VALUES"][$prop_id][$PROPERTY_VALUE_ID] = array(
								"tmp_name" => "",
								"del" => "Y",
							);
					}
					unset($arElement["PROPERTY_VALUES"][$prop_id]["bOld"]);
				}
			}

			if (isset($arXMLElement[$this->mess["IBLOCK_XML2_TAXES_VALUES"]]))
			{
				if (isset($this->PROPERTY_MAP["CML2_TAXES"]))
				{
					$arElement["PROPERTY_VALUES"][$this->PROPERTY_MAP["CML2_TAXES"]] = [];
					$i = 0;
					foreach ($arXMLElement[$this->mess["IBLOCK_XML2_TAXES_VALUES"]] as $value)
					{
						$arElement["PROPERTY_VALUES"][$this->PROPERTY_MAP["CML2_TAXES"]]["n" . $i] = [
							"VALUE" => $value[$this->mess["IBLOCK_XML2_TAX_VALUE"]],
							"DESCRIPTION" => $value[$this->mess["IBLOCK_XML2_NAME"]],
						];
						$i++;
					}
				}
			}

			$rsBaseUnit = $this->_xml_file->GetList(
				array("ID" => "asc"),
				array(
					"><LEFT_MARGIN" => array($arParent["LEFT_MARGIN"], $arParent["RIGHT_MARGIN"]),
					"NAME" => $this->mess["IBLOCK_XML2_BASE_UNIT"],
				),
				array("ID", "ATTRIBUTES")
			);
			while ($arBaseUnit = $rsBaseUnit->Fetch())
			{
				if($arBaseUnit["ATTRIBUTES"] <> '')
				{
					$info = unserialize($arBaseUnit["ATTRIBUTES"], ['allowed_classes' => false]);
					if(
						is_array($info)
						&& array_key_exists($this->mess["IBLOCK_XML2_CODE"], $info)
					)
					{
						$arXMLElement[$this->mess["IBLOCK_XML2_BASE_UNIT"]] = $info[$this->mess["IBLOCK_XML2_CODE"]];
					}
				}
			}

			if(isset($arXMLElement[$this->mess["IBLOCK_XML2_BASE_UNIT"]]))
			{
				$unitData = $this->convertBaseUnitFromXmlToPropertyValue($arXMLElement[$this->mess["IBLOCK_XML2_BASE_UNIT"]]);
				if (isset($this->PROPERTY_MAP["CML2_BASE_UNIT"]))
				{
					$arElement["PROPERTY_VALUES"][$this->PROPERTY_MAP["CML2_BASE_UNIT"]] = [
						"n0" => $unitData,
					];
				}
				if ($unitData["DESCRIPTION"])
				{
					$arElement['MEASURE'] = $unitData["DESCRIPTION"];
				}
			}

			if(isset($arXMLElement[$this->mess["IBLOCK_XML2_PROPERTIES_VALUES"]]))
			{
				foreach($arXMLElement[$this->mess["IBLOCK_XML2_PROPERTIES_VALUES"]] as $value)
				{
					if(!array_key_exists($this->mess["IBLOCK_XML2_ID"], $value))
						continue;

					$prop_id = $value[$this->mess["IBLOCK_XML2_ID"]];
					unset($value[$this->mess["IBLOCK_XML2_ID"]]);

					//Handle properties which is actually element fields
					if(!array_key_exists($prop_id, $this->PROPERTY_MAP))
					{
						if($prop_id == "CML2_CODE")
							$arElement["CODE"] = $value[$this->mess["IBLOCK_XML2_VALUE"]] ?? "";
						elseif($prop_id == "CML2_ACTIVE")
						{
							$value = array_pop($value);
							$arElement["ACTIVE"] = ($value=="true") || intval($value)? "Y": "N";
						}
						elseif($prop_id == "CML2_SORT")
							$arElement["SORT"] = array_pop($value);
						elseif($prop_id == "CML2_ACTIVE_FROM")
							$arElement["ACTIVE_FROM"] = CDatabase::FormatDate(array_pop($value), "YYYY-MM-DD HH:MI:SS", CLang::GetDateFormat("FULL"));
						elseif($prop_id == "CML2_ACTIVE_TO")
							$arElement["ACTIVE_TO"] = CDatabase::FormatDate(array_pop($value), "YYYY-MM-DD HH:MI:SS", CLang::GetDateFormat("FULL"));
						elseif($prop_id == "CML2_PREVIEW_TEXT")
						{
							if(array_key_exists($this->mess["IBLOCK_XML2_VALUE"], $value))
							{
								if(isset($value[$this->mess["IBLOCK_XML2_VALUE"]]))
									$arElement["PREVIEW_TEXT"] = $value[$this->mess["IBLOCK_XML2_VALUE"]];
								else
									$arElement["PREVIEW_TEXT"] = "";

								if(isset($value[$this->mess["IBLOCK_XML2_TYPE"]]))
									$arElement["PREVIEW_TEXT_TYPE"] = $value[$this->mess["IBLOCK_XML2_TYPE"]];
								else
									$arElement["PREVIEW_TEXT_TYPE"] = "html";
							}
						}
						elseif($prop_id == "CML2_DETAIL_TEXT")
						{
							if(array_key_exists($this->mess["IBLOCK_XML2_VALUE"], $value))
							{
								if(isset($value[$this->mess["IBLOCK_XML2_VALUE"]]))
									$arElement["DETAIL_TEXT"] = $value[$this->mess["IBLOCK_XML2_VALUE"]];
								else
									$arElement["DETAIL_TEXT"] = "";

								if(isset($value[$this->mess["IBLOCK_XML2_TYPE"]]))
									$arElement["DETAIL_TEXT_TYPE"] = $value[$this->mess["IBLOCK_XML2_TYPE"]];
								else
									$arElement["DETAIL_TEXT_TYPE"] = "html";
							}
						}
						elseif($prop_id == "CML2_PREVIEW_PICTURE")
						{
							if(!is_array($this->preview) || !$arElement["PREVIEW_PICTURE"])
							{
								$arElement["PREVIEW_PICTURE"] = $this->MakeFileArray($value[$this->mess["IBLOCK_XML2_VALUE"]], array("PREVIEW_PICTURE"));
								$arElement["PREVIEW_PICTURE"]["COPY_FILE"] = "Y";
							}
						}

						continue;
					}

					$prop_id = $this->PROPERTY_MAP[$prop_id];
					$prop_type = $this->arProperties[$prop_id]["PROPERTY_TYPE"];

					if(!array_key_exists($prop_id, $arElement["PROPERTY_VALUES"]))
						$arElement["PROPERTY_VALUES"][$prop_id] = array();

					//check for bitrix extended format
					if(array_key_exists($this->mess["IBLOCK_XML2_PROPERTY_VALUE"], $value))
					{
						$i = 1;
						$strPV = $this->mess["IBLOCK_XML2_PROPERTY_VALUE"];
						$lPV = mb_strlen($strPV);
						foreach($value as $k=>$prop_value)
						{
							if ($prop_value === null)
							{
								continue;
							}
							if(mb_substr($k, 0, $lPV) === $strPV)
							{
								if(array_key_exists($this->mess["IBLOCK_XML2_SERIALIZED"], $prop_value))
									$prop_value[$this->mess["IBLOCK_XML2_VALUE"]] = $this->Unserialize($prop_value[$this->mess["IBLOCK_XML2_VALUE"]]);
								if($prop_type=="F")
								{
									$prop_value[$this->mess["IBLOCK_XML2_VALUE"]] = $this->MakeFileArray($prop_value[$this->mess["IBLOCK_XML2_VALUE"]], array($prop_id));
								}
								elseif($prop_type=="G")
									$prop_value[$this->mess["IBLOCK_XML2_VALUE"]] = $this->GetSectionByXML_ID($this->arProperties[$prop_id]["LINK_IBLOCK_ID"], $prop_value[$this->mess["IBLOCK_XML2_VALUE"]]);
								elseif($prop_type=="E")
									$prop_value[$this->mess["IBLOCK_XML2_VALUE"]] = $this->GetElementByXML_ID($this->arProperties[$prop_id]["LINK_IBLOCK_ID"], $prop_value[$this->mess["IBLOCK_XML2_VALUE"]]);
								elseif($prop_type=="L")
									$prop_value[$this->mess["IBLOCK_XML2_VALUE"]] = $this->GetEnumByXML_ID($this->arProperties[$prop_id]["ID"], $prop_value[$this->mess["IBLOCK_XML2_VALUE"]]);

								if(array_key_exists("bOld", $arElement["PROPERTY_VALUES"][$prop_id]))
								{
									if($prop_type=="F")
									{
										foreach($arElement["PROPERTY_VALUES"][$prop_id] as $PROPERTY_VALUE_ID => $PROPERTY_VALUE)
											$arElement["PROPERTY_VALUES"][$prop_id][$PROPERTY_VALUE_ID] = array(
												"tmp_name" => "",
												"del" => "Y",
											);
										unset($arElement["PROPERTY_VALUES"][$prop_id]["bOld"]);
									}
									else
										$arElement["PROPERTY_VALUES"][$prop_id] = array();
								}

								$arElement["PROPERTY_VALUES"][$prop_id]["n".$i] = array(
									"VALUE" => $prop_value[$this->mess["IBLOCK_XML2_VALUE"]],
									"DESCRIPTION" => $prop_value[$this->mess["IBLOCK_XML2_DESCRIPTION"]] ?? null,
								);
								$i++;
							}
						}
					}
					else
					{
						if($prop_type == "L" && !array_key_exists($this->mess["IBLOCK_XML2_VALUE_ID"], $value))
							$l_key = $this->mess["IBLOCK_XML2_VALUE"];
						else
							$l_key = $this->mess["IBLOCK_XML2_VALUE_ID"];

						$i = 0;
						foreach($value as $k=>$prop_value)
						{
							if(array_key_exists("bOld", $arElement["PROPERTY_VALUES"][$prop_id]))
							{
								if($prop_type=="F")
								{
									foreach($arElement["PROPERTY_VALUES"][$prop_id] as $PROPERTY_VALUE_ID => $PROPERTY_VALUE)
										$arElement["PROPERTY_VALUES"][$prop_id][$PROPERTY_VALUE_ID] = array(
											"tmp_name" => "",
											"del" => "Y",
										);
									unset($arElement["PROPERTY_VALUES"][$prop_id]["bOld"]);
								}
								else
								{
									$arElement["PROPERTY_VALUES"][$prop_id] = array();
								}
							}

							if($prop_type == "L" && $k == $l_key)
							{
								$prop_value = $this->GetEnumByXML_ID($this->arProperties[$prop_id]["ID"], $prop_value);
							}
							elseif($prop_type == "N" && isset($this->next_step["sdp"]))
							{
								if ($prop_value <> '')
									$prop_value = $this->ToFloat($prop_value);
							}

							$arElement["PROPERTY_VALUES"][$prop_id]["n".$i] = array(
								"VALUE" => $prop_value,
								"DESCRIPTION" => false,
							);
							$i++;
						}
					}
				}
			}

			//If there is no BaseUnit specified check prices for it
			if (isset($this->PROPERTY_MAP["CML2_BASE_UNIT"]))
			{
				if (
					(
						!array_key_exists($this->PROPERTY_MAP["CML2_BASE_UNIT"], $arElement["PROPERTY_VALUES"])
						|| (
							is_array($arElement["PROPERTY_VALUES"][$this->PROPERTY_MAP["CML2_BASE_UNIT"]])
							&& array_key_exists("bOld",
								$arElement["PROPERTY_VALUES"][$this->PROPERTY_MAP["CML2_BASE_UNIT"]])
						)
					)
					&& isset($arXMLElement[$this->mess["IBLOCK_XML2_PRICES"]])
				)
				{
					foreach ($arXMLElement[$this->mess["IBLOCK_XML2_PRICES"]] as $price)
					{
						if (
							isset($price[$this->mess["IBLOCK_XML2_PRICE_TYPE_ID"]])
							&& array_key_exists($price[$this->mess["IBLOCK_XML2_PRICE_TYPE_ID"]], $this->PRICES_MAP)
							&& array_key_exists($this->mess["IBLOCK_XML2_MEASURE"], $price)
						)
						{
							$arElement["PROPERTY_VALUES"][$this->PROPERTY_MAP["CML2_BASE_UNIT"]] = [
								"n0" => $this->convertBaseUnitFromXmlToPropertyValue($price[$this->mess["IBLOCK_XML2_MEASURE"]]),
							];
							break;
						}
					}
				}
			}

			if($arDBElement)
			{
				foreach($arElement["PROPERTY_VALUES"] as $prop_id=>$prop)
				{
					if(is_array($arElement["PROPERTY_VALUES"][$prop_id]) && array_key_exists("bOld", $arElement["PROPERTY_VALUES"][$prop_id]))
					{
						if($this->arProperties[$prop_id]["PROPERTY_TYPE"]=="F")
							unset($arElement["PROPERTY_VALUES"][$prop_id]);
						else
							unset($arElement["PROPERTY_VALUES"][$prop_id]["bOld"]);
					}
				}

				if (!isset($arElement['MODIFIED_BY']) || (int)$arElement['MODIFIED_BY'] <= 0)
				{
					if ($this->currentUserId > 0)
					{
						$arElement['MODIFIED_BY'] = $this->currentUserId;
					}
				}

				if(!array_key_exists("CODE", $arElement) && is_array($this->translit_on_update))
				{
					$CODE = CUtil::translit($arElement["NAME"], LANGUAGE_ID, $this->translit_on_update);
					$CODE = $this->CheckElementCode($this->next_step["IBLOCK_ID"], $arDBElement["ID"], $CODE);
					if($CODE !== false)
						$arElement["CODE"] = $CODE;
				}

				//Check if detail picture hasn't been changed
				if (
					isset($arElement["DETAIL_PICTURE"])
					&& !isset($arElement["PREVIEW_PICTURE"])
					&& is_array($arElement["DETAIL_PICTURE"])
					&& isset($arElement["DETAIL_PICTURE"]["external_id"])
					&& $this->arElementFilesId
					&& $this->arElementFilesId["DETAIL_PICTURE"]
					&& isset($this->arElementFiles[$this->arElementFilesId["DETAIL_PICTURE"][0]])
					&& $this->arElementFiles[$this->arElementFilesId["DETAIL_PICTURE"][0]]["EXTERNAL_ID"] === $arElement["DETAIL_PICTURE"]["external_id"]
					&& $this->arElementFiles[$this->arElementFilesId["DETAIL_PICTURE"][0]]["DESCRIPTION"] === $arElement["DETAIL_PICTURE"]["description"]
				)
				{
					unset($arElement["DETAIL_PICTURE"]);
				}

				$updateResult = $obElement->Update($arDBElement["ID"], $arElement, $bWF, true, $this->iblock_resize);
				//In case element was not active in database we have to activate its offers
				if($arDBElement["ACTIVE"] != "Y")
				{
					$this->ChangeOffersStatus($arDBElement["ID"], "Y", $bWF);
				}
				$arElement["ID"] = $arDBElement["ID"];
				if($updateResult)
				{
					$counter["UPD"]++;
				}
				else
				{
					$this->LAST_ERROR = $obElement->LAST_ERROR;
					$counter["ERR"]++;
				}
			}
			else
			{
				if(!array_key_exists("CODE", $arElement) && is_array($this->translit_on_add))
				{
					$CODE = CUtil::translit($arElement["NAME"], LANGUAGE_ID, $this->translit_on_add);
					$CODE = $this->CheckElementCode($this->next_step["IBLOCK_ID"], 0, $CODE);
					if($CODE !== false)
						$arElement["CODE"] = $CODE;
				}

				$arElement["IBLOCK_ID"] = $this->next_step["IBLOCK_ID"];
				$this->fillDefaultPropertyValues($arElement, $this->arProperties);

				$arElement["ID"] = $obElement->Add($arElement, $bWF, true, $this->iblock_resize);
				if($arElement["ID"])
				{
					$counter["ADD"]++;
				}
				else
				{
					$this->LAST_ERROR = $obElement->LAST_ERROR;
					$counter["ERR"]++;
				}
			}
		}
		elseif(array_key_exists($this->mess["IBLOCK_XML2_PRICES"], $arXMLElement))
		{
			//Collect price information for future use
			$arElement["PRICES"] = array();
			if (is_array($arXMLElement[$this->mess["IBLOCK_XML2_PRICES"]]))
			{
				foreach($arXMLElement[$this->mess["IBLOCK_XML2_PRICES"]] as $price)
				{
					if(isset($price[$this->mess["IBLOCK_XML2_PRICE_TYPE_ID"]]) && array_key_exists($price[$this->mess["IBLOCK_XML2_PRICE_TYPE_ID"]], $this->PRICES_MAP))
					{
						$price["PRICE"] = $this->PRICES_MAP[$price[$this->mess["IBLOCK_XML2_PRICE_TYPE_ID"]]];
						$arElement["PRICES"][] = $price;
					}
				}
			}

			$arElement["DISCOUNTS"] = array();
			if(isset($arXMLElement[$this->mess["IBLOCK_XML2_DISCOUNTS"]]))
			{
				foreach($arXMLElement[$this->mess["IBLOCK_XML2_DISCOUNTS"]] as $discount)
				{
					if(
						isset($discount[$this->mess["IBLOCK_XML2_DISCOUNT_CONDITION"]])
						&& $discount[$this->mess["IBLOCK_XML2_DISCOUNT_CONDITION"]]===$this->mess["IBLOCK_XML2_DISCOUNT_COND_VOLUME"]
					)
					{
						$discount_value = $this->ToInt($discount[$this->mess["IBLOCK_XML2_DISCOUNT_COND_VALUE"]]);
						$discount_percent = $this->ToFloat($discount[$this->mess["IBLOCK_XML2_DISCOUNT_COND_PERCENT"]]);
						if($discount_value > 0 && $discount_percent > 0)
							$arElement["DISCOUNTS"][$discount_value] = $discount_percent;
					}
				}
			}

			if ($arDBElement)
			{
				$arElement["ID"] = $arDBElement["ID"];
				$counter["UPD"]++;
			}
		}

		if(isset($arXMLElement[$this->mess["IBLOCK_XML2_STORE_AMOUNT_LIST"]]))
		{
			$arElement["STORE_AMOUNT"] = array();
			foreach($arXMLElement[$this->mess["IBLOCK_XML2_STORE_AMOUNT_LIST"]] as $storeAmount)
			{
				if(isset($storeAmount[$this->mess["IBLOCK_XML2_STORE_ID"]]))
				{
					$storeXMLID = $storeAmount[$this->mess["IBLOCK_XML2_STORE_ID"]];
					$amount = $this->ToFloat($storeAmount[$this->mess["IBLOCK_XML2_AMOUNT"]]);
					$arElement["STORE_AMOUNT"][$storeXMLID] = $amount;
				}
			}
		}
		elseif(
			array_key_exists($this->mess["IBLOCK_XML2_STORES"], $arXMLElement)
			|| array_key_exists($this->mess["IBLOCK_XML2_STORE"], $arXMLElement)
		)
		{
			$arElement["STORE_AMOUNT"] = array();
			$rsStores = $this->_xml_file->GetList(
				array("ID" => "asc"),
				array(
					"><LEFT_MARGIN" => array($arParent["LEFT_MARGIN"], $arParent["RIGHT_MARGIN"]),
					"NAME" => $this->mess["IBLOCK_XML2_STORE"],
				),
				array("ID", "ATTRIBUTES")
			);
			while ($arStore = $rsStores->Fetch())
			{
				if($arStore["ATTRIBUTES"] <> '')
				{
					$info = unserialize($arStore["ATTRIBUTES"], ['allowed_classes' => false]);
					if(
						is_array($info)
						&& array_key_exists($this->mess["IBLOCK_XML2_STORE_ID"], $info)
						&& array_key_exists($this->mess["IBLOCK_XML2_STORE_AMOUNT"], $info)
					)
					{
						$arElement["STORE_AMOUNT"][$info[$this->mess["IBLOCK_XML2_STORE_ID"]]] = $this->ToFloat($info[$this->mess["IBLOCK_XML2_STORE_AMOUNT"]]);
					}
				}
			}
		}

		if($bMatch && $this->use_crc)
		{
			//nothing to do
		}
		elseif($arElement["ID"] && $this->bCatalog && $this->isCatalogIblock)
		{
			$arProduct = array(
				"ID" => $arElement["ID"],
			);

			if(isset($arElement["QUANTITY"]))
				$arProduct["QUANTITY"] = (float)$arElement["QUANTITY"];
			elseif(isset($arElement["STORE_AMOUNT"]) && !empty($arElement["STORE_AMOUNT"]))
				$arProduct["QUANTITY"] = $this->countTotalQuantity($arElement["STORE_AMOUNT"]);

			$CML_LINK = '';
			$CML_LINK_ELEMENT = 0;
			if (isset($this->PROPERTY_MAP["CML2_LINK"]))
			{
				$CML_LINK = $this->PROPERTY_MAP["CML2_LINK"];
				if (isset($arElement["PROPERTY_VALUES"][$CML_LINK]))
				{
					$CML_LINK_ELEMENT = $arElement["PROPERTY_VALUES"][$CML_LINK];
				}
			}
			if (is_array($CML_LINK_ELEMENT) && isset($CML_LINK_ELEMENT["n0"]))
			{
				$CML_LINK_ELEMENT = $CML_LINK_ELEMENT["n0"];
			}
			if (is_array($CML_LINK_ELEMENT) && isset($CML_LINK_ELEMENT["VALUE"]))
			{
				$CML_LINK_ELEMENT = $CML_LINK_ELEMENT["VALUE"];
			}

			if(isset($arElement["BASE_WEIGHT"]))
			{
				$arProduct["WEIGHT"] = (float)$arElement["BASE_WEIGHT"];
			}
			elseif ($CML_LINK_ELEMENT > 0)
			{
				$rsWeight = CIBlockElement::GetProperty($this->arProperties[$CML_LINK]["LINK_IBLOCK_ID"], $CML_LINK_ELEMENT, array(), array("CODE" => "CML2_TRAITS"));
				while($arWeight = $rsWeight->Fetch())
				{
					if($arWeight["DESCRIPTION"] == $this->mess["IBLOCK_XML2_WEIGHT"])
						$arProduct["WEIGHT"] = $this->ToFloat($arWeight["VALUE"])*1000;
				}
			}

			if ($CML_LINK_ELEMENT > 0)
			{
				$rsUnit = CIBlockElement::GetProperty($this->arProperties[$CML_LINK]["LINK_IBLOCK_ID"], $CML_LINK_ELEMENT, array(), array("CODE" => "CML2_BASE_UNIT"));
				while($arUnit = $rsUnit->Fetch())
				{
					if($arUnit["DESCRIPTION"] > 0)
						$arProduct["MEASURE"] = $arUnit["DESCRIPTION"];
				}
			}

			if(isset($arElement["PRICES"]))
			{
				//Here start VAT handling

				//Check if all the taxes exists in BSM catalog
				$arTaxMap = array();
				if ($CML_LINK !== '' && isset($this->arProperties[$CML_LINK]) && $CML_LINK_ELEMENT > 0)
				{
					$rsTaxProperty = CIBlockElement::GetProperty(
						$this->arProperties[$CML_LINK]["LINK_IBLOCK_ID"],
						$CML_LINK_ELEMENT,
						"sort",
						"asc",
						[
							"CODE" => "CML2_TAXES",
						]
					);
					while ($arTaxProperty = $rsTaxProperty->Fetch())
					{
						if (
							$arTaxProperty["DESCRIPTION"] <> ''
							&& !array_key_exists($arTaxProperty["DESCRIPTION"], $arTaxMap)
						)
						{
							if ($arTaxProperty["VALUE"] === '' || !is_numeric($arTaxProperty["VALUE"]))
							{
								$taxValue = null;
							}
							else
							{
								$taxValue = $this->ToFloat($arTaxProperty["VALUE"]);
							}
							$arTaxMap[$arTaxProperty["DESCRIPTION"]] = [
								"RATE" => $this->ToFloat($arTaxProperty["VALUE"]),
								"ID" => $this->CheckTax($arTaxProperty["DESCRIPTION"], $taxValue),
							];
						}
					}
					unset($rsTaxProperty);
				}

				//First find out if all the prices have TAX_IN_SUM true
				$TAX_IN_SUM = "Y";

				foreach($arElement["PRICES"] as $price)
				{
					if($price["PRICE"]["TAX_IN_SUM"] !== "true")
					{
						$TAX_IN_SUM = "N";
						break;
					}
				}
				//If there was found not included tax we'll make sure
				//that all prices has the same flag
				if($TAX_IN_SUM === "N")
				{
					foreach($arElement["PRICES"] as $price)
					{
						if($price["PRICE"]["TAX_IN_SUM"] !== "false")
						{
							$TAX_IN_SUM = "Y";
							break;
						}
					}
					//Check if there is a mix of tax in sum
					//and correct it by recalculating all the prices
					if($TAX_IN_SUM === "Y")
					{
						foreach($arElement["PRICES"] as $key=>$price)
						{
							if($price["PRICE"]["TAX_IN_SUM"] !== "true")
							{
								$TAX_NAME = $price["PRICE"]["TAX_NAME"];
								if(array_key_exists($TAX_NAME, $arTaxMap))
								{
									$PRICE_WO_TAX = $this->ToFloat($price[$this->mess["IBLOCK_XML2_PRICE_FOR_ONE"]]);
									$PRICE = $PRICE_WO_TAX + ($PRICE_WO_TAX / 100.0 * $arTaxMap[$TAX_NAME]["RATE"]);
									$arElement["PRICES"][$key][$this->mess["IBLOCK_XML2_PRICE_FOR_ONE"]] = $PRICE;
								}
							}
						}
					}
				}
				foreach($arElement["PRICES"] as $price)
				{
					$TAX_NAME = $price["PRICE"]["TAX_NAME"];
					if(array_key_exists($TAX_NAME, $arTaxMap))
					{
						$arProduct["VAT_ID"] = $arTaxMap[$TAX_NAME]["ID"];
						break;
					}
				}
				$arProduct["VAT_INCLUDED"] = $TAX_IN_SUM;
			}

			$arProduct = array_merge(
				$arProduct,
				$this->getPreparedProductFieldsFromArray($arElement)
			);

			$productCache = Catalog\Model\Product::getCacheItem($arProduct['ID'], true);
			if (!empty($productCache))
			{
				$productResult = Catalog\Model\Product::update(
					$arProduct['ID'],
					array(
						'fields' => $arProduct,
						'external_fields' => array(
							'IBLOCK_ID' => $this->next_step["IBLOCK_ID"]
						)
					)
				);
			}
			else
			{
				$productResult = Catalog\Model\Product::add(
					array(
						'fields' => $arProduct,
						'external_fields' => array(
							'IBLOCK_ID' => $this->next_step["IBLOCK_ID"]
						)
					)
				);
			}
			if ($productResult->isSuccess())
			{
				//TODO: replace this code after upload measure ratio from 1C
				$iterator = \Bitrix\Catalog\MeasureRatioTable::getList(array(
					'select' => array('ID'),
					'filter' => array('=PRODUCT_ID' => $arElement['ID'])
				));
				$ratioRow = $iterator->fetch();
				if (empty($ratioRow))
				{
					$ratioResult = \Bitrix\Catalog\MeasureRatioTable::add(array(
						'PRODUCT_ID' => $arElement['ID'],
						'RATIO' => 1,
						'IS_DEFAULT' => 'Y'
					));
					unset($ratioResult);
				}
				unset($ratioRow, $iterator);
			}

			if(isset($arElement["PRICES"]))
				$this->SetProductPrice($arElement["ID"], $arElement["PRICES"], $arElement["DISCOUNTS"]);

			if(isset($arElement["STORE_AMOUNT"]))
				$this->ImportStoresAmount($arElement["STORE_AMOUNT"], $arElement["ID"], $counter);
		}


		return $arElement["ID"];
	}

	protected function getProductFieldsFromXml(array $xmlElement): array
	{
		$result = [];

		if (array_key_exists($this->mess['IBLOCK_XML2_MARKING_CODE_GROUP'], $xmlElement))
		{
			$result[Product\SystemField\MarkingCodeGroup::FIELD_ID] = $xmlElement[$this->mess['IBLOCK_XML2_MARKING_CODE_GROUP']] ?? '';
		}
		if (array_key_exists($this->mess['IBLOCK_XML2_PRODUCT_MAPPING'], $xmlElement))
		{
			$result[Product\SystemField\ProductMapping::FIELD_ID] = [];
			if (is_array($xmlElement[$this->mess['IBLOCK_XML2_PRODUCT_MAPPING']]))
			{
				foreach ($xmlElement[$this->mess['IBLOCK_XML2_PRODUCT_MAPPING']] as $value)
				{
					$value = (string)$value;
					if ($value !== '')
					{
						$result[Product\SystemField\ProductMapping::FIELD_ID][] = $value;
					}
				}
			}
		}
		if (isset($xmlElement[$this->mess['IBLOCK_XML2_WIDTH']]))
		{
			$result['WIDTH'] = $this->ToFloatEmpty($xmlElement[$this->mess['IBLOCK_XML2_WIDTH']]);
		}
		if (isset($xmlElement[$this->mess['IBLOCK_XML2_LENGTH']]))
		{
			$result['LENGTH'] = $this->ToFloatEmpty($xmlElement[$this->mess['IBLOCK_XML2_LENGTH']]);
		}
		if (isset($xmlElement[$this->mess['IBLOCK_XML2_HEIGHT']]))
		{
			$result['HEIGHT'] = $this->ToFloatEmpty($xmlElement[$this->mess['IBLOCK_XML2_HEIGHT']]);
		}

		return $result;
	}

	protected function getPreparedProductFieldsFromArray(array $element): array
	{
		$result = [];

		$productSystemFields = Product\SystemField::getImportSelectFields();
		if (!empty($productSystemFields))
		{
			foreach ($productSystemFields as $index => $value)
			{
				$fieldName = is_string($index) ? $index : $value;
				if (isset($element[$fieldName]))
				{
					$result[$fieldName] = $element[$fieldName] === '' ? null : $element[$fieldName];
				}
			}
			unset($fieldName, $index, $value);

			Product\SystemField::prepareRow($result, Product\SystemField::OPERATION_IMPORT);
		}

		if (isset($element['MEASURE']))
		{
			$result['MEASURE'] = ($element['MEASURE'] === '' ? null : (int)$element['MEASURE']);
		}

		foreach ($this->productSizes as $fieldName)
		{
			if (isset($element[$fieldName]))
			{
				$result[$fieldName] = $element[$fieldName] === '' ? null : $element[$fieldName];
			}
		}
		unset($fieldName);

		return $result;
	}

	function ImportElementPrices($arXMLElement, &$counter, $arParent = false)
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;
		static $catalogs = array();

		$arElement = array(
			"ID" => 0,
			"XML_ID" => $arXMLElement[$this->mess["IBLOCK_XML2_ID"]],
		);

		$hashPosition = strrpos($arElement["XML_ID"], "#");
		if (
			$this->use_offers
			&& $hashPosition === false && !$this->force_offers
			&& isset($this->PROPERTY_MAP["CML2_LINK"])
			&& isset($this->arProperties[$this->PROPERTY_MAP["CML2_LINK"]])
		)
		{
			$IBLOCK_ID = $this->arProperties[$this->PROPERTY_MAP["CML2_LINK"]]["LINK_IBLOCK_ID"];
			if (!isset($catalogs[$IBLOCK_ID]))
			{
				$catalogs[$IBLOCK_ID] = true;

				$rs = CCatalog::GetList(array(),array("IBLOCK_ID" => $IBLOCK_ID));
				if (!$rs->Fetch())
				{
					$obCatalog = new CCatalog();
					$boolFlag = $obCatalog->Add(array(
						"IBLOCK_ID" => $IBLOCK_ID,
						"YANDEX_EXPORT" => "N",
						"SUBSCRIPTION" => "N",
					));
					if (!$boolFlag)
					{
						if ($ex = $APPLICATION->GetException())
							$this->LAST_ERROR = $ex->GetString();
						return 0;
					}
				}
			}
		}
		else
		{
			$IBLOCK_ID = $this->next_step["IBLOCK_ID"];
		}

		$obElement = new CIBlockElement;
		$rsElement = $obElement->GetList(
			Array("ID"=>"asc"),
			Array("=XML_ID" => $arElement["XML_ID"], "IBLOCK_ID" => $IBLOCK_ID),
			false, false,
			Array("ID", "TMP_ID", "ACTIVE")
		);
		$arDBElement = $rsElement->Fetch();
		if($arDBElement)
			$arElement["ID"] = $arDBElement["ID"];

		if(isset($arXMLElement[$this->mess["IBLOCK_XML2_STORE_AMOUNT_LIST"]]))
		{
			$arElement["STORE_AMOUNT"] = array();
			foreach($arXMLElement[$this->mess["IBLOCK_XML2_STORE_AMOUNT_LIST"]] as $storeAmount)
			{
				if(isset($storeAmount[$this->mess["IBLOCK_XML2_STORE_ID"]]))
				{
					$storeXMLID = $storeAmount[$this->mess["IBLOCK_XML2_STORE_ID"]];
					$amount = $this->ToFloat($storeAmount[$this->mess["IBLOCK_XML2_AMOUNT"]]);
					$arElement["STORE_AMOUNT"][$storeXMLID] = $amount;
				}
			}
		}
		elseif(isset($arXMLElement[$this->mess["IBLOCK_XML2_RESTS"]]))
		{
			$arElement["STORE_AMOUNT"] = array();
			foreach($arXMLElement[$this->mess["IBLOCK_XML2_RESTS"]] as $xmlRest)
			{
				foreach($xmlRest as $storeAmount)
				{
					if(is_array($storeAmount))
					{
						if (isset($storeAmount[$this->mess["IBLOCK_XML2_ID"]]))
						{
							$storeXMLID = $storeAmount[$this->mess["IBLOCK_XML2_ID"]];
							$amount = $this->ToFloat($storeAmount[$this->mess["IBLOCK_XML2_AMOUNT"]]);
							$arElement["STORE_AMOUNT"][$storeXMLID] = $amount;
						}
					}
					else
					{
						if ($storeAmount <> '')
						{
							$amount = $this->ToFloat($storeAmount);
							$arElement["QUANTITY"] = $amount;
						}
					}
				}
			}
		}
		elseif(
			$arParent
			&& (
				array_key_exists($this->mess["IBLOCK_XML2_STORES"], $arXMLElement)
				|| array_key_exists($this->mess["IBLOCK_XML2_STORE"], $arXMLElement)
			)
		)
		{
			$arElement["STORE_AMOUNT"] = array();
			$rsStores = $this->_xml_file->GetList(
				array("ID" => "asc"),
				array(
					"><LEFT_MARGIN" => array($arParent["LEFT_MARGIN"], $arParent["RIGHT_MARGIN"]),
					"NAME" => $this->mess["IBLOCK_XML2_STORE"],
				),
				array("ID", "ATTRIBUTES")
			);
			while ($arStore = $rsStores->Fetch())
			{
				if($arStore["ATTRIBUTES"] <> '')
				{
					$info = unserialize($arStore["ATTRIBUTES"], ['allowed_classes' => false]);
					if(
						is_array($info)
						&& array_key_exists($this->mess["IBLOCK_XML2_STORE_ID"], $info)
						&& array_key_exists($this->mess["IBLOCK_XML2_STORE_AMOUNT"], $info)
					)
					{
						$arElement["STORE_AMOUNT"][$info[$this->mess["IBLOCK_XML2_STORE_ID"]]] = $this->ToFloat($info[$this->mess["IBLOCK_XML2_STORE_AMOUNT"]]);
					}
				}
			}
		}

		if(isset($arElement["STORE_AMOUNT"]))
			$this->ImportStoresAmount($arElement["STORE_AMOUNT"], $arElement["ID"], $counter);

		if($arDBElement)
		{
			$arProduct = array(
				"ID" => $arElement["ID"],
			);

			if(isset($arXMLElement[$this->mess["IBLOCK_XML2_PRICES"]]))
			{
				$arElement["PRICES"] = array();
				foreach($arXMLElement[$this->mess["IBLOCK_XML2_PRICES"]] as $price)
				{
					if(
						isset($price[$this->mess["IBLOCK_XML2_PRICE_TYPE_ID"]])
						&& array_key_exists($price[$this->mess["IBLOCK_XML2_PRICE_TYPE_ID"]], $this->PRICES_MAP)
					)
					{
						$price["PRICE"] = $this->PRICES_MAP[$price[$this->mess["IBLOCK_XML2_PRICE_TYPE_ID"]]];
						$arElement["PRICES"][] = $price;

						if(
							array_key_exists($this->mess["IBLOCK_XML2_MEASURE"], $price)
							&& !isset($arProduct["MEASURE"])
						)
						{
							$tmp = $this->convertBaseUnitFromXmlToPropertyValue($price[$this->mess["IBLOCK_XML2_MEASURE"]]);
							if ($tmp["DESCRIPTION"] > 0)
								$arProduct["MEASURE"] = $tmp["DESCRIPTION"];
						}
					}
				}

				$arElement["DISCOUNTS"] = array();
				if(isset($arXMLElement[$this->mess["IBLOCK_XML2_DISCOUNTS"]]))
				{
					foreach($arXMLElement[$this->mess["IBLOCK_XML2_DISCOUNTS"]] as $discount)
					{
						if(
							isset($discount[$this->mess["IBLOCK_XML2_DISCOUNT_CONDITION"]])
							&& $discount[$this->mess["IBLOCK_XML2_DISCOUNT_CONDITION"]] === $this->mess["IBLOCK_XML2_DISCOUNT_COND_VOLUME"]
						)
						{
							$discount_value = $this->ToInt($discount[$this->mess["IBLOCK_XML2_DISCOUNT_COND_VALUE"]]);
							$discount_percent = $this->ToFloat($discount[$this->mess["IBLOCK_XML2_DISCOUNT_COND_PERCENT"]]);
							if($discount_value > 0 && $discount_percent > 0)
								$arElement["DISCOUNTS"][$discount_value] = $discount_percent;
						}
					}
				}
			}

			if($this->bCatalog && array_key_exists($this->mess["IBLOCK_XML2_AMOUNT"], $arXMLElement))
			{
				$arElement["QUANTITY_RESERVED"] = 0;
				if($arElement["ID"])
				{
					$iterator = Catalog\Model\Product::getList([
						'select' => ['ID', 'QUANTITY_RESERVED'],
						'filter' => ['=ID' => $arDBElement['ID']]
					]);
					$arElementTmp = $iterator->fetch();
					if (!empty($arElementTmp) && is_array($arElementTmp) && isset($arElementTmp["QUANTITY_RESERVED"]))
						$arElement["QUANTITY_RESERVED"] = (float)$arElementTmp["QUANTITY_RESERVED"];
					unset($arElementTmp);
					unset($iterator);
				}
				$arElement["QUANTITY"] = $this->ToFloat($arXMLElement[$this->mess["IBLOCK_XML2_AMOUNT"]]) - $arElement["QUANTITY_RESERVED"];
			}

			if(isset($arElement["PRICES"]) && $this->bCatalog)
			{
				if(isset($arElement["QUANTITY"]))
					$arProduct["QUANTITY"] = (float)$arElement["QUANTITY"];
				elseif(isset($arElement["STORE_AMOUNT"]) && !empty($arElement["STORE_AMOUNT"]))
					$arProduct["QUANTITY"] = $this->countTotalQuantity($arElement["STORE_AMOUNT"]);

				$rsWeight = CIBlockElement::GetProperty($IBLOCK_ID, $arElement["ID"], array(), array("CODE" => "CML2_TRAITS"));
				while($arWeight = $rsWeight->Fetch())
				{
					if($arWeight["DESCRIPTION"] == $this->mess["IBLOCK_XML2_WEIGHT"])
						$arProduct["WEIGHT"] = $this->ToFloat($arWeight["VALUE"])*1000;
				}

				$rsUnit = CIBlockElement::GetProperty($IBLOCK_ID, $arElement["ID"], array(), array("CODE" => "CML2_BASE_UNIT"));
				while($arUnit = $rsUnit->Fetch())
				{
					if($arUnit["DESCRIPTION"] > 0)
						$arProduct["MEASURE"] = $arUnit["DESCRIPTION"];
				}

				//Here start VAT handling

				//Check if all the taxes exists in BSM catalog
				$arTaxMap = array();
				$rsTaxProperty = CIBlockElement::GetProperty($IBLOCK_ID, $arElement["ID"], array("sort" => "asc"), array("CODE" => "CML2_TAXES"));
				while($arTaxProperty = $rsTaxProperty->Fetch())
				{
					if(
						$arTaxProperty["DESCRIPTION"] <> ''
						&& !array_key_exists($arTaxProperty["DESCRIPTION"], $arTaxMap)
					)
					{
						if ($arTaxProperty["VALUE"] === '' || !is_numeric($arTaxProperty["VALUE"]))
						{
							$taxValue = null;
						}
						else
						{
							$taxValue = $this->ToFloat($arTaxProperty["VALUE"]);
						}
						$arTaxMap[$arTaxProperty["DESCRIPTION"]] = array(
							"RATE" => $this->ToFloat($arTaxProperty["VALUE"]),
							"ID" => $this->CheckTax($arTaxProperty["DESCRIPTION"], $taxValue),
						);
					}
				}

				//Try to search in main element
				if (
					!$arTaxMap
					&& $this->use_offers
					&& $hashPosition !== false
					&& isset($this->PROPERTY_MAP["CML2_LINK"])
					&& $this->arProperties[$this->PROPERTY_MAP["CML2_LINK"]]["LINK_IBLOCK_ID"] > 0
				)
				{
					$rsLinkProperty = CIBlockElement::GetProperty($IBLOCK_ID, $arElement["ID"], array("sort" => "asc"), array("CODE" => "CML2_LINK"));
					if( ($arLinkProperty = $rsLinkProperty->Fetch()) && ($arLinkProperty["VALUE"] > 0))
					{
						$rsTaxProperty = CIBlockElement::GetProperty($this->arProperties[$this->PROPERTY_MAP["CML2_LINK"]]["LINK_IBLOCK_ID"], $arLinkProperty["VALUE"], array("sort" => "asc"), array("CODE" => "CML2_TAXES"));
						while($arTaxProperty = $rsTaxProperty->Fetch())
						{
							if(
								$arTaxProperty["DESCRIPTION"] <> ''
								&& !array_key_exists($arTaxProperty["DESCRIPTION"], $arTaxMap)
							)
							{
								if ($arTaxProperty["VALUE"] === '' || !is_numeric($arTaxProperty["VALUE"]))
								{
									$taxValue = null;
								}
								else
								{
									$taxValue = $this->ToFloat($arTaxProperty["VALUE"]);
								}
								$arTaxMap[$arTaxProperty["DESCRIPTION"]] = array(
									"RATE" => $this->ToFloat($arTaxProperty["VALUE"]),
									"ID" => $this->CheckTax($arTaxProperty["DESCRIPTION"], $taxValue),
								);
							}
						}
					}
				}

				//First find out if all the prices have TAX_IN_SUM true
				$TAX_IN_SUM = "Y";
				foreach($arElement["PRICES"] as $price)
				{
					if($price["PRICE"]["TAX_IN_SUM"] !== "true")
					{
						$TAX_IN_SUM = "N";
						break;
					}
				}
				//If there was found not included tax we'll make sure
				//that all prices has the same flag
				if($TAX_IN_SUM === "N")
				{
					foreach($arElement["PRICES"] as $price)
					{
						if($price["PRICE"]["TAX_IN_SUM"] !== "false")
						{
							$TAX_IN_SUM = "Y";
							break;
						}
					}
					//Check if there is a mix of tax in sum
					//and correct it by recalculating all the prices
					if($TAX_IN_SUM === "Y")
					{
						foreach($arElement["PRICES"] as $key=>$price)
						{
							if($price["PRICE"]["TAX_IN_SUM"] !== "true")
							{
								$TAX_NAME = $price["PRICE"]["TAX_NAME"];
								if(array_key_exists($TAX_NAME, $arTaxMap))
								{
									$PRICE_WO_TAX = $this->ToFloat($price[$this->mess["IBLOCK_XML2_PRICE_FOR_ONE"]]);
									$PRICE = $PRICE_WO_TAX + ($PRICE_WO_TAX / 100.0 * $arTaxMap[$TAX_NAME]["RATE"]);
									$arElement["PRICES"][$key][$this->mess["IBLOCK_XML2_PRICE_FOR_ONE"]] = $PRICE;
								}
							}
						}
					}
				}

				if ($TAX_IN_SUM == "Y" && $arTaxMap)
				{
					$vat = current($arTaxMap);
					$arProduct["VAT_ID"] = $vat["ID"];
				}
				else
				{
					foreach($arElement["PRICES"] as $price)
					{
						$TAX_NAME = $price["PRICE"]["TAX_NAME"];
						if(array_key_exists($TAX_NAME, $arTaxMap))
						{
							$arProduct["VAT_ID"] = $arTaxMap[$TAX_NAME]["ID"];
							break;
						}
					}
				}

				$arProduct["VAT_INCLUDED"] = $TAX_IN_SUM;

				$productCache = Catalog\Model\Product::getCacheItem($arProduct['ID'], true);
				if (!empty($productCache))
				{
					$productResult = Catalog\Model\Product::update(
						$arProduct['ID'],
						array(
							'fields' => $arProduct,
							'external_fields' => array(
								'IBLOCK_ID' => $IBLOCK_ID
							)
						)
					);
				}
				else
				{
					$productResult = Catalog\Model\Product::add(
						array(
							'fields' => $arProduct,
							'external_fields' => array(
								'IBLOCK_ID' => $IBLOCK_ID
							)
						)
					);
				}
				if ($productResult->isSuccess())
				{
					//TODO: replace this code after upload measure ratio from 1C
					$iterator = \Bitrix\Catalog\MeasureRatioTable::getList(array(
						'select' => array('ID'),
						'filter' => array('=PRODUCT_ID' => $arElement['ID'])
					));
					$ratioRow = $iterator->fetch();
					if (empty($ratioRow))
					{
						$ratioResult = \Bitrix\Catalog\MeasureRatioTable::add(array(
							'PRODUCT_ID' => $arElement['ID'],
							'RATIO' => 1,
							'IS_DEFAULT' => 'Y'
						));
						unset($ratioResult);
					}
					unset($ratioRow, $iterator);
				}

				$this->SetProductPrice($arElement["ID"], $arElement["PRICES"], $arElement["DISCOUNTS"]);
				\Bitrix\Iblock\PropertyIndex\Manager::updateElementIndex($IBLOCK_ID, $arElement["ID"]);
			}
			elseif(
				$this->bCatalog
				&& (
					(isset($arElement["STORE_AMOUNT"]) && !empty($arElement["STORE_AMOUNT"]))
					|| isset($arElement["QUANTITY"])
				)
			)
			{
				$iterator = Catalog\Model\Product::getList([
					'select' => ['ID', 'QUANTITY_RESERVED'],
					'filter' => ['=ID' => $arElement['ID']]
				]);
				$arElementTmp = $iterator->fetch();
				if (!empty($arElementTmp) && is_array($arElementTmp))
				{
					$quantityReserved = 0;
					if (isset($arElementTmp["QUANTITY_RESERVED"]))
						$quantityReserved = (float)$arElementTmp["QUANTITY_RESERVED"];
					$internalFields = [];
					if (isset($arElement["STORE_AMOUNT"]) && !empty($arElement["STORE_AMOUNT"]))
					{
						$internalFields['QUANTITY'] = $this->countTotalQuantity($arElement["STORE_AMOUNT"]);
					}
					elseif (isset($arElement["QUANTITY"]))
					{
						$internalFields['QUANTITY'] = $arElement["QUANTITY"];
					}
					if (!empty($internalFields))
					{
						$internalFields['QUANTITY'] -= $quantityReserved;
						$internalResult = Catalog\Model\Product::update(
							$arElement['ID'],
							array(
								'fields' => $internalFields,
								'external_fields' => array(
									'IBLOCK_ID' => $IBLOCK_ID
								)
							)
						);
						if (!$internalResult->isSuccess())
						{

						}
						unset($internalResult);
					}
					unset($internalFields);
					unset($quantityReserved);
				}
				unset($arElementTmp);
				unset($iterator);
			}
		}

		$counter["UPD"]++;
		return $arElement["ID"];
	}

	function fillDefaultPropertyValues(&$arElement, $iblockProperties)
	{
		if (isset($arElement["PROPERTY_VALUES"]))
		{
			$elementProperties = &$arElement["PROPERTY_VALUES"];
			foreach ($iblockProperties as $PID => $property)
			{
				if (!array_key_exists($PID, $elementProperties))
				{
					if ($property["PROPERTY_TYPE"] == "L")
					{
						$enumDefaults = CIBlockPropertyEnum::GetList(array(), array(
							"PROPERTY_ID" => $PID,
							"DEF" => "Y",
						));
						$i = 0;
						while($enum = $enumDefaults->Fetch())
						{
							$elementProperties[$PID]["n".$i] = $enum["ID"];
							$i++;
						}
					}
					elseif (is_array($property["DEFAULT_VALUE"]) || $property["DEFAULT_VALUE"] <> '')
					{
						$elementProperties[$PID]["n0"] = array(
							"VALUE" => $property["DEFAULT_VALUE"],
							"DESCRIPTION" => "",
						);
					}
				}
			}
		}
	}

	function ConvertDiscounts($arDiscounts)
	{
		if (is_array($arDiscounts) && count($arDiscounts) > 0)
		{
			if (!array_key_exists(0, $arDiscounts))
				$arDiscounts[0] = 0;

			ksort($arDiscounts);
			$keys = array_keys($arDiscounts);
			$cnt = count($keys);
			for ($i = 0; $i < $cnt; $i++)
			{
				$arDiscounts[$keys[$i]] = array(
					"QUANTITY_FROM" => $keys[$i] + 1,
					"QUANTITY_TO" => $i < $cnt ? $keys[$i + 1] : "",
					"PERCENT" => $arDiscounts[$keys[$i]],
				);
			}
		}
		else
		{
			$arDiscounts = array(
				array(
					"QUANTITY_FROM" => "",
					"QUANTITY_TO" => "",
					"PERCENT" => 0,
				),
			);
		}
		return $arDiscounts;
	}

	/**
	 * @param int $PRODUCT_ID
	 * @param array $arPrices
	 * @param bool|array $arDiscounts
	 */
	function SetProductPrice($PRODUCT_ID, $arPrices, $arDiscounts = false)
	{
		$arDBPrices = array();
		$iterator = Catalog\Model\Price::getList(array(
			'select' => array(
				'ID', 'PRODUCT_ID', 'CATALOG_GROUP_ID',
				'QUANTITY_FROM', 'QUANTITY_TO'
			),
			'filter' => array('=PRODUCT_ID' => $PRODUCT_ID)
		));
		while ($row = $iterator->fetch())
		{
			$arDBPrices[$row["CATALOG_GROUP_ID"].":".$row["QUANTITY_FROM"].":".$row["QUANTITY_TO"]] = $row["ID"];
		}

		$arToDelete = $arDBPrices;

		if(!is_array($arPrices))
			$arPrices = array();

		foreach($arPrices as $price)
		{

			if(!isset($price[$this->mess["IBLOCK_XML2_CURRENCY"]]))
				$price[$this->mess["IBLOCK_XML2_CURRENCY"]] = $price["PRICE"]["CURRENCY"];

			$arPrice = Array(
				"PRODUCT_ID" => $PRODUCT_ID,
				"CATALOG_GROUP_ID" => $price["PRICE"]["ID"],
				"^PRICE" => $this->ToFloat($price[$this->mess["IBLOCK_XML2_PRICE_FOR_ONE"]]),
				"CURRENCY" => $this->CheckCurrency($price[$this->mess["IBLOCK_XML2_CURRENCY"]]),
			);

			if (
				(isset($price[$this->mess["IBLOCK_XML2_QUANTITY_FROM"]]) && (string)$price[$this->mess["IBLOCK_XML2_QUANTITY_FROM"]] != '')
				|| (isset($price[$this->mess["IBLOCK_XML2_QUANTITY_TO"]]) && (string)$price[$this->mess["IBLOCK_XML2_QUANTITY_TO"]] != '')
			)
			{
				$arPrice["QUANTITY_FROM"] = $price[$this->mess["IBLOCK_XML2_QUANTITY_FROM"]];
				$arPrice["QUANTITY_TO"] = $price[$this->mess["IBLOCK_XML2_QUANTITY_TO"]];
				$arPrice["PRICE"] = $arPrice["^PRICE"];
				unset($arPrice["^PRICE"]);
				if ($arPrice["QUANTITY_FROM"] === ''
					|| $arPrice["QUANTITY_FROM"] === false
					|| $arPrice["QUANTITY_FROM"] === '0'
					|| $arPrice["QUANTITY_FROM"] === 0
				)
					$arPrice["QUANTITY_FROM"] = null;
				if ($arPrice["QUANTITY_TO"] === ''
					|| $arPrice["QUANTITY_TO"] === false
					|| $arPrice["QUANTITY_TO"] === '0'
					|| $arPrice["QUANTITY_TO"] === 0
				)
					$arPrice["QUANTITY_TO"] = null;

				$id = $arPrice["CATALOG_GROUP_ID"].":".$arPrice["QUANTITY_FROM"].":".$arPrice["QUANTITY_TO"];
				if(isset($arDBPrices[$id]))
				{
					$priceResult = Catalog\Model\Price::update($arDBPrices[$id], $arPrice);
					unset($arToDelete[$id]);
				}
				else
				{
					$priceResult = Catalog\Model\Price::add($arPrice);
				}
			}
			else
			{
				foreach($this->ConvertDiscounts($arDiscounts) as $arDiscount)
				{
					$arPrice["QUANTITY_FROM"] = $arDiscount["QUANTITY_FROM"];
					$arPrice["QUANTITY_TO"] = $arDiscount["QUANTITY_TO"];
					if($arDiscount["PERCENT"] > 0)
						$arPrice["PRICE"] = $arPrice["^PRICE"] - $arPrice["^PRICE"]/100*$arDiscount["PERCENT"];
					else
						$arPrice["PRICE"] = $arPrice["^PRICE"];
					unset($arPrice["^PRICE"]);
					if ($arPrice["QUANTITY_FROM"] === ''
						|| $arPrice["QUANTITY_FROM"] === false
						|| $arPrice["QUANTITY_FROM"] === '0'
						|| $arPrice["QUANTITY_FROM"] === 0
					)
						$arPrice["QUANTITY_FROM"] = null;
					if ($arPrice["QUANTITY_TO"] === ''
						|| $arPrice["QUANTITY_TO"] === false
						|| $arPrice["QUANTITY_TO"] === '0'
						|| $arPrice["QUANTITY_TO"] === 0
					)
						$arPrice["QUANTITY_TO"] = null;

					$id = $arPrice["CATALOG_GROUP_ID"].":".$arPrice["QUANTITY_FROM"].":".$arPrice["QUANTITY_TO"];
					if(isset($arDBPrices[$id]))
					{
						$priceResult = Catalog\Model\Price::update($arDBPrices[$id], $arPrice);
						unset($arToDelete[$id]);
					}
					else
					{
						$priceResult = Catalog\Model\Price::add($arPrice);
					}
				}
			}
		}

		foreach($arToDelete as $id)
		{
			$priceResult = Catalog\Model\Price::delete($id);
		}
	}

	function ImportSection($xml_tree_id, $IBLOCK_ID, $parent_section_id)
	{
		/** @var CUserTypeManager $USER_FIELD_MANAGER */
		global $USER_FIELD_MANAGER;
		/** @var CDatabase $DB */
		global $DB;

		static $arUserFields;
		if($parent_section_id === false)
		{
			$arUserFields = array();
			foreach($USER_FIELD_MANAGER->GetUserFields("IBLOCK_".$IBLOCK_ID."_SECTION") as $FIELD_ID => $arField)
			{
				if($arField["XML_ID"] == '')
					$arUserFields[$FIELD_ID] = $arField;
				else
					$arUserFields[$arField["XML_ID"]] = $arField;
			}
		}

		$this->next_step["section_sort"] += 10;
		$arSection = array(
			"IBLOCK_SECTION_ID" => $parent_section_id,
			"ACTIVE" => "Y",
		);
		$rsS = $this->_xml_file->GetList(
			array("ID" => "asc"),
			array("PARENT_ID" => $xml_tree_id)
		);
		$XML_SECTIONS_PARENT = false;
		$XML_PROPERTIES_PARENT = false;
		$XML_SECTION_PROPERTIES = false;
		$deletedStatus = false;
		while($arS = $rsS->Fetch())
		{
			if(isset($arS["VALUE_CLOB"]))
				$arS["VALUE"] = $arS["VALUE_CLOB"];

			if($arS["NAME"]==$this->mess["IBLOCK_XML2_ID"])
				$arSection["XML_ID"] = $arS["VALUE"];
			elseif($arS["NAME"]==$this->mess["IBLOCK_XML2_NAME"])
				$arSection["NAME"] = $arS["VALUE"];
			elseif($arS["NAME"]==$this->mess["IBLOCK_XML2_DESCRIPTION"])
			{
				$arSection["DESCRIPTION"] = $arS["VALUE"];
				$arSection["DESCRIPTION_TYPE"] = "html";
			}
			elseif($arS["NAME"]==$this->mess["IBLOCK_XML2_GROUPS"])
				$XML_SECTIONS_PARENT = $arS["ID"];
			elseif($arS["NAME"]==$this->mess["IBLOCK_XML2_PROPERTIES_VALUES"])
				$XML_PROPERTIES_PARENT = $arS["ID"];
			elseif($arS["NAME"]==$this->mess["IBLOCK_XML2_BX_SORT"])
				$arSection["SORT"] = intval($arS["VALUE"]);
			elseif($arS["NAME"]==$this->mess["IBLOCK_XML2_BX_CODE"])
				$arSection["CODE"] = $arS["VALUE"];
			elseif($arS["NAME"] == $this->mess["IBLOCK_XML2_BX_PICTURE"])
			{
				if($arS["VALUE"] <> '')
					$arSection["PICTURE"] = $this->MakeFileArray($arS["VALUE"]);
				else
					$arSection["PICTURE"] = $this->MakeFileArray($this->_xml_file->GetAllChildrenArray($arS["ID"]));
			}
			elseif($arS["NAME"] == $this->mess["IBLOCK_XML2_BX_DETAIL_PICTURE"])
			{
				if($arS["VALUE"] <> '')
					$arSection["DETAIL_PICTURE"] = $this->MakeFileArray($arS["VALUE"]);
				else
					$arSection["DETAIL_PICTURE"] = $this->MakeFileArray($this->_xml_file->GetAllChildrenArray($arS["ID"]));
			}
			elseif($arS["NAME"] == $this->mess["IBLOCK_XML2_BX_ACTIVE"])
				$arSection["ACTIVE"] = ($arS["VALUE"]=="true") || intval($arS["VALUE"])? "Y": "N";
			elseif($arS["NAME"] == $this->mess["IBLOCK_XML2_SECTION_PROPERTIES"])
				$XML_SECTION_PROPERTIES = $arS["ID"];
			elseif($arS["NAME"] == $this->mess["IBLOCK_XML2_STATUS"])
				$deletedStatus = $arS["VALUE"] === $this->mess["IBLOCK_XML2_DELETED"];
			elseif($arS["NAME"] == $this->mess["IBLOCK_XML2_INHERITED_TEMPLATES"])
			{
				$arSection["IPROPERTY_TEMPLATES"] = array();
				$arTemplates = $this->_xml_file->GetAllChildrenArray($arS["ID"]);
				foreach($arTemplates as $TEMPLATE)
				{
					$id = $TEMPLATE[$this->mess["IBLOCK_XML2_ID"]];
					$template = $TEMPLATE[$this->mess["IBLOCK_XML2_VALUE"]];
					if($id <> '' && $template <> '')
						$arSection["IPROPERTY_TEMPLATES"][$id] = $template;
				}
			}
			elseif($arS["NAME"] == $this->mess["IBLOCK_XML2_DELETE_MARK"])
			{
				$arSection["ACTIVE"] = ($arS["VALUE"]=="true") || intval($arS["VALUE"])? "N": "Y";
			}
		}

		if ($deletedStatus)
		{
			$obSection = new CIBlockSection;
			$rsSection = $obSection->GetList(array(), array(
				"IBLOCK_ID" => $IBLOCK_ID,
				"XML_ID" => $arSection["XML_ID"],
			), false, array("ID"));
			if($arDBSection = $rsSection->Fetch())
			{
				$obSection->Update($arDBSection["ID"], array(
					"ACTIVE" => "N",
				));
				$this->_xml_file->Add(array("PARENT_ID" => 0, "LEFT_MARGIN" => $arDBSection["ID"]));
			}
			return true;
		}

		if($XML_PROPERTIES_PARENT)
		{
			$rs = $this->_xml_file->GetList(
				array("ID" => "asc"),
				array("PARENT_ID" => $XML_PROPERTIES_PARENT),
				array("ID")
			);
			while($ar = $rs->Fetch())
			{
				$arXMLProp = $this->_xml_file->GetAllChildrenArray($ar["ID"]);
				if(
					array_key_exists($this->mess["IBLOCK_XML2_ID"], $arXMLProp)
					&& array_key_exists($arXMLProp[$this->mess["IBLOCK_XML2_ID"]], $arUserFields)
				)
				{
					$arUserField = $arUserFields[$arXMLProp[$this->mess["IBLOCK_XML2_ID"]]];
					unset($arXMLProp[$this->mess["IBLOCK_XML2_ID"]]);

					$arProp = array();
					$i = 0;
					foreach($arXMLProp as $value)
					{
						if($arUserField["USER_TYPE"]["BASE_TYPE"] === "file")
							$arProp["n".($i++)] = $this->MakeFileArray($value);
						elseif($arUserField["USER_TYPE"]["BASE_TYPE"] === "enum")
							$arProp["n".($i++)] = $this->GetSectionEnumByXML_ID($arUserField["ID"], $value);
						else
							$arProp["n".($i++)] = $value;
					}

					if($arUserField["MULTIPLE"] == "N")
						$arSection[$arUserField["FIELD_NAME"]] = array_pop($arProp);
					else
						$arSection[$arUserField["FIELD_NAME"]] = $arProp;
				}
			}
		}

		$obSection = new CIBlockSection;
		$rsSection = $obSection->GetList(array(), array("IBLOCK_ID"=>$IBLOCK_ID, "XML_ID"=>$arSection["XML_ID"]), false);
		if($arDBSection = $rsSection->Fetch())
		{
			if(
				!array_key_exists("CODE", $arSection)
				&& is_array($this->translit_on_update)
			)
			{
				$CODE = CUtil::translit($arSection["NAME"], LANGUAGE_ID, $this->translit_on_update);
				$CODE = $this->CheckSectionCode($IBLOCK_ID, $arDBSection["ID"], $CODE);
				if ($CODE !== false)
					$arSection["CODE"] = $CODE;
			}

			$bChanged = false;
			foreach($arSection as $key=>$value)
			{
				if(is_array($arDBSection[$key]) || ($arDBSection[$key] != $value))
				{
					$bChanged = true;
					break;
				}
			}

			if($bChanged)
			{
				foreach($arUserFields as $arField1)
				{
					if($arField1["USER_TYPE"]["BASE_TYPE"] == "file")
					{
						$sectionUF = $USER_FIELD_MANAGER->GetUserFields("IBLOCK_".$IBLOCK_ID."_SECTION", $arDBSection["ID"]);
						foreach($sectionUF as $arField2)
						{
							if(
								$arField2["USER_TYPE"]["BASE_TYPE"] == "file"
								&& isset($arSection[$arField2["FIELD_NAME"]])
							)
							{
								if($arField2["MULTIPLE"] == "Y" && is_array($arField2["VALUE"]))
								{
									foreach($arField2["VALUE"] as $old_file_id)
										$arSection[$arField2["FIELD_NAME"]][] = array("del"=>true,"old_id"=>$old_file_id);
								}
								elseif($arField2["MULTIPLE"] == "N" && $arField2["VALUE"] > 0)
								{
									$arSection[$arField2["FIELD_NAME"]]["old_id"] = $arField2["VALUE"];
								}
							}
						}
						break;
					}
				}

				if (!isset($arSection['MODIFIED_BY']) || (int)$arSection['MODIFIED_BY'] <= 0)
				{
					if ($this->currentUserId > 0)
					{
						$arSection['MODIFIED_BY'] = $this->currentUserId;
					}
				}

				$res = $obSection->Update($arDBSection["ID"], $arSection);
				if(!$res)
				{
					$this->LAST_ERROR = $obSection->LAST_ERROR;
					return $this->LAST_ERROR;
				}
			}
			else
			{
				$strUpdate = "TIMESTAMP_X = ".$DB->CurrentTimeFunction();
				if ($this->currentUserId > 0)
					$strUpdate .= ", MODIFIED_BY=".$this->currentUserId;
				$DB->Query("UPDATE b_iblock_section SET ".$strUpdate." WHERE ID=".$arDBSection["ID"]);
				unset($strUpdate);
			}

			$arSection["ID"] = $arDBSection["ID"];
		}
		else
		{
			if(!array_key_exists("CODE", $arSection) && is_array($this->translit_on_add))
			{
				$CODE = CUtil::translit($arSection["NAME"], LANGUAGE_ID, $this->translit_on_add);
				$CODE = $this->CheckSectionCode($IBLOCK_ID, 0, $CODE);
				if ($CODE !== false)
					$arSection["CODE"] = $CODE;
			}

			$arSection["IBLOCK_ID"] = $IBLOCK_ID;
			if(!isset($arSection["SORT"]))
				$arSection["SORT"] = $this->next_step["section_sort"];

			$arSection["ID"] = $obSection->Add($arSection);
			if(!$arSection["ID"])
			{
				$this->LAST_ERROR = $obSection->LAST_ERROR;
				return $this->LAST_ERROR;
			}
		}

		if($XML_SECTION_PROPERTIES)
		{
			$this->ImportSectionProperties($XML_SECTION_PROPERTIES, $IBLOCK_ID, $arSection["ID"]);
		}

		//Clear seo cache
		if(isset($bChanged) && $bChanged)
		{
			$ipropValues = new \Bitrix\Iblock\InheritedProperty\SectionValues($this->next_step["IBLOCK_ID"], $arSection["ID"]);
			$ipropValues->clearValues();
		}

		if($arSection["ID"])
		{
			$this->_xml_file->Add(array("PARENT_ID" => 0, "LEFT_MARGIN" => $arSection["ID"]));
		}

		if($XML_SECTIONS_PARENT)
		{
			$rs = $this->_xml_file->GetList(
				array("ID" => "asc"),
				array("PARENT_ID" => $XML_SECTIONS_PARENT),
				array("ID")
			);
			while($ar = $rs->Fetch())
			{
				$result = $this->ImportSection($ar["ID"], $IBLOCK_ID, $arSection["ID"]);
				if($result !== true)
					return $result;
			}
		}

		return true;
	}

	function CheckElementCode($IBLOCK_ID, $ID, $CODE)
	{
		$arCodes = array();
		$rsCodeLike = CIBlockElement::GetList(array(), array(
			"IBLOCK_ID" => $IBLOCK_ID,
			"CODE" => $CODE."%",
		), false, false, array("ID", "CODE"));
		while($ar = $rsCodeLike->Fetch())
		{
			if ($ID == $ar["ID"])
				return false;
			$arCodes[$ar["CODE"]] = true;
		}

		if (array_key_exists($CODE, $arCodes))
		{
			$i = 1;
			while(array_key_exists($CODE."_".$i, $arCodes))
				$i++;

			return $CODE."_".$i;
		}
		else
		{
			return $CODE;
		}
	}

	function CheckSectionCode($IBLOCK_ID, $ID, $CODE)
	{
		$arCodes = array();
		$rsCodeLike = CIBlockSection::GetList(array(), array(
			"IBLOCK_ID" => $IBLOCK_ID,
			"CODE" => $CODE."%",
		), false, array("ID", "CODE"));
		while($ar = $rsCodeLike->Fetch())
		{
			if ($ID == $ar["ID"])
				return false;
			$arCodes[$ar["CODE"]] = true;
		}

		if (array_key_exists($CODE, $arCodes))
		{
			$i = 1;
			while(array_key_exists($CODE."_".$i, $arCodes))
				$i++;

			return $CODE."_".$i;
		}
		else
		{
			return $CODE;
		}
	}

	function DeactivateElement($action, $start_time, $interval)
	{
		$counter = array(
			"DEL" => 0,
			"DEA" => 0,
			"NON" => 0,
		);

		if(array_key_exists("bUpdateOnly", $this->next_step) && $this->next_step["bUpdateOnly"])
			return $counter;

		if($action!="D" && $action!="A")
			return $counter;

		$bDelete = $action=="D";

		//This will protect us from deactivating when next_step is lost
		$IBLOCK_ID = intval($this->next_step["IBLOCK_ID"]);
		if($IBLOCK_ID < 1)
			return $counter;

		$arFilter = array(
			">ID" => $this->next_step["LAST_ID"] ?? 0,
			"IBLOCK_ID" => $IBLOCK_ID,
		);
		if(!$bDelete)
			$arFilter["ACTIVE"] = "Y";

		$obElement = new CIBlockElement;
		$rsElement = $obElement->GetList(
			Array("ID"=>"asc"),
			$arFilter,
			false, false,
			Array("ID", "ACTIVE")
		);

		while($arElement = $rsElement->Fetch())
		{
			$rs = $this->_xml_file->GetList(
				array(),
				array("PARENT_ID+0" => 0, "LEFT_MARGIN" => $arElement["ID"]),
				array("ID")
			);
			$ar = $rs->Fetch();
			if(!$ar)
			{
				if($bDelete)
				{
					$obElement->Delete($arElement["ID"]);
					$counter["DEL"]++;
				}
				else
				{
					$elementFields = array("ACTIVE"=>"N");
					if ((string)\Bitrix\Main\Config\Option::get('iblock', 'change_user_by_group_active_modify') === 'Y')
					{
						$elementFields['MODIFIED_BY'] = $this->currentUserId;
					}
					$obElement->Update($arElement["ID"], $elementFields);
					$counter["DEA"]++;
				}
			}
			else
			{
				$counter["NON"]++;
			}

			$this->next_step["LAST_ID"] = $arElement["ID"];

			if($interval > 0 && (time()-$start_time) > $interval)
				break;

		}
		return $counter;
	}

	/**
	 * @param array $counters
	 * @return int
	 */
	public function updateCounters(array $counters): int
	{
		$result = 0;

		if (!isset($this->next_step["DONE"]))
			$this->next_step["DONE"] = [];
		foreach ($counters as $index => $value)
		{
			if (!isset($this->next_step["DONE"][$index]))
				$this->next_step["DONE"][$index] = 0;
			$value = (int)$value;
			$this->next_step["DONE"][$index] += $value;
			$result += $value;
		}
		unset($index, $value);

		return $result;
	}

	/**
	 * @param bool $description
	 * @return array
	 */
	public static function getIblockCacheModeList(bool $description = false): array
	{
		if ($description)
		{
			return array(
				self::IBLOCK_CACHE_NORMAL => GetMessage('IBLOCK_XML2_IBLOCK_CACHE_MODE_NORMAL'),
				self::IBLOCK_CACHE_HIT => GetMessage('IBLOCK_XML2_IBLOCK_CACHE_MODE_HIT'),
				self::IBLOCK_CACHE_FINAL => GetMessage('IBLOCK_XML2_IBLOCK_CACHE_MODE_FINAL'),
				self::IBLOCK_CACHE_FREEZE => GetMessage('IBLOCK_XML2_IBLOCK_CACHE_MODE_FREEZE')
			);
		}
		return array(
			self::IBLOCK_CACHE_NORMAL,
			self::IBLOCK_CACHE_HIT,
			self::IBLOCK_CACHE_FINAL,
			self::IBLOCK_CACHE_FREEZE
		);
	}

	/**
	 * @return void
	 */
	public function freezeIblockCache(): void
	{
		if (
			isset($this->next_step['IBLOCK_ID'])
			&& $this->next_step['IBLOCK_ID'] > 0
			&& !$this->isIblockCacheModeNormal()
		)
		{
			CIBlock::disableClearTagCache();
		}
	}

	/**
	 * @return void
	 */
	public function unFreezeIblockCache(): void
	{
		if (
			isset($this->next_step['IBLOCK_ID'])
			&& $this->next_step['IBLOCK_ID'] > 0
			&& !$this->isIblockCacheModeNormal()
		)
		{
			CIBlock::enableClearTagCache();
		}
	}

	/**
	 * @return void
	 */
	public function clearIblockCacheOnHit(): void
	{
		if (
			!isset($this->next_step['IBLOCK_ID'])
			|| $this->next_step['IBLOCK_ID'] <= 0
			|| !$this->isIblockCacheModeHit()
		)
		{
			return;
		}

		CIBlock::clearIblockTagCache($this->next_step['IBLOCK_ID']);
	}

	/**
	 * @return void
	 */
	public function clearIblockCacheAfterFinal(): void
	{
		if (
			!isset($this->next_step['IBLOCK_ID'])
			|| $this->next_step['IBLOCK_ID'] <= 0
			|| !$this->isIblockCacheModeFinal()
		)
		{
			return;
		}

		CIBlock::clearIblockTagCache($this->next_step['IBLOCK_ID']);
	}

	/**
	 * @return string
	 */
	public function getIblockCacheMode(): string
	{
		return $this->iblockCacheMode;
	}

	/**
	 * @return bool
	 */
	public function isIblockCacheModeNormal(): bool
	{
		return $this->getIblockCacheMode() === self::IBLOCK_CACHE_NORMAL;
	}

	/**
	 * @return bool
	 */
	public function isIblockCacheModeHit(): bool
	{
		return $this->getIblockCacheMode() === self::IBLOCK_CACHE_HIT;
	}

	/**
	 * @return bool
	 */
	public function isIblockCacheModeFinal(): bool
	{
		return $this->getIblockCacheMode() === self::IBLOCK_CACHE_FINAL;
	}

	/**
	 * @return bool
	 */
	public function isIblockCacheModeFreeze(): bool
	{
		return $this->getIblockCacheMode() === self::IBLOCK_CACHE_FREEZE;
	}

	/**
	 * Returns a external codes list of warehouses.
	 *
	 * @return array
	 */
	protected function getStoreList(): array
	{
		$result = array();
		if ($this->bCatalog)
		{
			$iterator = Catalog\StoreTable::getList(array(
				'select' => array('ID', 'XML_ID')
			));
			while ($row = $iterator->fetch())
			{
				$result[$row['XML_ID']] = $row['ID'];
			}
			unset($row);
			unset($iterator);
		}
		return $result;
	}

	/**
	 * Returns a external codes list of active warehouses.
	 *
	 * @return array
	 */
	protected function getActiveStores(): array
	{
		$result = array();
		if ($this->bCatalog)
		{
			$iterator = Catalog\StoreTable::getList(array(
				'select' => array('ID', 'XML_ID'),
				'filter' => array('=ACTIVE' => 'Y')
			));
			while ($row = $iterator->fetch())
			{
				$result[$row['XML_ID']] = $row['ID'];
			}
			unset($row);
			unset($iterator);
		}
		return $result;
	}

	/**
	 * Count total product quantity.
	 *
	 * @param array $stores
	 * @return int|float
	 */
	protected function countTotalQuantity(array $stores)
	{
		$totalQuantity = 0;
		foreach ($stores as $xmlId => $quantity)
		{
			if (isset($this->activeStores[$xmlId]))
			{
				$totalQuantity += $quantity;
			}
		}
		return $totalQuantity;
	}
}

class CIBlockCMLExport
{
	var $fp = null;
	var $IBLOCK_ID = false;
	var $bExtended = false;
	var $work_dir = false;
	var $file_dir = false;
	/** @var bool|array */
	var $next_step = false;
	/** @var array */
	var $arIBlock = false;
	var $prices = array();
	var $only_price = false;
	var $download_files = true;
	var $export_as_url = false;
	var $PRODUCT_IBLOCK_ID = false;

	function Init($fp, $IBLOCK_ID, $next_step, $bExtended=false, $work_dir=false, $file_dir=false, $bCheckPermissions = true, $PRODUCT_IBLOCK_ID = false)
	{
		$this->fp = $fp;
		$this->IBLOCK_ID = intval($IBLOCK_ID);
		$this->bExtended = $bExtended;
		$this->work_dir = $work_dir;
		$this->file_dir = $file_dir;
		$this->next_step = $next_step;
		$this->only_price = false;
		$this->download_files = true;
		$this->PRODUCT_IBLOCK_ID = intval($PRODUCT_IBLOCK_ID);

		$arFilter = array(
			"ID" => $this->IBLOCK_ID,
			"MIN_PERMISSION" => "W",
		);
		if(!$bCheckPermissions)
			$arFilter["CHECK_PERMISSIONS"] = "N";

		$rsIBlock = CIBlock::GetList(array(), $arFilter);
		if(($this->arIBlock = $rsIBlock->Fetch()) && ($this->arIBlock["ID"]==$this->IBLOCK_ID))
		{
			$this->next_step["catalog"] = Loader::includeModule('catalog');
			if($this->next_step["catalog"])
			{
				$rs = CCatalog::GetList(array(),array("IBLOCK_ID"=>$this->arIBlock["ID"]));
				if($rs->Fetch())
				{
					$this->next_step["catalog"] = true;
					$this->prices = array();
					foreach (Catalog\GroupTable::getTypeList() as $arPrice)
					{
						$this->prices[$arPrice["ID"]] = $arPrice["NAME"];
					}
				}
				else
				{
					$this->next_step["catalog"] = false;
				}
			}
			return true;
		}
		else
			return false;
	}

	function DoNotDownloadCloudFiles()
	{
		$this->download_files = false;
	}

	function NotCatalog()
	{
		$this->next_step["catalog"] = false;
	}

	function ExportFileAsURL()
	{
		$this->export_as_url = true;
	}

	function GetIBlockXML_ID($IBLOCK_ID, $XML_ID=false)
	{
		if($XML_ID === false)
		{
			$IBLOCK_ID = intval($IBLOCK_ID);
			if($IBLOCK_ID>0)
			{
				$obIBlock = new CIBlock;
				$rsIBlock = $obIBlock->GetList(array(), array("ID"=>$IBLOCK_ID));
				if($arIBlock = $rsIBlock->Fetch())
					$XML_ID = $arIBlock["XML_ID"];
				else
					return "";
			}
			else
				return "";
		}
		if($XML_ID == '')
		{
			$XML_ID = $IBLOCK_ID;
			$obIBlock = new CIBlock;
			$rsIBlock = $obIBlock->GetList(array(), array("XML_ID"=>$XML_ID));
			while($rsIBlock->Fetch())
			{
				$XML_ID = md5(uniqid(mt_rand(), true));
				$rsIBlock = $obIBlock->GetList(array(), array("XML_ID"=>$XML_ID));
			}
			$obIBlock->Update($IBLOCK_ID, array("XML_ID" => $XML_ID));
		}
		return $XML_ID;
	}

	function GetSectionXML_ID($IBLOCK_ID, $SECTION_ID, $XML_ID = false)
	{
		if($XML_ID === false)
		{
			$rsSection = CIBlockSection::GetList(array(), array("IBLOCK_ID"=>$IBLOCK_ID, "ID"=>$SECTION_ID), false, array('ID', 'XML_ID'));
			if($arSection = $rsSection->Fetch())
			{
				$XML_ID = $arSection["XML_ID"];
			}
			unset($rsSection);
		}
		if($XML_ID == '')
		{
			$XML_ID = $SECTION_ID;
			$obSection = new CIBlockSection;
			$rsSection = $obSection->GetList(array(), array("IBLOCK_ID"=>$IBLOCK_ID, "EXTERNAL_ID"=>$XML_ID), false, array('ID'));
			while($rsSection->Fetch())
			{
				$XML_ID = md5(uniqid(mt_rand(), true));
				$rsSection = $obSection->GetList(array(), array("IBLOCK_ID"=>$IBLOCK_ID, "EXTERNAL_ID"=>$XML_ID), false, array('ID'));
			}
			$obSection->Update($SECTION_ID, array("XML_ID" => $XML_ID), false, false);
		}
		return $XML_ID;
	}

	function GetElementXML_ID($IBLOCK_ID, $ELEMENT_ID, $XML_ID = false)
	{
		if($XML_ID === false)
		{
			$arFilter = array(
				"ID" => $ELEMENT_ID,
				"SHOW_HISTORY"=>"Y",
			);
			if($IBLOCK_ID > 0)
				$arFilter["IBLOCK_ID"] = $IBLOCK_ID;
			$rsElement = CIBlockElement::GetList(
					Array("ID"=>"asc"),
					$arFilter,
					false, false,
					Array("ID", "XML_ID")
			);
			if($arElement = $rsElement->Fetch())
			{
				$XML_ID = $arElement["XML_ID"];
			}
			unset($rsElement);
			unset($arElement);
		}
		return $XML_ID;
	}

	function GetPropertyXML_ID($IBLOCK_ID, $NAME, $PROPERTY_ID, $XML_ID)
	{
		if($XML_ID == '')
		{
			$XML_ID = $PROPERTY_ID;
			$obProperty = new CIBlockProperty;
			$rsProperty = $obProperty->GetList(array(), array("IBLOCK_ID"=>$IBLOCK_ID, "XML_ID"=>$XML_ID));
			while($rsProperty->Fetch())
			{
				$XML_ID = md5(uniqid(mt_rand(), true));
				$rsProperty = $obProperty->GetList(array(), array("IBLOCK_ID"=>$IBLOCK_ID, "XML_ID"=>$XML_ID));
			}
			$obProperty->Update($PROPERTY_ID, array("NAME"=>$NAME, "XML_ID" => $XML_ID));
		}
		return $XML_ID;
	}

	function StartExport()
	{
		fwrite($this->fp, "<"."?xml version=\"1.0\" encoding=\"".LANG_CHARSET."\"?".">\n");
		fwrite($this->fp, "<".GetMessage("IBLOCK_XML2_COMMERCE_INFO")." ".GetMessage("IBLOCK_XML2_SCHEMA_VERSION")."=\"2.021\" ".GetMessage("IBLOCK_XML2_TIMESTAMP")."=\"".date("Y-m-d")."T".date("H:i:s")."\">\n");
	}

	function ExportFile($FILE_ID)
	{
		if($this->work_dir)
		{
			$arFile = CFile::GetFileArray($FILE_ID);
			if($arFile)
			{
				if((!$this->download_files) && ($arFile["HANDLER_ID"] > 0))
				{
					return array(
						GetMessage("IBLOCK_XML2_BX_ORIGINAL_NAME") => $arFile["ORIGINAL_NAME"],
						GetMessage("IBLOCK_XML2_DESCRIPTION") => $arFile["DESCRIPTION"],
						GetMessage("IBLOCK_XML2_BX_URL") => urldecode($arFile["SRC"]),
						GetMessage("IBLOCK_XML2_BX_FILE_SIZE") => $arFile["FILE_SIZE"],
						GetMessage("IBLOCK_XML2_BX_FILE_WIDTH") => $arFile["WIDTH"],
						GetMessage("IBLOCK_XML2_BX_FILE_HEIGHT") => $arFile["HEIGHT"],
						GetMessage("IBLOCK_XML2_BX_FILE_CONTENT_TYPE") => $arFile["CONTENT_TYPE"],
					);
				}
				else
				{
					$arTempFile = CFile::MakeFileArray($FILE_ID);
					if(isset($arTempFile["tmp_name"]) && $arTempFile["tmp_name"] <> "")
					{
						$strFile = $arFile["SUBDIR"]."/".$arFile["FILE_NAME"];
						$strNewFile = str_replace("//", "/", $this->work_dir.$this->file_dir.$strFile);
						CheckDirPath($strNewFile);

						if(@copy($arTempFile["tmp_name"], $strNewFile))
							return $this->file_dir.$strFile;
					}
				}
			}
		}
		elseif($this->export_as_url)
		{
			$arFile = CFile::GetFileArray($FILE_ID);
			if($arFile)
				return CHTTP::URN2URI($arFile["SRC"]);
		}

		return "";
	}

	function ExportEnum($arUserField, $value)
	{
		static $cache = array();
		if (!isset($cache[$value]))
		{
			$obEnum = new CUserFieldEnum;
			$rsEnum = $obEnum->GetList(array(), array(
				"USER_FIELD_ID" => $arUserField["ID"],
				"ID" => $value,
			));
			$cache[$value] = $rsEnum->Fetch();
		}
		return $cache[$value]["XML_ID"];
	}

	function formatXMLNode($level, $tagName, $value)
	{
		if(is_array($value))
		{
			$xmlValue = "";
			foreach($value as $k => $v)
			{
				if($k)
					$xmlValue .= "\n".rtrim($this->formatXMLNode($level+1, $k, $v), "\n");
			}
			$xmlValue .= "\n".str_repeat("\t", $level);
		}
		else
		{
			$xmlValue = htmlspecialcharsbx($value);
		}

		return str_repeat("\t", $level)."<".$tagName.">".$xmlValue."</".$tagName.">\n";
	}

	function StartExportMetadata()
	{
		$xml_id = $this->GetIBlockXML_ID($this->arIBlock["ID"], $this->arIBlock["XML_ID"]);
		$this->arIBlock["XML_ID"] = $xml_id;
		fwrite($this->fp, "\t<".GetMessage("IBLOCK_XML2_METADATA").">\n");
		fwrite($this->fp, $this->formatXMLNode(2, GetMessage("IBLOCK_XML2_ID"), $xml_id));
		fwrite($this->fp, $this->formatXMLNode(2, GetMessage("IBLOCK_XML2_NAME"), $this->arIBlock["NAME"]));
		if($this->arIBlock["DESCRIPTION"] <> '')
			fwrite($this->fp, $this->formatXMLNode(2, GetMessage("IBLOCK_XML2_DESCRIPTION"), FormatText($this->arIBlock["DESCRIPTION"], $this->arIBlock["DESCRIPTION_TYPE"])));
	}

	function ExportSectionsProperties($arUserFields)
	{
		if(empty($arUserFields))
			return;

		fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_GROUPS_PROPERTIES").">\n");
		foreach($arUserFields as $FIELD_ID => $arField)
		{
			fwrite($this->fp, "\t\t\t<".GetMessage("IBLOCK_XML2_PROPERTY").">\n");
			fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_ID").">".htmlspecialcharsbx($arField["XML_ID"])."</".GetMessage("IBLOCK_XML2_ID").">\n");
			fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_NAME").">".htmlspecialcharsbx($FIELD_ID)."</".GetMessage("IBLOCK_XML2_NAME").">\n");
			fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_SORT").">".htmlspecialcharsbx($arField["SORT"])."</".GetMessage("IBLOCK_XML2_SORT").">\n");
			fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_MULTIPLE").">".($arField["MULTIPLE"] == "Y"? "true": "false")."</".GetMessage("IBLOCK_XML2_MULTIPLE").">\n");
			fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_BX_PROPERTY_TYPE").">".htmlspecialcharsbx($arField["USER_TYPE_ID"])."</".GetMessage("IBLOCK_XML2_BX_PROPERTY_TYPE").">\n");
			fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_BX_IS_REQUIRED").">".($arField["MANDATORY"] == "Y"? "true": "false")."</".GetMessage("IBLOCK_XML2_BX_IS_REQUIRED").">\n");
			fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_BX_FILTER").">".($arField["SHOW_FILTER"] == "Y"? "true": "false")."</".GetMessage("IBLOCK_XML2_BX_FILTER").">\n");
			fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_BX_SHOW_IN_LIST").">".($arField["SHOW_IN_LIST"] == "Y"? "true": "false")."</".GetMessage("IBLOCK_XML2_BX_SHOW_IN_LIST").">\n");
			fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_BX_EDIT_IN_LIST").">".($arField["EDIT_IN_LIST"] == "Y"? "true": "false")."</".GetMessage("IBLOCK_XML2_BX_EDIT_IN_LIST").">\n");
			fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_BX_SEARCH").">".($arField["IS_SEARCHABLE"] == "Y"? "true": "false")."</".GetMessage("IBLOCK_XML2_BX_SEARCH").">\n");
			fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_BX_SETTINGS").">".htmlspecialcharsbx(serialize($arField["SETTINGS"]))."</".GetMessage("IBLOCK_XML2_BX_SETTINGS").">\n");

			if (is_callable(array($arField["USER_TYPE"]['CLASS_NAME'], 'getlist')))
			{
				fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_CHOICE_VALUES").">\n");

				$rsEnum = call_user_func_array(
					array($arField["USER_TYPE"]["CLASS_NAME"], "getlist"),
					array(
						$arField,
					)
				);
				if (is_object($rsEnum))
				{
					while ($arEnum = $rsEnum->GetNext())
					{
						fwrite($this->fp,
							"\t\t\t\t\t<".GetMessage("IBLOCK_XML2_CHOICE").">\n"
							.$this->formatXMLNode(6, GetMessage("IBLOCK_XML2_ID"), $arEnum["XML_ID"])
							.$this->formatXMLNode(6, GetMessage("IBLOCK_XML2_VALUE"), $arEnum["VALUE"])
							.$this->formatXMLNode(6, GetMessage("IBLOCK_XML2_BY_DEFAULT"), ($arEnum["DEF"] == "Y" ? "true" : "false"))
							.$this->formatXMLNode(6, GetMessage("IBLOCK_XML2_SORT"), intval($arEnum["SORT"]))
							."\t\t\t\t\t</".GetMessage("IBLOCK_XML2_CHOICE").">\n"
						);
					}
				}
				unset($rsEnum);

				fwrite($this->fp, "\t\t\t\t</".GetMessage("IBLOCK_XML2_CHOICE_VALUES").">\n");
			}

			fwrite($this->fp, "\t\t\t</".GetMessage("IBLOCK_XML2_PROPERTY").">\n");
		}
		fwrite($this->fp, "\t\t</".GetMessage("IBLOCK_XML2_GROUPS_PROPERTIES").">\n");
	}

	function ExportSections(&$SECTION_MAP, $start_time, $INTERVAL, $FILTER = "", $PROPERTY_MAP = array())
	{
		/** @var CUserTypeManager $USER_FIELD_MANAGER */
		global $USER_FIELD_MANAGER;

		$counter = 0;
		if(!array_key_exists("CURRENT_DEPTH", $this->next_step))
			$this->next_step["CURRENT_DEPTH"]=0;
		else // this makes second "step"
			return $counter;

		$arUserFields = $USER_FIELD_MANAGER->GetUserFields("IBLOCK_".$this->arIBlock["ID"]."_SECTION");
		foreach($arUserFields as $FIELD_ID => $arField)
			if($arField["XML_ID"] == '')
				$arUserFields[$FIELD_ID]["XML_ID"] = $FIELD_ID;

		if($this->bExtended)
			$this->ExportSectionsProperties($arUserFields);

		$SECTION_MAP = array();

		if($FILTER === "none")
			return 0;
		$arFilter = array(
			"IBLOCK_ID" => $this->arIBlock["ID"],
			"GLOBAL_ACTIVE" => "Y",
			"CHECK_PERMISSIONS" => "N",
		);
		if($FILTER === "all")
			unset($arFilter["GLOBAL_ACTIVE"]);

		$rsSections = CIBlockSection::GetList(array("left_margin"=>"asc"), $arFilter, false, array("UF_*"));
		fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_GROUPS").">\n");
		while($arSection = $rsSections->Fetch())
		{
			$white_space = str_repeat("\t\t", $arSection["DEPTH_LEVEL"]);
			$level = ($arSection["DEPTH_LEVEL"]+1)*2;

			while($this->next_step["CURRENT_DEPTH"] >= $arSection["DEPTH_LEVEL"])
			{
				fwrite($this->fp, str_repeat("\t\t", $this->next_step["CURRENT_DEPTH"])."\t\t</".GetMessage("IBLOCK_XML2_GROUPS").">\n");
				fwrite($this->fp, str_repeat("\t\t", $this->next_step["CURRENT_DEPTH"]-1)."\t\t\t</".GetMessage("IBLOCK_XML2_GROUP").">\n");
				$this->next_step["CURRENT_DEPTH"]--;
			}

			$xml_id = $this->GetSectionXML_ID($this->arIBlock["ID"], $arSection["ID"], $arSection["XML_ID"]);
			$SECTION_MAP[$arSection["ID"]] = $xml_id;

			fwrite($this->fp,
				$white_space."\t<".GetMessage("IBLOCK_XML2_GROUP").">\n"
				.$this->formatXMLNode($level, GetMessage("IBLOCK_XML2_ID"), $xml_id)
				.$this->formatXMLNode($level, GetMessage("IBLOCK_XML2_NAME"), $arSection["NAME"])
			);
			if($arSection["DESCRIPTION"] <> '')
				fwrite($this->fp, $white_space."\t\t<".GetMessage("IBLOCK_XML2_DESCRIPTION").">".htmlspecialcharsbx(FormatText($arSection["DESCRIPTION"], $arSection["DESCRIPTION_TYPE"]))."</".GetMessage("IBLOCK_XML2_DESCRIPTION").">\n");
			if($this->bExtended)
			{
				fwrite($this->fp,
					$this->formatXMLNode($level, GetMessage("IBLOCK_XML2_BX_ACTIVE"), ($arSection["ACTIVE"]=="Y"? "true": "false"))
					.$this->formatXMLNode($level, GetMessage("IBLOCK_XML2_BX_SORT"), intval($arSection["SORT"]))
					.$this->formatXMLNode($level, GetMessage("IBLOCK_XML2_BX_CODE"), $arSection["CODE"])
					.$this->formatXMLNode($level, GetMessage("IBLOCK_XML2_BX_PICTURE"), $this->ExportFile($arSection["PICTURE"]))
					.$this->formatXMLNode($level, GetMessage("IBLOCK_XML2_BX_DETAIL_PICTURE"), $this->ExportFile($arSection["DETAIL_PICTURE"]))
				);

				if(!empty($arUserFields))
				{
					fwrite($this->fp, $white_space."\t\t<".GetMessage("IBLOCK_XML2_PROPERTIES_VALUES").">\n");
					foreach($arUserFields as $FIELD_ID => $arField)
					{
						fwrite($this->fp, $white_space."\t\t\t<".GetMessage("IBLOCK_XML2_PROPERTY_VALUES").">\n");
						fwrite($this->fp, $this->formatXMLNode($level+2, GetMessage("IBLOCK_XML2_ID"), $arField["XML_ID"]));

						$values = array();
						if(!is_array($arSection[$FIELD_ID]))
						{
							if($arField["USER_TYPE"]["BASE_TYPE"] === "file")
								$values[] = $this->ExportFile($arSection[$FIELD_ID]);
							elseif($arField["USER_TYPE"]["BASE_TYPE"] === "enum")
								$values[] = $this->ExportEnum($arField, $arSection[$FIELD_ID]);
							else
								$values[] = $arSection[$FIELD_ID];
						}
						elseif(empty($arSection[$FIELD_ID]))
						{
							$values[] = "";
						}
						else
						{
							foreach($arSection[$FIELD_ID] as $value)
							{
								if($arField["USER_TYPE"]["BASE_TYPE"] === "file")
									$values[] = $this->ExportFile($value);
								elseif($arField["USER_TYPE"]["BASE_TYPE"] === "enum")
									$values[] = $this->ExportEnum($arField, $value);
								else
									$values[] = $value;
							}
						}

						foreach($values as $value)
						{
							fwrite($this->fp, $this->formatXMLNode($level+2, GetMessage("IBLOCK_XML2_VALUE"), $value));
						}

						fwrite($this->fp, $white_space."\t\t\t</".GetMessage("IBLOCK_XML2_PROPERTY_VALUES").">\n");
					}
					fwrite($this->fp, $white_space."\t\t</".GetMessage("IBLOCK_XML2_PROPERTIES_VALUES").">\n");
				}

				$this->ExportSmartFilter($level, $this->arIBlock["ID"], $arSection["ID"], $PROPERTY_MAP);

				$sectionTemplates = new \Bitrix\Iblock\InheritedProperty\SectionTemplates($this->arIBlock["ID"], $arSection["ID"]);
				$this->exportInheritedTemplates($arSection["DEPTH_LEVEL"]*2 + 2, $sectionTemplates);
			}

			fwrite($this->fp, $white_space."\t\t<".GetMessage("IBLOCK_XML2_GROUPS").">\n");

			$this->next_step["CURRENT_DEPTH"] = $arSection["DEPTH_LEVEL"];
			$counter++;
		}

		while($this->next_step["CURRENT_DEPTH"] > 0)
		{
			fwrite($this->fp, str_repeat("\t\t", $this->next_step["CURRENT_DEPTH"])."\t\t</".GetMessage("IBLOCK_XML2_GROUPS").">\n");
			fwrite($this->fp, str_repeat("\t\t", $this->next_step["CURRENT_DEPTH"]-1)."\t\t\t</".GetMessage("IBLOCK_XML2_GROUP").">\n");
			$this->next_step["CURRENT_DEPTH"]--;
		}
		fwrite($this->fp, "\t\t</".GetMessage("IBLOCK_XML2_GROUPS").">\n");

		return $counter;
	}

	function ExportProperties(&$PROPERTY_MAP)
	{
		$PROPERTY_MAP = array();

		fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_PROPERTIES").">\n");

		if($this->bExtended)
		{
			$arElementFields = array(
				"CML2_ACTIVE" => GetMessage("IBLOCK_XML2_BX_ACTIVE"),
				"CML2_CODE" => GetMessage("IBLOCK_XML2_SYMBOL_CODE"),
				"CML2_SORT" => GetMessage("IBLOCK_XML2_SORT"),
				"CML2_ACTIVE_FROM" => GetMessage("IBLOCK_XML2_START_TIME"),
				"CML2_ACTIVE_TO" => GetMessage("IBLOCK_XML2_END_TIME"),
				"CML2_PREVIEW_TEXT" => GetMessage("IBLOCK_XML2_ANONS"),
				"CML2_DETAIL_TEXT" => GetMessage("IBLOCK_XML2_DETAIL"),
				"CML2_PREVIEW_PICTURE" => GetMessage("IBLOCK_XML2_PREVIEW_PICTURE"),
			);

			foreach($arElementFields as $key => $value)
			{
				fwrite($this->fp, $this->formatXMLNode(3, GetMessage("IBLOCK_XML2_PROPERTY"), array(
					GetMessage("IBLOCK_XML2_ID") => $key,
					GetMessage("IBLOCK_XML2_NAME") => $value,
					GetMessage("IBLOCK_XML2_MULTIPLE") => "false",
				)));
			}
		}

		$featureList = [];
		$iterator = Iblock\PropertyFeatureTable::getList([
			'select' => ['PROPERTY_ID', 'MODULE_ID', 'FEATURE_ID', 'IS_ENABLED'],
			'filter' => [
				'=PROPERTY.IBLOCK_ID' => $this->arIBlock['ID'],
				'=PROPERTY.ACTIVE' => 'Y'
			],
			'order' => ['PROPERTY_ID' => 'ASC']
		]);
		while ($row = $iterator->fetch())
		{
			$id = (int)$row['PROPERTY_ID'];
			if (!isset($featureList[$id]))
				$featureList[$id] = [];
			$featureList[$id][] = [
				'MODULE_ID' => $row['MODULE_ID'],
				'FEATURE_ID' => $row['FEATURE_ID'],
				'IS_ENABLED' => $row['IS_ENABLED']
			];
			unset($id);
		}
		unset($row, $iterator);

		$arFilter = array(
			"IBLOCK_ID" => $this->arIBlock["ID"],
			"ACTIVE" => "Y",
		);
		$arSort = array(
			"sort" => "asc",
		);

		$obProp = new CIBlockProperty();
		$rsProp = $obProp->GetList($arSort, $arFilter);
		while($arProp = $rsProp->Fetch())
		{
			$id = (int)$arProp["ID"];
			fwrite($this->fp, "\t\t\t<".GetMessage("IBLOCK_XML2_PROPERTY").">\n");

			$xml_id = $this->GetPropertyXML_ID($this->arIBlock["ID"], $arProp["NAME"], $arProp["ID"], $arProp["XML_ID"]);
			$PROPERTY_MAP[$arProp["ID"]] = $xml_id;
			$PROPERTY_MAP["~".$arProp["ID"]] = $arProp["NAME"];
			fwrite($this->fp, $this->formatXMLNode(4, GetMessage("IBLOCK_XML2_ID"), $xml_id));

			fwrite($this->fp, $this->formatXMLNode(4, GetMessage("IBLOCK_XML2_NAME"), $arProp["NAME"]));
			fwrite($this->fp, $this->formatXMLNode(4, GetMessage("IBLOCK_XML2_MULTIPLE"), ($arProp["MULTIPLE"]=="Y"? "true": "false")));
			if($arProp["PROPERTY_TYPE"]=="L")
			{
				fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_CHOICE_VALUES").">\n");
				$rsEnum = CIBlockProperty::GetPropertyEnum($arProp["ID"]);
				while($arEnum = $rsEnum->Fetch())
				{
					fwrite($this->fp, $this->formatXMLNode(5, GetMessage("IBLOCK_XML2_VALUE"), $arEnum["VALUE"]));
					if($this->bExtended)
					{
						fwrite($this->fp,
							"\t\t\t\t\t<".GetMessage("IBLOCK_XML2_CHOICE").">\n"
							.$this->formatXMLNode(6, GetMessage("IBLOCK_XML2_ID"), $arEnum["XML_ID"])
							.$this->formatXMLNode(6, GetMessage("IBLOCK_XML2_VALUE"), $arEnum["VALUE"])
							.$this->formatXMLNode(6, GetMessage("IBLOCK_XML2_BY_DEFAULT"), ($arEnum["DEF"]=="Y"? "true": "false"))
							.$this->formatXMLNode(6, GetMessage("IBLOCK_XML2_SORT"), intval($arEnum["SORT"]))
							."\t\t\t\t\t</".GetMessage("IBLOCK_XML2_CHOICE").">\n"
						);
					}
				}
				fwrite($this->fp, "\t\t\t\t</".GetMessage("IBLOCK_XML2_CHOICE_VALUES").">\n");
			}

			if($this->bExtended)
			{
				$strUserSettings = '';
				if ('' != $arProp["USER_TYPE"])
				{
					if (!empty($arProp['USER_TYPE_SETTINGS']) && is_array($arProp['USER_TYPE_SETTINGS']))
					{
						$strUserSettings = $this->formatXMLNode(4, GetMessage("IBLOCK_XML2_BX_USER_TYPE_SETTINGS"), serialize($arProp['USER_TYPE_SETTINGS']));
					}
				}
				$propertyFeatures = '';
				if (isset($featureList[$id]))
				{
					$propertyFeatures = $this->formatXMLNode(
						4,
						GetMessage('IBLOCK_XML2_BX_PROPERTY_FEATURE_LIST'),
						serialize($featureList[$id])
					);
				}

				fwrite($this->fp,
					$this->formatXMLNode(4, GetMessage("IBLOCK_XML2_BX_SORT"), intval($arProp["SORT"]))
					.$this->formatXMLNode(4, GetMessage("IBLOCK_XML2_BX_CODE"), $arProp["CODE"])
					.$this->formatXMLNode(4, GetMessage("IBLOCK_XML2_BX_PROPERTY_TYPE"), $arProp["PROPERTY_TYPE"])
					.$this->formatXMLNode(4, GetMessage("IBLOCK_XML2_BX_ROWS"), $arProp["ROW_COUNT"])
					.$this->formatXMLNode(4, GetMessage("IBLOCK_XML2_BX_COLUMNS"), $arProp["COL_COUNT"])
					.$this->formatXMLNode(4, GetMessage("IBLOCK_XML2_BX_LIST_TYPE"), $arProp["LIST_TYPE"])
					.$this->formatXMLNode(4, GetMessage("IBLOCK_XML2_BX_FILE_EXT"), $arProp["FILE_TYPE"])
					.$this->formatXMLNode(4, GetMessage("IBLOCK_XML2_BX_FIELDS_COUNT"), $arProp["MULTIPLE_CNT"])
					.$this->formatXMLNode(4, GetMessage("IBLOCK_XML2_BX_LINKED_IBLOCK"), $this->GetIBlockXML_ID($arProp["LINK_IBLOCK_ID"]))
					.$this->formatXMLNode(4, GetMessage("IBLOCK_XML2_BX_WITH_DESCRIPTION"), ($arProp["WITH_DESCRIPTION"]=="Y"? "true": "false"))
					.$this->formatXMLNode(4, GetMessage("IBLOCK_XML2_BX_SEARCH"), ($arProp["SEARCHABLE"]=="Y"? "true": "false"))
					.$this->formatXMLNode(4, GetMessage("IBLOCK_XML2_BX_FILTER"), ($arProp["FILTRABLE"]=="Y"? "true": "false"))
					.$this->formatXMLNode(4, GetMessage("IBLOCK_XML2_BX_USER_TYPE"), $arProp["USER_TYPE"])
					.$this->formatXMLNode(4, GetMessage("IBLOCK_XML2_BX_IS_REQUIRED"), ($arProp["IS_REQUIRED"]=="Y"? "true": "false"))
					.$this->formatXMLNode(4, GetMessage("IBLOCK_XML2_BX_DEFAULT_VALUE"), serialize($arProp["DEFAULT_VALUE"]))
					.$this->formatXMLNode(4, GetMessage("IBLOCK_XML2_SERIALIZED"), 1)
					.$strUserSettings
					.$propertyFeatures
				);
			}
			fwrite($this->fp, "\t\t\t</".GetMessage("IBLOCK_XML2_PROPERTY").">\n");
		}
		fwrite($this->fp, "\t\t</".GetMessage("IBLOCK_XML2_PROPERTIES").">\n");

		if($this->bExtended)
		{
			$catalog = false;
			if (Loader::includeModule("catalog"))
			{
				$catalog = CCatalogSKU::getInfoByOfferIBlock($this->arIBlock["ID"]);
			}

			if (!empty($catalog) && is_array($catalog))
			{
				$this->ExportSmartFilter(2, $this->arIBlock["ID"], false, $PROPERTY_MAP, $catalog["PRODUCT_IBLOCK_ID"]);
			}
			else
			{
				$this->ExportSmartFilter(2, $this->arIBlock["ID"], 0, $PROPERTY_MAP);
			}
		}
	}

	function ExportSmartFilter($level, $iblockId, $sectionId, $PROPERTY_MAP, $productIblockId = 0)
	{
		$propertyLinksBySection = array();
		if ($sectionId === false)
		{
			$propertyLinksBySection[0] = CIBlockSectionPropertyLink::GetArray($iblockId, 0);
			foreach($propertyLinksBySection[0] as $PID => $arLink)
			{
				if($arLink["INHERITED"] != "N" || !array_key_exists($PID, $PROPERTY_MAP))
				{
					unset($propertyLinksBySection[0][$PID]);
				}
				else
				{
					if ($productIblockId > 0)
					{
						$iblock_xml_id = $this->GetIBlockXML_ID($productIblockId, CIBlock::GetArrayByID($productIblockId, "XML_ID"));
						$propertyLinksBySection[0][$PID]["IBLOCK_XML_ID"] = $iblock_xml_id;
					}
				}
			}

			$arFilter = array(
				"IBLOCK_ID" => $productIblockId? $productIblockId: $iblockId,
				"CHECK_PERMISSIONS" => "N",
			);
			$rsSections = CIBlockSection::GetList(array("left_margin"=>"asc"), $arFilter, false, array("ID", "XML_ID", "IBLOCK_ID"));
			while($arSection = $rsSections->Fetch())
			{
				$section_xml_id = $this->GetSectionXML_ID($arSection["IBLOCK_ID"], $arSection["ID"], $arSection["XML_ID"]);
				$iblock_xml_id = $this->GetIBlockXML_ID($arSection["IBLOCK_ID"], CIBlock::GetArrayByID($arSection["IBLOCK_ID"], "XML_ID"));

				$propertyLinksBySection[$arSection["ID"]] = CIBlockSectionPropertyLink::GetArray($iblockId, $arSection["ID"]);
				foreach($propertyLinksBySection[$arSection["ID"]] as $PID => $arLink)
				{
					if($arLink["INHERITED"] != "N" || !array_key_exists($PID, $PROPERTY_MAP))
					{
						unset($propertyLinksBySection[$arSection["ID"]][$PID]);
					}
					else
					{
						$propertyLinksBySection[$arSection["ID"]][$PID]["IBLOCK_XML_ID"] = $iblock_xml_id;
						$propertyLinksBySection[$arSection["ID"]][$PID]["SECTION_XML_ID"] = $section_xml_id;
					}
				}
			}
		}
		else
		{
			$propertyLinksBySection[$sectionId] = CIBlockSectionPropertyLink::GetArray($iblockId, $sectionId);
			foreach($propertyLinksBySection[$sectionId] as $PID => $arLink)
			{
				if($arLink["INHERITED"] != "N" || !array_key_exists($PID, $PROPERTY_MAP))
					unset($propertyLinksBySection[$sectionId][$PID]);
			}
		}

		$first = true;
		foreach ($propertyLinksBySection as $arPropLink)
		{
			if(!empty($arPropLink))
			{
				if ($first)
				{
					fwrite($this->fp, str_repeat("\t", $level)."<".GetMessage("IBLOCK_XML2_SECTION_PROPERTIES").">\n");
					$first = false;
				}

				foreach($arPropLink as $PID => $arLink)
				{
					$xmlLink = array(
						GetMessage("IBLOCK_XML2_ID") => $PROPERTY_MAP[$PID],
						GetMessage("IBLOCK_XML2_SMART_FILTER") => ($arLink["SMART_FILTER"] == "Y"? "true": "false"),
						GetMessage("IBLOCK_XML2_SMART_FILTER_DISPLAY_TYPE") => $arLink["DISPLAY_TYPE"],
						GetMessage("IBLOCK_XML2_SMART_FILTER_DISPLAY_EXPANDED") => ($arLink["DISPLAY_EXPANDED"] == "Y"? "true": "false"),
						GetMessage("IBLOCK_XML2_SMART_FILTER_HINT") => $arLink["FILTER_HINT"],
					);

					if (isset($arLink["IBLOCK_XML_ID"]))
					{
						$xmlLink[GetMessage("IBLOCK_XML2_BX_LINKED_IBLOCK")] = $arLink["IBLOCK_XML_ID"];
					}

					if (isset($arLink["SECTION_XML_ID"]))
					{
						$xmlLink[GetMessage("IBLOCK_XML2_GROUP")] = $arLink["SECTION_XML_ID"];
					}

					fwrite($this->fp, $this->formatXMLNode($level+1, GetMessage("IBLOCK_XML2_PROPERTY"), $xmlLink));
				}
			}
		}
		if (!$first)
		{
			fwrite($this->fp, str_repeat("\t", $level)."</".GetMessage("IBLOCK_XML2_SECTION_PROPERTIES").">\n");
		}
	}

	function ExportPrices()
	{
		if($this->next_step["catalog"])
		{
			$priceTypes = CCatalogGroup::GetListArray();
			if (!empty($priceTypes))
			{
				fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_PRICE_TYPES").">\n");
				foreach ($priceTypes as $arPrice)
				{
					fwrite($this->fp, $this->formatXMLNode(3, GetMessage("IBLOCK_XML2_PRICE_TYPE"), array(
						GetMessage("IBLOCK_XML2_ID") => $arPrice["NAME"],
						GetMessage("IBLOCK_XML2_NAME") => $arPrice["NAME"],
					)));
				}
				unset($arPrice);
				fwrite($this->fp, "\t\t</".GetMessage("IBLOCK_XML2_PRICE_TYPES").">\n");
			}
			unset($priceTypes);
		}
	}

	function EndExportMetadata()
	{
		fwrite($this->fp, "\t</".GetMessage("IBLOCK_XML2_METADATA").">\n");
	}

	function StartExportCatalog($with_metadata = true, $changes_only = false)
	{
		if($this->next_step["catalog"])
			fwrite($this->fp, "\t<".GetMessage("IBLOCK_XML2_OFFER_LIST").">\n");
		else
			fwrite($this->fp, "\t<".GetMessage("IBLOCK_XML2_CATALOG").">\n");

		if($this->PRODUCT_IBLOCK_ID)
			$xml_id = $this->GetIBlockXML_ID($this->PRODUCT_IBLOCK_ID, CIBlock::GetArrayByID($this->PRODUCT_IBLOCK_ID, "XML_ID"));
		else
			$xml_id = $this->GetIBlockXML_ID($this->arIBlock["ID"], $this->arIBlock["XML_ID"]);
		$this->arIBlock["XML_ID"] = $xml_id;

		fwrite($this->fp, $this->formatXMLNode(2, GetMessage("IBLOCK_XML2_ID"), $xml_id));
		if($with_metadata)
		{
			fwrite($this->fp, $this->formatXMLNode(2, GetMessage("IBLOCK_XML2_METADATA_ID"), $xml_id));
			fwrite($this->fp, $this->formatXMLNode(2, GetMessage("IBLOCK_XML2_NAME"), $this->arIBlock["NAME"]));

			if($this->arIBlock["DESCRIPTION"] <> '')
				fwrite($this->fp, $this->formatXMLNode(2, GetMessage("IBLOCK_XML2_DESCRIPTION"), FormatText($this->arIBlock["DESCRIPTION"], $this->arIBlock["DESCRIPTION_TYPE"])));

			if($this->bExtended)
			{
				fwrite($this->fp,
					$this->formatXMLNode(2, GetMessage("IBLOCK_XML2_BX_CODE"), $this->arIBlock["CODE"])
					.$this->formatXMLNode(2, GetMessage("IBLOCK_XML2_BX_API_CODE"), (string)$this->arIBlock["API_CODE"])
					.$this->formatXMLNode(2, GetMessage("IBLOCK_XML2_BX_SORT"), intval($this->arIBlock["SORT"]))
					.$this->formatXMLNode(2, GetMessage("IBLOCK_XML2_BX_LIST_URL"), $this->arIBlock["LIST_PAGE_URL"])
					.$this->formatXMLNode(2, GetMessage("IBLOCK_XML2_BX_DETAIL_URL"), $this->arIBlock["DETAIL_PAGE_URL"])
					.$this->formatXMLNode(2, GetMessage("IBLOCK_XML2_BX_SECTION_URL"), $this->arIBlock["SECTION_PAGE_URL"])
					.$this->formatXMLNode(2, GetMessage("IBLOCK_XML2_BX_CANONICAL_URL"), $this->arIBlock["CANONICAL_PAGE_URL"])
					.$this->formatXMLNode(2, GetMessage("IBLOCK_XML2_BX_PICTURE"), $this->ExportFile($this->arIBlock["PICTURE"]))
					.$this->formatXMLNode(2, GetMessage("IBLOCK_XML2_BX_INDEX_ELEMENTS"), ($this->arIBlock["INDEX_ELEMENT"]=="Y"? "true": "false"))
					.$this->formatXMLNode(2, GetMessage("IBLOCK_XML2_BX_INDEX_SECTIONS"), ($this->arIBlock["INDEX_SECTION"]=="Y"? "true": "false"))
					.$this->formatXMLNode(2, GetMessage("IBLOCK_XML2_BX_WORKFLOW"), ($this->arIBlock["WORKFLOW"]=="Y"? "true": "false"))
				);

				fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_LABELS").">\n");
				$arLabels = CIBlock::GetMessages($this->arIBlock["ID"]);
				foreach($arLabels as $id => $label)
				{
					fwrite($this->fp, $this->formatXMLNode(3, GetMessage("IBLOCK_XML2_LABEL"), array(
						GetMessage("IBLOCK_XML2_ID") => $id,
						GetMessage("IBLOCK_XML2_VALUE") => $label,
					)));
				}
				fwrite($this->fp, "\t\t</".GetMessage("IBLOCK_XML2_LABELS").">\n");

				$iblockTemplates = new \Bitrix\Iblock\InheritedProperty\IblockTemplates($this->arIBlock["ID"]);
				$this->exportInheritedTemplates(2, $iblockTemplates);
			}
		}

		if($with_metadata || $this->only_price)
		{
			$this->ExportPrices();
		}

		if($changes_only)
			fwrite($this->fp, $this->formatXMLNode(2, GetMessage("IBLOCK_XML2_UPDATE_ONLY"), "true"));

		if($this->next_step["catalog"])
			fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_OFFERS").">\n");
		else
			fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_POSITIONS").">\n");
	}

	function ExportPropertyValue($xml_id, $value, $type = null)
	{
		fwrite($this->fp, $this->formatXMLNode(5, GetMessage("IBLOCK_XML2_PROPERTY_VALUES"), array(
			GetMessage("IBLOCK_XML2_ID") => $xml_id,
			GetMessage("IBLOCK_XML2_VALUE") => $value,
			(isset($type)? GetMessage("IBLOCK_XML2_TYPE"): "") => $type,
		)));
	}

	function exportInheritedTemplates($level, \Bitrix\Iblock\InheritedProperty\BaseTemplate $template)
	{
		$templates = $template->get();
		if (!empty($templates))
		{
			$ws = str_repeat("\t", $level);
			fwrite($this->fp, $ws."<".GetMessage("IBLOCK_XML2_INHERITED_TEMPLATES").">\n");
			foreach ($templates as $CODE => $TEMPLATE)
			{
				fwrite($this->fp, $ws."\t<".GetMessage("IBLOCK_XML2_TEMPLATE").">\n");
				fwrite($this->fp, $ws."\t\t<".GetMessage("IBLOCK_XML2_ID").">".htmlspecialcharsbx($CODE)."</".GetMessage("IBLOCK_XML2_ID").">\n");
				fwrite($this->fp, $ws."\t\t<".GetMessage("IBLOCK_XML2_VALUE").">".htmlspecialcharsbx($TEMPLATE["TEMPLATE"])."</".GetMessage("IBLOCK_XML2_VALUE").">\n");
				fwrite($this->fp, $ws."\t</".GetMessage("IBLOCK_XML2_TEMPLATE").">\n");
			}
			fwrite($this->fp, $ws."</".GetMessage("IBLOCK_XML2_INHERITED_TEMPLATES").">\n");
		}
	}

	/**
	 * @param array $arElement
	 * @param array $arFilter
	 * @return array
	 */
	function preparePropertiesValues($arElement, $arFilter=array("ACTIVE"=>"Y"))
	{
		$arPropOrder = array(
			"sort" => "asc",
			"id" => "asc",
			"enum_sort" => "asc",
			"value_id" => "asc",
		);

		$rsProps = CIBlockElement::GetProperty($this->arIBlock["ID"], $arElement["ID"], $arPropOrder, $arFilter);
		$arProps = array();
		while($arProp = $rsProps->Fetch())
		{
			$pid = $arProp["ID"];
			if(!array_key_exists($pid, $arProps))
				$arProps[$pid] = array(
					"PROPERTY_TYPE" => $arProp["PROPERTY_TYPE"],
					"LINK_IBLOCK_ID" => $arProp["LINK_IBLOCK_ID"],
					"VALUES" => array(),
				);

			if($arProp["PROPERTY_TYPE"] == "L")
				$arProps[$pid]["VALUES"][] = array(
					"VALUE" => $arProp["VALUE_ENUM"],
					"DESCRIPTION" => $arProp["DESCRIPTION"],
					"VALUE_ENUM_ID" => $arProp["VALUE"],
				);
			else
				$arProps[$pid]["VALUES"][] = array(
					"VALUE" => $arProp["VALUE"],
					"DESCRIPTION" => $arProp["DESCRIPTION"],
					"VALUE_ENUM_ID" => $arProp["VALUE_ENUM_ID"],
				);
		}

		return $arProps;
	}

	function exportElementProperties($arElement, $PROPERTY_MAP)
	{
		if($this->bExtended)
		{
			$this->ExportPropertyValue("CML2_ACTIVE", ($arElement["ACTIVE"]=="Y"? "true": "false"));
			$this->ExportPropertyValue("CML2_CODE", $arElement["CODE"]);
			$this->ExportPropertyValue("CML2_SORT", intval($arElement["SORT"]));
			$this->ExportPropertyValue("CML2_ACTIVE_FROM", CDatabase::FormatDate($arElement["ACTIVE_FROM"], CLang::GetDateFormat("FULL"), "YYYY-MM-DD HH:MI:SS"));
			$this->ExportPropertyValue("CML2_ACTIVE_TO", CDatabase::FormatDate($arElement["ACTIVE_TO"], CLang::GetDateFormat("FULL"), "YYYY-MM-DD HH:MI:SS"));
			$this->ExportPropertyValue("CML2_PREVIEW_TEXT", $arElement["PREVIEW_TEXT"], $arElement["PREVIEW_TEXT_TYPE"]);
			$this->ExportPropertyValue("CML2_DETAIL_TEXT", $arElement["DETAIL_TEXT"], $arElement["DETAIL_TEXT_TYPE"]);
			$this->ExportPropertyValue("CML2_PREVIEW_PICTURE", $this->ExportFile($arElement["PREVIEW_PICTURE"]));
		}

		$arProps = $this->preparePropertiesValues($arElement);

		foreach($arProps as $pid => $arProp)
		{
			$bEmpty = true;

			if($this->next_step["catalog"] && !$this->bExtended)
				fwrite($this->fp, "\t\t\t\t\t<".GetMessage("IBLOCK_XML2_ITEM_ATTRIBUTE").">\n");
			else
				fwrite($this->fp, "\t\t\t\t\t<".GetMessage("IBLOCK_XML2_PROPERTY_VALUES").">\n");

			if($this->next_step["catalog"] && !$this->bExtended)
				fwrite($this->fp, "\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_NAME").">".htmlspecialcharsbx($PROPERTY_MAP["~".$pid])."</".GetMessage("IBLOCK_XML2_NAME").">\n");
			else
				fwrite($this->fp, "\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_ID").">".htmlspecialcharsbx($PROPERTY_MAP[$pid])."</".GetMessage("IBLOCK_XML2_ID").">\n");

			foreach($arProp["VALUES"] as $arValue)
			{
				$value = $arValue["VALUE"];
				if(is_array($value) || (string)$value != '')
				{
					$bEmpty = false;
					$bSerialized = false;
					$this->preparePropertyValue($arProp, $arValue, $value, $bSerialized);
					fwrite($this->fp, $this->formatXMLNode(6, GetMessage("IBLOCK_XML2_VALUE"), $value));
					if($this->bExtended)
					{
						fwrite($this->fp, "\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_PROPERTY_VALUE").">\n");
						if($bSerialized)
							fwrite($this->fp, "\t\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_SERIALIZED").">true</".GetMessage("IBLOCK_XML2_SERIALIZED").">\n");
						fwrite($this->fp, $this->formatXMLNode(7, GetMessage("IBLOCK_XML2_VALUE"), $value));
						fwrite($this->fp, "\t\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_DESCRIPTION").">".htmlspecialcharsbx($arValue["DESCRIPTION"])."</".GetMessage("IBLOCK_XML2_DESCRIPTION").">\n");
						fwrite($this->fp, "\t\t\t\t\t\t</".GetMessage("IBLOCK_XML2_PROPERTY_VALUE").">\n");
					}
				}
			}

			if($bEmpty)
				fwrite($this->fp, "\t\t\t\t\t\t<".GetMessage("IBLOCK_XML2_VALUE")."></".GetMessage("IBLOCK_XML2_VALUE").">\n");

			if($this->next_step["catalog"] && !$this->bExtended)
				fwrite($this->fp, "\t\t\t\t\t</".GetMessage("IBLOCK_XML2_ITEM_ATTRIBUTE").">\n");
			else
				fwrite($this->fp, "\t\t\t\t\t</".GetMessage("IBLOCK_XML2_PROPERTY_VALUES").">\n");
		}
	}

	function preparePropertyValue($arProp, $arValue, &$value, &$bSerialized)
	{
		if($this->bExtended)
		{
			if($arProp["PROPERTY_TYPE"]=="L")
			{
				$value = CIBlockPropertyEnum::GetByID($arValue["VALUE_ENUM_ID"]);
				$value = $value["XML_ID"];
			}
			elseif($arProp["PROPERTY_TYPE"]=="F")
			{
				$value = $this->ExportFile($value);
			}
			elseif($arProp["PROPERTY_TYPE"]=="G")
			{
				$value = $this->GetSectionXML_ID($arProp["LINK_IBLOCK_ID"], $value);
			}
			elseif($arProp["PROPERTY_TYPE"]=="E")
			{
				$value = $this->GetElementXML_ID($arProp["LINK_IBLOCK_ID"], $value);
			}

			if(is_array($value) && $arProp["PROPERTY_TYPE"]!=="F")
			{
				$bSerialized = true;
				$value = serialize($value);
			}
		}
	}

	function exportElementFields($arElement, $SECTION_MAP)
	{
		fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_NAME").">".htmlspecialcharsbx($arElement["NAME"])."</".GetMessage("IBLOCK_XML2_NAME").">\n");
		if($this->bExtended)
			fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_BX_TAGS").">".htmlspecialcharsbx($arElement["TAGS"])."</".GetMessage("IBLOCK_XML2_BX_TAGS").">\n");

		$arSections = array();
		$rsSections = CIBlockElement::GetElementGroups($arElement["ID"], true);
		while($arSection = $rsSections->Fetch())
			if(array_key_exists($arSection["ID"], $SECTION_MAP))
				$arSections[] = $SECTION_MAP[$arSection["ID"]];

		fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_GROUPS").">\n");
		foreach($arSections as $xml_id)
			fwrite($this->fp, "\t\t\t\t\t<".GetMessage("IBLOCK_XML2_ID").">".htmlspecialcharsbx($xml_id)."</".GetMessage("IBLOCK_XML2_ID").">\n");
		fwrite($this->fp, "\t\t\t\t</".GetMessage("IBLOCK_XML2_GROUPS").">\n");

		if(!$this->bExtended)
			fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_DESCRIPTION").">".htmlspecialcharsbx(FormatText($arElement["DETAIL_TEXT"], $arElement["DETAIL_TEXT_TYPE"]))."</".GetMessage("IBLOCK_XML2_DESCRIPTION").">\n");

		fwrite($this->fp, $this->formatXMLNode(4, GetMessage("IBLOCK_XML2_PICTURE"), $this->ExportFile($arElement["DETAIL_PICTURE"])));
	}

	function aliasXmlId($xml_id)
	{
		return '';
	}

	function exportElement($arElement, $SECTION_MAP, $PROPERTY_MAP)
	{
		if($arElement["XML_ID"] <> '')
			$xml_id = $arElement["XML_ID"];
		else
			$xml_id = $arElement["ID"];

		if($this->PRODUCT_IBLOCK_ID > 0)
		{
			$arPropOrder = array(
				"sort" => "asc",
				"id" => "asc",
				"enum_sort" => "asc",
				"value_id" => "asc",
			);
			$rsLink = CIBlockElement::GetProperty($this->arIBlock["ID"], $arElement["ID"], $arPropOrder, array("ACTIVE"=>"Y", "CODE" => "CML2_LINK"));
			$arLink = $rsLink->Fetch();
			if(is_array($arLink) && !is_array($arLink["VALUE"]) && $arLink["VALUE"] > 0)
			{
				$parent_xml_id = $this->GetElementXML_ID($this->PRODUCT_IBLOCK_ID, $arLink["VALUE"]);
				if ($parent_xml_id === $xml_id)
					$xml_id = $parent_xml_id;
				else
				{
					[$productXmlId, $offerXmlId] = explode("#", $xml_id, 2);
					if ($productXmlId !== $parent_xml_id)
						$xml_id = $parent_xml_id."#".$xml_id;
				}
			}
		}

		fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_ID").">".htmlspecialcharsbx($xml_id)."</".GetMessage("IBLOCK_XML2_ID").">\n");

		if(($aliasXmlId = $this->aliasXmlId($xml_id))>0)
			fwrite($this->fp, $aliasXmlId);

		if(!$this->only_price)
		{
			$this->exportElementFields($arElement, $SECTION_MAP);

			if($this->next_step["catalog"] && !$this->bExtended)
				fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_ITEM_ATTRIBUTES").">\n");
			else
				fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_PROPERTIES_VALUES").">\n");

			$this->exportElementProperties($arElement, $PROPERTY_MAP);

			if($this->next_step["catalog"] && !$this->bExtended)
				fwrite($this->fp, "\t\t\t\t</".GetMessage("IBLOCK_XML2_ITEM_ATTRIBUTES").">\n");
			else
				fwrite($this->fp, "\t\t\t\t</".GetMessage("IBLOCK_XML2_PROPERTIES_VALUES").">\n");

			if($this->bExtended)
			{
				$elementTemplates = new \Bitrix\Iblock\InheritedProperty\ElementTemplates($this->arIBlock["ID"], $arElement["ID"]);
				$this->exportInheritedTemplates(4, $elementTemplates);
			}
		}

		if($this->next_step["catalog"])
		{
			static $measure = null;
			if (!isset($measure))
			{
				$measure = array();
				$rsBaseUnit = CCatalogMeasure::GetList(array(), array());
				while($arIDUnit = $rsBaseUnit->Fetch())
					$measure[$arIDUnit["ID"]] = $arIDUnit["CODE"];
			}

			static $systemFieldTypes = null;
			if (!isset($systemFieldTypes))
			{
				$systemFieldTypes = [];
				if (Product\SystemField\MarkingCodeGroup::isAllowed())
				{
					$systemFieldTypes[Product\SystemField\MarkingCodeGroup::FIELD_ID] = array_fill_keys(
						Product\SystemField\MarkingCodeGroup::getAllowedProductTypeList(),
						true
					);
				}
				if (Product\SystemField\ProductMapping::isAllowed())
				{
					$systemFieldTypes[Product\SystemField\ProductMapping::FIELD_ID] = array_fill_keys(
						Product\SystemField\ProductMapping::getAllowedProductTypeList(),
						true
					);
				}
			}

			//TODO: change this code after refactoring product system fields
			$selectFields = ['ID', 'MEASURE', 'QUANTITY', 'TYPE', 'WIDTH', 'LENGTH', 'HEIGHT'];
			$productSystemFields = Catalog\Product\SystemField::getExportSelectFields();

			$selectFields = array_merge($selectFields, $productSystemFields);
			$iterator = Catalog\ProductTable::getList([
				'select' => $selectFields,
				'filter' => ['=ID' => $arElement['ID']]
			]);
			$row = $iterator->fetch();
			unset($iterator);
			if (!empty($row) && is_array($row))
			{
				Product\SystemField::prepareRow($row, Product\SystemField::OPERATION_EXPORT);
				$row['TYPE'] = (int)$row['TYPE'];

				if (
					isset($systemFieldTypes[Product\SystemField\MarkingCodeGroup::FIELD_ID])
					&& isset($systemFieldTypes[Product\SystemField\MarkingCodeGroup::FIELD_ID][$row['TYPE']])
				)
				{
					fwrite(
						$this->fp,
						"\t\t\t\t<".GetMessage("IBLOCK_XML2_MARKING_CODE_GROUP").">"
						.htmlspecialcharsbx((string)$row[Product\SystemField\MarkingCodeGroup::FIELD_ID])
						."</".GetMessage("IBLOCK_XML2_MARKING_CODE_GROUP").">\n"
					);
				}
				if (
					isset($systemFieldTypes[Product\SystemField\ProductMapping::FIELD_ID])
					&& isset($systemFieldTypes[Product\SystemField\ProductMapping::FIELD_ID][$row['TYPE']])
				)
				{
					fwrite(
						$this->fp,
						"\t\t\t\t<".GetMessage("IBLOCK_XML2_PRODUCT_MAPPING").">\n"
					);
					if (
						!empty($row[Product\SystemField\ProductMapping::FIELD_ID])
						&& is_array($row[Product\SystemField\ProductMapping::FIELD_ID])
					)
					{
						foreach ($row[Product\SystemField\ProductMapping::FIELD_ID] as $value)
						{
							fwrite(
								$this->fp,
								"\t\t\t\t\t<".GetMessage('IBLOCK_XML2_ID').">"
								.htmlspecialcharsbx($value)
								."</".GetMessage('IBLOCK_XML2_ID').">\n"
							);
						}
					}
					fwrite(
						$this->fp,
						"\t\t\t\t</".GetMessage("IBLOCK_XML2_PRODUCT_MAPPING").">\n"
					);
				}

				if (
					$row['TYPE'] == Catalog\ProductTable::TYPE_PRODUCT
					|| $row['TYPE'] == Catalog\ProductTable::TYPE_SET
					|| $row['TYPE'] == Catalog\ProductTable::TYPE_OFFER
					|| (
						$row['TYPE'] == Catalog\ProductTable::TYPE_SKU
						&& Main\Config\Option::get('catalog', 'show_catalog_tab_with_offers') === 'Y'
					)
				)
				{
					$row['MEASURE'] = (int)$row['MEASURE'];
					$xmlMeasure = GetMessage("IBLOCK_XML2_PCS");
					if ($row["MEASURE"] > 0 && isset($measure[$row["MEASURE"]]))
						$xmlMeasure = $measure[$row["MEASURE"]];

					$arPrices = [];
					$rsPrices = Catalog\PriceTable::getList([
						'select' => ['ID', 'CATALOG_GROUP_ID', 'PRICE', 'CURRENCY', 'QUANTITY_FROM', 'QUANTITY_TO'],
						'filter' => ['=PRODUCT_ID' => $arElement['ID']]
					]);
					while($arPrice = $rsPrices->fetch())
					{
						$arPrices[] = [
							GetMessage("IBLOCK_XML2_PRICE_TYPE_ID") => $this->prices[$arPrice["CATALOG_GROUP_ID"]],
							GetMessage("IBLOCK_XML2_PRICE_FOR_ONE") => $arPrice["PRICE"],
							GetMessage("IBLOCK_XML2_CURRENCY") => $arPrice["CURRENCY"],
							GetMessage("IBLOCK_XML2_MEASURE") => $xmlMeasure,
							GetMessage("IBLOCK_XML2_QUANTITY_FROM") => $arPrice["QUANTITY_FROM"],
							GetMessage("IBLOCK_XML2_QUANTITY_TO") => $arPrice["QUANTITY_TO"],
						];
					}
					unset($arPrice);
					unset($rsPrices);
					if (!empty($arPrices))
					{
						fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_PRICES").">\n");
						foreach($arPrices as $arPrice)
						{
							fwrite($this->fp, "\t\t\t\t\t<".GetMessage("IBLOCK_XML2_PRICE").">\n");
							foreach($arPrice as $key=>$value)
							{
								fwrite($this->fp, "\t\t\t\t\t\t<".$key.">".htmlspecialcharsbx($value)."</".$key.">\n");
							}
							fwrite($this->fp, "\t\t\t\t\t</".GetMessage("IBLOCK_XML2_PRICE").">\n");
						}
						fwrite($this->fp, "\t\t\t\t</".GetMessage("IBLOCK_XML2_PRICES").">\n");
					}
					unset($arPrices);
					if ($row['TYPE'] != Catalog\ProductTable::TYPE_SET)
					{
						fwrite(
							$this->fp,
							"\t\t\t\t<".GetMessage("IBLOCK_XML2_AMOUNT").">"
							.htmlspecialcharsbx($row["QUANTITY"])
							."</".GetMessage("IBLOCK_XML2_AMOUNT").">\n"
						);
					}
					fwrite(
						$this->fp,
						"\t\t\t\t<".GetMessage("IBLOCK_XML2_BASE_UNIT").">"
						.htmlspecialcharsbx($xmlMeasure)
						."</".GetMessage("IBLOCK_XML2_BASE_UNIT").">\n"
					);
					fwrite(
						$this->fp,
						"\t\t\t\t<".GetMessage("IBLOCK_XML2_WIDTH").">"
						.htmlspecialcharsbx($row["WIDTH"])
						."</".GetMessage("IBLOCK_XML2_WIDTH").">\n"
					);
					fwrite(
						$this->fp,
						"\t\t\t\t<".GetMessage("IBLOCK_XML2_LENGTH").">"
						.htmlspecialcharsbx($row["LENGTH"])
						."</".GetMessage("IBLOCK_XML2_LENGTH").">\n"
					);
					fwrite(
						$this->fp,
						"\t\t\t\t<".GetMessage("IBLOCK_XML2_HEIGHT").">"
						.htmlspecialcharsbx($row["HEIGHT"])
						."</".GetMessage("IBLOCK_XML2_HEIGHT").">\n"
					);
				}
			}
			unset($row);
		}
	}

	function ExportElements($PROPERTY_MAP, $SECTION_MAP, $start_time, $INTERVAL, $counter_limit = 0, $arElementFilter = false)
	{
		$counter = 0;
		$arSelect = array(
			"ID",
			"IBLOCK_ID",
			"XML_ID",
			"ACTIVE",
			"CODE",
			"NAME",
			"PREVIEW_TEXT",
			"PREVIEW_TEXT_TYPE",
			"ACTIVE_FROM",
			"ACTIVE_TO",
			"SORT",
			"TAGS",
			"DETAIL_TEXT",
			"DETAIL_TEXT_TYPE",
			"PREVIEW_PICTURE",
			"DETAIL_PICTURE",
		);

		if(is_array($arElementFilter))
		{
			$arFilter = $arElementFilter;
		}
		else
		{
			if($arElementFilter === "none")
				return 0;
			$arFilter = array (
				"IBLOCK_ID"=> $this->arIBlock["ID"],
				"ACTIVE" => "Y",
				">ID" => $this->next_step["LAST_ID"] ?? 0,
			);
			if($arElementFilter === "all")
				unset($arFilter["ACTIVE"]);
		}

		$arOrder = array(
			"ID" => "ASC",
		);

		$rsElements = CIBlockElement::GetList($arOrder, $arFilter, false, false, $arSelect);
		while($arElement = $rsElements->Fetch())
		{
			if($this->next_step["catalog"])
				fwrite($this->fp, "\t\t\t<".GetMessage("IBLOCK_XML2_OFFER").">\n");
			else
				fwrite($this->fp, "\t\t\t<".GetMessage("IBLOCK_XML2_POSITION").">\n");

			$this->exportElement($arElement, $SECTION_MAP, $PROPERTY_MAP);

			if($this->next_step["catalog"])
				fwrite($this->fp, "\t\t\t</".GetMessage("IBLOCK_XML2_OFFER").">\n");
			else
				fwrite($this->fp, "\t\t\t</".GetMessage("IBLOCK_XML2_POSITION").">\n");

			$this->next_step["LAST_ID"] = $arElement["ID"];
			$counter++;
			if($INTERVAL > 0 && (time()-$start_time) > $INTERVAL)
				break;
			if($counter_limit > 0 && ($counter >= $counter_limit))
				break;
		}
		return $counter;
	}

	function EndExportCatalog()
	{
		if($this->next_step["catalog"])
		{
			fwrite($this->fp, "\t\t</".GetMessage("IBLOCK_XML2_OFFERS").">\n");
			fwrite($this->fp, "\t</".GetMessage("IBLOCK_XML2_OFFER_LIST").">\n");
		}
		else
		{
			fwrite($this->fp, "\t\t</".GetMessage("IBLOCK_XML2_POSITIONS").">\n");
			fwrite($this->fp, "\t</".GetMessage("IBLOCK_XML2_CATALOG").">\n");
		}
	}

	function ExportProductSet($elementId, $elementXml)
	{
		$arSetItems = CCatalogProductSet::getAllSetsByProduct($elementId, CCatalogProductSet::TYPE_GROUP);
		if (is_array($arSetItems) && !empty($arSetItems))
		{
			fwrite($this->fp, "\t\t<".GetMessage("IBLOCK_XML2_PRODUCT_SETS").">\n");
			fwrite($this->fp, "\t\t\t<".GetMessage("IBLOCK_XML2_ID").">".htmlspecialcharsbx($elementXml)."</".GetMessage("IBLOCK_XML2_ID").">\n");
			foreach ($arSetItems as $arOneSet)
			{
				fwrite($this->fp, "\t\t\t<".GetMessage("IBLOCK_XML2_PRODUCT_SET").">\n");
				if (is_array($arOneSet["ITEMS"]) && !empty($arOneSet["ITEMS"]))
				{
					foreach ($arOneSet["ITEMS"] as $setItem)
					{
						$xmlId = $this->GetElementXML_ID($this->arIBlock["ID"], $setItem["ITEM_ID"]);
						if ($xmlId !== false)
						{
							fwrite($this->fp, "\t\t\t\t<".GetMessage("IBLOCK_XML2_PRODUCT_SET_ITEM").">\n");
							fwrite($this->fp, "\t\t\t\t\t<".GetMessage("IBLOCK_XML2_VALUE").">".htmlspecialcharsbx($xmlId)."</".GetMessage("IBLOCK_XML2_VALUE").">\n");
							fwrite($this->fp, "\t\t\t\t\t<".GetMessage("IBLOCK_XML2_AMOUNT").">".intval($setItem["QUANTITY"])."</".GetMessage("IBLOCK_XML2_AMOUNT").">\n");
							fwrite($this->fp, "\t\t\t\t\t<".GetMessage("IBLOCK_XML2_SORT").">".intval($setItem["SORT"])."</".GetMessage("IBLOCK_XML2_SORT").">\n");
							fwrite($this->fp, "\t\t\t\t</".GetMessage("IBLOCK_XML2_PRODUCT_SET_ITEM").">\n");
						}
					}
				}
				fwrite($this->fp, "\t\t\t</".GetMessage("IBLOCK_XML2_PRODUCT_SET").">\n");
			}
			fwrite($this->fp, "\t\t</".GetMessage("IBLOCK_XML2_PRODUCT_SETS").">\n");
		}
	}

	function ExportProductSets()
	{
		if ($this->next_step["catalog"] && $this->bExtended)
		{
			unset($this->next_step["FILTER"][">ID"]);
			$rsElements = CIBlockElement::GetList(array(), $this->next_step["FILTER"], false, false, array("ID", "XML_ID"));

			fwrite($this->fp, "\t<".GetMessage("IBLOCK_XML2_PRODUCTS_SETS").">\n");
			while($arElement = $rsElements->Fetch())
			{
				if (CCatalogProductSet::isProductHaveSet($arElement["ID"], CCatalogProductSet::TYPE_GROUP))
				{
					if($arElement["XML_ID"] <> '')
						$xml_id = $arElement["XML_ID"];
					else
						$xml_id = $arElement["ID"];

					$this->ExportProductSet($arElement["ID"], $xml_id);
				}
			}
			fwrite($this->fp, "\t</".GetMessage("IBLOCK_XML2_PRODUCTS_SETS").">\n");
		}

	}

	function EndExport()
	{
		fwrite($this->fp, "</".GetMessage("IBLOCK_XML2_COMMERCE_INFO").">\n");
	}
}
/*
GetMessage("IBLOCK_XML2_COEFF")
GetMessage("IBLOCK_XML2_OWNER")
GetMessage("IBLOCK_XML2_TITLE")
GetMessage("IBLOCK_XML2_VALUES_TYPE")
GetMessage("IBLOCK_XML2_VIEW")
*/

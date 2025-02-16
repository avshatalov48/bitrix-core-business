<?
class CIBlockResult extends CDBResult
{
	/** @var bool|array */
	var $arIBlockMultProps=false;
	/** @var bool|array */
	var $arIBlockConvProps=false;
	/** @var bool|array */
	var $arIBlockAllProps =false;
	/** @var bool|array */
	var $arIBlockNumProps =false;
	/** @var bool|array */
	var $arIBlockLongProps = false;

	var $nInitialSize;
	var $table_id;
	var $strDetailUrl = false;
	var $strSectionUrl = false;
	var $strListUrl = false;
	var $arSectionContext = false;
	var $bIBlockSection = false;
	var $nameTemplate = "";

	var $_LAST_IBLOCK_ID = "";
	var $_FILTER_IBLOCK_ID = array();

	public function __construct($res = null)
	{
		parent::__construct($res);
	}

	function SetUrlTemplates($DetailUrl = "", $SectionUrl = "", $ListUrl = "")
	{
		$this->strDetailUrl = $DetailUrl;
		$this->strSectionUrl = $SectionUrl;
		$this->strListUrl = $ListUrl;
	}

	function SetSectionContext($arSection)
	{
		if (is_array($arSection) && array_key_exists("ID", $arSection))
		{
			$code = '';
			if (isset($arSection['~CODE']))
			{
				$code = $arSection['~CODE'];
			}
			elseif (isset($arSection['CODE']))
			{
				$code = $arSection['CODE'];
			}

			$this->arSectionContext = array(
				"ID" => (int)$arSection["ID"] > 0 ? (int)$arSection["ID"] : "",
				"CODE" => urlencode($code),
				"IBLOCK_ID" => intval($arSection["IBLOCK_ID"] ?? 0),
			);
		}
		else
		{
			$this->arSectionContext = false;
		}
	}

	function SetIBlockTag($iblock_id)
	{
		if(is_array($iblock_id))
		{
			foreach($iblock_id as $id)
				$this->SetIBlockTag($id);
		}
		else
		{
			$id = intval($iblock_id);
			if ($id > 0)
				$this->_FILTER_IBLOCK_ID[$id] = true;
		}
	}

	function SetNameTemplate($nameTemplate)
	{
		$this->nameTemplate = $nameTemplate;
	}

	function Fetch()
	{
		/** @global CDatabase $DB */
		global $DB;
		$res = parent::Fetch();

		if(!isset($this) || !is_object($this))
			return $res;

		if($res)
		{
			$arUpdate = array();
			if(!empty($this->arIBlockLongProps) && is_array($this->arIBlockLongProps))
			{
				foreach($res as $k=>$v)
				{
					if(preg_match("#^ALIAS_(\\d+)_(.*)$#", $k, $match))
					{
						$res[$this->arIBlockLongProps[$match[1]].$match[2]] = $v;
						unset($res[$k]);
					}
				}
			}

			if(
				isset($res["IBLOCK_ID"])
				&& $res["IBLOCK_ID"] != $this->_LAST_IBLOCK_ID
				&& defined("BX_COMP_MANAGED_CACHE")
			)
			{
				CIBlock::registerWithTagCache($res["IBLOCK_ID"]);
				$this->_LAST_IBLOCK_ID = $res["IBLOCK_ID"];
			}

			if(isset($res["ID"]) && $res["ID"] != "" && !empty($this->arIBlockMultProps) && is_array($this->arIBlockMultProps))
			{
				foreach($this->arIBlockMultProps as $field_name => $db_prop)
				{
					if(array_key_exists($field_name, $res))
					{
						if(is_object($res[$field_name]))
							$res[$field_name]=$res[$field_name]->load();

						if(preg_match("/(_VALUE)$/", $field_name))
						{
							$descr_name = preg_replace("/(_VALUE)$/", "_DESCRIPTION", $field_name);
							$value_id_name = preg_replace("/(_VALUE)$/", "_PROPERTY_VALUE_ID", $field_name);;
						}
						else
						{
							$descr_name = preg_replace("/^(PROPERTY_)/", "DESCRIPTION_", $field_name);
							$value_id_name = preg_replace("/^(PROPERTY_)/", "PROPERTY_VALUE_ID_", $field_name);
						}

						$update = false;
						if ($res[$field_name] == '')
						{
							$update = true;
						}
						else
						{
							$tmp = unserialize($res[$field_name], ['allowed_classes' => false]);
							if (!isset($tmp['ID']))
								$update = true;
						}
						if ($update)
						{
							$strSql = "
								SELECT ID, VALUE, DESCRIPTION
								FROM b_iblock_element_prop_m".$db_prop["IBLOCK_ID"]."
								WHERE
									IBLOCK_ELEMENT_ID = ".intval($res["ID"])."
									AND IBLOCK_PROPERTY_ID = ".intval($db_prop["ORIG_ID"])."
								ORDER BY ID
							";
							$rs = $DB->Query($strSql);
							$res[$field_name] = array();
							$res[$descr_name] = array();
							$res[$value_id_name] = array();
							while($ar=$rs->Fetch())
							{
								$res[$field_name][]=$ar["VALUE"];
								$res[$descr_name][]=$ar["DESCRIPTION"];
								$res[$value_id_name][] = $ar['ID'];
							}
							$arUpdate["b_iblock_element_prop_s".$db_prop["IBLOCK_ID"]]["PROPERTY_".$db_prop["ORIG_ID"]] = serialize(array("VALUE"=>$res[$field_name],"DESCRIPTION"=>$res[$descr_name],"ID"=>$res[$value_id_name]));
						}
						else
						{
							$res[$field_name] = $tmp["VALUE"];
							$res[$descr_name] = $tmp["DESCRIPTION"];
							$res[$value_id_name] = $tmp["ID"];
						}

						if(is_array($res[$field_name]) && $db_prop["PROPERTY_TYPE"]=="L")
						{
							$arTemp = array();
							foreach($res[$field_name] as $key=>$val)
							{
								$arEnum = CIBlockPropertyEnum::GetByID($val);
								if($arEnum!==false)
									$arTemp[$val] = $arEnum["VALUE"];
							}
							$res[$field_name] = $arTemp;
						}
					}
				}

				if (!empty($arUpdate))
				{
					$pool = \Bitrix\Main\Application::getInstance()->getConnectionPool();
					$pool->useMasterOnly(true);
					foreach ($arUpdate as $strTable => $arFields)
					{
						$strUpdate = $DB->PrepareUpdate($strTable, $arFields);
						if ($strUpdate != "")
						{
							$strSql = "UPDATE ".$strTable." SET ".$strUpdate." WHERE IBLOCK_ELEMENT_ID = ".intval($res["ID"]);
							$DB->QueryBind($strSql, $arFields);
						}
					}
					$pool->useMasterOnly(false);
					unset($pool);
				}
			}
			if(!empty($this->arIBlockConvProps) && is_array($this->arIBlockConvProps))
			{
				foreach($this->arIBlockConvProps as $strFieldName=>$arCallback)
				{
					if(is_array($res[$strFieldName]))
					{
						foreach($res[$strFieldName] as $key=>$value)
						{
							$arValue = call_user_func_array(
								$arCallback['ConvertFromDB'],
								[
									$arCallback['PROPERTY'],
									[
										'VALUE' => $value,
										'DESCRIPTION' => '',
									]
								]
							);
							$res[$strFieldName][$key] = $arValue['VALUE'] ?? null;
						}
					}
					else
					{
						$arValue = call_user_func_array(
							$arCallback['ConvertFromDB'],
							[
								$arCallback['PROPERTY'],
								[
									'VALUE' => $res[$strFieldName],
									'DESCRIPTION' => '',
								]
							]
						);
						$res[$strFieldName] = $arValue['VALUE'] ?? null;
					}
				}
			}
			if(!empty($this->arIBlockNumProps) && is_array($this->arIBlockNumProps))
			{
				foreach($this->arIBlockNumProps as $field_name => $db_prop)
				{
					if($res[$field_name] <> '')
						$res[$field_name] = htmlspecialcharsex(CIBlock::NumberFormat($res[$field_name]));
				}
			}
			if (isset($res["UC_ID"]))
			{
				$res["CREATED_BY_FORMATTED"] = CUser::FormatName($this->nameTemplate, array(
					"NAME" => $res["UC_NAME"],
					"LAST_NAME" => $res["UC_LAST_NAME"],
					"SECOND_NAME" => $res["UC_SECOND_NAME"],
					"EMAIL" => $res["UC_EMAIL"],
					"ID" => $res["UC_ID"],
					"LOGIN" => $res["UC_LOGIN"],
				), true, false);
				unset($res["UC_NAME"]);
				unset($res["UC_LAST_NAME"]);
				unset($res["UC_SECOND_NAME"]);
				unset($res["UC_EMAIL"]);
				unset($res["UC_ID"]);
				unset($res["UC_LOGIN"]);
			}
			unset($arUpdate);
		}
		elseif(
			defined("BX_COMP_MANAGED_CACHE")
			&& $this->_LAST_IBLOCK_ID == ""
			&& count($this->_FILTER_IBLOCK_ID)
		)
		{
			foreach($this->_FILTER_IBLOCK_ID as $iblock_id => $t)
				CIBlock::registerWithTagCache($iblock_id);
		}

		return $res;
	}

	function GetNext($bTextHtmlAuto=true, $use_tilda=true)
	{
		static $arSectionPathCache = array();

		$res = parent::GetNext($bTextHtmlAuto, $use_tilda);
		if($res)
		{
			//Handle List URL for Element, Section or IBlock
			if($this->strListUrl)
				$TEMPLATE = $this->strListUrl;
			elseif(array_key_exists("~LIST_PAGE_URL", $res))
				$TEMPLATE = $res["~LIST_PAGE_URL"];
			elseif(!$use_tilda && array_key_exists("LIST_PAGE_URL", $res))
				$TEMPLATE = $res["LIST_PAGE_URL"];
			else
				$TEMPLATE = "";

			if($TEMPLATE)
			{
				$res_tmp = $res;
				if((intval(($res["IBLOCK_ID"] ?? 0)) <= 0) && (intval($res["ID"]) > 0))
				{
					$res_tmp["IBLOCK_ID"] = $res["ID"];
					$res_tmp["IBLOCK_CODE"] = $res["CODE"];
					$res_tmp["IBLOCK_EXTERNAL_ID"] = $res["EXTERNAL_ID"];
					if($use_tilda)
					{
						$res_tmp["~IBLOCK_ID"] = $res["~ID"];
						$res_tmp["~IBLOCK_CODE"] = $res["~CODE"];
						$res_tmp["~IBLOCK_EXTERNAL_ID"] = $res["~EXTERNAL_ID"];
					}
				}

				if($use_tilda)
				{
					$res["~LIST_PAGE_URL"] = CIBlock::ReplaceDetailUrl($TEMPLATE, $res_tmp, true, false);
					$res["LIST_PAGE_URL"] = htmlspecialcharsbx($res["~LIST_PAGE_URL"]);
				}
				else
				{
					$res["LIST_PAGE_URL"] = CIBlock::ReplaceDetailUrl($TEMPLATE, $res_tmp, true, false);
				}
			}

			//If this is Element or Section then process it's detail and section URLs
			if(($res["IBLOCK_ID"] ?? '') <> '')
			{

				if(array_key_exists("GLOBAL_ACTIVE", $res))
				{
					$type = "S";
				}
				else
				{
					$type = "E";
				}

				if($this->strDetailUrl)
				{
					$TEMPLATE = $this->strDetailUrl;
				}
				elseif(array_key_exists("~DETAIL_PAGE_URL", $res))
				{
					$TEMPLATE = $res["~DETAIL_PAGE_URL"];
				}
				elseif(!$use_tilda && array_key_exists("DETAIL_PAGE_URL", $res))
				{
					$TEMPLATE = $res["DETAIL_PAGE_URL"];
				}
				else
				{
					$TEMPLATE = "";
				}

				if($TEMPLATE)
				{
					if($this->arSectionContext)
					{
						$TEMPLATE = str_replace("#SECTION_ID#", $this->arSectionContext["ID"], $TEMPLATE);
						$TEMPLATE = str_replace("#SECTION_CODE#", $this->arSectionContext["CODE"], $TEMPLATE);
						if(
							$this->arSectionContext["ID"] > 0
							&& $this->arSectionContext["IBLOCK_ID"] > 0
							&& mb_strpos($TEMPLATE, "#SECTION_CODE_PATH#") !== false
						)
						{
							if(!isset($arSectionPathCache[$this->arSectionContext["ID"]]))
							{
								$rs = CIBlockSection::GetNavChain(
									$this->arSectionContext["IBLOCK_ID"],
									$this->arSectionContext["ID"],
									array("ID", "IBLOCK_SECTION_ID", "CODE"),
									true
								);
								if (!empty($rs))
								{
									$arSectionPathCache[$this->arSectionContext["ID"]] = '';
									foreach ($rs as $a)
									{
										$arSectionPathCache[$this->arSectionContext["ID"]] .= rawurlencode($a["CODE"])."/";
									}
									unset($a);
								}
								unset($rs);
							}
							if(isset($arSectionPathCache[$this->arSectionContext["ID"]]))
							{
								$SECTION_CODE_PATH = rtrim($arSectionPathCache[$this->arSectionContext["ID"]], "/");
							}
							else
							{
								$SECTION_CODE_PATH = "";
							}
							$TEMPLATE = str_replace("#SECTION_CODE_PATH#", $SECTION_CODE_PATH, $TEMPLATE);
						}
					}

					if($use_tilda)
					{
						$res["~DETAIL_PAGE_URL"] = CIBlock::ReplaceDetailUrl($TEMPLATE, $res, true, $type);
						$res["DETAIL_PAGE_URL"] = htmlspecialcharsbx($res["~DETAIL_PAGE_URL"]);
					}
					else
					{
						$res["DETAIL_PAGE_URL"] = CIBlock::ReplaceDetailUrl($TEMPLATE, $res, true, $type);
					}
				}

				if($this->strSectionUrl)
				{
					$TEMPLATE = $this->strSectionUrl;
				}
				elseif(array_key_exists("~SECTION_PAGE_URL", $res))
				{
					$TEMPLATE = $res["~SECTION_PAGE_URL"];
				}
				elseif(!$use_tilda && array_key_exists("SECTION_PAGE_URL", $res))
				{
					$TEMPLATE = $res["SECTION_PAGE_URL"];
				}
				else
				{
					$TEMPLATE = "";
				}

				if($TEMPLATE)
				{
					if($use_tilda)
					{
						$res["~SECTION_PAGE_URL"] = CIBlock::ReplaceSectionUrl($TEMPLATE, $res, true, $type);
						$res["SECTION_PAGE_URL"] = htmlspecialcharsbx($res["~SECTION_PAGE_URL"]);
					}
					else
					{
						$res["SECTION_PAGE_URL"] = CIBlock::ReplaceSectionUrl($TEMPLATE, $res, true, $type);
					}
				}
			}

			if(array_key_exists("~CANONICAL_PAGE_URL", $res))
				$TEMPLATE = $res["~CANONICAL_PAGE_URL"];
			elseif(!$use_tilda && array_key_exists("CANONICAL_PAGE_URL", $res))
				$TEMPLATE = $res["CANONICAL_PAGE_URL"];
			else
				$TEMPLATE = "";

			if($TEMPLATE)
			{
				if($use_tilda)
				{
					$res["~CANONICAL_PAGE_URL"] = CIBlock::ReplaceDetailUrl($TEMPLATE, $res, true, "E");
					$res["CANONICAL_PAGE_URL"] = htmlspecialcharsbx($res["~CANONICAL_PAGE_URL"]);
				}
				else
				{
					$res["CANONICAL_PAGE_URL"] = CIBlock::ReplaceDetailUrl($TEMPLATE, $res, true, "E");
				}
			}
		}
		return $res;
	}

	function GetNextElement($bTextHtmlAuto=true, $use_tilda=true)
	{
		if(!($r = $this->GetNext($bTextHtmlAuto, $use_tilda)))
			return $r;

		$res = new _CIBElement;
		$res->fields = $r;
		if(!empty($this->arIBlockAllProps) && is_array($this->arIBlockAllProps))
			$res->props = $this->arIBlockAllProps;
		return $res;
	}

	function SetTableID($table_id)
	{
		$this->table_id = $table_id;
	}

	function NavStart($nPageSize=20, $bShowAll=true, $iNumPage=false)
	{
		if($this->table_id)
		{
			if ($_REQUEST["mode"] == "excel")
				return;
			$navResult = new CAdminResult(null, '');
			$nSize = $navResult->GetNavSize($this->table_id, $nPageSize);
			unset($navResult);
			if(is_array($nPageSize))
			{
				$this->nInitialSize = $nPageSize["nPageSize"];
				$nPageSize["nPageSize"] = $nSize;
			}
			else
			{
				$this->nInitialSize = $nPageSize;
				$nPageSize = $nSize;
			}
		}
		parent::NavStart($nPageSize, $bShowAll, $iNumPage);
	}

	function GetNavPrint($title, $show_allways=true, $StyleText="", $template_path=false, $arDeleteParam=false)
	{
		if($this->table_id && ($template_path === false))
			$template_path = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/interface/navigation.php";
		return parent::GetNavPrint($title, $show_allways, $StyleText, $template_path, $arDeleteParam);
	}
}
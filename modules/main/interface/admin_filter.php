<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2016 Bitrix
 */

class CAdminFilter
{
	public 	$id;
	private $popup;
	private $arItems = array();
	private $arOptFlt = array();
	private static $defaultSort = 100;
	private static $defaultPresetSort = 50;
	private $url=false;
	private $tableId=false;

	const SESS_PARAMS_NAME = "main.adminFilter";

	public function __construct($id, $popup=false, $arExtraParams=array())
	{
		global $USER;

		$uid = $USER->GetID();
		$isAdmin = $USER->CanDoOperation('edit_other_settings');

		if(empty($popup) || !is_array($popup))
			$popup = false;

		$this->id = $id;
		$this->popup = $popup;

		if(is_array($arExtraParams))
		{
			if(isset($arExtraParams["url"]) && !empty($arExtraParams["url"]))
				$this->url = $arExtraParams["url"];

			if(isset($arExtraParams["table_id"]) && !empty($arExtraParams["table_id"]))
				$this->tableId = $arExtraParams["table_id"];
		}

		$this->arOptFlt = CUserOptions::GetOption("filter", $this->id, array(
			"rows" => "",
			"styleFolded" => "N",
			"presetsDeleted" => ""
		));

		$presetsDeleted = explode(",", $this->arOptFlt["presetsDeleted"]);

		$this->arOptFlt["presetsDeleted"] = $presetsDeleted ? $presetsDeleted : array();

		$presetsDeletedJS='';

		if(is_array($presetsDeleted))
			foreach($presetsDeleted as $preset)
				if(trim($preset) <> "")
					$presetsDeletedJS .= ($presetsDeletedJS <> "" ? ",":"").'"'.CUtil::JSEscape(trim($preset)).'"';

		$this->arOptFlt["presetsDeletedJS"] = $presetsDeletedJS;

		$dbRes = self::GetList(array(), array("USER_ID" => $uid, "FILTER_ID" => $this->id), true);
		while($arFilter = $dbRes->Fetch())
		{
			if(!is_null($arFilter["LANGUAGE_ID"]) && $arFilter["LANGUAGE_ID"] != LANG )
				continue;

			$arItem = $arFilter;
			$arItem["FIELDS"] = unserialize($arFilter["FIELDS"]);

			if(!is_null($arFilter["SORT_FIELD"]))
				$arItem["SORT_FIELD"] = unserialize($arFilter["SORT_FIELD"]);

			if($arFilter["PRESET"] == "Y" && is_null($arFilter["LANGUAGE_ID"]))
			{
				$langName = GetMessage($arFilter["NAME"]);

				if($langName)
						$arItem["NAME"] = $langName;

				foreach ($arItem["FIELDS"] as $key => $field)
				{
					$langValue = GetMessage($arItem["FIELDS"][$key]["value"]);

					if($langValue)
						$arItem["FIELDS"][$key]["value"] = $langValue;
				}
			}

			$arItem["EDITABLE"] = ((($isAdmin || $arFilter["USER_ID"] == $uid ) && $arFilter["PRESET"] != "Y") ? true : false );

			$this->AddItem($arItem);
		}
	}

	private function err_mess()
	{
		return "<br>Class: CAdminFilter<br>File: ".__FILE__;
	}

	private function AddItem($arItem, $bInsertFirst = false)
	{
		//if user "deleted" preset http://jabber.bx/view.php?id=34405
		if(!$arItem["EDITABLE"] && !empty($this->arOptFlt["presetsDeleted"]))
			if(in_array($arItem["ID"], $this->arOptFlt["presetsDeleted"]))
				return false;

		$customPresetId = $this->FindItemByPresetId($arItem["ID"]);

		if($customPresetId)
		{
			$this->arItems[$customPresetId]["SORT"] = $arItem["SORT"];
			return false;
		}

		if(isset($arItem["PRESET_ID"]))
		{
			$presetID = $this->FindItemByID($arItem["PRESET_ID"]);

			if($presetID)
			{
				$arItem["SORT"] = $this->arItems[$presetID]["SORT"];
				unset($this->arItems[$presetID]);
			}

		}

		if(!isset($arItem["SORT"]))
			$arItem["SORT"] = self::$defaultSort;

		if($bInsertFirst)
		{
			$arNewItems[$arItem["ID"]] = $arItem;

			foreach ($this->arItems as $key => $item)
				$arNewItems[$key] = $item;

			$this->arItems = $arNewItems;
		}
		else
			$this->arItems[$arItem["ID"]] = $arItem;

		unset($this->arItems[$arItem["ID"]][$arItem["ID"]]);

		return true;
	}

	private function CheckFields($arFields)
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;

		$aMsg = array();

		if(!is_set($arFields, "FILTER_ID") || (is_set($arFields, "FILTER_ID") && trim($arFields["FILTER_ID"])==""))
			$aMsg[] = array("id"=>"FILTER_ID", "text"=>GetMessage("filters_error_table_name"));

		if(!is_set($arFields, "NAME") || (is_set($arFields, "NAME") && trim($arFields["NAME"])==""))
			$aMsg[] = array("id"=>"NAME", "text"=>GetMessage("filters_error_name"));

		if(!is_set($arFields, "FIELDS") || (is_set($arFields, "FIELDS") && trim($arFields["FIELDS"])==""))
			$aMsg[] = array("id"=>"FIELDS", "text"=>GetMessage("filters_error_fields"));

		if((!is_set($arFields, "USER_ID") && $arFields["COMMON"] != "Y") || (is_set($arFields, "USER_ID") && trim($arFields["USER_ID"])==""))
			$aMsg[] = array("id"=>"USER_ID", "text"=>GetMessage("filters_error_user"));

		if(is_set($arFields, "USER_ID"))
		{
			if(intval($arFields["USER_ID"]) > 0)
			{
				$res = CUser::GetByID(intval($arFields["USER_ID"]));
				if(!$res->Fetch())
					$aMsg[] = array("id"=>"USER_ID", "text"=>GetMessage("filters_error_user"));
			}
		}

		if(!empty($aMsg))
		{
			$e = new CAdminException($aMsg);
			$APPLICATION->ThrowException($e);
			return false;
		}

		return true;
	}

	private function FieldsExcess($arFields)
	{
		$arResult = array();

		if(is_array($arFields))
			foreach ($arFields as $key => $field)
					$arResult[$key] = array(
										"value" => $field,
										"hidden" => "false",
										);
		return $arResult;
	}

	private function FieldsDelHiddenEmpty($arFields)
	{
		$arResult = array();

		if(!is_array($arFields))
			return false;

		foreach ($arFields as $key => $field)
		{
				if(!empty($field["value"]) || $field["hidden"] == "false")
					$arResult[$key] = array(
										"value" => $field["value"],
										"hidden" => $field["hidden"],
										);
		}
		return $arResult;
	}

	/**
	 * Sets default rows, witch will be shown to user when he comes to page for the first time/
	 * This function must be called on admin page with the filter initialization.
	 * For example: $oFilter->SetDefaultRows("find_created, find_menu_id");
	 *
	 * @param str rows - rows identificators separated by commas ("rowid_1, rowid_2, ...")
	 * @return bool
	 */
	public function SetDefaultRows($rows)
	{
		if(is_array($rows))
			$outRows = implode(",",$rows);
		else
			$outRows = $rows;

		if(!$outRows)
			return false;

		if(!empty($this->arOptFlt["rows"]))
			return true;

		$this->arOptFlt["rows"] = $outRows;

		return true;
	}

	public static function SetDefaultRowsOption($filterId, $rows)
	{
		if(!$filterId)
			return false;

		if(is_array($rows))
			$outRows = implode(",",$rows);
		else
			$outRows = $rows;

		if(!$outRows)
			return false;

		return CUserOptions::SetOption("filter", $filterId, array("rows" => $outRows),true);
	}

	/**
	 * Sets new filter tab with collection of fields and values
	 * This function must be called on admin page with the filter initialization.
	 * For example: $oFilter->AddPreset(array(
	 *									"ID" => "preset1",
	 *									"NAME" => "Test filter",
	 *									"SORT" => 100,
	 *									"SORT_FIELD" => array ("name" => "asc"),
	 *									"FIELDS" => array(
	 *										"find_name"=>"Smith",
	 *										"find_id"=>"15"
	 *										)
	 *									));
	 *
	 * @param array $arFields = array(
	 *								"ID" =>  filter id,
	 *								"NAME" => filter name,
	 *								"SORT" = > filter sorting order. Default value - 100, for presets - 50;
	 *								"SORT_FIELD" => array("Table column name" => "sort order"),
	 *								"FIELDS" => array(
	 *											"field1_name" => "field1_value",
	 *											"field2_name" => "field2_value",
	 *											...
	 * 												)
	 *							)
	 * @return bool
	 */
	public function AddPreset($arFields)
	{
		if(!isset($arFields["NAME"]) || empty($arFields["NAME"]))
			return false;

		if(!isset($arFields["ID"]) || empty($arFields["ID"]))
			return false;

		$item = array(
			"ID" => "page-".$arFields["ID"],
			"FILTER_ID" => $this->id,
			"NAME" => $arFields["NAME"],
			"EDITABLE" => false,
			"PRESET" => "Y"
			);

		if(isset($arFields["FIELDS"]))
			$item["FIELDS"] = CAdminFilter::FieldsExcess($arFields["FIELDS"]);
		else
			$item["FIELDS"] = array();

		if(isset($arFields["SORT"]) && !empty($arFields["SORT"]))
			$item["SORT"] = intval($arFields["SORT"]);
		else
			$item["SORT"] =self::$defaultPresetSort+count($this->arItems)*10;

		if(isset($arFields["SORT_FIELD"]) && is_array($arFields["SORT_FIELD"]) && !empty($arFields["SORT_FIELD"]))
			$item["SORT_FIELD"] = $arFields["SORT_FIELD"];

		return $this->AddItem($item, false);
	}


	private function FindItemByPresetId($strID)
	{

		if(!is_array($this->arItems))
			return false;

		foreach ($this->arItems as $key => $item)
			if($item["PRESET_ID"] == $strID)
				return $key;

		return false;
	}

	private function FindItemByID($strID)
	{
		if(!is_array($this->arItems))
			return false;

		foreach ($this->arItems as $key => $item)
			if($item["ID"] == $strID)
				return $key;

		return false;
	}

	public function AddPresetToBase($arFields)
	{
		if(!isset($arFields["NAME"]) || empty($arFields["NAME"]))
			return false;

		$arFields["PRESET"] = "Y";
		$arFields["COMMON"] = "Y";

		if(isset($arFields["FIELDS"]))
			$arFields["FIELDS"] = CAdminFilter::FieldsExcess($arFields["FIELDS"]);
		else
			$item["FIELDS"] = array();


		if(!isset($arFields["SORT"]) || empty($arFields["SORT"]))
			$arFields["SORT"] = self::$defaultPresetSort;

		return CAdminFilter::Add($arFields);
	}

	public static function Add($arFields)
	{
		global $DB;

		$arFields["FIELDS"] = CAdminFilter::FieldsDelHiddenEmpty($arFields["FIELDS"]);

		if(!$arFields["FIELDS"])
			return false;

		$arFields["FIELDS"] = serialize($arFields["FIELDS"]);

		if(isset($arFields["SORT_FIELD"]))
			$arFields["SORT_FIELD"] = serialize($arFields["SORT_FIELD"]);

		if(!CAdminFilter::CheckFields($arFields))
			return false;

		$ID = $DB->Add("b_filters", $arFields, array("FIELDS"));
		return $ID;
	}

	public static function Delete($ID)
	{
		global $DB;

		return ($DB->Query("DELETE FROM b_filters WHERE ID='".intval($ID)."'", false, "File: ".__FILE__."<br>Line: ".__LINE__));
	}

	public static function Update($ID, $arFields)
	{
		global $DB;
		$ID = intval($ID);

		$arFields["FIELDS"] = CAdminFilter::FieldsDelHiddenEmpty($arFields["FIELDS"]);

		if(!$arFields["FIELDS"])
			return false;

		$arFields["FIELDS"] = serialize($arFields["FIELDS"]);

		if(isset($arFields["SORT_FIELD"]))
			$arFields["SORT_FIELD"] = serialize($arFields["SORT_FIELD"]);

		if(!CAdminFilter::CheckFields($arFields))
			return false;

		$strUpdate = $DB->PrepareUpdate("b_filters", $arFields);

		$arBinds=Array();
		if(is_set($arFields, "FIELDS"))
			$arBinds["FIELDS"] = $arFields["FIELDS"];


		if(strlen($strUpdate) > 0)
		{
			$strSql = "UPDATE b_filters SET ".$strUpdate." WHERE ID=".$ID;
			return $DB->QueryBind($strSql, $arBinds);

			//if(!$DB->Query($strSql))
			//	return false;
		}

		return false;
	}

	public static function GetList($aSort=array(), $arFilter=Array(), $getCommon=true)
	{
		global $DB;

		$err_mess = (CAdminFilter::err_mess())."<br>Function: GetList<br>Line: ";
		$arSqlSearch = Array();
		if (is_array($arFilter))
		{
			foreach ($arFilter as $key => $val)
			{
				if (strlen($val)<=0 || $val=="NOT_REF")
					continue;

				switch(strtoupper($key))
				{
				case "ID":
					$arSqlSearch[] = GetFilterQuery("F.ID",$val,"N");
					break;
				case "USER_ID":
					if($getCommon)
						$arSqlSearch[] = "F.USER_ID=".intval($val)." OR F.COMMON='Y'";
					else
						$arSqlSearch[] = "F.USER_ID = ".intval($val);
					break;
				case "FILTER_ID":
					$arSqlSearch[] = "F.FILTER_ID = '".$DB->ForSql($val)."'";
					break;
				case "NAME":
					$arSqlSearch[] = GetFilterQuery("F.NAME", $val);
					break;
				case "FIELDS":
					$arSqlSearch[] = GetFilterQuery("F.FIELDS", $val);
					break;
				case "COMMON":
					$arSqlSearch[] = "F.COMMON = '".$DB->ForSql($val,1)."'";
					break;
				case "PRESET":
					$arSqlSearch[] = "F.PRESET = '".$DB->ForSql($val,1)."'";
					break;
				case "LANGUAGE_ID":
					$arSqlSearch[] = "F.LANGUAGE_ID = '".$DB->ForSql($val,2)."'";
					break;
				case "PRESET_ID":
					$arSqlSearch[] = GetFilterQuery("F.PRESET_ID", $val);
					break;
				case "SORT":
					$arSqlSearch[] = GetFilterQuery("F.SORT", $val);
					break;
				case "SORT_FIELD":
					$arSqlSearch[] = GetFilterQuery("F.SORT_FIELD", $val);
					break;
				}
			}
		}

		$sOrder = "";
		foreach($aSort as $key=>$val)
		{
			$ord = (strtoupper($val) <> "ASC"? "DESC":"ASC");
			switch (strtoupper($key))
			{
				case "ID":		$sOrder .= ", F.ID ".$ord; break;
				case "USER_ID":	$sOrder .= ", F.USER_ID ".$ord; break;
				case "FILTER_ID":	$sOrder .= ", F.FILTER_ID ".$ord; break;
				case "NAME":	$sOrder .= ", F.NAME ".$ord; break;
				case "FIELDS":	$sOrder .= ", F.FIELDS ".$ord; break;
				case "COMMON":	$sOrder .= ", F.COMMON ".$ord; break;
				case "PRESET":	$sOrder .= ", F.PRESET ".$ord; break;
				case "LANGUAGE_ID":	$sOrder .= ", F.LANGUAGE_ID ".$ord; break;
				case "PRESET_ID":	$sOrder .= ", F.PRESET_ID ".$ord; break;
				case "SORT":	$sOrder .= ", F.SORT ".$ord; break;
				case "SORT_FIELD":	$sOrder .= ", F.SORT_FIELD ".$ord; break;
			}
		}
		if (strlen($sOrder)<=0)
			$sOrder = "F.ID ASC";
		$strSqlOrder = " ORDER BY ".TrimEx($sOrder,",");

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch,"noFilterLogic");
		$strSql = "
			SELECT
				F.ID, F.USER_ID, F.NAME, F.FILTER_ID, F.FIELDS, F.COMMON, F.PRESET, F.LANGUAGE_ID, F.PRESET_ID, F.SORT, F.SORT_FIELD
			FROM
				b_filters F
			WHERE
			".$strSqlSearch."
			".$strSqlOrder;

		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		return $res;
	}

	private static function Cmp($a, $b)
	{
		if ($a["SORT"] == $b["SORT"])
			return ($a["ID"] < $b["ID"]) ? -1 : 1;

		return ($a["SORT"] < $b["SORT"]) ? -1 : 1;
	}

	public function Begin()
	{
		uasort($this->arItems, "CAdminFilter::Cmp");

		echo '
<div id="adm-filter-tab-wrap-'.$this->id.'" class="adm-filter-wrap'.($this->arOptFlt["styleFolded"]=="Y" ? " adm-filter-folded" : "").'" style = "display: none;">
	<table class="adm-filter-main-table">
		<tr>
			<td class="adm-filter-main-table-cell">
				<div class="adm-filter-tabs-block" id="filter-tabs-'.$this->id.'">
					<span id="adm-filter-tab-'.$this->id.'-0" class="adm-filter-tab adm-filter-tab-active" onclick="'.$this->id.'.SetActiveTab(this); '.$this->id.'.ApplyFilter(\'0\'); " title="'.GetMessage("admin_lib_filter_goto_dfilter").'">'.GetMessage("admin_lib_filter_filter").'</span>';

		if(is_array($this->arItems) && !empty($this->arItems))
		{
			foreach($this->arItems as $filter_id => $filter)
			{
				$name = ($filter["NAME"] <> '' ? $filter["NAME"] : GetMessage("admin_lib_filter_no_name"));
				echo '<span id="adm-filter-tab-'.$this->id.'-'.$filter_id.'" class="adm-filter-tab" onclick="'.$this->id.'.SetActiveTab(this); '.$this->id.'.ApplyFilter(\''.$filter_id.'\');" title="'.GetMessage("admin_lib_filter_goto_filter").": &quot;".htmlspecialcharsbx($name).'&quot;">'.htmlspecialcharsbx($name).'</span>';
			}
		}

			echo '<span id="adm-filter-add-tab-'.$this->id.'" class="adm-filter-tab adm-filter-add-tab" onclick="'.$this->id.'.SaveAs();" title="'.GetMessage("admin_lib_filter_new").'"></span><span onclick="'.$this->id.'.SetFoldedView();" class="adm-filter-switcher-tab"><span id="adm-filter-switcher-tab" class="adm-filter-switcher-tab-icon"></span></span><span class="adm-filter-tabs-block-underlay"></span>
				</div>
			</td>
		</tr>
		<tr>
			<td class="adm-filter-main-table-cell">
				<div class="adm-filter-content" id="'.$this->id.'_content">
					<div class="adm-filter-content-table-wrap">
						<table cellspacing="0" class="adm-filter-content-table" id="'.$this->id.'">';
	}

	/**
	 * @param bool|array $aParams
	 */
	public function Buttons($aParams=false)
	{
		$hkInst = CHotKeys::getInstance();

		echo '

						</table>
					</div>
					<div class="adm-filter-bottom-separate" id="'.$this->id.'_bottom_separator"></div>
					<div class="adm-filter-bottom">';

		if($aParams !== false)
		{
			$url = $aParams["url"];
			if(strpos($url, "?")===false)
				$url .= "?";
			else
				$url .= "&";

			if(strpos($url, "lang=")===false)
				$url .= "lang=".LANG;

			if(!$this->url)
				$this->url = $url;

			if(!$this->tableId)
				$this->tableId = $aParams["table_id"];

			if(isset($aParams['report']) && $aParams['report'])
			{
				echo '
						<input type="submit" class="adm-btn" id="'.$this->id.'set_filter" name="set_filter" title="'.GetMessage("admin_lib_filter_set_rep_title").$hkInst->GetTitle("set_filter").'" onclick="return '.htmlspecialcharsbx($this->id.'.OnSet(\''.CUtil::AddSlashes($aParams["table_id"]).'\', \''.CUtil::AddSlashes($url).'\', this);').'" value="'.GetMessage("admin_lib_filter_set_rep").'">
						<input type="submit" class="adm-btn" id="'.$this->id.'del_filter" name="del_filter" title="'.GetMessage("admin_lib_filter_clear_butt_title").$hkInst->GetTitle("del_filter").'" onclick="return '.htmlspecialcharsbx($this->id.'.OnClear(\''.CUtil::AddSlashes($aParams["table_id"]).'\', \''.CUtil::AddSlashes($url).'\', this);').'" value="'.GetMessage("admin_lib_filter_clear_butt").'">';
			}
			else
				echo '
						<input type="submit" class="adm-btn" id="'.$this->id.'set_filter" name="set_filter" title="'.GetMessage("admin_lib_filter_set_butt").$hkInst->GetTitle("set_filter").'" onclick="return '.htmlspecialcharsbx($this->id.'.OnSet(\''.CUtil::AddSlashes($aParams["table_id"]).'\', \''.CUtil::AddSlashes($url).'\', this);').'" value="'.GetMessage("admin_lib_filter_set_butt").'">
						<input type="submit" class="adm-btn" id="'.$this->id.'del_filter" name="del_filter" title="'.GetMessage("admin_lib_filter_clear_butt").$hkInst->GetTitle("del_filter").'" onclick="return '.htmlspecialcharsbx($this->id.'.OnClear(\''.CUtil::AddSlashes($aParams["table_id"]).'\', \''.CUtil::AddSlashes($url).'\', this);').'" value="'.GetMessage("admin_lib_filter_clear_butt").'">';

		}
		if($this->popup)
		{

			echo '
						<div class="adm-filter-setting-block">
							<span class="adm-filter-setting" onClick="this.blur();'.$this->id.'.SaveMenuShow(this);return false;" hidefocus="true" title="'.GetMessage("admin_lib_filter_savedel_title").'"></span>
							<span class="adm-filter-add-button" onClick="this.blur();'.$this->id.'.SettMenuShow(this);return false;" hidefocus="true" title="'.GetMessage("admin_lib_filter_more_title").'"></span>
						</div>';
		}
	}

	public function End()
	{

		echo '
					</div>
				</div>
			</td>
		</tr>
	</table>
</div>';

		$sRowIds = $sVisRowsIds = "";


		if(is_array($this->popup))
		{
			foreach($this->popup as $key=>$item)
				if($item !== null)
					$sRowIds .= ($sRowIds <> ""? ",":"").'"'.CUtil::JSEscape($key).'"';

			$aRows = explode(",", $this->arOptFlt["rows"]);

			if(is_array($aRows))
				foreach($aRows as $row)
					if(trim($row) <> "")
						$sVisRowsIds .= ($sVisRowsIds <> ""? ",":"").'"'.CUtil::JSEscape(trim($row)).'":true';
		}

		$this->PrintSaveOptionsDIV();
		$this->GetParamsFromCookie();

		$openedTabUri = false;
		$openedTabSes = $filteredTab = null;

		if(isset($_REQUEST["adm_filter_applied"]) && !empty($_REQUEST["adm_filter_applied"]))
		{
			$openedTabUri = $_REQUEST["adm_filter_applied"];
		}
		else
		{
			$openedTabSes = $_SESSION[self::SESS_PARAMS_NAME][$this->id]["activeTabId"];
			$filteredTab = $_SESSION[self::SESS_PARAMS_NAME][$this->id]["filteredId"];
		}

		echo '
<script type="text/javascript">
	var '.$this->id.' = {};
	BX.ready(function(){
		'.$this->id.' = new BX.AdminFilter("'.$this->id.'", ['.$sRowIds.']);
		'.$this->id.'.state.init = true;
		'.$this->id.'.state.folded = '.($this->arOptFlt["styleFolded"] == "Y" ? "true" : "false").';
		'.$this->id.'.InitFilter({'.$sVisRowsIds.'});
		'.$this->id.'.oOptions = '.CUtil::PhpToJsObject($this->arItems).';
		'.$this->id.'.popupItems = '.CUtil::PhpToJsObject($this->popup).';
		'.$this->id.'.InitFirst();
		'.$this->id.'.url = "'.CUtil::JSEscape($this->url).'";
		'.$this->id.'.table_id = "'.CUtil::JSEscape($this->tableId).'";
		'.$this->id.'.presetsDeleted = ['.$this->arOptFlt["presetsDeletedJS"].'];';

		if($filteredTab != null || $openedTabUri != false)
		{
			$tabToInit = ($openedTabUri ? $openedTabUri : $filteredTab);

			echo '
		'.$this->id.'.InitFilteredTab("'.CUtil::JSEscape($tabToInit).'");';
		}

		if($openedTabSes != null || $openedTabUri != false)
			echo '
		var openedFTab = '.$this->id.'.InitOpenedTab("'.CUtil::JSEscape($openedTabUri).'", "'.CUtil::JSEscape($openedTabSes).'");';

		echo '
		'.$this->id.'.state.init = false;
		BX("adm-filter-tab-wrap-'.$this->id.'").style.display = "block";';

		//making filter tabs draggable
		if($this->url)
		{
			$registerUrl = CHTTP::urlDeleteParams($this->url, array("adm_filter_applied", "adm_filter_preset"));

			foreach($this->arItems as $filter_id => $filter)
			{
				$arParamsAdd = array("adm_filter_applied"=>$filter_id);

				if(isset($filter["PRESET_ID"]))
					$arParamsAdd["adm_filter_preset"] = $filter["PRESET_ID"];

				$filterUrl = CHTTP::urlAddParams($registerUrl, $arParamsAdd, array("encode","skip_empty"));

				echo "
		BX.adminMenu.registerItem('adm-filter-tab-".$this->id.'-'.$filter_id."', {URL:'".$filterUrl."', TITLE: true});";
			}
		}

		echo '
	});
</script>';

		$hkInst = CHotKeys::getInstance();
		$Execs = $hkInst->GetCodeByClassName("CAdminFilter");
		echo $hkInst->PrintJSExecs($Execs);
	}


	//experemental
	//extracting filter params from cookie and transfer them to session
	private function GetParamsFromCookie()
	{
		$cookieName = COption::GetOptionString("main", "cookie_name", "BITRIX_SM")."_ADM_FLT_PARAMS";
		if(!isset($_COOKIE[$cookieName]) || $_COOKIE[$cookieName] == "")
			return false;

		$aParams = explode(",",$_COOKIE[$cookieName]);
		SetCookie($cookieName,'');

		if(empty($aParams))
			return false;

		$filterId = "";

		foreach ($aParams as $key => $aValue)
		{
			$aParam = explode(":",$aValue);
			unset($aParams[$key]);

			if(!empty($aParam) && $aParam[0] != "filter_id")
				$aParams[$aParam[0]] = $aParam[1];
			elseif($aParam[0] == "filter_id")
				$filterId = $aParam[1];
		}

		if($filterId == "")
			return false;

		foreach ($aParams as $paramName => $value)
			$_SESSION[self::SESS_PARAMS_NAME][$filterId][$paramName] = $value;

		return true;
	}

	//experemental
	private function IsFiltered()
	{
		$fltTable = $_SESSION["SESS_ADMIN"][$this->tableId];

		if(!isset($fltTable) || !is_array($fltTable))
			return false;

		foreach ($fltTable as $value)
			if(!is_null($value))
				return true;

		return false;
	}

	private function PrintSaveOptionsDIV()
	{
		global $USER;
		$isAdmin = $USER->CanDoOperation('edit_other_settings');
		?>
<div style="display:none">
	<div id="filter_save_opts_<?=$this->id?>">
		<table width="100%">
			<tr>
				<td align="right" width="40%"><?=GetMessage("admin_lib_filter_sett_name")?></td>
				<td><input type="text" name="save_filter_name" value="" size="30" maxlength="255"></td>
			</tr>
			<?if($isAdmin):?>
				<tr>
					<td align="right" width="40%"><?=GetMessage("admin_lib_filter_sett_common")?></td>
					<td><input type="checkbox" name="common" ></td>
				</tr>
			<?endif;?>
		</table>
	</div>
</div>
		<?
	}

	public static function UnEscape($aFilter)
	{
		if(defined("BX_UTF"))
			return;
		if(!is_array($aFilter))
			return;
		foreach($aFilter as $flt)
			if(is_string($GLOBALS[$flt]) && CUtil::DetectUTF8($GLOBALS[$flt]))
				CUtil::decodeURIComponent($GLOBALS[$flt]);
	}
}

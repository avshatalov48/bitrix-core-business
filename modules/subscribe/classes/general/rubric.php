<?
IncludeModuleLangFile(__FILE__);

class CRubric
{
	var $LAST_ERROR="";

	//Get list
	public static function GetList($aSort=array(), $aFilter=array())
	{
		global $DB;

		$arFilter = array();
		foreach($aFilter as $key=>$val)
		{
			if(strlen($val)<=0)
				continue;

			$key = strtoupper($key);
			switch($key)
			{
				case "ID":
				case "ACTIVE":
				case "VISIBLE":
				case "LID":
				case "AUTO":
				case "CODE":
					$arFilter[] = "R.".$key." = '".$DB->ForSql($val)."'";
					break;
				case "NAME":
					$arFilter[] = "R.NAME like '%".$DB->ForSql($val)."%'";
					break;
			}
		}

		$arOrder = array();
		foreach($aSort as $key=>$val)
		{
			$ord = (strtoupper($val) <> "ASC"? "DESC": "ASC");
			$key = strtoupper($key);

			switch($key)
			{
				case "ID":
				case "NAME":
				case "SORT":
				case "LAST_EXECUTED":
				case "VISIBLE":
				case "LID":
				case "AUTO":
				case "CODE":
					$arOrder[] = "R.".$key." ".$ord;
					break;
				case "ACT":
					$arOrder[] = "R.ACTIVE ".$ord;
					break;
			}
		}
		if(count($arOrder) == 0)
			$arOrder[] = "R.ID DESC";
		$sOrder = "\nORDER BY ".implode(", ",$arOrder);

		if(count($arFilter) == 0)
			$sFilter = "";
		else
			$sFilter = "\nWHERE ".implode("\nAND ", $arFilter);

		$strSql = "
			SELECT
				R.ID
				,R.NAME
				,R.CODE
				,R.SORT
				,R.LID
				,R.ACTIVE
				,R.DESCRIPTION
				,R.AUTO
				,R.VISIBLE
				,".$DB->DateToCharFunction("R.LAST_EXECUTED", "FULL")." AS LAST_EXECUTED
				,R.FROM_FIELD
				,R.DAYS_OF_MONTH
				,R.DAYS_OF_WEEK
				,R.TIMES_OF_DAY
				,R.TEMPLATE
			FROM
				b_list_rubric R
			".$sFilter.$sOrder;

		return $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
	}

	//Get by ID
	public static function GetByID($ID)
	{
		global $DB;
		$ID = intval($ID);

		$strSql = "
			SELECT
				R.*
				,".$DB->DateToCharFunction("R.LAST_EXECUTED", "FULL")." AS LAST_EXECUTED
			FROM b_list_rubric R
			WHERE R.ID = ".$ID."
		";

		return $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
	}

	//Count of subscribers
	public static function GetSubscriptionCount($ID)
	{
		global $DB;
		$ID = intval($ID);

		$strSql = "
			SELECT COUNT('x') AS CNT
			FROM b_subscription_rubric SR
			WHERE SR.LIST_RUBRIC_ID = ".$ID."
		";

		$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if($res_arr = $res->Fetch())
			return intval($res_arr["CNT"]);
		else
			return 0;
	}


	// delete
	public static function Delete($ID)
	{
		global $DB;
		$ID = intval($ID);

		$DB->StartTransaction();

		$res = $DB->Query("DELETE FROM b_subscription_rubric WHERE LIST_RUBRIC_ID=".$ID, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if($res)
			$res = $DB->Query("DELETE FROM b_posting_rubric WHERE LIST_RUBRIC_ID=".$ID, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if($res)
			$res = $DB->Query("DELETE FROM b_list_rubric WHERE ID=".$ID, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if($res)
			$DB->Commit();
		else
			$DB->Rollback();

		return $res;
	}

	public static function OnBeforeLangDelete($lang)
	{
		global $DB, $APPLICATION;
		$rs = $DB->Query("SELECT count(*) C FROM b_list_rubric WHERE LID='".$DB->ForSql($lang, 2)."'", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$ar = $rs->Fetch();
		if($ar["C"] > 0)
		{
			$APPLICATION->ThrowException(GetMessage("class_rub_err_exists", array("#COUNT#"=>$ar["C"])));
			return false;
		}
		else
		{
			return true;
		}
	}

	//check fields before writing
	function CheckFields($arFields)
	{
		global $DB;
		$this->LAST_ERROR = "";
		$aMsg = array();

		if(strlen($arFields["NAME"]) == 0)
			$aMsg[] = array("id"=>"NAME", "text"=>GetMessage("class_rub_err_name"));
		if(strlen($arFields["LID"]) > 0)
		{
			$r = CLang::GetByID($arFields["LID"]);
			if(!$r->Fetch())
				$aMsg[] = array("id"=>"LID", "text"=>GetMessage("class_rub_err_lang"));
		}
		else
			$aMsg[] = array("id"=>"LID", "text"=>GetMessage("class_rub_err_lang2"));
		if(strlen($arFields["DAYS_OF_MONTH"]) > 0)
		{
			$arDoM = explode(",", $arFields["DAYS_OF_MONTH"]);
			$arFound = array();
			foreach($arDoM as $strDoM)
			{
				if(preg_match("/^(\d{1,2})$/", trim($strDoM), $arFound))
				{
					if(intval($arFound[1]) < 1 || intval($arFound[1]) > 31)
					{
						$aMsg[] = array("id"=>"DAYS_OF_MONTH", "text"=>GetMessage("class_rub_err_dom"));
						break;
					}
				}
				elseif(preg_match("/^(\d{1,2})-(\d{1,2})$/", trim($strDoM), $arFound))
				{
					if(intval($arFound[1]) < 1 || intval($arFound[1]) > 31 || intval($arFound[2]) < 1 || intval($arFound[2]) > 31 || intval($arFound[1]) >= intval($arFound[2]))
					{
						$aMsg[] = array("id"=>"DAYS_OF_MONTH", "text"=>GetMessage("class_rub_err_dom"));
						break;
					}
				}
				else
				{
					$aMsg[] = array("id"=>"DAYS_OF_MONTH", "text"=>GetMessage("class_rub_err_dom2"));
					break;
				}
			}
		}
		if(strlen($arFields["DAYS_OF_WEEK"]) > 0)
		{
			$arDoW = explode(",", $arFields["DAYS_OF_WEEK"]);
			$arFound = array();
			foreach($arDoW as $strDoW)
			{
				if(preg_match("/^(\d)$/", trim($strDoW), $arFound))
				{
					if(intval($arFound[1]) < 1 || intval($arFound[1]) > 7)
					{
						$aMsg[] = array("id"=>"DAYS_OF_WEEK", "text"=>GetMessage("class_rub_err_dow"));
						break;
					}
				}
				else
				{
					$aMsg[] = array("id"=>"DAYS_OF_WEEK", "text"=>GetMessage("class_rub_err_dow2"));
					break;
				}
			}
		}
		if(strlen($arFields["TIMES_OF_DAY"]) > 0)
		{
			$arToD = explode(",", $arFields["TIMES_OF_DAY"]);
			$arFound = array();
			foreach($arToD as $strToD)
			{
				if(preg_match("/^(\d{1,2}):(\d{1,2})$/", trim($strToD), $arFound))
				{
					if(intval($arFound[1]) > 23 || intval($arFound[2]) > 59)
					{
						$aMsg[] = array("id"=>"TIMES_OF_DAY", "text"=>GetMessage("class_rub_err_tod"));
						break;
					}
				}
				else
				{
					$aMsg[] = array("id"=>"TIMES_OF_DAY", "text"=>GetMessage("class_rub_err_tod2"));
					break;
				}
			}
		}
		if(strlen($arFields["TEMPLATE"])>0 && !CPostingTemplate::IsExists($arFields["TEMPLATE"]))
			$aMsg[] = array("id"=>"TEMPLATE", "text"=>GetMessage("class_rub_err_wrong_templ"));
		if($arFields["AUTO"]=="Y")
		{
			if((strlen($arFields["FROM_FIELD"]) < 3) || !check_email($arFields["FROM_FIELD"]))
				$aMsg[] = array("id"=>"FROM_FIELD", "text"=>GetMessage("class_rub_err_email"));
			if(strlen($arFields["DAYS_OF_MONTH"])+strlen($arFields["DAYS_OF_WEEK"]) <= 0)
				$aMsg[] = array("id"=>"DAYS_OF_MONTH", "text"=>GetMessage("class_rub_err_days_missing"));
			if(strlen($arFields["TIMES_OF_DAY"]) <= 0)
				$aMsg[] = array("id"=>"TIMES_OF_DAY", "text"=>GetMessage("class_rub_err_times_missing"));
			if(strlen($arFields["TEMPLATE"]) <= 0)
				$aMsg[] = array("id"=>"TEMPLATE", "text"=>GetMessage("class_rub_err_templ_missing"));
			if(is_set($arFields, "FROM_FIELD") && strlen($arFields["FROM_FIELD"])<=0)
				$aMsg[] = array("id"=>"FROM_FIELD", "text"=>GetMessage("class_rub_err_from"));
			if(strlen($arFields["LAST_EXECUTED"])<=0)
				$aMsg[] = array("id"=>"LAST_EXECUTED", "text"=>GetMessage("class_rub_err_le_missing"));
			elseif(is_set($arFields, "LAST_EXECUTED") && $arFields["LAST_EXECUTED"]!==false && $DB->IsDate($arFields["LAST_EXECUTED"], false, false, "FULL")!==true)
				$aMsg[] = array("id"=>"LAST_EXECUTED", "text"=>GetMessage("class_rub_err_le_wrong"));
		}

		if(!empty($aMsg))
		{
			$e = new CAdminException($aMsg);
			$GLOBALS["APPLICATION"]->ThrowException($e);
			$this->LAST_ERROR = $e->GetString();
			return false;
		}
		return true;
	}

	//add
	function Add($arFields)
	{
		global $DB;

		if(!$this->CheckFields($arFields))
			return false;

		$ID = $DB->Add("b_list_rubric", $arFields);

		if($ID>0 && $arFields["ACTIVE"]=="Y" && $arFields["AUTO"]=="Y" && COption::GetOptionString("subscribe", "subscribe_template_method")!=="cron")
				CAgent::AddAgent("CPostingTemplate::Execute();", "subscribe", "N", COption::GetOptionString("subscribe", "subscribe_template_interval"));
		return $ID;
	}

	//update
	function Update($ID, $arFields)
	{
		global $DB;
		$ID = intval($ID);

		if(!$this->CheckFields($arFields))
			return false;

		$strUpdate = $DB->PrepareUpdate("b_list_rubric", $arFields);
		if($strUpdate!="")
		{
			$strSql = "UPDATE b_list_rubric SET ".$strUpdate." WHERE ID=".$ID;
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if($ID>0 && $arFields["ACTIVE"]=="Y" && $arFields["AUTO"]=="Y" && COption::GetOptionString("subscribe", "subscribe_template_method")!=="cron")
					CAgent::AddAgent("CPostingTemplate::Execute();", "subscribe", "N", COption::GetOptionString("subscribe", "subscribe_template_interval"));
		}
		return true;
	}
}
?>
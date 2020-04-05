<?
IncludeModuleLangFile(__FILE__);

class CPostingTemplate
{
	var $LAST_ERROR = "";

	//Get list
	public static function GetList()
	{
		$io = CBXVirtualIo::GetInstance();
		$arTemplates = array();

		$dir = substr(getLocalPath("php_interface/subscribe/templates", BX_PERSONAL_ROOT), 1); //cut leading slash
		$abs_dir = $_SERVER["DOCUMENT_ROOT"]."/".$dir;
		if ($io->DirectoryExists($abs_dir))
		{
			$d = $io->GetDirectory($abs_dir);
			foreach ($d->GetChildren() as $dir_entry)
			{
				if ($dir_entry->IsDirectory())
				{
					$arTemplates[] = $dir."/".$dir_entry->GetName();
				}
			}
		}

		return $arTemplates;
	}

	public static function GetByID($path="")
	{
		global $MESS;

		if(!CPostingTemplate::IsExists($path))
			return false;

		$arTemplate = array();

		$strFileName= $_SERVER["DOCUMENT_ROOT"]."/".$path."/lang/".LANGUAGE_ID."/description.php";
		if(file_exists($strFileName))
			include($strFileName);

		$strFileName= $_SERVER["DOCUMENT_ROOT"]."/".$path."/description.php";
		if(file_exists($strFileName))
			include($strFileName);

		$arTemplate["PATH"] = $path;
		return $arTemplate;
	}

	public static function IsExists($path="")
	{
		$io = CBXVirtualIo::GetInstance();

		$dir = substr(getLocalPath("php_interface/subscribe/templates", BX_PERSONAL_ROOT), 1);
		if (strpos($path, $dir."/") === 0)
		{
			$template = substr($path, strlen($dir) + 1);
			if(
				strpos($template, "\0") !== false
				|| strpos($template, "\\") !== false
				|| strpos($template, "/") !== false
				|| strpos($template, "..") !== false
			)
			{
				return false;
			}

			return $io->DirectoryExists($_SERVER["DOCUMENT_ROOT"]."/".$path);
		}
		return false;
	}

	public static function Execute()
	{
		$rubrics = CRubric::GetList(array(), array("ACTIVE"=>"Y", "AUTO"=>"Y"));
		$current_time = time();
		$time_of_exec = false;
		$result = "";
		while(($arRubric=$rubrics->Fetch()) && $time_of_exec===false)
		{
			if ($arRubric["LAST_EXECUTED"] == '')
				continue;

			$last_executed = MakeTimeStamp(ConvertDateTime($arRubric["LAST_EXECUTED"], "DD.MM.YYYY HH:MI:SS"), "DD.MM.YYYY HH:MI:SS");

			if ($last_executed <= 0)
				continue;

			//parse schedule
			$arDoM = CPostingTemplate::ParseDaysOfMonth($arRubric["DAYS_OF_MONTH"]);
			$arDoW = CPostingTemplate::ParseDaysOfWeek($arRubric["DAYS_OF_WEEK"]);
			$arToD = CPostingTemplate::ParseTimesOfDay($arRubric["TIMES_OF_DAY"]);
			if($arToD)
				sort($arToD, SORT_NUMERIC);
			//sdate = truncate(last_execute)
			$arSDate = localtime($last_executed);
			$sdate = mktime(0, 0, 0, $arSDate[4]+1, $arSDate[3], $arSDate[5]+1900);
			while($sdate < $current_time && $time_of_exec===false)
			{
				$arSDate = localtime($sdate);
				if($arSDate[6]==0) $arSDate[6]=7;
				//determine if date is good for execution
				if($arDoM)
				{
					$flag = array_search($arSDate[3], $arDoM);
					if($arDoW)
						$flag = array_search($arSDate[6], $arDoW);
				}
				elseif($arDoW)
					$flag = array_search($arSDate[6], $arDoW);
				else
					$flag=false;

				if($flag!==false && $arToD)
					foreach($arToD as $intToD)
					{
						if($sdate+$intToD >  $last_executed && $sdate+$intToD <= $current_time)
						{
							$time_of_exec = $sdate+$intToD;
							break;
						}
					}
				$sdate = mktime(0, 0, 0, date("m",$sdate), date("d",$sdate)+1, date("Y",$sdate));//next day
			}
			if($time_of_exec!==false)
			{
				$arRubric["START_TIME"] = ConvertTimeStamp($last_executed, "FULL");
				$arRubric["END_TIME"] = ConvertTimeStamp($time_of_exec, "FULL");
				$arRubric["SITE_ID"] = $arRubric["LID"];
				CPostingTemplate::AddPosting($arRubric);
			}
			$result = "CPostingTemplate::Execute();";
		}
		return $result;
	}

	public static function AddPosting($arRubric)
	{
		global $DB, $USER, $MESS;
		if(!is_object($USER)) $USER = new CUser;
		//Include language file for template.php
		$rsSite = CSite::GetByID($arRubric["SITE_ID"]);
		$arSite = $rsSite->Fetch();
		$rsLang = CLanguage::GetByID($arSite["LANGUAGE_ID"]);
		$arLang = $rsLang->Fetch();

		$strBody="";
		$arFields=false;
		if(CPostingTemplate::IsExists($arRubric["TEMPLATE"]))
		{
			$strFileName= $_SERVER["DOCUMENT_ROOT"]."/".$arRubric["TEMPLATE"]."/lang/".$arSite["LANGUAGE_ID"]."/template.php";
			if(file_exists($strFileName))
				include($strFileName);
			//Execute template
			$strFileName= $_SERVER["DOCUMENT_ROOT"]."/".$arRubric["TEMPLATE"]."/template.php";
			if(file_exists($strFileName))
			{
				ob_start();
				$arFields = @include($strFileName);
				$strBody = ob_get_contents();
				ob_end_clean();
			}
		}
		$ID = false;
		//If there was an array returned then add posting
		if(is_array($arFields))
		{
			$arFields["BODY"] = $strBody;
			$cPosting=new CPosting;
			$arFields["AUTO_SEND_TIME"]=$arRubric["END_TIME"];
			$arFields["RUB_ID"]=array($arRubric["ID"]);
			$arFields["MSG_CHARSET"] = $arLang["CHARSET"];
			$ID = $cPosting->Add($arFields);
			if($ID)
			{
				if(array_key_exists("FILES", $arFields))
				{
					foreach($arFields["FILES"] as $arFile)
						$cPosting->SaveFile($ID, $arFile);
				}
				if(!array_key_exists("DO_NOT_SEND", $arFields) || $arFields["DO_NOT_SEND"]!="Y")
				{
					$cPosting->ChangeStatus($ID, "P");
					if(COption::GetOptionString("subscribe", "subscribe_auto_method")!=="cron")
						CAgent::AddAgent("CPosting::AutoSend(".$ID.",true,\"".$arRubric["LID"]."\");", "subscribe", "N", 0, $arRubric["END_TIME"], "Y", $arRubric["END_TIME"]);
				}
			}
		}
		//Update last execution time mark
		$strSql = "UPDATE b_list_rubric SET LAST_EXECUTED=".$DB->CharToDateFunction($arRubric["END_TIME"])." WHERE ID=".intval($arRubric["ID"]);
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		return $ID;
	}

	public static function ParseDaysOfMonth($strDaysOfMonth)
	{
		$arResult=array();
		if(strlen($strDaysOfMonth) > 0)
		{
			$arDoM = explode(",", $strDaysOfMonth);
			$arFound = array();
			foreach($arDoM as $strDoM)
			{
				if(preg_match("/^(\d{1,2})$/", trim($strDoM), $arFound))
				{
					if(intval($arFound[1]) < 1 || intval($arFound[1]) > 31)
						return false;
					else
						$arResult[]=intval($arFound[1]);
				}
				elseif(preg_match("/^(\d{1,2})-(\d{1,2})$/", trim($strDoM), $arFound))
				{
					if(intval($arFound[1]) < 1 || intval($arFound[1]) > 31 || intval($arFound[2]) < 1 || intval($arFound[2]) > 31 || intval($arFound[1]) >= intval($arFound[2]))
						return false;
					else
						for($i=intval($arFound[1]);$i<=intval($arFound[2]);$i++)
							$arResult[]=intval($i);
				}
				else
					return false;
			}
		}
		else
			return false;
		return $arResult;
	}

	public static function ParseDaysOfWeek($strDaysOfWeek)
	{
		if(strlen($strDaysOfWeek) <= 0)
			return false;

		$arResult = array();

		$arDoW = explode(",", $strDaysOfWeek);
		foreach($arDoW as $strDoW)
		{
			$arFound = array();
			if(
				preg_match("/^(\d)$/", trim($strDoW), $arFound)
				&& $arFound[1] >= 1
				&& $arFound[1] <= 7
			)
			{
				$arResult[]=intval($arFound[1]);
			}
			else
			{
				return false;
			}
		}

		return $arResult;
	}

	public static function ParseTimesOfDay($strTimesOfDay)
	{
		if(strlen($strTimesOfDay) <= 0)
			return false;

		$arResult = array();

		$arToD = explode(",", $strTimesOfDay);
		foreach($arToD as $strToD)
		{
			$arFound = array();
			if(
				preg_match("/^(\d{1,2}):(\d{1,2})$/", trim($strToD), $arFound)
				&& $arFound[1] <= 23
				&& $arFound[2] <= 59
			)
			{
				$arResult[]=intval($arFound[1])*3600+intval($arFound[2])*60;
			}
			else
			{
				return false;
			}
		}

		return $arResult;
	}
}
?>

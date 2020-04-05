<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if (!function_exists("_AttemptExists"))
{
	function _AttemptExists($testID, $attemptID = false)
	{
		$arFields = Array(
			"STUDENT_ID" => intval($GLOBALS["USER"]->GetID()),
			"TEST_ID" => $testID,
			"STATUS"=> array("B", "N"),
		);

		if ($attemptID !== false)
			$arFields["ID"] = $attemptID;

		CTimeZone::Disable();
		$rsAttempt = CTestAttempt::GetList(Array(), $arFields);
		CTimeZone::Enable();

		return ($rsAttempt->GetNext());
	}
}

if (!function_exists("_TimeToStringFormat"))
{
	function _TimeToStringFormat($secTotal)
	{
		$strTime = "";

		if ($secTotal <= 1)
			return $strTime;

		$hours = intval($secTotal/3600);
		if ($hours>0)
		{
			$strTime .= ($hours < 10 ? "0" : "").$hours.":";
			$secTotal = $secTotal - $hours*3600;
		}
		else
			$strTime .= "00:";

		$minutes = intval($secTotal/60);
		if ($minutes>0)
		{
			$strTime .= ($minutes < 10 ? "0" : "").$minutes.":";
			$secTotal = $secTotal - $minutes*60;
		}
		else
			$strTime .= "00:";

		$seconds = ($secTotal%60);
		$strTime .= ($seconds < 10 ? "0" : "").$seconds;

		return $strTime;
	}
}
?>
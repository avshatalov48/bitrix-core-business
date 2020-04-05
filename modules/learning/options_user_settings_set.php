<?
$learningWarningTmp = "";

if (CModule::IncludeModule("learning")):
	$arStudentFields = Array(
		"RESUME" => $student_RESUME,
		"PUBLIC_PROFILE" => ($student_PUBLIC_PROFILE=="Y" ? "Y" : "N")
	);

	$ar_res = CStudent::GetList(Array(), Array("USER_ID" => $ID));
	if ($arStudent = $ar_res->Fetch())
	{
		$learning_res = CStudent::Update($ID, $arStudentFields);
	}
	else
	{
		$arStudentFields["USER_ID"] = $ID;
		$STUDENT_USER_ID = CStudent::Add($arStudentFields);
		$learning_res = (intval($STUDENT_USER_ID)>0);
	}
endif;
?>
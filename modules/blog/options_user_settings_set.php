<?
$blogWarningTmp = "";

if (CModule::IncludeModule("blog") && check_bitrix_sessid()):
	$arblogFields = Array(
		"ALIAS" => $blog_ALIAS,
		"DESCRIPTION" => $blog_DESCRIPTION,
		"INTERESTS" => $blog_INTERESTS,
		"AVATAR" => $_FILES["blog_AVATAR"]
	);
	$arblogFields["AVATAR"]["del"] = $blog_AVATAR_del;

	if ($USER->IsAdmin())
		$arblogFields["ALLOW_POST"] = (($blog_ALLOW_POST=="Y") ? "Y" : "N");

	$ar_res = CBlogUser::GetByID($ID, BLOG_BY_USER_ID);
	if ($ar_res)
	{
		$arblogFields["AVATAR"]["old_file"] = $ar_res["AVATAR"];
		$BLOG_USER_ID = intval($ar_res["ID"]);

		$BLOG_USER_ID1 = CBlogUser::Update($BLOG_USER_ID, $arblogFields);
		$blog_res = (intval($BLOG_USER_ID1)>0);
	}
	else
	{
		$arblogFields["USER_ID"] = $ID;
		$arblogFields["~DATE_REG"] = CDatabase::CurrentTimeFunction();
		$BLOG_USER_ID = CBlogUser::Add($arblogFields);
		$blog_res = (intval($BLOG_USER_ID)>0);
	}
endif;
?>
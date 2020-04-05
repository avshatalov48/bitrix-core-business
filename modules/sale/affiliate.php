<?
$affiliateParam = COption::GetOptionString("sale", "affiliate_param_name", "partner");
if (StrLen($affiliateParam) > 0)
	if (array_key_exists($affiliateParam, $_GET))
		if (IntVal($_GET[$affiliateParam]) > 0)
			if (CModule::IncludeModule("sale"))
				CSaleAffiliate::GetAffiliate();
?>
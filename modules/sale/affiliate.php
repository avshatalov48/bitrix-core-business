<?
$affiliateParam = COption::GetOptionString("sale", "affiliate_param_name", "partner");
if ($affiliateParam <> '')
	if (array_key_exists($affiliateParam, $_GET))
		if (intval($_GET[$affiliateParam]) > 0)
			if (CModule::IncludeModule("sale"))
				CSaleAffiliate::GetAffiliate();
?>
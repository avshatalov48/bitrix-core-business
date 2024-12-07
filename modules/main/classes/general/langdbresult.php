<?php

use Bitrix\Main\SiteDomainTable;

class _CLangDBResult extends CDBResult
{
	function Fetch()
	{
		if ($res = parent::Fetch())
		{
			static $arCache = [];

			if (isset($arCache[$res["LID"]]))
			{
				$res["DOMAINS"] = $arCache[$res["LID"]];
			}
			else
			{
				$rs = SiteDomainTable::getList([
					'order' => ['DOMAIN_LENGTH' => 'ASC'],
					'cache' => ['ttl' => 86400],
				]);
				while ($ar = $rs->fetch())
				{
					$arLangDomain[$ar["LID"]][] = $ar;
				}

				$res["DOMAINS"] = "";
				if (isset($arLangDomain[$res["LID"]]) && is_array($arLangDomain[$res["LID"]]))
				{
					foreach ($arLangDomain[$res["LID"]] as $ar_res)
					{
						$domain = $ar_res["DOMAIN"];
						$arErrorsTmp = [];
						if ($domainTmp = CBXPunycode::ToUnicode($ar_res["DOMAIN"], $arErrorsTmp))
						{
							$domain = $domainTmp;
						}
						$res["DOMAINS"] .= $domain . "\r\n";
					}
				}

				$res["DOMAINS"] = trim($res["DOMAINS"]);
				$arCache[$res["LID"]] = $res["DOMAINS"];
			}

			if (empty($res["DOC_ROOT"]) || trim($res["DOC_ROOT"]) === "")
			{
				$res["ABS_DOC_ROOT"] = $_SERVER["DOCUMENT_ROOT"];
			}
			else
			{
				$res["ABS_DOC_ROOT"] = Rel2Abs($_SERVER["DOCUMENT_ROOT"], $res["DOC_ROOT"]);
			}

			if ($res["ABS_DOC_ROOT"] !== $_SERVER["DOCUMENT_ROOT"])
			{
				$res["SITE_URL"] = (CMain::IsHTTPS() ? "https://" : "http://") . $res["SERVER_NAME"];
			}
		}
		return $res;
	}
}

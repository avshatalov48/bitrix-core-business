<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

require_once(mb_substr(__FILE__, 0, mb_strlen(__FILE__) - mb_strlen("/classes/mysql/main.php"))."/bx_root.php");

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/main.php");

class CMain extends CAllMain
{
	/** @deprecated */
	public static function __GetConditionFName()
	{
		return "`CONDITION`";
	}

	/** @deprecated Will be removed soon  */
	public static function FileAction()
	{
	}

	public function GetLang($cur_dir=false, $cur_host=false)
	{
		global $DB, $lang, $MAIN_LANGS_CACHE, $MAIN_LANGS_ADMIN_CACHE;

		if($cur_dir === false)
			$cur_dir = $this->GetCurDir();
		if($cur_host === false)
			$cur_host = $_SERVER["HTTP_HOST"];

		if(
			mb_strpos($cur_dir, BX_ROOT."/admin/") === 0
			|| mb_strpos($cur_dir, BX_ROOT."/updates/") === 0
			|| (defined("ADMIN_SECTION") &&  ADMIN_SECTION === true)
			|| (defined("BX_PUBLIC_TOOLS") && BX_PUBLIC_TOOLS === true)
		)
		{
			//if admin section

			//lang by global var
			if($lang == '')
				$lang = COption::GetOptionString("main", "admin_lid", "ru");

			$R = CLanguage::GetList($o, $b, array("LID"=>$lang, "ACTIVE"=>"Y"));
			if($res = $R->Fetch())
			{
				$MAIN_LANGS_ADMIN_CACHE[$res["LID"]]=$res;
				return $res;
			}

			//no lang param - get default
			$R = CLanguage::GetList($by = "def", $order = "desc", array("ACTIVE"=>"Y"));
			if($res = $R->Fetch())
			{
				$MAIN_LANGS_ADMIN_CACHE[$res["LID"]]=$res;
				return $res;
			}
		}
		else
		{
			// all other sections

			$arURL = parse_url("http://".$cur_host);
			if($arURL["scheme"]=="" && $arURL["host"] <> '')
				$CURR_DOMAIN = $arURL["host"];
			else
				$CURR_DOMAIN = $cur_host;

			if(mb_strpos($CURR_DOMAIN, ':') > 0)
				$CURR_DOMAIN = mb_substr($CURR_DOMAIN, 0, mb_strpos($CURR_DOMAIN, ':'));
			$CURR_DOMAIN = trim($CURR_DOMAIN, "\t\r\n\0 .");

			//get site by path
			if(CACHED_b_lang!==false && CACHED_b_lang_domain!==false)
			{
				global $CACHE_MANAGER;
				$strSql =
					"SELECT L.*, L.LID as ID, L.LID as SITE_ID, ".
					"	C.FORMAT_DATE, C.FORMAT_DATETIME, C.FORMAT_NAME, C.WEEK_START, C.CHARSET, C.DIRECTION ".
					"FROM b_lang L, b_culture C ".
					"WHERE C.ID=L.CULTURE_ID AND L.ACTIVE='Y' ".
					"ORDER BY ".
					"	LENGTH(L.DIR) DESC, ".
					"	L.DOMAIN_LIMITED DESC, ".
					"	L.SORT ";
				if($CACHE_MANAGER->Read(CACHED_b_lang, "b_lang".md5($strSql), "b_lang"))
				{
					$arLang = $CACHE_MANAGER->Get("b_lang".md5($strSql));
				}
				else
				{
					$arLang = array();
					$R = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
					while($row = $R->Fetch())
						$arLang[] = $row;
					$CACHE_MANAGER->Set("b_lang".md5($strSql), $arLang);
				}

				$strSql =
					"SELECT  ".
					"LD.LID as LD_LID,LD.DOMAIN as LD_DOMAIN ".
					"FROM  ".
					"	 b_lang_domain LD  ".
					"ORDER BY ".
					"	LENGTH(LD.DOMAIN) DESC ";
				if($CACHE_MANAGER->Read(CACHED_b_lang_domain, "b_lang_domain2", "b_lang_domain"))
				{
					$arLangDomain = $CACHE_MANAGER->Get("b_lang_domain2");
				}
				else
				{
					$arLangDomain = array();
					$R = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
					while($row = $R->Fetch())
						$arLangDomain[$row["LD_LID"]][]=$row;
					$CACHE_MANAGER->Set("b_lang_domain2", $arLangDomain);
				}

				$arJoin = array();
				foreach($arLang as $row)
				{
					//LEFT JOIN
					$bLeft = true;
					//LEFT JOIN b_lang_domain LD ON L.LID=LD.LID
					if(array_key_exists($row["LID"], $arLangDomain))
					{
						foreach($arLangDomain[$row["LID"]] as $dom)
						{
							//AND '".$DB->ForSql($CURR_DOMAIN, 255)."' LIKE CONCAT('%', LD.DOMAIN)
							if(strcasecmp(mb_substr(".".$CURR_DOMAIN, -mb_strlen(".".$dom["LD_DOMAIN"])), ".".$dom["LD_DOMAIN"]) == 0)
							{
								$arJoin[] = $row+$dom;
								$bLeft = false;
							}
						}
					}
					if($bLeft)
					{
						$arJoin[] = $row+array("LD_LID"=>"","LD_DOMAIN"=>"");
					}
				}
				$A = array();
				foreach($arJoin as $row)
				{
					//WHERE ('".$DB->ForSql($cur_dir)."' LIKE CONCAT(L.DIR, '%') OR LD.LID IS NOT NULL)
					if($row["LD_LID"]!="" || strcasecmp(mb_substr($cur_dir, 0, mb_strlen($row["DIR"])), $row["DIR"]) == 0)
						$A[]=$row;
				}

				$res=false;
				if($res===false)
				{
					foreach($A as $row)
					{
						if(
							(strcasecmp(mb_substr($cur_dir, 0, mb_strlen($row["DIR"])), $row["DIR"]) == 0)
							&& (($row["DOMAIN_LIMITED"]=="Y" && $row["LD_LID"]!="")||$row["DOMAIN_LIMITED"]!="Y")
						)
						{
							$res=$row;
							break;
						}
					}
				}
				if($res===false)
				{
					foreach($A as $row)
					{
						if(strncasecmp($cur_dir, $row["DIR"], mb_strlen($cur_dir)) == 0)
						{
							$res=$row;
							break;
						}
					}
				}
				if($res===false)
				{
					foreach($A as $row)
					{
						if(($row["DOMAIN_LIMITED"] == "Y" && $row["LD_LID"] != "") || $row["DOMAIN_LIMITED"] != "Y")
						{
							$res=$row;
							break;
						}
					}
				}
				if($res===false && count($A)>0)
				{
					$res=$A[0];
				}
			}
			else
			{
				$strSql =
					"SELECT L.*, L.LID as ID, L.LID as SITE_ID, ".
					"	C.FORMAT_DATE, C.FORMAT_DATETIME, C.FORMAT_NAME, C.WEEK_START, C.CHARSET, C.DIRECTION ".
					"FROM b_lang L  ".
					"	LEFT JOIN b_lang_domain LD ON L.LID=LD.LID AND '".$DB->ForSql(".".$CURR_DOMAIN, 255)."' LIKE CONCAT('%.', LD.DOMAIN) ".
					"	INNER JOIN b_culture C ON C.ID=L.CULTURE_ID ".
					"WHERE ".
					"	('".$DB->ForSql($cur_dir)."' LIKE CONCAT(L.DIR, '%') OR LD.LID IS NOT NULL)".
					"	AND L.ACTIVE='Y' ".
					"ORDER BY ".
					"	IF((L.DOMAIN_LIMITED='Y' AND LD.LID IS NOT NULL) OR L.DOMAIN_LIMITED<>'Y', ".
					"		IF('".$DB->ForSql($cur_dir)."' LIKE CONCAT(L.DIR, '%'), 3, 1), ".
					"		IF('".$DB->ForSql($cur_dir)."' LIKE CONCAT(L.DIR, '%'), 2, 0) ".
					"	) DESC, ".
					"	LENGTH(L.DIR) DESC, ".
					"	L.DOMAIN_LIMITED DESC, ".
					"	L.SORT, ".
					"	LENGTH(LD.DOMAIN) DESC ";

				$R = $DB->Query($strSql, false, "File: ".__FILE__." Line:".__LINE__);
				$res = $R->Fetch();
			}

			if($res)
			{
				$MAIN_LANGS_CACHE[$res["LID"]] = $res;
				return $res;
			}

			//get default site
			$strSql =
				"SELECT L.*, L.LID as ID, L.LID as SITE_ID, ".
				"	C.FORMAT_DATE, C.FORMAT_DATETIME, C.FORMAT_NAME, C.WEEK_START, C.CHARSET, C.DIRECTION ".
				"FROM b_lang L, b_culture C ".
				"WHERE C.ID=L.CULTURE_ID AND L.ACTIVE='Y' ".
				"ORDER BY L.DEF DESC, L.SORT";

			$R = $DB->Query($strSql);
			if($res = $R->Fetch())
			{
				$MAIN_LANGS_CACHE[$res["LID"]]=$res;
				return $res;
			}
		}

		//core default
		return array(
			"LID" => "en",
			"DIR" => "/",
			"SERVER_NAME" => "",
			"CHARSET" => "UTF-8",
			"FORMAT_DATE" => "MM/DD/YYYY",
			"FORMAT_DATETIME" => "MM/DD/YYYY HH:MI:SS",
			"LANGUAGE_ID" => "en",
		);
	}
}

class CSite extends CAllSite
{
}

class CFilterQuery extends CAllFilterQuery
{
	public function BuildWhereClause($word)
	{
		$this->cnt++;
		//if($this->cnt>10) return "1=1";

		global $DB;
		if (isset($this->m_kav[$word]))
			$word = $this->m_kav[$word];

		$this->m_words[] = $word;

		$n = count($this->m_fields);
		$ret = "";
		if ($n>1) $ret = "(";
		for ($i=0; $i<$n; $i++)
		{
			$field = $this->m_fields[$i];
			if ($this->procent=="Y")
			{
				$ret.= "
					(upper($field) like upper('%".$DB->ForSqlLike($word, 2000)."%') and $field is not null)
					";
			}
			elseif (mb_strpos($word, "%") !== false || mb_strpos($word, "_") !== false)
			{
				$ret.= "
					(upper($field) like upper('".$DB->ForSqlLike($word, 2000)."') and $field is not null)
					";
			}
			else
			{
				$ret.= "
					($field='".$DB->ForSql($word, 2000)."' and $field is not null)
					";

			}
			if ($i<>$n-1) $ret.= " OR ";
		}
		if ($n>1) $ret.= ")";
		return $ret;
	}
}

class CLang extends CSite
{
}

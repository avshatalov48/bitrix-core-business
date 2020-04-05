<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002 Bitrix                  #
# http://www.bitrix.ru                       #
# mailto:admin@bitrix.ru                     #
##############################################
*/
global $level, $counter, $map, $APPLICATION, $strFolders, $arrChildMenu, $arAllMenu;
global $APPLICATION, $URI_404, $levels;
global $im0, $im1, $unfolded_arr, $im_c_invert, $im_i, $im_i_invert, $im_m, $im_m_invert, $im_n, $im_n_invert, $im_p, $im_p_invert, $im_t, $im_t_invert, $im_l, $im_l_invert, $im_null, $frm, $random_number, $im_folder;

extract($_REQUEST, EXTR_SKIP);

$return = "";
$URI_404 = "";
$random_number = "";
$frm = "";

IncludeTemplateLangFile(__FILE__);
$APPLICATION->SetTemplateCSS("main/map/default.css");
if(strlen($APPLICATION->GetTitle())<=0)
	$APPLICATION->SetTitle(GetMessage("MAP_TITLE"));

$arrMainMenu = explode(",",COption::GetOptionString("main","map_top_menu_type","top"));
$arrChildMenu = explode(",",COption::GetOptionString("main","map_left_menu_type","left"));

$im0 = "</TD><TD><IMG SRC=\"/bitrix/images/1.gif\" WIDTH=\"12\" HEIGHT=\"21\" BORDER=0 ALT=\"\">";
$im1 = "</TD><TD><IMG SRC=\"/bitrix/images/1.gif\" WIDTH=\"9\" HEIGHT=\"22\" BORDER=0 ALT=\"\">";
$im_c = "</TD><TD><IMG SRC=\"/bitrix/images/map/c.gif\" WIDTH=\"12\" HEIGHT=\"22\" BORDER=0 ALT=\"\">";
$im_c_invert = "</TD><TD><IMG SRC=\"/bitrix/images/map/c-invert.gif\" WIDTH=\"9\" HEIGHT=\"22\" BORDER=0 ALT=\"\">";
$im_i = "</TD><TD><IMG SRC=\"/bitrix/images/map/i.gif\" WIDTH=\"9\" HEIGHT=\"22\" BORDER=0 ALT=\"\">";
$im_i_invert = "</TD><TD background=\"/bitrix/images/map/i-invert.gif\">".
	"<IMG SRC=\"/bitrix/images/map/i-invert.gif\" WIDTH=\"12\" HEIGHT=\"22\" BORDER=0 ALT=\"\">";
$im_m = "</TD><TD><IMG SRC=\"/bitrix/images/map/m.gif\" WIDTH=\"9\" HEIGHT=\"21\" BORDER=0 ALT=\"\">";
$im_m_invert = "<IMG SRC=\"/bitrix/images/map/m-invert.gif\" WIDTH=\"9\" HEIGHT=\"22\" BORDER=0 ALT=\"\">";
$im_n = "</TD><TD><IMG SRC=\"/bitrix/images/map/n.gif\" WIDTH=\"9\" HEIGHT=\"21\" BORDER=0 ALT=\"\">";
$im_n_invert = "</TD><TD><IMG SRC=\"/bitrix/images/map/n-invert.gif\" WIDTH=\"9\" HEIGHT=\"21\" BORDER=0 ALT=\"\">";
$im_p = "</TD><TD><IMG SRC=\"/bitrix/images/map/p.gif\" WIDTH=\"9\" HEIGHT=\"21\" BORDER=0 ALT=\"\">";
$im_p_invert = "<IMG SRC=\"/bitrix/images/map/p-invert.gif\" WIDTH=\"9\" HEIGHT=\"21\" BORDER=0 ALT=\"\">";
$im_t = "</TD><TD><IMG SRC=\"/bitrix/images/map/t.gif\" WIDTH=\"9\" HEIGHT=\"21\" BORDER=0 ALT=\"\">";
$im_t_invert = "</TD><TD background=\"/bitrix/images/map/i-invert.gif\">".
	"<IMG SRC=\"/bitrix/images/map/t-invert.gif\" WIDTH=\"12\" HEIGHT=\"22\" BORDER=0 ALT=\"\">";
$im_trans = "</TD><TD><IMG SRC=\"/bitrix/images/map/trans.gif\" WIDTH=\"9\" HEIGHT=\"21\" BORDER=0 ALT=\"\">";
$im_l = "</TD><TD><IMG SRC=\"/bitrix/images/map/l.gif\" WIDTH=\"9\" HEIGHT=\"21\" BORDER=0 ALT=\"\">";
$im_l_invert = "</TD><TD><IMG SRC=\"/bitrix/images/map/l-invert.gif\" WIDTH=\"12\" HEIGHT=\"22\" BORDER=0 ALT=\"\">";
$im_folder = "<img src=\"/bitrix/images/map/folder.gif\" BORDER=0 alt=\"\" hspace=\"3\" width=\"10\" height=\"21\">";
//$im_null = "<IMG SRC=\"/bitrix/images/1.gif\" WIDTH=\"6\" HEIGHT=\"10\" BORDER=0 ALT=\"\">";

function FindChild(&$map, $iCurrent)
{
	global $APPLICATION, $URI_404, $levels;
	global $im0, $im1, $unfolded_arr, $im_c_invert, $im_i, $im_i_invert, $im_m, $im_m_invert, $im_n, $im_n_invert, $im_p, $im_p_invert, $im_t, $im_t_invert, $im_l, $im_l_invert, $im_null, $frm, $random_number, $im_folder;

	$fpathtmp = $map[$iCurrent]["FULL_PATH"];
	if($iCurrent>0 && $map[$iCurrent]["IS_DIR"]=="Y")
		$fpathtmp = substr($map[$iCurrent]["FULL_PATH"], 0, strlen($map[$iCurrent]["FULL_PATH"])-1);

	$nLevel = $map[$iCurrent]["LEVEL"];

	$last = false;
	if(count($map)==$iCurrent+1 || $map[$iCurrent]["PARENT_ID"]!=$map[$iCurrent+1]["PARENT_ID"])
	{
		$last = true;
		for($i=$iCurrent+1; $i<count($map); $i++)
			if($map[$iCurrent]["PARENT_ID"]==$map[$i]["PARENT_ID"])
			{
				$last = false;
				break;
			}
	}

	if ($last)
	{
		if ($nLevel>0)
		{
			$levels[$nLevel] = "N";
			$im_c = $im_l_invert;
		}
	}
	else
	{
		if ($nLevel>0) $levels[$nLevel] = "Y";
		$im_c = $im_t_invert;
	}

	for ($i=0; $i<$nLevel; $i++)
	{
		if($levels[$i]=="Y")
		{
			$s.=$im_i_invert;
		}
		elseif ($i>0)
		{
			$s.=$im0;
		}
	}

/*
print_r($map[$iCurrent]);
echo "\n";
echo "<br><br>".$map[$iCurrent]["PARENT_ID"]." - ".$map[$iCurrent]["ID"]." - ".$map[$iCurrent]["NAME"]." - level: ".$nLevel."<br>";
echo "last - ".$last."<br>";
print_r($levels);
*/


	$id = $map[$iCurrent]["ID"];
	if ($map[$iCurrent]["IS_DIR"]<>"Y")
	{
		$im_c_invert_temp = ($nLevel>0) ? $im_c_invert : $im1;

		// Child
		$ret = "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"100%\">".
		"<tr>".
		"<td align=\"left\" valign=\"middle\">".
		"$s$im_c$im_c_invert_temp<a href=\"".$map[$iCurrent]["FULL_PATH"]."\"><img src=\"/bitrix/images/map/doc.gif\" border=0 alt=\"\" hspace=\"3\" align=\"absbottom\" width=\"8\" height=\"21\"></a>".
		"</td>".
		"<td align=\"left\" width=\"100%\" valign=\"middle\" nowrap>".
		"<a class=\"maplink\" href=\"".$map[$iCurrent]["FULL_PATH"]."\">".$map[$iCurrent]["NAME"]."</a>".
		"</td>".
		"</tr></table>";

		return "<tr><td width=\"0%\">$im_null</td><td width=\"100%\">".$ret."</td></tr>";
	}

	$sfch="";
	for ($i=$iCurrent+1; $i<count($map); $i++)
	{
		if ($map[$iCurrent]["ID"] == $map[$i]["PARENT_ID"])
		{
			if ($map[$iCurrent]["IS_DIR"]=="Y")
			{
				$sfch .= FindChild($map, $i);
			}
			array_splice($map, $i, 1);
			$i--;
		}
	}

	if (strlen($sfch)>0)
	{
		$unfolded_arr_temp=$unfolded_arr;
		if (in_array($id,$unfolded_arr_temp))
		{
			$key = array_search($id, $unfolded_arr);
			unset($unfolded_arr_temp[$key]);
			$str = implode(",",$unfolded_arr_temp);
			$link = "</td><td><a href=\"javascript:document.form_$id.submit()\">$im_m_invert</a>";
		}
		else
		{
			$str = implode(",",$unfolded_arr_temp).",".$id;
			$link = "</td><td><a href=\"javascript:document.form_$id.submit()\">$im_p_invert</a>";
		}
		$frm .= "<form name=\"".htmlspecialcharsbx("form_".$id)."\" action=\"".$URI_404."?$random_number\" method=\"POST\"><input type=\"hidden\" name=\"unfolded\" value=\"".htmlspecialcharsbx($str)."\"><input type=\"hidden\" name=\"local\" value=\"Y\"><input type=\"hidden\" name=\"lang\" value=\"".LANG."\"></form>";
	}
	elseif ($iCurrent>0) $link = "$im_c_invert";
	else $link = "$im1";

	// Parent
	$a1 = "<a href=\"".$map[$iCurrent]["FULL_PATH"]."\" class=\"maplink\">";
	$a2 = "<a href=\"".$map[$iCurrent]["FULL_PATH"]."\">";
	$a3 = "</a>";
	$s =
		"<tr>".
			"<td width=\"0%\">$im_null</td>".
			"<td width=\"100%\">".
				"<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"100%\">".
					"<tr>".
						"<td align=\"left\" valign=\"middle\">".
							$s.(($iCurrent>0) ? $im_c : "").$link."</TD><TD>".$a2.$im_folder.$a3.
						"</td>".
						"<td align=\"left\" width=\"100%\" valign=\"middle\" nowrap>".
							$a1.$map[$iCurrent]["NAME"].$a3.
						"</td> ".
					"</tr>".
				"</table>".
			"</td>".
		"</tr>".(is_array($unfolded_arr) && in_array($id, $unfolded_arr)? $sfch : "");

	return $s;
}
global $arAllMenu;
$arAllMenu = array();
function GetMapChilds($PARENT_PATH, $PARENT_ID)
{
	global $level, $counter, $map, $APPLICATION, $strFolders, $arrChildMenu, $arAllMenu, $USER;
	if (is_array($arrChildMenu) && count($arrChildMenu)>0)
	{
		$arrChildMenuX = $arrChildMenu;
		reset($arrChildMenuX);
		foreach ($arrChildMenuX as $cmenu)
		{
			$aMenuLinks = array();

			$child_menu = $PARENT_PATH.".".trim($cmenu).".menu.php";
			$level++;
			$bExists = false;
			if(file_exists($_SERVER["DOCUMENT_ROOT"].$child_menu))
			{
				include($_SERVER["DOCUMENT_ROOT"].$child_menu);
				$bExists = true;
			}

			if(file_exists($_SERVER["DOCUMENT_ROOT"].$PARENT_PATH.".".trim($cmenu).".menu_ext.php"))
			{
				include($_SERVER["DOCUMENT_ROOT"].$PARENT_PATH.".".trim($cmenu).".menu_ext.php");
				$bExists = true;
			}

			if($bExists)
			{
				foreach ($aMenuLinks as $aMenu)
				{
					if(count($aMenu)>4)
					{
						$CONDITION = $aMenu[4];
						if(strlen($CONDITION)>0 && (!@eval("return ".$CONDITION.";")))
							continue;
					}

					if (strlen($aMenu[1])>0)
					{
						$search_child = true;
						if(preg_match("'^(([A-Za-z]+://)|mailto:|javascript:)'i", $aMenu[1]))
							$full_path = $aMenu[1];
						else
							$full_path = trim(Rel2Abs($PARENT_PATH, $aMenu[1]));
					}
					else
					{
						$search_child = false;
						$full_path = $PARENT_PATH;
					}

					if (strlen($full_path)>0)
					{
						$FILE_ACCESS = (preg_match("'^(([A-Za-z]+://)|mailto:|javascript:)'i", $full_path)) ? "R" : $APPLICATION->GetFileAccessPermission($full_path);
						if ($FILE_ACCESS!="D" && $aMenu[3]["SEPARATOR"]!="Y")
						{
							$is_dir = (is_dir($_SERVER["DOCUMENT_ROOT"].$full_path)) ? "Y" : "N";
							if ($is_dir=="Y")
							{
								$aCnt_parent = count_chars($PARENT_PATH,1);
								$parent_slash = $aCnt_parent[ord("/")];
								$aCnt_child = count_chars($full_path,1);
								$child_slash = $aCnt_child[ord("/")];
								if ($child_slash<=$parent_slash) $search_child = false;
							}
							$counter++;
							$ar["COUNTER"] = $counter;
							$ar["ID"] = md5($full_path.$ar["COUNTER"]);
							$ar["PARENT_ID"] = $PARENT_ID;
							$ar["LEVEL"] = $level;
							$ar["IS_DIR"] = $is_dir;
							$ar["NAME"] = $aMenu[0];
							$ar["PATH"] = $PARENT_PATH;
							$ar["FULL_PATH"] = $full_path;
							$map[] = $ar;
							if ($is_dir=="Y" && $search_child)
							{
								$strFolders .= ",".$ar["ID"];
								GetMapChilds($ar["FULL_PATH"], $ar["ID"]);
							}
						}
					}
				}
			}
			$level--;
		}
	}
}

define("IS_SITE_MAP","Y");

if (ERROR_404=="Y")
{
	$sl = CLang::GetDefList();
	while ($slr = $sl->Fetch())
	{
		if ($slr["LID"]==LANG)
		{
			$dir = $slr["DIR"];
			break;
		}
	}
	$URI_404 = $dir."map.php";
}

if (isset($unfolded)) $_SESSION["SESS_UNFOLDED"] = $unfolded;
$unfolded_arr = explode(",", $_SESSION["SESS_UNFOLDED"]);

$random_number = mt_rand (1,999999);
$levels="";

$cache_map = new CPHPCache;
$CACHE_ID = SITE_ID."|".__FILE__."|".md5(serialize($arParams))."|".$USER->GetGroups();
if ($cache_map->InitCache($GLOBALS["MAP_CACHE_TIME"], $CACHE_ID, "/map/"))
{
	$vars = $cache_map->GetVars();
	$arrMAP = $vars["MAP"];
	$MAP_COUNTER = count($arrMAP);
	reset($arrMAP);
	if (is_array($arrMAP) && count($arrMAP)>0)
	{
		foreach ($arrMAP as $arM)
		{
			if (is_array($arM) && count($arM)>0)
			{
				reset($arM);
				foreach ($arM as $ar)
				{
					if ($ar["IS_DIR"]=="Y") $strFolders .= ",".$ar["ID"];
				}
			}
		}
	}
}
else
{
	$sl = @CLang::GetList();
	while ($slr = $sl->Fetch())
	{
		if ($slr["LID"]==LANG)
		{
			$lang_dir = $slr["DIR"];
			break;
		}
	}
	$i = 0;
	if(is_array($arrMainMenu) && count($arrMainMenu)>0)
	{
		foreach($arrMainMenu as $mmenu)
		{
			$main_menu = $lang_dir.".".trim($mmenu).".menu.php";
			if(file_exists($_SERVER["DOCUMENT_ROOT"].$main_menu))
			{
				$aMenuLinks = array();
				include($_SERVER["DOCUMENT_ROOT"].$main_menu);
				foreach ($aMenuLinks as $aMenu)
				{
					if(count($aMenu)>4)
					{
						$CONDITION = $aMenu[4];
						if(strlen($CONDITION)>0 && (!@eval("return ".$CONDITION.";")))
							continue;
					}

					$full_path = $aMenu[1];
					$full_path = str_replace("\\", "/", $full_path);
					$full_path = str_replace("//", "/", $full_path);
					if (!file_exists($_SERVER["DOCUMENT_ROOT"].$full_path))
					{
						$full_path = $lang_dir."/".$full_path;
						$full_path = str_replace("\\", "/", $full_path);
						$full_path = str_replace("//", "/", $full_path);
					}
					if ($APPLICATION->GetFileAccessPermission($full_path)!="D")
					{
						$map = array();
						$i++;
						$MAP_COUNTER = $i;
						$title = $aMenu[0];
						$is_dir = is_dir($_SERVER["DOCUMENT_ROOT"].$full_path) ? "Y" : "N";
						$counter++;
						$ar["COUNTER"] = $counter;
						$ar["ID"] = md5($full_path.$ar["COUNTER"]);
						$ar["PARENT_ID"] = $ar["ID"];
						$ar["LEVEL"] = 0;
						$ar["IS_DIR"] = $is_dir;
						$ar["NAME"] = $title;
						$ar["PATH"] = $full_path;
						$ar["FULL_PATH"] = $full_path;
						$map[] = $ar;
						if ($is_dir=="Y")
						{
							$strFolders .= ",".$ar["ID"];
							$level = 0;
							GetMapChilds($ar["FULL_PATH"], $ar["ID"]);
						}
						if(!is_array(${"map_".$i}))
							${"map_".$i} = Array();
						${"map_".$i} = array_merge(${"map_".$i}, $map);
						$arrMAP[$i] = ${"map_".$i};
					}
				}
			}
		}
	}
	if ($cache_map->StartDataCache())
		$cache_map->EndDataCache(Array("MAP" => $arrMAP));
}

if (!isset($_SESSION["SESS_UNFOLDED"])) $unfolded_arr = explode(",", $strFolders);

for ($j=1; $j<=$MAP_COUNTER; $j++)
{
	if (count($arrMAP[$j])>0)
	{
		$arM = $arrMAP[$j];
		$PARENT_ID = $arM[0]["PARENT_ID"];
		$s_map = "";
		for ($i=0; $arM[$i]["PARENT_ID"]==$PARENT_ID; $i++)
		{
			$s_map .= FindChild($arM, $i);
		}
		$s_map = "<table border='0' cellspacing='0' cellpadding='0' width='100%'>".$s_map."</table>";
	}
	$return .= $s_map;
}
?>
<font class="text">[&nbsp;<a href="javascript:document.expand_all.submit()" class="text"><?=GetMessage("MAP_EXPAND_ALL")?></a>&nbsp;]&nbsp;&nbsp;[&nbsp;<a href="javascript:document.collapse_all.submit()" class="text"><?=GetMessage("MAP_COLLAPSE_ALL")?></a>&nbsp;]
<br><br><?=$return?>
<form name="expand_all" action="<?=htmlspecialcharsbx($URI_404)?>?<?=htmlspecialcharsbx($random_number)?>" method="POST">
<input type="hidden" name="unfolded" value="<?=htmlspecialcharsbx($strFolders)?>">
<input type="hidden" name="lang" value="<?=LANG?>">
</form>
<form name="collapse_all" action="<?=htmlspecialcharsbx($URI_404)?>?<?=htmlspecialcharsbx($random_number)?>" method="POST">
<input type="hidden" name="unfolded" value="">
<input type="hidden" name="lang" value="<?=LANG?>">
</form><?=$frm?>

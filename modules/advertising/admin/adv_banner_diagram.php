<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2004 Bitrix                  #
# https://www.bitrixsoft.com          #
# mailto:admin@bitrix.ru                     #
##############################################
*/

use Bitrix\Main\Loader;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/advertising/prolog.php");
Loader::includeModule('advertising');

$isAdmin = CAdvContract::IsAdmin();
$isDemo = CAdvContract::IsDemo();
$isManager = CAdvContract::IsManager();
$isAdvertiser = CAdvContract::IsAdvertiser();

if (!$isAdmin && !$isDemo && !$isManager && !$isAdvertiser)
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/advertising/admin/adv_stat_list.php");
include($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/advertising/colors.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/img.php");

/***************************************************************************
 * Обработка GET | POST
 ****************************************************************************/
$strError = '';
$rsContracts = CAdvContract::GetList("s_sort", "desc");
$group_ref = [];
$group_ref_id = [];
$banner_ref = [];
$banner_ref_id = [];

$rsBanns = CAdvBanner::GetList("s_dropdown", "desc");
while ($arBann = $rsBanns->Fetch())
{
	$banner_ref_id[] = $arBann["ID"];
	$banner_ref[] = "[" . $arBann["ID"] . "] " . $arBann["NAME"];
	if (!in_array($arBann["GROUP_SID"], $group_ref_id) && $arBann["GROUP_SID"] <> '')
	{
		$group_ref_id[] = $arBann["GROUP_SID"];
		$group_ref[] = $arBann["GROUP_SID"];
	}

	if ($find_type_sid <> '')
	{
		if ($arBann["TYPE_SID"] == $find_type_sid)
			$find_banner_id[] = $arBann["ID"];
	}
}
if (empty($banner_ref))
	$strError = GetMessage("ADV_NO_BANNERS_FOR_DIAGRAM");

$man = false;
if ((!isset($_SESSION["SESS_ADMIN"]["AD_STAT_BANNER_DIAGRAM"]) || empty($_SESSION["SESS_ADMIN"]["AD_STAT_BANNER_DIAGRAM"])) && $find_date1 == '' && $find_date2 == '' && !is_array($find_banner_id) && !is_array($find_what_show))
{
	$find_banner_id = $banner_ref_id;
	$find_what_show = ["ctr"];
	$man = true;
	$set_filter = "Y";
}

$FilterArr = [
	"find_date1",
	"find_date2",
	"find_group_sid",
	"find_banner_id",
	"find_what_show",
];
if ($set_filter <> '' || $man)
	InitFilterEx($FilterArr, "AD_STAT_BANNER_DIAGRAM", "set", true);
else
	InitFilterEx($FilterArr, "AD_STAT_BANNER_DIAGRAM", "get", true);
if ($del_filter <> '')
	DelFilterEx($FilterArr, "AD_STAT_LIST", true);

if ((empty($find_banner_id) || !is_set($find_what_show)) && mb_strlen($strError) < 0)
	$strError = GetMessage("ADV_F_NO_FIELDS");

$arFilter = [
	"DATE_1" => $find_date1,
	"DATE_2" => $find_date2,
	"GROUP_SID" => $find_group_sid,
	"BANNER_ID" => $find_banner_id,
	"WHAT_SHOW" => $find_what_show,
];

$arrDays = CAdvBanner::GetDynamicList($arFilter, $arrLegend, $is_filtered);

$arShow = $find_what_show;
$filter_selected = 0;
if (is_array($find_group_sid) && count($find_group_sid) > 0)
	$filter_selected++;
if (is_array($find_banner_id) && count($find_banner_id) > 0)
	$filter_selected++;

if ($filter_selected > 0)
	$is_filtered = true;

/***************************************************************************
 * HTML форма
 ****************************************************************************/
$APPLICATION->SetTitle(GetMessage("AD_BANNER_DIAGRAM_PAGE_TITLE"));
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
$FilterFields = [
	//GetMessage("AD_F_PERIOD"),
	GetMessage("AD_F_WHAT_TO_SHOW"),
];
$FilterFields[] = GetMessage("AD_F_BANNERS");
if (count($group_ref_id) > 0)
	$FilterFields[] = GetMessage("AD_F_GROUPS");

$filter = new CAdminFilter(
	$sTableID . "_filter_id",
	$FilterFields
);
?>

<form name="form1" method="POST" action="<?= $APPLICATION->GetCurPage() ?>?">
	<input type="hidden" name="lang" value="<?= htmlspecialcharsbx(LANGUAGE_ID) ?>">
	<? $filter->Begin();
	?>
	<tr valign="center">
		<td width="0%" nowrap><? echo GetMessage("AD_F_PERIOD") . " (" . CSite::GetDateFormat("SHORT") . "):" ?></td>
		<td width="0%"
			nowrap><? echo CalendarPeriod("find_date1", $find_date1, "find_date2", $find_date2, "form1", "Y") ?></td>
	</tr>
	<tr valign="top">
		<td nowrap valign="top"><span class="required">*</span><?= GetMessage("AD_F_WHAT_TO_SHOW") ?>
			:<br><img src="/bitrix/images/advertising/mouse.gif" width="44" height="21" border=0 alt=""></td>
		<td><?
			$arr = [
				"reference" => [
					GetMessage("AD_VISITOR_GRAPH"),
					GetMessage("AD_SHOW_GRAPH"),
					GetMessage("AD_CLICK_GRAPH"),
					"CTR",
				],
				"reference_id" => [
					"visitor",
					"show",
					"click",
					"ctr",
				],
			];
			echo SelectBoxMFromArray("find_what_show[]", $arr, $find_what_show, "", false, "4");
			?></td>
	</tr>
	<tr>
		<td nowrap valign="top"><span class="required">*</span><?= GetMessage("AD_F_BANNERS") ?>
			:<br><img src="/bitrix/images/advertising/mouse.gif" width="44" height="21" border=0 alt=""></td>
		<td><?
			echo SelectBoxMFromArray("find_banner_id[]", [
				"REFERENCE" => $banner_ref,
				"REFERENCE_ID" => $banner_ref_id,
			], $find_banner_id, "", false, "10", "style='width:100%'");
			?></td>
	</tr>
	<? if (count($group_ref_id) > 0): ?>
		<tr>
			<td nowrap valign="top"><?= GetMessage("AD_F_GROUPS") ?>:<br><img src="/bitrix/images/advertising/mouse.gif"
					width="44"
					height="21"
					border=0
					alt=""></td>
			<td><?
				echo SelectBoxMFromArray("find_group_sid[]", [
					"REFERENCE" => $group_ref,
					"REFERENCE_ID" => $group_ref_id,
				], $find_group_sid, "", false, "5", "style='width:100%'");
				?></td>
		</tr>
	<? endif; ?>
	<?
	$filter->Buttons();
	?>
	<input type="submit"
		id="set_filter"
		name="set_filter"
		value="<?= GetMessage("ADV_F_FIND") ?>"
		title="<?= GetMessage("ADV_F_FIND_TITLE") ?>">
	<input type="submit"
		name="del_filter"
		value="<?= GetMessage("ADV_F_CLEAR") ?>"
		title="<?= GetMessage("ADV_F_CLEAR_TITLE") ?>">
	<?
	$filter->End();
	?>
</form>
<?
echo CAdminMessage::ShowMessage($strError);

$diameter = intval(COption::GetOptionString("advertising", "BANNER_DIAGRAM_DIAMETER"));

if (!function_exists("ImageCreate")) :
	echo CAdminMessage::ShowMessage(GetMessage("AD_GD_NOT_INSTALLED") . "<br>");
elseif (count($arrLegend) > 0) :
	echo BeginNote();
	echo GetMessage("AD_SERVER_TIME") . "&nbsp;&nbsp;<i>" . GetTime(time(), "FULL") . "</i><br>";
	echo GetMessage("AD_DAYS_TO_KEEP") . "&nbsp;&nbsp;<i>" . COption::GetOptionString("advertising", "BANNER_DAYS") . "</i>";
	if ($isAdmin)
		echo "&nbsp;&nbsp;[<a href='/bitrix/admin/settings.php?lang=" . LANGUAGE_ID . "&mid=advertising' title='" . GetMessage("AD_SET_EDIT") . "'>" . GetMessage("AD_EDIT") . "</a>]";
	echo EndNote();

	// Диаграммы по баннерам
	if ($find_banner_summa != "Y" && count($find_banner_id) > 1) :

		$diagram_type = "BANNER";

		$sum_ctr = 0;
		$sum_show = 0;
		$sum_click = 0;
		$sum_visitor = 0;
		foreach ($arrLegend as $keyL => $arrS)
		{
			if ($arrS["COUNTER_TYPE"] == "DETAIL" && $arrS["TYPE"] == $diagram_type)
			{
				$sum_ctr += $arrS["CTR"];
				$sum_show += $arrS["SHOW"];
				$sum_click += $arrS["CLICK"];
				$sum_visitor += $arrS["VISITOR"];
			}
		}

		if ($sum_show > 0 || $sum_click > 0 || $sum_ctr > 0 || $sum_visitor > 0) :

			if (!function_exists("ImageCreate")) :
				echo CAdminMessage::ShowMessage(GetMessage("AD_GD_NOT_INSTALLED") . "<br>");
			else :
				reset($arShow);
				$aTabs = [];
				$i = 0;
				foreach ($arShow as $ctype)
				{
					$counter_type = mb_strtoupper($ctype);
					if (${"sum_" . mb_strtolower($ctype)} > 0)
					{
						$i++;
						$aTabs[] = [
							"DIV" => "ttttab" . $i,
							"TAB" => GetMessage("AD_" . $counter_type . "_DIAGRAM"),
							"TITLE" => GetMessage("AD_BANNER_DIAGRAM_TITLE"),
						];
					}
				}

				reset($arShow);
				$viewTabBanner = new CAdminViewTabControl("viewTabBanner", $aTabs);
				if (count($aTabs) > 0)
					$viewTabBanner->Begin();

				foreach ($arShow as $ctype) :
					$counter_type = mb_strtoupper($ctype);
					if (${"sum_" . mb_strtolower($ctype)} > 0):
						$viewTabBanner->BeginNextTab();
						?>
						<div class="graph">
							<table cellspacing=0 cellpadding=0 class="graph">

								<tr>
									<td valign="top"><img class="graph"
											src="/bitrix/admin/adv_diagram.php?<?= GetFilterParams($FilterArr) ?>&diagram_type=<? echo $diagram_type ?>&counter_type=<? echo $counter_type ?>"
											width="<? echo $diameter ?>"
											height="<? echo $diameter ?>"></td>
									<td valign="top">
										<table cellpadding=0 cellspacing=0 border=0 class="legend">
											<?
											$i = 0;
											foreach ($arrLegend as $keyL => $arrS) :
												if ($arrS["COUNTER_TYPE"] == "DETAIL" && $arrS["TYPE"] == $diagram_type):
													$i++;
													$counter = $arrS[$counter_type];
													if ($ctype != "ctr")
														$counter = intval($counter);
													$procent = round(($counter * 100) / ${"sum_" . $ctype}, 2);
													$color = $arrS["COLOR"];
													?>
													<tr>
														<td align="right" nowrap><?= $i . "." ?></td>
														<td valign="center">
															<div style="background-color: <?= "#" . $color ?>">
																<img src="/bitrix/images/1.gif"
																	width="12"
																	height="12"
																	border=0></div>
														</td>
														<td align="right"
															nowrap><? echo sprintf("%01.2f", $procent) . "%" ?></td>
														<td nowrap>(<?= $counter ?>)</td>
														<td nowrap><? echo '[<a href="/bitrix/admin/adv_banner_edit.php?ID=' . $arrS["ID"] . '&lang=' . LANGUAGE_ID . '&action=view" title="' . GetMessage("AD_BANNER_VIEW") . '">' . $arrS["ID"] . '</a>] ' . htmlspecialcharsEx($arrS["NAME"]); ?>
														</td>
													</tr>
												<?
												endif;
											endforeach;
											?>
										</table>
									</td>
								</tr>
							</table>
						</div>
					<?
					endif;
				endforeach;
				$viewTabBanner->End();
			endif;
		endif;
		?><br><br><?
	else:
		echo CAdminMessage::ShowMessage(GetMessage("ADV_NO_DATA_DIAGRAM"));
	endif;

	// Диаграммы по группам
	if ($find_group_summa != "Y" && isset($find_group_sid) && is_array($find_group_sid) && count($find_group_sid) > 1):

		$diagram_type = "GROUP";

		$sum_ctr = 0;
		$sum_show = 0;
		$sum_click = 0;
		$sum_visitor = 0;
		foreach ($arrLegend as $keyL => $arrS)
		{
			if ($arrS["COUNTER_TYPE"] == "DETAIL" && $arrS["TYPE"] == $diagram_type)
			{
				$sum_ctr += $arrS["CTR"];
				$sum_show += $arrS["SHOW"];
				$sum_click += $arrS["CLICK"];
				$sum_visitor += $arrS["VISITOR"];
			}
		}

		if ($sum_show > 0 || $sum_click > 0 || $sum_ctr > 0 || $sum_visitor > 0) :

			if (!function_exists("ImageCreate")) :
				echo CAdminMessage::ShowMessage(GetMessage("AD_GD_NOT_INSTALLED") . "<br>");
			else :
				reset($arShow);
				$aTabs = [];
				$i = 0;
				foreach ($arShow as $ctype)
				{
					$counter_type = mb_strtoupper($ctype);
					if (${"sum_" . mb_strtolower($counter_type)} > 0)
					{
						$i++;
						$aTabs[] = [
							"DIV" => "tttab" . $i,
							"TAB" => GetMessage("AD_" . $counter_type . "_DIAGRAM"),
							"TITLE" => GetMessage("AD_GROUP_DIAGRAM_TITLE"),
						];
					}
				}

				reset($arShow);
				$viewTabGroup = new CAdminViewTabControl("viewTabGroup", $aTabs);
				if (count($aTabs) > 0)
					$viewTabGroup->Begin();

				foreach ($arShow as $ctype) :
					$counter_type = mb_strtoupper($ctype);
					if (in_array($ctype, $arShow) && ${"sum_" . $ctype} > 0):
						$viewTabGroup->BeginNextTab();
						?>
						<div class="graph">
							<table cellspacing=0 cellpadding=0 class="graph">
								<tr>
									<td valign="top"><img class="graph"
											src="/bitrix/admin/adv_diagram.php?<?= GetFilterParams($FilterArr) ?>&diagram_type=<? echo $diagram_type ?>&counter_type=<? echo $counter_type ?>"
											width="<? echo $diameter ?>"
											height="<? echo $diameter ?>"></td>
									<td valign="top">
										<table cellpadding=0 cellspacing=0 border=0 class="legend">
											<?
											$i = 0;
											foreach ($arrLegend as $keyL => $arrS) :
												if ($arrS["COUNTER_TYPE"] == "DETAIL" && $arrS["TYPE"] == $diagram_type):
													$i++;
													$counter = $arrS[$counter_type];
													if ($ctype != "ctr")
														$counter = intval($counter);
													$procent = round(($counter * 100) / ${"sum_" . $ctype}, 2);
													$color = $arrS["COLOR"];
													?>
													<tr>
														<td align="right" nowrap><?= $i . "." ?></td>
														<td valign="center">
															<div style="background-color: <?= "#" . $color ?>">
																<img src="/bitrix/images/1.gif"
																	width="12"
																	height="12"
																	border=0></div>
														</td>
														<td align="right"
															nowrap><? echo sprintf("%01.2f", $procent) . "%" ?></td>
														<td nowrap>(<?= $counter ?>)</td>
														<td nowrap><?= $arrS["ID"] ?></td>
													</tr>
												<?
												endif;
											endforeach;
											?>
										</table>
									</td>
								</tr>
							</table>
						</div>
					<?
					endif;
				endforeach;
				$viewTabGroup->End();
			endif;
		endif;
	endif;
endif;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
?>

<?
/** Bitrix Framework */

/**
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 */

define("ADMIN_MODULE_NAME", "scale");

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use \Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

if(!\Bitrix\Main\Loader::includeModule("scale"))
	ShowError(Loc::getMessage("SCALE_GRAPH_MODULE_NOT_INSTALLED"));

if (!$USER->IsAdmin())
	$APPLICATION->AuthForm(Loc::getMessage("SCALE_GRAPH_ACCESS_DENIED"));

$APPLICATION->SetTitle(Loc::getMessage("SCALE_GRAPH_TITLE"));

$APPLICATION->SetAdditionalCSS("/bitrix/js/scale/css/scale-page-style.css");
$APPLICATION->AddHeadScript("/bitrix/js/scale/core.js");
\CUserCounter::Increment($USER->GetID(),'SCALE_GRAPH_VISITS', SITE_ID, false);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if(\Bitrix\Scale\Helper::checkBxEnvVersion())
{
	if(\Bitrix\Scale\Monitoring::isEnabled())
	{
		$serversList = \Bitrix\Scale\ServersData::getList();
		reset($serversList);
		$serverHostname = isset($_REQUEST["SERVER_HOSTNAME"]) ? htmlspecialcharsbx($_REQUEST["SERVER_HOSTNAME"]) : key($serversList);
		$monitoringCategoryId = isset($_REQUEST["GRAPH_CATEGORY"]) ? htmlspecialcharsbx($_REQUEST["GRAPH_CATEGORY"]) : "SYSTEM";
		$period = isset($_REQUEST["PERIOD"]) ? htmlspecialcharsbx($_REQUEST["PERIOD"]) : "day";
		$graphCategories = array();

		$sFilterID = "scale_graph_filter_id";
		$oFilter = new CAdminFilter(
			$sFilterID,
			array(
				Loc::getMessage("SCALE_GRAPH_SELECT_SERVER"),
				Loc::getMessage("SCALE_GRAPH_SELECT_CATEGORY"),
				Loc::getMessage("SCALE_GRAPH_SELECT_PERIOD")
			)
		);

		$graphs = \Bitrix\Scale\GraphData::getList();

		foreach($serversList as $hostname => $server)
		{
			$graphCategories[$hostname] = \Bitrix\Scale\ServersData::getGraphCategories($hostname);

			foreach($graphCategories[$hostname] as $key => $category)
			{
				if(!isset($graphs[$category]))
					continue;

				$graphCategories[$hostname][$category] = isset($graphs[$category]["NAME"]) ? $graphs[$category]["NAME"] : $category;
				unset($graphCategories[$hostname][$key]);
			}
		}

		?>
		<form name="form1" method="GET" action="<?=$APPLICATION->GetCurPage()?>?">
			<?$oFilter->Begin();?>
			<tr valign="center">
				<td class="bx-digit-cell" width="0%" nowrap><?echo Loc::getMessage("SCALE_GRAPH_SELECT_SERVER")?>:</td>
				<td width="0%" nowrap>
					<select id="SERVER_HOSTNAME" name="SERVER_HOSTNAME" onchange="changeGraphCategories();">
						<?foreach($serversList as $hostName => $data):?>
							<option value="<?=htmlspecialcharsbx($hostName)?>"<?=($hostName == $serverHostname ? " selected" : "")?>><?=htmlspecialcharsbx($hostName)?></option>
						<?endforeach;?>
					</select>
				</td>
			</tr>
			<tr valign="center">
				<td class="bx-digit-cell" width="0%" nowrap><?echo Loc::getMessage("SCALE_GRAPH_SELECT_CATEGORY")?>:</td>
				<td width="0%" nowrap>
					<select id="GRAPH_CATEGORY" name="GRAPH_CATEGORY">
						<?foreach($graphCategories[$serverHostname] as $categoryId => $category):?>
							<option value="<?=htmlspecialcharsbx($categoryId)?>"<?=($categoryId == $monitoringCategoryId ? " selected" : "")?>><?=htmlspecialcharsbx($category)?></option>
						<?endforeach;?>
					</select>
				</td>
			</tr>
			<tr valign="center">
				<td class="bx-digit-cell" width="0%" nowrap><?echo Loc::getMessage("SCALE_GRAPH_SELECT_PERIOD")?>:</td>
				<td width="0%" nowrap>
					<select id="PERIOD" name="PERIOD">
						<option value="day"<?=($period == "day" ? " selected" : "")?>><?=Loc::getMessage("SCALE_GRAPH_PERIOD_DAY")?></option>
						<option value="week"<?=($period == "week" ? " selected" : "")?>><?=Loc::getMessage("SCALE_GRAPH_PERIOD_WEEK")?></option>
						<option value="month"<?=($period == "month" ? " selected" : "")?>><?=Loc::getMessage("SCALE_GRAPH_PERIOD_MONTH")?></option>
						<option value="year"<?=($period == "year" ? " selected" : "")?>><?=Loc::getMessage("SCALE_GRAPH_PERIOD_YEAR")?></option>
					</select>
				</td>
			</tr>

			<?$oFilter->Buttons()?>
			<span class="adm-btn-wrap"><input type="submit" class="adm-btn" name="set_filter" value="<?=Loc::getMessage("SCALE_GRAPH_SHOW")?>" title="<?=Loc::getMessage("SCALE_GRAPH_SHOW")?>"></span>
			<?
			$oFilter->End();
			?>
		</form>

		<div class="adm-scale-page-wrap" id="adm-scale-page-wrap">
			<div class="adm-scale-blocks-wrapper" id="adm-scale-blocks-wrapper">
				<?if(\Bitrix\Scale\Monitoring::isDatabaseCreated($serverHostname)):?>
					<div class="bx-scale-graph">
						<div class="bx-scale-graph-category"><?=$graphs[$monitoringCategoryId]["NAME"]?></div>
						<?if( isset($graphs[$monitoringCategoryId]["ITEMS"]) && is_array($graphs[$monitoringCategoryId]["ITEMS"])): ?>
							<?foreach($graphs[$monitoringCategoryId]["ITEMS"] as $param):?>
								<div><img class="adm-scale-graph-img" src="scale_image.php?SERVER=<?=htmlspecialcharsbx($serverHostname)?>&PARAM=<?=$param?>&PERIOD=<?=$period?>"></div>
							<?endforeach;?>
						<?endif;?>
					</div>
				<?else:?>
					<?="<br>".Loc::getMessage("SCALE_GRAPH_MONITORING_DATABASE_CREATING")."."?>
				<?endif;?>
			</div>
		</div><?
	}
	else
	{
		echo Loc::getMessage("SCALE_GRAPH_MONITORING_DISABLED").".";
	}
}
else
{
	echo Loc::getMessage("SCALE_GRAPH_BVM_TOO_OLD").".";
}
?>

<script type="text/javascript">
	BX.Scale.graphCategoriesList = <?=CUtil::PhpToJSObject($graphCategories)?>;

	function changeGraphCategories()
	{
		var srv = BX('SERVER_HOSTNAME');
		var gCats = BX('GRAPH_CATEGORY');

		if(!srv || !gCats)
			return;

		if(BX.Scale.graphCategoriesList[srv.value])
		{
			while (gCats.options.length != 0)
				gCats.options.remove(gCats.options.length - 1);


			for(var i in BX.Scale.graphCategoriesList[srv.value])
			{
				var option = BX.create( 'option',{props: { value: i, text: BX.Scale.graphCategoriesList[srv.value][i]}});
				gCats.add( option );
			}
		}
	}
</script>
<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>
<?php

define("STOP_STATISTICS", true);
define("BX_SECURITY_SHOW_MESSAGE", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

global $USER;

__IncludeLang(__DIR__."/lang/".LANGUAGE_ID."/getdata.php");

if(!check_bitrix_sessid() || !isset($_REQUEST['id']))
{
	CMain::FinalActions();
}

$arGadgetParams = BXGadget::getGadgetSettings($_REQUEST['id'], $_REQUEST['params'] ?? []);

CModule::IncludeModule('socialservices');

$clientId = $arGadgetParams["APP_ID"];
$clientSecret = $arGadgetParams["APP_SECRET"];
$domain = $portalURI = $arGadgetParams["PORTAL_URI"];

?>
<div class="bx-gadgets-planner">
<?php
if($clientId == '' || $clientSecret == '' || $portalURI == '')
{
	CMain::FinalActions();
}

$needAuthorize = false;
$accessToken = '';
$redirectURI = \CHTTP::URN2URI('/bitrix/tools/oauth/bitrix24.php');
$savedPortalURI = CUserOptions::GetOption('socialservices', 'bitrix24_task_planer_gadget_portal', '');
$requestCode = CUserOptions::GetOption('socialservices', 'bitrix24_task_planer_gadget_code', '');

if($savedPortalURI !== $portalURI || $savedPortalURI == '')
{
	$needAuthorize = true;
	CUserOptions::SetOption('socialservices', 'bitrix24_task_planer_gadget_portal', $portalURI, false);
}
if(!preg_match('|^http[s]?|', $portalURI))
	$portalURI = 'https://'.$portalURI;
$objBitrixOAuth = new CSocServBitrixOAuth($clientId, $clientSecret, $portalURI, $redirectURI, $USER->GetID());
$objBitrixOAuth->addScope(array('task', 'calendar'));

$arTasks = $arEvents = array();
if($requestCode <> '')
{
	$accessToken = $objBitrixOAuth->getAccessToken($requestCode);
	$objBitrixOAuth->getEntityOAuth()->saveDataDB();

	CUserOptions::SetOption('socialservices', 'bitrix24_task_planer_gadget_code', '', false);
}
else
{
	$accessToken = $objBitrixOAuth->getStorageToken();
}

if($accessToken != '' && $domain != '' && !$needAuthorize)
{
	$obApp = new CBitrixPHPAppTransport($accessToken, $portalURI);

	$arPlannerTasks = array(
		array('task.planner.getlist', array()),
		array('task.items.getlist', array('ORDER' => array("ID" => 'desc'), 'FILTER' => array('ID' => '$result[0]'))),
	);
	$arTasksBatch = $obApp->batch($arPlannerTasks);

	if(is_array($arTasksBatch) && isset($arTasksBatch["result"]))
	{
		$arTasks = $arTasksBatch["result"];
	}

	$arEvents = $obApp->call('calendar.event.get.nearest', array('maxEventsCount' => '7'));
}

	if((isset($arTasks['result']) && is_array($arTasks['result'])) || (isset($arEvents['result']) && is_array($arEvents['result'])))
	{
		?>
		<?if(is_array($arTasks['result'][1]) && count($arTasks['result'][1]) > 0):?>
		<div class="bx-gadgets-content-padding-rl bx-gadgets-content-padding-t" style="font-weight: bold; font-size: 16; color:rgba(38,38,38,0.68); line-height: 28px;"><?=GetMessage("GD_PLANNER_TASKS");?>
			<span style="float: right; color:rgba(136,136,136,0.67); padding-right: 70px";><?=GetMessage("GD_PLANNER_TASKS_CREATOR");?></span></div>
		<div style="margin: 0 1px 0 1px; border-bottom: 1px solid #D7E0E8;"></div>
		<div class="bx-gadgets-content-padding-rl">
			<table class="bx-gadgets-info-site-table">
				<?
				foreach($arTasks['result'][1] as $num => $task)
				{
					?>
					<tr>
						<td width="80%" align="left" valign="top" style="color: #4F78C3; padding-bottom: 10px; line-height: 18px;"><span>
							<?echo ++$num.'. '.htmlspecialcharsbx($task['TITLE'])."</td><td>".htmlspecialcharsbx($task['CREATED_BY_LAST_NAME']).' '.htmlspecialcharsbx($task['CREATED_BY_NAME']);
							?>
						</td>
					</tr>
				<?
				}
				?>
			</table>
		</div>
	<?endif;?>
		<?if(is_array($arEvents['result']) && count($arEvents['result']) > 0):?>
		<div class="bx-gadgets-content-padding-rl bx-gadgets-content-padding-t" style="font-weight: bold; font-size: 16; color:rgba(38,38,38,0.68); line-height: 28px;"><?=GetMessage("GD_PLANNER_EVENTS");?>
			<span style="float: right; color:rgba(136,136,136,0.67); padding-right: 30px";><?=GetMessage("GD_PLANNER_EVENT_DATE");?></span></div>
		<div style="margin: 0 1px 0 1px; border-bottom: 1px solid #D7E0E8;"></div>
		<div class="bx-gadgets-content-padding-rl">
			<table class="bx-gadgets-info-site-table">
				<?
				foreach($arEvents['result'] as $num => $event)
				{
					$tsFrom = htmlspecialcharsbx($event['DT_FROM']);
					?>
					<tr>
						<td width="80%" align="left" valign="top" style="color: #4F78C3; padding-bottom: 10px; line-height: 18px;"><span>
								<?echo ++$num.'. '.htmlspecialcharsbx($event['NAME'])."</td><td>".$tsFrom;
								?>
					</tr>
				<?
				}
				?>
			</table>
		</div>
	<?endif;?>
		<?if(count($arTasks['result']) <= 0 && count($arEvents['result']) <= 0):?>
		<div class="bx-gadgets-content-padding-rl bx-gadgets-content-padding-t" style="font-weight: bold; line-height: 28px;">
			<?=GetMessage("GD_PLANNER_EMPTY_DATA");?>
		</div>
	<?endif;?>

	<?}
	else
	{
		?>
		<script type="text/javascript">
			function checkOauth()
			{
				var d = document.getElementById('portal').value;
				var protocol = 'https://';
				if(d.indexOf('http://', 0) == 0)
					protocol = 'http://';
				var url = protocol + d.replace(/http[s]?:\/\//, '')
					+ '/oauth/authorize/?client_id=<?=$clientId?>'
					+ '&response_type=code'
					+ '&redirect_uri=' + encodeURIComponent('<?=$redirectURI?>')
					+ '&state=' + encodeURIComponent('<?='check_key='.\CSocServAuthManager::getUniqueKey()?>')
					+ '&_=' + Math.random();
				window.open(url);
			}

		</script>
		<div class="bx-gadgets-content-padding-rl bx-gadgets-content-padding-t">
			<?=GetMessage("GD_PLANNER_URI_PORTAL_INPUT");?>&nbsp;
			https://&nbsp;<input type="text" id="portal" name="portal" value="<?=\Bitrix\Main\Text\HtmlFilter::encode($domain)?>">
			<input type="button" class="adm-btn" onclick="checkOauth();" value="<?=GetMessage("GD_PLANNER_AUTHORIZE");?>">
		</div>
	<?
	}
	?>
</div>
<?php
CMain::FinalActions();

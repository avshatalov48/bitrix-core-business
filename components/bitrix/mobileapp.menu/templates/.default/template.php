<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$APPLICATION->SetPageProperty("BodyClass", "menu-page");
$APPLICATION->AddHeadString("
<style type=\"text/css\">
html { -webkit-text-size-adjust:none; }

.menu-page {
	font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
	margin:0;
	padding:0;
	background-color: #405f7d;
	background-image: url('data:image/png;base64,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');
	background-repeat: repeat;
	background-size: 12px 12px;
}

.menu-items {
	-webkit-tap-highlight-color: transparent;
}

.menu-separator {
	background-repeat: repeat-x;
	height: 24px;
	line-height: 24px;
	border-top: 1px solid #2a3943;
	border-bottom: 1px solid #263640;
	color: #7b8a92;
	font-weight: bold;
	text-shadow: 0 1px 0 #192227;
	font-size: 13px;
	padding-left: 21px;
	background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#202c34), to(#2d3e48));
	background-image: -webkit-linear-gradient(#202c34 0%, #2d3e48 100%);
	background-image: linear-gradient(#202c34 0%, #2d3e48 100%);
	text-transform: uppercase;
}

.menu-item {
	height: 44px;
	line-height: 42px;
	font-size: 16px;
	color: #dde6ea;
	padding: 0 20px 0 30px;
	text-shadow:0 1px 0 #28363e;
	-webkit-tap-highlight-color: transparent;
	border-bottom: 1px solid #d0d0d0;
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
	position: relative;
	border-bottom: 1px solid rgba(0,0,0,.2);
	border-top: 1px solid rgba(255,255,255,.1);
}

.menu-item.menu-item-title {
	text-align: center;
	color: #fff;
	font-weight: bold;
	text-shadow:0 -1px 0 #424242;
	border-bottom: none;
}

.menu-section-groups .menu-item { padding-left: 21px; }

.menu-item:after {
	display: block;
	height: 2px;
	/*background-image: url(images/menu/menu-item-border.png);*/
	background-repeat: repeat-x;
	background-size: 1px 2px;
	width: 100%;
	position: absolute;
	content: '';
	left: 0;
	z-index: -1;
}

.menu-item:last-child:after { background: none; }
.menu-item:before { content: ''; position: absolute; left: 0; height: 44px; width: 60px; }

.menu-item-avatar {
	background-color: #63839c;
	/*background-image: url(images/menu/menu-avatar.png);*/
	background-image: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACwAAAAsCAIAAACR5s1WAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA2ZpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMC1jMDYwIDYxLjEzNDc3NywgMjAxMC8wMi8xMi0xNzozMjowMCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDoxQzAzNTk0Mjk4MjM2ODExOTIzQ0UzMzY5M0ZCNjAwQSIgeG1wTU06RG9jdW1lbnRJRD0ieG1wLmRpZDowRUEzRkQ1MkNEM0UxMUUxQTZCOURCRkUzNzFFQURBMyIgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDo5Q0FGN0U5Q0NEMjkxMUUxQTZCOURCRkUzNzFFQURBMyIgeG1wOkNyZWF0b3JUb29sPSJBZG9iZSBQaG90b3Nob3AgQ1M1IE1hY2ludG9zaCI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOkFFMUExODRBNzQyMDY4MTE4OEM2OEExMTQ2OTFCMDNEIiBzdFJlZjpkb2N1bWVudElEPSJ4bXAuZGlkOjFDMDM1OTQyOTgyMzY4MTE5MjNDRTMzNjkzRkI2MDBBIi8+IDwvcmRmOkRlc2NyaXB0aW9uPiA8L3JkZjpSREY+IDwveDp4bXBtZXRhPiA8P3hwYWNrZXQgZW5kPSJyIj8+dqo5jQAABQRJREFUeNrsWF1PHFUYPl8zszM7sNgPKBawlCpuV0ArYBqVWJNe6ZXx3gt/in/DG+OF8UpNaqKmJFxoiDVBLQUF+mER+mEplGW3uztz5hzfM2dZkd0dZmCJXvBm9iOzM3Oe87zved7nLP7wo4/Rfx0E/Q/iCMR2sH3cg6sf+F9npax+HjYIHI5de9ffqhAkhhcgwCEaeUggqmNjTDAiuBZVFgCAUCikEEhDkS0HUUNAcfgpBRJqODVvDQSwKXhQZkRsUyIPg4nqtLn3Ql/X2+ND2XPPMUrh1NrG5tzS3clrs2v5MiYUql0kyUhsEDBNoggQ3H/39ZF33npNz1PRgdCxTNvE2ND48OCnX03+srSCqKHIik0GScIBkoK7Jr78xqsBDB4WQe2AM6bBPnjvsmMRJDmkCLdcJ3QdBL5/aTRHQ0bqDwj4aeLlwcDzAC/GuKXpCFcBDBP4lRMdbUEQRFx7pqcLLqOmBflTNRsjISzu0pAhCO5BYQRhHTQLk1EAAawRwnC88oy/OlRNnuk+mRs8J4IoEH2nu/t7ulY2ypjKltVETREF56eOd0AuRGTA753PZOBiTQJuIRMKRBB4vg8gotedLmG4Gl6gYK1hQm7nVWL8842Few8eAhURx1axODO3WBMr2cIlGqoVK/HgyndTivHmxzdXp/LFMiVU61XrdEL1SMwMw7DSM7/dfLyxHjSJJ5v5q9Mzhp0mhgVNBcXrHglMDTBh2g427U8+/1JL5K4DTn72xRViOJadpowdgmIqEDA923Yzd+6vf/3tZD0NU99Pz99Zddo7jFQK2phSqniqyWK6GNXEQacoJXYatWfWnuTDmcudiwLWg5VyUrZNDQsWBqqJ2l42h+3p4UIXo1po6BckvGzLHH9lWKvCThATF8csJ/3T78ue4CCX4HyIREIduGq8EoHA/wAICVBnJAq4xfDoyMDw88pG+D7f9VjANDaUHR16cXbx7g+zt0pcEsqU2VEFFGW3WITmEKIzKmTA2x1jLHs2N9AHRcQDXqlwUddBggADMshYbqD3/EDv7NLytfnbBU/oiUCC5A4/HAOEJkHpVJC2yJsXBrP9PTC2V6loxYwQzfBOj1KaO/vsSwM909eXflz4ExEGLVU0kS8WYWGE5Ibk71+66FhmoVjcc/j6WUDWxnP9m1uF+ZV1wiz13GRMAAm+dyHbaxDytPg0un03Cx9zn/Ox8/2/3ly10owQGjRyGE0LEzoQr5RPdjilcinaxUQHJDFlWdwrGSlbLRkUk4nQR6mezCuQRt/3QREOsstTXYeoJkyYTKYToY8CMj3OWZKNTIOA0cFeECSSiJUqPhx6aFQqe04qdWAQQnuMZrLJmnkIvaOaW7w9PpLVO5z9IghuLN4q+SIN+imTKKaiAkrZSs3/8eD6/MLpE5nuU50d7e2uY4PhdV037TgNbyyVyvmtPMw7v1XIF4r3Hz6699djZLnusS5oac0YZQ1pCHeYlJmOnTleMYzVzcLyoyUoESgTlSW1XKV+313PwB8JWw1l0M2pYRqZTst2TdsFvZJNdgBRsk2ZkXLbDFhgaTCusEZArIJw1612OnjXE7HqDEroiQqYAwAhjDHDBAdAoYnAyUS9Q24bCEosQk1mKeOK9L4fh49q3JzV3i8Eh/TOByuTR2quIlkX3XmDbqVIm5T6Xt/olvr/buQBLb+se+i+/xY6+vfuCETc+FuAAQCMJERFG/TKEQAAAABJRU5ErkJggg==');
	background-repeat: no-repeat;
	background-size: 22px 22px;
	border: 1px solid #fff;
	width: 22px;
	height: 22px;
	position: absolute;
	top: 9px;
	left: 21px;
}
.menu-item-selected {
	background: #293841;
	height: 46px;
	margin-top: -2px;
	line-height: 46px;
	border-top: 1px solid rgba(0,0,0,.3);
}
.menu-item-selected:after { background: none;}
.menu-item-selected:before { height: 48px;}
</style>");

?>
<div class="menu-items" id="menu-items">
	<div class="menu-section menu-section-groups">
		<div class="menu-item menu-item-title"><?=$arResult['MENU_TITLE']?></div>
	</div>
<?

$htmlMenu = "";
$arPushParams = array();

foreach ($arResult["MENU"] as $arMenuSection)
{
	if(!isset($arMenuSection['type']) && $arMenuSection['type'] != "section")
		continue;

	$htmlMenu .= '<div class="menu-separator">'.(isset($arMenuSection['text']) ? $arMenuSection['text'] : '').'</div>';

	if(!isset($arMenuSection['items']) || !is_array($arMenuSection['items']))
		continue;

	$htmlMenu .= '<div class="menu-section menu-section-groups">';

	foreach ($arMenuSection['items'] as $arMenuItem)
	{
		$htmlMenu .= '<div class="menu-item';

		if(isset($arMenuItem["class"]))
			$htmlMenu .= ' '.$arMenuItem["class"];

		$htmlMenu .= '"';

		foreach ($arMenuItem as $attrName => $attrVal)
		{
			if($attrName == 'text' || $attrName == 'type' || $attrName == 'class' || $attrName == 'push-param')
				continue;

			$htmlMenu .= ' '.$attrName.'="'.$attrVal.'"';
		}

		$htmlMenu .= '>';

		if(isset($arMenuItem['text']))
			$htmlMenu .= $arMenuItem['text'];

		if(isset($arMenuItem['push-param']) && isset($arMenuItem['data-url']))
		{
			$arPushParams[$arMenuItem['push-param']] = array('data-url' => $arMenuItem['data-url']);

			if(isset($arMenuItem['data-pageid']))
				$arPushParams[$arMenuItem['push-param']]['data-pageid'] = $arMenuItem['data-pageid'];
		}

		$htmlMenu .= '</div>';
	}

	$htmlMenu .= '</div>';
}

echo $htmlMenu;
?>
</div>

<script>

	document.addEventListener("DOMContentLoaded", function() {
		Menu.init({
			currentItem: null,
			ajaxUrl: "<?=$arResult["AJAX_URL"]?>",
			pushParams: <?=CUtil::PhpToJsObject($arPushParams)?>
		});

	}, false);


	<?if($arResult['LOGOUT_REQUEST_URL']):?>
		Menu.logOut =  function()
			{
				if(app.enableInVersion(2))
				{
					app.asyncRequest({ url:"<?=$arResult['LOGOUT_REQUEST_URL']?>"+"&uuid="+device.uuid});
					return app.exec("showAuthForm");
				}
			}
	<?endif;?>

	if(BX.PULL)
	{
		BX.addCustomEvent("onPullExtendWatch", function(data) {
			BX.PULL.extendWatch(data.id);
		});

		BX.addCustomEvent("thisPageWillDie", function(data) {
			BX.PULL.clearWatch(data.page_id);
		});

		BX.addCustomEvent("onPullEvent", function (module_id, command, params)
		{
			if (module_id == 'main' && (command == 'user_authorize' || command == 'user_logout' || command == 'online_list'))
			{
				//app.onCustomEvent('onPullOnline', {'command': command, 'params': params});
			}
			else
			{
				app.onCustomEvent('onPull-'+module_id, {'command': command, 'params': params});
				app.onCustomEvent('onPull', {'module_id': module_id, 'command': command, 'params': params});
			}
		});
	}

	BX.ready( function(){
		Menu.getToken();
		<?
		if(!empty($arPushParams)):?>

			var lastPushParams = null;

			BX.addCustomEvent('onOpenPush', function (params){
				if(params.params)
				{
					lastPushParams = params.params;
					Menu.onOpenPush(params);
					app.onCustomEvent('onOpenPushReadyToSendParams', {head: Menu.getPushParamsHead(lastPushParams)});
				}
			});

			BX.addCustomEvent('onOpenPushReadyForParams', function (params){
				if(params.head && params.head == Menu.getPushParamsHead(lastPushParams))
				{
					app.onCustomEvent('onOpenPushParams', {params: lastPushParams});
					lastPushParams = null;
				}
			});
		<?endif;?>
	});

</script>
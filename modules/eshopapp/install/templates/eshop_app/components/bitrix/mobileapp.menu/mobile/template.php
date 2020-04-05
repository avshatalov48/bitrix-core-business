<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$APPLICATION->SetPageProperty("BodyClass","menu-page");
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
	background-size: 18px;
}

.menu-items {-webkit-tap-highlight-color: transparent}

.menu-separator {
	background-repeat: repeat-x;
	height: 24px;
	line-height: 24px;
	border-top: 1px solid #343434;
	border-bottom: 1px solid #3f3f3f;
	color: #929292;
	font-weight: bold;
	text-shadow: 0 1px 0 #232324;
	font-size: 13px;
	padding-left: 21px;
	background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#343434), to(#3f4040));
	background-image: -webkit-linear-gradient(#343434 0%, #3f4040 100%);
	background-image: linear-gradient(#343434 0%, #3f4040 100%);
	text-transform: uppercase;
}

.menu-item-title {
	text-align: center;
	color: #fff !important;
	font-weight: bold;
	text-shadow:0 -1px 0 #2e2e2f;
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,rgba(114,114,114,1)), color-stop(51%,rgba(68,68,68,1)), color-stop(100%,rgba(68,68,68,1))); /* Chrome,Safari4+ */
	background: -webkit-linear-gradient(top, rgba(114,114,114,1) 0%,rgba(68,68,68,1) 51%,rgba(68,68,68,1) 100%); /* Chrome10+,Safari5.1+ */
	background: linear-gradient(to bottom, rgba(114,114,114,1) 0%,rgba(68,68,68,1) 51%,rgba(68,68,68,1) 100%); /* W3C */
}
.menu-item {
	height: 44px;
	line-height: 42px;
	font-size: 17px;
	color: #fff;
	padding: 0 53px 0 30px;
	-webkit-tap-highlight-color: transparent;
	border-bottom: 1px solid rgba(0,0,0,.20);
	border-top: 1px solid rgba(255,255,255,.1);
	text-shadow:0 1px 0 #2e2f30;
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
	position: relative;
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
	background: #3a3b3d;
	height: 46px;
	margin-top: -2px;
	line-height: 46px;
	border-top: 1px solid #353637;
	border-bottom: 1px solid #353637;
}
.menu-item-selected:after { background: none;}
.menu-item-selected:before { height: 48px;}
</style>");

/*			array(
				"text" => GetMessage("MOBILEAPP_ADMIN"),
				"type" => "item",
				"sort" => "3"$arResult['MENU_TITLE']
			),
*/
if ($USER->IsAuthorized())
{
	$USER_ID = $USER->GetID();

	$arResult["USER_FULL_NAME"] = CUser::FormatName("#NAME# #LAST_NAME#", array(
		"NAME"	 => $USER->GetFirstName(),
		"LAST_NAME" 	=> $USER->GetLastName(),
		"SECOND_NAME" => 	$USER->GetSecondName(),
		"LOGIN" => $USER->GetLogin()
	));

	$arResult["USER"] = $USER->GetByID($USER_ID)->GetNext();
}
?>
<div class="menu-items" id="menu-items">
	<div class="menu-section  menu-section-groups" id="auth_block">
		<?if (is_array($arResult["USER"])):?>
		<div id="user_name" class="menu-item"><?=$arResult["USER_FULL_NAME"]?></div>
		<?else:?>
		<div class="menu-item" id="auth"  data-url="<?=SITE_DIR?>eshop_app/auth/"><?=GetMessage("MB_AUTH");?></div>
		<?endif?>
	</div>
<?

$htmlMenu = "";

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
			if($attrName == 'text' || $attrName == 'type' || $attrName == 'class')
				continue;

			$htmlMenu .= ' '.$attrName.'="'.$attrVal.'"';
		}

		$htmlMenu .= '>';

		if(isset($arMenuItem['text']))
			$htmlMenu .= $arMenuItem['text'];

		$htmlMenu .= '</div>';
	}

	$htmlMenu .= '</div>';
}

echo $htmlMenu;
?>
	<!-- Exit -->
	<div class="menu-separator"></div>
	<div id="logout_block" class="menu-section  menu-section-groups" <?if (!is_array($arResult["USER"])):?>style="display:none"<?endif?>>
		<div class="menu-item" onclick="logout();"><?=GetMessage("MB_EXIT");?></div>
	</div>
</div>

<script type="text/javascript">
	BX.addCustomEvent("onAuthSuccess", function(data) {
		BX.remove(BX("auth"));
		if (BX("user_name"))
			BX.remove(BX("user_name"));
		var user_div = BX.create('DIV', {
			props: {
				id :  "user_name"
			},
			html : data.user_name ,
			attrs : {
				class : "menu-item"
			}
		});
		BX('auth_block').appendChild(user_div);
		BX('logout_block').style.display = "block";
		//app.removeAllCache();
		if (data.open_left)
			app.openLeft();
	});

	function logout()
	{
		BX.ajax({
		//	timeout:   30,
			method:   'POST',
			url: '<?=SITE_DIR?>eshop_app/?logout=yes',
			processData: false,
			onsuccess: function(reply){
				BX.remove(BX("user_name"));
				var auth_div = BX.create('DIV', {
					props: {
						id :  "auth"
					},
					html : '<?=GetMessage("MB_AUTH");?>',
					attrs : {
						class : "menu-item",
						'data-url' : "/eshop_app/auth/"
					}
				});
				BX('auth_block').appendChild(auth_div);
				BX('logout_block').style.display = "none";
				app.removeAllCache();
			}
		});
	}

	function catalogSections()
	{
		app.openBXTable({
			url: '/eshop_app/catalog/sections.php',
			isroot: true,
			TABLE_SETTINGS : {
				cache : true,
				use_sections : true,
				searchField : false,
				showtitle : true,
				name : '<?=GetMessage("MB_SECTIONS")?>',
				button:
				{
					type:    'basket',
					style:   'custom',
					callback: function()
					{
						app.openNewPage('<?=SITE_DIR?>eshop_app/personal/cart/');
					}
				}
			}
		});
		app.closeMenu();
	}


	document.addEventListener("DOMContentLoaded", function() {
		Menu.init(null);
	}, false);

	Menu = {
		currentItem : null,

		init : function(currentItem)
		{
			this.currentItem = currentItem;
			var items = document.getElementById("menu-items");
			var that = this;
			items.addEventListener("click", function(event) {that.onItemClick(event); }, false);
		},

		onItemClick : function(event)
		{
			var target = event.target;
			if (target && target.nodeType && target.nodeType == 1 && BX.hasClass(target, "menu-item"))
			{
				if (this.currentItem != null)
					this.unselectItem(this.currentItem);
				this.selectItem(target);

				var url = target.getAttribute("data-url");
				var pageId = target.getAttribute("data-pageid");

				if(BX.type.isNotEmptyString(url) && BX.type.isNotEmptyString(pageId))
					app.loadPage(url, pageId);
				else if(BX.type.isNotEmptyString(url))
					app.loadPage(url);

				this.currentItem = target;
			}

		},

		selectItem : function(item)
		{
			if (!BX.hasClass(item, "menu-item-selected"))
				BX.addClass(item, "menu-item-selected");
		},

		unselectItem : function(item)
		{
			BX.removeClass(item,"menu-item-selected");
		},

		logOut :  function()
		{
			if(app.enableInVersion(2))
			{
				app.asyncRequest({ url:"<?=$arResult['LOGOUT_REQUEST_URL']?>"+"&uuid="+device.uuid});
				return app.exec("showAuthForm");
			}
		}
	}
</script>

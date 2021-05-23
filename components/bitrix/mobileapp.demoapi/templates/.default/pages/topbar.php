<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<button class="styled-button" onclick="BXMobileDemoApi.topBar.showTitle()"><i
		class="fa fa-pencil"></i><?= GetMessage("MB_DEMO_TITLE_SHOW_TEXT"); ?>
</button>
<button class="styled-button" onclick="BXMobileDemoApi.topBar.setDetail();"><i
		class="fa fa-paint-brush"></i><?= GetMessage("MB_DEMO_TITLE_ADD_DETAIL"); ?>
</button>
<button class="styled-button" onclick="BXMobileDemoApi.topBar.setIcon();"><i
		class="fa fa-picture-o"></i><?= GetMessage("MB_DEMO_TITLE_ADD_ICON"); ?>
</button>
<button class="styled-button" onclick="BXMobileDemoApi.topBar.setTitleCallback();"><i
		class="fa fa-hand-o-up"></i><?= GetMessage("MB_DEMO_TITLE_ADD_CALLBACK"); ?>
</button>
<button class="styled-button" onclick="colorSheet.show();"><i
		class="fa fa-eyedropper"></i><?= GetMessage("MB_DEMO_TITLE_CHANGE_COLOR"); ?></button>

<button class="styled-button" onclick="BXMobileDemoApi.topBar.resetTitle();"><i
		class="fa fa-refresh"></i> <?= GetMessage("MB_DEMO_TITLE_HIDE"); ?>
</button>
<script>
	var colorSheet = new BXMobileApp.UI.ActionSheet(
		{
			buttons: [
				{
					title: "<?=GetMessage("MB_DEMO_TITLE_CHANGE_RED");?>",
					callback: function ()
					{
						BXMobileDemoApi.topBar.setColor("#fb0000");
					}
				},
				{
					title: "<?=GetMessage("MB_DEMO_TITLE_CHANGE_BLUE");?>",
					callback: function ()
					{
						BXMobileDemoApi.topBar.setColor("#3962C9");
					}
				},
				{
					title: "<?=GetMessage("MB_DEMO_TITLE_CHANGE_YELLOW");?>",
					callback: function ()
					{
						BXMobileDemoApi.topBar.setColor("#ffff00");
					}
				},
				{
					title: "<?=GetMessage("MB_DEMO_TITLE_CHANGE_GREEN");?>",
					callback: function ()
					{
						BXMobileDemoApi.topBar.setColor("#3EC940");
					}
				}
			]
		},
		"Color"
	)
</script>


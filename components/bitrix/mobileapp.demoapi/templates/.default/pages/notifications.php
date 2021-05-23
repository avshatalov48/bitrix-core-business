<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<button class="styled-button" onclick="BXMobileDemoApi.notifications.loaderNotifyBar()"><i class="fa fa-bell-o"></i>
	<?=GetMessage("MB_DEMO_NF_LOADER");?>

</button>
<button class="styled-button" onclick="BXMobileDemoApi.notifications.fiftyPercentAlphaNotifyBar()"><i
		class="fa fa-bell-o"></i>
	<?=GetMessage("MB_DEMO_NF_ALPHA");?>

</button>
<button class="styled-button" onclick="BXMobileDemoApi.notifications.MultilineNotifyBar()"><i class="fa fa-bell-o"></i>
	<?=GetMessage("MB_DEMO_NF_MULTILINE");?>

</button>
<button class="styled-button" onclick="BXMobileDemoApi.notifications.MultilineAndImageNotifyBar()"><i
		class="fa fa-bell-o"></i>
	<?=GetMessage("MB_DEMO_NF_MULTILINE_IMAGE");?>

</button>

<button class="styled-button" onclick="BXMobileDemoApi.notifications.actionNotifyBar()"><i class="fa fa-bell-o"></i>
	<?=GetMessage("MB_DEMO_NF_CALLBACK");?>

</button>
<button class="styled-button" onclick="BXMobileDemoApi.notifications.actionNotifyBarMulti()"><i class="fa fa-bell-o"></i>
	<?=GetMessage("MB_DEMO_NF_MANY");?>

</button>


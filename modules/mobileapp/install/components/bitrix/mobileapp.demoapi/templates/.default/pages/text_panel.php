<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
} ?>
<button class="styled-button" onclick="BXMobileDemoApi.textPanel.show()"><i
		class="fa fa-comment-o"></i><?= GetMessage("MB_DEMO_TEXT_PANEL_SHOW"); ?>
</button>
<button class="styled-button" onclick="BXMobileDemoApi.textPanel.setText('<?= GetMessage("MB_DEMO_TEXT_PANEL_SET_TEXT_VALUE"); ?>')"><i
		class="fa fa-align-justify"></i><?= GetMessage("MB_DEMO_TEXT_PANEL_SET_TEXT"); ?>
</button>
<button class="styled-button" onclick="BXMobileDemoApi.textPanel.setPlusAction()"><i class="fa fa-plus"></i>
	<?= GetMessage("MB_DEMO_TEXT_PANEL_ADD_PLUS"); ?>
</button>



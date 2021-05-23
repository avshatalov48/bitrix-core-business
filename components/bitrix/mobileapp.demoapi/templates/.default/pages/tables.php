<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<button class="styled-button" onclick="BXMobileDemoApi.lists.openListMarkModeSingle();"><i
		class="fa fa-list"></i><?= GetMessage("MB_DEMO_LIST_SINGLE_CHOSE"); ?>
</button>
<button class="styled-button" onclick="BXMobileDemoApi.lists.openListMarkModeMultiple();"><i
		class="fa fa-list"></i><?= GetMessage("MB_DEMO_LIST_MULTI_CHOSE"); ?>
</button>
<button class="styled-button" onclick="BXMobileDemoApi.lists.openListSelected();"><i
		class="fa fa-list"></i><?= GetMessage("MB_DEMO_LIST_MULTI_CHOSE_SELECTED"); ?>
</button>
<button class="styled-button" onclick="BXMobileDemoApi.lists.openListSection();"><i
		class="fa fa-list"></i><?= GetMessage("MB_DEMO_LIST_SECTION"); ?></button>
<button class="styled-button" onclick="BXMobileDemoApi.lists.openListSectionWithAlphabet();"><i
		class="fa fa-list"></i><?= GetMessage("MB_DEMO_LIST_ALPHABET"); ?>
</button>
<button class="styled-button" onclick="BXMobileDemoApi.lists.openListNestedTable();"><i
		class="fa fa-list"></i><?= GetMessage("MB_DEMO_LIST_TREE"); ?></button>
<button class="styled-button" onclick="BXMobileDemoApi.lists.openModalList();"><i
		class="fa fa-list"></i><?= GetMessage("MB_DEMO_LIST_MODAL"); ?></button>

<script>
	BXMPage.getTitle().setText("<?=GetMessage("MB_DEMO_LISTS");?>");
	BXMPage.getTitle().show();
</script>


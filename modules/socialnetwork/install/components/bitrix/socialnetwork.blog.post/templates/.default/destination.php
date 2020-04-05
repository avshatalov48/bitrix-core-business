<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

?>
<script>
	BX.message({
		BX_FPD_LINK_1:'<?=GetMessageJS("MPF_DESTINATION_1")?>',
		BX_FPD_LINK_2:'<?=GetMessageJS("MPF_DESTINATION_2")?>'
	});

	var socBPDest = {
		shareUrl : '/bitrix/urlrewrite.php?SEF_APPLICATION_CUR_PAGE_URL=<?=str_replace("%23", "#", urlencode($arParams["PATH_TO_POST"]))?>',
		department : <?=(empty($arResult["FEED_DESTINATION"]['DEPARTMENT'])? '{}': CUtil::PhpToJSObject($arResult["FEED_DESTINATION"]['DEPARTMENT']))?>,
		departmentRelation : {},
		relation : {}
	};

	<?if(empty($arResult["FEED_DESTINATION"]['DEPARTMENT_RELATION']))
	{
		?>
		for(var iid in socBPDest.department)
		{
			var p = socBPDest.department[iid]['parent'];
			if (!socBPDest.relation[p])
				socBPDest.relation[p] = [];
			socBPDest.relation[p][socBPDest.relation[p].length] = iid;
		}
		function makeDepartmentTree(id, relation)
		{
			var arRelations = {};
			if (relation[id])
			{
				for (var x in relation[id])
				{
					var relId = relation[id][x];
					var arItems = [];
					if (relation[relId] && relation[relId].length > 0)
						arItems = makeDepartmentTree(relId, relation);

					arRelations[relId] = {
						id: relId,
						type: 'category',
						items: arItems
					};
				}
			}

			return arRelations;
		}
		socBPDest.departmentRelation = makeDepartmentTree('DR0', socBPDest.relation);
		<?
	}
	else
	{
		?>socBPDest.departmentRelation = <?=CUtil::PhpToJSObject($arResult["FEED_DESTINATION"]['DEPARTMENT_RELATION'])?>;<?
	}
	?>
</script>
<div class="feed-add-post-destination-block feed-add-post-destination-block-post" id="destination-sharing" style="display:none;">
	<form action="" name="blogShare" id="blogShare" method="POST" onsubmit="return false;">
	<div class="feed-add-post-destination-title"><?=GetMessage("MPF_DESTINATION")?></div>
	<div class="feed-add-post-destination-wrap" id="feed-add-post-destination-container-post">
		<span id="feed-add-post-destination-item-post"></span>
		<span class="feed-add-destination-input-box" id="feed-add-post-destination-input-box-post">
			<input type="text" value="" class="feed-add-destination-inp" id="feed-add-post-destination-input-post">
		</span>
		<a href="#" class="feed-add-destination-link" id="bx-destination-tag-post"><?=GetMessage("MPF_DESTINATION_1")?></a>
	</div>
	<input type="hidden" name="post_id" id="sharePostId" value="">
	<input type="hidden" name="user_id" id="shareUserId" value="">
	<input type="hidden" name="act" value="share">
	<?=bitrix_sessid_post()?>
	<div class="feed-add-post-buttons-post">
		<button id="sharePostSubmitButton" class="ui-btn ui-btn-lg ui-btn-primary" onclick="sharingPost()"><?=GetMessage("BLOG_SHARE_ADD")?></button>
		<button class="ui-btn ui-btn-lg ui-btn-link" onclick="closeSharing();"><?=GetMessage("BLOG_SHARE_CANCEL")?></button>
	</div>
	</form>
</div>
<script type="text/javascript">
	var BXSocNetLogDestinationFormNamePost = '<?=randString(6)?>';
	BXSocNetLogDestinationDisableBackspace = null;
	BX.SocNetLogDestination.init({
		name : BXSocNetLogDestinationFormNamePost,
		searchInput : BX('feed-add-post-destination-input-post'),
		extranetUser :  <?=($arResult["FEED_DESTINATION"]["EXTRANET_USER"] == 'Y'? 'true': 'false')?>,
		bindMainPopup : { 'node' : BX('feed-add-post-destination-container-post'), 'offsetTop' : '5px', 'offsetLeft': '15px'},
		bindSearchPopup : { 'node' : BX('feed-add-post-destination-container-post'), 'offsetTop' : '5px', 'offsetLeft': '15px'},
		callback : {
			select : BXfpdPostSelectCallback,
			unSelect : BX.delegate(BX.SocNetLogDestination.BXfpUnSelectCallback, {
				formName: window.BXSocNetLogDestinationFormNamePost,
				inputContainerName: 'feed-add-post-destination-item-post',
				inputName: 'feed-add-post-destination-input-post',
				tagInputName: 'bx-destination-tag-post',
				tagLink1: BX.message('BX_FPD_LINK_1'),
				tagLink2: BX.message('BX_FPD_LINK_2'),
				undeleteClassName: 'feed-add-post-destination-undelete'
			}),
			openDialog : BX.delegate(BX.SocNetLogDestination.BXfpOpenDialogCallback, {
				inputBoxName: 'feed-add-post-destination-input-box-post',
				inputName: 'feed-add-post-destination-input-post',
				tagInputName: 'bx-destination-tag-post'
			}),
			closeDialog : BX.delegate(BX.SocNetLogDestination.BXfpCloseDialogCallback, {
				inputBoxName: 'feed-add-post-destination-input-box-post',
				inputName: 'feed-add-post-destination-input-post',
				tagInputName: 'bx-destination-tag-post'
			}),
			openSearch : BX.delegate(BX.SocNetLogDestination.BXfpOpenDialogCallback, {
				inputBoxName: 'feed-add-post-destination-input-box-post',
				inputName: 'feed-add-post-destination-input-post',
				tagInputName: 'bx-destination-tag-post'
			})
		},
		items : {
			users : <?=(empty($arResult["FEED_DESTINATION"]['USERS'])? '{}': CUtil::PhpToJSObject($arResult["FEED_DESTINATION"]['USERS']))?>,
			emails: <?=(empty($arResult["FEED_DESTINATION"]['EMAILS'])? '{}': CUtil::PhpToJSObject($arResult["FEED_DESTINATION"]['EMAILS']))?>,
			crmemails: <?=(empty($arResult["FEED_DESTINATION"]['CRMEMAILS'])? '{}': CUtil::PhpToJSObject($arResult["FEED_DESTINATION"]['CRMEMAILS']))?>,
			groups : <?=(
				$arResult["FEED_DESTINATION"]["EXTRANET_USER"] == 'Y'
				|| (array_key_exists("DENY_TOALL", $arResult["FEED_DESTINATION"]) && $arResult["FEED_DESTINATION"]["DENY_TOALL"])
					? '{}'
					: "{'UA' : {'id':'UA','name': '".(!empty($arResult["FEED_DESTINATION"]['DEPARTMENT']) ? GetMessageJS("MPF_DESTINATION_3"): GetMessageJS("MPF_DESTINATION_4"))."'}}"
				)?>,
			sonetgroups : <?=(empty($arResult["FEED_DESTINATION"]['SONETGROUPS'])? '{}': CUtil::PhpToJSObject($arResult["FEED_DESTINATION"]['SONETGROUPS']))?>,
			department : socBPDest.department,
			departmentRelation : socBPDest.departmentRelation
		},
		itemsLast : {
			users : <?=(empty($arResult["FEED_DESTINATION"]['LAST']['USERS'])? '{}': CUtil::PhpToJSObject($arResult["FEED_DESTINATION"]['LAST']['USERS']))?>,
			emails: <?=(empty($arResult["FEED_DESTINATION"]['LAST']['EMAILS'])? '{}': CUtil::PhpToJSObject($arResult["FEED_DESTINATION"]['LAST']['EMAILS']))?>,
			crmemails: <?=(empty($arResult["FEED_DESTINATION"]['LAST']['CRMEMAILS'])? '{}': CUtil::PhpToJSObject($arResult["FEED_DESTINATION"]['LAST']['CRMEMAILS']))?>,
			sonetgroups : <?=(empty($arResult["FEED_DESTINATION"]['LAST']['SONETGROUPS'])? '{}': CUtil::PhpToJSObject($arResult["FEED_DESTINATION"]['LAST']['SONETGROUPS']))?>,
			department : <?=(empty($arResult["FEED_DESTINATION"]['LAST']['DEPARTMENT'])? '{}': CUtil::PhpToJSObject($arResult["FEED_DESTINATION"]['LAST']['DEPARTMENT']))?>,
			groups : <?=(
				$arResult["FEED_DESTINATION"]["EXTRANET_USER"] == 'Y'
				|| (array_key_exists("DENY_TOALL", $arResult["FEED_DESTINATION"]) && $arResult["FEED_DESTINATION"]["DENY_TOALL"])
					? '{}'
					: "{'UA':true}"
			)?>
		},
		itemsSelected : '{}',
		destSort : <?=CUtil::PhpToJSObject($arResult["DEST_SORT"])?>,
		useClientDatabase : <?=(!isset($arResult["bPublicPage"]) || !$arResult["bPublicPage"] ? 'true' : 'false'); ?>,
		allowAddUser: <?=($arResult["ALLOW_EMAIL_INVITATION"] ? 'true' : 'false'); ?>,
		allowAddCrmContact: <?=($arResult["ALLOW_EMAIL_INVITATION"] && CModule::IncludeModule('crm') && CCrmContact::CheckCreatePermission() ? 'true' : 'false'); ?>,
		showVacations: true,
		usersVacation : <?=(empty($arResult["FEED_DESTINATION"]['USERS_VACATION'])? '{}': CUtil::PhpToJSObject($arResult["FEED_DESTINATION"]['USERS_VACATION']))?>
	});
	BX.bind(BX('feed-add-post-destination-input-post'), 'keyup', BX.delegate(BX.SocNetLogDestination.BXfpSearch, {
		formName: BXSocNetLogDestinationFormNamePost,
		inputName: 'feed-add-post-destination-input-post',
		tagInputName: 'bx-destination-tag-post'
	}));
	BX.bind(BX('feed-add-post-destination-input-post'), 'keydown', BX.delegate(BX.SocNetLogDestination.BXfpSearchBefore, {
		formName: BXSocNetLogDestinationFormNamePost,
		inputName: 'feed-add-post-destination-input-post'
	}));
	BX.bind(BX('feed-add-post-destination-input-post'), 'blur', BX.delegate(BX.SocNetLogDestination.BXfpBlurInput, {
		inputBoxName: 'feed-add-post-destination-input-box-post',
		tagInputName: 'bx-destination-tag-post'
	}));
	BX.bind(BX('bx-destination-tag-post'), 'click', function(e){BX.SocNetLogDestination.openDialog(BXSocNetLogDestinationFormNamePost); BX.PreventDefault(e); });
	BX.bind(BX('feed-add-post-destination-container-post'), 'click', function(e){BX.SocNetLogDestination.openDialog(BXSocNetLogDestinationFormNamePost); BX.PreventDefault(e); });
</script>
<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!function_exists("__mpl_get_avatar"))
{
	function __mpl_get_avatar()
	{
		global $USER;
		static $avatar = null;
		if ($avatar == null)
		{
			$avatar = '/bitrix/images/1.gif';
			if ($USER->IsAuthorized())
			{
				$u = CUser::GetByID($USER->GetID())->Fetch();
				if (
					intval($u["PERSONAL_PHOTO"]) <= 0
					&& \Bitrix\Main\ModuleManager::isModuleInstalled('socialnetwork')
				)
				{
					switch ($u["PERSONAL_GENDER"])
					{
						case "M":
							$suffix = "male";
							break;
						case "F":
							$suffix = "female";
							break;
						default:
							$suffix = "unknown";
					}
					$u["PERSONAL_PHOTO"] = COption::GetOptionInt("socialnetwork", "default_user_picture_".$suffix, false, SITE_ID);
				}

				if ($u["PERSONAL_PHOTO"])
				{
					$res = CFile::ResizeImageGet(
						$u["PERSONAL_PHOTO"],
						array('width' => 100, 'height' => 100),
						BX_RESIZE_IMAGE_EXACT,
						false,
						false,
						true
					);
					if ($res["src"])
					{
						$avatar = $res["src"];
					}
				}
			}
		}
		return $avatar;
	}
}
?>
<script type="text/javascript">
<? if (IsModuleInstalled("im")): ?>
if (window.SPC)
{
	SPC.notifyManagerShow();
}
<? endif ?>

<? if (IsModuleInstalled("socialnetwork")): ?>
if (BX.CommentAux)
{
	BX.CommentAux.init({
		currentUserSonetGroupIdList: <?=CUtil::PhpToJSObject(\Bitrix\Socialnetwork\ComponentHelper::getUserSonetGroupIdList($USER->GetID(), SITE_ID))?>,
		mobile: false,
		publicSection: <?=(isset($arParams["bPublicPage"]) && $arParams["bPublicPage"] ? 'true' : 'false')?>,
		currentExtranetUser: <?=($arResult["currentExtranetUser"] ? 'true' : 'false')?>,
		availableUsersList: <?=CUtil::PhpToJSObject($arResult["availableUsersList"])?>,
	});
}
<? endif ?>

BX.message({
	MPL_HAVE_WRITTEN : ' <?=GetMessageJS("MPL_HAVE_WRITTEN")?>', // space here is important
	MPL_HAVE_WRITTEN_M : ' <?=GetMessageJS("MPL_HAVE_WRITTEN_M")?>',
	MPL_HAVE_WRITTEN_F : ' <?=GetMessageJS("MPL_HAVE_WRITTEN_F")?>',
	B_B_MS_LINK : '<?=GetMessageJS("B_B_MS_LINK2")?>',
	MPL_MES_HREF : '<?=GetMessageJS("MPL_MES_HREF")?>',
	BPC_MES_EDIT : '<?=GetMessageJS("BPC_MES_EDIT")?>',
	BPC_MES_HIDE : '<?=GetMessageJS("BPC_MES_HIDE")?>',
	BPC_MES_SHOW : '<?=GetMessageJS("BPC_MES_SHOW")?>',
	BPC_MES_DELETE : '<?=GetMessageJS("BPC_MES_DELETE")?>',
	BPC_MES_DELETE_POST_CONFIRM : '<?=GetMessageJS("BPC_MES_DELETE_POST_CONFIRM")?>',
	BPC_MES_CREATE_TASK : '<?=GetMessageJS("BPC_MES_CREATE_TASK")?>',
<?/* deprecated ?>	MPL_RECORD_TEMPLATE : '<?=CUtil::JSEscape($template)?>',<?*/?>
	JERROR_NO_MESSAGE : '<?=GetMessageJS("JERROR_NO_MESSAGE")?>',
	BLOG_C_HIDE : '<?=GetMessageJS("BLOG_C_HIDE")?>',
	MPL_IS_EXTRANET_SITE: '<?=(CModule::IncludeModule("extranet") && CExtranet::IsExtranetSite() ? 'Y' : 'N')?>',
	JQOUTE_AUTHOR_WRITES : '<?=GetMessageJS("JQOUTE_AUTHOR_WRITES")?>',
	FC_ERROR : '<?=GetMessageJS("B_B_PC_COM_ERROR")?>',
	MPL_SAFE_EDIT : '<?=GetMessageJS('MPL_SAFE_EDIT')?>',
	MPL_ERROR_OCCURRED : '<?=GetMessageJS('MPL_ERROR_OCCURRED')?>',
	MPL_CLOSE : '<?=GetMessageJS('MPL_CLOSE')?>',
	MPL_LINK_COPIED : '<?=GetMessageJS('MPL_LINK_COPIED')?>'
	<?
		if (IsModuleInstalled("socialnetwork"))
		{
			?>
			, MPL_WORKGROUPS_PATH : '<?=CUtil::JSEscape(COption::GetOptionString("socialnetwork", "workgroups_page", SITE_DIR."workgroups/", SITE_ID))?>'
			<?
		}
	?>
	});
</script>
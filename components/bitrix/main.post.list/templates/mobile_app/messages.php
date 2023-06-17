<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * @var string $template
 * @var string $thumb
*/
?>
<script type="text/javascript">
<? if (IsModuleInstalled("socialnetwork")): ?>
BX.ready(function()
{
	if (BX.CommentAux)
	{
		BX.	CommentAux.init({
			currentUserSonetGroupIdList: <?=CUtil::PhpToJSObject(\Bitrix\Socialnetwork\ComponentHelper::getUserSonetGroupIdList($USER->GetID(), SITE_ID))?>,
			mobile: true
		});
	}
});
<? endif ?>
BX.message({
	BPC_MES_EDIT : "<?=GetMessageJS("BPC_MES_EDIT")?>",
	BPC_MES_HIDE : "<?=GetMessageJS("BPC_MES_HIDE")?>",
	BPC_MES_SHOW : "<?=GetMessageJS("BPC_MES_SHOW")?>",
	BPC_MES_VOTE : "<?=GetMessageJS("BPC_MES_VOTE")?>",
	BPC_MES_VOTE1 : "<?=GetMessageJS("BPC_MES_VOTE1")?>",
	BPC_MES_VOTE2 : "<?=GetMessageJS("BPC_MES_VOTE2")?>",
	BPC_MES_DELETE : "<?=GetMessageJS("BPC_MES_DELETE")?>",
	BPC_MES_CREATETASK : "<?=GetMessageJS("BPC_MES_CREATETASK")?>",
	BPC_MES_COPYLINK : "<?=GetMessageJS("BPC_MES_COPYLINK")?>",
	BPC_MES_RESULT_V2 : "<?=GetMessageJS("BPC_MES_RESULT_V2")?>",
	MPL_RECORD_TEMPLATE : '<?=CUtil::JSEscape($template)?>',
	MPL_RECORD_THUMB : '<?=CUtil::JSEscape($thumb)?>',
	FC_ERROR : '<?=GetMessageJS("B_B_PC_COM_ERROR")?>',
	BLOG_C_REPLY : '<?=GetMessageJS("BLOG_C_REPLY")?>',
	BLOG_C_HIDE : '<?=GetMessageJS("BLOG_C_HIDE")?>',
	INCORRECT_SERVER_RESPONSE : '<?=GetMessageJS('INCORRECT_SERVER_RESPONSE')?>',
	INCORRECT_SERVER_RESPONSE_2 : '<?=GetMessageJS('INCORRECT_SERVER_RESPONSE_2')?>'
	});
</script>
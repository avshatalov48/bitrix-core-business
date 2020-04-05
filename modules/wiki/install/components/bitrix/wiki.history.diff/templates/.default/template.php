<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();?>

<div id="wiki-post">

<? if(!empty($arResult['FATAL_MESSAGE'])):
	?>
	<div class="wiki-errors wiki-note-box wiki-note-error">
		<div class="wiki-error-text">
			<?=$arResult['FATAL_MESSAGE']?>
		</div>
	</div>
	<?
else:
	if ($arResult['SOCNET']) :
		$APPLICATION->IncludeComponent('bitrix:main.user.link',
			'',
			array(
				'AJAX_ONLY' => 'Y',
				'PATH_TO_SONET_USER_PROFILE' => str_replace('#user_id#', '#ID#', $arResult['PATH_TO_USER']),
				'PATH_TO_SONET_MESSAGES_CHAT' => $arResult['PATH_TO_SONET_MESSAGES_CHAT'],
				'NAME_TEMPLATE' => $arResult['NAME_TEMPLATE'],
				'SHOW_LOGIN' => $arResult['SHOW_LOGIN'],
				'PATH_TO_CONPANY_DEPARTMENT' => $arResult['PATH_TO_CONPANY_DEPARTMENT'],
				'PATH_TO_VIDEO_CALL' => $arResult['PATH_TO_VIDEO_CALL']
			),
			$component,
			array('HIDE_ICONS' => 'Y')
		);
	endif;

?>
	<div id="wiki-post-content">
	<span> <?=GetMessage('WIKI_VERSION_FROM')?> <a href="<?=$arResult['VERSION_DIFF']['SHOW_LINK']?>"><?=$arResult['VERSION_DIFF']['MODIFIED']?></a>
	<? if (!empty($arResult['VERSION_DIFF']['USER_LINK'])): ?>
		<a href="<?=$arResult['VERSION_DIFF']['USER_LINK']?>" id="anchor_<?=$arResult['VERSION_DIFF']['ID']?>"><?=$arResult['VERSION_DIFF']['USER_LOGIN']?></a>
	<? else: ?>
		<?=$arResult['VERSION_DIFF']['USER_LOGIN']?>
	<? endif; ?>
	<hr />
	<span> <?=GetMessage('WIKI_DIFF_VERSION_FROM')?> <a href="<?=$arResult['VERSION_OLD']['SHOW_LINK']?>"><?=$arResult['VERSION_OLD']['MODIFIED']?></a>
	<? if (!empty($arResult['VERSION_OLD']['USER_LINK'])): ?>
		<a href="<?=$arResult['VERSION_OLD']['USER_LINK']?>" id="anchor_<?=$arResult['VERSION_OLD']['ID']?>"><?=$arResult['VERSION_OLD']['USER_LOGIN']?></a>
	<? else: ?>
		<?=$arResult['VERSION_OLD']['USER_LOGIN']?>
	<? endif; ?>
	(<a href="<?=$arResult['CANCEL_LINK']?>"><?=GetMessage('WIKI_RESTORE_TO_CURRENT')?></a>)
	<hr />
	<b><?=GetMessage('WIKI_DIFF_TITLE')?></b><br/>
	<?=$arResult['DIFF_NAME']?>
	<hr />
	<b><?=GetMessage('WIKI_DIFF_TEXT')?></b><br/>
	<?=$arResult['DIFF']?>
	</div>
	<? if ($arResult['SOCNET']) :?>
	<script type="text/javascript">
		BX.tooltip(<?=$arResult['VERSION_OLD']["USER_ID"]?>, "anchor_<?=$arResult['VERSION_OLD']['ID']?>", "<?=CUtil::JSEscape($arResult["AJAX_PAGE"])?>");
		BX.tooltip(<?=$arResult['VERSION_DIFF']["USER_ID"]?>, "anchor_<?=$arResult['VERSION_DIFF']['ID']?>", "<?=CUtil::JSEscape($arResult["AJAX_PAGE"])?>");
	</script>
	<? endif;?>
<? endif;?>
</div>

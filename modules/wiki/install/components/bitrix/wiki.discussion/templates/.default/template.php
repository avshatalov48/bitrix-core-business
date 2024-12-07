<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

if(!empty($arResult['FATAL_MESSAGE'])):
	?>
	<div class="wiki-errors">
		<div class="wiki-error-text">
			<?=$arResult['FATAL_MESSAGE']?>
		</div>
	</div>
	<?
else:
	?>
	<div id="wiki-post">
	<div id="wiki-post-content">
	<?
	$APPLICATION->IncludeComponent(
	'bitrix:forum.topic.reviews',
	'',
	Array(
		'CACHE_TYPE' => $arResult['CACHE_TYPE'],
		'CACHE_TIME' => (isset($_REQUEST['MID']) && intval($_REQUEST['MID'])>0) ? 0 : $arResult['CACHE_TIME'],   //http://jabber.bx/view.php?id=28044
		'MESSAGES_PER_PAGE' => $arResult['MESSAGES_PER_PAGE'],
		'USE_CAPTCHA' => $arResult['USE_CAPTCHA'],
		'PATH_TO_SMILE' => $arResult['PATH_TO_SMILE'],
		'PATH_TO_FORUM_SMILE' => $arResult['PATH_TO_SMILE'],
		'FORUM_ID' => $arResult['FORUM_ID'],
		'URL_TEMPLATES_READ' => $arResult['URL_TEMPLATES_READ'],
		'SHOW_LINK_TO_FORUM' => $arResult['SHOW_LINK_TO_FORUM'],
		'ELEMENT_ID' => $arResult['ELEMENT_ID'],
		'IBLOCK_ID' => $arResult['IBLOCK_ID'],
		'SHOW_RATING' => $arParams['SHOW_RATING'],
		'RATING_TYPE' => $arParams['RATING_TYPE'],
		'PATH_TO_USER' => $arParams['PATH_TO_USER'],
		"AJAX_POST" => "N", // http://jabber.bx/view.php?id=27296 http://jabber.bx/view.php?id=27267
		'POST_FIRST_MESSAGE' => $arResult['POST_FIRST_MESSAGE'],
		'URL_TEMPLATES_DETAIL' => $arResult['POST_FIRST_MESSAGE']==='Y'? $arResult['FOLDER'].$arResult['URL_TEMPLATES']['element'] :'',
		'PREORDER' => "Y",
		'NAME_TEMPLATE' => $arResult['NAME_TEMPLATE'],
		'SHOW_MINIMIZED' => !(isset($_REQUEST['preview_comment']) && $_REQUEST['preview_comment'] == 'VIEW') ? 'Y' : 'N'
	),
	$component
	);?>

	<div class="wiki_comment_addition" id="wiki_comment_addition" style="display: none"><?=GetMessage('WIKI_COMMENT_ADDING')?></div>
	<script>

	var replyFormExist = false;

	function WIKI_show_form_add(show)
	{
		var _divs = document.getElementsByTagName('div');
		for (var i = 0; i < _divs.length; i++)
		{
			if (_divs[i].className ==  'reviews-reply-form')
			{
				replyFormExist = true;
				_divs[i].style.display = show ? 'block' : 'none';
				break;
			}
		}
	}

	function WIKI_show_addition(show)
	{
		document.getElementById('wiki_comment_addition').style.display = show ? 'block' : 'none';
	}

	if(window.BX)
	{
		BX.ready(function()
		{
			var _aTags = BX.findChildren(document, {"className" : "reviews-button-small", "tagName" : "A", "attribute": {"href": '#review_anchor'}}, true);
			if (!!_aTags)
			{
				for (var i = 0; i < _aTags.length; i++)
				{
					BX.bind(
						_aTags[i],
						'click',
						function (e) { WIKI_show_form_add(true); }
					);
				}
			}
		});
	}

	BX.addCustomEvent(window, 'onBeforeForumCommentAJAXPost', function() { WIKI_show_form_add(false); WIKI_show_addition(true);});
	BX.addCustomEvent(window, 'onAfterForumCommentAJAXPost', function() { WIKI_show_form_add(false); WIKI_show_addition(false); });

	</script>

	</div>
	</div>

<?
endif;
?>
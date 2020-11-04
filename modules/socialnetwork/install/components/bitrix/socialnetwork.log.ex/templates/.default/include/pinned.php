<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @var boolean $is_unread */

if (
	in_array($arResult['PAGE_MODE'], ['first', 'refresh'])
	&& $arResult['SHOW_PINNED_PANEL'] === 'Y'
)
{
	ob_start();

	$blogPostLivefeedProvider = new \Bitrix\Socialnetwork\Livefeed\BlogPost;
	$blogPostEventIdList = $blogPostLivefeedProvider->getEventId();

	?><div data-livefeed-pinned-panel class="feed-pinned-panel"><?
		if (!empty($arResult['pinnedEvents']))
		{
			foreach($arResult['pinnedEvents'] as $pinnedEvent)
			{
				$arEvent = $pinnedEvent;
				if(in_array($pinnedEvent['EVENT_ID'], $blogPostEventIdList))
				{
					require($_SERVER["DOCUMENT_ROOT"].$templateFolder."/include/blog_post.php");
				}
				else
				{
					require($_SERVER["DOCUMENT_ROOT"].$templateFolder."/include/log_entry.php");
				}
			}
		}
	?></div><?

	$blockContent = ob_get_clean();

	if ($arResult['PAGE_MODE'] === 'refresh')
	{
		$targetHtml .= $blockContent;
	}
	else
	{
		echo $blockContent;
	}
}

<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @var boolean $is_unread */
/** @var string $templateFolder */
/** @var string $targetHtml */

use Bitrix\Main\Localization\Loc;

if (
	in_array($arResult['PAGE_MODE'], ['first', 'refresh'])
	&& $arResult['SHOW_PINNED_PANEL'] === 'Y'
)
{
	ob_start();

	$blogPostLivefeedProvider = new \Bitrix\Socialnetwork\Livefeed\BlogPost;
	$blogPostEventIdList = $blogPostLivefeedProvider->getEventId();

	$classList = [
		'feed-pinned-panel'
	];
	$pinnedEventCounter = 0;

	if (
		!empty($arResult['pinnedEvents'])
		&& is_array($arResult['pinnedEvents'])

	)
	{
		$classList[] = 'feed-pinned-panel-nonempty';

		if (count($arResult['pinnedEvents']) >= \Bitrix\Socialnetwork\Component\LogList\Util::getCollapsedPinnedPanelItemsLimit())
		{
			$pinnedEventCounter = count($arResult['pinnedEvents']);
			$classList[] = 'feed-pinned-panel-collapsed';
			$classList[] = 'feed-pinned-panel-items-collapsed';
		}
	}

	?><div data-livefeed-pinned-panel class="<?= implode(' ', $classList) ?>"><?php

		?><div class="feed-post-collapsed-panel">
			<div class="feed-post-collapsed-panel-left">
				<div class="feed-post-collapsed-panel-box">
					<div class="feed-post-collapsed-panel-txt-grey"><?= Loc::getMessage('SONET_C30_FEED_PINNED_COLLAPSED_POSTS') ?></div>
					<div class="feed-post-collapsed-panel-count feed-post-collapsed-panel-count-posts"><?= $pinnedEventCounter ?></div>
				</div>
				<div class="feed-post-collapsed-panel-box feed-post-collapsed-panel-box-comments">
					<div class="feed-post-collapsed-panel-txt-grey feed-post-collapsed-panel-txt-grey--light"><?= Loc::getMessage('SONET_C30_FEED_PINNED_COLLAPSED_NEW_COMMENTS') ?></div>
					<div class="feed-post-collapsed-panel-count feed-post-collapsed-panel-count-comments"><?php
						?><svg width="6" height="6" viewBox="0 0 6 6" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path opacity="0.840937" d="M3.36051 5.73145V3.76115H5.33081V2.70174H3.36051V0.731445H2.30111V2.70174H0.330811V3.76115H2.30111V5.73145H3.36051Z" fill="white"></path>
						</svg><?php
						?><div class="feed-post-collapsed-panel-count-comments-value"></div><?php
					?></div>
				</div>
			</div>
			<div class="feed-post-collapsed-panel-right">
				<div class="feed-post-collapsed-panel-txt-grey feed-post-collapsed-panel-txt-grey--light"><?= Loc::getMessage('SONET_C30_FEED_PINNED_COLLAPSED_EXPAND') ?></div>
				<div class="feed-post-collapsed-panel-icon"></div>
			</div>
		</div><?php

		?><div class="feed-pinned-panel-posts"><?php
			if (!empty($arResult['pinnedEvents']))
			{
				foreach($arResult['pinnedEvents'] as $pinnedEvent)
				{
					$arEvent = $pinnedEvent;
					if(in_array($pinnedEvent['EVENT_ID'], $blogPostEventIdList))
					{
						require($_SERVER["DOCUMENT_ROOT"] . $templateFolder . "/include/blog_post.php");
					}
					else
					{
						require($_SERVER["DOCUMENT_ROOT"] . $templateFolder . "/include/log_entry.php");
					}
				}
			}
		?></div><?php
	?></div><?php

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

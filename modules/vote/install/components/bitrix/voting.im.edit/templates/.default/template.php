<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

/** @var CMain $APPLICATION */
/** @var array $arResult */
/** @var array $arParams */

$APPLICATION->SetPageProperty('BodyClass', 'vote-creation-form-sidepanel');
$APPLICATION->SetTitle(\Bitrix\Main\Localization\Loc::getMessage('VOTE_IM_EDIT_COMPONENT_TEMPLATE_NEW_TITLE'));

\Bitrix\UI\Toolbar\Facade\Toolbar::deleteFavoriteStar();
\Bitrix\Main\UI\Extension::load(['vote.component.vote-creation-form']);

$containerId = 'vote-creation-form';
$chatId = intval($arResult['CHAT_ID'] ?? 0);
$maxQuestionsCount = \Bitrix\Vote\Integration\Im\ImVote::getMaxQuestionsCount();
$minAnswersCount = \Bitrix\Vote\Integration\Im\ImVote::getMinAnswersCount();
$maxAnswersCount = \Bitrix\Vote\Integration\Im\ImVote::getMaxAnswersCount();
?>

<div id=<?=$containerId?>></div>
<script>
	const { App } = BX.Vote.Component;
	App.mount({
		chatId: <?=$chatId?>,
		containerId: '<?=$containerId?>',
		maxQuestionsCount: <?=$maxQuestionsCount?>,
		minAnswersCount: <?=$minAnswersCount?>,
		maxAnswersCount: <?=$maxAnswersCount?>,
	});
</script>

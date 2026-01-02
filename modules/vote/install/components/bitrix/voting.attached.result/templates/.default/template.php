<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

/** @var CMain $APPLICATION */
/** @var array $arResult */
/** @var array $arParams */

$APPLICATION->SetTitle(Loc::getMessage('VOTE_ATTACHED_RESULT_COMPONENT_TITLE'));
\Bitrix\Main\UI\Extension::load([
	'ui.common',
	'vote.component.attached-result',
	'vote.analytics'
]);

$buttonId = 'vote-download-result';
$downloadButton = \Bitrix\UI\Buttons\Button::create()
   ->addClass('vote-result__btn')
   ->setTag(\Bitrix\UI\Buttons\Tag::LINK)
   ->addAttribute('id', $buttonId)
   ->setLink($arResult['VOTE']['attach']['downloadUrl'] ?? '')
   ->setText(Loc::getMessage('VOTE_ATTACHED_RESULT_EXPORT'))
   ->setColor(\Bitrix\UI\Buttons\Color::PRIMARY)
;

$containerId = 'vote-attach-result-container';
?>
<div id="<?= htmlspecialcharsbx($containerId) ?>">...</div>
<script>
	const voteData = <?= \Bitrix\Main\Web\Json::encode($arResult['VOTE'] ?? null) ?>;
	const container = document.getElementById('<?= CUtil::JSescape($containerId )?>');
	const ext = (new BX.Vote.Component.VoteAttachedResult({
		votedPageSize: <?= (int)($arResult['VOTED_PAGE_SIZE'] ?? 10) ?>,
	}));
	ext.renderTo(voteData, container);
	BX.ready(() => {
		const saveResultButton = document.getElementById('<?= CUtil::JSescape($buttonId)?>');
		BX.Event.bind(saveResultButton, 'click', () => {
			BX.Vote.VoteAnalytics.downloadResult(voteData.attach.entityId);
		});
	});
</script>

<?php
$APPLICATION->IncludeComponent('bitrix:ui.button.panel', '', [
	'ALIGN' => 'left',
	'BUTTONS' => [
		[
			'TYPE' => 'custom',
			'LAYOUT' => $downloadButton->render(),
		],
	]
]);
?>
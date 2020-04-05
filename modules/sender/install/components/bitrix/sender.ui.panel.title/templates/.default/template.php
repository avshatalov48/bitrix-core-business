<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Json;

use Bitrix\Sender\Integration;
use Bitrix\Sender\Internals\Model;

/** @var CAllMain $APPLICATION */
/** @var array $arParams */
/** @var array $arResult */
/** @var \CBitrixComponentTemplate $this */

$isBitrix24Template = defined('SITE_TEMPLATE_ID') && SITE_TEMPLATE_ID === "bitrix24";
if (!$isBitrix24Template)
{
	$this->addExternalCss($this->GetFolder() . '/admin.css');
	if (!isset($_REQUEST['IFRAME']))
	{
		?>
		<div class="pagetitle-inner-container">
			<?$APPLICATION->ShowViewContent('inside_pagetitle')?>
		</div>
		<?
	}
}
$this->SetViewTarget('inside_pagetitle');

$bodyClass = $APPLICATION->GetPageProperty('BodyClass');
$bodyClass = ($bodyClass ? $bodyClass . ' ' : '') . ' pagetitle-toolbar-field-view ';
$APPLICATION->SetPageProperty('BodyClass', $bodyClass);


foreach ($arParams['LIST'] as $item):

	if ($item['type'] === 'filter'):
		?>
		<div class="pagetitle-container pagetitle-flexible-space">
			<?=$item['content']?>
		</div>
		<?
	elseif ($item['type'] === 'buttons'):

		Extension::load("ui.buttons");
		Extension::load("ui.buttons.icons");

		?>
		<div class="pagetitle-container pagetitle-align-right-container">
		<?

		foreach ($item['list'] as $button):
			if (empty($button))
			{
				continue;
			}

			$button['id'] = isset($button['id']) ? $button['id'] : '';
			$button['class'] = isset($button['class']) ? $button['class'] : '';
			$button['href'] = isset($button['href']) ? $button['href'] : '';
			$button['caption'] = isset($button['caption']) ? $button['caption'] : '';
			$button['visible'] = isset($button['visible']) ? (bool) $button['visible'] : true;

			if ($button['type'] == 'list'):
				$button['class'] = $button['class'] ?: 'ui-btn-primary ui-btn-dropdown'
			?>
				<div id="<?=htmlspecialcharsbx($button['id'])?>"
					class="ui-btn <?=htmlspecialcharsbx($button['class'])?>"
					style="<?=($button['visible'] ? '' : 'display: none;')?>"
				>
					<?=htmlspecialcharsbx($button['caption'])?>
				</div>
			<?
			elseif ($button['type'] == 'add'):
				$button['class'] = $button['class'] ?: 'ui-btn-primary ui-btn-icon-add';
			?>
				<a id="<?=htmlspecialcharsbx($button['id'])?>"
					href="<?=htmlspecialcharsbx($button['href'])?>"
					class="ui-btn <?=htmlspecialcharsbx($button['class'])?>"
					style="<?=($button['visible'] ? '' : 'display: none;')?>"
				>
					<?=htmlspecialcharsbx($button['caption'])?>
				</a>
			<?
			elseif ($button['type'] == 'abuses'):
				if (!Integration\Bitrix24\Service::isPortal())
				{
					continue;
				}

				$button['class'] = $button['class'] ?: 'ui-btn-light-border ui-btn-icon-info';
				$button['caption'] = $button['caption'] ?: Loc::getMessage('SENDER_UI_BUTTON_PANEL_ABUSES');
				$button['counter'] = isset($button['counter']) ? $button['counter'] : Model\AbuseTable::getCountOfNew();
				$button['id'] = isset($button['id']) ? $button['id'] : '';

				\Bitrix\Main\Page\Asset::getInstance()->addString("
					<script type=\"text/javascript\">
						BX.ready(function () {
							top.BX.addCustomEvent('onSenderAbuseCountReset', function () {
								BX.remove(BX('sender-abuse-counter'));
							});
						});
					</script>
				");
			?>
				<a id="<?=htmlspecialcharsbx($button['id'])?>" title="<?=htmlspecialcharsbx($button['caption'])?>"
					href="<?=htmlspecialcharsbx($button['href'])?>"
					onclick="BX.Sender.Page.open('<?=htmlspecialcharsbx($button['href'])?>'); return false;"
					class="ui-btn <?=htmlspecialcharsbx($button['class'])?>"
				><?
					if ($button['counter']):
						?><i id="sender-abuse-counter" class="ui-btn-counter"><?=htmlspecialcharsbx($button['counter'])?></i><?
					endif;
				?></a>
			<?
			elseif ($button['type'] == 'feedback'):
				if (!Integration\Bitrix24\Service::isCloud())
				{
					continue;
				}

				\CJSCore::Init('sender_b24_feedback');
			?>
				<span id="SENDER_BUTTON_FEEDBACK" class="webform-small-button webform-small-button-transparent">
					<?=Loc::getMessage('SENDER_UI_BUTTON_PANEL_FEEDBACK')?>
				</span>
				<script>
					BX.ready(function () {
						BX.Sender.B24Feedback.init(<?=Json::encode(array(
							'b24_plan' => \CBitrix24::getLicenseType(),
							'b24_zone' => \CBitrix24::getPortalZone(),
						))?>);
					})
				</script>
			<?
			else:
				$button['class'] = $button['class'] ?: 'ui-btn-light-border'
			?>
				<a id="<?=htmlspecialcharsbx($button['id'])?>"
					<?if($button['href']):?>href="<?=htmlspecialcharsbx($button['href'])?>"<?endif;?>
					<?if($button['href'] && !empty($button['sliding'])):?>onclick="BX.Sender.Page.open('<?=htmlspecialcharsbx($button['href'])?>'); return false;"<?endif;?>
					class="ui-btn <?=htmlspecialcharsbx($button['class'])?>"
					style="<?=($button['visible'] ? '' : 'display: none;')?>"
				>
					<?=htmlspecialcharsbx($button['caption'])?>
				</a>
			<?
			endif;
		endforeach;
		?>
		</div>
		<?
	endif;

endforeach;
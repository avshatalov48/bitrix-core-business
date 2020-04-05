<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
\CJSCore::init(array('sidepanel'));
\Bitrix\Main\UI\Extension::load('ui.buttons');
\Bitrix\Main\UI\Extension::load('ui.buttons.icons');

if ($arResult['FATAL'])
{
	return;
}

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$curUrl = $request->getRequestUri();

$uriAjax = new \Bitrix\Main\Web\Uri($curUrl);
$uriAjax->addParams(array(
	'IS_AJAX' => 'Y'
));

$isBitrix24Template = SITE_TEMPLATE_ID === 'bitrix24';
$bodyClass = $APPLICATION->GetPageProperty('BodyClass');
$APPLICATION->SetPageProperty('BodyClass', ($bodyClass ? $bodyClass.' ' : '') . 'pagetitle-toolbar-field-view');
?>

<?php
if ($isBitrix24Template)
{
	$this->SetViewTarget('inside_pagetitle');
}
?>

<?if (!$isBitrix24Template):?>
<div class="tasks-interface-filter-container">
<?endif;?>

	<div class="pagetitle-container<?if (!$isBitrix24Template) {?> pagetitle-container-light<?}?> pagetitle-flexible-space">
		<?$APPLICATION->IncludeComponent(
			'bitrix:main.ui.filter',
			'',
			array(
				'FILTER_ID' => $arParams['FILTER_ID'],
				'GRID_ID' => $arParams['FILTER_ID'],
				'FILTER' => array(),
				'FILTER_PRESETS' => array(),
				'ENABLE_LABEL' => true,
				'ENABLE_LIVE_SEARCH' => true,
				'RESET_TO_DEFAULT_MODE' => true
			),
			$this->__component,
			array('HIDE_ICONS' => true)
		);?>
		<script type="text/javascript">
			var landingAjaxPath = '<?= \CUtil::jsEscape($uriAjax->getUri());?>';
		</script>
	</div>

	<style type="text/css">
		#popup-window-content-<?= $arParams['FILTER_ID']?>_search_container {
			display: none!important;
		}
	</style>

	<div class="landing-filter-buttons-container">
		<?if ($arParams['SETTING_LINK']):?>
			<script type="text/javascript">
				var lastLocation = top.location.toString();
				BX.SidePanel.Instance.bindAnchors({
					rules: [
						{
							condition: [
								'<?= str_replace('?', '\\\?', \CUtil::jsEscape($arParams['SETTING_LINK']));?>'
							],
							options: {
								events: {
									onClose: function()
									{
										if (window['landingSettingsSaved'] === true)
										{
											top.location = lastLocation;
										}

										if (BX.PopupMenu.getCurrentMenu())
										{
											BX.PopupMenu.getCurrentMenu().close();
										}
									}
								},
								allowChangeHistory: false
							}
						}]
				});
			</script>
			<a class="ui-btn ui-btn-light-border ui-btn-icon-setting" href="<?= $arParams['SETTING_LINK'];?>"></a>
		<?endif;?>
		<?if ($arParams['FOLDER_LID']):?>
		<a class="ui-btn ui-btn-light-border ui-btn-icon-add-folder landing-filter-buttons-add-folder" href=""></a>
		<?else:?>
		&nbsp;&nbsp;
		<?endif;?>

		<?
		if (isset($arParams['BUTTONS']) && is_array($arParams['BUTTONS']))
		{
			if (count($arParams['BUTTONS']) == 1)
			{
				$buuton = array_shift($arParams['BUTTONS']);
				?>
				<div class="pagetitle-container pagetitle-align-right-container">
					<a href="<?= \htmlspecialcharsbx($buuton['LINK']);?>" class="ui-btn ui-btn-md ui-btn-primary ui-btn-icon-add landing-filter-action-link">
						<?= \htmlspecialcharsbx($buuton['TITLE']);?>
					</a>
				</div>
				<?
			}
			else
			{
				$buuton = array_shift($arParams['BUTTONS']);
				?>
				<div class="pagetitle-container pagetitle-align-right-container" id="landing-menu-actions">
					<a href="<?= \htmlspecialcharsbx($buuton['LINK']);?>" class="ui-btn ui-btn-md ui-btn-primary ui-btn-icon-add landing-filter-action-link ui-btn-dropdown">
						<?= \htmlspecialcharsbx($buuton['TITLE']);?>
					</a>
				</div>
				<script type="text/javascript">
					var actionsMenuIds = [];
					var onActionsClick = function(event) {
						actionsMenuIds.push('landing-menu-action');
						var menu = (
							BX.PopupMenu.getMenuById('landing-menu-action') ||
							new BX.Landing.UI.Tool.Menu({
								id: 'landing-menu-action',
								bindElement: event.currentTarget,
								autoHide: true,
								zIndex: 1200,
								offsetLeft: 20,
								angle: true,
								closeByEsc: true,
								items: [
									<?foreach ($arParams['BUTTONS'] as $buuton):?>
									{
										href: '<?= \CUtil::JSEscape($buuton['LINK']);?>',
										text: '<?= \CUtil::JSEscape($buuton['TITLE']);?>'
									},
									<?endforeach;?>
									null
								]
							})
						);
						menu.show();
						BX.PreventDefault(event);
					};
					BX('landing-menu-actions').addEventListener(
						'click',
						BX.proxy(onActionsClick, BX('landing-menu-actions'))
					);
				</script>
				<?
				if (0)
				foreach ($arParams['BUTTONS'] as $buuton)
				{
					if (isset($buuton['LINK']) && isset($buuton['TITLE']))
					{
						?>
						<div class="pagetitle-container pagetitle-align-right-container">
							<a href="<?= \htmlspecialcharsbx($buuton['LINK']);?>" class="ui-btn ui-btn-md ui-btn-primary ui-btn-icon-add landing-filter-action-link">
								<?= \htmlspecialcharsbx($buuton['TITLE']);?>
							</a>
						</div>
						<?
					}
				}
			}
		}
		?>
	</div>


<?if (!$isBitrix24Template):?>
</div>
<?endif;?>

<?php
if ($isBitrix24Template)
{
	$this->EndViewTarget();
}
?>
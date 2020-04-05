<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Landing\Manager;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\ModuleManager;

\Bitrix\Main\UI\Extension::load('ui.buttons');
\Bitrix\Main\UI\Extension::load('ui.buttons.icons');
\Bitrix\Main\UI\Extension::load('ui.alerts');

Loc::loadMessages(__FILE__);

if ($arResult['ERRORS'])
{
	\showError(implode("\n", $arResult['ERRORS']));
	return;
}

$site = $arResult['SITE'];
$context = \Bitrix\Main\Application::getInstance()->getContext();
$request = $context->getRequest();
$curUrl = $request->getRequestUri();
$previewMode = $request->get('landing_mode') == 'preview';

// common url
$uriEdit = new \Bitrix\Main\Web\Uri($curUrl);
$uriEdit->addParams(array(
	'landing_mode' => 'edit'
));

\CJSCore::Init(array(
	'sidepanel', 'popup_menu', 'marketplace', 'applayout'
));

$this->getComponent()->initAPIKeys();

if (!$request->offsetExists('landing_mode')):
	// some url
	$uriPub = new \Bitrix\Main\Web\Uri($curUrl);
	$uriPub->addParams(array(
		'action' => 'publication',
		'param' => $arResult['LANDING']->getId(),
		'code' => $arResult['LANDING']->getXmlId(),
		'sessid' => bitrix_sessid()
	));
	$uriPreview = new \Bitrix\Main\Web\Uri($curUrl);
	$uriPreview->addParams(array(
		'landing_mode' => 'preview'
	));
	// b24 title
	$b24Title = \Bitrix\Main\Config\Option::get('bitrix24', 'site_title', '');
	$b24Logo = \Bitrix\Main\Config\Option::get('bitrix24', 'logo24show', 'Y');
	if (!$b24Title)
	{
		$b24Title = Loc::getMessage(
			'LANDING_TPL_START_PAGE_LOGO' . (!Manager::isB24() ? '_SMN' : '')
		);
	}
	// help url
	$helpUrl = \Bitrix\Landing\Help::getHelpUrl('LANDING_EDIT');
	?>
	<div class="landing-ui-panel landing-ui-panel-top">
		<div class="landing-ui-panel-top-logo">
		<?if ($arParams['PAGE_URL_URL_SITES']):?>
			<a href="<?= $arParams['PAGE_URL_URL_SITES'];?>"><?
				?><span class="landing-ui-panel-top-logo-text"><?= $b24Title;?></span><?
				if ($b24Logo != 'N'):
					?><span class="landing-ui-panel-top-logo-color"><?= Loc::getMessage('LANDING_TPL_START_PAGE_LOGO_24');?></span><?
				endif;?>
			</a>
		<?else:?>
			<span class="landing-ui-panel-top-logo-text"><?= $b24Title;?></span><?
			if ($b24Logo != 'N'):
				?><span class="landing-ui-panel-top-logo-color"><?= Loc::getMessage('LANDING_TPL_START_PAGE_LOGO_24');?></span>
			<?endif;?>
		<?endif;?>
		</div>
		<div class="landing-ui-panel-top-chain">
			<?if ($arParams['PAGE_URL_URL_SITES']):?>
			<a href="<?= $arParams['PAGE_URL_URL_SITES'];?>" class="ui-btn ui-btn-xs ui-btn-light ui-btn-round landing-ui-panel-top-chain-link landing-ui-panel-top-chain-link-sites" title="<?= Loc::getMessage('LANDING_TPL_START_PAGE');?>">
				<?
				$title = Loc::getMessage('LANDING_TPL_START_PAGE_' . $arParams['TYPE']);
				if (!$title)
				{
					$title = Loc::getMessage('LANDING_TPL_START_PAGE');
				}
				echo $title;
				?>
			</a><?
			?><strong class="landing-ui-panel-top-chain-link-sites">&thinsp;&ndash;&thinsp;</strong>
			<?endif;?><?
				$sitesCount = $component->getSitesCount();
				$pagesCount = $component->getPagesCount();
			?><<?=$sitesCount <= 1 ? "a href=\"".$arParams['PAGE_URL_LANDINGS']."\"" : "span"?> class="ui-btn ui-btn-xs ui-btn-light ui-btn-round landing-ui-panel-top-chain-link landing-ui-panel-top-chain-link-site<?=($sitesCount <= 1 ? " landing-ui-no-icon" : "")?>" title="<?= \htmlspecialcharsbx($site['TITLE']);?>">
				<?= \htmlspecialcharsbx($site['TITLE']);?>
			</<?=$sitesCount <= 1 ? "a" : "span"?>><?
			?><strong>&thinsp;&ndash;&thinsp;</strong><?
			?><span class="ui-btn ui-btn-xs ui-btn-light ui-btn-round landing-ui-panel-top-chain-link landing-ui-panel-top-chain-link-page<?=($pagesCount <= 1 ? " landing-ui-no-icon" : "")?>" title="<?= \htmlspecialcharsbx($arResult['LANDING']->getTitle());?>"><?
				echo \htmlspecialcharsbx($arResult['LANDING']->getTitle());
			?></span>
		</div>
		<div class="landing-ui-panel-top-devices">
			<div class="landing-ui-panel-top-devices-inner">
				<button class="landing-ui-button landing-ui-button-desktop active" data-id="desktop_button"></button>
				<button class="landing-ui-button landing-ui-button-tablet" data-id="tablet_button"></button>
				<button class="landing-ui-button landing-ui-button-mobile" data-id="mobile_button"></button>
			</div>
		</div>
		<div class="landing-ui-panel-top-history">
			<span class="landing-ui-panel-top-history-button landing-ui-panel-top-history-undo landing-ui-disabled"></span>
			<span class="landing-ui-panel-top-history-button landing-ui-panel-top-history-redo landing-ui-disabled"></span>
		</div>
		<div class="landing-ui-panel-top-menu">
			<span class="ui-btn ui-btn-link ui-btn-icon-setting landing-ui-panel-top-menu-link landing-ui-panel-top-menu-link-settings" title="<?= Loc::getMessage('LANDING_TPL_SETTINGS_URL');?>">&nbsp;</span>
			<span class="ui-btn ui-btn-xs ui-btn-light ui-btn-round landing-ui-panel-top-chain-link landing-ui-panel-top-menu-link-settings">
				<?= Loc::getMessage('LANDING_TPL_SETTINGS_URL');?>
			</span><?
			?><a href="<?= $uriPreview->getUri();?>" class="ui-btn ui-btn-light-border landing-ui-panel-top-menu-link" target="_blank"><?= Loc::getMessage('LANDING_TPL_PREVIEW_URL');?></a><?
			?><a href="<?= $uriPub->getUri();?>" class="ui-btn ui-btn-primary landing-ui-panel-top-menu-link" target="_blank"><?= Loc::getMessage('LANDING_TPL_PUBLIC_URL');?></a><?
			if ($helpUrl):
				?><a href="<?= $helpUrl?>" class="ui-btn ui-btn-light ui-btn-round landing-ui-panel-top-menu-link landing-ui-panel-top-menu-link-help" target="_blank">
					<span class="landing-ui-panel-top-menu-link-help-icon">?</span>
				</a><?
			endif;?>
		</div>
	</div>
	<div class="landing-ui-view-container">
	<?php
endif;

if ($previewMode)
{
	?>
	<style type="text/css">
		.landing-block-deactive {
			display: none;
		}
	</style>
	<?
}
else
{
	\CUtil::InitJSCore(array('landing_master'));
}

if ($request->offsetExists('landing_mode'))
{
	if ($request->get('landing_mode') == 'edit')
	{
		Manager::setPageClass('MainClass', 'landing-edit-mode');
	}
	$arResult['LANDING']->view(array(
		'parse_link' => $previewMode,
		//'apply_template' => $previewMode
	));
	?>
	<style type="text/css">
		.bx-session-message {
			display: none;
		}
	</style>
	<?
}
else
{
	// exec theme-hooks for correct assets
	$hooksSite = \Bitrix\Landing\Hook::getForSite($arResult['LANDING']->getSiteId());
	$hooksLanding = \Bitrix\Landing\Hook::getForLanding($arResult['LANDING']->getId());
	if (
		isset($hooksSite['THEME']) &&
		$hooksSite['THEME']->enabled()
	)
	{
		$hooksSite['THEME']->exec();
	}
	if (
		isset($hooksLanding['THEME']) &&
		$hooksLanding['THEME']->enabled()
	)
	{
		$hooksLanding['THEME']->exec();
	}
	// title
	Manager::getApplication()->setTitle(
			\htmlspecialcharsbx($arResult['LANDING']->getTitle())
	);
	?>
	<style type="text/css">
		html, body {
			height: 100%;
			overflow: hidden;
		}
	</style>
	<script type="text/javascript">
		BX.ready(function() {
			var settingButtons = [].slice.call(document.querySelectorAll('.landing-ui-panel-top-menu-link-settings'));
			var settingsMenuIds = [];

			/**
 			 * Handles click on settings button
			 */
			var onSettingsClick = function(index, event) {
				settingsMenuIds.push('landing-menu-settings' + index);
				var menu = (
					BX.PopupMenu.getMenuById('landing-menu-settings' + index) ||
					new BX.Landing.UI.Tool.Menu({
						id: 'landing-menu-settings' + index,
						bindElement: event.currentTarget,
						autoHide: true,
						zIndex: 1200,
						offsetLeft: 20,
						angle: true,
						closeByEsc: true,
						items: [
							{
								href: '<?= \CUtil::JSEscape($arParams['PAGE_URL_LANDING_EDIT']);?>',
								text: '<?= \CUtil::JSEscape(Loc::getMessage('LANDING_TPL_SETTINGS_PAGE_URL'));?>'
							},
							{
								href: '<?= \CUtil::JSEscape($arParams['PAGE_URL_SITE_EDIT']);?>',
								text: '<?= \CUtil::JSEscape(Loc::getMessage('LANDING_TPL_SETTINGS_SITE_URL'));?>'
							}
							<?if (
								($arParams['TYPE'] == 'STORE') ||
								(
									!Manager::isB24() &&
									ModuleManager::isModuleInstalled('iblock') &&
									ModuleManager::isModuleInstalled('catalog')
								)
							):
								$uriSettCatalog = new \Bitrix\Main\Web\Uri($arParams['PAGE_URL_SITE_EDIT']);
								$uriSettCatalog->addParams(array(
									'tpl' => 'catalog'
								));
							?>
							, {
								href: '<?= \CUtil::JSEscape($uriSettCatalog->getUri());?>',
								text: '<?= \CUtil::JSEscape(Loc::getMessage('LANDING_TPL_SETTINGS_CATALOG_URL'));?>'
							}
							<?endif;?>
							<?
							if (!empty($arResult['PLACEMENTS_SETTINGS']))
							{
								foreach ($arResult['PLACEMENTS_SETTINGS'] as $placement)
								{
									?>
									, {
										onclick: function()
										{
											BX.rest.AppLayout.openApplication(
												<?= $placement['APP_ID'];?>,
												{
													SITE_ID: <?= $arParams['SITE_ID'];?>,
													LID: <?= $arParams['LANDING_ID'];?>
												},
												{
													PLACEMENT: '<?= $placement['PLACEMENT'];?>',
													PLACEMENT_ID: <?= $placement['ID'];?>
												}
											);
										},
										text: '<?= \CUtil::JSEscape(\htmlspecialcharsbx($placement['TITLE']));?>'
									}
									<?
								}
							}
							?>
						]
					})
				);
				menu.show();
			};

			settingButtons.forEach(function(element, index) {
				element.addEventListener("click", onSettingsClick.bind(null, index));
			});


			/**
			 * Closes all settings menus
			 */
			var closeAllSettingsMenu = function() {
				settingsMenuIds.forEach(function(id) {
					var menu = BX.PopupMenu.getMenuById(id);

					if (menu)
					{
						menu.close();
					}
				})
			};


			// Force top and style panel initialization
			var forceBasePanelsInit = function() {
				BX.Landing.UI.Panel.StylePanel.getInstance();
				BX.Landing.UI.Panel.Top.getInstance();
			};

			// Binds on iframe events
			BX.Landing.PageObject.getInstance().view().then(function(iframe) {
				iframe.contentWindow.addEventListener("load", forceBasePanelsInit);
				iframe.contentWindow.addEventListener("click", closeAllSettingsMenu);
				iframe.contentWindow.addEventListener("resize", BX.debounce(closeAllSettingsMenu, 200));
			});


			// Hide panel by click on top panel
			BX.Landing.PageObject.getInstance().top().then(function(panel) {
				panel.addEventListener("click", function() {
				BX.Landing.PageObject.getInstance().view().then(function(iframe) {
						if (iframe.contentWindow.BX.Landing.Block.Node.Text.currentNode)
						{
							iframe.contentWindow.BX.Landing.Block.Node.Text.currentNode.disableEdit();
						}

						if (iframe.contentWindow.BX.Landing.UI.Field.BaseField.currentField)
						{
							iframe.contentWindow.BX.Landing.UI.Field.BaseField.currentField.disableEdit();
						}

						iframe.contentWindow.BX.Landing.UI.Panel.EditorPanel.getInstance().hide();
					})
				});
			});


			// side panel
			if (typeof BX.SidePanel !== 'undefined')
			{
				var lastLocation = top.location.toString();

				BX.SidePanel.Instance.bindAnchors({
					rules: [
						{
							condition: [
								<?if (
									isset($arParams['PARAMS']['sef_url']['landing_edit']) &&
									trim($arParams['PARAMS']['sef_url']['landing_edit'])
								):?>
								new RegExp('<?= str_replace(
									array(
										'#site_show#', '#landing_edit#', '?'
									),
									array(
										'[0-9]+', '[0-9]+', '\\\?'
									),
									\CUtil::jsEscape($arParams['PARAMS']['sef_url']['landing_edit'])
								);?>')
								<?endif;?>

								<?if (
									isset($arParams['PARAMS']['sef_url']['site_edit']) &&
									trim($arParams['PARAMS']['sef_url']['site_edit'])
								):?>
								, new RegExp('<?= str_replace(
									array(
										'#site_edit#', '#site_show#', '?'
									),
									array(
										'[0-9]+', '[0-9]+', '\\\?'
									),
									\CUtil::jsEscape($arParams['PARAMS']['sef_url']['site_edit'])
								);?>')
								<?endif;?>

								<?if (
									isset($arParams['PARAMS']['sef_url']['site_show']) &&
									trim($arParams['PARAMS']['sef_url']['site_show'])
								):?>
								, new RegExp('<?= str_replace(
									array(
										'#site_show#', '?'
									),
									array(
										'[0-9]+', '\\\?'
									),
									\CUtil::jsEscape($arParams['PARAMS']['sef_url']['site_show'])
								 );?>(?!view)')
								<?endif;?>
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
						}
					]
				});
			}
		});

		BX(function() {
            var loaderContainer = top.document.querySelector(".landing-editor-loader-container");
            var userActionContainer = top.document.querySelector(".landing-editor-required-user-action");

            if (loaderContainer)
            {
            	var loader = new BX.Loader({offset: {top: "-70px"}});
            	loader.show(loaderContainer);

            	BX.Landing.PageObject.getInstance().view().then(function(iframe) {
            		BX.bindOnce(iframe, "load", function() {
            			var action = BX.Landing.Main.getInstance().options.requiredUserAction;

            			if (BX.Landing.Utils.isPlainObject(action) && !BX.Landing.Utils.isEmpty(action))
                        {
                        	if (action.header)
                            {
                                userActionContainer.querySelector("h3").innerText = action.header;
                            }

							if (action.description)
							{
								userActionContainer.querySelector("p").innerText = action.description;
							}

							if (action.href)
							{
								userActionContainer.querySelector("a").setAttribute("href", action.href);
							}

							if (action.text)
                            {
								userActionContainer.querySelector("a").innerText = action.text;
                            }

							userActionContainer.classList.add("landing-ui-user-action-show");

                        	document.querySelector(".landing-ui-panel-top-history").classList.add("landing-ui-disabled");
                        	document.querySelector(".landing-ui-panel-top-devices").classList.add("landing-ui-disabled");
                        	document.querySelector(".landing-ui-panel-top-chain-link.landing-ui-panel-top-menu-link-settings").classList.add("landing-ui-disabled");
                        	[].slice.call(document.querySelectorAll(".landing-ui-panel-top-menu-link:not(.landing-ui-panel-top-menu-link-help)"))
                                .forEach(function(item) {
                                    item.classList.add("landing-ui-disabled");
                                });

                        }
                        else
                        {
							iframe.classList.add("landing-ui-view-show");
                        }

                        setTimeout(function() {
                            BX.remove(loaderContainer);
                            BX.remove(userActionContainer);
                        }, 200);
                    });
                });
            }
        });
	</script>
	<div class="landing-ui-view-wrapper">
        <div class="landing-editor-loader-container"></div>
        <div class="landing-editor-required-user-action">
            <h3></h3>
            <p></p>
            <div>
                <a href="" class="ui-btn"></a>
            </div>
        </div>
		<iframe src="<?= $uriEdit->getUri();?>" class="landing-ui-view" id="landing-view-frame" allowfullscreen></iframe>
	</div>
	</div>
	<?
}
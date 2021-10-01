<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Web\Json;
use Bitrix\Main\Localization\Loc;
use Bitrix\B24Connector\Connection;

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */

CJSCore::Init(array('clipboard'));
?>

<?if(Connection::isExist()):?>
	<?if(!empty($arResult['ITEMS'])):?>
		<div class="intranet-button-list-wrapper">
			<div id="crm_web_form_list_container">

				<?if(!empty($arParams['FILTER'])):?>
					<div class="intranet-button-list-header-container">
						<h3 class="intranet-button-list-header"><?=Loc::getMessage("B24C_BL_WIDGETS")?></h3>
					</div><!--intranet-button-list-header-container-->
				<?endif;?>

				<!--intranet-button-list-createform-container-->
				<?foreach($arResult['ITEMS_BY_IS_SYSTEM'] as $isSystem => $system):?>

					<?if(empty($arParams['FILTER'])):?>
						<div class="intranet-button-list-header-container">
							<h3 class="intranet-button-list-header"><?=$system['NAME']?></h3>
						</div><!--intranet-button-list-header-container-->
					<?endif;?>

					<?foreach($system['ITEMS'] as $item):?>
						<div class="intranet-button-list-widget-row"
							data-bx-crm-webform-item="<?=intval($item['ID'])?>"
							data-bx-crm-webform-item-is-system="<?=$isSystem?>"
						>
							<div class="intranet-button-list-buttons-container">
						<?/*
								<div class="intranet-button-list-buttons">
									<span class="intranet-button-list-hamburger" data-bx-crm-webform-item-settings=""></span>
									<?if($arResult['PERM_CAN_EDIT']):?>
										<span class="intranet-button-list-close" data-bx-crm-webform-item-delete="" title="<?=Loc::getMessage('B24C_BL_LIST_ACTIONS_REMOVE')?>"></span>
									<?endif;?>
								</div><!--intranet-button-list-buttons-->
						*/?>
							</div><!--intranet-button-list-button-container-->
							<div class="intranet-button-list-widget-container intranet-button-list-widget-left">
								<div class="intranet-button-list-widget intranet-button-list-widget-number <?=$item['viewClassName']?>" data-bx-crm-webform-item-view="">
									<div class="intranet-button-list-widget-head">
										<span class="intranet-button-list-widget-title-container">
											<span class="intranet-button-list-widget-title-inner">
												<a href="<?=htmlspecialcharsbx($item['PATH_TO_BUTTON_EDIT'])?>" title="<?=Loc::getMessage("B24C_BL_WIDGET_EDIT")?>">
												<span class="intranet-button-list-widget-title"><?=htmlspecialcharsbx($item['NAME'])?></span>
												</a>
											</span>
										</span>
									</div><!--intranet-button-list-widget-head-->
									<div class="intranet-button-list-widget-content">
										<div class="intranet-button-list-widget-content-amt">
											<div class="intranet-button-list-widget-content-inner">
												<div class="intranet-button-list-widget-content-inner-block" title="<?=htmlspecialcharsbx($arResult['TYPE_LIST']['openline'])?>">
													<?if($item['ITEMS']['openline']):?>
														<div class="intranet-button-list-widget-content-inner-item intranet-button-list-widget-active">
															<div class="intranet-button-list-widget-content-inner-item-image intranet-button-list-openlines"></div>
															<div class="intranet-button-list-widget-content-inner-item-text"><?=htmlspecialcharsbx($item['ITEMS']['openline']['NAME'])?></div>
														</div><!--intranet-button-list-widget-content-inner-item-->
													<?else:?>
														<div class="intranet-button-list-widget-content-inner-item">
															<div class="intranet-button-list-widget-content-inner-item-image intranet-button-list-openlines"></div>
															<div class="intranet-button-list-widget-content-inner-item-text"><?=Loc::getMessage("B24C_BL_NOT_CHOSEN")?></div>
														</div><!--intranet-button-list-widget-content-inner-item-->
													<?endif;?>
												</div><!--intranet-button-list-widget-content-inner-item-->
												<div class="intranet-button-list-widget-content-inner-block" title="<?=htmlspecialcharsbx($arResult['TYPE_LIST']['crmform'])?>">
													<?if($item['ITEMS']['crmform']):?>
														<div class="intranet-button-list-widget-content-inner-item intranet-button-list-widget-active">
															<div class="intranet-button-list-widget-content-inner-item-image intranet-button-list-webform"></div>
															<div class="intranet-button-list-widget-content-inner-item-text"><?=htmlspecialcharsbx($item['ITEMS']['crmform']['NAME'])?></div>
														</div><!--intranet-button-list-widget-content-inner-item-->
													<?else:?>
														<div class="intranet-button-list-widget-content-inner-item">
															<div class="intranet-button-list-widget-content-inner-item-image intranet-button-list-webform"></div>
															<div class="intranet-button-list-widget-content-inner-item-text"><?=Loc::getMessage("B24C_BL_NOT_CHOSEN")?></div>
														</div><!--intranet-button-list-widget-content-inner-item-->
													<?endif;?>
												</div><!--intranet-button-list-widget-content-inner-item-->
												<div class="intranet-button-list-widget-content-inner-block" title="<?=htmlspecialcharsbx($arResult['TYPE_LIST']['callback'])?>">
													<?if($item['ITEMS']['callback']):?>
														<div class="intranet-button-list-widget-content-inner-item intranet-button-list-widget-active">
															<div class="intranet-button-list-widget-content-inner-item-image intranet-button-list-call"></div>
															<div class="intranet-button-list-widget-content-inner-item-text"><?=htmlspecialcharsbx($item['ITEMS']['callback']['NAME'])?></div>
														</div><!--intranet-button-list-widget-content-inner-item-->
													<?else:?>
														<div class="intranet-button-list-widget-content-inner-item">
															<div class="intranet-button-list-widget-content-inner-item-image intranet-button-list-call"></div>
															<div class="intranet-button-list-widget-content-inner-item-text"><?=Loc::getMessage("B24C_BL_NOT_CHOSEN")?></div>
														</div><!--intranet-button-list-widget-content-inner-item-->
													<?endif;?>
												</div><!--intranet-button-list-widget-content-inner-item-->
											</div><!--intranet-button-list-widget-content-inner-->
										</div>
									</div><!--intranet-button-list-widget-content-->
								</div><!--intranet-button-list-widget intranet-button-list-widget-number-->
							</div><!--intranet-button-list-widget-container intranet-button-list-widget-left-->
							<div class="intranet-button-list-widget-container intranet-button-list-widget-right">

								<div class="intranet-button-list-inner-info-container">
									<div class="intranet-button-list-creation-date-container">
										<div class="intranet-button-list-creation-date-element">
											<span class="intranet-button-list-text"><?=Loc::getMessage('B24C_BL_LIST_ITEM_DATE_CREATE')?>:</span>
											<span class="intranet-button-list-date"><?=htmlspecialcharsbx($item['DATE_CREATE_DISPLAY'])?></span>
										</div>
									</div><!--intranet-button-list-creation-date-container-->
									<div class="intranet-button-list-position-container">
										<div class="intranet-button-list-position-element">
											<span class="intranet-button-list-position-inner-wrap">
												<span class="intranet-button-list-position-text"><?=Loc::getMessage("B24C_BL_LOCATION")?>:</span>
												<span class="intranet-button-list-position-text"><?=htmlspecialcharsbx(mb_strtolower($item['LOCATION_DISPLAY']))?></span>
											</span>
										</div>
									</div><!--intranet-button-list-url-container-->
									<div class="intranet-button-list-settings-container">
										<div class="intranet-button-list-settings-element">
											<span class="intranet-button-list-settings-text"><?=Loc::getMessage('B24C_BL_LIST_VIEW')?>:</span>
											<span class="intranet-button-list-settings-text"><?=htmlspecialcharsbx($item['PAGES_USE_DISPLAY'])?></span>
										</div>
									</div><!--intranet-button-list-deal-container-->
								</div><!--intranet-button-list-inner-info-container-->

								<div class="intranet-button-list-activate-wrapper">
									<div class="intranet-button-list-activate-container <?=($arResult['PERM_CAN_EDIT'] ? '' : 'intranet-button-list-activate-disabled')?> <?=($item['LOCAL_ACTIVE'] == 'Y' ? 'intranet-button-list-on' : 'intranet-button-list-off')?>"
										data-bx-crm-webform-item-active="">
										<div class="intranet-button-list-activate-button-container">
											<span class="intranet-button-list-activate-button">
												<span class="intranet-button-list-activate-button-text"><?=Loc::getMessage('B24C_BL_LIST_ITEM_ACTIVE_ON')?></span>
											</span>
											<span class="intranet-button-list-not-activate-button">
												<span class="intranet-button-list-activate-button-cursor"></span>
												<span class="intranet-button-list-not-activate-button-text"><?=Loc::getMessage('B24C_BL_LIST_ITEM_ACTIVE_OFF')?></span>
											</span>
										</div><!--intranet-button-list-activate-button-container-->
										<span class="intranet-button-list-activate-button-item-on"><?=Loc::getMessage('B24C_BL_BUT_ACTIVE')?></span>
										<span class="intranet-button-list-activate-button-item-off"><?=Loc::getMessage('B24C_BL_BUT_NOT_ACTIVE')?></span>
									</div><!--intranet-button-list-activate-container-->
									<span class="intranet-button-list-activate-user-wrapper" data-bx-crm-webform-item-active-date="" <?=(intval($item['LOCAL_ADD_BY']) <= 0 ? ' style="display:none"' : '')?>>
										<span class="intranet-button-list-activate-user-container user-container-on">
											<?
											if($item['ACTIVE_CHANGE_BY_DISPLAY']['ICON'])
											{
												$userIconStyle = 'background-image: url(\'' . htmlspecialcharsbx($item['ACTIVE_CHANGE_BY_DISPLAY']['ICON']) .'\');';
												$userIconClass = '';
											}
											else
											{
												$userIconStyle = '';
												$userIconClass = 'user-default-icon';
											}
											?>
											<span class="intranet-button-list-activate-user-icon <?=$userIconClass?>" style="<?=$userIconStyle?>"></span>
											<span class="intranet-button-list-activate-user-inner">
												<a href="<?=htmlspecialcharsbx($item['ACTIVE_CHANGE_BY_DISPLAY']['LINK'])?>" class="intranet-button-list-activate-user-element">
													<?=htmlspecialcharsbx($item['ACTIVE_CHANGE_BY_DISPLAY']['NAME'])?>
												</a>
												<div class="intranet-button-list-activate-comments">
													<?if($item['ACTIVE_CHANGE_DATE_DISPLAY']):?>
														<?=($item['ACTIVE'] == 'Y' ? Loc::getMessage('B24C_BL_LIST_ITEM_ACTIVE_ACTIVATED') : Loc::getMessage('B24C_BL_LIST_ITEM_ACTIVE_DEACTIVATED'))?> <?=Loc::getMessage('B24C_BL_LIST_ITEM_ACTIVE_ACT_ON')?>  <?=$item['ACTIVE_CHANGE_DATE_DISPLAY']?>
													<?endif;?>
												</div>
											</span>
										</span>
										<span class="intranet-button-list-activate-user-container user-container-off">
											<?
											if($item['ACTIVE_CHANGE_BY_NOW_DISPLAY']['ICON'])
											{
												$userIconStyle = 'background-image: url(\'' . htmlspecialcharsbx($item['ACTIVE_CHANGE_BY_NOW_DISPLAY']['ICON']) .'\');';
												$userIconClass = '';
											}
											else
											{
												$userIconStyle = '';
												$userIconClass = 'user-default-icon';
											}
											?>
											<span class="intranet-button-list-activate-user-icon <?=$userIconClass?>" style="<?=$userIconStyle?>"></span>
											<span class="intranet-button-list-activate-user-inner">
												<a href="<?=htmlspecialcharsbx($item['ACTIVE_CHANGE_BY_NOW_DISPLAY']['LINK'])?>" class="intranet-button-list-activate-user-element">
													<?=htmlspecialcharsbx($item['ACTIVE_CHANGE_BY_NOW_DISPLAY']['NAME'])?>
												</a>
												<div class="intranet-button-list-activate-comments">
													<span class="intranet-button-list-activate-comments-act"><?=Loc::getMessage('B24C_BL_LIST_ITEM_ACTIVE_ON_NOW')?></span>
													<span class="intranet-button-list-activate-comments-deact"><?=Loc::getMessage('B24C_BL_LIST_ITEM_ACTIVE_OFF_NOW')?></span>
												</div>
											</span>
										</span>
									</span>
								</div>

								<div class="intranet-button-list-site-restriction-container">
									<div class="intranet-button-list-site-restriction-label"><?=Loc::getMessage('B24C_BL_SHOW_ON_SPECIFIED_SITES_ONLY');?></div>
									<div class="intranet-button-list-site-restriction-saved"><?=Loc::getMessage('B24C_BL_SAVED_LABEL');?></div>
									<select
										class="intranet-button-list-site-restriction-list"
										multiple
										size="5"
										<?=($arResult['PERM_CAN_EDIT']) ? '' : 'disabled';?>>
										<?foreach ($arResult['SITES'] as $site):?>
											<option value="<?=$site['LID'];?>" <?=in_array($site['LID'], $item['SITES']) ? 'selected' : '';?>><?=$site['DISPLAY_NAME'];?></option>
										<?endforeach;?>
									</select>
								</div>

							</div><!--intranet-button-list-widget-container intranet-button-list-widget-right-->
						</div><!--intranet-button-list-widget-row-->

					<?endforeach;?>
				<?endforeach;?>
			</div><!--intranet-button-list-wrapper-->
		</div>

		<script>
			BX.ready(function(){
				(new CrmWebFormList(<?=Json::encode(
					array(
						'context' => 'crm_web_form_list_container',
						'canEdit' => $arResult['PERM_CAN_EDIT'],
						'detailPageUrlTemplate' => $arParams['PATH_TO_BUTTON_EDIT'],
						'actionRequestUrl' => $this->getComponent()->getPath() . '/ajax.php',
						'remoteData' => $arResult['REMOTE_DATA'],
						'localData' => $arResult['LOCAL_DATA'],
						'mess' => array(
							'errorAction' => Loc::getMessage('B24C_BL_LIST_ERROR_ACTION'),
							'deleteConfirmation' => Loc::getMessage('B24C_BL_LIST_DELETE_CONFIRM'),
							'dlgBtnClose' => Loc::getMessage('B24C_BL_LIST_CLOSE'),
							'dlgBtnApply' => Loc::getMessage('B24C_BL_LIST_APPLY'),
							'dlgBtnCancel' => Loc::getMessage('B24C_BL_LIST_CANCEL')
						)
					))?>
				));
			});
		</script>

	<?elseif(!empty($arResult['EMPTY_BUTTON']['URL']) && !empty($arResult['EMPTY_BUTTON']['TITLE'])): //empty($arResult['ITEMS']?>
		<div class="connector-create">
			<?='<a href="'.$arResult['EMPTY_BUTTON']['URL'].'" class="connector-btn-blue">'.$arResult['EMPTY_BUTTON']['TITLE'].'</a>'?>
		</div>
	<?endif;?>

<?else: //(!Connection::isExist())?>
	<div class="connector-create">
		<?=Connection::getButtonHtml()?>&nbsp;&nbsp;
		<?='<a href="https://www.bitrix24.'.$arResult['B24_LANG'].'/" class="connector-button-green">'.Loc::getMessage('B24C_BL_CREATE_B24').'</a>'?>
	</div>
<?endif?>

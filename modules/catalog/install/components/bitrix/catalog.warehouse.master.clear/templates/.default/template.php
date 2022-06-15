<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$APPLICATION->SetTitle('Title');
$messages = Loc::loadLanguageFile(__FILE__);
\Bitrix\Main\UI\Extension::load(['ui.hint', 'ui.label', 'ui.switcher', 'main.loader', 'ui.vue', 'ui.buttons', 'main.popup', 'catalog.store-use']);
CJSCore::Init(array('marketplace'));
$APPLICATION->SetPageProperty('BodyClass', ($bodyClass ? $bodyClass.' ' : '').'catalog-warehouse-master-clear');
?>

<div class="catalog-warehouse__master-clear--content">
	<div class="catalog-warehouse__master-clear--title">
		<?
		if ($arResult['MODE'] == WarehouseMasterClear::MODE_EDIT)
		{
			echo Loc::getMessage('CAT_WAREHOUSE_MASTER_CLEAR_10');
		}
		else
		{
			if ($arResult['IS_USED'])
			{
				echo Loc::getMessage('CAT_WAREHOUSE_MASTER_CLEAR_9');
			}
			else
			{
				echo Loc::getMessage('CAT_WAREHOUSE_MASTER_CLEAR_3');
			}
		}
		?>
	</div>
	<div class="catalog-warehouse__master-clear--section">
		<div class="catalog-warehouse__master-clear--text">
			<?if($arResult['IS_USED'] === false):?>
				<p><?=Loc::getMessage('CAT_WAREHOUSE_MASTER_CLEAR_4')?><a href=\"#\" onclick='top.BX.Helper.show("redirect=detail&code=15662712");event.preventDefault();' class="catalog-warehouse__master-clear--link"><?=Loc::getMessage('CAT_WAREHOUSE_MASTER_CLEAR_5')?></a></p>
			<?endif;?>
		</div>
		<div id="placeholder"></div>
	</div>
</div>

<script type="text/javascript">
	BX.message(<?=CUtil::PhpToJSObject($messages)?>);
	mode = <?= CUtil::PhpToJSObject($arResult['MODE']) ?>;
	isUsedOneC = <?= CUtil::PhpToJSObject((bool)$arResult['IS_USED_ONEC']) ?>;
	isWithOrdersMode = <?= CUtil::PhpToJSObject((bool)$arResult['IS_WITH_ORDERS_MODE']) ?>;
	isLeadEnabled = <?= CUtil::PhpToJSObject((bool)$arResult['IS_LEAD_ENABLED']) ?>;
	isPlanRestricted = <?= CUtil::PhpToJSObject((bool)$arResult['IS_PLAN_RESTRICTED']) ?>;
	isUsed = <?= CUtil::PhpToJSObject((bool)$arResult['IS_USED']) ?>;
	isEmpty = <?= CUtil::PhpToJSObject((bool)$arResult['IS_EMPTY']) ?>;
	conductedDocumentsExist = <?= CUtil::PhpToJSObject((bool)$arResult['CONDUCTED_DOCUMENTS_EXIST']) ?>;
	presetList = <?= CUtil::PhpToJSObject($arResult['PRESET_LIST']) ?>;
	previewLang = <?= CUtil::PhpToJSObject($arResult['PREVIEW_LANG']) ?>;

	BX.Vue.create({
		el: document.getElementById('placeholder'),
		data: () => ({
			isEmpty: isEmpty,
			isPlanRestricted: isPlanRestricted,
			isUsedOneC: isUsedOneC,
			mode: mode,
			isWithOrdersMode: isWithOrdersMode,
			isLeadEnabled: isLeadEnabled,
			isUsed: isUsed,
			conductedDocumentsExist: conductedDocumentsExist,
			showLoader: false,
			showLoaderMP: false,
			sliderType: 'store',
			currentSlider: BX.SidePanel.Instance.getTopSlider() ?? false,
			presetList: presetList,
			previewLang: previewLang
		}),
		created: function ()
		{
			var enableHandler = this.enable.bind(this);
			BX.Event.EventEmitter.subscribe(BX.Catalog.StoreUse.EventType.popup.confirm, enableHandler);
			var disableHandler = this.disable.bind(this);
			BX.Event.EventEmitter.subscribe(BX.Catalog.StoreUse.EventType.popup.disable, disableHandler);
		},
		mounted: function ()
		{
			BX.UI.Switcher.initByClassName();
		},
		computed:
			{
				loc()
				{
					return BX.Vue.getFilteredPhrases('CAT_WAREHOUSE_')
				},
				getObjectClass()
				{
					var classes = [
						'ui-btn',
						'ui-btn-round',
						'ui-btn-no-caps',
						'catalog-warehouse__master-clear--btn'
					];
					if (this.showLoader === true)
					{
						classes.push('ui-btn-wait');
					}
					return classes;
				},
				getObjectClassMP()
				{
					var classes = [
						'ui-btn',
						'ui-btn-round',
						'ui-btn-light-border',
						'ui-btn-no-caps',
						'catalog-warehouse__master-clear--btn'
					];
					if (this.showLoaderMP === true)
					{
						classes.push('ui-btn-wait');
					}
					return classes;
				},
				getSrcPreviewImage()
				{
					var path = '/bitrix/components/bitrix/catalog.warehouse.master.clear/templates/.default/images/';
					if (this.previewLang === 'ru')
					{
						path = path + 'catalog-warehouse-master-laptop.png'
					}
					else if (this.previewLang === 'en')
					{
						path = path + 'catalog-warehouse-master-laptop-en.png'
					}
					else if (this.previewLang === 'ua')
					{
						path = path + 'catalog-warehouse-master-laptop-ua.png'
					}
					return path;
				},
				isEdit()
				{
					return this.mode === 'edit'
				}
			},
		methods:
			{
				setSliderType(sliderType)
				{
					if(sliderType === 'marketplace')
					{
						this.sliderType = sliderType;
					}
					else
					{
						this.sliderType = 'store';
					}
				},
				handleInstallPresetClick()
				{
					this.showLoader = true;
					var master = new BX.Catalog.Master.CatalogWarehouseMasterClear();
					master
					.inventoryManagementInstallPreset({preset: this.getListCheckedPreset()})
					.then(() => {
						this.showLoader = false;
						if (this.currentSlider)
						{
							this.currentSlider.close();
						}
					})
				},
				handleEnableClick(sliderType)
				{
					this.setSliderType(sliderType);
					var storeControlHelpArticleId = 15718276;

					if (this.isPlanRestricted)
					{
						this.showRestrictionSlider()
					}
					else if(this.isUsedOneC)
					{
						this.showOneCPopup()
					}
					else if (this.isWithOrdersMode)
					{
						this.showErrorPopup({
							text: this.loc.CAT_WAREHOUSE_MASTER_STORE_ORDER_DEAL_MODE_ERROR,
							helpArticleId: storeControlHelpArticleId
						});
					}
					else if (this.isLeadEnabled)
					{
						this.showErrorPopup({
							text: this.loc.CAT_WAREHOUSE_MASTER_STORE_LEAD_ENABLED_MODE_ERROR,
							helpArticleId: storeControlHelpArticleId
						});
					}
					else
					{
						if(this.isEmpty)
						{
							this.enable()
						}
						else
						{
							this.showResetQuantityPopup()
						}
					}
				},
				handleDisableClick()
				{
					if (this.conductedDocumentsExist)
					{
						this.showConductedDocumentsPopup();
					}
					else
					{
						this.showConfirmDisablePopup();
					}
				},
				isGridAlreadyOpened()
				{
					var openSliders = BX.SidePanel.Instance.getOpenSliders();
					for (var openSlider of openSliders)
					{
						if (openSlider.getUrl().lastIndexOf('/shop/documents/') === 0)
						{
							return true;
						}
					}

					return false;
				},
				getListCheckedPreset()
				{
					var list = [];
					var preset = [];
					list = BX.UI.Switcher.getList()
					.filter(function (item) {
						return item.checked === true;
					});
					if (list.length>0)
					{
						list.forEach((item)=>{
							preset.push(item.id)
						})
					}
					return preset.length>0 ? preset : ['empty'];
				},
				enable()
				{
					if(this.sliderType === 'marketplace')
					{
						this.showLoaderMP = true;
					}
					else
					{
						this.showLoader = true;
					}

					var master = new BX.Catalog.Master.CatalogWarehouseMasterClear();
					master
						.inventoryManagementEnabled({preset: this.getListCheckedPreset()})
						.then(() => {
							if(this.sliderType === 'marketplace')
							{
								this.showLoaderMP = false;
							}
							else
							{
								this.showLoader = false;
							}
							var shouldOpenGridOnEnable = true;
							if (this.currentSlider)
							{
								this.currentSlider.getData().set('isInventoryManagementEnabled', true);
								shouldOpenGridOnEnable = this.currentSlider.getData().get('openGridOnDone') ?? true;
								var shouldCloseMasterSlider = this.currentSlider.getData().get('closeSliderOnDone') ?? true;
								var shouldCloseSliderOnMarketplace = this.currentSlider.getData().get('closeSliderOnMarketplace') !== false;
								if (
									(this.sliderType !== 'marketplace' && shouldCloseMasterSlider)
									|| (this.sliderType === 'marketplace' && (shouldCloseSliderOnMarketplace && shouldCloseMasterSlider)))
								{
									this.currentSlider.close();
								}
							}

							if(this.sliderType === 'marketplace')
							{
								// BX.rest.Marketplace.open({}, 'migration, inventory');

								var url = '/marketplace/?tag[0]=migrator&tag[1]=inventory'
								var rule = BX.SidePanel.Instance.getUrlRule(url);
								var options = (rule && BX.type.isPlainObject(rule.options)) ? rule.options : {};
								options["cacheable"] = false;
								options["allowChangeHistory"] = false;
								options["requestMethod"] = "post";
								options["requestParams"] = { sessid: BX.bitrix_sessid() };
								BX.SidePanel.Instance.open(url, options);
							}
							else
							{
								top.BX.UI.Notification.Center.notify({
									content: this.loc.CAT_WAREHOUSE_MASTER_CLEAR_ENABLED,
								});
								if (shouldOpenGridOnEnable && !this.isGridAlreadyOpened())
								{
									master.openSlider('/shop/documents/', {});
								}
							}

							this.isUsed = true;
						})
						.catch(() => {
							BX.UI.Notification.Center.notify({
								content: this.loc.CAT_WAREHOUSE_MASTER_CLEAR_18,
							});

							this.showLoaderMP = this.showLoader = false;
						});
				},
				disable()
				{
					this.showLoader = true;
					var master = new BX.Catalog.Master.CatalogWarehouseMasterClear();
					master
						.inventoryManagementDisabled()
						.then(() => {
							if (this.currentSlider)
							{
								this.currentSlider.close();
							}
							this.showLoader = false
							this.isUsed = false;
						})
						.catch(() => {
							BX.UI.Notification.Center.notify({
								content: this.loc.CAT_WAREHOUSE_MASTER_CLEAR_18,
							});

							this.showLoader = false;
						});
				},
				showOneCPopup()
				{
					var dialogOneC = new BX.Catalog.StoreUse.DialogOneC();
					dialogOneC.popup();
				},
				showErrorPopup(options)
				{
					(new BX.Catalog.StoreUse.DialogError(options)).popup();
				},
				showResetQuantityPopup()
				{
					var dialogClearing = new BX.Catalog.StoreUse.DialogClearing();
					dialogClearing.popup();
				},
				showConductedDocumentsPopup()
				{
					var dialogDisable = new BX.Catalog.StoreUse.DialogDisable();
					dialogDisable.conductedDocumentsPopup();
				},
				showConfirmDisablePopup()
				{
					var dialogDisable = new BX.Catalog.StoreUse.DialogDisable();
					dialogDisable.disablePopup();
				},
				showRestrictionSlider()
				{
					top.BX.UI.InfoHelper.show('limit_store_inventory_management');
				},
				getDataSwitcher(item)
				{
					return JSON.stringify({
						id: item.code,
						checked: item.checked === 'Y',
						handlers: { unchecked : 'Y' }
					})
				},
				showHint(event, index)
				{
					var preset = this.getPresetByIndex(index);
					this.popup = new BX.Catalog.StoreUse.Popup();
					this.popup.show(event.target, preset.hint);
				},
				hideHint()
				{
					if (this.popup)
					{
						this.popup.hide();
					}
				},
				getPresetByIndex(index)
				{
					let preset = null;
					this.presetList
					.forEach((item, inx)=> {
						if (index === inx)
						{
							preset = item;
						}
					});
					return preset;
				}
			},
		//language vue
		template: `
			<div>
				<div>
					<div class="catalog-warehouse__master-clear--info">
						<div class="catalog-warehouse__master-clear--selection-block">
							<div class="catalog-warehouse__master-clear--item" :class="{'--disable': item.available === 'N'}"  v-for="(item, index) in this.presetList">
								<div class="catalog-warehouse__master-clear--switcher">
									<span class="ui-switcher-color-green ui-switcher ui-switcher-size-sm" :data-switcher="getDataSwitcher(item, index)">
										<span class="ui-switcher-cursor"></span>
										<span class="ui-switcher-enabled"><?=Loc::getMessage('CAT_WAREHOUSE_MASTER_CLEAR_7')?></span>
										<span class="ui-switcher-disabled"><?=Loc::getMessage('CAT_WAREHOUSE_MASTER_CLEAR_8')?></span>
									</span>
								</div>
								<div>
									<div class="catalog-warehouse__master-clear--name" :class="{  '--single-element': item.available === 'N'}">
										{{item.title}}
										<div v-if="item.soon === 'Y'" class="catalog-warehouse__master-clear--label">
											<?=Loc::getMessage('CAT_WAREHOUSE_MASTER_CLEAR_6')?>
										</div>
										<div v-if="item.hint !== ''" class="catalog-warehouse__master-clear--hint">
											<div class="ui-hint"
												v-on:mouseenter="showHint($event, index)"
												v-on:mouseleave="hideHint">
												<span class="ui-hint-icon"></span>
											</div>
										</div>
									</div>
									<div v-if="item.description !== ''" class="catalog-warehouse__master-clear--prompt">
										{{item.description}}
									</div>
								</div>
							</div>
						</div>
						<div class="catalog-warehouse__master-clear--image-block">
							<img :src="getSrcPreviewImage" alt="text">
						</div>
					</div>
				</div>
				<div class="catalog-warehouse__master-clear--box --line">
					<template v-if="isEdit === false">
						<template v-if="isUsed === false">
							<button :class="getObjectClass" class="ui-btn-success" @click="handleEnableClick('shop')" >{{loc.CAT_WAREHOUSE_MASTER_CLEAR_1}}</button>
							<button :class="getObjectClassMP" class="ui-btn-success" @click="handleEnableClick('marketplace')" >{{loc.CAT_WAREHOUSE_MASTER_CLEAR_17}}</button>
						</template>
						<template v-else>
							<button :class="getObjectClassMP" @click="handleDisableClick()">{{loc.CAT_WAREHOUSE_MASTER_CLEAR_2}}</button>
						</template>
					</template>
					<template v-else>
						<button :class="getObjectClass" class="ui-btn-success" @click="handleInstallPresetClick()" >{{loc.CAT_WAREHOUSE_MASTER_CLEAR_19}}</button>
					</template>
				</div>
			</div>`,
	});
</script>

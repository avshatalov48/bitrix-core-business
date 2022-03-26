<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$APPLICATION->SetTitle('Title');
$messages = Loc::loadLanguageFile(__FILE__);
\Bitrix\Main\UI\Extension::load(['main.loader', 'ui.vue', 'ui.buttons', 'main.popup', 'catalog.store-use']);
CJSCore::Init(array('marketplace'));
$APPLICATION->SetPageProperty('BodyClass', ($bodyClass ? $bodyClass.' ' : '').'catalog-warehouse-master-clear');
?>

<div class="container catalog-warehouse-master-clear-container">
	<div class="catalog-warehouse-master-clear-title">
		<span class="catalog-warehouse-master-clear-title-text--new"><?=Loc::getMessage('CAT_WAREHOUSE_MASTER_CLEAR_3')?></span>
		<span class="catalog-warehouse-master-clear-title-text"><?=Loc::getMessage('CAT_WAREHOUSE_MASTER_CLEAR_4')?></span>
	</div>
	<div class="catalog-warehouse-master-clear-main">
		<div class="catalog-warehouse-master-clear-inner">
			<ul class="catalog-warehouse-master-clear-ul">
				<li class="catalog-warehouse-master-clear-ul-item"><?=Loc::getMessage('CAT_WAREHOUSE_MASTER_CLEAR_5')?></li>
				<li class="catalog-warehouse-master-clear-ul-item"><?=Loc::getMessage('CAT_WAREHOUSE_MASTER_CLEAR_6')?></li>
				<li class="catalog-warehouse-master-clear-ul-item"><?=Loc::getMessage('CAT_WAREHOUSE_MASTER_CLEAR_7')?></li>
				<li class="catalog-warehouse-master-clear-ul-item"><?=Loc::getMessage('CAT_WAREHOUSE_MASTER_CLEAR_8')?></li>
				<li class="catalog-warehouse-master-clear-ul-item"><?=Loc::getMessage('CAT_WAREHOUSE_MASTER_CLEAR_9')?></li>
				<li class="catalog-warehouse-master-clear-ul-item"><?=Loc::getMessage('CAT_WAREHOUSE_MASTER_CLEAR_10')?></li>
<!--				<li class="catalog-warehouse-master-clear-ul-item">--><?//=Loc::getMessage('CAT_WAREHOUSE_MASTER_CLEAR_11')?><!--</li>-->
			</ul>
			<div class="catalog-warehouse-master-clear-img"></div>
		</div>
		<div class="catalog-warehouse-master-clear-btn-box">
			<div id="placeholder"></div>
		</div>
	</div>
</div>

<script type="text/javascript">
	BX.message(<?=CUtil::PhpToJSObject($messages)?>);
	isUsedOneC = <?= CUtil::PhpToJSObject((bool)$arResult['IS_USED_ONEC']) ?>;
	isPlanRestricted = <?= CUtil::PhpToJSObject((bool)$arResult['IS_PLAN_RESTRICTED']) ?>;
	isUsed = <?= CUtil::PhpToJSObject((bool)$arResult['IS_USED']) ?>;
	isEmpty = <?= CUtil::PhpToJSObject((bool)$arResult['IS_EMPTY']) ?>;
	conductedDocumentsExist = <?= CUtil::PhpToJSObject((bool)$arResult['CONDUCTED_DOCUMENTS_EXIST']) ?>;

	BX.Vue.create({
		el: document.getElementById('placeholder'),
		data: () => ({
			isEmpty: isEmpty,
			isPlanRestricted: isPlanRestricted,
			isUsedOneC: isUsedOneC,
			isUsed: isUsed,
			conductedDocumentsExist: conductedDocumentsExist,
			showLoader: false,
			showLoaderMP: false,
			sliderType: 'store',
			currentSlider: BX.SidePanel.Instance.getTopSlider() ?? false,
		}),
		created: function ()
		{
			var enableHandler = this.enable.bind(this);
			BX.Event.EventEmitter.subscribe(BX.Catalog.StoreUse.EventType.popup.confirm, enableHandler);
			var disableHandler = this.disable.bind(this);
			BX.Event.EventEmitter.subscribe(BX.Catalog.StoreUse.EventType.popup.disable, disableHandler);
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
					'ui-btn-round'
				];

				if(this.showLoader === true)
				{
					classes.push('ui-btn-wait');
				}

				return classes;
			},

			getObjectClassMP()
			{
				var classes = [
					'ui-btn',
					'ui-btn-round'
				];

				if(this.showLoaderMP === true)
				{
					classes.push('ui-btn-wait');
				}

				return classes;
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

			handleEnableClick(sliderType)
			{
				this.setSliderType(sliderType);

				if (this.isPlanRestricted)
				{
					this.showRestrictionSlider()
				}
				else if(this.isUsedOneC)
				{
					this.showOneCPopup()
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
					.inventoryManagementEnabled()
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
							if (shouldOpenGridOnEnable)
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
							this.currentSlider.getData().set('isInventoryManagementDisabled', true);
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
		},
	//language vue
		template: `
			<div>
				<template v-if="isUsed === false">
					<button :class="getObjectClass" class="ui-btn-success" @click="handleEnableClick('shop')" >{{loc.CAT_WAREHOUSE_MASTER_CLEAR_1}}</button>
					<button :class="getObjectClassMP" class="ui-btn-success" @click="handleEnableClick('marketplace')" >{{loc.CAT_WAREHOUSE_MASTER_CLEAR_17}}</button>
				</template>
				<template v-else>
					<button :class="getObjectClass" @click="handleDisableClick()">{{loc.CAT_WAREHOUSE_MASTER_CLEAR_2}}</button>
				</template>
			</div>`,
	});
</script>

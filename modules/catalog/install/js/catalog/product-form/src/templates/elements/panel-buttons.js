import {Vuex} from 'ui.vue.vuex';
import {Popup} from 'main.popup';
import {ajax, Event, Loc, Tag, Text, Type} from 'main.core';
import {Vue} from "ui.vue";
import {config} from "../../config";
import "./panel-compilation";
import {EventEmitter} from "main.core.events";
import 'ui.hint';
import { MessageBox } from 'ui.dialogs.messagebox';

Vue.component(config.templatePanelButtons,
{
	/**
	 * @emits 'changeRowData' {index: number, fields: object}
	 * @emits 'refreshBasket'
	 * @emits 'addItem'
	 */

	props: {
		options: Object,
		mode: String,
	},
	data()
	{
		return {
			settings: []
		};
	},
	methods:
	{
		refreshBasket()
		{
			this.$emit('refreshBasket');
		},
		changeBasketItem(item)
		{
			this.$emit('changeRowData', item);
		},
		addBasketItemForm()
		{
			this.$emit('addItem');
		},
		getInternalIndexByProductId(skuId)
		{
			const basket = this.$store.getters['productList/getBasket']();
			return Object
				.keys(basket)
				.findIndex((inx) =>{
					return parseInt(basket[inx].skuId) === parseInt(skuId)
				});
		},
		handleAddItem(id, params)
		{
			const skuType = 4;
			if (Text.toNumber(params.type) === skuType)
			{
				ajax.runAction(
					'catalog.productSelector.getSelectedSku',
					{
						json: {
							variationId: id,
							options: {
								priceId: this.options.basePriceId,
								urlBuilder: this.options.urlBuilder,
								currency: this.options.currency,
								resetSku: true
							}
						}
					}
				).then(response => this.processResponse(response, params.isAddAnyway));
			}
			else
			{
				ajax.runAction(
					'catalog.productSelector.getProduct',
					{
						json: {
							productId: id,
							options: {
								priceId: this.options.basePriceId,
								urlBuilder: this.options.urlBuilder,
								currency: this.options.currency,
							}
						}
					}
				).then(response => this.processResponse(response, params.isAddAnyway));
			}
		},
		processResponse(response, isAddAnyway)
		{
			const index = isAddAnyway ? -1 : this.getInternalIndexByProductId(response.data.skuId);
			if (index < 0)
			{
				const productData = response.data;
				const basePrice = Text.toNumber(productData.fields.BASE_PRICE);
				productData.fields = productData.fields || {};
				let newItem = this.$store.getters['productList/getBaseProduct']();
				newItem.fields = Object.assign(newItem.fields, {
					price: basePrice,
					priceExclusive: basePrice,
					basePrice,
					name: productData.fields.NAME || '',
					productId: productData.productId,
					skuId: productData.skuId,
					measureCode: productData.fields.MEASURE_CODE,
					measureName: productData.fields.MEASURE_NAME,
					measureRatio: productData.fields.MEASURE_RATIO,
					properties: productData.fields.PROPERTIES,
					offerId: productData.skuId > 0 ? productData.skuId : productData.productId,
					module: 'catalog',
					isCustomPrice: Type.isNil(productData.fields.PRICE) ? 'Y' : 'N',
					discountType: this.options.defaultDiscountType,
				});

				delete(productData.fields);
				newItem = Object.assign(newItem, productData);
				newItem.sum = basePrice;

				this.$root.$app.addProduct(newItem);
			}
		},
		onUpdateBasketItem(inx, item)
		{
			this.$store.dispatch('productList/changeRowData', {
				index : inx,
				fields : item
			});
			this.$store.dispatch('productList/changeProduct', {
				index : inx,
				fields : item.fields
			});
		},
		/*
		* By default, basket collection contains a fake|empty item,
		*  that is deleted when you select items from the catalog.
		* Also, products can be added to the form and become an empty string,
		*  while stay a item of basket collection
		* */
		removeEmptyItems()
		{
			const basket = this.$store.getters['productList/getBasket']();
			basket.forEach((item, i)=>{
				if(
					basket[i].name === ''
					&& basket[i].price < 1e-10
				)
				{
					this.$store.commit('productList/deleteItem', {
						index: i
					});
				}
			});
		},
		modifyBasketItem(params)
		{
			const skuId = parseInt(params.id);
			if(skuId > 0)
			{
				const index = this.getInternalIndexByProductId(skuId);
				if(index >= 0)
				{
					this.showDialogProductExists(params);
				}
				else
				{
					this.removeEmptyItems();
					this.handleAddItem(skuId, params);
				}
			}
		},
		showDialogProductExists(params)
		{
			MessageBox.confirm(
				Loc.getMessage('CATALOG_FORM_BLOCK_PROD_EXIST_DLG_TEXT_FOR_DOUBLE').replace('#NAME#', params.name),
				Loc.getMessage('CATALOG_FORM_BLOCK_PROD_EXIST_DLG_TITLE'),
				(messageBox) => {
					const productId = parseInt(params.id, 10);
					const index = this.getInternalIndexByProductId(productId);
					if (index >= 0)
					{
						this.handleAddItem(productId, {
							...params,
							isAddAnyway: true,
						});
					}
					messageBox.close();
				},
				Loc.getMessage('CATALOG_FORM_BLOCK_PROD_EXIST_DLG_OK'),
				(messageBox) => messageBox.close(),
				Loc.getMessage('CATALOG_FORM_BLOCK_PROD_EXIST_DLG_NO'),
			);
		},
		showDialogProductSearch()
		{
			const funcName = 'addBasketItemFromDialogProductSearch';
			window[funcName] = params => this.modifyBasketItem(params);

			const popup = new BX.CDialog({
				content_url: '/bitrix/tools/sale/product_search_dialog.php?'+
					//todo: 'lang='+this._settings.languageId+
					//todo: '&LID='+this._settings.siteId+
					'&caller=order_edit'+
					'&func_name='+funcName+
					'&STORE_FROM_ID=0'+
					'&public_mode=Y',
				height: Math.max(500, window.innerHeight-400),
				width: Math.max(800, window.innerWidth-400),
				draggable: true,
				resizable: true,
				min_height: 500,
				min_width: 800,
				zIndex: 3100
			});

			popup.Show();
		},
		setSetting(event)
		{
			if (event.target.dataset.settingId === 'taxIncludedOption')
			{
				const value = event.target.checked ? 'Y' : 'N';
				this.$root.$app.changeFormOption('taxIncluded', value);
			}
			else if (event.target.dataset.settingId === 'showDiscountInputOption')
			{
				const value = event.target.checked ? 'Y' : 'N';
				this.$root.$app.changeFormOption('showDiscountBlock', value);
			}
			else if (event.target.dataset.settingId === 'showTaxInputOption')
			{
				const value = event.target.checked ? 'Y' : 'N';
				this.$root.$app.changeFormOption('showTaxBlock', value);
			}
		},
		getSettingItem(item): HTMLElement
		{
			const input = Tag.render`
					<input type="checkbox"  class="ui-ctl-element">
				`;
			input.checked = item.checked;
			input.disabled = item.disabled ?? false;
			input.dataset.settingId = item.id;

			const hintNode = (
				Type.isStringFilled(item.hint)
					? Tag.render`<span class="catalog-product-form-setting-hint" data-hint="${item.hint}"></span>`
					: ''
			);

			const setting = Tag.render`
				<label class="ui-ctl ui-ctl-checkbox ui-ctl-w100">
					${input}
					<div class="ui-ctl-label-text ${item.disabled ? 'catalog-product-form-disabled-setting' : ''}">${item.title}${hintNode}</div>
				</label>
			`;

			BX.UI.Hint.init(setting);

			Event.bind(setting, 'change', this.setSetting.bind(this));

			return setting;
		},
		getSettingItems()
		{
			const items = [
				// {
				// 	id: 'taxIncludedOption',
				// 	checked: (this.options.taxIncluded === 'Y'),
				// 	title: this.localize.CATALOG_FORM_ADD_TAX_INCLUDED,
				// },
				{
					id: 'showDiscountInputOption',
					checked: (this.options.showDiscountBlock !== 'N'),
					title: this.localize.CATALOG_FORM_ADD_SHOW_DISCOUNTS_OPTION,
				},
				// {
				// 	id: 'showTaxInputOption',
				// 	checked: (this.options.showTaxBlock !== 'N'),
				// 	title: this.localize.CATALOG_FORM_ADD_SHOW_TAXES_OPTION,
				// },
			];

			return items;
		},

		prepareSettingsContent(): HTMLElement
		{
			const content = Tag.render`
					<div class='catalog-pf-product-config-popup'></div>
				`;

			this.settings.forEach(item => {
				content.append(this.getSettingItem(item));
			});

			return content;
		},
		showConfigPopup(event)
		{
			// if (!this.popupMenu)
			// {
				this.popupMenu = new Popup(null, event.target,
					{
						autoHide: true,
						draggable: false,
						offsetLeft: 0,
						offsetTop: 0,
						noAllPaddings: true,
						bindOptions: {forceBindPosition: true},
						closeByEsc: true,
						content: this.prepareSettingsContent()
					}
				);
			// }

			this.popupMenu.show();
		},
		openSlider(url, options)
		{
			if(!Type.isPlainObject(options))
			{
				options = {};
			}
			options = {...{cacheable: false, allowChangeHistory: false, events: {}}, ...options};
			return new Promise((resolve) =>
				{
					if(Type.isString(url) && url.length > 1)
					{
						options.events.onClose = function(event)
							{
								resolve(event.getSlider());
					};
					BX.SidePanel.Instance.open(url, options);
				}
				else
				{
					resolve();
				}
			});
		}
	},
	computed:
	{
		hasAccessToCatalog()
		{
			return this.options.isCatalogAccess;
		},
		localize()
		{
			return Vue.getFilteredPhrases('CATALOG_');
		},
		countItems()
		{
			return this.order.basket.length;
		},
		isCatalogHidden()
		{
			return this.options.isCatalogHidden;
		},
		...Vuex.mapState({
			productList: state => state.productList,
		})
	},
	mounted()
	{
		this.settings = this.getSettingItems();

		BX.UI.Hint.init();
	},
	// language=Vue
	template: `
		<div>
			<div class="catalog-pf-product-add">
				<div class="catalog-pf-product-add-wrapper">
					<span class="catalog-pf-product-add-link" @click="addBasketItemForm">{{localize.CATALOG_FORM_ADD_PRODUCT}}</span>
					<span
						v-if="hasAccessToCatalog && !isCatalogHidden"
						class="catalog-pf-product-add-link catalog-pf-product-add-link--gray"
						@click="showDialogProductSearch"
					>{{localize.CATALOG_FORM_ADD_PRODUCT_FROM_CATALOG}}</span>
					<span
						v-else-if="!isCatalogHidden"
						class="catalog-pf-product-add-link catalog-pf-product-add-link--gray catalog-pf-product-add-link--disabled"
						:data-hint="localize.CATALOG_FORM_ADD_PRODUCT_FROM_CATALOG_DENIED_HINT"
						data-hint-no-icon
					>{{localize.CATALOG_FORM_ADD_PRODUCT_FROM_CATALOG}}</span>
				</div>
				<div class="catalog-pf-product-configure-link" @click="showConfigPopup">{{localize.CATALOG_FORM_DISCOUNT_EDIT_PAGE_URL_TITLE}}</div>
			</div>
		</div>
	`
});

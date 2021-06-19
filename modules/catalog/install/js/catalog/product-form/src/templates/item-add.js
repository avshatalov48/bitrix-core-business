import {Vuex} from 'ui.vue.vuex';
import {Menu, Popup} from 'main.popup';
import {ajax, Event, Loc, Tag, Text, Type} from 'main.core';
import {Vue} from "ui.vue";
import {config} from "../config";

Vue.component(config.templateProductAddName,
{
	/**
	 * @emits 'changeRowData' {index: number, fields: object}
	 * @emits 'refreshBasket'
	 * @emits 'addItem'
	 */

	props: ['options'],
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
			let basket = this.$store.getters['productList/getBasket']();
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
								resetSku: true
							}
						}
					}
				).then(response => this.processResponse(response));
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
								urlBuilder: this.options.urlBuilder
							}
						}
					}
				).then(response => this.processResponse(response));
			}
		},
		processResponse(response)
		{
			const index = this.getInternalIndexByProductId(response.data.skuId);
			if (index < 0)
			{
				const productData = response.data;
				const price = Text.toNumber(productData.fields.PRICE);
				productData.fields = productData.fields || {};
				let newItem = this.$store.getters['productList/getBaseProduct']();
				newItem.fields = Object.assign(newItem.fields, {
					price,
					priceExclusive: price,
					basePrice: price,
					name: productData.fields.NAME || '',
					productId: productData.productId,
					skuId: productData.skuId,
					offerId: productData.skuId > 0 ? productData.skuId : productData.productId,
					module: 'catalog',
					isCustomPrice: Type.isNil(productData.fields.PRICE) ? 'Y' : 'N',
					discountType: this.options.defaultDiscountType,
				});

				delete(productData.fields);
				newItem = Object.assign(newItem, productData);
				newItem.sum = price;

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
			let basket = this.$store.getters['productList/getBasket']();
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
			this.popup = new Popup(null, null, {
				events: {
					onPopupClose: () => {this.popup.destroy()}
				},
				zIndex: 4000,
				autoHide: true,
				closeByEsc: true,
				closeIcon: true,
				titleBar: Loc.getMessage('CATALOG_FORM_BLOCK_PROD_EXIST_DLG_TITLE'),
				draggable: true,
				resizable: false,
				lightShadow: true,
				cacheable: false,
				overlay: true,
				content: Loc.getMessage('CATALOG_FORM_BLOCK_PROD_EXIST_DLG_TEXT').replace('#NAME#', params.name),
				buttons: this.getButtons(params),
			});

			this.popup.show();
		},
		getButtons(product)
		{
			let buttons = [];
			let params = product;
			buttons.push(
				new BX.UI.SaveButton(
					{
						text : Loc.getMessage('CATALOG_FORM_BLOCK_PROD_EXIST_DLG_OK'),
						onclick: () => {
							let productId = parseInt(params.id);
							let inx = this.getInternalIndexByProductId(productId);
							if(inx >= 0)
							{
								const item = this.$store.getters['productList/getBasket']()[inx];
								item.fields.quantity++;
								item.calculatedFields.QUANTITY++;
								this.onUpdateBasketItem(inx, item);
							}
							this.popup.destroy();
						}
					}
				)
			);

			buttons.push(
				new BX.UI.CancelButton(
					{
						text : Loc.getMessage('CATALOG_FORM_BLOCK_PROD_EXIST_DLG_NO'),
						onclick: () => {this.popup.destroy()}
					}
				)
			);
			return buttons;
		},
		showDialogProductSearch()
		{
			let funcName = 'addBasketItemFromDialogProductSearch';
			window[funcName] = params => this.modifyBasketItem(params);

			let popup = new BX.CDialog({
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
			input.dataset.settingId = item.id;

			const setting = Tag.render`
				<label class="ui-ctl ui-ctl-checkbox ui-ctl-w100">
					${input}
					<div class="ui-ctl-label-text">${item.title}</div>
				</label>
			`;

			Event.bind(setting, 'change', this.setSetting.bind(this));

			return setting;
		},
		prepareSettingsContent(): HTMLElement
		{
			const settings = [
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

			const content = Tag.render`
					<div class='catalog-pf-product-config-popup'></div>
				`;

			settings.forEach(item => {
				content.append(this.getSettingItem(item));
			});

			return content;
		},
		showConfigPopup(event)
		{
			if (!this.popupMenu)
			{
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
			}

			this.popupMenu.show();
		},
	},
	computed:
	{
		localize()
		{
			return Vue.getFilteredPhrases('CATALOG_');
		},
		countItems()
		{
			return this.order.basket.length;
		},
		...Vuex.mapState({
			productList: state => state.productList,
		})
	},
	template: `
		<div class="catalog-pf-product-add">
			<div class="catalog-pf-product-add-wrapper">
				<span class="catalog-pf-product-add-link" @click="addBasketItemForm">{{localize.CATALOG_FORM_ADD_PRODUCT}}</span>
				<span class="catalog-pf-product-add-link catalog-pf-product-add-link--gray" @click="showDialogProductSearch">{{localize.CATALOG_FORM_ADD_PRODUCT_FROM_CATALOG}}</span>
			</div>
			<div class="catalog-pf-product-configure-link" @click="showConfigPopup">{{localize.CATALOG_FORM_DISCOUNT_EDIT_PAGE_URL_TITLE}}</div>
		</div>
	`
});
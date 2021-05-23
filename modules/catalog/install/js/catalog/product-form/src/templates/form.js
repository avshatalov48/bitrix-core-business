import {config} from '../config';

import {Vue} from 'ui.vue';
import {Vuex} from 'ui.vue.vuex';
import "currency";
import "ui.layout-form";
import "ui.forms";
import "ui.buttons";

import "./row";
import "./item-add";
import {ProductFormElementPosition} from "../product-form";

Vue.component(config.templateName,
{
	props: ['options'],
	created()
	{
		BX.ajax.runAction(
			"catalog.productSelector.getFileInput",
			{ json: { iblockId: this.options.iblockId } }
		);
	},
	methods:
	{
		refreshBasket()
		{
			this.$store.dispatch('productList/refreshBasket');
		},
		changeProduct(item)
		{
			this.$root.$app.changeProduct(item);
		},
		changeRowData(item)
		{
			delete(item.fields.fields);
			this.$store.dispatch('productList/changeItem', item);
		},
		removeItem(item)
		{
			this.$root.$app.removeProduct(item);
		},
		addItem()
		{
			this.$root.$app.addProduct();
		},
	},
	computed:
	{
		localize()
		{
			return Vue.getFilteredPhrases('CATALOG_');
		},
		editable()
		{
			return this.$root.$app.editable;
		},

		showTaxResult()
		{
			return this.options.showTaxBlock !== 'N';
		},

		showResults()
		{
			return this.options.showResults !== false;
		},

		showButtonsTop()
		{
			return this.options.singleProductMode !== true
				&& this.editable
				&& this.options.buttonsPosition !== ProductFormElementPosition.BOTTOM
			;
		},

		showButtonsBottom()
		{
			return this.options.singleProductMode !== true
				&& this.editable
				&& this.options.buttonsPosition === ProductFormElementPosition.BOTTOM
			;
		},

		showResultBlock()
		{
			return this.showResults || this.enableAddButtons;
		},

		countItems()
		{
			return this.productList.basket.length;
		},

		...Vuex.mapState({
			productList: state => state.productList,
		})
	},
	template: `
	<div class="catalog-product-form-container">
		<${config.templateProductAddName}
			:options="options" 
			@refreshBasket="refreshBasket" 
			@addItem="addItem"
			@changeRowData="changeRowData"
			@changeProduct="changeProduct" 
			v-if="showButtonsTop"
		/>
		<div v-for="(item, index) in productList.basket" :key="item.selectorId">
			<${config.templateProductRowName} 
				:basketItem="item" 
				:basketItemIndex="index"  
				:countItems="countItems"
				:options="options"
				:editable="editable"
				@changeProduct="changeProduct" 
				@changeRowData="changeRowData" 
				@removeItem="removeItem" 
				@refreshBasket="refreshBasket" 
			/>
		</div>
		<${config.templateProductAddName}
			:options="options" 
			@refreshBasket="refreshBasket" 
			@addItem="addItem"
			@changeRowData="changeRowData"
			@changeProduct="changeProduct" 
			v-if="showButtonsBottom"
		/>
		<div class="catalog-pf-result-line"></div>
		<div class="catalog-pf-result-wrapper" v-if="showResultBlock">
			<table class="catalog-pf-result" v-if="showResultBlock">
				<tr v-if="showResults">
					<td>
						<span class="catalog-pf-text">{{localize.CATALOG_FORM_PRODUCTS_PRICE}}:</span>
					</td>
					<td>
						<span v-html="productList.total.sum"
							:class="productList.total.result !== productList.total.sum ? 'catalog-pf-text catalog-pf-text--line-through' : 'catalog-pf-text'"
						></span>
						<span class="catalog-pf-symbol" v-html="options.currencySymbol"></span>
					</td>
				</tr>
				<tr v-if="showResults">
					<td>
						<span class="catalog-pf-text catalog-pf-text--discount">{{localize.CATALOG_FORM_TOTAL_DISCOUNT}}:</span>
					</td>
					<td>
						<span class="catalog-pf-text catalog-pf-text--discount" v-html="productList.total.discount"></span>
						<span class="catalog-pf-symbol" v-html="options.currencySymbol"></span>
					</td>
				</tr>
				<tr v-if="showResults && showTaxResult">
					<td class="catalog-pf-tax">
						<span class="catalog-pf-text catalog-pf-text--tax">{{localize.CATALOG_FORM_TAX_TITLE}}:</span>
					</td>
					<td class="catalog-pf-tax">
						<span class="catalog-pf-text catalog-pf-text--tax" v-html="productList.total.taxSum"></span>
						<span class="catalog-pf-symbol" v-html="options.currencySymbol"></span>
					</td>
				</tr>
				<tr v-if="showResults">
					<td class="catalog-pf-result-padding">
						<span class="catalog-pf-text catalog-pf-text--total catalog-pf-text--border">{{localize.CATALOG_FORM_TOTAL_RESULT}}:</span>
					</td>
					<td class="catalog-pf-result-padding">
						<span class="catalog-pf-text catalog-pf-text--total" v-html="productList.total.result"></span>
						<span class="catalog-pf-symbol catalog-pf-symbol--total" v-html="options.currencySymbol"></span>
					</td>
				</tr>
			</table>
		</div>
	</div>
`,
});
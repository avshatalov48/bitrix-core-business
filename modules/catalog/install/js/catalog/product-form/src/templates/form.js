import {config} from '../config';

import {Vue} from 'ui.vue';
import {Vuex} from 'ui.vue.vuex';
import "currency";
import "ui.layout-form";
import "ui.forms";
import "ui.buttons";

import "./row";
import "./elements/panel-buttons";
import {FormElementPosition} from "../types/form-element-position";
import {FormMode} from "../types/form-mode";

Vue.component(config.templateName,
{
	props: {
		options: Object,
		mode: String,
	},
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

		emitErrorsChange()
		{
			this.$root.$app.emitErrorsChange();
		},

		changeRowData(item)
		{
			delete(item.product.fields);
			this.$store.commit('productList/updateItem', item);
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
				&& this.mode !== FormMode.READ_ONLY
				&& this.options.buttonsPosition !== FormElementPosition.BOTTOM
			;
		},

		showButtonsBottom()
		{
			return this.options.singleProductMode !== true
				&& this.mode !== FormMode.READ_ONLY
				&& this.options.buttonsPosition === FormElementPosition.BOTTOM
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

		totalResultLabel()
		{
			return this.options.hasOwnProperty('totalResultLabel') && this.options.totalResultLabel
				? this.options.totalResultLabel
				: this.localize.CATALOG_FORM_TOTAL_RESULT;
		},

		...Vuex.mapState({
			productList: state => state.productList,
		})
	},
	// language=Vue
	template: `
	<div class="catalog-product-form-container">
		<${config.templatePanelButtons}
			:options="options"
			:mode="mode"
			@refreshBasket="refreshBasket"
			@addItem="addItem"
			@changeRowData="changeRowData"
			@changeProduct="changeProduct"
			v-if="showButtonsTop"
		/>
		<div v-for="(item, index) in productList.basket" :key="item.selectorId">
			<${config.templateRowName}
				:basketItem="item"
				:basketItemIndex="index"
				:basketLength="productList.basket.length"
				:countItems="countItems"
				:options="options"
				:mode="mode"
				@changeProduct="changeProduct"
				@changeRowData="changeRowData"
				@removeItem="removeItem"
				@refreshBasket="refreshBasket"
				@emitErrorsChange="emitErrorsChange"
			/>
		</div>
		<${config.templatePanelButtons}
			:options="options"
			:mode="mode"
			@refreshBasket="refreshBasket"
			@addItem="addItem"
			@changeRowData="changeRowData"
			@changeProduct="changeProduct"
			v-if="showButtonsBottom"
		/>
		<${config.templatePanelCompilation}
			v-if="options.showCompilationModeSwitcher"
			:compilationOptions="options.compilationFormOption"
			:mode="mode"
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
					<td class="catalog-pf-result-padding-bottom">
						<span class="catalog-pf-text catalog-pf-text--discount">{{localize.CATALOG_FORM_TOTAL_DISCOUNT}}:</span>
					</td>
					<td class="catalog-pf-result-padding-bottom">
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
						<span class="catalog-pf-text catalog-pf-text--total catalog-pf-text--border">{{totalResultLabel}}:</span>
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

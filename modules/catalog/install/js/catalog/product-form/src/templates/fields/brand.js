import {ajax, Cache, Dom, Loc, Tag, Text, Type} from 'main.core';
import {Vue} from "ui.vue";
import {config} from "../../config";
import type {BaseEvent} from "main.core.events";
import {EventEmitter} from 'main.core.events';
import {Dialog, TagSelector} from "ui.entity-selector";

Vue.component(config.templateFieldBrand,
{
	/**
	 * @emits 'changeBrand' {values: Array<any>}
	 */

	props: {
		brands: [Array, String],
		options: Object,
		editable: Boolean,
		hasError: Boolean,
		selectorId: String,
	},
	data()
	{
		return {
			cache: new Cache.MemoryCache(),
		}
	},
	created()
	{
		if (this.editable)
		{
			this.selector = new TagSelector({
				id: this.selectorId,
				dialogOptions: {
					id: this.selectorId,
					context: 'CATALOG_BRANDS',
					// enableSearch: true,
					preselectedItems: this.getPreselectedBrands(),
					events: {
						'Item:onSelect': this.onBrandChange.bind(this),
						'Item:onDeselect': this.onBrandChange.bind(this),
						'Search:onItemCreateAsync': this.createBrand.bind(this)
					},
					searchTabOptions: {
						stub: true,
						stubOptions: {
							title: Tag.message`${'CATALOG_FORM_BRAND_SELECTOR_IS_EMPTY_TITLE'}`,
							subtitle: Tag.message`${'CATALOG_FORM_BRAND_SELECTOR_IS_EMPTY_SUBTITLE'}`,
							arrow: true
						}
					},
					searchOptions: {
						allowCreateItem: true
					},
					entities: [
						{
							id: 'brand',
							options: {
								iblockId: this.options.iblockId,
							},
							dynamicSearch: true,
							dynamicLoad: true
						},
					]
				},
			});
			this.isSelectedByProductChange = false;

			this.$parent.$on('onInlineSelectorProductChange', this.selectCurrentBrands.bind(this));
		}
	},
	mounted()
	{
		if (this.editable)
		{
			this.selector.renderTo(this.$refs.brandSelectorWrapper);
		}
		else
		{
			this.brands.forEach((brand, brandIndex, brands) => {
				const separator = (brandIndex < brands.length - 1) ? ',&nbsp;' : '';
				this.$refs.brandSelectorWrapper.appendChild(Tag.render`
					<span>
						<span
							class="catalog-pf-product-input-brand-read-only-item"
							style="background-image:url('${brand['IMAGE_SRC']}');"
						></span>
						${brand['NAME'] + separator}
					</span>
				`);
			});
		}
	},
	methods:
	{
		selectCurrentBrands(brands: Array)
		{
			this.isSelectedByProductChange = true;
			this.brands = brands;
			if (this.selector.getDialog().isLoaded())
			{
				this.selector.getDialog().deselectAll();
				this.selectDialogItems();
			}
			else
			{
				this.selector.getDialog().load();
				EventEmitter.subscribe(this.selector.getDialog(), 'onLoad', this.selectDialogItems.bind(this));
			}
		},
		selectDialogItems()
		{
			this.brands.forEach(brand => {
				const item = this.selector.getDialog().getItem({id: brand['VALUE'], entityId: 'brand'});
				item.select();
			});
			this.isSelectedByProductChange = false;
		},
		getPreselectedBrands()
		{
			if (!Type.isArray(this.brands) || this.brands.length === 0)
			{
				return [];
			}

			return this.brands.map((item) => {
				return ['brand', item['VALUE']]
			});
		},
		onBrandChange(event: BaseEvent)
		{
			const items = event.getTarget().getSelectedItems();
			const resultValues = [];
			if (Type.isArray(items))
			{
				items.forEach((item) => {
					resultValues.push({
						'VALUE': item.getId(),
						'NAME': item.getTitle(),
						'IMAGE_SRC': item.getAvatar(),
					});
				});
			}

			const eventData = {
				resultValues: resultValues,
				isSelectedByProductChange: this.isSelectedByProductChange,
			};

			this.$emit('changeBrand', eventData);
		},
		createBrand(event): Promise
		{
			const {searchQuery} = event.getData();
			const iblockId = this.options.iblockId;

			return new Promise(
				(resolve, reject) => {
					const dialog: Dialog = event.getTarget();
					const fields = {
						name: searchQuery.getQuery(),
						iblockId,
					};

					dialog.showLoader();
					ajax.runAction(
						'catalog.productForm.createBrand',
						{
							data: {
								fields
							}
						}
					)
						.then(response => {
							dialog.hideLoader();
							const item = dialog.addItem({
								id: response.data.id,
								entityId: 'brand',
								title: searchQuery.getQuery(),
								tabs: dialog.getRecentTab().getId(),
							});

							if (item)
							{
								item.select();
							}

							dialog.hide();
							resolve();
						})
						.catch(() => reject());
				});
		},
	},
	computed:
	{
		localize()
		{
			return Vue.getFilteredPhrases('CATALOG_');
		},
	},
	// language=Vue
	template: `
		<div class="catalog-pf-product-control ui-ctl-w100" v-bind:class="{ 'ui-ctl-danger': hasError }">
			<div class="catalog-pf-product-input-wrapper" ref="brandSelectorWrapper" :id="selectorId"></div>
		</div>
	`
});

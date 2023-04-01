import 'ui.switcher';
import 'ui.forms';
import {EventEmitter} from 'main.core.events';
import {CheckboxListSections} from './sections';
import {CheckboxListCategory} from './category';
import {Loc, Type} from "main.core";

export const Content = {
	components: {
		CheckboxListSections,
		CheckboxListCategory,
	},

	props: [
		'dialog',
		'popup',
		'columnCount',
		'compactField',
		'lang',
		'sections',
		'categories',
		'options',
	],

	data()
	{
		return {
			dataSections: this.sections,
			dataCategories: this.categories,
			dataOptions: this.options,
			dataCompactField: this.compactField,
			search: '',

			longContent: false,
			scrollIsBottom: true,
			scrollIsTop: false,
		}
	},

	methods:{
		renderSwitcher()
		{
			if (this.dataCompactField)
			{
				const switcher = new BX.UI.Switcher({
					node: this.$refs.switcher,
					checked: this.dataCompactField.value,
					size: 'small',
					handlers: {
						toggled: () => this.handleSwitcherToggled(),
					},
				});
			}
		},
		handleSwitcherToggled()
		{
			this.dataCompactField.value = !this.dataCompactField.value;
		},
		handleCheckBoxToggled(id)
		{
			const item = this.dataOptions.find(option => option.id === id);

			if(item) {
				item.value = !item.value;
			}
		},
		clearSearch()
		{
			this.search = '';
		},
		handleClearSearchButtonClick()
		{
			this.$refs.searchInput.focus();
			this.clearSearch();
		},
		handleSectionsToggled(key)
		{
			const section = this.dataSections.find(section => section.key === key);

			if(section) {
				section.value = !section.value;
			}
		},
		getOptionsByCategory(category)
		{
			return this.optionsByTitle.filter(item => item.categoryKey === category)
		},
		getCheckedOptionsId()
		{
			return this.dataOptions.filter(option => option.value === true).map(option => option.id);
		},
		checkLongContent()
		{
			if (this.$refs.container)
			{
				this.longContent = this.$refs.container.clientHeight < this.$refs.container.scrollHeight;
			}
			else
			{
				this.longContent = false;
			}
		},
		getBottomIndent() {
			this.scrollIsBottom = !((this.$refs.container.scrollTop + this.$refs.container.clientHeight) >= this.$refs.container.scrollHeight - 10);
		},
		getTopIndent() {
			this.scrollIsTop = this.$refs.container.scrollTop;
		},
		handleScroll() {
			this.getBottomIndent();
			this.getTopIndent();
		},
		handleSearchEscKeyUp() {
			this.$refs.container.focus();
			this.clearSearch();
		},
		defaultSettings()
		{
			this.clearSearch();

			if (this.dataCompactField && this.dataCompactField.value !== this.dataCompactField.defaultValue)
			{
				this.$refs.switcher.click();
			}

			this.dataOptions.forEach(option => option.value = option.defaultValue);
			if (Array.isArray(this.dataSections))
			{
				this.dataSections.forEach(sections => sections.value = true);
			}
		},
		selectAll()
		{
			this.categoryBySection.forEach((category) => {
				this.getOptionsByCategory(category.key).forEach(option => option.value = true);
			});
		},
		deselectAll()
		{
			this.categoryBySection.forEach((category) => {
				this.getOptionsByCategory(category.key).forEach(option => option.value = false);
			});
		},
		cancel()
		{
			this.popup.destroy();
		},
		apply()
		{
			EventEmitter.emit(
				this.dialog,
				'onApply',
				{
					switcher: this.dataCompactField,
					fields: this.getCheckedOptionsId(),
				}
			);
			this.popup.destroy();
		},
	},

	watch: {
		search()
		{
			this.$nextTick(() => {
				this.checkLongContent();
			})
		},
		categoryBySection()
		{
			this.$nextTick(() => {
				this.checkLongContent();
			})
		},
	},

	computed: {
		visibleOptions() {
			if(!Array.isArray(this.dataSections) || !this.dataSections.length)
			{
				return this.optionsByTitle;
			}

			return this.optionsByTitle.filter(option => {
				const category = this.dataCategories.find(category => category.key === option.categoryKey);
				const section = this.dataSections.find(section => section.key === category.sectionKey);
				return section?.value;
			});

		},
		isEmptyContent()
		{
			return this.visibleOptions.length > 0;
		},
		isSearchDisabled(): boolean
		{
			if(this.dataSections)
			{
				return !this.dataSections.some(section => section.value) ;
			}

			return false;
		},
		optionsByTitle()
		{
			return this.dataOptions.filter(item => item.title.toLowerCase().indexOf(this.search.toLowerCase()) !== -1);
		},
		categoryBySection()
		{
			if (!Array.isArray(this.dataSections) || !Type.isArrayFilled(this.dataSections))
			{
				return this.dataCategories;
			}

			return this.dataCategories.filter(category => {
				const section = this.dataSections.find(section => category.sectionKey === section.key);
				return section?.value;
			});
		},
		wrapperClassName()
		{
			return [
				'ui-checkbox-list__wrapper',
				{'--long': this.longContent},
				{'--bottom': this.scrollIsBottom},
				{'--top': this.scrollIsTop},
			];
		},
		searchClassName()
		{
			return [
				'ui-checkbox-list__search',
				{'--disabled': this.isSearchDisabled},
			];
		},
		SwitcherText(): string
		{
			return (
				Type.isStringFilled(this.lang.switcher)
					? this.lang.switcher
					: Loc.getMessage('UI_CHECKBOX_LIST_DEFAULT_SETTINGS_SWITCHER')
			);
		},
		placeholderText(): string
		{
			return (
				Type.isStringFilled(this.lang.placeholder)
					? this.lang.placeholder
					: Loc.getMessage('UI_CHECKBOX_LIST_DEFAULT_SETTINGS_PLACEHOLDER')
			);
		},
		defaultSettingsBtnText(): string
		{
			return (
				Type.isStringFilled(this.lang.defaultBtn)
					? this.lang.defaultBtn
					: Loc.getMessage('UI_CHECKBOX_LIST_DEFAULT_SETTINGS')
			);
		},
		applyBtnText(): string
		{
			return (
				Type.isStringFilled(this.lang.acceptBtn)
					? this.lang.acceptBtn
					: Loc.getMessage('UI_CHECKBOX_LIST_DEFAULT_ACCEPT_BUTTON')
			);
		},
		cancelBtnText(): string
		{
			return (
				Type.isStringFilled(this.lang.cancelBtn)
					? this.lang.cancelBtn
					: Loc.getMessage('UI_CHECKBOX_LIST_DEFAULT_CANCEL_BUTTON')
			);
		},
		selectAllBtnText(): string
		{
			return (
				Type.isStringFilled(this.lang.selectAllBtn)
					? this.lang.selectAllBtn
					: Loc.getMessage('UI_CHECKBOX_LIST_DEFAULT_SELECT_ALL')
			);
		},
		deselectAllBtnText(): string
		{
			return (
				Type.isStringFilled(this.lang.deselectAllBtn)
					? this.lang.deselectAllBtn
					: Loc.getMessage('UI_CHECKBOX_LIST_DEFAULT_SETTINGS_DESELECT_ALL')
			);
		},
		emptyStateTitleText(): string
		{
			return Loc.getMessage('UI_CHECKBOX_LIST_DEFAULT_SETTINGS_EMPTY_STATE_TITLE');
		},
		emptyStateDescriptionText(): string
		{
			return Loc.getMessage('UI_CHECKBOX_LIST_DEFAULT_SETTINGS_EMPTY_STATE_DESCRIPTION');
		},
	},

	mounted()
	{
		this.renderSwitcher();

		this.$nextTick(() => {
			this.checkLongContent();
		})
	},

	template: `
		<div class="ui-checkbox-list">
		<div class="ui-checkbox-list__header">

			<checkbox-list-sections
				v-if="sections"
				:sections="dataSections"
				@sectionToggled="handleSectionsToggled"
			/>

			<div class="ui-checkbox-list__header_options">
				<div
					v-if="compactField"
					class="ui-checkbox-list__switcher"
				>
					<div class="ui-checkbox-list__switcher-text">
						{{ SwitcherText }}
					</div>
					<div class="switcher" ref="switcher"></div>
				</div>
				<div
					:class="searchClassName"
				>
					<div class="ui-checkbox-list__search-wrapper">
						<div class="ui-ctl ui-ctl-textbox ui-ctl-before-icon ui-ctl-after-icon ui-ctl-w100">

							<div class="ui-ctl-before ui-ctl-icon-search"></div>
							<button
								@click="handleClearSearchButtonClick"
								class="ui-ctl-after ui-ctl-icon-clear ui-checkbox-list__search-clear"
							></button>
							<input
								:placeholder="placeholderText"
								type="text"
								class="ui-ctl-element"
								v-model="search"
								@keyup.esc.stop="handleSearchEscKeyUp"
								ref="searchInput"
							>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div
			ref="wrapper"
			:class="wrapperClassName"
		>
			<div
				ref="container"
				class="ui-checkbox-list__container"
				@scroll="handleScroll"
				tabindex="0"
				v-if="isEmptyContent"
			>
				<checkbox-list-category
					v-for="category in categoryBySection"
					:key="category.key"
					:category="category"
					:columnCount="columnCount"
					:options="getOptionsByCategory(category.key)"
					@changeOption="handleCheckBoxToggled"
				/>
			</div>
			<div
				v-else
				class="ui-checkbox-list__empty"
			>
				<img
					src="/bitrix/js/ui/dialogs/checkbox-list/images/ui-checkbox-list-empty.svg"
					:alt="emptyStateTitleText">
				<div class="ui-checkbox-list__empty-title">
					{{ emptyStateTitleText }}
				</div>
				<div class="ui-checkbox-list__empty-description">
					{{ emptyStateDescriptionText }}
				</div>
			</div>
		</div>

		<div class="ui-checkbox-list__footer">
			<div class="ui-checkbox-list__footer-block">
				<div
					class="ui-checkbox-list__footer-link --default"
					@click="defaultSettings()"
				>
					{{ defaultSettingsBtnText }}
				</div>
			</div>
			<div class="ui-checkbox-list__footer-block">
				<button
					@click="apply()"
					class="ui-btn ui-btn-success">
					{{ applyBtnText }}
				</button>

				<button
					@click="cancel()"
					class="ui-btn ui-btn-link"
				>
					{{ cancelBtnText }}
				</button>
			</div>
			<div class="ui-checkbox-list__footer-block --right">
				<div
					@click="selectAll()"
					class="ui-checkbox-list__footer-link"
				>
					{{ selectAllBtnText }}
				</div>
				<div
					@click="deselectAll()"
					class="ui-checkbox-list__footer-link"
				>
					{{ deselectAllBtnText }}
				</div>
			</div>
		</div>
		</div>
	`,
}
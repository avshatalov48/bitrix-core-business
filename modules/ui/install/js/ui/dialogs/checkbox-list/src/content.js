import { Loc, Type } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';
import 'ui.forms';
import 'ui.switcher';
import { CheckboxListCategory } from './category';
import type { CheckboxListOption } from './checkbox-list';
import { CheckboxListParams } from './checkbox-list';
import { CheckboxComponent } from './controls/checkbox-component';
import { TextToggleComponent } from './controls/texttoggle-component';
import { CheckboxListSections } from './sections';

export const Content = {
	components: {
		CheckboxListSections,
		CheckboxListCategory,
		CheckboxComponent,
		TextToggleComponent,
	},

	props: [
		'dialog',
		'popup',
		'columnCount',
		'compactField',
		'customFooterElements',
		'lang',
		'sections',
		'categories',
		'options',
		'params',
		'context',
	],

	data()
	{
		return {
			dataSections: this.sections,
			dataCategories: this.categories,
			dataCompactField: this.compactField,
			dataOptions: this.getPreparedDataOptions(),
			dataParams: this.getPreparedParams(),

			optionsRef: new Map(),
			search: '',
			longContent: false,
			scrollIsBottom: true,
			scrollIsTop: false,
		};
	},

	methods: {
		getPreparedDataOptions(): Map<string, CheckboxListOption>
		{
			return new Map(this.options.map((option) => [option.id, option]));
		},
		getPreparedParams(): CheckboxListParams
		{
			const { params } = this;

			return {
				useSearch: Boolean(params.useSearch ?? true),
				useSectioning: Boolean(params.useSectioning ?? true),
				closeAfterApply: Boolean(params.closeAfterApply ?? true),
				showBackToDefaultSettings: Boolean(params.showBackToDefaultSettings ?? true),
				isEditableOptionsTitle: Boolean(params.isEditableOptionsTitle ?? false),
				destroyPopupAfterClose: Boolean(params.destroyPopupAfterClose ?? true),
			};
		},
		renderSwitcher()
		{
			if (this.dataCompactField)
			{
				new BX.UI.Switcher({
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
			if (this.dataCompactField)
			{
				this.dataCompactField.value = !this.dataCompactField.value;
			}
		},
		clearSearch()
		{
			this.search = '';
		},
		handleClearSearchButtonClick()
		{
			this.setFocusToSearchInput();
			this.clearSearch();
		},
		setFocusToSearchInput()
		{
			this.$refs?.searchInput?.focus();
		},
		handleSectionsToggled(key)
		{
			const section = this.dataSections.find((item) => item.key === key);

			if (section)
			{
				section.value = !section.value;
			}
		},
		getOptionsByCategory(category = null)
		{
			return this.getOptions().filter((item) => item.categoryKey === category);
		},
		getOptions(): CheckboxListOption[]
		{
			return this.optionsByTitle;
		},
		getCheckedOptionsId(): string[]
		{
			return this.getCheckedOptions().map((option) => option.getId());
		},
		getCheckedOptions(): CheckboxListOption[]
		{
			return this.getOptionRefs().filter((option) => option.getValue());
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
		getBottomIndent()
		{
			const { scrollTop, clientHeight, scrollHeight } = this.$refs.container;

			this.scrollIsBottom = (scrollTop + clientHeight) < scrollHeight - 10;
		},
		getTopIndent()
		{
			this.scrollIsTop = this.$refs.container.scrollTop;
		},
		handleScroll()
		{
			this.getBottomIndent();
			this.getTopIndent();
		},
		handleSearchEscKeyUp() {
			this.$refs.container.focus();
			this.clearSearch();
		},
		defaultSettings()
		{
			const event = new BaseEvent({
				data: {
					switcher: this.dataCompactField,
					fields: this.getCheckedOptionsId(),
				},
			});

			EventEmitter.emit(this.dialog, 'onDefault', event);

			if (event.isDefaultPrevented())
			{
				return;
			}

			this.clearSearch();

			const { dataCompactField, sections, categories, $refs } = this;

			if (dataCompactField && dataCompactField.value !== dataCompactField.defaultValue)
			{
				$refs.switcher.click();
			}

			this.dataSections = sections;
			this.dataOptions = this.getPreparedDataOptions();
			this.dataCategories = categories;

			this.setDefaultValuesForOptions();
		},
		setDefaultValuesForOptions(): void
		{
			void this.$nextTick(() => {
				this.getOptionRefs().forEach(
					(option) => option.setValue(this.dataOptions.get(option.getId()).defaultValue),
				);
			});
		},
		toggleOption(id: string): void
		{
			const option = this.optionsRef.get(id);
			if (!option)
			{
				return;
			}

			option.setValue(!option.getValue());
		},
		onSelectAllClick(): void
		{
			if (this.isAllSelected)
			{
				this.deselectAll();
			}
			else
			{
				this.selectAll();
			}
		},
		select(id: string, value: boolean = true): void
		{
			const option = this.getOptionRefs().find((item) => item.id === id);
			option?.setValue(value);
		},
		selectAll()
		{
			const visibleOptionIds: Set<string> = new Set(this.getOptions().map((option) => option.id));

			this.getOptionRefs().forEach((option) => {
				return !option.isLocked && visibleOptionIds.has(option.getId()) && option.setValue(true);
			});
		},
		deselectAll()
		{
			const visibleOptionIds: Set<string> = new Set(this.getOptions().map((option) => option.id));

			this.getOptionRefs().forEach((option) => {
				return !option.isLocked && visibleOptionIds.has(option.getId()) && option.setValue(false);
			});
		},
		getOptionRefs(): []
		{
			return [...this.optionsRef.values()];
		},
		cancel(): void
		{
			EventEmitter.emit(this.dialog, 'onCancel');

			this.restoreOptionValues();
			this.destroyOrClosePopup();
		},
		restoreOptionValues(): void
		{
			this.getOptionRefs().forEach((option) => option.setStateFromProps());
		},
		apply(): void
		{
			if (this.isCheckedCheckboxes)
			{
				return;
			}

			const fields = this.getCheckedOptionsId();

			const eventParams = {
				switcher: this.dataCompactField,
				fields,
				data: {
					titles: this.getOptionTitles(),
				},
			};
			EventEmitter.emit(this.dialog, 'onApply', eventParams);

			this.adjustOptions(fields);

			if (this.dataParams.closeAfterApply)
			{
				this.destroyOrClosePopup();
			}
		},
		getOptionTitles(): {[key: string]: string}[]
		{
			const titles = {};

			this.getOptionRefs().forEach((option) => {
				titles[option.getId()] = option.getTitle();
			});

			return titles;
		},
		adjustOptions(checkedFieldIds: string[] = []): void
		{
			for (const option of this.optionsRef.values())
			{
				const id = option.getId();
				const value = checkedFieldIds.includes(id);

				this.dataOptions.set(id, {
					...this.dataOptions.get(id),
					title: option.getTitle(),
					value,
				});

				void this.$nextTick(() => option.setStateFromProps(value));
			}
		},
		destroyOrClosePopup(): void
		{
			if (this.dataParams.destroyPopupAfterClose)
			{
				this.destroyPopup();
			}
			else
			{
				this.closePopup();
			}
		},
		destroyPopup()
		{
			this.popup.destroy();
		},
		closePopup()
		{
			this.popup.close();
		},
		setOptionRef(id: string, ref): void
		{
			this.optionsRef.set(id, ref);
		},
		isAllSectionsDisabled(): boolean
		{
			return (
				Type.isArrayFilled(this.dataSections)
				&& this.dataSections.every((section) => section.value === false)
			);
		},
		onToggleOption(event)
		{
			if (this.dataOptions.has(event.id))
			{
				const option = this.dataOptions.get(event.id);
				option.value = event.isChecked;
				this.dataOptions.set(event.id, option);
			}
		},
	},

	watch: {
		search()
		{
			void this.$nextTick(() => this.checkLongContent());
		},
		categoryBySection()
		{
			void this.$nextTick(() => this.checkLongContent());
		},
	},

	computed: {
		visibleOptions()
		{
			const { dataSections, optionsByTitle, dataCategories } = this;

			if (!Type.isArrayFilled(dataSections))
			{
				return optionsByTitle;
			}

			return optionsByTitle.filter((option) => {
				const category = dataCategories.find((item) => item.key === option.categoryKey);
				const section = dataSections.find((item) => item.key === category.sectionKey);

				return section?.value;
			});
		},
		isEmptyContent()
		{
			return Type.isArrayFilled(this.visibleOptions);
		},
		// @temporary temp, waiting for a new ui for this case
		isNarrowWidth(): boolean
		{
			return (window.innerWidth * 0.9 < 500);
		},
		isSearchDisabled(): boolean
		{
			if (Type.isArrayFilled(this.dataSections))
			{
				return !this.dataSections.some((section) => section.value);
			}

			return false;
		},
		isCheckedCheckboxes(): boolean
		{
			for (const option of this.optionsRef.values())
			{
				if (option.getValue() === true && option.locked !== true)
				{
					return false;
				}
			}

			return true;
		},
		optionsByTitle(): CheckboxListOption[]
		{
			const options: CheckboxListOption[] = [...this.dataOptions.values()];

			return options.filter((item) => item.title.toLowerCase().includes(this.search.toLowerCase()));
		},
		categoryBySection()
		{
			if (!Type.isArrayFilled(this.dataSections))
			{
				return this.dataCategories;
			}

			return this.dataCategories.filter((category) => {
				const section = this.dataSections.find((item) => category.sectionKey === item.key);

				return section?.value;
			});
		},
		wrapperClassName()
		{
			return [
				'ui-checkbox-list__wrapper',
				{ '--long': this.longContent },
				{ '--bottom': this.scrollIsBottom },
				{ '--top': this.scrollIsTop },
			];
		},
		searchClassName()
		{
			return [
				'ui-checkbox-list__search',
				{ '--disabled': this.isSearchDisabled },
			];
		},
		applyClassName()
		{
			return [
				'ui-btn ui-btn-primary',
				{ 'ui-btn-disabled': this.isCheckedCheckboxes },
			];
		},
		selectAllClassName()
		{
			return [
				'ui-checkbox-list__footer-link --select-all',
				{ '--narrow': this.isNarrowWidth },
			];
		},
		switcherText(): string
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
					: Loc.getMessage('UI_CHECKBOX_LIST_DEFAULT_SETTINGS_MSGVER_1')
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
					: Loc.getMessage('UI_CHECKBOX_LIST_DEFAULT_SELECT_ALL_MSGVER_1')
			);
		},
		emptyStateTitleText(): string
		{
			if (this.isAllSectionsDisabled())
			{
				return (
					Type.isStringFilled(this.lang.allSectionsDisabledTitle)
						? this.lang.allSectionsDisabledTitle
						: Loc.getMessage('UI_CHECKBOX_LIST_DEFAULT_SETTINGS_EMPTY_STATE_TITLE_MSGVER_1')
				);
			}

			return (
				Type.isStringFilled(this.lang.emptyStateTitle)
					? this.lang.emptyStateTitle
					: Loc.getMessage('UI_CHECKBOX_LIST_DEFAULT_SETTINGS_EMPTY_STATE_TITLE_MSGVER_1')
			);
		},
		emptyStateDescriptionText(): string
		{
			if (this.isAllSectionsDisabled())
			{
				return '';
			}

			return (
				Type.isStringFilled(this.lang.emptyStateDescription)
					? this.lang.emptyStateDescription
					: Loc.getMessage('UI_CHECKBOX_LIST_DEFAULT_SETTINGS_EMPTY_STATE_DESCRIPTION_MSGVER_1')
			);
		},
		isAllSelected(): boolean
		{
			const isAllSelected = this.getOptionRefs()
				.filter((option) => !option.isLocked)
				.every((option) => option.getValue() === true)
			;
			const isSomeSelected = this.getOptionRefs()
				.filter((option) => !option.isLocked)
				.some((option) => option.getValue() === true && !option.isLocked)
			;

			if (
				!isAllSelected
				&& isSomeSelected
				&& this.$refs.selectAllCheckbox
			)
			{
				this.$refs.selectAllCheckbox.indeterminate = true;

				return false;
			}

			if (this.$refs.selectAllCheckbox)
			{
				this.$refs.selectAllCheckbox.indeterminate = false;
			}

			return isAllSelected;
		},
	},

	mounted()
	{
		this.renderSwitcher();

		void this.$nextTick(() => {
			this.checkLongContent();
			this.setFocusToSearchInput();
		});
	},

	template: `
		<div class="ui-checkbox-list">
			<div
				class="ui-checkbox-list__header"
				v-if="dataParams.useSearch || (dataSections && dataParams.useSectioning)"
			>
				<div class="ui-checkbox-list__header_options">
					<div
						v-if="dataCompactField"
						class="ui-checkbox-list__switcher"
					>
						<div class="ui-checkbox-list__switcher-text">
							{{ switcherText }}
						</div>
						<div class="switcher" ref="switcher"></div>
					</div>
					<div
						v-if="dataParams.useSearch"
						:class="searchClassName"
					>
						<div class="ui-checkbox-list__search-wrapper">
							<div class="ui-ctl ui-ctl-textbox ui-ctl-after-icon ui-ctl-w100">
								<input
									:placeholder="placeholderText"
									type="text"
									class="ui-ctl-element"
									v-model="search"
									@keyup.esc.stop="handleSearchEscKeyUp"
									ref="searchInput"
								>
								<button
									v-if="search.length > 0"
									@click="handleClearSearchButtonClick"
									class="ui-ctl-after ui-ctl-icon-clear ui-checkbox-list__search-clear"
								></button>
								<div
									v-else
									class="ui-ctl-after ui-ctl-icon-search"
								></div>
							</div>
						</div>
					</div>
				</div>
				<checkbox-list-sections
					v-if="dataSections && dataParams.useSectioning"
					:sections="dataSections"
					@sectionToggled="handleSectionsToggled"
				/>
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
						v-if="dataParams.useSectioning"
						v-for="category in categoryBySection"
						:key="category.key"
						:context="context"
						:category="category"
						:columnCount="columnCount"
						:options="getOptionsByCategory(category.key)"
						:isActiveSearch="search.length > 0"
						:isEditableOptionsTitle="dataParams.isEditableOptionsTitle"
						:setOptionRef="setOptionRef"
						@onToggleOption="onToggleOption"
					/>
	
					<checkbox-list-category
						v-else
						:context="context"
						:columnCount="columnCount"
						:options="getOptions()"
						:isActiveSearch="search.length > 0"
						:isEditableOptionsTitle="dataParams.isEditableOptionsTitle"
						:setOptionRef="setOptionRef"
						@onToggleOption="onToggleOption"
					/>
				</div>
				<div
					v-else
					class="ui-checkbox-list__empty"
				>
					<img
						src="/bitrix/js/ui/dialogs/checkbox-list/images/ui-checkbox-list-empty.svg"
						:alt="emptyStateTitleText"
					>
					<div class="ui-checkbox-list__empty-title">
						{{ emptyStateTitleText }}
					</div>
					<div class="ui-checkbox-list__empty-description">
						{{ emptyStateDescriptionText }}
					</div>
	
					<div
						class="ui-checkbox-list__options"
						:style="{ 'column-count': columnCount, opacity: 0 }"
					>
						<div>
							<label class="ui-ctl"></label>
						</div>
					</div>
				</div>
			</div>

			<div class="ui-checkbox-list__footer">
				<div class="ui-checkbox-list__footer-block --left">
					<div
						@click="onSelectAllClick()"
						:class="selectAllClassName"
					>
						<input 
							type="checkbox" 
							name="selectAllCheckbox"
							ref="selectAllCheckbox"
							v-model="isAllSelected"
						>
						<label
							v-if="!isNarrowWidth"
							for="selectAllCheckbox"
						>
							{{ selectAllBtnText }}
						</label>
					</div>
	
					<div
						v-if="customFooterElements"
						v-for="customElement in customFooterElements"
					>
						<checkbox-component
							v-if="customElement.type === 'checkbox'"
							:id="customElement.id"
							:title="customElement.title"
							@onToggled="customElement.onClick"
						/>
						<text-toggle-component
							v-if="customElement.type === 'textToggle'"
							:id="customElement.id"
							:title="customElement.title"
							:dataItems="customElement.dataItems"
							@onToggled="customElement.onClick"
						/>
					</div>
				</div>
				<div class="ui-checkbox-list__footer-block --right">
					<div
						v-if="dataParams.showBackToDefaultSettings"
						class="ui-checkbox-list__footer-link --default"
						@click="defaultSettings()"
					>
						{{ defaultSettingsBtnText }}
					</div>
				</div>
				<div class="ui-checkbox-list__footer-block --center">
					<button
						@click="apply()"
						:class="applyClassName"
					>
						{{ applyBtnText }}
					</button>
					<button
						@click="cancel()"
						class="ui-btn ui-btn-link"
					>
						{{ cancelBtnText }}
					</button>
				</div>
			</div>
		</div>
	`,
};

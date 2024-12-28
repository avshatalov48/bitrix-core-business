import { Runtime, Type } from 'main.core';
import type { BaseEvent } from 'main.core.events';
import { Dialog, type Item, type ItemOptions } from 'ui.entity-selector';
import Footer from '../../../../integration/entity-selector/footer';
import type { Variable, VariableCollection } from '../../../../store/model/access-rights-model';
import { getMultipleSelectedVariablesHintHtml, getMultipleSelectedVariablesTitle } from '../../../../utils';
import { SelectedHint } from './../../../util/selected-hint';

export const Multivariables = {
	name: 'Multivariables',
	components: {
		SelectedHint,
	},
	props: {
		// value for selector is id of a selected variable
		value: {
			/** @type AccessRightValue */
			type: Object,
			required: true,
		},
	},
	inject: ['section', 'userGroup', 'right'],
	computed: {
		isAllSelected(): boolean {
			return this.value.values.has(this.right.allSelectedCode);
		},
		selectedVariables(): VariableCollection {
			if (this.isAllSelected)
			{
				return this.right.variables;
			}

			const selected = new Map();

			for (const [variableId, variable] of this.right.variables)
			{
				if (this.value.values.has(variableId))
				{
					selected.set(variableId, variable);
				}
			}

			return selected;
		},
		emptyValues(): Set<string> {
			return this.$store.getters['accessRights/getEmptyValue'](this.section.sectionCode, this.value.id);
		},
		currentAlias(): ?string {
			return this.$store.getters['accessRights/getSelectedVariablesAlias'](this.section.sectionCode, this.value.id, this.value.values);
		},
		title(): string {
			if (Type.isString(this.currentAlias))
			{
				return this.currentAlias;
			}

			if (this.isAllSelected)
			{
				return this.$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_ALL_ACCEPTED');
			}

			if (this.selectedVariables.size <= 0)
			{
				return this.$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_ADD');
			}

			return getMultipleSelectedVariablesTitle(this.selectedVariables);
		},
		hintHtml(): string {
			return getMultipleSelectedVariablesHintHtml(this.selectedVariables, this.hintTitle, this.right.variables);
		},
		hintTitle(): string {
			if (Type.isString(this.right.hintTitle))
			{
				return this.right.hintTitle;
			}

			return this.$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_SELECTED_ITEMS_TITLE');
		},
		dialogItems(): ItemOptions[] {
			const items: ItemOptions[] = [];

			for (const variable: Variable of this.right.variables.values())
			{
				const item = Runtime.clone(variable);
				item.entityId = item.entityId || 'editor-right-item';
				item.tabs = 'recents';
				if (item.selectionStrategy)
				{
					item.customData = { ...item.customData, selectionStrategy: item.selectionStrategy };
				}

				items.push(item);
			}

			return items;
		},
		selectedDialogItems(): ItemOptions[] {
			return this.dialogItems.filter((item) => this.selectedVariables.has(item.id));
		},
	},
	methods: {
		showSelector(): void {
			const dialog = new Dialog({
				targetNode: this.$el,
				height: 300,
				context: 'editor-right-items',
				enableSearch: this.right.enableSearch,
				multiple: true,
				dropdownMode: true,
				compactView: this.right.compactView,
				showAvatars: this.right.showAvatars,
				selectedItems: this.selectedDialogItems,
				searchOptions: {
					allowCreateItem: false,
				},
				cacheable: false,
				events: {
					'Item:onSelect': this.onItemSelect,
					'Item:onDeselect': this.onItemDeselect,
					onHide: this.setEmptyValueIfNoneSelected,
				},
				entities: [
					{
						id: 'editor-right-item',
					},
				],
				items: this.dialogItems,
				footer: this.right.disableSelectAll ? null : Footer,
			});

			dialog.show();
		},
		onItemSelect(event: BaseEvent): void {
			const addedItem: Item = event.getData().item;

			this.processSelectionLogic(addedItem);

			const addedValue = String(addedItem.getId());

			/**
			 * Multivariables has complex logic that takes into account current values. And those values can be changed
			 * multiple times during a single `onItemSelect` call (deselect for items with `selectionStrategy`).
			 * Vue caches props changes. We would receive new `this.value.values` only after `onItemSelect` returned
			 * completely. Therefore, if we do all mutations in a single event loop message, all mutations will operate
			 * with `this.value.values` that have yet to be updated, and our resulting values will be a mess.
			 * Delaying mutation to a next event loop message ensures that we will operate with updated `this.value.values`.
			 */
			setTimeout(() => {
				this.addValue(addedValue);
			});
		},
		processSelectionLogic(addedItem: Item): void {
			const selected: Item[] = addedItem.getDialog().getSelectedItems();

			// clear other selected items
			if (addedItem.customData.get('selectionStrategy') === 'mutually-exclusive')
			{
				for (const item of selected)
				{
					if (addedItem.getId() !== item.getId())
					{
						item.deselect();
					}
				}
			}

			for (const item of selected)
			{
				if (
					item.customData.get('selectionStrategy') === 'mutually-exclusive'
					&& addedItem.getId() !== item.getId()
				)
				{
					item.deselect();
				}
			}
		},
		onItemDeselect(event: BaseEvent): void {
			const removedItem: Item = event.getData().item;

			const removedValue = String(removedItem.getId());

			/**
			 * Multivariables has complex logic that takes into account current values. And those values can be changed
			 * multiple times during a single `onItemSelect` call (deselect for items with `selectionStrategy`).
			 * Vue caches props changes. We would receive new `this.value.values` only after `onItemSelect` returned
			 * completely. Therefore, if we do all mutations in a single event loop message, all mutations will operate
			 * with `this.value.values` that have yet to be updated, and our resulting values will be a mess.
			 * Delaying mutation to a next event loop message ensures that we will operate with updated `this.value.values`.
			 */
			setTimeout(() => {
				this.removeValue(removedValue);
			});
		},
		addValue(value: string): void {
			const newValues = Runtime.clone(this.value.values);

			newValues.add(value);

			if (newValues.length >= this.right.variables.size)
			{
				this.setValues(new Set([this.right.allSelectedCode]));
			}
			else
			{
				this.setValues(newValues);
			}
		},
		removeValue(value: string): void {
			if (this.value.values.has(this.right.allSelectedCode))
			{
				const allVariablesIds = [...this.right.variables.values()].map((variable: Variable) => variable.id);

				const allVariablesIdsWithoutRemoved = new Set(allVariablesIds);
				allVariablesIdsWithoutRemoved.delete(value);

				this.setValues(allVariablesIdsWithoutRemoved);
			}
			else
			{
				const newValues = [...this.value.values].filter((candidate) => candidate !== value);

				this.setValues(new Set(newValues));
			}
		},
		setEmptyValueIfNoneSelected(): void {
			if (this.value.values.size <= 0)
			{
				for (const empty of this.emptyValues)
				{
					this.addValue(empty);
				}
			}
		},
		setValues(newValues: Set<string>): void {
			this.$store.dispatch('userGroups/setAccessRightValues', {
				sectionCode: this.section.sectionCode,
				userGroupId: this.userGroup.id,
				valueId: this.value.id,
				values: newValues,
			});
		},
	},
	template: `
		<SelectedHint 
			v-if="hintHtml"
			:html="hintHtml" 
			class='ui-access-rights-v2-column-item-text-link'
			@click="showSelector"
		>
			{{ title }}
		</SelectedHint>
		<div 
			v-else
			class='ui-access-rights-v2-column-item-text-link ui-access-rights-v2-text-ellipsis'
			@click="showSelector"
			:title="title"
		>
			{{ title }}
		</div>
	`,
};

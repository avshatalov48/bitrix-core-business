import { Runtime } from 'main.core';
import type { BaseEvent } from 'main.core.events';
import { Dialog, type Item, type ItemOptions } from 'ui.entity-selector';
import { EntitySelectorContext, EntitySelectorEntities } from '../../../../integration/entity-selector/dictionary';
import { Footer } from '../../../../integration/entity-selector/footer';
import { Header } from '../../../../integration/entity-selector/header';
import { ItemsMapper } from '../../../../integration/entity-selector/items-mapper';
import type { Variable, VariableCollection } from '../../../../store/model/access-rights-model';
import { getSelectedVariables } from '../../../../utils';

export const Selector = {
	name: 'Selector',
	emits: ['apply', 'close'],
	props: {
		// value for selector is id of a selected variable
		initialValues: {
			type: Set,
			default: new Set(),
		},
	},
	inject: ['section', 'right'],
	data(): Object {
		return {
			// values modified during popup lifetime and not yet dispatched to store
			values: this.initialValues,
		};
	},
	dialog: null,
	computed: {
		isAllSelected(): boolean {
			return this.values.has(this.right.allSelectedCode);
		},
		selectedVariables(): VariableCollection {
			return getSelectedVariables(this.right.variables, this.values, this.isAllSelected);
		},
		dialogItems(): ItemOptions[] {
			return ItemsMapper.mapVariables(this.right.variables);
		},
		selectedDialogItems(): ItemOptions[] {
			return this.dialogItems.filter((item) => this.selectedVariables.has(item.id));
		},
	},
	mounted()
	{
		this.showSelector();
	},
	beforeUnmount()
	{
		this.dialog?.hide();
	},
	methods: {
		showSelector(): void {
			this.dialog = new Dialog({
				height: 400,
				context: EntitySelectorContext.VARIABLE,
				enableSearch: this.right.enableSearch,
				multiple: true,
				autoHide: true,
				hideByEsc: true,
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
					onHide: this.apply,
					onDestroy: () => {
						this.dialog = null;
					},
				},
				entities: [
					{
						id: EntitySelectorEntities.VARIABLE,
					},
				],
				items: this.dialogItems,
				header: Header,
				headerOptions: {
					section: this.section,
					right: this.right,
				},
				footer: Footer,
			});

			this.dialog.show();
		},
		onItemSelect(event: BaseEvent): void {
			const addedItem: Item = event.getData().item;

			this.addValue(String(addedItem.getId()));
		},
		onItemDeselect(event: BaseEvent): void {
			const removedItem: Item = event.getData().item;

			this.removeValue(String(removedItem.getId()));
		},
		addValue(value: string): void {
			const newValues = Runtime.clone(this.values);

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
			if (this.values.has(this.right.allSelectedCode))
			{
				const allVariablesIds = [...this.right.variables.values()].map((variable: Variable) => variable.id);

				const allVariablesIdsWithoutRemoved = new Set(allVariablesIds);
				allVariablesIdsWithoutRemoved.delete(value);

				this.setValues(allVariablesIdsWithoutRemoved);
			}
			else
			{
				const newValues = [...this.values].filter((candidate) => candidate !== value);

				this.setValues(new Set(newValues));
			}
		},
		setValues(newValues: Set<string>): void {
			this.values = newValues;
		},
		apply(): void {
			this.setNothingSelectedValueIfNeeded();

			this.$emit('apply', {
				values: this.values,
			});

			this.$emit('close');
		},
		setNothingSelectedValueIfNeeded(): void {
			if (this.values.size <= 0)
			{
				const nothingSelected = this.$store.getters['accessRights/getNothingSelectedValue'](
					this.section.sectionCode,
					this.right.id,
				);

				for (const nothing of nothingSelected)
				{
					this.addValue(nothing);
				}
			}
		},
	},
	template: `
		<div></div>
	`,
};

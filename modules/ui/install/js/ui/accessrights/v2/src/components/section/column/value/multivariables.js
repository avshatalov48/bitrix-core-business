import { Type } from 'main.core';
import type { VariableCollection } from '../../../../store/model/access-rights-model';
import {
	getMultipleSelectedVariablesHintHtml,
	getMultipleSelectedVariablesTitle,
	getSelectedVariables,
} from '../../../../utils';
import { Selector } from '../../value/multivariables/selector';
import { SelectedHint } from './../../../util/selected-hint';

export const Multivariables = {
	name: 'Multivariables',
	components: {
		SelectedHint,
		Selector,
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
	data(): Object {
		return {
			isSelectorShown: false,
		};
	},
	computed: {
		isAllSelected(): boolean {
			return this.value.values.has(this.right.allSelectedCode);
		},
		selectedVariables(): VariableCollection {
			return getSelectedVariables(this.right.variables, this.value.values, this.isAllSelected);
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
	},
	methods: {
		showSelector(): void {
			this.isSelectorShown = true;
		},
		setValues({ values }): void {
			this.$store.dispatch('userGroups/setAccessRightValues', {
				sectionCode: this.section.sectionCode,
				userGroupId: this.userGroup.id,
				valueId: this.value.id,
				values,
			});
		},
	},
	template: `
		<SelectedHint 
			v-if="hintHtml"
			:html="hintHtml" 
			class='ui-access-rights-v2-column-item-text-link'
			@click="showSelector"
			v-bind="$attrs"
		>
			{{ title }}
		</SelectedHint>
		<div 
			v-else
			class='ui-access-rights-v2-column-item-text-link ui-access-rights-v2-text-ellipsis'
			@click="showSelector"
			:title="title"
			v-bind="$attrs"
		>
			{{ title }}
		</div>
		<Selector
			v-if="isSelectorShown" 
			:initial-values="value.values"
			@close="isSelectorShown = false"
			@apply="setValues"
		/>
	`,
};

import { Type } from 'main.core';
import type { VariableCollection } from '../../../../store/model/access-rights-model';
import {
	getMultipleSelectedVariablesHintHtml,
	getMultipleSelectedVariablesTitle,
	getSelectedVariables,
} from '../../../../utils';
import { PopupContent } from '../../value/dependent-variables/popup-content';
import { SingleRoleTitle } from '../../value/popup-header/master-switcher/single-role-title';
import { ValuePopup } from '../../value/value-popup';
import { SelectedHint } from './../../../util/selected-hint';

export const DependentVariables = {
	name: 'DependentVariables',
	components: { ValuePopup, PopupContent, SelectedHint, SingleRoleTitle },
	props: {
		// value for selector is id of a selected variable
		value: {
			/** @type AccessRightValue */
			type: Object,
			required: true,
		},
	},
	data(): Object {
		return {
			isPopupShown: false,
		};
	},
	inject: ['section', 'userGroup', 'right'],
	computed: {
		selectedVariables(): VariableCollection {
			return getSelectedVariables(this.right.variables, this.value.values, false);
		},
		currentAlias(): ?string {
			return this.$store.getters['accessRights/getSelectedVariablesAlias'](this.section.sectionCode, this.value.id, this.value.values);
		},
		title(): string {
			if (Type.isString(this.currentAlias))
			{
				return this.currentAlias;
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
		apply({ values }): void
		{
			this.$store.dispatch('userGroups/setAccessRightValues', {
				sectionCode: this.section.sectionCode,
				userGroupId: this.userGroup.id,
				valueId: this.value.id,
				values,
			});
		},
	},
	template: `
		<div class='ui-access-rights-v2-column-item-text-link' :class="{
			'ui-access-rights-v2-text-ellipsis': !hintHtml
		}" @click="isPopupShown = true">
			<SelectedHint v-if="hintHtml" :html="hintHtml">{{title}}</SelectedHint>
			<div v-else :title="title">{{title}}</div>
			<ValuePopup v-if="isPopupShown" @close="isPopupShown = false">
				<PopupContent
					@apply="apply"
					:initial-values="value.values"
				>
					<template #role-title>
						<SingleRoleTitle :user-group-title="userGroup.title"/>
					</template>
				</PopupContent>
			</ValuePopup>
		</div>
	`,
};

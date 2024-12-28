import { Type } from 'main.core';
import type { PopupOptions } from 'main.popup';
import { Popup } from 'ui.vue3.components.popup';
import type { VariableCollection } from '../../../../store/model/access-rights-model';
import { getMultipleSelectedVariablesHintHtml, getMultipleSelectedVariablesTitle } from '../../../../utils';
import { SelectedHint } from './../../../util/selected-hint';
import { PopupContent } from './dependent-variables/popup-content';

export const DependentVariables = {
	name: 'DependentVariables',
	components: { Popup, PopupContent, SelectedHint },
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
	inject: ['section', 'right'],
	computed: {
		selectedVariables(): VariableCollection {
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
		popupOptions(): PopupOptions {
			return {
				autoHide: true,
				closeEsc: true,
				cacheable: false,
				minWidth: 466,
				padding: 18,
			};
		},
	},
	template: `
		<div class='ui-access-rights-v2-column-item-text-link' :class="{
			'ui-access-rights-v2-text-ellipsis': !hintHtml
		}" @click="isPopupShown = true">
			<SelectedHint v-if="hintHtml" :html="hintHtml">{{title}}</SelectedHint>
			<div v-else :title="title">{{title}}</div>
			<Popup v-if="isPopupShown" @close="isPopupShown = false" :options="popupOptions">
				<PopupContent 
					@close="isPopupShown = false"
					:value="value"
				/>
			</Popup>
		</div>
	`,
};

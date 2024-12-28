import { Type } from 'main.core';
import { Switcher } from 'ui.vue3.components.switcher';
import { Icon as SectionIcon } from '../../../icon';
import '../../../../../css/cell/dependent-variables/popup-header.css';

export const PopupHeader = {
	name: 'PopupHeader',
	components: { Switcher, SectionIcon },
	emits: ['setMax', 'setMin'],
	props: {
		// later in a row menu here should be passed text 'All roles'
		userGroupTitle: {
			type: String,
			required: true,
		},
		values: {
			/** @type Set<string> */
			type: Set,
			required: true,
		},
	},
	inject: ['section', 'right'],
	computed: {
		rightOrGroupTitle(): string {
			if (!this.right.group)
			{
				return this.right.title;
			}

			const groupHead = this.section.rights.get(this.right.group);

			return groupHead?.title;
		},
		isMinMaxValuesSet(): boolean {
			return !Type.isNil(this.right.minValue) && !Type.isNil(this.right.maxValue);
		},
		isSelectedAnythingBesidesMin(): boolean {
			if (this.values.size <= 0)
			{
				return false;
			}

			for (const variableId of this.values)
			{
				if (!this.right.minValue.has(variableId))
				{
					return true;
				}
			}

			return false;
		},
		switcherOptions(): Object {
			return {
				size: 'small',
				color: 'green',
			};
		},
	},
	template: `
		<div class="ui-access-rights-v2-cell-popup-header">
			<div class="ui-access-rights-v2-cell-popup-header-locator">
				<SectionIcon/>
				<span
					class="ui-access-rights-v2-text-ellipsis"
					:title="section.sectionTitle"
				>{{ section.sectionTitle }}</span>
				<span
					v-if="section.sectionSubTitle" 
					class="ui-access-rights-v2-text-ellipsis"
					:title="section.sectionSubTitle"
					style="margin-left: 5px; color: var(--ui-color-palette-gray-70);"
				>{{ section.sectionSubTitle }}</span>
				<div class="ui-icon-set --chevron-right ui-access-rights-v2-cell-popup-header-chevron"></div>
				<template v-if="rightOrGroupTitle !== right.title">
					<span class="ui-access-rights-v2-text-ellipsis" :title="right.title">{{ right.title }}</span>
					<div class="ui-icon-set --chevron-right ui-access-rights-v2-cell-popup-header-chevron"></div>
				</template>
				<span class="ui-access-rights-v2-text-ellipsis" :title="rightOrGroupTitle">{{ rightOrGroupTitle }}</span>
			</div>
			<div class="ui-access-rights-v2-cell-popup-header-role-line">
				<div class="ui-access-rights-v2-cell-popup-header-role-container">
					<span class="ui-icon ui-icon-square ui-icon-xs ui-access-rights-v2-cell-popup-header-role-icon">
						<i></i>
					</span>
					<div>
						<div class="ui-access-rights-v2-cell-popup-header-role-caption">
							{{ $Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_ROLE') }}
						</div>
						<div
							class="ui-access-rights-v2-cell-popup-header-role-title ui-access-rights-v2-text-ellipsis"
							:title="userGroupTitle"
						>
							{{ userGroupTitle }}
						</div>
					</div>
				</div>
				<div v-if="isMinMaxValuesSet" class="ui-access-rights-v2-cell-popup-header-toggle-container">
					<span class="ui-access-rights-v2-cell-popup-header-toggle-caption">{{
						$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_ACCESS')
					}}</span>
					<Switcher
						:is-checked="isSelectedAnythingBesidesMin"
						@check="$emit('setMax')"
						@uncheck="$emit('setMin')"
						:options="switcherOptions"
						data-accessrights-min-max
					/>
				</div>
			</div>
		</div>
	`,
};

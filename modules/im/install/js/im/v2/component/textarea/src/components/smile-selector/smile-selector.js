import { BIcon, Outline as OutlineIcons } from 'ui.icon-set.api.vue';

import { Color } from 'im.v2.const';

import { SmilePopup } from './components/smile-popup';

import type { JsonObject } from 'main.core';

const ICON_SIZE = 24;

// @vue/component
export const SmileSelector = {
	name: 'SmileSelector',
	components: { BIcon, SmilePopup },
	props: {
		dialogId: {
			type: String,
			required: true,
		},
	},
	data(): JsonObject
	{
		return {
			showPopup: false,
		};
	},
	computed:
	{
		OutlineIcons: () => OutlineIcons,
		ICON_SIZE: () => ICON_SIZE,
		iconColor(): string
		{
			if (this.showPopup)
			{
				return Color.accentBlue;
			}

			return Color.gray40;
		},
	},
	methods:
	{
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div ref="addSmile" class="bx-im-textarea__icon-container">
			<BIcon
				:name="OutlineIcons.SMILE"
				:title="loc('IM_TEXTAREA_ICON_SMILE')"
				:size="ICON_SIZE"
				:color="iconColor"
				class="bx-im-textarea__icon"
				@click="showPopup = true"
			/>
		</div>
		<SmilePopup
			v-if="showPopup"
			:bindElement="$refs['addSmile']"
			:dialogId="dialogId"
			@close="showPopup = false"
		/>
	`,
};

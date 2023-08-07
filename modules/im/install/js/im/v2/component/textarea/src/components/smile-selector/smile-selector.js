import {SmilePopup} from './components/smile-popup';

// @vue/component
export const SmileSelector = {
	name: 'SmileSelector',
	components: {SmilePopup},
	props: {
		dialogId: {
			type: String,
			required: true
		}
	},
	data()
	{
		return {
			showPopup: false
		};
	},
	methods:
	{
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		}
	},
	template: `
		<div
			@click="showPopup = true"
			:title="loc('IM_TEXTAREA_ICON_SMILE')"
			class="bx-im-textarea__icon --smile"
			:class="{'--active': showPopup}"
			ref="addSmile"
		>
		</div>
		<SmilePopup
			v-if="showPopup"
			:bindElement="$refs['addSmile']"
			:dialogId="dialogId"
			@close="showPopup = false"
		/>
	`
};
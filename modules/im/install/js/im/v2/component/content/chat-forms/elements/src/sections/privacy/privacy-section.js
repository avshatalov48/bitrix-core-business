import 'ui.forms';

import { CreateChatSection } from '../section/section';
import { RadioOption, type RadioOptionItem } from '../../elements/radio';

// @vue/component
export const PrivacySection = {
	components: { CreateChatSection, RadioOption },
	props: {
		withSearchOption: {
			type: Boolean,
			default: true,
		},
		isAvailableInSearch: {
			type: Boolean,
			default: false,
		},
	},
	emits: ['chatTypeChange'],
	computed:
	{
		privacyOptions(): RadioOptionItem[]
		{
			return [
				{
					value: false,
					text: this.loc('IM_CREATE_CHAT_PRIVACY_SECTION_PRIVATE_TITLE'),
					subtext: this.loc('IM_CREATE_CHAT_PRIVACY_SECTION_PRIVATE_SUBTITLE_V2'),
					selected: !this.isAvailableInSearch,
				},
				{
					value: true,
					text: this.loc('IM_CREATE_CHAT_PRIVACY_SECTION_OPEN_TITLE'),
					subtext: this.loc('IM_CREATE_CHAT_PRIVACY_SECTION_OPEN_SUBTITLE_V2'),
					selected: this.isAvailableInSearch,
				},
			];
		},
	},
	methods:
	{
		onTypeChange(isAvailableInSearch: boolean)
		{
			this.$emit('chatTypeChange', isAvailableInSearch);
		},
		loc(phraseCode: string, replacements: {[p: string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
	},
	template: `
		<CreateChatSection
			name="privacy"
			:title="loc('IM_CREATE_CHAT_PRIVACY_SECTION_V2')"
			:alwaysOpened="true"
		>
			<div class="bx-im-content-create-chat__section_block">
				<RadioOption :items="privacyOptions" @change="onTypeChange" />
			</div>
		</CreateChatSection>
	`,
};

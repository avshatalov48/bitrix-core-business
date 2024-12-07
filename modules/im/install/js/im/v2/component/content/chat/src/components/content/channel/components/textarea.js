import { ChatTextarea } from 'im.v2.component.textarea';

// @vue/component
export const ChannelTextarea = {
	name: 'ChannelTextarea',
	components: { ChatTextarea },
	props:
	{
		dialogId: {
			type: String,
			default: '',
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
		<ChatTextarea
			:dialogId="dialogId"
			:placeholder="this.loc('IM_CONTENT_CHANNEL_TEXTAREA_PLACEHOLDER')"
			:withCreateMenu="false"
			:withMarket="false"
			:withAudioInput="false"
			class="bx-im-channel-send-panel__container"
		/>
	`,
};

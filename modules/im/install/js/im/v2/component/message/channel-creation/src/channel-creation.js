import { BaseMessage } from 'im.v2.component.message.base';

import './css/channel-creation.css';

// @vue/component
export const ChannelCreationMessage = {
	name: 'ChannelCreationMessage',
	components: { BaseMessage },
	props:
	{
		item: {
			type: Object,
			required: true,
		},
		dialogId: {
			type: String,
			required: true,
		},
	},
	computed:
	{
		description(): string
		{
			return this.loc('IM_MESSAGE_CHANNEL_CREATION_DESCRIPTION', {
				'#BR#': '\n',
			});
		},
	},
	methods:
	{
		loc(phraseCode: string, replacements: {[p: string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
	},
	template: `
		<BaseMessage
			:dialogId="dialogId"
			:item="item"
			:withContextMenu="false"
			:withReactions="false"
			:withBackground="false"
		>
			<div class="bx-im-message-channel-creation__container">
				<div class="bx-im-message-channel-creation__image"></div>
				<div class="bx-im-message-channel-creation__content">
					<div class="bx-im-message-channel-creation__title">
						{{ loc('IM_MESSAGE_CHANNEL_CREATION_TITLE') }}
					</div>
					<div class="bx-im-message-channel-creation__description">
						{{ description }}
					</div>
				</div>
			</div>
		</BaseMessage>
	`,
};

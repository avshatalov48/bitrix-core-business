import { ChatTitle, EditableChatTitle } from 'im.v2.component.elements';

// @vue/component
export const MultidialogChatTitle = {
	name: 'MultidialogChatTitle',
	components: { EditableChatTitle, ChatTitle },
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
	},
	emits: ['newTitle'],
	computed:
	{
		isSupportBot(): boolean
		{
			return this.$store.getters['users/bots/isSupport'](this.dialogId);
		},
		subtitle(): string
		{
			return this.$Bitrix.Loc.getMessage('IM_CONTENT_CHAT_HEADER_SUPPORT_SUBTITLE');
		},
	},
	template: `
		<div class="bx-im-chat-header__info">
			<ChatTitle v-if="isSupportBot" :dialogId="dialogId" />
			<EditableChatTitle v-else :dialogId="dialogId" @newTitleSubmit="$emit('newTitle', $event)" />
			<div class="bx-im-chat-header__subtitle_container">
				<div class="bx-im-chat-header__subtitle_content">{{ subtitle }}</div>
			</div>
		</div>
	`,
};

import '../../css/create-chat-help.css';

// @vue/component
export const CreateChatHelp = {
	emits: ['articleOpen'],
	data()
	{
		return {};
	},
	methods:
	{
		openHelpArticle()
		{
			const ARTICLE_CODE = 17412872;
			BX.Helper?.show(`redirect=detail&code=${ARTICLE_CODE}`);
			this.$emit('articleOpen');
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		}
	},
	template: `
		<div class="bx-im-create-chat-help__container">
			<div @click="openHelpArticle" class="bx-im-create-chat-help__content">
				<div class="bx-im-create-chat-help__icon"></div>
				<div class="bx-im-create-chat-help__text">{{ loc('IM_RECENT_CREATE_CHAT_WHAT_TO_CHOOSE') }}</div>	
			</div>
		</div>
	`
};
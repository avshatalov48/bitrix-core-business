import { Loc, Runtime } from 'main.core';

import { MessageMenu } from 'im.v2.component.message-list';
import { CopilotManager } from 'im.v2.lib.copilot';

import type { ImModelChat } from 'im.v2.model';
import type { MenuItem } from 'im.v2.lib.menu';

const CopilotChatContext = Object.freeze({
	personal: 'chat_copilot_tab_one_by_one',
	group: 'chat_copilot_tab_multi',
});

export class CopilotMessageMenu extends MessageMenu
{
	getMenuItems(): MenuItem[]
	{
		return [
			this.getCopyItem(),
			this.getFavoriteItem(),
			this.getForwardItem(),
			this.getSendFeedbackItem(),
			this.getDeleteItem(),
		];
	}

	getSendFeedbackItem(): MenuItem
	{
		const copilotManager = new CopilotManager();
		if (!copilotManager.isCopilotBot(this.context.authorId))
		{
			return null;
		}

		return {
			text: Loc.getMessage('IM_CONTENT_COPILOT_CONTEXT_MENU_FEEDBACK'),
			onclick: () => {
				void this.openForm();
				this.menuInstance.close();
			},
		};
	}

	async openForm()
	{
		const formId = Math.round(Math.random() * 1000);

		await Runtime.loadExtension(['ui.feedback.form']);
		BX.UI.Feedback.Form.open({
			id: `im.copilot.feedback-${formId}`,
			forms: [
				{ zones: ['es'], id: 684, lang: 'es', sec: 'svvq1x' },
				{ zones: ['en'], id: 686, lang: 'en', sec: 'tjwodz' },
				{ zones: ['de'], id: 688, lang: 'de', sec: 'nrwksg' },
				{ zones: ['com.br'], id: 690, lang: 'com.br', sec: 'kpte6m' },
				{ zones: ['ru', 'by', 'kz'], id: 692, lang: 'ru', sec: 'jbujn0' },
			],
			presets: {
				sender_page: this.getCopilotChatContext(),
				language: Loc.getMessage('LANGUAGE_ID'),
				cp_answer: this.context.text,
			},
		});
	}

	getCopilotChatContext(): $Values<typeof CopilotChatContext>
	{
		const chat: ImModelChat = this.store.getters['chats/get'](this.context.dialogId);
		if (chat.userCounter <= 2)
		{
			return CopilotChatContext.personal;
		}

		return CopilotChatContext.group;
	}
}

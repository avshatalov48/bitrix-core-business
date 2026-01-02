import { Dom, Loc } from 'main.core';
import { CopilotChat as CopilotChatInstance, CopilotChatEvents as cce } from 'ai.copilot-chat.core';
import { CopilotChatMessageType as ccmt } from 'ai.copilot-chat.ui';

import './chat.css';

export type GetCopilotChatInstanceOptions = {
	entityId: string;
	chatId?: number;
	showChatButtonElement?: HTMLElement;
	isSiteEditChat?: boolean;
	scenario?: string;
};

export const CopilotChatEvents = cce;
export const CopilotChatMessageType = ccmt;

export class Chat
{
	static CopilotChatEvents = cce;
	static CopilotChatMessageType = ccmt;

	static getCopilotChatInstance(options: GetCopilotChatInstanceOptions): ? CopilotChatInstance
	{
		const entityId = options.entityId;
		const chatId = options.chatId ?? null;
		const scenario = options.scenario ?? 'site_with_ai';

		return new BX.AI.CopilotChat.Core.CopilotChat({
			entityId,
			chatId,
			scenarioCode: scenario,
			entityType: 'landing',
			initChatExtraOptions: {
				isSiteEditChat: options.isSiteEditChat ?? false,
			},
			chatOptions: {
				popupOptions: {
					cacheable: true,
					width: this.getPopupWidth(),
					height: this.getPopupHeight(options.showChatButtonElement),
					bindElement: this.getPopupPosition(Boolean(options.showChatButtonElement)),
					className: 'landing__create-site-copilot-chat-popup',
					animation: {
						showClassName: 'create-site-chat-show',
						closeClassName: 'create-site-chat-hide',
						closeAnimationType: 'animation',
					},
					events: {
						onPopupFirstShow: (popup: Popup) => {
							popup.subscribe('onBeforeAdjustPosition', () => {
								popup.setHeight(this.getPopupHeight(options.showChatButtonElement));
								popup.setBindElement(this.getPopupPosition(Boolean(options.showChatButtonElement)));
							});
						},
					},
				},
				header: {
					title: Loc.getMessage('LANDING_COPILOT_CHAT_TITLE'),
					subtitle: Loc.getMessage('LANDING_COPILOT_CHAT_SUBTITLE'),
					avatar: '/bitrix/js/landing/copilot/chat/images/avatar.png?v2',
				},
				botOptions: {
					messageTitle: Loc.getMessage('LANDING_COPILOT_CHAT_BOT_TITLE'),
					avatar: '/bitrix/js/landing/copilot/chat/images/avatar.png?v2',
				},
				slots: {
					LOADER: null,
					LOADER_ERROR: null,
				},
				vueComponents: {},
				showCopilotWarningMessage: false,
				inputPlaceholder: Loc.getMessage('LANDING_COPILOT_CHAT_INPUT_PLACEHOLDER'),
			},
		});
	}

	static getPopupPosition(usedWithShowPopupButton: boolean): { top: number, left: number }
	{
		return {
			top: this.getTopPanelHeight() + this.getPopupVerticalOffset(),
			left: document.body.clientWidth - this.getPopupWidth() - this.getPopupHorizontalOffset(usedWithShowPopupButton),
		};
	}

	static getPopupWidth(): number
	{
		return 375;
	}

	static getPopupHeight(showChatButton: HTMLElement): number
	{
		const showChatButtonPos = Dom.getPosition(showChatButton);

		return (showChatButtonPos.top || window.innerHeight) - this.getTopPanelHeight() - this.getPopupVerticalOffset() * 2;
	}

	static getPopupVerticalOffset(): number
	{
		return 15;
	}

	static getPopupHorizontalOffset(usedWithShowPopupButton: boolean): number
	{
		return usedWithShowPopupButton ? 32 : 12;
	}

	static getTopPanelHeight(): number
	{
		return 66;
	}
}

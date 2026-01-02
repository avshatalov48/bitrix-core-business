import { Dom, Tag, Event, Type, Runtime } from 'main.core';
import { BaseEvent } from 'main.core.events';
import { type CopilotChat as CopilotChatInstance } from 'ai.copilot-chat.core';
import {
	type Chat as LandingCopilotChat,
	type CopilotChatEvents,
	type CopilotChatMessageType,
} from 'landing.copilot.chat';
import { Metrika } from 'landing.metrika';

import 'ui.design-tokens';
import { BlockGenerator } from './block-generator';

type CopilotChatOptions = {
	chatId?: number;
	entityId?: string;
	isSiteEditChat?: boolean;
	onChatCreate?: (data: {}) => void;
	isCopilotFeatureAvailable?: boolean;
	isCopilotFeatureEnabled?: boolean;
	isCopilotActive?: boolean;
	copilotFeatureEnabledSlider: string;
	copilotUnactiveSlider: string;
}

export type SlidePanelOptions = {
	copilotChatOptions: CopilotChatOptions;
	siteGenerationId: ?number;
};

export class SlidePanel
{
	#copilotChat: CopilotChatInstance;
	#copilotChatOptions: CopilotChatOptions;
	#blockGenerator: BlockGenerator;
	#siteGenerationId: ?number;

	#metrika: Metrika;

	copilotChatEvents: CopilotChatEvents;
	copilotChatMessageType: CopilotChatMessageType;

	constructor(options: SlidePanelOptions)
	{
		this.layout = {
			container: null,
			pulse: null,
			close: null,
			switcherPreview: null,
			switcherChat: null,
			chat: null,
			preview: null,
		};
		this.switchers = [];

		this.#copilotChatOptions = options.copilotChatOptions;
		this.#siteGenerationId = options.siteGenerationId;
		this.#metrika = new Metrika(true, 'ai');

		this.init();
	}

	getSwitcherChat(): HTMLElement
	{
		if (!this.layout.switcherChat)
		{
			this.layout.switcherChat = Tag.render`
				<div class="landing-slide-panel__navigation-item --chat">
					<div class="ui-icon-set --copilot-ai"></div>
				</div>
			`;

			this.switchers.push(this.layout.switcherChat);
			Event.bind(this.layout.switcherChat, 'click', () => {
				this.showChat();
				this.hidePreview();
				this.adjustSwitcher(this.layout.switcherChat);
			});
		}

		return this.layout.switcherChat;
	}

	getSwitcherPreview(): HTMLElement
	{
		if (!this.layout.switcherPreview)
		{
			this.layout.switcherPreview = Tag.render`
				<div class="landing-slide-panel__navigation-item --preview">
					<div class="ui-icon-set --mobile-2"></div>
				</div>
			`;

			this.switchers.push(this.layout.switcherPreview);
			Event.bind(this.layout.switcherPreview, 'click', () => {
				this.showPreview();
				this.hideChat();
				this.adjustSwitcher(this.layout.switcherPreview);
			});
		}

		return this.layout.switcherPreview;
	}

	async showChat()
	{
		let slider = null;

		if (!this.#copilotChatOptions.isCopilotFeatureEnabled)
		{
			slider = this.#copilotChatOptions.copilotFeatureEnabledSlider;
		}
		else if (!this.#copilotChatOptions.isCopilotActive)
		{
			slider = this.#copilotChatOptions.copilotUnactiveSlider;
		}

		if (slider)
		{
			BX.UI.InfoHelper.show(slider);

			return;
		}

		try
		{
			if (!this.#copilotChat)
			{
				this.#copilotChat = await this.#initCopilotChat();
			}

			if (
				!this.#blockGenerator
				&& this.#copilotChat
			)
			{
				this.#blockGenerator = await BlockGenerator.create({
					chatId: this.#copilotChatOptions.chatId || null,
					copilotChat: this.#copilotChat,
				});

				if (this.#blockGenerator)
				{
					this.#blockGenerator.subscribe('onGenerationStart', () => {
						this.hide();
					});
					this.#blockGenerator.subscribe('onGenerationFinish', () => {
						this.show();
						this.showChat();
						this.adjustSwitcher(this.layout.switcherChat);
					});
				}
			}

			if (this.#copilotChat.isShown() === false)
			{
				this.#copilotChat.show();

				const siteType = this.#siteGenerationId ? 'ai' : 'manual';
				this.#metrika.sendData({
					category: 'block_edition',
					event: 'open',
					c_section: 'site_editor',
					p3: ['siteType', siteType],
				});
			}

			this.#blockGenerator?.setSelectMode();
			Dom.addClass(this.getChatContainer(), '--show');
		}
		catch (error)
		{
			console.error(error);
		}
	}

	async #initCopilotChat(): Promise<CopilotChatInstance>
	{
		const exports = await Runtime.loadExtension('landing.copilot.chat');
		const Chat: LandingCopilotChat = exports.Chat;

		this.copilotChatEvents = exports.CopilotChatEvents;
		this.copilotChatMessageType = exports.CopilotChatMessageType;

		const chat = await Chat.getCopilotChatInstance({
			entityId: this.#copilotChatOptions?.entityId,
			chatId: this.#copilotChatOptions?.chatId,
			scenario: 'site_with_ai_change_block',
			showChatButtonElement: this.layout.switcherChat ?? null,
			isSiteEditChat: this.#copilotChatOptions?.isSiteEditChat === true,
		});

		if (
			!this.#copilotChatOptions?.chatId
			&& this.#copilotChatOptions?.onChatCreate
		)
		{
			chat.subscribe(this.copilotChatEvents.INIT_CHAT, (event: BaseEvent) => {
				this.#copilotChatOptions.onChatCreate(event.getData());
				this.#copilotChatOptions.chatId = event.getData().chatId;
			});
		}

		return chat;
	}

	hideChat()
	{
		this.#copilotChat?.hide();
		this.#blockGenerator?.unsetSelectMode();
		Dom.removeClass(this.getChatContainer(), '--show');
	}

	showPreview()
	{
		Dom.addClass(this.getPreviewContainer(), '--show');
	}

	hidePreview()
	{
		Dom.removeClass(this.getPreviewContainer(), '--show');
	}

	getClose(): HTMLElement
	{
		if (!this.layout.close)
		{
			this.layout.close = Tag.render`
				<div class="landing-slide-panel__navigation-item --close">
					<div class="ui-icon-set --cross-45"></div>
				</div>
			`;
			Event.bind(this.layout.close, 'click', () => this.hide());
		}

		return this.layout.close;
	}

	getPulse(): HTMLElement
	{
		if (!this.layout.pulse)
		{
			if (this.#copilotChatOptions.isCopilotFeatureAvailable)
			{
				this.layout.pulse = Tag.render`
					<div class="landing-slide-panel__navigation-item --pulse --animate">
						<div class="ui-icon-set --copilot-ai"></div>
						<div class="ui-icon-set --mobile-2"></div>
					</div>
				`;
			}
			else
			{
				this.layout.pulse = Tag.render`
					<div class="landing-slide-panel__navigation-item --pulse">
						<div class="ui-icon-set --mobile-2"></div>
					</div>
				`;
			}
			Event.bind(this.layout.pulse, 'click', () => this.show());
		}

		return this.layout.pulse;
	}

	getChatContainer(): HTMLElement
	{
		if (!this.layout.chat)
		{
			this.layout.chat = Tag.render`
				<div class="landing-slide-panel__container-item --chat"></div>
			`;
		}

		return this.layout.chat;
	}

	getPreviewContainer(): HTMLElement
	{
		if (!this.layout.preview)
		{
			this.layout.preview = Tag.render`
				<div class="landing-slide-panel__container-item --preview"></div>
			`;
		}

		return this.layout.preview;
	}

	getContainer(): HTMLElement
	{
		if (!this.layout.container)
		{
			this.layout.container = Tag.render`
				<div class="landing-slide-panel">
					<div class="landing-slide-panel-inner">					
						<div class="landing-slide-panel__container">
							${this.getChatContainer()}
							${this.getPreviewContainer()}
						</div>
						<div class="landing-slide-panel__navigation">
						 	${this.#copilotChatOptions.isCopilotFeatureAvailable ? this.getSwitcherChat() : ''}
							${this.getSwitcherPreview()}
							${this.getClose()}
						</div>
					</div>
					${this.getPulse()}
				</div>
			`;
		}

		return this.layout.container;
	}

	adjustSwitcher(switcher: HTMLElement): void
	{
		this.switchers.forEach((item) => Dom.removeClass(item, '--active'));
		if (Type.isDomNode(switcher))
		{
			Dom.addClass(switcher, '--active');
		}
	}

	show()
	{
		Dom.addClass(this.getContainer(), '--open');
	}

	hide()
	{
		Dom.removeClass(this.getContainer(), '--open');
		this.hideChat();
		this.hidePreview();
		this.adjustSwitcher();
	}

	init(): void
	{
		Dom.append(this.getContainer(), document.body);
	}
}

import { Event, ajax as Ajax, Runtime, Dom } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';
import { PULL, PullClient } from 'pull.client';
import { type CopilotChat as CopilotChatInstance } from 'ai.copilot-chat.core';
import {
	type CopilotChatEvents,
	type CopilotChatMessageType,
} from 'landing.copilot.chat';
import { PageObject } from 'landing.pageobject';
import { Highlight } from 'landing.ui.highlight';
import { Copilot as CopilotAnimation } from 'landing.animation.copilot';
import { Env } from 'landing.env';
import { History } from 'landing.history';
import { StylePanel } from 'landing.ui.panel.stylepanel';

type BlockGeneratorOptions = {
	chatId: ?number,
	copilotChat: ?CopilotChatInstance,
}

type SelectedBlock = {
	id: number,
	anchor: string,
	content: HTMLElement,
};

export class BlockGenerator extends EventEmitter
{
	static selectModeClass = 'landing-copilot-generation';
	static highlightColor = '#8e52ec';
	static highlightWidth = 4;
	static highlightBackground = 'rgba(142, 82, 236, 0.4)';

	#chatId: number;
	#copilotChat: CopilotChatInstance;
	#animationCopilot: ?CopilotAnimation = null;
	#copilotChatEvents: CopilotChatEvents;
	#copilotChatMessageType: CopilotChatMessageType;

	#generationId: ?number = null;
	#isGenerationProcessed: boolean = false;

	#blocks: Map<number, SelectedBlock>;
	#selectMode: boolean = false;
	#selectedBlock: ?SelectedBlock;
	#isSelectedBlockGeneratable: boolean;

	#editorWindow: ?Window;
	#highlight: Highlight;
	#highlightSelectable: Highlight;

	static async create(options: BlockGeneratorOptions): ?BlockGenerator
	{
		if (
			!options.copilotChat
			|| !options.chatId
		)
		{
			return null;
		}

		const generator = new BlockGenerator(options);
		await generator.init();

		return generator;
	}

	constructor(options: BlockGeneratorOptions)
	{
		super();
		this.setEventNamespace('BX.Landing.View.BlockGenerator');

		this.#blocks = new Map();
		this.#selectedBlock = null;
		this.#isSelectedBlockGeneratable = false;

		this.#highlight = (new Highlight())
			.setBorder(BlockGenerator.highlightColor, BlockGenerator.highlightWidth)
			.setBackground('transparent')
			.setSingleMode()
		;
		this.#highlightSelectable = (new Highlight())
			.setBorder(BlockGenerator.highlightColor, 0)
			.setBackground(BlockGenerator.highlightBackground)
			.setSingleMode()
		;

		this.#chatId = options.chatId;
		this.#copilotChat = options.copilotChat;

		this.onBlockMouseEnter = this.onBlockMouseEnter.bind(this);
		this.onBlockClick = this.onBlockClick.bind(this);
		this.onChatMessage = this.onChatMessage.bind(this);
		this.onEditorLoad = this.onEditorLoad.bind(this);

		this.#editorWindow = PageObject.getEditorWindow();
		if (!this.#editorWindow)
		{
			console.error('Can not load editor window');
		}

		if (this.#editorWindow.document.readyState === 'complete')
		{
			this.onEditorLoad();
		}
		else
		{
			Event.bind(this.#editorWindow, 'load', this.onEditorLoad);
		}
	}

	async init()
	{
		const exports = await Runtime.loadExtension('landing.copilot.chat');

		this.#copilotChatEvents = exports.CopilotChatEvents;
		this.#copilotChatMessageType = exports.CopilotChatMessageType;
	}

	onEditorLoad()
	{
		const blocks = PageObject.getBlocks();
		blocks.forEach((block) => {
			this.#blocks.set(block.id, {
				id: block.id,
				anchor: block.anchor,
				content: block.content,
			});
		});

		this.#editorWindow.BX.addCustomEvent(this.#editorWindow, 'BX.Landing.Block:init', (event) => {
			this.onBlockAdd(event.blockId, event.block.id, event.content);
		});

		if (this.#selectMode)
		{
			this.#bindBlocksSelectEvents();
		}
	}

	onBlockAdd(blockId: number, anchor: string, content: HTMLElement): void
	{
		this.#blocks.set(blockId, {
			id: blockId,
			anchor,
			content,
		});
		if (this.#selectMode)
		{
			this.#bindBlocksSelectEvents();
		}
	}

	onBlockClick(event: MouseEvent)
	{
		const target = event.currentTarget;
		let selectedBlock: ?SelectedBlock = null;
		for (const block of this.#blocks.values())
		{
			if (target === block.content)
			{
				selectedBlock = block;
			}
		}

		const chatId = this.#chatId;
		if (selectedBlock === null)
		{
			return;
		}

		if (this.#selectedBlock !== null && this.#selectedBlock.id === selectedBlock.id)
		{
			return;
		}

		this.#selectedBlock = selectedBlock;

		Ajax.runAction(
			'landing.api.copilot.checkBlockGeneratable',
			{
				data: {
					blockId: selectedBlock.id,
					chatId,
				},
			},
		)
			.then((result) => {
				if (result.data)
				{
					this.#isSelectedBlockGeneratable = true;
					this.#highlightSelectable.show(this.#selectedBlock.content);
				}
				else
				{
					this.#isSelectedBlockGeneratable = false;
					this.#highlightSelectable.hide();
				}
			})

			.catch(() => {
				this.#isSelectedBlockGeneratable = false;
				this.#highlightSelectable.hide();
			})
		;
	}

	onChatMessage(event: BaseEvent): void
	{
		const message = event.getData()?.message;
		if (
			message
			&& (message.authorId || 0) > 0
			&& message.type === this.#copilotChatMessageType.DEFAULT
		)
		{
			if (this.#selectedBlock === null)
			{
				Ajax.runAction(
					'landing.api.copilot.sendBlockGenerationNeedSelectMessage',
					{
						data: {
							siteId: Env.getInstance().getSiteId(),
						},
					},
				);

				return;
			}

			if (!this.#isSelectedBlockGeneratable)
			{
				Ajax.runAction(
					'landing.api.copilot.sendBlockGenerationWrongSelectMessage',
					{
						data: {
							siteId: Env.getInstance().getSiteId(),
						},
					},
				);

				return;
			}

			let loadAnimation: Promise = Promise.resolve();

			if (!this.#animationCopilot)
			{
				const editorWindow = PageObject.getEditorWindow();
				loadAnimation = editorWindow.BX.Runtime
					.loadExtension('landing.animation.copilot')
					.then((exports) => {
						this.#animationCopilot = new exports.Copilot();
						this.#animationCopilot.subscribe(
							'onBlockFinish',
							this.onBlockAnimateFinish.bind(this),
						);
						this.#subscribePullEvents();
					});
			}

			loadAnimation
				.then(() => {
					return Ajax.runAction(
						'landing.api.copilot.executeBlockGeneration',
						{
							data: {
								siteId: Env.getInstance().getSiteId(),
								blockId: this.#selectedBlock.id,
								wishes: message.content,
							},
						},
					);
				})
				.then((result) => {
					if (result.status === 'success')
					{
						this.#generationId = result.data;

						this.#animationCopilot
							.setBlocksData([
								{
									id: this.#selectedBlock.id,
									anchor: this.#selectedBlock.anchor,
									element: this.#selectedBlock.content,
									images: [],
								},
							])
							.disableEditor()
							.showLoader()
						;

						this.onBlockGenerationStart();
					}
				})
				.catch((err) => {
					console.error('Error while execute block generation', err);
				})
			;
		}
	}

	#subscribePullEvents()
	{
		PULL.subscribe({
			type: PullClient.SubscriptionType.Server,
			moduleId: 'landing',
			callback: event => {
				if (!this.#isGenerationProcessed)
				{
					return;
				}

				if (
					event.params.generationId !== undefined
					&& this.#generationId !== null
					&& event.params.generationId !== this.#generationId
				)
				{
					return;
				}

				const command = event.command;

				if (command === 'LandingCopilotGeneration:onGenerationFinish')
				{
					History.getInstance().push();
				}

				if (command === 'LandingCopilotGeneration:onGenerationError')
				{
					this.#animationCopilot.stop();
					this.#animationCopilot.hideLoader();
					this.#animationCopilot.enableEditor();
					this.onBlockAnimateFinish();
				}
			},
		});
	}

	onBlockGenerationStart(): void
	{
		this.#isGenerationProcessed = true;

		setTimeout(() => {
			if (this.#isGenerationProcessed)
			{
				this.emit('onGenerationStart');
			}
		}, 3000);
	}

	onBlockAnimateFinish(): void
	{
		this.#isGenerationProcessed = false;
		this.#selectedBlock = null;
		this.#highlightSelectable.hide()

		setTimeout(() => {
			this.emit('onGenerationFinish');
		}, 3000);
	}

	setSelectMode(): void
	{
		const stylePanel = StylePanel.getInstance();
		if (stylePanel.isShown())
		{
			stylePanel.hide();
		}

		if (this.#selectMode)
		{
			return;
		}

		this.#selectMode = true;
		this.#bindBlocksSelectEvents();

		if (this.#copilotChat && this.#copilotChatEvents && this.#copilotChatMessageType)
		{
			this.#copilotChat.subscribe(this.#copilotChatEvents.NEW_MESSAGE, this.onChatMessage);
		}

		this.disableEditor();
	}

	disableEditor()
	{
		Dom.addClass(this.#editorWindow.document.body, BlockGenerator.selectModeClass);
	}

	#bindBlocksSelectEvents(): void
	{
		for (const block of this.#blocks.values())
		{
			const node = block.content;
			Event.bind(node, 'mouseenter', this.onBlockMouseEnter);
			Event.bind(node, 'click', this.onBlockClick);
		}
	}

	unsetSelectMode(): void
	{
		if (!this.#selectMode)
		{
			return;
		}

		this.#selectMode = false;
		this.#unbindBlocksSelectEvents();
		this.#highlight.hide();
		this.#highlightSelectable.hide();

		if (this.#copilotChat && this.#copilotChatEvents)
		{
			this.#copilotChat.unsubscribe(this.#copilotChatEvents.NEW_MESSAGE, this.onChatMessage);
		}

		this.enableEditor();
	}

	enableEditor()
	{
		Dom.removeClass(this.#editorWindow.document.body, BlockGenerator.selectModeClass);
	}

	#unbindBlocksSelectEvents(): void
	{
		for (const block of this.#blocks.values())
		{
			const node = block.content;
			Event.unbind(node, 'mouseenter', this.onBlockMouseEnter);
			Event.unbind(node, 'click', this.onBlockClick);
		}
	}

	onBlockMouseEnter(event: MouseEvent)
	{
		this.#highlight.show(event.currentTarget);
	}
}

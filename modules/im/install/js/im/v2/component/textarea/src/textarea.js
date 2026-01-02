import 'ui.icon-set.outline';
import { Extension, Type } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';
import { BIcon, Outline as OutlineIcons } from 'ui.icon-set.api.vue';

import { EventType, LocalStorageKey, SoundType, TextareaPanelType as PanelType, Color } from 'im.v2.const';
import { Analytics } from 'im.v2.lib.analytics';
import { Logger } from 'im.v2.lib.logger';
import { DraftManager } from 'im.v2.lib.draft';
import { Utils } from 'im.v2.lib.utils';
import { Parser } from 'im.v2.lib.parser';
import { LocalStorageManager } from 'im.v2.lib.local-storage';
import { SendingService } from 'im.v2.provider.service.sending';
import { MessageService } from 'im.v2.provider.service.message';
import { UploadingService, MultiUploadingService, type MultiUploadingResult } from 'im.v2.provider.service.uploading';
import { SoundNotificationManager } from 'im.v2.lib.sound-notification';
import { isSendMessageCombination, isNewLineCombination } from 'im.v2.lib.hotkey';
import { Textarea } from 'im.v2.lib.textarea';
import { InputAction } from 'im.v2.lib.input-action';
import { SendButton } from 'im.v2.component.elements.send-button';
import { getFilesFromDataTransfer, isFilePasted } from 'ui.uploader.core';
import { EscEventAction } from 'im.v2.lib.esc-manager';

import { MentionManager, MentionManagerEvents } from './classes/mention-manager';
import { InputSenderService } from './classes/input-sender-service';
import { ResizeDirection, ResizeManager } from './classes/resize-manager';
import { AudioInput } from './components/audio-input/audio-input';
import { SmileSelector } from './components/smile-selector/smile-selector';
import { UploadMenu } from './components/upload-menu/upload-menu';
import { UploadPreviewPopup } from './components/upload-preview/upload-preview-popup';
import { MentionPopup } from './components/mention/mention-popup';
import { TextareaPanel } from './components/panel/panel';
import { AutoDeleteSelector } from './components/auto-delete-selector/auto-delete-selector';

import './css/textarea.css';

import type { JsonObject } from 'main.core';
import type { ImModelChat, ImModelMessage } from 'im.v2.model';
import type { InsertTextEvent, InsertMentionEvent } from 'im.v2.const';
import type { ForwardedEntityConfig, PanelContextWithMultipleIds } from 'im.v2.provider.service.sending';

const MESSAGE_ACTION_PANELS = new Set([PanelType.edit, PanelType.reply, PanelType.forward, PanelType.forwardEntity]);
const TextareaHeight = {
	max: 400,
	min: 22,
};
const ICON_SIZE = 24;

// @vue/component
export const ChatTextarea = {
	components: {
		UploadMenu,
		SmileSelector,
		SendButton,
		UploadPreviewPopup,
		MentionPopup,
		TextareaPanel,
		AudioInput,
		AutoDeleteSelector,
		BIcon,
	},
	props: {
		dialogId: {
			type: String,
			default: '',
		},
		placeholder: {
			type: String,
			default: '',
		},
		withMarket: {
			type: Boolean,
			default: true,
		},
		withEdit: {
			type: Boolean,
			default: true,
		},
		withUploadMenu: {
			type: Boolean,
			default: true,
		},
		withSmileSelector: {
			type: Boolean,
			default: true,
		},
		withAutoFocus: {
			type: Boolean,
			default: true,
		},
		withMention: {
			type: Boolean,
			default: true,
		},
	},
	emits: ['mounted'],
	data(): JsonObject
	{
		return {
			text: '',
			textareaHeight: TextareaHeight.min,

			showMention: false,
			mentionQuery: '',

			showUploadPreviewPopup: false,
			previewPopupUploaderIds: [],
			previewPopupUploadingId: null,
			previewPopupSourceFilesCount: 0,

			panelType: PanelType.none,
			panelContext: {
				messageId: 0,
			},
		};
	},
	computed:
	{
		OutlineIcons: () => OutlineIcons,
		ICON_SIZE: () => ICON_SIZE,
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		dialogInited(): boolean
		{
			return this.dialog.inited;
		},
		replyMode(): boolean
		{
			return this.panelType === PanelType.reply;
		},
		forwardMode(): boolean
		{
			return this.panelType === PanelType.forward;
		},
		forwardEntityMode(): boolean
		{
			return this.panelType === PanelType.forwardEntity;
		},
		editMode(): boolean
		{
			return this.panelType === PanelType.edit;
		},
		marketMode(): boolean
		{
			return this.panelType === PanelType.market;
		},
		isDisabled(): boolean
		{
			return this.text.trim() === '' && !this.editMode && !this.forwardMode && !this.forwardEntityMode;
		},
		textareaPlaceholder(): string
		{
			if (!this.placeholder)
			{
				return this.loc('IM_TEXTAREA_PLACEHOLDER_V3');
			}

			return this.placeholder;
		},
		textareaStyle(): Object
		{
			let height = `${this.textareaHeight}px`;
			if (this.textareaHeight === 'auto')
			{
				height = 'auto';
			}

			return {
				height,
				maxHeight: height,
			};
		},
		textareaMaxLength(): number
		{
			const settings = Extension.getSettings('im.v2.component.textarea');

			return settings.get('maxLength');
		},
		isEmptyText(): boolean
		{
			return this.text === '';
		},
		isAutoDeleteEnabled(): boolean
		{
			return this.$store.getters['chats/autoDelete/isEnabled'](this.dialog.chatId);
		},
		marketIconColor(): string
		{
			if (this.marketMode)
			{
				return Color.accentBlue;
			}

			return Color.gray40;
		},
		isFocused(): boolean
		{
			return this.$refs.textarea === document.activeElement;
		},
	},
	watch:
	{
		text(newValue)
		{
			this.adjustTextareaHeight();
			this.getDraftManager().setDraftText(this.dialogId, newValue);

			if (Type.isStringFilled(newValue))
			{
				this.getInputActionService().startAction(InputAction.writing);
			}
		},
	},
	created()
	{
		this.initResizeManager();
		this.restoreTextareaHeight();
		void this.restorePanel();
		this.initSendingService();

		EventEmitter.subscribe(EventType.textarea.insertMention, this.onInsertMention);
		EventEmitter.subscribe(EventType.textarea.insertText, this.onInsertText);
		EventEmitter.subscribe(EventType.textarea.editMessage, this.onEditMessage);
		EventEmitter.subscribe(EventType.textarea.replyMessage, this.onReplyMessage);
		EventEmitter.subscribe(EventType.textarea.forwardEntity, this.onForwardEntity);
		EventEmitter.subscribe(EventType.textarea.sendMessage, this.onSendMessage);
		EventEmitter.subscribe(EventType.textarea.insertForward, this.onInsertForward);
		EventEmitter.subscribe(EventType.textarea.openUploadPreview, this.onOpenUploadPreview);
		EventEmitter.subscribe(EventType.key.onBeforeEscape, this.onBeforeEscape);
		EventEmitter.subscribe(EventType.dialog.closeComments, this.onCloseComments);

		EventEmitter.subscribe(EventType.dialog.onMessageDeleted, this.onMessageDeleted);
	},
	mounted()
	{
		void this.initMentionManager();

		if (this.withAutoFocus)
		{
			this.focus();
		}

		this.$emit('mounted');
	},
	beforeUnmount()
	{
		this.resizeManager.destroy();
		this.unbindUploadingService();
		EventEmitter.unsubscribe(EventType.textarea.insertMention, this.onInsertMention);
		EventEmitter.unsubscribe(EventType.textarea.insertText, this.onInsertText);
		EventEmitter.unsubscribe(EventType.textarea.editMessage, this.onEditMessage);
		EventEmitter.unsubscribe(EventType.textarea.replyMessage, this.onReplyMessage);
		EventEmitter.unsubscribe(EventType.textarea.forwardEntity, this.onForwardEntity);
		EventEmitter.unsubscribe(EventType.textarea.sendMessage, this.onSendMessage);
		EventEmitter.unsubscribe(EventType.textarea.insertForward, this.onInsertForward);
		EventEmitter.unsubscribe(EventType.textarea.openUploadPreview, this.onOpenUploadPreview);
		EventEmitter.unsubscribe(EventType.key.onBeforeEscape, this.onBeforeEscape);
		EventEmitter.unsubscribe(EventType.dialog.closeComments, this.onCloseComments);

		EventEmitter.unsubscribe(EventType.dialog.onMessageDeleted, this.onMessageDeleted);
	},
	methods:
	{
		sendMessage()
		{
			this.text = this.text.trim();
			if (this.isDisabled || !this.dialogInited)
			{
				return;
			}

			const text = this.mentionManager.replaceMentions(this.text);

			if (this.hasActiveMessageAction())
			{
				this.handlePanelAction(text);
				this.closePanel();
			}
			else
			{
				this.getSendingService().sendMessage({ text, dialogId: this.dialogId });
				SoundNotificationManager.getInstance().playOnce(SoundType.send);
			}

			this.getInputActionService().stopAction(InputAction.writing);
			this.clear();
			this.getDraftManager().clearDraft(this.dialogId);
			this.focus();
			EventEmitter.emit(EventType.textarea.onAfterSendMessage);
		},
		handlePanelAction(text: string)
		{
			if (this.editMode)
			{
				this.handleEditAction(text);
			}
			else if (this.forwardMode)
			{
				void this.getSendingService().forwardMessages({
					text,
					dialogId: this.dialogId,
					forwardIds: this.panelContext.messagesIds,
				});
				SoundNotificationManager.getInstance().playOnce(SoundType.send);
			}
			else if (this.forwardEntityMode)
			{
				console.error('sending forwarded entity message');
			}
			else if (this.replyMode)
			{
				this.getSendingService().sendMessage({
					text,
					dialogId: this.dialogId,
					replyId: this.panelContext.messageId,
				});
				SoundNotificationManager.getInstance().playOnce(SoundType.send);
			}
		},
		handleEditAction(text: string): void
		{
			if (text === '' && !this.messageHasFiles(this.panelContext.messageId))
			{
				return this.getMessageService().deleteMessages([this.panelContext.messageId]);
			}

			return this.getMessageService().editMessageText(this.panelContext.messageId, text);
		},
		messageHasFiles(messageId: number): boolean
		{
			const message = this.$store.getters['messages/getById'](messageId);
			if (!message)
			{
				return false;
			}

			return message.files.length > 0;
		},
		clear()
		{
			this.text = '';
			this.mentionManager?.clearMentionReplacements();
		},
		hasActiveMessageAction(): boolean
		{
			return MESSAGE_ACTION_PANELS.has(this.panelType);
		},
		closePanel()
		{
			if (this.editMode)
			{
				this.clear();
			}
			this.panelType = PanelType.none;
			this.panelContext = {
				messageId: 0,
			};

			this.draftManager.setDraftPanel(this.dialogId, this.panelType, this.panelContext);
		},
		openEditPanel(messageId: number)
		{
			if (!this.withEdit)
			{
				return;
			}

			const message: ImModelMessage = this.$store.getters['messages/getById'](messageId);
			if (message.isDeleted)
			{
				return;
			}

			this.panelType = PanelType.edit;
			this.panelContext.messageId = messageId;

			const mentions = this.mentionManager.extractMentions(message.text);
			this.mentionManager.setMentionReplacements(mentions);

			this.text = Parser.prepareEdit(message);
			this.focus();

			this.draftManager.setDraftText(this.dialogId, this.text);
			this.draftManager.setDraftPanel(this.dialogId, this.panelType, this.panelContext);
			this.draftManager.setDraftMentions(this.dialogId, mentions);
		},
		openReplyPanel(messageId: number)
		{
			if (this.editMode)
			{
				this.clear();
			}
			this.panelType = PanelType.reply;
			this.panelContext.messageId = messageId;
			this.focus();

			this.draftManager.setDraftPanel(this.dialogId, this.panelType, this.panelContext);
		},
		openForwardPanel(messagesIds: number[])
		{
			this.panelType = PanelType.forward;
			this.panelContext.messageId = 0;
			this.panelContext.messagesIds = messagesIds;
			this.clear();
			this.focus();

			this.draftManager.setDraftPanel(this.dialogId, this.panelType, this.panelContext);
		},
		async openForwardEntityPanel(entityConfig: ForwardedEntityConfig)
		{
			this.panelType = PanelType.forwardEntity;
			this.panelContext.messageId = 0;
			this.panelContext.entityConfig = entityConfig;
			this.clear();
			this.focus();
		},
		toggleMarketPanel()
		{
			if (this.marketMode)
			{
				this.panelType = PanelType.none;

				return;
			}
			this.panelType = PanelType.market;
			this.panelContext.messageId = 0;
		},
		async adjustTextareaHeight()
		{
			this.textareaHeight = 'auto';

			await this.$nextTick();
			const newMaxPoint = Math.min(TextareaHeight.max, this.$refs.textarea.scrollHeight);
			if (this.resizedTextareaHeight)
			{
				this.textareaHeight = Math.max(newMaxPoint, this.resizedTextareaHeight);

				return;
			}

			this.textareaHeight = Math.max(newMaxPoint, TextareaHeight.min);
		},
		saveTextareaHeight()
		{
			const WRITE_TO_STORAGE_TIMEOUT = 200;
			clearTimeout(this.saveTextareaTimeout);
			this.saveTextareaTimeout = setTimeout(() => {
				LocalStorageManager.getInstance().set(LocalStorageKey.textareaHeight, this.resizedTextareaHeight);
			}, WRITE_TO_STORAGE_TIMEOUT);
		},
		restoreTextareaHeight()
		{
			const rawSavedHeight = LocalStorageManager.getInstance().get(LocalStorageKey.textareaHeight);
			const savedHeight = Number.parseInt(rawSavedHeight, 10);
			if (!savedHeight)
			{
				return;
			}

			this.resizedTextareaHeight = savedHeight;
			this.textareaHeight = savedHeight;
		},
		checkMessageExists(messageId: number): boolean
		{
			return this.$store.getters['messages/isExists'](messageId);
		},
		verifyPanelContext(panelContext: PanelContextWithMultipleIds): boolean
		{
			if (panelContext.messagesIds)
			{
				return panelContext.messagesIds.every((messageId) => this.checkMessageExists(messageId));
			}

			return this.checkMessageExists(panelContext.messageId);
		},
		async restorePanel()
		{
			const {
				text = '',
				panelType = PanelType.none,
				panelContext = {
					messageId: 0,
				},
			} = await this.getDraftManager().getDraft(this.dialogId);

			const noPanel = this.panelType === PanelType.none;

			if (!noPanel && !this.verifyPanelContext(panelContext))
			{
				return;
			}

			this.text = text;
			if (noPanel)
			{
				this.panelType = panelType;
			}
			this.panelContext = panelContext;
		},
		onBeforeEscape(): $Values<typeof EscEventAction>
		{
			if (this.hasActiveMessageAction())
			{
				this.closePanel();

				return EscEventAction.handled;
			}

			if (this.isFocused && !this.isEmptyText)
			{
				return EscEventAction.handled;
			}

			return EscEventAction.ignored;
		},
		async onKeyDown(event: KeyboardEvent)
		{
			Analytics.getInstance().onTypeMessage(this.dialog);

			if (this.showMention && this.withMention)
			{
				this.mentionManager.onActiveMentionKeyDown(event);

				return;
			}

			const sendMessageCombination = isSendMessageCombination(event);
			const newLineCombination = isNewLineCombination(event);
			if (sendMessageCombination && !newLineCombination)
			{
				event.preventDefault();
				this.sendMessage();

				return;
			}

			if (newLineCombination)
			{
				this.handleNewLine();

				return;
			}

			const tabCombination = Utils.key.isCombination(event, 'Tab');
			if (tabCombination)
			{
				this.handleTab(event);

				return;
			}

			const decorationCombination = Utils.key.isExactCombination(event, ['Ctrl+b', 'Ctrl+i', 'Ctrl+u', 'Ctrl+s']);
			if (decorationCombination)
			{
				event.preventDefault();
				this.text = Textarea.handleDecorationTag(this.$refs.textarea, event.code);

				return;
			}

			if (this.text === '' && Utils.key.isCombination(event, 'ArrowUp'))
			{
				this.handleLastOwnMessageEdit(event);

				return;
			}

			this.mentionManager.onKeyDown(event);
		},
		handleNewLine()
		{
			this.text = Textarea.addNewLine(this.$refs.textarea);
		},
		handleTab(event: KeyboardEvent)
		{
			event.preventDefault();
			if (event.shiftKey)
			{
				this.text = Textarea.removeTab(this.$refs.textarea);

				return;
			}
			this.text = Textarea.addTab(this.$refs.textarea);
		},
		handleLastOwnMessageEdit(event: KeyboardEvent)
		{
			event.preventDefault();
			const lastOwnMessageId = this.$store.getters['messages/getLastOwnMessageId'](this.dialog.chatId);
			const isForward = this.$store.getters['messages/isForward'](lastOwnMessageId);
			if (lastOwnMessageId && !isForward)
			{
				this.openEditPanel(lastOwnMessageId);
			}
		},
		onSendMessage(event: BaseEvent<{ text: string, dialogId: string }>)
		{
			const { text, dialogId } = event.getData();
			if (this.dialogId !== dialogId)
			{
				return;
			}
			this.getSendingService().sendMessage({ text, dialogId: this.dialogId });
		},
		onResizeStart(event)
		{
			this.resizeManager.onResizeStart(event, this.textareaHeight);
		},
		async onFileSelect({ event, sendAsFile }: Event)
		{
			const multiUploadingService: MultiUploadingService = this.getMultiUploadingService();
			const multiUploadingResult: MultiUploadingResult = await multiUploadingService.upload({
				files: Object.values(event.target.files),
				sendAsFile,
				dialogId: this.dialogId,
				autoUpload: false,
			});

			this.showUploadPreviewPopup = true;
			this.previewPopupUploaderIds = multiUploadingResult.uploaderIds;
			this.previewPopupUploadingId = multiUploadingResult.uploadingId;
			this.previewPopupSourceFilesCount = multiUploadingResult.sourceFilesCount;
		},
		onDiskFileSelect({ files })
		{
			this.getUploadingService().uploadFileFromDisk(files, this.dialogId);
		},
		onInsertMention(event: BaseEvent<InsertMentionEvent>)
		{
			const { mentionText, mentionReplacement, dialogId, isMentionSymbol = true } = event.getData();
			let { textToReplace = '' } = event.getData();
			if (this.dialogId !== dialogId)
			{
				return;
			}

			const mentions = this.mentionManager.addMentionReplacement(mentionText, mentionReplacement);
			this.draftManager.setDraftMentions(this.dialogId, mentions);

			const mentionSymbol = isMentionSymbol ? this.mentionManager.getMentionSymbol() : '';
			textToReplace = `${mentionSymbol}${textToReplace}`;
			this.text = Textarea.insertMention(this.$refs.textarea, {
				textToInsert: mentionText,
				textToReplace,
			});
			this.mentionManager.clearMentionSymbol();
		},
		onInsertText(event: BaseEvent<InsertTextEvent>)
		{
			const { dialogId } = event.getData();
			if (this.dialogId !== dialogId)
			{
				return;
			}
			this.text = Textarea.insertText(this.$refs.textarea, event.getData());
		},
		onEditMessage(event: BaseEvent<{ messageId: number, dialogId: string }>)
		{
			const { messageId, dialogId } = event.getData();
			if (this.dialogId !== dialogId)
			{
				return;
			}
			this.openEditPanel(messageId);
		},
		onReplyMessage(event: BaseEvent<{ messageId: number, dialogId: string }>)
		{
			const { messageId, dialogId } = event.getData();
			if (this.dialogId !== dialogId)
			{
				return;
			}
			this.openReplyPanel(messageId);
		},
		onForwardEntity(event: BaseEvent<{ dialogId: string, entityConfig: ForwardedEntityConfig }>)
		{
			const { dialogId, entityConfig } = event.getData();
			if (this.dialogId !== dialogId)
			{
				return;
			}
			this.openForwardEntityPanel(entityConfig);
		},
		onInsertForward(event: BaseEvent<{ messagesIds: number[], dialogId: string }>)
		{
			const { messagesIds, dialogId } = event.getData();
			if (this.dialogId !== dialogId)
			{
				return;
			}

			this.openForwardPanel(messagesIds);
		},
		async onPaste(event: ClipboardEvent)
		{
			this.text = Textarea.handlePasteUrl(this.$refs.textarea, event);

			if (!this.withUploadMenu)
			{
				return;
			}

			if (!event.clipboardData || !isFilePasted(event.clipboardData))
			{
				return;
			}

			event.preventDefault();

			const multiUploadingService: MultiUploadingService = this.getMultiUploadingService();
			const multiUploadingResult: MultiUploadingResult = await multiUploadingService.upload({
				files: await getFilesFromDataTransfer(event.clipboardData),
				dialogId: this.dialogId,
				autoUpload: false,
			});

			if (!Type.isArrayFilled(multiUploadingResult.uploaderIds))
			{
				return;
			}

			this.showUploadPreviewPopup = true;
			this.previewPopupUploaderIds = multiUploadingResult.uploaderIds;
			this.previewPopupUploadingId = multiUploadingResult.uploadingId;
			this.previewPopupSourceFilesCount = multiUploadingResult.sourceFilesCount;
		},
		onOpenUploadPreview(event: BaseEvent)
		{
			const { multiUploadingResult } = event.getData();

			this.showUploadPreviewPopup = true;
			this.previewPopupUploaderIds = multiUploadingResult.uploaderIds;
			this.previewPopupUploadingId = multiUploadingResult.uploadingId;
			this.previewPopupSourceFilesCount = multiUploadingResult.sourceFilesCount;
		},
		onMarketIconClick()
		{
			this.toggleMarketPanel();
		},
		onMessageDeleted(event: BaseEvent<{ messageId: number }>)
		{
			const { messageId } = event.getData();

			if (this.panelContext.messageId === messageId)
			{
				this.closePanel();
			}

			if (this.panelContext.messagesIds && this.panelContext.messagesIds.includes(messageId))
			{
				this.closePanel();
			}
		},
		initResizeManager()
		{
			this.resizeManager = new ResizeManager({
				direction: ResizeDirection.up,
				maxHeight: TextareaHeight.max,
				minHeight: TextareaHeight.min,
			});

			this.resizeManager.subscribe(ResizeManager.events.onHeightChange, ({ data: { newHeight } }) => {
				Logger.warn('Textarea: Resize height change', newHeight);
				this.textareaHeight = newHeight;
			});
			this.resizeManager.subscribe(ResizeManager.events.onResizeStop, () => {
				Logger.warn('Textarea: Resize stop');
				this.resizedTextareaHeight = this.textareaHeight;
				this.saveTextareaHeight();
			});
		},
		initSendingService()
		{
			if (this.sendingService)
			{
				return;
			}

			this.sendingService = SendingService.getInstance();
		},
		async initMentionManager()
		{
			const {
				mentions = {},
			} = await this.getDraftManager().getDraft(this.dialogId);

			this.mentionManager = new MentionManager(this.$refs.textarea);
			this.mentionManager.setMentionReplacements(mentions);

			this.mentionManager.subscribe(MentionManagerEvents.showMentionPopup, (event) => {
				const { mentionQuery } = event.getData();
				this.showMentionPopup(mentionQuery);
			});

			this.mentionManager.subscribe(MentionManagerEvents.hideMentionPopup, () => {
				this.closeMentionPopup();
			});
		},
		getSendingService(): SendingService
		{
			return this.sendingService;
		},
		getInputActionService(): InputSenderService
		{
			if (!this.inputSenderService)
			{
				this.inputSenderService = new InputSenderService(this.dialogId);
			}

			return this.inputSenderService;
		},
		getDraftManager(): DraftManager
		{
			if (!this.draftManager)
			{
				this.draftManager = DraftManager.getInstance();
			}

			return this.draftManager;
		},
		getMessageService(): MessageService
		{
			if (!this.messageService)
			{
				this.messageService = new MessageService({ chatId: this.dialog.chatId });
			}

			return this.messageService;
		},
		getUploadingService(): UploadingService
		{
			if (!this.uploadingService)
			{
				this.initUploadingService();
			}

			return this.uploadingService;
		},
		initUploadingService()
		{
			this.uploadingService = UploadingService.getInstance();

			this.startFileUploadAction = () => {
				this.getInputActionService().startAction(InputAction.sendingFile);
			};

			this.stopFileUploadAction = () => {
				this.getInputActionService().stopAction(InputAction.sendingFile);
			};
			this.uploadingService.subscribe(UploadingService.event.uploadStart, this.startFileUploadAction);
			this.uploadingService.subscribe(UploadingService.event.uploadComplete, this.stopFileUploadAction);
			this.uploadingService.subscribe(UploadingService.event.uploadCancel, this.stopFileUploadAction);
			this.uploadingService.subscribe(UploadingService.event.uploadError, this.stopFileUploadAction);
		},
		unbindUploadingService()
		{
			if (!this.uploadingService)
			{
				return;
			}

			this.uploadingService.unsubscribe(UploadingService.event.uploadStart, this.startFileUploadAction);
			this.uploadingService.unsubscribe(UploadingService.event.uploadComplete, this.stopFileUploadAction);
			this.uploadingService.unsubscribe(UploadingService.event.uploadCancel, this.stopFileUploadAction);
			this.uploadingService.unsubscribe(UploadingService.event.uploadError, this.stopFileUploadAction);
		},
		getMultiUploadingService(): MultiUploadingService
		{
			if (!this.multiUploadingService)
			{
				this.multiUploadingService = new MultiUploadingService();
			}

			return this.multiUploadingService;
		},
		async onSendFilesFromPreviewPopup(event)
		{
			this.text = '';
			const { text, files, sendAsFile } = event;
			const textWithMentions = this.mentionManager.replaceMentions(text);

			const multiUploadingResult = await this.getMultiUploadingService().upload({
				files,
				dialogId: this.dialogId,
				autoUpload: false,
				sendAsFile,
			});

			await multiUploadingResult.loadAllComplete;

			multiUploadingResult.uploaderIds.forEach((uploaderId: string, index) => {
				this.getUploadingService().sendMessageWithFiles({
					uploaderId,
					text: index === 0 ? textWithMentions : '',
				});
			});

			this.focus();
		},
		closeMentionPopup()
		{
			this.showMention = false;
			this.mentionQuery = '';
			this.mentionManager.onMentionPopupClose();
		},
		showMentionPopup(mentionQuery: string)
		{
			this.mentionQuery = mentionQuery;
			this.showMention = true;
		},
		focus(): void
		{
			this.$refs.textarea?.focus({ preventScroll: true });
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
		onAudioInputStart()
		{
			if (this.isEmptyText)
			{
				return;
			}

			this.text += ' ';
		},
		onAudioInputResult(inputText: string)
		{
			this.text += inputText;
		},
		onCloseComments()
		{
			this.restoreTextareaHeight();
		},
	},
	template: `
		<div class="bx-im-send-panel__scope bx-im-send-panel__container --ui-context-content-light">
			<div class="bx-im-textarea__container">
				<div @mousedown="onResizeStart" class="bx-im-textarea__drag-handle"></div>
				<TextareaPanel
					:type="panelType"
					:context="panelContext"
					:dialogId="dialogId"
					@close="closePanel"
				/>
				<div class="bx-im-textarea__content" ref="textarea-content" @click="focus">
					<div class="bx-im-textarea__top">
						<UploadMenu
							v-if="withUploadMenu"
							:dialogId="dialogId" 
							@fileSelect="onFileSelect" 
							@diskFileSelect="onDiskFileSelect" 
						/>
						<textarea
							v-model="text"
							:style="textareaStyle"
							:placeholder="textareaPlaceholder"
							:maxlength="textareaMaxLength"
							@keydown="onKeyDown"
							@paste="onPaste"
							class="bx-im-textarea__element"
							ref="textarea"
							rows="1"
						></textarea>
					</div>
					<div class="bx-im-textarea__bottom">
						<slot name="bottom-panel-buttons"></slot>
						<AutoDeleteSelector
							v-if="isAutoDeleteEnabled"
							:dialogId="dialogId"
						/>
						<BIcon
							v-if="withMarket"
							:name="OutlineIcons.APPS"
							:title="loc('IM_TEXTAREA_ICON_APPLICATION')"
							:size="ICON_SIZE"
							:color="marketIconColor"
							class="bx-im-textarea__icon"
							@click="onMarketIconClick"
						/>
						<SmileSelector
							v-if="withSmileSelector"
							:dialogId="dialogId"
						/>
						<AudioInput
							@inputStart="onAudioInputStart"
							@inputResult="onAudioInputResult"
						/>
						<SendButton :dialogId="dialogId" :editMode="editMode" :isDisabled="isDisabled" @click="sendMessage" />
					</div>
				</div>
			</div>
			<UploadPreviewPopup
				v-if="showUploadPreviewPopup"
				:dialogId="dialogId"
				:uploaderIds="previewPopupUploaderIds"
				:uploadingId="previewPopupUploadingId"
				:sourceFilesCount="previewPopupSourceFilesCount"
				:textareaValue="text"
				@close="showUploadPreviewPopup = false"
				@sendFiles="onSendFilesFromPreviewPopup"
			/>
			<MentionPopup 
				v-if="withMention && showMention" 
				:bindElement="$refs['textarea-content']"
				:dialogId="dialogId"
				:query="mentionQuery"
				@close="closeMentionPopup"
			/>
		</div>
	`,
};

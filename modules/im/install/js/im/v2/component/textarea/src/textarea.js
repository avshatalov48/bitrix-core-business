import { Extension, Type } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';

import { EventType, LocalStorageKey, SoundType, TextareaPanelType as PanelType } from 'im.v2.const';
import { Logger } from 'im.v2.lib.logger';
import { DraftManager } from 'im.v2.lib.draft';
import { Utils } from 'im.v2.lib.utils';
import { Parser } from 'im.v2.lib.parser';
import { LocalStorageManager } from 'im.v2.lib.local-storage';
import {
	MessageService,
	SendingService,
	UploadingService,
} from 'im.v2.provider.service';
import { SoundNotificationManager } from 'im.v2.lib.sound-notification';
import { isSendMessageCombination, isNewLineCombination } from 'im.v2.lib.hotkey';
import { Textarea } from 'im.v2.lib.textarea';
import { ChannelManager } from 'im.v2.lib.channel';

import { MentionManager, MentionManagerEvents } from './classes/mention-manager';
import { ResizeDirection, ResizeManager } from './classes/resize-manager';
import { TypingService } from './classes/typing-service';
import { AudioInput } from './components/audio-input/audio-input';
import { SmileSelector } from './components/smile-selector/smile-selector';
import { UploadMenu } from './components/upload-menu/upload-menu';
import { SendButton } from './components/send-button';
import { UploadPreviewPopup } from './components/upload-preview/upload-preview-popup';
import { MentionPopup } from './components/mention/mention-popup';
import { TextareaPanel } from './components/panel/panel';

import './css/textarea.css';

import type { JsonObject } from 'main.core';
import type { ImModelChat, ImModelMessage } from 'im.v2.model';
import type { InsertTextEvent, InsertMentionEvent } from 'im.v2.const';
import type { ForwardedEntityConfig } from 'im.v2.provider.service';

const MESSAGE_ACTION_PANELS = new Set([PanelType.edit, PanelType.reply, PanelType.forward, PanelType.forwardEntity]);
const TextareaHeight = {
	max: 400,
	min: 22,
};

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
		withCreateMenu: {
			type: Boolean,
			default: true,
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
		withAudioInput: {
			type: Boolean,
			default: true,
		},
		draftManagerClass: {
			type: Function,
			default: DraftManager,
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
			previewPopupUploaderId: '',

			panelType: PanelType.none,
			panelContext: {
				messageId: 0,
			},
		};
	},
	computed:
	{
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
		isChannelType(): boolean
		{
			return ChannelManager.isChannel(this.dialogId);
		},
		isEmptyText(): boolean
		{
			return this.text === '';
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
				this.getTypingService().startTyping();
			}
		},
	},
	created()
	{
		this.initResizeManager();
		this.restoreTextareaHeight();
		this.restoreDraft();
		this.initSendingService();

		EventEmitter.subscribe(EventType.textarea.insertMention, this.onInsertMention);
		EventEmitter.subscribe(EventType.textarea.insertText, this.onInsertText);
		EventEmitter.subscribe(EventType.textarea.editMessage, this.onEditMessage);
		EventEmitter.subscribe(EventType.textarea.replyMessage, this.onReplyMessage);
		EventEmitter.subscribe(EventType.textarea.forwardEntity, this.onForwardEntity);
		EventEmitter.subscribe(EventType.textarea.sendMessage, this.onSendMessage);
		EventEmitter.subscribe(EventType.textarea.insertForward, this.onInsertForward);
		EventEmitter.subscribe(EventType.textarea.openUploadPreview, this.onOpenUploadPreview);

		EventEmitter.subscribe(EventType.dialog.onMessageDeleted, this.onMessageDeleted);
	},
	mounted()
	{
		this.initMentionManager();
		this.focus();
		this.$emit('mounted');
	},
	beforeUnmount()
	{
		this.resizeManager.destroy();
		EventEmitter.unsubscribe(EventType.textarea.insertMention, this.onInsertMention);
		EventEmitter.unsubscribe(EventType.textarea.insertText, this.onInsertText);
		EventEmitter.unsubscribe(EventType.textarea.editMessage, this.onEditMessage);
		EventEmitter.unsubscribe(EventType.textarea.replyMessage, this.onReplyMessage);
		EventEmitter.unsubscribe(EventType.textarea.forwardEntity, this.onForwardEntity);
		EventEmitter.unsubscribe(EventType.textarea.sendMessage, this.onSendMessage);
		EventEmitter.unsubscribe(EventType.textarea.insertForward, this.onInsertForward);
		EventEmitter.unsubscribe(EventType.textarea.openUploadPreview, this.onOpenUploadPreview);

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
			}

			this.getTypingService().stopTyping();
			this.clear();
			this.getDraftManager().clearDraft(this.dialogId);
			SoundNotificationManager.getInstance().playOnce(SoundType.send);
			this.focus();
			EventEmitter.emit(EventType.textarea.onAfterSendMessage);
		},
		handlePanelAction(text: string)
		{
			if (this.editMode && text === '')
			{
				void this.getMessageService().deleteMessage(this.panelContext.messageId);
			}
			else if (this.editMode && text !== '')
			{
				this.getMessageService().editMessageText(this.panelContext.messageId, text);
			}
			else if (this.forwardMode)
			{
				this.getSendingService().forwardMessages({
					text,
					dialogId: this.dialogId,
					forwardIds: this.panelContext.messagesIds,
				});
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
			}
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
		async restoreDraft()
		{
			const {
				text = '',
				panelType = PanelType.none,
				panelContext = {
					messageId: 0,
				},
			} = await this.getDraftManager().getDraft(this.dialogId);

			this.text = text;
			if (this.panelType === PanelType.none)
			{
				this.panelType = panelType;
			}
			this.panelContext = panelContext;
		},
		async onKeyDown(event: KeyboardEvent)
		{
			if (this.showMention)
			{
				this.mentionManager.onActiveMentionKeyDown(event);

				return;
			}

			const exitActionCombination = Utils.key.isCombination(event, 'Escape');
			if (this.hasActiveMessageAction() && exitActionCombination)
			{
				this.closePanel();

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
		async onFileSelect({ event, sendAsFile }: InputEvent)
		{
			const uploaderId = await this.getUploadingService().uploadFromInput({
				event,
				sendAsFile,
				dialogId: this.dialogId,
			});

			this.showUploadPreviewPopup = true;
			this.previewPopupUploaderId = uploaderId;
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
		async onPaste(clipboardEvent: ClipboardEvent)
		{
			if (!this.withUploadMenu)
			{
				return;
			}

			const uploaderId = await this.getUploadingService().uploadFromClipboard({
				clipboardEvent,
				dialogId: this.dialogId,
				imagesOnly: !this.isChannelType,
			});

			if (!uploaderId)
			{
				return;
			}

			this.showUploadPreviewPopup = true;
			this.previewPopupUploaderId = uploaderId;
		},
		onOpenUploadPreview(event: BaseEvent)
		{
			const { uploaderId } = event.getData();

			this.showUploadPreviewPopup = true;
			this.previewPopupUploaderId = uploaderId;
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
		getTypingService(): TypingService
		{
			if (!this.typingService)
			{
				this.typingService = new TypingService(this.dialogId);
			}

			return this.typingService;
		},
		getDraftManager(): DraftManager
		{
			if (!this.draftManager)
			{
				this.draftManager = this.draftManagerClass.getInstance();
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
				this.uploadingService = UploadingService.getInstance();
			}

			return this.uploadingService;
		},
		onSendFilesFromPreviewPopup(event)
		{
			this.text = '';
			const { groupFiles, text, uploaderId } = event;
			if (groupFiles)
			{
				return;
			}

			const textWithMentions = this.mentionManager.replaceMentions(text);
			this.getUploadingService().sendMessageWithFiles({ uploaderId, text: textWithMentions });
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
	},
	template: `
		<div class="bx-im-send-panel__scope bx-im-send-panel__container">
			<div class="bx-im-textarea__container">
				<div @mousedown="onResizeStart" class="bx-im-textarea__drag-handle"></div>
				<TextareaPanel
					:type="panelType"
					:context="panelContext"
					:dialogId="dialogId"
					@close="closePanel"
				/>
				<div class="bx-im-textarea__content" ref="textarea-content">
					<div class="bx-im-textarea__left">
						<div v-if="withUploadMenu" class="bx-im-textarea__upload_container">
							<UploadMenu 
								:dialogId="dialogId" 
								@fileSelect="onFileSelect" 
								@diskFileSelect="onDiskFileSelect" 
							/>
						</div>
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
						<AudioInput
							v-if="withAudioInput"
							@inputStart="onAudioInputStart"
							@inputResult="onAudioInputResult"
						/>
					</div>
					<div class="bx-im-textarea__right">
						<div class="bx-im-textarea__action-panel">
							<div
								v-if="withMarket"
								:title="loc('IM_TEXTAREA_ICON_APPLICATION')"
								@click="onMarketIconClick"
								class="bx-im-textarea__icon --market"
								:class="{'--active': marketMode}"
							></div>
							<SmileSelector 
								v-if="withSmileSelector" 
								:dialogId="dialogId" 
							/>
						</div>
					</div>
				</div>
			</div>
			<SendButton :dialogId="dialogId" :editMode="editMode" :isDisabled="isDisabled" @click="sendMessage" />
			<UploadPreviewPopup
				v-if="showUploadPreviewPopup"
				:dialogId="dialogId"
				:uploaderId="previewPopupUploaderId"
				:textareaValue="text"
				@close="showUploadPreviewPopup = false"
				@sendFiles="onSendFilesFromPreviewPopup"
			/>
			<MentionPopup 
				v-if="showMention" 
				:bindElement="$refs['textarea-content']"
				:dialogId="dialogId"
				:query="mentionQuery"
				@close="closeMentionPopup"
			/>
		</div>
	`,
};

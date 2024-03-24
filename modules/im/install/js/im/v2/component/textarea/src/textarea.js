import { Extension, Type } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';
import { isFilePasted } from 'ui.uploader.core';

import { EventType, LocalStorageKey, SoundType, TextareaPanelType as PanelType } from 'im.v2.const';
import { Logger } from 'im.v2.lib.logger';
import { DraftManager } from 'im.v2.lib.draft';
import { Utils } from 'im.v2.lib.utils';
import { Parser } from 'im.v2.lib.parser';
import { LocalStorageManager } from 'im.v2.lib.local-storage';
import { MessageService, SendingService, UploadingService } from 'im.v2.provider.service';
import { SoundNotificationManager } from 'im.v2.lib.sound-notification';
import { isSendMessageCombination, isNewLineCombination } from 'im.v2.lib.hotkey';
import { Textarea } from 'im.v2.lib.textarea';

import { MentionManager, MentionManagerEvents } from './classes/mention-manager';
import { ResizeManager } from './classes/resize-manager';
import { TypingService } from './classes/typing-service';
import { SmileSelector } from './components/smile-selector/smile-selector';
import { UploadMenu } from './components/upload-menu/upload-menu';
import { CreateEntityMenu } from './components/create-entity-menu/create-entity-menu';
import { SendButton } from './components/send-button';
import { UploadPreviewPopup } from './components/upload-preview/upload-preview-popup';
import { MentionPopup } from './components/mention/mention-popup';
import { TextareaPanel } from './components/panel/panel';

import './css/textarea.css';

import type { ImModelChat, ImModelMessage } from 'im.v2.model';
import type { InsertTextEvent, InsertMentionEvent } from 'im.v2.const';

const MESSAGE_ACTION_PANELS = new Set([PanelType.edit, PanelType.reply, PanelType.forward]);

// @vue/component
export const ChatTextarea = {
	components: {
		UploadMenu,
		CreateEntityMenu,
		SmileSelector,
		SendButton,
		UploadPreviewPopup,
		MentionPopup,
		TextareaPanel,
	},
	props: {
		dialogId: {
			type: String,
			default: '',
		},
	},
	data(): { [key: string]: any}
	{
		return {
			text: '',
			textareaHeight: ResizeManager.minHeight,

			showMention: false,
			mentionQuery: '',

			showUploadPreviewPopup: false,
			previewPopupUploaderId: '',

			panelType: PanelType.none,
			panelMessageId: 0,
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
			return this.text.trim() === '' && !this.editMode && !this.forwardMode;
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
		EventEmitter.subscribe(EventType.textarea.sendMessage, this.onSendMessage);
		EventEmitter.subscribe(EventType.textarea.insertForward, this.onInsertForward);

		EventEmitter.subscribe(EventType.dialog.onMessageDeleted, this.onMessageDeleted);
	},
	mounted()
	{
		this.initMentionManager();
		this.focus();
	},
	beforeUnmount()
	{
		this.resizeManager.destroy();
		EventEmitter.unsubscribe(EventType.textarea.insertMention, this.onInsertMention);
		EventEmitter.unsubscribe(EventType.textarea.insertText, this.onInsertText);
		EventEmitter.unsubscribe(EventType.textarea.editMessage, this.onEditMessage);
		EventEmitter.unsubscribe(EventType.textarea.replyMessage, this.onReplyMessage);
		EventEmitter.unsubscribe(EventType.textarea.sendMessage, this.onSendMessage);
		EventEmitter.unsubscribe(EventType.textarea.insertForward, this.onInsertForward);

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
		},
		handlePanelAction(text: string)
		{
			if (this.editMode && text === '')
			{
				void this.getMessageService().deleteMessage(this.panelMessageId);
			}
			else if (this.editMode && text !== '')
			{
				this.getMessageService().editMessageText(this.panelMessageId, text);
			}
			else if (this.forwardMode)
			{
				this.getSendingService().forwardMessages({
					text,
					dialogId: this.dialogId,
					forwardIds: [this.panelMessageId],
				});
			}
			else if (this.replyMode)
			{
				this.getSendingService().sendMessage({
					text,
					dialogId: this.dialogId,
					replyId: this.panelMessageId,
				});
			}
		},
		clear()
		{
			this.text = '';
			this.mentionManager.clearMentionReplacements();
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
			this.panelMessageId = 0;

			this.draftManager.setDraftPanel(this.dialogId, this.panelType, this.panelMessageId);
		},
		openEditPanel(messageId: number)
		{
			const message: ImModelMessage = this.$store.getters['messages/getById'](messageId);
			if (message.isDeleted)
			{
				return;
			}

			this.panelType = PanelType.edit;
			this.panelMessageId = messageId;

			const mentions = this.mentionManager.extractMentions(message.text);
			console.warn('openEditPanel', mentions);
			this.mentionManager.setMentionReplacements(mentions);

			this.text = Parser.prepareEdit(message);
			this.focus();

			this.draftManager.setDraftText(this.dialogId, this.text);
			this.draftManager.setDraftPanel(this.dialogId, this.panelType, this.panelMessageId);
			this.draftManager.setDraftMentions(this.dialogId, mentions);
		},
		openReplyPanel(messageId: number)
		{
			if (this.editMode)
			{
				this.clear();
			}
			this.panelType = PanelType.reply;
			this.panelMessageId = messageId;
			this.focus();

			this.draftManager.setDraftPanel(this.dialogId, this.panelType, this.panelMessageId);
		},
		openForwardPanel(messageId: number)
		{
			this.panelType = PanelType.forward;
			this.panelMessageId = messageId;
			this.clear();
			this.focus();

			this.draftManager.setDraftPanel(this.dialogId, this.panelType, this.panelMessageId);
		},
		toggleMarketPanel()
		{
			if (this.marketMode)
			{
				this.panelType = PanelType.none;

				return;
			}
			this.panelType = PanelType.market;
			this.panelMessageId = 0;
		},
		async adjustTextareaHeight()
		{
			this.textareaHeight = 'auto';

			await this.$nextTick();
			const newMaxPoint = Math.min(ResizeManager.maxHeight, this.$refs.textarea.scrollHeight);
			if (this.resizedTextareaHeight)
			{
				this.textareaHeight = Math.max(newMaxPoint, this.resizedTextareaHeight);

				return;
			}

			this.textareaHeight = Math.max(newMaxPoint, ResizeManager.minHeight);
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
			this.adjustTextareaHeight();
		},
		async restoreDraft()
		{
			const {
				text = '',
				panelType = PanelType.none,
				panelMessageId = 0,
			} = await this.getDraftManager().getDraft(this.dialogId);

			this.text = text;
			this.panelType = panelType;
			this.panelMessageId = panelMessageId;
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

			const decorationCombination = Utils.key.isCombination(event, ['Ctrl+b', 'Ctrl+i', 'Ctrl+u', 'Ctrl+s']);
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
		onSendMessage(event: BaseEvent<{ text: string }>)
		{
			const { text } = event.getData();
			this.getSendingService().sendMessage({ text, dialogId: this.dialogId });
		},
		onResizeStart(event)
		{
			this.resizeManager.onResizeStart(event, this.textareaHeight);
		},
		onFileSelect({ event, sendAsFile }: InputEvent)
		{
			const files = Object.values(event.target.files);

			this.getUploadingService().addFilesFromInput(files, this.dialogId, sendAsFile);
		},
		onDiskFileSelect({ files })
		{
			this.getUploadingService().uploadFileFromDisk(files, this.dialogId);
		},
		onInsertMention(event: BaseEvent<InsertMentionEvent>)
		{
			const { mentionText, mentionReplacement, textToReplace = '' } = event.getData();

			const mentions = this.mentionManager.addMentionReplacement(mentionText, mentionReplacement);
			this.draftManager.setDraftMentions(this.dialogId, mentions);

			this.text = this.mentionManager.prepareMentionText({
				currentText: this.text,
				textToInsert: mentionText,
				textToReplace,
			});
			this.focus();
		},
		onInsertText(event: BaseEvent<InsertTextEvent>)
		{
			this.text = Textarea.insertText(this.$refs.textarea, event.getData());
			this.focus();
		},
		onEditMessage(event: BaseEvent<{ messageId: number }>)
		{
			const { messageId } = event.getData();
			this.openEditPanel(messageId);
		},
		onReplyMessage(event: BaseEvent<{ messageId: number }>)
		{
			const { messageId } = event.getData();
			this.openReplyPanel(messageId);
		},
		onInsertForward(event: BaseEvent<{ messageId: number}>)
		{
			const { messageId } = event.getData();
			this.openForwardPanel(messageId);
		},
		async onPaste(clipboardEvent: ClipboardEvent)
		{
			const { clipboardData } = clipboardEvent;
			if (!clipboardData || !isFilePasted(clipboardData))
			{
				return;
			}

			clipboardEvent.preventDefault();

			const { files, uploaderId } = await this.getUploadingService().addFilesFromClipboard(clipboardData, this.dialogId)
				.catch((error) => {
					Logger.error('Textarea: error paste file from clipboard', error);
				});

			if (files.length === 0)
			{
				return;
			}

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
			if (this.panelMessageId === messageId)
			{
				this.closePanel();
			}
		},
		initResizeManager()
		{
			this.resizeManager = new ResizeManager();
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
			this.getUploadingService().sendSeparateMessagesWithFiles({ uploaderId, text: textWithMentions });
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
			this.$refs?.textarea.focus();
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-send-panel__scope bx-im-send-panel__container">
			<div class="bx-im-textarea__container">
				<div @mousedown="onResizeStart" class="bx-im-textarea__drag-handle"></div>
				<TextareaPanel
					:type="panelType"
					:messageId="panelMessageId"
					:dialogId="dialogId"
					@close="closePanel"
				/>
				<div class="bx-im-textarea__content">
					<div class="bx-im-textarea__left">
						<div class="bx-im-textarea__upload_container">
							<UploadMenu @fileSelect="onFileSelect" @diskFileSelect="onDiskFileSelect" />
						</div>
						<textarea
							v-model="text"
							:style="textareaStyle"
							:placeholder="loc('IM_TEXTAREA_PLACEHOLDER_V3')"
							:maxlength="textareaMaxLength"
							@keydown="onKeyDown"
							@paste="onPaste"
							class="bx-im-textarea__element"
							ref="textarea"
							rows="1"
						></textarea>
					</div>
					<div class="bx-im-textarea__right">
						<div class="bx-im-textarea__action-panel">
							<CreateEntityMenu :dialogId="dialogId" :textareaValue="text" />
							<div 
								:title="loc('IM_TEXTAREA_ICON_APPLICATION')"
								@click="onMarketIconClick"
								class="bx-im-textarea__icon --market"
								:class="{'--active': marketMode}"
							></div>
							<SmileSelector :dialogId="dialogId" />
						</div>
					</div>
				</div>
			</div>
			<SendButton :editMode="editMode" :isDisabled="isDisabled" @click="sendMessage" />
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
				:bindElement="$refs.textarea"
				:dialogId="dialogId"
				:query="mentionQuery"
				@close="closeMentionPopup"
			/>
		</div>
	`,
};

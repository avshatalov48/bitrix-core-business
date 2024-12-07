import { Extension } from 'main.core';
import { EventEmitter } from 'main.core.events';

import { Button as MessengerButton, ButtonColor, ButtonSize } from 'im.v2.component.elements';
import { EventType, FileType } from 'im.v2.const';
import { DraftManager } from 'im.v2.lib.draft';
import { isNewLineCombination, isSendMessageCombination } from 'im.v2.lib.hotkey';
import { Textarea } from 'im.v2.lib.textarea';
import { UploadingService } from 'im.v2.provider.service';

import { ResizeDirection, ResizeManager } from '../../classes/resize-manager';
import { FileItem } from './file-item';

import '../../css/upload-preview/upload-preview-content.css';

import type { JsonObject } from 'main.core';
import type { ImModelFile } from 'im.v2.model';
import type { UploaderFile } from 'ui.uploader.core';

const BUTTONS_CONTAINER_HEIGHT = 74;
const TextareaHeight = {
	max: 208,
	min: 46,
};

// @vue/component
export const UploadPreviewContent = {
	name: 'UploadPreviewContent',
	components: { MessengerButton, FileItem },
	props: {
		dialogId: {
			type: String,
			required: true,
		},
		uploaderId: {
			type: String,
			required: true,
		},
		textareaValue: {
			type: String,
			required: false,
			default: '',
		},
	},
	emits: ['sendFiles', 'close', 'updateTitle'],
	data(): JsonObject
	{
		return {
			text: '',
			sendAsFile: false,
			files: [],
			textareaHeight: TextareaHeight.min,
			textareaResizedHeight: 0,
		};
	},
	computed:
	{
		ButtonSize: () => ButtonSize,
		ButtonColor: () => ButtonColor,
		filesFromStore(): ImModelFile[]
		{
			const filesFromStore = [];
			this.files.forEach((file) => {
				const fileFromStore = this.$store.getters['files/get'](file.getId());
				if (fileFromStore)
				{
					filesFromStore.push(fileFromStore);
				}
			});

			return filesFromStore;
		},
		filesCount(): number
		{
			return this.files.length;
		},
		isSingleFile(): boolean
		{
			return this.filesFromStore.length === 1;
		},
		inputMaxLength(): number
		{
			const settings = Extension.getSettings('im.v2.component.textarea');

			return settings.get('maxLength');
		},
		textareaHeightStyle(): number | string
		{
			return this.textareaHeight === 'auto' ? 'auto' : `${this.textareaHeight}px`;
		},
		title(): string
		{
			const onlyImages = this.filesFromStore.every((file) => {
				return file.type === FileType.image;
			});

			return onlyImages
				? this.$Bitrix.Loc.getMessage('IM_TEXTAREA_UPLOAD_PREVIEW_POPUP_TITLE')
				: this.$Bitrix.Loc.getMessage('IM_TEXTAREA_UPLOAD_PREVIEW_POPUP_TITLE_FILES');
		},
	},
	watch:
	{
		text()
		{
			void this.adjustTextareaHeight();
		},
		title()
		{
			this.$emit('updateTitle', this.title);
		},
		sendAsFile(newValue: boolean)
		{
			this.files.forEach((file: UploaderFile) => {
				file.setCustomData('sendAsFile', newValue);
			});
		},
	},
	created()
	{
		this.initResizeManager();
		this.files = this.getUploadingService().getFiles(this.uploaderId);
	},
	mounted()
	{
		this.text = this.textareaValue;
		this.insertText('');
		this.$refs.messageText.focus();
	},
	beforeUnmount()
	{
		this.insertText(this.text);
		DraftManager.getInstance().setDraftText(this.dialogId, this.text);
		this.resizeManager.destroy();
	},
	methods:
	{
		async adjustTextareaHeight()
		{
			this.textareaHeight = 'auto';
			await this.$nextTick();

			if (!this.$refs.messageText)
			{
				return;
			}

			const TEXTAREA_BORDERS_WIDTH = 2;
			const newMaxPoint = Math.min(TextareaHeight.max, this.$refs.messageText.scrollHeight + TEXTAREA_BORDERS_WIDTH);
			if (this.doesContentOverflowScreen(newMaxPoint))
			{
				const textareaTopPoint = this.$refs.messageText.getBoundingClientRect().top;
				const availableHeight = window.innerHeight - textareaTopPoint - BUTTONS_CONTAINER_HEIGHT;
				this.textareaHeight = Math.max(TextareaHeight.min, availableHeight);

				return;
			}

			if (this.resizedTextareaHeight)
			{
				this.textareaHeight = Math.max(newMaxPoint, this.resizedTextareaHeight);

				return;
			}

			this.textareaHeight = Math.max(newMaxPoint, TextareaHeight.min);
		},
		getUploadingService(): UploadingService
		{
			if (!this.uploadingService)
			{
				this.uploadingService = UploadingService.getInstance();
			}

			return this.uploadingService;
		},
		onCancel()
		{
			this.$emit('close', { text: this.text });
		},
		onSend()
		{
			if (this.sendAsFile)
			{
				this.files.forEach((file: UploaderFile) => {
					this.removePreview(file);
				});
			}

			this.$emit('sendFiles', {
				groupFiles: false,
				text: this.text,
				uploaderId: this.uploaderId,
				sendAsFile: this.sendAsFile,
			});

			this.text = '';
		},
		onKeyDownHandler(event: KeyboardEvent)
		{
			const sendMessageCombination = isSendMessageCombination(event);
			const newLineCombination = isNewLineCombination(event);
			if (sendMessageCombination && !newLineCombination)
			{
				event.preventDefault();
				this.onSend();

				return;
			}

			if (newLineCombination)
			{
				event.preventDefault();
				this.text = Textarea.addNewLine(this.$refs.messageText);
			}
		},
		removePreview(file: UploaderFile)
		{
			this.$store.dispatch('files/update', {
				id: file.getId(),
				fields: {
					urlPreview: '',
					image: false,
				},
			});
		},
		insertText(text: string)
		{
			EventEmitter.emit(EventType.textarea.insertText, {
				text,
				dialogId: this.dialogId,
				replace: true,
			});
		},
		loc(phraseCode: string, replacements: {[p: string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
		initResizeManager()
		{
			this.resizeManager = new ResizeManager({
				direction: ResizeDirection.down,
				minHeight: TextareaHeight.min,
				maxHeight: TextareaHeight.max,
			});
			this.resizeManager.subscribe(ResizeManager.events.onHeightChange, ({ data: { newHeight } }) => {
				this.textareaHeight = newHeight;
			});
			this.resizeManager.subscribe(ResizeManager.events.onResizeStop, () => {
				this.resizedTextareaHeight = this.textareaHeight;
			});
		},
		onResizeStart(event)
		{
			this.resizeManager.onResizeStart(event, this.textareaHeight);
		},
		doesContentOverflowScreen(newMaxPoint: number): boolean
		{
			const textareaTop = this.$refs.messageText.getBoundingClientRect().top;

			return textareaTop + newMaxPoint + BUTTONS_CONTAINER_HEIGHT > window.innerHeight;
		},
	},
	template: `
		<div class="bx-im-upload-preview__container">
			<div class="bx-im-upload-preview__upper-delimiter"></div>
			<div class="bx-im-upload-preview__items-container">
				<FileItem v-for="fileItem in filesFromStore" :file="fileItem" :class="{'--single': isSingleFile}" />
			</div>
			<div class="bx-im-upload-preview__bottom-delimiter"></div>
			<div class="bx-im-upload-preview__controls-container">
				<!--<label class="bx-im-upload-preview__control-compress-image">-->
				<!--<input type="checkbox" v-model="sendAsFile">-->
				<!--{{ $Bitrix.Loc.getMessage('IM_TEXTAREA_UPLOAD_PREVIEW_POPUP_SEND_AS_FILE') }}-->
				<!--</label>-->
				<textarea
					ref="messageText"
					v-model="text"
					:placeholder="loc('IM_TEXTAREA_UPLOAD_PREVIEW_POPUP_INPUT_PLACEHOLDER')"
					:maxlength="inputMaxLength"
					:style="{'height': textareaHeightStyle}"
					class="bx-im-upload-preview__message-text"
					rows="1"
					@keydown="onKeyDownHandler"
				></textarea>
				<div @mousedown="onResizeStart" class="bx-im-upload-preview__drag-handle"></div>
			</div>
			<div class="bx-im-upload-preview__controls-buttons">
				<MessengerButton
					:color="ButtonColor.Primary"
					:size="ButtonSize.L"
					:isRounded="true"
					:text="loc('IM_TEXTAREA_UPLOAD_PREVIEW_POPUP_BUTTON_SEND')"
					@click="onSend"
				/>
				<MessengerButton
					:color="ButtonColor.LightBorder"
					:size="ButtonSize.L"
					:isRounded="true"
					:text="loc('IM_TEXTAREA_UPLOAD_PREVIEW_POPUP_BUTTON_CANCEL')"
					@click="onCancel"
				/>
			</div>
		</div>
	`,
};

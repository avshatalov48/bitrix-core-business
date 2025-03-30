import { Extension } from 'main.core';
import { EventEmitter } from 'main.core.events';

import { EventType, FileType } from 'im.v2.const';
import { DraftManager } from 'im.v2.lib.draft';
import { isNewLineCombination, isSendMessageCombination } from 'im.v2.lib.hotkey';
import { Textarea } from 'im.v2.lib.textarea';
import { UploadingService } from 'im.v2.provider.service';

import { ResizeDirection, ResizeManager } from '../../classes/resize-manager';
import { MediaContent } from 'im.v2.component.message.file';
import { SendButton } from '../send-button';
import { FileItem } from './file-item';

import '../../css/upload-preview/upload-preview-content.css';

import type { JsonObject } from 'main.core';
import type { ImModelFile } from 'im.v2.model';
import type { UploaderFile } from 'ui.uploader.core';

const MAX_FILES_COUNT = 10;
const BUTTONS_CONTAINER_HEIGHT = 74;
const TextareaHeight = {
	max: 208,
	min: 46,
};

type FakeMessage = {
	id: string,
	files: Array<string>,
	text: string,
	attach: Array<any>,
	forward: { [keys: string]: any },
};

// @vue/component
export const UploadPreviewContent = {
	name: 'UploadPreviewContent',
	components: { MediaContent, FileItem, SendButton },
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
			uploaderFiles: [],
			textareaHeight: TextareaHeight.min,
			textareaResizedHeight: 0,
		};
	},
	computed:
	{
		files(): Array<ImModelFile>
		{
			return this.uploaderFiles.map((file: UploaderFile) => {
				return this.$store.getters['files/get'](file.getId());
			});
		},
		fileIds(): number | string
		{
			return this.files.map((file: ImModelFile) => {
				return file.id;
			});
		},
		fakeMessage(): FakeMessage
		{
			return {
				id: 'fake',
				files: this.fileIds,
				text: '',
				attach: [],
				forward: {},
			};
		},
		filesCount(): number
		{
			return this.files.length;
		},
		isSingleFile(): boolean
		{
			return this.files.length === 1;
		},
		sourceFilesCount(): number
		{
			return this.getUploadingService().getSourceFilesCount(this.uploaderId);
		},
		isOverMaxFilesLimit(): boolean
		{
			return this.sourceFilesCount > MAX_FILES_COUNT;
		},
		isMediaOnly(): boolean
		{
			return this.files.every((file: ImModelFile) => {
				return (file.type === FileType.image || file.type === FileType.video);
			});
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
			return this.$Bitrix.Loc.getMessage(
				'IM_TEXTAREA_UPLOAD_PREVIEW_POPUP_COMPUTED_TITLE',
				{ '#COUNT#': this.filesCount },
			);
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
			this.uploaderFiles.forEach((file: UploaderFile) => {
				file.setCustomData('sendAsFile', newValue);
			});
		},
	},
	created()
	{
		this.initResizeManager();
		this.getUploadingService().getFiles(this.uploaderId).forEach((file) => {
			this.uploaderFiles.push(file);
		});
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
			if (this.sendAsFile || !this.isMediaOnly)
			{
				this.uploaderFiles.forEach((file: UploaderFile) => {
					this.removePreviewParams(file);
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
		removePreviewParams(file: UploaderFile)
		{
			this.$store.dispatch('files/update', {
				id: file.getId(),
				fields: {
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
		onRemoveItem(event)
		{
			this.getUploadingService().removeFileFromUploader({
				uploaderId: this.uploaderId,
				filesIds: [event.file.id],
			});

			this.uploaderFiles = this.getUploadingService().getFiles(this.uploaderId);

			if (this.filesCount === 0)
			{
				this.$emit('close');
			}
		},
	},
	template: `
		<div class="bx-im-upload-preview__container">
			<div class="bx-im-upload-preview__items-container">
				<MediaContent 
					v-if="isMediaOnly && !sendAsFile" 
					:item="fakeMessage" 
					:previewMode="true" 
					:removable="true"
					@onRemoveItem="onRemoveItem"
				/>
				<FileItem 
					v-else 
					v-for="fileItem in files" 
					:file="fileItem" 
					:class="{'--single': isSingleFile}" 
					:removable="true"
					@onRemoveItem="onRemoveItem"
				/>
			</div>
			<div class="bx-im-upload-preview__controls-container">
				<div v-if="isOverMaxFilesLimit" class="ui-alert ui-alert-xs ui-alert-icon-warning bx-im-upload-preview__controls-files-limit-message">
					<span class="ui-alert-message">{{ loc('IM_TEXTAREA_UPLOAD_PREVIEW_POPUP_FILES_LIMIT_MESSAGE_10') }}</span>
				</div>
				<label v-if="isMediaOnly" class="bx-im-upload-preview__control-compress-image">
					<input type="checkbox" class="bx-im-upload-preview__control-compress-image-checkbox" v-model="sendAsFile">
					{{ loc('IM_TEXTAREA_UPLOAD_PREVIEW_POPUP_SEND_WITHOUT_COMPRESSION') }}
				</label>
				<div class="bx-im-upload-preview__control-form">
					<textarea
						ref="messageText"
						v-model="text"
						:placeholder="loc('IM_TEXTAREA_UPLOAD_PREVIEW_POPUP_INPUT_PLACEHOLDER_2')"
						:maxlength="inputMaxLength"
						:style="{'height': textareaHeightStyle}"
						class="bx-im-upload-preview__message-text"
						rows="1"
						@keydown="onKeyDownHandler"
					></textarea>
					<SendButton :dialogId="dialogId" @click="onSend" />
				</div>
				<div @mousedown="onResizeStart" class="bx-im-upload-preview__drag-handle"></div>
			</div>
		</div>
	`,
};

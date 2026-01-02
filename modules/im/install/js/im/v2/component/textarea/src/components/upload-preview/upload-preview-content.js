import { Extension, Type } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { PopupManager } from 'main.popup';

import { EventType, FileType } from 'im.v2.const';
import { DraftManager } from 'im.v2.lib.draft';
import { isNewLineCombination, isSendMessageCombination } from 'im.v2.lib.hotkey';
import { Textarea } from 'im.v2.lib.textarea';
import { UploadingService, MultiUploadingService } from 'im.v2.provider.service.uploading';
import { MediaGallery } from 'im.v2.component.elements.media-gallery';
import { SendButton } from 'im.v2.component.elements.send-button';

import { ResizeDirection, ResizeManager } from '../../classes/resize-manager';
import { FileItem } from './file-item';

import '../../css/upload-preview/upload-preview-content.css';

import type { JsonObject } from 'main.core';
import type { ImModelFile } from 'im.v2.model';
import type { UploaderFile } from 'ui.uploader.core';

const MAX_FILES_COUNT = 100;
const BUTTONS_CONTAINER_HEIGHT = 74;
const TEXT_LIMIT_COUNTER_SHOW_RANGE = 200;
const TextareaHeight = {
	max: 208,
	min: 46,
};

// @vue/component
export const UploadPreviewContent = {
	name: 'UploadPreviewContent',
	components: { FileItem, SendButton, MediaGallery },
	props: {
		dialogId: {
			type: String,
			required: true,
		},
		uploaderIds: {
			type: Array,
			required: true,
		},
		uploadingId: {
			type: String || null,
			default: null,
		},
		sourceFilesCount: {
			type: Number,
			required: true,
		},
		textareaValue: {
			type: String,
			required: false,
			default: '',
		},
		popupId: {
			type: String,
			required: true,
		},
		allowAdjustPosition: {
			type: Boolean,
			default: true,
		},
	},
	emits: ['sendFiles', 'close', 'updateTitle'],
	data(): JsonObject
	{
		return {
			text: '',
			sendAsFile: false,
			chunks: [],
			uploaderChunks: [],
			textareaHeight: TextareaHeight.min,
			textareaResizedHeight: 0,
			draggedItemEvent: null,
			lastTargetItem: null,
			lastTargetPosition: null,
			highlightDropzone: {},
			draggedFile: null,
			axis: 'x',
			insertPosition: null,
		};
	},
	computed:
	{
		files(): Array<ImModelFile>
		{
			return this.chunks.flat();
		},
		uploaderFiles(): Array<UploaderFile>
		{
			const allUploaderFiles = this.uploaderIds.flatMap((uploaderId: string) => {
				return this.getUploadingService().getFiles(uploaderId);
			});

			const fileIds: Array<string> = this.files.map((file: ImModelFile) => {
				return file.id;
			});

			return fileIds.map((fileId: string) => {
				return allUploaderFiles.find((file: UploaderFile) => {
					return file.getId() === fileId;
				});
			});
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
		allowedTextLimit(): number
		{
			return this.inputMaxLength - this.text.length;
		},
		showInputLengthCounter(): boolean
		{
			return this.allowedTextLimit <= TEXT_LIMIT_COUNTER_SHOW_RANGE;
		},
		textareaHeightStyle(): number | string
		{
			return this.textareaHeight === 'auto' ? 'auto' : `${this.textareaHeight}px`;
		},
		title(): string
		{
			const filesCount: number = Math.min(this.files.length, MAX_FILES_COUNT);

			return this.$Bitrix.Loc.getMessage(
				'IM_TEXTAREA_UPLOAD_PREVIEW_POPUP_COMPUTED_TITLE',
				{ '#COUNT#': filesCount },
			);
		},
	},
	watch:
	{
		async text()
		{
			await this.adjustTextareaHeight();
			if (this.allowAdjustPosition)
			{
				this.adjustPopupPosition();
			}
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

		this.uploaderIds.forEach((uploaderId) => {
			const files = [];
			this.getUploadingService().getFiles(uploaderId).forEach((file) => {
				files.push(this.$store.getters['files/get'](file.getId()));
			});

			this.chunks.push(files);
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
			const sendAsFile = this.sendAsFile || !this.isMediaOnly;
			if (sendAsFile)
			{
				this.uploaderFiles.forEach((file: UploaderFile) => {
					this.removePreviewParams(file);
					file.setTreatImageAsFile(true);
					file.setCustomData('sendAsFile', true);
				});
			}

			this.$emit('sendFiles', {
				text: this.text,
				uploaderIds: this.uploaderIds,
				files: this.uploaderFiles,
				sendAsFile,
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
		onRemoveItem(event: { file: ImModelFile })
		{
			const files: Array<ImModelFile> = this.chunks.flat().filter((file: ImModelFile) => {
				return file.id !== event.file.id;
			});

			this.chunks = MultiUploadingService.makeChunks({ files });

			if (this.chunks.length === 0)
			{
				this.onCancel();
			}
		},
		adjustPopupPosition()
		{
			PopupManager.getPopupById(this.popupId)?.adjustPosition({
				forceBindPosition: true,
				position: 'bottom',
			});
		},
		onItemDragStart(event)
		{
			this.draggedItemEvent = event;
			this.draggedFile = event.file;
			this.axis = event.axis || this.axis;
			// eslint-disable-next-line no-param-reassign
			event.event.dataTransfer.effectAllowed = 'move';
		},
		onItemDragEnd(event)
		{
			event.event.preventDefault();

			delete this.highlightDropzone.fileId;
			delete this.highlightDropzone.position;

			const files = this.chunks.flat();
			const currentFileIndex = files.indexOf(this.draggedFile);
			const targetIndex = (() => {
				const index = files.indexOf(this.lastTargetItem);
				if (this.lastTargetPosition === 'before')
				{
					return index - 1;
				}

				return index;
			})();

			files.splice(currentFileIndex, 1);
			files.splice(targetIndex + 1, 0, this.draggedFile);

			this.chunks = MultiUploadingService.makeChunks({ files });
			this.draggedFile = null;
		},
		onItemDragOver(event)
		{
			if (this.draggedFile)
			{
				event.event.preventDefault();

				const currentTarget = event.file;
				const currentTargetPosition = (() => {
					const targetRect = event.event.currentTarget.getBoundingClientRect();
					const targetCenter = (() => {
						if (this.axis === 'x')
						{
							return targetRect.left + (targetRect.width / 2);
						}

						return targetRect.top + (targetRect.height / 2);
					})();

					if (
						(this.axis === 'x' && event.event.x > targetCenter)
						|| (this.axis === 'y' && event.event.y > targetCenter)
					)
					{
						return 'after';
					}

					return 'before';
				})();

				if (
					currentTarget !== this.lastTargetItem
					|| (
						currentTarget === this.lastTargetItem
						&& currentTargetPosition !== this.lastTargetPosition
					)
				)
				{
					this.lastTargetPosition = currentTargetPosition;
					this.lastTargetItem = currentTarget;

					this.highlightDropzone = {
						fileId: currentTarget.id,
						position: currentTargetPosition,
					};
				}
			}
		},
		onDrop(event)
		{
			event.preventDefault();
		},
	},
	template: `
		<div class="bx-im-upload-preview__container" @drop="onDrop">
			<div class="bx-im-upload-preview__items-container">
				<div v-if="isMediaOnly && !sendAsFile" v-for="chunk in chunks" class="bx-im-upload-preview__items-chunk">
					<MediaGallery
						:files="chunk"
						:allowRemoveItem="true"
						:allowSorting="true"
						:viewerGroupBy="uploadingId"
						@removeItem="onRemoveItem"
						@itemDragStart="onItemDragStart"
						@itemDragEnd="onItemDragEnd"
						@itemDragOver="onItemDragOver"
						:highlightDropzone="highlightDropzone"
					/>
				</div>
				<div v-else v-for="chunk in chunks" class="bx-im-upload-preview__items-chunk">
					<FileItem
						v-for="fileItem in chunk"
						:file="fileItem"
						:removable="true"
						:allowSorting="true"
						:viewerGroupBy="uploadingId"
						@removeItem="onRemoveItem"
						@itemDragStart="onItemDragStart"
						@itemDragEnd="onItemDragEnd"
						@itemDragOver="onItemDragOver"
						:highlightDropzone="highlightDropzone"
					/>
				</div>
			</div>
			<div class="bx-im-upload-preview__controls-container">
				<div v-if="isOverMaxFilesLimit" class="bx-im-upload-preview__controls-files-limit-message">
					<span>{{ loc('IM_TEXTAREA_UPLOAD_PREVIEW_POPUP_FILES_LIMIT_MESSAGE_100') }}</span>
				</div>
				<label v-if="isMediaOnly" class="bx-im-upload-preview__control-compress-image">
					<input type="checkbox" class="bx-im-upload-preview__control-compress-image-checkbox" v-model="sendAsFile">
					{{ loc('IM_TEXTAREA_UPLOAD_PREVIEW_POPUP_SEND_WITHOUT_COMPRESSION') }}
				</label>
				<div class="bx-im-upload-preview__control-form">
					<div class="bx-im-upload-preview__message-text__wrapper">
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
						<div v-if="showInputLengthCounter" class="bx-im-upload-preview__message-text__counter">
							<span>{{ this.allowedTextLimit }}</span>
						</div>
						<div @mousedown="onResizeStart" class="bx-im-upload-preview__message-text__drag-handle"></div>
					</div>
					<SendButton :dialogId="dialogId" @click="onSend" />
				</div>
			</div>
		</div>
	`,
};

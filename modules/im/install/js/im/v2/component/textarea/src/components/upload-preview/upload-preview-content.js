import { Extension } from 'main.core';

import { UploadingService } from 'im.v2.provider.service';
import { Button as MessengerButton, ButtonSize, ButtonColor } from 'im.v2.component.elements';
import { isSendMessageCombination, isNewLineCombination } from 'im.v2.lib.hotkey';
import { Textarea } from 'im.v2.lib.textarea';

import { FileItem } from './file-item';

import '../../css/upload-preview/upload-preview-content.css';

import type { UploaderFile } from 'ui.uploader.core';
import type { ImModelFile } from 'im.v2.model';

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
	emits: ['sendFiles', 'close'],
	data(): { text: string }
	{
		return {
			text: '',
			sendAsFile: false,
			files: [],
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
	},
	watch:
	{
		sendAsFile(newValue: boolean)
		{
			this.files.forEach((file: UploaderFile) => {
				file.setCustomData('sendAsFile', newValue);
			});
		},
	},
	created()
	{
		this.text = this.textareaValue;
		this.files = this.getUploadingService().getFiles(this.uploaderId);
	},
	mounted()
	{
		this.$refs.messageText.focus();
	},
	methods:
	{
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
					type="text"
					v-model="text"
					@keydown="onKeyDownHandler"
					class="bx-im-upload-preview__message-text"
					rows="1"
					:placeholder="$Bitrix.Loc.getMessage('IM_TEXTAREA_UPLOAD_PREVIEW_POPUP_INPUT_PLACEHOLDER')"
					:maxlength="inputMaxLength"
				></textarea>
			</div>
			<div class="bx-im-upload-preview__controls-buttons">
				<MessengerButton
					:color="ButtonColor.Primary"
					:size="ButtonSize.L"
					:isRounded="true"
					:text="$Bitrix.Loc.getMessage('IM_TEXTAREA_UPLOAD_PREVIEW_POPUP_BUTTON_SEND')"
					@click="onSend"
				/>
				<MessengerButton
					:color="ButtonColor.LightBorder"
					:size="ButtonSize.L"
					:isRounded="true"
					:text="$Bitrix.Loc.getMessage('IM_TEXTAREA_UPLOAD_PREVIEW_POPUP_BUTTON_CANCEL')"
					@click="onCancel"
				/>
			</div>
		</div>
	`,
};

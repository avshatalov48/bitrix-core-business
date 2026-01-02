import { ImModelFile } from 'im.v2.model';
import { UploadingService } from 'im.v2.provider.service.uploading';
import { PopupManager } from 'main.popup';
import { MessengerPopup } from 'im.v2.component.elements.popup';
import { Loader, Spinner } from 'im.v2.component.elements.loader';
import { UploadPreviewContent } from './upload-preview-content';

import '../../css/upload-preview/upload-preview-popup.css';

import type { UploaderFile } from 'ui.uploader.core';
import type { PopupOptions } from 'main.popup';

const POPUP_ID = 'im-chat-upload-preview-popup';

// @vue/component
export const UploadPreviewPopup = {
	name: 'UploadPreviewPopup',
	components: { MessengerPopup, UploadPreviewContent, Loader, Spinner },
	props:
	{
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
	},
	emits: ['close', 'sendFiles'],
	data()
	{
		return {
			allowAdjustPosition: true,
		};
	},
	computed:
	{
		POPUP_ID: () => POPUP_ID,
		config(): PopupOptions
		{
			return {
				width: 400,
				targetContainer: document.body,
				fixed: true,
				draggable: { restrict: true },
				titleBar: ' ',
				offsetTop: 0,
				padding: 0,
				closeIcon: true,
				contentColor: 'transparent',
				contentPadding: 0,
				className: 'bx-im-upload-preview__scope',
				autoHide: true,
				closeByEsc: false,
				overlay: true,
			};
		},
		files(): Array<ImModelFile>
		{
			const uploadingService: UploadingService = this.getUploadingService();

			return this.uploaderIds.flatMap((uploaderId: string) => {
				return uploadingService.getFiles(uploaderId).map((file: UploaderFile) => {
					return this.$store.getters['files/get'](file.getId());
				});
			});
		},
		isReady(): boolean
		{
			return this.files.every((file: ImModelFile) => {
				return file.image !== null;
			});
		},
	},
	watch:
	{
		isReady()
		{
			if (this.isReady)
			{
				queueMicrotask(() => {
					PopupManager.getPopupById(POPUP_ID)?.adjustPosition({
						forceBindPosition: true,
						position: 'bottom',
					});
				});
			}
		},
	},
	methods:
	{
		onSendFiles(event)
		{
			this.$emit('sendFiles', event);
			this.$emit('close');
		},
		onUpdateTitle(title: string)
		{
			PopupManager.getPopupById(POPUP_ID)?.setTitleBar(title);
		},
		getUploadingService(): UploadingService
		{
			return UploadingService.getInstance();
		},
		onDragStart()
		{
			this.allowAdjustPosition = false;
		},
	},
	template: `
		<MessengerPopup
			:config="config"
			@close="$emit('close')"
			@popupDragStart="onDragStart"
			:id="POPUP_ID"
		>
			<UploadPreviewContent
				v-if="isReady"
				:dialogId="dialogId"
				:uploaderIds="uploaderIds"
				:uploadingId="uploadingId"
				:sourceFilesCount="sourceFilesCount"
				:textareaValue="textareaValue"
				:popupId="POPUP_ID"
				:allowAdjustPosition="allowAdjustPosition"
				@close="$emit('close')"
				@sendFiles="onSendFiles"
				@updateTitle="onUpdateTitle"
			/>
			<div v-else class="bx-im-upload-preview-popup-preparing">
				<Spinner></Spinner>
			</div>
		</MessengerPopup>
	`,
};

import { EventEmitter } from 'main.core.events';

import { MessengerPopup } from 'im.v2.component.elements';
import { EventType } from 'im.v2.const';

import { UploadPreviewContent } from './upload-preview-content';

import type { PopupOptions } from 'main.popup';

const POPUP_ID = 'im-chat-upload-preview-popup';

// @vue/component
export const UploadPreviewPopup = {
	name: 'UploadPreviewPopup',
	components: { MessengerPopup, UploadPreviewContent },
	props:
	{
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
	emits: ['close', 'sendFiles'],
	computed:
	{
		POPUP_ID: () => POPUP_ID,
		config(): PopupOptions
		{
			return {
				width: 400,
				targetContainer: document.body,
				fixed: true,
				draggable: true,
				offsetTop: 0,
				padding: 0,
				closeIcon: true,
				titleBar: this.$Bitrix.Loc.getMessage('IM_TEXTAREA_UPLOAD_PREVIEW_POPUP_TITLE'),
				contentColor: 'transparent',
				contentPadding: 0,
				className: 'bx-im-upload-preview__scope',
			};
		},
	},
	created()
	{
		this.initialText = this.textareaValue;
		EventEmitter.emit(EventType.textarea.insertText, {
			text: '',
			replace: true,
		});
	},
	methods:
	{
		onContentClose()
		{
			this.insertText(this.initialText);
			this.$emit('close');
		},
		onSendFiles(event)
		{
			this.$emit('sendFiles', event);
			this.$emit('close');
		},
		insertText(text: string)
		{
			EventEmitter.emit(EventType.textarea.insertText, {
				text,
				replace: true,
			});
		},
	},
	template: `
		<MessengerPopup
			:config="config"
			@close="$emit('close')"
			:id="POPUP_ID"
		>
			<UploadPreviewContent 
				:dialogId="dialogId" 
				:uploaderId="uploaderId"
				:textareaValue="textareaValue"
				@close="onContentClose"
				@sendFiles="onSendFiles"
			/>
		</MessengerPopup>
	`,
};

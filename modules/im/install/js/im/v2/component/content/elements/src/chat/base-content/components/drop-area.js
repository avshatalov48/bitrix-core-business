import { getFilesFromDataTransfer, hasDataTransferOnlyFiles } from 'ui.uploader.core';
import { Event, Type, type JsonObject } from 'main.core';
import { EventEmitter } from 'main.core.events';

import { EventType } from 'im.v2.const';
import { MultiUploadingService, type MultiUploadingResult } from 'im.v2.provider.service.uploading';

import { Height } from '../const/size';

import type { ImModelChat } from 'im.v2.model';

import '../css/drop-area.css';

// @vue/component
export const DropArea = {
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
		container: {
			type: Object,
			required: true,
		},
	},
	data(): JsonObject
	{
		return {
			showDropArea: false,
			lastDropAreaEnterTarget: null,
		};
	},
	computed:
	{
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		hasPinnedMessages(): boolean
		{
			return this.$store.getters['messages/pin/getPinned'](this.dialog.chatId).length > 0;
		},
		dropAreaStyles(): {[top: string]: string}
		{
			let offset = Height.dropAreaOffset + Height.chatHeader;
			if (this.hasPinnedMessages)
			{
				offset += Height.pinnedMessages;
			}

			return { top: `${offset}px` };
		},
	},
	watch:
	{
		container: {
			immediate: true,
			handler(newValue)
			{
				if (!Type.isElementNode(newValue))
				{
					return;
				}

				this.bindEvents();
			},
		},
	},
	beforeUnmount()
	{
		this.unbindEvents();
	},
	methods:
	{
		bindEvents()
		{
			Event.bind(this.container, 'dragenter', this.onDragEnter);
			Event.bind(this.container, 'dragleave', this.onDragLeave);
			Event.bind(this.container, 'dragover', this.onDragOver);
			Event.bind(this.container, 'drop', this.onDrop);
		},
		unbindEvents()
		{
			Event.unbind(this.container, 'dragenter', this.onDragEnter);
			Event.unbind(this.container, 'dragleave', this.onDragLeave);
			Event.unbind(this.container, 'dragover', this.onDragOver);
			Event.unbind(this.container, 'drop', this.onDrop);
		},
		async onDragEnter(event: DragEvent)
		{
			event.stopPropagation();
			event.preventDefault();

			const success = await hasDataTransferOnlyFiles(event.dataTransfer, false);
			if (!success)
			{
				return;
			}
			this.lastDropAreaEnterTarget = event.target;
			this.showDropArea = true;
		},
		onDragLeave(event: DragEvent)
		{
			event.stopPropagation();
			event.preventDefault();

			if (this.lastDropAreaEnterTarget !== event.target)
			{
				return;
			}

			this.showDropArea = false;
		},
		onDragOver(event: DragEvent)
		{
			event.preventDefault();
		},
		async onDrop(event: DragEvent)
		{
			event.preventDefault();

			const multiUploadingService: MultiUploadingService = this.getMultiUploadingService();
			const multiUploadingResult: MultiUploadingResult = await multiUploadingService.upload({
				files: await getFilesFromDataTransfer(event.dataTransfer),
				dialogId: this.dialogId,
				sendAsFile: false,
				autoUpload: false,
			});

			if (Type.isArrayFilled(multiUploadingResult.uploaderIds))
			{
				EventEmitter.emit(EventType.textarea.openUploadPreview, { multiUploadingResult });
			}
			this.showDropArea = false;
		},
		getMultiUploadingService(): MultiUploadingService
		{
			if (!this.multiUploadingService)
			{
				this.multiUploadingService = new MultiUploadingService();
			}

			return this.multiUploadingService;
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<Transition name="drop-area-fade">
			<div v-if="showDropArea" :style="dropAreaStyles" class="bx-im-content-chat-drop-area__container bx-im-content-chat-drop-area__scope">
				<div class="bx-im-content-chat-drop-area__box">
					<span class="bx-im-content-chat-drop-area__icon"></span>
					<label class="bx-im-content-chat-drop-area__label-text">
						{{ loc('IM_CONTENT_DROP_AREA') }}
					</label>
				</div>
			</div>
		</Transition>
	`,
};

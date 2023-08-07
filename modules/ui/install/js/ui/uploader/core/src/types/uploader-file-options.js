import type { BaseEvent } from 'main.core.events';
import type { FileEvent } from '../enums/file-event';
import type AbstractUploadController from '../backend/abstract-upload-controller';
import type AbstractLoadController from '../backend/abstract-load-controller';
import type AbstractRemoveController from '../backend/abstract-remove-controller';

export type UploaderFileOptions = {
	id?: string,
	serverFileId?: number | string,
	name?: string,
	type?: string,
	size?: number,
	width?: number,
	height?: number,
	treatImageAsFile?: boolean,
	downloadUrl?: string,
	clientPreview?: Blob,
	clientPreviewWidth?: number,
	clientPreviewHeight?: number,
	serverPreviewUrl?: string,
	serverPreviewWidth?: number,
	serverPreviewHeight?: number,
	preload?: boolean,
	uploadController?: AbstractUploadController,
	loadController?: AbstractLoadController,
	removeController?: AbstractRemoveController,
	customData?: { [key: string]: any },
	events?: { [eventName: $Values<FileEvent>]: (event: BaseEvent) => void },
};

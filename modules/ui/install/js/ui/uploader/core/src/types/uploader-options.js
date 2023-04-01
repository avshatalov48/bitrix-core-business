import type { BaseEvent } from 'main.core.events';
import type { FilterType } from '../enums/filter-type';
import type { ServerOptions } from './server-options';
import type { UploaderFileOptions } from './uploader-file-options';
import type Filter from '../filters/filter';
import type { ResizeImageMode, ResizeImageMimeType, ResizeImageMimeTypeMode } from '../types/resize-image-options';

export type UploaderOptions = {
	controller?: string,
	controllerOptions?: { [key: string]: string | number },
	browseElement?: HTMLElement | HTMLElement[],
	dropElement?: HTMLElement | HTMLElement[],
	pasteElement?: HTMLElement | HTMLElement[],
	hiddenFieldsContainer?: string | HTMLElement,
	hiddenFieldName?: string,
	assignAsFile?: boolean,
	autoUpload?: boolean,
	multiple?: boolean,
	allowReplaceSingle?: boolean,
	maxParallelUploads?: number,
	maxParallelLoads?: number,
	acceptOnlyImages?: boolean,
	acceptedFileTypes?: string | string[],
	maxFileSize?: number,
	minFileSize?: number,
	maxTotalFileSize?: number,
	maxFileCount?: number,

	imageMinWidth?: number,
	imageMinHeight?: number,
	imageMaxWidth?: number,
	imageMaxHeight?: number,
	imageMaxFileSize?: number,
	imageMinFileSize?: number,

	imageResizeWidth?: number,
	imageResizeHeight?: number,
	imageResizeMode?: ResizeImageMode,
	imageResizeMimeType?: ResizeImageMimeType,
	imageResizeMimeTypeMode?: ResizeImageMimeTypeMode,
	imageResizeQuality?: number,

	imagePreviewWidth?: number,
	imagePreviewHeight?: number,
	imagePreviewResizeMode?: ResizeImageMode,
	imagePreviewMimeType?: ResizeImageMimeType,
	imagePreviewMimeTypeMode?: ResizeImageMimeTypeMode,
	imagePreviewQuality?: number,
	imagePreviewUpscale?: boolean,

	ignoredFileNames?: string[],
	serverOptions: ServerOptions,
	filters?: Array<{ type: FilterType, filter: Filter | Function | string, options: { [key: string]: any } }>,
	files?: UploaderFileOptions[],
	events?: { [eventName: string]: (event: BaseEvent) => void },
};
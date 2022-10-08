import type { BaseEvent } from 'main.core.events';
import type { FilterType } from '../enums/filter-type';
import type { ServerOptions } from './server-options';
import type { UploaderFileOptions } from './uploader-file-options';
import type Filter from '../filters/filter';

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
	imageResizeMimeType?: 'image/jpeg' | 'image/png',
	imageResizeQuality?: number,
	imageResizeMethod?: 'contain' | 'cover' | 'force',

	imagePreviewWidth?: number,
	imagePreviewHeight?: number,
	imagePreviewQuality?: number,
	imagePreviewMimeType?: 'image/jpeg' | 'image/png',
	imagePreviewUpscale?: boolean,
	imagePreviewResizeMethod?: 'contain' | 'cover' | 'force',

	ignoredFileNames?: string[],
	serverOptions: ServerOptions,
	filters?: Array<{ type: FilterType, filter: Filter | Function | string, options: { [key: string]: any } }>,
	files?: UploaderFileOptions[],
	events?: { [eventName: string]: (event: BaseEvent) => void },
};
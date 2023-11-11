import type { BaseEvent } from 'main.core.events';
import type { FilterType } from '../enums/filter-type';
import type { ServerOptions } from './server-options';
import type { UploaderFileOptions } from './uploader-file-options';
import type Filter from '../filters/filter';
import type UploaderFile from '../uploader-file';
import type {
	ResizeImageMode,
	ResizeImageMimeType,
	ResizeImageMimeTypeMode,
	ResizeImageOptions,
} from '../types/resize-image-options';

export type UploaderOptions = {
	controller?: string,
	controllerOptions?: { [key: string]: string | number },
	id?: string,
	browseElement?: HTMLElement | HTMLElement[],
	dropElement?: HTMLElement | HTMLElement[],
	pasteElement?: HTMLElement | HTMLElement[],
	hiddenFieldsContainer?: string | HTMLElement,
	hiddenFieldName?: string,
	assignAsFile?: boolean,
	assignServerFile?: boolean,
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
	ignoreUnknownImageTypes?: boolean,
	treatOversizeImageAsFile?: boolean,

	imageResizeWidth?: number,
	imageResizeHeight?: number,
	imageResizeMode?: ResizeImageMode,
	imageResizeMimeType?: ResizeImageMimeType,
	imageResizeMimeTypeMode?: ResizeImageMimeTypeMode,
	imageResizeQuality?: number,
	imageResizeFilter?: (file: UploaderFile) => true | ResizeImageOptions,

	imagePreviewWidth?: number,
	imagePreviewHeight?: number,
	imagePreviewResizeMode?: ResizeImageMode,
	imagePreviewMimeType?: ResizeImageMimeType,
	imagePreviewMimeTypeMode?: ResizeImageMimeTypeMode,
	imagePreviewQuality?: number,
	imagePreviewUpscale?: boolean,
	imagePreviewFilter?: (file: UploaderFile) => true | ResizeImageOptions,

	ignoredFileNames?: string[],
	serverOptions: ServerOptions,
	filters?: Array<{ type: FilterType, filter: Filter | Class<Filter> | string, options: { [key: string]: any } }>,
	files?: UploaderFileOptions[],
	events?: { [eventName: string]: (event: BaseEvent) => void },
};

export type ResizeImageMode = 'contain' | 'crop' | 'force';
export type ResizeImageMimeType = 'image/jpeg' | 'image/png' | 'image/webp';
export type ResizeImageMimeTypeMode = 'auto' | 'force';

export type ResizeImageOptions = {
	mode?: ResizeImageMode,
	upscale?: boolean,
	width?: number,
	height?: number,
	quality?: number,
	mimeType?: ResizeImageMimeType,
	mimeTypeMode: ResizeImageMimeTypeMode,
};
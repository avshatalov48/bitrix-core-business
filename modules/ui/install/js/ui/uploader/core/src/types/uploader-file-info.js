import type { FileStatus } from '../enums/file-status';
import type { FileOrigin } from '../enums/file-origin';
import UploaderError from '../uploader-error';

export type UploaderFileInfo = {
	id: string,
	serverFileId: number | string | null,
	status: FileStatus,
	name: string,
	size: number,
	sizeFormatted: string,
	type: string,
	extension: string,
	origin: FileOrigin,
	isImage: boolean,
	isVideo: boolean,
	failed: boolean,
	width: ?number,
	height: ?number,
	progress: number,

	error: ?UploaderError,
	errors: UploaderError[],

	previewUrl: ?string,
	previewWidth: ?number,
	previewHeight: ?number,
	clientPreviewUrl: ?string,
	clientPreviewWidth: ?number,
	clientPreviewHeight: ?number,
	serverPreviewUrl: ?string,
	serverPreviewWidth: ?number,
	serverPreviewHeight: ?number,
	downloadUrl: ?string,
	customData: Object<string, any>,
};

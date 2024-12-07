/**
 * @namespace BX.UI.Uploader
 */
export type FileStatusType = {
	INIT: string,
	ADDED: string,
	LOADING: string,
	PENDING: string,
	PREPARING: string,
	UPLOADING: string,
	COMPLETE: string,
	LOAD_FAILED: string,
	UPLOAD_FAILED: string,
};

/**
 * @namespace BX.UI.Uploader
 */
export const FileStatus: FileStatusType = {
	INIT: 'init',
	ADDED: 'added',
	LOADING: 'loading',
	PENDING: 'pending',
	PREPARING: 'preparing',
	UPLOADING: 'uploading',
	COMPLETE: 'complete',
	// REMOVING: 'removing',
	// REMOVE_FAILED: 'remove-failed',
	LOAD_FAILED: 'load-failed',
	UPLOAD_FAILED: 'upload-failed',
};

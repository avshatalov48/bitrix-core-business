export type UploaderTask = {
	taskId: string,
	progress: number,
	chunkSize: number,
	fileData: File,
	fileName?: string,
	diskFolderId: number,
	generateUniqueName?: boolean,
	listener?: Function,
	status?: number,
	previewBlob?: Blob,
};
export type UploaderTask = {
	taskId: string,
	chunkSize: number,
	fileData: File,
	fileName?: string,
	diskFolderId: number,
	generateUniqueName?: boolean,
	listener?: Function,
	status?: number,
	previewBlob?: Blob
};
export type UploaderResultTask = {
	taskId: string,
	diskFolderId: number,
	fileData: File,
	fileName: string,
	progress: number,
	readOffset: number,
	status: number,
	token: string,
	uploadResult: Object,
};
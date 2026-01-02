export type FileFromDisk = {
	id: string;
	name: string;
	type: string;
	size: string;
	sizeInt: string;
	modifyBy: string;
	modifyDate: string;
	modifyDateInt: number;
	ext: string;
	fileType: string;
};

export type MessageWithFile = {
	tempMessageId?: string,
	tempFileId: string,
	file: FileFromDisk | File,
	dialogId: string,
	chatId?: number,
	uploaderId: string,
	sendAsFile: boolean,
}

export type FileCommitParams = {
	temporaryFileId: string,
	tempMessageId: string,
	chatId: number,
	realFileId: number,
	fromDisk: boolean,
	sendAsFile: boolean,
	messageText: string
}

export type UploadFilesParams = {
	files: File[],
	dialogId: string,
	autoUpload: boolean,
	sendAsFile: boolean,
	maxParallelLoads: number,
	maxParallelUploads: number,
}

export type UploadParams = {
	files: Array<File | Blob>,
	dialogId: string,
	autoUpload: boolean,
	sendAsFile: boolean,
}

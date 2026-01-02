type ViewerAttributes = {
	actions: string,
	objectId: string,
	src: string,
	title: string,
	viewer: boolean,
	viewerGroupBy: string,
	viewerType: string,
	viewerPreview?: string,
	viewerTypeClass?: string,
	viewerSeparateItem?: boolean,
	viewerExtension?: string,
	imChatId?: number
};

export type File = {
	id: number | string, // 'temporary2342'
	chatId: number,
	authorId: number,
	authorName: string,
	date: Date,
	type: string,
	extension: string,
	icon: string,
	isTranscribable: boolean,
	name: string,
	size: number,
	image: null | boolean | {width: number, height: number},
	status: string,
	progress: number,
	urlPreview: string,
	urlDownload: string,
	urlShow: string,
	viewerAttrs: ?ViewerAttributes
};

export type Transcription = {
	fileId: number,
	status: 'Success' | 'Pending' | 'Error',
	transcriptText: string | null,
}

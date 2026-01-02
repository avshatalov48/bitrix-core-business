import { type BaseEvent } from 'main.core.events';

export type UploaderWrapperOptions = {
	uploaderId: string,
	events: { [key: string]: (event: BaseEvent) => void },
	uploaderOptions: {
		autoUpload: boolean,
		maxParallelLoads: number,
		maxParallelUploads: number,
		controllerOptions: {
			folderId: number,
			chat: {
				chatId: number,
				dialogId: string,
			},
		},
	},
	customData?: { [key: string]: any },
};

export type UploaderWrapperFileOptions = {
	file: File | Blob,
	options: {
		id: string,
		customData?: { [key: string]: any },
	},
};

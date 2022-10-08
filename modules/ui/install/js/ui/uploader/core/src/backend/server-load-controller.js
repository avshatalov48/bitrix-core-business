import AbstractLoadController from './abstract-load-controller';
import type Server from './server';
import type UploaderFile from '../uploader-file';
import { loadMultiple, abort } from './server-load-multiple';

export default class ServerLoadController extends AbstractLoadController
{
	constructor(server: Server)
	{
		super(server);
	}

	load(file: UploaderFile): void
	{
		if (this.getServer().getController())
		{
			loadMultiple(this, file);
		}
		else
		{
			this.emit('onProgress', { file, progress: 100 });
			this.emit('onLoad', { fileInfo: file });
		}
		// const controllerOptions = this.getServer().getControllerOptions();
		// Ajax.runAction('ui.fileuploader.load', {
		// 		data: {
		// 			fileIds: [file.getServerId()],
		// 		},
		// 		getParameters: {
		// 			controller: this.getServer().getController(),
		// 			controllerOptions: controllerOptions ? JSON.stringify(controllerOptions) : null,
		// 		},
		// 		onrequeststart: (xhr) => {
		// 			this.xhr = xhr;
		// 		},
		// 		onprogress: (event: ProgressEvent) => {
		// 			if (event.lengthComputable)
		// 			{
		// 				const progress = event.total > 0 ? Math.floor(event.loaded / event.total * 100): 100;
		// 				this.emit('onProgress', { progress });
		// 			}
		// 		}
		// 	})
		// 	.then(response => {
		// 		if (response.data?.files)
		// 		{
		// 			this.emit('onProgress', { file, progress: 100 });
		// 			this.emit('onLoad', { file: response.data.file })
		// 		}
		// 		else
		// 		{
		// 			this.emit('onError', { error: new UploaderError('SERVER_ERROR') });
		// 		}
		// 	})
		// 	.catch(response => {
		// 		this.emit('onError', { error: UploaderError.createFromAjaxErrors(response.errors) });
		// 	})
		// ;
	}

	abort(): void
	{
		if (this.getServer().getController())
		{
			abort(this);
		}
	}
}
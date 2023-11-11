import { ajax as Ajax, Runtime } from 'main.core';

import UploaderError from '../uploader-error';

import type UploaderFile from '../uploader-file';
import type Server from './server';
import type RemoveController from './remove-controller';
import type ServerLoadController from './server-load-controller';

type Queue = {
	tasks: Array<{ controller: ServerLoadController, file: UploaderFile }>,
	remove: Function,
	xhr: XMLHttpRequest,
}

const queues: WeakMap<Server, Queue> = new WeakMap();

export function removeMultiple(controller: RemoveController, file: UploaderFile)
{
	const server = controller.getServer();
	let queue = queues.get(server);
	if (!queue)
	{
		queue = {
			tasks: [],
			remove: Runtime.debounce(removeInternal, 1000, server),
			xhr: null,
		};

		queues.set(server, queue);
	}

	queue.tasks.push({ controller, file });
	queue.remove();
}

function removeInternal()
{
	// eslint-disable-next-line no-invalid-this,unicorn/no-this-assignment
	const server: Server = this;
	const queue = queues.get(server);
	if (!queue)
	{
		return;
	}

	const { tasks } = queue;
	queues.delete(server);

	const fileIds = [];
	tasks.forEach((task) => {
		const file: UploaderFile = task.file;
		if (file.getServerFileId() !== null)
		{
			fileIds.push(file.getServerFileId());
		}
	});

	if (fileIds.length === 0)
	{
		return;
	}

	const controllerOptions = server.getControllerOptions();
	Ajax.runAction('ui.fileuploader.remove', {
		data: {
			fileIds,
		},
		getParameters: {
			controller: server.getController(),
			controllerOptions: controllerOptions ? JSON.stringify(controllerOptions) : null,
		},
		onrequeststart: (xhr) => {
			queue.xhr = xhr;
		},
	}).then((response) => {
		if (response.data?.files)
		{
			const fileResults = {};
			response.data.files.forEach((fileResult) => {
				fileResults[fileResult.id] = fileResult;
			});

			tasks.forEach((task) => {
				const { controller, file } = task;
				const fileResult = fileResults[file.getServerFileId()] || null;

				if (fileResult && fileResult.success)
				{
					controller.emit('onRemove', { fileId: fileResult.id });
				}
				else
				{
					const error = UploaderError.createFromAjaxErrors(fileResult?.errors);
					controller.emit('onError', { error });
				}
			});
		}
		else
		{
			const error = new UploaderError('SERVER_ERROR');
			tasks.forEach((task) => {
				const { controller } = task;
				controller.emit('onError', { error: error.clone() });
			});
		}
	}).catch((response) => {
		const error = UploaderError.createFromAjaxErrors(response.errors);
		tasks.forEach((task) => {
			const { controller } = task;
			controller.emit('onError', { error: error.clone() });
		});
	});
}

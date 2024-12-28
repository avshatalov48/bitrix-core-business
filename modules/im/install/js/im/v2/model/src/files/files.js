import { Type, type JsonObject } from 'main.core';
import { BuilderModel } from 'ui.vue3.vuex';
import { Logger } from 'im.v2.lib.logger';
import { Core } from 'im.v2.application.core';
import { FileStatus } from 'im.v2.const';
import { Utils } from 'im.v2.lib.utils';

import type { ImModelFile } from 'im.v2.model';

type FilesState = {
	collection: {
		[fileId: string]: ImModelFile
	},
	temporaryFilesMap: Map<number, string>,
};

export class FilesModel extends BuilderModel
{
	getName(): string
	{
		return 'files';
	}

	getState(): FilesState
	{
		return {
			collection: {},
			temporaryFilesMap: new Map(),
		};
	}

	getElementState(): ImModelFile
	{
		return {
			id: 0,
			chatId: 0,
			name: 'File is deleted',
			date: new Date(),
			type: 'file',
			extension: '',
			icon: 'empty',
			size: 0,
			image: false,
			status: FileStatus.done,
			progress: 100,
			authorId: 0,
			authorName: '',
			urlPreview: '',
			urlShow: '',
			urlDownload: '',
			viewerAttrs: null,
		};
	}

	getGetters(): JsonObject
	{
		return {
			/** @function files/get */
			get: (state: FilesState, getters) => (fileId: number, getTemporary = false): ?ImModelFile => {
				if (!fileId)
				{
					return null;
				}

				if (!getTemporary && !state.collection[fileId])
				{
					return null;
				}

				const file: ?ImModelFile = state.collection[fileId];
				const hasMappedTemporaryFile: boolean = getters.hasMappedTemporaryFile({ serverFileId: fileId });
				if (file && hasMappedTemporaryFile)
				{
					const temporaryFile: ImModelFile = getters.getMappedTemporaryFile({ serverFileId: fileId });

					return {
						...file,
						urlPreview: temporaryFile.urlPreview,
						urlShow: temporaryFile.urlShow,
					};
				}

				return file;
			},
			/** @function files/isInCollection */
			isInCollection: (state: FilesState) => (payload: {fileId: number | string}): boolean => {
				const { fileId } = payload;

				return Boolean(state.collection[fileId]);
			},
			/** @function files/hasMappedTemporaryFile */
			hasMappedTemporaryFile: (state: FilesState) => (payload: {serverFileId: number | string}): boolean => {
				if (state.temporaryFilesMap.has(payload.serverFileId))
				{
					const temporaryFileId: string = state.temporaryFilesMap.get(payload.serverFileId);

					return Object.hasOwn(state.collection, temporaryFileId);
				}

				return false;
			},
			/** @function files/getMappedTemporaryFile */
			getMappedTemporaryFile: (state: FilesState) => (payload: {serverFileId: number}): ?ImModelFile => {
				const { serverFileId } = payload;

				if (state.temporaryFilesMap.has(serverFileId))
				{
					const temporaryFileId: string = state.temporaryFilesMap.get(serverFileId);

					return state.collection[temporaryFileId];
				}

				return null;
			},
		};
	}

	getActions(): JsonObject
	{
		return {
			/** @function files/add */
			add: (store, payload: Object) => {
				const preparedFile = { ...this.getElementState(), ...this.validate(payload) };

				store.commit('add', { files: [preparedFile] });
			},
			/** @function files/set */
			set: (store, ...payload: Array<JsonObject>) => {
				const files: Array<ImModelFile> = payload.flat().map((file) => {
					return { ...this.getElementState(), ...this.validate(file) };
				});

				store.commit('add', { files });
			},
			/** @function files/update */
			update: (store, payload) => {
				const { id, fields } = payload;
				const existingItem = store.state.collection[id];
				if (!existingItem)
				{
					return false;
				}

				store.commit('update', {
					id,
					fields: this.validate(fields),
				});

				return true;
			},
			/** @function files/updateWithId */
			updateWithId: (store, payload: {id: string | number, fields: Object}) => {
				const { id, fields } = payload;
				if (!store.state.collection[id])
				{
					return;
				}

				store.commit('updateWithId', {
					id,
					fields: this.validate(fields),
				});
			},
			/** @function files/delete */
			delete: (store, payload: {id: string | number}) => {
				const { id } = payload;
				if (!store.state.collection[id])
				{
					return;
				}

				store.commit('delete', { id });
			},
			/** @function files/setTemporaryFileMapping */
			setTemporaryFileMapping: (store, payload: {serverFileId: number, temporaryFileId: string}) => {
				store.commit('setTemporaryFileMapping', payload);
			},
		};
	}

	/* eslint-disable no-param-reassign */
	getMutations(): JsonObject
	{
		return {
			add: (state: FilesState, payload: {files: ImModelFile[]}) => {
				payload.files.forEach((file) => {
					state.collection[file.id] = file;
				});
			},
			update: (state: FilesState, payload) => {
				Object.entries(payload.fields).forEach(([key, value]) => {
					state.collection[payload.id][key] = value;
				});
			},
			updateWithId: (state: FilesState, payload: {id: number | string, fields: Object}) => {
				const { id, fields } = payload;
				const currentFile = { ...state.collection[id] };

				delete state.collection[id];
				state.collection[fields.id] = { ...currentFile, ...fields };
			},
			delete: (state: FilesState, payload: {id: number | string}) => {
				Logger.warn('Files model: delete mutation', payload);
				const { id } = payload;
				delete state.collection[id];
			},
			setTemporaryFileMapping: (state: FilesState, payload: {serverFileId: number, temporaryFileId: string}) => {
				state.temporaryFilesMap.set(payload.serverFileId, payload.temporaryFileId);
			},
		};
	}

	// eslint-disable-next-line max-lines-per-function,sonarjs/cognitive-complexity
	validate(file: Object, options = {}): ImModelFile
	{
		const result = {};

		if (Type.isNumber(file.id) || Type.isStringFilled(file.id))
		{
			result.id = file.id;
		}

		if (Type.isNumber(file.chatId) || Type.isString(file.chatId))
		{
			result.chatId = Number.parseInt(file.chatId, 10);
		}

		if (!Type.isUndefined(file.date))
		{
			result.date = Utils.date.cast(file.date);
		}

		if (Type.isString(file.type))
		{
			result.type = file.type;
		}

		if (Type.isString(file.extension))
		{
			result.extension = file.extension.toString();

			if (result.type === 'image')
			{
				result.icon = 'img';
			}
			else if (result.type === 'video')
			{
				result.icon = 'mov';
			}
			else
			{
				result.icon = Utils.file.getIconTypeByExtension(result.extension);
			}
		}

		if (Type.isString(file.name) || Type.isNumber(file.name))
		{
			result.name = file.name.toString();
		}

		if (Type.isNumber(file.size) || Type.isString(file.size))
		{
			result.size = Number.parseInt(file.size, 10);
		}

		if (Type.isBoolean(file.image))
		{
			result.image = false;
		}
		else if (Type.isPlainObject(file.image))
		{
			result.image = {
				width: 0,
				height: 0,
			};

			if (Type.isString(file.image.width) || Type.isNumber(file.image.width))
			{
				result.image.width = Number.parseInt(file.image.width, 10);
			}

			if (Type.isString(file.image.height) || Type.isNumber(file.image.height))
			{
				result.image.height = Number.parseInt(file.image.height, 10);
			}

			if (result.image.width <= 0 || result.image.height <= 0)
			{
				result.image = false;
			}
		}

		if (Type.isString(file.status) && !Type.isUndefined(FileStatus[file.status]))
		{
			result.status = file.status;
		}

		if (Type.isNumber(file.progress) || Type.isString(file.progress))
		{
			result.progress = Number.parseInt(file.progress, 10);
		}

		if (Type.isNumber(file.authorId) || Type.isString(file.authorId))
		{
			result.authorId = Number.parseInt(file.authorId, 10);
		}

		if (Type.isString(file.authorName) || Type.isNumber(file.authorName))
		{
			result.authorName = file.authorName.toString();
		}

		if (Type.isString(file.urlPreview))
		{
			if (
				!file.urlPreview
				|| file.urlPreview.startsWith('http')
				|| file.urlPreview.startsWith('bx')
				|| file.urlPreview.startsWith('file')
				|| file.urlPreview.startsWith('blob')
			)
			{
				result.urlPreview = file.urlPreview;
			}
			else
			{
				result.urlPreview = Core.getHost() + file.urlPreview;
			}
		}

		if (Type.isString(file.urlDownload))
		{
			if (
				!file.urlDownload
				|| file.urlDownload.startsWith('http')
				|| file.urlDownload.startsWith('bx')
				|| file.urlPreview.startsWith('file')
			)
			{
				result.urlDownload = file.urlDownload;
			}
			else
			{
				result.urlDownload = Core.getHost() + file.urlDownload;
			}
		}

		if (Type.isString(file.urlShow))
		{
			if (
				!file.urlShow
				|| file.urlShow.startsWith('http')
				|| file.urlShow.startsWith('bx')
				|| file.urlShow.startsWith('file')
				|| file.urlShow.startsWith('blob')
			)
			{
				result.urlShow = file.urlShow;
			}
			else
			{
				result.urlShow = Core.getHost() + file.urlShow;
			}
		}

		if (Type.isPlainObject(file.viewerAttrs))
		{
			result.viewerAttrs = this.validateViewerAttributes(file.viewerAttrs);
		}

		return result;
	}

	validateViewerAttributes(viewerAttrs): ImModelFile['ViewerAttrs']
	{
		const result = {
			viewer: true,
		};

		if (Type.isString(viewerAttrs.actions))
		{
			result.actions = viewerAttrs.actions;
		}

		if (Type.isString(viewerAttrs.objectId))
		{
			result.objectId = viewerAttrs.objectId;
		}

		if (Type.isString(viewerAttrs.src))
		{
			result.src = viewerAttrs.src;
		}

		if (Type.isString(viewerAttrs.title))
		{
			result.title = viewerAttrs.title;
		}

		if (Type.isString(viewerAttrs.viewerGroupBy))
		{
			result.viewerGroupBy = viewerAttrs.viewerGroupBy;
		}

		if (Type.isString(viewerAttrs.viewerType))
		{
			result.viewerType = viewerAttrs.viewerType;
		}

		if (Type.isString(viewerAttrs.viewerPreview))
		{
			result.viewerPreview = viewerAttrs.viewerPreview;
		}

		if (Type.isString(viewerAttrs.viewerTypeClass))
		{
			result.viewerTypeClass = viewerAttrs.viewerTypeClass;
		}

		if (Type.isBoolean(viewerAttrs.viewerSeparateItem))
		{
			result.viewerSeparateItem = viewerAttrs.viewerSeparateItem;
		}

		if (Type.isString(viewerAttrs.viewerExtension))
		{
			result.viewerExtension = viewerAttrs.viewerExtension;
		}

		if (Type.isNumber(viewerAttrs.imChatId))
		{
			result.imChatId = viewerAttrs.imChatId;
		}

		return result;
	}
}

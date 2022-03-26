/**
 * Bitrix Messenger
 * Files model (Vuex Builder model)
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2020 Bitrix
 */


import {Vue} from 'ui.vue';
import {VuexBuilderModel} from 'ui.vue.vuex';
import {FileStatus, FileType, MutationType} from 'im.const';
import {Utils} from "im.lib.utils";

export class FilesModel extends VuexBuilderModel
{
	static maxDiskFileSize = 5242880;

	getName()
	{
		return 'files';
	}

	getState()
	{
		return {
			created: 0,
			host: this.getVariable('host', location.protocol+'//'+location.host),
			collection: {},
			index: {},
		}
	}

	getElementState(params = {})
	{
		let {
			id = 0,
			chatId = 0,
			name = this.getVariable('default.name', ''),
		} = params;

		return {
			id,
			chatId,
			name,
			templateId: id,
			date: new Date(),
			type: 'file',
			extension: "",
			icon: "empty",
			size: 0,
			image: false,
			status: FileStatus.done,
			progress: 100,
			authorId: 0,
			authorName: "",
			urlPreview: "",
			urlShow: "",
			urlDownload: "",
			init: false,
			viewerAttrs: {}
		};
	}

	getGetters()
	{
		return {
			get: state => (chatId, fileId, getTemporary = false) =>
			{
				if (!chatId || !fileId)
				{
					return null;
				}

				if (!state.index[chatId] || !state.index[chatId][fileId])
				{
					return null;
				}

				if (!getTemporary && !state.index[chatId][fileId].init)
				{
					return null;
				}

				return state.index[chatId][fileId];
			},
			getList: state => chatId =>
			{
				if (!state.index[chatId])
				{
					return null;
				}

				return state.index[chatId];
			},
			getBlank: state => params =>
			{
				return this.getElementState(params);
			}
		}
	}

	getActions()
	{
		return {
			add: (store, payload) =>
			{
				let result = this.validate(Object.assign({}, payload), {host: store.state.host});
				if (payload.id)
				{
					result.id = payload.id;
				}
				else
				{
					result.id = 'temporary' + (new Date).getTime() + store.state.created;
				}
				result.templateId = result.id;
				result.init = true;

				store.commit('add', Object.assign({}, this.getElementState(), result));

				return result.id;
			},
			set: (store, payload) =>
			{
				if (payload instanceof Array)
				{
					payload = payload.map(file => {
						let result = this.validate(Object.assign({}, file), {host: store.state.host});
						result.templateId = result.id;
						return Object.assign({}, this.getElementState(), result, {init: true});
					});
				}
				else
				{
					let result = this.validate(Object.assign({}, payload), {host: store.state.host});
					result.templateId = result.id;
					payload = [];
					payload.push(
						Object.assign({}, this.getElementState(), result, {init: true})
					);
				}

				store.commit('set', {
					insertType : MutationType.setAfter,
					data : payload
				});
			},
			setBefore: (store, payload) =>
			{
				if (payload instanceof Array)
				{
					payload = payload.map(file => {
						let result = this.validate(Object.assign({}, file), {host: store.state.host});
						result.templateId = result.id;
						return Object.assign({}, this.getElementState(), result, {init: true});
					});
				}
				else
				{
					let result = this.validate(Object.assign({}, payload), {host: store.state.host});
					result.templateId = result.id;
					payload = [];
					payload.push(
						Object.assign({}, this.getElementState(), result, {init: true})
					);
				}

				store.commit('set', {
					actionName: 'setBefore',
					insertType : MutationType.setBefore,
					data : payload
				});
			},
			update: (store, payload) =>
			{
				let result = this.validate(Object.assign({}, payload.fields), {host: store.state.host});

				store.commit('initCollection', {chatId: payload.chatId});

				let index = store.state.collection[payload.chatId].findIndex(el => el.id === payload.id);
				if (index < 0)
				{
					return false;
				}

				store.commit('update', {
					id : payload.id,
					chatId : payload.chatId,
					index : index,
					fields : result
				});

				if (payload.fields.blink)
				{
					setTimeout(() => {
						store.commit('update', {
							id : payload.id ,
							chatId : payload.chatId,
							fields : {blink: false}
						});
					}, 1000);
				}

				return true;
			},
			delete: (store, payload) =>
			{
				store.commit('delete', {
					id : payload.id,
					chatId : payload.chatId
				});
				return true;
			},
			saveState: (store, payload) =>
			{
				store.commit('saveState', {});
				return true;
			},
		}
	}

	getMutations()
	{
		return {
			initCollection: (state, payload) =>
			{
				this.initCollection(state, payload);
			},
			add: (state, payload) =>
			{
				this.initCollection(state, payload);

				state.collection[payload.chatId].push(payload);
				state.index[payload.chatId][payload.id] = payload;

				state.created += 1;

				this.saveState(state);
			},
			set: (state, payload) =>
			{
				for (let element of payload.data)
				{
					this.initCollection(state, {chatId: element.chatId});

					let index = state.collection[element.chatId].findIndex(el => el.id === element.id);
					if (index > -1)
					{
						delete element.templateId;
						state.collection[element.chatId][index] = Object.assign(state.collection[element.chatId][index], element);
					}
					else if (payload.insertType === MutationType.setBefore)
					{
						state.collection[element.chatId].unshift(element);
					}
					else
					{
						state.collection[element.chatId].push(element);
					}

					state.index[element.chatId][element.id] = element;

					this.saveState(state);
				}
			},
			update: (state, payload) =>
			{
				this.initCollection(state, payload);

				let index = -1;
				if (typeof payload.index !== 'undefined' && state.collection[payload.chatId][payload.index])
				{
					index = payload.index;
				}
				else
				{
					index = state.collection[payload.chatId].findIndex(el => el.id === payload.id);
				}

				if (index >= 0)
				{
					delete payload.fields.templateId;
					let element = Object.assign(
						state.collection[payload.chatId][index],
						payload.fields
					);
					state.collection[payload.chatId][index] = element;
					state.index[payload.chatId][element.id] = element;

					this.saveState(state);
				}
			},
			delete: (state, payload) =>
			{
				this.initCollection(state, payload);

				state.collection[payload.chatId] = state.collection[payload.chatId].filter(element => element.id !== payload.id);
				delete state.index[payload.chatId][payload.id];

				this.saveState(state);
			},
			saveState: (state, payload) =>
			{
				this.saveState(state);
			},
		}
	}

	initCollection(state, payload)
	{
		if (typeof state.collection[payload.chatId] !== 'undefined')
		{
			return true;
		}

		Vue.set(state.collection, payload.chatId, []);
		Vue.set(state.index, payload.chatId, {});

		return true;
	}

	getLoadedState(state)
	{
		if (!state || typeof state !== 'object')
		{
			return state;
		}

		if (typeof state.collection !== 'object')
		{
			return state;
		}

		state.index = {};

		for (let chatId in state.collection)
		{
			if (!state.collection.hasOwnProperty(chatId))
			{
				continue;
			}

			state.index[chatId] = {};

			state.collection[chatId]
				.filter(file => file != null)
				.forEach(file => {
					state.index[chatId][file.id] = file;
			});
		}

		return state;
	}

	getSaveFileList()
	{
		if (!this.db)
		{
			return [];
		}

		if (!this.store.getters['messages/getSaveFileList'])
		{
			return [];
		}

		let list = this.store.getters['messages/getSaveFileList']();
		if (!list)
		{
			return [];
		}

		return list;
	}

	getSaveTimeout()
	{
		return 250;
	}

	saveState(state)
	{
		if (!this.isSaveAvailable())
		{
			return false;
		}

		super.saveState(() =>
		{
			let list = this.getSaveFileList();
			if (!list)
			{
				return false;
			}

			let storedState = {
				collection: {},
			};

			for (let chatId in list)
			{
				if (!list.hasOwnProperty(chatId))
				{
					continue;
				}

				list[chatId].forEach(fileId =>
				{
					if (!state.index[chatId])
					{
						return false;
					}

					if (!state.index[chatId][fileId])
					{
						return false;
					}

					if (!storedState.collection[chatId])
					{
						storedState.collection[chatId] = [];
					}

					storedState.collection[chatId].push(
						state.index[chatId][fileId]
					);
				});
			}

			return storedState;
		});
	}

	validate(fields, options = {})
	{
		const result = {};

		options.host = options.host || this.getState().host;

		if (typeof fields.id === "number")
		{
			result.id = fields.id;
		}
		else if (typeof fields.id === "string")
		{
			if (fields.id.startsWith('temporary'))
			{
				result.id = fields.id;
			}
			else
			{
				result.id = parseInt(fields.id);
			}
		}

		if (typeof fields.templateId === "number")
		{
			result.templateId = fields.templateId;
		}
		else if (typeof fields.templateId === "string")
		{
			if (fields.templateId.startsWith('temporary'))
			{
				result.templateId = fields.templateId;
			}
			else
			{
				result.templateId = parseInt(fields.templateId);
			}
		}

		if (typeof fields.chatId === "number" || typeof fields.chatId === "string")
		{
			result.chatId = parseInt(fields.chatId);
		}

		if (typeof fields.date !== "undefined")
		{
			result.date = Utils.date.cast(fields.date);
		}

		if (typeof fields.type === "string")
		{
			result.type = fields.type;
		}

		if (typeof fields.extension === "string")
		{
			result.extension = fields.extension.toString();

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
				result.icon = FilesModel.getIconType(result.extension);
			}
		}

		if (typeof fields.name === "string" || typeof fields.name === "number")
		{
			result.name = fields.name.toString();
		}


		if (typeof fields.size === "number" || typeof fields.size === "string")
		{
			result.size = parseInt(fields.size);
		}

		if (typeof fields.image === 'boolean')
		{
			result.image = false;
		}
		else if (typeof fields.image === 'object' && fields.image)
		{
			result.image = {
				width: 0,
				height: 0,
			};

			if (typeof fields.image.width === "string" || typeof fields.image.width === "number")
			{
				result.image.width = parseInt(fields.image.width);
			}
			if (typeof fields.image.height === "string" || typeof fields.image.height === "number")
			{
				result.image.height = parseInt(fields.image.height);
			}

			if (result.image.width <= 0 || result.image.height <= 0)
			{
				result.image = false;
			}
		}

		if (typeof fields.status === "string" && typeof FileStatus[fields.status] !== 'undefined')
		{
			result.status = fields.status;
		}

		if (typeof fields.progress === "number" || typeof fields.progress === "string")
		{
			result.progress = parseInt(fields.progress);
		}

		if (typeof fields.authorId === "number" || typeof fields.authorId === "string")
		{
			result.authorId = parseInt(fields.authorId);
		}

		if (typeof fields.authorName === "string" || typeof fields.authorName === "number")
		{
			result.authorName = fields.authorName.toString();
		}

		if (typeof fields.urlPreview === 'string')
		{
			if (
				!fields.urlPreview
				|| fields.urlPreview.startsWith('http')
				|| fields.urlPreview.startsWith('bx')
				|| fields.urlPreview.startsWith('file')
				|| fields.urlPreview.startsWith('blob')
			)
			{
				result.urlPreview = fields.urlPreview;
			}
			else
			{
				result.urlPreview = options.host+fields.urlPreview;
			}
		}

		if (typeof fields.urlDownload === 'string')
		{
			if (
				!fields.urlDownload
				|| fields.urlDownload.startsWith('http')
				|| fields.urlDownload.startsWith('bx')
				|| fields.urlPreview.startsWith('file')
			)
			{
				result.urlDownload = fields.urlDownload;
			}
			else
			{
				result.urlDownload = options.host+fields.urlDownload;
			}
		}

		if (typeof fields.urlShow === 'string')
		{
			if (
				!fields.urlShow
				|| fields.urlShow.startsWith('http')
				|| fields.urlShow.startsWith('bx')
				|| fields.urlShow.startsWith('file')
			)
			{
				result.urlShow = fields.urlShow;
			}
			else
			{
				result.urlShow = options.host+fields.urlShow;
			}
		}

		if (typeof fields.viewerAttrs === 'object')
		{
			if (result.type === 'image' && !Utils.platform.isBitrixMobile())
			{
				result.viewerAttrs = fields.viewerAttrs;
			}

			if (result.type === 'video' && !Utils.platform.isBitrixMobile() && result.size > FilesModel.maxDiskFileSize)
			{
				result.viewerAttrs = fields.viewerAttrs;
			}
		}

		return result;
	}

	static getType(type)
	{
		type = type.toString().toLowerCase().split('.').splice(-1)[0];

		switch(type)
		{
			case 'png':
			case 'jpe':
			case 'jpg':
			case 'jpeg':
			case 'gif':
			case 'heic':
			case 'bmp':
			case 'webp':
				return FileType.image;

			case 'mp4':
			case 'mkv':
			case 'webm':
			case 'mpeg':
			case 'hevc':
			case 'avi':
			case '3gp':
			case 'flv':
			case 'm4v':
			case 'ogg':
			case 'wmv':
			case 'mov':
				return FileType.video;

			case 'mp3':
				return FileType.audio;
		}

		return FileType.file
	}

	static getIconType(extension)
	{
		let icon = 'empty';

		switch(extension.toString())
		{
			case 'png':
			case 'jpe':
			case 'jpg':
			case 'jpeg':
			case 'gif':
			case 'heic':
			case 'bmp':
			case 'webp':
				icon = 'img';
				break;

			case 'mp4':
			case 'mkv':
			case 'webm':
			case 'mpeg':
			case 'hevc':
			case 'avi':
			case '3gp':
			case 'flv':
			case 'm4v':
			case 'ogg':
			case 'wmv':
			case 'mov':
				icon = 'mov';
				break;

			case 'txt':
				icon = 'txt';
				break;

			case 'doc':
			case 'docx':
				icon = 'doc';
				break;

			case 'xls':
			case 'xlsx':
				icon = 'xls';
				break;

			case 'php':
				icon = 'php';
				break;

			case 'pdf':
				icon = 'pdf';
				break;

			case 'ppt':
			case 'pptx':
				icon = 'ppt';
				break;

			case 'rar':
				icon = 'rar';
				break;

			case 'zip':
			case '7z':
			case 'tar':
			case 'gz':
			case 'gzip':
				icon = 'zip';
				break;

			case 'set':
				icon = 'set';
				break;

			case 'conf':
			case 'ini':
			case 'plist':
				icon = 'set';
				break;
		}

		return icon;
	}
}
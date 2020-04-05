/**
 * Bitrix Messenger
 * File model (Vuex Builder model)
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2019 Bitrix
 */


import {Vue} from 'ui.vue';
import {VuexBuilderModel} from 'ui.vue.vuex';
import {InsertType} from 'im.const';

class FilesModel extends VuexBuilderModel
{
	getName()
	{
		return 'files';
	}

	getState()
	{
		return {
			host: this.getVariable('host', location.protocol+'//'+location.host),
			created: 0,
			collection: {},
			index: {},
		}
	}

	getElementState()
	{
		return {
			id: 0,
			templateId: 0,
			chatId: 0,
			date: new Date(),
			type: 'file',
			name: "",
			extension: "",
			icon: "empty",
			size: 0,
			image: false,
			status: 'done',
			progress: 100,
			authorId: 0,
			authorName: "",
			urlPreview: "",
			urlShow: "",
			urlDownload: "",
		};
	}

	getGetters()
	{
		return {
			get: state => chatId =>
			{
				if (!state.collection[chatId])
				{
					return null;
				}

				return state.collection[chatId];
			},
			getObject: state => chatId =>
			{
				if (!state.index[chatId])
				{
					return null;
				}

				return state.index[chatId];
			},
			getBlank: state => params =>
			{
				return this.getElementState();
			}
		}
	}

	getActions()
	{
		return {
			set: (store, payload) =>
			{
				if (payload instanceof Array)
				{
					payload = payload.map(file => {
						let result = this.validate(Object.assign({}, file), {host: store.state.host});
						result.templateId = result.id;
						return Object.assign({}, this.getElementState(), result);
					});
				}
				else
				{
					let result = this.validate(Object.assign({}, payload), {host: store.state.host});
					result.templateId = result.id;
					payload = [];
					payload.push(
						Object.assign({}, this.getElementState(), result)
					);
				}

				store.commit('set', {
					insertType : InsertType.after,
					data : payload
				});
			},
			setBefore: (store, payload) =>
			{
				if (payload instanceof Array)
				{
					payload = payload.map(message => {
						let result = this.validate(Object.assign({}, message), {host: store.state.host});
						result.templateId = result.id;
						return Object.assign({}, this.getElementState(), result);
					});
				}
				else
				{
					let result = this.validate(Object.assign({}, payload), {host: store.state.host});
					result.templateId = result.id;
					payload = [];
					payload.push(
						Object.assign({}, this.getElementState(), result)
					);
				}

				store.commit('set', {
					actionName: 'setBefore',
					insertType : InsertType.before,
					data : payload
				});
			},
			update: (store, payload) =>
			{
				let result = this.validate(Object.assign({}, payload.fields), {host: store.state.host});

				if (typeof store.state.collection[payload.chatId] === 'undefined')
				{
					Vue.set(store.state.collection, payload.chatId, []);
					Vue.set(store.state.index, payload.chatId, {});
				}

				let index = store.state.collection[payload.chatId].findIndex(el => el.id == payload.id);
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
		}
	}

	getMutations()
	{
		return {
			initCollection: (state, payload) =>
			{
				if (typeof state.collection[payload.chatId] === 'undefined')
				{
					Vue.set(state.collection, payload.chatId, []);
					Vue.set(state.index, payload.chatId, {});
				}
			},
			set: (state, payload) =>
			{
				if (payload.insertType == InsertType.after)
				{
					for (let element of payload.data)
					{
						if (typeof state.collection[element.chatId] === 'undefined')
						{
							Vue.set(state.collection, element.chatId, []);
							Vue.set(state.index, element.chatId, {});
						}

						let index = state.collection[element.chatId].findIndex(el => el.id === element.id);
						if (index > -1)
						{
							state.collection[element.chatId][index] = element;
						}
						else
						{
							state.collection[element.chatId].push(element);
						}

						state.index[element.chatId][element.id] = element;
					}
				}
				else
				{
					for (let element of payload.data)
					{
						if (typeof state.collection[element.chatId] === 'undefined')
						{
							Vue.set(state.collection, element.chatId, []);
							Vue.set(state.index, element.chatId, {});
						}

						let index = state.collection[element.chatId].findIndex(el => el.id === element.id);
						if (index > -1)
						{
							state.collection[element.chatId][index] = element;
						}
						else
						{
							state.collection[element.chatId].unshift(element);
						}

						state.index[element.chatId][element.id] = element;
					}
				}
			},
			update: (state, payload) =>
			{
				if (typeof state.collection[payload.chatId] === 'undefined')
				{
					Vue.set(state.collection, payload.chatId, []);
					Vue.set(state.index, payload.chatId, {});
				}

				let index = -1;
				if (typeof payload.index !== 'undefined' && state.collection[payload.chatId][payload.index])
				{
					index = payload.index;
				}
				else
				{
					index = state.collection[payload.chatId].findIndex(el => el.id == payload.id);
				}

				if (index >= 0)
				{
					let element = Object.assign(
						state.collection[payload.chatId][index],
						payload.fields
					);
					state.collection[payload.chatId][index] = element;
					state.index[payload.chatId][element.id] = element;
				}
			},
			delete: (state, payload) =>
			{
				if (typeof state.collection[payload.chatId] === 'undefined')
				{
					Vue.set(state.collection, payload.chatId, []);
					Vue.set(state.index, payload.chatId, {});
				}

				state.collection[payload.chatId] = state.collection[payload.chatId].filter(element => element.id != payload.id);
				delete state.index[payload.chatId][payload.id];
			},
		}
	}

	validate(fields, options = {})
	{
		const result = {};

		options.host = options.host || this.getState().host;

		if (typeof fields.id === "number" || typeof fields.id === "string")
		{
			result.id = parseInt(fields.id);
		}

		if (typeof fields.chatId === "number" || typeof fields.chatId === "string")
		{
			result.chatId = parseInt(fields.chatId);
		}

		if (fields.date instanceof Date)
		{
			result.date = fields.date;
		}
		else if (typeof fields.date === "string")
		{
			result.date = new Date(fields.date);
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
			else if (result.extension === 'docx' || result.extension === 'doc')
			{
				result.icon = 'doc';
			}
			else if (result.extension === 'xlsx' || result.extension === 'xls')
			{
				result.icon = 'xls';
			}
			else if (result.extension === 'pptx' || result.extension === 'ppt')
			{
				result.icon = 'ppt';
			}
			else if (result.extension === 'rar')
			{
				result.icon = 'rar';
			}
			else if (result.extension === 'zip')
			{
				result.icon = 'zip';
			}
			else if (result.extension === 'pdf')
			{
				result.icon = 'pdf';
			}
			else if (result.extension === 'txt')
			{
				result.icon = 'txt';
			}
			else if (result.extension === 'php')
			{
				result.icon = 'php';
			}
			else if (result.extension === 'conf' || result.extension === 'ini' || result.extension === 'plist')
			{
				result.icon = 'set';
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

			if (typeof fields.image.width === "number")
			{
				result.image.width = fields.image.width;
			}
			if (typeof fields.image.height === "number")
			{
				result.image.height = fields.image.height;
			}
		}

		if (typeof fields.status === "string")
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
			if (!fields.urlPreview || fields.urlPreview.startsWith('http'))
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
			if (!fields.urlDownload || fields.urlDownload.startsWith('http'))
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
			if (!fields.urlShow || fields.urlShow.startsWith('http'))
			{
				result.urlShow = fields.urlShow;
			}
			else
			{
				result.urlShow = options.host+fields.urlShow;
			}
		}

		return result;
	}
}

export {FilesModel};
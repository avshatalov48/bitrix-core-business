/**
 * Bitrix Messenger
 * Message model (Vuex module)
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2019 Bitrix
 */

const InsertType = Object.freeze({
	after: 'after',
	before: 'before',
});

class ModelMessages
{
	static getInstance()
	{
		return new ModelMessages();
	}

	static getName()
	{
		return 'messengerMessages';
	}

	getStore()
	{
		return {
			namespaced : true,

			state:
			{
				created: 0,
				collection: {},
			},

			getters:
			{
				getLastId: state => chatId =>
				{
					if (!state.collection[chatId] || state.collection[chatId].length <= 0)
					{
						return null;
					}

					for (let index = state.collection[chatId].length-1; index >= 0; index--)
					{
						let element = state.collection[chatId][index];
						if (element.sending)
							continue;

						return element.id;
					}

					return null;
				}
			},

			actions:
			{
				add(store, payload)
				{
					let result = ModelMessages.validate(Object.assign({}, payload));
					result.params = Object.assign({}, ModelMessages.getMessageBlank().params, result.params);
					result.id = 'temporary' + store.state.created;
					result.templateId = result.id;
					result.unread = false;

					store.commit('add', Object.assign({}, ModelMessages.getMessageBlank(), result));
					store.dispatch('actionStart', {
						id: result.id,
						chatId: result.chatId,
					});

					return result.id;
				},
				actionStart(store, payload)
				{
					BX.Vue.nextTick(() => {
						store.commit('update', {
							id : payload.id ,
							chatId : payload.chatId,
							fields : {sending: true}
						});
					});
				},
				actionError(store, payload)
				{
					BX.Vue.nextTick(() => {
						store.commit('update', {
							id : payload.id ,
							chatId : payload.chatId,
							fields : {sending: false, error: true}
						});
					});
				},
				actionFinish(store, payload)
				{
					BX.Vue.nextTick(() => {
						store.commit('update', {
							id : payload.id ,
							chatId : payload.chatId,
							fields : {sending: false, error: false}
						});
					});
				},
				set(store, payload)
				{
					if (payload instanceof Array)
					{
						payload = payload.map(message => {
							let result = ModelMessages.validate(Object.assign({}, message));
							result.params = Object.assign({}, ModelMessages.getMessageBlank().params, result.params);
							result.templateId = result.id;
							return Object.assign({}, ModelMessages.getMessageBlank(), result);
						});
					}
					else
					{
						let result = ModelMessages.validate(Object.assign({}, payload));
						result.params = Object.assign({}, ModelMessages.getMessageBlank().params, result.params);
						result.templateId = result.id;
						payload = [];
						payload.push(
							Object.assign({}, ModelMessages.getMessageBlank(), result)
						);
					}

					store.commit('set', {
						insertType : InsertType.after,
						data : payload
					});
				},
				setBefore(store, payload)
				{
					if (payload instanceof Array)
					{
						payload = payload.map(message => {
							let result = ModelMessages.validate(Object.assign({}, message));
							result.params = Object.assign({}, ModelMessages.getMessageBlank().params, result.params);
							result.templateId = result.id;
							return Object.assign({}, ModelMessages.getMessageBlank(), result);
						});
					}
					else
					{
						let result = ModelMessages.validate(Object.assign({}, payload));
						result.params = Object.assign({}, ModelMessages.getMessageBlank().params, result.params);
						result.templateId = result.id;
						payload = [];
						payload.push(
							Object.assign({}, ModelMessages.getMessageBlank(), result)
						);
					}

					store.commit('set', {
						actionName: 'setBefore',
						insertType : InsertType.before,
						data : payload
					});
				},
				update(store, payload)
				{
					let result = ModelMessages.validate(Object.assign({}, payload.fields));

					if (typeof store.state.collection[payload.chatId] === 'undefined')
					{
						BX.Vue.set(store.state.collection, payload.chatId, []);
					}

					let index = store.state.collection[payload.chatId].findIndex(el => el.id == payload.id);
					if (index < 0)
					{
						return false;
					}

					if (payload.fields.params)
					{
						result.params = Object.assign(
							{},
							ModelMessages.getMessageBlank().params,
							store.state.collection[payload.chatId][index].params,
							payload.fields.params
						);
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
				delete(store, payload)
				{
					store.commit('delete', {
						id : payload.id,
						chatId : payload.chatId
					});
					return true;
				},
				readMessages(store, payload)
				{
					payload.readId = payload.readId || 0;

					if (typeof store.state.collection[payload.chatId] === 'undefined')
					{
						return {count: 0}
					}

					let count = 0;
					for (let index = store.state.collection[payload.chatId].length-1; index >= 0; index--)
					{
						let element = store.state.collection[payload.chatId][index];
						if (!element.unread)
							continue;

						if (payload.readId === 0 || element.id <= payload.readId)
						{
							count++;
						}
					}

					let result = store.commit('readMessages', {
						chatId: payload.chatId,
						readId: payload.readId,
					});

					return {count};
				},
			},

			mutations:
			{
				initCollection(state, payload)
				{
					if (typeof state.collection[payload.chatId] === 'undefined')
					{
						BX.Vue.set(state.collection, payload.chatId, payload.messages? [].concat(payload.messages): []);
					}
				},
				add(state, payload)
				{
					if (typeof state.collection[payload.chatId] === 'undefined')
					{
						BX.Vue.set(state.collection, payload.chatId, []);
					}

					state.collection[payload.chatId].push(payload);
					state.created += 1;
				},
				set(state, payload)
				{
					if (payload.insertType == InsertType.after)
					{
						for (let element of payload.data)
						{
							if (typeof state.collection[element.chatId] === 'undefined')
							{
								BX.Vue.set(state.collection, element.chatId, []);
							}

							let index = state.collection[element.chatId].findIndex(el => el.id === element.id);
							if (index > -1)
							{
								state.collection[element.chatId][index] = Object.assign(
									state.collection[element.chatId][index],
									element
								);
							}
							else
							{
								state.collection[element.chatId].push(element);
							}
						}
					}
					else
					{
						for (let element of payload.data)
						{
							if (typeof state.collection[element.chatId] === 'undefined')
							{
								BX.Vue.set(state.collection, element.chatId, []);
							}

							let index = state.collection[element.chatId].findIndex(el => el.id === element.id);
							if (index > -1)
							{
								state.collection[element.chatId][index] = Object.assign(
									state.collection[element.chatId][index],
									element
								);
							}
							else
							{
								state.collection[element.chatId].unshift(element);
							}
						}
					}
				},
				update(state, payload)
				{
					if (typeof state.collection[payload.chatId] === 'undefined')
					{
						BX.Vue.set(state.collection, payload.chatId, []);
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
						state.collection[payload.chatId][index] = Object.assign(
							state.collection[payload.chatId][index],
							payload.fields
						);
					}
				},
				delete(state, payload)
				{
					if (typeof state.collection[payload.chatId] === 'undefined')
					{
						BX.Vue.set(state.collection, payload.chatId, []);
					}

					state.collection[payload.chatId] = state.collection[payload.chatId].filter(element => element.id != payload.id);
				},
				readMessages(state, payload)
				{
					if (typeof state.collection[payload.chatId] === 'undefined')
					{
						BX.Vue.set(state.collection, payload.chatId, []);
					}

					for (let index = state.collection[payload.chatId].length-1; index >= 0; index--)
					{
						let element = state.collection[payload.chatId][index];
						if (!element.unread)
							continue;

						if (payload.readId === 0 || element.id <= payload.readId)
						{
							state.collection[payload.chatId][index] = Object.assign(
								state.collection[payload.chatId][index],
								{unread: false}
							);
						}
					}
				}
			}
		};
	}

	static getMessageBlank()
	{
		return {
			templateId: 0,
			templateType: 'message',

			id: 0,
			chatId: 0,
			authorId: 0,
			date: new Date(),
			text: "",
			textConverted: "",
			params: {
				TYPE : 'default',
				COMPONENT_ID : 'bx-messenger-message',
			},

			unread: false,
			sending: false,
			error: false,
			blink: false,
		};
	}

	static convertToHtml(params = {})
	{
		let {
			quote = true,
			image = true,
			text = '',
			highlightText = '',
			isConverted = false,
			enableBigSmile = true
		} = params;

		text = text.trim();

		if (!isConverted)
		{
			text = text.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
		}

		if (text.startsWith('/me'))
		{
			text = `<i>${text.substr(4)}</i>`;
		}
		else if (text.startsWith('/loud'))
		{
			text = `<b>${text.substr(6)}</b>`;
		}

		const quoteSign = "&gt;&gt;";
		if (quote && text.indexOf(quoteSign) >= 0)
		{
			let textPrepareFlag = false;
			let textPrepare = text.split(isConverted? "<br />": "\n");
			for (let i = 0; i < textPrepare.length; i++)
			{
				if (textPrepare[i].startsWith(quoteSign))
				{
					textPrepare[i] = textPrepare[i].replace(quoteSign, '<div class="bx-im-message-content-quote"><div class="bx-im-message-content-quote-wrap">');
					while (++i < textPrepare.length && textPrepare[i].startsWith(quoteSign))
					{
						textPrepare[i] = textPrepare[i].replace(quoteSign, '');
					}
					textPrepare[i - 1] += '</div></div><br>';
					textPrepareFlag = true;
				}
			}
			text = textPrepare.join("<br />");
		}

		text = this.decodeBbCode(text, false, enableBigSmile);

		text = text.replace(/\n/gi, '<br />');

		text = text.replace(/\t/gi, '&nbsp;&nbsp;&nbsp;&nbsp;');

		if (quote)
		{
			text = text.replace(/------------------------------------------------------<br \/>(.*?)\[(.*?)\]<br \/>(.*?)------------------------------------------------------(<br \/>)?/g, function (whole, p1, p2, p3, p4, offset) {
				return (offset > 0? '<br>': '') + "<div class=\"bx-im-message-content-quote\"><div class=\"bx-im-message-content-quote-wrap\"><div class=\"bx-im-message-content-quote-name\">" + p1 + " <span class=\"bx-im-message-content-quote-time\">" + p2 + "</span></div>" + p3 + "</div></div><br />";
			});
			text = text.replace(/------------------------------------------------------<br \/>(.*?)------------------------------------------------------(<br \/>)?/g, function (whole, p1, p2, p3, offset) {
				return (offset > 0? '<br>': '') + "<div class=\"bx-im-message-content-quote\"><div class=\"bx-im-message-content-quote-wrap\">" + p1 + "</div></div><br />";
			});
		}

		if (image)
		{
			let changed = false;
			text = text.replace(/<a(.*?)>(http[s]{0,1}:\/\/.*?)<\/a>/ig, function(whole, aInner, text, offset)
			{
				if(!text.match(/(\.(jpg|jpeg|png|gif)\?|\.(jpg|jpeg|png|gif)$)/i) || text.indexOf("/docs/pub/") > 0 || text.indexOf("logout=yes") > 0)
				{
					return whole;
				}
				else
				{
					changed = true;
					return (offset > 0? '<br />':'')+'<a' +aInner+ ' target="_blank" class="bx-im-element-file-image"><img src="'+text+'" class="bx-im-element-file-image-source-text" onerror="BX.Messenger.Model.Messages.hideErrorImage(this)"></a></span>';
				}
			});
			if (changed)
			{
				text = text.replace(/<\/span>(\n?)<br(\s\/?)>/ig, '</span>').replace(/<br(\s\/?)>(\n?)<br(\s\/?)>(\n?)<span/ig, '<br /><span');
			}
		}

		if (highlightText)
		{
			text = text.replace(new RegExp("(" + highlightText.replace(/[\-\[\]\/{}()*+?.\\^$|]/g, "\\$&") + ")", 'ig'), '<span class="bx-messenger-highlight">$1</span>');
		}

		if (enableBigSmile)
		{
			text = text.replace(
				/^(\s*<img\s+src=[^>]+?data-code=[^>]+?data-definition="UHD"[^>]+?style="width:)(\d+)(px[^>]+?height:)(\d+)(px[^>]+?class="bx-smile"\s*\/?>\s*)$/,
				function doubleSmileSize(match, start, width, middle, height, end) {
					return start + (parseInt(width, 10) * 2) + middle + (parseInt(height, 10) * 2) + end;
				}
			);
		}

		if (text.substr(-6) == '<br />')
		{
			text = text.substr(0, text.length - 6);
		}
		text = text.replace(/<br><br \/>/ig, '<br />');
		text = text.replace(/<br \/><br>/ig, '<br />');

		return text;
	};

	static hideErrorImage(element)
	{
		if (element.parentNode && element.parentNode)
		{
			element.parentNode.innerHTML = '<a href="'+element.src+'" target="_blank">'+element.src+'</a>';
		}
		return true;
	};

	static decodeBbCode(textElement, textOnly = false, enableBigSmile = true)
	{
		let codeReplacement = [];

		textElement = textElement.replace(/\[CODE\]\n?(.*?)\[\/CODE\]/sig, function(whole, text)
		{
			let id = codeReplacement.length;
			codeReplacement.push(text);
			return '####REPLACEMENT_MARK_'+id+'####';
		});

		textElement = textElement.replace(/\[LIKE\]/ig, '<span class="bx-smile bx-im-smile-like"></span>');
		textElement = textElement.replace(/\[DISLIKE\]/ig, '<span class="bx-smile bx-im-smile-dislike"></span>');

		textElement = textElement.replace(/\[USER=([0-9]{1,})\](.*?)\[\/USER\]/ig, (whole, userId, text) => text);

		textElement = textElement.replace(/\[CHAT=(imol\|)?([0-9]{1,})\](.*?)\[\/CHAT\]/ig, (whole, openlines, chatId, text) => text);

		textElement = textElement.replace(/\[PCH=([0-9]{1,})\](.*?)\[\/PCH\]/ig, (whole, historyId, text) => text);

		textElement = textElement.replace(/\[SEND(?:=(.+?))?\](.+?)?\[\/SEND\]/ig, (whole, command, text) =>
		{
			let html = '';

			text = text? text: command;
			command = command? command: text;

			if (!textOnly && text)
			{
				text = text.replace(/<([\w]+)[^>]*>(.*?)<\\1>/i, "$2", text);
				text = text.replace(/\[([\w]+)[^\]]*\](.*?)\[\/\1\]/i, "$2", text);

				html = '<span class="bx-im-message-command" data-entity="send">'+text+'</span>';
				html += '<span class="bx-im-message-command-data">'+command+'</span>';
			}
			else
			{
				html = text;
			}

			return html;
		});

		textElement = textElement.replace(/\[PUT(?:=(.+?))?\](.+?)?\[\/PUT\]/ig, (whole, command, text) =>
		{
			let html = '';

			text = text? text: command;
			command = command? command: text;

			if (!textOnly && text)
			{
				text = text.replace(/<([\w]+)[^>]*>(.*?)<\/\1>/i, "$2", text);
				text = text.replace(/\[([\w]+)[^\]]*\](.*?)\[\/\1\]/i, "$2", text);

				html = '<span class="bx-im-message-command" data-entity="put" v-on:click="alert(1)">'+text+'</span>';
				html += '<span class="bx-im-message-command-data">'+command+'</span>';
			}
			else
			{
				html = text;
			}

			return html;
		});

		textElement = textElement.replace(/\[CALL(?:=(.+?))?\](.+?)?\[\/CALL\]/ig, (whole, command, text) => text);

		let textElementSize = 0;
		if (enableBigSmile)
		{
			textElementSize = textElement.replace(/\[icon\=([^\]]*)\]/ig, '').trim().length;
		}

		textElement = textElement.replace(/\[icon\=([^\]]*)\]/ig, (whole) =>
		{
			let url = whole.match(/icon\=(\S+[^\s.,> )\];\'\"!?])/i);
			if (url && url[1])
			{
				url = url[1];
			}
			else
			{
				return '';
			}

			let attrs = {'src': url, 'border': 0};

			let size = whole.match(/size\=(\d+)/i);
			if (size && size[1])
			{
				attrs['width'] = size[1];
				attrs['height'] = size[1];
			}
			else
			{
				let width = whole.match(/width\=(\d+)/i);
				if (width && width[1])
				{
					attrs['width'] = width[1];
				}

				let height = whole.match(/height\=(\d+)/i);
				if (height && height[1])
				{
					attrs['height'] = height[1];
				}

				if (attrs['width'] && !attrs['height'])
				{
					attrs['height'] = attrs['width'];
				}
				else if (attrs['height'] && !attrs['width'])
				{
					attrs['width'] = attrs['height'];
				}
				else if (attrs['height'] && attrs['width'])
				{}
				else
				{
					attrs['width'] = 20;
					attrs['height'] = 20;
				}
			}

			attrs['width'] = attrs['width']>100? 100: attrs['width'];
			attrs['height'] = attrs['height']>100? 100: attrs['height'];

			if (enableBigSmile && textElementSize == 0 && attrs['width'] == attrs['height'] && attrs['width'] == 20)
			{
				attrs['width'] = 40;
				attrs['height'] = 40;
			}

			let title = whole.match(/title\=(.*[^\s\]])/i);
			if (title && title[1])
			{
				title = title[1];
				if (title.indexOf('width=') > -1)
				{
					title = title.substr(0, title.indexOf('width='))
				}
				if (title.indexOf('height=') > -1)
				{
					title = title.substr(0, title.indexOf('height='))
				}
				if (title.indexOf('size=') > -1)
				{
					title = title.substr(0, title.indexOf('size='))
				}
				if (title)
				{
					attrs['title'] = BX.Messenger.Utils.htmlspecialchars(title).trim();
					attrs['alt'] = BX.Messenger.Utils.htmlspecialchars(title).trim();
				}
			}

			let attributes = '';
			for (let name in attrs)
			{
				if (attrs.hasOwnProperty(name))
				{
					attributes += name+'="'+attrs[name]+'" ';
				}
			}


			return '<img class="bx-smile bx-icon" '+attributes+'>';
		});

		codeReplacement.forEach((code, index) => {
			textElement = textElement.replace('####REPLACEMENT_MARK_'+index+'####',
				!textOnly? '<div class="bx-im-message-content-code">'+code+'</div>': code
			)
		});

		return textElement;
	}

	static validate(fields)
	{
		const result = {};

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

		if (typeof fields.chat_id !== 'undefined')
		{
			fields.chatId = fields.chat_id;
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

		// previous P&P format
		if (typeof fields.textOriginal === "string" || typeof fields.textOriginal === "number")
		{
			result.text = fields.textOriginal.toString();

			if (typeof fields.text === "string" || typeof fields.text === "number")
			{
				result.textConverted = ModelMessages.convertToHtml({
					text: fields.text.toString(),
					isConverted: true
				});
			}
		}
		else // modern format
		{
			if (typeof fields.text_converted !== 'undefined')
			{
				fields.textConverted = fields.text_converted;
			}
			if (typeof fields.textConverted === "string" || typeof fields.textConverted === "number")
			{
				result.textConverted = fields.textConverted.toString();
			}
			if (typeof fields.text === "string" || typeof fields.text === "number")
			{
				result.text = fields.text.toString();

				let isConverted = typeof result.textConverted !== 'undefined';

				result.textConverted = ModelMessages.convertToHtml({
					text: isConverted? result.textConverted: result.text,
					isConverted
				});
			}
		}

		if (typeof fields.senderId !== 'undefined')
		{
			fields.authorId = fields.senderId;
		}
		else if (typeof fields.author_id !== 'undefined')
		{
			fields.authorId = fields.author_id;
		}
		if (typeof fields.authorId === "number" || typeof fields.authorId === "string")
		{
			if (fields.system === true || fields.system === 'Y')
			{
				result.authorId = 0;
			}
			else
			{
				result.authorId = parseInt(fields.authorId);
			}
		}

		if (typeof fields.params === "object" && fields.params !== null)
		{
			const params = ModelMessages.validateParams(fields.params);
			if (params)
			{
				result.params = params;
			}
		}

		if (typeof fields.sending === "boolean")
		{
			result.sending = fields.sending;
		}

		if (typeof fields.unread === "boolean")
		{
			result.unread = fields.unread;
		}

		if (typeof fields.blink === "boolean")
		{
			result.blink = fields.blink;
		}

		if (typeof fields.error === "boolean" || typeof fields.error === "string")
		{
			result.error = fields.error;
		}

		return result;
	}

	static validateParams(params)
	{
		const result = {};

		try
		{
			for (let field in params)
			{
				if (!params.hasOwnProperty(field))
				{
					continue;
				}

				if (field === 'COMPONENT_ID')
				{
					if (typeof params[field] === "string" && BX.Vue.isComponent(params[field]))
					{
						result[field] = params[field];
					}
				}
				else
				{
					result[field] = params[field];
				}
			}
		}
		catch (e) {}

		let hasResultElements = false;
		for (let field in result)
		{
			if (!result.hasOwnProperty(field))
			{
				continue;
			}

			hasResultElements = true;
			break
		}

		return hasResultElements? result: null;
	}
}

if (!window.BX)
{
	window.BX = {};
}
if (typeof window.BX.Messenger == 'undefined')
{
	window.BX.Messenger = {};
}
if (typeof window.BX.Messenger.Model == 'undefined')
{
	window.BX.Messenger.Model = {};
}
if (typeof window.BX.Messenger.Model.Messages == 'undefined')
{
	BX.Messenger.Model.Messages = ModelMessages;
}
/**
 * Bitrix Messenger
 * Utils
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2019 Bitrix
 */

import {Text, Type, Dom} from 'main.core';

import {DateFormat} from 'im.const';

import 'main.date';

import './css/utils.css';

let Utils =
{
	browser:
	{
		isSafari()
		{
			if (this.isChrome())
			{
				return false;
			}

			if (!navigator.userAgent.toLowerCase().includes('safari'))
			{
				return false;
			}

			return !this.isSafariBased();
		},
		isSafariBased()
		{
			if (!navigator.userAgent.toLowerCase().includes('applewebkit'))
			{
				return false;
			}

			return (
				navigator.userAgent.toLowerCase().includes('yabrowser')
				|| navigator.userAgent.toLowerCase().includes('yaapp_ios_browser')
				|| navigator.userAgent.toLowerCase().includes('crios')
			)
		},
		isChrome()
		{
			return navigator.userAgent.toLowerCase().includes('chrome');
		},
		isFirefox()
		{
			return navigator.userAgent.toLowerCase().includes('firefox');
		},
		isIe()
		{
			return navigator.userAgent.match(/(Trident\/|MSIE\/)/) !== null;
		},

		findParent(item, findTag)
		{
			let isHtmlElement = findTag instanceof HTMLElement;

			if (
				!findTag
				|| typeof findTag !== 'string' && !isHtmlElement
			)
			{
				return null;
			}

			for (; item && item !== document; item = item.parentNode)
			{
				if (typeof findTag === 'string')
				{
					if (item.classList.contains(findTag))
					{
						return item;
					}
				}
				else if (isHtmlElement)
				{
					if (item === findTag)
					{
						return item;
					}
				}
			}

			return null;
		}
	},

	platform:
	{
		isMac()
		{
			return navigator.userAgent.toLowerCase().includes('macintosh');
		},
		isLinux()
		{
			return navigator.userAgent.toLowerCase().includes('linux');
		},
		isWindows()
		{
			return navigator.userAgent.toLowerCase().includes('windows') || (!this.isMac() && !this.isLinux());
		},
		isBitrixMobile()
		{
			return navigator.userAgent.toLowerCase().includes('bitrixmobile');
		},
		isBitrixDesktop()
		{
			return navigator.userAgent.toLowerCase().includes('bitrixdesktop');
		},
		getDesktopVersion()
		{
			if (typeof this.getDesktopVersionStatic !== 'undefined')
			{
				return this.getDesktopVersionStatic;
			}

			if (typeof BXDesktopSystem === 'undefined')
			{
				return 0;
			}

			const version = BXDesktopSystem.GetProperty('versionParts');
			this.getDesktopVersionStatic = version[3];

			return this.getDesktopVersionStatic;
		},
		isDesktopFeatureEnabled(code)
		{
			if (typeof BXDesktopSystem === 'undefined')
			{
				return false;
			}

			if (typeof BXDesktopSystem.FeatureEnabled !== 'function')
			{
				return false;
			}

			return !!BXDesktopSystem.FeatureEnabled(code);
		},
		isMobile()
		{
			return this.isAndroid() || this.isIos() || this.isBitrixMobile();
		},
		isIos(): boolean
		{
			return navigator.userAgent.toLowerCase().includes('iphone') || navigator.userAgent.toLowerCase().includes('ipad');
		},
		getIosVersion()
		{
			if (!this.isIos())
			{
				return null;
			}

			let matches = navigator.userAgent.toLowerCase().match(/(iphone|ipad)(.+)(OS\s([0-9]+)([_.]([0-9]+))?)/i);
			if (!matches || !matches[4])
			{
				return null;
			}

			return parseFloat(matches[4]+'.'+(matches[6]? matches[6]: 0));
		},
		isAndroid()
		{
			return navigator.userAgent.toLowerCase().includes('android');
		},
		openNewPage(url)
		{
			if (!url)
			{
				return false;
			}

			if (this.isBitrixMobile())
			{
				if (typeof BX.MobileTools !== 'undefined')
				{
					let openWidget = BX.MobileTools.resolveOpenFunction(url);
					if (openWidget)
					{
						openWidget();
						return true;
					}
				}

				app.openNewPage(url);
			}
			else
			{
				window.open(url, '_blank');
			}

			return true;
		}
	},

	device:
	{
		isDesktop()
		{
			return !this.isMobile();
		},

		isMobile()
		{
			if (typeof this.isMobileStatic !== 'undefined')
			{
				return this.isMobileStatic;
			}

			this.isMobileStatic = (
				navigator.userAgent.toLowerCase().includes('android')
				|| navigator.userAgent.toLowerCase().includes('webos')
				|| navigator.userAgent.toLowerCase().includes('iphone')
				|| navigator.userAgent.toLowerCase().includes('ipad')
				|| navigator.userAgent.toLowerCase().includes('ipod')
				|| navigator.userAgent.toLowerCase().includes('blackberry')
				|| navigator.userAgent.toLowerCase().includes('windows phone')
			);

			return this.isMobileStatic;
		},

		orientationHorizontal: 'horizontal',
		orientationPortrait: 'portrait',

		getOrientation()
		{
			if (!this.isMobile())
			{
				return this.orientationHorizontal;
			}

			return Math.abs(window.orientation) === 0? this.orientationPortrait: this.orientationHorizontal;
		},
	},

	types:
	{
		isString(item)
		{
			return item === '' ? true : (item ? (typeof (item) == "string" || item instanceof String) : false);
		},

		isArray(item)
		{
			return item && Object.prototype.toString.call(item) == "[object Array]";
		},

		isFunction(item)
		{
			return item === null ? false : (typeof (item) == "function" || item instanceof Function);
		},

		isDomNode(item)
		{
			return item && typeof (item) == "object" && "nodeType" in item;
		},

		isDate(item)
		{
			return item && Object.prototype.toString.call(item) == "[object Date]";
		},

		isPlainObject(item)
		{
			if (!item || typeof item !== "object" || item.nodeType)
			{
				return false;
			}

			const hasProp = Object.prototype.hasOwnProperty;
			try
			{
				if (
					item.constructor
					&& !hasProp.call(item, "constructor")
					&& !hasProp.call(item.constructor.prototype, "isPrototypeOf")
				)
				{
					return false;
				}
			}
			catch (e)
			{
				return false;
			}

			let key;
			for (let key in item)
			{
			}

			return typeof(key) === "undefined" || hasProp.call(item, key);
		},

		isUuidV4(uuid)
		{
			if (!this.isString(uuid))
			{
				return false;
			}

			const uuidV4pattern = new RegExp(/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i);

			return uuid.search(uuidV4pattern) === 0;
		},
	},

	dialog:
	{
		getChatIdByDialogId(dialogId)
		{
			if (!this.isChatId(dialogId))
			{
				return 0;
			}

			return parseInt(dialogId.toString().substr(4));
		},

		isChatId(dialogId)
		{
			return dialogId.toString().startsWith('chat')
		},

		isEmptyDialogId(dialogId)
		{
			if (!dialogId)
			{
				return true;
			}

			if (typeof dialogId === "string")
			{
				if (dialogId === 'chat0' || dialogId === "0")
				{
					return true;
				}
			}

			return false;
		},
	},

	text:
	{
		quote(text, params, files = {}, localize = null)
		{
			if (typeof text !== 'string')
			{
				return text.toString();
			}

			if (!localize)
			{
				localize = BX.message;
			}

			text = text.replace(/\[USER=([0-9]{1,})](.*?)\[\/USER]/gi, (whole, userId, text) => text);
			text = text.replace(/\[CHAT=(imol\|)?([0-9]{1,})](.*?)[\/CHAT]/gi, (whole, imol, chatId, text) => text);
			text = text.replace(/\[CALL(?:=(.+?))?](.+?)?\[\/CALL]/gi, (whole, command, text) => text? text: command);
			text = text.replace(/\[ATTACH=([0-9]{1,})]/gi, (whole, command, text) => command === 10000? '': '['+localize['IM_UTILS_TEXT_ATTACH']+'] ');
			text = text.replace(/\[RATING=([1-5]{1})]/gi, (whole, rating) => '['+localize.IM_F_RATING+'] ');
			text = text.replace(/&nbsp;/gi, " ");

			text = text.replace(/------------------------------------------------------(.*?)------------------------------------------------------/gmis, "["+localize["IM_UTILS_TEXT_QUOTE"]+"]");
			text = text.replace(/^(>>(.*)\n)/gi, "["+localize["IM_UTILS_TEXT_QUOTE"]+"]\n");

			if (params && params.FILE_ID && params.FILE_ID.length > 0)
			{
				let filesText = [];
				params.FILE_ID.forEach(fileId =>
				{
					if (files[fileId].type === 'image')
					{
						filesText.push(localize['IM_UTILS_TEXT_IMAGE']);
					}
					else if (files[fileId].type === 'audio')
					{
						filesText.push(localize['IM_UTILS_TEXT_AUDIO']);
					}
					else if (files[fileId].type === 'video')
					{
						filesText.push(localize['IM_UTILS_TEXT_VIDEO']);
					}
					else
					{
						filesText.push(files[fileId].name);
					}
				});

				if (filesText.length <= 0)
				{
					filesText.push(localize['IM_UTILS_TEXT_FILE']);
				}

				text = filesText.join('\n')+text;
			}
			else if (params && params.ATTACH && params.ATTACH.length > 0)
			{
				text = '['+localize['IM_UTILS_TEXT_ATTACH']+']\n'+text;
			}
			if (text.length <= 0)
			{
				text = localize['IM_UTILS_TEXT_DELETED'];
			}

			return text.trim();
		},

		purify(text, params, files = {}, localize = null)
		{
			if (typeof text !== 'string')
			{
				return text.toString();
			}

			if (!localize)
			{
				localize = BX.message;
			}

			text = text.trim();

			if (text.startsWith('/me'))
			{
				text = text.substr(4);
			}
			else if (text.startsWith('/loud'))
			{
				text = text.substr(6);
			}

			text = text.replace(/<br><br \/>/gi, '<br />');
			text = text.replace(/<br \/><br>/gi, '<br />');

			const codeReplacement = [];
			text = text.replace(/\[CODE](<br \/>)?(.*?)\[\/CODE]/sig, (whole, br, text) =>
			{
				const id = codeReplacement.length;
				codeReplacement.push(text);
				return '####REPLACEMENT_CODE_'+id+'####';
			});

			text = text.replace(/\[PUT(?:=(?:.+?))?\](?:.+?)?\[\/PUT]/gi, function(match)
			{
				return match.replace(/\[PUT(?:=(.+))?\](.+?)?\[\/PUT]/gi, function(whole, command, text) {
					return  text? text: command;
				});
			});

			text = text.replace(/\[SEND(?:=(?:.+?))?\](?:.+?)?\[\/SEND]/gi, function(match)
			{
				return match.replace(/\[SEND(?:=(.+))?\](.+?)?\[\/SEND]/gi, function(whole, command, text) {
					return  text? text: command;
				});
			});

			text = text.replace(/\[b]([^[]*(?:\[(?!b]|\/b])[^[]*)*)\[\/b]/gi, (whole, text) => text);
			text = text.replace(/\[u]([^[]*(?:\[(?!u]|\/u])[^[]*)*)\[\/u]/gi, (whole, text) => text);
			text = text.replace(/\[i]([^[]*(?:\[(?!i]|\/i])[^[]*)*)\[\/i]/gi, (whole, text) => text);

			text = text.replace(/\[url](.*?)\[\/url]/gis, '$1');
			text = text.replace(/\[RATING=([1-5]{1})]/gi, () => '['+localize['IM_UTILS_TEXT_RATING']+'] ');
			text = text.replace(/\[ATTACH=([0-9]{1,})]/gi, () => '['+localize['IM_UTILS_TEXT_ATTACH']+'] ');
			text = text.replace(/\[USER=([0-9]+)( REPLACE)?](.*?)\[\/USER]/gi, '$3');
			text = text.replace(/\[CHAT=([0-9]{1,})](.*?)\[\/CHAT]/gi, '$2');
			text = text.replace(/\[context=(chat\d+|\d+:\d+)\/(\d+)](.*?)\[\/context]/gis, (whole, dialogId, messageId, message) => message);
			text = text.replace(/\[SEND(?:=(?:.+?))?\](.+?)?\[\/SEND]/gi, '$1');
			text = text.replace(/\[PUT(?:=(?:.+?))?\](.+?)?\[\/PUT]/gi, '$1');
			text = text.replace(/\[CALL=(.*?)](.*?)\[\/CALL\]/gi, '$2');
			text = text.replace(/\[PCH=([0-9]{1,})](.*?)\[\/PCH]/gi, '$2');
			text = text.replace(/\[size=(\d+)](.*?)\[\/size]/gis, '$2');
			text = text.replace(/\[color=#([0-9a-f]{3}|[0-9a-f]{6})](.*?)\[\/color]/gis, '$2');
			text = text.replace(/<img.*?data-code="([^"]*)".*?>/gi, '$1');
			text = text.replace(/<span.*?title="([^"]*)".*?>.*?<\/span>/gi, '($1)');
			text = text.replace(/<img.*?title="([^"]*)".*?>/gi, '($1)');
			text = text.replace(/\[ATTACH=([0-9]{1,})]/gi, (whole, command, text) => command === 10000? '': '['+localize['IM_UTILS_TEXT_ATTACH']+'] ');
			text = text.replace(/<s>([^"]*)<\/s>/gi, ' ');
			text = text.replace(/\[s]([^"]*)\[\/s]/gi, ' ');
			text = text.replace(/\[icon=([^\]]*)]/gi, (whole) =>
			{
				let title = whole.match(/title=(.*[^\s\]])/i);
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
						title = '('+title.trim()+')';
					}
				}
				else
				{
					title = '('+localize['IM_UTILS_TEXT_ICON']+')';
				}
				return title;
			});

			codeReplacement.forEach((element, index) => {
				text = text.replace('####REPLACEMENT_CODE_'+index+'####', element);
			});

			text = text.replace(/------------------------------------------------------(.*?)------------------------------------------------------/gmis, "["+localize["IM_UTILS_TEXT_QUOTE"]+"] ");
			text = text.replace(/^(>>(.*)(\n)?)/gmi, "["+localize["IM_UTILS_TEXT_QUOTE"]+"] ");

			text = text.replace(/<\/?[^>]+>/gi, '');

			if (params && params.FILE_ID && params.FILE_ID.length > 0)
			{
				let filesText = [];

				if (typeof files === 'object')
				{
					params.FILE_ID.forEach(fileId =>
					{
						if (typeof files[fileId] === 'undefined')
						{
						}
						else if (files[fileId].type === 'image')
						{
							filesText.push(localize['IM_UTILS_TEXT_IMAGE']);
						}
						else if (files[fileId].type === 'audio')
						{
							filesText.push(localize['IM_UTILS_TEXT_AUDIO']);
						}
						else if (files[fileId].type === 'video')
						{
							filesText.push(localize['IM_UTILS_TEXT_VIDEO']);
						}
						else
						{
							filesText.push(files[fileId].name);
						}
					});
				}

				if (filesText.length <= 0)
				{
					filesText.push(localize['IM_UTILS_TEXT_FILE']);
				}

				text = filesText.join(' ')+text;
			}
			else if (params && (params.WITH_ATTACH || params.ATTACH && params.ATTACH.length > 0))
			{
				text = '['+localize['IM_UTILS_TEXT_ATTACH']+'] '+text;
			}
			else if (params && params.WITH_FILE)
			{
				text = '['+localize['IM_UTILS_TEXT_FILE']+'] '+text;
			}
			if (text.length <= 0)
			{
				text = localize['IM_UTILS_TEXT_DELETED'];
			}

			return text.replace('\n', ' ').trim();
		},

		decode(text = '', options = {})
		{
			if (!text)
			{
				return text;
			}

			const enableBigSmile = true;

			text = text.toString().trim();
			text = Utils.text.htmlspecialchars(text);

			if (text.startsWith('/me'))
			{
				text = `<i>${text.substr(4)}</i>`;
			}
			else if (text.startsWith('/loud'))
			{
				text = `<b>${text.substr(6)}</b>`;
			}

			const quoteSign = "&gt;&gt;";
			if (text.indexOf(quoteSign) >= 0)
			{
				let textPrepareFlag = false;
				const textPrepare = text.split("\n");
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

			text = text.replace(/\n/gi, '<br />');

			text = text.replace(/\t/gi, '&nbsp;&nbsp;&nbsp;&nbsp;');

			text = this.decodeBbCode(text, enableBigSmile);

			text = text.replace(/------------------------------------------------------<br \/>(.*?)\[(.*?)\](?: #(?:(?:chat)?\d+|\d+:\d+)\/\d+)?<br \/>(.*?)------------------------------------------------------(<br \/>)?/g, function (whole, p1, p2, p3, p4, offset) {
				return (offset > 0? '<br>': '') + "<div class=\"bx-im-message-content-quote\"><div class=\"bx-im-message-content-quote-wrap\"><div class=\"bx-im-message-content-quote-name\"><span class=\"bx-im-message-content-quote-name-text\">" + p1 + "</span><span class=\"bx-im-message-content-quote-name-time\">" + p2 + "</span></div>" + p3 + "</div></div><br />";
			});
			text = text.replace(/------------------------------------------------------<br \/>(.*?)------------------------------------------------------(<br \/>)?/g, function (whole, p1, p2, p3, offset) {
				return (offset > 0? '<br>': '') + "<div class=\"bx-im-message-content-quote\"><div class=\"bx-im-message-content-quote-wrap\">" + p1 + "</div></div><br />";
			});

			if (options.skipImages !== true)
			{

				let changed = false;
				text = text.replace(/(.)?((https|http):\/\/([\S]+)\.(jpg|jpeg|png|gif|webp)(\?[\S]+)?)/gi, function(whole, letter, url, offset)
				{
					if(
						letter && !(['>', ']'].includes(letter))
						|| !url.match(/(\.(jpg|jpeg|png|gif|webp)\?|\.(jpg|jpeg|png|gif|webp)$)/i)
						|| url.toLowerCase().indexOf("/docs/pub/") > 0
						|| url.toLowerCase().indexOf("logout=yes") > 0
					)
					{
						return whole;
					}
					else
					{
						changed = true;
						return (letter? letter: '')+'<span class="bx-im-element-file-image"><img src="'+url+'" class="bx-im-element-file-image-source-text" onerror="Utils.hideErrorImage(this)"></span>';
					}
				});
				if (changed)
				{
					text = text
						.replace(/<\/span>(\n?)<\/a>(\n?)<br(\s\/?)>/gi, '</span></a>')
						.replace(/<\/span>(\n?)(\n?)<br(\s\/?)>/gi, '</span>')
					;
				}

				if (enableBigSmile)
				{
					text = text.replace(
						/^(\s*<img\s+src=[^>]+?data-code=[^>]+?data-definition="UHD"[^>]+?style="width:)(\d+)(px[^>]+?height:)(\d+)(px[^>]+?class="bx-smile"\s*\/?>\s*)$/,
						function doubleSmileSize(match, start, width, middle, height, end) {
							return start + (parseInt(width, 10) * 1.7) + middle + (parseInt(height, 10) * 1.7) + end;
						}
					);
				}
			}

			if (text.substr(-6) == '<br />')
			{
				text = text.substr(0, text.length - 6);
			}
			text = text.replace(/<br><br \/>/gi, '<br />');
			text = text.replace(/<br \/><br>/gi, '<br />');

			return text;
		},

		decodeBbCode(text, enableBigSmile = true)
		{
			const textOnly = false;

			let putReplacement = [];
			text = text.replace(/\[PUT(?:=(.+?))?\](.+?)?\[\/PUT\]/gi, function(whole)
			{
				var id = putReplacement.length;
				putReplacement.push(whole);
				return '####REPLACEMENT_PUT_'+id+'####';
			});

			let sendReplacement = [];
			text = text.replace(/\[SEND(?:=(.+?))?\](.+?)?\[\/SEND\]/gi, function(whole)
			{
				var id = sendReplacement.length;
				sendReplacement.push(whole);
				return '####REPLACEMENT_SEND_'+id+'####';
			});

			let codeReplacement = [];
			text = text.replace(/\[CODE\]\n?(.*?)\[\/CODE\]/gis, function(whole, text) {
				let id = codeReplacement.length;
				codeReplacement.push(text);
				return '####REPLACEMENT_CODE_'+id+'####';
			});

			// base pattern for urls
			text = text.replace(/\[url(?:=([^[\]]+))?](.*?)\[\/url]/gis, (whole, link, text) =>
			{
				const url = Text.decode(link || text);
				if (!Utils.text.checkUrl(url))
				{
					return text;
				}

				return Dom.create({
					tag: 'a',
					attrs: {
						href: url,
						target: "_blank"
					},
					html: text
				}).outerHTML;
			});

			// url like https://bitrix24.com/?params[1]="test"
			text = text.replace(/\[url(?:=(.+?[^[\]]))?](.*?)\[\/url]/gis, (whole, link, text) =>
			{
				let url = Text.decode(link || text);
				if (!Utils.text.checkUrl(url))
				{
					return text;
				}

				if (!url.slice(url.lastIndexOf('[')).includes(']'))
				{
					if (text.startsWith(']'))
					{
						url = `${url}]`;
						text = text.slice(1);
					}
					else if (text.startsWith('='))
					{
						const urlPart = Text.decode(text.slice(1, text.lastIndexOf(']')));
						url = `${url}]=${urlPart}`;
						text = text.slice(text.lastIndexOf(']')+1);
					}
				}

				return Dom.create({
					tag: 'a',
					attrs: {
						href: url,
						target: "_blank"
					},
					html: text
				}).outerHTML;
			});

			text = text.replace(/\[LIKE\]/gi, '<span class="bx-smile bx-im-smile-like"></span>');
			text = text.replace(/\[DISLIKE\]/gi, '<span class="bx-smile bx-im-smile-dislike"></span>');

			text = text.replace(/\[BR\]/gi, '<br/>');

			text = text.replace(/\[b]([^[]*(?:\[(?!b]|\/b])[^[]*)*)\[\/b]/gi, (whole, text) => '<b>'+text+'</b>');
			text = text.replace(/\[u]([^[]*(?:\[(?!u]|\/u])[^[]*)*)\[\/u]/gi, (whole, text) => '<u>'+text+'</u>');
			text = text.replace(/\[i]([^[]*(?:\[(?!i]|\/i])[^[]*)*)\[\/i]/gi, (whole, text) => '<i>'+text+'</i>');
			text = text.replace(/\[s]([^[]*(?:\[(?!s]|\/s])[^[]*)*)\[\/s]/gi, (whole, text) => '<s>'+text+'</s>');

			text = text.replace(/\[size=(\d+)(?:pt|px)?](.*?)\[\/size]/gis, (whole, number, text) => {
				return Dom.create({
					tag: 'span',
					style: { fontSize: number + 'px' },
					html: text
				}).outerHTML;
			});

			text = text.replace(/\[color=#([0-9a-f]{3}|[0-9a-f]{6})](.*?)\[\/color]/gis, (whole, hex, text) => {
				return Dom.create({
					tag: 'span',
					style: { color: '#'+ hex },
					html: text
				}).outerHTML;
			});

			text = text.replace(/\[USER=([0-9]+)( REPLACE)?](.*?)\[\/USER]/gi, (whole, userId, replace, userName) => {
				userId = Number.parseInt(userId, 10);

				if (!Type.isNumber(userId) || userId === 0)
				{
					return userName;
				}

				if (replace || !userName)
				{
					const user = BX.Messenger.Application.Core.controller.store.getters['users/get'](userId);
					if (user)
					{
						userName = Utils.text.htmlspecialchars(user.name);
					}
				}
				else
				{
					userName = Text.decode(userName);
				}

				if (!userName)
				{
					userName = `User ${userId}`;
				}

				return BX.Dom.create({
					tag: 'span',
					attrs: {
						className: 'bx-im-mention',
						'data-type': 'USER',
						'data-value': userId,
					},
					text: userName
				}).outerHTML;
			});

			text = text.replace(/\[RATING\=([1-5]{1})\]/gi, (whole, rating) => {
				// todo: refactor legacy call
				return BX.MessengerCommon.linesVoteHeadNodes(0, rating, false).outerHTML;
			});

			text = text.replace(/\[CHAT=(imol\|)?([0-9]{1,})\](.*?)\[\/CHAT\]/gi, (whole, openlines, chatId, inner) => {
				chatId = parseInt(chatId);

				if (chatId <= 0)
				{
					return inner;
				}

				if (openlines)
				{
					return Dom.create({
						tag: 'span',
						attrs: {
							className: 'bx-im-mention',
							'data-type': 'OPENLINES',
							'data-value': chatId,
						},
						text: inner
					}).outerHTML;
				}

				return Dom.create({
					tag: 'span',
					attrs: {
						className: 'bx-im-mention',
						'data-type': 'CHAT',
						'data-value': chatId,
					},
					text: inner
				}).outerHTML;
			});

			text = text.replace(/\[context=(chat\d+|\d+:\d+)\/(\d+)](.*?)\[\/context]/gis, (whole, dialogId, messageId, message) => {
				return message;
			});

			if (false && Utils.device.isMobile())
			{
				let replacements = [];
				text = text.replace(/\[CALL(?:=(.+?))?\](.+?)?\[\/CALL\]/gi, (whole, number, text) => {
					let index = replacements.length;
					replacements.push({number, text});
					return `####REPLACEMENT_MARK_${index}####`;
				});

				text = text.replace(/[+]{0,1}(?:[-\/. ()\[\]~;#,]*[0-9]){10,}[^\n\r<][-\/. ()\[\]~;#,0-9^]*/g, (number) => {
					let pureNumber = number.replace(/\D/g, '');
					return `[CALL=${pureNumber}]${number}[/CALL]`;
				});

				replacements.forEach((item, index) => {
					text = text.replace(`####REPLACEMENT_MARK_${index}####`, `[CALL=${item.number}]${item.text}[/CALL]`)
				});
			}

			text = text.replace(/\[CALL(?:=(.+?))?\](.+?)?\[\/CALL\]/gi, (whole, number, text) => '<span class="bx-im-mention" data-type="CALL" data-value="'+Utils.text.htmlspecialchars(number)+'">'+text+'</span>'); // TODO tag CHAT

			text = text.replace(/\[PCH=([0-9]{1,})\](.*?)\[\/PCH\]/gi, (whole, historyId, text) => text); // TODO tag PCH

			let textElementSize = 0;
			if (enableBigSmile)
			{
				textElementSize = text.replace(/\[icon\=([^\]]*)\]/gi, '').trim().length;
			}

			text = text.replace(/\[icon\=([^\]]*)\]/gi, (whole) =>
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

				if (enableBigSmile && textElementSize === 0 && attrs['width'] === attrs['height'] && attrs['width'] === 20)
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
						attrs['title'] = Utils.text.htmlspecialchars(title).trim();
						attrs['alt'] = attrs['title'];
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

			sendReplacement.forEach((value, index) => {
				text = text.replace('####REPLACEMENT_SEND_'+index+'####', value);
			});

			text = text.replace(/\[SEND(?:=(?:.+?))?\](?:.+?)?\[\/SEND]/gi, (match) =>
			{
				return match.replace(/\[SEND(?:=(.+))?\](.+?)?\[\/SEND]/gi, (whole, command, text) =>
				{
					let html = '';

					text = text? text: command;
					command = (command? command: text).replace('<br />', '\n');

					if (!textOnly && text)
					{
						text = text.replace(/<([\w]+)[^>]*>(.*?)<\\1>/i, "$2", text);
						text = text.replace(/\[([\w]+)[^\]]*\](.*?)\[\/\1\]/i, "$2", text);

						command = command.split('####REPLACEMENT_PUT_').join('####REPLACEMENT_SP_');

						html = '<!--IM_COMMAND_START-->' +
							'<span class="bx-im-message-command-wrap">'+
							'<span class="bx-im-message-command" data-entity="send">'+text+'</span>'+
							'<span class="bx-im-message-command-data">'+command+'</span>'+
							'</span>'+
							'<!--IM_COMMAND_END-->';
					}
					else
					{
						html = text;
					}

					return html;
				});
			});

			putReplacement.forEach((value, index) => {
				text = text.replace('####REPLACEMENT_PUT_'+index+'####', value);
			});

			text = text.replace(/\[PUT(?:=(?:.+?))?\](?:.+?)?\[\/PUT]/gi, (match) =>
			{
				return match.replace(/\[PUT(?:=(.+))?\](.+?)?\[\/PUT]/gi, (whole, command, text) =>
				{
					let html = '';

					text = text? text: command;
					command = (command? command: text).replace('<br />', '\n');

					if (!textOnly && text)
					{
						text = text.replace(/<([\w]+)[^>]*>(.*?)<\/\1>/i, "$2", text);
						text = text.replace(/\[([\w]+)[^\]]*\](.*?)\[\/\1\]/i, "$2", text);

						html = '<!--IM_COMMAND_START-->' +
							'<span class="bx-im-message-command-wrap">'+
							'<span class="bx-im-message-command" data-entity="put">'+text+'</span>'+
							'<span class="bx-im-message-command-data">'+command+'</span>'+
							'</span>'+
							'<!--IM_COMMAND_END-->';
					}
					else
					{
						html = text;
					}

					return html;
				});
			});

			codeReplacement.forEach((code, index) => {
				text = text.replace('####REPLACEMENT_CODE_'+index+'####',
					!textOnly? '<div class="bx-im-message-content-code">'+code+'</div>': code
				)
			});

			if (sendReplacement.length > 0)
			{
				do
				{
					sendReplacement.forEach((value, index) => {
						text = text.replace('####REPLACEMENT_SEND_'+index+'####', value);
					});
				}
				while (text.includes('####REPLACEMENT_SEND_'));
			}

			text = text.split('####REPLACEMENT_SP_').join('####REPLACEMENT_PUT_');

			if (putReplacement.length > 0)
			{
				do
				{
					putReplacement.forEach((value, index) => {
						text = text.replace('####REPLACEMENT_PUT_'+index+'####', value);
					});
				}
				while (text.includes('####REPLACEMENT_PUT_'));
			}

			return text;
		},

		checkUrl(url): boolean
		{
			const allowList = [
				"http:",
				"https:",
				"ftp:",
				"file:",
				"tel:",
				"callto:",
				"mailto:",
				"skype:",
				"viber:",
			];

			const checkCorrectStartLink = ['/', ...allowList].find(protocol => {
				return url.startsWith(protocol);
			});
			if (!checkCorrectStartLink)
			{
				return false;
			}

			const element = Dom.create({ tag: 'a', attrs: { href: url }});

			return allowList.indexOf(element.protocol) > -1;
		},

		htmlspecialchars(text)
		{
			if (typeof text !== 'string')
			{
				return text;
			}

			return text.replace(/&/g, '&amp;')
				.replace(/"/g, '&quot;')
				.replace(/</g, '&lt;')
				.replace(/>/g, '&gt;');
		},

		htmlspecialcharsback(text)
		{
			if (typeof text !== 'string')
			{
				return text;
			}

			return text.replace(/\&quot;/g, '"')
				.replace(/&#039;/g, "'")
				.replace(/\&lt;/g, '<')
				.replace(/\&gt;/g, '>')
				.replace(/\&amp;/g, '&')
				.replace(/\&nbsp;/g, ' ');
		},

		getLocalizeForNumber(phrase, number, language = 'en', localize = null)
		{
			if (!localize)
			{
				localize = BX.message;
			}

			let pluralFormType = 1;

			number = parseInt(number);

			if (number < 0)
			{
				number = number * -1;
			}

			if (language)
			{
				switch (language)
				{
					case 'de':
					case 'en':
						pluralFormType = ((number !== 1) ? 1 : 0);
					break;

					case 'ru':
					case 'ua':
						pluralFormType = (((number%10 === 1) && (number%100 !== 11)) ? 0 : (((number%10 >= 2) && (number%10 <= 4) && ((number%100 < 10) || (number%100 >= 20))) ? 1 : 2));
					break;
				}
			}

			return localize[phrase + '_PLURAL_' + pluralFormType];
		}
	},

	date:
	{
		getFormatType(type = DateFormat.default, localize = null)
		{
			if (!localize)
			{
				localize = BX.message;
			}

			let format = [];
			if (type === DateFormat.groupTitle)
			{
				format = [
					["tommorow", "tommorow"],
					["today", "today"],
					["yesterday", "yesterday"],
					["", localize["IM_UTILS_FORMAT_DATE"]]
				];
			}
			else if (type === DateFormat.message)
			{
				format = [
					["", localize["IM_UTILS_FORMAT_TIME"]]
				];
			}
			else if (type === DateFormat.recentTitle)
			{
				format = [
					["tommorow", "today"],
					["today", "today"],
					["yesterday", "yesterday"],
					["", localize["IM_UTILS_FORMAT_DATE_RECENT"]]
				]
			}
			else if (type === DateFormat.recentLinesTitle)
			{
				format = [
					["tommorow", "tommorow"],
					["today", "today"],
					["yesterday", "yesterday"],
					["", localize["IM_UTILS_FORMAT_DATE_RECENT"]]
				]
			}
			else if (type === DateFormat.readedTitle)
			{
				format = [
					["tommorow", "tommorow, "+localize["IM_UTILS_FORMAT_TIME"]],
					["today", "today, "+localize["IM_UTILS_FORMAT_TIME"]],
					["yesterday", "yesterday, "+localize["IM_UTILS_FORMAT_TIME"]],
					["", localize["IM_UTILS_FORMAT_READED"]]
				];
			}
			else if (type === DateFormat.vacationTitle)
			{
				format = [
					["", localize["IM_UTILS_FORMAT_DATE_SHORT"]]
				];
			}
			else
			{
				format = [
					["tommorow", "tommorow, "+localize["IM_UTILS_FORMAT_TIME"]],
					["today", "today, "+localize["IM_UTILS_FORMAT_TIME"]],
					["yesterday", "yesterday, "+localize["IM_UTILS_FORMAT_TIME"]],
					["", localize["IM_UTILS_FORMAT_DATE_TIME"]]
				];
			}

			return format;
		},

		getDateFunction(localize = null)
		{
			if (this.dateFormatFunction)
			{
				return this.dateFormatFunction;
			}

			this.dateFormatFunction = Object.create(BX.Main.Date);
			if (localize)
			{
				this.dateFormatFunction._getMessage = (phrase) => localize[phrase];
			}

			return this.dateFormatFunction;
		},

		format(timestamp, format = null, localize = null)
		{
			if (!format)
			{
				format = this.getFormatType(DateFormat.default, localize);
			}

			return this.getDateFunction(localize).format(format, timestamp);
		},

		cast(date, def = new Date())
		{
			let result = def;

			if (date instanceof Date)
			{
				result = date;
			}
			else if (typeof date === "string")
			{
				result = new Date(date);
			}
			else if (typeof date === "number")
			{
				result = new Date(date*1000);
			}

			if (
				result instanceof Date
				&& Number.isNaN(result.getTime())
			)
			{
				result = def;
			}

			return result;
		},
	},

	object:
	{
		countKeys(obj)
		{
			let result = 0;

			for (let i in obj)
			{
				if (obj.hasOwnProperty(i))
				{
					result++;
				}
			}

			return result;
		},
	},

	user:
	{
		getLastDateText(params, localize = null)
		{
			if (!params)
			{
				return '';
			}

			let dateFunction = Utils.date.getDateFunction(localize);

			if (!localize)
			{
				localize = BX.message || {};
			}

			let text = '';
			let online = {};
			if (params.bot || params.network)
			{
				text = '';
			}
			else if (params.absent && !this.isMobileActive(params, localize))
			{
				online = this.getOnlineStatus(params, localize);
				text = localize['IM_STATUS_VACATION_TITLE'].replace('#DATE#',
					dateFunction.format(Utils.date.getFormatType(DateFormat.vacationTitle, localize), params.absent.getTime()/1000)
				);

				if (online.isOnline && params.idle)
				{
					 text = localize['IM_STATUS_AWAY_TITLE'].replace('#TIME#', this.getIdleText(params, localize))+'. '+text;
				}
				else if (online.isOnline && !online.lastSeenText)
				{
					text = online.statusText+'. '+text;
				}
				else if (online.lastSeenText)
				{
					if (!Utils.platform.isMobile())
					{
						text = text+'. '+localize['IM_LAST_SEEN_'+(params.gender === 'F'? 'F': 'M')].replace('#POSITION#', text).replace('#LAST_SEEN#', online.lastSeenText);
					}
				}
			}
			else if (params.lastActivityDate)
			{
				online = this.getOnlineStatus(params, localize);
				if (online.isOnline && params.idle && !this.isMobileActive(params, localize))
				{
					 text = localize['IM_STATUS_AWAY_TITLE'].replace('#TIME#', this.getIdleText(params, localize));
				}
				else if (online.isOnline && !online.lastSeenText)
				{
					if (Utils.platform.isMobile() && this.isMobileActive(params, localize))
					{
						text = localize['IM_STATUS_MOBILE'];
					}
					else
					{
						text = online.statusText;
					}
				}
				else if (online.lastSeenText)
				{
					if (Utils.platform.isMobile())
					{
						text = localize['IM_LAST_SEEN_SHORT_'+(params.gender === 'F'? 'F': 'M')].replace('#LAST_SEEN#', online.lastSeenText);
					}
					else
					{
						text = localize['IM_LAST_SEEN_'+(params.gender === 'F'? 'F': 'M')].replace('#POSITION#', text).replace('#LAST_SEEN#', online.lastSeenText);
					}
				}
			}

			return text;
		},

		getIdleText(params, localize = null)
		{
			if (!params)
			{
				return '';
			}

			if (!params.idle)
			{
				return '';
			}

			return Utils.date.getDateFunction(localize).format([
			   ["s60", "sdiff"],
			   ["i60", "idiff"],
			   ["H24", "Hdiff"],
			   ["", "ddiff"]
			], params.idle);
		},

		getOnlineStatus(params, localize = null)
		{
			let result = {
				'isOnline': false,
				'status': 'offline',
				'statusText': localize? localize.IM_STATUS_OFFLINE: 'offline',
				'lastSeen': params.lastActivityDate,
				'lastSeenText': '',
			};

			if (!params.lastActivityDate || params.lastActivityDate.getTime() === 0)
			{
				return result;
			}

			let date = new Date();

			result.isOnline = date.getTime() - params.lastActivityDate.getTime() <= this.getOnlineLimit(localize)*1000;
			result.status = result.isOnline? params.status: 'offline';
			result.statusText = localize && localize['IM_STATUS_'+result.status.toUpperCase()]? localize['IM_STATUS_'+result.status.toUpperCase()]: result.status;

			if (localize && params.lastActivityDate.getTime() > 0 && date.getTime() - params.lastActivityDate.getTime() > 300*1000)
			{
				result.lastSeenText = Utils.date.getDateFunction(localize).formatLastActivityDate(params.lastActivityDate);
			}

			return result;
		},

		isMobileActive(params, localize = null)
		{
			if (!params)
			{
				return false;
			}

			if (!localize)
			{
				localize = BX.message || {};
			}

			return (
				params.mobileLastDate
				&& new Date() - params.mobileLastDate < this.getOnlineLimit(localize)*1000
				&& params.lastActivityDate-params.mobileLastDate < 300*1000
			);
		},

		getOnlineLimit(localize = null)
		{
			if (!localize)
			{
				localize = BX.message || {};
			}

			return localize.LIMIT_ONLINE? parseInt(localize.LIMIT_ONLINE): 15*60;
		},
	},

	isDarkColor(hex)
	{
		if (!hex || !hex.match(/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/))
		{
			return false;
		}

		if (hex.length === 4)
		{
			hex = hex.replace(/#([A-Fa-f0-9])/gi, "$1$1");
		}
		else
		{
			hex = hex.replace(/#([A-Fa-f0-9])/gi, "$1");
		}

		hex = hex.toLowerCase();

		let darkColor = [
			"#17a3ea",
			"#00aeef",
			"#00c4fb",
			"#47d1e2",
			"#75d900",
			"#ffab00",
			"#ff5752",
			"#468ee5",
			"#1eae43"
		];

		if (darkColor.includes('#'+hex))
		{
			return true;
		}

		let bigint = parseInt(hex, 16);

		let red = (bigint >> 16) & 255;
		let green = (bigint >> 8) & 255;
		let blue = bigint & 255;

		let brightness = (red * 299 + green * 587 + blue * 114) / 1000;

		return brightness < 128;
	},

	hashCode(string = '')
	{
		let hash = 0;

		if (typeof string === 'object' && string)
		{
			string = JSON.stringify(string);
		}
		else if (typeof string !== 'string')
		{
			string = string.toString();
		}

		if (typeof string !== 'string')
		{
			return hash;
		}

		for (let i = 0; i < string.length; i++)
		{
			let char = string.charCodeAt(i);
			hash = ((hash<<5)-hash)+char;
			hash = hash & hash;
		}
		return hash;
	},

	hideErrorImage(element)
	{
		if (element.parentNode)
		{
			element.parentNode.innerHTML = '<a href="'+encodeURI(element.src)+'" target="_blank">'+element.src+'</a>';
		}
		return true;
	},

	/**
	 * The method compares versions, and returns - 0 if they are the same, 1 if version1 is greater, -1 if version1 is less
	 *
	 * @param version1
	 * @param version2
	 * @returns {number|NaN}
	 */
	versionCompare(version1, version2)
	{
		let isNumberRegExp = /^([\d+\.]+)$/;

		if (
			!isNumberRegExp.test(version1)
			|| !isNumberRegExp.test(version2)
		)
		{
			return NaN;
		}

		version1 = version1.toString().split('.');
		version2 = version2.toString().split('.');

		if (version1.length < version2.length)
		{
			while (version1.length < version2.length)
			{
				version1.push(0);
			}
		}
		else if (version2.length < version1.length)
		{
			while (version2.length < version1.length)
			{
				version2.push(0);
			}
		}

		for (var i = 0; i < version1.length; i++)
		{
			if (version1[i] > version2[i])
			{
				return 1;
			}
			else if (version1[i] < version2[i])
			{
				return -1;
			}
		}

		return 0;
	},

	/**
	 * Throttle function. Callback will be executed no more than 'wait' period (in ms).
	 *
	 * @param callback
	 * @param wait
	 * @param context
	 * @returns {Function}
	 */
	throttle(callback, wait, context = this)
	{
		let timeout = null;
		let callbackArgs = null;

		const nextCallback = () => {
			callback.apply(context, callbackArgs);
			timeout = null;
		};

		return function()
		{
			if (!timeout)
			{
				callbackArgs = arguments;
				timeout = setTimeout(nextCallback, wait);
			}
		}
	},

	/**
	 * Debounce function. Callback will be executed if it hast been called for longer than 'wait' period (in ms).
	 *
	 * @param callback
	 * @param wait
	 * @param context
	 * @returns {Function}
	 */
	debounce(callback, wait, context = this)
	{
		let timeout = null;
		let callbackArgs = null;

		const nextCallback = () => {
			callback.apply(context, callbackArgs);
		};

		return function()
		{
			callbackArgs = arguments;

			clearTimeout(timeout);
			timeout = setTimeout(nextCallback, wait);
		}
	},

	getLogTrackingParams(params = {}): string
	{
		let result = [];

		let {
			name = 'tracking',
			data = [],
			dialog = null,
			message = null,
			files = null,
		} = params;

		name = encodeURIComponent(name);

		if (
			data
			&& !(data instanceof Array)
			&& typeof data === 'object'
		)
		{
			let dataArray = [];
			for (let name in data)
			{
				if (data.hasOwnProperty(name))
				{
					dataArray.push(encodeURIComponent(name)+"="+encodeURIComponent(data[name]));
				}
			}
			data = dataArray;
		}
		else if (!data instanceof Array)
		{
			data = [];
		}

		if (dialog)
		{
			result.push('timType='+dialog.type);

			if (dialog.type === 'lines')
			{
				result.push('timLinesType='+dialog.entityId.split('|')[0]);
			}
		}

		if (files)
		{
			let type = 'file';
			if (files instanceof Array && files[0])
			{
				type = files[0].type;
			}
			else
			{
				type = files.type;
			}
			result.push('timMessageType='+type);
		}
		else if (message)
		{
			result.push('timMessageType=text');
		}

		if (this.platform.isBitrixMobile())
		{
			result.push('timDevice=bitrixMobile');
		}
		else if (this.platform.isBitrixDesktop())
		{
			result.push('timDevice=bitrixDesktop');
		}
		else if (this.platform.isIos() || this.platform.isAndroid())
		{
			result.push('timDevice=mobile');
		}
		else
		{
			result.push('timDevice=web');
		}

		return name + (data.length? '&'+data.join('&'): '') + (result.length? '&'+result.join('&'): '');
	}
};

export {Utils};
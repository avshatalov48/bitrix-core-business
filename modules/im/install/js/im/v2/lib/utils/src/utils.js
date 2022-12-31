import {DateFormat} from 'im.v2.const';
import {Loc, Type} from 'main.core';
import 'main.date';

const Utils = {
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
			);
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
			const isHtmlElement = findTag instanceof HTMLElement;

			if (
				!findTag
				|| (typeof findTag !== 'string' && !isHtmlElement)
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
				else if (isHtmlElement && item === findTag)
				{
					return item;
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
		isDesktopFeatureEnabled(code: string)
		{
			if (!this.isBitrixDesktop() || !Type.isFunction(BXDesktopSystem.FeatureEnabled))
			{
				return false;
			}

			return !!BXDesktopSystem.FeatureEnabled(code);
		},
		isMobile(): boolean
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

			text = text.replace(/\[USER=([0-9]{1,})](.*?)\[\/USER]/ig, (whole, userId, text) => text);
			text = text.replace(/\[CHAT=(imol\|)?([0-9]{1,})](.*?)[\/CHAT]/ig, (whole, imol, chatId, text) => text);
			text = text.replace(/\[CALL(?:=(.+?))?](.+?)?\[\/CALL]/ig, (whole, command, text) => text? text: command);
			text = text.replace(/\[ATTACH=([0-9]{1,})]/ig, (whole, command, text) => command === 10000? '': '['+localize['IM_UTILS_TEXT_ATTACH']+'] ');
			text = text.replace(/\[RATING=([1-5]{1})]/ig, (whole, rating) => '['+localize.IM_F_RATING+'] ');
			text = text.replace(/&nbsp;/ig, " ");

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

			text = text.replace(/<br><br \/>/ig, '<br />');
			text = text.replace(/<br \/><br>/ig, '<br />');

			const codeReplacement = [];
			text = text.replace(/\[CODE\]\n?([\0-\uFFFF]*?)\[\/CODE\]/ig, function(whole,text)
			{
				const id = codeReplacement.length;
				codeReplacement.push(text);
				return '####REPLACEMENT_CODE_'+id+'####';
			});

			text = text.replace(/\[PUT(?:=(?:.+?))?\](?:.+?)?\[\/PUT]/ig, function(match)
			{
				return match.replace(/\[PUT(?:=(.+))?\](.+?)?\[\/PUT]/ig, function(whole, command, text) {
					return  text? text: command;
				});
			});

			text = text.replace(/\[SEND(?:=(?:.+?))?\](?:.+?)?\[\/SEND]/ig, function(match)
			{
				return match.replace(/\[SEND(?:=(.+))?\](.+?)?\[\/SEND]/ig, function(whole, command, text) {
					return  text? text: command;
				});
			});

			text = text.replace(/\[[buis]](.*?)\[\/[buis]]/ig, '$1');
			text = text.replace(/\[url](.*?)\[\/url]/ig, '$1');
			text = text.replace(/\[RATING=([1-5]{1})]/ig, () => '['+localize['IM_UTILS_TEXT_RATING']+'] ');
			text = text.replace(/\[ATTACH=([0-9]{1,})]/ig, () => '['+localize['IM_UTILS_TEXT_ATTACH']+'] ');
			text = text.replace(/\[USER=([0-9]{1,})](.*?)\[\/USER]/ig, '$2');
			text = text.replace(/\[CHAT=([0-9]{1,})](.*?)\[\/CHAT]/ig, '$2');
			text = text.replace(/\[SEND(?:=(?:.+?))?\](.+?)?\[\/SEND]/ig, '$1');
			text = text.replace(/\[PUT(?:=(?:.+?))?\](.+?)?\[\/PUT]/ig, '$1');
			text = text.replace(/\[CALL=(.*?)](.*?)\[\/CALL\]/ig, '$2');
			text = text.replace(/\[PCH=([0-9]{1,})](.*?)\[\/PCH]/ig, '$2');
			text = text.replace(/<img.*?data-code="([^"]*)".*?>/ig, '$1');
			text = text.replace(/<span.*?title="([^"]*)".*?>.*?<\/span>/ig, '($1)');
			text = text.replace(/<img.*?title="([^"]*)".*?>/ig, '($1)');
			text = text.replace(/\[ATTACH=([0-9]{1,})]/ig, (whole, command, text) => command === 10000? '': '['+localize['IM_UTILS_TEXT_ATTACH']+'] ');
			text = text.replace(/<s>([^"]*)<\/s>/ig, ' ');
			text = text.replace(/\[s]([^"]*)\[\/s]/ig, ' ');
			text = text.replace(/\[icon=([^\]]*)]/ig, (whole) =>
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
		},

		getFirstLetters(text): string
		{
			const validSymbolsPattern = /[\p{L}\p{N} ]/u;
			const words = text.split(/[\s,]/).filter(word => {
				const firstLetter = word.charAt(0);
				return validSymbolsPattern.test(firstLetter);
			});
			if (words.length === 0)
			{
				return '';
			}
			if (words.length > 1)
			{
				return words[0].charAt(0) + words[1].charAt(0);
			}

			return words[0].charAt(0);
		},

		convertSnakeToCamelCase(text: string): string
		{
			return text.replace(/(_[a-z])/gi, ($1) => {
				return $1.toUpperCase().replace('_', '');
			});
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
			else if (typeof date === 'string')
			{
				result = new Date(date);
			}
			else if (typeof date === 'number')
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

		getTimeToNextMidnight(): number
		{
			const nextMidnight = new Date(new Date().setHours(24, 0, 0)).getTime();
			return nextMidnight - Date.now();
		},

		getStartOfTheDay(): Date
		{
			return new Date((new Date()).setHours(0, 0));
		},

		isToday(date)
		{
			return this.cast(date).toDateString() === (new Date()).toDateString();
		}
	},

	user:
	{
		getLastDateText(params = {}): string
		{
			if (params.bot || params.network || !params.lastActivityDate)
			{
				return '';
			}

			const isOnline = this.isOnline(params.lastActivityDate);
			const isMobileOnline = this.isMobileOnline(params.lastActivityDate, params.mobileLastDate);

			let text = '';

			// "away for X minutes"
			if (isOnline && params.idle && !isMobileOnline)
			{
				text = Loc.getMessage('IM_STATUS_AWAY_TITLE').replace('#TIME#', this.getIdleText(params.idle));
			}

			const lastSeenText = this.getLastSeenText(params.lastActivityDate);
			// truly online, last activity date < 5 minutes ago - show status text
			if (isOnline && !lastSeenText)
			{
				text = this.getStatusText(params.status);
			}

			// last activity date > 5 minutes ago - "Was online X minutes ago"
			if (lastSeenText)
			{
				const phraseCode = `IM_LAST_SEEN_${params.gender}`;
				text = Loc.getMessage(phraseCode).replace('#POSITION#. ', '').replace('#LAST_SEEN#', lastSeenText);
			}

			// if on vacation - add postfix with vacation info
			if (params.absent)
			{
				const dateFunction = Utils.date.getDateFunction();
				const vacationFormat = Utils.date.getFormatType(DateFormat.vacationTitle);
				const vacationText = Loc.getMessage('IM_STATUS_VACATION_TITLE').replace('#DATE#',
					dateFunction.format(vacationFormat, params.absent.getTime() / 1000)
				);

				text = text ? `${text}. ${vacationText}`: vacationText;
			}

			return text;
		},

		getIdleText(idle = '')
		{
			if (!idle)
			{
				return '';
			}

			return Utils.date.getDateFunction().format([
				['s60', 'sdiff'],
				['i60', 'idiff'],
				['H24', 'Hdiff'],
				['', 'ddiff']
			], idle);
		},

		isOnline(lastActivityDate): boolean
		{
			if (!lastActivityDate)
			{
				return false;
			}

			return Date.now() - lastActivityDate.getTime() <= this.getOnlineLimit() * 1000;
		},

		isMobileOnline(lastActivityDate, mobileLastDate): boolean
		{
			if (!lastActivityDate || !mobileLastDate)
			{
				return false;
			}

			const FIVE_MINUTES = 5 * 60 * 1000;
			return (
				Date.now() - mobileLastDate.getTime() < this.getOnlineLimit() * 1000
				&& lastActivityDate - mobileLastDate < FIVE_MINUTES
			);
		},

		getStatusText(status: string): string
		{
			const localize = BX.message || {};
			status = status.toUpperCase();
			const phraseCode = `IM_STATUS_${status}`;

			return localize[phraseCode] ?? status;
		},

		getLastSeenText(lastActivityDate): string
		{
			if (!lastActivityDate)
			{
				return '';
			}

			const FIVE_MINUTES = 5 * 60 * 1000;
			if (Date.now() - lastActivityDate.getTime() > FIVE_MINUTES)
			{
				return Utils.date.getDateFunction().formatLastActivityDate(lastActivityDate);
			}

			return '';
		},

		isBirthdayToday(birthday): boolean
		{
			return birthday === Utils.date.format(new Date(), 'd-m');
		},

		getOnlineLimit()
		{
			const localize = BX.message || {};

			const FIFTEEN_MINUTES = 15 * 60;
			return localize.LIMIT_ONLINE? Number.parseInt(localize.LIMIT_ONLINE, 10): FIFTEEN_MINUTES;
		},
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
	},
	types:
	{
		isUuidV4(uuid)
		{
			if (typeof uuid !== 'string')
			{
				return false;
			}

			const uuidV4pattern = new RegExp(/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i);

			return uuid.search(uuidV4pattern) === 0;
		},
	},
};

export {Utils};
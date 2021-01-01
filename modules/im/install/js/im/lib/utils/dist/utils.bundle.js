this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
(function (exports,im_const) {
	'use strict';

	/**
	 * Bitrix Messenger
	 * Utils
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2019 Bitrix
	 */
	var Utils = {
	  browser: {
	    isSafari: function isSafari() {
	      if (this.isChrome()) {
	        return false;
	      }

	      if (!navigator.userAgent.toLowerCase().includes('safari')) {
	        return false;
	      }

	      return !this.isSafariBased();
	    },
	    isSafariBased: function isSafariBased() {
	      if (!navigator.userAgent.toLowerCase().includes('applewebkit')) {
	        return false;
	      }

	      return navigator.userAgent.toLowerCase().includes('yabrowser') || navigator.userAgent.toLowerCase().includes('yaapp_ios_browser') || navigator.userAgent.toLowerCase().includes('crios');
	    },
	    isChrome: function isChrome() {
	      return navigator.userAgent.toLowerCase().includes('chrome');
	    },
	    isFirefox: function isFirefox() {
	      return navigator.userAgent.toLowerCase().includes('firefox');
	    },
	    isIe: function isIe() {
	      return navigator.userAgent.match(/(Trident\/|MSIE\/)/) !== null;
	    },
	    findParent: function findParent(item, findTag) {
	      var isHtmlElement = findTag instanceof HTMLElement;

	      if (!findTag || typeof findTag !== 'string' && !isHtmlElement) {
	        return null;
	      }

	      for (; item && item !== document; item = item.parentNode) {
	        if (typeof findTag === 'string') {
	          if (item.classList.contains(findTag)) {
	            return item;
	          }
	        } else if (isHtmlElement) {
	          if (item === findTag) {
	            return item;
	          }
	        }
	      }

	      return null;
	    }
	  },
	  platform: {
	    isMac: function isMac() {
	      return navigator.userAgent.toLowerCase().includes('macintosh');
	    },
	    isLinux: function isLinux() {
	      return navigator.userAgent.toLowerCase().includes('linux');
	    },
	    isWindows: function isWindows() {
	      return navigator.userAgent.toLowerCase().includes('windows') || !this.isMac() && !this.isLinux();
	    },
	    isBitrixMobile: function isBitrixMobile() {
	      return navigator.userAgent.toLowerCase().includes('bitrixmobile');
	    },
	    isBitrixDesktop: function isBitrixDesktop() {
	      return navigator.userAgent.toLowerCase().includes('bitrixdesktop');
	    },
	    getDesktopVersion: function getDesktopVersion() {
	      if (typeof this.getDesktopVersionStatic !== 'undefined') {
	        return this.getDesktopVersionStatic;
	      }

	      if (typeof BXDesktopSystem === 'undefined') {
	        return 0;
	      }

	      var version = BXDesktopSystem.GetProperty('versionParts');
	      this.getDesktopVersionStatic = version[3];
	      return this.getDesktopVersionStatic;
	    },
	    isMobile: function isMobile() {
	      return this.isAndroid() || this.isIos() || this.isBitrixMobile();
	    },
	    isIos: function isIos() {
	      return navigator.userAgent.toLowerCase().includes('iphone') || navigator.userAgent.toLowerCase().includes('ipad');
	    },
	    getIosVersion: function getIosVersion() {
	      if (!this.isIos()) {
	        return null;
	      }

	      var matches = navigator.userAgent.toLowerCase().match(/(iphone|ipad)(.+)(OS\s([0-9]+)([_.]([0-9]+))?)/i);

	      if (!matches || !matches[4]) {
	        return null;
	      }

	      return parseFloat(matches[4] + '.' + (matches[6] ? matches[6] : 0));
	    },
	    isAndroid: function isAndroid() {
	      return navigator.userAgent.toLowerCase().includes('android');
	    },
	    openNewPage: function openNewPage(url) {
	      if (!url) {
	        return false;
	      }

	      if (this.isBitrixMobile()) {
	        if (typeof BX.MobileTools !== 'undefined') {
	          var openWidget = BX.MobileTools.resolveOpenFunction(url);

	          if (openWidget) {
	            openWidget();
	            return true;
	          }
	        }

	        app.openNewPage(url);
	      } else {
	        window.open(url, '_blank');
	      }

	      return true;
	    }
	  },
	  device: {
	    isDesktop: function isDesktop() {
	      return !this.isMobile();
	    },
	    isMobile: function isMobile() {
	      if (typeof this.isMobileStatic !== 'undefined') {
	        return this.isMobileStatic;
	      }

	      this.isMobileStatic = navigator.userAgent.toLowerCase().includes('android') || navigator.userAgent.toLowerCase().includes('webos') || navigator.userAgent.toLowerCase().includes('iphone') || navigator.userAgent.toLowerCase().includes('ipad') || navigator.userAgent.toLowerCase().includes('ipod') || navigator.userAgent.toLowerCase().includes('blackberry') || navigator.userAgent.toLowerCase().includes('windows phone');
	      return this.isMobileStatic;
	    },
	    orientationHorizontal: 'horizontal',
	    orientationPortrait: 'portrait',
	    getOrientation: function getOrientation() {
	      if (!this.isMobile()) {
	        return this.orientationHorizontal;
	      }

	      return Math.abs(window.orientation) === 0 ? this.orientationPortrait : this.orientationHorizontal;
	    }
	  },
	  types: {
	    isString: function isString(item) {
	      return item === '' ? true : item ? typeof item == "string" || item instanceof String : false;
	    },
	    isArray: function isArray(item) {
	      return item && Object.prototype.toString.call(item) == "[object Array]";
	    },
	    isFunction: function isFunction(item) {
	      return item === null ? false : typeof item == "function" || item instanceof Function;
	    },
	    isDomNode: function isDomNode(item) {
	      return item && babelHelpers.typeof(item) == "object" && "nodeType" in item;
	    },
	    isDate: function isDate(item) {
	      return item && Object.prototype.toString.call(item) == "[object Date]";
	    },
	    isPlainObject: function isPlainObject(item) {
	      if (!item || babelHelpers.typeof(item) !== "object" || item.nodeType) {
	        return false;
	      }

	      var hasProp = Object.prototype.hasOwnProperty;

	      try {
	        if (item.constructor && !hasProp.call(item, "constructor") && !hasProp.call(item.constructor.prototype, "isPrototypeOf")) {
	          return false;
	        }
	      } catch (e) {
	        return false;
	      }

	      var key;

	      return typeof key === "undefined" || hasProp.call(item, key);
	    }
	  },
	  dialog: {
	    getChatIdByDialogId: function getChatIdByDialogId(dialogId) {
	      if (!this.isChatId(dialogId)) {
	        return 0;
	      }

	      return parseInt(dialogId.toString().substr(4));
	    },
	    isChatId: function isChatId(dialogId) {
	      return dialogId.toString().startsWith('chat');
	    },
	    isEmptyDialogId: function isEmptyDialogId(dialogId) {
	      if (!dialogId) {
	        return true;
	      }

	      if (typeof dialogId === "string") {
	        if (dialogId === 'chat0' || dialogId === "0") {
	          return true;
	        }
	      }

	      return false;
	    }
	  },
	  text: {
	    quote: function quote(text, params) {
	      var files = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
	      var localize = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : null;

	      if (typeof text !== 'string') {
	        return text.toString();
	      }

	      if (!localize) {
	        localize = BX.message;
	      }

	      text = text.replace(/\[USER=([0-9]{1,})](.*?)\[\/USER]/ig, function (whole, userId, text) {
	        return text;
	      });
	      text = text.replace(/\[CHAT=(imol\|)?([0-9]{1,})](.*?)[\/CHAT]/ig, function (whole, imol, chatId, text) {
	        return text;
	      });
	      text = text.replace(/\[SEND(?:=(.+?))?](.+?)?\[\/SEND]/ig, function (whole, command, text) {
	        return text ? text : command;
	      });
	      text = text.replace(/\[PUT(?:=(.+?))?](.+?)?\[\/PUT]/ig, function (whole, command, text) {
	        return text ? text : command;
	      });
	      text = text.replace(/\[CALL(?:=(.+?))?](.+?)?\[\/CALL]/ig, function (whole, command, text) {
	        return text ? text : command;
	      });
	      text = text.replace(/\[ATTACH=([0-9]{1,})]/ig, function (whole, command, text) {
	        return command === 10000 ? '' : '[' + localize['IM_UTILS_TEXT_ATTACH'] + '] ';
	      });
	      text = text.replace(/\[RATING=([1-5]{1})]/ig, function (whole, rating) {
	        return '[' + localize.IM_F_RATING + '] ';
	      });
	      text = text.replace(/&nbsp;/ig, " ");
	      text = text.replace(/\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D([\s\S]*?)\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D/gmi, "[" + localize["IM_UTILS_TEXT_QUOTE"] + "]");
	      text = text.replace(/^(>>(.*)\n)/gi, "[" + localize["IM_UTILS_TEXT_QUOTE"] + "]\n");

	      if (params && params.FILE_ID && params.FILE_ID.length > 0) {
	        var filesText = [];
	        params.FILE_ID.forEach(function (fileId) {
	          if (files[fileId].type === 'image') {
	            filesText.push(localize['IM_UTILS_TEXT_IMAGE']);
	          } else if (files[fileId].type === 'audio') {
	            filesText.push(localize['IM_UTILS_TEXT_AUDIO']);
	          } else if (files[fileId].type === 'video') {
	            filesText.push(localize['IM_UTILS_TEXT_VIDEO']);
	          } else {
	            filesText.push(files[fileId].name);
	          }
	        });

	        if (filesText.length <= 0) {
	          filesText.push(localize['IM_UTILS_TEXT_FILE']);
	        }

	        text = filesText.join('\n') + text;
	      } else if (params && params.ATTACH && params.ATTACH.length > 0) {
	        text = '[' + localize['IM_UTILS_TEXT_ATTACH'] + ']\n' + text;
	      }

	      if (text.length <= 0) {
	        text = localize['IM_UTILS_TEXT_DELETED'];
	      }

	      return text.trim();
	    },
	    purify: function purify(text, params) {
	      var files = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
	      var localize = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : null;

	      if (typeof text !== 'string') {
	        return text.toString();
	      }

	      if (!localize) {
	        localize = BX.message;
	      }

	      text = text.trim();

	      if (text.startsWith('/me')) {
	        text = text.substr(4);
	      } else if (text.startsWith('/loud')) {
	        text = text.substr(6);
	      }

	      text = text.replace(/<br><br \/>/ig, '<br />');
	      text = text.replace(/<br \/><br>/ig, '<br />');
	      text = text.replace(/\[[buis]](.*?)\[\/[buis]]/ig, '$1');
	      text = text.replace(/\[CODE]\n?([\0-\uFFFF]*?)\[\/CODE]/ig, '$1');
	      text = text.replace(/\[url](.*?)\[\/url]/ig, '$1');
	      text = text.replace(/\[RATING=([1-5]{1})]/ig, function () {
	        return '[' + localize['IM_UTILS_TEXT_RATING'] + '] ';
	      });
	      text = text.replace(/\[ATTACH=([0-9]{1,})]/ig, function () {
	        return '[' + localize['IM_UTILS_TEXT_ATTACH'] + '] ';
	      });
	      text = text.replace(/\[USER=([0-9]{1,})](.*?)\[\/USER]/ig, '$2');
	      text = text.replace(/\[CHAT=([0-9]{1,})](.*?)\[\/CHAT]/ig, '$2');
	      text = text.replace(/\[SEND=([0-9]{1,})](.*?)\[\/SEND]/ig, '$2');
	      text = text.replace(/\[PUT=([0-9]{1,})](.*?)\[\/PUT]/ig, '$2');
	      text = text.replace(/\[CALL=([0-9]{1,})](.*?)\[\/CALL]/ig, '$2');
	      text = text.replace(/\[PCH=([0-9]{1,})](.*?)\[\/PCH]/ig, '$2');
	      text = text.replace(/<img.*?data-code="([^"]*)".*?>/ig, '$1');
	      text = text.replace(/<span.*?title="([^"]*)".*?>.*?<\/span>/ig, '($1)');
	      text = text.replace(/<img.*?title="([^"]*)".*?>/ig, '($1)');
	      text = text.replace(/\[ATTACH=([0-9]{1,})]/ig, function (whole, command, text) {
	        return command === 10000 ? '' : '[' + localize['IM_UTILS_TEXT_ATTACH'] + '] ';
	      });
	      text = text.replace(/<s>([^"]*)<\/s>/ig, ' ');
	      text = text.replace(/\[s]([^"]*)\[\/s]/ig, ' ');
	      text = text.replace(/\[icon=([^\]]*)]/ig, function (whole) {
	        var title = whole.match(/title=(.*[^\s\]])/i);

	        if (title && title[1]) {
	          title = title[1];

	          if (title.indexOf('width=') > -1) {
	            title = title.substr(0, title.indexOf('width='));
	          }

	          if (title.indexOf('height=') > -1) {
	            title = title.substr(0, title.indexOf('height='));
	          }

	          if (title.indexOf('size=') > -1) {
	            title = title.substr(0, title.indexOf('size='));
	          }

	          if (title) {
	            title = '(' + title.trim() + ')';
	          }
	        } else {
	          title = '(' + localize['IM_UTILS_TEXT_ICON'] + ')';
	        }

	        return title;
	      });
	      text = text.replace(/\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D([\s\S]*?)\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D\x2D/gmi, "[" + localize["IM_UTILS_TEXT_QUOTE"] + "] ");
	      text = text.replace(/^(>>(.*)\n)/gi, "[" + localize["IM_UTILS_TEXT_QUOTE"] + "] ");
	      text = text.replace(/<\/?[^>]+>/gi, '');

	      if (params && params.FILE_ID && params.FILE_ID.length > 0) {
	        var filesText = [];
	        params.FILE_ID.forEach(function (fileId) {
	          if (files[fileId].type === 'image') {
	            filesText.push(localize['IM_UTILS_TEXT_IMAGE']);
	          } else if (files[fileId].type === 'audio') {
	            filesText.push(localize['IM_UTILS_TEXT_AUDIO']);
	          } else if (files[fileId].type === 'video') {
	            filesText.push(localize['IM_UTILS_TEXT_VIDEO']);
	          } else {
	            filesText.push(files[fileId].name);
	          }
	        });

	        if (filesText.length <= 0) {
	          filesText.push(localize['IM_UTILS_TEXT_FILE']);
	        }

	        text = filesText.join(' ') + text;
	      } else if (params && params.ATTACH && params.ATTACH.length > 0) {
	        text = '[' + localize['IM_UTILS_TEXT_ATTACH'] + '] ' + text;
	      }

	      if (text.length <= 0) {
	        text = localize['IM_UTILS_TEXT_DELETED'];
	      }

	      return text.replace('\n', ' ').trim();
	    },
	    htmlspecialchars: function htmlspecialchars(text) {
	      if (typeof text !== 'string') {
	        return text;
	      }

	      return text.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
	    },
	    htmlspecialcharsback: function htmlspecialcharsback(text) {
	      if (typeof text !== 'string') {
	        return text;
	      }

	      return text.replace(/\&quot;/g, '"').replace(/&#39;/g, "'").replace(/\&lt;/g, '<').replace(/\&gt;/g, '>').replace(/\&amp;/g, '&').replace(/\&nbsp;/g, ' ');
	    },
	    getLocalizeForNumber: function getLocalizeForNumber(phrase, number) {
	      var language = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 'en';
	      var localize = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : null;

	      if (!localize) {
	        localize = BX.message;
	      }

	      var pluralFormType = 1;
	      number = parseInt(number);

	      if (number < 0) {
	        number = number * -1;
	      }

	      if (language) {
	        switch (language) {
	          case 'de':
	          case 'en':
	            pluralFormType = number !== 1 ? 1 : 0;
	            break;

	          case 'ru':
	          case 'ua':
	            pluralFormType = number % 10 === 1 && number % 100 !== 11 ? 0 : number % 10 >= 2 && number % 10 <= 4 && (number % 100 < 10 || number % 100 >= 20) ? 1 : 2;
	            break;
	        }
	      }

	      return localize[phrase + '_PLURAL_' + pluralFormType];
	    }
	  },
	  date: {
	    getFormatType: function getFormatType() {
	      var type = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : im_const.DateFormat.default;
	      var localize = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;

	      if (!localize) {
	        localize = BX.message;
	      }

	      var format = [];

	      if (type === im_const.DateFormat.groupTitle) {
	        format = [["tommorow", "tommorow"], ["today", "today"], ["yesterday", "yesterday"], ["", localize["IM_UTILS_FORMAT_DATE"]]];
	      } else if (type === im_const.DateFormat.message) {
	        format = [["", localize["IM_UTILS_FORMAT_TIME"]]];
	      } else if (type === im_const.DateFormat.recentTitle) {
	        format = [["tommorow", "today"], ["today", "today"], ["yesterday", "yesterday"], ["", localize["IM_UTILS_FORMAT_DATE_RECENT"]]];
	      } else if (type === im_const.DateFormat.recentLinesTitle) {
	        format = [["tommorow", "tommorow"], ["today", "today"], ["yesterday", "yesterday"], ["", localize["IM_UTILS_FORMAT_DATE_RECENT"]]];
	      } else if (type === im_const.DateFormat.readedTitle) {
	        format = [["tommorow", "tommorow, " + localize["IM_UTILS_FORMAT_TIME"]], ["today", "today, " + localize["IM_UTILS_FORMAT_TIME"]], ["yesterday", "yesterday, " + localize["IM_UTILS_FORMAT_TIME"]], ["", localize["IM_UTILS_FORMAT_READED"]]];
	      } else if (type === im_const.DateFormat.vacationTitle) {
	        format = [["", localize["IM_UTILS_FORMAT_DATE_SHORT"]]];
	      } else {
	        format = [["tommorow", "tommorow, " + localize["IM_UTILS_FORMAT_TIME"]], ["today", "today, " + localize["IM_UTILS_FORMAT_TIME"]], ["yesterday", "yesterday, " + localize["IM_UTILS_FORMAT_TIME"]], ["", localize["IM_UTILS_FORMAT_DATE_TIME"]]];
	      }

	      return format;
	    },
	    getDateFunction: function getDateFunction() {
	      var localize = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;

	      if (this.dateFormatFunction) {
	        return this.dateFormatFunction;
	      }

	      this.dateFormatFunction = Object.create(BX.Main.Date);

	      if (localize) {
	        this.dateFormatFunction._getMessage = function (phrase) {
	          return localize[phrase];
	        };
	      }

	      return this.dateFormatFunction;
	    },
	    format: function format(timestamp) {
	      var format = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	      var localize = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;

	      if (!format) {
	        format = this.getFormatType(im_const.DateFormat.default, localize);
	      }

	      return this.getDateFunction(localize).format(format, timestamp);
	    },
	    cast: function cast(date) {
	      var def = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : new Date();
	      var result = def;

	      if (date instanceof Date) {
	        result = date;
	      } else if (typeof date === "string") {
	        result = new Date(date);
	      } else if (typeof date === "number") {
	        result = new Date(date * 1000);
	      }

	      if (result instanceof Date && Number.isNaN(result.getTime())) {
	        result = def;
	      }

	      return result;
	    }
	  },
	  object: {
	    countKeys: function countKeys(obj) {
	      var result = 0;

	      for (var i in obj) {
	        if (obj.hasOwnProperty(i)) {
	          result++;
	        }
	      }

	      return result;
	    }
	  },
	  user: {
	    getLastDateText: function getLastDateText(params) {
	      var localize = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;

	      if (!params) {
	        return '';
	      }

	      var dateFunction = Utils.date.getDateFunction(localize);

	      if (!localize) {
	        localize = BX.message || {};
	      }

	      var text = '';
	      var online = {};

	      if (params.bot || params.network) {
	        text = '';
	      } else if (params.absent && !this.isMobileActive(params, localize)) {
	        online = this.getOnlineStatus(params, localize);
	        text = localize['IM_STATUS_VACATION_TITLE'].replace('#DATE#', dateFunction.format(Utils.date.getFormatType(im_const.DateFormat.vacationTitle, localize), params.absent.getTime() / 1000));

	        if (online.isOnline && params.idle) {
	          text = localize['IM_STATUS_AWAY_TITLE'].replace('#TIME#', this.getIdleText(params, localize)) + '. ' + text;
	        } else if (online.isOnline && !online.lastSeenText) {
	          text = online.statusText + '. ' + text;
	        } else if (online.lastSeenText) {
	          if (!Utils.platform.isMobile()) {
	            text = text + '. ' + localize['IM_LAST_SEEN_' + (params.gender === 'F' ? 'F' : 'M')].replace('#POSITION#', text).replace('#LAST_SEEN#', online.lastSeenText);
	          }
	        }
	      } else if (params.lastActivityDate) {
	        online = this.getOnlineStatus(params, localize);

	        if (online.isOnline && params.idle && !this.isMobileActive(params, localize)) {
	          text = localize['IM_STATUS_AWAY_TITLE'].replace('#TIME#', this.getIdleText(params, localize));
	        } else if (online.isOnline && !online.lastSeenText) {
	          if (Utils.platform.isMobile() && this.isMobileActive(params, localize)) {
	            text = localize['IM_STATUS_MOBILE'];
	          } else {
	            text = online.statusText;
	          }
	        } else if (online.lastSeenText) {
	          if (Utils.platform.isMobile()) {
	            text = localize['IM_LAST_SEEN_SHORT_' + (params.gender === 'F' ? 'F' : 'M')].replace('#LAST_SEEN#', online.lastSeenText);
	          } else {
	            text = localize['IM_LAST_SEEN_' + (params.gender === 'F' ? 'F' : 'M')].replace('#POSITION#', text).replace('#LAST_SEEN#', online.lastSeenText);
	          }
	        }
	      }

	      return text;
	    },
	    getIdleText: function getIdleText(params) {
	      var localize = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;

	      if (!params) {
	        return '';
	      }

	      if (!params.idle) {
	        return '';
	      }

	      return Utils.date.getDateFunction(localize).format([["s60", "sdiff"], ["i60", "idiff"], ["H24", "Hdiff"], ["", "ddiff"]], params.idle);
	    },
	    getOnlineStatus: function getOnlineStatus(params) {
	      var localize = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	      var result = {
	        'isOnline': false,
	        'status': 'offline',
	        'statusText': localize ? localize.IM_STATUS_OFFLINE : 'offline',
	        'lastSeen': params.lastActivityDate,
	        'lastSeenText': ''
	      };

	      if (!params.lastActivityDate || params.lastActivityDate.getTime() === 0) {
	        return result;
	      }

	      var date = new Date();
	      result.isOnline = date.getTime() - params.lastActivityDate.getTime() <= this.getOnlineLimit(localize) * 1000;
	      result.status = result.isOnline ? params.status : 'offline';
	      result.statusText = localize && localize['IM_STATUS_' + result.status.toUpperCase()] ? localize['IM_STATUS_' + result.status.toUpperCase()] : result.status;

	      if (localize && params.lastActivityDate.getTime() > 0 && date.getTime() - params.lastActivityDate.getTime() > 300 * 1000) {
	        result.lastSeenText = Utils.date.getDateFunction(localize).formatLastActivityDate(params.lastActivityDate);
	      }

	      return result;
	    },
	    isMobileActive: function isMobileActive(params) {
	      var localize = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;

	      if (!params) {
	        return false;
	      }

	      if (!localize) {
	        localize = BX.message || {};
	      }

	      return params.mobileLastDate && new Date() - params.mobileLastDate < this.getOnlineLimit(localize) * 1000 && params.lastActivityDate - params.mobileLastDate < 300 * 1000;
	    },
	    getOnlineLimit: function getOnlineLimit() {
	      var localize = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;

	      if (!localize) {
	        localize = BX.message || {};
	      }

	      return localize.LIMIT_ONLINE ? parseInt(localize.LIMIT_ONLINE) : 15 * 60;
	    }
	  },
	  isDarkColor: function isDarkColor(hex) {
	    if (!hex || !hex.match(/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/)) {
	      return false;
	    }

	    if (hex.length === 4) {
	      hex = hex.replace(/#([A-Fa-f0-9])/gi, "$1$1");
	    } else {
	      hex = hex.replace(/#([A-Fa-f0-9])/gi, "$1");
	    }

	    hex = hex.toLowerCase();
	    var darkColor = ["#17a3ea", "#00aeef", "#00c4fb", "#47d1e2", "#75d900", "#ffab00", "#ff5752", "#468ee5", "#1eae43"];

	    if (darkColor.includes('#' + hex)) {
	      return true;
	    }

	    var bigint = parseInt(hex, 16);
	    var red = bigint >> 16 & 255;
	    var green = bigint >> 8 & 255;
	    var blue = bigint & 255;
	    var brightness = (red * 299 + green * 587 + blue * 114) / 1000;
	    return brightness < 128;
	  },
	  hashCode: function hashCode() {
	    var string = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
	    var hash = 0;

	    if (babelHelpers.typeof(string) === 'object' && string) {
	      string = JSON.stringify(string);
	    } else if (typeof string !== 'string') {
	      string = string.toString();
	    }

	    if (typeof string !== 'string') {
	      return hash;
	    }

	    for (var i = 0; i < string.length; i++) {
	      var char = string.charCodeAt(i);
	      hash = (hash << 5) - hash + char;
	      hash = hash & hash;
	    }

	    return hash;
	  },

	  /**
	   * The method compares versions, and returns - 0 if they are the same, 1 if version1 is greater, -1 if version1 is less
	   *
	   * @param version1
	   * @param version2
	   * @returns {number|NaN}
	   */
	  versionCompare: function versionCompare(version1, version2) {
	    var isNumberRegExp = /^([\d+\.]+)$/;

	    if (!isNumberRegExp.test(version1) || !isNumberRegExp.test(version2)) {
	      return NaN;
	    }

	    version1 = version1.toString().split('.');
	    version2 = version2.toString().split('.');

	    if (version1.length < version2.length) {
	      while (version1.length < version2.length) {
	        version1.push(0);
	      }
	    } else if (version2.length < version1.length) {
	      while (version2.length < version1.length) {
	        version2.push(0);
	      }
	    }

	    for (var i = 0; i < version1.length; i++) {
	      if (version1[i] > version2[i]) {
	        return 1;
	      } else if (version1[i] < version2[i]) {
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
	  throttle: function throttle(callback, wait) {
	    var context = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : this;
	    var timeout = null;
	    var callbackArgs = null;

	    var nextCallback = function nextCallback() {
	      callback.apply(context, callbackArgs);
	      timeout = null;
	    };

	    return function () {
	      if (!timeout) {
	        callbackArgs = arguments;
	        timeout = setTimeout(nextCallback, wait);
	      }
	    };
	  },

	  /**
	   * Debounce function. Callback will be executed if it hast been called for longer than 'wait' period (in ms).
	   *
	   * @param callback
	   * @param wait
	   * @param context
	   * @returns {Function}
	   */
	  debounce: function debounce(callback, wait) {
	    var context = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : this;
	    var timeout = null;
	    var callbackArgs = null;

	    var nextCallback = function nextCallback() {
	      callback.apply(context, callbackArgs);
	    };

	    return function () {
	      callbackArgs = arguments;
	      clearTimeout(timeout);
	      timeout = setTimeout(nextCallback, wait);
	    };
	  },
	  getLogTrackingParams: function getLogTrackingParams() {
	    var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    var result = [];
	    var _params$name = params.name,
	        name = _params$name === void 0 ? 'tracking' : _params$name,
	        _params$data = params.data,
	        data = _params$data === void 0 ? [] : _params$data,
	        _params$dialog = params.dialog,
	        dialog = _params$dialog === void 0 ? null : _params$dialog,
	        _params$message = params.message,
	        message = _params$message === void 0 ? null : _params$message,
	        _params$files = params.files,
	        files = _params$files === void 0 ? null : _params$files;
	    name = encodeURIComponent(name);

	    if (data && !(data instanceof Array) && babelHelpers.typeof(data) === 'object') {
	      var dataArray = [];

	      for (var _name in data) {
	        if (data.hasOwnProperty(_name)) {
	          dataArray.push(encodeURIComponent(_name) + "=" + encodeURIComponent(data[_name]));
	        }
	      }

	      data = dataArray;
	    } else if (!data instanceof Array) {
	      data = [];
	    }

	    if (dialog) {
	      result.push('timType=' + dialog.type);

	      if (dialog.type === 'lines') {
	        result.push('timLinesType=' + dialog.entityId.split('|')[0]);
	      }
	    }

	    if (files) {
	      var type = 'file';

	      if (files instanceof Array && files[0]) {
	        type = files[0].type;
	      } else {
	        type = files.type;
	      }

	      result.push('timMessageType=' + type);
	    } else if (message) {
	      result.push('timMessageType=text');
	    }

	    if (this.platform.isBitrixMobile()) {
	      result.push('timDevice=bitrixMobile');
	    } else if (this.platform.isBitrixDesktop()) {
	      result.push('timDevice=bitrixDesktop');
	    } else if (this.platform.isIos() || this.platform.isAndroid()) {
	      result.push('timDevice=mobile');
	    } else {
	      result.push('timDevice=web');
	    }

	    return name + (data.length ? '&' + data.join('&') : '') + (result.length ? '&' + result.join('&') : '');
	  }
	};

	exports.Utils = Utils;

}((this.BX.Messenger.Lib = this.BX.Messenger.Lib || {}),BX.Messenger.Const));
//# sourceMappingURL=utils.bundle.js.map

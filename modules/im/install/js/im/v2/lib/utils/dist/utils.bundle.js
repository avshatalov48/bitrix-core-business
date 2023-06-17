this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,im_v2_lib_desktop,main_date,im_v2_lib_dateFormatter,im_v2_const,main_core) {
	'use strict';

	const UA = navigator.userAgent.toLowerCase();
	const BrowserUtil = {
	  isChrome() {
	    return main_core.Browser.isChrome();
	  },
	  isFirefox() {
	    return main_core.Browser.isFirefox();
	  },
	  isIe() {
	    return main_core.Browser.isIE();
	  },
	  isSafari() {
	    if (this.isChrome()) {
	      return false;
	    }
	    if (!UA.includes('safari')) {
	      return false;
	    }
	    return !this.isSafariBased();
	  },
	  isSafariBased() {
	    if (!UA.includes('applewebkit')) {
	      return false;
	    }
	    return UA.includes('yabrowser') || UA.includes('yaapp_ios_browser') || UA.includes('crios');
	  },
	  findParent(item, findTag) {
	    const isHtmlElement = findTag instanceof HTMLElement;
	    if (!findTag || !main_core.Type.isString(findTag) && !isHtmlElement) {
	      return null;
	    }
	    for (; item && item !== document; item = item.parentNode) {
	      if (main_core.Type.isString(findTag)) {
	        if (main_core.Dom.hasClass(findTag)) {
	          return item;
	        }
	      } else if (isHtmlElement && item === findTag) {
	        return item;
	      }
	    }
	    return null;
	  },
	  openLink(link, target = '_blank') {
	    window.open(link, target, '', true);
	    return true;
	  }
	};

	const DateUtil = {
	  cast(date, def = new Date()) {
	    let result = def;
	    if (date instanceof Date) {
	      result = date;
	    } else if (main_core.Type.isString(date)) {
	      result = new Date(date);
	    } else if (main_core.Type.isNumber(date)) {
	      result = new Date(date * 1000);
	    }
	    if (result instanceof Date && Number.isNaN(result.getTime())) {
	      result = def;
	    }
	    return result;
	  },
	  getTimeToNextMidnight() {
	    const nextMidnight = new Date(new Date().setHours(24, 0, 0)).getTime();
	    return nextMidnight - Date.now();
	  },
	  getStartOfTheDay() {
	    return new Date(new Date().setHours(0, 0));
	  },
	  isToday(date) {
	    return this.cast(date).toDateString() === new Date().toDateString();
	  }
	};

	const UA$1 = navigator.userAgent.toLowerCase();
	const DeviceUtil = {
	  isDesktop() {
	    return !this.isMobile();
	  },
	  isMobile() {
	    if (!main_core.Type.isUndefined(this.isMobileStatic)) {
	      return this.isMobileStatic;
	    }
	    this.isMobileStatic = UA$1.includes('android') || UA$1.includes('webos') || UA$1.includes('iphone') || UA$1.includes('ipad') || UA$1.includes('ipod') || UA$1.includes('blackberry') || UA$1.includes('windows phone');
	    return this.isMobileStatic;
	  },
	  orientationHorizontal: 'horizontal',
	  orientationPortrait: 'portrait',
	  getOrientation() {
	    if (!this.isMobile()) {
	      return this.orientationHorizontal;
	    }
	    return Math.abs(window.orientation) === 0 ? this.orientationPortrait : this.orientationHorizontal;
	  }
	};

	const UA$2 = navigator.userAgent.toLowerCase();
	const PlatformUtil = {
	  isMac() {
	    return main_core.Browser.isMac();
	  },
	  isLinux() {
	    return main_core.Browser.isLinux();
	  },
	  isWindows() {
	    return main_core.Browser.isWin() || !this.isMac() && !this.isLinux();
	  },
	  isBitrixMobile() {
	    return UA$2.includes('bitrixmobile');
	  },
	  isBitrixDesktop() {
	    return im_v2_lib_desktop.DesktopManager.isDesktop();
	  },
	  getDesktopVersion() {
	    return im_v2_lib_desktop.DesktopManager.getInstance().getDesktopVersion();
	  },
	  isDesktopFeatureEnabled(code) {
	    return im_v2_lib_desktop.DesktopManager.getInstance().isDesktopFeatureEnabled(code);
	  },
	  isMobile() {
	    return this.isAndroid() || this.isIos() || this.isBitrixMobile();
	  },
	  isIos() {
	    return main_core.Browser.isIOS();
	  },
	  getIosVersion() {
	    if (!this.isIos()) {
	      return null;
	    }
	    const matches = UA$2.match(/(iphone|ipad)(.+)(OS\s([0-9]+)([_.]([0-9]+))?)/i);
	    if (!matches || !matches[4]) {
	      return null;
	    }
	    return parseFloat(matches[4] + '.' + (matches[6] ? matches[6] : 0));
	  },
	  isAndroid() {
	    return main_core.Browser.isAndroid();
	  },
	  openNewPage(url) {
	    if (!url) {
	      return false;
	    }
	    if (this.isBitrixMobile()) {
	      const MobileTools = window.BX.MobileTools;
	      if (main_core.Type.isUndefined()) {
	        const openWidget = MobileTools.resolveOpenFunction(url);
	        if (openWidget) {
	          openWidget();
	          return true;
	        }
	      }
	      window.app.openNewPage(url);
	      return true;
	    }
	    window.open(url, '_blank');
	    return true;
	  }
	};

	const RestUtil = {
	  getLogTrackingParams(params = {}) {
	    const result = [];
	    let {
	      name = 'tracking',
	      data = []
	    } = params;
	    const {
	      dialog = null,
	      message = null,
	      files = null
	    } = params;
	    name = encodeURIComponent(name);
	    if (main_core.Type.isPlainObject(data)) {
	      const dataArray = [];
	      for (const name in data) {
	        if (data.hasOwnProperty(name)) {
	          dataArray.push(encodeURIComponent(name) + "=" + encodeURIComponent(data[name]));
	        }
	      }
	      data = dataArray;
	    } else if (!main_core.Type.isArray(data)) {
	      data = [];
	    }
	    if (main_core.Type.isObjectLike(dialog)) {
	      result.push('timType=' + dialog.type);
	      if (dialog.type === 'lines') {
	        result.push('timLinesType=' + dialog.entityId.split('|')[0]);
	      }
	    }
	    if (!main_core.Type.isNull(files)) {
	      let type = 'file';
	      if (main_core.Type.isArray(files) && files[0]) {
	        type = files[0].type;
	      } else if (main_core.Type.isObjectLike(files)) {
	        type = files.type;
	      }
	      result.push('timMessageType=' + type);
	    } else if (!main_core.Type.isNull(message)) {
	      result.push('timMessageType=text');
	    }
	    if (PlatformUtil.isBitrixMobile()) {
	      result.push('timDevice=bitrixMobile');
	    } else if (PlatformUtil.isBitrixDesktop()) {
	      result.push('timDevice=bitrixDesktop');
	    } else if (PlatformUtil.isIos() || PlatformUtil.isAndroid()) {
	      result.push('timDevice=mobile');
	    } else {
	      result.push('timDevice=web');
	    }
	    return name + (data.length ? '&' + data.join('&') : '') + (result.length ? '&' + result.join('&') : '');
	  }
	};

	const TextUtil = {
	  convertHtmlEntities(text) {
	    return main_core.Dom.create({
	      tag: 'span',
	      html: text
	    }).innerText;
	  },
	  convertSnakeToCamelCase(text) {
	    return text.replace(/(_[a-z])/gi, $1 => {
	      return $1.toUpperCase().replace('_', '');
	    });
	  },
	  escapeRegex(string) {
	    return string.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
	  },
	  getLocalizeForNumber(phrase, number, language = 'en') {
	    let pluralFormType = 1;
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
	    return main_core.Loc.getMessage(phrase + '_PLURAL_' + pluralFormType);
	  },
	  getFirstLetters(text) {
	    const validSymbolsPattern = /[\p{L}\p{N} ]/u;
	    const words = text.split(/[\s,]/).filter(word => {
	      const firstLetter = word.charAt(0);
	      return validSymbolsPattern.test(firstLetter);
	    });
	    if (words.length === 0) {
	      return '';
	    }
	    if (words.length > 1) {
	      return words[0].charAt(0) + words[1].charAt(0);
	    }
	    return words[0].charAt(0);
	  },
	  insertUnseenWhitespace(text, splitIndex) {
	    if (text.length <= splitIndex) {
	      return text;
	    }
	    const UNSEEN_SPACE = '\u200B';
	    let firstPart = text.slice(0, splitIndex + 1);
	    const secondPart = text.slice(splitIndex + 1);
	    const hasWhitespace = /\s/.test(firstPart);
	    const hasUserCode = /\[user=(\d+)(\s)?(replace)?](.*?)\[\/user]/gi.test(text);
	    if (firstPart.length === splitIndex + 1 && !hasWhitespace && !hasUserCode) {
	      firstPart += UNSEEN_SPACE;
	    }
	    return firstPart + secondPart;
	  },
	  getUuidV4() {
	    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, c => {
	      var r = Math.random() * 16 | 0,
	        v = c == 'x' ? r : r & 0x3 | 0x8;
	      return v.toString(16);
	    });
	  },
	  isUuidV4(uuid) {
	    if (!main_core.Type.isString(uuid)) {
	      return false;
	    }
	    const uuidV4pattern = new RegExp(/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i);
	    return uuid.search(uuidV4pattern) === 0;
	  },
	  isTempMessage(messageId) {
	    return this.isUuidV4(messageId) || messageId.toString().startsWith('temp');
	  },
	  checkUrl(url) {
	    const allowList = ["http:", "https:", "ftp:", "file:", "tel:", "callto:", "mailto:", "skype:", "viber:"];
	    const checkCorrectStartLink = ['/', ...allowList].find(protocol => {
	      return url.startsWith(protocol);
	    });
	    if (!checkCorrectStartLink) {
	      return false;
	    }
	    const element = main_core.Dom.create({
	      tag: 'a',
	      attrs: {
	        href: url
	      }
	    });
	    return allowList.indexOf(element.protocol) > -1;
	  },
	  /**
	   * @deprecated
	   * @use Text.encode from main.core
	   */
	  htmlspecialchars(text) {
	    return main_core.Text.encode(text);
	  },
	  /**
	   * @deprecated
	   * @use Text.decode from main.core
	   */
	  htmlspecialcharsback(text) {
	    return main_core.Text.decode(text);
	  }
	};

	const settings = main_core.Extension.getSettings('im.v2.lib.utils');
	const UserUtil = {
	  getLastDateText(params = {}) {
	    if (params.bot || params.network || !params.lastActivityDate) {
	      return '';
	    }
	    const isOnline = this.isOnline(params.lastActivityDate);
	    const isMobileOnline = this.isMobileOnline(params.lastActivityDate, params.mobileLastDate);
	    let text = '';
	    const lastSeenText = this.getLastSeenText(params.lastActivityDate);

	    // "away for X minutes"
	    if (isOnline && params.idle && !isMobileOnline) {
	      text = main_core.Loc.getMessage('IM_STATUS_AWAY_TITLE').replace('#TIME#', this.getIdleText(params.idle));
	    }
	    // truly online, last activity date < 5 minutes ago - show status text
	    else if (isOnline && !lastSeenText) {
	      text = this.getStatusTextForLastDate(params.status);
	    }
	    // last activity date > 5 minutes ago - "Was online X minutes ago"
	    else if (lastSeenText) {
	      const phraseCode = `IM_LAST_SEEN_${params.gender}`;
	      text = main_core.Loc.getMessage(phraseCode).replace('#POSITION#. ', '').replace('#LAST_SEEN#', lastSeenText);
	    }

	    // if on vacation - add postfix with vacation info
	    if (params.absent) {
	      const vacationText = main_core.Loc.getMessage('IM_STATUS_VACATION_TITLE').replace('#DATE#', im_v2_lib_dateFormatter.DateFormatter.formatByCode(params.absent.getTime() / 1000, im_v2_lib_dateFormatter.DateCode.shortDateFormat));
	      text = text ? `${text}. ${vacationText}` : vacationText;
	    }
	    return text;
	  },
	  getIdleText(idle = '') {
	    if (!idle) {
	      return '';
	    }
	    return main_date.DateTimeFormat.format([['s60', 'sdiff'], ['i60', 'idiff'], ['H24', 'Hdiff'], ['', 'ddiff']], idle);
	  },
	  isOnline(lastActivityDate) {
	    if (!lastActivityDate) {
	      return false;
	    }
	    return Date.now() - lastActivityDate.getTime() <= this.getOnlineLimit() * 1000;
	  },
	  isMobileOnline(lastActivityDate, mobileLastDate) {
	    if (!lastActivityDate || !mobileLastDate) {
	      return false;
	    }
	    const FIVE_MINUTES = 5 * 60 * 1000;
	    return Date.now() - mobileLastDate.getTime() < this.getOnlineLimit() * 1000 && lastActivityDate - mobileLastDate < FIVE_MINUTES;
	  },
	  getStatusTextForLastDate(status) {
	    var _Loc$getMessage;
	    status = status.toUpperCase();
	    return (_Loc$getMessage = main_core.Loc.getMessage(`IM_STATUS_${status}`)) != null ? _Loc$getMessage : status;
	  },
	  getStatusText(status) {
	    var _Loc$getMessage2;
	    status = status.toUpperCase();
	    return (_Loc$getMessage2 = main_core.Loc.getMessage(`IM_STATUS_TEXT_${status}`)) != null ? _Loc$getMessage2 : status;
	  },
	  getLastSeenText(lastActivityDate) {
	    if (!lastActivityDate) {
	      return '';
	    }
	    const FIVE_MINUTES = 5 * 60 * 1000;
	    if (Date.now() - lastActivityDate.getTime() > FIVE_MINUTES) {
	      return main_date.DateTimeFormat.formatLastActivityDate(lastActivityDate);
	    }
	    return '';
	  },
	  isBirthdayToday(birthday) {
	    return birthday === main_date.DateTimeFormat.format('d-m', new Date());
	  },
	  getOnlineLimit() {
	    const limitOnline = settings.get('limitOnline', false);
	    const FIFTEEN_MINUTES = 15 * 60;
	    return limitOnline ? Number.parseInt(limitOnline, 10) : FIFTEEN_MINUTES;
	  },
	  getProfileLink(userId) {
	    if (main_core.Type.isString(userId)) {
	      userId = Number.parseInt(userId, 10);
	    }
	    return `/company/personal/user/${userId}/`;
	  },
	  getCalendarLink(userId) {
	    if (main_core.Type.isString(userId)) {
	      userId = Number.parseInt(userId, 10);
	    }
	    const path = main_core.Extension.getSettings('im.v2.lib.utils').get('pathToUserCalendar');
	    return path.replace('#user_id#', userId);
	  },
	  getMentionBbCode(userId, name) {
	    if (main_core.Type.isString(userId)) {
	      userId = Number.parseInt(userId, 10);
	    }
	    return `[USER=${userId}]${name}[/USER]`;
	  }
	};

	const FileUtil = {
	  getFileExtension(fileName) {
	    return fileName.split('.').splice(-1)[0];
	  },
	  getIconTypeByFilename(fileName) {
	    const extension = this.getFileExtension(fileName);
	    return this.getIconTypeByExtension(extension);
	  },
	  getIconTypeByExtension(extension) {
	    let icon = 'empty';
	    switch (extension.toString()) {
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
	  },
	  getFileTypeByExtension(extension) {
	    let type = im_v2_const.FileType.file;
	    switch (extension) {
	      case 'png':
	      case 'jpe':
	      case 'jpg':
	      case 'jpeg':
	      case 'gif':
	      case 'heic':
	      case 'bmp':
	      case 'webp':
	        type = im_v2_const.FileType.image;
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
	        type = im_v2_const.FileType.video;
	        break;
	      case 'mp3':
	        type = im_v2_const.FileType.audio;
	        break;
	    }
	    return type;
	  },
	  formatFileSize(fileSize) {
	    if (!fileSize || fileSize <= 0) {
	      fileSize = 0;
	    }
	    const sizes = ['BYTE', 'KB', 'MB', 'GB', 'TB'];
	    const KILOBYTE_SIZE = 1024;
	    let position = 0;
	    while (fileSize >= KILOBYTE_SIZE && position < sizes.length - 1) {
	      fileSize /= KILOBYTE_SIZE;
	      position++;
	    }
	    const phrase = main_core.Loc.getMessage(`IM_UTILS_FILE_SIZE_${sizes[position]}`);
	    const roundedSize = Math.round(fileSize);
	    return `${roundedSize} ${phrase}`;
	  },
	  getShortFileName(fileName, maxLength) {
	    if (!fileName || fileName.length < maxLength) {
	      return fileName;
	    }
	    const DOT_LENGTH = 1;
	    const SYMBOLS_TO_TAKE_BEFORE_EXTENSION = 10;
	    const extension = this.getFileExtension(fileName);
	    const symbolsToTakeFromEnd = extension.length + DOT_LENGTH + SYMBOLS_TO_TAKE_BEFORE_EXTENSION;
	    const secondPart = fileName.slice(-symbolsToTakeFromEnd);
	    const firstPart = fileName.slice(0, maxLength - secondPart.length - DOT_LENGTH * 3);
	    return `${firstPart.trim()}...${secondPart.trim()}`;
	  },
	  getViewerDataAttributes(viewerAttributes) {
	    if (!viewerAttributes) {
	      return {};
	    }
	    const dataAttributes = {
	      'data-viewer': true
	    };
	    Object.entries(viewerAttributes).forEach(([key, value]) => {
	      dataAttributes[`data-${main_core.Text.toKebabCase(key)}`] = value;
	    });
	    return dataAttributes;
	  },
	  isImage(fileName) {
	    const extension = FileUtil.getFileExtension(fileName);
	    const fileType = FileUtil.getFileTypeByExtension(extension);
	    return fileType === im_v2_const.FileType.image;
	  }
	};

	const LETTER_CODE_PREFIX = 'Key';
	const DIGIT_CODE_PREFIX = 'Digit';
	const CTRL = 'Ctrl';
	const ALT = 'Alt';
	const SHIFT = 'Shift';
	const MODIFIERS = new Set([CTRL, ALT, SHIFT]);
	const KeyUtil = {
	  isCmdOrCtrl(event) {
	    if (PlatformUtil.isMac()) {
	      return event.metaKey;
	    }
	    if (PlatformUtil.isLinux() || PlatformUtil.isWindows()) {
	      return event.ctrlKey;
	    }
	    return false;
	  },
	  isAltOrOption(event) {
	    return event.altKey;
	  },
	  isCombination(event, combinationList) {
	    if (main_core.Type.isString(combinationList)) {
	      combinationList = [combinationList];
	    }
	    return combinationList.some(combination => {
	      return checkCombination(event, combination);
	    });
	  }
	};
	function checkCombination(event, combination) {
	  if (combination.includes(SHIFT) && !event.shiftKey) {
	    return false;
	  }
	  if (combination.includes(ALT) && !event.altKey) {
	    return false;
	  }
	  if (combination.includes(CTRL) && !this.isCmdOrCtrl(event)) {
	    return false;
	  }
	  const keys = combination.split('+').filter(key => {
	    return !MODIFIERS.has(key);
	  }).map(key => {
	    const singleLetterRegexp = /^[A-Za-z]$/;
	    const singleDigitRegexp = /^\d$/;
	    if (singleLetterRegexp.test(key)) {
	      return `${LETTER_CODE_PREFIX}${key.toUpperCase()}`;
	    } else if (singleDigitRegexp.test(key)) {
	      return `${DIGIT_CODE_PREFIX}${key}`;
	    }
	    return key;
	  });
	  let result = true;
	  keys.forEach(key => {
	    if (key !== event.code) {
	      result = false;
	    }
	  });
	  return result;
	}

	const DomUtil = {
	  recursiveBackwardNodeSearch(node, className, maxNodeLevel = 10) {
	    while (maxNodeLevel > 0) {
	      if (main_core.Dom.hasClass(node, className)) {
	        return node;
	      }
	      if (!node || !node.parentNode) {
	        return null;
	      }
	      node = node.parentNode;
	      maxNodeLevel--;
	    }
	    return null;
	  }
	};

	const DialogUtil = {
	  isDialogId(dialogId) {
	    return /(chat\d+)|\d+/i.test(dialogId);
	  }
	};

	const Utils = {
	  browser: BrowserUtil,
	  date: DateUtil,
	  device: DeviceUtil,
	  platform: PlatformUtil,
	  rest: RestUtil,
	  text: TextUtil,
	  user: UserUtil,
	  file: FileUtil,
	  dom: DomUtil,
	  key: KeyUtil,
	  dialog: DialogUtil
	};

	exports.Utils = Utils;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.Messenger.v2.Lib,BX.Main,BX.Im.V2.Lib,BX.Messenger.v2.Const,BX));
//# sourceMappingURL=utils.bundle.js.map

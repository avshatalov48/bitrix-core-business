/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,im_v2_lib_desktopApi,main_date,im_v2_lib_dateFormatter,im_v2_const,main_core) {
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
	  },
	  waitForSelectionToUpdate() {
	    return new Promise(resolve => {
	      setTimeout(() => {
	        resolve();
	      }, 0);
	    });
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
	  },
	  isSameDay(firstDate, secondDate) {
	    return firstDate.getFullYear() === secondDate.getFullYear() && firstDate.getMonth() === secondDate.getMonth() && firstDate.getDate() === secondDate.getDate();
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
	    return im_v2_lib_desktopApi.DesktopApi.isDesktop();
	  },
	  getDesktopVersion() {
	    return im_v2_lib_desktopApi.DesktopApi.getApiVersion();
	  },
	  isDesktopFeatureEnabled(code) {
	    return im_v2_lib_desktopApi.DesktopApi.isFeatureEnabled(code);
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

	/**
	 * emoji-test-regex-pattern v15.1
	 * (c) Copyright Mathias Bynens <https://mathiasbynens.be/>
	 * @license MIT
	 *
	 * @source: https://github.com/mathiasbynens/emoji-test-regex-pattern/blob/main/dist/emoji-15.1/javascript.txt
	 */
	/**
	 * Modify list for integration with Bitrix Framework:
	 * - changed fie extension from txt to js
	 * - added exported const emojiRegex with original content.
	 * - added flag g to regular expression.
	 */

	const emojiRegex = /[#*0-9]\uFE0F?\u20E3|[\xA9\xAE\u203C\u2049\u2122\u2139\u2194-\u2199\u21A9\u21AA\u231A\u231B\u2328\u23CF\u23ED-\u23EF\u23F1\u23F2\u23F8-\u23FA\u24C2\u25AA\u25AB\u25B6\u25C0\u25FB\u25FC\u25FE\u2600-\u2604\u260E\u2611\u2614\u2615\u2618\u2620\u2622\u2623\u2626\u262A\u262E\u262F\u2638-\u263A\u2640\u2642\u2648-\u2653\u265F\u2660\u2663\u2665\u2666\u2668\u267B\u267E\u267F\u2692\u2694-\u2697\u2699\u269B\u269C\u26A0\u26A7\u26AA\u26B0\u26B1\u26BD\u26BE\u26C4\u26C8\u26CF\u26D1\u26E9\u26F0-\u26F5\u26F7\u26F8\u26FA\u2702\u2708\u2709\u270F\u2712\u2714\u2716\u271D\u2721\u2733\u2734\u2744\u2747\u2757\u2763\u27A1\u2934\u2935\u2B05-\u2B07\u2B1B\u2B1C\u2B55\u3030\u303D\u3297\u3299]\uFE0F?|[\u261D\u270C\u270D](?:\uFE0F|\uD83C[\uDFFB-\uDFFF])?|[\u270A\u270B](?:\uD83C[\uDFFB-\uDFFF])?|[\u23E9-\u23EC\u23F0\u23F3\u25FD\u2693\u26A1\u26AB\u26C5\u26CE\u26D4\u26EA\u26FD\u2705\u2728\u274C\u274E\u2753-\u2755\u2795-\u2797\u27B0\u27BF\u2B50]|\u26D3\uFE0F?(?:\u200D\uD83D\uDCA5)?|\u26F9(?:\uFE0F|\uD83C[\uDFFB-\uDFFF])?(?:\u200D[\u2640\u2642]\uFE0F?)?|\u2764\uFE0F?(?:\u200D(?:\uD83D\uDD25|\uD83E\uDE79))?|\uD83C(?:[\uDC04\uDD70\uDD71\uDD7E\uDD7F\uDE02\uDE37\uDF21\uDF24-\uDF2C\uDF36\uDF7D\uDF96\uDF97\uDF99-\uDF9B\uDF9E\uDF9F\uDFCD\uDFCE\uDFD4-\uDFDF\uDFF5\uDFF7]\uFE0F?|[\uDF85\uDFC2\uDFC7](?:\uD83C[\uDFFB-\uDFFF])?|[\uDFC4\uDFCA](?:\uD83C[\uDFFB-\uDFFF])?(?:\u200D[\u2640\u2642]\uFE0F?)?|[\uDFCB\uDFCC](?:\uFE0F|\uD83C[\uDFFB-\uDFFF])?(?:\u200D[\u2640\u2642]\uFE0F?)?|[\uDCCF\uDD8E\uDD91-\uDD9A\uDE01\uDE1A\uDE2F\uDE32-\uDE36\uDE38-\uDE3A\uDE50\uDE51\uDF00-\uDF20\uDF2D-\uDF35\uDF37-\uDF43\uDF45-\uDF4A\uDF4C-\uDF7C\uDF7E-\uDF84\uDF86-\uDF93\uDFA0-\uDFC1\uDFC5\uDFC6\uDFC8\uDFC9\uDFCF-\uDFD3\uDFE0-\uDFF0\uDFF8-\uDFFF]|\uDDE6\uD83C[\uDDE8-\uDDEC\uDDEE\uDDF1\uDDF2\uDDF4\uDDF6-\uDDFA\uDDFC\uDDFD\uDDFF]|\uDDE7\uD83C[\uDDE6\uDDE7\uDDE9-\uDDEF\uDDF1-\uDDF4\uDDF6-\uDDF9\uDDFB\uDDFC\uDDFE\uDDFF]|\uDDE8\uD83C[\uDDE6\uDDE8\uDDE9\uDDEB-\uDDEE\uDDF0-\uDDF5\uDDF7\uDDFA-\uDDFF]|\uDDE9\uD83C[\uDDEA\uDDEC\uDDEF\uDDF0\uDDF2\uDDF4\uDDFF]|\uDDEA\uD83C[\uDDE6\uDDE8\uDDEA\uDDEC\uDDED\uDDF7-\uDDFA]|\uDDEB\uD83C[\uDDEE-\uDDF0\uDDF2\uDDF4\uDDF7]|\uDDEC\uD83C[\uDDE6\uDDE7\uDDE9-\uDDEE\uDDF1-\uDDF3\uDDF5-\uDDFA\uDDFC\uDDFE]|\uDDED\uD83C[\uDDF0\uDDF2\uDDF3\uDDF7\uDDF9\uDDFA]|\uDDEE\uD83C[\uDDE8-\uDDEA\uDDF1-\uDDF4\uDDF6-\uDDF9]|\uDDEF\uD83C[\uDDEA\uDDF2\uDDF4\uDDF5]|\uDDF0\uD83C[\uDDEA\uDDEC-\uDDEE\uDDF2\uDDF3\uDDF5\uDDF7\uDDFC\uDDFE\uDDFF]|\uDDF1\uD83C[\uDDE6-\uDDE8\uDDEE\uDDF0\uDDF7-\uDDFB\uDDFE]|\uDDF2\uD83C[\uDDE6\uDDE8-\uDDED\uDDF0-\uDDFF]|\uDDF3\uD83C[\uDDE6\uDDE8\uDDEA-\uDDEC\uDDEE\uDDF1\uDDF4\uDDF5\uDDF7\uDDFA\uDDFF]|\uDDF4\uD83C\uDDF2|\uDDF5\uD83C[\uDDE6\uDDEA-\uDDED\uDDF0-\uDDF3\uDDF7-\uDDF9\uDDFC\uDDFE]|\uDDF6\uD83C\uDDE6|\uDDF7\uD83C[\uDDEA\uDDF4\uDDF8\uDDFA\uDDFC]|\uDDF8\uD83C[\uDDE6-\uDDEA\uDDEC-\uDDF4\uDDF7-\uDDF9\uDDFB\uDDFD-\uDDFF]|\uDDF9\uD83C[\uDDE6\uDDE8\uDDE9\uDDEB-\uDDED\uDDEF-\uDDF4\uDDF7\uDDF9\uDDFB\uDDFC\uDDFF]|\uDDFA\uD83C[\uDDE6\uDDEC\uDDF2\uDDF3\uDDF8\uDDFE\uDDFF]|\uDDFB\uD83C[\uDDE6\uDDE8\uDDEA\uDDEC\uDDEE\uDDF3\uDDFA]|\uDDFC\uD83C[\uDDEB\uDDF8]|\uDDFD\uD83C\uDDF0|\uDDFE\uD83C[\uDDEA\uDDF9]|\uDDFF\uD83C[\uDDE6\uDDF2\uDDFC]|\uDF44(?:\u200D\uD83D\uDFEB)?|\uDF4B(?:\u200D\uD83D\uDFE9)?|\uDFC3(?:\uD83C[\uDFFB-\uDFFF])?(?:\u200D(?:[\u2640\u2642]\uFE0F?(?:\u200D\u27A1\uFE0F?)?|\u27A1\uFE0F?))?|\uDFF3\uFE0F?(?:\u200D(?:\u26A7\uFE0F?|\uD83C\uDF08))?|\uDFF4(?:\u200D\u2620\uFE0F?|\uDB40\uDC67\uDB40\uDC62\uDB40(?:\uDC65\uDB40\uDC6E\uDB40\uDC67|\uDC73\uDB40\uDC63\uDB40\uDC74|\uDC77\uDB40\uDC6C\uDB40\uDC73)\uDB40\uDC7F)?)|\uD83D(?:[\uDC3F\uDCFD\uDD49\uDD4A\uDD6F\uDD70\uDD73\uDD76-\uDD79\uDD87\uDD8A-\uDD8D\uDDA5\uDDA8\uDDB1\uDDB2\uDDBC\uDDC2-\uDDC4\uDDD1-\uDDD3\uDDDC-\uDDDE\uDDE1\uDDE3\uDDE8\uDDEF\uDDF3\uDDFA\uDECB\uDECD-\uDECF\uDEE0-\uDEE5\uDEE9\uDEF0\uDEF3]\uFE0F?|[\uDC42\uDC43\uDC46-\uDC50\uDC66\uDC67\uDC6B-\uDC6D\uDC72\uDC74-\uDC76\uDC78\uDC7C\uDC83\uDC85\uDC8F\uDC91\uDCAA\uDD7A\uDD95\uDD96\uDE4C\uDE4F\uDEC0\uDECC](?:\uD83C[\uDFFB-\uDFFF])?|[\uDC6E\uDC70\uDC71\uDC73\uDC77\uDC81\uDC82\uDC86\uDC87\uDE45-\uDE47\uDE4B\uDE4D\uDE4E\uDEA3\uDEB4\uDEB5](?:\uD83C[\uDFFB-\uDFFF])?(?:\u200D[\u2640\u2642]\uFE0F?)?|[\uDD74\uDD90](?:\uFE0F|\uD83C[\uDFFB-\uDFFF])?|[\uDC00-\uDC07\uDC09-\uDC14\uDC16-\uDC25\uDC27-\uDC3A\uDC3C-\uDC3E\uDC40\uDC44\uDC45\uDC51-\uDC65\uDC6A\uDC79-\uDC7B\uDC7D-\uDC80\uDC84\uDC88-\uDC8E\uDC90\uDC92-\uDCA9\uDCAB-\uDCFC\uDCFF-\uDD3D\uDD4B-\uDD4E\uDD50-\uDD67\uDDA4\uDDFB-\uDE2D\uDE2F-\uDE34\uDE37-\uDE41\uDE43\uDE44\uDE48-\uDE4A\uDE80-\uDEA2\uDEA4-\uDEB3\uDEB7-\uDEBF\uDEC1-\uDEC5\uDED0-\uDED2\uDED5-\uDED7\uDEDC-\uDEDF\uDEEB\uDEEC\uDEF4-\uDEFC\uDFE0-\uDFEB\uDFF0]|\uDC08(?:\u200D\u2B1B)?|\uDC15(?:\u200D\uD83E\uDDBA)?|\uDC26(?:\u200D(?:\u2B1B|\uD83D\uDD25))?|\uDC3B(?:\u200D\u2744\uFE0F?)?|\uDC41\uFE0F?(?:\u200D\uD83D\uDDE8\uFE0F?)?|\uDC68(?:\u200D(?:[\u2695\u2696\u2708]\uFE0F?|\u2764\uFE0F?\u200D\uD83D(?:\uDC8B\u200D\uD83D)?\uDC68|\uD83C[\uDF3E\uDF73\uDF7C\uDF93\uDFA4\uDFA8\uDFEB\uDFED]|\uD83D(?:[\uDC68\uDC69]\u200D\uD83D(?:\uDC66(?:\u200D\uD83D\uDC66)?|\uDC67(?:\u200D\uD83D[\uDC66\uDC67])?)|[\uDCBB\uDCBC\uDD27\uDD2C\uDE80\uDE92]|\uDC66(?:\u200D\uD83D\uDC66)?|\uDC67(?:\u200D\uD83D[\uDC66\uDC67])?)|\uD83E(?:[\uDDAF\uDDBC\uDDBD](?:\u200D\u27A1\uFE0F?)?|[\uDDB0-\uDDB3]))|\uD83C(?:\uDFFB(?:\u200D(?:[\u2695\u2696\u2708]\uFE0F?|\u2764\uFE0F?\u200D\uD83D(?:\uDC8B\u200D\uD83D)?\uDC68\uD83C[\uDFFB-\uDFFF]|\uD83C[\uDF3E\uDF73\uDF7C\uDF93\uDFA4\uDFA8\uDFEB\uDFED]|\uD83D[\uDCBB\uDCBC\uDD27\uDD2C\uDE80\uDE92]|\uD83E(?:[\uDDAF\uDDBC\uDDBD](?:\u200D\u27A1\uFE0F?)?|[\uDDB0-\uDDB3]|\uDD1D\u200D\uD83D\uDC68\uD83C[\uDFFC-\uDFFF])))?|\uDFFC(?:\u200D(?:[\u2695\u2696\u2708]\uFE0F?|\u2764\uFE0F?\u200D\uD83D(?:\uDC8B\u200D\uD83D)?\uDC68\uD83C[\uDFFB-\uDFFF]|\uD83C[\uDF3E\uDF73\uDF7C\uDF93\uDFA4\uDFA8\uDFEB\uDFED]|\uD83D[\uDCBB\uDCBC\uDD27\uDD2C\uDE80\uDE92]|\uD83E(?:[\uDDAF\uDDBC\uDDBD](?:\u200D\u27A1\uFE0F?)?|[\uDDB0-\uDDB3]|\uDD1D\u200D\uD83D\uDC68\uD83C[\uDFFB\uDFFD-\uDFFF])))?|\uDFFD(?:\u200D(?:[\u2695\u2696\u2708]\uFE0F?|\u2764\uFE0F?\u200D\uD83D(?:\uDC8B\u200D\uD83D)?\uDC68\uD83C[\uDFFB-\uDFFF]|\uD83C[\uDF3E\uDF73\uDF7C\uDF93\uDFA4\uDFA8\uDFEB\uDFED]|\uD83D[\uDCBB\uDCBC\uDD27\uDD2C\uDE80\uDE92]|\uD83E(?:[\uDDAF\uDDBC\uDDBD](?:\u200D\u27A1\uFE0F?)?|[\uDDB0-\uDDB3]|\uDD1D\u200D\uD83D\uDC68\uD83C[\uDFFB\uDFFC\uDFFE\uDFFF])))?|\uDFFE(?:\u200D(?:[\u2695\u2696\u2708]\uFE0F?|\u2764\uFE0F?\u200D\uD83D(?:\uDC8B\u200D\uD83D)?\uDC68\uD83C[\uDFFB-\uDFFF]|\uD83C[\uDF3E\uDF73\uDF7C\uDF93\uDFA4\uDFA8\uDFEB\uDFED]|\uD83D[\uDCBB\uDCBC\uDD27\uDD2C\uDE80\uDE92]|\uD83E(?:[\uDDAF\uDDBC\uDDBD](?:\u200D\u27A1\uFE0F?)?|[\uDDB0-\uDDB3]|\uDD1D\u200D\uD83D\uDC68\uD83C[\uDFFB-\uDFFD\uDFFF])))?|\uDFFF(?:\u200D(?:[\u2695\u2696\u2708]\uFE0F?|\u2764\uFE0F?\u200D\uD83D(?:\uDC8B\u200D\uD83D)?\uDC68\uD83C[\uDFFB-\uDFFF]|\uD83C[\uDF3E\uDF73\uDF7C\uDF93\uDFA4\uDFA8\uDFEB\uDFED]|\uD83D[\uDCBB\uDCBC\uDD27\uDD2C\uDE80\uDE92]|\uD83E(?:[\uDDAF\uDDBC\uDDBD](?:\u200D\u27A1\uFE0F?)?|[\uDDB0-\uDDB3]|\uDD1D\u200D\uD83D\uDC68\uD83C[\uDFFB-\uDFFE])))?))?|\uDC69(?:\u200D(?:[\u2695\u2696\u2708]\uFE0F?|\u2764\uFE0F?\u200D\uD83D(?:\uDC8B\u200D\uD83D)?[\uDC68\uDC69]|\uD83C[\uDF3E\uDF73\uDF7C\uDF93\uDFA4\uDFA8\uDFEB\uDFED]|\uD83D(?:[\uDCBB\uDCBC\uDD27\uDD2C\uDE80\uDE92]|\uDC66(?:\u200D\uD83D\uDC66)?|\uDC67(?:\u200D\uD83D[\uDC66\uDC67])?|\uDC69\u200D\uD83D(?:\uDC66(?:\u200D\uD83D\uDC66)?|\uDC67(?:\u200D\uD83D[\uDC66\uDC67])?))|\uD83E(?:[\uDDAF\uDDBC\uDDBD](?:\u200D\u27A1\uFE0F?)?|[\uDDB0-\uDDB3]))|\uD83C(?:\uDFFB(?:\u200D(?:[\u2695\u2696\u2708]\uFE0F?|\u2764\uFE0F?\u200D\uD83D(?:[\uDC68\uDC69]|\uDC8B\u200D\uD83D[\uDC68\uDC69])\uD83C[\uDFFB-\uDFFF]|\uD83C[\uDF3E\uDF73\uDF7C\uDF93\uDFA4\uDFA8\uDFEB\uDFED]|\uD83D[\uDCBB\uDCBC\uDD27\uDD2C\uDE80\uDE92]|\uD83E(?:[\uDDAF\uDDBC\uDDBD](?:\u200D\u27A1\uFE0F?)?|[\uDDB0-\uDDB3]|\uDD1D\u200D\uD83D[\uDC68\uDC69]\uD83C[\uDFFC-\uDFFF])))?|\uDFFC(?:\u200D(?:[\u2695\u2696\u2708]\uFE0F?|\u2764\uFE0F?\u200D\uD83D(?:[\uDC68\uDC69]|\uDC8B\u200D\uD83D[\uDC68\uDC69])\uD83C[\uDFFB-\uDFFF]|\uD83C[\uDF3E\uDF73\uDF7C\uDF93\uDFA4\uDFA8\uDFEB\uDFED]|\uD83D[\uDCBB\uDCBC\uDD27\uDD2C\uDE80\uDE92]|\uD83E(?:[\uDDAF\uDDBC\uDDBD](?:\u200D\u27A1\uFE0F?)?|[\uDDB0-\uDDB3]|\uDD1D\u200D\uD83D[\uDC68\uDC69]\uD83C[\uDFFB\uDFFD-\uDFFF])))?|\uDFFD(?:\u200D(?:[\u2695\u2696\u2708]\uFE0F?|\u2764\uFE0F?\u200D\uD83D(?:[\uDC68\uDC69]|\uDC8B\u200D\uD83D[\uDC68\uDC69])\uD83C[\uDFFB-\uDFFF]|\uD83C[\uDF3E\uDF73\uDF7C\uDF93\uDFA4\uDFA8\uDFEB\uDFED]|\uD83D[\uDCBB\uDCBC\uDD27\uDD2C\uDE80\uDE92]|\uD83E(?:[\uDDAF\uDDBC\uDDBD](?:\u200D\u27A1\uFE0F?)?|[\uDDB0-\uDDB3]|\uDD1D\u200D\uD83D[\uDC68\uDC69]\uD83C[\uDFFB\uDFFC\uDFFE\uDFFF])))?|\uDFFE(?:\u200D(?:[\u2695\u2696\u2708]\uFE0F?|\u2764\uFE0F?\u200D\uD83D(?:[\uDC68\uDC69]|\uDC8B\u200D\uD83D[\uDC68\uDC69])\uD83C[\uDFFB-\uDFFF]|\uD83C[\uDF3E\uDF73\uDF7C\uDF93\uDFA4\uDFA8\uDFEB\uDFED]|\uD83D[\uDCBB\uDCBC\uDD27\uDD2C\uDE80\uDE92]|\uD83E(?:[\uDDAF\uDDBC\uDDBD](?:\u200D\u27A1\uFE0F?)?|[\uDDB0-\uDDB3]|\uDD1D\u200D\uD83D[\uDC68\uDC69]\uD83C[\uDFFB-\uDFFD\uDFFF])))?|\uDFFF(?:\u200D(?:[\u2695\u2696\u2708]\uFE0F?|\u2764\uFE0F?\u200D\uD83D(?:[\uDC68\uDC69]|\uDC8B\u200D\uD83D[\uDC68\uDC69])\uD83C[\uDFFB-\uDFFF]|\uD83C[\uDF3E\uDF73\uDF7C\uDF93\uDFA4\uDFA8\uDFEB\uDFED]|\uD83D[\uDCBB\uDCBC\uDD27\uDD2C\uDE80\uDE92]|\uD83E(?:[\uDDAF\uDDBC\uDDBD](?:\u200D\u27A1\uFE0F?)?|[\uDDB0-\uDDB3]|\uDD1D\u200D\uD83D[\uDC68\uDC69]\uD83C[\uDFFB-\uDFFE])))?))?|\uDC6F(?:\u200D[\u2640\u2642]\uFE0F?)?|\uDD75(?:\uFE0F|\uD83C[\uDFFB-\uDFFF])?(?:\u200D[\u2640\u2642]\uFE0F?)?|\uDE2E(?:\u200D\uD83D\uDCA8)?|\uDE35(?:\u200D\uD83D\uDCAB)?|\uDE36(?:\u200D\uD83C\uDF2B\uFE0F?)?|\uDE42(?:\u200D[\u2194\u2195]\uFE0F?)?|\uDEB6(?:\uD83C[\uDFFB-\uDFFF])?(?:\u200D(?:[\u2640\u2642]\uFE0F?(?:\u200D\u27A1\uFE0F?)?|\u27A1\uFE0F?))?)|\uD83E(?:[\uDD0C\uDD0F\uDD18-\uDD1F\uDD30-\uDD34\uDD36\uDD77\uDDB5\uDDB6\uDDBB\uDDD2\uDDD3\uDDD5\uDEC3-\uDEC5\uDEF0\uDEF2-\uDEF8](?:\uD83C[\uDFFB-\uDFFF])?|[\uDD26\uDD35\uDD37-\uDD39\uDD3D\uDD3E\uDDB8\uDDB9\uDDCD\uDDCF\uDDD4\uDDD6-\uDDDD](?:\uD83C[\uDFFB-\uDFFF])?(?:\u200D[\u2640\u2642]\uFE0F?)?|[\uDDDE\uDDDF](?:\u200D[\u2640\u2642]\uFE0F?)?|[\uDD0D\uDD0E\uDD10-\uDD17\uDD20-\uDD25\uDD27-\uDD2F\uDD3A\uDD3F-\uDD45\uDD47-\uDD76\uDD78-\uDDB4\uDDB7\uDDBA\uDDBC-\uDDCC\uDDD0\uDDE0-\uDDFF\uDE70-\uDE7C\uDE80-\uDE88\uDE90-\uDEBD\uDEBF-\uDEC2\uDECE-\uDEDB\uDEE0-\uDEE8]|\uDD3C(?:\u200D[\u2640\u2642]\uFE0F?|\uD83C[\uDFFB-\uDFFF])?|\uDDCE(?:\uD83C[\uDFFB-\uDFFF])?(?:\u200D(?:[\u2640\u2642]\uFE0F?(?:\u200D\u27A1\uFE0F?)?|\u27A1\uFE0F?))?|\uDDD1(?:\u200D(?:[\u2695\u2696\u2708]\uFE0F?|\uD83C[\uDF3E\uDF73\uDF7C\uDF84\uDF93\uDFA4\uDFA8\uDFEB\uDFED]|\uD83D[\uDCBB\uDCBC\uDD27\uDD2C\uDE80\uDE92]|\uD83E(?:[\uDDAF\uDDBC\uDDBD](?:\u200D\u27A1\uFE0F?)?|[\uDDB0-\uDDB3]|\uDD1D\u200D\uD83E\uDDD1|\uDDD1\u200D\uD83E\uDDD2(?:\u200D\uD83E\uDDD2)?|\uDDD2(?:\u200D\uD83E\uDDD2)?))|\uD83C(?:\uDFFB(?:\u200D(?:[\u2695\u2696\u2708]\uFE0F?|\u2764\uFE0F?\u200D(?:\uD83D\uDC8B\u200D)?\uD83E\uDDD1\uD83C[\uDFFC-\uDFFF]|\uD83C[\uDF3E\uDF73\uDF7C\uDF84\uDF93\uDFA4\uDFA8\uDFEB\uDFED]|\uD83D[\uDCBB\uDCBC\uDD27\uDD2C\uDE80\uDE92]|\uD83E(?:[\uDDAF\uDDBC\uDDBD](?:\u200D\u27A1\uFE0F?)?|[\uDDB0-\uDDB3]|\uDD1D\u200D\uD83E\uDDD1\uD83C[\uDFFB-\uDFFF])))?|\uDFFC(?:\u200D(?:[\u2695\u2696\u2708]\uFE0F?|\u2764\uFE0F?\u200D(?:\uD83D\uDC8B\u200D)?\uD83E\uDDD1\uD83C[\uDFFB\uDFFD-\uDFFF]|\uD83C[\uDF3E\uDF73\uDF7C\uDF84\uDF93\uDFA4\uDFA8\uDFEB\uDFED]|\uD83D[\uDCBB\uDCBC\uDD27\uDD2C\uDE80\uDE92]|\uD83E(?:[\uDDAF\uDDBC\uDDBD](?:\u200D\u27A1\uFE0F?)?|[\uDDB0-\uDDB3]|\uDD1D\u200D\uD83E\uDDD1\uD83C[\uDFFB-\uDFFF])))?|\uDFFD(?:\u200D(?:[\u2695\u2696\u2708]\uFE0F?|\u2764\uFE0F?\u200D(?:\uD83D\uDC8B\u200D)?\uD83E\uDDD1\uD83C[\uDFFB\uDFFC\uDFFE\uDFFF]|\uD83C[\uDF3E\uDF73\uDF7C\uDF84\uDF93\uDFA4\uDFA8\uDFEB\uDFED]|\uD83D[\uDCBB\uDCBC\uDD27\uDD2C\uDE80\uDE92]|\uD83E(?:[\uDDAF\uDDBC\uDDBD](?:\u200D\u27A1\uFE0F?)?|[\uDDB0-\uDDB3]|\uDD1D\u200D\uD83E\uDDD1\uD83C[\uDFFB-\uDFFF])))?|\uDFFE(?:\u200D(?:[\u2695\u2696\u2708]\uFE0F?|\u2764\uFE0F?\u200D(?:\uD83D\uDC8B\u200D)?\uD83E\uDDD1\uD83C[\uDFFB-\uDFFD\uDFFF]|\uD83C[\uDF3E\uDF73\uDF7C\uDF84\uDF93\uDFA4\uDFA8\uDFEB\uDFED]|\uD83D[\uDCBB\uDCBC\uDD27\uDD2C\uDE80\uDE92]|\uD83E(?:[\uDDAF\uDDBC\uDDBD](?:\u200D\u27A1\uFE0F?)?|[\uDDB0-\uDDB3]|\uDD1D\u200D\uD83E\uDDD1\uD83C[\uDFFB-\uDFFF])))?|\uDFFF(?:\u200D(?:[\u2695\u2696\u2708]\uFE0F?|\u2764\uFE0F?\u200D(?:\uD83D\uDC8B\u200D)?\uD83E\uDDD1\uD83C[\uDFFB-\uDFFE]|\uD83C[\uDF3E\uDF73\uDF7C\uDF84\uDF93\uDFA4\uDFA8\uDFEB\uDFED]|\uD83D[\uDCBB\uDCBC\uDD27\uDD2C\uDE80\uDE92]|\uD83E(?:[\uDDAF\uDDBC\uDDBD](?:\u200D\u27A1\uFE0F?)?|[\uDDB0-\uDDB3]|\uDD1D\u200D\uD83E\uDDD1\uD83C[\uDFFB-\uDFFF])))?))?|\uDEF1(?:\uD83C(?:\uDFFB(?:\u200D\uD83E\uDEF2\uD83C[\uDFFC-\uDFFF])?|\uDFFC(?:\u200D\uD83E\uDEF2\uD83C[\uDFFB\uDFFD-\uDFFF])?|\uDFFD(?:\u200D\uD83E\uDEF2\uD83C[\uDFFB\uDFFC\uDFFE\uDFFF])?|\uDFFE(?:\u200D\uD83E\uDEF2\uD83C[\uDFFB-\uDFFD\uDFFF])?|\uDFFF(?:\u200D\uD83E\uDEF2\uD83C[\uDFFB-\uDFFE])?))?)/g;

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
	  isEmojiOnly(messageText) {
	    const text = messageText.replaceAll(emojiRegex, '');
	    return text.replaceAll(/\s/g, '').length === 0;
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
	  },
	  getWordsFromString(string) {
	    const clearedString = string.replaceAll('(', ' ').replaceAll(')', ' ').replaceAll('[', ' ').replaceAll(']', ' ').replaceAll('{', ' ').replaceAll('}', ' ').replaceAll('<', ' ').replaceAll('>', ' ').replaceAll('-', ' ').replaceAll('#', ' ').replaceAll('"', ' ').replaceAll('\'', ' ').replaceAll(/\s\s+/g, ' ');
	    return clearedString.split(' ').filter(word => word !== '');
	  },
	  getMentionBbCode(dialogId, name) {
	    if (main_core.Type.isString(dialogId) && dialogId.startsWith('chat')) {
	      return `[CHAT=${dialogId.slice(4)}]${name}[/CHAT]`;
	    }
	    return `[USER=${dialogId}]${name}[/USER]`;
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
	      default:
	        icon = 'empty';
	    }
	    return icon;
	  },
	  getFileTypeByExtension(extension) {
	    let type = im_v2_const.FileType.file;
	    const normalizedExtension = extension.toLowerCase();
	    switch (normalizedExtension) {
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
	      default:
	        type = im_v2_const.FileType.file;
	    }
	    return type;
	  },
	  formatFileSize(fileSize) {
	    let resultFileSize = fileSize;
	    if (!resultFileSize || resultFileSize <= 0) {
	      resultFileSize = 0;
	    }
	    const sizes = ['BYTE', 'KB', 'MB', 'GB', 'TB'];
	    const KILOBYTE_SIZE = 1024;
	    let position = 0;
	    while (resultFileSize >= KILOBYTE_SIZE && position < sizes.length - 1) {
	      resultFileSize /= KILOBYTE_SIZE;
	      position++;
	    }
	    const phrase = main_core.Loc.getMessage(`IM_UTILS_FILE_SIZE_${sizes[position]}`);
	    const roundedSize = Math.round(resultFileSize);
	    return `${roundedSize} ${phrase}`;
	  },
	  getShortFileName(fileName, maxLength) {
	    if (!fileName || fileName.length < maxLength) {
	      return fileName;
	    }
	    const DELIMITER = '...';
	    const DOT_LENGTH = 1;
	    const SYMBOLS_TO_TAKE_BEFORE_EXTENSION = 2;
	    const extension = this.getFileExtension(fileName);
	    const extensionLength = extension.length + DOT_LENGTH;
	    const fileNameWithoutExtension = fileName.slice(0, -extensionLength);
	    if (fileNameWithoutExtension.length <= maxLength) {
	      return fileName;
	    }
	    const availableLength = maxLength - SYMBOLS_TO_TAKE_BEFORE_EXTENSION - DELIMITER.length;
	    if (availableLength <= 0) {
	      return fileName;
	    }
	    const firstPart = fileNameWithoutExtension.slice(0, availableLength).trim();
	    const secondPart = fileNameWithoutExtension.slice(-SYMBOLS_TO_TAKE_BEFORE_EXTENSION).trim();
	    return `${firstPart}${DELIMITER}${secondPart}.${extension}`;
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
	  createDownloadLink(text, urlDownload, fileName) {
	    const anchorTag = main_core.Dom.create('a', {
	      text
	    });
	    main_core.Dom.style(anchorTag, 'display', 'block');
	    main_core.Dom.style(anchorTag, 'color', 'inherit');
	    main_core.Dom.style(anchorTag, 'text-decoration', 'inherit');
	    anchorTag.setAttribute('href', urlDownload);
	    anchorTag.setAttribute('download', fileName);
	    return anchorTag;
	  },
	  isImage(fileName) {
	    const extension = FileUtil.getFileExtension(fileName);
	    const fileType = FileUtil.getFileTypeByExtension(extension);
	    return fileType === im_v2_const.FileType.image;
	  },
	  getBase64(file) {
	    const reader = new FileReader();
	    return new Promise(resolve => {
	      main_core.Event.bind(reader, 'load', () => {
	        const fullBase64 = reader.result;
	        const commaPosition = fullBase64.indexOf(',');
	        const cutBase64 = fullBase64.slice(commaPosition + 1);
	        resolve(cutBase64);
	      });
	      reader.readAsDataURL(file);
	    });
	  }
	};

	const LETTER_CODE_PREFIX = 'Key';
	const DIGIT_CODE_PREFIX = 'Digit';
	const CTRL = 'Ctrl';
	const ALT = 'Alt';
	const SHIFT = 'Shift';
	const MODIFIERS = new Set([CTRL, ALT, SHIFT]);
	var _event = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("event");
	var _prepareCombination = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareCombination");
	var _checkCombination = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("checkCombination");
	var _checkModifiers = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("checkModifiers");
	var _checkShift = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("checkShift");
	var _checkAlt = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("checkAlt");
	var _checkCtrl = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("checkCtrl");
	var _splitCombinationIntoKeyCodes = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("splitCombinationIntoKeyCodes");
	class KeyChecker {
	  constructor(event) {
	    Object.defineProperty(this, _splitCombinationIntoKeyCodes, {
	      value: _splitCombinationIntoKeyCodes2
	    });
	    Object.defineProperty(this, _checkCtrl, {
	      value: _checkCtrl2
	    });
	    Object.defineProperty(this, _checkAlt, {
	      value: _checkAlt2
	    });
	    Object.defineProperty(this, _checkShift, {
	      value: _checkShift2
	    });
	    Object.defineProperty(this, _checkModifiers, {
	      value: _checkModifiers2
	    });
	    Object.defineProperty(this, _checkCombination, {
	      value: _checkCombination2
	    });
	    Object.defineProperty(this, _prepareCombination, {
	      value: _prepareCombination2
	    });
	    Object.defineProperty(this, _event, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _event)[_event] = event;
	  }
	  isCmdOrCtrl() {
	    if (PlatformUtil.isMac()) {
	      return babelHelpers.classPrivateFieldLooseBase(this, _event)[_event].metaKey;
	    }
	    if (PlatformUtil.isLinux() || PlatformUtil.isWindows()) {
	      return babelHelpers.classPrivateFieldLooseBase(this, _event)[_event].ctrlKey;
	    }
	    return false;
	  }
	  isShift() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _event)[_event].shiftKey;
	  }
	  isAltOrOption() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _event)[_event].altKey;
	  }
	  isCombination(rawCombination) {
	    const combinationList = babelHelpers.classPrivateFieldLooseBase(this, _prepareCombination)[_prepareCombination](rawCombination);
	    return combinationList.some(combination => {
	      return babelHelpers.classPrivateFieldLooseBase(this, _checkCombination)[_checkCombination](combination);
	    });
	  }
	  isExactCombination(rawCombination) {
	    const combinationList = babelHelpers.classPrivateFieldLooseBase(this, _prepareCombination)[_prepareCombination](rawCombination);
	    return combinationList.some(combination => {
	      return babelHelpers.classPrivateFieldLooseBase(this, _checkCombination)[_checkCombination](combination, true);
	    });
	  }
	}
	function _prepareCombination2(combination) {
	  if (Array.isArray(combination)) {
	    return combination;
	  }
	  if (main_core.Type.isStringFilled(combination)) {
	    return [combination];
	  }
	  return [];
	}
	function _checkCombination2(combination, exact = false) {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _checkModifiers)[_checkModifiers](combination, exact)) {
	    return false;
	  }
	  const keyCodes = babelHelpers.classPrivateFieldLooseBase(this, _splitCombinationIntoKeyCodes)[_splitCombinationIntoKeyCodes](combination);
	  let result = true;
	  keyCodes.forEach(keyCode => {
	    if (keyCode !== babelHelpers.classPrivateFieldLooseBase(this, _event)[_event].code) {
	      result = false;
	    }
	  });
	  return result;
	}
	function _checkModifiers2(combination, exact = false) {
	  let result = true;
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _checkShift)[_checkShift](combination, exact) || !babelHelpers.classPrivateFieldLooseBase(this, _checkAlt)[_checkAlt](combination, exact) || !babelHelpers.classPrivateFieldLooseBase(this, _checkCtrl)[_checkCtrl](combination, exact)) {
	    result = false;
	  }
	  return result;
	}
	function _checkShift2(combination, exact = false) {
	  let result = true;
	  const missingShift = combination.includes(SHIFT) && !babelHelpers.classPrivateFieldLooseBase(this, _event)[_event].shiftKey;
	  const excessShift = exact && !combination.includes(SHIFT) && babelHelpers.classPrivateFieldLooseBase(this, _event)[_event].shiftKey;
	  if (missingShift || excessShift) {
	    result = false;
	  }
	  return result;
	}
	function _checkAlt2(combination, exact = false) {
	  let result = true;
	  const missingAlt = combination.includes(ALT) && !babelHelpers.classPrivateFieldLooseBase(this, _event)[_event].altKey;
	  const excessAlt = exact && !combination.includes(ALT) && babelHelpers.classPrivateFieldLooseBase(this, _event)[_event].altKey;
	  if (missingAlt || excessAlt) {
	    result = false;
	  }
	  return result;
	}
	function _checkCtrl2(combination, exact = false) {
	  let result = true;
	  const missingCtrl = combination.includes(CTRL) && !this.isCmdOrCtrl();
	  const excessCtrl = exact && !combination.includes(CTRL) && this.isCmdOrCtrl();
	  if (missingCtrl || excessCtrl) {
	    result = false;
	  }
	  return result;
	}
	function _splitCombinationIntoKeyCodes2(combination) {
	  const split = combination.split('+');
	  const withoutModifiers = split.filter(key => {
	    return !MODIFIERS.has(key);
	  });
	  return withoutModifiers.map(key => {
	    const singleLetterRegexp = /^[A-Za-z]$/;
	    const singleDigitRegexp = /^\d$/;
	    if (singleLetterRegexp.test(key)) {
	      return `${LETTER_CODE_PREFIX}${key.toUpperCase()}`;
	    }
	    if (singleDigitRegexp.test(key)) {
	      return `${DIGIT_CODE_PREFIX}${key}`;
	    }
	    return key;
	  });
	}

	const KeyUtil = {
	  isCmdOrCtrl(event) {
	    return new KeyChecker(event).isCmdOrCtrl();
	  },
	  isShift(event) {
	    return new KeyChecker(event).isShift();
	  },
	  isAltOrOption(event) {
	    return new KeyChecker(event).isAltOrOption();
	  },
	  isCombination(event, rawCombinationList) {
	    return new KeyChecker(event).isCombination(rawCombinationList);
	  },
	  isExactCombination(event, rawCombinationList) {
	    return new KeyChecker(event).isExactCombination(rawCombinationList);
	  }
	};

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
	    return /^(chat\d+)$|^\d+$/i.test(dialogId);
	  },
	  isExternalId(dialogId) {
	    return this.isGroupExternalId(dialogId) || this.isCrmExternalId(dialogId);
	  },
	  isGroupExternalId(dialogId) {
	    const GROUP_PREFIX = 'sg';
	    return dialogId.startsWith(GROUP_PREFIX);
	  },
	  isCrmExternalId(dialogId) {
	    const CRM_PREFIX = 'crm|';
	    return dialogId.startsWith(CRM_PREFIX);
	  },
	  isLinesExternalId(dialogId) {
	    const LINES_PREFIX = 'imol|';
	    return dialogId.toString().startsWith(LINES_PREFIX) && !this.isLinesHistoryId(dialogId);
	  },
	  isLinesHistoryId(dialogId) {
	    return /^imol\|\d+$/.test(dialogId);
	  }
	};

	const ConferenceUtil = {
	  isValidUrl(url) {
	    return /^(https|http):\/\/(.*)\/video\/([\d.a-z-]+)/i.test(url);
	  },
	  isValidCode(code) {
	    return /^([\d.a-z-]+)$/i.test(code);
	  },
	  isCurrentPortal(url) {
	    if (!main_core.Type.isStringFilled(url)) {
	      return false;
	    }
	    const result = url.match(/^(https|http):\/\/(.*)\/video\/([\d.a-z-]+)/i);
	    if (!result) {
	      return false;
	    }
	    const host = result[2];
	    return host.includes(location.host);
	  },
	  getCodeFromUrl(url) {
	    if (!main_core.Type.isStringFilled(url)) {
	      return null;
	    }
	    const result = url.match(/^(https|http):\/\/(.*)\/video\/([\d.a-z-]+)/i);
	    if (!result) {
	      return null;
	    }
	    const code = result[3];
	    if (!main_core.Type.isStringFilled(code)) {
	      return null;
	    }
	    return code;
	  },
	  getUrlByCode(code) {
	    if (!this.isValidCode(code)) {
	      return null;
	    }
	    const origin = location.origin.replace('http://', 'https://');
	    return `${origin}/video/${code}`;
	  },
	  getCodeByOptions(options = {}) {
	    if (main_core.Type.isStringFilled(options.link) && this.isValidUrl(options.link)) {
	      return this.getCodeFromUrl(options.link);
	    }
	    if (main_core.Type.isStringFilled(options.code) && this.isValidCode(options.code)) {
	      return options.code;
	    }
	    return null;
	  },
	  getWindowNameByCode(code) {
	    if (!this.isValidCode(code)) {
	      return null;
	    }
	    return `im-conference-${code}`;
	  }
	};

	const CallUtil = {
	  isNumber(text) {
	    return /^([\d #()+./-]+)$/.test(text);
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
	  dialog: DialogUtil,
	  conference: ConferenceUtil,
	  call: CallUtil
	};

	exports.Utils = Utils;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.Messenger.v2.Lib,BX.Main,BX.Im.V2.Lib,BX.Messenger.v2.Const,BX));
//# sourceMappingURL=utils.bundle.js.map

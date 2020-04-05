/**
 * Bitrix Messenger
 * Logger class
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2019 Bitrix
 */

if (!window.BX)
{
	window.BX = {};
}
if (typeof window.BX.Messenger == 'undefined')
{
	window.BX.Messenger = {};
}
if (typeof window.BX.Messenger.Const == 'undefined')
{
	window.BX.Messenger.Const = {};
}
if (typeof window.BX.Messenger.Utils == 'undefined')
{
	window.BX.Messenger.Utils = {};
}

BX.Messenger.Const.dateFormat = Object.freeze({
	groupTitle: 'groupTitle',
	message: 'message',
	recentTitle: 'recentTitle',
	recentLinesTitle: 'recentLinesTitle',
	default: 'default',
});

BX.Messenger.Utils =
{
	browser:
	{
		isSafari()
		{
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
			return navigator.userAgent && navigator.userAgent.toLowerCase().includes('bitrixmobile');
		},
		isBitrixDesktop()
		{
			return navigator.userAgent.toLowerCase().includes('bitrixdesktop');
		},

		isMobile()
		{
			return this.isAndroid() || this.isIos() || this.isBitrixMobile();
		},

		isIos()
		{
			return navigator.userAgent.toLowerCase().includes('iphone') || navigator.userAgent.toLowerCase().includes('ipad');
		},
		getIosVersion()
		{
			if (!this.isIos())
			{
				return null;
			}

			let matches = navigator.userAgent.toLowerCase().match(/(iphone|ipad)(.+)(OS\s([0-9]+))/i);
			if (!matches || !matches[4])
			{
				return null;
			}

			return matches[4];
		},
		isAndroid()
		{
			return navigator.userAgent.toLowerCase().includes('android');
		},
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
		}
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
		}
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

	getDateFormatType(type = BX.Messenger.Const.dateFormat.default, localize = null)
	{
		if (!localize)
		{
			localize = BX.message;
		}

		let format = [];
		if (type === BX.Messenger.Const.dateFormat.groupTitle)
		{
			format = [
				["tommorow", "tommorow"],
				["today", "today"],
				["yesterday", "yesterday"],
				["", BX.Main.Date.convertBitrixFormat(localize["IM_UTILS_FORMAT_DATE"])]
			];
		}
		else if (type === BX.Messenger.Const.dateFormat.message)
		{
			format = [
				["", localize["IM_UTILS_FORMAT_TIME"]]
			];
		}
		else if (type === BX.Messenger.Const.dateFormat.recentTitle)
		{
			format = [
				["tommorow", "today"],
				["today", "today"],
				["yesterday", "yesterday"],
				["", BX.Main.Date.convertBitrixFormat(localize["IM_UTILS_FORMAT_DATE_RECENT"])]
			]
		}
		else if (type === BX.Messenger.Const.dateFormat.recentLinesTitle)
		{
			format = [
				["tommorow", "tommorow"],
				["today", "today"],
				["yesterday", "yesterday"],
				["", BX.Main.Date.convertBitrixFormat(localize["IM_UTILS_FORMAT_DATE_RECENT"])]
			]
		}
		else
		{
			format = [
				["tommorow", "tommorow, "+localize["IM_UTILS_FORMAT_TIME"]],
				["today", "today, "+localize["IM_UTILS_FORMAT_TIME"]],
				["yesterday", "yesterday, "+localize["IM_UTILS_FORMAT_TIME"]],
				["", BX.Main.Date.convertBitrixFormat(localize["FORMAT_DATETIME"])]
			];
		}

		return format;
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

	htmlspecialchars(string)
	{
		if (typeof string !== 'string')
		{
			return string;
		}

		return string.replace(/&/g, '&amp;')
			.replace(/"/g, '&quot;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;');
	},

	htmlspecialcharsback(string)
	{
		if (typeof string !== 'string')
		{
			return string;
		}

		return string.replace(/\&quot;/g, '"')
			.replace(/&#39;/g, "'")
			.replace(/\&lt;/g, '<')
			.replace(/\&gt;/g, '>')
			.replace(/\&amp;/g, '&')
			.replace(/\&nbsp;/g, ' ');
	},

	getLogTrackingParams(params = {})
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
import Type from './type';
import Dom from './dom';

const UA = navigator.userAgent.toLowerCase();

/**
 * @memberOf BX
 */
export default class Browser
{
	static isOpera()
	{
		return UA.includes('opera');
	}

	static isIE()
	{
		return ('attachEvent' in document) && !Browser.isOpera();
	}

	static isIE6()
	{
		return UA.includes('msie 6');
	}

	static isIE7()
	{
		return UA.includes('msie 7');
	}

	static isIE8()
	{
		return UA.includes('msie 8');
	}

	static isIE9()
	{
		return ('documentMode' in document) && document.documentMode >= 9;
	}

	static isIE10()
	{
		return ('documentMode' in document) && document.documentMode >= 10;
	}

	static isSafari()
	{
		return UA.includes('safari') && !UA.includes('chrome');
	}

	static isFirefox()
	{
		return UA.includes('firefox');
	}

	static isChrome()
	{
		return UA.includes('chrome');
	}

	static detectIEVersion()
	{
		if (Browser.isOpera() || Browser.isSafari() || Browser.isFirefox() || Browser.isChrome())
		{
			return -1;
		}

		let rv = -1;

		if (
			!!(window.MSStream)
			&& !(window.ActiveXObject)
			&& ('ActiveXObject' in window)
		)
		{
			rv = 11;
		}
		else if (Browser.isIE10())
		{
			rv = 10;
		}
		else if (Browser.isIE9())
		{
			rv = 9;
		}
		else if (Browser.isIE())
		{
			rv = 8;
		}

		if (rv === -1 || rv === 8)
		{
			if (navigator.appName === 'Microsoft Internet Explorer')
			{
				const re = new RegExp('MSIE ([0-9]+[.0-9]*)');
				const res = navigator.userAgent.match(re);

				if (Type.isArrayLike(res) && res.length > 0)
				{
					rv = parseFloat(res[1]);
				}
			}

			if (navigator.appName === 'Netscape')
			{
				// Alternative check for IE 11
				rv = 11;
				const re = new RegExp('Trident/.*rv:([0-9]+[.0-9]*)');

				if (re.exec(navigator.userAgent) != null)
				{
					const res = navigator.userAgent.match(re);

					if (Type.isArrayLike(res) && res.length > 0)
					{
						rv = parseFloat(res[1]);
					}
				}
			}
		}

		return rv;
	}

	static isIE11()
	{
		return Browser.detectIEVersion() >= 11;
	}

	static isMac()
	{
		return UA.includes('macintosh');
	}

	static isWin()
	{
		return UA.includes('windows');
	}

	static isLinux()
	{
		return UA.includes('linux') && !Browser.isAndroid();
	}

	static isAndroid()
	{
		return UA.includes('android');
	}

	static isIPad()
	{
		return UA.includes('ipad;') || (this.isMac() && this.isTouchDevice());
	}

	static isIPhone()
	{
		return UA.includes('iphone;');
	}

	static isIOS()
	{
		return Browser.isIPad() || Browser.isIPhone();
	}

	static isMobile()
	{
		return (
			Browser.isIPhone()
			|| Browser.isIPad()
			|| Browser.isAndroid()
			|| UA.includes('mobile')
			|| UA.includes('touch')
		);
	}

	static isRetina()
	{
		return window.devicePixelRatio && window.devicePixelRatio >= 2;
	}

	static isTouchDevice()
	{
		return (
			('ontouchstart' in window) || navigator.maxTouchPoints > 0 || navigator.msMaxTouchPoints > 0
		);
	}

	static isDoctype(target: any)
	{
		const doc = target || document;

		if (doc.compatMode)
		{
			return (doc.compatMode === 'CSS1Compat');
		}

		return (doc.documentElement && doc.documentElement.clientHeight);
	}

	static isLocalStorageSupported()
	{
		try
		{
			localStorage.setItem('test', 'test');
			localStorage.removeItem('test');
			return true;
		}
		catch (e)
		{
			return false;
		}
	}

	static addGlobalClass(target: Element)
	{
		let globalClass = 'bx-core';

		target = Type.isElementNode(target) ? target : document.documentElement;
		if (Dom.hasClass(target, globalClass))
		{
			return;
		}

		if (Browser.isIOS())
		{
			globalClass += ' bx-ios';
		}
		else if (Browser.isWin())
		{
			globalClass += ' bx-win';
		}
		else if (Browser.isMac())
		{
			globalClass += ' bx-mac';
		}
		else if (Browser.isLinux())
		{
			globalClass += ' bx-linux';
		}
		else if (Browser.isAndroid())
		{
			globalClass += ' bx-android';
		}

		globalClass += (Browser.isMobile() ? ' bx-touch' : ' bx-no-touch');
		globalClass += (Browser.isRetina() ? ' bx-retina' : ' bx-no-retina');

		if (/AppleWebKit/.test(navigator.userAgent))
		{
			globalClass += ' bx-chrome';
		}
		else if (/Opera/.test(navigator.userAgent))
		{
			globalClass += ' bx-opera';
		}
		else if (Browser.isFirefox())
		{
			globalClass += ' bx-firefox';
		}

		Dom.addClass(target, globalClass);
	}

	static detectAndroidVersion()
	{
		const re = new RegExp('Android ([0-9]+[.0-9]*)');

		if (re.exec(navigator.userAgent) != null)
		{
			const res = navigator.userAgent.match(re);

			if (Type.isArrayLike(res) && res.length > 0)
			{
				return parseFloat(res[1]);
			}
		}

		return 0;
	}

	static isPropertySupported(jsProperty: any, returnCSSName: any)
	{
		if (jsProperty === '')
		{
			return false;
		}

		function getCssName(propertyName)
		{
			return propertyName.replace(/([A-Z])/g, (...args) => `-${args[1].toLowerCase()}`);
		}

		function getJsName(cssName)
		{
			const reg = /(\\-([a-z]))/g;

			if (reg.test(cssName))
			{
				return cssName.replace(reg, (...args) => args[2].toUpperCase());
			}

			return cssName;
		}

		const property = jsProperty.includes('-') ? getJsName(jsProperty) : jsProperty;
		const bReturnCSSName = !!returnCSSName;
		const ucProperty = property.charAt(0).toUpperCase() + property.slice(1);
		const props = ['Webkit', 'Moz', 'O', 'ms'].join(`${ucProperty} `);
		const properties = `${property} ${props} ${ucProperty}`.split(' ');

		const obj = document.body || document.documentElement;

		for (let i = 0; i < properties.length; i += 1)
		{
			const prop = properties[i];

			if (obj && 'style' in obj && prop in obj.style)
			{
				const lowerProp = prop.substr(0, prop.length - property.length).toLowerCase();
				const prefix = prop === property ? '' : `-${lowerProp}-`;
				return bReturnCSSName ? prefix + getCssName(property) : prop;
			}
		}

		return false;
	}

	static addGlobalFeatures(features: any)
	{
		if (!Type.isArray(features))
		{
			return;
		}

		const classNames = [];

		for (let i = 0; i < features.length; i += 1)
		{
			const support = !!Browser.isPropertySupported(features[i]);
			classNames.push(`bx-${(support ? '' : 'no-')}${features[i].toLowerCase()}`);
		}

		Dom.addClass(document.documentElement, classNames.join(' '));
	}
}
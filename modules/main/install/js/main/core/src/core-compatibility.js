/* eslint-disable prefer-rest-params */

import Type from './lib/type';
import Reflection from './lib/reflection';
import Dom from './lib/dom';
import Browser from './lib/browser';
import Event from './lib/event';
import Http from './lib/http';
import Runtime from './lib/runtime';
import Text from './lib/text';
import messageFunction from './lib/loc/message';
import * as debugNs from './lib/runtime/debug';
import {isReady} from './lib/event/ready';
import getElement from './internal/get-element';
import getWindow from './internal/get-window';
import EventEmitter from './lib/event/event-emitter';
import BaseEvent from './lib/event/base-event';

// BX.*
export const {getClass, namespace} = Reflection;
export const message = messageFunction;

/**
 * @memberOf BX
 */
export const {
	replace,
	remove,
	clean,
	insertBefore,
	insertAfter,
	append,
	prepend,
	style,
	adjust,
	create,
	isShown,
} = Dom;

export const addClass = function addClass() {
	Dom.addClass(...Runtime.merge([], Array.from(arguments), [getElement(arguments[0])]));
};

export const removeClass = function removeClass() {
	Dom.removeClass(...Runtime.merge(Array.from(arguments), [getElement(arguments[0])]));
};

export const hasClass = function hasClass() {
	return Dom.hasClass(...Runtime.merge(Array.from(arguments), [getElement(arguments[0])]));
};

export const toggleClass = function toggleClass() {
	Dom.toggleClass(...Runtime.merge(Array.from(arguments), [getElement(arguments[0])]));
};

export const cleanNode = (element, removeElement = false) => {
	const currentElement = getElement(element);

	if (Type.isDomNode(currentElement))
	{
		Dom.clean(currentElement);

		if (removeElement)
		{
			Dom.remove(currentElement);
			return currentElement;
		}
	}

	return currentElement;
};

export const getCookie = Http.Cookie.get;
export const setCookie = (name, value, options = {}) => {
	const attributes = {...options};

	if (Type.isNumber(attributes.expires))
	{
		attributes.expires /= (3600 * 24);
	}

	Http.Cookie.set(name, value, attributes);
};

export const {
	bind,
	unbind,
	unbindAll,
	bindOnce,
	ready,
} = Event;

export {isReady};
export const {
	debugState: debugEnableFlag,
	isDebugEnabled: debugStatus,
	default: debug,
} = debugNs;

export const debugEnable = (value) => {
	if (value)
	{
		debugNs.enableDebug();
	}
	else
	{
		debugNs.disableDebug();
	}
};

export const {
	clone,
	loadExtension: loadExt,
	debounce,
	throttle,
	html,
} = Runtime;

// BX.type
export const type = {
	...Object.getOwnPropertyNames(Type)
		.filter(key => !['name', 'length', 'prototype', 'caller', 'arguments'].includes(key))
		.reduce((acc, key) => {
			acc[key] = Type[key];
			return acc;
		}, {}),
	isNotEmptyString: value => Type.isString(value) && value !== '',
	isNotEmptyObject: value => Type.isObjectLike(value) && Object.keys(value).length > 0,
	isMapKey: Type.isObject,
	stringToInt: (value) => {
		const parsed = parseInt(value);
		return !Number.isNaN(parsed) ? parsed : 0;
	},
};

// BX.browser
export const browser = {
	IsOpera: Browser.isOpera,
	IsIE: Browser.isIE,
	IsIE6: Browser.isIE6,
	IsIE7: Browser.isIE7,
	IsIE8: Browser.isIE8,
	IsIE9: Browser.isIE9,
	IsIE10: Browser.isIE10,
	IsIE11: Browser.isIE11,
	IsSafari: Browser.isSafari,
	IsFirefox: Browser.isFirefox,
	IsChrome: Browser.isChrome,
	DetectIeVersion: Browser.detectIEVersion,
	IsMac: Browser.isMac,
	IsAndroid: Browser.isAndroid,
	isIPad: Browser.isIPad,
	isIPhone: Browser.isIPhone,
	IsIOS: Browser.isIOS,
	IsMobile: Browser.isMobile,
	isRetina: Browser.isRetina,
	IsDoctype: Browser.isDoctype,
	SupportLocalStorage: Browser.isLocalStorageSupported,
	addGlobalClass: Browser.addGlobalClass,
	DetectAndroidVersion: Browser.detectAndroidVersion,
	isPropertySupported: Browser.isPropertySupported,
	addGlobalFeatures: Browser.addGlobalFeatures,
};

// eslint-disable-next-line
const ajax = window.BX ? window.BX.ajax : () => {};
export {ajax};

export function GetWindowScrollSize(doc = document)
{
	return {
		scrollWidth: doc.documentElement.scrollWidth,
		scrollHeight: doc.documentElement.scrollHeight,
	};
}

export function GetWindowScrollPos(doc = document)
{
	const win = getWindow(doc);

	return {
		scrollLeft: win.pageXOffset,
		scrollTop: win.pageYOffset,
	};
}

export function GetWindowInnerSize(doc = document)
{
	const win = getWindow(doc);
	return {innerWidth: win.innerWidth, innerHeight: win.innerHeight};
}

export function GetWindowSize(doc = document)
{
	return {
		...GetWindowInnerSize(doc),
		...GetWindowScrollPos(doc),
		...GetWindowScrollSize(doc),
	};
}

export function GetContext(node)
{
	return getWindow(node);
}

export function pos(element, relative = false)
{
	if (!element)
	{
		return (new DOMRect()).toJSON();
	}

	if (element.ownerDocument === document && !relative)
	{
		const clientRect = element.getBoundingClientRect();
		const root = document.documentElement;
		const {body} = document;

		return {
			top: Math.round(clientRect.top + (root.scrollTop || body.scrollTop)),
			left: Math.round(clientRect.left + (root.scrollLeft || body.scrollLeft)),
			width: Math.round(clientRect.right - clientRect.left),
			height: Math.round(clientRect.bottom - clientRect.top),
			right: Math.round(clientRect.right + (root.scrollLeft || body.scrollLeft)),
			bottom: Math.round(clientRect.bottom + (root.scrollTop || body.scrollTop)),
		};
	}

	let x = 0;
	let y = 0;
	const w = element.offsetWidth;
	const h = element.offsetHeight;
	let first = true;

	// eslint-disable-next-line no-param-reassign
	for (; element != null; element = element.offsetParent)
	{
		if (!first && relative && BX.is_relative(element))
		{
			break;
		}

		x += element.offsetLeft;
		y += element.offsetTop;

		if (first)
		{
			first = false;
			// eslint-disable-next-line no-continue
			continue;
		}

		x += Text.toNumber(Dom.style(element, 'border-left-width'));
		y += Text.toNumber(Dom.style(element, 'border-top-width'));
	}

	return (new DOMRect(x, y, w, h)).toJSON();
}

export function addCustomEvent(eventObject, eventName, eventHandler)
{
	if (Type.isString(eventObject))
	{
		eventHandler = eventName;
		eventName = eventObject;
		eventObject = EventEmitter.GLOBAL_TARGET;
	}

	if (eventObject === window)
	{
		eventObject = EventEmitter.GLOBAL_TARGET;
	}

	if (!Type.isObject(eventObject))
	{
		console.error('The "eventObject" argument must be an object. Received type '+ typeof(eventObject) + '.');
		return;
	}

	if (!Type.isStringFilled(eventName))
	{
		console.error('The "eventName" argument must be a string.');
		return;
	}

	if (!Type.isFunction(eventHandler))
	{
		console.error('The "eventHandler" argument must be a function. Received type '+ typeof(eventHandler) + '.');
		return;
	}

	eventName = eventName.toLowerCase();

	EventEmitter.subscribe(eventObject, eventName, eventHandler, { compatMode: true, useGlobalNaming: true });
}

export function onCustomEvent(eventObject, eventName, eventParams, secureParams)
{
	if (Type.isString(eventObject))
	{
		secureParams = eventParams;
		eventParams = eventName;
		eventName = eventObject;
		eventObject = EventEmitter.GLOBAL_TARGET;
	}

	if (!Type.isObject(eventObject) || eventObject === window)
	{
		eventObject = EventEmitter.GLOBAL_TARGET;
	}

	if (!eventParams)
	{
		eventParams = [];
	}

	eventName = eventName.toLowerCase();

	const event = new BaseEvent();
	event.setData(eventParams);
	event.setCompatData(eventParams);

	EventEmitter.emit(eventObject, eventName, event, { cloneData: secureParams === true, useGlobalNaming: true });
}

export function removeCustomEvent(eventObject, eventName, eventHandler)
{
	if (Type.isString(eventObject))
	{
		eventHandler = eventName;
		eventName = eventObject;
		eventObject = EventEmitter.GLOBAL_TARGET;
	}

	if (!Type.isFunction(eventHandler))
	{
		console.error('The "eventHandler" argument must be a function. Received type '+ typeof(eventHandler) + '.');
		return;
	}

	if (eventObject === window)
	{
		eventObject = EventEmitter.GLOBAL_TARGET;
	}

	eventName = eventName.toLowerCase();

	EventEmitter.unsubscribe(eventObject, eventName, eventHandler, { useGlobalNaming: true });
}

export function removeAllCustomEvents(eventObject, eventName)
{
	if (Type.isString(eventObject))
	{
		eventName = eventObject;
		eventObject = EventEmitter.GLOBAL_TARGET;
	}

	if (eventObject === window)
	{
		eventObject = EventEmitter.GLOBAL_TARGET;
	}

	eventName = eventName.toLowerCase();

	EventEmitter.unsubscribeAll(eventObject, eventName, { useGlobalNaming: true });
}
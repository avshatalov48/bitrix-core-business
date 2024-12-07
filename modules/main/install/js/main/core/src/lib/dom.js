import Type from './type';
import Event from './event';
import encodeAttributeValue from '../internal/encode-attribute-value';
import decodeAttributeValue from '../internal/decode-attribute-value';
import getPageScroll from '../internal/get-page-scroll';

/**
 * @memberOf BX
 */
export default class Dom
{
	/**
	 * Replaces old html element to new html element
	 * @param oldElement
	 * @param newElement
	 */
	static replace(oldElement: ?HTMLElement, newElement: ?HTMLElement)
	{
		if (Type.isDomNode(oldElement) && Type.isDomNode(newElement))
		{
			if (Type.isDomNode(oldElement.parentNode))
			{
				oldElement.parentNode.replaceChild(newElement, oldElement);
			}
		}
	}

	/**
	 * Removes element
	 * @param element
	 */
	static remove(element: ?HTMLElement)
	{
		if (Type.isDomNode(element) && Type.isDomNode(element.parentNode))
		{
			element.parentNode.removeChild(element);
		}
	}

	/**
	 * Cleans element
	 * @param element
	 */
	static clean(element: HTMLElement | string)
	{
		if (Type.isDomNode(element))
		{
			while (element.childNodes.length > 0)
			{
				element.removeChild(element.firstChild);
			}

			return;
		}

		if (Type.isString(element))
		{
			Dom.clean(document.getElementById(element));
		}
	}

	/**
	 * Inserts element before target element
	 * @param current
	 * @param target
	 */
	static insertBefore(current: ?HTMLElement, target: ?HTMLElement)
	{
		if (Type.isDomNode(current) && Type.isDomNode(target))
		{
			if (Type.isDomNode(target.parentNode))
			{
				target.parentNode.insertBefore(current, target);
			}
		}
	}

	/**
	 * Inserts element after target element
	 * @param current
	 * @param target
	 */
	static insertAfter(current: ?HTMLElement, target: ?HTMLElement)
	{
		if (Type.isDomNode(current) && Type.isDomNode(target))
		{
			if (Type.isDomNode(target.parentNode))
			{
				const parent = target.parentNode;

				if (Type.isDomNode(target.nextSibling))
				{
					parent.insertBefore(current, target.nextSibling);
					return;
				}

				parent.appendChild(current);
			}
		}
	}

	/**
	 * Appends element to target element
	 * @param current
	 * @param target
	 */
	static append(current: ?HTMLElement, target: ?HTMLElement)
	{
		if (Type.isDomNode(current) && Type.isDomNode(target))
		{
			target.appendChild(current);
		}
	}

	/**
	 * Prepends element to target element
	 * @param current
	 * @param target
	 */
	static prepend(current: ?HTMLElement, target: ?HTMLElement)
	{
		if (Type.isDomNode(current) && Type.isDomNode(target))
		{
			if (Type.isDomNode(target.firstChild))
			{
				target.insertBefore(current, target.firstChild);
				return;
			}

			Dom.append(current, target);
		}
	}

	/**
	 * Checks that element contains class name or class names
	 * @param element
	 * @param className
	 * @return {Boolean}
	 */
	static hasClass(element: any, className: string | Array<string>): boolean
	{
		if (Type.isElementNode(element))
		{
			if (Type.isString(className))
			{
				const preparedClassName = className.trim();

				if (preparedClassName.length > 0)
				{
					if (preparedClassName.includes(' '))
					{
						return preparedClassName.split(' ')
							.every(name => Dom.hasClass(element, name));
					}

					if ('classList' in element)
					{
						return element.classList.contains(preparedClassName);
					}

					if (
						Type.isObject(element.className)
						&& Type.isString(element.className.baseVal)
					)
					{
						return element.getAttribute('class').split(' ')
							.some(name => name === preparedClassName);
					}
				}
			}

			if (Type.isArray(className) && className.length > 0)
			{
				return className.every(name => Dom.hasClass(element, name));
			}
		}

		return false;
	}

	/**
	 * Adds class name
	 * @param element
	 * @param className
	 */
	static addClass(element: any, className: string | Array<string>)
	{
		if (Type.isElementNode(element))
		{
			if (Type.isString(className))
			{
				const preparedClassName = className.trim();

				if (preparedClassName.length > 0)
				{
					if (preparedClassName.includes(' '))
					{
						Dom.addClass(element, preparedClassName.split(' '));
						return;
					}

					if ('classList' in element)
					{
						element.classList.add(preparedClassName);
						return;
					}

					if (
						Type.isObject(element.className)
						&& Type.isString(element.className.baseVal)
					)
					{
						if (element.className.baseVal === '')
						{
							element.className.baseVal = preparedClassName;
							return;
						}

						const names = element.className.baseVal.split(' ');

						if (!names.includes(preparedClassName))
						{
							names.push(preparedClassName);
							element.className.baseVal = names.join(' ').trim();
							return;
						}
					}

					return;
				}
			}

			if (Type.isArray(className))
			{
				className.forEach(name => Dom.addClass(element, name));
			}
		}
	}

	/**
	 * Removes class name
	 * @param element
	 * @param className
	 */
	static removeClass(element: any, className: string | Array<string>)
	{
		if (Type.isElementNode(element))
		{
			if (Type.isString(className))
			{
				const preparedClassName = className.trim();

				if (preparedClassName.length > 0)
				{
					if (preparedClassName.includes(' '))
					{
						Dom.removeClass(element, preparedClassName.split(' '));
						return;
					}

					if ('classList' in element)
					{
						element.classList.remove(preparedClassName);
						return;
					}

					if (
						Type.isObject(element.className)
						&& Type.isString(element.className.baseVal)
					)
					{
						const names = element.className.baseVal.split(' ')
							.filter(name => name !== preparedClassName);

						element.className.baseVal = names.join(' ');
						return;
					}
				}
			}

			if (Type.isArray(className))
			{
				className.forEach(name => Dom.removeClass(element, name));
			}
		}
	}

	/**
	 * Toggles class name
	 * @param element
	 * @param className
	 */
	static toggleClass(element: any, className: string | Array<string>)
	{
		if (Type.isElementNode(element))
		{
			if (Type.isString(className))
			{
				const preparedClassName = className.trim();

				if (preparedClassName.length > 0)
				{
					if (preparedClassName.includes(' '))
					{
						Dom.toggleClass(element, preparedClassName.split(' '));
						return;
					}

					element.classList.toggle(preparedClassName);
					return;
				}
			}

			if (Type.isArray(className))
			{
				className.forEach(name => Dom.toggleClass(element, name));
			}
		}
	}

	/**
	 * Styles element
	 */
	static style(
		element: ?HTMLElement,
		prop: ?string | {[key: string]: any},
		value?: any,
	): ?string | number | Element
	{
		if (Type.isElementNode(element))
		{
			if (Type.isNull(prop))
			{
				element.removeAttribute('style');
				return element;
			}

			if (Type.isPlainObject(prop))
			{
				Object.entries(prop).forEach((item) => {
					const [currentKey, currentValue] = item;
					Dom.style(element, currentKey, currentValue);
				});

				return element;
			}

			if (Type.isString(prop))
			{
				if (Type.isUndefined(value)
					&& element.nodeType !== Node.DOCUMENT_NODE)
				{
					const computedStyle = getComputedStyle(element);

					if (prop in computedStyle)
					{
						return computedStyle[prop];
					}

					return computedStyle.getPropertyValue(prop);
				}

				if (
					Type.isNull(value)
					|| value === ''
					|| value === 'null'
				)
				{
					if (String(prop).startsWith('--'))
					{
						// eslint-disable-next-line
						element.style.removeProperty(prop);
						return element;
					}

					// eslint-disable-next-line
					element.style[prop] = '';

					return element;
				}

				if (Type.isString(value) || Type.isNumber(value))
				{
					if (String(prop).startsWith('--'))
					{
						// eslint-disable-next-line
						element.style.setProperty(prop, value);
						return element;
					}

					// eslint-disable-next-line
					element.style[prop] = value;

					return element;
				}
			}
		}

		return null;
	}

	/**
	 * Adjusts element
	 * @param target
	 * @param data
	 * @return {*}
	 */
	static adjust(
		target: HTMLElement | HTMLDocument,
		data: Object = {},
	): HTMLElement | HTMLBodyElement
	{
		if (!target.nodeType)
		{
			return null;
		}

		let element = target;

		if (target.nodeType === Node.DOCUMENT_NODE)
		{
			element = target.body;
		}

		if (Type.isPlainObject(data))
		{
			if (Type.isPlainObject(data.attrs))
			{
				Object.keys(data.attrs).forEach((key) => {
					if (key === 'class' || key.toLowerCase() === 'classname')
					{
						element.className = data.attrs[key];
						return;
					}

					// eslint-disable-next-line
					if (data.attrs[key] == '')
					{
						element.removeAttribute(key);
						return;
					}

					element.setAttribute(key, data.attrs[key]);
				});
			}

			if (Type.isPlainObject(data.style))
			{
				Dom.style(element, data.style);
			}

			if (Type.isPlainObject(data.props))
			{
				Object.keys(data.props).forEach((key) => {
					element[key] = data.props[key];
				});
			}

			if (Type.isPlainObject(data.events))
			{
				Object.keys(data.events).forEach((key) => {
					Event.bind(element, key, data.events[key]);
				});
			}

			if (Type.isPlainObject(data.dataset))
			{
				Object.keys(data.dataset).forEach((key) => {
					element.dataset[key] = data.dataset[key];
				});
			}

			if (Type.isString(data.children))
			{
				data.children = [data.children];
			}

			if (Type.isArray(data.children) && data.children.length > 0)
			{
				data.children.forEach((item) => {
					if (Type.isDomNode(item))
					{
						Dom.append(item, element);
					}

					if (Type.isString(item))
					{
						element.insertAdjacentHTML('beforeend', item);
					}
				});

				return element;
			}

			if ('text' in data && !Type.isNil(data.text))
			{
				element.textContent = data.text;
				return element;
			}

			if ('html' in data && !Type.isNil(data.html))
			{
				element.innerHTML = data.html;
			}
		}

		return element;
	}

	/**
	 * Creates element
	 * @param tag
	 * @param data
	 * @param context
	 * @return {HTMLElement|HTMLBodyElement}
	 */
	static create(
		tag: string | {[key: string]: any},
		data?: Object = {},
		context: HTMLDocument = document,
	)
	{
		let tagName = tag;
		let options = data;

		if (Type.isObjectLike(tag))
		{
			options = tag;
			tagName = tag.tag;
		}

		return Dom.adjust(context.createElement(tagName), options);
	}

	/**
	 * Shows element
	 * @param element
	 */
	static show(element: ?HTMLElement)
	{
		if (Type.isDomNode(element))
		{
			// eslint-disable-next-line
			element.hidden = false;
		}
	}

	/**
	 * Hides element
	 * @param element
	 */
	static hide(element: ?HTMLElement)
	{
		if (Type.isDomNode(element))
		{
			// eslint-disable-next-line
			element.hidden = true;
		}
	}

	/**
	 * Checks that element is shown
	 * @param element
	 * @return {*|boolean}
	 */
	static isShown(element: ?HTMLElement)
	{
		return (
			Type.isDomNode(element)
			&& !element.hidden
			&& element.style.getPropertyValue('display') !== 'none'
		);
	}

	/**
	 * Toggles element visibility
	 * @param element
	 */
	static toggle(element: HTMLElement)
	{
		if (Type.isDomNode(element))
		{
			if (Dom.isShown(element))
			{
				Dom.hide(element);
			}
			else
			{
				Dom.show(element);
			}
		}
	}

	/**
	 * Gets element position relative page
	 * @param {HTMLElement} element
	 * @return {DOMRect}
	 */
	static getPosition(element: HTMLElement): DOMRect
	{
		if (Type.isDomNode(element))
		{
			const elementRect = element.getBoundingClientRect();
			const {scrollLeft, scrollTop} = getPageScroll();

			return new DOMRect(
				(elementRect.left + scrollLeft),
				(elementRect.top + scrollTop),
				elementRect.width,
				elementRect.height,
			);
		}

		return new DOMRect();
	}

	/**
	 * Gets element position relative specified element position
	 * @param {HTMLElement} element
	 * @param {HTMLElement} relationElement
	 * @return {DOMRect}
	 */
	static getRelativePosition(element: HTMLElement, relationElement: HTMLElement): DOMRect
	{
		if (Type.isDomNode(element) && Type.isDomNode(relationElement))
		{
			const elementPosition = Dom.getPosition(element);
			const relationElementPosition = Dom.getPosition(relationElement);

			return new DOMRect(
				elementPosition.left - relationElementPosition.left,
				elementPosition.top - relationElementPosition.top,
				elementPosition.width,
				elementPosition.height,
			);
		}

		return new DOMRect();
	}

	static attr(
		element: ?HTMLElement,
		attr: string | {[key: string]: any},
		value?: any,
	)
	{
		if (Type.isElementNode(element))
		{
			if (Type.isString(attr))
			{
				if (!Type.isNil(value))
				{
					return element.setAttribute(attr, encodeAttributeValue(value));
				}

				if (Type.isNull(value))
				{
					return element.removeAttribute(attr);
				}

				return decodeAttributeValue(element.getAttribute(attr));
			}

			if (Type.isPlainObject(attr))
			{
				return Object.entries(attr).forEach(([attrKey, attrValue]) => {
					Dom.attr(element, attrKey, attrValue);
				});
			}
		}

		return null;
	}
}

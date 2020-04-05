;(function() {
	'use strict';

	BX.namespace('BX.Filter');


	/**
	 * @type {{
	 * 		cache: {},
	 * 		styleForEach: BX.Filter.Utils.styleForEach,
	 * 		closestParent: BX.Filter.Utils.closestParent,
	 * 		closestChilds: BX.Filter.Utils.closestChilds,
	 * 		getNext: BX.Filter.Utils.getNext,
	 * 		getPrev: BX.Filter.Utils.getPrev,
	 * 		collectionSort: BX.Filter.Utils.collectionSort,
	 * 		getIndex: BX.Filter.Utils.getIndex,
	 * 		getByClass: BX.Filter.Utils.getByClass,
	 * 		getByTag: BX.Filter.Utils.getByTag,
	 * 		getBySelector: BX.Filter.Utils.getBySelector,
	 * 		requestAnimationFrame: BX.Filter.Utils.requestAnimationFrame,
	 * 		sortObject: BX.Filter.Utils.sortObject,
	 * 		objectsIsEquals: BX.Filter.Utils.objectsIsEquals,
	 * 		isKey: BX.Filter.Utils.isKey
	 * 	}}
	 */
	BX.Filter.Utils = {
		/** @protected **/
		cache: {},

		/**
		 * Sets css properties for element or elements collection
		 * @param {?HTMLElement|?HTMLElement[]} collection
		 * @param {object} properties
		 */
		styleForEach: function(collection, properties)
		{
			var keys;
			properties = BX.type.isPlainObject(properties) ? properties : null;
			keys = Object.keys(properties);

			[].forEach.call((collection || []), function(current) {
				keys.forEach(function(propKey) {
					BX.style(current, propKey, properties[propKey]);
				});
			});
		},


		/**
		 * Gets closest parent or closest parent element with class name
		 * @param {HTMLElement} item
		 * @param {?string} [className]
		 * @return {?HTMLElement|?Node}
		 */
		closestParent: function(item, className)
		{
			if (item)
			{
				if (!className)
				{
					return item.parentNode || null;
				}
				else
				{
					return BX.findParent(
						item,
						{className: className}
					);
				}
			}
		},


		/**
		 * Gets closest childs elements
		 * @param {HTMLElement} item
		 * @return {?HTMLElement}
		 */
		closestChilds: function(item)
		{
			return !!item ? item.children : null;
		},


		/**
		 * Gets next element
		 * @param {HTMLElement} currentItem
		 * @return {?HTMLElement}
		 */
		getNext: function(currentItem)
		{
			return !!currentItem ? currentItem.nextElementSibling : null;
		},


		/**
		 * Gets previews element
		 * @param {HTMLElement} currentItem
		 * @return {?HTMLElement}
		 */
		getPrev: function(currentItem)
		{
			return !!currentItem ? currentItem.previousElementSibling : null
		},


		/**
		 * Move current item after target item
		 * @param {HTMLElement} current
		 * @param {HTMLElement} target
		 */
		collectionSort: function(current, target)
		{
			var root, collection, collectionLength, currentIndex, targetIndex;

			if (current && target && current !== target && current.parentNode === target.parentNode)
			{
				root = this.closestParent(target);
				collection = this.closestChilds(root);
				collectionLength = collection.length;
				currentIndex = this.getIndex(collection, current);
				targetIndex = this.getIndex(collection, target);

				if (collectionLength === targetIndex) {
					root.appendChild(target);
				}

				if (currentIndex > targetIndex) {
					root.insertBefore(current, target);
				}

				if (currentIndex < targetIndex && collectionLength !== targetIndex)
				{
					root.insertBefore(current, this.getNext(target));
				}
			}
		},


		/**
		 * Gets collection item index
		 * @param {Array|HTMLCollection|NodeList} collection
		 * @param {*} item
		 * @return {int}
		 */
		getIndex: function(collection, item)
		{
			return [].indexOf.call((collection || []), item);
		},


		/**
		 * Gets elements by class name
		 * @param {HTMLElement|HTMLDocument} rootElement
		 * @param {string} className
		 * @param {boolean} [all = false]
		 * @returns {?HTMLElement|?HTMLElement[]}
		 */
		getByClass: function(rootElement, className, all)
		{
			var result = [];

			if (className)
			{
				result = (rootElement || document.body).getElementsByClassName(className);

				if (!all)
				{
					result = result.length ? result[0] : null;
				}
				else
				{
					result = [].slice.call(result);
				}
			}

			return result;
		},


		/**
		 * Gets element or elements by tag name
		 * @param {HTMLElement|HTMLDocument} rootElement
		 * @param {string} tag
		 * @param {boolean} [all = false]
		 * @return {?HTMLElement|?HTMLElement[]}
		 */
		getByTag: function(rootElement, tag, all)
		{
			var result = [];

			if (tag)
			{
				result = (rootElement || document.body).getElementsByTagName(tag);

				if (!all)
				{
					result = result.length ? result[0] : null;
				}
				else
				{
					result = [].slice.call(result);
				}
			}

			return result;
		},


		/**
		 * Gets element or elements by css selector
		 * @param {HTMLElement|HTMLDocument|Node} rootElement
		 * @param {string} selector
		 * @param {boolean} [all = false]
		 * @return {?HTMLElement|?HTMLElement[]}
		 */
		getBySelector: function(rootElement, selector, all)
		{
			var result = [];

			if (selector)
			{
				if (!all)
				{
					result = (rootElement || document.body).querySelector(selector);
				}
				else
				{
					result = (rootElement || document.body).querySelectorAll(selector);
					result = [].slice.call(result);
				}
			}

			return result;
		},

		requestAnimationFrame: function()
		{
			var raf = (
				window.requestAnimationFrame ||
				window.webkitRequestAnimationFrame ||
				window.mozRequestAnimationFrame ||
				window.msRequestAnimationFrame ||
				window.oRequestAnimationFrame ||
				function(callback){ window.setTimeout(callback, 1000/60) }
			);

			raf.apply(window, arguments);
		},


		/**
		 * Sorts object properties
		 * @param {object} input
		 * @return {object}
		 */
		sortObject: function(input)
		{
			var output = {};

			Object.keys(input).sort().forEach(function(key) {
				output[key] = input[key];
			});

			return output;
		},


		/**
		 * Compares two objects or arrays
		 * @param {object} object1
		 * @param {object} object2
		 * @return {boolean}
		 */
		objectsIsEquals: function(object1, object2)
		{
			return JSON.stringify(object1) === JSON.stringify(object2);
		},

		isKey: function(event, keyCode)
		{
			var keyboard = {8: 'backspace', 9: 'tab', 13: 'enter', 16: 'shift', 17: 'ctrl', 18: 'alt', 27: 'escape',
				32: 'space', 37: 'leftArrow', 38: 'upArrow', 39: 'rightArrow', 40: 'downArrow', 46: 'delete',
				112: 'f1', 113: 'f2', 114: 'f3', 115: 'f4', 116: 'f5', 117: 'f6', 118: 'f7', 119: 'f8', 120: 'f9',
				121: 'f10', 122: 'f11', 123: 'f12', 65: 'a'};

			var code = !!event ? (('keyCode' in event) ? event.keyCode : 'which' in event ? event.which : 0) : 0;

			return code in keyboard && keyboard[code] === keyCode;
		}
	};
})();
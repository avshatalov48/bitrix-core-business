;(function() {
	'use strict';

	BX.namespace('BX.Grid');

	BX.Grid.Utils = {
		/**
		 * Prepares url for ajax request
		 * @param {string} url
		 * @param {string} ajaxId Bitrix ajax id
		 * @returns {string} Prepares ajax url with ajax id
		 */
		ajaxUrl: function(url, ajaxId)
		{
			return this.addUrlParams(url, {'bxajaxid': ajaxId});
		},

		addUrlParams: function(url, params)
		{
			return BX.util.add_url_param(url, params);
		},

		/**
		 * Moves array item currentIndex to newIndex
		 * @param {array} array
		 * @param {int} currentIndex
		 * @param {int} newIndex
		 * @returns {*}
		 */
		arrayMove: function(array, currentIndex, newIndex)
		{
			if (newIndex >= array.length)
			{
				var k = newIndex - array.length;
				while ((k--) + 1)
				{
					array.push(undefined);
				}
			}
			array.splice(newIndex, 0, array.splice(currentIndex, 1)[0]);

			return array;
		},

		/**
		 * Gets item index in array or HTMLCollection
		 * @param {array|HTMLCollection} collection
		 * @param {*} item
		 * @returns {number}
		 */
		getIndex: function(collection, item)
		{
			return [].indexOf.call((collection || []), item);
		},

		/**
		 * Gets nextElementSibling
		 * @param {Element} currentItem
		 * @returns {Element|null}
		 */
		getNext: function(currentItem)
		{
			if (currentItem) { return currentItem.nextElementSibling || null; }
		},

		/**
		 * Gets previousElementSibling
		 * @param {Element} currentItem
		 * @returns {Element|null}
		 */
		getPrev: function(currentItem)
		{
			if (currentItem) { return currentItem.previousElementSibling || null; }
		},

		/**
		 * Gets closest parent element of node
		 * @param {Node} item
		 * @param {string} [className]
		 * @returns {*|null|Node}
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
		 * Gets closest childs of node
		 * @param item
		 * @returns {Array|null}
		 */
		closestChilds: function(item)
		{
			if (item) { return item.children || null; }
		},

		/**
		 * Sorts collection
		 * @param current
		 * @param target
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
		 * Gets table collumn
		 * @param table
		 * @param cell
		 * @returns {Array}
		 */
		getColumn: function(table, cell)
		{
			var currentIndex = this.getIndex(
				this.closestChilds(this.closestParent(cell)),
				cell
			);
			var column = [];

			[].forEach.call(table.rows, function(current) {
				column.push(current.cells[currentIndex]);
			});

			return column;
		},

		/**
		 * Sets style properties and values for each item in collection
		 * @param {HTMLElement[]|HTMLCollection} collection
		 * @param {object} properties
		 */
		styleForEach: function(collection, properties)
		{
			properties = BX.type.isPlainObject(properties) ? properties : null;
			var keys = Object.keys(properties);

			[].forEach.call((collection || []), function(current) {
				keys.forEach(function(propKey) {
					BX.style(current, propKey, properties[propKey]);
				});
			});
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
		 * Gets elements by class name
		 * @param rootElement
		 * @param className
		 * @param first
		 * @returns {Array|null}
		 */
		getByClass: function(rootElement, className, first)
		{
			var result = [];

			if (className)
			{
				result = rootElement ? rootElement.getElementsByClassName(className) : [];

				if (first)
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

		getByTag: function(rootElement, tag, first)
		{
			var result = [];

			if (tag)
			{
				result = rootElement ? rootElement.getElementsByTagName(tag) : [];

				if (first)
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

		getBySelector: function(rootElement, selector, first)
		{
			var result = [];

			if (selector)
			{
				if (first)
				{
					result = rootElement ? rootElement.querySelector(selector) : null;
				}
				else
				{
					result = rootElement ? rootElement.querySelectorAll(selector) : [];
					result = [].slice.call(result);
				}
			}

			return result;
		},

		listenerParams: function(params)
		{
			try {
				window.addEventListener('test', null, params);
			} catch (e) {
				params = false;
			}

			return params;
		}
	};
})();
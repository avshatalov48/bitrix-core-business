(function() {
	'use strict';

	BX.namespace('BX.Grid');

	BX.Grid.Utils = {
		/**
		 * Prepares url for ajax request
		 * @param {string} url
		 * @param {string} ajaxId Bitrix ajax id
		 * @returns {string} Prepares ajax url with ajax id
		 */
		ajaxUrl(url, ajaxId)
		{
			return this.addUrlParams(url, { bxajaxid: ajaxId });
		},

		addUrlParams(url, params)
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
		arrayMove(array, currentIndex, newIndex)
		{
			if (newIndex >= array.length)
			{
				let k = newIndex - array.length;
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
		getIndex(collection, item)
		{
			return [].indexOf.call((collection || []), item);
		},

		/**
		 * Gets nextElementSibling
		 * @param {Element} currentItem
		 * @returns {Element|null}
		 */
		getNext(currentItem)
		{
			if (currentItem)
			
			{ return currentItem.nextElementSibling || null;
			}
		},

		/**
		 * Gets previousElementSibling
		 * @param {Element} currentItem
		 * @returns {Element|null}
		 */
		getPrev(currentItem)
		{
			if (currentItem)
			
			{ return currentItem.previousElementSibling || null;
			}
		},

		/**
		 * Gets closest parent element of node
		 * @param {Node} item
		 * @param {string} [className]
		 * @returns {*|null|Node}
		 */
		closestParent(item, className)
		{
			if (item)
			{
				if (!className)
				{
					return item.parentNode || null;
				}

				return BX.findParent(
					item,
					{ className },
				);
			}
		},

		/**
		 * Gets closest childs of node
		 * @param item
		 * @returns {Array|null}
		 */
		closestChilds(item)
		{
			if (item)
			
			{ return item.children || null;
			}
		},

		/**
		 * Sorts collection
		 * @param current
		 * @param target
		 */
		collectionSort(current, target)
		{
			let root; let collection; let collectionLength; let currentIndex; let
				targetIndex;

			if (current && target && current !== target && current.parentNode === target.parentNode)
			{
				root = this.closestParent(target);
				collection = this.closestChilds(root);
				collectionLength = collection.length;
				currentIndex = this.getIndex(collection, current);
				targetIndex = this.getIndex(collection, target);

				if (collectionLength === targetIndex)
				{
					root.appendChild(target);
				}

				if (currentIndex > targetIndex)
				{
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
		getColumn(table, cell)
		{
			const currentIndex = this.getIndex(
				this.closestChilds(this.closestParent(cell)),
				cell,
			);
			const column = [];

			[].forEach.call(table.rows, (current) => {
				column.push(current.cells[currentIndex]);
			});

			return column;
		},

		/**
		 * Sets style properties and values for each item in collection
		 * @param {HTMLElement[]|HTMLCollection} collection
		 * @param {object} properties
		 */
		styleForEach(collection, properties)
		{
			properties = BX.type.isPlainObject(properties) ? properties : null;
			const keys = Object.keys(properties);

			[].forEach.call((collection || []), (current) => {
				keys.forEach((propKey) => {
					BX.style(current, propKey, properties[propKey]);
				});
			});
		},

		requestAnimationFrame()
		{
			const raf = (
				window.requestAnimationFrame
				|| window.webkitRequestAnimationFrame
				|| window.mozRequestAnimationFrame
				|| window.msRequestAnimationFrame
				|| window.oRequestAnimationFrame
				|| function(callback) { window.setTimeout(callback, 1000 / 60); }
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
		getByClass(rootElement, className, first)
		{
			let result = [];

			if (className)
			{
				result = rootElement ? rootElement.getElementsByClassName(className) : [];

				if (first)
				{
					result = result.length > 0 ? result[0] : null;
				}
				else
				{
					result = [].slice.call(result);
				}
			}

			return result;
		},

		getByTag(rootElement, tag, first)
		{
			let result = [];

			if (tag)
			{
				result = rootElement ? rootElement.getElementsByTagName(tag) : [];

				if (first)
				{
					result = result.length > 0 ? result[0] : null;
				}
				else
				{
					result = [].slice.call(result);
				}
			}

			return result;
		},

		getBySelector(rootElement, selector, first)
		{
			let result = [];

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

		listenerParams(params)
		{
			try
			{
				window.addEventListener('test', null, params);
			}
			catch
			{
				params = false;
			}

			return params;
		},
	};
})();

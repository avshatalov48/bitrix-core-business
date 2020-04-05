(function() {
	"use strict";

	BX.namespace("BX.Landing");

	/**
	 * Landing utils.
	 */
	BX.Landing.Utils = function() {};


	/**
	 * Shows element
	 * @param {Element|HTMLElement} element
	 * @return {Promise}
	 * @constructor
	 */
	BX.Landing.Utils.Show = function(element)
	{
		return new Promise(function(resolve) {
			if (!!element && !BX.Landing.Utils.isShown(element))
			{
				BX.Landing.Utils.onAnimationEnd(element)
					.then(function(event) {
						element.dataset.isShown = true;
						resolve(event);
					});

				requestAnimationFrame(function() {
					element.hidden = false;
					element.classList.remove("landing-ui-hide");
					element.classList.add("landing-ui-show");
				});
			}
			else
			{
				resolve();
			}
		});
	};


	/**
	 * Checks that element is shown
	 * @param {HTMLElement} element
	 * @return {boolean}
	 */
	BX.Landing.Utils.isShown = function(element)
	{
		return element.dataset.isShown === "true";
	};


	/**
	 * Hides element
	 * @param {HTMLElement} element
	 * @return {Promise}
	 * @constructor
	 */
	BX.Landing.Utils.Hide = function(element)
	{
		return new Promise(function(resolve) {
			if (!!element && BX.Landing.Utils.isShown(element))
			{
				BX.Landing.Utils.onAnimationEnd(element)
					.then(function(event) {
						element.hidden = true;
						element.dataset.isShown = false;
						resolve(event);
					});

				requestAnimationFrame(function() {
					element.classList.remove("landing-ui-show");
					element.classList.add("landing-ui-hide");
				});
			}
			else
			{
				resolve();
			}
		});
	};

	BX.Landing.Utils.isValidElementId = function(id)
	{
		var re = new RegExp('^[A-Za-z]+[\\w\\-\\:\\.]*$');
		return re.test(id)
	};

	BX.Landing.Utils.ignorePromiseDecorator = function(fn)
	{
		var nothing = function() {};

		return function() {
			fn.apply(null, arguments).then(nothing);
		}
	};


	BX.Landing.Utils.appendHTML = function(element, html)
	{
		element.innerHTML = element.innerHTML + html;
	};


	/**
	 * Gets CSS unique selector of element
	 * @param {HTMLElement} element
	 * @return {string}
	 */
	BX.Landing.Utils.getCSSSelector = function(element)
	{
		var names = [];

		while (element.parentNode)
		{
			if (element.id)
			{
				names.unshift('#'+element.id);
				break;
			}
			else
			{
				if (element === element.ownerDocument.documentElement)
				{
					names.unshift(element.tagName.toLowerCase());
				}
				else
				{
					for (var c=1, e=element; e.previousElementSibling; e=e.previousElementSibling, c++)
					{
					}
					names.unshift(element.tagName.toLowerCase()+":nth-child("+c+")");
				}

				element = element.parentNode;
			}
		}
		return names.join(" > ");
	};


	/**
	 * Handles transition end event
	 * @param {HTMLElement|HTMLElement[]} elements
	 */
	BX.Landing.Utils.onTransitionEnd = function(elements)
	{
		elements = BX.type.isArray(elements) ? elements : [elements];

		return Promise.all(elements.map(function(element) {
			return new Promise(function(resolve) {
				element.addEventListener("webkitTransitionEnd", resolve);
				element.addEventListener("transitionend", resolve);
				element.addEventListener("msTransitionEnd", resolve);
				element.addEventListener("oTransitionEnd", resolve);
				return resolve;
			}).then(function(resolver) {
				element.removeEventListener("webkitTransitionEnd", resolver);
				element.removeEventListener("transitionend", resolver);
				element.removeEventListener("msTransitionEnd", resolver);
				element.removeEventListener("oTransitionEnd", resolver);
			})
		}));
	};


	/**
	 * Handles animationend event
	 * @param {Element} element
	 * @param {string} [animationName]
	 * @return {Promise<AnimationEvent>}
	 */
	BX.Landing.Utils.onAnimationEnd = function(element, animationName)
	{
		return new Promise(function(resolve) {
			var onAnimationEnd = function(event)
			{
				if (!animationName || (event.animationName === animationName))
				{
					resolve(event);
					element.removeEventListener("animationend", onAnimationEnd);
					element.removeEventListener("oAnimationEnd", onAnimationEnd);
					element.removeEventListener("webkitAnimationEnd", onAnimationEnd);
				}
			};

			element.addEventListener("animationend", onAnimationEnd);
			element.addEventListener("oAnimationEnd", onAnimationEnd);
			element.addEventListener("webkitAnimationEnd", onAnimationEnd);
		});
	};


	/**
	 * Converts html to Element
	 * @param {string} html
	 * @return {?HTMLElement}
	 */
	BX.Landing.Utils.htmlToElement = function(html)
	{
		return BX.create("div", {html: html}).firstElementChild;
	};


	/**
	 * Converts html to DocumentFragment
	 * @param {string} html
	 * @return {DocumentFragment}
	 */
	BX.Landing.Utils.htmlToFragment = function(html)
	{
		var tmpElement = BX.create("div", {html: html});
		var fragment = document.createDocumentFragment();

		[].slice.call(tmpElement.children).forEach(function(element) {
			fragment.appendChild(element);
		});

		return fragment;
	};


	/**
	 * Freezes object
	 * @param {object} object
	 * @return {object}
	 */
	BX.Landing.Utils.deepFreeze = function(object)
	{
		Object.freeze(object);

		Object.keys(object).forEach(function(prop) {
			if (!!object[prop] && (typeof object[prop] === "object" || typeof object[prop] === "function"))
			{
				BX.Landing.Utils.deepFreeze(object[prop]);
			}
		});

		return object;
	};


	/**
	 * Inserts element with a specified position
	 * @param {HTMLElement} container
	 * @param {HTMLElement} element
	 * @param {int} position
	 * @static
	 */
	BX.Landing.Utils.insert = function(container, element, position)
	{
		if (position === 0)
		{
			BX.prepend(element, container);
		}
		else if (position > 0 && position <= container.children.length-1)
		{
			container.insertBefore(element, container.children[position]);
		}
		else
		{
			container.appendChild(element);
		}
	};

	/**
	 * Contains RegExp's form media services
	 * @type {{
	 * 		youtube: RegExp,
	 * 		vimeo: RegExp,
	 * 		vine: RegExp,
	 * 		instagram: RegExp,
	 * 		googleMapsSearch: RegExp,
	 * 		googleMapsPlace: RegExp
	 * 	}}
	 */
	BX.Landing.Utils.Matchers = {
		youtube: new RegExp("(youtube\\.com|youtu\\.be|youtube\\-nocookie\\.com)\\/(watch\\?(.*&)?v=|v\\/|u\\/|embed\\/?)?(videoseries\\?list=(.*)|[\\w-]{11}|\\?listType=(.*)&list=(.*))(.*)"),
		vimeo: new RegExp("^.+vimeo.com\\/(.*\\/)?([\\d]+)(.*)?"),
		vine: new RegExp("vine.co\\/v\\/([a-zA-Z0-9\\?\\=\\-]+)"),
		instagram: new RegExp("(instagr\\.am|instagram\\.com)\\/p\\/([a-zA-Z0-9_\\-]+)\\/?"),

		// Examples:
		// https://www.google.com/maps/search/Bitrix24+office/
		// https://www.google.com/maps/search/?api=1&query=Bitrix24+office
		// https://www.google.com/maps/search/?api=1&query=47.5951518,-122.3316393
		googleMapsSearch: new RegExp("(maps\\.)?google\\.([a-z]{2,3}(\\.[a-z]{2})?)\\/(maps\\/search\\/)(.*)", "i"),

		// Examples:
		// http://maps.google.com/?ll=48.857995,2.294297&spn=0.007666,0.021136&t=m&z=16
		// https://www.google.com/maps/@37.7852006,-122.4146355,14.65z
		// https://www.google.com/maps/place/Bitrix24+office/@37.4220041,-122.0833494,17z/data=!4m5!3m4!1s0x0:0x6c296c66619367e0!8m2!3d37.4219998!4d-122.0840572
		googleMapsPlace: new RegExp("(maps\\.)?google\\.([a-z]{2,3}(\\.[a-z]{2})?)\\/(((maps\\/(place\\/(.*)\\/)?\\@(.*),(\\d+.?\\d+?)z))|(\\?ll=))(.*)?", "i"),
		headerTag: new RegExp("^H[1-6]$"),
		russianText: new RegExp("[\u0400-\u04FF]"),
		facebookPages: new RegExp("(?:http:\/\/)?(?:www\.)?facebook\.com\/(?:(?:\w)*#!\/)?(?:pages\/)?(?:[\w\-]*\/)*([\w\-]*)"),
		facebookPosts: new RegExp("^https:\/\/www\.facebook\.com\/(photo(\.php|s)|permalink\.php|media|questions|notes|[^\/]+\/(activity|posts))[\/?].*$"),
		facebookVideos: new RegExp("^(?:(?:https?:)?\/\/)?(?:www\.)?facebook\.com\/[a-z0-9\.]+\/videos\/(?:[a-z0-9\.]+\/)?([0-9]+)\/?(?:\\?.*)?$")
	};


	/**
	 * Gets URL preview object
	 * @param {string} url
	 * @return {Promise<Object, Object>}
	 */
	BX.Landing.Utils.getURLPreview = function(url)
	{
		return BX.Landing.Backend.getInstance()
			.action("Utils::getUrlPreview", {url: url});
	};


	/**
	 * Converts HTML string to HTMLElement
	 * @param {string} html
	 * @return {HTMLElement}
	 * @static
	 */
	BX.Landing.Utils.HTMLToElement = function(html)
	{
		return BX.create("div", {html: html}).firstElementChild;
	};



	/**
	 * Gets all URL params with value
	 * @param {string} url
	 * @return {object.<string, string>}
	 */
	BX.Landing.Utils.getQueryParams = function(url)
	{
		var result = {};

		if (typeof url === "string")
		{
			var queryString = url.split("?")[1];

			if (queryString)
			{
				var vars = queryString.split("&");

				for (var i = 0; i < vars.length; i++)
				{
					var pair = vars[i].split("=");

					if (typeof result[pair[0]] === "undefined")
					{
						result[pair[0]] = decodeURIComponent(pair[1]);
					}
					else if (typeof result[pair[0]] === "string")
					{
						result[pair[0]] = [result[pair[0]], decodeURIComponent(pair[1])];
					}
					else
					{
						result[pair[0]].push(decodeURIComponent(pair[1]));
					}
				}
			}
		}

		return result;
	};


	/**
	 * Escapes html
	 * @param {string} html
	 * @return {string}
	 */
	BX.Landing.Utils.escapeHtml = function(html)
	{
		return BX.util.htmlspecialchars(
			BX.util.htmlspecialcharsback("" + html)
		);
	};


	/**
	 * Escapes text
	 * @param {*} text
	 * @return {string}
	 */
	BX.Landing.Utils.escapeText = function(text)
	{
		var result = text;

		if (typeof text === "number" || typeof text === "boolean")
		{
			result = "" + text;
		}
		else if (!!text && typeof text === "object")
		{
			result = JSON.stringify(text);
		}

		return BX.Landing.Utils.escapeHtml(result);
	};


	/**
	 * Escapes attribute value
	 * @param {*} value
	 * @return {string}
	 */
	BX.Landing.Utils.escapeAttributeValue = function(value)
	{
		if (BX.Landing.Utils.isPlainObject(value) || BX.Landing.Utils.isArray(value))
		{
			value = JSON.stringify(value);
		}

		return BX.util.jsencode("" + value);
	};


	/**
	 * Sets text content to node
	 * @param {HTMLElement} element
	 * @param {string} text
	 */
	BX.Landing.Utils.setTextContent = function(element, text)
	{
		if (typeof text === "string")
		{
			var firstNode = element.firstChild;

			if (firstNode &&
				firstNode === element.lastChild &&
				firstNode.nodeType === Node.TEXT_NODE)
			{
				firstNode.nodeValue = text;
				return;
			}
		}

		element.textContent = text;
	};


	/**
	 * Encodes data attribute value
	 * @param {*} value
	 * @return {string}
	 */
	BX.Landing.Utils.encodeDataValue = function(value)
	{
		if (BX.Landing.Utils.isPlainObject(value) || BX.Landing.Utils.isArray(value))
		{
			value = JSON.stringify(value);
		}
		else
		{
			if (BX.Landing.Utils.isString(value))
			{
				value = BX.Landing.Utils.escapeHtml(value);
			}
		}

		return "" + value;
	};


	/**
	 * Decodes data attribute value
	 * @param {string} value
	 * @return {*}
	 */
	BX.Landing.Utils.decodeDataValue = function(value)
	{
		var result = value;

		try
		{
			result = JSON.parse(value);
		}
		catch(e)
		{
			result = value;
		}

		if (BX.Landing.Utils.isString(result))
		{
			result = BX.util.htmlspecialcharsback(result);
		}

		return result;
	};


	/**
	 * Works with data-attributes.
	 * @param {HTMLElement} element
	 * @param {string|object} [name]
	 * @param {*} [value]
	 * @return {*}
	 */
	BX.Landing.Utils.data = function(element, name, value)
	{
		var decodeDataValue = BX.Landing.Utils.decodeDataValue;
		var encodeDataValue = BX.Landing.Utils.encodeDataValue;
		var isPlainObject = BX.Landing.Utils.isPlainObject;
		var isString = BX.Landing.Utils.isString;
		var dataRegExp = new RegExp("^data-");

		if (!element)
		{
			throw new TypeError("Element is required");
		}

		// Get all data attributes if name not set
		if (!name)
		{
			var result = {};

			[].forEach.call(element.attributes, function(attr) {
				if (dataRegExp.test(attr.name))
				{
					result[attr.name] = decodeDataValue(attr.value);
				}
			});

			return result;
		}

		if (isString(name))
		{
			name = !dataRegExp.test(name) ? "data-" + name : name;

			// Get single value
			if (value === undefined)
			{
				return decodeDataValue(element.getAttribute(name));
			}

			// Remove attribute
			if (value === null)
			{
				return element.removeAttribute(name);
			}

			// Sets single value
			return element.setAttribute(name, encodeDataValue(value));
		}

		if (isPlainObject(name))
		{
			// Sets attributes values from object
			Object.keys(name).forEach(function(attr) {
				BX.Landing.Utils.data(element, attr, name[attr]);
			});
		}
	};


	function getTextNodes(el)
	{
		el = el || document.body;

		var doc = el.ownerDocument || document;
		var walker = doc.createTreeWalker(el, NodeFilter.SHOW_TEXT, null, false);
		var textNodes = [];
		var node;

		while (node = walker.nextNode())
		{
			textNodes.push(node)
		}

		return textNodes
	}

	function rangesIntersect(rangeA, rangeB)
	{
		return rangeA.compareBoundaryPoints(Range.END_TO_START, rangeB) === -1 &&
			rangeA.compareBoundaryPoints(Range.START_TO_END, rangeB) === 1
	}

	function createRangeFromNode(node)
	{
		var range = node.ownerDocument.createRange();

		try {
			range.selectNode(node)
		} catch (e) {
			range.selectNodeContents(node)
		}

		return range
	}

	function rangeIntersectsNode(range, node)
	{
		if (range.intersectsNode)
		{
			return range.intersectsNode(node)
		}
		else
		{
			return rangesIntersect(range, createRangeFromNode(node))
		}
	}

	function getRangeTextNodes(range)
	{
		var container = range.commonAncestorContainer;
		var nodes = getTextNodes(container.parentNode || container);

		return nodes.filter(function (node) {
			return rangeIntersectsNode(range, node) && isNonEmptyTextNode(node);
		})
	}

	function isNonEmptyTextNode(node)
	{
		return node.textContent.length > 0;
	}

	function remove(el)
	{
		if (el.parentNode)
		{
			el.parentNode.removeChild(el);
		}
	}

	function replaceNode(replacementNode, node)
	{
		remove(replacementNode);
		node.parentNode.insertBefore(replacementNode, node);
		remove(node)
	}

	function unwrap(el)
	{
		var range = document.createRange();
		range.selectNodeContents(el);
		replaceNode(range.extractContents(), el);
	}

	function undo(nodes)
	{
		nodes.forEach(function (node) {
			var parent = node.parentNode;
			unwrap(node);
			parent.normalize();
		})
	}

	function createWrapperFunction(wrapperEl, range)
	{
		var startNode = range.startContainer;
		var endNode = range.endContainer;
		var startOffset = range.startOffset;
		var endOffset = range.endOffset;

		return function wrapNode(node)
		{
			var currentRange = document.createRange();
			var currentWrapper = wrapperEl;

			currentRange.selectNodeContents(node);

			if (node === startNode && startNode.nodeType === 3)
			{
				currentRange.setStart(node, startOffset);
				startNode = currentWrapper;
				startOffset = 0;
			}

			if (node === endNode && endNode.nodeType === 3)
			{
				currentRange.setEnd(node, endOffset);
				endNode = currentWrapper;
				endOffset = 1;
			}

			currentRange.surroundContents(currentWrapper);
			return currentWrapper;
		}
	}

	BX.Landing.Utils.wrapSelection = function(wrapperEl, range)
	{
		var nodes;
		var wrapNode;
		var wrapperObj = {};

		if (typeof range === 'undefined')
		{
			range = window.getSelection().getRangeAt(0);
		}

		if (range.isCollapsed)
		{
			return [];
		}

		if (typeof wrapperEl === 'undefined')
		{
			wrapperEl = 'span';
		}

		if (typeof wrapperEl === 'string')
		{
			wrapperEl = document.createElement(wrapperEl);
		}

		wrapNode = createWrapperFunction(wrapperEl, range);

		nodes = getRangeTextNodes(range);
		nodes = nodes.map(wrapNode);

		wrapperObj.nodes = nodes;
		wrapperObj.unwrap = function() {
			if (this.nodes.length)
			{
				undo(this.nodes);
				this.nodes = [];
			}
		};

		return wrapperObj;
	};


	BX.Landing.Utils.createRangeFromNode = createRangeFromNode;


	/**
	 * Creates selection range for node, from start and end points
	 * @param {HTMLElement} el
	 * @param {int} start
	 * @param {int} end
	 * @return {Range}
	 */
	BX.Landing.Utils.createSelectionRange = function(el, start, end)
	{
		var range;

		if (document.createRange && window.getSelection)
		{
			range = document.createRange();
			range.selectNodeContents(el);
			var textNodes = getTextNodes(el);
			var foundStart = false;
			var charCount = 0, endCharCount;

			for (var i = 0, textNode; textNode = textNodes[i++]; )
			{
				endCharCount = charCount + textNode.length;
				if (!foundStart && start >= charCount && (start < endCharCount || (start === endCharCount && i <= textNodes.length)))
				{
					range.setStart(textNode, start - charCount);
					foundStart = true;
				}

				if (foundStart && end <= endCharCount)
				{
					range.setEnd(textNode, end - charCount);
					break;
				}

				charCount = endCharCount;
			}
		}
		else if (document.selection && document.body.createTextRange)
		{
			range = document.body.createTextRange();
			range.moveToElementText(el);
			range.collapse(true);
			range.moveEnd("character", end);
			range.moveStart("character", start);
		}

		return range;
	};


	/**
	 * Sets element styles
	 *
	 * @param {HTMLElement} element
	 * @param {?object} styles - Null removes all styles
	 *
	 * @return {Promise} - Resolves when styles applied
	 */
	BX.Landing.Utils.style = function(element, styles)
	{
		return new Promise(function(resolve) {
			if (styles === null)
			{
				requestAnimationFrame(function() {
					element.style = null;
					resolve();
				});
			}

			if (!!styles && typeof styles === "object")
			{
				requestAnimationFrame(function() {
					Object.keys(styles).forEach(function(style) {
						element.style.setProperty(style, styles[style]);
					});
					resolve();
				});
			}
		});
	};


	/**
	 * Translates element by y axis
	 * @param {HTMLElement} element
	 * @param {Number} translateLength
	 * @return {Promise}
	 */
	BX.Landing.Utils.translateY = function(element, translateLength)
	{
		return BX.Landing.Utils.translate("y", element, translateLength);
	};


	/**
	 * Translates element by x axis
	 * @param {HTMLElement} element
	 * @param {Number} translateLength
	 * @return {Promise}
	 */
	BX.Landing.Utils.translateX = function(element, translateLength)
	{
		return BX.Landing.Utils.translate("x", element, translateLength);
	};


	/**
	 * Translates element by axis
	 * @param {string} axis - x|y
 	 * @param {HTMLElement} element
	 * @param {Number} translateLength
	 * @return {Promise}
	 */
	BX.Landing.Utils.translate = function(axis, element, translateLength)
	{
		void BX.Landing.Utils.style(element, {
			"transition": "transform 200ms ease",
			"transform": "translate"+axis.toUpperCase()+"("+translateLength+"px) translateZ(0)"
		});
		return BX.Landing.Utils.onTransitionEnd(element);
	};


	/**
	 * Inserts element before target element
	 * @param {HTMLElement} element
	 * @param {HTMLElement} targetElement
	 */
	BX.Landing.Utils.insertBefore = function(element, targetElement)
	{
		targetElement.parentElement.insertBefore(element, targetElement);
	};


	/**
	 * Gets bounding client rect of element or range
	 * @param {Element|Range} element
	 * @return {ClientRect | {left, top, width, height}}
	 */
	BX.Landing.Utils.rect = function(element)
	{
		return element.getBoundingClientRect();
	};


	/**
	 * Finds next element sibling
	 * @param {HTMLElement} element
	 * @param {String} [className]
	 * @return {HTMLElement}
	 */
	BX.Landing.Utils.nextSibling = function(element, className)
	{
		return className ? BX.findNextSibling(element, {className: className}) : element.nextElementSibling;
	};


	/**
	 * Finds previous element sibling
	 * @param {HTMLElement} element
	 * @param {String} className
	 * @return {HTMLElement}
	 */
	BX.Landing.Utils.prevSibling = function(element, className)
	{
		return className ? BX.findPreviousSibling(element, {className: className}) : element.previousElementSibling;
	};


	/**
	 * Joins arguments
	 * @return {string}
	 */
	BX.Landing.Utils.join = function()
	{
		return [].slice.call(arguments).join("");
	};


	/**
	 * @param item
	 * @return {*[]}
	 */
	BX.Landing.Utils.slice = function(item)
	{
		return [].slice.call(item);
	};


	/**
	 * Set element attributes
	 * @param {HTMLElement|Element} element
	 * @param {Object.<String, ?String>|String} attrs
	 * @param {?String} [value]
	 * @return {String|void}
	 */
	BX.Landing.Utils.attr = function(element, attrs, value)
	{
		if (BX.Landing.Utils.isString(attrs))
		{
			if (typeof value === "undefined")
			{
				return element.getAttribute(attrs);
			}

			element.setAttribute(attrs, BX.Landing.Utils.encodeDataValue(value));
		}

		if (BX.Landing.Utils.isPlainObject(attrs))
		{
			Object.keys(attrs).forEach(function(key) {
				if (attrs[key] === null)
				{
					element.removeAttribute(key);
				}
				else
				{
					element.setAttribute(key, BX.Landing.Utils.encodeDataValue(attrs[key]));
				}
			});
		}
	};


	/**
	 * Removes all panels from element
	 * @param {HTMLElement} element
	 * @return {?HTMLElement}
	 */
	BX.Landing.Utils.removePanels = function(element)
	{
		[].slice.call(element.querySelectorAll(".landing-ui-panel"))
			.forEach(function(panel) {
				BX.remove(panel);
			});
		return element;
	};


	/**
	 * Gets file extension
	 * @param {string} filename
	 * @return {string}
	 */
	BX.Landing.Utils.getFileExtension = function(filename)
	{
		var name = "fm";
		var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)");
		var results = regex.exec(filename);

		if (!results || !results[2])
		{
			return '';
		}

		return decodeURIComponent(results[2].replace(/\+/g, " "));
	};


	/**
	 * Checks pressed key
	 * @type {{
	 * 		isUp: BX.Landing.Utils.key.isUp,
	 * 		isDown: BX.Landing.Utils.key.isDown,
	 * 		isRight: BX.Landing.Utils.key.isRight,
	 * 		isLeft: BX.Landing.Utils.key.isLeft,
	 * 		isEnter: BX.Landing.Utils.key.isEnter,
	 * 		isEscape: BX.Landing.Utils.key.isEscape
	 * 	}}
	 */
	BX.Landing.Utils.key = {
		isUp: function(event) {
			return event.keyCode === 38;
		},

		isDown: function(event) {
			return event.keyCode === 40;
		},

		isRight: function(event) {
			return event.keyCode === 39;
		},

		isLeft: function(event) {
			return event.keyCode === 37;
		},

		isEnter: function(event) {
			return event.keyCode === 13;
		},

		isEscape: function(event)
		{
			return event.keyCode === 27;
		}
	};


	/**
	 * Extends BX.PopupMenuWindow. Adds menu items filter/search
	 * @param {BX.PopupMenuWindow} menu
	 */
	BX.Landing.Utils.makeFilterablePopupMenu = function(menu)
	{
		var append = BX.Landing.Utils.append;
		var prepend = BX.Landing.Utils.prepend;
		var onCustomEvent = BX.Landing.Utils.onCustomEvent;
		var create = BX.Landing.Utils.create;
		var addClass = BX.Landing.Utils.addClass;


		/**
		 * Checks that menuItems list contains visible item
		 * @param {BX.PopupMenuItem[]} menuItems
		 * @return {boolean}
		 */
		function containsVisibleItems(menuItems)
		{
			return menuItems.some(function(menuItem) {
				return !menuItem.layout.item.hidden;
			});
		}


		/**
		 * Handles filter input event
		 * @param event
		 */
		function onInput(event)
		{
			var search = event.currentTarget.value.toLowerCase();
			menu.menuItems.forEach(function(item) {
				item.layout.item.hidden = !item.text.toLowerCase().includes(search);
			});

			emptyResult.hidden = containsVisibleItems(menu.menuItems);
		}

		var filter = create("div", {props: {className: "landing-ui-popup-filter"}});
		var filterInput = create("input", {
			props: {className: "landing-ui-popup-filter-input"},
			attrs: {placeholder: BX.message("LANDING_MENU_ITEM_FILTER")},
			events: {"input": onInput}
		});
		var emptyResult = create("div", {
			props: {className: "landing-ui-popup-filter-empty"},
			children: [create("span", {
				props: {className: "landing-ui-popup-filter-empty-text"},
				html: BX.message("LANDING_MENU_ITEM_FILTER_EMPTY")
			})],
			attrs: {hidden: true}
		});

		append(filterInput, filter);
		prepend(filter, menu.popupWindow.contentContainer);
		append(emptyResult, menu.popupWindow.contentContainer);

		addClass(menu.popupWindow.popupContainer, "landing-ui-popup-filterable");

		filterInput.focus();

		onCustomEvent(menu.popupWindow, "onAfterPopupShow", function() {
			requestAnimationFrame(function() {
				filterInput.focus()
			});
		});
	};


	/**
	 * Extends BX.PopupMenuWindow. Makes navigation by arrows keys
	 * @param {BX.PopupMenuWindow} menu
	 */
	BX.Landing.Utils.makeSelectablePopupMenu = function(menu)
	{
		var addClass = BX.Landing.Utils.addClass;
		var removeClass = BX.Landing.Utils.removeClass;
		var hasClass = BX.Landing.Utils.hasClass;
		var bind = BX.Landing.Utils.bind;
		var key = BX.Landing.Utils.key;

		var currentItem = null;
		var currentIndex = -1;

		/**
		 * Selects menu items
		 * @param {BX.PopupMenuItem} menuItem
		 */
		function selectItem(menuItem)
		{
			addClass(menuItem.layout.item, "landing-ui-select");
		}


		/**
		 * Removes selection from menu item
		 * @param {BX.PopupMenuItem} menuItem
		 */
		function unselectMenuItem(menuItem)
		{
			removeClass(menuItem.layout.item, "landing-ui-select");
		}


		/**
		 * Removes selection from all menu items
		 * @param {BX.PopupMenuItem[]} menuItems
		 */
		function unselectItems(menuItems)
		{
			menuItems.forEach(unselectMenuItem);
		}


		/**
		 * Get selected item
		 * @param {BX.PopupMenuItem[]} menuItems
		 */
		function getSelected(menuItems)
		{
			return menuItems.find(function(item) {
				return hasClass(item.layout.item, "landing-ui-select");
			});
		}


		/**
		 * Gets menu item index
		 * @param {BX.PopupMenuItem[]} menuItems
		 * @param {BX.PopupMenuItem} menuItem
		 * @return {number|*}
		 */
		function getItemIndex(menuItems, menuItem)
		{
			return menuItems.findIndex(function(item) {
				return menuItem === item;
			});
		}


		/**
		 * Gets first item
		 * @param {BX.PopupMenuItem[]} menuItems
		 * @return {?BX.PopupMenuItem}
		 */
		function getFirstItem(menuItems)
		{
			return menuItems.find(function(item) {
				return !item.layout.item.hidden;
			});
		}


		/**
		 * Gets net item
		 * @param {BX.PopupMenuItem[]} menuItems
		 * @return {BX.PopupMenuItem}
		 */
		function getNextItem(menuItems)
		{
			if (currentItem)
			{
				currentIndex = getItemIndex(menuItems, currentItem);
			}

			var nextItem = menuItems.find(function(item, index) {
				return index > currentIndex && !item.layout.item.hidden;
			});

			if (nextItem)
			{
				currentItem = nextItem;
				return nextItem;
			}

			nextItem = getFirstItem(menuItems);
			currentItem = nextItem;

			return nextItem;
		}


		/**
		 * Checks that pressed key from allowed list
		 * @param {KeyboardEvent} event
		 * @return {boolean}
		 */
		function isAllowedKeyPress(event)
		{
			var key = BX.Landing.Utils.key;

			return (
				key.isLeft(event) ||
				key.isRight(event) ||
				key.isUp(event) ||
				key.isDown(event) ||
				key.isEnter(event)
			);
		}


		/**
		 * Closes all sub menu
		 * @param {BX.PopupMenuItem[]} menuItems
		 */
		function closeAllSubMenu(menuItems)
		{
			menuItems.forEach(function(item) {
				item.closeSubMenu();

				var subMenu = item.getSubMenu();

				if (subMenu)
				{
					unselectItems(subMenu.menuItems);
				}
			});
		}

		var isRevert = false;

		bind(menu.popupWindow.popupContainer, "keydown", function(event) {
			var currentMenu = menu;
			if (currentItem && currentItem.menuWindow.popupWindow.isShown())
			{
				currentMenu = currentItem.menuWindow;
			}

			if (isAllowedKeyPress(event))
			{
				var selectedItem = getSelected(currentMenu.menuItems);

				if (key.isDown(event) && isRevert && currentMenu === menu)
				{
					isRevert = false;
					currentMenu.menuItems = currentMenu.menuItems.reverse();
				}

				if (key.isUp(event) && !isRevert && currentMenu === menu)
				{
					isRevert = true;
					currentMenu.menuItems = currentMenu.menuItems.reverse();
				}

				if (key.isRight(event))
				{
					if (selectedItem)
					{
						selectedItem.showSubMenu();

						if (selectedItem.hasSubMenu())
						{
							var submenu = selectedItem.getSubMenu();
							unselectItems(submenu.menuItems);
							selectItem(submenu.menuItems[0]);
							currentItem = submenu.menuItems[0];
						}
					}

					return;
				}

				if (key.isLeft(event))
				{
					closeAllSubMenu(menu.menuItems);
					currentItem = getSelected(menu.menuItems);
					return;
				}

				if (key.isEnter(event))
				{
					if (selectedItem)
					{
						BX.fireEvent(selectedItem.layout.item, "click");
						return;
					}

					if (selectedItem.hasSubMenu())
					{
						selectedItem.showSubMenu();
						return;
					}
				}

				unselectItems(currentMenu.menuItems);

				var nextItem = getNextItem(currentMenu.menuItems);

				if (nextItem)
				{
					selectItem(nextItem);
					return;
				}
			}

			if (key.isEscape(event))
			{
				currentMenu.close();
			}

			closeAllSubMenu(menu.menuItems);
		});
	};


	BX.Landing.Utils.delay = function(delay, data)
	{
		return new Promise(function(resolve) {
			setTimeout(resolve.bind(null, data), delay);
		});
	};


	/**
	 * Highlights node
	 * @param {HTMLElement} node
	 * @param {?boolean} [useRangeRect = false]
	 * @param {?boolean} [highlightBottom = false]
	 * @return {Promise}
	 */
	BX.Landing.Utils.highlight = function(node, useRangeRect, highlightBottom)
	{
		var rect;

		if (useRangeRect)
		{
			var range = document.createRange();
			range.selectNodeContents(node);
			rect = range.getBoundingClientRect();
		}
		else
		{
			rect = node.getBoundingClientRect();
		}

		if (highlightBottom)
		{
			rect = {
				top: rect.bottom,
				left: rect.left,
				right: rect.right,
				bottom: rect.bottom+1,
				height: 20,
				width: rect.width
			};
		}


		return BX.Landing.History.Highlight.getInstance().show(node, rect);
	};



	/**
	 * Scrolls to node
	 * @param {HTMLElement} node
	 * @return {Promise}
	 */
	BX.Landing.Utils.scrollTo = function(node)
	{
		return BX.Landing.PageObject.getInstance().view().then(function(iframe) {
			return BX.Landing.UI.Panel.Content.scrollTo(iframe, node)
				.then(function() {
					return new Promise(function(resolve) {
						setTimeout(resolve, 50);
					})
				})
		});
	};


	/**
	 * @todo refactoring
	 * @param element
	 * @param offsetParent
	 * @return {number}
	 */
	BX.Landing.Utils.offsetTop = function(element, offsetParent)
	{
		var elementRect = element.getBoundingClientRect();
		var parentRect = offsetParent.getBoundingClientRect();
		var scrollTop = offsetParent.scrollTop;
		var borderTopWidth = parseInt(BX.style(offsetParent, "border-top-width"));
		borderTopWidth = borderTopWidth === borderTopWidth ? borderTopWidth : 0;

		return (elementRect.top + scrollTop) - parentRect.top - borderTopWidth;
	};


	/**
	 * @todo refactoring
	 * @param element
	 * @param offsetParent
	 * @return {number}
	 */
	BX.Landing.Utils.offsetLeft = function(element, offsetParent)
	{
		var elementRect = element.getBoundingClientRect();
		var parentRect = offsetParent.getBoundingClientRect();
		var scrollLeft = offsetParent.scrollLeft;

		return (elementRect.left + scrollLeft) - parentRect.left;
	};


	/**
	 * Checks that value is array-like object
	 * @param value
	 * @return {boolean}
	 */
	BX.Landing.Utils.isArrayLike = function(value)
	{
		var isBoolean = BX.Landing.Utils.isBoolean;
		var isNumber = BX.Landing.Utils.isNumber;
		var isFunction = BX.Landing.Utils.isFunction;

		return (
			value !== null &&
			!isFunction(value) &&
			!isBoolean(value) &&
			!isNumber(value) &&
			value.length > 0 &&
			value.length <= Number.MAX_SAFE_INTEGER
		);
	};


	/**
	 * Check that value is arguments
	 * @param value
	 * @return {boolean}
	 */
	BX.Landing.Utils.isArguments = function(value)
	{
		var isArrayLike = BX.Landing.Utils.isArrayLike;
		return isArrayLike(value) && value.toString() === "[object Arguments]";
	};


	/**
	 * Checks that value is empty
	 * @param value
	 * @return {boolean}
	 */
	BX.Landing.Utils.isEmpty = function(value)
	{
		var isArrayLike = BX.Landing.Utils.isArrayLike;

		if (value == null)
		{
			return true;
		}

		if (isArrayLike(value))
		{
			return !value.length;
		}

		for (var key in value)
		{
			if (value.hasOwnProperty(key))
			{
				return false;
			}
		}

		return true;
	};


	/**
	 * Gets random integer
	 * @param {int} min
	 * @param {int} max
	 * @return {int}
	 */
	BX.Landing.Utils.randomInt = function(min, max)
	{
		max += 1;
		return Math.floor(Math.random() * (max - min)) + min;
	};


	/**
	 * Produces an array that contains every
	 * item shared between all the passed-in arrays.
	 * @param {...}
	 * @return {*[]}
	 */
	BX.Landing.Utils.intersection = function()
	{
		var slice = BX.Landing.Utils.slice;

		return slice(arguments).reduce(function(previous, current){
			return previous.filter(function(element){
				return current.includes(element);
			});
		});
	};


	/**
	 * Takes the difference between one array
	 * and a number of other arrays.
	 * @param {...}
	 * @return {*[]}
	 */
	BX.Landing.Utils.difference = function()
	{
		var slice = BX.Landing.Utils.slice;

		return slice(arguments).reduce(function(previous, current){
			return previous.filter(function(element){
				return !current.includes(element);
			});
		});
	};


	BX.Landing.Utils.changeTagName = function(element, tagName)
	{
		if (!element || !tagName)
		{
			return null;
		}

		var slice = BX.Landing.Utils.slice;
		var create = BX.Landing.Utils.create;
		var attributes = slice(element.attributes);
		var elementStyle = getComputedStyle(element);
		var fontSize = elementStyle.getPropertyValue("font-size");
		var fontWeight = elementStyle.getPropertyValue("font-weight");
		var newElement = create(tagName);
		var innerHTML = element.innerHTML;

		attributes.forEach(function(attribute) {
			newElement.setAttribute(attribute.nodeName, attribute.nodeValue);
		});

		newElement.style.fontSize = fontSize;
		newElement.style.fontWeight = fontWeight;
		newElement.innerHTML = innerHTML;

		element.parentElement.replaceChild(newElement, element);

		return newElement;
	};


	/**
	 * Creates hash from array like objects
	 * @param value
	 * @return {string}
	 */
	BX.Landing.Utils.hash = function(value)
	{
		if (BX.Landing.Utils.isArray(value) || BX.Landing.Utils.isPlainObject(value))
		{
			value = JSON.stringify(BX.Landing.Utils.sortObject(value));
		}

		return "" + BX.util.hashCode(value);
	};


	/**
	 * Sort object by keys
	 * @param unordered
	 * @return {{}}
	 */
	BX.Landing.Utils.sortObject = function(unordered)
	{
		return Object.keys(unordered).sort().reduce(function(ordered, key) {
			return ordered[key] = unordered[key], ordered;
		}, {});
	};


	/**
	 * Capitalizes string
	 * @param {string} str
	 * @return {string}
	 */
	BX.Landing.Utils.capitalize = function(str)
	{
		return str.charAt(0).toUpperCase() + str.slice(1);
	};

	BX.Landing.Utils.textToPlaceholders = function(str)
	{
		var matcher = new RegExp("<span[^>]*data-placeholder=\"(\\w+)\"[^>]*>(.+?)<\\/span>", 'gm');
		var segments = matcher.exec(str);

		if (segments)
		{
			return str.replace(matcher, "{{"+segments[1]+"}}");
		}

		return str;
	};

	/**
	 * Changes file path extension
	 * @param {string} path
	 * @param {string} newExtension
	 * @return {*}
	 */
	BX.Landing.Utils.changeExtension = function(path, newExtension)
	{
		return !!path ? path.replace(/\.[^\.]+$/, "." + newExtension) : path;
	};

	/**
	 * @param path
	 * @return {*}
	 */
	BX.Landing.Utils.rename2x = function(path)
	{
		path = path.replace(/@2x/, "");
		return !!path ? path.replace(/\.[^\.]+$/, "@2x." + BX.util.getExtension(path)) : path;
	};

	/**
	 * Gets delta from event
	 * @param event
	 * @return {{x, y: number}}
	 */
	BX.Landing.Utils.getDeltaFromEvent = function(event)
	{
		var deltaX = event.deltaX;
		var deltaY = -1 * event.deltaY;

		if (typeof deltaX === "undefined" || typeof deltaY === "undefined")
		{
			deltaX = -1 * event.wheelDeltaX / 6;
			deltaY = event.wheelDeltaY / 6;
		}

		if (event.deltaMode && event.deltaMode === 1)
		{
			deltaX *= 10;
			deltaY *= 10;
		}

		if (event.deltaMode && event.deltaMode === 1)
		{
			deltaX *= 10;
			deltaY *= 10;
		}

		/** NaN checks */
		if (deltaX !== deltaX && deltaY !== deltaY)
		{
			deltaX = 0;
			deltaY = event.wheelDelta;
		}

		return {x: deltaX, y: deltaY};
	};

	/**
	 * Loads file as blob
	 * @param url
	 * @return {Promise<Blob, string>}
	 */
	BX.Landing.Utils.urlToBlob = function(url)
	{
		if (!BX.type.isString(url))
		{
			return Promise.resolve(url);
		}

		return new Promise(function(resolve, reject) {
			try {
				var xhr = BX.ajax.xhr();
				xhr.open("GET", url);
				xhr.responseType = "blob";
				xhr.onerror = function()
				{
					reject("Network error.")
				};
				xhr.onload = function()
				{
					if (xhr.status === 200)
					{
						resolve(xhr.response);
					}
					else
					{
						reject("Loading error:" + xhr.statusText);
					}
				};
				xhr.send();
			}
			catch(err)
			{
				reject(err.message);
			}
		});
	};

	/**
	 * Makes user friendly file size
	 * @param {Number} size
	 * @return {*}
	 */
	BX.Landing.Utils.fileSize = function(size)
	{
		var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
		var mysize;

		sizes.forEach(function(unit, id) {
			var s = Math.pow(1024, id);
			var fixed;

			if (size >= s)
			{
				fixed = String((size / s).toFixed(1));

				if (fixed.indexOf('.0') === fixed.length - 2)
				{
					fixed = fixed.slice(0, -2);
				}

				mysize = fixed + ' ' + unit;
			}
		});

		if (!mysize)
		{
			mysize = '0 ' + sizes[0];
		}

		return mysize;
	};

	BX.Landing.Utils.getFileName = function(path)
	{
		return path.split('\\').pop().split('/').pop();
	};

	BX.Landing.Utils.getSelectedElement = function() {
		var range, sel, container;
		if (document.selection)
		{
			range = document.selection.createRange();
			return range.parentElement();
		}
		else
		{
			sel = window.getSelection();
			if (sel.getRangeAt)
			{
				if (sel.rangeCount > 0)
				{
					range = sel.getRangeAt(0);
				}
			}
			else
			{
				// Old WebKit
				range = document.createRange();
				range.setStart(sel.anchorNode, sel.anchorOffset);
				range.setEnd(sel.focusNode, sel.focusOffset);

				if (range.collapsed !== sel.isCollapsed) {
					range.setStart(sel.focusNode, sel.focusOffset);
					range.setEnd(sel.anchorNode, sel.anchorOffset);
				}
			}

			if (range)
			{
				container = range["endContainer"];

				return container.nodeType === 3 ? container.parentNode : container;
			}
		}
	};

	/**
	 * Fires custom event
	 * @param {Object} [target = window]
	 * @param {string} eventName
	 * @param {*[]} [params]
	 */
	BX.Landing.Utils.fireCustomEvent = function(target, eventName, params)
	{
		try
		{
			BX.onCustomEvent(target, eventName, params);
		}
		catch (err)
		{
			console.error(eventName, err);
		}
	};

	BX.Landing.Utils.onCustomEvent = BX.addCustomEvent;
	BX.Landing.Utils.removeCustomEvent = BX.removeCustomEvent;


	BX.Landing.Utils.insertAfter = BX.insertAfter;
	BX.Landing.Utils.isPlainObject = BX.type.isPlainObject;
	BX.Landing.Utils.append = BX.append;
	BX.Landing.Utils.prepend = BX.prepend;
	BX.Landing.Utils.isBoolean = BX.type.isBoolean;
	BX.Landing.Utils.isNumber = BX.type.isNumber;
	BX.Landing.Utils.isString = BX.type.isString;
	BX.Landing.Utils.isArray = BX.type.isArray;
	BX.Landing.Utils.isFunction = BX.type.isFunction;
	BX.Landing.Utils.addClass = BX.addClass;
	BX.Landing.Utils.removeClass = BX.removeClass;
	BX.Landing.Utils.toggleClass = BX.toggleClass;
	BX.Landing.Utils.hasClass = BX.hasClass;
	BX.Landing.Utils.debounce = BX.debounce;
	BX.Landing.Utils.throttle = BX.throttle;
	BX.Landing.Utils.bind = BX.bind;
	BX.Landing.Utils.unbind = BX.unbind;
	BX.Landing.Utils.getClass = BX.getClass;
	BX.Landing.Utils.pos = BX.pos;
	BX.Landing.Utils.assign = Object.assign || BX.util.objectMerge;
	BX.Landing.Utils.clone = BX.clone;
	BX.Landing.Utils.create = BX.create;
	BX.Landing.Utils.remove = BX.remove;
	BX.Landing.Utils.trim = BX.util.trim;
	BX.Landing.Utils.random = BX.util.getRandomString;
	BX.Landing.Utils.findParent = BX.findParent;
	BX.Landing.Utils.proxy = BX.proxy;
	BX.Landing.Utils.arrayUnique = BX.util.array_unique;
	BX.Landing.Utils.keys = Object.keys;
	BX.Landing.Utils.fireEvent = BX.fireEvent;
	BX.Landing.Utils.addQueryParams = BX.util.add_url_param.bind(BX.util);
})();
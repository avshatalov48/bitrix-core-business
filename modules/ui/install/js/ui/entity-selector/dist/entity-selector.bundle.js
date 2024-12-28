/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_popup,main_core_collections,main_core_events,main_core,main_loader) {
	'use strict';

	let ItemNodeComparator = /*#__PURE__*/function () {
	  function ItemNodeComparator() {
	    babelHelpers.classCallCheck(this, ItemNodeComparator);
	  }
	  babelHelpers.createClass(ItemNodeComparator, null, [{
	    key: "makeMultipleComparator",
	    value: function makeMultipleComparator(order) {
	      const props = Object.keys(order).map(property => `get${main_core.Text.capitalize(property)}`);

	      /*
	      asc *
	      asc nulls last *
	      asc nulls first
	      	desc *
	      desc nulls first *
	      desc nulls last
	      */
	      const directions = [];
	      Object.values(order).forEach(element => {
	        const direction = element.toLowerCase().trim();

	        // Default sorting: 'asc' || 'asc nulls last'
	        let ascOrdering = true;
	        let nullsOrdering = true;
	        if (direction === 'desc' || direction === 'desc nulls first') {
	          ascOrdering = false;
	        } else if (direction === 'asc nulls first') {
	          nullsOrdering = false;
	        } else if (direction === 'desc nulls last') {
	          ascOrdering = false;
	          nullsOrdering = false;
	        }
	        directions.push({
	          ascOrdering,
	          nullsOrdering
	        });
	      });
	      const numberOfProperties = props.length;
	      return (nodeA, nodeB) => {
	        let i = 0;
	        let result = 0;
	        while (result === 0 && i < numberOfProperties) {
	          const propertyGetter = props[i];
	          const direction = directions[i];
	          result = this.compareItemNodes(nodeA, nodeB, propertyGetter, direction.ascOrdering, direction.nullsOrdering);
	          i += 1;
	        }
	        return result;
	      };
	    }
	  }, {
	    key: "compareItemNodes",
	    value: function compareItemNodes(nodeA, nodeB, propertyGetter, ascOrdering, nullsOrdering) {
	      const itemA = nodeA.getItem();
	      const itemB = nodeB.getItem();
	      itemA.getCustomData().get();
	      const valueA = itemA[propertyGetter]();
	      const valueB = itemB[propertyGetter]();
	      let result = 0;
	      if (valueA !== null && valueB === null) {
	        result = nullsOrdering ? -1 : 1;
	      } else if (valueA === null && valueB !== null) {
	        result = nullsOrdering ? 1 : -1;
	      } else if (valueA === null && valueB === null) {
	        result = ascOrdering ? -1 : 1;
	      } else {
	        if (main_core.Type.isString(valueA)) {
	          result = valueA.localeCompare(valueB);
	        } else {
	          result = valueA - valueB;
	        }
	      }
	      const sortOrder = ascOrdering ? 1 : -1;
	      return result * sortOrder;
	    }
	  }]);
	  return ItemNodeComparator;
	}();

	let TextNodeType = /*#__PURE__*/function () {
	  function TextNodeType() {
	    babelHelpers.classCallCheck(this, TextNodeType);
	  }
	  babelHelpers.createClass(TextNodeType, null, [{
	    key: "isValid",
	    value: function isValid(type) {
	      return main_core.Type.isString(type) && (type === this.HTML || type === this.TEXT);
	    }
	  }]);
	  return TextNodeType;
	}();
	babelHelpers.defineProperty(TextNodeType, "TEXT", 'text');
	babelHelpers.defineProperty(TextNodeType, "HTML", 'html');

	let TextNode = /*#__PURE__*/function () {
	  function TextNode(options) {
	    babelHelpers.classCallCheck(this, TextNode);
	    babelHelpers.defineProperty(this, "text", null);
	    babelHelpers.defineProperty(this, "type", null);
	    if (main_core.Type.isPlainObject(options)) {
	      if (main_core.Type.isString(options.text)) {
	        this.text = options.text;
	      }
	      if (TextNodeType.isValid(options.type)) {
	        this.type = options.type;
	      }
	    } else if (main_core.Type.isString(options)) {
	      this.text = options;
	    }
	  }
	  babelHelpers.createClass(TextNode, [{
	    key: "getText",
	    value: function getText() {
	      return this.text;
	    }
	  }, {
	    key: "getType",
	    value: function getType() {
	      return this.type;
	    }
	  }, {
	    key: "isNullable",
	    value: function isNullable() {
	      return this.getText() === null;
	    }
	  }, {
	    key: "renderTo",
	    value: function renderTo(element) {
	      const text = this.getText();
	      if (text === null) {
	        return;
	      }
	      if (this.getType() === null || this.getType() === TextNodeType.TEXT) {
	        element.textContent = text;
	      } else if (this.getType() === TextNodeType.HTML) {
	        element.innerHTML = text;
	      }
	    }
	  }, {
	    key: "toString",
	    value: function toString() {
	      var _this$getText;
	      return (_this$getText = this.getText()) !== null && _this$getText !== void 0 ? _this$getText : '';
	    }
	  }, {
	    key: "toJSON",
	    value: function toJSON() {
	      if (this.getType() === null) {
	        return this.getText();
	      } else {
	        return {
	          text: this.getText(),
	          type: this.getType()
	        };
	      }
	    }
	  }]);
	  return TextNode;
	}();

	let Highlighter = /*#__PURE__*/function () {
	  function Highlighter() {
	    babelHelpers.classCallCheck(this, Highlighter);
	  }
	  babelHelpers.createClass(Highlighter, null, [{
	    key: "mark",
	    value: function mark(text, matches) {
	      let encode = true;
	      if (text instanceof TextNode) {
	        if (text.getType() === 'html') {
	          encode = false;
	        }
	        text = text.getText();
	      }
	      if (!main_core.Type.isStringFilled(text) || !matches || matches.count() === 0) {
	        return text;
	      }
	      let result = '';
	      let offset = 0;
	      let chunk = '';
	      matches.forEach(match => {
	        if (offset > match.getStartIndex()) {
	          return;
	        }
	        chunk = text.substring(offset, match.getStartIndex());
	        result += encode ? main_core.Text.encode(chunk) : chunk;
	        result += '<span class="ui-selector-highlight-mark">';
	        chunk = text.substring(match.getStartIndex(), match.getEndIndex());
	        result += encode ? main_core.Text.encode(chunk) : chunk;
	        result += '</span>';
	        offset = match.getEndIndex();
	      });
	      chunk = text.substring(offset);
	      result += encode ? main_core.Text.encode(chunk) : chunk;
	      return result;
	    }
	  }]);
	  return Highlighter;
	}();

	let ItemBadge = /*#__PURE__*/function () {
	  function ItemBadge(badgeOptions) {
	    babelHelpers.classCallCheck(this, ItemBadge);
	    babelHelpers.defineProperty(this, "title", null);
	    babelHelpers.defineProperty(this, "textColor", null);
	    babelHelpers.defineProperty(this, "bgColor", null);
	    babelHelpers.defineProperty(this, "containers", new WeakMap());
	    const options = main_core.Type.isPlainObject(badgeOptions) ? badgeOptions : {};
	    this.setTitle(options.title);
	    this.setTextColor(options.textColor);
	    this.setBgColor(options.bgColor);
	  }
	  babelHelpers.createClass(ItemBadge, [{
	    key: "getTitle",
	    value: function getTitle() {
	      const titleNode = this.getTitleNode();
	      return titleNode !== null && !titleNode.isNullable() ? titleNode.getText() : '';
	    }
	  }, {
	    key: "getTitleNode",
	    value: function getTitleNode() {
	      return this.title;
	    }
	  }, {
	    key: "setTitle",
	    value: function setTitle(title) {
	      if (main_core.Type.isStringFilled(title) || main_core.Type.isPlainObject(title) || title === null) {
	        this.title = title === null ? null : new TextNode(title);
	      }
	    }
	  }, {
	    key: "getTextColor",
	    value: function getTextColor() {
	      return this.textColor;
	    }
	  }, {
	    key: "setTextColor",
	    value: function setTextColor(textColor) {
	      if (main_core.Type.isString(textColor) || textColor === null) {
	        this.textColor = textColor;
	      }
	    }
	  }, {
	    key: "getBgColor",
	    value: function getBgColor() {
	      return this.bgColor;
	    }
	  }, {
	    key: "setBgColor",
	    value: function setBgColor(bgColor) {
	      if (main_core.Type.isString(bgColor) || bgColor === null) {
	        this.bgColor = bgColor;
	      }
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer(target) {
	      let container = this.containers.get(target);
	      if (!container) {
	        container = document.createElement('span');
	        container.className = 'ui-selector-item-badge';
	        this.containers.set(target, container);
	      }
	      return container;
	    }
	  }, {
	    key: "renderTo",
	    value: function renderTo(target) {
	      const container = this.getContainer(target);
	      const titleNode = this.getTitleNode();
	      if (titleNode) {
	        this.getTitleNode().renderTo(container);
	      } else {
	        container.textContent = '';
	      }
	      main_core.Dom.style(container, 'color', this.getTextColor());
	      main_core.Dom.style(container, 'background-color', this.getBgColor());
	      main_core.Dom.append(container, target);
	    }
	  }, {
	    key: "toJSON",
	    value: function toJSON() {
	      return {
	        title: this.getTitleNode(),
	        textColor: this.getTextColor(),
	        bgColor: this.getBgColor()
	      };
	    }
	  }]);
	  return ItemBadge;
	}();

	let SearchField = /*#__PURE__*/function () {
	  function SearchField(fieldOptions) {
	    babelHelpers.classCallCheck(this, SearchField);
	    babelHelpers.defineProperty(this, "name", null);
	    babelHelpers.defineProperty(this, "type", 'string');
	    babelHelpers.defineProperty(this, "searchable", true);
	    babelHelpers.defineProperty(this, "system", false);
	    babelHelpers.defineProperty(this, "sort", null);
	    const options = main_core.Type.isPlainObject(fieldOptions) ? fieldOptions : {};
	    if (!main_core.Type.isStringFilled(options.name)) {
	      throw new Error('EntitySelector.SearchField: "name" parameter is required.');
	    }
	    this.name = options.name;
	    this.setType(options.type);
	    this.setSystem(options.system);
	    this.setSort(options.sort);
	    this.setSearchable(options.searchable);
	  }
	  babelHelpers.createClass(SearchField, [{
	    key: "getName",
	    value: function getName() {
	      return this.name;
	    }
	  }, {
	    key: "getType",
	    value: function getType() {
	      return this.type;
	    }
	  }, {
	    key: "setType",
	    value: function setType(type) {
	      if (main_core.Type.isStringFilled(type)) {
	        this.type = type;
	      }
	    }
	  }, {
	    key: "getSort",
	    value: function getSort() {
	      return this.sort;
	    }
	  }, {
	    key: "setSort",
	    value: function setSort(sort) {
	      if (main_core.Type.isNumber(sort) || sort === null) {
	        this.sort = sort;
	      }
	    }
	  }, {
	    key: "setSearchable",
	    value: function setSearchable(flag) {
	      if (main_core.Type.isBoolean(flag)) {
	        this.searchable = flag;
	      }
	    }
	  }, {
	    key: "isSearchable",
	    value: function isSearchable() {
	      return this.searchable;
	    }
	  }, {
	    key: "setSystem",
	    value: function setSystem(flag) {
	      if (main_core.Type.isBoolean(flag)) {
	        this.system = flag;
	      }
	    }
	  }, {
	    key: "isCustom",
	    value: function isCustom() {
	      return !this.isSystem();
	    }
	  }, {
	    key: "isSystem",
	    value: function isSystem() {
	      return this.system;
	    }
	  }]);
	  return SearchField;
	}();

	let MatchIndex = /*#__PURE__*/function () {
	  function MatchIndex(field, queryWord, startIndex) {
	    babelHelpers.classCallCheck(this, MatchIndex);
	    babelHelpers.defineProperty(this, "field", null);
	    babelHelpers.defineProperty(this, "queryWord", null);
	    babelHelpers.defineProperty(this, "startIndex", null);
	    babelHelpers.defineProperty(this, "endIndex", null);
	    this.field = field;
	    this.queryWord = queryWord;
	    this.startIndex = startIndex;
	    this.endIndex = startIndex + queryWord.length;
	  }
	  babelHelpers.createClass(MatchIndex, [{
	    key: "getField",
	    value: function getField() {
	      return this.field;
	    }
	  }, {
	    key: "getQueryWord",
	    value: function getQueryWord() {
	      return this.queryWord;
	    }
	  }, {
	    key: "getStartIndex",
	    value: function getStartIndex() {
	      return this.startIndex;
	    }
	  }, {
	    key: "getEndIndex",
	    value: function getEndIndex() {
	      return this.endIndex;
	    }
	  }]);
	  return MatchIndex;
	}();

	const comparator = (a, b) => {
	  if (a.getStartIndex() === b.getStartIndex()) {
	    return a.getEndIndex() > b.getEndIndex() ? -1 : 1;
	  } else {
	    return a.getStartIndex() > b.getStartIndex() ? 1 : -1;
	  }
	};
	let MatchField = /*#__PURE__*/function () {
	  function MatchField(field, indexes = []) {
	    babelHelpers.classCallCheck(this, MatchField);
	    babelHelpers.defineProperty(this, "field", null);
	    babelHelpers.defineProperty(this, "matchIndexes", new main_core_collections.OrderedArray(comparator));
	    this.field = field;
	    this.addIndexes(indexes);
	  }
	  babelHelpers.createClass(MatchField, [{
	    key: "getField",
	    value: function getField() {
	      return this.field;
	    }
	  }, {
	    key: "getMatches",
	    value: function getMatches() {
	      return this.matchIndexes;
	    }
	  }, {
	    key: "addIndex",
	    value: function addIndex(matchIndex) {
	      this.matchIndexes.add(matchIndex);
	    }
	  }, {
	    key: "addIndexes",
	    value: function addIndexes(matchIndexes) {
	      if (main_core.Type.isArray(matchIndexes)) {
	        matchIndexes.forEach(matchIndex => {
	          this.addIndex(matchIndex);
	        });
	      }
	    }
	  }]);
	  return MatchField;
	}();

	let Animation = /*#__PURE__*/function () {
	  function Animation() {
	    babelHelpers.classCallCheck(this, Animation);
	  }
	  babelHelpers.createClass(Animation, null, [{
	    key: "handleTransitionEnd",
	    value: function handleTransitionEnd(element, propertyName) {
	      const properties = main_core.Type.isArray(propertyName) ? new Set(propertyName) : new Set([propertyName]);
	      return new Promise(function (resolve) {
	        const handler = event => {
	          if (event.target !== element || !properties.has(event.propertyName)) {
	            return;
	          }
	          properties.delete(event.propertyName);
	          if (properties.size === 0) {
	            resolve(event);
	            main_core.Event.unbind(element, 'transitionend', handler);
	          }
	        };
	        main_core.Event.bind(element, 'transitionend', handler);
	      });
	    }
	  }, {
	    key: "handleAnimationEnd",
	    value: function handleAnimationEnd(element, animationName) {
	      return new Promise(resolve => {
	        const handler = event => {
	          if (!animationName || event.animationName === animationName) {
	            resolve(event);
	            main_core.Event.unbind(element, 'animationend', handler);
	          }
	        };
	        main_core.Event.bind(element, 'animationend', handler);
	      });
	    }
	  }]);
	  return Animation;
	}();

	const regexp = /^data:((?:\w+\/(?:(?!;).)+)?)((?:;[\w\W]*?[^;])*),(.+)$/;
	const isDataUri = str => {
	  return typeof str === 'string' ? str.match(regexp) : false;
	};
	function encodeUrl(url) {
	  if (isDataUri(url)) {
	    return url;
	  }
	  return encodeURI(url);
	}

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classStaticPrivateMethodGet(receiver, classConstructor, method) { _classCheckPrivateStaticAccess(receiver, classConstructor); return method; }
	function _classCheckPrivateStaticAccess(receiver, classConstructor) { if (receiver !== classConstructor) { throw new TypeError("Private static access of wrong provenance"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	let RenderMode = function RenderMode() {
	  babelHelpers.classCallCheck(this, RenderMode);
	};
	babelHelpers.defineProperty(RenderMode, "PARTIAL", 'partial');
	babelHelpers.defineProperty(RenderMode, "OVERRIDE", 'override');
	var _setHidden = /*#__PURE__*/new WeakSet();
	var _makeEllipsisTitle = /*#__PURE__*/new WeakSet();
	let ItemNode = /*#__PURE__*/function () {
	  // for the fast access

	  function ItemNode(item, nodeOptions) {
	    babelHelpers.classCallCheck(this, ItemNode);
	    _classPrivateMethodInitSpec(this, _makeEllipsisTitle);
	    _classPrivateMethodInitSpec(this, _setHidden);
	    babelHelpers.defineProperty(this, "item", null);
	    babelHelpers.defineProperty(this, "tab", null);
	    babelHelpers.defineProperty(this, "cache", new main_core.Cache.MemoryCache());
	    babelHelpers.defineProperty(this, "parentNode", null);
	    babelHelpers.defineProperty(this, "children", null);
	    babelHelpers.defineProperty(this, "childItems", new WeakMap());
	    babelHelpers.defineProperty(this, "loaded", false);
	    babelHelpers.defineProperty(this, "dynamic", false);
	    babelHelpers.defineProperty(this, "dynamicPromise", null);
	    babelHelpers.defineProperty(this, "loader", null);
	    babelHelpers.defineProperty(this, "open", false);
	    babelHelpers.defineProperty(this, "autoOpen", false);
	    babelHelpers.defineProperty(this, "focused", false);
	    babelHelpers.defineProperty(this, "renderMode", RenderMode.PARTIAL);
	    babelHelpers.defineProperty(this, "title", null);
	    babelHelpers.defineProperty(this, "subtitle", null);
	    babelHelpers.defineProperty(this, "supertitle", null);
	    babelHelpers.defineProperty(this, "caption", null);
	    babelHelpers.defineProperty(this, "captionOptions", {});
	    babelHelpers.defineProperty(this, "avatar", null);
	    babelHelpers.defineProperty(this, "avatarOptions", null);
	    babelHelpers.defineProperty(this, "link", null);
	    babelHelpers.defineProperty(this, "linkTitle", null);
	    babelHelpers.defineProperty(this, "textColor", null);
	    babelHelpers.defineProperty(this, "badges", null);
	    babelHelpers.defineProperty(this, "badgesOptions", {});
	    babelHelpers.defineProperty(this, "hidden", false);
	    babelHelpers.defineProperty(this, "highlights", []);
	    babelHelpers.defineProperty(this, "renderWithDebounce", main_core.Runtime.debounce(this.render, 50, this));
	    const options = main_core.Type.isPlainObject(nodeOptions) ? nodeOptions : {};
	    if (main_core.Type.isObject(item)) {
	      this.item = item;
	    }
	    let comparator = null;
	    if (main_core.Type.isFunction(options.itemOrder)) {
	      comparator = options.itemOrder;
	    } else if (main_core.Type.isPlainObject(options.itemOrder)) {
	      comparator = ItemNodeComparator.makeMultipleComparator(options.itemOrder);
	    }
	    this.children = new main_core_collections.OrderedArray(comparator);
	    this.renderMode = options.renderMode === RenderMode.OVERRIDE ? RenderMode.OVERRIDE : RenderMode.PARTIAL;
	    if (this.renderMode === RenderMode.OVERRIDE) {
	      this.setTitle('');
	      this.setSubtitle('');
	      this.setSupertitle('');
	      this.setCaption('');
	      this.setLinkTitle('');
	      this.avatar = '';
	      this.avatarOptions = {
	        bgSize: null,
	        bgColor: null,
	        bgImage: null,
	        border: null,
	        borderRadius: null,
	        outline: null,
	        outlineOffset: null
	      };
	      this.textColor = '';
	      this.link = '';
	      this.badges = [];
	      this.captionOptions = {
	        fitContent: null,
	        maxWidth: null,
	        justifyContent: null
	      };
	      this.badgesOptions = {
	        fitContent: null,
	        maxWidth: null,
	        justifyContent: null
	      };
	    }
	    this.setTitle(options.title);
	    this.setSubtitle(options.subtitle);
	    this.setSupertitle(options.supertitle);
	    this.setCaption(options.caption);
	    this.setCaptionOptions(options.captionOptions);
	    this.setAvatar(options.avatar);
	    this.setAvatarOptions(options.avatarOptions);
	    this.setTextColor(options.textColor);
	    this.setLink(options.link);
	    this.setLinkTitle(options.linkTitle);
	    this.setBadges(options.badges);
	    this.setBadgesOptions(options.badgesOptions);
	    this.setDynamic(options.dynamic);
	    this.setOpen(options.open);
	  }
	  babelHelpers.createClass(ItemNode, [{
	    key: "getItem",
	    value: function getItem() {
	      return this.item;
	    }
	  }, {
	    key: "isRoot",
	    value: function isRoot() {
	      return this.getParentNode() === null;
	    }
	  }, {
	    key: "getDialog",
	    value: function getDialog() {
	      return this.getTab().getDialog();
	    }
	  }, {
	    key: "setTab",
	    value: function setTab(tab) {
	      this.tab = tab;
	    }
	  }, {
	    key: "getTab",
	    value: function getTab() {
	      return this.tab;
	    }
	  }, {
	    key: "getParentNode",
	    value: function getParentNode() {
	      return this.parentNode;
	    }
	  }, {
	    key: "setParentNode",
	    value: function setParentNode(parentNode) {
	      this.parentNode = parentNode;
	    }
	  }, {
	    key: "getNextSibling",
	    value: function getNextSibling() {
	      if (!this.getParentNode()) {
	        return null;
	      }
	      const siblings = this.getParentNode().getChildren();
	      const index = siblings.getIndex(this);
	      return siblings.getByIndex(index + 1);
	    }
	  }, {
	    key: "getPreviousSibling",
	    value: function getPreviousSibling() {
	      if (!this.getParentNode()) {
	        return null;
	      }
	      const siblings = this.getParentNode().getChildren();
	      const index = siblings.getIndex(this);
	      return siblings.getByIndex(index - 1);
	    }
	  }, {
	    key: "addChildren",
	    value: function addChildren(children) {
	      if (!main_core.Type.isArray(children)) {
	        return;
	      }
	      children.forEach(childOptions => {
	        delete childOptions.tabs;
	        const childItem = this.getDialog().addItem(childOptions);
	        const childNode = this.addItem(childItem, childOptions.nodeOptions);
	        childNode.addChildren(childOptions.children);
	      });
	    }
	  }, {
	    key: "addChild",
	    value: function addChild(child) {
	      if (!(child instanceof ItemNode)) {
	        throw new Error('EntitySelector.ItemNode: an item must be an instance of EntitySelector.ItemNode.');
	      }
	      if (this.isChildOf(child) || child === this) {
	        throw new Error('EntitySelector.ItemNode: a child item cannot be a parent of current item.');
	      }
	      if (this.getChildren().has(child) || this.childItems.has(child.getItem())) {
	        return null;
	      }
	      this.getChildren().add(child);
	      this.childItems.set(child.getItem(), child);
	      child.setTab(this.getTab());
	      child.setParentNode(this);
	      if (this.isRendered()) {
	        this.renderWithDebounce();
	      }
	      return child;
	    }
	  }, {
	    key: "getDepthLevel",
	    value: function getDepthLevel() {
	      return this.isRoot() ? 0 : this.getParentNode().getDepthLevel() + 1;
	    }
	  }, {
	    key: "addItem",
	    value: function addItem(item, nodeOptions) {
	      let itemNode = this.childItems.get(item);
	      if (!itemNode) {
	        itemNode = item.createNode(nodeOptions);
	        this.addChild(itemNode);
	      }
	      return itemNode;
	    }
	  }, {
	    key: "addItems",
	    value: function addItems(items) {
	      if (main_core.Type.isArray(items)) {
	        this.disableRender();
	        items.forEach(item => {
	          if (main_core.Type.isArray(item) && item.length === 2) {
	            this.addItem(item[0], item[1]);
	          } else if (item instanceof Item) {
	            this.addItem(item);
	          }
	        });
	        this.enableRender();
	        if (this.isRendered()) {
	          this.renderWithDebounce();
	        }
	      }
	    }
	  }, {
	    key: "hasItem",
	    value: function hasItem(item) {
	      return this.childItems.has(item);
	    }
	  }, {
	    key: "removeChild",
	    value: function removeChild(child) {
	      if (!this.getChildren().has(child)) {
	        return false;
	      }
	      child.removeChildren();
	      if (child.isFocused()) {
	        child.unfocus();
	      }
	      child.setParentNode(null);
	      child.getItem().removeNode(child);
	      this.getChildren().delete(child);
	      this.childItems.delete(child.getItem());
	      if (this.isRendered()) {
	        main_core.Dom.remove(child.getOuterContainer());
	      }
	      return true;
	    }
	  }, {
	    key: "removeChildren",
	    value: function removeChildren() {
	      if (!this.hasChildren()) {
	        return;
	      }
	      this.getChildren().forEach(node => {
	        node.removeChildren();
	        if (node.isFocused()) {
	          node.unfocus();
	        }
	        node.setParentNode(null);
	        node.getItem().removeNode(node);
	      });
	      this.getChildren().clear();
	      this.childItems = new WeakMap();
	      if (this.isRendered()) {
	        if (main_core.Browser.isIE()) {
	          main_core.Dom.clean(this.getChildrenContainer());
	        } else {
	          this.getChildrenContainer().textContent = '';
	        }
	      }
	    }
	  }, {
	    key: "hasChild",
	    value: function hasChild(child) {
	      return this.getChildren().has(child);
	    }
	  }, {
	    key: "isChildOf",
	    value: function isChildOf(parent) {
	      let parentNode = this.getParentNode();
	      while (parentNode !== null) {
	        if (parentNode === parent) {
	          return true;
	        }
	        parentNode = parentNode.getParentNode();
	      }
	      return false;
	    }
	  }, {
	    key: "getFirstChild",
	    value: function getFirstChild() {
	      return this.children.getFirst();
	    }
	  }, {
	    key: "getLastChild",
	    value: function getLastChild() {
	      return this.children.getLast();
	    }
	  }, {
	    key: "getChildren",
	    value: function getChildren() {
	      return this.children;
	    }
	  }, {
	    key: "hasChildren",
	    value: function hasChildren() {
	      return this.children.count() > 0;
	    }
	  }, {
	    key: "loadChildren",
	    value: function loadChildren() {
	      if (!this.isDynamic()) {
	        throw new Error('EntitySelector.ItemNode.loadChildren: an item node is not dynamic.');
	      }
	      if (this.dynamicPromise) {
	        return this.dynamicPromise;
	      }
	      this.dynamicPromise = main_core.ajax.runAction('ui.entityselector.getChildren', {
	        json: {
	          parentItem: this.getItem().getAjaxJson(),
	          dialog: this.getDialog().getAjaxJson()
	        },
	        getParameters: {
	          context: this.getDialog().getContext()
	        }
	      });
	      this.dynamicPromise.then(response => {
	        if (response && response.data && main_core.Type.isPlainObject(response.data.dialog)) {
	          this.addChildren(response.data.dialog.items);
	          this.render();
	        }
	        this.loaded = true;
	      });
	      this.dynamicPromise.catch(error => {
	        this.loaded = false;
	        this.dynamicPromise = null;
	        console.error(error);
	      });
	      return this.dynamicPromise;
	    }
	  }, {
	    key: "setOpen",
	    value: function setOpen(open) {
	      if (main_core.Type.isBoolean(open)) {
	        if (open && this.isDynamic() && !this.isLoaded()) {
	          this.setAutoOpen(true);
	        } else {
	          this.open = open;
	        }
	      }
	    }
	  }, {
	    key: "isOpen",
	    value: function isOpen() {
	      return this.open;
	    }
	  }, {
	    key: "isAutoOpen",
	    value: function isAutoOpen() {
	      return this.autoOpen && this.isDynamic() && !this.isLoaded();
	    }
	  }, {
	    key: "setAutoOpen",
	    value: function setAutoOpen(autoOpen) {
	      if (main_core.Type.isBoolean(autoOpen)) {
	        this.autoOpen = autoOpen;
	      }
	    }
	  }, {
	    key: "setDynamic",
	    value: function setDynamic(dynamic) {
	      if (main_core.Type.isBoolean(dynamic)) {
	        this.dynamic = dynamic;
	      }
	    }
	  }, {
	    key: "isDynamic",
	    value: function isDynamic() {
	      return this.dynamic;
	    }
	  }, {
	    key: "isLoaded",
	    value: function isLoaded() {
	      return this.loaded;
	    }
	  }, {
	    key: "getLoader",
	    value: function getLoader() {
	      if (this.loader === null) {
	        this.loader = new main_loader.Loader({
	          target: this.getIndicatorContainer(),
	          size: 30
	        });
	      }
	      return this.loader;
	    }
	  }, {
	    key: "showLoader",
	    value: function showLoader() {
	      void this.getLoader().show();
	      main_core.Dom.addClass(this.getIndicatorContainer(), 'ui-selector-item-indicator-hidden');
	    }
	  }, {
	    key: "hideLoader",
	    value: function hideLoader() {
	      void this.getLoader().hide();
	      main_core.Dom.removeClass(this.getIndicatorContainer(), 'ui-selector-item-indicator-hidden');
	    }
	  }, {
	    key: "destroyLoader",
	    value: function destroyLoader() {
	      this.getLoader().destroy();
	      this.loader = null;
	      main_core.Dom.removeClass(this.getIndicatorContainer(), 'ui-selector-item-indicator-hidden');
	    }
	  }, {
	    key: "expand",
	    value: function expand() {
	      if (this.isOpen() || !this.hasChildren() && !this.isDynamic()) {
	        return;
	      }
	      if (this.isDynamic() && !this.isLoaded()) {
	        this.loadChildren().then(() => {
	          this.destroyLoader();
	          this.expand();
	        });
	        this.showLoader();
	        return;
	      }
	      main_core.Dom.addClass(this.getOuterContainer(), 'ui-selector-item-box-open');
	      main_core.Dom.style(this.getChildrenContainer(), 'height', '0px');
	      main_core.Dom.style(this.getChildrenContainer(), 'opacity', 0);
	      requestAnimationFrame(() => {
	        requestAnimationFrame(() => {
	          main_core.Dom.style(this.getChildrenContainer(), 'height', `${this.getChildrenContainer().scrollHeight}px`);
	          main_core.Dom.style(this.getChildrenContainer(), 'opacity', 1);
	          Animation.handleTransitionEnd(this.getChildrenContainer(), 'height').then(() => {
	            main_core.Dom.style(this.getChildrenContainer(), 'height', null);
	            main_core.Dom.style(this.getChildrenContainer(), 'opacity', null);
	            main_core.Dom.addClass(this.getOuterContainer(), 'ui-selector-item-box-open');
	            this.setOpen(true);
	          });
	        });
	      });
	    }
	  }, {
	    key: "collapse",
	    value: function collapse() {
	      if (!this.isOpen()) {
	        return;
	      }
	      main_core.Dom.style(this.getChildrenContainer(), 'height', `${this.getChildrenContainer().offsetHeight}px`);
	      requestAnimationFrame(() => {
	        requestAnimationFrame(() => {
	          main_core.Dom.style(this.getChildrenContainer(), 'height', '0px');
	          main_core.Dom.style(this.getChildrenContainer(), 'opacity', 0);
	          Animation.handleTransitionEnd(this.getChildrenContainer(), 'height').then(() => {
	            main_core.Dom.style(this.getChildrenContainer(), 'height', null);
	            main_core.Dom.style(this.getChildrenContainer(), 'opacity', null);
	            main_core.Dom.removeClass(this.getOuterContainer(), 'ui-selector-item-box-open');
	            this.setOpen(false);
	          });
	        });
	      });
	    }
	  }, {
	    key: "render",
	    value: function render(appendChildren = false) {
	      if (this.isRoot()) {
	        this.renderRoot(appendChildren);
	        return;
	      }
	      const titleNode = this.getTitleNode();
	      if (titleNode) {
	        titleNode.renderTo(this.getTitleContainer());
	      } else {
	        this.getTitleContainer().textContent = '';
	      }
	      const supertitleNode = this.getSupertitleNode();
	      if (supertitleNode) {
	        supertitleNode.renderTo(this.getSupertitleContainer());
	      } else {
	        this.getSupertitleContainer().textContent = '';
	      }
	      const subtitleNode = this.getSubtitleNode();
	      if (subtitleNode) {
	        subtitleNode.renderTo(this.getSubtitleContainer());
	      } else {
	        this.getSubtitleContainer().textContent = '';
	      }
	      const captionNode = this.getCaptionNode();
	      if (captionNode) {
	        captionNode.renderTo(this.getCaptionContainer());
	      } else {
	        this.getCaptionContainer().textContent = '';
	      }
	      const captionFitContent = this.getCaptionOption('fitContent');
	      if (main_core.Type.isBoolean(captionFitContent)) {
	        main_core.Dom.style(this.getCaptionContainer(), 'flex-shrink', captionFitContent ? 0 : null);
	      }
	      const captionJustifyContent = this.getCaptionOption('justifyContent');
	      if (main_core.Type.isStringFilled(captionJustifyContent) || captionJustifyContent === null) {
	        main_core.Dom.style(this.getCaptionContainer(), {
	          flexGrow: captionJustifyContent ? '1' : null,
	          textAlign: captionJustifyContent || null
	        });
	      }
	      const captionMaxWidth = this.getCaptionOption('maxWidth');
	      if (main_core.Type.isString(captionMaxWidth) || main_core.Type.isNumber(captionMaxWidth)) {
	        main_core.Dom.style(this.getCaptionContainer(), 'max-width', main_core.Type.isNumber(captionMaxWidth) ? `${captionMaxWidth}px` : captionMaxWidth);
	      }
	      if (main_core.Type.isStringFilled(this.getTextColor())) {
	        this.getTitleContainer().style.color = this.getTextColor();
	      } else {
	        this.getTitleContainer().style.removeProperty('color');
	      }
	      const avatar = this.getAvatar();
	      if (main_core.Type.isStringFilled(avatar)) {
	        this.getAvatarContainer().style.backgroundImage = `url('${encodeUrl(avatar)}')`;
	      } else {
	        const bgImage = this.getAvatarOption('bgImage');
	        if (main_core.Type.isStringFilled(bgImage)) {
	          this.getAvatarContainer().style.backgroundImage = bgImage;
	        } else {
	          this.getAvatarContainer().style.removeProperty('background-image');
	        }
	      }
	      const bgColor = this.getAvatarOption('bgColor');
	      if (main_core.Type.isStringFilled(bgColor)) {
	        this.getAvatarContainer().style.backgroundColor = bgColor;
	      } else {
	        this.getAvatarContainer().style.removeProperty('background-color');
	      }
	      const bgSize = this.getAvatarOption('bgSize');
	      if (main_core.Type.isStringFilled(bgSize)) {
	        this.getAvatarContainer().style.backgroundSize = bgSize;
	      } else {
	        this.getAvatarContainer().style.removeProperty('background-size');
	      }
	      const border = this.getAvatarOption('border');
	      if (main_core.Type.isStringFilled(border)) {
	        this.getAvatarContainer().style.border = border;
	      } else {
	        this.getAvatarContainer().style.removeProperty('border');
	      }
	      const borderRadius = this.getAvatarOption('borderRadius');
	      if (main_core.Type.isStringFilled(borderRadius)) {
	        this.getAvatarContainer().style.borderRadius = borderRadius;
	      } else {
	        this.getAvatarContainer().style.removeProperty('border-radius');
	      }
	      const outline = this.getAvatarOption('outline');
	      main_core.Dom.style(this.getAvatarContainer(), 'outline', outline);
	      const outlineOffset = this.getAvatarOption('outlineOffset');
	      main_core.Dom.style(this.getAvatarContainer(), 'outline-offset', outlineOffset);
	      main_core.Dom.clean(this.getBadgeContainer());
	      this.getBadges().forEach(badge => {
	        badge.renderTo(this.getBadgeContainer());
	      });
	      const badgesFitContent = this.getBadgesOption('fitContent');
	      if (main_core.Type.isBoolean(badgesFitContent)) {
	        main_core.Dom.style(this.getBadgeContainer(), 'flex-shrink', badgesFitContent ? 0 : null);
	      }
	      const badgesJustifyContent = this.getBadgesOption('justifyContent');
	      if (main_core.Type.isStringFilled(badgesJustifyContent) || badgesJustifyContent === null) {
	        main_core.Dom.style(this.getBadgeContainer(), {
	          flexGrow: badgesJustifyContent ? '1' : null,
	          justifyContent: badgesJustifyContent || null
	        });
	      }
	      const badgesMaxWidth = this.getBadgesOption('maxWidth');
	      if (main_core.Type.isString(badgesMaxWidth) || main_core.Type.isNumber(badgesMaxWidth)) {
	        main_core.Dom.style(this.getBadgeContainer(), 'max-width', main_core.Type.isNumber(badgesMaxWidth) ? `${badgesMaxWidth}px` : badgesMaxWidth);
	      }
	      const linkTitleNode = this.getLinkTitleNode();
	      if (linkTitleNode) {
	        linkTitleNode.renderTo(this.getLinkTextContainer());
	      } else {
	        this.getLinkTextContainer().textContent = '';
	      }
	      if (this.hasChildren() || this.isDynamic()) {
	        main_core.Dom.addClass(this.getOuterContainer(), 'ui-selector-item-box-has-children');
	        if (this.getDepthLevel() >= this.getTab().getItemMaxDepth()) {
	          main_core.Dom.addClass(this.getOuterContainer(), 'ui-selector-item-box-max-depth');
	        }
	      } else if (this.getOuterContainer().classList.contains('ui-selector-item-box-has-children')) {
	        main_core.Dom.removeClass(this.getOuterContainer(), ['ui-selector-item-box-has-children', 'ui-selector-item-box-max-depth']);
	      }
	      if (this.hasChildren()) {
	        const hasVisibleChild = this.getChildren().getAll().some(child => {
	          return child.isHidden() !== true;
	        });
	        if (!hasVisibleChild) {
	          _classPrivateMethodGet(this, _setHidden, _setHidden2).call(this, true);
	        }
	      }
	      this.toggleVisibility();
	      this.highlight();
	      this.renderChildren(appendChildren);
	      if (this.isAutoOpen()) {
	        this.setAutoOpen(false);
	        requestAnimationFrame(() => {
	          requestAnimationFrame(() => {
	            this.expand();
	          });
	        });
	      }
	      this.rendered = true;
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "renderRoot",
	    value: function renderRoot(appendChildren = false) {
	      this.renderChildren(appendChildren);
	      this.rendered = true;
	      const stub = this.getTab().getStub();
	      if (stub && stub.isAutoShow() && (this.getDialog().isLoaded() || !this.getDialog().hasDynamicLoad())) {
	        if (this.hasChildren()) {
	          stub.hide();
	        } else {
	          stub.show();
	        }
	      }
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "renderChildren",
	    value: function renderChildren(appendChildren = false) {
	      if (!appendChildren) {
	        if (main_core.Browser.isIE()) {
	          main_core.Dom.clean(this.getChildrenContainer());
	        } else {
	          this.getChildrenContainer().textContent = '';
	        }
	      }
	      if (this.hasChildren()) {
	        let previousSibling = null;
	        this.getChildren().forEach(child => {
	          child.render(appendChildren);
	          const container = child.getOuterContainer();
	          if (!appendChildren) {
	            main_core.Dom.append(container, this.getChildrenContainer());
	          }
	          if (!container.parentNode) {
	            if (previousSibling !== null) {
	              main_core.Dom.insertAfter(container, previousSibling.getOuterContainer());
	            } else {
	              main_core.Dom.append(container, this.getChildrenContainer());
	            }
	          }
	          previousSibling = child;
	        });
	      }
	    }
	  }, {
	    key: "isRendered",
	    value: function isRendered() {
	      return this.rendered && this.getDialog() && this.getDialog().isRendered();
	    }
	  }, {
	    key: "enableRender",
	    value: function enableRender() {
	      this.rendered = true;
	    }
	  }, {
	    key: "disableRender",
	    value: function disableRender() {
	      this.rendered = false;
	    }
	  }, {
	    key: "getRenderMode",
	    value: function getRenderMode() {
	      return this.renderMode;
	    }
	  }, {
	    key: "isHidden",
	    value: function isHidden() {
	      return this.hidden === true || this.getItem().isHidden() === true;
	    }
	  }, {
	    key: "setHidden",
	    value: function setHidden(flag) {
	      if (!main_core.Type.isBoolean(flag) || this.isRoot()) {
	        return;
	      }
	      _classPrivateMethodGet(this, _setHidden, _setHidden2).call(this, flag);
	      if (this.isRendered()) {
	        this.toggleVisibility();
	        let parentNode = this.getParentNode();
	        const isHidden = this.isHidden();
	        while (parentNode.isRoot() === false) {
	          if (isHidden) {
	            const hasVisibleChild = parentNode.getChildren().getAll().some(child => {
	              return child.isHidden() !== true;
	            });
	            if (!hasVisibleChild) {
	              var _parentNode;
	              _classPrivateMethodGet(_parentNode = parentNode, _setHidden, _setHidden2).call(_parentNode, true);
	            }
	            parentNode.toggleVisibility();
	          } else {
	            var _parentNode2;
	            _classPrivateMethodGet(_parentNode2 = parentNode, _setHidden, _setHidden2).call(_parentNode2, false);
	            parentNode.toggleVisibility();
	            if (parentNode.isHidden()) {
	              break;
	            }
	          }
	          parentNode = parentNode.getParentNode();
	        }
	      }
	    }
	  }, {
	    key: "toggleVisibility",
	    value: function toggleVisibility() {
	      if (this.isHidden()) {
	        main_core.Dom.addClass(this.getOuterContainer(), '--hidden');
	      } else if (this.getOuterContainer().classList.contains('--hidden')) {
	        main_core.Dom.removeClass(this.getOuterContainer(), '--hidden');
	      }
	    }
	  }, {
	    key: "getTitle",
	    value: function getTitle() {
	      const titleNode = this.getTitleNode();
	      return titleNode !== null ? titleNode.getText() : null;
	    }
	  }, {
	    key: "getTitleNode",
	    value: function getTitleNode() {
	      return this.title !== null ? this.title : this.getItem().getTitleNode();
	    }
	  }, {
	    key: "setTitle",
	    value: function setTitle(title) {
	      if (main_core.Type.isString(title) || main_core.Type.isPlainObject(title)) {
	        this.title = new TextNode(title);
	      } else if (title === null) {
	        this.title = null;
	      }
	    }
	  }, {
	    key: "getSubtitle",
	    value: function getSubtitle() {
	      const subtitleNode = this.getSubtitleNode();
	      return subtitleNode !== null ? subtitleNode.getText() : null;
	    }
	  }, {
	    key: "getSubtitleNode",
	    value: function getSubtitleNode() {
	      return this.subtitle !== null ? this.subtitle : this.getItem().getSubtitleNode();
	    }
	  }, {
	    key: "setSubtitle",
	    value: function setSubtitle(subtitle) {
	      if (main_core.Type.isString(subtitle) || main_core.Type.isPlainObject(subtitle)) {
	        this.subtitle = new TextNode(subtitle);
	      } else if (subtitle === null) {
	        this.subtitle = null;
	      }
	    }
	  }, {
	    key: "getSupertitle",
	    value: function getSupertitle() {
	      const supertitleNode = this.getSupertitleNode();
	      return supertitleNode !== null ? supertitleNode.getText() : null;
	    }
	  }, {
	    key: "getSupertitleNode",
	    value: function getSupertitleNode() {
	      return this.supertitle !== null ? this.supertitle : this.getItem().getSupertitleNode();
	    }
	  }, {
	    key: "setSupertitle",
	    value: function setSupertitle(supertitle) {
	      if (main_core.Type.isString(supertitle) || main_core.Type.isPlainObject(supertitle)) {
	        this.supertitle = new TextNode(supertitle);
	      } else if (supertitle === null) {
	        this.supertitle = null;
	      }
	    }
	  }, {
	    key: "getCaption",
	    value: function getCaption() {
	      const caption = this.getCaptionNode();
	      return caption !== null ? caption.getText() : null;
	    }
	  }, {
	    key: "getCaptionNode",
	    value: function getCaptionNode() {
	      return this.caption !== null ? this.caption : this.getItem().getCaptionNode();
	    }
	  }, {
	    key: "setCaption",
	    value: function setCaption(caption) {
	      if (main_core.Type.isString(caption) || main_core.Type.isPlainObject(caption)) {
	        this.caption = new TextNode(caption);
	      } else if (caption === null) {
	        this.caption = null;
	      }
	    }
	  }, {
	    key: "getCaptionOption",
	    value: function getCaptionOption(option) {
	      if (!main_core.Type.isUndefined(this.captionOptions[option])) {
	        return this.captionOptions[option];
	      }
	      return this.getItem().getCaptionOption(option);
	    }
	  }, {
	    key: "setCaptionOption",
	    value: function setCaptionOption(option, value) {
	      if (main_core.Type.isStringFilled(option) && !main_core.Type.isUndefined(value)) {
	        this.captionOptions[option] = value;
	      }
	    }
	  }, {
	    key: "setCaptionOptions",
	    value: function setCaptionOptions(options) {
	      if (main_core.Type.isPlainObject(options)) {
	        Object.keys(options).forEach(option => {
	          this.setCaptionOption(option, options[option]);
	        });
	      }
	    }
	  }, {
	    key: "getAvatar",
	    value: function getAvatar() {
	      return this.avatar !== null ? this.avatar : this.getItem().getAvatar();
	    }
	  }, {
	    key: "setAvatar",
	    value: function setAvatar(avatar) {
	      if (main_core.Type.isString(avatar) || avatar === null) {
	        this.avatar = avatar;
	      }
	    }
	  }, {
	    key: "getAvatarOption",
	    value: function getAvatarOption(option) {
	      return this.avatarOptions === null || main_core.Type.isUndefined(this.avatarOptions[option]) ? this.getItem().getAvatarOption(option) : this.avatarOptions[option];
	    }
	  }, {
	    key: "setAvatarOption",
	    value: function setAvatarOption(option, value) {
	      if (main_core.Type.isStringFilled(option) && !main_core.Type.isUndefined(value)) {
	        if (this.avatarOptions === null) {
	          this.avatarOptions = {};
	        }
	        this.avatarOptions[option] = value;
	      }
	    }
	  }, {
	    key: "setAvatarOptions",
	    value: function setAvatarOptions(avatarOptions) {
	      if (main_core.Type.isPlainObject(avatarOptions)) {
	        Object.keys(avatarOptions).forEach(option => {
	          this.setAvatarOption(option, avatarOptions[option]);
	        });
	      }
	    }
	  }, {
	    key: "getTextColor",
	    value: function getTextColor() {
	      return this.textColor !== null ? this.textColor : this.getItem().getTextColor();
	    }
	  }, {
	    key: "setTextColor",
	    value: function setTextColor(textColor) {
	      if (main_core.Type.isString(textColor) || textColor === null) {
	        this.textColor = textColor;
	      }
	    }
	  }, {
	    key: "getLink",
	    value: function getLink() {
	      return this.link !== null ? this.getItem().replaceMacros(this.link) : this.getItem().getLink();
	    }
	  }, {
	    key: "setLink",
	    value: function setLink(link) {
	      if (main_core.Type.isString(link) || link === null) {
	        this.link = link;
	      }
	    }
	  }, {
	    key: "getLinkTitle",
	    value: function getLinkTitle() {
	      const linkTitle = this.getLinkTitleNode();
	      return linkTitle !== null ? linkTitle.getText() : null;
	    }
	  }, {
	    key: "getLinkTitleNode",
	    value: function getLinkTitleNode() {
	      return this.linkTitle !== null ? this.linkTitle : this.getItem().getLinkTitleNode();
	    }
	  }, {
	    key: "setLinkTitle",
	    value: function setLinkTitle(title) {
	      if (main_core.Type.isString(title) || main_core.Type.isPlainObject(title)) {
	        this.linkTitle = new TextNode(title);
	      } else if (title === null) {
	        this.linkTitle = null;
	      }
	    }
	  }, {
	    key: "getBadges",
	    value: function getBadges() {
	      return this.badges !== null ? this.badges : this.getItem().getBadges();
	    }
	  }, {
	    key: "setBadges",
	    value: function setBadges(badges) {
	      if (main_core.Type.isArray(badges)) {
	        this.badges = [];
	        badges.forEach(badge => {
	          this.badges.push(new ItemBadge(badge));
	        });
	      } else if (badges === null) {
	        this.badges = null;
	      }
	    }
	  }, {
	    key: "getBadgesOption",
	    value: function getBadgesOption(option) {
	      if (!main_core.Type.isUndefined(this.badgesOptions[option])) {
	        return this.badgesOptions[option];
	      }
	      return this.getItem().getBadgesOption(option);
	    }
	  }, {
	    key: "setBadgesOption",
	    value: function setBadgesOption(option, value) {
	      if (main_core.Type.isStringFilled(option) && !main_core.Type.isUndefined(value)) {
	        this.badgesOptions[option] = value;
	      }
	    }
	  }, {
	    key: "setBadgesOptions",
	    value: function setBadgesOptions(options) {
	      if (main_core.Type.isPlainObject(options)) {
	        Object.keys(options).forEach(option => {
	          this.setBadgesOption(option, options[option]);
	        });
	      }
	    }
	  }, {
	    key: "getOuterContainer",
	    value: function getOuterContainer() {
	      return this.cache.remember('outer-container', () => {
	        let className = '';
	        if (this.hasChildren() || this.isDynamic()) {
	          className += ' ui-selector-item-box-has-children';
	          if (this.getDepthLevel() >= this.getTab().getItemMaxDepth()) {
	            className += ' ui-selector-item-box-max-depth';
	          }
	        } else if (this.getItem().isSelected()) {
	          className += ' ui-selector-item-box-selected';
	        }
	        if (this.isOpen()) {
	          className += ' ui-selector-item-box-open';
	        }
	        const div = document.createElement('div');
	        div.className = `ui-selector-item-box${className}`;
	        div.appendChild(this.getContainer());
	        div.appendChild(this.getChildrenContainer());
	        return div;
	      });
	    }
	  }, {
	    key: "getChildrenContainer",
	    value: function getChildrenContainer() {
	      if (this.isRoot() && this.getTab()) {
	        return this.getTab().getItemsContainer();
	      }
	      return this.cache.remember('children-container', () => {
	        const div = document.createElement('div');
	        div.className = 'ui-selector-item-children';
	        return div;
	      });
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      return this.cache.remember('container', () => {
	        const div = document.createElement('div');
	        div.className = 'ui-selector-item';
	        main_core.Event.bind(div, 'click', this.handleClick.bind(this));
	        main_core.Event.bind(div, 'mouseenter', this.handleMouseEnter.bind(this));
	        main_core.Event.bind(div, 'mouseleave', this.handleMouseLeave.bind(this));
	        div.appendChild(this.getAvatarContainer());
	        div.appendChild(this.getTitlesContainer());
	        div.appendChild(this.getIndicatorContainer());
	        if (main_core.Type.isStringFilled(this.getLink())) {
	          div.appendChild(this.getLinkContainer());
	        }
	        return div;
	      });
	    }
	  }, {
	    key: "getAvatarContainer",
	    value: function getAvatarContainer() {
	      return this.cache.remember('avatar', () => {
	        const div = document.createElement('div');
	        div.className = 'ui-selector-item-avatar';
	        return div;
	      });
	    }
	  }, {
	    key: "getTitlesContainer",
	    value: function getTitlesContainer() {
	      return this.cache.remember('titles', () => {
	        const div = document.createElement('div');
	        div.className = 'ui-selector-item-titles';
	        div.appendChild(this.getSupertitleContainer());
	        div.appendChild(this.getTitleBoxContainer());
	        div.appendChild(this.getSubtitleContainer());
	        return div;
	      });
	    }
	  }, {
	    key: "getTitleBoxContainer",
	    value: function getTitleBoxContainer() {
	      return this.cache.remember('title-box', () => {
	        const div = document.createElement('div');
	        div.className = 'ui-selector-item-title-box';
	        div.appendChild(this.getTitleContainer());
	        div.appendChild(this.getBadgeContainer());
	        div.appendChild(this.getCaptionContainer());
	        return div;
	      });
	    }
	  }, {
	    key: "getTitleContainer",
	    value: function getTitleContainer() {
	      return this.cache.remember('title', () => {
	        const div = document.createElement('div');
	        div.className = 'ui-selector-item-title';
	        return div;
	      });
	    }
	  }, {
	    key: "getSubtitleContainer",
	    value: function getSubtitleContainer() {
	      return this.cache.remember('subtitle', () => {
	        const div = document.createElement('div');
	        div.className = 'ui-selector-item-subtitle';
	        return div;
	      });
	    }
	  }, {
	    key: "getSupertitleContainer",
	    value: function getSupertitleContainer() {
	      return this.cache.remember('supertitle', () => {
	        const div = document.createElement('div');
	        div.className = 'ui-selector-item-supertitle';
	        return div;
	      });
	    }
	  }, {
	    key: "getCaptionContainer",
	    value: function getCaptionContainer() {
	      return this.cache.remember('caption', () => {
	        const div = document.createElement('div');
	        div.className = 'ui-selector-item-caption';
	        return div;
	      });
	    }
	  }, {
	    key: "getIndicatorContainer",
	    value: function getIndicatorContainer() {
	      return this.cache.remember('indicator', () => {
	        const div = document.createElement('div');
	        div.className = 'ui-selector-item-indicator';
	        return div;
	      });
	    }
	  }, {
	    key: "getBadgeContainer",
	    value: function getBadgeContainer() {
	      return this.cache.remember('badge', () => {
	        const div = document.createElement('div');
	        div.className = 'ui-selector-item-badges';
	        return div;
	      });
	    }
	  }, {
	    key: "getLinkContainer",
	    value: function getLinkContainer() {
	      return this.cache.remember('link', () => {
	        const anchor = document.createElement('a');
	        anchor.className = 'ui-selector-item-link';
	        anchor.href = this.getLink();
	        anchor.target = '_blank';
	        anchor.title = '';
	        main_core.Event.bind(anchor, 'click', this.handleLinkClick.bind(this));
	        anchor.appendChild(this.getLinkTextContainer());
	        return anchor;
	      });
	    }
	  }, {
	    key: "getLinkTextContainer",
	    value: function getLinkTextContainer() {
	      return this.cache.remember('link-text', () => {
	        const span = document.createElement('span');
	        span.className = 'ui-selector-item-link-text';
	        return span;
	      });
	    }
	  }, {
	    key: "showLink",
	    value: function showLink() {
	      if (main_core.Type.isStringFilled(this.getLink())) {
	        main_core.Dom.addClass(this.getLinkContainer(), 'ui-selector-item-link--show');
	        requestAnimationFrame(() => {
	          requestAnimationFrame(() => {
	            main_core.Dom.addClass(this.getLinkContainer(), 'ui-selector-item-link--animate');
	          });
	        });
	      }
	    }
	  }, {
	    key: "hideLink",
	    value: function hideLink() {
	      if (main_core.Type.isStringFilled(this.getLink())) {
	        main_core.Dom.removeClass(this.getLinkContainer(), ['ui-selector-item-link--show', 'ui-selector-item-link--animate']);
	      }
	    }
	  }, {
	    key: "setHighlights",
	    value: function setHighlights(highlights) {
	      this.highlights = highlights;
	    }
	  }, {
	    key: "getHighlights",
	    value: function getHighlights() {
	      return this.highlights;
	    }
	  }, {
	    key: "highlight",
	    value: function highlight() {
	      this.getHighlights().forEach(matchField => {
	        const field = matchField.getField();
	        const fieldName = field.getName();
	        if (field.isCustom()) {
	          const text = this.getItem().getCustomData().get(fieldName);
	          this.getSubtitleContainer().innerHTML = Highlighter.mark(text, matchField.getMatches());
	        } else if (field.getName() === 'title') {
	          this.getTitleContainer().innerHTML = Highlighter.mark(this.getItem().getTitleNode(), matchField.getMatches());
	        } else if (field.getName() === 'subtitle') {
	          this.getSubtitleContainer().innerHTML = Highlighter.mark(this.getItem().getSubtitleNode(), matchField.getMatches());
	        } else if (field.getName() === 'supertitle') {
	          this.getSupertitleContainer().innerHTML = Highlighter.mark(this.getItem().getSupertitleNode(), matchField.getMatches());
	        } else if (field.getName() === 'caption') {
	          this.getCaptionContainer().innerHTML = Highlighter.mark(this.getItem().getCaptionNode(), matchField.getMatches());
	        }
	      });
	    }
	  }, {
	    key: "select",
	    value: function select() {
	      if (this.hasChildren() || this.isDynamic()) {
	        return;
	      }
	      main_core.Dom.addClass(this.getOuterContainer(), 'ui-selector-item-box-selected');
	    }
	  }, {
	    key: "deselect",
	    value: function deselect() {
	      if (this.hasChildren() || this.isDynamic()) {
	        return;
	      }
	      main_core.Dom.removeClass(this.getOuterContainer(), 'ui-selector-item-box-selected');
	    }
	  }, {
	    key: "focus",
	    value: function focus() {
	      if (this.isFocused()) {
	        return;
	      }
	      this.focused = true;
	      main_core.Dom.addClass(this.getOuterContainer(), 'ui-selector-item-box-focused');
	      this.getDialog().emit('ItemNode:onFocus', {
	        node: this
	      });
	    }
	  }, {
	    key: "unfocus",
	    value: function unfocus() {
	      if (!this.isFocused()) {
	        return;
	      }
	      this.focused = false;
	      main_core.Dom.removeClass(this.getOuterContainer(), 'ui-selector-item-box-focused');
	      this.getDialog().emit('ItemNode:onUnfocus', {
	        node: this
	      });
	    }
	  }, {
	    key: "isFocused",
	    value: function isFocused() {
	      return this.focused;
	    }
	  }, {
	    key: "click",
	    value: function click() {
	      if (this.hasChildren() || this.isDynamic()) {
	        if (this.isOpen()) {
	          this.collapse();
	        } else {
	          this.expand();
	        }
	      } else {
	        if (this.getItem().isSelected()) {
	          if (this.getItem().isDeselectable()) {
	            this.getItem().deselect();
	          }
	          if (this.getDialog().shouldHideOnDeselect()) {
	            this.getDialog().hide();
	          }
	        } else {
	          this.getItem().select();
	          if (this.getDialog().shouldClearSearchOnSelect()) {
	            this.getDialog().clearSearch();
	          }
	          if (this.getDialog().shouldHideOnSelect()) {
	            this.getDialog().hide();
	          }
	        }
	      }
	      this.getDialog().focusSearch();
	    }
	  }, {
	    key: "scrollIntoView",
	    value: function scrollIntoView() {
	      const tabContainer = this.getTab().getContainer();
	      const nodeContainer = this.getContainer();
	      const tabRect = main_core.Dom.getPosition(tabContainer);
	      const nodeRect = main_core.Dom.getPosition(nodeContainer);
	      const margin = 9; // 'ui-selector-items' padding - 'ui-selector-item' margin = 10 - 1

	      if (nodeRect.top < tabRect.top)
	        // scroll up
	        {
	          tabContainer.scrollTop -= tabRect.top - nodeRect.top + margin;
	        } else if (nodeRect.bottom > tabRect.bottom)
	        // scroll down
	        {
	          tabContainer.scrollTop += nodeRect.bottom - tabRect.bottom + margin;
	        }
	    }
	  }, {
	    key: "handleClick",
	    value: function handleClick() {
	      this.click();
	    }
	  }, {
	    key: "handleLinkClick",
	    value: function handleLinkClick(event) {
	      this.getDialog().emit('ItemNode:onLinkClick', {
	        node: this,
	        event
	      });
	      event.stopPropagation();
	    }
	  }, {
	    key: "handleMouseEnter",
	    value: function handleMouseEnter() {
	      this.focus();
	      this.showLink();
	      _classPrivateMethodGet(this, _makeEllipsisTitle, _makeEllipsisTitle2).call(this);
	    }
	  }, {
	    key: "handleMouseLeave",
	    value: function handleMouseLeave() {
	      this.unfocus();
	      this.hideLink();
	    }
	  }]);
	  return ItemNode;
	}();
	function _setHidden2(flag) {
	  if (main_core.Type.isBoolean(flag) && !this.isRoot()) {
	    this.hidden = flag;
	  }
	}
	function _makeEllipsisTitle2() {
	  var _this$constructor;
	  if (_classStaticPrivateMethodGet(_this$constructor = this.constructor, ItemNode, _isEllipsisActive).call(_this$constructor, this.getTitleContainer())) {
	    var _this$constructor2;
	    this.getContainer().setAttribute('title', _classStaticPrivateMethodGet(_this$constructor2 = this.constructor, ItemNode, _sanitizeTitle).call(_this$constructor2, this.getTitleContainer().textContent));
	  } else {
	    main_core.Dom.attr(this.getContainer(), 'title', null);
	  }
	  const containers = [this.getSupertitleContainer(), this.getSubtitleContainer(), this.getCaptionContainer(), ...this.getBadges().map(badge => badge.getContainer(this.getBadgeContainer()))];
	  containers.forEach(container => {
	    var _this$constructor3;
	    if (_classStaticPrivateMethodGet(_this$constructor3 = this.constructor, ItemNode, _isEllipsisActive).call(_this$constructor3, container)) {
	      var _this$constructor4;
	      container.setAttribute('title', _classStaticPrivateMethodGet(_this$constructor4 = this.constructor, ItemNode, _sanitizeTitle).call(_this$constructor4, container.textContent));
	    } else {
	      main_core.Dom.attr(container, 'title', null);
	    }
	  });
	}
	function _isEllipsisActive(element) {
	  return element.offsetWidth < element.scrollWidth;
	}
	function _sanitizeTitle(text) {
	  return text.replace(/[\t ]+/gm, ' ').replace(/\n+/gm, '\n').trim();
	}

	let SearchFieldIndex = /*#__PURE__*/function () {
	  function SearchFieldIndex(field, indexes = []) {
	    babelHelpers.classCallCheck(this, SearchFieldIndex);
	    babelHelpers.defineProperty(this, "field", null);
	    babelHelpers.defineProperty(this, "indexes", []);
	    this.field = field;
	    this.addIndexes(indexes);
	  }
	  babelHelpers.createClass(SearchFieldIndex, [{
	    key: "getField",
	    value: function getField() {
	      return this.field;
	    }
	  }, {
	    key: "getIndexes",
	    value: function getIndexes() {
	      return this.indexes;
	    }
	  }, {
	    key: "addIndex",
	    value: function addIndex(index) {
	      this.getIndexes().push(index);
	    }
	  }, {
	    key: "addIndexes",
	    value: function addIndexes(indexes) {
	      indexes.forEach(index => {
	        this.addIndex(index);
	      });
	    }
	  }]);
	  return SearchFieldIndex;
	}();

	let WordIndex = /*#__PURE__*/function () {
	  function WordIndex(word, startIndex) {
	    babelHelpers.classCallCheck(this, WordIndex);
	    babelHelpers.defineProperty(this, "word", '');
	    babelHelpers.defineProperty(this, "startIndex", 0);
	    this.setWord(word);
	    this.setStartIndex(startIndex);
	  }
	  babelHelpers.createClass(WordIndex, [{
	    key: "getWord",
	    value: function getWord() {
	      return this.word;
	    }
	  }, {
	    key: "setWord",
	    value: function setWord(word) {
	      if (main_core.Type.isStringFilled(word)) {
	        this.word = word;
	      }
	      return this;
	    }
	  }, {
	    key: "getStartIndex",
	    value: function getStartIndex() {
	      return this.startIndex;
	    }
	  }, {
	    key: "setStartIndex",
	    value: function setStartIndex(index) {
	      if (main_core.Type.isNumber(index) && index >= 0) {
	        this.startIndex = index;
	      }
	      return this;
	    }
	  }]);
	  return WordIndex;
	}();

	/**
	 * @license
	 * Lodash <https://lodash.com/>
	 * Copyright OpenJS Foundation and other contributors <https://openjsf.org/>
	 * Released under MIT license <https://lodash.com/license>
	 * Based on Underscore.js 1.8.3 <http://underscorejs.org/LICENSE>
	 * Copyright Jeremy Ashkenas, DocumentCloud and Investigative Reporters & Editors
	 */

	/** Used to compose unicode character classes. */
	const rsAstralRange = '\\ud800-\\udfff';
	const rsComboMarksRange = '\\u0300-\\u036f';
	const reComboHalfMarksRange = '\\ufe20-\\ufe2f';
	const rsComboSymbolsRange = '\\u20d0-\\u20ff';
	const rsComboMarksExtendedRange = '\\u1ab0-\\u1aff';
	const rsComboMarksSupplementRange = '\\u1dc0-\\u1dff';
	const rsComboRange = rsComboMarksRange + reComboHalfMarksRange + rsComboSymbolsRange + rsComboMarksExtendedRange + rsComboMarksSupplementRange;
	const rsDingbatRange = '\\u2700-\\u27bf';
	const rsLowerRange = 'a-z\\xdf-\\xf6\\xf8-\\xff';
	const rsMathOpRange = '\\xac\\xb1\\xd7\\xf7';
	const rsNonCharRange = '\\x00-\\x2f\\x3a-\\x40\\x5b-\\x60\\x7b-\\xbf';
	const rsPunctuationRange = '\\u2000-\\u206f';
	const rsSpaceRange = ' \\t\\x0b\\f\\xa0\\ufeff\\n\\r\\u2028\\u2029\\u1680\\u180e\\u2000\\u2001\\u2002\\u2003\\u2004\\u2005\\u2006\\u2007\\u2008\\u2009\\u200a\\u202f\\u205f\\u3000';
	const rsUpperRange = 'A-Z\\xc0-\\xd6\\xd8-\\xde';
	const rsVarRange = '\\ufe0e\\ufe0f';
	const rsBreakRange = rsMathOpRange + rsNonCharRange + rsPunctuationRange + rsSpaceRange;

	/** Used to compose unicode capture groups. */
	const rsApos = '[\'\u2019]';
	const rsBreak = `[${rsBreakRange}]`;
	const rsCombo = `[${rsComboRange}]`;
	const rsDigit = '\\d';
	const rsDingbat = `[${rsDingbatRange}]`;
	const rsLower = `[${rsLowerRange}]`;
	const rsMisc = `[^${rsAstralRange}${rsBreakRange + rsDigit + rsDingbatRange + rsLowerRange + rsUpperRange}]`;
	const rsFitz = '\\ud83c[\\udffb-\\udfff]';
	const rsModifier = `(?:${rsCombo}|${rsFitz})`;
	const rsNonAstral = `[^${rsAstralRange}]`;
	const rsRegional = '(?:\\ud83c[\\udde6-\\uddff]){2}';
	const rsSurrPair = '[\\ud800-\\udbff][\\udc00-\\udfff]';
	const rsUpper = `[${rsUpperRange}]`;
	const rsZWJ = '\\u200d';

	/** Used to compose unicode regexes. */
	const rsMiscLower = `(?:${rsLower}|${rsMisc})`;
	const rsMiscUpper = `(?:${rsUpper}|${rsMisc})`;
	const rsOptContrLower = `(?:${rsApos}(?:d|ll|m|re|s|t|ve))?`;
	const rsOptContrUpper = `(?:${rsApos}(?:D|LL|M|RE|S|T|VE))?`;
	const reOptMod = `${rsModifier}?`;
	const rsOptVar = `[${rsVarRange}]?`;
	const rsOptJoin = `(?:${rsZWJ}(?:${[rsNonAstral, rsRegional, rsSurrPair].join('|')})${rsOptVar + reOptMod})*`;
	const rsOrdLower = '\\d*(?:1st|2nd|3rd|(?![123])\\dth)(?=\\b|[A-Z_])';
	const rsOrdUpper = '\\d*(?:1ST|2ND|3RD|(?![123])\\dTH)(?=\\b|[a-z_])';
	const rsSeq = rsOptVar + reOptMod + rsOptJoin;
	const rsEmoji = `(?:${[rsDingbat, rsRegional, rsSurrPair].join('|')})${rsSeq}`;
	const unicodeWordsRegExp = new RegExp([`${rsUpper}?${rsLower}+${rsOptContrLower}(?=${[rsBreak, rsUpper, '$'].join('|')})`, `${rsMiscUpper}+${rsOptContrUpper}(?=${[rsBreak, rsUpper + rsMiscLower, '$'].join('|')})`, `${rsUpper}?${rsMiscLower}+${rsOptContrLower}`, `${rsUpper}+${rsOptContrUpper}`, rsOrdUpper, rsOrdLower, `${rsDigit}+`, rsEmoji].join('|'), 'g');

	const asciiWordRegExp = /[^\x00-\x2f\x3a-\x40\x5b-\x60\x7b-\x7f]+/g;
	const hasUnicodeWordRegExp = /[a-z][A-Z]|[A-Z]{2}[a-z]|[0-9][a-zA-Z]|[a-zA-Z][0-9]|[^a-zA-Z0-9 ]/;
	const nonWhitespaceRegExp = /[^\s]+/g;
	const specialChars = `!"#$%&'()*+,-.\/:;<=>?@[\\]^_\`{|}`;
	const specialCharsRegExp = new RegExp(`[${specialChars}]`);
	let SearchIndex = /*#__PURE__*/function () {
	  function SearchIndex() {
	    babelHelpers.classCallCheck(this, SearchIndex);
	    babelHelpers.defineProperty(this, "indexes", []);
	  }
	  babelHelpers.createClass(SearchIndex, [{
	    key: "addIndex",
	    value: function addIndex(fieldIndex) {
	      if (main_core.Type.isObject(fieldIndex)) {
	        this.getIndexes().push(fieldIndex);
	      }
	    }
	  }, {
	    key: "getIndexes",
	    value: function getIndexes() {
	      return this.indexes;
	    }
	  }], [{
	    key: "create",
	    value: function create(item) {
	      const index = new SearchIndex();
	      const entity = item.getEntity();
	      if (!item.isSearchable() || !entity.isSearchable() || item.isHidden()) {
	        return index;
	      }
	      const searchFields = entity.getSearchFields();
	      searchFields.forEach(field => {
	        if (!field.isSearchable()) {
	          return;
	        }
	        if (field.isSystem()) {
	          if (field.getName() === 'title') {
	            const textNode = item.getTitleNode();
	            const stripTags = textNode !== null && textNode.getType() === 'html';
	            index.addIndex(this.createIndex(field, item.getTitle(), stripTags));
	          } else if (field.getName() === 'subtitle') {
	            const textNode = item.getSubtitleNode();
	            const stripTags = textNode !== null && textNode.getType() === 'html';
	            index.addIndex(this.createIndex(field, item.getSubtitle(), stripTags));
	          } else if (field.getName() === 'supertitle') {
	            const textNode = item.getSupertitleNode();
	            const stripTags = textNode !== null && textNode.getType() === 'html';
	            index.addIndex(this.createIndex(field, item.getSupertitle(), stripTags));
	          } else if (field.getName() === 'caption') {
	            const textNode = item.getCaptionNode();
	            const stripTags = textNode !== null && textNode.getType() === 'html';
	            index.addIndex(this.createIndex(field, item.getCaption(), stripTags));
	          }
	        } else {
	          const customData = item.getCustomData().get(field.getName());
	          if (!main_core.Type.isUndefined(customData)) {
	            index.addIndex(this.createIndex(field, customData));
	          }
	        }
	      });
	      return index;
	    }
	  }, {
	    key: "createIndex",
	    value: function createIndex(field, text, stripTags = false) {
	      if (!main_core.Type.isStringFilled(text)) {
	        return null;
	      }
	      if (stripTags) {
	        text = text.replace(/<\/?[^>]+>/g, match => ' '.repeat(match.length));
	        text = text.replace(/&(?:#\d+|#x[\da-fA-F]+|[0-9a-zA-Z]+);/g, match => ' '.repeat(match.length));
	      }
	      let index = null;
	      if (field.getType() === 'string') {
	        const wordIndexes = this.splitText(text);
	        if (main_core.Type.isArrayFilled(wordIndexes)) {
	          // "GoPro111 Leto15"
	          // [go, pro, 111, leto, 15] + [gopro111, leto15]
	          this.fillComplexWords(wordIndexes);
	          this.fillNonCharWords(wordIndexes, text);
	          index = new SearchFieldIndex(field, wordIndexes);
	        }
	      } else if (field.getType() === 'email') {
	        const position = text.indexOf('@');
	        if (position !== -1) {
	          index = new SearchFieldIndex(field, [new WordIndex(text.toLowerCase(), 0), new WordIndex(text.substr(position + 1).toLowerCase(), position + 1)]);
	        }
	      }
	      return index;
	    }
	  }, {
	    key: "splitText",
	    value: function splitText(text) {
	      if (!main_core.Type.isStringFilled(text)) {
	        return [];
	      }
	      return this.hasUnicodeWord(text) ? this.splitUnicodeText(text) : this.splitAsciiText(text);
	    }
	  }, {
	    key: "splitUnicodeText",
	    value: function splitUnicodeText(text) {
	      return this.splitTextInternal(text, unicodeWordsRegExp);
	    }
	  }, {
	    key: "splitAsciiText",
	    value: function splitAsciiText(text) {
	      return this.splitTextInternal(text, asciiWordRegExp);
	    }
	  }, {
	    key: "hasUnicodeWord",
	    value: function hasUnicodeWord(text) {
	      return hasUnicodeWordRegExp.test(text);
	    }
	  }, {
	    key: "splitTextInternal",
	    value: function splitTextInternal(text, regExp) {
	      let match;
	      const result = [];
	      regExp.lastIndex = 0;
	      while ((match = regExp.exec(text)) !== null) {
	        if (match.index === regExp.lastIndex) {
	          regExp.lastIndex++;
	        }
	        result.push(new WordIndex(match[0].toLowerCase(), match.index));
	      }
	      return result;
	    }
	    /**
	     *  @private
	     */
	  }, {
	    key: "fillComplexWords",
	    value: function fillComplexWords(indexes) {
	      if (indexes.length < 2) {
	        return;
	      }
	      let complexWord = null;
	      let startIndex = null;
	      indexes.forEach((currentIndex, currentArrayIndex) => {
	        const nextIndex = indexes[currentArrayIndex + 1];
	        if (nextIndex) {
	          const sameWord = currentIndex.getStartIndex() + currentIndex.getWord().length === nextIndex.getStartIndex();
	          if (sameWord) {
	            if (complexWord === null) {
	              complexWord = currentIndex.getWord();
	              startIndex = currentIndex.getStartIndex();
	            }
	            complexWord += nextIndex.getWord();
	          } else if (complexWord !== null) {
	            indexes.push(new WordIndex(complexWord, startIndex));
	            complexWord = null;
	            startIndex = null;
	          }
	        } else if (complexWord !== null) {
	          indexes.push(new WordIndex(complexWord, startIndex));
	          complexWord = null;
	          startIndex = null;
	        }
	      });
	    }
	    /**
	     *  @private
	     */
	  }, {
	    key: "fillNonCharWords",
	    value: function fillNonCharWords(indexes, text) {
	      if (!specialCharsRegExp.test(text)) {
	        return;
	      }
	      let match;
	      while ((match = nonWhitespaceRegExp.exec(text)) !== null) {
	        if (match.index === nonWhitespaceRegExp.lastIndex) {
	          nonWhitespaceRegExp.lastIndex++;
	        }
	        const word = match[0];
	        if (specialCharsRegExp.test(word)) {
	          indexes.push(new WordIndex(word.toLowerCase(), match.index));
	          for (let i = 0; i < word.length; i++) {
	            const char = word[i];
	            if (!specialChars.includes(char)) {
	              break;
	            }
	            const wordToIndex = word.substr(i + 1);
	            if (wordToIndex.length) {
	              indexes.push(new WordIndex(wordToIndex.toLowerCase(), match.index + i + 1));
	            }
	          }
	        }
	      }
	      nonWhitespaceRegExp.lastIndex = 0;
	    }
	  }]);
	  return SearchIndex;
	}();

	let EntityFilter = /*#__PURE__*/function () {
	  function EntityFilter(filterOptions) {
	    babelHelpers.classCallCheck(this, EntityFilter);
	    babelHelpers.defineProperty(this, "id", null);
	    babelHelpers.defineProperty(this, "options", {});
	    const options = main_core.Type.isPlainObject(filterOptions) ? filterOptions : {};
	    this.id = options.id;
	    this.options = options.options;
	  }
	  babelHelpers.createClass(EntityFilter, [{
	    key: "getId",
	    value: function getId() {
	      return this.id;
	    }
	  }, {
	    key: "getOptions",
	    value: function getOptions() {
	      return this.options;
	    }
	  }, {
	    key: "toJSON",
	    value: function toJSON() {
	      return {
	        id: this.getId(),
	        options: this.getOptions()
	      };
	    }
	  }]);
	  return EntityFilter;
	}();

	/**
	 * @memberof BX.UI.EntitySelector
	 */
	let Entity = /*#__PURE__*/function () {
	  function Entity(entityOptions) {
	    babelHelpers.classCallCheck(this, Entity);
	    babelHelpers.defineProperty(this, "id", null);
	    babelHelpers.defineProperty(this, "options", {});
	    babelHelpers.defineProperty(this, "searchable", true);
	    babelHelpers.defineProperty(this, "searchFields", null);
	    babelHelpers.defineProperty(this, "dynamicLoad", false);
	    babelHelpers.defineProperty(this, "dynamicSearch", false);
	    babelHelpers.defineProperty(this, "substituteEntityId", null);
	    babelHelpers.defineProperty(this, "searchCacheLimits", []);
	    babelHelpers.defineProperty(this, "filters", new Map());
	    babelHelpers.defineProperty(this, "itemOptions", {});
	    babelHelpers.defineProperty(this, "tagOptions", {});
	    babelHelpers.defineProperty(this, "badgeOptions", []);
	    babelHelpers.defineProperty(this, "textNodes", new Map());
	    let options = main_core.Type.isPlainObject(entityOptions) ? entityOptions : {};
	    if (!main_core.Type.isStringFilled(options.id)) {
	      throw new Error('EntitySelector.Entity: "id" parameter is required.');
	    }
	    const defaultOptions = this.constructor.getEntityDefaultOptions(options.id) || {};
	    options = main_core.Runtime.merge(JSON.parse(JSON.stringify(defaultOptions)), options);
	    this.id = options.id.toLowerCase();
	    this.options = main_core.Type.isPlainObject(options.options) ? options.options : {};
	    this.itemOptions = main_core.Type.isPlainObject(options.itemOptions) ? options.itemOptions : {};
	    this.tagOptions = main_core.Type.isPlainObject(options.tagOptions) ? options.tagOptions : {};
	    this.badgeOptions = main_core.Type.isArray(options.badgeOptions) ? options.badgeOptions : [];
	    this.substituteEntityId = main_core.Type.isStringFilled(options.substituteEntityId) ? options.substituteEntityId : null;
	    if (main_core.Type.isArray(options.filters)) {
	      options.filters.forEach(filterOptions => {
	        this.addFilter(filterOptions);
	      });
	    }
	    this.searchFields = new main_core_collections.OrderedArray((fieldA, fieldB) => {
	      if (fieldA.getSort() !== null && fieldB.getSort() === null) {
	        return -1;
	      } else if (fieldA.getSort() === null && fieldB.getSort() !== null) {
	        return 1;
	      } else if (fieldA.getSort() === null && fieldB.getSort() === null) {
	        return -1;
	      } else {
	        return fieldA.getSort() - fieldB.getSort();
	      }
	    });
	    this.setSearchable(options.searchable);
	    this.setDynamicLoad(options.dynamicLoad);
	    this.setDynamicSearch(options.dynamicSearch);
	    this.setSearchFields(options.searchFields);
	    this.setSearchCacheLimits(options.searchCacheLimits);
	  }
	  babelHelpers.createClass(Entity, [{
	    key: "getId",
	    value: function getId() {
	      return this.id;
	    }
	  }, {
	    key: "getOptions",
	    value: function getOptions() {
	      return this.options;
	    }
	  }, {
	    key: "getItemOptions",
	    value: function getItemOptions() {
	      return this.itemOptions;
	    }
	  }, {
	    key: "getItemOption",
	    value: function getItemOption(option, entityType) {
	      return this.constructor.getOptionInternal(this.itemOptions, option, entityType);
	    }
	  }, {
	    key: "getTagOptions",
	    value: function getTagOptions() {
	      return this.tagOptions;
	    }
	  }, {
	    key: "getTagOption",
	    value: function getTagOption(option, entityType) {
	      return this.constructor.getOptionInternal(this.tagOptions, option, entityType);
	    }
	  }, {
	    key: "getBadges",
	    value: function getBadges(item) {
	      const entityTypeBadges = this.getItemOption('badges', item.getEntityType()) || [];
	      const badges = [...entityTypeBadges];
	      this.badgeOptions.forEach(badge => {
	        if (main_core.Type.isPlainObject(badge.conditions)) {
	          for (const condition in badge.conditions) {
	            if (item.getCustomData().get(condition) !== badge.conditions[condition]) {
	              return;
	            }
	          }
	          badges.push(badge);
	        }
	      });
	      return badges;
	    }
	  }, {
	    key: "getOptionTextNode",
	    value: function getOptionTextNode(option, entityType) {
	      if (!main_core.Type.isString(option)) {
	        return null;
	      }
	      if (!main_core.Type.isString(entityType)) {
	        entityType = 'default';
	      }
	      let optionNodes = this.textNodes.get(option);
	      let node = optionNodes ? optionNodes.get(entityType) : undefined;
	      if (main_core.Type.isUndefined(node)) {
	        if (!optionNodes) {
	          optionNodes = new Map();
	          this.textNodes.set(option, optionNodes);
	        }
	        const itemOption = this.getItemOption(option, entityType);
	        node = main_core.Type.isString(itemOption) || main_core.Type.isPlainObject(itemOption) ? new TextNode(itemOption) : null;
	        optionNodes.set(entityType, node);
	      }
	      return node;
	    }
	  }, {
	    key: "isSearchable",
	    value: function isSearchable() {
	      return this.searchable;
	    }
	  }, {
	    key: "setSearchable",
	    value: function setSearchable(flag) {
	      if (main_core.Type.isBoolean(flag)) {
	        this.searchable = flag;
	      }
	    }
	  }, {
	    key: "getSearchFields",
	    value: function getSearchFields() {
	      return this.searchFields;
	    }
	  }, {
	    key: "setSearchFields",
	    value: function setSearchFields(searchFields) {
	      this.searchFields.clear();

	      // Default Search Fields
	      const titleField = new SearchField({
	        name: 'title',
	        searchable: true,
	        system: true,
	        type: 'string'
	      });
	      const subtitleField = new SearchField({
	        name: 'subtitle',
	        searchable: true,
	        system: true,
	        type: 'string'
	      });
	      this.searchFields.add(titleField);
	      this.searchFields.add(subtitleField);

	      // Custom Search Fields
	      const customFields = main_core.Type.isArray(searchFields) ? searchFields : [];
	      customFields.forEach(fieldOptions => {
	        const field = new SearchField(fieldOptions);
	        if (field.isSystem())
	          // Entity can override default fields.
	          {
	            // delete a default title field
	            if (field.getName() === 'title') {
	              this.searchFields.delete(titleField);
	            } else if (field.getName() === 'subtitle') {
	              this.searchFields.delete(subtitleField);
	            }
	          }
	        this.searchFields.add(field);
	      });
	      this.searchFields.forEach((field, index) => {
	        field.setSort(index);
	      });
	    }
	  }, {
	    key: "setSearchCacheLimits",
	    value: function setSearchCacheLimits(limits) {
	      if (main_core.Type.isArrayFilled(limits)) {
	        limits.forEach(limit => {
	          if (main_core.Type.isStringFilled(limit)) {
	            this.searchCacheLimits.push(new RegExp(limit, 'i'));
	          }
	        });
	      }
	    }
	  }, {
	    key: "getSearchCacheLimits",
	    value: function getSearchCacheLimits() {
	      return this.searchCacheLimits;
	    }
	  }, {
	    key: "hasDynamicLoad",
	    value: function hasDynamicLoad() {
	      return this.dynamicLoad;
	    }
	  }, {
	    key: "setDynamicLoad",
	    value: function setDynamicLoad(flag) {
	      if (main_core.Type.isBoolean(flag)) {
	        this.dynamicLoad = flag;
	      }
	    }
	  }, {
	    key: "hasDynamicSearch",
	    value: function hasDynamicSearch() {
	      return this.dynamicSearch;
	    }
	  }, {
	    key: "setDynamicSearch",
	    value: function setDynamicSearch(flag) {
	      if (main_core.Type.isBoolean(flag)) {
	        this.dynamicSearch = flag;
	      }
	    }
	  }, {
	    key: "getFilters",
	    value: function getFilters() {
	      return Array.from(this.filters.values());
	    }
	  }, {
	    key: "addFilters",
	    value: function addFilters(filters) {
	      if (main_core.Type.isArray(filters)) {
	        filters.forEach(filterOptions => {
	          this.addFilter(filterOptions);
	        });
	      }
	    }
	  }, {
	    key: "addFilter",
	    value: function addFilter(filterOptions) {
	      if (main_core.Type.isPlainObject(filterOptions)) {
	        const filter = new EntityFilter(filterOptions);
	        this.filters.set(filter.getId(), filter);
	      }
	    }
	  }, {
	    key: "getFilter",
	    value: function getFilter(id) {
	      return this.filters.get(id) || null;
	    }
	  }, {
	    key: "getSubstituteEntityId",
	    value: function getSubstituteEntityId() {
	      return this.substituteEntityId;
	    }
	  }, {
	    key: "toJSON",
	    value: function toJSON() {
	      return {
	        id: this.getId(),
	        options: this.getOptions(),
	        searchable: this.isSearchable(),
	        dynamicLoad: this.hasDynamicLoad(),
	        dynamicSearch: this.hasDynamicSearch(),
	        filters: this.getFilters(),
	        substituteEntityId: this.getSubstituteEntityId()
	      };
	    }
	  }], [{
	    key: "getDefaultOptions",
	    value: function getDefaultOptions() {
	      if (this.defaultOptions === null) {
	        this.defaultOptions = {};
	        this.getExtensions().forEach(extension => {
	          const settings = main_core.Extension.getSettings(extension);
	          const entities = settings.get('entities', []);
	          entities.forEach(entity => {
	            if (main_core.Type.isStringFilled(entity.id) && main_core.Type.isPlainObject(entity.options)) {
	              this.defaultOptions[entity.id] = JSON.parse(JSON.stringify(entity.options)); // clone
	            }
	          });
	        });
	      }

	      return this.defaultOptions;
	    }
	  }, {
	    key: "getExtensions",
	    value: function getExtensions() {
	      if (this.extensions === null) {
	        const settings = main_core.Extension.getSettings('ui.entity-selector');
	        this.extensions = settings.get('extensions', []);
	      }
	      return this.extensions;
	    }
	  }, {
	    key: "getEntityDefaultOptions",
	    value: function getEntityDefaultOptions(entityId) {
	      return this.getDefaultOptions()[entityId] || null;
	    }
	  }, {
	    key: "getItemOptions",
	    value: function getItemOptions(entityId, entityType) {
	      if (!main_core.Type.isStringFilled(entityId)) {
	        return null;
	      }
	      const options = this.getEntityDefaultOptions(entityId);
	      const itemOptions = options && options['itemOptions'] ? options['itemOptions'] : null;
	      if (main_core.Type.isUndefined(entityType)) {
	        return itemOptions;
	      } else {
	        return itemOptions && !main_core.Type.isUndefined(itemOptions[entityType]) ? itemOptions[entityType] : null;
	      }
	    }
	  }, {
	    key: "getTagOptions",
	    value: function getTagOptions(entityId, entityType) {
	      if (!main_core.Type.isStringFilled(entityId)) {
	        return null;
	      }
	      const options = this.getEntityDefaultOptions(entityId);
	      const tagOptions = options && options['tagOptions'] ? options['tagOptions'] : null;
	      if (main_core.Type.isUndefined(entityType)) {
	        return tagOptions;
	      } else {
	        return tagOptions && !main_core.Type.isUndefined(tagOptions[entityType]) ? tagOptions[entityType] : null;
	      }
	    }
	  }, {
	    key: "getItemOption",
	    value: function getItemOption(entityId, option, entityType) {
	      return this.getOptionInternal(this.getItemOptions(entityId), option, entityType);
	    }
	  }, {
	    key: "getTagOption",
	    value: function getTagOption(entityId, option, entityType) {
	      return this.getOptionInternal(this.getTagOptions(entityId), option, entityType);
	    }
	  }, {
	    key: "getOptionInternal",
	    value: function getOptionInternal(options, option, type) {
	      if (!main_core.Type.isPlainObject(options)) {
	        return null;
	      }
	      if (options[type] && !main_core.Type.isUndefined(options[type][option])) {
	        return options[type][option];
	      } else if (options['default'] && !main_core.Type.isUndefined(options['default'][option])) {
	        return options['default'][option];
	      }
	      return null;
	    }
	  }]);
	  return Entity;
	}();
	babelHelpers.defineProperty(Entity, "extensions", null);
	babelHelpers.defineProperty(Entity, "defaultOptions", null);

	let TypeUtils = /*#__PURE__*/function () {
	  function TypeUtils() {
	    babelHelpers.classCallCheck(this, TypeUtils);
	  }
	  babelHelpers.createClass(TypeUtils, null, [{
	    key: "createMapFromOptions",
	    value: function createMapFromOptions(options) {
	      if (main_core.Type.isPlainObject(options)) {
	        return new Map(Object.entries(options));
	      }
	      const map = new Map();
	      if (main_core.Type.isArrayFilled(options)) {
	        options.forEach(element => {
	          if (main_core.Type.isArray(element) && element.length === 2 && main_core.Type.isString(element[0])) {
	            map.set(element[0], element[1]);
	          }
	        });
	      }
	      return map;
	    }
	  }, {
	    key: "convertMapToObject",
	    value: function convertMapToObject(map) {
	      const obj = {};
	      if (main_core.Type.isMap(map)) {
	        map.forEach((value, key) => {
	          if (main_core.Type.isString(key)) {
	            obj[key] = value;
	          }
	        });
	      }
	      return obj;
	    }
	  }]);
	  return TypeUtils;
	}();

	function _classPrivateMethodInitSpec$1(obj, privateSet) { _checkPrivateRedeclaration$1(obj, privateSet); privateSet.add(obj); }
	function _checkPrivateRedeclaration$1(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$1(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _renderNodes = /*#__PURE__*/new WeakSet();
	/**
	 * @memberof BX.UI.EntitySelector
	 * @package ui.entity-selector
	 */
	let Item = /*#__PURE__*/function () {
	  function Item(itemOptions) {
	    babelHelpers.classCallCheck(this, Item);
	    _classPrivateMethodInitSpec$1(this, _renderNodes);
	    babelHelpers.defineProperty(this, "id", null);
	    babelHelpers.defineProperty(this, "entityId", null);
	    babelHelpers.defineProperty(this, "entityType", null);
	    babelHelpers.defineProperty(this, "title", null);
	    babelHelpers.defineProperty(this, "subtitle", null);
	    babelHelpers.defineProperty(this, "supertitle", null);
	    babelHelpers.defineProperty(this, "caption", null);
	    babelHelpers.defineProperty(this, "captionOptions", {});
	    babelHelpers.defineProperty(this, "avatar", null);
	    babelHelpers.defineProperty(this, "avatarOptions", null);
	    babelHelpers.defineProperty(this, "textColor", null);
	    babelHelpers.defineProperty(this, "link", null);
	    babelHelpers.defineProperty(this, "linkTitle", null);
	    babelHelpers.defineProperty(this, "tagOptions", null);
	    babelHelpers.defineProperty(this, "badges", null);
	    babelHelpers.defineProperty(this, "badgesOptions", {});
	    babelHelpers.defineProperty(this, "dialog", null);
	    babelHelpers.defineProperty(this, "nodes", new Set());
	    babelHelpers.defineProperty(this, "selected", false);
	    babelHelpers.defineProperty(this, "searchable", true);
	    babelHelpers.defineProperty(this, "saveable", true);
	    babelHelpers.defineProperty(this, "deselectable", true);
	    babelHelpers.defineProperty(this, "hidden", false);
	    babelHelpers.defineProperty(this, "searchIndex", null);
	    babelHelpers.defineProperty(this, "customData", null);
	    babelHelpers.defineProperty(this, "sort", null);
	    babelHelpers.defineProperty(this, "contextSort", null);
	    babelHelpers.defineProperty(this, "globalSort", null);
	    const options = main_core.Type.isPlainObject(itemOptions) ? itemOptions : {};
	    if (!main_core.Type.isStringFilled(options.id) && !main_core.Type.isNumber(options.id)) {
	      throw new Error('EntitySelector.Item: "id" parameter is required.');
	    }
	    if (!main_core.Type.isStringFilled(options.entityId)) {
	      throw new Error('EntitySelector.Item: "entityId" parameter is required.');
	    }
	    this.id = options.id;
	    this.entityId = options.entityId.toLowerCase();
	    this.entityType = main_core.Type.isStringFilled(options.entityType) ? options.entityType : 'default';
	    this.selected = main_core.Type.isBoolean(options.selected) ? options.selected : false;
	    this.customData = TypeUtils.createMapFromOptions(options.customData);
	    this.tagOptions = TypeUtils.createMapFromOptions(options.tagOptions);
	    this.setTitle(options.title);
	    this.setSubtitle(options.subtitle);
	    this.setSupertitle(options.supertitle);
	    this.setCaption(options.caption);
	    this.setCaptionOptions(options.captionOptions);
	    this.setAvatar(options.avatar);
	    this.setAvatarOptions(options.avatarOptions);
	    this.setTextColor(options.textColor);
	    this.setLink(options.link);
	    this.setLinkTitle(options.linkTitle);
	    this.setBadges(options.badges);
	    this.setBadgesOptions(options.badgesOptions);
	    this.setSearchable(options.searchable);
	    this.setSaveable(options.saveable);
	    this.setDeselectable(options.deselectable);
	    this.setHidden(options.hidden);
	    this.setContextSort(options.contextSort);
	    this.setGlobalSort(options.globalSort);
	    this.setSort(options.sort);
	  }
	  babelHelpers.createClass(Item, [{
	    key: "getId",
	    value: function getId() {
	      return this.id;
	    }
	  }, {
	    key: "getEntityId",
	    value: function getEntityId() {
	      return this.entityId;
	    }
	  }, {
	    key: "getEntity",
	    value: function getEntity() {
	      let entity = this.getDialog().getEntity(this.getEntityId());
	      if (entity === null) {
	        entity = new Entity({
	          id: this.getEntityId()
	        });
	        this.getDialog().addEntity(entity);
	      }
	      return entity;
	    }
	  }, {
	    key: "getEntityType",
	    value: function getEntityType() {
	      return this.entityType;
	    }
	  }, {
	    key: "getTitle",
	    value: function getTitle() {
	      const titleNode = this.getTitleNode();
	      return titleNode !== null && !titleNode.isNullable() ? titleNode.getText() : '';
	    }
	  }, {
	    key: "getTitleNode",
	    value: function getTitleNode() {
	      return this.title;
	    }
	  }, {
	    key: "setTitle",
	    value: function setTitle(title) {
	      if (main_core.Type.isStringFilled(title) || main_core.Type.isPlainObject(title) || title === null) {
	        this.title = title === null ? null : new TextNode(title);
	        this.resetSearchIndex();
	        _classPrivateMethodGet$1(this, _renderNodes, _renderNodes2).call(this);
	      }
	    }
	  }, {
	    key: "getSubtitle",
	    value: function getSubtitle() {
	      const subtitleNode = this.getSubtitleNode();
	      return subtitleNode !== null ? subtitleNode.getText() : null;
	    }
	  }, {
	    key: "getSubtitleNode",
	    value: function getSubtitleNode() {
	      return this.subtitle !== null ? this.subtitle : this.getEntityTextNode('subtitle');
	    }
	  }, {
	    key: "setSubtitle",
	    value: function setSubtitle(subtitle) {
	      if (main_core.Type.isString(subtitle) || main_core.Type.isPlainObject(subtitle) || subtitle === null) {
	        this.subtitle = subtitle === null ? null : new TextNode(subtitle);
	        this.resetSearchIndex();
	        _classPrivateMethodGet$1(this, _renderNodes, _renderNodes2).call(this);
	      }
	    }
	  }, {
	    key: "getSupertitle",
	    value: function getSupertitle() {
	      const supertitleNode = this.getSupertitleNode();
	      return supertitleNode !== null ? supertitleNode.getText() : null;
	    }
	  }, {
	    key: "getSupertitleNode",
	    value: function getSupertitleNode() {
	      return this.supertitle !== null ? this.supertitle : this.getEntityTextNode('supertitle');
	    }
	  }, {
	    key: "setSupertitle",
	    value: function setSupertitle(supertitle) {
	      if (main_core.Type.isString(supertitle) || main_core.Type.isPlainObject(supertitle) || supertitle === null) {
	        this.supertitle = supertitle === null ? null : new TextNode(supertitle);
	        this.resetSearchIndex();
	        _classPrivateMethodGet$1(this, _renderNodes, _renderNodes2).call(this);
	      }
	    }
	  }, {
	    key: "getCaption",
	    value: function getCaption() {
	      const captionNode = this.getCaptionNode();
	      return captionNode !== null ? captionNode.getText() : null;
	    }
	  }, {
	    key: "getCaptionNode",
	    value: function getCaptionNode() {
	      return this.caption !== null ? this.caption : this.getEntityTextNode('caption');
	    }
	  }, {
	    key: "setCaption",
	    value: function setCaption(caption) {
	      if (main_core.Type.isString(caption) || main_core.Type.isPlainObject(caption) || caption === null) {
	        this.caption = caption === null ? null : new TextNode(caption);
	        this.resetSearchIndex();
	        _classPrivateMethodGet$1(this, _renderNodes, _renderNodes2).call(this);
	      }
	    }
	  }, {
	    key: "getCaptionOption",
	    value: function getCaptionOption(option) {
	      if (!main_core.Type.isUndefined(this.captionOptions[option])) {
	        return this.captionOptions[option];
	      }
	      const captionOptions = this.getEntityItemOption('captionOptions');
	      if (main_core.Type.isPlainObject(captionOptions) && !main_core.Type.isUndefined(captionOptions[option])) {
	        return captionOptions[option];
	      }
	      return null;
	    }
	  }, {
	    key: "setCaptionOption",
	    value: function setCaptionOption(option, value) {
	      if (main_core.Type.isStringFilled(option) && !main_core.Type.isUndefined(value)) {
	        this.captionOptions[option] = value;
	        _classPrivateMethodGet$1(this, _renderNodes, _renderNodes2).call(this);
	      }
	    }
	  }, {
	    key: "setCaptionOptions",
	    value: function setCaptionOptions(options) {
	      if (main_core.Type.isPlainObject(options)) {
	        Object.keys(options).forEach(option => {
	          this.setCaptionOption(option, options[option]);
	        });
	      }
	    }
	  }, {
	    key: "getAvatar",
	    value: function getAvatar() {
	      return this.avatar !== null ? this.avatar : this.getEntityItemOption('avatar');
	    }
	  }, {
	    key: "setAvatar",
	    value: function setAvatar(avatar) {
	      if (main_core.Type.isString(avatar) || avatar === null) {
	        this.avatar = avatar;
	        _classPrivateMethodGet$1(this, _renderNodes, _renderNodes2).call(this);
	      }
	    }
	  }, {
	    key: "getAvatarOption",
	    value: function getAvatarOption(option) {
	      if (this.avatarOptions !== null && !main_core.Type.isUndefined(this.avatarOptions[option])) {
	        return this.avatarOptions[option];
	      }
	      const avatarOptions = this.getEntityItemOption('avatarOptions');
	      if (main_core.Type.isPlainObject(avatarOptions) && !main_core.Type.isUndefined(avatarOptions[option])) {
	        return avatarOptions[option];
	      }
	      return null;
	    }
	  }, {
	    key: "setAvatarOption",
	    value: function setAvatarOption(option, value) {
	      if (main_core.Type.isStringFilled(option) && !main_core.Type.isUndefined(value)) {
	        if (this.avatarOptions === null) {
	          this.avatarOptions = {};
	        }
	        this.avatarOptions[option] = value;
	        _classPrivateMethodGet$1(this, _renderNodes, _renderNodes2).call(this);
	      }
	    }
	  }, {
	    key: "setAvatarOptions",
	    value: function setAvatarOptions(options) {
	      if (main_core.Type.isPlainObject(options)) {
	        Object.keys(options).forEach(option => {
	          this.setAvatarOption(option, options[option]);
	        });
	      }
	    }
	  }, {
	    key: "getTextColor",
	    value: function getTextColor() {
	      return this.textColor !== null ? this.textColor : this.getEntityItemOption('textColor');
	    }
	  }, {
	    key: "setTextColor",
	    value: function setTextColor(textColor) {
	      if (main_core.Type.isString(textColor) || textColor === null) {
	        this.textColor = textColor;
	        _classPrivateMethodGet$1(this, _renderNodes, _renderNodes2).call(this);
	      }
	    }
	  }, {
	    key: "getLink",
	    value: function getLink() {
	      const link = this.link !== null ? this.link : this.getEntityItemOption('link');
	      return this.replaceMacros(link);
	    }
	  }, {
	    key: "setLink",
	    value: function setLink(link) {
	      if (main_core.Type.isString(link) || link === null) {
	        this.link = link;
	        _classPrivateMethodGet$1(this, _renderNodes, _renderNodes2).call(this);
	      }
	    }
	  }, {
	    key: "getLinkTitle",
	    value: function getLinkTitle() {
	      const linkTitleNode = this.getLinkTitleNode();
	      return linkTitleNode !== null ? linkTitleNode.getText() : main_core.Loc.getMessage('UI_SELECTOR_ITEM_LINK_TITLE');
	    }
	  }, {
	    key: "getLinkTitleNode",
	    value: function getLinkTitleNode() {
	      return this.linkTitle !== null ? this.linkTitle : this.getEntityTextNode('linkTitle');
	    }
	  }, {
	    key: "setLinkTitle",
	    value: function setLinkTitle(linkTitle) {
	      if (main_core.Type.isString(linkTitle) || main_core.Type.isPlainObject(linkTitle) || linkTitle === null) {
	        this.linkTitle = linkTitle === null ? null : new TextNode(linkTitle);
	        _classPrivateMethodGet$1(this, _renderNodes, _renderNodes2).call(this);
	      }
	    }
	  }, {
	    key: "getBadges",
	    value: function getBadges() {
	      if (this.badges !== null) {
	        return this.badges;
	      }
	      const badges = this.getEntity().getBadges(this);
	      if (main_core.Type.isArray(badges)) {
	        this.setBadges(badges);
	      } else {
	        this.badges = [];
	      }
	      return this.badges;
	    }
	  }, {
	    key: "setBadges",
	    value: function setBadges(badges) {
	      if (main_core.Type.isArray(badges)) {
	        this.badges = [];
	        badges.forEach(badge => {
	          this.badges.push(new ItemBadge(badge));
	        });
	        _classPrivateMethodGet$1(this, _renderNodes, _renderNodes2).call(this);
	      } else if (badges === null) {
	        this.badges = null;
	        _classPrivateMethodGet$1(this, _renderNodes, _renderNodes2).call(this);
	      }
	    }
	  }, {
	    key: "getBadgesOption",
	    value: function getBadgesOption(option) {
	      if (!main_core.Type.isUndefined(this.badgesOptions[option])) {
	        return this.badgesOptions[option];
	      }
	      const badgesOptions = this.getEntityItemOption('badgesOptions');
	      if (main_core.Type.isPlainObject(badgesOptions) && !main_core.Type.isUndefined(badgesOptions[option])) {
	        return badgesOptions[option];
	      }
	      return null;
	    }
	  }, {
	    key: "setBadgesOption",
	    value: function setBadgesOption(option, value) {
	      if (main_core.Type.isStringFilled(option) && !main_core.Type.isUndefined(value)) {
	        this.badgesOptions[option] = value;
	        _classPrivateMethodGet$1(this, _renderNodes, _renderNodes2).call(this);
	      }
	    }
	  }, {
	    key: "setBadgesOptions",
	    value: function setBadgesOptions(options) {
	      if (main_core.Type.isPlainObject(options)) {
	        Object.keys(options).forEach(option => {
	          this.setBadgesOption(option, options[option]);
	        });
	      }
	    }
	    /**
	     * @internal
	     */
	  }, {
	    key: "setDialog",
	    value: function setDialog(dialog) {
	      this.dialog = dialog;
	    }
	  }, {
	    key: "getDialog",
	    value: function getDialog() {
	      return this.dialog;
	    }
	  }, {
	    key: "createNode",
	    value: function createNode(nodeOptions) {
	      const itemNode = new ItemNode(this, nodeOptions);
	      this.nodes.add(itemNode);
	      return itemNode;
	    }
	  }, {
	    key: "removeNode",
	    value: function removeNode(node) {
	      this.nodes.delete(node);
	    }
	  }, {
	    key: "getNodes",
	    value: function getNodes() {
	      return this.nodes;
	    }
	  }, {
	    key: "select",
	    value: function select(preselectedMode = false) {
	      if (this.selected) {
	        return;
	      }
	      const dialog = this.getDialog();
	      const emitEvents = dialog && !preselectedMode;
	      if (emitEvents) {
	        const event = new main_core_events.BaseEvent({
	          data: {
	            item: this
	          }
	        });
	        dialog.emit('Item:onBeforeSelect', event);
	        if (event.isDefaultPrevented()) {
	          return;
	        }
	      }
	      this.selected = true;
	      if (dialog) {
	        dialog.handleItemSelect(this, !preselectedMode);
	      }
	      if (this.isRendered()) {
	        this.getNodes().forEach(node => {
	          node.select();
	        });
	      }
	      if (emitEvents) {
	        dialog.emit('Item:onSelect', {
	          item: this
	        });
	        dialog.saveRecentItem(this);
	      }
	    }
	  }, {
	    key: "deselect",
	    value: function deselect() {
	      if (!this.selected) {
	        return;
	      }
	      const dialog = this.getDialog();
	      if (dialog) {
	        const event = new main_core_events.BaseEvent({
	          data: {
	            item: this
	          }
	        });
	        dialog.emit('Item:onBeforeDeselect', event);
	        if (event.isDefaultPrevented()) {
	          return;
	        }
	      }
	      this.selected = false;
	      if (this.isRendered()) {
	        this.getNodes().forEach(node => {
	          node.deselect();
	        });
	      }
	      if (dialog) {
	        dialog.handleItemDeselect(this);
	        dialog.emit('Item:onDeselect', {
	          item: this
	        });
	      }
	    }
	  }, {
	    key: "isSelected",
	    value: function isSelected() {
	      return this.selected;
	    }
	  }, {
	    key: "setSearchable",
	    value: function setSearchable(flag) {
	      if (main_core.Type.isBoolean(flag)) {
	        this.searchable = flag;
	      }
	    }
	  }, {
	    key: "isSearchable",
	    value: function isSearchable() {
	      return this.searchable;
	    }
	  }, {
	    key: "setSaveable",
	    value: function setSaveable(flag) {
	      if (main_core.Type.isBoolean(flag)) {
	        this.saveable = flag;
	      }
	    }
	  }, {
	    key: "isSaveable",
	    value: function isSaveable() {
	      return this.saveable;
	    }
	  }, {
	    key: "setDeselectable",
	    value: function setDeselectable(flag) {
	      if (main_core.Type.isBoolean(flag)) {
	        this.deselectable = flag;
	        if (this.getDialog() && this.getDialog().getTagSelector()) {
	          const tag = this.getDialog().getTagSelector().getTag({
	            id: this.getId(),
	            entityId: this.getEntityId()
	          });
	          if (tag) {
	            tag.setDeselectable(flag);
	          }
	        }
	      }
	    }
	  }, {
	    key: "isDeselectable",
	    value: function isDeselectable() {
	      return this.deselectable;
	    }
	  }, {
	    key: "setHidden",
	    value: function setHidden(flag) {
	      if (main_core.Type.isBoolean(flag)) {
	        this.hidden = flag;
	        if (this.isRendered()) {
	          this.getNodes().forEach(node => {
	            node.setHidden(flag);
	          });
	        }
	      }
	    }
	  }, {
	    key: "isHidden",
	    value: function isHidden() {
	      return this.hidden;
	    }
	  }, {
	    key: "setContextSort",
	    value: function setContextSort(sort) {
	      if (main_core.Type.isNumber(sort) || sort === null) {
	        this.contextSort = sort;
	      }
	    }
	  }, {
	    key: "getContextSort",
	    value: function getContextSort() {
	      return this.contextSort;
	    }
	  }, {
	    key: "setGlobalSort",
	    value: function setGlobalSort(sort) {
	      if (main_core.Type.isNumber(sort) || sort === null) {
	        this.globalSort = sort;
	      }
	    }
	  }, {
	    key: "getGlobalSort",
	    value: function getGlobalSort() {
	      return this.globalSort;
	    }
	  }, {
	    key: "setSort",
	    value: function setSort(sort) {
	      if (main_core.Type.isNumber(sort) || sort === null) {
	        this.sort = sort;
	      }
	    }
	  }, {
	    key: "getSort",
	    value: function getSort() {
	      return this.sort;
	    }
	  }, {
	    key: "getSearchIndex",
	    value: function getSearchIndex() {
	      if (this.searchIndex === null) {
	        this.searchIndex = SearchIndex.create(this);
	      }
	      return this.searchIndex;
	    }
	  }, {
	    key: "resetSearchIndex",
	    value: function resetSearchIndex() {
	      this.searchIndex = null;
	    }
	  }, {
	    key: "getCustomData",
	    value: function getCustomData() {
	      return this.customData;
	    }
	  }, {
	    key: "isRendered",
	    value: function isRendered() {
	      return this.getDialog() && this.getDialog().isRendered();
	    }
	  }, {
	    key: "getEntityItemOption",
	    value: function getEntityItemOption(option) {
	      return this.getEntity().getItemOption(option, this.getEntityType());
	    }
	  }, {
	    key: "getEntityTagOption",
	    value: function getEntityTagOption(option) {
	      return this.getEntity().getTagOption(option, this.getEntityType());
	    }
	  }, {
	    key: "getEntityTextNode",
	    value: function getEntityTextNode(option) {
	      return this.getEntity().getOptionTextNode(option, this.getEntityType());
	    }
	  }, {
	    key: "getTagOptions",
	    value: function getTagOptions() {
	      return this.tagOptions;
	    }
	  }, {
	    key: "getTagOption",
	    value: function getTagOption(option) {
	      const value = this.getTagOptions().get(option);
	      if (!main_core.Type.isUndefined(value)) {
	        return value;
	      }
	      return null;
	    }
	  }, {
	    key: "getTagGlobalOption",
	    value: function getTagGlobalOption(option, useItemOptions = false) {
	      if (!main_core.Type.isStringFilled(option)) {
	        return null;
	      }
	      let value = this.getTagOption(option);
	      if (value === null && useItemOptions === true && this[option] !== null) {
	        value = this[option];
	      }
	      if (value === null && this.getDialog().getTagSelector()) {
	        const fn = `getTag${main_core.Text.toPascalCase(option)}`;
	        if (main_core.Type.isFunction(this.getDialog().getTagSelector()[fn])) {
	          value = this.getDialog().getTagSelector()[fn]();
	        }
	      }
	      if (value === null) {
	        value = this.getEntityTagOption(option);
	      }
	      if (value === null && useItemOptions === true) {
	        value = this.getEntityItemOption(option);
	      }
	      return value;
	    }
	  }, {
	    key: "getTagBgColor",
	    value: function getTagBgColor() {
	      return this.getTagGlobalOption('bgColor');
	    }
	  }, {
	    key: "getTagTextColor",
	    value: function getTagTextColor() {
	      return this.getTagGlobalOption('textColor');
	    }
	  }, {
	    key: "getTagMaxWidth",
	    value: function getTagMaxWidth() {
	      return this.getTagGlobalOption('maxWidth');
	    }
	  }, {
	    key: "getTagFontWeight",
	    value: function getTagFontWeight() {
	      return this.getTagGlobalOption('fontWeight');
	    }
	  }, {
	    key: "getTagAvatar",
	    value: function getTagAvatar() {
	      return this.getTagGlobalOption('avatar', true);
	    }
	  }, {
	    key: "getTagAvatarOptions",
	    value: function getTagAvatarOptions() {
	      return this.getTagGlobalOption('avatarOptions', true);
	    }
	  }, {
	    key: "getTagLink",
	    value: function getTagLink() {
	      return this.replaceMacros(this.getTagGlobalOption('link', true));
	    }
	    /**
	     * @internal
	     */
	  }, {
	    key: "replaceMacros",
	    value: function replaceMacros(str) {
	      if (!main_core.Type.isStringFilled(str)) {
	        return str;
	      }
	      return str.replace(/#id#/i, this.getId()).replace(/#element_id#/i, this.getId());
	    }
	    /**
	     * @internal
	     */
	  }, {
	    key: "createTag",
	    value: function createTag() {
	      return {
	        id: this.getId(),
	        entityId: this.getEntityId(),
	        entityType: this.getEntityType(),
	        title: this.getTagOption('title') || this.getTitleNode() && this.getTitleNode().toJSON() || '',
	        deselectable: this.isDeselectable(),
	        avatar: this.getTagAvatar(),
	        avatarOptions: this.getTagAvatarOptions(),
	        link: this.getTagLink(),
	        maxWidth: this.getTagMaxWidth(),
	        textColor: this.getTagTextColor(),
	        bgColor: this.getTagBgColor(),
	        fontWeight: this.getTagFontWeight(),
	        onclick: this.getTagOption('onclick')
	      };
	    }
	  }, {
	    key: "getAjaxJson",
	    value: function getAjaxJson() {
	      return this.toJSON();
	    }
	  }, {
	    key: "toJSON",
	    value: function toJSON() {
	      return {
	        id: this.getId(),
	        entityId: this.getEntityId(),
	        entityType: this.getEntityType(),
	        selected: this.isSelected(),
	        deselectable: this.isDeselectable(),
	        searchable: this.isSearchable(),
	        saveable: this.isSaveable(),
	        hidden: this.isHidden(),
	        title: this.getTitleNode(),
	        link: this.getLink(),
	        linkTitle: this.getLinkTitleNode(),
	        subtitle: this.getSubtitleNode(),
	        supertitle: this.getSupertitleNode(),
	        caption: this.getCaptionNode(),
	        avatar: this.getAvatar(),
	        textColor: this.getTextColor(),
	        sort: this.getSort(),
	        contextSort: this.getContextSort(),
	        globalSort: this.getGlobalSort(),
	        customData: TypeUtils.convertMapToObject(this.getCustomData()),
	        tagOptions: TypeUtils.convertMapToObject(this.getTagOptions()),
	        badges: this.getBadges()
	      };
	    }
	  }]);
	  return Item;
	}();
	function _renderNodes2() {
	  if (this.isRendered()) {
	    this.getNodes().forEach(node => {
	      node.render();
	    });
	  }
	}

	let _ = t => t,
	  _t;
	let BaseStub = /*#__PURE__*/function () {
	  function BaseStub(tab, options) {
	    babelHelpers.classCallCheck(this, BaseStub);
	    babelHelpers.defineProperty(this, "tab", null);
	    babelHelpers.defineProperty(this, "autoShow", true);
	    babelHelpers.defineProperty(this, "cache", new main_core.Cache.MemoryCache());
	    babelHelpers.defineProperty(this, "content", null);
	    this.options = main_core.Type.isPlainObject(options) ? options : {};
	    this.tab = tab;
	    this.autoShow = this.getOption('autoShow', true);
	  }

	  /**
	   * @abstract
	   */
	  babelHelpers.createClass(BaseStub, [{
	    key: "render",
	    value: function render() {
	      throw new Error('You must implement render() method.');
	    }
	  }, {
	    key: "getTab",
	    value: function getTab() {
	      return this.tab;
	    }
	  }, {
	    key: "getOuterContainer",
	    value: function getOuterContainer() {
	      return this.cache.remember('outer-container', () => {
	        return main_core.Tag.render(_t || (_t = _`
				<div class="ui-selector-tab-stub">${0}</div>
			`), this.render());
	      });
	    }
	  }, {
	    key: "isAutoShow",
	    value: function isAutoShow() {
	      return this.autoShow;
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      main_core.Dom.append(this.getOuterContainer(), this.getTab().getContainer());
	      /*requestAnimationFrame(() => {
	      	Dom.addClass(this.getOuterContainer(), 'ui-selector-tab-stub--show');
	      });*/
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      // Dom.removeClass(this.getOuterContainer(), 'ui-selector-tab-stub--show');
	      main_core.Dom.remove(this.getOuterContainer());
	    }
	  }, {
	    key: "getOptions",
	    value: function getOptions() {
	      return this.options;
	    }
	  }, {
	    key: "getOption",
	    value: function getOption(option, defaultValue) {
	      if (!main_core.Type.isUndefined(this.options[option])) {
	        return this.options[option];
	      } else if (!main_core.Type.isUndefined(defaultValue)) {
	        return defaultValue;
	      }
	      return null;
	    }
	  }]);
	  return BaseStub;
	}();

	let _$1 = t => t,
	  _t$1,
	  _t2,
	  _t3,
	  _t4;
	let DefaultStub = /*#__PURE__*/function (_BaseStub) {
	  babelHelpers.inherits(DefaultStub, _BaseStub);
	  function DefaultStub(tab, options) {
	    var _this;
	    babelHelpers.classCallCheck(this, DefaultStub);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(DefaultStub).call(this, tab, options));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "content", null);
	    return _this;
	  }
	  babelHelpers.createClass(DefaultStub, [{
	    key: "getContainer",
	    value: function getContainer() {
	      return this.cache.remember('container', () => {
	        const subtitle = this.getOption('subtitle');
	        const title = main_core.Type.isStringFilled(this.getOption('title')) ? this.getOption('title') : this.getDefaultTitle();
	        const icon = this.getOption('icon') || this.getTab().getIcon('default');
	        let iconOpacity = 35;
	        if (main_core.Type.isNumber(this.getOption('iconOpacity'))) {
	          iconOpacity = Math.min(100, Math.max(0, this.getOption('iconOpacity')));
	        }
	        const iconStyle = main_core.Type.isStringFilled(icon) ? `style="background-image: url('${encodeUrl(icon)}'); opacity: ${iconOpacity / 100};"` : '';
	        const arrow = this.getOption('arrow', false) && this.getTab().getDialog().getActiveFooter() !== null;
	        return main_core.Tag.render(_t$1 || (_t$1 = _$1`
				<div class="ui-selector-tab-default-stub">
					<div class="ui-selector-tab-default-stub-icon" ${0}></div>
					<div class="ui-selector-tab-default-stub-titles">
						<div class="ui-selector-tab-default-stub-title">${0}</div>
						${0}
					</div>
					
					${0}
				</div>
			`), iconStyle, title, subtitle ? main_core.Tag.render(_t2 || (_t2 = _$1`<div class="ui-selector-tab-default-stub-subtitle">${0}</div>`), subtitle) : '', arrow ? main_core.Tag.render(_t3 || (_t3 = _$1`<div class="ui-selector-tab-default-stub-arrow"></div>`)) : '');
	      });
	    }
	  }, {
	    key: "getDefaultTitle",
	    value: function getDefaultTitle() {
	      const titleNode = this.getTab().getTitleNode();
	      if (titleNode === null) {
	        return main_core.Loc.getMessage('UI_SELECTOR_TAB_STUB_TITLE').replace(/#TAB_TITLE#/, '');
	      }
	      const titleContainer = main_core.Tag.render(_t4 || (_t4 = _$1`<span class="ui-selector-tab-default-stub-title"></span>`));
	      titleNode.renderTo(titleContainer);
	      return main_core.Loc.getMessage('UI_SELECTOR_TAB_STUB_TITLE').replace(/#TAB_TITLE#/, titleContainer.innerHTML);
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      return this.getContainer();
	    }
	  }]);
	  return DefaultStub;
	}(BaseStub);

	let _$2 = t => t,
	  _t$2;
	let BaseHeader = /*#__PURE__*/function () {
	  function BaseHeader(context, options) {
	    babelHelpers.classCallCheck(this, BaseHeader);
	    babelHelpers.defineProperty(this, "dialog", null);
	    babelHelpers.defineProperty(this, "tab", null);
	    babelHelpers.defineProperty(this, "container", null);
	    babelHelpers.defineProperty(this, "cache", new main_core.Cache.MemoryCache());
	    this.options = main_core.Type.isPlainObject(options) ? options : {};
	    if (context instanceof Dialog) {
	      this.dialog = context;
	    } else {
	      this.tab = context;
	      this.dialog = this.tab.getDialog();
	    }
	  }
	  babelHelpers.createClass(BaseHeader, [{
	    key: "getDialog",
	    value: function getDialog() {
	      return this.dialog;
	    }
	  }, {
	    key: "getTab",
	    value: function getTab() {
	      return this.tab;
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      main_core.Dom.addClass(this.getContainer(), 'ui-selector-header--show');
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      main_core.Dom.removeClass(this.getContainer(), 'ui-selector-header--show');
	    }
	  }, {
	    key: "getOptions",
	    value: function getOptions() {
	      return this.options;
	    }
	  }, {
	    key: "getOption",
	    value: function getOption(option, defaultValue) {
	      if (!main_core.Type.isUndefined(this.options[option])) {
	        return this.options[option];
	      } else if (!main_core.Type.isUndefined(defaultValue)) {
	        return defaultValue;
	      }
	      return null;
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      if (this.container === null) {
	        this.container = main_core.Tag.render(_t$2 || (_t$2 = _$2`
				<div class="ui-selector-header">${0}</div>
			`), this.render());
	      }
	      return this.container;
	    }
	    /**
	     * @abstract
	     */
	  }, {
	    key: "render",
	    value: function render() {
	      throw new Error('You must implement render() method.');
	    }
	  }]);
	  return BaseHeader;
	}();

	let _$3 = t => t,
	  _t$3;
	let BaseFooter = /*#__PURE__*/function () {
	  function BaseFooter(context, options) {
	    babelHelpers.classCallCheck(this, BaseFooter);
	    babelHelpers.defineProperty(this, "dialog", null);
	    babelHelpers.defineProperty(this, "tab", null);
	    babelHelpers.defineProperty(this, "container", null);
	    babelHelpers.defineProperty(this, "cache", new main_core.Cache.MemoryCache());
	    this.options = main_core.Type.isPlainObject(options) ? options : {};
	    if (context instanceof Dialog) {
	      this.dialog = context;
	    } else {
	      this.tab = context;
	      this.dialog = this.tab.getDialog();
	    }
	  }
	  babelHelpers.createClass(BaseFooter, [{
	    key: "getDialog",
	    value: function getDialog() {
	      return this.dialog;
	    }
	  }, {
	    key: "getTab",
	    value: function getTab() {
	      return this.tab;
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      main_core.Dom.addClass(this.getContainer(), 'ui-selector-footer--show');
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      main_core.Dom.removeClass(this.getContainer(), 'ui-selector-footer--show');
	    }
	  }, {
	    key: "getOptions",
	    value: function getOptions() {
	      return this.options;
	    }
	  }, {
	    key: "getOption",
	    value: function getOption(option, defaultValue) {
	      if (!main_core.Type.isUndefined(this.options[option])) {
	        return this.options[option];
	      } else if (!main_core.Type.isUndefined(defaultValue)) {
	        return defaultValue;
	      }
	      return null;
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      if (this.container === null) {
	        this.container = main_core.Tag.render(_t$3 || (_t$3 = _$3`
				<div class="ui-selector-footer">${0}</div>
			`), this.render());
	      }
	      return this.container;
	    }
	    /**
	     * @abstract
	     */
	  }, {
	    key: "render",
	    value: function render() {
	      throw new Error('You must implement render() method.');
	    }
	  }]);
	  return BaseFooter;
	}();

	let _$4 = t => t,
	  _t$4,
	  _t2$1,
	  _t3$1,
	  _t4$1,
	  _t5;
	/**
	 * @memberof BX.UI.EntitySelector
	 */
	let Tab = /*#__PURE__*/function () {
	  function Tab(dialog, tabOptions) {
	    babelHelpers.classCallCheck(this, Tab);
	    babelHelpers.defineProperty(this, "id", null);
	    babelHelpers.defineProperty(this, "title", null);
	    babelHelpers.defineProperty(this, "rootNode", null);
	    babelHelpers.defineProperty(this, "dialog", null);
	    babelHelpers.defineProperty(this, "stub", null);
	    babelHelpers.defineProperty(this, "visible", true);
	    babelHelpers.defineProperty(this, "rendered", false);
	    babelHelpers.defineProperty(this, "locked", false);
	    babelHelpers.defineProperty(this, "selected", false);
	    babelHelpers.defineProperty(this, "hovered", false);
	    babelHelpers.defineProperty(this, "icon", {});
	    babelHelpers.defineProperty(this, "textColor", {});
	    babelHelpers.defineProperty(this, "bgColor", {});
	    babelHelpers.defineProperty(this, "itemMaxDepth", 5);
	    babelHelpers.defineProperty(this, "header", null);
	    babelHelpers.defineProperty(this, "showDefaultHeader", true);
	    babelHelpers.defineProperty(this, "footer", null);
	    babelHelpers.defineProperty(this, "showDefaultFooter", true);
	    babelHelpers.defineProperty(this, "showAvatars", null);
	    babelHelpers.defineProperty(this, "cache", new main_core.Cache.MemoryCache());
	    const options = main_core.Type.isPlainObject(tabOptions) ? tabOptions : {};
	    if (!main_core.Type.isStringFilled(options.id)) {
	      throw new Error('EntitySelector.Tab: "id" parameter is required.');
	    }
	    this.setDialog(dialog);
	    this.id = options.id;
	    this.showDefaultHeader = options.showDefaultHeader !== false;
	    this.showDefaultFooter = options.showDefaultFooter !== false;
	    this.rootNode = new ItemNode(null, {
	      itemOrder: options.itemOrder
	    });
	    this.rootNode.setTab(this);
	    this.setVisible(options.visible);
	    this.setTitle(options.title);
	    this.setItemMaxDepth(options.itemMaxDepth);
	    this.setIcon(options.icon);
	    this.setTextColor(options.textColor);
	    this.setBgColor(options.bgColor);
	    this.setStub(options.stub, options.stubOptions);
	    this.setHeader(options.header, options.headerOptions);
	    this.setFooter(options.footer, options.footerOptions);
	    this.setShowAvatars(options.showAvatars);
	  }
	  babelHelpers.createClass(Tab, [{
	    key: "getId",
	    value: function getId() {
	      return this.id;
	    }
	    /**
	     * @internal
	     */
	  }, {
	    key: "setDialog",
	    value: function setDialog(dialog) {
	      this.dialog = dialog;
	    }
	  }, {
	    key: "getDialog",
	    value: function getDialog() {
	      return this.dialog;
	    }
	  }, {
	    key: "getStub",
	    value: function getStub() {
	      return this.stub;
	    }
	  }, {
	    key: "setStub",
	    value: function setStub(stub, stubOptions) {
	      let instance = null;
	      const options = main_core.Type.isPlainObject(stubOptions) ? stubOptions : {};
	      if (main_core.Type.isString(stub) || main_core.Type.isFunction(stub)) {
	        const className = main_core.Type.isString(stub) ? main_core.Reflection.getClass(stub) : stub;
	        if (main_core.Type.isFunction(className)) {
	          instance = new className(this, options);
	          if (!(instance instanceof BaseStub)) {
	            console.error('EntitySelector: stub is not an instance of BaseStub.');
	            instance = null;
	          }
	        }
	      }
	      if (!instance && stub !== false) {
	        instance = new DefaultStub(this, options);
	      }
	      this.stub = instance;
	    }
	  }, {
	    key: "getHeader",
	    value: function getHeader() {
	      return this.header;
	    }
	  }, {
	    key: "setHeader",
	    value: function setHeader(headerContent, headerOptions) {
	      /** @var {BaseHeader} */
	      let header = null;
	      if (headerContent !== null) {
	        header = Dialog.createHeader(this, headerContent, headerOptions);
	        if (header === null) {
	          return;
	        }
	      }
	      if (this.isRendered() && this.getHeader() !== null) {
	        main_core.Dom.remove(this.getHeader().getContainer());
	        this.getDialog().adjustHeader();
	      }
	      this.header = header;
	      if (this.isRendered()) {
	        this.getDialog().appendHeader(header);
	        this.getDialog().adjustHeader();
	      }
	    }
	  }, {
	    key: "canShowDefaultHeader",
	    value: function canShowDefaultHeader() {
	      return this.showDefaultHeader;
	    }
	  }, {
	    key: "enableDefaultHeader",
	    value: function enableDefaultHeader() {
	      this.showDefaultHeader = true;
	      this.getDialog().adjustHeader();
	    }
	  }, {
	    key: "disableDefaultHeader",
	    value: function disableDefaultHeader() {
	      this.showDefaultHeader = false;
	      this.getDialog().adjustHeader();
	    }
	  }, {
	    key: "getFooter",
	    value: function getFooter() {
	      return this.footer;
	    }
	  }, {
	    key: "setFooter",
	    value: function setFooter(footerContent, footerOptions) {
	      /** @var {BaseFooter} */
	      let footer = null;
	      if (footerContent !== null) {
	        footer = Dialog.createFooter(this, footerContent, footerOptions);
	        if (footer === null) {
	          return;
	        }
	      }
	      if (this.isRendered() && this.getFooter() !== null) {
	        main_core.Dom.remove(this.getFooter().getContainer());
	        this.getDialog().adjustFooter();
	      }
	      this.footer = footer;
	      if (this.isRendered()) {
	        this.getDialog().appendFooter(footer);
	        this.getDialog().adjustFooter();
	      }
	    }
	  }, {
	    key: "canShowDefaultFooter",
	    value: function canShowDefaultFooter() {
	      return this.showDefaultFooter;
	    }
	  }, {
	    key: "enableDefaultFooter",
	    value: function enableDefaultFooter() {
	      this.showDefaultFooter = true;
	      this.getDialog().adjustFooter();
	    }
	  }, {
	    key: "disableDefaultFooter",
	    value: function disableDefaultFooter() {
	      this.showDefaultFooter = false;
	      this.getDialog().adjustFooter();
	    }
	  }, {
	    key: "setShowAvatars",
	    value: function setShowAvatars(flag) {
	      if (main_core.Type.isBoolean(flag) || flag === null) {
	        this.showAvatars = flag;
	        if (this.isRendered()) {
	          this.renderContainer();
	        }
	      }
	    }
	  }, {
	    key: "shouldShowAvatars",
	    value: function shouldShowAvatars() {
	      var _this$showAvatars;
	      return (_this$showAvatars = this.showAvatars) !== null && _this$showAvatars !== void 0 ? _this$showAvatars : this.getDialog().shouldShowAvatars();
	    }
	  }, {
	    key: "getRootNode",
	    value: function getRootNode() {
	      return this.rootNode;
	    }
	  }, {
	    key: "setTitle",
	    value: function setTitle(title) {
	      if (main_core.Type.isStringFilled(title) || main_core.Type.isPlainObject(title) || title === null) {
	        this.title = title === null ? null : new TextNode(title);
	        if (this.isRendered()) {
	          this.renderLabel();
	        }
	      }
	    }
	  }, {
	    key: "getTitle",
	    value: function getTitle() {
	      const titleNode = this.getTitleNode();
	      return titleNode !== null && !titleNode.isNullable() ? titleNode.getText() : '';
	    }
	  }, {
	    key: "getTitleNode",
	    value: function getTitleNode() {
	      return this.title;
	    }
	  }, {
	    key: "setIcon",
	    value: function setIcon(icon) {
	      return this.setProperty('icon', icon);
	    }
	  }, {
	    key: "getIcon",
	    value: function getIcon(state) {
	      return this.getPropertyByState('icon', state);
	    }
	  }, {
	    key: "setBgColor",
	    value: function setBgColor(bgColor) {
	      return this.setProperty('bgColor', bgColor);
	    }
	  }, {
	    key: "getBgColor",
	    value: function getBgColor(state) {
	      return this.getPropertyByState('bgColor', state);
	    }
	  }, {
	    key: "setTextColor",
	    value: function setTextColor(textColor) {
	      return this.setProperty('textColor', textColor);
	    }
	  }, {
	    key: "getTextColor",
	    value: function getTextColor(state) {
	      return this.getPropertyByState('textColor', state);
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "setProperty",
	    value: function setProperty(name, states) {
	      const property = this[name];
	      if (!property) {
	        return;
	      }
	      if (main_core.Type.isPlainObject(states)) {
	        Object.keys(states).forEach(state => {
	          if (main_core.Type.isStringFilled(states[state])) {
	            property[state] = states[state];
	          }
	        });
	      } else if (main_core.Type.isStringFilled(states)) {
	        property['default'] = states;
	      }
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "getPropertyByState",
	    value: function getPropertyByState(name, state) {
	      const property = this[name];
	      const labelState = main_core.Type.isStringFilled(state) ? state : 'default';
	      if (!main_core.Type.isUndefined(property) && !main_core.Type.isUndefined(property[labelState])) {
	        return property[labelState];
	      }
	      return null;
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "getPropertyByCurrentState",
	    value: function getPropertyByCurrentState(name) {
	      const property = this[name];
	      if (this.isSelected() && this.isHovered() && property.selectedHovered) {
	        return property.selectedHovered;
	      } else if (this.isSelected() && property.selected) {
	        return property.selected;
	      } else if (this.isHovered() && property.hovered) {
	        return property.hovered;
	      } else if (property.default) {
	        return property.default;
	      }
	      return null;
	    }
	  }, {
	    key: "setItemMaxDepth",
	    value: function setItemMaxDepth(depth) {
	      if (main_core.Type.isNumber(depth) && depth > 0) {
	        this.itemMaxDepth = depth;
	      }
	    }
	  }, {
	    key: "getItemMaxDepth",
	    value: function getItemMaxDepth() {
	      return this.itemMaxDepth;
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      return this.cache.remember('container', () => {
	        return main_core.Tag.render(_t$4 || (_t$4 = _$4`
				<div class="ui-selector-tab-content">${0}</div>
			`), this.getItemsContainer());
	      });
	    }
	  }, {
	    key: "getLabelContainer",
	    value: function getLabelContainer() {
	      return this.cache.remember('label', () => {
	        const className = this.isVisible() ? '' : ' ui-selector-tab-label-hidden';
	        return main_core.Tag.render(_t2$1 || (_t2$1 = _$4`
				<div
					class="ui-selector-tab-label${0}"
					onclick="${0}"
					onmouseenter="${0}"
					onmouseleave="${0}"
				>
					${0}
					${0}
				</div>
			`), className, this.handleLabelClick.bind(this), this.handleLabelMouseEnter.bind(this), this.handleLabelMouseLeave.bind(this), this.getIconContainer(), this.getTitleContainer());
	      });
	    }
	  }, {
	    key: "getIconContainer",
	    value: function getIconContainer() {
	      return this.cache.remember('icon', () => {
	        return main_core.Tag.render(_t3$1 || (_t3$1 = _$4`
				<div class="ui-selector-tab-icon"></div>
			`));
	      });
	    }
	  }, {
	    key: "getTitleContainer",
	    value: function getTitleContainer() {
	      return this.cache.remember('title', () => {
	        return main_core.Tag.render(_t4$1 || (_t4$1 = _$4`
				<div class="ui-selector-tab-title"></div>
			`));
	      });
	    }
	  }, {
	    key: "getItemsContainer",
	    value: function getItemsContainer() {
	      return this.cache.remember('items', () => {
	        return main_core.Tag.render(_t5 || (_t5 = _$4`
				<div class="ui-selector-items"></div>
			`));
	      });
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      this.getRootNode().render();
	      this.rendered = true;
	    } /** @internal **/
	  }, {
	    key: "renderLabel",
	    value: function renderLabel() {
	      main_core.Dom.style(this.getTitleContainer(), 'color', this.getPropertyByCurrentState('textColor'));
	      main_core.Dom.style(this.getLabelContainer(), 'background-color', this.getPropertyByCurrentState('bgColor'));
	      const icon = this.getPropertyByCurrentState('icon');
	      main_core.Dom.style(this.getIconContainer(), 'background-image', icon ? `url('${encodeUrl(icon)}')` : null);
	      const titleNode = this.getTitleNode();
	      if (titleNode) {
	        this.getTitleNode().renderTo(this.getTitleContainer());
	      } else {
	        this.getTitleContainer().textContent = '';
	      }
	    } /** @internal **/
	  }, {
	    key: "renderContainer",
	    value: function renderContainer() {
	      const className = 'ui-selector-tab-content--hide-avatars';
	      if (this.shouldShowAvatars()) {
	        main_core.Dom.removeClass(this.getContainer(), className);
	      } else {
	        main_core.Dom.addClass(this.getContainer(), className);
	      }
	    }
	  }, {
	    key: "isVisible",
	    value: function isVisible() {
	      return this.visible;
	    }
	  }, {
	    key: "setVisible",
	    value: function setVisible(flag) {
	      if (main_core.Type.isBoolean(flag)) {
	        this.visible = flag;
	        if (this.isRendered()) {
	          if (this.visible) {
	            main_core.Dom.remove(this.getLabelContainer(), 'ui-selector-tab-label-hidden');
	          } else {
	            main_core.Dom.addClass(this.getLabelContainer(), 'ui-selector-tab-label-hidden');
	          }
	        }
	      }
	    }
	  }, {
	    key: "isRendered",
	    value: function isRendered() {
	      return this.rendered && this.getDialog() && this.getDialog().isRendered();
	    }
	    /**
	     * @internal
	     */
	  }, {
	    key: "select",
	    value: function select() {
	      if (this.isSelected()) {
	        return;
	      }
	      main_core.Dom.addClass(this.getContainer(), 'ui-selector-tab-content-active');
	      if (this.isVisible()) {
	        main_core.Dom.addClass(this.getLabelContainer(), 'ui-selector-tab-label-active');
	        this.renderLabel();
	      }
	      this.selected = true;
	      if (this.isVisible()) {
	        this.renderLabel();
	      }
	      if (this.getHeader()) {
	        this.getHeader().show();
	      }
	      if (this.getFooter()) {
	        this.getFooter().show();
	      }
	      this.getDialog().emit('Tab:onSelect', {
	        tab: this
	      });
	    }
	    /**
	     * @internal
	     */
	  }, {
	    key: "deselect",
	    value: function deselect() {
	      if (!this.isSelected()) {
	        return;
	      }
	      main_core.Dom.removeClass(this.getContainer(), 'ui-selector-tab-content-active');
	      if (this.isVisible()) {
	        main_core.Dom.removeClass(this.getLabelContainer(), 'ui-selector-tab-label-active');
	      }
	      this.selected = false;
	      if (this.isVisible()) {
	        this.renderLabel();
	      }
	      if (this.getHeader()) {
	        this.getHeader().hide();
	      }
	      if (this.getFooter()) {
	        this.getFooter().hide();
	      }
	      this.getDialog().emit('Tab:onDeselect', {
	        tab: this
	      });
	    }
	  }, {
	    key: "hover",
	    value: function hover() {
	      if (this.isHovered()) {
	        return;
	      }
	      main_core.Dom.addClass(this.getLabelContainer(), 'ui-selector-tab-label-hover');
	      this.hovered = true;
	      this.renderLabel();
	    }
	  }, {
	    key: "unhover",
	    value: function unhover() {
	      if (!this.isHovered()) {
	        return;
	      }
	      main_core.Dom.removeClass(this.getLabelContainer(), 'ui-selector-tab-label-hover');
	      this.hovered = false;
	      this.renderLabel();
	    }
	  }, {
	    key: "isSelected",
	    value: function isSelected() {
	      return this.selected;
	    }
	  }, {
	    key: "isHovered",
	    value: function isHovered() {
	      return this.hovered;
	    }
	  }, {
	    key: "lock",
	    value: function lock() {
	      this.locked = true;
	      main_core.Dom.addClass(this.getContainer(), 'ui-selector-tab-content-locked');
	    }
	  }, {
	    key: "unlock",
	    value: function unlock() {
	      this.locked = false;
	      main_core.Dom.removeClass(this.getContainer(), 'ui-selector-tab-content-locked');
	    }
	  }, {
	    key: "isLocked",
	    value: function isLocked() {
	      return this.locked;
	    }
	  }, {
	    key: "handleLabelClick",
	    value: function handleLabelClick() {
	      this.getDialog().selectTab(this.getId());
	    }
	  }, {
	    key: "handleLabelMouseEnter",
	    value: function handleLabelMouseEnter() {
	      this.hover();
	    }
	  }, {
	    key: "handleLabelMouseLeave",
	    value: function handleLabelMouseLeave() {
	      this.unhover();
	    }
	  }]);
	  return Tab;
	}();

	let _$5 = t => t,
	  _t$5,
	  _t2$2,
	  _t3$2,
	  _t4$2,
	  _t5$1,
	  _t6;
	function _classStaticPrivateMethodGet$1(receiver, classConstructor, method) { _classCheckPrivateStaticAccess$1(receiver, classConstructor); return method; }
	function _classCheckPrivateStaticAccess$1(receiver, classConstructor) { if (receiver !== classConstructor) { throw new TypeError("Private static access of wrong provenance"); } }
	let TagItem = /*#__PURE__*/function () {
	  function TagItem(itemOptions) {
	    babelHelpers.classCallCheck(this, TagItem);
	    babelHelpers.defineProperty(this, "id", null);
	    babelHelpers.defineProperty(this, "entityId", null);
	    babelHelpers.defineProperty(this, "entityType", null);
	    babelHelpers.defineProperty(this, "title", null);
	    babelHelpers.defineProperty(this, "avatar", null);
	    babelHelpers.defineProperty(this, "avatarOptions", null);
	    babelHelpers.defineProperty(this, "maxWidth", null);
	    babelHelpers.defineProperty(this, "textColor", null);
	    babelHelpers.defineProperty(this, "bgColor", null);
	    babelHelpers.defineProperty(this, "fontWeight", null);
	    babelHelpers.defineProperty(this, "link", null);
	    babelHelpers.defineProperty(this, "onclick", null);
	    babelHelpers.defineProperty(this, "clickable", null);
	    babelHelpers.defineProperty(this, "deselectable", null);
	    babelHelpers.defineProperty(this, "customData", null);
	    babelHelpers.defineProperty(this, "cache", new main_core.Cache.MemoryCache());
	    babelHelpers.defineProperty(this, "selector", null);
	    babelHelpers.defineProperty(this, "rendered", false);
	    const options = main_core.Type.isPlainObject(itemOptions) ? itemOptions : {};
	    if (!main_core.Type.isStringFilled(options.id) && !main_core.Type.isNumber(options.id)) {
	      throw new Error('TagSelector.TagItem: "id" parameter is required.');
	    }
	    if (!main_core.Type.isStringFilled(options.entityId)) {
	      throw new Error('TagSelector.TagItem: "entityId" parameter is required.');
	    }
	    this.id = options.id;
	    this.entityId = options.entityId.toLowerCase();
	    this.entityType = main_core.Type.isStringFilled(options.entityType) ? options.entityType : 'default';
	    this.customData = TypeUtils.createMapFromOptions(options.customData);
	    this.onclick = main_core.Type.isFunction(options.onclick) ? options.onclick : null;
	    this.link = main_core.Type.isStringFilled(options.link) ? options.link : null;
	    this.setTitle(options.title);
	    this.setDeselectable(options.deselectable);
	    this.setAvatar(options.avatar);
	    this.setAvatarOptions(options.avatarOptions);
	    this.setMaxWidth(options.maxWidth);
	    this.setTextColor(options.textColor);
	    this.setBgColor(options.bgColor);
	    this.setFontWeight(options.fontWeight);
	    this.setClickable(options.clickable);
	  }
	  babelHelpers.createClass(TagItem, [{
	    key: "getId",
	    value: function getId() {
	      return this.id;
	    }
	  }, {
	    key: "getEntityId",
	    value: function getEntityId() {
	      return this.entityId;
	    }
	  }, {
	    key: "getEntityType",
	    value: function getEntityType() {
	      return this.entityType;
	    }
	  }, {
	    key: "getSelector",
	    value: function getSelector() {
	      return this.selector;
	    }
	  }, {
	    key: "setSelector",
	    value: function setSelector(selector) {
	      this.selector = selector;
	    }
	  }, {
	    key: "getTitle",
	    value: function getTitle() {
	      return this.getTitleNode() && !this.getTitleNode().isNullable() ? this.getTitleNode().getText() : '';
	    }
	  }, {
	    key: "getTitleNode",
	    value: function getTitleNode() {
	      return this.title;
	    }
	  }, {
	    key: "setTitle",
	    value: function setTitle(title) {
	      if (main_core.Type.isStringFilled(title) || main_core.Type.isPlainObject(title) || title === null) {
	        this.title = title === null ? null : new TextNode(title);
	      }
	    }
	  }, {
	    key: "getAvatar",
	    value: function getAvatar() {
	      if (this.avatar !== null) {
	        return this.avatar;
	      }
	      if (this.getSelector().getTagAvatar() !== null) {
	        return this.getSelector().getTagAvatar();
	      }
	      if (this.getEntityTagOption('avatar') !== null) {
	        return this.getEntityTagOption('avatar');
	      }
	      return this.getEntityItemOption('avatar');
	    }
	  }, {
	    key: "setAvatar",
	    value: function setAvatar(avatar) {
	      if (main_core.Type.isString(avatar) || avatar === null) {
	        this.avatar = avatar;
	      }
	    }
	  }, {
	    key: "getAvatarOption",
	    value: function getAvatarOption(option) {
	      if (this.avatarOptions !== null && !main_core.Type.isUndefined(this.avatarOptions[option])) {
	        return this.avatarOptions[option];
	      }
	      const selectorAvatarOption = this.getSelector().getTagAvatarOption(option);
	      if (selectorAvatarOption !== null) {
	        return selectorAvatarOption[option];
	      }
	      const entityTagAvatarOptions = this.getEntityTagOption('avatarOptions');
	      if (main_core.Type.isPlainObject(entityTagAvatarOptions) && !main_core.Type.isUndefined(entityTagAvatarOptions[option])) {
	        return entityTagAvatarOptions[option];
	      }
	      const entityItemAvatarOptions = this.getEntityItemOption('avatarOptions');
	      if (main_core.Type.isPlainObject(entityItemAvatarOptions) && !main_core.Type.isUndefined(entityItemAvatarOptions[option])) {
	        return entityItemAvatarOptions[option];
	      }
	      return null;
	    }
	  }, {
	    key: "setAvatarOption",
	    value: function setAvatarOption(option, value) {
	      if (main_core.Type.isStringFilled(option) && !main_core.Type.isUndefined(value)) {
	        if (this.avatarOptions === null) {
	          this.avatarOptions = {};
	        }
	        this.avatarOptions[option] = value;
	      }
	    }
	  }, {
	    key: "setAvatarOptions",
	    value: function setAvatarOptions(options) {
	      if (main_core.Type.isPlainObject(options)) {
	        Object.keys(options).forEach(option => {
	          this.setAvatarOption(option, options[option]);
	        });
	      }
	    }
	  }, {
	    key: "getTextColor",
	    value: function getTextColor() {
	      if (this.textColor !== null) {
	        return this.textColor;
	      }
	      if (this.getSelector().getTagTextColor() !== null) {
	        return this.getSelector().getTagTextColor();
	      }
	      return this.getEntityTagOption('textColor');
	    }
	  }, {
	    key: "setTextColor",
	    value: function setTextColor(textColor) {
	      if (main_core.Type.isString(textColor) || textColor === null) {
	        this.textColor = textColor;
	      }
	    }
	  }, {
	    key: "getBgColor",
	    value: function getBgColor() {
	      if (this.bgColor !== null) {
	        return this.bgColor;
	      }
	      if (this.getSelector().getTagBgColor() !== null) {
	        return this.getSelector().getTagBgColor();
	      }
	      return this.getEntityTagOption('bgColor');
	    }
	  }, {
	    key: "setBgColor",
	    value: function setBgColor(bgColor) {
	      if (main_core.Type.isString(bgColor) || bgColor === null) {
	        this.bgColor = bgColor;
	      }
	    }
	  }, {
	    key: "getFontWeight",
	    value: function getFontWeight() {
	      if (this.fontWeight !== null) {
	        return this.fontWeight;
	      }
	      if (this.getSelector().getTagFontWeight() !== null) {
	        return this.getSelector().getTagFontWeight();
	      }
	      return this.getEntityTagOption('fontWeight');
	    }
	  }, {
	    key: "setFontWeight",
	    value: function setFontWeight(fontWeight) {
	      if (main_core.Type.isString(fontWeight) || fontWeight === null) {
	        this.fontWeight = fontWeight;
	      }
	    }
	  }, {
	    key: "getMaxWidth",
	    value: function getMaxWidth() {
	      if (this.maxWidth !== null) {
	        return this.maxWidth;
	      }
	      if (this.getSelector().getTagMaxWidth() !== null) {
	        return this.getSelector().getTagMaxWidth();
	      }
	      return this.getEntityTagOption('maxWidth');
	    }
	  }, {
	    key: "setMaxWidth",
	    value: function setMaxWidth(width) {
	      if (main_core.Type.isNumber(width) && width >= 0 || width === null) {
	        this.maxWidth = width;
	      }
	    }
	  }, {
	    key: "setDeselectable",
	    value: function setDeselectable(flag) {
	      if (main_core.Type.isBoolean(flag)) {
	        this.deselectable = flag;
	      }
	    }
	  }, {
	    key: "isDeselectable",
	    value: function isDeselectable() {
	      return this.deselectable === null ? this.getSelector().isDeselectable() : this.deselectable;
	    }
	  }, {
	    key: "getCustomData",
	    value: function getCustomData() {
	      return this.customData;
	    }
	  }, {
	    key: "getLink",
	    value: function getLink() {
	      return this.link;
	    }
	  }, {
	    key: "getOnclick",
	    value: function getOnclick() {
	      return this.onclick;
	    }
	  }, {
	    key: "setClickable",
	    value: function setClickable(flag) {
	      if (main_core.Type.isBoolean(flag)) {
	        this.clickable = flag;
	      }
	    }
	  }, {
	    key: "isClickable",
	    value: function isClickable() {
	      if (this.clickable !== null) {
	        return this.clickable;
	      }
	      if (this.getSelector().getTagClickable() !== null) {
	        return this.getSelector().getTagClickable();
	      }
	      if (this.getEntityTagOption('clickable') !== null) {
	        return this.getEntityTagOption('clickable');
	      }
	      if (this.getEntityItemOption('clickable') !== null) {
	        return this.getEntityItemOption('clickable');
	      }
	      return false;
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      const titleNode = this.getTitleNode();
	      if (titleNode) {
	        var _this$constructor;
	        titleNode.renderTo(this.getTitleContainer());
	        const title = this.getTitleContainer().textContent;
	        this.getContentContainer().setAttribute('title', _classStaticPrivateMethodGet$1(_this$constructor = this.constructor, TagItem, _sanitizeTitle$1).call(_this$constructor, title));
	      } else {
	        this.getTitleContainer().textContent = '';
	        main_core.Dom.attr(this.getContentContainer(), 'title', null);
	      }
	      const avatar = this.getAvatar();
	      const bgImage = this.getAvatarOption('bgImage');
	      if (main_core.Type.isStringFilled(avatar)) {
	        main_core.Dom.style(this.getAvatarContainer(), 'background-image', `url('${encodeUrl(avatar)}')`);
	      } else {
	        main_core.Dom.style(this.getAvatarContainer(), 'background-image', bgImage);
	      }
	      const bgColor = this.getAvatarOption('bgColor');
	      const bgSize = this.getAvatarOption('bgSize');
	      const border = this.getAvatarOption('border');
	      const borderRadius = this.getAvatarOption('borderRadius');
	      const outline = this.getAvatarOption('outline');
	      const outlineOffset = this.getAvatarOption('outlineOffset');
	      main_core.Dom.style(this.getAvatarContainer(), 'background-color', bgColor);
	      main_core.Dom.style(this.getAvatarContainer(), 'background-size', bgSize);
	      main_core.Dom.style(this.getAvatarContainer(), 'border', border);
	      main_core.Dom.style(this.getAvatarContainer(), 'border-radius', borderRadius);
	      main_core.Dom.style(this.getAvatarContainer(), 'outline', outline);
	      main_core.Dom.style(this.getAvatarContainer(), 'outline-offset', outlineOffset);
	      const hasAvatar = avatar || bgColor && bgColor !== 'none' || bgImage && bgImage !== 'none';
	      if (hasAvatar) {
	        main_core.Dom.addClass(this.getContainer(), 'ui-tag-selector-tag--has-avatar');
	      } else {
	        main_core.Dom.removeClass(this.getContainer(), 'ui-tag-selector-tag--has-avatar');
	      }
	      const maxWidth = this.getMaxWidth();
	      if (maxWidth > 0) {
	        main_core.Dom.style(this.getContainer(), 'max-width', `${maxWidth}px`);
	      } else {
	        main_core.Dom.style(this.getContainer(), 'max-width', null);
	      }
	      if (this.isDeselectable()) {
	        main_core.Dom.removeClass(this.getContainer(), 'ui-tag-selector-tag-readonly');
	      } else {
	        main_core.Dom.addClass(this.getContainer(), 'ui-tag-selector-tag-readonly');
	      }
	      main_core.Dom.style(this.getTitleContainer(), 'color', this.getTextColor());
	      main_core.Dom.style(this.getTitleContainer(), 'font-weight', this.getFontWeight());
	      main_core.Dom.style(this.getContainer(), 'background-color', this.getBgColor());
	      this.rendered = true;
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      return this.cache.remember('container', () => {
	        return main_core.Tag.render(_t$5 || (_t$5 = _$5`
				<div class="ui-tag-selector-item ui-tag-selector-tag">
					${0}
					${0}
				</div>
			`), this.getContentContainer(), this.getRemoveIcon());
	      });
	    }
	  }, {
	    key: "getContentContainer",
	    value: function getContentContainer() {
	      return this.cache.remember('content-container', () => {
	        if (main_core.Type.isStringFilled(this.getLink())) {
	          return main_core.Tag.render(_t2$2 || (_t2$2 = _$5`
					<a
						class="ui-tag-selector-tag-content"
						onclick="${0}"
						href="${0}"
						target="_blank"
					>
						${0}
						${0}
					</a>
				`), this.handleContainerClick.bind(this), this.getLink(), this.getAvatarContainer(), this.getTitleContainer());
	        }
	        const className = this.isClickable() || this.getOnclick() !== null ? ' ui-tag-selector-tag-content--clickable' : '';
	        return main_core.Tag.render(_t3$2 || (_t3$2 = _$5`
				<div
					class="ui-tag-selector-tag-content${0}"
					onclick="${0}"
				>
					${0}
					${0}
				</div>
			`), className, this.handleContainerClick.bind(this), this.getAvatarContainer(), this.getTitleContainer());
	      });
	    }
	  }, {
	    key: "getAvatarContainer",
	    value: function getAvatarContainer() {
	      return this.cache.remember('avatar', () => {
	        return main_core.Tag.render(_t4$2 || (_t4$2 = _$5`
				<div class="ui-tag-selector-tag-avatar"></div>
			`));
	      });
	    }
	  }, {
	    key: "getTitleContainer",
	    value: function getTitleContainer() {
	      return this.cache.remember('title', () => {
	        return main_core.Tag.render(_t5$1 || (_t5$1 = _$5`
				<div class="ui-tag-selector-tag-title"></div>
			`));
	      });
	    }
	  }, {
	    key: "getRemoveIcon",
	    value: function getRemoveIcon() {
	      return this.cache.remember('remove-icon', () => {
	        return main_core.Tag.render(_t6 || (_t6 = _$5`
				<div class="ui-tag-selector-tag-remove" onclick="${0}"></div>
			`), this.handleRemoveIconClick.bind(this));
	      });
	    }
	  }, {
	    key: "getEntityTagOption",
	    value: function getEntityTagOption(option) {
	      return Entity.getTagOption(this.getEntityId(), option, this.getEntityType());
	    }
	  }, {
	    key: "getEntityItemOption",
	    value: function getEntityItemOption(option) {
	      return Entity.getItemOption(this.getEntityId(), option, this.getEntityType());
	    }
	  }, {
	    key: "isRendered",
	    value: function isRendered() {
	      return this.rendered && this.getSelector() && this.getSelector().isRendered();
	    }
	  }, {
	    key: "remove",
	    value: function remove(animate = true) {
	      if (animate === false) {
	        main_core.Dom.remove(this.getContainer());
	        return Promise.resolve();
	      }
	      return new Promise(resolve => {
	        main_core.Dom.style(this.getContainer(), 'width', `${this.getContainer().offsetWidth}px`);
	        main_core.Dom.addClass(this.getContainer(), 'ui-tag-selector-tag--remove');
	        Animation.handleAnimationEnd(this.getContainer(), 'ui-tag-selector-tag-remove').then(() => {
	          main_core.Dom.remove(this.getContainer());
	          resolve();
	        }).catch(() => {
	          // Fail silently
	        });
	      });
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      return new Promise(resolve => {
	        main_core.Dom.addClass(this.getContainer(), 'ui-tag-selector-tag--show');
	        Animation.handleAnimationEnd(this.getContainer(), 'ui-tag-selector-tag-show').then(() => {
	          main_core.Dom.removeClass(this.getContainer(), 'ui-tag-selector-tag--show');
	          resolve();
	        }).catch(() => {
	          // Fail silently
	        });
	      });
	    }
	  }, {
	    key: "handleContainerClick",
	    value: function handleContainerClick() {
	      const fn = this.getOnclick();
	      if (main_core.Type.isFunction(fn)) {
	        fn(this);
	      }
	      const selector = this.getSelector();
	      selector.emit('TagItem:onClick', {
	        item: this
	      });
	    }
	  }, {
	    key: "handleRemoveIconClick",
	    value: function handleRemoveIconClick(event) {
	      event.stopPropagation();
	      if (this.isDeselectable()) {
	        this.getSelector().removeTag(this);
	      }
	    }
	  }]);
	  return TagItem;
	}();
	function _sanitizeTitle$1(text) {
	  return text.replaceAll(/[\t ]+/gm, ' ').replaceAll(/\n+/gm, '\n').trim();
	}

	let _$6 = t => t,
	  _t$6,
	  _t2$3,
	  _t3$3,
	  _t4$3,
	  _t5$2,
	  _t6$1,
	  _t7;
	/**
	 * @memberof BX.UI.EntitySelector
	 */
	let TagSelector = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(TagSelector, _EventEmitter);
	  function TagSelector(selectorOptions) {
	    var _this;
	    babelHelpers.classCallCheck(this, TagSelector);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(TagSelector).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "tags", []);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "cache", new main_core.Cache.MemoryCache());
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "multiple", true);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "readonly", false);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "locked", false);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "deselectable", true);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "addButtonCaption", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "addButtonCaptionMore", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "createButtonCaption", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "addButtonVisible", true);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "createButtonVisible", false);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "textBoxVisible", false);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "textBoxWidth", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "maxHeight", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "placeholder", '');
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "textBoxAutoHide", false);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "textBoxOldValue", '');
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "tagAvatar", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "tagAvatarOptions", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "tagTextColor", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "tagBgColor", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "tagFontWeight", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "tagMaxWidth", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "tagClickable", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "dialog", null);
	    _this.setEventNamespace('BX.UI.EntitySelector.TagSelector');
	    const options = main_core.Type.isPlainObject(selectorOptions) ? selectorOptions : {};
	    _this.id = main_core.Type.isStringFilled(options.id) ? options.id : `ui-tag-selector-${main_core.Text.getRandom().toLowerCase()}`;
	    _this.multiple = main_core.Type.isBoolean(options.multiple) ? options.multiple : true;
	    _this.addButtonVisible = options.showAddButton !== false;
	    _this.createButtonVisible = options.showCreateButton === true;
	    _this.textBoxVisible = options.showTextBox === true;
	    _this.setReadonly(options.readonly);
	    _this.setLocked(options.locked);
	    _this.setAddButtonCaption(options.addButtonCaption);
	    _this.setAddButtonCaptionMore(options.addButtonCaptionMore);
	    _this.setCreateButtonCaption(options.createButtonCaption);
	    _this.setPlaceholder(options.placeholder);
	    _this.setTextBoxAutoHide(options.textBoxAutoHide);
	    _this.setTextBoxWidth(options.textBoxWidth);
	    _this.setDeselectable(options.deselectable);
	    _this.setMaxHeight(options.maxHeight);
	    _this.setTagAvatar(options.tagAvatar);
	    _this.setTagAvatarOptions(options.tagAvatarOptions);
	    _this.setTagMaxWidth(options.tagMaxWidth);
	    _this.setTagTextColor(options.tagTextColor);
	    _this.setTagBgColor(options.tagBgColor);
	    _this.setTagFontWeight(options.tagFontWeight);
	    _this.setTagClickable(options.tagClickable);
	    if (main_core.Type.isPlainObject(options.dialogOptions)) {
	      let selectedItems = main_core.Type.isArray(options.items) ? options.items : [];
	      if (main_core.Type.isArray(options.dialogOptions.selectedItems)) {
	        selectedItems = selectedItems.concat(options.dialogOptions.selectedItems);
	      }
	      const dialogOptions = Object.assign({}, options.dialogOptions, {
	        tagSelectorOptions: null,
	        selectedItems,
	        multiple: _this.isMultiple(),
	        tagSelector: babelHelpers.assertThisInitialized(_this)
	      });
	      new Dialog(dialogOptions);
	    } else if (main_core.Type.isArray(options.items)) {
	      options.items.forEach(item => {
	        _this.addTag(item);
	      });
	    }
	    _this.subscribeFromOptions(options.events);
	    return _this;
	  }
	  babelHelpers.createClass(TagSelector, [{
	    key: "getDialog",
	    value: function getDialog() {
	      return this.dialog;
	    }
	    /**
	     * @internal
	     * @param dialog
	     */
	  }, {
	    key: "setDialog",
	    value: function setDialog(dialog) {
	      this.dialog = dialog;
	    }
	  }, {
	    key: "setReadonly",
	    value: function setReadonly(flag) {
	      if (main_core.Type.isBoolean(flag)) {
	        this.readonly = flag;
	        if (this.isRendered()) {
	          if (flag) {
	            main_core.Dom.addClass(this.getOuterContainer(), 'ui-tag-selector-container-readonly');
	          } else {
	            main_core.Dom.removeClass(this.getOuterContainer(), 'ui-tag-selector-container-readonly');
	          }
	        }
	      }
	    }
	  }, {
	    key: "isReadonly",
	    value: function isReadonly() {
	      return this.readonly;
	    }
	  }, {
	    key: "setLocked",
	    value: function setLocked(flag) {
	      if (main_core.Type.isBoolean(flag)) {
	        this.locked = flag;
	        if (flag) {
	          main_core.Dom.addClass(this.getOuterContainer(), 'ui-tag-selector-container-locked');
	          this.getTextBox().disabled = true;
	        } else {
	          main_core.Dom.removeClass(this.getOuterContainer(), 'ui-tag-selector-container-locked');
	          this.getTextBox().disabled = false;
	        }
	      }
	    }
	  }, {
	    key: "lock",
	    value: function lock() {
	      if (!this.isLocked()) {
	        this.setLocked(true);
	      }
	    }
	  }, {
	    key: "unlock",
	    value: function unlock() {
	      if (this.isLocked()) {
	        this.setLocked(false);
	      }
	    }
	  }, {
	    key: "isLocked",
	    value: function isLocked() {
	      return this.locked;
	    }
	  }, {
	    key: "isMultiple",
	    value: function isMultiple() {
	      return this.multiple;
	    }
	  }, {
	    key: "setDeselectable",
	    value: function setDeselectable(flag) {
	      if (main_core.Type.isBoolean(flag)) {
	        const changed = this.deselectable !== flag;
	        this.deselectable = flag;
	        if (changed) {
	          this.updateTags();
	        }
	      }
	    }
	  }, {
	    key: "isDeselectable",
	    value: function isDeselectable() {
	      return this.deselectable;
	    }
	  }, {
	    key: "getTag",
	    value: function getTag(tagItem) {
	      if (tagItem instanceof TagItem) {
	        return this.getTags().find(tag => tag === tagItem);
	      } else if (main_core.Type.isPlainObject(tagItem)) {
	        const {
	          id,
	          entityId
	        } = tagItem;
	        return this.getTags().find(tag => tag.getId() === id && tag.getEntityId() === entityId);
	      }
	      return null;
	    }
	  }, {
	    key: "addTag",
	    value: function addTag(tagOptions) {
	      if (!main_core.Type.isObjectLike(tagOptions)) {
	        throw new Error('TagSelector.addTag: wrong item options.');
	      }
	      if (this.getTag(tagOptions)) {
	        return null;
	      }
	      const tag = new TagItem(tagOptions);
	      tag.setSelector(this);
	      const event = new main_core_events.BaseEvent({
	        data: {
	          tag
	        }
	      });
	      this.emit('onBeforeTagAdd', event);
	      if (event.isDefaultPrevented()) {
	        return null;
	      }
	      if (!this.isMultiple()) {
	        this.removeTags();
	      }
	      this.tags.push(tag);
	      this.emit('onTagAdd', {
	        tag
	      });
	      if (this.isRendered()) {
	        tag.render();
	        this.getItemsContainer().insertBefore(tag.getContainer(), this.getTextBox());
	        if (tagOptions.animate !== false) {
	          tag.show().then(() => {
	            this.getContainer().scrollTop = this.getContainer().scrollHeight - this.getContainer().offsetHeight;
	            this.emit('onAfterTagAdd', {
	              tag
	            });
	          });
	        } else {
	          this.emit('onAfterTagAdd', {
	            tag
	          });
	        }
	        this.toggleAddButtonCaption();
	      } else {
	        this.emit('onAfterTagAdd', {
	          tag
	        });
	      }
	      return tag;
	    }
	  }, {
	    key: "removeTag",
	    value: function removeTag(item, animate = true) {
	      const tagItem = this.getTag(item);
	      if (!tagItem) {
	        return;
	      }
	      const event = new main_core_events.BaseEvent({
	        data: {
	          tag: tagItem
	        }
	      });
	      this.emit('onBeforeTagRemove', event);
	      if (event.isDefaultPrevented()) {
	        return;
	      }
	      this.tags = this.tags.filter(el => el !== tagItem);
	      this.emit('onTagRemove', {
	        tag: tagItem
	      });
	      if (this.isRendered()) {
	        tagItem.remove(animate).then(() => {
	          this.toggleAddButtonCaption();
	          this.emit('onAfterTagRemove', {
	            tag: tagItem
	          });
	        });
	      } else {
	        this.emit('onAfterTagRemove', {
	          tag: tagItem
	        });
	      }
	    }
	  }, {
	    key: "removeTags",
	    value: function removeTags() {
	      this.getTags().forEach(tag => {
	        this.removeTag(tag, false);
	      });
	    }
	  }, {
	    key: "getTags",
	    value: function getTags() {
	      return this.tags;
	    }
	  }, {
	    key: "renderTo",
	    value: function renderTo(node) {
	      this.rendered = true;
	      this.getTags().forEach(tag => {
	        tag.render();
	        this.getItemsContainer().insertBefore(tag.getContainer(), this.getTextBox());
	      });
	      if (main_core.Type.isDomNode(node)) {
	        main_core.Dom.append(this.getOuterContainer(), node);
	      }
	    }
	  }, {
	    key: "isRendered",
	    value: function isRendered() {
	      return this.rendered;
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "updateTags",
	    value: function updateTags() {
	      if (this.isRendered()) {
	        this.getTags().forEach(tag => {
	          tag.render();
	        });
	      }
	    }
	  }, {
	    key: "getOuterContainer",
	    value: function getOuterContainer() {
	      return this.cache.remember('outer-container', () => {
	        let className = this.isReadonly() ? ' ui-tag-selector-container-readonly' : '';
	        className += this.isLocked() ? ' ui-tag-selector-container-locked' : '';
	        return main_core.Tag.render(_t$6 || (_t$6 = _$6`
				<div class="ui-tag-selector-outer-container${0}">${0}</div>
			`), className, this.getContainer());
	      });
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      return this.cache.remember('container', () => {
	        const style = this.getMaxHeight() ? ` style="max-height: ${this.getMaxHeight()}px; -ms-overflow-style: -ms-autohiding-scrollbar;"` : '';
	        return main_core.Tag.render(_t2$3 || (_t2$3 = _$6`
				<div 
					class="ui-tag-selector-container" 
					onclick="${0}"
					${0}
				>
					${0}
					${0}
				</div>
			`), this.handleContainerClick.bind(this), style, this.getItemsContainer(), this.getCreateButton());
	      });
	    }
	  }, {
	    key: "getItemsContainer",
	    value: function getItemsContainer() {
	      return this.cache.remember('items-container', () => {
	        return main_core.Tag.render(_t3$3 || (_t3$3 = _$6`
				<div class="ui-tag-selector-items">
					${0}
					${0}
				</div>
			`), this.getTextBox(), this.getAddButton());
	      });
	    }
	  }, {
	    key: "getTextBox",
	    value: function getTextBox() {
	      return this.cache.remember('text-box', () => {
	        const className = this.textBoxVisible ? '' : ' ui-tag-selector-item-hidden';
	        const input = main_core.Tag.render(_t4$3 || (_t4$3 = _$6`
				<input 
					type="text" 
					class="ui-tag-selector-item ui-tag-selector-text-box${0}" 
					autocomplete="off"
					placeholder="${0}"
					oninput="${0}"
					onblur="${0}"
					onkeyup="${0}"
					onkeydown="${0}"
					value=""
				>
			`), className, main_core.Text.encode(this.getPlaceholder()), this.handleTextBoxInput.bind(this), this.handleTextBoxBlur.bind(this), this.handleTextBoxKeyUp.bind(this), this.handleTextBoxKeyDown.bind(this));
	        const width = this.getTextBoxWidth();
	        if (width !== null) {
	          main_core.Dom.style(input, 'width', main_core.Type.isStringFilled(width) ? width : `${width}px`);
	        }
	        if (this.isLocked()) {
	          input.disabled = true;
	        }
	        return input;
	      });
	    }
	  }, {
	    key: "getItemsHeight",
	    value: function getItemsHeight() {
	      return this.getItemsContainer().scrollHeight;
	    }
	  }, {
	    key: "calcHeight",
	    value: function calcHeight() {
	      if (this.getMaxHeight() !== null) {
	        return Math.min(this.getItemsHeight(), this.getMaxHeight());
	      }
	      return Math.max(this.getItemsHeight(), this.getMinHeight());
	    }
	  }, {
	    key: "getTextBoxValue",
	    value: function getTextBoxValue() {
	      return this.getTextBox().value;
	    }
	  }, {
	    key: "clearTextBox",
	    value: function clearTextBox() {
	      this.getTextBox().value = '';
	      this.textBoxOldValue = '';
	    }
	  }, {
	    key: "showTextBox",
	    value: function showTextBox() {
	      this.textBoxVisible = true;
	      main_core.Dom.removeClass(this.getTextBox(), 'ui-tag-selector-item-hidden');
	    }
	  }, {
	    key: "hideTextBox",
	    value: function hideTextBox() {
	      this.textBoxVisible = false;
	      main_core.Dom.addClass(this.getTextBox(), 'ui-tag-selector-item-hidden');
	    }
	  }, {
	    key: "focusTextBox",
	    value: function focusTextBox() {
	      this.getTextBox().focus();
	    }
	  }, {
	    key: "setTextBoxAutoHide",
	    value: function setTextBoxAutoHide(autoHide) {
	      if (main_core.Type.isBoolean(autoHide)) {
	        this.textBoxAutoHide = autoHide;
	      }
	    }
	  }, {
	    key: "getTextBoxWidth",
	    value: function getTextBoxWidth() {
	      return this.textBoxWidth;
	    }
	  }, {
	    key: "setTextBoxWidth",
	    value: function setTextBoxWidth(width) {
	      if (main_core.Type.isStringFilled(width) || width === null) {
	        this.textBoxWidth = width;
	        if (this.isRendered()) {
	          main_core.Dom.style(this.getTextBox(), 'width', width);
	        }
	      } else if (main_core.Type.isNumber(width) && width > 0) {
	        this.textBoxWidth = width;
	        if (this.isRendered()) {
	          main_core.Dom.style(this.getTextBox(), 'width', `${width}px`);
	        }
	      }
	    }
	  }, {
	    key: "getTagMaxWidth",
	    value: function getTagMaxWidth() {
	      return this.tagMaxWidth;
	    }
	  }, {
	    key: "setTagMaxWidth",
	    value: function setTagMaxWidth(width) {
	      if (main_core.Type.isNumber(width) && width >= 0 || width === null) {
	        this.tagMaxWidth = width;
	        this.updateTags();
	      }
	    }
	  }, {
	    key: "getTagAvatar",
	    value: function getTagAvatar() {
	      return this.tagAvatar;
	    }
	  }, {
	    key: "setTagAvatar",
	    value: function setTagAvatar(tagAvatar) {
	      if (main_core.Type.isString(tagAvatar) || tagAvatar === null) {
	        this.tagAvatar = tagAvatar;
	        this.updateTags();
	      }
	    }
	  }, {
	    key: "getTagClickable",
	    value: function getTagClickable() {
	      return this.tagClickable;
	    }
	  }, {
	    key: "setTagClickable",
	    value: function setTagClickable(flag) {
	      if (main_core.Type.isBoolean(flag) || flag === null) {
	        this.tagClickable = flag;
	        this.updateTags();
	      }
	    }
	  }, {
	    key: "getTagAvatarOptions",
	    value: function getTagAvatarOptions() {
	      return this.tagAvatarOptions;
	    }
	  }, {
	    key: "getTagAvatarOption",
	    value: function getTagAvatarOption(option) {
	      if (this.tagAvatarOptions !== null && !main_core.Type.isUndefined(this.tagAvatarOptions[option])) {
	        return this.tagAvatarOptions[option];
	      }
	      return null;
	    }
	  }, {
	    key: "setTagAvatarOption",
	    value: function setTagAvatarOption(option, value) {
	      if (main_core.Type.isStringFilled(option) && !main_core.Type.isUndefined(value)) {
	        if (this.tagAvatarOptions === null) {
	          this.tagAvatarOptions = {};
	        }
	        this.tagAvatarOptions[option] = value;
	        this.updateTags();
	      }
	    }
	  }, {
	    key: "setTagAvatarOptions",
	    value: function setTagAvatarOptions(options) {
	      if (main_core.Type.isPlainObject(options)) {
	        Object.keys(options).forEach(option => {
	          this.setTagAvatarOption(option, options[option]);
	        });
	      }
	    }
	  }, {
	    key: "getTagTextColor",
	    value: function getTagTextColor() {
	      return this.tagTextColor;
	    }
	  }, {
	    key: "setTagTextColor",
	    value: function setTagTextColor(textColor) {
	      if (main_core.Type.isString(textColor) || textColor === null) {
	        this.tagTextColor = textColor;
	        this.updateTags();
	      }
	    }
	  }, {
	    key: "getTagBgColor",
	    value: function getTagBgColor() {
	      return this.tagBgColor;
	    }
	  }, {
	    key: "setTagBgColor",
	    value: function setTagBgColor(bgColor) {
	      if (main_core.Type.isString(bgColor) || bgColor === null) {
	        this.tagBgColor = bgColor;
	        this.updateTags();
	      }
	    }
	  }, {
	    key: "getTagFontWeight",
	    value: function getTagFontWeight() {
	      return this.tagFontWeight;
	    }
	  }, {
	    key: "setTagFontWeight",
	    value: function setTagFontWeight(fontWeight) {
	      if (main_core.Type.isString(fontWeight) || fontWeight === null) {
	        this.tagFontWeight = fontWeight;
	        this.updateTags();
	      }
	    }
	  }, {
	    key: "getPlaceholder",
	    value: function getPlaceholder() {
	      return this.placeholder;
	    }
	  }, {
	    key: "setPlaceholder",
	    value: function setPlaceholder(placeholder) {
	      if (main_core.Type.isStringFilled(placeholder)) {
	        this.placeholder = placeholder;
	        if (this.isRendered()) {
	          this.getTextBox().placeholder = placeholder;
	        }
	      }
	    }
	  }, {
	    key: "getMaxHeight",
	    value: function getMaxHeight() {
	      return this.maxHeight;
	    }
	  }, {
	    key: "getMinHeight",
	    value: function getMinHeight() {
	      return 34;
	    }
	  }, {
	    key: "setMaxHeight",
	    value: function setMaxHeight(height) {
	      if (main_core.Type.isNumber(height) && height > 0 || height === null) {
	        this.maxHeight = height;
	        if (this.isRendered()) {
	          main_core.Dom.style(this.getContainer(), 'max-height', height > 0 ? `${height}px` : null);
	          main_core.Dom.style(this.getContainer(), '-ms-overflow-style', height > 0 ? '-ms-autohiding-scrollbar' : null);
	        }
	      }
	    }
	  }, {
	    key: "getAddButton",
	    value: function getAddButton() {
	      return this.cache.remember('add-button', () => {
	        const className = this.addButtonVisible ? '' : ' ui-tag-selector-item-hidden';
	        return main_core.Tag.render(_t5$2 || (_t5$2 = _$6`
				<span class="ui-tag-selector-item ui-tag-selector-add-button${0}">
					${0}
				</span>
			`), className, this.getAddButtonLink());
	      });
	    }
	  }, {
	    key: "getAddButtonLink",
	    value: function getAddButtonLink() {
	      return this.cache.remember('add-button-link', () => {
	        const caption = main_core.Text.encode(this.getActualButtonCaption());
	        return main_core.Tag.render(_t6$1 || (_t6$1 = _$6`
				<span 
					class="ui-tag-selector-add-button-caption" 
					onclick="${0}">${0}</span>
			`), this.handleAddButtonClick.bind(this), caption);
	      });
	    }
	  }, {
	    key: "getAddButtonCaption",
	    value: function getAddButtonCaption() {
	      return this.addButtonCaption === null ? main_core.Loc.getMessage('UI_TAG_SELECTOR_ADD_BUTTON_CAPTION') : this.addButtonCaption;
	    }
	  }, {
	    key: "setAddButtonCaption",
	    value: function setAddButtonCaption(caption) {
	      if (main_core.Type.isStringFilled(caption)) {
	        this.addButtonCaption = caption;
	        if (this.isRendered()) {
	          this.toggleAddButtonCaption();
	        }
	      }
	    }
	  }, {
	    key: "getAddButtonCaptionMore",
	    value: function getAddButtonCaptionMore() {
	      return this.addButtonCaptionMore === null ? this.isMultiple() ? main_core.Loc.getMessage('UI_TAG_SELECTOR_ADD_BUTTON_CAPTION') : main_core.Loc.getMessage('UI_TAG_SELECTOR_ADD_BUTTON_CAPTION_SINGLE') : this.addButtonCaptionMore;
	    }
	  }, {
	    key: "setAddButtonCaptionMore",
	    value: function setAddButtonCaptionMore(caption) {
	      if (main_core.Type.isStringFilled(caption)) {
	        this.addButtonCaptionMore = caption;
	        if (this.isRendered()) {
	          this.toggleAddButtonCaption();
	        }
	      }
	    }
	  }, {
	    key: "toggleAddButtonCaption",
	    value: function toggleAddButtonCaption() {
	      if (this.getAddButtonCaptionMore() === null) {
	        return;
	      }
	      this.getAddButtonLink().textContent = this.getActualButtonCaption();
	    }
	  }, {
	    key: "getActualButtonCaption",
	    value: function getActualButtonCaption() {
	      return this.getTags().length > 0 && this.getAddButtonCaptionMore() !== null ? this.getAddButtonCaptionMore() : this.getAddButtonCaption();
	    }
	  }, {
	    key: "showAddButton",
	    value: function showAddButton() {
	      this.addButtonVisible = true;
	      main_core.Dom.removeClass(this.getAddButton(), 'ui-tag-selector-item-hidden');
	    }
	  }, {
	    key: "hideAddButton",
	    value: function hideAddButton() {
	      this.addButtonVisible = false;
	      main_core.Dom.addClass(this.getAddButton(), 'ui-tag-selector-item-hidden');
	    }
	  }, {
	    key: "getCreateButton",
	    value: function getCreateButton() {
	      return this.cache.remember('create-button', () => {
	        const className = this.createButtonVisible ? '' : ' ui-tag-selector-item-hidden';
	        return main_core.Tag.render(_t7 || (_t7 = _$6`
				<div class="ui-tag-selector-create-button${0}">
					<span 
						class="ui-tag-selector-create-button-caption"
						onclick="${0}"
					>${0}</span>
				</div>
			`), className, this.handleCreateButtonClick.bind(this), main_core.Text.encode(this.getCreateButtonCaption()));
	      });
	    }
	  }, {
	    key: "showCreateButton",
	    value: function showCreateButton() {
	      this.createButtonVisible = true;
	      main_core.Dom.removeClass(this.getCreateButton(), 'ui-tag-selector-item-hidden');
	    }
	  }, {
	    key: "hideCreateButton",
	    value: function hideCreateButton() {
	      this.createButtonVisible = false;
	      main_core.Dom.addClass(this.getCreateButton(), 'ui-tag-selector-item-hidden');
	    }
	  }, {
	    key: "getCreateButtonCaption",
	    value: function getCreateButtonCaption() {
	      return this.createButtonCaption === null ? main_core.Loc.getMessage('UI_TAG_SELECTOR_CREATE_BUTTON_CAPTION') : this.createButtonCaption;
	    }
	  }, {
	    key: "setCreateButtonCaption",
	    value: function setCreateButtonCaption(caption) {
	      if (main_core.Type.isStringFilled(caption)) {
	        this.createButtonCaption = caption;
	        if (this.isRendered()) {
	          this.getCreateButton().children[0].textContent = caption;
	        }
	      }
	    }
	  }, {
	    key: "handleContainerClick",
	    value: function handleContainerClick(event) {
	      this.emit('onContainerClick', {
	        event
	      });
	    }
	  }, {
	    key: "handleTextBoxInput",
	    value: function handleTextBoxInput(event) {
	      const newValue = this.getTextBoxValue();
	      if (newValue !== this.textBoxOldValue) {
	        this.textBoxOldValue = newValue;
	        this.emit('onInput', {
	          event
	        });
	      }
	    }
	  }, {
	    key: "handleTextBoxBlur",
	    value: function handleTextBoxBlur(event) {
	      this.emit('onBlur', {
	        event
	      });
	      if (this.textBoxAutoHide) {
	        this.clearTextBox();
	        this.showAddButton();
	        this.hideTextBox();
	      }
	    }
	  }, {
	    key: "handleTextBoxKeyUp",
	    value: function handleTextBoxKeyUp(event) {
	      this.emit('onKeyUp', {
	        event
	      });
	      if (event.key === 'Enter') {
	        this.emit('onEnter', {
	          event
	        });
	        if (this.textBoxAutoHide) {
	          this.clearTextBox();
	          this.showAddButton();
	          this.hideTextBox();
	        }
	      }
	    }
	  }, {
	    key: "handleTextBoxKeyDown",
	    value: function handleTextBoxKeyDown(event) {
	      if (event.key === 'Enter') {
	        // prevent a form submit
	        event.preventDefault();
	        if (main_core.Browser.isMac() && event.metaKey || event.ctrlKey) {
	          this.emit('onMetaEnter', {
	            event
	          });
	        }
	      }
	      this.emit('onKeyDown', {
	        event
	      });
	    }
	  }, {
	    key: "handleAddButtonClick",
	    value: function handleAddButtonClick(event) {
	      this.hideAddButton();
	      this.showTextBox();
	      this.focusTextBox();
	      this.emit('onAddButtonClick', {
	        event
	      });
	    }
	  }, {
	    key: "handleCreateButtonClick",
	    value: function handleCreateButtonClick(event) {
	      this.emit('onCreateButtonClick', {
	        event
	      });
	    }
	  }]);
	  return TagSelector;
	}(main_core_events.EventEmitter);

	let Navigation = /*#__PURE__*/function () {
	  // IE/Edge compatible event names

	  function Navigation(dialog) {
	    babelHelpers.classCallCheck(this, Navigation);
	    babelHelpers.defineProperty(this, "dialog", null);
	    babelHelpers.defineProperty(this, "lockedTab", null);
	    babelHelpers.defineProperty(this, "enabled", false);
	    this.dialog = dialog;
	    this.dialog.subscribe('onShow', this.handleDialogShow.bind(this));
	    this.dialog.subscribe('onHide', this.handleDialogHide.bind(this));
	    this.dialog.subscribe('onDestroy', this.handleDialogDestroy.bind(this));
	    this.handleDocumentKeyDown = this.handleDocumentKeyDown.bind(this);
	    this.handleDocumentMouseMove = this.handleDocumentMouseMove.bind(this);
	  }
	  babelHelpers.createClass(Navigation, [{
	    key: "getDialog",
	    value: function getDialog() {
	      return this.dialog;
	    }
	  }, {
	    key: "enable",
	    value: function enable() {
	      if (!this.isEnabled()) {
	        this.bindEvents();
	      }
	      this.enabled = true;
	    }
	  }, {
	    key: "disable",
	    value: function disable() {
	      if (this.isEnabled()) {
	        this.unbindEvents();
	        this.unlockTab();
	      }
	      this.enabled = false;
	    }
	  }, {
	    key: "isEnabled",
	    value: function isEnabled() {
	      return this.enabled;
	    }
	  }, {
	    key: "bindEvents",
	    value: function bindEvents() {
	      main_core.Event.bind(document, 'keydown', this.handleDocumentKeyDown);
	    }
	  }, {
	    key: "unbindEvents",
	    value: function unbindEvents() {
	      main_core.Event.unbind(document, 'keydown', this.handleDocumentKeyDown);
	    }
	  }, {
	    key: "getNextNode",
	    value: function getNextNode() {
	      if (!this.getActiveNode()) {
	        return null;
	      }
	      let nextNode = null;
	      let currentNode = this.getActiveNode();
	      if (currentNode.hasChildren() && currentNode.isOpen()) {
	        nextNode = currentNode.getFirstChild();
	      }
	      while (nextNode === null && currentNode !== null) {
	        nextNode = currentNode.getNextSibling();
	        if (nextNode) {
	          break;
	        }
	        currentNode = currentNode.getParentNode();
	      }
	      return nextNode;
	    }
	  }, {
	    key: "getPreviousNode",
	    value: function getPreviousNode() {
	      if (!this.getActiveNode()) {
	        return null;
	      }
	      let previousNode = this.getActiveNode().getPreviousSibling();
	      if (previousNode) {
	        while (previousNode.hasChildren() && previousNode.isOpen()) {
	          const lastChild = previousNode.getLastChild();
	          if (lastChild === null) {
	            break;
	          }
	          previousNode = lastChild;
	        }
	      } else {
	        if (this.getActiveNode().getParentNode() && !this.getActiveNode().getParentNode().isRoot()) {
	          previousNode = this.getActiveNode().getParentNode();
	        }
	      }
	      return previousNode;
	    }
	  }, {
	    key: "getFirstNode",
	    value: function getFirstNode() {
	      const tab = this.getDialog().getActiveTab();
	      return tab && tab.getRootNode().getFirstChild();
	    }
	  }, {
	    key: "getLastNode",
	    value: function getLastNode() {
	      const tab = this.getDialog().getActiveTab();
	      if (!tab) {
	        return null;
	      }
	      let lastNode = tab.getRootNode().getLastChild();
	      if (lastNode !== null) {
	        while (lastNode.hasChildren() && lastNode.isOpen()) {
	          const lastChild = lastNode.getLastChild();
	          if (lastChild === null) {
	            break;
	          }
	          lastNode = lastChild;
	        }
	      }
	      return lastNode;
	    }
	  }, {
	    key: "getActiveNode",
	    value: function getActiveNode() {
	      return this.getDialog().getFocusedNode();
	    }
	  }, {
	    key: "focusOnNode",
	    value: function focusOnNode(node) {
	      if (node) {
	        node.focus();
	        node.scrollIntoView();
	      }
	    }
	  }, {
	    key: "lockTab",
	    value: function lockTab() {
	      const activeTab = this.getDialog().getActiveTab();
	      if (this.lockedTab === activeTab) {
	        return;
	      } else if (this.lockedTab !== null) {
	        this.unlockTab();
	      }
	      this.lockedTab = activeTab;
	      this.lockedTab.lock();
	      main_core.Event.bind(document, 'mousemove', this.handleDocumentMouseMove);
	    }
	  }, {
	    key: "unlockTab",
	    value: function unlockTab() {
	      if (this.lockedTab === null) {
	        return;
	      }
	      this.lockedTab.unlock();
	      this.lockedTab = null;
	      main_core.Event.unbind(document, 'mousemove', this.handleDocumentMouseMove);
	    }
	  }, {
	    key: "handleDialogShow",
	    value: function handleDialogShow() {
	      this.enable();
	    }
	  }, {
	    key: "handleDialogHide",
	    value: function handleDialogHide() {
	      this.disable();
	    }
	  }, {
	    key: "handleDialogDestroy",
	    value: function handleDialogDestroy() {
	      this.disable();
	    }
	  }, {
	    key: "handleDocumentMouseMove",
	    value: function handleDocumentMouseMove() {
	      this.unlockTab();
	    }
	  }, {
	    key: "handleDocumentKeyDown",
	    value: function handleDocumentKeyDown(event) {
	      if (!this.getDialog().isOpen()) {
	        this.unbindEvents();
	        return;
	      }
	      if (event.metaKey || event.ctrlKey || event.altKey) {
	        return;
	      }
	      const activeTab = this.getDialog().getActiveTab();
	      if (!activeTab) {
	        return;
	      }
	      const keyName = this.constructor.keyMap[event.key] || event.key;
	      if (activeTab === this.getDialog().getSearchTab() && ['ArrowLeft', 'ArrowRight'].includes(keyName)) {
	        return;
	      }
	      const handler = this[`handle${keyName}Press`];
	      if (handler) {
	        handler.call(this, event);
	        this.lockTab(activeTab);
	        event.preventDefault();
	      }
	    }
	  }, {
	    key: "handleArrowDownPress",
	    value: function handleArrowDownPress() {
	      if (!this.getActiveNode()) {
	        const firstNode = this.getFirstNode();
	        this.focusOnNode(firstNode);
	      } else {
	        const nextNode = this.getNextNode();
	        if (nextNode) {
	          this.focusOnNode(nextNode);
	        } else {
	          const firstNode = this.getFirstNode();
	          this.focusOnNode(firstNode);
	        }
	      }
	    }
	  }, {
	    key: "handleArrowUpPress",
	    value: function handleArrowUpPress() {
	      if (!this.getActiveNode()) {
	        const lastNode = this.getLastNode();
	        this.focusOnNode(lastNode);
	      } else {
	        const previousNode = this.getPreviousNode();
	        if (previousNode) {
	          this.focusOnNode(previousNode);
	        } else {
	          const lastNode = this.getLastNode();
	          this.focusOnNode(lastNode);
	        }
	      }
	    }
	  }, {
	    key: "handleArrowRightPress",
	    value: function handleArrowRightPress() {
	      if (this.getActiveNode()) {
	        this.getActiveNode().expand();
	      }
	    }
	  }, {
	    key: "handleArrowLeftPress",
	    value: function handleArrowLeftPress() {
	      if (!this.getActiveNode()) {
	        return;
	      }
	      if (this.getActiveNode().isOpen()) {
	        this.getActiveNode().collapse();
	      } else {
	        const parentNode = this.getActiveNode().getParentNode();
	        if (parentNode && !parentNode.isRoot()) {
	          this.focusOnNode(parentNode);
	        }
	      }
	    }
	  }, {
	    key: "handleEnterPress",
	    value: function handleEnterPress() {
	      if (this.getActiveNode()) {
	        this.getActiveNode().click();
	      }
	    }
	    /*handleSpacePress(event: KeyboardEvent): void
	    {
	    	const searchQuery = this.getDialog().getTagSelector() && this.getDialog().getTagSelector().getTextBoxValue();
	    	if (this.getActiveNode() && !Type.isStringFilled(searchQuery))
	    	{
	    		this.getActiveNode().click();
	    		event.preventDefault();
	    	}
	    }*/
	  }, {
	    key: "handleTabPress",
	    value: function handleTabPress(event) {
	      const activeTab = this.getDialog().getActiveTab();
	      if (!activeTab) {
	        this.getDialog().selectFirstTab();
	        return;
	      }
	      if (event.shiftKey) {
	        const previousTab = this.getDialog().getPreviousTab();
	        if (previousTab) {
	          this.getDialog().selectTab(previousTab.getId());
	        } else {
	          this.getDialog().selectLastTab();
	        }
	      } else {
	        const nextTab = this.getDialog().getNextTab();
	        if (nextTab) {
	          this.getDialog().selectTab(nextTab.getId());
	        } else {
	          this.getDialog().selectFirstTab();
	        }
	      }
	    }
	  }]);
	  return Navigation;
	}();
	babelHelpers.defineProperty(Navigation, "keyMap", {
	  'Down': 'ArrowDown',
	  'Up': 'ArrowUp',
	  'Left': 'ArrowLeft',
	  'Right': 'ArrowRight',
	  'Spacebar': 'Space',
	  ' ': 'Space' // For all browsers
	});

	let SliderIntegration = /*#__PURE__*/function () {
	  function SliderIntegration(dialog) {
	    babelHelpers.classCallCheck(this, SliderIntegration);
	    babelHelpers.defineProperty(this, "dialog", null);
	    babelHelpers.defineProperty(this, "sliders", new Set());
	    this.dialog = dialog;
	    this.dialog.subscribe('onShow', this.handleDialogShow.bind(this));
	    this.dialog.subscribe('onHide', this.handleDialogHide.bind(this));
	    this.dialog.subscribe('onDestroy', this.handleDialogDestroy.bind(this));
	    this.handleSliderOpen = this.handleSliderOpen.bind(this);
	    this.handleSliderClose = this.handleSliderClose.bind(this);
	    this.handleSliderDestroy = this.handleSliderDestroy.bind(this);
	  }
	  babelHelpers.createClass(SliderIntegration, [{
	    key: "getDialog",
	    value: function getDialog() {
	      return this.dialog;
	    }
	  }, {
	    key: "bindEvents",
	    value: function bindEvents() {
	      this.unbindEvents();
	      if (top.BX) {
	        top.BX.Event.EventEmitter.subscribe('SidePanel.Slider:onOpen', this.handleSliderOpen);
	        top.BX.Event.EventEmitter.subscribe('SidePanel.Slider:onCloseComplete', this.handleSliderClose);
	        top.BX.Event.EventEmitter.subscribe('SidePanel.Slider:onDestroy', this.handleSliderDestroy);
	      }
	    }
	  }, {
	    key: "unbindEvents",
	    value: function unbindEvents() {
	      if (top.BX) {
	        top.BX.Event.EventEmitter.unsubscribe('SidePanel.Slider:onOpen', this.handleSliderOpen);
	        top.BX.Event.EventEmitter.unsubscribe('SidePanel.Slider:onCloseComplete', this.handleSliderClose);
	        top.BX.Event.EventEmitter.unsubscribe('SidePanel.Slider:onDestroy', this.handleSliderDestroy);
	      }
	    }
	  }, {
	    key: "isDialogInSlider",
	    value: function isDialogInSlider(slider) {
	      if (slider.getFrameWindow()) {
	        return slider.getFrameWindow().document.contains(this.getDialog().getContainer());
	      } else {
	        return slider.getContainer().contains(this.getDialog().getContainer());
	      }
	    }
	  }, {
	    key: "handleDialogShow",
	    value: function handleDialogShow() {
	      this.bindEvents();
	    }
	  }, {
	    key: "handleDialogHide",
	    value: function handleDialogHide() {
	      this.sliders.clear();
	      this.unbindEvents();
	      this.getDialog().unfreeze();
	    }
	  }, {
	    key: "handleDialogDestroy",
	    value: function handleDialogDestroy() {
	      this.sliders.clear();
	      this.unbindEvents();
	    }
	  }, {
	    key: "handleSliderOpen",
	    value: function handleSliderOpen(event) {
	      const [sliderEvent] = event.getData();
	      const slider = sliderEvent.getSlider();
	      if (!this.isDialogInSlider(slider)) {
	        this.sliders.add(slider);
	        this.getDialog().freeze();
	      }
	    }
	  }, {
	    key: "handleSliderClose",
	    value: function handleSliderClose(event) {
	      const [sliderEvent] = event.getData();
	      const slider = sliderEvent.getSlider();
	      this.sliders.delete(slider);
	      if (this.sliders.size === 0) {
	        this.getDialog().unfreeze();
	      }
	    }
	  }, {
	    key: "handleSliderDestroy",
	    value: function handleSliderDestroy(event) {
	      const [sliderEvent] = event.getData();
	      const slider = sliderEvent.getSlider();
	      if (this.isDialogInSlider(slider)) {
	        this.unbindEvents();
	        this.dialog.destroy();
	      } else {
	        this.sliders.delete(slider);
	        if (this.sliders.size === 0) {
	          this.getDialog().unfreeze();
	        }
	      }
	    }
	  }]);
	  return SliderIntegration;
	}();

	let _$7 = t => t,
	  _t$7;
	let DefaultHeader = /*#__PURE__*/function (_BaseHeader) {
	  babelHelpers.inherits(DefaultHeader, _BaseHeader);
	  function DefaultHeader(context, options) {
	    var _this;
	    babelHelpers.classCallCheck(this, DefaultHeader);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(DefaultHeader).call(this, context, options));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "content", null);
	    _this.setContent(_this.getOption('content'));
	    return _this;
	  }
	  babelHelpers.createClass(DefaultHeader, [{
	    key: "render",
	    value: function render() {
	      const container = main_core.Tag.render(_t$7 || (_t$7 = _$7`
			<div>
				${0}
			</div>
		`), this.getContent() ? this.getContent() : '');
	      const className = this.getOption('containerClass', 'ui-selector-header-default');
	      const containerStyles = this.getOption('containerStyles', {});
	      main_core.Dom.addClass(container, className);
	      main_core.Dom.style(container, containerStyles);
	      return container;
	    }
	  }, {
	    key: "getContent",
	    value: function getContent() {
	      return this.content;
	    }
	  }, {
	    key: "setContent",
	    value: function setContent(content) {
	      if (main_core.Type.isStringFilled(content) || main_core.Type.isDomNode(content) || main_core.Type.isArrayFilled(content)) {
	        this.content = content;
	      }
	    }
	  }]);
	  return DefaultHeader;
	}(BaseHeader);

	let _$8 = t => t,
	  _t$8;
	let DefaultFooter = /*#__PURE__*/function (_BaseFooter) {
	  babelHelpers.inherits(DefaultFooter, _BaseFooter);
	  function DefaultFooter(context, options) {
	    var _this;
	    babelHelpers.classCallCheck(this, DefaultFooter);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(DefaultFooter).call(this, context, options));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "content", null);
	    _this.setContent(_this.getOption('content'));
	    return _this;
	  }
	  babelHelpers.createClass(DefaultFooter, [{
	    key: "render",
	    value: function render() {
	      const container = main_core.Tag.render(_t$8 || (_t$8 = _$8`
			<div>
				${0}
			</div>
		`), this.getContent() ? this.getContent() : '');
	      const className = this.getOption('containerClass', 'ui-selector-footer-default');
	      const containerStyles = this.getOption('containerStyles', {});
	      main_core.Dom.addClass(container, className);
	      main_core.Dom.style(container, containerStyles);
	      return container;
	    }
	  }, {
	    key: "getContent",
	    value: function getContent() {
	      return this.content;
	    }
	  }, {
	    key: "setContent",
	    value: function setContent(content) {
	      if (main_core.Type.isStringFilled(content) || main_core.Type.isDomNode(content) || main_core.Type.isArrayFilled(content)) {
	        this.content = content;
	      }
	    }
	  }]);
	  return DefaultFooter;
	}(BaseFooter);

	let RecentTab = /*#__PURE__*/function (_Tab) {
	  babelHelpers.inherits(RecentTab, _Tab);
	  function RecentTab(dialog, tabOptions) {
	    babelHelpers.classCallCheck(this, RecentTab);
	    const icon = 'data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2223%22%20height%3D%2223%22%20fill%3D%' + '22none%22%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%3E%3Cpath%20d%3D%22M14.432%2013.985a.96.' + '96%200%2000-.96-.96H8.505a.96.96%200%20000%201.92h4.967c.53%200%20.96-.43.96-.96zM14.432%2011.' + '009a.96.96%200%2000-.96-.96H8.505a.96.96%200%20000%201.92h4.967c.53%200%20.96-.43.96-.96zM14.' + '432%208.033a.96.96%200%2000-.96-.96H8.505a.96.96%200%20000%201.92h4.967c.53%200%20.96-.43.96-.' + '96z%22%20fill%3D%22%23ABB1B8%22/%3E%3Cpath%20fill-rule%3D%22evenodd%22%20clip-rule%3D%22evenodd' + '%22%20d%3D%22M10.988%2019.52c1.8%200%203.469-.558%204.844-1.51l2.205%202.204a1.525%201.525%200%20' + '102.157-2.157l-2.205-2.205a8.512%208.512%200%2010-7%203.668zm0-2.403a6.108%206.108%200%20100-12.2' + '16%206.108%206.108%200%20000%2012.216z%22%20fill%3D%22%23ABB1B8%22/%3E%3C/svg%3E';
	    const defaults = {
	      title: main_core.Loc.getMessage('UI_SELECTOR_RECENT_TAB_TITLE'),
	      itemOrder: {
	        sort: 'asc'
	      },
	      visible: !dialog.isDropdownMode(),
	      stub: !dialog.isDropdownMode(),
	      icon: {
	        //default: '/bitrix/js/ui/entity-selector/src/css/images/recent-tab-icon.svg',
	        //selected: '/bitrix/js/ui/entity-selector/src/css/images/recent-tab-icon-selected.svg'
	        default: icon,
	        selected: icon.replace(/ABB1B8/g, 'fff')
	      }
	    };
	    const options = Object.assign({}, defaults, tabOptions);
	    options.id = 'recents';
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(RecentTab).call(this, dialog, options));
	  }
	  return RecentTab;
	}(Tab);

	let MatchResult = /*#__PURE__*/function () {
	  function MatchResult(item, queryWords, matchIndexes = []) {
	    babelHelpers.classCallCheck(this, MatchResult);
	    babelHelpers.defineProperty(this, "item", null);
	    babelHelpers.defineProperty(this, "queryWords", null);
	    babelHelpers.defineProperty(this, "matchFields", new Map());
	    babelHelpers.defineProperty(this, "sort", null);
	    this.item = item;
	    this.queryWords = queryWords;
	    this.addIndexes(matchIndexes);
	  }
	  babelHelpers.createClass(MatchResult, [{
	    key: "getItem",
	    value: function getItem() {
	      return this.item;
	    }
	  }, {
	    key: "getQueryWords",
	    value: function getQueryWords() {
	      return this.queryWords;
	    }
	  }, {
	    key: "getMatchFields",
	    value: function getMatchFields() {
	      return this.matchFields;
	    }
	  }, {
	    key: "getSort",
	    value: function getSort() {
	      return this.sort;
	    }
	  }, {
	    key: "addIndex",
	    value: function addIndex(matchIndex) {
	      let matchField = this.matchFields.get(matchIndex.getField());
	      if (!matchField) {
	        matchField = new MatchField(matchIndex.getField());
	        this.matchFields.set(matchIndex.getField(), matchField);
	        const fieldSort = matchIndex.getField().getSort();
	        if (fieldSort !== null) {
	          this.sort = this.sort === null ? fieldSort : Math.min(this.sort, fieldSort);
	        }
	      }
	      matchField.addIndex(matchIndex);
	    }
	  }, {
	    key: "addIndexes",
	    value: function addIndexes(matchIndexes) {
	      matchIndexes.forEach(matchIndex => {
	        this.addIndex(matchIndex);
	      });
	    }
	  }]);
	  return MatchResult;
	}();

	const collator = new Intl.Collator(undefined, {
	  sensitivity: 'base'
	});
	let SearchEngine = /*#__PURE__*/function () {
	  function SearchEngine() {
	    babelHelpers.classCallCheck(this, SearchEngine);
	  }
	  babelHelpers.createClass(SearchEngine, null, [{
	    key: "matchItems",
	    value: function matchItems(items, searchQuery) {
	      const matchResults = [];
	      const queryWords = searchQuery.getQueryWords();
	      let limit = searchQuery.getResultLimit();
	      for (let i = 0; i < items.length; i++) {
	        if (limit === 0) {
	          break;
	        }
	        const item = items[i];
	        if (item.isSelected() || !item.isSearchable() || item.isHidden() || !item.getEntity().isSearchable()) {
	          continue;
	        }
	        const matchResult = this.matchItem(item, queryWords);
	        if (matchResult) {
	          matchResults.push(matchResult);
	          limit--;
	        }
	      }
	      return matchResults;
	    }
	  }, {
	    key: "matchItem",
	    value: function matchItem(item, queryWords) {
	      let matches = [];
	      for (let i = 0; i < queryWords.length; i++) {
	        const queryWord = queryWords[i];
	        const results = this.matchWord(item, queryWord);
	        //const match = this.matchWord(item, queryWord);
	        //if (match === null)
	        if (results.length === 0) {
	          return null;
	        } else {
	          matches = matches.concat(results);
	          //matches.push(match);
	        }
	      }

	      if (matches.length > 0) {
	        return new MatchResult(item, queryWords, matches);
	      } else {
	        return null;
	      }
	    }
	  }, {
	    key: "matchWord",
	    value: function matchWord(item, queryWord) {
	      const searchIndexes = item.getSearchIndex().getIndexes();
	      const matches = [];
	      for (let i = 0; i < searchIndexes.length; i++) {
	        const fieldIndex = searchIndexes[i];
	        const indexes = fieldIndex.getIndexes();
	        for (let j = 0; j < indexes.length; j++) {
	          const index = indexes[j];
	          const word = index.getWord().substring(0, queryWord.length);
	          if (collator.compare(queryWord, word) === 0) {
	            matches.push(new MatchIndex(fieldIndex.getField(), queryWord, index.getStartIndex()));
	            //return new MatchIndex(field, queryWord, index[i][1]);
	          }
	        }

	        if (matches.length > 0) {
	          break;
	        }
	      }
	      return matches;
	      //return null;
	    }
	  }]);
	  return SearchEngine;
	}();

	let SearchQuery = /*#__PURE__*/function () {
	  function SearchQuery(query) {
	    babelHelpers.classCallCheck(this, SearchQuery);
	    babelHelpers.defineProperty(this, "queryWords", []);
	    babelHelpers.defineProperty(this, "query", '');
	    babelHelpers.defineProperty(this, "cacheable", true);
	    babelHelpers.defineProperty(this, "dynamicSearchEntities", []);
	    babelHelpers.defineProperty(this, "resultLimit", 100);
	    this.query = query.trim().replace(/\s\s+/g, ' ');
	    this.queryWords = main_core.Type.isStringFilled(this.query) ? this.query.split(' ') : [];
	  }
	  babelHelpers.createClass(SearchQuery, [{
	    key: "getQueryWords",
	    value: function getQueryWords() {
	      return this.queryWords;
	    }
	  }, {
	    key: "getQuery",
	    value: function getQuery() {
	      return this.query;
	    }
	  }, {
	    key: "isEmpty",
	    value: function isEmpty() {
	      return this.getQueryWords().length === 0;
	    }
	  }, {
	    key: "setCacheable",
	    value: function setCacheable(flag) {
	      if (main_core.Type.isBoolean(flag)) {
	        this.cacheable = flag;
	      }
	    }
	  }, {
	    key: "isCacheable",
	    value: function isCacheable() {
	      return this.cacheable;
	    }
	  }, {
	    key: "setResultLimit",
	    value: function setResultLimit(limit) {
	      if (main_core.Type.isNumber(limit) && limit >= 0) {
	        this.resultLimit = limit;
	      }
	    }
	  }, {
	    key: "getResultLimit",
	    value: function getResultLimit() {
	      return this.resultLimit;
	    }
	  }, {
	    key: "hasDynamicSearch",
	    value: function hasDynamicSearch() {
	      return this.getDynamicSearchEntities().length > 0;
	    }
	  }, {
	    key: "hasDynamicSearchEntity",
	    value: function hasDynamicSearchEntity(entityId) {
	      return this.getDynamicSearchEntities().includes(entityId);
	    }
	  }, {
	    key: "setDynamicSearchEntities",
	    value: function setDynamicSearchEntities(entities) {
	      if (main_core.Type.isArrayFilled(entities)) {
	        entities.forEach(entityId => {
	          if (main_core.Type.isStringFilled(entityId) && !this.hasDynamicSearchEntity(entityId)) {
	            this.dynamicSearchEntities.push(entityId);
	          }
	        });
	      }
	      return this.dynamicSearchEntities;
	    }
	  }, {
	    key: "getDynamicSearchEntities",
	    value: function getDynamicSearchEntities() {
	      return this.dynamicSearchEntities;
	    }
	  }, {
	    key: "getAjaxJson",
	    value: function getAjaxJson() {
	      return this.toJSON();
	    }
	  }, {
	    key: "toJSON",
	    value: function toJSON() {
	      return {
	        queryWords: this.getQueryWords(),
	        query: this.getQuery(),
	        dynamicSearchEntities: this.getDynamicSearchEntities()
	      };
	    }
	  }]);
	  return SearchQuery;
	}();

	let _$9 = t => t,
	  _t$9,
	  _t2$4,
	  _t3$4,
	  _t4$4,
	  _t5$3;
	let SearchLoader = /*#__PURE__*/function () {
	  function SearchLoader(tab) {
	    babelHelpers.classCallCheck(this, SearchLoader);
	    babelHelpers.defineProperty(this, "tab", null);
	    babelHelpers.defineProperty(this, "loader", null);
	    babelHelpers.defineProperty(this, "cache", new main_core.Cache.MemoryCache());
	    this.tab = tab;
	  }
	  babelHelpers.createClass(SearchLoader, [{
	    key: "getTab",
	    value: function getTab() {
	      return this.tab;
	    }
	  }, {
	    key: "getLoader",
	    value: function getLoader() {
	      if (this.loader === null) {
	        this.loader = new main_loader.Loader({
	          target: this.getIconContainer(),
	          size: 32
	        });
	      }
	      return this.loader;
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      return this.cache.remember('container', () => {
	        return main_core.Tag.render(_t$9 || (_t$9 = _$9`
				<div class="ui-selector-search-loader">
					${0}
					${0}
				</div>
			`), this.getBoxContainer(), this.getSpacerContainer());
	      });
	    }
	  }, {
	    key: "getBoxContainer",
	    value: function getBoxContainer() {
	      return this.cache.remember('box-container', () => {
	        return main_core.Tag.render(_t2$4 || (_t2$4 = _$9`
				<div class="ui-selector-search-loader-box">
					${0}
					${0}
				</div>`), this.getIconContainer(), this.getTextContainer());
	      });
	    }
	  }, {
	    key: "getIconContainer",
	    value: function getIconContainer() {
	      return this.cache.remember('icon', () => {
	        return main_core.Tag.render(_t3$4 || (_t3$4 = _$9`<div class="ui-selector-search-loader-icon"></div>`));
	      });
	    }
	  }, {
	    key: "getTextContainer",
	    value: function getTextContainer() {
	      return this.cache.remember('text', () => {
	        return main_core.Tag.render(_t4$4 || (_t4$4 = _$9`
				<div class="ui-selector-search-loader-text">${0}</div>
			`), main_core.Loc.getMessage('UI_SELECTOR_SEARCH_LOADER_TEXT'));
	      });
	    }
	  }, {
	    key: "getSpacerContainer",
	    value: function getSpacerContainer() {
	      return this.cache.remember('spacer', () => {
	        return main_core.Tag.render(_t5$3 || (_t5$3 = _$9`<div class="ui-selector-search-loader-spacer"></div>`));
	      });
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      if (!this.getContainer().parentNode) {
	        main_core.Dom.append(this.getContainer(), this.getTab().getContainer());
	      }
	      void this.getLoader().show();
	      main_core.Dom.addClass(this.getContainer(), 'ui-selector-search-loader--show');
	      requestAnimationFrame(() => {
	        main_core.Dom.addClass(this.getContainer(), 'ui-selector-search-loader--animate');
	      });
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      if (this.loader === null) {
	        return;
	      }
	      main_core.Dom.removeClass(this.getContainer(), ['ui-selector-search-loader--show', 'ui-selector-search-loader--animate']);
	      void this.getLoader().hide();
	    }
	  }, {
	    key: "isShown",
	    value: function isShown() {
	      return this.loader !== null && this.loader.isShown();
	    }
	  }]);
	  return SearchLoader;
	}();

	let _$a = t => t,
	  _t$a,
	  _t2$5,
	  _t3$5,
	  _t4$5;
	let SearchTabFooter = /*#__PURE__*/function (_BaseFooter) {
	  babelHelpers.inherits(SearchTabFooter, _BaseFooter);
	  function SearchTabFooter(tab, options) {
	    var _this;
	    babelHelpers.classCallCheck(this, SearchTabFooter);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(SearchTabFooter).call(this, tab, options));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "loader", null);
	    _this.getDialog().subscribe('onSearch', _this.handleOnSearch.bind(babelHelpers.assertThisInitialized(_this)));
	    const tagSelector = _this.getDialog().getTagSelector();
	    if (tagSelector) {
	      tagSelector.subscribe('onMetaEnter', _this.handleMetaEnter.bind(babelHelpers.assertThisInitialized(_this)));
	    }
	    return _this;
	  }
	  babelHelpers.createClass(SearchTabFooter, [{
	    key: "render",
	    value: function render() {
	      return main_core.Tag.render(_t$a || (_t$a = _$a`
			<div class="ui-selector-search-footer" onclick="${0}">
				<div class="ui-selector-search-footer-box">
					${0}
					${0}
					${0}
				</div>
				<div class="ui-selector-search-footer-cmd">${0}</div>
			</div>
		`), this.handleClick.bind(this), this.getLabelContainer(), this.getQueryContainer(), this.getLoaderContainer(), main_core.Browser.isMac() ? '&#8984;+Enter' : 'Ctrl+Enter');
	    }
	  }, {
	    key: "getLoader",
	    value: function getLoader() {
	      if (this.loader === null) {
	        this.loader = new main_loader.Loader({
	          target: this.getLoaderContainer(),
	          size: 17,
	          color: 'rgba(82, 92, 105, 0.9)'
	        });
	      }
	      return this.loader;
	    }
	  }, {
	    key: "showLoader",
	    value: function showLoader() {
	      void this.getLoader().show();
	    }
	  }, {
	    key: "hideLoader",
	    value: function hideLoader() {
	      void this.getLoader().hide();
	    }
	  }, {
	    key: "setLabel",
	    value: function setLabel(label) {
	      if (main_core.Type.isString(label)) {
	        this.getLabelContainer().textContent = label;
	      }
	    }
	  }, {
	    key: "getLabelContainer",
	    value: function getLabelContainer() {
	      return this.cache.remember('label', () => {
	        return main_core.Tag.render(_t2$5 || (_t2$5 = _$a`
				<span class="ui-selector-search-footer-label">${0}</span>
			`), this.getOption('label', main_core.Loc.getMessage('UI_SELECTOR_CREATE_ITEM_LABEL')));
	      });
	    }
	  }, {
	    key: "getQueryContainer",
	    value: function getQueryContainer() {
	      return this.cache.remember('name-container', () => {
	        return main_core.Tag.render(_t3$5 || (_t3$5 = _$a`
				<span class="ui-selector-search-footer-query"></span>
			`));
	      });
	    }
	  }, {
	    key: "getLoaderContainer",
	    value: function getLoaderContainer() {
	      return this.cache.remember('loader', () => {
	        return main_core.Tag.render(_t4$5 || (_t4$5 = _$a`
				<div class="ui-selector-search-footer-loader"></div>
			`));
	      });
	    }
	  }, {
	    key: "createItem",
	    value: function createItem() {
	      const tagSelector = this.getDialog().getTagSelector();
	      if (tagSelector && tagSelector.isLocked()) {
	        return;
	      }
	      const finalize = () => {
	        this.hideLoader();
	        if (this.getDialog().getTagSelector()) {
	          this.getDialog().getTagSelector().unlock();
	          this.getDialog().focusSearch();
	        }
	      };
	      event.preventDefault();
	      this.showLoader();
	      if (tagSelector) {
	        tagSelector.lock();
	      }
	      this.getDialog().emitAsync('Search:onItemCreateAsync', {
	        searchQuery: this.getTab().getLastSearchQuery()
	      }).then(() => {
	        this.getTab().clearResults();
	        this.getDialog().clearSearch();
	        if (this.getDialog().getActiveTab() === this.getTab()) {
	          this.getDialog().selectFirstTab();
	        }
	        finalize();
	      }).catch(() => {
	        finalize();
	      });
	    }
	  }, {
	    key: "handleClick",
	    value: function handleClick() {
	      this.createItem();
	    }
	  }, {
	    key: "handleMetaEnter",
	    value: function handleMetaEnter(event) {
	      const keyboardEvent = event.getData().event;
	      keyboardEvent.stopPropagation();
	      if (this.getDialog().getActiveTab() !== this.getTab()) {
	        return;
	      }
	      this.handleClick();
	    }
	  }, {
	    key: "handleOnSearch",
	    value: function handleOnSearch(event) {
	      const {
	        query
	      } = event.getData();
	      this.getQueryContainer().textContent = query;
	    }
	  }]);
	  return SearchTabFooter;
	}(BaseFooter);

	let SearchTab = /*#__PURE__*/function (_Tab) {
	  babelHelpers.inherits(SearchTab, _Tab);
	  function SearchTab(dialog, tabOptions, searchOptions) {
	    var _this;
	    babelHelpers.classCallCheck(this, SearchTab);
	    const defaults = {
	      title: main_core.Loc.getMessage('UI_SELECTOR_SEARCH_TAB_TITLE'),
	      visible: false,
	      stub: true,
	      stubOptions: {
	        autoShow: false,
	        title: main_core.Loc.getMessage('UI_SELECTOR_SEARCH_STUB_TITLE'),
	        subtitle: main_core.Loc.getMessage('UI_SELECTOR_SEARCH_STUB_SUBTITLE_MSGVER_1')
	      }
	    };
	    const options = Object.assign({}, defaults, tabOptions);
	    options.id = 'search';
	    options.stubOptions.autoShow = false;
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(SearchTab).call(this, dialog, options));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "lastSearchQuery", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "queryCache", new Set());
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "queryXhr", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "searchLoader", new SearchLoader(babelHelpers.assertThisInitialized(_this)));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "allowCreateItem", false);
	    searchOptions = main_core.Type.isPlainObject(searchOptions) ? searchOptions : {};
	    _this.setAllowCreateItem(searchOptions.allowCreateItem, searchOptions.footerOptions);
	    _this.loadWithDebounce = main_core.Runtime.debounce(() => {
	      _this.load(_this.getLastSearchQuery());
	    }, 500);
	    return _this;
	  }
	  babelHelpers.createClass(SearchTab, [{
	    key: "search",
	    value: function search(query) {
	      const searchQuery = new SearchQuery(query);
	      const dynamicEntities = this.getDynamicEntities(searchQuery);
	      searchQuery.setDynamicSearchEntities(dynamicEntities);
	      if (searchQuery.isEmpty()) {
	        this.getSearchLoader().hide();
	        return;
	      }
	      this.lastSearchQuery = searchQuery;
	      const matchResults = SearchEngine.matchItems(this.getDialog().getItems(), searchQuery);
	      this.clearResults();
	      this.appendResults(matchResults);
	      if (this.getDialog().shouldFocusOnFirst()) {
	        this.getDialog().focusOnFirstNode();
	      }
	      if (this.shouldLoad(searchQuery)) {
	        this.loadWithDebounce();
	        if (!this.isEmptyResult()) {
	          this.getStub().hide();
	        }
	      } else {
	        if (!this.getSearchLoader().isShown()) {
	          this.toggleEmptyResult();
	        }
	      }
	    }
	  }, {
	    key: "getLastSearchQuery",
	    value: function getLastSearchQuery() {
	      return this.lastSearchQuery;
	    }
	  }, {
	    key: "setAllowCreateItem",
	    value: function setAllowCreateItem(flag, options) {
	      if (main_core.Type.isBoolean(flag)) {
	        this.allowCreateItem = flag;
	        if (flag) {
	          this.setFooter(SearchTabFooter, options);
	        } else {
	          this.setFooter(null);
	        }
	      }
	    }
	  }, {
	    key: "canCreateItem",
	    value: function canCreateItem() {
	      return this.allowCreateItem;
	    }
	  }, {
	    key: "appendResults",
	    value: function appendResults(matchResults) {
	      matchResults.sort((a, b) => {
	        const matchSortA = a.getSort();
	        const matchSortB = b.getSort();
	        if (matchSortA !== null && matchSortB !== null && matchSortA !== matchSortB) {
	          return matchSortA - matchSortB;
	        }
	        if (matchSortA !== null && matchSortB === null) {
	          return -1;
	        } else if (matchSortA === null && matchSortB !== null) {
	          return 1;
	        }
	        const contextSortA = a.getItem().getContextSort();
	        const contextSortB = b.getItem().getContextSort();
	        if (contextSortA !== null && contextSortB === null) {
	          return -1;
	        } else if (contextSortA === null && contextSortB !== null) {
	          return 1;
	        } else if (contextSortA !== null && contextSortB !== null) {
	          return contextSortB - contextSortA;
	        } else {
	          const globalSortA = a.getItem().getGlobalSort();
	          const globalSortB = b.getItem().getGlobalSort();
	          if (globalSortA !== null && globalSortB === null) {
	            return -1;
	          } else if (globalSortA === null && globalSortB !== null) {
	            return 1;
	          } else if (globalSortA !== null && globalSortB !== null) {
	            return globalSortB - globalSortA;
	          }
	          return 0;
	        }
	      });
	      this.getRootNode().disableRender();
	      matchResults.forEach(matchResult => {
	        const item = matchResult.getItem();
	        if (!this.getRootNode().hasItem(item)) {
	          const node = this.getRootNode().addItem(item);
	          node.setHighlights(matchResult.getMatchFields());
	        }
	      });
	      this.getRootNode().enableRender();
	      this.getRootNode().render(true);
	    }
	  }, {
	    key: "getDynamicEntities",
	    value: function getDynamicEntities(searchQuery) {
	      const result = [];
	      this.getDialog().getEntities().forEach(entity => {
	        if (entity.isSearchable()) {
	          const hasCacheLimit = entity.getSearchCacheLimits().some(pattern => {
	            return pattern.test(searchQuery.getQuery());
	          });
	          if (hasCacheLimit) {
	            result.push(entity.getId());
	          }
	        }
	      });
	      return result;
	    }
	  }, {
	    key: "isQueryCacheable",
	    value: function isQueryCacheable(searchQuery) {
	      return searchQuery.isCacheable() && !searchQuery.hasDynamicSearch();
	    }
	  }, {
	    key: "isQueryLoaded",
	    value: function isQueryLoaded(searchQuery) {
	      let found = false;
	      this.queryCache.forEach(query => {
	        if (found === false && searchQuery.getQuery().startsWith(query)) {
	          found = true;
	        }
	      });
	      return found;
	    }
	  }, {
	    key: "addCacheQuery",
	    value: function addCacheQuery(searchQuery) {
	      if (this.isQueryCacheable(searchQuery)) {
	        this.queryCache.add(searchQuery.getQuery());
	      }
	    }
	  }, {
	    key: "removeCacheQuery",
	    value: function removeCacheQuery(searchQuery) {
	      this.queryCache.delete(searchQuery.getQuery());
	    }
	  }, {
	    key: "shouldLoad",
	    value: function shouldLoad(searchQuery) {
	      if (!this.isQueryCacheable(searchQuery)) {
	        return true;
	      }
	      if (!this.getDialog().hasDynamicSearch()) {
	        return false;
	      }
	      return !this.isQueryLoaded(searchQuery);
	    }
	  }, {
	    key: "load",
	    value: function load(searchQuery) {
	      if (!this.shouldLoad(searchQuery)) {
	        return;
	      }

	      // if (this.queryXhr)
	      // {
	      // 	this.queryXhr.abort();
	      // }

	      this.addCacheQuery(searchQuery);
	      this.getStub().hide();
	      this.getSearchLoader().show();
	      main_core.ajax.runAction('ui.entityselector.doSearch', {
	        json: {
	          dialog: this.getDialog().getAjaxJson(),
	          searchQuery: searchQuery.getAjaxJson()
	        },
	        onrequeststart: xhr => {
	          this.queryXhr = xhr;
	        },
	        getParameters: {
	          context: this.getDialog().getContext()
	        }
	      }).then(response => {
	        this.getSearchLoader().hide();
	        if (!response || !response.data || !response.data.dialog || !response.data.dialog.items) {
	          this.removeCacheQuery(searchQuery);
	          this.toggleEmptyResult();
	          this.getDialog().emit('SearchTab:onLoad', {
	            searchTab: this
	          });
	          return;
	        }
	        if (response.data.searchQuery && response.data.searchQuery.cacheable === false) {
	          var _this$getLastSearchQu;
	          this.removeCacheQuery(searchQuery);
	          if (((_this$getLastSearchQu = this.getLastSearchQuery()) === null || _this$getLastSearchQu === void 0 ? void 0 : _this$getLastSearchQu.getQuery()) !== searchQuery.getQuery()) {
	            this.loadWithDebounce();
	          }
	        }
	        if (main_core.Type.isArrayFilled(response.data.dialog.items)) {
	          const items = new Set();
	          response.data.dialog.items.forEach(itemOptions => {
	            delete itemOptions.tabs;
	            delete itemOptions.children;
	            const item = this.getDialog().addItem(itemOptions);
	            items.add(item);
	          });
	          const isTabEmpty = this.isEmptyResult();
	          const matchResults = SearchEngine.matchItems(Array.from(items.values()), this.getLastSearchQuery());
	          this.appendResults(matchResults);
	          if (isTabEmpty && this.getDialog().shouldFocusOnFirst()) {
	            this.getDialog().focusOnFirstNode();
	          }
	        }
	        this.toggleEmptyResult();
	        this.getDialog().emit('SearchTab:onLoad', {
	          searchTab: this
	        });
	      }).catch(error => {
	        this.removeCacheQuery(searchQuery);
	        this.getSearchLoader().hide();
	        this.toggleEmptyResult();
	        console.error(error);
	      });
	    }
	  }, {
	    key: "getSearchLoader",
	    value: function getSearchLoader() {
	      return this.searchLoader;
	    }
	  }, {
	    key: "clearResults",
	    value: function clearResults() {
	      this.getRootNode().removeChildren();
	    }
	  }, {
	    key: "isEmptyResult",
	    value: function isEmptyResult() {
	      return !this.getRootNode().hasChildren();
	    }
	  }, {
	    key: "toggleEmptyResult",
	    value: function toggleEmptyResult() {
	      if (this.isEmptyResult()) {
	        this.getStub().show();
	      } else {
	        this.getStub().hide();
	      }
	    }
	  }]);
	  return SearchTab;
	}(Tab);

	let _$b = t => t,
	  _t$b,
	  _t2$6,
	  _t3$6,
	  _t4$6,
	  _t5$4,
	  _t6$2,
	  _t7$1;
	let LoadState = function LoadState() {
	  babelHelpers.classCallCheck(this, LoadState);
	};
	babelHelpers.defineProperty(LoadState, "UNSENT", 'UNSENT');
	babelHelpers.defineProperty(LoadState, "LOADING", 'LOADING');
	babelHelpers.defineProperty(LoadState, "DONE", 'DONE');
	let TagSelectorMode = function TagSelectorMode() {
	  babelHelpers.classCallCheck(this, TagSelectorMode);
	};
	babelHelpers.defineProperty(TagSelectorMode, "INSIDE", 'INSIDE');
	babelHelpers.defineProperty(TagSelectorMode, "OUTSIDE", 'OUTSIDE');
	const instances = new Map();

	/**
	 * @memberof BX.UI.EntitySelector
	 */
	let Dialog = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Dialog, _EventEmitter);
	  babelHelpers.createClass(Dialog, null, [{
	    key: "getById",
	    value: function getById(id) {
	      return instances.get(id) || null;
	    }
	  }, {
	    key: "getInstances",
	    value: function getInstances() {
	      return Array.from(instances.values());
	    }
	  }]);
	  function Dialog(dialogOptions) {
	    var _this;
	    babelHelpers.classCallCheck(this, Dialog);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Dialog).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "id", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "items", new Map());
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "tabs", new Map());
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "entities", new Map());
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "targetNode", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "popup", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "cache", new main_core.Cache.MemoryCache());
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "multiple", true);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "hideOnSelect", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "hideOnDeselect", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "addTagOnSelect", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "clearSearchOnSelect", true);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "context", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "selectedItems", new Set());
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "preselectedItems", []);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "undeselectedItems", []);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "dropdownMode", false);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "frozen", false);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "frozenProps", {});
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "hideByEsc", true);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "autoHide", true);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "autoHideHandler", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "offsetTop", 5);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "offsetLeft", 0);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "cacheable", true);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "width", 565);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "height", 420);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "maxLabelWidth", 160);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "minLabelWidth", 45);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "alwaysShowLabels", false);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "showAvatars", true);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "compactView", false);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "activeTab", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "recentTab", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "searchTab", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "rendered", false);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "loadState", LoadState.UNSENT);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "loader", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "tagSelector", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "tagSelectorMode", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "tagSelectorHeight", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "saveRecentItemsWithDebounce", main_core.Runtime.debounce(_this.saveRecentItems, 2000, babelHelpers.assertThisInitialized(_this)));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "recentItemsToSave", []);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "recentItemsLimit", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "navigation", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "header", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "footer", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "popupOptions", {});
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "focusOnFirst", true);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "focusedNode", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "clearUnavailableItems", false);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "overlappingObserver", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "offsetAnimation", true);
	    _this.setEventNamespace('BX.UI.EntitySelector.Dialog');
	    const options = main_core.Type.isPlainObject(dialogOptions) ? dialogOptions : {};
	    _this.id = main_core.Type.isStringFilled(options.id) ? options.id : `ui-selector-${main_core.Text.getRandom().toLowerCase()}`;
	    _this.multiple = main_core.Type.isBoolean(options.multiple) ? options.multiple : true;
	    _this.context = main_core.Type.isStringFilled(options.context) ? options.context : null;
	    _this.clearUnavailableItems = options.clearUnavailableItems === true;
	    _this.compactView = options.compactView === true;
	    _this.dropdownMode = main_core.Type.isBoolean(options.dropdownMode) ? options.dropdownMode : false;
	    _this.alwaysShowLabels = main_core.Type.isBoolean(options.alwaysShowLabels) ? options.alwaysShowLabels : false;
	    if (main_core.Type.isArray(options.entities)) {
	      options.entities.forEach(entity => {
	        _this.addEntity(entity);
	      });
	    }
	    if (options.tagSelector instanceof TagSelector) {
	      _this.tagSelectorMode = TagSelectorMode.OUTSIDE;
	      _this.setTagSelector(options.tagSelector);
	    } else if (options.enableSearch === true) {
	      const defaultOptions = {
	        placeholder: main_core.Loc.getMessage('UI_TAG_SELECTOR_SEARCH_PLACEHOLDER'),
	        maxHeight: 102,
	        // three lines
	        textBoxWidth: 105
	      };
	      const customOptions = main_core.Type.isPlainObject(options.tagSelectorOptions) ? options.tagSelectorOptions : {};
	      const mandatoryOptions = {
	        dialogOptions: null,
	        showTextBox: true,
	        showAddButton: false,
	        showCreateButton: false,
	        multiple: _this.isMultiple()
	      };
	      const tagSelectorOptions = Object.assign(defaultOptions, customOptions, mandatoryOptions);
	      const tagSelector = new TagSelector(tagSelectorOptions);
	      _this.tagSelectorMode = TagSelectorMode.INSIDE;
	      _this.setTagSelector(tagSelector);
	    }
	    _this.setTargetNode(options.targetNode);
	    _this.setHideOnSelect(options.hideOnSelect);
	    _this.setHideOnDeselect(options.hideOnDeselect);
	    _this.setAddTagOnSelect(options.addTagOnSelect);
	    _this.setClearSearchOnSelect(options.clearSearchOnSelect);
	    _this.setWidth(options.width);
	    void _this.setHeight(options.height);
	    _this.setAutoHide(options.autoHide);
	    _this.setAutoHideHandler(options.autoHideHandler);
	    _this.setHideByEsc(options.hideByEsc);
	    _this.setOffsetLeft(options.offsetLeft);
	    _this.setOffsetTop(options.offsetTop);
	    _this.setCacheable(options.cacheable);
	    _this.setFocusOnFirst(options.focusOnFirst);
	    _this.setShowAvatars(options.showAvatars);
	    _this.setRecentItemsLimit(options.recentItemsLimit);
	    _this.setOffsetAnimation(options.offsetAnimation);
	    _this.recentTab = new RecentTab(babelHelpers.assertThisInitialized(_this), options.recentTabOptions);
	    _this.searchTab = new SearchTab(babelHelpers.assertThisInitialized(_this), options.searchTabOptions, options.searchOptions);
	    _this.addTab(_this.recentTab);
	    _this.addTab(_this.searchTab);
	    _this.setPreselectedItems(options.preselectedItems);
	    _this.setUndeselectedItems(options.undeselectedItems);
	    _this.setOptions(options);
	    const preload = options.preload === true || _this.getPreselectedItems().length > 0;
	    if (preload) {
	      _this.load();
	    }
	    if (main_core.Type.isPlainObject(options.popupOptions)) {
	      const allowedOptions = new Set(['overlay', 'bindOptions', 'targetContainer', 'zIndexOptions', 'events', 'animation', 'className']);
	      const popupOptions = {};
	      Object.keys(options.popupOptions).forEach(option => {
	        if (allowedOptions.has(option)) {
	          popupOptions[option] = options.popupOptions[option];
	        }
	      });
	      _this.popupOptions = popupOptions;
	    }
	    _this.navigation = new Navigation(babelHelpers.assertThisInitialized(_this));
	    new SliderIntegration(babelHelpers.assertThisInitialized(_this));
	    _this.subscribe('ItemNode:onFocus', _this.handleItemNodeFocus.bind(babelHelpers.assertThisInitialized(_this)));
	    _this.subscribe('ItemNode:onUnfocus', _this.handleItemNodeUnfocus.bind(babelHelpers.assertThisInitialized(_this)));
	    _this.subscribeFromOptions(options.events);
	    instances.set(_this.id, babelHelpers.assertThisInitialized(_this));
	    return _this;
	  }
	  babelHelpers.createClass(Dialog, [{
	    key: "show",
	    value: function show() {
	      this.load();
	      this.getPopup().show();
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      this.getPopup().close();
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      if (this.destroying) {
	        return;
	      }
	      this.destroying = true;
	      this.emit('onDestroy');
	      this.disconnectTabOverlapping();
	      instances.delete(this.getId());
	      if (this.isRendered()) {
	        this.getPopup().destroy();
	      }
	      for (const property in this) {
	        if (this.hasOwnProperty(property)) {
	          delete this[property];
	        }
	      }
	      Object.setPrototypeOf(this, null);
	    }
	  }, {
	    key: "isOpen",
	    value: function isOpen() {
	      return this.popup && this.popup.isShown();
	    }
	  }, {
	    key: "adjustPosition",
	    value: function adjustPosition() {
	      if (this.isRendered()) {
	        this.getPopup().adjustPosition();
	      }
	    }
	  }, {
	    key: "search",
	    value: function search(queryString) {
	      const query = main_core.Type.isStringFilled(queryString) ? queryString.trim() : '';
	      const event = new main_core_events.BaseEvent({
	        data: {
	          query
	        }
	      });
	      this.emit('onBeforeSearch', event);
	      if (event.isDefaultPrevented()) {
	        return;
	      }
	      if (!main_core.Type.isStringFilled(query)) {
	        this.selectFirstTab();
	        if (this.getSearchTab()) {
	          this.getSearchTab().clearResults();
	        }
	      } else if (this.getSearchTab()) {
	        this.selectTab(this.getSearchTab().getId());
	        this.getSearchTab().search(query);
	      }
	      this.emit('onSearch', {
	        query
	      });
	    }
	  }, {
	    key: "addItem",
	    value: function addItem(options) {
	      if (!main_core.Type.isPlainObject(options)) {
	        throw new Error('EntitySelector.addItem: wrong item options.');
	      }
	      let item = this.getItem(options);
	      if (!item) {
	        item = new Item(options);
	        const undeselectable = this.getUndeselectedItems().some(itemId => {
	          return itemId[0] === item.getEntityId() && String(itemId[1]) === String(item.getId());
	        });
	        if (undeselectable) {
	          item.setDeselectable(false);
	        }
	        item.setDialog(this);
	        const entity = this.getEntity(item.getEntityId());
	        if (entity === null) {
	          this.addEntity({
	            id: item.getEntityId()
	          });
	        }
	        let entityItems = this.items.get(item.getEntityId());
	        if (!entityItems) {
	          entityItems = new Map();
	          this.items.set(item.getEntityId(), entityItems);
	        }
	        entityItems.set(String(item.getId()), item);
	        if (item.isSelected()) {
	          this.handleItemSelect(item);
	        }
	      }
	      let tabs = [];
	      if (main_core.Type.isArray(options.tabs)) {
	        tabs = options.tabs;
	      } else if (main_core.Type.isStringFilled(options.tabs)) {
	        tabs = [options.tabs];
	      }
	      const children = main_core.Type.isArray(options.children) ? options.children : [];
	      tabs.forEach(tabId => {
	        const tab = this.getTab(tabId);
	        if (tab) {
	          const itemNode = tab.getRootNode().addItem(item, options.nodeOptions);
	          itemNode.addChildren(children);
	        }
	      });
	      return item;
	    }
	  }, {
	    key: "removeItem",
	    value: function removeItem(item) {
	      item = this.getItem(item);
	      if (item) {
	        this.handleItemDeselect(item);
	        item.getNodes().forEach(node => {
	          node.getParentNode().removeChild(node);
	        });
	        const entityItems = this.getEntityItemsInternal(item.getEntityId());
	        if (entityItems) {
	          entityItems.delete(String(item.getId()));
	          if (entityItems.size === 0) {
	            this.items.delete(item.getEntityId());
	          }
	        }
	      }
	      return item;
	    }
	  }, {
	    key: "removeItems",
	    value: function removeItems() {
	      this.getItemsInternal().forEach(items => {
	        items.forEach(item => {
	          this.removeItem(item);
	        });
	      });
	    }
	  }, {
	    key: "getItem",
	    value: function getItem(item) {
	      let id = null;
	      let entityId = null;
	      if (main_core.Type.isArray(item) && item.length === 2) {
	        [entityId, id] = item;
	      } else if (item instanceof Item) {
	        id = item.getId();
	        entityId = item.getEntityId();
	      } else if (main_core.Type.isObjectLike(item)) {
	        ({
	          id,
	          entityId
	        } = item);
	      }
	      const entityItems = this.getEntityItemsInternal(entityId);
	      if (entityItems) {
	        return entityItems.get(String(id)) || null;
	      }
	      return null;
	    }
	  }, {
	    key: "getSelectedItems",
	    value: function getSelectedItems() {
	      return Array.from(this.selectedItems);
	    }
	  }, {
	    key: "getItems",
	    value: function getItems() {
	      const items = [];
	      this.getItemsInternal().forEach(entityItems => {
	        Array.prototype.push.apply(items, Array.from(entityItems.values()));
	      });
	      return items;
	    }
	    /**
	     * @internal
	     */
	  }, {
	    key: "getItemsInternal",
	    value: function getItemsInternal() {
	      return this.items;
	    }
	  }, {
	    key: "getEntityItems",
	    value: function getEntityItems(entityId) {
	      const items = this.getEntityItemsInternal(entityId);
	      return items === null ? [] : Array.from(items.values());
	    }
	    /**
	     * @internal
	     */
	  }, {
	    key: "getEntityItemsInternal",
	    value: function getEntityItemsInternal(entityId) {
	      return this.items.get(entityId) || null;
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "validateItemIds",
	    value: function validateItemIds(itemIds) {
	      if (!main_core.Type.isArrayFilled(itemIds)) {
	        return [];
	      }
	      const result = [];
	      itemIds.forEach(itemId => {
	        if (!main_core.Type.isArray(itemId) || itemId.length !== 2) {
	          return;
	        }
	        const [entityId, id] = itemId;
	        if (main_core.Type.isStringFilled(entityId) && (main_core.Type.isStringFilled(id) || main_core.Type.isNumber(id))) {
	          result.push(itemId);
	        }
	      });
	      return result;
	    }
	  }, {
	    key: "addTab",
	    value: function addTab(tab) {
	      if (main_core.Type.isPlainObject(tab)) {
	        tab = new Tab(this, tab);
	      }
	      if (!(tab instanceof Tab)) {
	        throw new Error('EntitySelector: a tab must be an instance of EntitySelector.Tab.');
	      }
	      if (this.getTab(tab.getId())) {
	        console.error(`EntitySelector: the "${tab.getId()}" tab is already existed.`);
	        return tab;
	      }
	      tab.setDialog(this);
	      this.tabs.set(tab.getId(), tab);
	      if (this.isRendered()) {
	        this.insertTab(tab);
	      }
	      return tab;
	    }
	  }, {
	    key: "getTabs",
	    value: function getTabs() {
	      return Array.from(this.tabs.values());
	    }
	  }, {
	    key: "getTab",
	    value: function getTab(id) {
	      return this.tabs.get(id) || null;
	    }
	  }, {
	    key: "getRecentTab",
	    value: function getRecentTab() {
	      return this.recentTab;
	    }
	  }, {
	    key: "getSearchTab",
	    value: function getSearchTab() {
	      return this.searchTab;
	    }
	  }, {
	    key: "selectTab",
	    value: function selectTab(id) {
	      const newActiveTab = this.getTab(id);
	      if (!newActiveTab || newActiveTab === this.getActiveTab()) {
	        return newActiveTab;
	      }
	      if (this.getActiveTab()) {
	        this.getActiveTab().deselect();
	      }
	      this.activeTab = newActiveTab;
	      newActiveTab.select();
	      if (!newActiveTab.isRendered()) {
	        newActiveTab.render();
	      }
	      requestAnimationFrame(() => {
	        requestAnimationFrame(() => {
	          this.focusSearch();
	        });
	      });
	      this.clearNodeFocus();
	      if (this.shouldFocusOnFirst()) {
	        this.focusOnFirstNode();
	      }
	      this.adjustHeader();
	      this.adjustFooter();
	      return newActiveTab;
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "insertTab",
	    value: function insertTab(tab) {
	      tab.renderLabel();
	      tab.renderContainer();
	      main_core.Dom.append(tab.getLabelContainer(), this.getLabelsContainer());
	      main_core.Dom.append(tab.getContainer(), this.getTabContentsContainer());
	      if (tab.getHeader()) {
	        main_core.Dom.append(tab.getHeader().getContainer(), this.getHeaderContainer());
	      }
	      if (tab.getFooter()) {
	        main_core.Dom.append(tab.getFooter().getContainer(), this.getFooterContainer());
	      }
	    }
	  }, {
	    key: "selectFirstTab",
	    value: function selectFirstTab(onlyVisible = true) {
	      const tabs = this.getTabs();
	      for (let i = 0; i < tabs.length; i++) {
	        const tab = tabs[i];
	        if (onlyVisible === false || tab.isVisible()) {
	          return this.selectTab(tab.getId());
	        }
	      }
	      if (this.isDropdownMode()) {
	        return this.selectTab(this.getRecentTab().getId());
	      }
	      return null;
	    }
	  }, {
	    key: "selectLastTab",
	    value: function selectLastTab(onlyVisible = true) {
	      const tabs = this.getTabs();
	      for (let i = tabs.length - 1; i >= 0; i--) {
	        const tab = tabs[i];
	        if (onlyVisible === false || tab.isVisible()) {
	          return this.selectTab(tab.getId());
	        }
	      }
	      if (this.isDropdownMode()) {
	        return this.selectTab(this.getRecentTab().getId());
	      }
	      return null;
	    }
	  }, {
	    key: "getActiveTab",
	    value: function getActiveTab() {
	      return this.activeTab;
	    }
	  }, {
	    key: "getNextTab",
	    value: function getNextTab(onlyVisible = true) {
	      let nextTab = null;
	      let activeFound = false;
	      const tabs = this.getTabs();
	      for (let i = 0; i < tabs.length; i++) {
	        const tab = tabs[i];
	        if (onlyVisible && !tab.isVisible()) {
	          continue;
	        }
	        if (tab === this.getActiveTab()) {
	          activeFound = true;
	        } else if (activeFound) {
	          nextTab = tab;
	          break;
	        }
	      }
	      return nextTab;
	    }
	  }, {
	    key: "getPreviousTab",
	    value: function getPreviousTab(onlyVisible = true) {
	      let previousTab = null;
	      let activeFound = false;
	      const tabs = this.getTabs();
	      for (let i = tabs.length - 1; i >= 0; i--) {
	        const tab = tabs[i];
	        if (onlyVisible && !tab.isVisible()) {
	          continue;
	        }
	        if (tab === this.getActiveTab()) {
	          activeFound = true;
	        } else if (activeFound) {
	          previousTab = tab;
	          break;
	        }
	      }
	      return previousTab;
	    }
	  }, {
	    key: "removeTab",
	    value: function removeTab(id) {
	      const tab = this.getTab(id);
	      if (!tab) {
	        return;
	      }
	      tab.getRootNode().removeChildren();
	      this.tabs.delete(id);
	      main_core.Dom.remove(tab.getLabelContainer(), this.getLabelsContainer());
	      main_core.Dom.remove(tab.getContainer(), this.getTabContentsContainer());
	      if (tab.getHeader()) {
	        main_core.Dom.remove(tab.getHeader().getContainer(), this.getHeaderContainer());
	      }
	      if (tab.getFooter()) {
	        main_core.Dom.remove(tab.getFooter().getContainer(), this.getFooterContainer());
	      }
	      this.selectFirstTab();
	    }
	  }, {
	    key: "addEntity",
	    value: function addEntity(entity) {
	      if (main_core.Type.isPlainObject(entity)) {
	        entity = new Entity(entity);
	      }
	      if (!(entity instanceof Entity)) {
	        throw new Error('EntitySelector: an entity must be an instance of EntitySelector.Entity.');
	      }
	      if (this.hasEntity(entity.getId())) {
	        console.error(`EntitySelector: the "${entity.getId()}" entity is already existed.`);
	        return entity;
	      }
	      this.entities.set(entity.getId(), entity);
	      return entity;
	    }
	  }, {
	    key: "getEntity",
	    value: function getEntity(id) {
	      return this.entities.get(id) || null;
	    }
	  }, {
	    key: "hasEntity",
	    value: function hasEntity(id) {
	      return this.entities.has(id);
	    }
	  }, {
	    key: "getEntities",
	    value: function getEntities() {
	      return Array.from(this.entities.values());
	    }
	  }, {
	    key: "removeEntity",
	    value: function removeEntity(id) {
	      this.removeEntityItems(id);
	      this.entities.delete(id);
	    }
	  }, {
	    key: "removeEntityItems",
	    value: function removeEntityItems(id) {
	      const items = this.getEntityItemsInternal(id);
	      if (items) {
	        items.forEach(item => {
	          this.removeItem(item);
	        });
	      }
	    }
	  }, {
	    key: "getHeader",
	    value: function getHeader() {
	      return this.header;
	    }
	  }, {
	    key: "getActiveHeader",
	    value: function getActiveHeader() {
	      if (!this.getActiveTab()) {
	        return null;
	      }
	      if (this.getActiveTab().getHeader()) {
	        return this.getActiveTab().getHeader();
	      }
	      return this.getHeader() && this.getActiveTab().canShowDefaultHeader() ? this.getHeader() : null;
	    }
	    /**
	     * @internal
	     */
	  }, {
	    key: "adjustHeader",
	    value: function adjustHeader() {
	      if (!this.getActiveTab()) {
	        return;
	      }
	      if (this.getActiveTab().getHeader()) {
	        if (this.getHeader()) {
	          this.getHeader().hide();
	        }
	        this.getActiveTab().getHeader().show();
	      } else {
	        if (this.getHeader()) {
	          if (this.getActiveTab().canShowDefaultHeader()) {
	            this.getHeader().show();
	          } else {
	            this.getHeader().hide();
	          }
	        }
	      }
	    }
	  }, {
	    key: "setHeader",
	    value: function setHeader(headerContent, headerOptions) {
	      /** @var {BaseHeader} */
	      let header = null;
	      if (headerContent !== null) {
	        header = this.constructor.createHeader(this, headerContent, headerOptions);
	        if (header === null) {
	          return null;
	        }
	      }
	      if (this.isRendered() && this.getHeader() !== null) {
	        main_core.Dom.remove(this.getHeader().getContainer());
	        this.adjustHeader();
	      }
	      this.header = header;
	      if (this.isRendered()) {
	        this.appendHeader(header);
	        this.adjustHeader();
	      }
	      return header;
	    }
	    /**
	     * @internal
	     */
	  }, {
	    key: "appendHeader",
	    value: function appendHeader(header) {
	      if (header instanceof BaseHeader) {
	        main_core.Dom.append(header.getContainer(), this.getHeaderContainer());
	      }
	    }
	    /**
	     * @internal
	     */
	  }, {
	    key: "getFooter",
	    value: function getFooter() {
	      return this.footer;
	    }
	  }, {
	    key: "getActiveFooter",
	    value: function getActiveFooter() {
	      if (!this.getActiveTab()) {
	        return null;
	      }
	      if (this.getActiveTab().getFooter()) {
	        return this.getActiveTab().getFooter();
	      }
	      return this.getFooter() && this.getActiveTab().canShowDefaultFooter() ? this.getFooter() : null;
	    }
	    /**
	     * @internal
	     */
	  }, {
	    key: "adjustFooter",
	    value: function adjustFooter() {
	      if (!this.getActiveTab()) {
	        return;
	      }
	      if (this.getActiveTab().getFooter()) {
	        if (this.getFooter()) {
	          this.getFooter().hide();
	        }
	        this.getActiveTab().getFooter().show();
	      } else {
	        if (this.getFooter()) {
	          if (this.getActiveTab().canShowDefaultFooter()) {
	            this.getFooter().show();
	          } else {
	            this.getFooter().hide();
	          }
	        }
	      }
	    }
	  }, {
	    key: "setFooter",
	    value: function setFooter(footerContent, footerOptions) {
	      /** @var {BaseFooter} */
	      let footer = null;
	      if (footerContent !== null) {
	        footer = this.constructor.createFooter(this, footerContent, footerOptions);
	        if (footer === null) {
	          return null;
	        }
	      }
	      if (this.isRendered() && this.getFooter() !== null) {
	        main_core.Dom.remove(this.getFooter().getContainer());
	        this.adjustFooter();
	      }
	      this.footer = footer;
	      if (this.isRendered()) {
	        this.appendFooter(footer);
	        this.adjustFooter();
	      }
	      return footer;
	    }
	    /**
	     * @internal
	     */
	  }, {
	    key: "appendFooter",
	    value: function appendFooter(footer) {
	      if (footer instanceof BaseFooter) {
	        main_core.Dom.append(footer.getContainer(), this.getFooterContainer());
	      }
	    }
	    /**
	     * @internal
	     */
	  }, {
	    key: "getId",
	    value: function getId() {
	      return this.id;
	    }
	  }, {
	    key: "getContext",
	    value: function getContext() {
	      return this.context;
	    }
	  }, {
	    key: "getNavigation",
	    value: function getNavigation() {
	      return this.navigation;
	    }
	  }, {
	    key: "deselectAll",
	    value: function deselectAll() {
	      this.getSelectedItems().forEach(item => {
	        item.deselect();
	      });
	    }
	  }, {
	    key: "isMultiple",
	    value: function isMultiple() {
	      return this.multiple;
	    }
	  }, {
	    key: "setTargetNode",
	    value: function setTargetNode(node) {
	      if (!main_core.Type.isDomNode(node) && !main_core.Type.isNull(node) && !main_core.Type.isObject(node)) {
	        return;
	      }
	      this.targetNode = node;
	      if (this.isRendered()) {
	        this.getPopup().setBindElement(this.targetNode);
	        this.getPopup().adjustPosition();
	      }
	    }
	  }, {
	    key: "getTargetNode",
	    value: function getTargetNode() {
	      if (this.targetNode === null) {
	        if (this.getTagSelectorMode() === TagSelectorMode.OUTSIDE) {
	          return this.getTagSelector().getOuterContainer();
	        }
	      }
	      return this.targetNode;
	    }
	  }, {
	    key: "setHideOnSelect",
	    value: function setHideOnSelect(flag) {
	      if (main_core.Type.isBoolean(flag)) {
	        this.hideOnSelect = flag;
	      }
	    }
	  }, {
	    key: "shouldHideOnSelect",
	    value: function shouldHideOnSelect() {
	      if (this.hideOnSelect !== null) {
	        return this.hideOnSelect;
	      }
	      return !this.isMultiple();
	    }
	  }, {
	    key: "setHideOnDeselect",
	    value: function setHideOnDeselect(flag) {
	      if (main_core.Type.isBoolean(flag)) {
	        this.hideOnDeselect = flag;
	      }
	    }
	  }, {
	    key: "shouldHideOnDeselect",
	    value: function shouldHideOnDeselect() {
	      if (this.hideOnDeselect !== null) {
	        return this.hideOnDeselect;
	      }
	      return false;
	    }
	  }, {
	    key: "setClearSearchOnSelect",
	    value: function setClearSearchOnSelect(flag) {
	      if (main_core.Type.isBoolean(flag)) {
	        this.clearSearchOnSelect = flag;
	      }
	    }
	  }, {
	    key: "shouldClearSearchOnSelect",
	    value: function shouldClearSearchOnSelect() {
	      return this.clearSearchOnSelect;
	    }
	  }, {
	    key: "setAddTagOnSelect",
	    value: function setAddTagOnSelect(flag) {
	      if (main_core.Type.isBoolean(flag)) {
	        this.addTagOnSelect = flag;
	      }
	    }
	  }, {
	    key: "shouldAddTagOnSelect",
	    value: function shouldAddTagOnSelect() {
	      if (this.addTagOnSelect !== null) {
	        return this.addTagOnSelect;
	      }
	      return this.isMultiple() || this.isTagSelectorOutside();
	    }
	  }, {
	    key: "setShowAvatars",
	    value: function setShowAvatars(flag) {
	      if (main_core.Type.isBoolean(flag)) {
	        this.showAvatars = flag;
	        if (this.isRendered()) {
	          this.getTabs().forEach(tab => {
	            tab.renderContainer();
	          });
	        }
	      }
	    }
	  }, {
	    key: "shouldShowAvatars",
	    value: function shouldShowAvatars() {
	      return this.showAvatars;
	    }
	  }, {
	    key: "setRecentItemsLimit",
	    value: function setRecentItemsLimit(recentItemsLimit) {
	      if (main_core.Type.isNumber(recentItemsLimit) && recentItemsLimit > 0) {
	        this.recentItemsLimit = recentItemsLimit;
	      }
	    }
	  }, {
	    key: "getRecentItemsLimit",
	    value: function getRecentItemsLimit() {
	      return this.recentItemsLimit;
	    }
	  }, {
	    key: "setOffsetAnimation",
	    value: function setOffsetAnimation(flag) {
	      if (main_core.Type.isBoolean(flag)) {
	        this.offsetAnimation = flag;
	        if (this.isRendered() && !this.offsetAnimation) {
	          main_core.Dom.removeClass(this.getPopup().getPopupContainer(), 'ui-selector-popup-offset-animation');
	        }
	      }
	    }
	  }, {
	    key: "isCompactView",
	    value: function isCompactView() {
	      return this.compactView;
	    }
	  }, {
	    key: "setAutoHide",
	    value: function setAutoHide(enable) {
	      if (main_core.Type.isBoolean(enable)) {
	        this.autoHide = enable;
	        if (this.isRendered()) {
	          this.getPopup().setAutoHide(enable);
	        }
	      }
	    }
	  }, {
	    key: "isAutoHide",
	    value: function isAutoHide() {
	      return this.autoHide;
	    }
	  }, {
	    key: "setAutoHideHandler",
	    value: function setAutoHideHandler(handler) {
	      if (main_core.Type.isFunction(handler) || handler === null) {
	        this.autoHideHandler = handler;
	      }
	    }
	  }, {
	    key: "setHideByEsc",
	    value: function setHideByEsc(enable) {
	      if (main_core.Type.isBoolean(enable)) {
	        this.hideByEsc = enable;
	        if (this.isRendered()) {
	          this.getPopup().setClosingByEsc(enable);
	        }
	      }
	    }
	  }, {
	    key: "shouldHideByEsc",
	    value: function shouldHideByEsc() {
	      return this.hideByEsc;
	    }
	  }, {
	    key: "getWidth",
	    value: function getWidth() {
	      return this.width;
	    }
	  }, {
	    key: "setWidth",
	    value: function setWidth(width) {
	      if (main_core.Type.isNumber(width) && width > 0) {
	        this.width = width;
	        if (this.isRendered()) {
	          main_core.Dom.style(this.getContainer(), 'width', `${width}px`);
	        }
	      }
	    }
	  }, {
	    key: "getHeight",
	    value: function getHeight() {
	      return this.height;
	    }
	  }, {
	    key: "setHeight",
	    value: function setHeight(height) {
	      if (main_core.Type.isNumber(height) && height > 0) {
	        this.height = height;
	        if (this.isRendered()) {
	          main_core.Dom.style(this.getContainer(), 'height', `${height}px`);
	          return Animation.handleTransitionEnd(this.getContainer(), 'height');
	        } else {
	          return Promise.resolve();
	        }
	      } else {
	        return Promise.resolve();
	      }
	    }
	  }, {
	    key: "getOffsetLeft",
	    value: function getOffsetLeft() {
	      return this.offsetLeft;
	    }
	  }, {
	    key: "setOffsetLeft",
	    value: function setOffsetLeft(offset) {
	      if (main_core.Type.isNumber(offset) && offset >= 0) {
	        this.offsetLeft = offset;
	        if (this.isRendered()) {
	          this.getPopup().setOffset({
	            offsetLeft: offset
	          });
	          this.adjustPosition();
	        }
	      }
	    }
	  }, {
	    key: "getOffsetTop",
	    value: function getOffsetTop() {
	      return this.offsetTop;
	    }
	  }, {
	    key: "setOffsetTop",
	    value: function setOffsetTop(offset) {
	      if (main_core.Type.isNumber(offset) && offset >= 0) {
	        this.offsetTop = offset;
	        if (this.isRendered()) {
	          this.getPopup().setOffset({
	            offsetTop: offset
	          });
	          this.adjustPosition();
	        }
	      }
	    }
	  }, {
	    key: "getZindex",
	    value: function getZindex() {
	      return this.getPopup().getZindex();
	    }
	  }, {
	    key: "isCacheable",
	    value: function isCacheable() {
	      return this.cacheable;
	    }
	  }, {
	    key: "setCacheable",
	    value: function setCacheable(cacheable) {
	      if (main_core.Type.isBoolean(cacheable)) {
	        this.cacheable = cacheable;
	        if (this.isRendered()) {
	          this.getPopup().setCacheable(cacheable);
	        }
	      }
	    }
	  }, {
	    key: "shouldFocusOnFirst",
	    value: function shouldFocusOnFirst() {
	      return this.focusOnFirst;
	    }
	  }, {
	    key: "setFocusOnFirst",
	    value: function setFocusOnFirst(flag) {
	      if (main_core.Type.isBoolean(flag)) {
	        this.focusOnFirst = flag;
	      }
	    }
	  }, {
	    key: "focusOnFirstNode",
	    value: function focusOnFirstNode() {
	      if (this.getActiveTab()) {
	        const itemNode = this.getActiveTab().getRootNode().getFirstChild();
	        if (itemNode) {
	          itemNode.focus();
	          return itemNode;
	        }
	      }
	      return null;
	    }
	  }, {
	    key: "getFocusedNode",
	    value: function getFocusedNode() {
	      return this.focusedNode;
	    }
	  }, {
	    key: "clearNodeFocus",
	    value: function clearNodeFocus() {
	      if (this.focusedNode) {
	        this.focusedNode.unfocus();
	        this.focusedNode = null;
	      }
	    }
	  }, {
	    key: "isDropdownMode",
	    value: function isDropdownMode() {
	      return this.dropdownMode;
	    }
	  }, {
	    key: "setPreselectedItems",
	    value: function setPreselectedItems(itemIds) {
	      this.preselectedItems = this.validateItemIds(itemIds);
	    }
	  }, {
	    key: "getPreselectedItems",
	    value: function getPreselectedItems() {
	      return this.preselectedItems;
	    }
	  }, {
	    key: "setUndeselectedItems",
	    value: function setUndeselectedItems(itemIds) {
	      this.undeselectedItems = this.validateItemIds(itemIds);
	    }
	  }, {
	    key: "getUndeselectedItems",
	    value: function getUndeselectedItems() {
	      return this.undeselectedItems;
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "setOptions",
	    value: function setOptions(dialogOptions) {
	      const options = main_core.Type.isPlainObject(dialogOptions) ? dialogOptions : {};
	      if (main_core.Type.isArray(options.tabs)) {
	        options.tabs.forEach(tab => {
	          this.addTab(tab);
	        });
	      }
	      if (main_core.Type.isArray(options.selectedItems)) {
	        options.selectedItems.forEach(itemOptions => {
	          const options = Object.assign({}, main_core.Type.isPlainObject(itemOptions) ? itemOptions : {});
	          options.selected = true;
	          this.addItem(options);
	        });
	      }
	      if (main_core.Type.isArray(options.items)) {
	        options.items.forEach(itemOptions => {
	          this.addItem(itemOptions);
	        });
	      }
	      this.setHeader(options.header, options.headerOptions);
	      this.setFooter(options.footer, options.footerOptions);
	    }
	  }, {
	    key: "getMaxLabelWidth",
	    value: function getMaxLabelWidth() {
	      return this.maxLabelWidth;
	    }
	  }, {
	    key: "getMinLabelWidth",
	    value: function getMinLabelWidth() {
	      return this.minLabelWidth;
	    }
	  }, {
	    key: "expandLabels",
	    value: function expandLabels(animate = true) {
	      const freeSpace = parseInt(this.getPopup().getPopupContainer().style.left, 10);
	      if (freeSpace > this.getMinLabelWidth()) {
	        main_core.Dom.removeClass(this.getLabelsContainer(), 'ui-selector-tab-labels--animate-hide');
	        if (animate) {
	          main_core.Dom.addClass(this.getLabelsContainer(), 'ui-selector-tab-labels--animate-show');
	          main_core.Dom.style(this.getLabelsContainer(), 'max-width', `${Math.min(freeSpace, this.getMaxLabelWidth())}px`);
	          Animation.handleTransitionEnd(this.getLabelsContainer(), 'max-width').then(() => {
	            main_core.Dom.removeClass(this.getLabelsContainer(), 'ui-selector-tab-labels--animate-show');
	            main_core.Dom.addClass(this.getLabelsContainer(), 'ui-selector-tab-labels--active');
	          }).catch(() => {
	            // fail silently
	          });
	        } else {
	          main_core.Dom.style(this.getLabelsContainer(), 'max-width', `${Math.min(freeSpace, this.getMaxLabelWidth())}px`);
	        }
	      } else {
	        main_core.Dom.addClass(this.getLabelsContainer(), 'ui-selector-tab-labels--active');
	      }
	    }
	  }, {
	    key: "collapseLabels",
	    value: function collapseLabels(animate = true) {
	      main_core.Dom.removeClass(this.getLabelsContainer(), 'ui-selector-tab-labels--animate-show');
	      main_core.Dom.removeClass(this.getLabelsContainer(), 'ui-selector-tab-labels--active');
	      if (animate) {
	        main_core.Dom.addClass(this.getLabelsContainer(), 'ui-selector-tab-labels--animate-hide');
	        Animation.handleTransitionEnd(this.getLabelsContainer(), 'max-width').then(() => {
	          main_core.Dom.removeClass(this.getLabelsContainer(), 'ui-selector-tab-labels--animate-hide');
	        }).catch(() => {
	          // fail silently
	        });
	      }
	      main_core.Dom.style(this.getLabelsContainer(), 'max-width', null);
	    }
	  }, {
	    key: "getTagSelector",
	    value: function getTagSelector() {
	      return this.tagSelector;
	    }
	  }, {
	    key: "getTagSelectorMode",
	    value: function getTagSelectorMode() {
	      return this.tagSelectorMode;
	    }
	  }, {
	    key: "isTagSelectorInside",
	    value: function isTagSelectorInside() {
	      return this.getTagSelector() && this.getTagSelectorMode() === TagSelectorMode.INSIDE;
	    }
	  }, {
	    key: "isTagSelectorOutside",
	    value: function isTagSelectorOutside() {
	      return this.getTagSelector() && this.getTagSelectorMode() === TagSelectorMode.OUTSIDE;
	    }
	  }, {
	    key: "getTagSelectorQuery",
	    value: function getTagSelectorQuery() {
	      return this.getTagSelector() ? this.getTagSelector().getTextBoxValue() : '';
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "setTagSelector",
	    value: function setTagSelector(tagSelector) {
	      this.tagSelector = tagSelector;
	      this.tagSelector.subscribe('onInput', main_core.Runtime.debounce(this.handleTagSelectorInput, 200, this));
	      this.tagSelector.subscribe('onAddButtonClick', this.handleTagSelectorAddButtonClick.bind(this));
	      this.tagSelector.subscribe('onTagRemove', this.handleTagSelectorTagRemove.bind(this));
	      this.tagSelector.subscribe('onAfterTagRemove', this.handleTagSelectorAfterTagRemove.bind(this));
	      this.tagSelector.subscribe('onAfterTagAdd', this.handleTagSelectorAfterTagAdd.bind(this));
	      this.tagSelector.subscribe('onContainerClick', this.handleTagSelectorClick.bind(this));
	      this.tagSelector.setDialog(this);
	    }
	  }, {
	    key: "focusSearch",
	    value: function focusSearch() {
	      if (this.getTagSelector()) {
	        if (this.getActiveTab() !== this.getSearchTab()) {
	          this.getTagSelector().clearTextBox();
	        }
	        this.getTagSelector().focusTextBox();
	      }
	    }
	  }, {
	    key: "clearSearch",
	    value: function clearSearch() {
	      if (this.getTagSelector()) {
	        this.getTagSelector().clearTextBox();
	        if (this.getActiveTab() === this.getSearchTab()) {
	          this.selectFirstTab();
	        }
	      }
	    }
	  }, {
	    key: "getLoader",
	    value: function getLoader() {
	      if (this.loader === null) {
	        this.loader = new main_loader.Loader({
	          target: this.getTabsContainer(),
	          size: 100
	        });
	      }
	      return this.loader;
	    }
	  }, {
	    key: "showLoader",
	    value: function showLoader() {
	      void this.getLoader().show();
	    }
	  }, {
	    key: "hideLoader",
	    value: function hideLoader() {
	      if (this.loader !== null) {
	        void this.getLoader().hide();
	      }
	    }
	  }, {
	    key: "destroyLoader",
	    value: function destroyLoader() {
	      if (this.loader !== null) {
	        this.getLoader().destroy();
	      }
	      this.loader = null;
	    }
	  }, {
	    key: "getPopup",
	    value: function getPopup() {
	      if (this.popup !== null) {
	        return this.popup;
	      }
	      this.getTabs().forEach(tab => {
	        this.insertTab(tab);
	      });
	      const popupOptions = {
	        ...this.popupOptions
	      };
	      const userEvents = popupOptions.events;
	      delete popupOptions.events;
	      this.popup = new main_popup.Popup({
	        contentPadding: 0,
	        padding: 0,
	        offsetTop: this.getOffsetTop(),
	        offsetLeft: this.getOffsetLeft(),
	        animation: {
	          showClassName: 'ui-selector-popup-animation-show',
	          closeClassName: 'ui-selector-popup-animation-close',
	          closeAnimationType: 'animation'
	        },
	        bindElement: this.getTargetNode(),
	        bindOptions: {
	          forceBindPosition: true
	        },
	        autoHide: this.isAutoHide(),
	        autoHideHandler: this.handleAutoHide.bind(this),
	        closeByEsc: this.shouldHideByEsc(),
	        cacheable: this.isCacheable(),
	        events: {
	          onFirstShow: this.handlePopupFirstShow.bind(this),
	          onShow: this.handlePopupShow.bind(this),
	          onAfterShow: this.handlePopupAfterShow.bind(this),
	          onAfterClose: this.handlePopupAfterClose.bind(this),
	          onDestroy: this.handlePopupDestroy.bind(this)
	        },
	        content: this.getContainer(),
	        ...popupOptions
	      });
	      this.popup.subscribeFromOptions(userEvents);
	      this.rendered = true;
	      this.selectFirstTab();
	      return this.popup;
	    }
	  }, {
	    key: "isRendered",
	    value: function isRendered() {
	      return this.rendered;
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      return this.cache.remember('container', () => {
	        let searchContainer = '';
	        if (this.getTagSelectorMode() === TagSelectorMode.INSIDE) {
	          searchContainer = main_core.Tag.render(_t$b || (_t$b = _$b`<div class="ui-selector-search"></div>`));
	          this.getTagSelector().renderTo(searchContainer);
	        }
	        const className = this.isCompactView() ? ' ui-selector-dialog--compact-view' : '';
	        return main_core.Tag.render(_t2$6 || (_t2$6 = _$b`
				<div
					class="ui-selector-dialog${0}"
					style="width:${0}px; height:${0}px;"
				>
					${0}
					${0}
					${0}
					${0}
				</div>
			`), className, this.getWidth(), this.getHeight(), this.getHeaderContainer(), searchContainer, this.getTabsContainer(), this.getFooterContainer());
	      });
	    }
	  }, {
	    key: "getTabsContainer",
	    value: function getTabsContainer() {
	      return this.cache.remember('tabs-container', () => {
	        return main_core.Tag.render(_t3$6 || (_t3$6 = _$b`
				<div class="ui-selector-tabs">
					${0}
					${0}
				</div>
			`), this.getTabContentsContainer(), this.getLabelsContainer());
	      });
	    }
	  }, {
	    key: "getTabContentsContainer",
	    value: function getTabContentsContainer() {
	      return this.cache.remember('tab-contents', () => {
	        return main_core.Tag.render(_t4$6 || (_t4$6 = _$b`<div class="ui-selector-tab-contents"></div>`));
	      });
	    }
	  }, {
	    key: "getLabelsContainer",
	    value: function getLabelsContainer() {
	      return this.cache.remember('labels-container', () => {
	        return main_core.Tag.render(_t5$4 || (_t5$4 = _$b`
				<div
					class="ui-selector-tab-labels"
					onmouseenter="${0}"
					onmouseleave="${0}"
				></div>
			`), this.alwaysShowLabels ? null : this.handleLabelsMouseEnter.bind(this), this.alwaysShowLabels ? null : this.handleLabelsMouseLeave.bind(this));
	      });
	    }
	  }, {
	    key: "getHeaderContainer",
	    value: function getHeaderContainer() {
	      return this.cache.remember('header', () => {
	        const header = this.getHeader() && this.getHeader().getContainer();
	        return main_core.Tag.render(_t6$2 || (_t6$2 = _$b`
				<div class="ui-selector-header-container">${0}</div>
			`), header ? header : '');
	      });
	    }
	  }, {
	    key: "getFooterContainer",
	    value: function getFooterContainer() {
	      return this.cache.remember('footer', () => {
	        const footer = this.getFooter() && this.getFooter().getContainer();
	        return main_core.Tag.render(_t7$1 || (_t7$1 = _$b`
				<div class="ui-selector-footer-container">${0}</div>
			`), footer ? footer : '');
	      });
	    }
	  }, {
	    key: "freeze",
	    value: function freeze() {
	      if (this.isFrozen()) {
	        return;
	      }
	      this.frozenProps = {
	        autoHide: this.isAutoHide(),
	        hideByEsc: this.shouldHideByEsc()
	      };
	      this.setAutoHide(false);
	      this.setHideByEsc(false);
	      this.getNavigation().disable();
	      main_core.Dom.addClass(this.getContainer(), 'ui-selector-dialog--freeze');
	      this.frozen = true;
	    }
	  }, {
	    key: "unfreeze",
	    value: function unfreeze() {
	      if (!this.isFrozen()) {
	        return;
	      }
	      this.setAutoHide(this.frozenProps.autoHide !== false);
	      this.setHideByEsc(this.frozenProps.hideByEsc !== false);
	      this.getNavigation().enable();
	      main_core.Dom.removeClass(this.getContainer(), 'ui-selector-dialog--freeze');
	      this.frozen = false;
	    }
	  }, {
	    key: "isFrozen",
	    value: function isFrozen() {
	      return this.frozen;
	    }
	  }, {
	    key: "hasRecentItems",
	    value: function hasRecentItems() {
	      return new Promise((resolve, reject) => {
	        main_core.ajax.runAction('ui.entityselector.load', {
	          json: {
	            dialog: this.getAjaxJson()
	          },
	          getParameters: {
	            context: this.getContext()
	          }
	        }).then(response => {
	          resolve(response.data && response.data.dialog && main_core.Type.isArrayFilled(response.data.dialog.recentItems));
	        }).catch(error => {
	          reject(error);
	        });
	      });
	    }
	  }, {
	    key: "load",
	    value: function load() {
	      if (this.loadState !== LoadState.UNSENT || !this.hasDynamicLoad()) {
	        return;
	      }
	      if (this.getTagSelector()) {
	        this.getTagSelector().lock();
	      }
	      setTimeout(() => {
	        if (this.isLoading()) {
	          this.showLoader();
	        }
	      }, 400);
	      this.loadState = LoadState.LOADING;
	      main_core.ajax.runAction('ui.entityselector.load', {
	        json: {
	          dialog: this.getAjaxJson()
	        },
	        getParameters: {
	          context: this.getContext()
	        }
	      }).then(response => {
	        if (response && response.data && main_core.Type.isPlainObject(response.data.dialog)) {
	          this.loadState = LoadState.DONE;
	          const entities = main_core.Type.isArrayFilled(response.data.dialog.entities) ? response.data.dialog.entities : [];
	          entities.forEach(entityOptions => {
	            const entity = this.getEntity(entityOptions.id);
	            if (entity) {
	              entity.setDynamicSearch(entityOptions.dynamicSearch);
	            }
	          });
	          this.setOptions(response.data.dialog);
	          this.getPreselectedItems().forEach(preselectedItem => {
	            const item = this.getItem(preselectedItem);
	            if (item) {
	              item.select(true);
	            }
	          });
	          const recentItems = response.data.dialog.recentItems;
	          if (main_core.Type.isArray(recentItems)) {
	            const nodeOptionsMap = new Map();
	            const itemsOptions = response.data.dialog.items;
	            if (main_core.Type.isArray(itemsOptions)) {
	              itemsOptions.forEach(itemOptions => {
	                if (itemOptions.nodeOptions) {
	                  const item = this.getItem(itemOptions);
	                  if (item) {
	                    nodeOptionsMap.set(item, itemOptions.nodeOptions);
	                  }
	                }
	              });
	            }
	            const items = recentItems.map(recentItem => {
	              const item = this.getItem(recentItem);
	              return [item, nodeOptionsMap.get(item)];
	            });
	            this.getRecentTab().getRootNode().addItems(items);
	          }
	          if (!this.getRecentTab().getRootNode().hasChildren() && this.getRecentTab().getStub()) {
	            this.getRecentTab().getStub().show();
	          }
	          if (this.getTagSelector()) {
	            this.getTagSelector().unlock();
	          }
	          if (this.isRendered()) {
	            if (this.isDropdownMode() && this.getActiveTab() === this.getRecentTab()) {
	              this.selectFirstTab();
	            } else if (!this.getActiveTab()) {
	              this.selectFirstTab();
	            }
	          }
	          this.focusSearch();
	          this.destroyLoader();
	          if (this.shouldFocusOnFirst()) {
	            this.focusOnFirstNode();
	          }
	          this.emit('onLoad');
	        }
	      }).catch(error => {
	        this.loadState = LoadState.UNSENT;
	        if (this.getTagSelector()) {
	          this.getTagSelector().unlock();
	        }
	        this.focusSearch();
	        this.destroyLoader();
	        this.emit('onLoadError', {
	          error
	        });
	        console.error(error);
	      });
	    }
	  }, {
	    key: "isLoaded",
	    value: function isLoaded() {
	      return this.loadState === LoadState.DONE;
	    }
	  }, {
	    key: "isLoading",
	    value: function isLoading() {
	      return this.loadState === LoadState.LOADING;
	    }
	  }, {
	    key: "hasDynamicLoad",
	    value: function hasDynamicLoad() {
	      let hasDynamicLoad = false;
	      this.entities.forEach(entity => {
	        hasDynamicLoad = hasDynamicLoad || entity.hasDynamicLoad();
	      });
	      return hasDynamicLoad;
	    }
	  }, {
	    key: "hasDynamicSearch",
	    value: function hasDynamicSearch() {
	      let hasDynamicSearch = false;
	      this.entities.forEach(entity => {
	        hasDynamicSearch = hasDynamicSearch || entity.isSearchable() && entity.hasDynamicSearch();
	      });
	      return hasDynamicSearch;
	    }
	  }, {
	    key: "saveRecentItem",
	    value: function saveRecentItem(item) {
	      if (this.getContext() === null || !item.isSaveable()) {
	        return;
	      }
	      this.recentItemsToSave.push(item);
	      this.saveRecentItemsWithDebounce();
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "saveRecentItems",
	    value: function saveRecentItems() {
	      if (!main_core.Type.isArrayFilled(this.recentItemsToSave)) {
	        return;
	      }
	      main_core.ajax.runAction('ui.entityselector.saveRecentItems', {
	        json: {
	          dialog: this.getAjaxJson(),
	          recentItems: this.recentItemsToSave.map(item => item.getAjaxJson())
	        },
	        getParameters: {
	          context: this.getContext()
	        }
	      }).then(response => {}).catch(error => {
	        console.error(error);
	      });
	      this.recentItemsToSave = [];
	    }
	  }, {
	    key: "shouldClearUnavailableItems",
	    value: function shouldClearUnavailableItems() {
	      return this.clearUnavailableItems;
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "handleTagSelectorInput",
	    value: function handleTagSelectorInput() {
	      if (this.getTagSelectorMode() === TagSelectorMode.OUTSIDE && !this.isOpen()) {
	        this.show();
	      }
	      const query = this.getTagSelector().getTextBoxValue();
	      this.search(query);
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "handleTagSelectorAddButtonClick",
	    value: function handleTagSelectorAddButtonClick() {
	      this.show();
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "handleTagSelectorTagRemove",
	    value: function handleTagSelectorTagRemove(event) {
	      const {
	        tag
	      } = event.getData();
	      const item = this.getItem({
	        id: tag.getId(),
	        entityId: tag.getEntityId()
	      });
	      if (item) {
	        item.deselect();
	      }
	      this.focusSearch();
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "handleTagSelectorAfterTagRemove",
	    value: function handleTagSelectorAfterTagRemove() {
	      this.adjustByTagSelector();
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "handleTagSelectorAfterTagAdd",
	    value: function handleTagSelectorAfterTagAdd() {
	      this.adjustByTagSelector();
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "adjustByTagSelector",
	    value: function adjustByTagSelector() {
	      if (this.getTagSelectorMode() === TagSelectorMode.OUTSIDE) {
	        this.adjustPosition();
	      } else if (this.getTagSelectorMode() === TagSelectorMode.INSIDE) {
	        const newTagSelectorHeight = this.getTagSelector().calcHeight();
	        if (newTagSelectorHeight > 0) {
	          const offset = newTagSelectorHeight - (this.tagSelectorHeight || this.getTagSelector().getMinHeight());
	          this.tagSelectorHeight = newTagSelectorHeight;
	          if (offset !== 0) {
	            const height = this.getHeight();
	            this.setHeight(height + offset).then(() => {
	              this.adjustPosition();
	            });
	          }
	        }
	      }
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "handleTagSelectorClick",
	    value: function handleTagSelectorClick() {
	      this.focusSearch();
	    }
	    /**
	     * @internal
	     */
	  }, {
	    key: "handleItemSelect",
	    value: function handleItemSelect(item, animate = true) {
	      const shouldAnimate = this.isMultiple() ? animate : this.getSelectedItems().length === 0;
	      if (!this.isMultiple()) {
	        this.deselectAll();
	        if (this.getSelectedItems().length > 0) {
	          console.error('EntitySelector: some items are still selected.', this.getSelectedItems());
	        }
	      }
	      if (this.getTagSelector() && this.shouldAddTagOnSelect()) {
	        const tag = item.createTag();
	        tag.animate = shouldAnimate;
	        this.getTagSelector().addTag(tag);
	      }
	      this.selectedItems.add(item);
	    }
	    /**
	     * @internal
	     */
	  }, {
	    key: "handleItemDeselect",
	    value: function handleItemDeselect(item) {
	      const shouldAnimate = this.isMultiple();
	      this.selectedItems.delete(item);
	      if (this.getTagSelector()) {
	        this.getTagSelector().removeTag({
	          id: item.getId(),
	          entityId: item.getEntityId()
	        }, shouldAnimate);
	      }
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "handlePopupAfterShow",
	    value: function handlePopupAfterShow() {
	      this.focusSearch();
	      this.adjustByTagSelector();
	      this.emit('onShow');
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "handlePopupFirstShow",
	    value: function handlePopupFirstShow() {
	      this.emit('onFirstShow');
	      this.observeTabOverlapping();
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "handlePopupShow",
	    value: function handlePopupShow() {
	      if (this.offsetAnimation) {
	        requestAnimationFrame(() => {
	          requestAnimationFrame(() => {
	            main_core.Dom.addClass(this.getPopup().getPopupContainer(), 'ui-selector-popup-offset-animation');
	          });
	        });
	      }
	      if (this.alwaysShowLabels) {
	        setTimeout(() => {
	          // We have to call the method after adjustPosition()
	          this.expandLabels(false);
	        }, 0);
	      }
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "handleAutoHide",
	    value: function handleAutoHide(event) {
	      const target = event.target;
	      const el = this.getPopup().getPopupContainer();
	      if (target === el || el.contains(target)) {
	        return false;
	      }
	      if (this.isTagSelectorOutside() && target === this.getTagSelector().getTextBox() && main_core.Type.isStringFilled(this.getTagSelector().getTextBoxValue())) {
	        return false;
	      }
	      if (this.autoHideHandler !== null) {
	        const result = this.autoHideHandler(event, this);
	        if (main_core.Type.isBoolean(result)) {
	          return result;
	        }
	      }
	      return true;
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "observeTabOverlapping",
	    value: function observeTabOverlapping() {
	      this.disconnectTabOverlapping();
	      this.overlappingObserver = new MutationObserver(() => {
	        if (this.getLabelsContainer().offsetWidth > 0) {
	          const left = parseInt(this.getPopup().getPopupContainer().style.left, 10);
	          if (left < this.getMinLabelWidth()) {
	            main_core.Dom.style(this.getPopup().getPopupContainer(), 'left', `${this.getMinLabelWidth()}px`);
	            this.collapseLabels(false);
	          } else if (this.alwaysShowLabels) {
	            this.expandLabels(false);
	          }
	        }
	      });
	      this.overlappingObserver.observe(this.getPopup().getPopupContainer(), {
	        attributes: true,
	        attributeFilter: ['style']
	      });
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "disconnectTabOverlapping",
	    value: function disconnectTabOverlapping() {
	      if (this.overlappingObserver) {
	        this.overlappingObserver.disconnect();
	      }
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "handlePopupAfterClose",
	    value: function handlePopupAfterClose() {
	      if (this.isTagSelectorOutside()) {
	        if (this.getActiveTab() && this.getActiveTab() === this.getSearchTab()) {
	          this.selectFirstTab();
	        }
	        this.getTagSelector().clearTextBox();
	        this.getTagSelector().showAddButton();
	        this.getTagSelector().hideTextBox();
	      }
	      if (this.offsetAnimation) {
	        main_core.Dom.removeClass(this.getPopup().getPopupContainer(), 'ui-selector-popup-offset-animation');
	      }
	      this.emit('onHide');
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "handlePopupDestroy",
	    value: function handlePopupDestroy() {
	      this.destroy();
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "handleLabelsMouseEnter",
	    value: function handleLabelsMouseEnter() {
	      this.expandLabels();
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "handleLabelsMouseLeave",
	    value: function handleLabelsMouseLeave() {
	      this.collapseLabels();
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "handleItemNodeFocus",
	    value: function handleItemNodeFocus(event) {
	      const {
	        node
	      } = event.getData();
	      if (this.focusedNode === node) {
	        return;
	      }
	      this.clearNodeFocus();
	      this.focusedNode = node;
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "handleItemNodeUnfocus",
	    value: function handleItemNodeUnfocus() {
	      this.clearNodeFocus();
	    }
	  }, {
	    key: "getAjaxJson",
	    value: function getAjaxJson() {
	      return {
	        id: this.getId(),
	        context: this.getContext(),
	        entities: this.getEntities(),
	        preselectedItems: this.getPreselectedItems(),
	        recentItemsLimit: this.getRecentItemsLimit(),
	        clearUnavailableItems: this.shouldClearUnavailableItems()
	      };
	    }
	  }], [{
	    key: "createHeader",
	    value: function createHeader(context, headerContent, headerOptions) {
	      if (!main_core.Type.isStringFilled(headerContent) && !main_core.Type.isArrayFilled(headerContent) && !main_core.Type.isDomNode(headerContent) && !main_core.Type.isFunction(headerContent)) {
	        return null;
	      }

	      /** @var {BaseHeader} */
	      let header = null;
	      const options = main_core.Type.isPlainObject(headerOptions) ? headerOptions : {};
	      if (main_core.Type.isFunction(headerContent) || main_core.Type.isString(headerContent)) {
	        const className = main_core.Type.isString(headerContent) ? main_core.Reflection.getClass(headerContent) : headerContent;
	        if (main_core.Type.isFunction(className)) {
	          header = new className(context, options);
	          if (!(header instanceof BaseHeader)) {
	            console.error('EntitySelector: header is not an instance of BaseHeader.');
	            header = null;
	          }
	        }
	      }
	      if (headerContent !== null && !header) {
	        header = new DefaultHeader(context, Object.assign({}, options, {
	          content: headerContent
	        }));
	      }
	      return header;
	    }
	  }, {
	    key: "createFooter",
	    value: function createFooter(context, footerContent, footerOptions) {
	      if (!main_core.Type.isStringFilled(footerContent) && !main_core.Type.isArrayFilled(footerContent) && !main_core.Type.isDomNode(footerContent) && !main_core.Type.isFunction(footerContent)) {
	        return null;
	      }

	      /** @var {BaseFooter} */
	      let footer = null;
	      const options = main_core.Type.isPlainObject(footerOptions) ? footerOptions : {};
	      if (main_core.Type.isFunction(footerContent) || main_core.Type.isString(footerContent)) {
	        const className = main_core.Type.isString(footerContent) ? main_core.Reflection.getClass(footerContent) : footerContent;
	        if (main_core.Type.isFunction(className)) {
	          footer = new className(context, options);
	          if (!(footer instanceof BaseFooter)) {
	            console.error('EntitySelector: footer is not an instance of BaseFooter.');
	            footer = null;
	          }
	        }
	      }
	      if (footerContent !== null && !footer) {
	        footer = new DefaultFooter(context, Object.assign({}, options, {
	          content: footerContent
	        }));
	      }
	      return footer;
	    }
	  }]);
	  return Dialog;
	}(main_core_events.EventEmitter);

	const EntitySelector = {
	  Dialog,
	  Item,
	  Tab,
	  Entity,
	  TagSelector,
	  BaseHeader,
	  DefaultHeader,
	  BaseFooter,
	  DefaultFooter,
	  BaseStub,
	  DefaultStub
	};

	exports.EntitySelector = EntitySelector;
	exports.Dialog = Dialog;
	exports.Item = Item;
	exports.Tab = Tab;
	exports.Entity = Entity;
	exports.TagSelector = TagSelector;
	exports.BaseHeader = BaseHeader;
	exports.DefaultHeader = DefaultHeader;
	exports.BaseFooter = BaseFooter;
	exports.DefaultFooter = DefaultFooter;
	exports.BaseStub = BaseStub;
	exports.DefaultStub = DefaultStub;

}((this.BX.UI.EntitySelector = this.BX.UI.EntitySelector || {}),BX.Main,BX.Collections,BX.Event,BX,BX));
//# sourceMappingURL=entity-selector.bundle.js.map

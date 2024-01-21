/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_core) {
	'use strict';

	const privateMap = new WeakMap();
	const nameSymbol = Symbol('name');
	class Node {
	  constructor(options = {}) {
	    this[nameSymbol] = 'unknown';
	    this.children = [];
	    privateMap.set(this, {
	      delayedChildren: []
	    });
	    this.setScheme(options.scheme);
	    this.setParent(options.parent);
	    this.setName(options.name);
	    this.setChildren(options.children);
	  }
	  static get ELEMENT_NODE() {
	    return 1;
	  }
	  static get TEXT_NODE() {
	    return 2;
	  }
	  static get ROOT_NODE() {
	    return 3;
	  }
	  static get FRAGMENT_NODE() {
	    return 4;
	  }
	  static freezeProperty(node, property, value, enumerable = true) {
	    Object.defineProperty(node, property, {
	      value,
	      writable: false,
	      configurable: false,
	      enumerable
	    });
	  }
	  static makeNonEnumerableProperty(node, property) {
	    Object.defineProperty(node, property, {
	      writable: false,
	      enumerable: false,
	      configurable: false
	    });
	  }
	  static flattenChildren(children) {
	    if (main_core.Type.isArrayFilled(children)) {
	      return children.flatMap(node => {
	        if (node.getType() === Node.FRAGMENT_NODE) {
	          return node.getChildren();
	        }
	        return node;
	      });
	    }
	    return [];
	  }
	  setScheme(scheme) {
	    privateMap.get(this).scheme = scheme;
	  }
	  getScheme() {
	    return privateMap.get(this).scheme;
	  }
	  prepareCase(value) {
	    const scheme = this.getScheme();
	    const currentCase = scheme.getTagCase();
	    if (currentCase === 'upperCase') {
	      return value.toUpperCase();
	    }
	    return value.toLowerCase();
	  }
	  setName(name) {
	    if (main_core.Type.isString(name)) {
	      this[nameSymbol] = name.toLowerCase();
	    }
	  }
	  getName() {
	    return this[nameSymbol];
	  }
	  getDisplayedName() {
	    return this.prepareCase(this.getName());
	  }
	  setParent(parent = null) {
	    const mounted = !this.hasParent() && parent;
	    privateMap.get(this).parent = parent;
	    if (mounted) {
	      this.onNodeDidMount();
	    }
	  }
	  getParent() {
	    return privateMap.get(this).parent;
	  }
	  getType() {
	    return privateMap.get(this).type;
	  }
	  hasParent() {
	    return Boolean(privateMap.get(this).parent);
	  }
	  remove() {
	    if (this.hasParent()) {
	      this.getParent().removeChild(this);
	    }
	  }
	  setChildren(children) {
	    if (main_core.Type.isArray(children)) {
	      this.children = [];
	      this.appendChild(...children);
	    }
	  }
	  getChildren() {
	    return [...this.children];
	  }
	  getLastChild() {
	    return this.getChildren().at(-1);
	  }
	  getLastChildOfType(type) {
	    return this.getChildren().reverse().find(node => {
	      return node.getType() === type;
	    });
	  }
	  getLastChildOfName(name) {
	    return this.getChildren().reverse().find(node => {
	      return node.getType() === Node.ELEMENT_NODE && node.getName() === name;
	    });
	  }
	  getFirstChild() {
	    return this.getChildren().at(0);
	  }
	  getFirstChildOfType(type) {
	    return this.getChildren().find(node => {
	      return node.getType() === type;
	    });
	  }
	  getFirstChildOfName(name) {
	    return this.getChildren().find(node => {
	      return node.getType() === Node.ELEMENT_NODE && node.getName() === name;
	    });
	  }
	  getPreviewsSibling() {
	    if (this.hasParent()) {
	      const parentChildren = this.getParent().getChildren();
	      const currentIndex = parentChildren.indexOf(this);
	      if (currentIndex > 0) {
	        return parentChildren.at(currentIndex - 1);
	      }
	    }
	    return null;
	  }
	  getNextSibling() {
	    if (this.hasParent()) {
	      const parentChildren = this.getParent().getChildren();
	      const currentIndex = parentChildren.indexOf(this);
	      if (currentIndex !== -1 && currentIndex !== parentChildren.length) {
	        return parentChildren.at(currentIndex + 1);
	      }
	    }
	    return null;
	  }
	  getChildrenCount() {
	    return this.children.length;
	  }
	  hasChildren() {
	    return this.getChildrenCount() > 0;
	  }
	  setDelayedChildren(children) {
	    if (main_core.Type.isArray(children)) {
	      privateMap.get(this).delayedChildren = children;
	    }
	  }
	  addDelayedChildren(children) {
	    if (main_core.Type.isArrayFilled(children)) {
	      this.setDelayedChildren([...this.getDelayedChildren(), ...children]);
	    }
	  }
	  hasDelayedChildren() {
	    return privateMap.get(this).delayedChildren.length > 0;
	  }
	  getDelayedChildren() {
	    return [...privateMap.get(this).delayedChildren];
	  }
	  appendChild(...children) {
	    const flattenedChildren = Node.flattenChildren(children);
	    flattenedChildren.forEach(node => {
	      node.remove();
	      node.setParent(this);
	      this.children.push(node);
	    });
	  }
	  prependChild(...children) {
	    const flattenedChildren = Node.flattenChildren(children);
	    flattenedChildren.forEach(node => {
	      node.remove();
	      node.setParent(this);
	      this.children.unshift(node);
	    });
	  }
	  propagateChild(...children) {
	    if (this.hasParent()) {
	      this.getParent().prependChild(...children.filter(node => {
	        return node.getType() === Node.ELEMENT_NODE || node.getName() === '#text';
	      }));
	    } else {
	      this.addDelayedChildren(children);
	    }
	  }
	  onNodeDidMount() {
	    const delayedChildren = this.getDelayedChildren();
	    if (main_core.Type.isArrayFilled(delayedChildren)) {
	      this.propagateChild(...delayedChildren);
	      this.setDelayedChildren([]);
	    }
	  }
	  removeChild(...children) {
	    this.children = this.children.reduce((acc, node) => {
	      if (children.includes(node)) {
	        node.setParent(null);
	        return acc;
	      }
	      return [...acc, node];
	    }, []);
	  }
	  replaceChild(targetNode, ...children) {
	    this.children = this.children.flatMap(node => {
	      if (node === targetNode) {
	        node.setParent(null);
	        const flattenedChildren = Node.flattenChildren(children);
	        return flattenedChildren.map(child => {
	          child.remove();
	          child.setParent(this);
	          return child;
	        });
	      }
	      return node;
	    });
	  }
	  replace(...children) {
	    if (this.hasParent()) {
	      const parent = this.getParent();
	      parent.replaceChild(this, ...children);
	    }
	  }
	  clone(options = {}) {
	    const children = (() => {
	      if (options.deep) {
	        return this.getChildren().map(child => {
	          return child.clone(options);
	        });
	      }
	      return [];
	    })();
	    return new Node({
	      name: this.getName(),
	      scheme: this.getScheme(),
	      parent: this.getParent(),
	      children
	    });
	  }
	  toJSON() {
	    return {
	      name: this.getName(),
	      children: this.getChildren().map(child => {
	        return child.toJSON();
	      })
	    };
	  }
	}

	const toLowerCase = value => {
	  if (main_core.Type.isString(value)) {
	    return value.toLowerCase();
	  }
	  return value;
	};
	class Tag {
	  static isInline(tagName) {
	    return Tag.INLINE_TAGS.has(toLowerCase(tagName));
	  }
	  static isBlock(tagName) {
	    return Tag.BLOCK_TAGS.has(toLowerCase(tagName));
	  }
	  static isList(tagName) {
	    return Tag.LIST_TAGS.has(toLowerCase(tagName));
	  }
	  static isListItem(tagName) {
	    return Tag.LIST_ITEM_TAGS.has(toLowerCase(tagName));
	  }
	}
	Tag.BOLD = 'b';
	Tag.ITALIC = 'i';
	Tag.STRIKE = 's';
	Tag.UNDERLINE = 'u';
	Tag.SIZE = 'size';
	Tag.COLOR = 'color';
	Tag.CENTER = 'center';
	Tag.LEFT = 'left';
	Tag.RIGHT = 'right';
	Tag.URL = 'url';
	Tag.IMG = 'img';
	Tag.PARAGRAPH = 'p';
	Tag.LIST = 'list';
	Tag.LIST_UL = 'ul';
	Tag.LIST_OL = 'ol';
	Tag.LIST_ITEM = '*';
	Tag.LIST_ITEM_LI = 'li';
	Tag.TABLE = 'table';
	Tag.TABLE_ROW = 'tr';
	Tag.TABLE_CELL = 'td';
	Tag.TABLE_HEAD_CELL = 'th';
	Tag.QUOTE = 'quote';
	Tag.CODE = 'code';
	Tag.SPOILER = 'spoiler';
	Tag.INLINE_TAGS = new Set([Tag.BOLD, Tag.ITALIC, Tag.STRIKE, Tag.UNDERLINE, Tag.SIZE, Tag.COLOR, Tag.CENTER, Tag.LEFT, Tag.RIGHT, Tag.URL, Tag.IMG, Tag.LIST_ITEM, Tag.LIST_ITEM_LI]);
	Tag.BLOCK_TAGS = new Set([Tag.PARAGRAPH, Tag.LIST, Tag.LIST_UL, Tag.LIST_OL, Tag.TABLE, Tag.TABLE_ROW, Tag.TABLE_HEAD_CELL, Tag.TABLE_CELL, Tag.QUOTE, Tag.CODE, Tag.SPOILER]);
	Tag.LIST_TAGS = new Set([Tag.LIST, Tag.LIST_UL, Tag.LIST_OL]);
	Tag.LIST_ITEM_TAGS = new Set([Tag.LIST_ITEM, Tag.LIST_ITEM_LI]);

	class Text {
	  static isAnyTextNode(node) {
	    return node && Text.TEXT_NAMES.has(node.getName());
	  }
	  static isPlainTextNode(node) {
	    return node && node.getName() === Text.TEXT_NAME;
	  }
	  static isNewLineNode(node) {
	    return node && node.getName() === Text.NEW_LINE_NAME;
	  }
	  static isTabNode(node) {
	    return node && node.getName() === Text.TAB_NAME;
	  }
	  static isNewLineContent(content) {
	    return content === Text.NEW_LINE_CONTENT;
	  }
	  static isTabContent(content) {
	    return content === Text.TAB_CONTENT;
	  }
	  static isSpecialCharContent(content) {
	    return Text.SPECIAL_CHARS_CONTENT.has(content);
	  }
	}
	Text.TAB_CONTENT = '\t';
	Text.NEW_LINE_CONTENT = '\n';
	Text.SPECIAL_CHARS_CONTENT = new Set([Text.TAB_CONTENT, Text.NEW_LINE_CONTENT]);
	Text.TEXT_NAME = '#text';
	Text.NEW_LINE_NAME = '#linebreak';
	Text.TAB_NAME = '#tab';
	Text.TEXT_NAMES = new Set([Text.TEXT_NAME, Text.NEW_LINE_NAME, Text.TAB_NAME]);

	const childFilters = {
	  [Tag.LIST]: node => {
	    return node.getName() === Tag.LIST_ITEM;
	  },
	  [Tag.LIST_OL]: node => {
	    return node.getName() === Tag.LIST_ITEM_LI;
	  },
	  [Tag.LIST_UL]: node => {
	    return node.getName() === Tag.LIST_ITEM_LI;
	  },
	  [Tag.LIST_ITEM]: node => {
	    return node && (Tag.isList(node.getName()) || Text.isPlainTextNode(node) || Text.isNewLineNode(node) || Tag.isInline(node.getName()) && !Tag.isListItem(node.getName()));
	  },
	  [Tag.LIST_ITEM_LI]: node => {
	    return Tag.isListItem(node.getName()) || Text.isPlainTextNode(node) || Text.isNewLineNode(node) || node.isInline() && !Tag.isListItem(node.getName());
	  },
	  [Tag.TABLE]: node => {
	    return node.getName() === Tag.TABLE_ROW;
	  },
	  [Tag.TABLE_ROW]: node => {
	    return node.getName() === Tag.TABLE_CELL || node.getName() === Tag.TABLE_HEAD_CELL;
	  },
	  [Tag.TABLE_CELL]: node => {
	    return Tag.isInline(node.getName()) || Text.isPlainTextNode(node) || Text.isNewLineNode(node);
	  },
	  [Tag.TABLE_HEAD_CELL]: node => {
	    return Tag.isInline(node.getName()) || Text.isPlainTextNode(node) || Text.isNewLineNode(node);
	  },
	  [Tag.PARAGRAPH]: node => {
	    return Tag.isInline(node.getName()) || Text.isPlainTextNode(node) || Text.isNewLineNode(node);
	  },
	  '#inline': node => {
	    return Tag.isInline(node.getName()) || Text.isPlainTextNode(node) || Text.isNewLineNode(node);
	  }
	};

	const contentSymbol = Symbol('content');
	class TextNode extends Node {
	  constructor(options = {}) {
	    const nodeOptions = main_core.Type.isString(options) ? {
	      content: options
	    } : options;
	    super(nodeOptions);
	    this[nameSymbol] = Text.TEXT_NAME;
	    this[contentSymbol] = '';
	    privateMap.get(this).type = Node.TEXT_NODE;
	    this.setContent(nodeOptions.content);
	    Node.makeNonEnumerableProperty(this, 'children');
	  }
	  static isTextNodeContent(value) {
	    return main_core.Type.isString(value) || main_core.Type.isNumber(value);
	  }
	  static decodeSpecialChars(content) {
	    if (TextNode.isTextNodeContent(content)) {
	      return content.replaceAll('&#91;', '[').replaceAll('&#93;', ']');
	    }
	    return content;
	  }
	  setName(name) {}
	  setContent(content) {
	    if (TextNode.isTextNodeContent(content)) {
	      this[contentSymbol] = TextNode.decodeSpecialChars(content);
	    }
	  }
	  getContent() {
	    return TextNode.decodeSpecialChars(this[contentSymbol]);
	  }
	  getLength() {
	    return String(this[contentSymbol]).length;
	  }
	  clone(options) {
	    const Constructor = this.constructor;
	    return new Constructor({
	      content: this.getContent(),
	      scheme: this.getScheme()
	    });
	  }
	  splitText(offset) {
	    if (!main_core.Type.isNumber(offset)) {
	      throw new TypeError('offset is not a number');
	    }
	    const contentLength = this.getLength();
	    if (offset < 0 || offset > contentLength) {
	      throw new TypeError(`offset '${offset}' is out of range ${0}-${contentLength}`);
	    }
	    const content = this.getContent();
	    const rightContent = content.slice(offset, contentLength);
	    const leftNode = (() => {
	      if (offset === contentLength) {
	        return this;
	      }
	      if (offset === 0) {
	        return null;
	      }
	      return new TextNode({
	        content: content.slice(0, offset),
	        scheme: this.getScheme()
	      });
	    })();
	    const rightNode = (() => {
	      if (offset === 0) {
	        return this;
	      }
	      if (offset === contentLength) {
	        return null;
	      }
	      return new TextNode({
	        content: rightContent,
	        scheme: this.getScheme()
	      });
	    })();
	    if (leftNode && rightNode) {
	      this.replace(leftNode, rightNode);
	    }
	    return [leftNode, rightNode];
	  }
	  toString() {
	    return this.getContent();
	  }
	  toJSON() {
	    return {
	      name: this.getName(),
	      content: this.toString()
	    };
	  }
	}

	const childConverters = {
	  [Tag.CODE]: node => {
	    if (node.getName() === '#text') {
	      return node;
	    }
	    return new TextNode({
	      content: node.toString()
	    });
	  }
	};

	class BBCodeScheme {
	  /** @private */

	  /** @private */

	  /** @private */

	  /** @private */

	  /** @private */

	  /** @private */

	  /** @private */

	  /** @private */

	  /** @private */

	  /** @private */

	  constructor(options = {}) {
	    this.childFilters = new Map();
	    this.childConverters = new Map();
	    this.allowedTags = new Set();
	    this.propagateUnresolvedNodes = true;
	    this.tagCase = BBCodeScheme.LOWER_CASE;
	    this.allowNewLineBeforeBlockOpeningTag = true;
	    this.allowNewLineAfterBlockOpeningTag = true;
	    this.allowNewLineBeforeBlockClosingTag = true;
	    this.allowNewLineAfterBlockClosingTag = true;
	    this.allowNewLineAfterListItem = true;
	    this.setChildFilters(childFilters);
	    this.setChildConverters(childConverters);
	    if (main_core.Type.isPlainObject(options)) {
	      this.setAllowedTags(options.allowedTags);
	      this.setChildFilters(options.childFilters);
	      this.setChildConverters(options.childConverters);
	      this.setPropagateUnresolvedNodes(options.propagateUnresolvedNodes);
	      this.setTagCase(options.tagCase);
	      this.setAllowNewLineBeforeBlockOpeningTag(options.newLineBeforeBlockOpeningTag);
	      this.setAllowNewLineAfterBlockOpeningTag(options.newLineAfterBlockOpeningTag);
	      this.setAllowNewLineBeforeBlockClosingTag(options.newLineBeforeBlockClosingTag);
	      this.setAllowNewLineAfterBlockClosingTag(options.newLineAfterBlockClosingTag);
	      this.setAllowNewLineAfterListItem(options.newLineAfterListItem);
	    }
	  }
	  setAllowedTags(allowedTags) {
	    if (main_core.Type.isArray(allowedTags)) {
	      this.allowedTags = new Set(allowedTags);
	    }
	  }
	  addAllowedTag(tag) {
	    if (main_core.Type.isStringFilled(tag)) {
	      this.getAllowedTags().add(tag);
	    }
	  }
	  getAllowedTags() {
	    return this.allowedTags;
	  }
	  getChildFilters() {
	    return this.childFilters;
	  }
	  getChildFilter(tagName) {
	    return this.getChildFilters().get(tagName);
	  }
	  setChildFilters(filters) {
	    if (main_core.Type.isPlainObject(filters)) {
	      const childFiltersMap = this.getChildFilters();
	      Object.entries(filters).forEach(([tagName, filter]) => {
	        childFiltersMap.set(tagName, filter);
	      });
	    }
	  }
	  getChildConverters() {
	    return this.childConverters;
	  }
	  getChildConverter(tagName) {
	    return this.getChildConverters().get(tagName);
	  }
	  setChildConverters(converters) {
	    if (main_core.Type.isPlainObject(converters)) {
	      const convertersMap = this.getChildConverters();
	      Object.entries(converters).forEach(([tagName, converter]) => {
	        convertersMap.set(tagName, converter);
	      });
	    }
	  }
	  setPropagateUnresolvedNodes(value) {
	    if (main_core.Type.isBoolean(value)) {
	      this.propagateUnresolvedNodes = value;
	    }
	  }
	  isPropagateUnresolvedNodes() {
	    return this.propagateUnresolvedNodes;
	  }
	  setTagCase(tagCase) {
	    if (BBCodeScheme.allowedCases.has(tagCase)) {
	      this.tagCase = tagCase;
	    }
	  }
	  getTagCase() {
	    return this.tagCase;
	  }
	  setAllowNewLineBeforeBlockOpeningTag(value) {
	    if (main_core.Type.isBoolean(value)) {
	      this.allowNewLineBeforeBlockOpeningTag = value;
	    }
	  }
	  isAllowNewLineBeforeBlockOpeningTag() {
	    return this.allowNewLineBeforeBlockOpeningTag;
	  }
	  setAllowNewLineAfterBlockOpeningTag(value) {
	    if (main_core.Type.isBoolean(value)) {
	      this.allowNewLineAfterBlockOpeningTag = value;
	    }
	  }
	  isAllowNewLineAfterBlockOpeningTag() {
	    return this.allowNewLineAfterBlockOpeningTag;
	  }
	  setAllowNewLineBeforeBlockClosingTag(value) {
	    if (main_core.Type.isBoolean(value)) {
	      this.allowNewLineBeforeBlockClosingTag = value;
	    }
	  }
	  isAllowNewLineBeforeBlockClosingTag() {
	    return this.allowNewLineBeforeBlockClosingTag;
	  }
	  setAllowNewLineAfterBlockClosingTag(value) {
	    if (main_core.Type.isBoolean(value)) {
	      this.allowNewLineAfterBlockClosingTag = value;
	    }
	  }
	  isAllowNewLineAfterBlockClosingTag() {
	    return this.allowNewLineAfterBlockClosingTag;
	  }
	  setAllowNewLineAfterListItem(value) {
	    if (main_core.Type.isBoolean(value)) {
	      this.allowNewLineAfterListItem = value;
	    }
	  }
	  isAllowNewLineAfterListItem() {
	    return this.allowNewLineAfterListItem;
	  }
	}
	BBCodeScheme.LOWER_CASE = 'lowerCase';
	BBCodeScheme.UPPER_CASE = 'upperCase';
	BBCodeScheme.allowedCases = new Set([BBCodeScheme.LOWER_CASE, BBCodeScheme.UPPER_CASE]);

	class ElementNode extends Node {
	  constructor(options = {}) {
	    super(options);
	    this.attributes = {};
	    this.value = '';
	    this.void = false;
	    this.inline = false;
	    privateMap.get(this).type = Node.ELEMENT_NODE;
	    const preparedOptions = {
	      inline: Tag.isInline(this.getName()),
	      ...options
	    };
	    this.setInline(preparedOptions.inline);
	    this.setValue(preparedOptions.value);
	    this.setVoid(preparedOptions.void);
	    this.setAttributes(preparedOptions.attributes);
	  }
	  filterChildren(children) {
	    const filteredChildren = {
	      resolved: [],
	      unresolved: []
	    };
	    const byTagFilter = this.getScheme().getChildFilter(this.getName());
	    if (byTagFilter) {
	      return children.reduce((acc, child) => {
	        const isAllowed = byTagFilter(child);
	        if (isAllowed) {
	          acc.resolved.push(child);
	        } else {
	          acc.unresolved.push(child);
	        }
	        return acc;
	      }, filteredChildren);
	    }
	    if (this.isInline()) {
	      const inlineChildFilter = this.getScheme().getChildFilter('#inline');
	      return children.reduce((acc, child) => {
	        const isAllowed = inlineChildFilter(child);
	        if (isAllowed) {
	          acc.resolved.push(child);
	        } else {
	          acc.unresolved.push(child);
	        }
	        return acc;
	      }, {
	        resolved: [],
	        unresolved: []
	      });
	    }
	    filteredChildren.resolved = children;
	    return filteredChildren;
	  }
	  convertChildren(children) {
	    const childConverter = this.getScheme().getChildConverter(this.getName());
	    if (childConverter) {
	      return children.map(child => {
	        return childConverter(child);
	      });
	    }
	    return children;
	  }
	  setValue(value) {
	    if (main_core.Type.isString(value) || main_core.Type.isNumber(value) || main_core.Type.isBoolean(value)) {
	      this.value = value;
	    }
	  }
	  getValue() {
	    return this.value;
	  }
	  setVoid(value) {
	    if (main_core.Type.isBoolean(value)) {
	      this.void = value;
	    }
	  }
	  isVoid() {
	    return this.void;
	  }
	  setInline(value) {
	    if (main_core.Type.isBoolean(value)) {
	      this.inline = value;
	    }
	  }
	  isInline() {
	    return this.inline;
	  }
	  setAttributes(attributes) {
	    if (main_core.Type.isPlainObject(attributes)) {
	      const entries = Object.entries(attributes).map(([key, value]) => {
	        return [key.toLowerCase(), value];
	      });
	      this.attributes = Object.fromEntries(entries);
	    }
	  }
	  setAttribute(name, value) {
	    if (main_core.Type.isStringFilled(name)) {
	      const preparedName = name.toLowerCase();
	      if (main_core.Type.isNil(value)) {
	        delete this.attributes[preparedName];
	      } else {
	        this.attributes[preparedName] = value;
	      }
	    }
	  }
	  getAttribute(name) {
	    if (main_core.Type.isString(name)) {
	      return this.attributes[name.toLowerCase()];
	    }
	    return null;
	  }
	  getAttributes() {
	    return {
	      ...this.attributes
	    };
	  }
	  appendChild(...children) {
	    const flattenedChildren = Node.flattenChildren(children);
	    const filteredChildren = this.filterChildren(flattenedChildren);
	    const convertedChildren = this.convertChildren(filteredChildren.resolved);
	    convertedChildren.forEach(node => {
	      node.remove();
	      node.setParent(this);
	      this.children.push(node);
	    });
	    if (main_core.Type.isArrayFilled(filteredChildren.unresolved)) {
	      this.propagateChild(...filteredChildren.unresolved);
	    }
	  }
	  prependChild(...children) {
	    const flattenedChildren = Node.flattenChildren(children);
	    const filteredChildren = this.filterChildren(flattenedChildren);
	    const convertedChildren = this.convertChildren(filteredChildren.resolved);
	    convertedChildren.forEach(node => {
	      node.remove();
	      node.setParent(this);
	      this.children.unshift(node);
	    });
	    if (main_core.Type.isArrayFilled(filteredChildren.unresolved)) {
	      this.propagateChild(...filteredChildren.unresolved);
	    }
	  }
	  replaceChild(targetNode, ...children) {
	    this.children = this.children.flatMap(node => {
	      if (node === targetNode) {
	        node.setParent(null);
	        const flattenedChildren = Node.flattenChildren(children);
	        const filteredChildren = this.filterChildren(flattenedChildren);
	        const convertedChildren = this.convertChildren(filteredChildren.resolved);
	        return convertedChildren.map(child => {
	          child.remove();
	          child.setParent(this);
	          return child;
	        });
	      }
	      return node;
	    });
	  }
	  toStringValue() {
	    const value = this.getValue();
	    return value ? `=${value}` : '';
	  }
	  toStringAttributes() {
	    return Object.entries(this.getAttributes()).map(([key, attrValue]) => {
	      const preparedKey = this.prepareCase(key);
	      return attrValue ? `${preparedKey}=${attrValue}` : preparedKey;
	    }).join(' ');
	  }
	  getNewLineAfterOpeningTag() {
	    if (!this.isInline() && this.getScheme().isAllowNewLineAfterBlockOpeningTag()) {
	      const firstChild = this.getFirstChild();
	      if (firstChild && firstChild.getName() !== '#linebreak') {
	        return '\n';
	      }
	    }
	    return '';
	  }
	  getNewLineBeforeClosingTag() {
	    const scheme = this.getScheme();
	    if (scheme.isAllowNewLineBeforeBlockClosingTag()) {
	      if (!this.isInline()) {
	        const lastChild = this.getLastChild();
	        if (lastChild && lastChild.getName() !== '#linebreak') {
	          return '\n';
	        }
	      }
	      if (Tag.isListItem(this.getName()) && scheme.isAllowNewLineAfterListItem()) {
	        const lastChild = this.getParent().getLastChild();
	        if (lastChild !== this) {
	          return '\n';
	        }
	      }
	    }
	    return '';
	  }
	  getNewLineBeforeOpeningTag() {
	    if (!this.isInline() && this.hasParent() && this.getScheme().isAllowNewLineBeforeBlockOpeningTag()) {
	      const previewsSibling = this.getPreviewsSibling();
	      if (previewsSibling && (Text.isPlainTextNode(previewsSibling) || Tag.isInline(previewsSibling.getName()))) {
	        return '\n';
	      }
	    }
	    return '';
	  }
	  getNewLineAfterClosingTag() {
	    if (!this.isInline() && this.hasParent() && this.getScheme().isAllowNewLineAfterBlockClosingTag()) {
	      const nextSibling = this.getNextSibling();
	      if (nextSibling && nextSibling.getName() !== '#linebreak') {
	        return '\n';
	      }
	    }
	    return '';
	  }
	  getContent() {
	    if (Tag.isListItem(this.getName())) {
	      return this.getChildren().reduceRight((acc, node) => {
	        if (!main_core.Type.isArrayFilled(acc) && (node.getName() === '#linebreak' || node.getName() === '#tab')) {
	          return acc;
	        }
	        return [node.toString(), ...acc];
	      }, []);
	    }
	    return this.getChildren().map(child => {
	      return child.toString();
	    }).join('');
	  }
	  getOpeningTag() {
	    const displayedName = this.getDisplayedName();
	    const tagValue = this.toStringValue();
	    const attributes = this.toStringAttributes();
	    const formattedAttributes = main_core.Type.isStringFilled(attributes) ? ` ${attributes}` : '';
	    return `[${displayedName}${tagValue}${formattedAttributes}]`;
	  }
	  getClosingTag() {
	    return `[/${this.getDisplayedName()}]`;
	  }
	  clone(options = {}) {
	    const children = (() => {
	      if (options.deep) {
	        return this.getChildren().map(child => {
	          return child.clone(options);
	        });
	      }
	      return [];
	    })();
	    return new ElementNode({
	      name: this.getName(),
	      void: this.isVoid(),
	      inline: this.isInline(),
	      value: this.getValue(),
	      attributes: {
	        ...this.getAttributes()
	      },
	      scheme: this.getScheme(),
	      children
	    });
	  }
	  toString() {
	    const openingTag = this.getOpeningTag();
	    if (this.isVoid()) {
	      return openingTag;
	    }
	    if (Tag.isListItem(this.getName())) {
	      return `${openingTag}${this.getContent()}${this.getNewLineBeforeClosingTag()}`;
	    }
	    if (this.isInline()) {
	      return `${openingTag}${this.getContent()}${this.getClosingTag()}`;
	    }
	    return [this.getNewLineBeforeOpeningTag(), openingTag, this.getNewLineAfterOpeningTag(), this.getContent(), this.getNewLineBeforeClosingTag(), this.getClosingTag(), this.getNewLineAfterClosingTag()].join('');
	  }
	  toJSON() {
	    return {
	      ...super.toJSON(),
	      value: this.getValue(),
	      attributes: this.getAttributes(),
	      void: this.isVoid(),
	      inline: this.isInline()
	    };
	  }
	}

	class RootNode extends ElementNode {
	  constructor(options) {
	    super(options);
	    this[nameSymbol] = '#root';
	    privateMap.get(this).type = Node.ROOT_NODE;
	    RootNode.makeNonEnumerableProperty(this, 'value');
	    RootNode.makeNonEnumerableProperty(this, 'void');
	    RootNode.makeNonEnumerableProperty(this, 'inline');
	    RootNode.makeNonEnumerableProperty(this, 'attributes');
	  }
	  getParent() {
	    return null;
	  }
	  setName(name) {}
	  clone(options = {}) {
	    const children = (() => {
	      if (options.deep) {
	        return this.getChildren().map(child => {
	          return child.clone(options);
	        });
	      }
	      return [];
	    })();
	    return new RootNode({
	      children
	    });
	  }
	  toString() {
	    return this.getChildren().map(child => {
	      return child.toString();
	    }).join('');
	  }
	  toJSON() {
	    return this.getChildren().map(node => {
	      return node.toJSON();
	    });
	  }
	}

	class FragmentNode extends ElementNode {
	  constructor(options) {
	    super(options);
	    this[nameSymbol] = '#fragment';
	    privateMap.get(this).type = Node.FRAGMENT_NODE;
	    FragmentNode.makeNonEnumerableProperty(this, 'value');
	    FragmentNode.makeNonEnumerableProperty(this, 'void');
	    FragmentNode.makeNonEnumerableProperty(this, 'inline');
	    FragmentNode.makeNonEnumerableProperty(this, 'attributes');
	  }
	  setName() {}
	  clone(options = {}) {
	    const children = (() => {
	      if (options.deep) {
	        return this.getChildren().map(child => {
	          return child.clone(options);
	        });
	      }
	      return [];
	    })();
	    return new FragmentNode({
	      children,
	      scheme: this.getScheme()
	    });
	  }
	}

	class NewLineNode extends TextNode {
	  constructor(options = {}) {
	    super(options);
	    this[nameSymbol] = Text.NEW_LINE_NAME;
	    this[contentSymbol] = Text.NEW_LINE_CONTENT;
	  }
	  setContent(options) {}
	}

	class TabNode extends TextNode {
	  constructor(options = {}) {
	    super(options);
	    this[nameSymbol] = Text.TAB_NAME;
	    this[contentSymbol] = Text.TAB_CONTENT;
	  }
	  setContent(options) {}
	}

	class ModelFactory {
	  /** @private */

	  constructor(options = {}) {
	    if (main_core.Type.isObject(options.scheme)) {
	      this.setScheme(options.scheme);
	    } else {
	      this.setScheme(new BBCodeScheme());
	    }
	  }
	  setScheme(scheme) {
	    this.scheme = scheme;
	  }
	  getScheme() {
	    return this.scheme;
	  }
	  createRootNode(options = {}) {
	    return new RootNode({
	      ...options,
	      scheme: this.getScheme()
	    });
	  }
	  createElementNode(options = {}) {
	    return new ElementNode({
	      ...options,
	      scheme: this.getScheme()
	    });
	  }
	  createTextNode(options = {}) {
	    const preparedOptions = main_core.Type.isString(options) ? {
	      content: options
	    } : options;
	    return new TextNode({
	      ...preparedOptions,
	      scheme: this.getScheme()
	    });
	  }
	  createNewLineNode(options = {}) {
	    const preparedOptions = main_core.Type.isString(options) ? {
	      content: options
	    } : options;
	    return new NewLineNode({
	      ...preparedOptions,
	      scheme: this.getScheme()
	    });
	  }
	  createTabNode(options = {}) {
	    const preparedOptions = main_core.Type.isString(options) ? {
	      content: options
	    } : options;
	    return new TabNode({
	      ...preparedOptions,
	      scheme: this.getScheme()
	    });
	  }
	  createFragmentNode(options = {}) {
	    return new FragmentNode({
	      ...options,
	      scheme: this.getScheme()
	    });
	  }
	  createNode(options = {}) {
	    return new Node({
	      ...options,
	      scheme: this.getScheme()
	    });
	  }
	}

	exports.Node = Node;
	exports.RootNode = RootNode;
	exports.ElementNode = ElementNode;
	exports.FragmentNode = FragmentNode;
	exports.NewLineNode = NewLineNode;
	exports.TabNode = TabNode;
	exports.TextNode = TextNode;
	exports.ModelFactory = ModelFactory;
	exports.Tag = Tag;
	exports.Text = Text;
	exports.BBCodeScheme = BBCodeScheme;

}((this.BX.UI.Bbcode = this.BX.UI.Bbcode || {}),BX));
//# sourceMappingURL=model.bundle.js.map

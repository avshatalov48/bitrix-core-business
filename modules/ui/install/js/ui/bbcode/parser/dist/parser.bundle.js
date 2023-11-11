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
	    privateMap.set(this, {});
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
	  setName(name) {
	    if (main_core.Type.isString(name)) {
	      this[nameSymbol] = name;
	    }
	  }
	  getName() {
	    return this[nameSymbol];
	  }
	  setParent(parent = null) {
	    privateMap.get(this).parent = parent;
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
	  toJSON() {
	    return {
	      name: this.getName(),
	      children: this.getChildren().map(child => {
	        return child.toJSON();
	      })
	    };
	  }
	}

	const contentSymbol = Symbol('content');
	class TextNode extends Node {
	  constructor(options = {}) {
	    const nodeOptions = main_core.Type.isString(options) ? {
	      content: options
	    } : options;
	    super(nodeOptions);
	    this[nameSymbol] = '#text';
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

	const TAB = '\t';
	const NEW_LINE = '\n';
	const SPECIAL_CHARS = new Set([TAB, NEW_LINE]);
	const BOLD = 'b';
	const ITALIC = 'i';
	const STRIKE = 's';
	const UNDERLINE = 'u';
	const SIZE = 'size';
	const COLOR = 'color';
	const CENTER = 'center';
	const LEFT = 'left';
	const RIGHT = 'right';
	const URL = 'url';
	const IMG = 'img';
	const LIST = 'list';
	const LIST_UL = 'ul';
	const LIST_OL = 'ol';
	const LIST_ITEM = '*';
	const LIST_ITEM_LI = 'li';
	const TABLE = 'table';
	const TABLE_ROW = 'tr';
	const TABLE_CELL = 'td';
	const TABLE_HEAD_CELL = 'th';
	const CODE = 'code';
	const INLINE_ELEMENTS = new Set([BOLD, ITALIC, STRIKE, UNDERLINE, SIZE, COLOR, CENTER, LEFT, RIGHT, URL, IMG, LIST_ITEM, LIST_ITEM_LI]);
	const LIST_ELEMENTS = new Set([LIST, LIST_UL, LIST_OL]);
	const LIST_ITEM_ELEMENTS = new Set([LIST_ITEM, LIST_ITEM_LI]);
	class NodeType {
	  static isAnyText(node) {
	    return node.getType() === Node.TEXT_NODE;
	  }
	  static isText(node) {
	    return node && NodeType.isAnyText(node) && !SPECIAL_CHARS.has(node.getContent());
	  }
	  static isNewLine(node) {
	    return node && NodeType.isAnyText(node) && node.getContent() === NEW_LINE;
	  }
	  static isTab(node) {
	    return node && NodeType.isAnyText(node) && node.getContent() === TAB;
	  }
	  static isElement(node) {
	    return node && node.getType() === Node.ELEMENT_NODE;
	  }
	  static isList(node) {
	    return node && NodeType.isElement(node) && LIST_ELEMENTS.has(node.getName());
	  }
	  static isListItem(node) {
	    return node && NodeType.isElement(node) && LIST_ITEM_ELEMENTS.has(node.getName());
	  }
	  static isInline(node) {
	    return node && NodeType.isElement(node) && INLINE_ELEMENTS.has(node.getName());
	  }
	  static isTableCell(node) {
	    return node && NodeType.isElement(node) && [TABLE_CELL, TABLE_HEAD_CELL].includes(node.getName());
	  }
	  static isTable(node) {
	    return node && NodeType.isElement(node) && node.getName() === TABLE;
	  }
	  static isTableRow(node) {
	    return node && NodeType.isElement(node) && node.getName() === TABLE_ROW;
	  }
	}
	const listChildFilter = node => {
	  return NodeType.isListItem(node);
	};
	const ulOlListChildFilter = node => {
	  return NodeType.isElement(node) && node.getName() === LIST_ITEM_LI;
	};
	const listItemChildFilter = node => {
	  return NodeType.isAnyText(node) && !NodeType.isTab(node) || NodeType.isInline(node) && !NodeType.isListItem(node) || NodeType.isList(node);
	};
	const tableChildFilter = node => {
	  return NodeType.isTableRow(node);
	};
	const tableRowChildFilter = node => {
	  return NodeType.isTableCell(node);
	};
	const tableCellChildFilter = node => {
	  return NodeType.isText(node) || NodeType.isNewLine(node) || NodeType.isInline(node) && !NodeType.isListItem(node);
	};
	const inlineChildFilter = node => {
	  return NodeType.isAnyText(node) && !NodeType.isTab(node) || NodeType.isInline(node) && !NodeType.isListItem(node);
	};
	const childFiltersMap = new Map();
	childFiltersMap.set(LIST, listChildFilter);
	childFiltersMap.set(LIST_ITEM, listItemChildFilter);
	childFiltersMap.set(LIST_ITEM_LI, listItemChildFilter);
	childFiltersMap.set(LIST_OL, ulOlListChildFilter);
	childFiltersMap.set(LIST_UL, ulOlListChildFilter);
	childFiltersMap.set(TABLE, tableChildFilter);
	childFiltersMap.set(TABLE_ROW, tableRowChildFilter);
	childFiltersMap.set(TABLE_CELL, tableCellChildFilter);
	childFiltersMap.set(TABLE_HEAD_CELL, tableCellChildFilter);
	childFiltersMap.set('#inline', inlineChildFilter);
	const childConvertersMap = new Map();
	childConvertersMap.set(CODE, node => {
	  if (node.getType() === Node.TEXT_NODE) {
	    return node;
	  }
	  return new TextNode(node.toString());
	});

	class ElementNode extends Node {
	  constructor(options = {}) {
	    super(options);
	    this.attributes = {};
	    this.value = '';
	    this.void = false;
	    this.inline = false;
	    privateMap.get(this).type = Node.ELEMENT_NODE;
	    const preparedOptions = {
	      inline: INLINE_ELEMENTS.has(options.name),
	      ...options
	    };
	    this.setInline(preparedOptions.inline);
	    this.setValue(preparedOptions.value);
	    this.setVoid(preparedOptions.void);
	    this.setAttributes(preparedOptions.attributes);
	  }
	  static filterChildren(node, children) {
	    const filteredChildren = {
	      resolved: [],
	      unresolved: []
	    };
	    const byTagFilter = childFiltersMap.get(node.getName());
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
	    if (node.isInline()) {
	      const inlineChildFilter = childFiltersMap.get('#inline');
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
	  static convertChildren(node, children) {
	    const childConverter = childConvertersMap.get(node.getName());
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
	      this.attributes = {
	        ...attributes
	      };
	    }
	  }
	  setAttribute(name, value) {
	    if (main_core.Type.isStringFilled(name)) {
	      if (main_core.Type.isNil(value)) {
	        delete this.attributes[name];
	      } else {
	        this.attributes[name] = value;
	      }
	    }
	  }
	  getAttribute(name) {
	    return this.attributes[name];
	  }
	  getAttributes() {
	    return {
	      ...this.attributes
	    };
	  }
	  appendChild(...children) {
	    const flattenedChildren = Node.flattenChildren(children);
	    const filteredChildren = ElementNode.filterChildren(this, flattenedChildren);
	    const convertedChildren = ElementNode.convertChildren(this, filteredChildren.resolved);
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
	    const filteredChildren = ElementNode.filterChildren(this, flattenedChildren);
	    const convertedChildren = ElementNode.convertChildren(this, filteredChildren.resolved);
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
	        const filteredChildren = ElementNode.filterChildren(this, flattenedChildren);
	        const convertedChildren = ElementNode.convertChildren(this, filteredChildren.resolved);
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
	      return attrValue ? `${key}=${attrValue}` : key;
	    }).join(' ');
	  }
	  getNewLineBeforeContent() {
	    if (!this.isInline()) {
	      const firstChild = this.getFirstChild();
	      if (firstChild && !NodeType.isNewLine(firstChild)) {
	        return '\n';
	      }
	    }
	    return '';
	  }
	  getNewLineAfterContent() {
	    if (!this.isInline()) {
	      const lastChild = this.getLastChild();
	      if (lastChild && !NodeType.isNewLine(lastChild)) {
	        return '\n';
	      }
	    }
	    if (NodeType.isListItem(this)) {
	      const lastChild = this.getParent().getLastChild();
	      if (lastChild !== this) {
	        return '\n';
	      }
	    }
	    return '';
	  }
	  getNewLineBeforeOpeningTag() {
	    if (!this.isInline() && this.hasParent()) {
	      const previewsSibling = this.getPreviewsSibling();
	      if (NodeType.isText(previewsSibling) || NodeType.isInline(previewsSibling)) {
	        return '\n';
	      }
	    }
	    return '';
	  }
	  getNewLineAfterClosingTag() {
	    if (!this.isInline() && this.hasParent()) {
	      const nextSibling = this.getNextSibling();
	      if (nextSibling && nextSibling.getName() !== '#linebreak') {
	        return '\n';
	      }
	    }
	    return '';
	  }
	  getContent() {
	    if (NodeType.isListItem(this)) {
	      return this.getChildren().reduceRight((acc, node) => {
	        if (!main_core.Type.isArrayFilled(acc) && (NodeType.isNewLine(node) || NodeType.isTab(node))) {
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
	    const tagName = this.getName();
	    const tagValue = this.toStringValue();
	    const attributes = this.toStringAttributes();
	    const formattedAttributes = main_core.Type.isStringFilled(attributes) ? ` ${attributes}` : '';
	    return `[${tagName}${tagValue}${formattedAttributes}]`;
	  }
	  getClosingTag() {
	    return `[/${this.getName()}]`;
	  }
	  toString() {
	    const openingTag = this.getOpeningTag();
	    if (this.isVoid()) {
	      return openingTag;
	    }
	    if (NodeType.isListItem(this)) {
	      return `${openingTag}${this.getContent()}${this.getNewLineAfterContent()}`;
	    }
	    if (this.isInline()) {
	      return `${openingTag}${this.getContent()}${this.getClosingTag()}`;
	    }
	    return [this.getNewLineBeforeOpeningTag(), openingTag, this.getNewLineBeforeContent(), this.getContent(), this.getNewLineAfterContent(), this.getClosingTag(), this.getNewLineAfterClosingTag()].join('');
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
	    privateMap.get(this).type = Node.ROOT_NODE;
	    RootNode.freezeProperty(this, 'name', '#root', false);
	    RootNode.makeNonEnumerableProperty(this, 'value');
	    RootNode.makeNonEnumerableProperty(this, 'void');
	    RootNode.makeNonEnumerableProperty(this, 'inline');
	    RootNode.makeNonEnumerableProperty(this, 'attributes');
	  }
	  getParent() {
	    return null;
	  }
	  setName(name) {}
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

	class NewLineNode extends TextNode {
	  constructor(options = {}) {
	    super(options);
	    this[nameSymbol] = '#linebreak';
	    this[contentSymbol] = NEW_LINE;
	  }
	  setContent(options) {}
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
	}

	class TabNode extends TextNode {
	  constructor(options = {}) {
	    super(options);
	    this[nameSymbol] = '#tab';
	    this[contentSymbol] = TAB;
	  }
	  setContent(options) {}
	}

	const TAG_REGEX = /\[(\/)?(\w+|\*)([\s\w./:=]+)?]/gs;
	class Parser {
	  static toLowerCase(value) {
	    if (main_core.Type.isStringFilled(value)) {
	      return value.toLowerCase();
	    }
	    return value;
	  }
	  parseText(text) {
	    if (main_core.Type.isStringFilled(text)) {
	      return [...text].reduce((acc, symbol) => {
	        if (SPECIAL_CHARS.has(symbol)) {
	          acc.push(symbol);
	        } else {
	          const lastItem = acc.at(-1);
	          if (SPECIAL_CHARS.has(lastItem) || main_core.Type.isNil(lastItem)) {
	            acc.push(symbol);
	          } else {
	            acc[acc.length - 1] += symbol;
	          }
	        }
	        return acc;
	      }, []).map(fragment => {
	        if (fragment === NEW_LINE) {
	          return new NewLineNode();
	        }
	        if (fragment === TAB) {
	          return new TabNode();
	        }
	        return new TextNode(fragment);
	      });
	    }
	    return [];
	  }
	  static findNextTagIndex(bbcode, startIndex = 0) {
	    const nextContent = bbcode.slice(startIndex);
	    const [nextTag] = nextContent.match(new RegExp(TAG_REGEX)) || [];
	    if (nextTag) {
	      return bbcode.indexOf(nextTag, startIndex);
	    }
	    return -1;
	  }
	  parseAttributes(sourceAttributes) {
	    const result = {
	      value: '',
	      attributes: []
	    };
	    if (main_core.Type.isStringFilled(sourceAttributes)) {
	      return sourceAttributes.trim().split(' ').filter(Boolean).reduce((acc, item) => {
	        if (item.startsWith('=')) {
	          acc.value = item.slice(1);
	          return acc;
	        }
	        const [key, value = ''] = item.split('=');
	        acc.attributes.push([Parser.toLowerCase(key), value]);
	        return acc;
	      }, result);
	    }
	    return result;
	  }
	  parse(bbcode) {
	    const result = new RootNode();
	    const stack = [];
	    let current = null;
	    let level = -1;
	    const firstTagIndex = Parser.findNextTagIndex(bbcode);
	    if (firstTagIndex !== 0) {
	      const textBeforeFirstTag = firstTagIndex === -1 ? bbcode : bbcode.slice(0, firstTagIndex);
	      result.appendChild(...this.parseText(textBeforeFirstTag));
	    }
	    bbcode.replace(TAG_REGEX, (fullTag, slash, tagName, attrs, index) => {
	      const isOpenTag = Boolean(slash) === false;
	      const startIndex = fullTag.length + index;
	      const nextContent = bbcode.slice(startIndex);
	      const attributes = this.parseAttributes(attrs);
	      const lowerCaseTagName = Parser.toLowerCase(tagName);
	      let parent = null;
	      if (isOpenTag) {
	        level++;
	        if (nextContent.includes(`[/${tagName}]`) || LIST_ITEM_ELEMENTS.has(lowerCaseTagName)) {
	          current = new ElementNode({
	            name: lowerCaseTagName,
	            value: attributes.value,
	            attributes: Object.fromEntries(attributes.attributes)
	          });
	          const nextTagIndex = Parser.findNextTagIndex(bbcode, startIndex);
	          if (nextTagIndex !== 0) {
	            const content = nextTagIndex === -1 ? nextContent : bbcode.slice(startIndex, nextTagIndex);
	            current.appendChild(...this.parseText(content));
	          }
	        } else {
	          current = new ElementNode({
	            name: lowerCaseTagName,
	            value: attributes.value,
	            attributes: Object.fromEntries(attributes.attributes),
	            void: true
	          });
	        }
	        if (level === 0) {
	          result.appendChild(current);
	        }
	        parent = stack[level - 1];
	        if (LIST_ELEMENTS.has(current.getName())) {
	          if (parent && LIST_ELEMENTS.has(parent.getName())) {
	            stack[level].appendChild(current);
	          }
	        } else if (parent && LIST_ELEMENTS.has(parent.getName()) && !LIST_ITEM_ELEMENTS.has(current.getName())) {
	          const lastItem = parent.getChildren().at(-1);
	          if (lastItem) {
	            lastItem.appendChild(current);
	          }
	        } else if (parent) {
	          parent.appendChild(current);
	        }
	        stack[level] = current;
	        if (LIST_ITEM_ELEMENTS.has(lowerCaseTagName) && level > -1) {
	          level--;
	          current = level === -1 ? result : stack[level];
	        }
	      }
	      if (!isOpenTag || current.isVoid()) {
	        if (level > -1 && current.getName() === lowerCaseTagName) {
	          level--;
	          current = level === -1 ? result : stack[level];
	        }
	        const nextTagIndex = Parser.findNextTagIndex(bbcode, startIndex);
	        if (nextTagIndex !== startIndex) {
	          parent = level === -1 ? result : stack[level];
	          const content = bbcode.slice(startIndex, nextTagIndex === -1 ? undefined : nextTagIndex);
	          if (LIST_ELEMENTS.has(parent.getName())) {
	            const lastItem = parent.getChildren().at(-1);
	            if (lastItem) {
	              lastItem.appendChild(...this.parseText(content));
	            }
	          } else {
	            parent.appendChild(...this.parseText(content));
	          }
	        }
	      }
	    });
	    return result;
	  }
	}

	exports.Parser = Parser;
	exports.RootNode = RootNode;
	exports.Node = Node;
	exports.ElementNode = ElementNode;
	exports.TextNode = TextNode;
	exports.NewLineNode = NewLineNode;
	exports.FragmentNode = FragmentNode;
	exports.TabNode = TabNode;

}((this.BX.UI.Bbcode = this.BX.UI.Bbcode || {}),BX));
//# sourceMappingURL=parser.bundle.js.map

/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_core) {
	'use strict';

	class RootNode {
	  constructor(options = {}) {
	    this.children = [];
	    this.setChildren(options.children);
	  }
	  setChildren(children) {
	    if (main_core.Type.isArray(children)) {
	      this.children = [...children];
	    }
	  }
	  appendChild(...children) {
	    this.children.push(...children);
	  }
	  replaceChild(targetNode, ...children) {
	    this.children = this.children.flatMap(node => {
	      if (node === targetNode) {
	        return children;
	      }
	      return node;
	    });
	  }
	  getChildren() {
	    return [...this.children];
	  }
	  toString() {
	    return this.getChildren().map(child => {
	      return child.toString();
	    }).join('');
	  }
	}

	class Node {
	  constructor(options = {}) {
	    this.name = '';
	    this.value = '';
	    this.attributes = {};
	    this.children = [];
	    this.parent = null;
	    this.setName(options.name);
	    this.setValue(options.value);
	    this.setAttributes(options.attributes);
	    this.setChildren(options.children);
	    this.setParent(options.parent);
	  }
	  setName(name) {
	    if (main_core.Type.isStringFilled(name)) {
	      this.name = name;
	    }
	  }
	  getName() {
	    return this.name;
	  }
	  setValue(value) {
	    if (main_core.Type.isStringFilled(value) || main_core.Type.isNumber(value)) {
	      this.value = value;
	    } else {
	      this.value = '';
	    }
	  }
	  getValue() {
	    return this.value;
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
	  setChildren(children) {
	    if (main_core.Type.isArray(children)) {
	      this.children = [...children];
	    }
	  }
	  appendChild(...children) {
	    this.children.push(...children);
	  }
	  replaceChild(targetNode, ...children) {
	    this.children = this.children.flatMap(node => {
	      if (node === targetNode) {
	        return children;
	      }
	      return node;
	    });
	  }
	  getChildren() {
	    return [...this.children];
	  }
	  setParent(node) {
	    this.parent = node;
	  }
	  getParent() {
	    return this.parent;
	  }
	  toString() {
	    const value = this.getValue();
	    const valueString = value ? `=${value}` : '';
	    const attributes = Object.entries(this.getAttributes()).map(([key, attrValue]) => {
	      return attrValue ? `${key}=${attrValue}` : key;
	    }).join(' ');
	    const children = this.getChildren().map(child => {
	      return child.toString();
	    }).join('');

	    // eslint-disable-next-line sonarjs/no-nested-template-literals
	    return `[${this.getName()}${valueString}${attributes ? ` ${attributes}` : ''}]${children}[/${this.getName()}]`;
	  }
	}

	class VoidNode {
	  constructor(options = {}) {
	    this.name = '';
	    this.value = '';
	    this.attributes = {};
	    this.parent = null;
	    this.setName(options.name);
	    this.setValue(options.value);
	    this.setAttributes(options.attributes);
	    this.setParent(options.parent);
	  }
	  setName(name) {
	    if (main_core.Type.isStringFilled(name)) {
	      this.name = name;
	    }
	  }
	  getName() {
	    return this.name;
	  }
	  setValue(value) {
	    if (main_core.Type.isStringFilled(value) || main_core.Type.isNumber(value)) {
	      this.value = value;
	    } else {
	      this.value = '';
	    }
	  }
	  getValue() {
	    return this.value;
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
	  getAttributes() {
	    return {
	      ...this.attributes
	    };
	  }
	  getAttribute(key) {
	    return this.attributes[key];
	  }
	  setParent(node) {
	    this.parent = node;
	  }
	  getParent() {
	    return this.parent;
	  }
	  toString() {
	    const value = this.getValue();
	    const valueString = value ? `=${value}` : '';
	    const attributes = Object.entries(this.getAttributes()).map(([key, attrValue]) => {
	      return attrValue ? `${key}=${attrValue}` : key;
	    }).join(' ');
	    return `[${this.getName()}${valueString}${attributes ? ` ${attributes}` : ''}]`;
	  }
	}

	class TextNode {
	  constructor(options = {}) {
	    this.content = '';
	    this.parent = null;
	    this.setContent(options.content);
	    this.setParent(options.parent);
	  }
	  setContent(content) {
	    if (main_core.Type.isString(content) || main_core.Type.isNumber(content)) {
	      this.content = content;
	    }
	  }
	  getContent() {
	    return this.content;
	  }
	  setParent(node) {
	    this.parent = node;
	  }
	  getParent() {
	    return this.parent;
	  }
	  toString() {
	    return this.getContent();
	  }
	}

	class NewLineNode extends TextNode {}

	const TAG_REGEX = /\[(\/)?(\w+|\*)([\s\w./:=]+)?]/gs;
	class Parser {
	  constructor(options = {}) {
	    this.options = {
	      tagNameCase: 'lowerCase',
	      attributeNameCase: 'lowerCase',
	      ...options
	    };
	  }
	  static prepareCase(value, resultCase) {
	    if (main_core.Type.isStringFilled(value)) {
	      if (resultCase === 'lowerCase') {
	        return value.toLowerCase();
	      }
	      if (resultCase === 'upperCase') {
	        return value.toUpperCase();
	      }
	    }
	    return value;
	  }
	  prepareTagNameCase(name) {
	    return Parser.prepareCase(name, this.options.tagNameCase);
	  }
	  prepareAttributeNameCase(name) {
	    return Parser.prepareCase(name, this.options.attributeNameCase);
	  }
	  parseText(text, parent = null) {
	    if (main_core.Type.isStringFilled(text)) {
	      const fragments = (() => {
	        const result = text.split('\n');
	        if (/^\n+$/g.test(text)) {
	          return result.slice(1);
	        }
	        return result;
	      })();
	      return fragments.map(fragment => {
	        if (main_core.Type.isStringFilled(fragment)) {
	          return new TextNode({
	            content: fragment,
	            parent
	          });
	        }
	        return new NewLineNode({
	          content: '\n',
	          parent
	        });
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
	        acc.attributes.push([this.prepareAttributeNameCase(key), value]);
	        return acc;
	      }, result);
	    }
	    return result;
	  }
	  isListTag(tagName) {
	    return ['list', 'ul', 'ol'].includes(tagName);
	  }
	  isListItemTag(tagName) {
	    return ['*', 'li'].includes(tagName);
	  }

	  // eslint-disable-next-line sonarjs/cognitive-complexity
	  parse(bbcode) {
	    const result = new RootNode();
	    const stack = [];
	    let level = -1;
	    let current = null;
	    const firstTagIndex = Parser.findNextTagIndex(bbcode);
	    if (firstTagIndex !== 0) {
	      const textBeforeFirstTag = firstTagIndex === -1 ? bbcode : bbcode.slice(0, firstTagIndex);
	      // eslint-disable-next-line @bitrix24/bitrix24-rules/no-native-dom-methods
	      result.appendChild(...this.parseText(textBeforeFirstTag));
	    }
	    bbcode.replace(TAG_REGEX, (fullTag, slash, tagName, attrs, index) => {
	      const isOpenTag = Boolean(slash) === false;
	      const startIndex = fullTag.length + index;
	      const nextContent = bbcode.slice(startIndex);
	      const attributes = this.parseAttributes(attrs);
	      const caseSensitivityTagName = this.prepareTagNameCase(tagName);
	      let parent = null;
	      if (isOpenTag) {
	        level++;
	        if (nextContent.includes(`[/${tagName}]`) || this.isListItemTag(caseSensitivityTagName)) {
	          current = new Node({
	            name: caseSensitivityTagName,
	            value: attributes.value,
	            attributes: Object.fromEntries(attributes.attributes)
	          });
	          const nextTagIndex = Parser.findNextTagIndex(bbcode, startIndex);
	          if (nextTagIndex !== 0) {
	            const content = nextTagIndex === -1 ? nextContent : bbcode.slice(startIndex, nextTagIndex);
	            // eslint-disable-next-line @bitrix24/bitrix24-rules/no-native-dom-methods
	            current.appendChild(...this.parseText(content, current));
	          }
	        } else {
	          current = new VoidNode({
	            name: caseSensitivityTagName,
	            value: attributes.value,
	            attributes: Object.fromEntries(attributes.attributes)
	          });
	        }
	        if (level === 0) {
	          // eslint-disable-next-line @bitrix24/bitrix24-rules/no-native-dom-methods
	          result.appendChild(current);
	        }
	        parent = stack[level - 1];
	        if (this.isListTag(current.getName())) {
	          if (parent && this.isListTag(parent.getName())) {
	            current.setParent(stack[level]);
	            // eslint-disable-next-line @bitrix24/bitrix24-rules/no-native-dom-methods
	            stack[level].appendChild(current);
	          }
	        } else if (parent) {
	          current.setParent(parent);
	          // eslint-disable-next-line @bitrix24/bitrix24-rules/no-native-dom-methods
	          parent.appendChild(current);
	        }
	        stack[level] = current;
	        if (this.isListItemTag(caseSensitivityTagName) && level > -1) {
	          level--;
	          current = level === -1 ? result : stack[level];
	        }
	      }
	      if (!isOpenTag || current instanceof VoidNode) {
	        if (level > -1 && current.getName() === caseSensitivityTagName) {
	          level--;
	          current = level === -1 ? result : stack[level];
	        }
	        const nextTagIndex = Parser.findNextTagIndex(bbcode, startIndex);
	        if (nextTagIndex !== startIndex) {
	          parent = level === -1 ? result : stack[level];
	          const content = bbcode.slice(startIndex, nextTagIndex);
	          // eslint-disable-next-line @bitrix24/bitrix24-rules/no-native-dom-methods
	          parent.appendChild(...this.parseText(content, current));
	        }
	      }
	    });
	    return result;
	  }
	}

	exports.Parser = Parser;
	exports.RootNode = RootNode;
	exports.Node = Node;
	exports.TextNode = TextNode;
	exports.NewLineNode = NewLineNode;
	exports.VoidNode = VoidNode;

}((this.BX.UI.Bbcode = this.BX.UI.Bbcode || {}),BX));
//# sourceMappingURL=parser.bundle.js.map

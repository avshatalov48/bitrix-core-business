/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_core,ui_bbcode_model) {
	'use strict';

	const TAG_REGEX = /\[(\/)?(\w+|\*)([\s\w./:=]+)?]/gs;
	class Parser {
	  constructor(options = {}) {
	    if (options.factory) {
	      this.setFactory(options.factory);
	    } else {
	      this.setFactory(new ui_bbcode_model.ModelFactory());
	    }
	  }
	  setFactory(factory) {
	    this.factory = factory;
	  }
	  getFactory() {
	    return this.factory;
	  }
	  static toLowerCase(value) {
	    if (main_core.Type.isStringFilled(value)) {
	      return value.toLowerCase();
	    }
	    return value;
	  }
	  parseText(text) {
	    const factory = this.getFactory();
	    if (main_core.Type.isStringFilled(text)) {
	      return [...text].reduce((acc, symbol) => {
	        if (ui_bbcode_model.Text.isSpecialCharContent(symbol)) {
	          acc.push(symbol);
	        } else {
	          const lastItem = acc.at(-1);
	          if (ui_bbcode_model.Text.isSpecialCharContent(lastItem) || main_core.Type.isNil(lastItem)) {
	            acc.push(symbol);
	          } else {
	            acc[acc.length - 1] += symbol;
	          }
	        }
	        return acc;
	      }, []).map(fragment => {
	        if (ui_bbcode_model.Text.isNewLineContent(fragment)) {
	          return factory.createNewLineNode();
	        }
	        if (ui_bbcode_model.Text.isTabContent(fragment)) {
	          return factory.createTabNode();
	        }
	        return factory.createTextNode({
	          content: fragment
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
	        acc.attributes.push([Parser.toLowerCase(key), value]);
	        return acc;
	      }, result);
	    }
	    return result;
	  }
	  parse(bbcode) {
	    const factory = this.getFactory();
	    const result = factory.createRootNode();
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
	        if (nextContent.includes(`[/${tagName}]`) || ui_bbcode_model.Tag.isListItem(lowerCaseTagName)) {
	          current = factory.createElementNode({
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
	          current = factory.createElementNode({
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
	        if (ui_bbcode_model.Tag.isList(current.getName())) {
	          if (parent && ui_bbcode_model.Tag.isList(parent.getName())) {
	            stack[level].appendChild(current);
	          }
	        } else if (parent && ui_bbcode_model.Tag.isList(parent.getName()) && !ui_bbcode_model.Tag.isListItem(current.getName())) {
	          const lastItem = parent.getChildren().at(-1);
	          if (lastItem) {
	            lastItem.appendChild(current);
	          }
	        } else if (parent) {
	          parent.appendChild(current);
	        }
	        stack[level] = current;
	        if (ui_bbcode_model.Tag.isListItem(lowerCaseTagName) && level > -1) {
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
	          if (ui_bbcode_model.Tag.isList(parent.getName())) {
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

}((this.BX.UI.Bbcode = this.BX.UI.Bbcode || {}),BX,BX.UI.Bbcode));
//# sourceMappingURL=parser.bundle.js.map

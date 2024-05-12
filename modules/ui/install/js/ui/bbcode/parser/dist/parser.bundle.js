/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_core,ui_bbcode_model) {
	'use strict';

	function getByIndex(array, index) {
	  if (!main_core.Type.isArray(array)) {
	    throw new TypeError('array is not a array');
	  }
	  if (!main_core.Type.isInteger(index)) {
	    throw new TypeError('index is not a integer');
	  }
	  const preparedIndex = index < 0 ? array.length + index : index;
	  return array[preparedIndex];
	}

	class ParserScheme extends ui_bbcode_model.BBCodeScheme {
	  getTagScheme(tagName) {
	    if (tagName === 'code') {
	      return new ui_bbcode_model.BBCodeTagScheme({
	        name: 'code',
	        convertChild: (child, scheme) => {
	          if (['#linebreak', '#tab', '#text'].includes(child.getName())) {
	            return child;
	          }
	          return scheme.createText(child.toString());
	        }
	      });
	    }
	    return new ui_bbcode_model.BBCodeTagScheme({
	      name: 'any'
	    });
	  }
	  isAllowedTag(tagName) {
	    return true;
	  }
	  isChildAllowed(parent, child) {
	    return true;
	  }
	}

	const TAG_REGEX = /\[(\/)?(\w+|\*)(.*?)]/gs;
	const isSpecialChar = symbol => {
	  return ['\n', '\t'].includes(symbol);
	};
	const isList = tagName => {
	  return ['list', 'ul', 'ol'].includes(String(tagName).toLowerCase());
	};
	const isListItem = tagName => {
	  return ['*', 'li'].includes(String(tagName).toLowerCase());
	};
	const parserScheme = new ParserScheme();
	class BBCodeParser {
	  constructor(options = {}) {
	    if (options.scheme) {
	      this.setScheme(options.scheme);
	    } else {
	      this.setScheme(new ui_bbcode_model.DefaultBBCodeScheme());
	    }
	    if (main_core.Type.isFunction(options.onUnknown)) {
	      this.setOnUnknown(options.onUnknown);
	    } else {
	      this.setOnUnknown(BBCodeParser.defaultOnUnknownHandler);
	    }
	  }
	  setScheme(scheme) {
	    this.scheme = scheme;
	  }
	  getScheme() {
	    return this.scheme;
	  }
	  setOnUnknown(handler) {
	    if (!main_core.Type.isFunction(handler)) {
	      throw new TypeError('handler is not a function');
	    }
	    this.onUnknownHandler = handler;
	  }
	  getOnUnknownHandler() {
	    return this.onUnknownHandler;
	  }
	  static defaultOnUnknownHandler(node, scheme) {
	    if (node.getType() === ui_bbcode_model.BBCodeNode.ELEMENT_NODE) {
	      const nodeName = node.getName();
	      if (['left', 'center', 'right', 'justify'].includes(nodeName)) {
	        node.replace(scheme.createElement({
	          name: 'p',
	          children: node.getChildren()
	        }));
	      } else if (['background', 'color', 'size'].includes(nodeName)) {
	        node.replace(scheme.createElement({
	          name: 'b',
	          children: node.getChildren()
	        }));
	      } else {
	        const openingTag = node.getOpeningTag();
	        const closingTag = node.getClosingTag();
	        node.replace(scheme.createText(openingTag), ...node.getChildren(), scheme.createText(closingTag));
	      }
	    }
	  }
	  static toLowerCase(value) {
	    if (main_core.Type.isStringFilled(value)) {
	      return value.toLowerCase();
	    }
	    return value;
	  }
	  parseText(text) {
	    if (main_core.Type.isStringFilled(text)) {
	      return [...text].reduce((acc, symbol) => {
	        if (isSpecialChar(symbol)) {
	          acc.push(symbol);
	        } else {
	          const lastItem = getByIndex(acc, -1);
	          if (isSpecialChar(lastItem) || main_core.Type.isNil(lastItem)) {
	            acc.push(symbol);
	          } else {
	            acc[acc.length - 1] += symbol;
	          }
	        }
	        return acc;
	      }, []).map(fragment => {
	        if (fragment === '\n') {
	          return parserScheme.createNewLine();
	        }
	        if (fragment === '\t') {
	          return parserScheme.createTab();
	        }
	        return parserScheme.createText({
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
	  static trimQuotes(value) {
	    const source = String(value);
	    if (/^["'].*["']$/g.test(source)) {
	      return source.slice(1, -1);
	    }
	    return value;
	  }
	  parseAttributes(sourceAttributes) {
	    const result = {
	      value: '',
	      attributes: []
	    };
	    if (main_core.Type.isStringFilled(sourceAttributes)) {
	      if (sourceAttributes.startsWith('=')) {
	        result.value = BBCodeParser.trimQuotes(sourceAttributes.slice(1));
	        return result;
	      }
	      return sourceAttributes.trim().split(' ').filter(Boolean).reduce((acc, item) => {
	        const [key, value = ''] = item.split('=');
	        acc.attributes.push([BBCodeParser.toLowerCase(key), BBCodeParser.trimQuotes(value)]);
	        return acc;
	      }, result);
	    }
	    return result;
	  }
	  parse(bbcode) {
	    const result = parserScheme.createRoot();
	    const firstTagIndex = BBCodeParser.findNextTagIndex(bbcode);
	    if (firstTagIndex !== 0) {
	      const textBeforeFirstTag = firstTagIndex === -1 ? bbcode : bbcode.slice(0, firstTagIndex);
	      result.appendChild(...this.parseText(textBeforeFirstTag));
	    }
	    const stack = [result];
	    const wasOpened = [];
	    let current = null;
	    let level = 0;
	    bbcode.replace(TAG_REGEX, (fullTag, slash, tagName, attrs, index) => {
	      const isOpeningTag = Boolean(slash) === false;
	      const startIndex = fullTag.length + index;
	      const nextContent = bbcode.slice(startIndex);
	      const attributes = this.parseAttributes(attrs);
	      const lowerCaseTagName = BBCodeParser.toLowerCase(tagName);
	      let parent = stack[level];
	      if (isOpeningTag) {
	        const isPotentiallyVoid = !nextContent.includes(`[/${tagName}]`);
	        if (isPotentiallyVoid && !isListItem(lowerCaseTagName)) {
	          const tagScheme = this.getScheme().getTagScheme(lowerCaseTagName);
	          const isAllowedVoidTag = tagScheme && tagScheme.isVoid();
	          if (isAllowedVoidTag) {
	            current = parserScheme.createElement({
	              name: lowerCaseTagName,
	              value: attributes.value,
	              attributes: Object.fromEntries(attributes.attributes)
	            });
	            current.setScheme(this.getScheme());
	            parent.appendChild(current);
	          } else {
	            parent.appendChild(parserScheme.createText(fullTag));
	          }
	          const nextTagIndex = BBCodeParser.findNextTagIndex(bbcode, startIndex);
	          if (nextTagIndex !== 0) {
	            const content = nextTagIndex === -1 ? nextContent : bbcode.slice(startIndex, nextTagIndex);
	            parent.appendChild(...this.parseText(content));
	          }
	        } else {
	          if (isListItem(lowerCaseTagName) && current && isListItem(current.getName())) {
	            level--;
	            parent = stack[level];
	          }
	          current = parserScheme.createElement({
	            name: lowerCaseTagName,
	            value: attributes.value,
	            attributes: Object.fromEntries(attributes.attributes)
	          });
	          const nextTagIndex = BBCodeParser.findNextTagIndex(bbcode, startIndex);
	          if (nextTagIndex !== 0) {
	            const content = nextTagIndex === -1 ? nextContent : bbcode.slice(startIndex, nextTagIndex);
	            current.appendChild(...this.parseText(content));
	          }
	          parent.appendChild(current);
	          level++;
	          stack[level] = current;
	          wasOpened.push(lowerCaseTagName);
	        }
	      } else {
	        if (wasOpened.includes(lowerCaseTagName)) {
	          level--;
	          const openedTagIndex = wasOpened.indexOf(lowerCaseTagName);
	          wasOpened.splice(openedTagIndex, 1);
	        } else {
	          stack[level].appendChild(parserScheme.createText(fullTag));
	        }
	        if (isList(lowerCaseTagName) && level > 0) {
	          level--;
	        }
	        const nextTagIndex = BBCodeParser.findNextTagIndex(bbcode, startIndex);
	        if (nextTagIndex !== 0 && stack[level]) {
	          const content = nextTagIndex === -1 ? nextContent : bbcode.slice(startIndex, nextTagIndex);
	          stack[level].appendChild(...this.parseText(content));
	        }
	        if (isListItem(stack[level].getName()) && level > 0) {
	          level--;
	        }
	      }
	    });
	    const getFinalLineBreaksIndexes = node => {
	      let skip = false;
	      return node.getChildren().reduceRight((acc, child, index) => {
	        if (!skip && child.getName() === '#linebreak') {
	          acc.push(index);
	        } else if (!skip && child.getName() !== '#tab') {
	          skip = true;
	        }
	        return acc;
	      }, []);
	    };
	    ui_bbcode_model.BBCodeNode.flattenAst(result).forEach(node => {
	      if (node.getName() === '*') {
	        const finalLinebreaksIndexes = getFinalLineBreaksIndexes(node);
	        if (finalLinebreaksIndexes.length === 1) {
	          node.setChildren(node.getChildren().slice(0, getByIndex(finalLinebreaksIndexes, 0)));
	        }
	        if (finalLinebreaksIndexes.length > 1 && (finalLinebreaksIndexes & 2) === 0) {
	          node.setChildren(node.getChildren().slice(0, getByIndex(finalLinebreaksIndexes, 0)));
	        }
	      }
	    });
	    result.setScheme(this.getScheme(), this.getOnUnknownHandler());
	    return result;
	  }
	}

	exports.BBCodeParser = BBCodeParser;

}((this.BX.UI.Bbcode = this.BX.UI.Bbcode || {}),BX,BX.UI.Bbcode));
//# sourceMappingURL=parser.bundle.js.map

/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
this.BX.UI.BBCode = this.BX.UI.BBCode || {};
(function (exports,ui_smiley,ui_codeParser,ui_bbcode_model,ui_videoService,ui_bbcode_formatter,ui_typography,main_core) {
	'use strict';

	function createEmptyParagraphs(scheme, count = 1) {
	  const result = [];
	  for (let i = 0; i < count; i++) {
	    result.push(scheme.createElement({
	      name: 'p'
	    }));
	  }
	  return result;
	}

	function shouldWrapInParagraph(node) {
	  return node.getType() !== ui_bbcode_model.BBCodeNode.ELEMENT_NODE || node.hasGroup('#inline') || node.hasGroup('#inlineBlock');
	}

	function wrapTextNodes(nodes, scheme) {
	  const result = [];
	  let currentParagraph = null;
	  let lineBreaks = 0;
	  for (const node of nodes) {
	    if (scheme.isNewLine(node)) {
	      lineBreaks++;
	      continue;
	    }
	    if (shouldWrapInParagraph(node)) {
	      if (currentParagraph === null || lineBreaks >= 2) {
	        result.push(...createEmptyParagraphs(scheme, lineBreaks - 2));
	        currentParagraph = scheme.createElement({
	          name: 'p'
	        });
	        result.push(currentParagraph);
	      } else if (lineBreaks === 1) {
	        currentParagraph.appendChild(scheme.createNewLine());
	      }
	      currentParagraph.appendChild(node);
	    } else {
	      if (lineBreaks > 2) {
	        result.push(...createEmptyParagraphs(scheme, lineBreaks - 2));
	      }
	      result.push(node);
	      currentParagraph = null;
	    }
	    lineBreaks = 0;
	  }

	  // to avoid a height collapsing for empty elements
	  if (result.length === 0) {
	    return [scheme.createElement({
	      name: 'p'
	    })];
	  }
	  return result;
	}

	function normalizeTextNodes(node) {
	  const scheme = node.getScheme();
	  const children = wrapTextNodes(node.getChildren(), scheme);
	  node.setChildren(children);
	  return node;
	}

	let _ = t => t,
	  _t;
	class RootNodeFormatter extends ui_bbcode_formatter.NodeFormatter {
	  constructor(options = {}) {
	    super({
	      name: '#root',
	      convert() {
	        return document.createDocumentFragment();
	      },
	      before({
	        node
	      }) {
	        return normalizeTextNodes(node);
	      },
	      after({
	        element,
	        formatter
	      }) {
	        const mode = formatter.getContainerMode();
	        if (mode === 'void' || mode === 'collapsed') {
	          const container = main_core.Tag.render(_t || (_t = _`<div class="ui-typography-container --${0}"></div>`), mode);
	          container.appendChild(element);
	          return container;
	        }
	        return element;
	      },
	      ...options
	    });
	  }
	}

	function findParent(startingNode, findFn) {
	  let curr = startingNode;
	  while (curr !== null && curr.getType() !== ui_bbcode_model.BBCodeNode.ROOT_NODE) {
	    if (findFn(curr)) {
	      return curr;
	    }
	    curr = curr.getParent();
	  }
	  return null;
	}

	var _smileyParser = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("smileyParser");
	class TextNodeFormatter extends ui_bbcode_formatter.NodeFormatter {
	  constructor(options = {}) {
	    super({
	      name: '#text',
	      convert({
	        node
	      }) {
	        const text = node.toString({
	          encode: false
	        });
	        if (findParent(node, parent => parent.getName() === 'code')) {
	          return document.createTextNode(text);
	        }
	        const splits = babelHelpers.classPrivateFieldLooseBase(this, _smileyParser)[_smileyParser].parse(text);
	        if (splits.length === 0) {
	          return document.createTextNode(text);
	        }

	        // console.log('splits', splits);

	        const fragment = document.createDocumentFragment();
	        let currentIndex = 0;
	        for (const split of splits) {
	          if (currentIndex < split.start) {
	            const chunk = document.createTextNode(text.slice(currentIndex, split.start));
	            fragment.appendChild(chunk);
	          }
	          const typing = text.slice(split.start, split.end);
	          const smiley = ui_smiley.SmileyManager.get(typing);
	          if (smiley === null) {
	            fragment.appendChild(document.createTextNode(typing));
	          } else {
	            fragment.appendChild(this.createImg(smiley));
	          }
	          currentIndex = split.end;
	        }
	        if (currentIndex < text.length) {
	          const tail = document.createTextNode(text.slice(currentIndex));
	          fragment.appendChild(tail);
	        }
	        return fragment;
	      },
	      ...options
	    });
	    Object.defineProperty(this, _smileyParser, {
	      writable: true,
	      value: null
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _smileyParser)[_smileyParser] = new ui_smiley.SmileyParser(ui_smiley.SmileyManager.getAll());
	  }
	  createImg(smiley) {
	    const img = document.createElement('img');
	    img.src = encodeURI(smiley.getImage());
	    img.className = 'ui-typography-smiley';
	    img.alt = smiley.getTyping();
	    if (smiley.getWidth() > 0 && smiley.getHeight() > 0) {
	      img.width = smiley.getWidth();
	      img.height = smiley.getHeight();
	    }
	    return img;
	  }
	}

	class TabNodeFormatter extends ui_bbcode_formatter.NodeFormatter {
	  constructor(options = {}) {
	    super({
	      name: '#tab',
	      convert({
	        node
	      }) {
	        if (node.getParent().getName() === 'code') {
	          return document.createTextNode(node.toString());
	        }
	        return document.createTextNode(' ');
	      },
	      ...options
	    });
	  }
	}

	class LinebreakNodeFormatter extends ui_bbcode_formatter.NodeFormatter {
	  constructor(options = {}) {
	    super({
	      name: '#linebreak',
	      convert({
	        node
	      }) {
	        return document.createElement('br');
	      },
	      ...options
	    });
	  }
	}

	function trimLineBreaks(nodes) {
	  const trimmedNodes = [...nodes];
	  const firstNode = trimmedNodes[0];
	  const lastNode = trimmedNodes[trimmedNodes.length - 1];
	  if (isLineBreakNode(firstNode) || isParagraphNode(firstNode) && firstNode.isEmpty()) {
	    trimmedNodes.splice(0, 1);
	  }
	  if (isLineBreakNode(lastNode) || isParagraphNode(lastNode) && lastNode.isEmpty()) {
	    trimmedNodes.splice(-1, 1);
	  }
	  return trimmedNodes;
	}
	function isLineBreakNode(node) {
	  return node && node.getScheme().isNewLine(node);
	}
	function isParagraphNode(node) {
	  return node && node.getName() === 'p';
	}

	function normalizeLineBreaks(node) {
	  const scheme = node.getScheme();
	  const children = trimLineBreaks(node.getChildren(), scheme);
	  node.setChildren(children);

	  // to avoid a height collapsing for empty elements
	  if (children.length === 0 || !scheme.isNewLine(children.at(-1)) && /^\s*$/.test(node.getTextContent())) {
	    node.appendChild(scheme.createNewLine());
	  }
	  return node;
	}

	class ParagraphNodeFormatter extends ui_bbcode_formatter.NodeFormatter {
	  constructor(options = {}) {
	    super({
	      name: 'p',
	      convert({
	        node
	      }) {
	        return main_core.Dom.create({
	          tag: node.getName(),
	          attrs: {
	            className: 'ui-typography-paragraph'
	          }
	        });
	      },
	      before({
	        node
	      }) {
	        return normalizeLineBreaks(node);
	      },
	      ...options
	    });
	  }
	}

	class BoldNodeFormatter extends ui_bbcode_formatter.NodeFormatter {
	  constructor(options = {}) {
	    super({
	      name: 'b',
	      convert({
	        node
	      }) {
	        return main_core.Dom.create({
	          tag: 'b',
	          attrs: {
	            ...node.getAttributes(),
	            className: 'ui-typography-text-bold'
	          }
	        });
	      },
	      ...options
	    });
	  }
	}

	class UnderlineNodeFormatter extends ui_bbcode_formatter.NodeFormatter {
	  constructor(options = {}) {
	    super({
	      name: 'u',
	      convert({
	        node
	      }) {
	        return main_core.Dom.create({
	          tag: 'u',
	          attrs: {
	            ...node.getAttributes(),
	            className: 'ui-typography-text-underline'
	          }
	        });
	      },
	      ...options
	    });
	  }
	}

	class ItalicNodeFormatter extends ui_bbcode_formatter.NodeFormatter {
	  constructor(options = {}) {
	    super({
	      name: 'i',
	      convert({
	        node
	      }) {
	        return main_core.Dom.create({
	          tag: 'i',
	          attrs: {
	            ...node.getAttributes(),
	            className: 'ui-typography-text-italic'
	          }
	        });
	      },
	      ...options
	    });
	  }
	}

	class StrikethroughNodeFormatter extends ui_bbcode_formatter.NodeFormatter {
	  constructor(options = {}) {
	    super({
	      name: 's',
	      convert({
	        node
	      }) {
	        return main_core.Dom.create({
	          tag: 's',
	          attrs: {
	            ...node.getAttributes(),
	            className: 'ui-typography-text-strikethrough'
	          }
	        });
	      },
	      ...options
	    });
	  }
	}

	class TableNodeFormatter extends ui_bbcode_formatter.NodeFormatter {
	  constructor(options = {}) {
	    super({
	      name: 'table',
	      convert() {
	        return main_core.Dom.create({
	          tag: 'table',
	          attrs: {
	            classname: 'ui-typography-table'
	          }
	        });
	      },
	      ...options
	    });
	  }
	}

	class TableHeadCellNodeFormatter extends ui_bbcode_formatter.NodeFormatter {
	  constructor(options = {}) {
	    super({
	      name: 'th',
	      convert() {
	        return main_core.Dom.create({
	          tag: 'th',
	          attrs: {
	            classname: 'ui-typography-table-cell ui-typography-table-cell-header'
	          }
	        });
	      },
	      before({
	        node
	      }) {
	        return normalizeTextNodes(node);
	      },
	      ...options
	    });
	  }
	}

	class TableDataCellNodeFormatter extends ui_bbcode_formatter.NodeFormatter {
	  constructor(options = {}) {
	    super({
	      name: 'td',
	      convert() {
	        return main_core.Dom.create({
	          tag: 'td',
	          attrs: {
	            classname: 'ui-typography-table-cell'
	          }
	        });
	      },
	      before({
	        node
	      }) {
	        return normalizeTextNodes(node);
	      },
	      ...options
	    });
	  }
	}

	class TableRowNodeFormatter extends ui_bbcode_formatter.NodeFormatter {
	  constructor(options = {}) {
	    super({
	      name: 'tr',
	      convert() {
	        return main_core.Dom.create({
	          tag: 'tr',
	          attrs: {
	            classname: 'ui-typography-table-row'
	          }
	        });
	      },
	      ...options
	    });
	  }
	}

	class ListNodeFormatter extends ui_bbcode_formatter.NodeFormatter {
	  constructor(options) {
	    super({
	      name: 'list',
	      convert({
	        node
	      }) {
	        const tagName = node.getValue() === '1' ? 'ol' : 'ul';
	        return main_core.Dom.create({
	          tag: tagName,
	          attrs: {
	            ...node.getAttributes(),
	            className: `ui-typography-${tagName}`
	          }
	        });
	      },
	      ...options
	    });
	  }
	}

	class ListItemNodeFormatter extends ui_bbcode_formatter.NodeFormatter {
	  constructor(options) {
	    super({
	      name: '*',
	      convert({
	        node
	      }) {
	        return main_core.Dom.create({
	          tag: 'li',
	          attrs: {
	            ...node.getAttributes(),
	            className: 'ui-typography-li'
	          }
	        });
	      },
	      ...options
	    });
	  }
	}

	class QuoteNodeFormatter extends ui_bbcode_formatter.NodeFormatter {
	  constructor(options = {}) {
	    super({
	      name: 'quote',
	      convert() {
	        return main_core.Dom.create({
	          tag: 'blockquote',
	          attrs: {
	            className: 'ui-typography-quote'
	          }
	        });
	      },
	      before({
	        node
	      }) {
	        return normalizeTextNodes(node);
	      },
	      ...options
	    });
	  }
	}

	var _codeParser = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("codeParser");
	class CodeNodeFormatter extends ui_bbcode_formatter.NodeFormatter {
	  constructor(options = {}) {
	    super({
	      name: 'code',
	      before({
	        node
	      }) {
	        return normalizeLineBreaks(node);
	      },
	      convert({
	        node
	      }) {
	        const content = node.getTextContent();
	        return main_core.Dom.create({
	          tag: 'code',
	          attrs: {
	            className: 'ui-typography-code'
	          },
	          dataset: {
	            decorator: true
	          },
	          children: getCodeTokenNodes(babelHelpers.classPrivateFieldLooseBase(this, _codeParser)[_codeParser].parse(content))
	        });
	      },
	      ...options
	    });
	    Object.defineProperty(this, _codeParser, {
	      writable: true,
	      value: new ui_codeParser.CodeParser()
	    });
	  }
	}
	function getCodeTokenNodes(tokens) {
	  const nodes = [];
	  tokens.forEach(token => {
	    const partials = token.content.split(/([\t\n])/);
	    const partialsLength = partials.length;
	    for (let i = 0; i < partialsLength; i++) {
	      const part = partials[i];
	      if (part === '\n' || part === '\r\n') {
	        nodes.push(document.createElement('br'));
	      } else if (part === '\t') {
	        nodes.push(document.createTextNode('\t'));
	      } else if (part.length > 0) {
	        const span = document.createElement('span');
	        span.className = `ui-typography-token-${token.type}`;
	        span.textContent = part;
	        nodes.push(span);
	      }
	    }
	  });
	  return nodes;
	}

	class LinkNodeFormatter extends ui_bbcode_formatter.NodeFormatter {
	  constructor(options = {}) {
	    super({
	      name: 'url',
	      validate({
	        node
	      }) {
	        const nodeValue = LinkNodeFormatter.fetchNodeValue(node);
	        return !LinkNodeFormatter.startsWithJavascriptScheme(nodeValue);
	      },
	      before({
	        node,
	        formatter
	      }) {
	        if (formatter.isShortLinkEnabled()) {
	          const isIncludeImg = node.getChildren().some(node => {
	            return node.getName() === 'img';
	          });
	          if (!isIncludeImg) {
	            const scheme = node.getScheme();
	            const nodeContentLength = node.getPlainTextLength();
	            const {
	              shortLink
	            } = formatter.getLinkSettings();
	            if (nodeContentLength > shortLink.maxLength) {
	              const sourceHref = LinkNodeFormatter.fetchNodeValue(node);
	              const nodeRoot = scheme.createRoot({
	                children: node.getChildren()
	              });
	              const [left, right] = nodeRoot.split({
	                offset: shortLink.maxLength - shortLink.lastFragmentLength
	              });
	              const sourceRightFragmentLength = right.getPlainTextLength();
	              const newLink = node.clone();
	              newLink.setValue(sourceHref);
	              if (sourceRightFragmentLength > shortLink.lastFragmentLength) {
	                newLink.appendChild(...left.getChildren(), scheme.createText('...'));
	                const [, lastFragment] = right.split({
	                  offset: sourceRightFragmentLength - shortLink.lastFragmentLength
	                });
	                newLink.appendChild(...lastFragment.getChildren());
	                return newLink;
	              }
	              newLink.setChildren([...left.getChildren(), scheme.createText('...'), ...right.getChildren()]);
	              return newLink;
	            }
	          }
	        }
	        return node;
	      },
	      convert({
	        node,
	        formatter
	      }) {
	        const sourceHref = (() => {
	          const value = node.getValue();
	          if (main_core.Type.isStringFilled(value)) {
	            return value;
	          }
	          return node.getContent();
	        })();
	        const nodeAttributes = node.getAttributes();
	        const {
	          defaultTarget = '_blank',
	          attributes
	        } = formatter.getLinkSettings();
	        return main_core.Dom.create({
	          tag: 'a',
	          attrs: {
	            ...nodeAttributes,
	            ...attributes,
	            href: sourceHref,
	            target: defaultTarget,
	            className: 'ui-typography-link'
	          }
	        });
	      },
	      ...options
	    });
	  }
	  static fetchNodeValue(node) {
	    const value = node.getValue();
	    if (main_core.Type.isStringFilled(value)) {
	      return value;
	    }
	    return node.toPlainText();
	  }
	  static startsWithJavascriptScheme(sourceHref) {
	    if (main_core.Type.isStringFilled(sourceHref)) {
	      // eslint-disable-next-line no-control-regex
	      const regexp = /^[\u0000-\u001F ]*j[\t\n\r]*a[\t\n\r]*v[\t\n\r]*a[\t\n\r]*s[\t\n\r]*c[\t\n\r]*r[\t\n\r]*i[\t\n\r]*p[\t\n\r]*t[\t\n\r]*:/i;
	      return regexp.test(sourceHref);
	    }
	    return false;
	  }
	}

	class SpoilerNodeFormatter extends ui_bbcode_formatter.NodeFormatter {
	  constructor(options = {}) {
	    super({
	      name: 'spoiler',
	      convert({
	        node
	      }) {
	        const value = node.getValue().trim();
	        const title = main_core.Type.isStringFilled(value) ? value : main_core.Loc.getMessage('HTML_FORMATTER_SPOILER_TITLE');
	        return main_core.Dom.create({
	          tag: 'details',
	          attrs: {
	            className: 'ui-typography-spoiler ui-icon-set__scope'
	          },
	          children: [main_core.Dom.create({
	            tag: 'summary',
	            attrs: {
	              className: 'ui-typography-spoiler-title'
	            },
	            text: title
	          })]
	        });
	      },
	      before({
	        node
	      }) {
	        return normalizeTextNodes(node);
	      },
	      after({
	        element
	      }) {
	        const [summary, ...content] = element.children;
	        element.appendChild(summary);
	        element.appendChild(main_core.Dom.create({
	          tag: 'div',
	          attrs: {
	            className: 'ui-typography-spoiler-content'
	          },
	          dataset: {
	            spoilerContent: 'true'
	          },
	          children: [...content]
	        }));
	        return element;
	      },
	      ...options
	    });
	  }
	}

	class UserNodeFormatter extends ui_bbcode_formatter.NodeFormatter {
	  constructor(options) {
	    super({
	      name: 'user',
	      convert({
	        node,
	        formatter
	      }) {
	        var _mentionSettings$urlT;
	        const mentionSettings = formatter.getMentionSettings();
	        if (main_core.Type.isStringFilled(mentionSettings == null ? void 0 : (_mentionSettings$urlT = mentionSettings.urlTemplate) == null ? void 0 : _mentionSettings$urlT.user)) {
	          const urlTemplate = mentionSettings.urlTemplate.user;
	          const userUrl = urlTemplate.replaceAll('#ID#', node.getValue());
	          return main_core.Dom.create({
	            tag: 'a',
	            attrs: {
	              href: userUrl,
	              className: 'ui-typography-mention'
	            },
	            dataset: {
	              mentionEntityId: 'user',
	              mentionId: node.getValue()
	            }
	          });
	        }
	        return main_core.Dom.create({
	          tag: 'span',
	          attrs: {
	            className: 'ui-typography-mention'
	          },
	          dataset: {
	            mentionEntityId: 'user',
	            mentionId: node.getValue()
	          }
	        });
	      },
	      ...options
	    });
	  }
	}

	class DepartmentNodeFormatter extends ui_bbcode_formatter.NodeFormatter {
	  constructor(options = {}) {
	    super({
	      name: 'department',
	      convert({
	        node,
	        formatter
	      }) {
	        var _mentionSettings$urlT;
	        const mentionSettings = formatter.getMentionSettings();
	        if (main_core.Type.isStringFilled(mentionSettings == null ? void 0 : (_mentionSettings$urlT = mentionSettings.urlTemplate) == null ? void 0 : _mentionSettings$urlT.department)) {
	          const urlTemplate = mentionSettings.urlTemplate.department;
	          const departmentUrl = urlTemplate.replaceAll('#ID#', node.getValue());
	          return main_core.Dom.create({
	            tag: 'a',
	            attrs: {
	              href: departmentUrl,
	              className: 'ui-typography-mention'
	            },
	            dataset: {
	              mentionEntityId: 'department',
	              mentionId: node.getValue()
	            }
	          });
	        }
	        return main_core.Dom.create({
	          tag: 'span',
	          attrs: {
	            className: 'ui-typography-mention'
	          },
	          dataset: {
	            mentionEntityId: 'department',
	            mentionId: node.getValue()
	          }
	        });
	      },
	      ...options
	    });
	  }
	}

	class ProjectNodeFormatter extends ui_bbcode_formatter.NodeFormatter {
	  constructor(options = {}) {
	    super({
	      name: 'project',
	      convert({
	        node,
	        formatter
	      }) {
	        var _mentionSettings$urlT;
	        const mentionSettings = formatter.getMentionSettings();
	        if (main_core.Type.isStringFilled(mentionSettings == null ? void 0 : (_mentionSettings$urlT = mentionSettings.urlTemplate) == null ? void 0 : _mentionSettings$urlT.project)) {
	          const urlTemplate = mentionSettings.urlTemplate.project;
	          const projectUrl = urlTemplate.replaceAll('#group_id#', node.getValue());
	          return main_core.Dom.create({
	            tag: 'a',
	            attrs: {
	              href: projectUrl,
	              className: 'ui-typography-mention'
	            },
	            dataset: {
	              mentionEntityId: 'project',
	              mentionId: node.getValue()
	            }
	          });
	        }
	        return main_core.Dom.create({
	          tag: 'span',
	          attrs: {
	            className: 'ui-typography-mention'
	          },
	          dataset: {
	            mentionEntityId: 'project',
	            mentionId: node.getValue()
	          }
	        });
	      },
	      ...options
	    });
	  }
	}

	function createImageNode({
	  src,
	  width,
	  height
	}) {
	  return main_core.Dom.create({
	    tag: 'span',
	    attrs: {
	      className: 'ui-typography-image-container'
	    },
	    dataset: {
	      decorator: true
	    },
	    children: [main_core.Dom.create({
	      tag: 'img',
	      attrs: {
	        src,
	        className: 'ui-typography-image',
	        width,
	        loading: 'lazy'
	      },
	      style: {
	        aspectRatio: width > 0 && height > 0 ? `${width} / ${height}` : 'auto'
	      },
	      events: {
	        error: event => {
	          const img = event.target;
	          img.src = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
	          main_core.Dom.addClass(img.parentNode, '--error ui-icon-set__scope');
	        }
	      }
	    })]
	  });
	}

	function validateImageUrl(url) {
	  return /^(http:|https:|ftp:|\/)/i.test(url);
	}

	class ImageNodeFormatter extends ui_bbcode_formatter.NodeFormatter {
	  constructor(options = {}) {
	    super({
	      name: 'img',
	      convert({
	        node
	      }) {
	        // [img]{url}[/img]
	        // [img width={width} height={height}]{url}[/img]
	        const src = node.getContent().trim();
	        let width = Number(node.getAttribute('width'));
	        let height = Number(node.getAttribute('height'));
	        width = main_core.Type.isNumber(width) && width > 0 ? Math.round(width) : null;
	        height = main_core.Type.isNumber(height) && height > 0 ? Math.round(height) : null;
	        if (validateImageUrl(src)) {
	          return createImageNode({
	            src,
	            width,
	            height
	          });
	        }
	        return document.createTextNode(node.toString());
	      },
	      ...options
	    });
	  }
	}

	function createVideoNode({
	  url,
	  width,
	  height,
	  type
	}) {
	  const video = main_core.Dom.create({
	    tag: 'video',
	    attrs: {
	      controls: true,
	      className: 'ui-typography-video-object',
	      preload: 'metadata',
	      playsinline: true,
	      src: url,
	      width
	    },
	    dataset: {
	      decorator: true
	    },
	    style: {
	      aspectRatio: width > 0 && height > 0 ? `${width} / ${height}` : 'auto'
	    }
	    /* children: [
	    	Dom.create({
	    		tag: 'source',
	    		attrs: {
	    			type,
	    			src: url,
	    		},
	    	}),
	    ], */
	  });

	  return main_core.Dom.create({
	    tag: 'span',
	    attrs: {
	      className: 'ui-typography-video-container'
	    },
	    dataset: {
	      decorator: true
	    },
	    children: [video]
	  });
	}

	// eslint-disable-next-line no-control-regex
	const ATTRIBUTE_WHITESPACES = /[\u0000-\u0020\u00A0\u1680\u180E\u2000-\u2029\u205F\u3000]/g;
	const SAFE_URL = /^(?:(?:https?|ftps?|mailto):|[^a-z]|[+.a-z-]+(?:[^+.:a-z-]|$))/i;
	function sanitizeUrl(url) {
	  if (!main_core.Type.isStringFilled(url)) {
	    return '';
	  }
	  const normalizedUrl = url.replaceAll(ATTRIBUTE_WHITESPACES, '');
	  return SAFE_URL.test(normalizedUrl) ? normalizedUrl : '';
	}

	function validateVideoUrl(url) {
	  return /^(http:|https:|\/)/i.test(url);
	}

	class VideoNodeFormatter extends ui_bbcode_formatter.NodeFormatter {
	  constructor(options = {}) {
	    super({
	      name: 'video',
	      convert({
	        node
	      }) {
	        // [video type={type} width={width} height={height}]{url}[/video]
	        const src = sanitizeUrl(node.getContent().trim());
	        if (!validateVideoUrl(src)) {
	          return document.createTextNode(node.toString());
	        }
	        let width = Number(node.getAttribute('width'));
	        let height = Number(node.getAttribute('height'));
	        width = main_core.Type.isNumber(width) && width > 0 ? Math.round(width) : 560;
	        height = main_core.Type.isNumber(height) && height > 0 ? Math.round(height) : 315;
	        const url = /^https?:/.test(src) ? src : `https://${src.replace(/^\/\//, '')}`;
	        const uri = new main_core.Uri(url);
	        const trusted = ui_videoService.VideoService.createByHost(uri.getHost()) !== null;
	        if (trusted) {
	          const video = main_core.Dom.create({
	            tag: 'iframe',
	            attrs: {
	              src,
	              className: 'ui-typography-video-object',
	              width,
	              height: 'auto',
	              frameborder: 0,
	              allowfullscreen: true
	            },
	            style: {
	              aspectRatio: `${width} / ${height}`
	            }
	          });
	          return main_core.Dom.create({
	            tag: 'span',
	            attrs: {
	              className: 'ui-typography-video-container'
	            },
	            dataset: {
	              decorator: true
	            },
	            children: [video]
	          });
	        }

	        // const path = uri.getPath();
	        // const position: number = path.lastIndexOf('.');
	        // const extension = position >= 0 ? path.slice(Math.max(0, position + 1)).toLowerCase() : '';
	        // const type = ['mp4', 'webm', 'mov'].includes(extension) ? `video/${extension}` : null;

	        return createVideoNode({
	          url,
	          width,
	          height
	        });
	      },
	      ...options
	    });
	  }
	}

	class FileNodeFormatter extends ui_bbcode_formatter.NodeFormatter {
	  constructor(options = {}) {
	    const formatter = options.formatter;
	    const fileMode = formatter.getFileMode();
	    super({
	      name: fileMode || '__unknown__',
	      convert({
	        node,
	        data
	      }) {
	        if (fileMode === null) {
	          return node;
	        }
	        // [DISK FILE ID=n14194]
	        // [DISK FILE ID=14194]

	        // [FILE ID=5b87ba3b-edb1-49df-a840-50d17b6c3e8c.fbbdd477d5ff19d61...a875e731fa89cfd1e1]
	        // [FILE ID=14194]
	        const serverFileId = node.getAttribute('id');
	        const createTextNode = () => {
	          return document.createTextNode(node.toString());
	        };
	        if (!main_core.Type.isStringFilled(serverFileId) || fileMode === 'disk' && !/^n?\d+$/i.test(serverFileId) || fileMode === 'file' && !/^(\d+|[\da-f-]{36}\.[\da-f]{32,})$/i.test(serverFileId)) {
	          return createTextNode();
	        }
	        const info = data.files.find(file => {
	          return file.serverFileId.toString() === serverFileId.toString();
	        });
	        if (!info) {
	          return createTextNode();
	        }
	        if (info.isImage) {
	          let width = main_core.Text.toInteger(node.getAttribute('width'));
	          let height = main_core.Text.toInteger(node.getAttribute('height'));
	          width = main_core.Type.isNumber(width) && width > 0 ? Math.round(width) : info.serverPreviewWidth;
	          height = main_core.Type.isNumber(height) && height > 0 ? Math.round(height) : info.serverPreviewHeight;
	          return createImageNode({
	            width,
	            height,
	            src: info.serverPreviewUrl
	          });
	        }
	        if (info.isVideo) {
	          let width = Number(node.getAttribute('width'));
	          let height = Number(node.getAttribute('height'));
	          width = main_core.Type.isNumber(width) && width > 0 ? Math.round(width) : 600;
	          height = main_core.Type.isNumber(height) && height > 0 ? Math.round(height) : null;
	          return createVideoNode({
	            url: info.downloadUrl,
	            width,
	            height
	          });
	        }
	        return main_core.Dom.create({
	          tag: 'a',
	          attrs: {
	            href: info.downloadUrl,
	            className: 'ui-typography-link'
	          },
	          text: info.name || 'unknown',
	          dataset: {
	            fileId: info.serverFileId,
	            fileInfo: JSON.stringify(info)
	          }
	        });
	      },
	      ...options
	    });
	  }
	}



	var NodeFormatters = /*#__PURE__*/Object.freeze({
		RootNodeFormatter: RootNodeFormatter,
		TextNodeFormatter: TextNodeFormatter,
		TabNodeFormatter: TabNodeFormatter,
		LinebreakNodeFormatter: LinebreakNodeFormatter,
		ParagraphNodeFormatter: ParagraphNodeFormatter,
		BoldNodeFormatter: BoldNodeFormatter,
		UnderlineNodeFormatter: UnderlineNodeFormatter,
		ItalicNodeFormatter: ItalicNodeFormatter,
		StrikethroughNodeFormatter: StrikethroughNodeFormatter,
		TableNodeFormatter: TableNodeFormatter,
		TableHeadCellNodeFormatter: TableHeadCellNodeFormatter,
		TableDataCellNodeFormatter: TableDataCellNodeFormatter,
		TableRowNodeFormatter: TableRowNodeFormatter,
		ListNodeFormatter: ListNodeFormatter,
		ListItemNodeFormatter: ListItemNodeFormatter,
		QuoteNodeFormatter: QuoteNodeFormatter,
		CodeNodeFormatter: CodeNodeFormatter,
		LinkNodeFormatter: LinkNodeFormatter,
		SpoilerNodeFormatter: SpoilerNodeFormatter,
		UserNodeFormatter: UserNodeFormatter,
		DepartmentNodeFormatter: DepartmentNodeFormatter,
		ProjectNodeFormatter: ProjectNodeFormatter,
		ImageNodeFormatter: ImageNodeFormatter,
		VideoNodeFormatter: VideoNodeFormatter,
		FileNodeFormatter: FileNodeFormatter
	});

	const globalSettings = main_core.Extension.getSettings('ui.bbcode.formatter.html-formatter');

	/**
	 * @memberOf BX.UI.BBCode.Formatter
	 */
	var _linkSettings = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("linkSettings");
	var _mentionSettings = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("mentionSettings");
	var _fileMode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("fileMode");
	var _containerMode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("containerMode");
	var _isVoidElement = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isVoidElement");
	class HtmlFormatter extends ui_bbcode_formatter.Formatter {
	  constructor(options = {}) {
	    super();
	    Object.defineProperty(this, _isVoidElement, {
	      value: _isVoidElement2
	    });
	    Object.defineProperty(this, _linkSettings, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _mentionSettings, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _fileMode, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _containerMode, {
	      writable: true,
	      value: void 0
	    });
	    this.setLinkSettings({
	      ...globalSettings.linkSettings,
	      ...options.linkSettings
	    });
	    this.setMentionSettings({
	      ...globalSettings.mention,
	      ...options.mention
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _fileMode)[_fileMode] = ['file', 'disk'].includes(options.fileMode) ? options.fileMode : null;
	    const defaultFormatters = Object.values(NodeFormatters).map(FormatterClass => {
	      return new FormatterClass({
	        formatter: this
	      });
	    });
	    this.setContainerMode(options.containerMode);
	    this.setNodeFormatters(defaultFormatters);
	    this.setNodeFormatters(options.formatters);
	  }
	  isShortLinkEnabled() {
	    const {
	      shortLink
	    } = this.getLinkSettings();
	    return main_core.Type.isPlainObject(shortLink) && shortLink.enabled === true && main_core.Type.isInteger(shortLink.maxLength);
	  }
	  setLinkSettings(settings) {
	    babelHelpers.classPrivateFieldLooseBase(this, _linkSettings)[_linkSettings] = {
	      ...settings
	    };
	  }
	  getLinkSettings() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _linkSettings)[_linkSettings];
	  }
	  getFileMode() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _fileMode)[_fileMode];
	  }
	  setMentionSettings(settings) {
	    babelHelpers.classPrivateFieldLooseBase(this, _mentionSettings)[_mentionSettings] = {
	      ...settings
	    };
	  }
	  getMentionSettings() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _mentionSettings)[_mentionSettings];
	  }
	  setContainerMode(mode) {
	    babelHelpers.classPrivateFieldLooseBase(this, _containerMode)[_containerMode] = mode;
	  }
	  getContainerMode() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _containerMode)[_containerMode];
	  }
	  isElement(source) {
	    if (source.nodeType === Node.DOCUMENT_FRAGMENT_NODE) {
	      return true;
	    }
	    if (source.nodeType !== Node.ELEMENT_NODE || babelHelpers.classPrivateFieldLooseBase(this, _isVoidElement)[_isVoidElement](source)) {
	      return false;
	    }
	    return main_core.Type.isUndefined(source.dataset.decorator);
	  }
	  getDefaultUnknownNodeCallback(options) {
	    return () => {
	      return new ui_bbcode_formatter.NodeFormatter({
	        name: 'unknown',
	        before({
	          node
	        }) {
	          const scheme = node.getScheme();
	          if (node.isVoid()) {
	            return scheme.createFragment({
	              children: [scheme.createText(node.getOpeningTag())]
	            });
	          }
	          return scheme.createFragment({
	            children: [scheme.createText(node.getOpeningTag()), ...node.getChildren(), scheme.createText(node.getClosingTag())]
	          });
	        },
	        convert() {
	          return document.createDocumentFragment();
	        }
	      });
	    };
	  }
	}
	function _isVoidElement2(source) {
	  return ['img', 'br', 'hr', 'input'].includes(source.tagName.toLowerCase());
	}

	const HtmlFormatterComponent = {
	  props: {
	    bbcode: {
	      type: String,
	      required: false,
	      default: ''
	    }
	  },
	  beforeCreate() {
	    this.htmlFormatter = null;
	  },
	  mounted() {
	    this.format(this.bbcode);
	  },
	  unmounted() {
	    this.htmlFormatter = null;
	  },
	  watch: {
	    bbcode(newValue) {
	      this.format(newValue);
	    }
	  },
	  methods: {
	    format(bbcode) {
	      const result = this.getHtmlFormatter().format({
	        source: bbcode
	      });
	      const container = this.$refs.content;
	      main_core.Dom.clean(container);
	      // eslint-disable-next-line @bitrix24/bitrix24-rules/no-native-dom-methods
	      container.appendChild(result);
	      // container.parentNode.replaceChild(result, container);
	    },

	    getHtmlFormatter() {
	      if (this.htmlFormatter !== null) {
	        return this.htmlFormatter;
	      }
	      this.htmlFormatter = new HtmlFormatter();
	      return this.htmlFormatter;
	    }
	  },
	  template: '<div class="ui-typography-container" ref="content"></div>'
	};

	exports.HtmlFormatter = HtmlFormatter;
	exports.HtmlFormatterComponent = HtmlFormatterComponent;

}((this.BX.UI.BBCode.Formatter = this.BX.UI.BBCode.Formatter || {}),BX.UI.Smiley,BX.UI.CodeParser,BX.UI.BBCode,BX.UI.VideoService,BX.UI.BBCode,window,BX));
//# sourceMappingURL=html-formatter.bundle.js.map

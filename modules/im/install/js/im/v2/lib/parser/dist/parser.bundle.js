this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core_events,main_core) {
	'use strict';

	const ParserSlashCommand = {
	  decode(text) {
	    if (text.startsWith('/me')) {
	      return `[i]${text.substr(4)}[/i]`;
	    }
	    if (text.startsWith('/loud')) {
	      return `[size=20]${text.substr(6)}[/size]`;
	    }
	    return text;
	  },
	  purify(text) {
	    if (text.startsWith('/me')) {
	      return text.substr(4);
	    }
	    if (text.startsWith('/loud')) {
	      return text.substr(6);
	    }
	    return text;
	  }
	};

	const isDesktop = main_core.Type.isObject(window.BXDesktopSystem);
	const settings = main_core.Extension.getSettings('im.v2.lib.parser');
	const v2 = settings.get('v2') && !isDesktop;
	const getCore = () => {
	  return v2 ? BX.Messenger.v2.Application.Core : BX.Messenger.Embedding.Application.Core;
	};
	const getUtils = () => {
	  return v2 ? BX.Messenger.v2.Lib.Utils : BX.Messenger.Embedding.Lib.Utils;
	};
	const getLogger = () => {
	  return v2 ? BX.Messenger.v2.Lib.Logger : BX.Messenger.Embedding.Lib.Logger;
	};
	const getConst = () => {
	  return v2 ? BX.Messenger.v2.Const : BX.Messenger.Embedding.Const;
	};
	const getSmileManager = () => {
	  return v2 ? BX.Messenger.v2.Lib.SmileManager : BX.Messenger.Embedding.Lib.SmileManager;
	};
	const getBigSmileOption = () => {
	  if (v2) {
	    const settingName = BX.Messenger.v2.Const.Settings.dialog.bigSmiles;
	    return getCore().getStore().getters['application/settings/get'](settingName);
	  }
	  return getCore().getStore().getters['application/getOption']('bigSmileEnable');
	};

	const RECURSIVE_LIMIT = 10;
	const ParserUtils = {
	  recursiveReplace(text, pattern, replacement) {
	    if (!main_core.Type.isStringFilled(text)) {
	      return text;
	    }
	    let count = 0;
	    let deep = true;
	    do {
	      deep = false;
	      count++;
	      text = text.replace(pattern, (...params) => {
	        deep = true;
	        return replacement(...params);
	      });
	    } while (deep && count <= RECURSIVE_LIMIT);
	    return text;
	  },
	  getFinalContextTag(contextTag) {
	    const match = contextTag.match(/(chat\d+|(\d+):(\d+))\/(\d+)/i);
	    if (!match) {
	      return '';
	    }
	    let [, dialogId, user1, user2, messageId] = match;
	    if (dialogId.toString().startsWith('chat')) {
	      if (dialogId === 'chat0') {
	        return '';
	      }
	      return contextTag;
	    }
	    user1 = Number.parseInt(user1, 10);
	    user2 = Number.parseInt(user2, 10);
	    if (getCore().getUserId() === user1) {
	      return `${user2}/${messageId}}`;
	    }
	    if (getCore().getUserId() === user2) {
	      return `${user1}/${messageId}}`;
	    }
	    return '';
	  }
	};

	const {
	  FileType,
	  FileIconType,
	  AttachDescription
	} = getConst();
	const ParserIcon = {
	  getIcon(icon, fallbackText = '') {
	    return fallbackText;
	    /*
	    if (!FileIconType[icon])
	    {
	    	return fallbackText;
	    }
	    	return Dom.create({
	    	tag: 'span',
	    	attrs: {
	    		className: `bx-im-icon --${icon}`,
	    	},
	    }).outerHTML;
	     */
	  },

	  addIconToShortText(config) {
	    let {
	      text
	    } = config;
	    const {
	      attach,
	      files
	    } = config;
	    if (main_core.Type.isArray(files) && files.length > 0) {
	      text = this.getTextForFile(text, files);
	    } else if (attach === true || main_core.Type.isArray(attach) && attach.length > 0 || main_core.Type.isStringFilled(attach)) {
	      text = this.getTextForAttach(text, attach);
	    }
	    return text.trim();
	  },
	  getQuoteBlock() {
	    const icon = this.getIcon(FileIconType.quote);
	    if (icon) {
	      return icon;
	    }
	    return `[${main_core.Loc.getMessage('IM_PARSER_ICON_TYPE_QUOTE')}]`;
	  },
	  getCodeBlock() {
	    const icon = this.getIcon(FileIconType.code);
	    if (icon) {
	      return icon;
	    }
	    return `[${main_core.Loc.getMessage('IM_PARSER_ICON_TYPE_CODE')}]`;
	  },
	  getImageBlock() {
	    const icon = this.getIcon(FileIconType.image);
	    if (icon) {
	      return icon;
	    }
	    return `[${main_core.Loc.getMessage('IM_PARSER_ICON_TYPE_IMAGE')}]`;
	  },
	  getFileBlock() {
	    const icon = this.getIcon(FileIconType.file);
	    if (icon) {
	      return icon;
	    }
	    return `[${main_core.Loc.getMessage('IM_PARSER_ICON_TYPE_FILE')}]`;
	  },
	  getTextForFile(text, files) {
	    if (main_core.Type.isArray(files) && files.length > 0) {
	      const [firstFile] = files;
	      text = this.getIconTextForFile(text, firstFile);
	    } else if (files === true) {
	      text = this.getIconTextForFileType(text, FileIconType.file);
	    }
	    return text;
	  },
	  getTextForAttach(text, attach) {
	    let attachDescription = '';
	    if (main_core.Type.isArray(attach) && attach.length > 0) {
	      const [firstAttach] = attach;
	      if (main_core.Type.isStringFilled(firstAttach.DESCRIPTION)) {
	        attachDescription = firstAttach.DESCRIPTION;
	      }
	    } else if (main_core.Type.isStringFilled(attach)) {
	      attachDescription = attach;
	    }
	    if (main_core.Type.isStringFilled(attachDescription)) {
	      if (attachDescription === AttachDescription.SKIP_MESSAGE) {
	        attachDescription = '';
	      } else {
	        attachDescription = Parser.purifyText(attachDescription, {
	          showPhraseMessageWasDeleted: false
	        });
	      }
	    } else {
	      const icon = this.getIcon(FileIconType.attach);
	      if (icon) {
	        attachDescription = `${icon} ${main_core.Loc.getMessage('IM_PARSER_ICON_TYPE_ATTACH')}`;
	      } else {
	        attachDescription = `[${main_core.Loc.getMessage('IM_PARSER_ICON_TYPE_ATTACH')}]`;
	      }
	    }
	    return `${text} ${attachDescription}`.trim();
	  },
	  getIconTextForFileType(text, type = FileIconType.file) {
	    let result = text;
	    const icon = this.getIcon(type);
	    const iconText = main_core.Loc.getMessage(`IM_PARSER_ICON_TYPE_${type.toUpperCase()}`);
	    if (icon) {
	      const withText = text.replace(/(\s|\n)/gi, '').length > 0;
	      const textDescription = withText ? text : iconText;
	      result = `${icon} ${textDescription}`;
	    } else {
	      result = `[${iconText}] ${text}`;
	    }
	    return result.trim();
	  },
	  getIconTextForFile(text, file) {
	    const withText = text.replace(/(\s|\n)/gi, '').length > 0;

	    // todo: remove this hack after fix receiving messages with files on P&P
	    if (!file || !file.type) {
	      return text;
	    }
	    if (file.type === FileType.image) {
	      return this.getIconTextForFileType(text, FileIconType.image);
	    } else if (file.type === FileType.audio) {
	      return this.getIconTextForFileType(text, FileIconType.audio);
	    } else if (file.type === FileType.video) {
	      return this.getIconTextForFileType(text, FileIconType.video);
	    } else {
	      const icon = this.getIcon(FileIconType.file);
	      if (icon) {
	        const textDescription = withText ? text : '';
	        text = `${icon} ${file.name} ${textDescription}`;
	      } else {
	        text = `${main_core.Loc.getMessage('IM_PARSER_ICON_TYPE_FILE')}: ${file.name} ${text}`;
	      }
	      return text.trim();
	    }
	  }
	};

	let _ = t => t,
	  _t,
	  _t2;
	const {
	  EventType
	} = getConst();
	const QUOTE_SIGN = '&gt;&gt;';
	const ParserQuote = {
	  decodeArrowQuote(text) {
	    if (!text.includes(QUOTE_SIGN)) {
	      return text;
	    }
	    let isProcessed = false;
	    const textLines = text.split('<br />');
	    for (let i = 0; i < textLines.length; i++) {
	      if (!textLines[i].startsWith(QUOTE_SIGN)) {
	        continue;
	      }
	      const quoteStartIndex = i;
	      const outerContainerStart = '<div class="bx-im-message-quote --inline">';
	      const innerContainerStart = '<div class="bx-im-message-quote__wrap">';
	      const containerEnd = '</div>';
	      textLines[quoteStartIndex] = textLines[quoteStartIndex].replace(QUOTE_SIGN, `${outerContainerStart}${innerContainerStart}`);
	      // remove >> from all next lines
	      while (++i < textLines.length && textLines[i].startsWith(QUOTE_SIGN)) {
	        textLines[i] = textLines[i].replace(QUOTE_SIGN, '');
	      }
	      const quoteEndIndex = i - 1;
	      textLines[quoteEndIndex] += `${containerEnd}${containerEnd}`;
	      isProcessed = true;
	    }
	    if (!isProcessed) {
	      return text;
	    }
	    return textLines.join('<br />');
	  },
	  purifyArrowQuote(text, spaceLetter = ' ') {
	    text = text.replace(new RegExp(`^(${QUOTE_SIGN}(.*))`, 'gim'), ParserIcon.getQuoteBlock() + spaceLetter);
	    return text;
	  },
	  decodeQuote(text) {
	    text = text.replace(/-{54}(<br \/>(.*?)\[(.*?)]( #(?:chat\d+|\d+:\d+)\/\d+)?)?<br \/>(.*?)-{54}(<br \/>)?/gs, (whole, userBlock, userName, timeTag, contextTag, text) => {
	      const skipUserBlock = !userName || !timeTag;
	      if (skipUserBlock && !text)
	        // greedy date detector :(
	        {
	          text = `${timeTag}`;
	        }
	      let userContainer = '';
	      if (!skipUserBlock) {
	        userContainer = main_core.Tag.render(_t || (_t = _`
						<div class='bx-im-message-quote__name'>
							<div class="bx-im-message-quote__name-text">${0}</div>
							<div class="bx-im-message-quote__name-time">${0}</div>
						</div>
					`), userName, timeTag);
	      }
	      let quoteBaseClass = 'bx-im-message-quote';
	      if (contextTag) {
	        contextTag = contextTag.trim().slice(1);
	        contextTag = ParserUtils.getFinalContextTag(contextTag);
	      }
	      if (contextTag) {
	        quoteBaseClass += ' --with-context';
	      } else {
	        contextTag = 'none';
	      }
	      const layout = main_core.Tag.render(_t2 || (_t2 = _`
					<div class='${0}' data-context='${0}'>
						<div class='bx-im-message-quote__wrap'>
							${0}
							<div class='bx-im-message-quote__text'>${0}</div>
						</div>
					</div>
				`), quoteBaseClass, contextTag, userContainer, text);
	      return layout.outerHTML;
	    });
	    return text;
	  },
	  purifyQuote(text, spaceLetter = ' ') {
	    return text.replace(/-{54}(.*?)-{54}/gims, ParserIcon.getQuoteBlock() + spaceLetter);
	  },
	  decodeCode(text) {
	    return text.replace(/\[code](<br \/>)?([\0-\uFFFF]*?)\[\/code](<br \/>)?/gis, (whole, br, code) => {
	      return main_core.Dom.create({
	        tag: 'div',
	        attrs: {
	          className: 'bx-im-message-content-code'
	        },
	        html: code
	      }).outerHTML;
	    });
	  },
	  purifyCode(text, spaceLetter = ' ') {
	    return text.replace(/\[code](<br \/>)?([\0-\uFFFF]*?)\[\/code]/gis, ParserIcon.getCodeBlock() + spaceLetter);
	  },
	  executeClickEvent(event) {
	    if (!event.target.className.startsWith('bx-im-message-quote') && !(event.target.parentNode && event.target.parentNode.className.startsWith('bx-im-message-quote'))) {
	      return;
	    }
	    const target = getUtils().dom.recursiveBackwardNodeSearch(event.target, '--with-context');
	    if (!target || target.dataset.context === 'none') {
	      return;
	    }
	    const [dialogId, messageId] = target.dataset.context.split('/');
	    main_core_events.EventEmitter.emit(EventType.dialog.goToMessageContext, {
	      messageId: Number.parseInt(messageId, 10),
	      dialogId: dialogId.toString()
	    });
	  }
	};

	const ParserImage = {
	  decodeLink(text) {
	    text = text.replace(/(.)?((https|http):\/\/(\S+)\.(jpg|jpeg|png|gif|webp)(\?\S+)?)/gi, function (whole, letter, url) {
	      url = main_core.Text.decode(url);
	      if (letter && !['>', ']'].includes(letter) || !url.match(/(\.(jpg|jpeg|png|gif|webp)\?|\.(jpg|jpeg|png|gif|webp)$)/i) || url.toLowerCase().indexOf("/docs/pub/") > 0 || url.toLowerCase().indexOf("logout=yes") > 0) {
	        return whole;
	      }
	      if (!getUtils().text.checkUrl(url)) {
	        return whole;
	      }
	      return (letter ? letter : '') + main_core.Dom.create({
	        tag: 'span',
	        attrs: {
	          className: 'bx-im-message-image'
	        },
	        children: [main_core.Dom.create({
	          tag: 'img',
	          attrs: {
	            className: 'bx-im-message-image-source',
	            src: url
	          },
	          events: {
	            error: () => {
	              ParserImage._hideErrorImage(this);
	            }
	          }
	        })]
	      }).outerHTML;
	    });
	    return text;
	  },
	  purifyLink(text) {
	    text = text.replace(/(.)?((https|http):\/\/(\S+)\.(jpg|jpeg|png|gif|webp)(\?\S+)?)/gi, function (whole, letter, url) {
	      if (letter && !['>', ']', ' '].includes(letter) || !url.match(/(\.(jpg|jpeg|png|gif|webp)\?|\.(jpg|jpeg|png|gif|webp)$)/i) || url.toLowerCase().indexOf("/docs/pub/") > 0 || url.toLowerCase().indexOf("logout=yes") > 0) {
	        return whole;
	      } else {
	        return (letter ? letter : '') + ParserIcon.getImageBlock();
	      }
	    });
	    return text;
	  },
	  decodeIcon(text) {
	    let textElementSize = 0;
	    const enableBigSmile = getBigSmileOption();
	    if (enableBigSmile) {
	      textElementSize = text.replace(/\[icon=([^\]]*)]/gi, '').trim().length;
	    }
	    text = text.replace(/\[icon=([^\]]*)]/gi, whole => {
	      let url = whole.match(/icon=(\S+[^\s.,> )\];'"!?])/i);
	      if (url && url[1]) {
	        url = url[1];
	      } else {
	        return '';
	      }
	      if (!getUtils().text.checkUrl(url)) {
	        return whole;
	      }
	      const attrs = {
	        'src': url,
	        'border': 0
	      };
	      const size = whole.match(/size=(\d+)/i);
	      if (size && size[1]) {
	        attrs['width'] = size[1];
	        attrs['height'] = size[1];
	      } else {
	        const width = whole.match(/width=(\d+)/i);
	        if (width && width[1]) {
	          attrs['width'] = width[1];
	        }
	        const height = whole.match(/height=(\d+)/i);
	        if (height && height[1]) {
	          attrs['height'] = height[1];
	        }
	        if (attrs['width'] && !attrs['height']) {
	          attrs['height'] = attrs['width'];
	        } else if (attrs['height'] && !attrs['width']) {
	          attrs['width'] = attrs['height'];
	        } else if (attrs['height'] && attrs['width']) ; else {
	          attrs['width'] = 20;
	          attrs['height'] = 20;
	        }
	      }
	      attrs['width'] = attrs['width'] > 100 ? 100 : attrs['width'];
	      attrs['height'] = attrs['height'] > 100 ? 100 : attrs['height'];
	      if (enableBigSmile && textElementSize === 0 && attrs['width'] === attrs['height'] && attrs['width'] === 20) {
	        attrs['width'] = 40;
	        attrs['height'] = 40;
	      }
	      let title = whole.match(/title=(.*[^\s\]])/i);
	      if (title && title[1]) {
	        title = title[1];
	        if (title.indexOf('width=') > -1) {
	          title = title.substr(0, title.indexOf('width='));
	        }
	        if (title.indexOf('height=') > -1) {
	          title = title.substr(0, title.indexOf('height='));
	        }
	        if (title.indexOf('size=') > -1) {
	          title = title.substr(0, title.indexOf('size='));
	        }
	        if (title) {
	          attrs['title'] = main_core.Text.decode(title).trim();
	          attrs['alt'] = attrs['title'];
	        }
	      }
	      return main_core.Dom.create({
	        tag: 'img',
	        attrs: {
	          className: 'bx-smile bx-icon',
	          ...attrs
	        }
	      }).outerHTML;
	    });
	    return text;
	  },
	  purifyIcon(text) {
	    text = text.replace(/\[icon=([^\]]*)]/gi, whole => {
	      let title = whole.match(/title=(.*[^\s\]])/i);
	      if (title && title[1]) {
	        title = title[1];
	        if (title.indexOf('width=') > -1) {
	          title = title.substr(0, title.indexOf('width='));
	        }
	        if (title.indexOf('height=') > -1) {
	          title = title.substr(0, title.indexOf('height='));
	        }
	        if (title.indexOf('size=') > -1) {
	          title = title.substr(0, title.indexOf('size='));
	        }
	        if (title) {
	          title = '(' + title.trim() + ')';
	        }
	      } else {
	        title = '(' + main_core.Loc.getMessage('IM_PARSER_IMAGE_ICON') + ')';
	      }
	      return title;
	    });
	    return text;
	  },
	  _hideErrorImage(element) {
	    if (element.parentNode) {
	      element.parentNode.innerHTML = '<a href="' + encodeURI(element.src) + '" target="_blank">' + element.src + '</a>';
	    }
	  }
	};

	let _$1 = t => t,
	  _t$1;
	const RatioConfig = Object.freeze({
	  Default: 1,
	  Big: 1.6
	});
	const getSmileRatio = (text, pattern, config = RatioConfig) => {
	  const replacedText = text.replaceAll(new RegExp(pattern, 'g'), '');
	  const hasOnlySmiles = replacedText.trim().length === 0;
	  const matchOnlySmiles = new RegExp(`(?:(?:${pattern})\\s*){3,}`);
	  if (hasOnlySmiles && !matchOnlySmiles.test(text)) {
	    return config.Big;
	  }
	  return config.Default;
	};
	const mapTypings = smiles => {
	  const typings = smiles.reduce((acc, smile) => {
	    const {
	      image,
	      typing,
	      definition,
	      name,
	      width,
	      height
	    } = smile;
	    const smileImg = main_core.Tag.render(_t$1 || (_t$1 = _$1`
			<img
				src="${0}"
				data-code="${0}"
				data-definition="${0}"
				title="${0}"
				alt="${0}"
				class="bx-smile bx-im-message-base__text_smile"
				style="width: ${0}px; height: ${0}px;"
			/>
		`), image, typing, definition, name != null ? name : typing, typing, width, height);
	    return {
	      ...acc,
	      [typing]: smileImg
	    };
	  }, {});
	  return typings;
	};
	const lookBehind = function (text, match, offset) {
	  const substring = text.slice(0, offset + match.length);
	  const escaped = getUtils().text.escapeRegex(match);
	  const regExp = new RegExp(`(?:^|&quot;|>|(?:${this.pattern})|\\s|<)(?:${escaped})$`);
	  return substring.match(regExp);
	};
	const ParserSmile = {
	  typings: null,
	  pattern: '',
	  loadSmilePatterns() {
	    var _smileManager$smileLi, _smileManager$smileLi2;
	    const smileManager = getSmileManager().getInstance();
	    const smiles = (_smileManager$smileLi = (_smileManager$smileLi2 = smileManager.smileList) == null ? void 0 : _smileManager$smileLi2.smiles) != null ? _smileManager$smileLi : [];
	    const sortedSmiles = [...smiles].sort((a, b) => {
	      return b.typing.localeCompare(a.typing);
	    });
	    this.pattern = sortedSmiles.map(smile => {
	      return getUtils().text.escapeRegex(smile.typing);
	    }).join('|');
	    this.typings = mapTypings(sortedSmiles);
	  },
	  decodeSmile(text, options = {})
	  // TODO add options types
	  {
	    if (!this.typings) {
	      this.loadSmilePatterns();
	    }
	    if (!this.pattern) {
	      return text;
	    }
	    let enableBigSmile;
	    if (main_core.Type.isBoolean(options.enableBigSmile)) {
	      enableBigSmile = options.enableBigSmile;
	    } else {
	      enableBigSmile = getBigSmileOption();
	    }
	    const ratioConfig = main_core.Type.isObjectLike(options.ratioConfig) ? options.ratioConfig : RatioConfig;
	    const ratio = enableBigSmile ? getSmileRatio(text, this.pattern, ratioConfig) : ratioConfig.Default;
	    const pattern = `(?:(?:${this.pattern})(?=(?:(?:${this.pattern})|\\s|&quot;|<|$)))`;
	    const regExp = new RegExp(pattern, 'g');
	    const replacedText = text.replaceAll(regExp, (match, offset) => {
	      const behindMatching = lookBehind.call(this, text, match, offset);
	      if (!behindMatching) {
	        return match;
	      }
	      const image = this.typings[match].cloneNode();
	      const {
	        width,
	        height
	      } = image.style;
	      main_core.Dom.style(image, 'width', `${Number.parseInt(width, 10) * ratio}px`);
	      main_core.Dom.style(image, 'height', `${Number.parseInt(height, 10) * ratio}px`);
	      return image.outerHTML;
	    });
	    return replacedText;
	  }
	};

	const ParserUrl = {
	  decode(text, config = {}) {
	    const {
	      urlTarget = "_blank",
	      removeLinks = false
	    } = config;

	    // base pattern for urls
	    text = text.replace(/\[url(?:=([^[\]]+))?](.*?)\[\/url]/gis, (whole, link, text) => {
	      const url = main_core.Text.decode(link || text);
	      if (!getUtils().text.checkUrl(url)) {
	        return text;
	      }
	      return main_core.Dom.create({
	        tag: 'a',
	        attrs: {
	          href: url,
	          target: urlTarget
	        },
	        html: text
	      }).outerHTML;
	    });

	    // url like https://bitrix24.com/?params[1]="test"
	    text = text.replace(/\[url(?:=(.+?[^[\]]))?](.*?)\[\/url]/gis, (whole, link, text) => {
	      let url = main_core.Text.decode(link || text);
	      if (!getUtils().text.checkUrl(url)) {
	        return text;
	      }
	      if (!url.slice(url.lastIndexOf('[')).includes(']')) {
	        if (text.startsWith(']')) {
	          url = `${url}]`;
	          text = text.slice(1);
	        } else if (text.startsWith('=')) {
	          const urlPart = main_core.Text.decode(text.slice(1, text.lastIndexOf(']')));
	          url = `${url}]=${urlPart}`;
	          text = text.slice(text.lastIndexOf(']') + 1);
	        }
	      }
	      return main_core.Dom.create({
	        tag: 'a',
	        attrs: {
	          href: url,
	          target: urlTarget
	        },
	        html: text
	      }).outerHTML;
	    });
	    if (removeLinks) {
	      text = text.replace(/<a.*?href="([^"]*)".*?>(.*?)<\/a>/gi, '$2');
	    }
	    return text;
	  },
	  purify(text) {
	    text = text.replace(/\[url(?:=([^\[\]]+))?](.*?)\[\/url]/gis, (whole, link, text) => {
	      return text ? text : link;
	    });
	    text = text.replace(/\[url(?:=(.+))?](.*?)\[\/url]/gis, (whole, link, text) => {
	      return text ? text : link;
	    });
	    return text;
	  },
	  removeSimpleUrlTag(text) {
	    text = text.replace(/\[url](.*?)\[\/url]/gis, (whole, link) => link);
	    return text;
	  }
	};

	const ParserFont = {
	  decode(text) {
	    text = ParserUtils.recursiveReplace(text, /\[b]([^[]*(?:\[(?!b]|\/b])[^[]*)*)\[\/b]/gi, (whole, text) => '<b>' + text + '</b>');
	    text = ParserUtils.recursiveReplace(text, /\[u]([^[]*(?:\[(?!u]|\/u])[^[]*)*)\[\/u]/gi, (whole, text) => '<u>' + text + '</u>');
	    text = ParserUtils.recursiveReplace(text, /\[i]([^[]*(?:\[(?!i]|\/i])[^[]*)*)\[\/i]/gi, (whole, text) => '<i>' + text + '</i>');
	    text = ParserUtils.recursiveReplace(text, /\[s]([^[]*(?:\[(?!s]|\/s])[^[]*)*)\[\/s]/gi, (whole, text) => '<s>' + text + '</s>');
	    text = ParserUtils.recursiveReplace(text, /\[size=(\d+)(?:pt|px)?](.*?)\[\/size]/gis, (whole, number, text) => {
	      number = Number.parseInt(number, 10);
	      if (number <= 8) {
	        number = 8;
	      } else if (number >= 30) {
	        number = 30;
	      }
	      return main_core.Dom.create({
	        tag: 'span',
	        style: {
	          fontSize: `${number}px`
	        },
	        html: text
	      }).outerHTML;
	    });
	    text = ParserUtils.recursiveReplace(text, /\[color=#([0-9a-f]{3}|[0-9a-f]{6})](.*?)\[\/color]/gis, (whole, hex, text) => {
	      return main_core.Dom.create({
	        tag: 'span',
	        style: {
	          color: '#' + hex
	        },
	        html: text
	      }).outerHTML;
	    });
	    return text;
	  },
	  purify(text, removeStrike = true) {
	    if (removeStrike) {
	      text = ParserUtils.recursiveReplace(text, /\[s]([^[]*(?:\[(?!s]|\/s])[^[]*)*)\[\/s]/gi, () => ' ');
	    }
	    text = ParserUtils.recursiveReplace(text, /\[b]([^[]*(?:\[(?!b]|\/b])[^[]*)*)\[\/b]/gi, (whole, text) => text);
	    text = ParserUtils.recursiveReplace(text, /\[u]([^[]*(?:\[(?!u]|\/u])[^[]*)*)\[\/u]/gi, (whole, text) => text);
	    text = ParserUtils.recursiveReplace(text, /\[i]([^[]*(?:\[(?!i]|\/i])[^[]*)*)\[\/i]/gi, (whole, text) => text);
	    text = ParserUtils.recursiveReplace(text, /\[s]([^[]*(?:\[(?!s]|\/s])[^[]*)*)\[\/s]/gi, (whole, text) => text);
	    text = ParserUtils.recursiveReplace(text, /\[size=(\d+)(?:pt|px)?](.*?)\[\/size]/gis, (whole, number, text) => text);
	    text = ParserUtils.recursiveReplace(text, /\[color=#([0-9a-f]{3}|[0-9a-f]{6})](.*?)\[\/color]/gis, (whole, hex, text) => text);
	    return text;
	  }
	};

	const ParserLines = {
	  decode(text) {
	    text = text.replace(/\[LIKE]/gi, `<span style="color: #004d00">${main_core.Loc.getMessage('IM_PARSER_LINES_RATING_LIKE')}</span>`);
	    text = text.replace(/\[DISLIKE]/gi, `<span style="color: #cc0000">${main_core.Loc.getMessage('IM_PARSER_LINES_RATING_DISLIKE')}</span>`);
	    text = text.replace(/\[RATING=([1-5])]/gi, (rating, group) => {
	      rating = parseInt(group);
	      return `<span class="bx-smile bx-im-smile-rating bx-im-smile-rating-'+rating+'">${main_core.Loc.getMessage('IM_PARSER_LINES_RATING')} - ${rating}</span>`;
	    });
	    return text;
	  },
	  purify(text) {
	    text = text.replace(/\[LIKE]/gi, main_core.Loc.getMessage('IM_PARSER_LINES_RATING_LIKE'));
	    text = text.replace(/\[DISLIKE]/gi, main_core.Loc.getMessage('IM_PARSER_LINES_RATING_DISLIKE'));
	    text = text.replace(/\[RATING=([1-5])]/gi, () => {
	      return '[' + main_core.Loc.getMessage('IM_PARSER_LINES_RATING') + '] ';
	    });
	    return text;
	  }
	};

	const atomRegExpPart = '\\d{4}-\\d{2}-\\d{2}T[0-2]\\d:[0-5]\\d:[0-5]\\d[+-][0-2]\\d:[0-5]\\d';
	const ParserAction = {
	  decodePut(text) {
	    text = text.replace(/\[PUT(?:=(?:.+?))?](?:.+?)?\[\/PUT]/gi, match => {
	      return match.replace(/\[PUT(?:=(.+))?](.+?)?\[\/PUT]/gi, (whole, command, text) => {
	        text = text ? text : command;
	        command = command ? command : text;
	        text = main_core.Text.decode(text);
	        command = main_core.Text.decode(command).replace('<br />', '\n');
	        if (!text.trim()) {
	          return '';
	        }
	        text = text.replace(/<(\w+)[^>]*>(.*?)<\/\1>/i, "$2", text);
	        text = text.replace(/\[(\w+)[^\]]*](.*?)\[\/\1]/i, "$2", text);
	        return this._getHtmlForAction('put', text, command);
	      });
	    });
	    return text;
	  },
	  purifyPut(text) {
	    text = text.replace(/\[PUT(?:=(?:.+?))?](?:.+?)?\[\/PUT]/gi, match => {
	      return match.replace(/\[PUT(?:=(.+))?](.+?)?\[\/PUT]/gi, (whole, command, text) => {
	        return text ? text : command;
	      });
	    });
	    return text;
	  },
	  decodeSend(text) {
	    text = text.replace(/\[SEND(?:=(?:.+?))?](?:.+?)?\[\/SEND]/gi, match => {
	      return match.replace(/\[SEND(?:=(.+))?](.+?)?\[\/SEND]/gi, (whole, command, text) => {
	        text = text ? text : command;
	        command = command ? command : text;
	        text = main_core.Text.decode(text);
	        command = main_core.Text.decode(command).replace('<br />', '\n');
	        if (!text.trim()) {
	          return '';
	        }
	        text = text.replace(/<(\w+)[^>]*>(.*?)<\\1>/i, "$2", text);
	        text = text.replace(/\[(\w+)[^\]]*](.*?)\[\/\1]/i, "$2", text);
	        command = command.split('####REPLACEMENT_PUT_').join('####REPLACEMENT_SP_');
	        return this._getHtmlForAction('send', text, command);
	      });
	    });
	    return text;
	  },
	  purifySend(text) {
	    text = text.replace(/\[SEND(?:=(?:.+?))?](?:.+?)?\[\/SEND]/gi, match => {
	      return match.replace(/\[SEND(?:=(.+))?](.+?)?\[\/SEND]/gi, (whole, command, text) => {
	        return text ? text : command;
	      });
	    });
	    return text;
	  },
	  decodeDate(text) {
	    text = text.replace(RegExp('\\[DATE=(' + atomRegExpPart + ')](.+?)\\[\\/DATE]', 'ig'), (whole, date, text) => {
	      text = text.replace(/<(\w+)[^>]*>(.*?)<\\1>/i, "$2", text);
	      text = text.replace(/\[(\w+)[^\]]*](.*?)\[\/\1]/i, "$2", text);
	      return this._getHtmlForAction('date', text, date);
	    });
	    return text;
	  },
	  purifyDate(text) {
	    const atomRegexp = getUtils().date.atomRegexpString;
	    text = text.replace(RegExp('\[DATE=(' + atomRegexp + ')](.+?)\[\/DATE]', 'ig'), (whole, date, text) => {
	      return text;
	    });
	    return text;
	  },
	  _getHtmlForAction(method, text, data) {
	    return main_core.Dom.create({
	      tag: 'span',
	      attrs: {
	        className: 'bx-im-message-command-wrap'
	      },
	      children: [main_core.Dom.create({
	        tag: 'span',
	        attrs: {
	          className: 'bx-im-message-command',
	          'data-entity': method
	        },
	        text
	      }), main_core.Dom.create({
	        tag: 'span',
	        attrs: {
	          className: 'bx-im-message-command-data'
	        },
	        text: data
	      })]
	    }).outerHTML;
	  }
	};

	const ParserCall = {
	  decode(text) {
	    text = text.replace(/\[CALL(?:=([-+\d()./# ]+))?](.+?)\[\/CALL]/gi, (whole, number, text) => {
	      if (!text) {
	        return whole;
	      }
	      if (!number) {
	        if (text.match(/^([-+\d()./# ]+)$/)) {
	          number = text;
	        } else {
	          return whole;
	        }
	      }
	      text = main_core.Text.decode(text);
	      return main_core.Dom.create({
	        tag: 'span',
	        attrs: {
	          className: 'bx-im-mention',
	          'data-type': 'CALL',
	          'data-value': number
	        },
	        text
	      }).outerHTML;
	    });
	    text = text.replace(/\[PCH=([0-9]+)](.*?)\[\/PCH]/gi, (whole, historyId, text) => text);
	    return text;
	  },
	  purify(text) {
	    text = text.replace(/\[CALL(?:=([-+\d()./# ]+))?](.+?)\[\/CALL]/gi, (whole, number, text) => text ? text : number);
	    text = text.replace(/\[PCH=([0-9]+)](.*?)\[\/PCH]/gi, (whole, historyId, text) => text);
	    return text;
	  }
	};

	const {
	  EventType: EventType$1,
	  MessageMentionType
	} = getConst();
	const ParserMention = {
	  decode(text) {
	    text = text.replace(/\[USER=([0-9]+)( REPLACE)?](.*?)\[\/USER]/gi, (whole, userId, replace, userName) => {
	      userId = Number.parseInt(userId, 10);
	      if (!main_core.Type.isNumber(userId) || userId === 0) {
	        return userName;
	      }
	      if (replace || !userName) {
	        const user = getCore().getStore().getters['users/get'](userId);
	        if (user) {
	          userName = user.name;
	        }
	      } else {
	        userName = main_core.Text.decode(userName);
	      }
	      if (!userName) {
	        userName = `User ${userId}`;
	      }
	      return main_core.Dom.create({
	        tag: 'span',
	        attrs: {
	          className: 'bx-im-mention',
	          'data-type': 'USER',
	          'data-value': userId
	        },
	        text: userName
	      }).outerHTML;
	    });
	    text = text.replace(/\[chat=(imol\|)?(\d+)](.*?)\[\/chat]/gi, (whole, openlines, chatId, chatName) => {
	      chatId = Number.parseInt(chatId, 10);
	      if (!main_core.Type.isNumber(chatId) || chatId === 0 || openlines) {
	        return chatName;
	      }
	      if (chatName) {
	        chatName = main_core.Text.decode(chatName);
	      } else {
	        const dialog = getCore().store.getters['dialogues/get']('chat' + chatId);
	        chatName = dialog ? dialog.name : 'Chat ' + chatId;
	      }
	      return main_core.Dom.create({
	        tag: 'span',
	        attrs: {
	          className: 'bx-im-mention',
	          'data-type': 'CHAT',
	          'data-value': 'chat' + chatId
	        },
	        text: chatName
	      }).outerHTML;
	    });
	    text = text.replace(/\[context=((?:chat\d+|\d+:\d+)\/(\d+))](.*?)\[\/context]/gis, (whole, contextTag, messageId, text) => {
	      if (!text) {
	        return '';
	      }
	      text = main_core.Text.decode(text);
	      contextTag = ParserUtils.getFinalContextTag(contextTag);
	      if (!contextTag) {
	        return text;
	      }
	      const dialogId = contextTag.split('/')[0];
	      let title = '';
	      messageId = Number.parseInt(messageId, 10);
	      if (main_core.Type.isNumber(messageId) && messageId > 0) {
	        const message = getCore().store.getters['messages/getById'](messageId);
	        if (message) {
	          title = Parser.purifyMessage(message);
	          const user = getCore().store.getters['users/get'](message.authorId);
	          if (user) {
	            title = `${user.name}: ${title}`;
	          }
	        }
	      }
	      if (!main_core.Type.isStringFilled(title)) {
	        title = main_core.Loc.getMessage('IM_PARSER_MENTION_DIALOG');
	      }
	      return main_core.Dom.create({
	        tag: 'span',
	        attrs: {
	          className: 'bx-im-mention',
	          'data-type': 'CONTEXT',
	          'data-dialog-id': dialogId,
	          'data-message-id': messageId,
	          title
	        },
	        text
	      }).outerHTML;
	    });
	    return text;
	  },
	  purify(text) {
	    text = text.replace(/\[USER=([0-9]+)( REPLACE)?](.*?)\[\/USER]/gi, (whole, userId, replace, userName) => {
	      userId = Number.parseInt(userId, 10);
	      if (!main_core.Type.isNumber(userId) || userId === 0) {
	        return userName;
	      }
	      if (replace || !userName) {
	        const user = getCore().getStore().getters['users/get'](userId);
	        if (user) {
	          userName = user.name;
	        }
	      } else {
	        userName = main_core.Text.decode(userName);
	      }
	      if (!userName) {
	        userName = `User ${userId}`;
	      }
	      return userName;
	    });
	    text = text.replace(/\[CHAT=(imol\|)?(\d+)](.*?)\[\/CHAT]/gi, (whole, openlines, chatId, chatName) => {
	      chatId = Number.parseInt(chatId, 10);
	      if (!chatName) {
	        const dialog = getCore().store.getters['dialogues/get']('chat' + chatId);
	        chatName = dialog ? dialog.name : 'Chat ' + chatId;
	      }
	      return chatName;
	    });
	    text = text.replace(/\[context=(chat\d+|\d+:\d+)\/(\d+)](.*?)\[\/context]/gis, (whole, dialogId, messageId, text) => {
	      if (!text) {
	        const dialog = getCore().store.getters['dialogues/get'](dialogId);
	        text = dialog ? dialog.name : 'Dialog ' + dialogId;
	      }
	      return text;
	    });
	    return text;
	  },
	  executeClickEvent(event) {
	    if (!main_core.Dom.hasClass(event.target, 'bx-im-mention')) {
	      return;
	    }
	    if (event.target.dataset.type === MessageMentionType.user || event.target.dataset.type === MessageMentionType.chat) {
	      main_core_events.EventEmitter.emit(EventType$1.mention.openChatInfo, {
	        event,
	        dialogId: event.target.dataset.value
	      });
	    } else if (event.target.dataset.type === MessageMentionType.context) {
	      main_core_events.EventEmitter.emit(EventType$1.dialog.goToMessageContext, {
	        messageId: Number.parseInt(event.target.dataset.messageId, 10),
	        dialogId: event.target.dataset.dialogId.toString()
	      });
	    }
	  }
	};

	const ParserCommon = {
	  decodeNewLine(text) {
	    text = text.replace(/\n/gi, '<br />');
	    text = text.replace(/\[BR]/gi, '<br />');
	    return text;
	  },
	  purifyNewLine(text, replaceSymbol = ' ') {
	    if (replaceSymbol !== "\n") {
	      text = text.replace(/\n/gi, replaceSymbol);
	    }
	    text = text.replace(/\[BR]/gi, replaceSymbol);
	    return text;
	  },
	  purifyBreakLine(text, replaceLetter = ' ') {
	    text = text.replace(/<br><br \/>/gi, '<br />');
	    text = text.replace(/<br \/><br>/gi, '<br />');
	    text = text.replace(/\[BR]/gi, '<br />');
	    text = text.replace(/<br \/>/gi, replaceLetter);

	    // text = text.replace(/<\/?[^>]+>/gi, '');

	    return text;
	  },
	  decodeTabulation(text) {
	    text = text.replace(/( ){4}/gi, '\t');
	    text = text.replace(/\t/gi, '&nbsp;&nbsp;&nbsp;&nbsp;');
	    return text;
	  },
	  purifyTabulation(text) {
	    text = text.replace(/&nbsp;&nbsp;&nbsp;&nbsp;/gi, " ");
	    return text;
	  },
	  purifyNbsp(text) {
	    text = text.replace(/&nbsp;/gi, " ");
	    return text;
	  },
	  removeDuplicateTags(text) {
	    if (text.substr(-6) === '<br />') {
	      text = text.substr(0, text.length - 6);
	    }
	    text = text.replace(/<br><br \/>/gi, '<br />');
	    text = text.replace(/<br \/><br>/gi, '<br />');
	    return text;
	  }
	};

	const ParserRecursionPrevention = {
	  _startTagReplacement: [],
	  _putReplacement: [],
	  _sendReplacement: [],
	  _codeReplacement: [],
	  clean() {
	    this._startTagReplacement = [];
	    this._putReplacement = [];
	    this._sendReplacement = [];
	    this._codeReplacement = [];
	  },
	  cutStartTag(text) {
	    text = text.replace(/\[(.+?)]/gi, tag => {
	      if (tag.startsWith('/')) {
	        return tag;
	      }
	      const id = this._startTagReplacement.length;
	      this._startTagReplacement.push(tag);
	      return '####REPLACEMENT_TAG_' + id + '####';
	    });
	    return text;
	  },
	  recoverStartTag(text) {
	    this._startTagReplacement.forEach((tag, index) => {
	      text = text.replace('####REPLACEMENT_TAG_' + index + '####', tag);
	    });
	    return text;
	  },
	  cutPutTag(text) {
	    text = text.replace(/\[PUT(?:=(.+?))?](.+?)?\[\/PUT]/gi, whole => {
	      const id = this._putReplacement.length;
	      this._putReplacement.push(whole);
	      return '####REPLACEMENT_PUT_' + id + '####';
	    });
	    return text;
	  },
	  recoverPutTag(text) {
	    this._putReplacement.forEach((value, index) => {
	      text = text.replace('####REPLACEMENT_PUT_' + index + '####', value);
	    });
	    return text;
	  },
	  cutSendTag(text) {
	    text = text.replace(/\[SEND(?:=(.+?))?](.+?)?\[\/SEND]/gi, whole => {
	      const id = this._sendReplacement.length;
	      this._sendReplacement.push(whole);
	      return '####REPLACEMENT_SEND_' + id + '####';
	    });
	    return text;
	  },
	  recoverSendTag(text) {
	    this._sendReplacement.forEach((value, index) => {
	      text = text.replace('####REPLACEMENT_SEND_' + index + '####', value);
	    });
	    return text;
	  },
	  cutCodeTag(text) {
	    text = text.replace(/\[CODE](<br \/>)?(.*?)\[\/CODE]/sig, whole => {
	      const id = this._codeReplacement.length;
	      this._codeReplacement.push(whole);
	      return '####REPLACEMENT_CODE_' + id + '####';
	    });
	    return text;
	  },
	  recoverCodeTag(text) {
	    this._codeReplacement.forEach((value, index) => {
	      text = text.replace('####REPLACEMENT_CODE_' + index + '####', value);
	    });
	    if (this._sendReplacement.length > 0) {
	      do {
	        this._sendReplacement.forEach((value, index) => {
	          text = text.replace('####REPLACEMENT_SEND_' + index + '####', value);
	        });
	      } while (text.includes('####REPLACEMENT_SEND_'));
	    }
	    return text;
	  },
	  recoverRecursionTag(text) {
	    if (this._sendReplacement.length > 0) {
	      do {
	        this._sendReplacement.forEach((value, index) => {
	          text = text.replace('####REPLACEMENT_SEND_' + index + '####', value);
	        });
	      } while (text.includes('####REPLACEMENT_SEND_'));
	    }
	    text = text.split('####REPLACEMENT_SP_').join('####REPLACEMENT_PUT_');
	    if (this._putReplacement.length > 0) {
	      do {
	        this._putReplacement.forEach((value, index) => {
	          text = text.replace('####REPLACEMENT_PUT_' + index + '####', value);
	        });
	      } while (text.includes('####REPLACEMENT_PUT_'));
	    }
	    return text;
	  }
	};

	const ParserReplace = {
	  decode(text, replace = []) {
	    if (replace.length === 0) {
	      return text;
	    }
	    const replacedText = replace.reduce((replacedText, item) => {
	      return this.replaceDate(replacedText, item);
	    }, text);
	    return replacedText;
	  },
	  replaceDate(text, item) {
	    const originalText = text.substring(item.start, item.end);
	    if (originalText !== item.text) {
	      return text;
	    }
	    const left = text.substring(0, item.start);
	    const right = text.substring(item.end);
	    return left + '[DATE=' + item.value + ']' + originalText + '[/DATE]' + right;
	  }
	};

	const {
	  FileIconType: FileIconType$1
	} = getConst();
	const ParserDisk = {
	  decode(text) {
	    const icon = ParserIcon.getIcon(FileIconType$1.file);
	    let diskText;
	    if (icon) {
	      diskText = `${icon} ${main_core.Loc.getMessage('IM_PARSER_ICON_TYPE_FILE')}`;
	    } else {
	      diskText = `[${main_core.Loc.getMessage('IM_PARSER_ICON_TYPE_FILE')}]`;
	    }
	    text = text.replace(/\[disk=\d+]/gi, diskText);
	    return text;
	  },
	  purify(text) {
	    return this.decode(text);
	  }
	};

	const Parser = {
	  decodeMessage(message) {
	    const messageFiles = getCore().store.getters['messages/getMessageFiles'](message.id);
	    return this.decode({
	      text: message.text,
	      attach: message.attach,
	      files: messageFiles,
	      replaces: message.replaces,
	      showIconIfEmptyText: false
	    });
	  },
	  decodeNotification(notification) {
	    var _notification$params$;
	    return this.decode({
	      text: notification.text,
	      attach: (_notification$params$ = notification.params.ATTACH) != null ? _notification$params$ : false,
	      replaces: notification.replaces,
	      showIconIfEmptyText: false,
	      showImageFromLink: false,
	      urlTarget: '_self'
	    });
	  },
	  decodeText(text) {
	    return this.decode({
	      text
	    });
	  },
	  decodeHtml(text) {
	    return this.decode({
	      text
	    });
	  },
	  decodeSmileForLegacyCore(text, options) {
	    options.ratioConfig = Object.freeze({
	      Default: 1,
	      Big: 1.6
	    });
	    return ParserSmile.decodeSmile(text, options);
	  },
	  decode(config) {
	    if (!main_core.Type.isPlainObject(config)) {
	      getLogger().error('Parser.decode: the first parameter must be object', config);
	      return '<b style="color:red">Parser.decode: the first parameter must be a parameter object</b';
	    }
	    let {
	      text
	    } = config;
	    const {
	      attach = false,
	      files = false,
	      replaces = [],
	      removeLinks = false,
	      showIconIfEmptyText = true,
	      showImageFromLink = true,
	      urlTarget = '_blank'
	    } = config;
	    if (!main_core.Type.isString(text)) {
	      if (main_core.Type.isNumber(text)) {
	        return text.toString();
	      }
	      return '';
	    }
	    if (!text) {
	      if (showIconIfEmptyText) {
	        text = ParserIcon.addIconToShortText({
	          text,
	          attach,
	          files
	        });
	      }
	      return text.trim();
	    }
	    text = ParserReplace.decode(text, replaces);
	    text = main_core.Text.encode(text.trim());
	    text = ParserCommon.decodeNewLine(text);
	    text = ParserCommon.decodeTabulation(text);
	    text = ParserRecursionPrevention.cutPutTag(text);
	    text = ParserRecursionPrevention.cutSendTag(text);
	    text = ParserRecursionPrevention.cutCodeTag(text);
	    text = ParserSmile.decodeSmile(text);
	    text = ParserSlashCommand.decode(text);
	    text = ParserQuote.decodeArrowQuote(text);
	    text = ParserQuote.decodeQuote(text);
	    text = ParserUrl.decode(text, {
	      urlTarget,
	      removeLinks
	    });
	    text = ParserFont.decode(text);
	    text = ParserLines.decode(text);
	    text = ParserMention.decode(text);
	    text = ParserCall.decode(text);
	    text = ParserImage.decodeIcon(text);
	    if (showImageFromLink) {
	      text = ParserImage.decodeLink(text);
	    }
	    text = ParserDisk.decode(text);
	    text = ParserAction.decodeDate(text);
	    text = ParserRecursionPrevention.cutStartTag(text);
	    text = ParserRecursionPrevention.recoverStartTag(text);
	    text = ParserRecursionPrevention.recoverSendTag(text);
	    text = ParserAction.decodeSend(text);
	    text = ParserRecursionPrevention.recoverPutTag(text);
	    text = ParserAction.decodePut(text);
	    text = ParserRecursionPrevention.recoverCodeTag(text);
	    text = ParserQuote.decodeCode(text);
	    text = ParserRecursionPrevention.recoverRecursionTag(text);
	    text = ParserCommon.removeDuplicateTags(text);
	    ParserRecursionPrevention.clean();
	    return text;
	  },
	  purifyMessage(message) {
	    const messageFiles = getCore().store.getters['messages/getMessageFiles'](message.id);
	    return this.purify({
	      text: message.text,
	      attach: message.attach,
	      files: messageFiles
	    });
	  },
	  purifyNotification(notification) {
	    var _notification$params$2;
	    const messageFiles = getCore().store.getters['messages/getMessageFiles'](notification.id);
	    return this.purify({
	      text: notification.text,
	      attach: (_notification$params$2 = notification.params.ATTACH) != null ? _notification$params$2 : false,
	      files: messageFiles
	    });
	  },
	  purifyRecent(recentMessage) {
	    const {
	      files,
	      attach
	    } = this.prepareConfigForRecent(recentMessage);
	    return this.purify({
	      text: recentMessage.message.text,
	      attach,
	      files,
	      showPhraseMessageWasDeleted: recentMessage.message.id !== 0
	    });
	  },
	  purifyText(text) {
	    return this.purify({
	      text
	    });
	  },
	  purify(config) {
	    if (!main_core.Type.isPlainObject(config)) {
	      getLogger().error('Parser.purify: the first parameter must be a object', config);
	      return 'Parser.purify: the first parameter must be a parameter object';
	    }
	    let {
	      text
	    } = config;
	    const {
	      attach = false,
	      files = false,
	      replaces = [],
	      showIconIfEmptyText = true,
	      showPhraseMessageWasDeleted = true
	    } = config;
	    if (!main_core.Type.isString(text)) {
	      text = main_core.Type.isNumber(text) ? text.toString() : '';
	    }
	    if (!text) {
	      text = ParserIcon.addIconToShortText({
	        text,
	        attach,
	        files
	      });
	      return text.trim();
	    }
	    text = main_core.Text.encode(text.trim());
	    text = ParserCommon.purifyNewLine(text, '\n');
	    text = ParserSlashCommand.purify(text);
	    text = ParserQuote.purifyArrowQuote(text);
	    text = ParserQuote.purifyQuote(text);
	    text = ParserQuote.purifyCode(text);
	    text = ParserAction.purifyPut(text);
	    text = ParserAction.purifySend(text);
	    text = ParserMention.purify(text);
	    text = ParserFont.purify(text);
	    text = ParserLines.purify(text);
	    text = ParserCall.purify(text);
	    text = ParserImage.purifyLink(text);
	    text = ParserImage.purifyIcon(text);
	    text = ParserUrl.purify(text);
	    text = ParserDisk.purify(text);
	    text = ParserCommon.purifyNewLine(text);
	    text = ParserIcon.addIconToShortText({
	      text,
	      attach,
	      files
	    });
	    if (text.length > 0) {
	      text = main_core.Text.decode(text);
	    } else if (showPhraseMessageWasDeleted) {
	      text = main_core.Loc.getMessage('IM_PARSER_MESSAGE_DELETED');
	    }
	    return text.trim();
	  },
	  prepareQuote(message) {
	    const {
	      id,
	      attach
	    } = message;
	    let {
	      text
	    } = message;
	    const files = getCore().store.getters['messages/getMessageFiles'](id);
	    text = main_core.Text.encode(text.trim());
	    text = ParserMention.purify(text);
	    text = ParserCall.purify(text);
	    text = ParserLines.purify(text);
	    text = ParserCommon.purifyBreakLine(text, '\n');
	    text = ParserCommon.purifyNbsp(text);
	    text = ParserUrl.removeSimpleUrlTag(text);
	    text = ParserQuote.purifyCode(text, ' ');
	    text = ParserQuote.purifyQuote(text, ' ');
	    text = ParserQuote.purifyArrowQuote(text, ' ');
	    text = ParserIcon.addIconToShortText({
	      text,
	      attach,
	      files
	    });
	    if (text.length > 0) {
	      text = main_core.Text.decode(text);
	    } else {
	      text = main_core.Loc.getMessage('IM_PARSER_MESSAGE_DELETED');
	    }
	    return text.trim();
	  },
	  prepareEdit(message) {
	    let {
	      text
	    } = message;
	    text = ParserUrl.removeSimpleUrlTag(text);
	    return text.trim();
	  },
	  prepareCopy(message) {
	    const {
	      id
	    } = message;
	    let {
	      text
	    } = message;
	    const files = getCore().store.getters['messages/getMessageFiles'](id).map(file => {
	      return `[DISK=${file.id}]\n`;
	    });
	    text = files.join('\n') + text;
	    return text.trim();
	  },
	  prepareConfigForRecent(recentMessage) {
	    let files = false;
	    const fileField = recentMessage.message.params.withFile;
	    if (main_core.Type.isBoolean(fileField)) {
	      files = fileField;
	    } else if (main_core.Type.isPlainObject(fileField)) {
	      files = [fileField];
	    }
	    let attach = false;
	    const attachField = recentMessage.message.params.withAttach;
	    if (main_core.Type.isBoolean(attachField) || main_core.Type.isStringFilled(attachField) || main_core.Type.isArray(attachField)) {
	      attach = attachField;
	    } else if (main_core.Type.isPlainObject(attachField)) {
	      attach = [attachField];
	    }
	    return {
	      files,
	      attach
	    };
	  },
	  executeClickEvent(event) {
	    ParserMention.executeClickEvent(event);
	    ParserQuote.executeClickEvent(event);
	  }
	};

	exports.Parser = Parser;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.Event,BX));
//# sourceMappingURL=parser.bundle.js.map

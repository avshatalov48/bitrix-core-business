/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_popup,main_core_events,main_core,main_loader) {
	'use strict';

	let _ = t => t,
	  _t;
	class PopupComponentsMakerItem extends main_core_events.EventEmitter {
	  constructor(options = {}) {
	    super();
	    this.html = null;
	    this.awaitContent = null;
	    this.flex = null;
	    this.withoutBackground = null;
	    this.backgroundColor = null;
	    this.backgroundImage = null;
	    this.background = null;
	    this.marginBottom = null;
	    this.disabled = null;
	    this.secondary = null;
	    this.overflow = null;
	    this.displayBlock = null;
	    this.attrs = null;
	    this.minHeight = null;
	    this.sizeLoader = 45;
	    this.asyncSecondary = null;
	    this.margin = null;
	    this.setParams(options);
	    this.layout = {
	      container: null
	    };
	    if (this.awaitContent) {
	      this.await();
	    }
	  }
	  setParams(options = {}) {
	    this.html = main_core.Type.isDomNode(options == null ? void 0 : options.html) ? options.html : this.html;
	    this.awaitContent = main_core.Type.isBoolean(options == null ? void 0 : options.awaitContent) ? options == null ? void 0 : options.awaitContent : this.awaitContent;
	    this.flex = main_core.Type.isNumber(options == null ? void 0 : options.flex) ? options.flex : this.flex;
	    this.withoutBackground = main_core.Type.isBoolean(options == null ? void 0 : options.withoutBackground) ? options.withoutBackground : this.withoutBackground;
	    this.background = main_core.Type.isString(options == null ? void 0 : options.background) ? options.background : this.background;
	    this.backgroundColor = main_core.Type.isString(options == null ? void 0 : options.backgroundColor) ? options.backgroundColor : this.backgroundColor;
	    this.backgroundImage = main_core.Type.isString(options == null ? void 0 : options.backgroundImage) ? options.backgroundImage : this.backgroundImage;
	    this.marginBottom = main_core.Type.isNumber(options == null ? void 0 : options.marginBottom) ? options.marginBottom : this.marginBottom;
	    this.disabled = main_core.Type.isBoolean(options == null ? void 0 : options.disabled) ? options.disabled : this.disabled;
	    this.secondary = main_core.Type.isBoolean(options == null ? void 0 : options.secondary) ? options.secondary : this.secondary;
	    this.overflow = main_core.Type.isBoolean(options == null ? void 0 : options.overflow) ? options.overflow : this.overflow;
	    this.displayBlock = main_core.Type.isBoolean(options == null ? void 0 : options.displayBlock) ? options.displayBlock : this.displayBlock;
	    this.attrs = main_core.Type.isPlainObject(options == null ? void 0 : options.attrs) ? options.attrs : this.attrs;
	    this.minHeight = main_core.Type.isString(options == null ? void 0 : options.minHeight) ? options.minHeight : this.minHeight;
	    this.margin = main_core.Type.isString(options.margin) ? options.margin : this.margin;
	    this.sizeLoader = main_core.Type.isNumber(options == null ? void 0 : options.sizeLoader) ? options.sizeLoader : this.sizeLoader;
	    this.asyncSecondary = (options == null ? void 0 : options.asyncSecondary) instanceof Promise ? options.asyncSecondary : this.asyncSecondary;
	  }
	  getLoader() {
	    if (!this.loader) {
	      this.loader = new main_loader.Loader({
	        target: this.getContainer(),
	        size: this.sizeLoader
	      });
	    }
	    return this.loader;
	  }
	  await() {
	    this.getContainer().classList.add('--awaiting');
	    this.showLoader();
	  }
	  stopAwait() {
	    this.getContainer().classList.remove('--awaiting');
	    this.hideLoader();
	  }
	  showLoader() {
	    void this.getLoader().show();
	  }
	  hideLoader() {
	    void this.getLoader().hide();
	  }
	  getContent() {
	    if (this.html) {
	      return this.html;
	    }
	    return '';
	  }
	  updateContent(node) {
	    if (main_core.Type.isDomNode(node)) {
	      main_core.Dom.clean(this.getContainer());
	      this.getContainer().appendChild(node);
	    }
	  }
	  setBackgroundColor(color) {
	    if (main_core.Type.isString(color)) {
	      this.getContainer().style.backgroundColor = color;
	    }
	  }
	  getMarginBottom() {
	    return this.marginBottom;
	  }
	  getContainer() {
	    if (!this.layout.container) {
	      this.layout.container = main_core.Tag.render(_t || (_t = _`
				<div class="ui-popupcomponentmaker__content--section-item">${0}</div>
			`), this.getContent());
	    }
	    if (this.background) {
	      this.layout.container.style.background = this.background;
	    }
	    if (this.backgroundColor) {
	      this.layout.container.style.backgroundColor = this.backgroundColor;
	    }
	    if (this.backgroundImage) {
	      this.layout.container.style.backgroundImage = this.backgroundImage;
	    }
	    if (this.withoutBackground && !this.backgroundColor && !this.background) {
	      this.layout.container.classList.add('--transparent');
	    }
	    if (this.flex) {
	      this.layout.container.style.flex = this.flex;
	    }
	    if (this.disabled) {
	      this.layout.container.classList.add('--disabled');
	    }
	    if (this.secondary) {
	      main_core.Dom.addClass(this.layout.container, '--secondary');
	    }
	    if (this.overflow) {
	      this.layout.container.classList.add('--overflow-hidden');
	    }
	    if (this.displayBlock) {
	      this.layout.container.classList.add('--block');
	    }
	    if (this.attrs) {
	      main_core.Dom.adjust(this.layout.container, {
	        attrs: this.attrs
	      });
	    }
	    if (this.minHeight) {
	      main_core.Dom.style(this.layout.container, 'min-height', this.minHeight);
	    }
	    if (this.margin) {
	      main_core.Dom.style(this.layout.container, 'margin', this.margin);
	    }
	    if (this.asyncSecondary) {
	      this.asyncSecondary.then(secondary => {
	        if (secondary === false) {
	          main_core.Dom.removeClass(this.layout.container, '--secondary');
	        } else {
	          main_core.Dom.addClass(this.layout.container, '--secondary');
	        }
	      });
	    }
	    return this.layout.container;
	  }
	}

	let _$1 = t => t,
	  _t$1,
	  _t2,
	  _t3;
	class PopupComponentsMaker {
	  constructor({
	    id,
	    target,
	    content,
	    width,
	    cacheable,
	    contentPadding,
	    padding,
	    offsetTop,
	    blurBackground,
	    useAngle
	  }) {
	    this.id = main_core.Type.isString(id) ? id : null;
	    this.target = main_core.Type.isElementNode(target) ? target : null;
	    this.content = content || null;
	    this.contentWrapper = null;
	    this.popup = null;
	    this.loader = null;
	    this.items = [];
	    this.width = main_core.Type.isNumber(width) ? width : null;
	    this.cacheable = main_core.Type.isBoolean(cacheable) ? cacheable : true;
	    this.contentPadding = main_core.Type.isNumber(contentPadding) ? contentPadding : 0;
	    this.padding = main_core.Type.isNumber(padding) ? padding : 13;
	    this.offsetTop = main_core.Type.isNumber(offsetTop) ? offsetTop : 0;
	    this.blurBlackground = main_core.Type.isBoolean(blurBackground) ? blurBackground : false;
	    this.useAngle = main_core.Type.isUndefined(useAngle) || useAngle !== false;
	  }
	  getItems() {
	    return this.items;
	  }
	  getItem(item) {
	    if (item instanceof PopupComponentsMakerItem) {
	      return item;
	    }
	    item = new PopupComponentsMakerItem(item);
	    if (this.items.indexOf(item) === -1) {
	      this.items.push(item);
	    }
	    return item;
	  }
	  getPopup() {
	    if (!this.popup) {
	      const popupWidth = this.width ? this.width : 350;
	      const popupId = this.id ? this.id + '-popup' : null;
	      this.popup = new main_popup.Popup(popupId, this.target, {
	        className: 'ui-popupcomponentmaker',
	        contentBackground: 'transparent',
	        contentPadding: this.contentPadding,
	        angle: this.useAngle ? {
	          offset: popupWidth / 2 - 16
	        } : false,
	        offsetTop: this.offsetTop,
	        width: popupWidth,
	        offsetLeft: -(popupWidth / 2) + (this.target ? this.target.offsetWidth / 2 : 0) + 40,
	        autoHide: true,
	        closeByEsc: true,
	        padding: this.padding,
	        animation: 'fading-slide',
	        content: this.getContentWrapper(),
	        cacheable: this.cacheable
	      });
	      if (this.blurBlackground) {
	        main_core.Dom.addClass(this.popup.getPopupContainer(), 'popup-with-radius');
	        this.setBlurBackground();
	        main_core_events.EventEmitter.subscribe(main_core_events.EventEmitter.GLOBAL_TARGET, 'BX.Intranet.Bitrix24:ThemePicker:onThemeApply', () => {
	          setTimeout(() => {
	            this.setBlurBackground();
	          }, 200);
	        });
	      }
	      this.popup.getContentContainer().style.overflowX = null;
	    }
	    return this.popup;
	  }
	  isShown() {
	    return this.getPopup().isShown();
	  }
	  getContentWrapper() {
	    if (!this.contentWrapper) {
	      this.contentWrapper = main_core.Tag.render(_t$1 || (_t$1 = _$1`
				<div class="ui-popupcomponentmaker__content"></div>
			`));
	      if (!this.content) {
	        return;
	      }
	      this.content.map(item => {
	        var _item$html;
	        let sectionNode = this.getSection();
	        if (item != null && item.marginBottom) {
	          main_core.Type.isNumber(item.marginBottom) ? sectionNode.style.marginBottom = item.marginBottom + 'px' : null;
	        }
	        if (item != null && item.className) {
	          main_core.Dom.addClass(sectionNode, item.className);
	        }
	        if (item != null && item.attrs) {
	          main_core.Dom.adjust(sectionNode, {
	            attrs: item.attrs
	          });
	        }
	        if (main_core.Type.isDomNode(item == null ? void 0 : item.html)) {
	          sectionNode.appendChild(this.getItem(item).getContainer());
	          this.contentWrapper.appendChild(sectionNode);
	        }
	        if (main_core.Type.isArray(item == null ? void 0 : item.html)) {
	          let innerSection = main_core.Tag.render(_t2 || (_t2 = _$1`
						<div class="ui-popupcomponentmaker__content--section-item --flex-column --transparent"></div>
					`));
	          item.html.map(itemObj => {
	            var _itemObj$html;
	            if (itemObj != null && (_itemObj$html = itemObj.html) != null && _itemObj$html.then) {
	              this.adjustPromise(itemObj, sectionNode);
	              main_core.Type.isNumber(itemObj == null ? void 0 : itemObj.marginBottom) ? sectionNode.style.marginBottom = itemObj.marginBottom + 'px' : null;
	            } else {
	              if (main_core.Type.isArray(itemObj == null ? void 0 : itemObj.html)) {
	                itemObj.html.map(itemInner => {
	                  innerSection.appendChild(this.getItem(itemInner).getContainer());
	                });
	                sectionNode.appendChild(innerSection);
	              } else {
	                sectionNode.appendChild(this.getItem(itemObj).getContainer());
	              }
	            }
	          });
	          this.contentWrapper.appendChild(sectionNode);
	        }
	        if (main_core.Type.isFunction(item == null ? void 0 : (_item$html = item.html) == null ? void 0 : _item$html.then)) {
	          this.adjustPromise(item, sectionNode);
	          this.contentWrapper.appendChild(sectionNode);
	        }
	      });
	    }
	    return this.contentWrapper;
	  }
	  adjustPromise(item, sectionNode) {
	    item.awaitContent = true;
	    let itemObj = this.getItem(item);
	    if (sectionNode) {
	      var _item$html2;
	      sectionNode.appendChild(itemObj.getContainer());
	      item == null ? void 0 : (_item$html2 = item.html) == null ? void 0 : _item$html2.then(result => {
	        if (main_core.Type.isDomNode(result)) {
	          itemObj.stopAwait();
	          itemObj.updateContent(result);
	        } else if (main_core.Type.isPlainObject(result) && main_core.Type.isDomNode(result.node)) {
	          if (main_core.Type.isPlainObject(result.options)) {
	            itemObj.setParams(result.options);
	          }
	          itemObj.stopAwait();
	          itemObj.updateContent(result.node);
	        }
	      });
	    }
	  }

	  /**
	   * @private
	   */
	  getSection() {
	    return main_core.Tag.render(_t3 || (_t3 = _$1`
			<div class="ui-popupcomponentmaker__content--section"></div>
		`));
	  }
	  setBlurBackground() {
	    const container = this.getPopup().getPopupContainer();
	    const windowStyles = window.getComputedStyle(document.body);
	    const backgroundImage = windowStyles.backgroundImage;
	    const backgroundColor = windowStyles.backgroundColor;
	    if (main_core.Type.isDomNode(container)) {
	      main_core.Dom.addClass(container, 'popup-window-blur');
	    }
	    let blurStyle = main_core.Dom.create('style', {
	      attrs: {
	        type: 'text/css',
	        id: 'styles-widget-blur'
	      }
	    });
	    let styles = '.popup-window-content:after { ' + 'background-image: ' + backgroundImage + ';' + 'background-color: ' + backgroundColor + '} ';
	    styles = document.createTextNode(styles);
	    blurStyle.appendChild(styles);
	    let stylesWithAngle = '.popup-window-angly:after { ' + 'background-color: ' + backgroundColor + '} ';
	    stylesWithAngle = document.createTextNode(stylesWithAngle);
	    blurStyle.appendChild(stylesWithAngle);
	    const headStyle = document.head.querySelector('#styles-widget-blur');
	    if (headStyle) {
	      main_core.Dom.replace(headStyle, blurStyle);
	    } else {
	      document.head.appendChild(blurStyle);
	    }
	  }
	  show() {
	    if (!main_core.Type.isDomNode(this.target)) {
	      return;
	    }
	    this.getPopup().show();
	  }
	  close() {
	    this.getPopup().close();
	  }
	}

	exports.PopupComponentsMakerItem = PopupComponentsMakerItem;
	exports.PopupComponentsMaker = PopupComponentsMaker;

}((this.BX.UI = this.BX.UI || {}),BX.Main,BX.Event,BX,BX));
//# sourceMappingURL=popupcomponentsmaker.bundle.js.map

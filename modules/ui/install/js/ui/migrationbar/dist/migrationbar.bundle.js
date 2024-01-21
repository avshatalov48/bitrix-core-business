/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core,ui_buttons,main_popup) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4,
	  _t5,
	  _t6,
	  _t7,
	  _t8,
	  _t9;
	class MigrationBar {
	  constructor({
	    target,
	    title,
	    cross,
	    items,
	    buttons,
	    link,
	    hint,
	    width,
	    height,
	    minWidth,
	    minHeight
	  }) {
	    this.target = main_core.Type.isDomNode(target) ? target : null;
	    this.title = main_core.Type.isString(title) || main_core.Type.isObject(title) ? title : null;
	    this.cross = main_core.Type.isBoolean(cross) ? cross : true;
	    this.items = main_core.Type.isArray(items) ? items : [];
	    this.buttons = main_core.Type.isArray(buttons) ? buttons : null;
	    this.link = main_core.Type.isObject(link) ? link : null;
	    this.hint = main_core.Type.isString(hint) ? hint : null;
	    this.width = main_core.Type.isNumber(width) ? width : null;
	    this.height = main_core.Type.isNumber(height) ? height : null;
	    this.minWidth = main_core.Type.isNumber(minWidth) ? minWidth : null;
	    this.minHeight = main_core.Type.isNumber(minHeight) ? minHeight : null;
	    this.layout = {
	      wrapper: null,
	      container: null,
	      items: null,
	      title: null,
	      text: null,
	      link: null,
	      remove: null,
	      buttons: null
	    };
	    this.popupHint = null;
	  }
	  getWrapper() {
	    if (!this.layout.wrapper) {
	      this.layout.wrapper = main_core.Tag.render(_t || (_t = _`
				<div class="ui-migration-bar__wrap"></div>
			`));
	    }
	    return this.layout.wrapper;
	  }
	  getContainer() {
	    if (!this.layout.container) {
	      this.layout.container = main_core.Tag.render(_t2 || (_t2 = _`
				<div class="ui-migration-bar__container ui-migration-bar__scope --show">
					${0}
					<div class="ui-migration-bar__content">
						${0}
						${0}
					</div>
					${0}
				</div>
			`), this.cross ? this.getCross() : '', this.title ? this.getTitle() : '', this.getItemContainer(), this.getButtonsContainer());
	      this.layout.container.addEventListener('animationend', () => {
	        this.layout.container.classList.remove('--show');
	      }, {
	        once: true
	      });
	      if (this.width) {
	        this.layout.container.style.setProperty('width', this.width + 'px');
	      }
	      if (this.height) {
	        this.layout.container.style.setProperty('height', this.height + 'px');
	      }
	      if (this.minWidth) {
	        this.layout.container.style.setProperty('min-width', this.minWidth + 'px');
	      }
	      if (this.minHeight) {
	        this.layout.container.style.setProperty('min-height', this.minHeight + 'px');
	      }
	    }
	    return this.layout.container;
	  }
	  getTitle() {
	    if (!this.layout.title) {
	      var _this$title, _this$title2;
	      const isTitleObject = main_core.Type.isObject(this.title);
	      const titleText = isTitleObject ? (_this$title = this.title) == null ? void 0 : _this$title.text : this.title;
	      const alignTitle = isTitleObject ? (_this$title2 = this.title) == null ? void 0 : _this$title2.align : null;
	      this.layout.title = main_core.Tag.render(_t3 || (_t3 = _`
				<div class="ui-migration-bar__title ${0}">
					${0}
					${0}
				</div>
			`), alignTitle ? '--align-' + alignTitle : '', titleText, this.hint ? this.getHint() : '');
	    }
	    return this.layout.title;
	  }
	  getCross() {
	    if (!this.layout.remove) {
	      this.layout.remove = main_core.Tag.render(_t4 || (_t4 = _`
				<div class="ui-migration-bar__remove">
					<div class="ui-migration-bar__remove-icon"></div>
				</div>
			`));
	      this.layout.remove.addEventListener('click', () => this.remove());
	    }
	    return this.layout.remove;
	  }
	  getButtonsContainer() {
	    if (!this.layout.buttons) {
	      this.layout.buttons = main_core.Tag.render(_t5 || (_t5 = _`
				<div class="ui-migration-bar__btn-container"></div>
			`));
	    }
	    return this.layout.buttons;
	  }
	  getItemContainer() {
	    if (!this.layout.items) {
	      this.layout.items = main_core.Tag.render(_t6 || (_t6 = _`
				<div class="ui-migration-bar__item-container"></div>
			`));
	    }
	    return this.layout.items;
	  }
	  getImage() {
	    return this.items;
	  }
	  getLink() {
	    if (!this.layout.link) {
	      var _this$link;
	      const linkNode = (_this$link = this.link) != null && _this$link.href ? 'a' : 'div';
	      this.layout.link = main_core.Tag.render(_t7 || (_t7 = _`
				<${0} class="ui-migration-bar__link">${0}</${0}>
			`), linkNode, this.link.text, linkNode);
	      const setCursorPointerMode = () => {
	        this.layout.link.classList.add('--cursor-pointer');
	      };
	      if (this.link.href) {
	        setCursorPointerMode();
	        this.layout.link.href = this.link.href;
	      }
	      if (this.link.target) {
	        this.layout.link.target = this.link.target;
	      }
	      if (this.link.events) {
	        setCursorPointerMode();
	        const eventKeys = Object.keys(this.link.events);
	        eventKeys.forEach(event => {
	          this.layout.link.addEventListener(event, () => {
	            this.link.events[event]();
	          });
	        });
	      }
	    }
	    return this.layout.link;
	  }
	  getHint() {
	    if (!this.layout.hint) {
	      this.layout.hint = main_core.Tag.render(_t8 || (_t8 = _`
				<div class="ui-migration-bar__hint">
					<div class="ui-migration-bar__hint-icon"></div>
				</div>
			`));
	      const popupHintWidth = 200;
	      const hintIconWidth = 20;
	      this.popupHint = new main_popup.Popup(null, this.layout.hint, {
	        darkMode: true,
	        content: this.hint,
	        angle: {
	          offset: popupHintWidth / 2 - 16
	        },
	        width: popupHintWidth,
	        offsetLeft: -(popupHintWidth / 2) + hintIconWidth / 2 + 40,
	        animation: 'fading-slide'
	      });
	      this.layout.hint.addEventListener('mouseover', () => {
	        this.popupHint.show();
	      });
	      this.layout.hint.addEventListener('mouseleave', () => {
	        this.popupHint.close();
	      });
	    }
	    return this.layout.hint;
	  }
	  adjustItemData() {
	    this.items = this.items.map(item => {
	      return {
	        id: item.id ? item.id : null,
	        src: item.src ? item.src : null,
	        events: item.events ? item.events : null
	      };
	    });
	  }
	  setButtons() {
	    if (this.buttons.length > 0) {
	      this.buttons.forEach(button => {
	        const option = Object.assign({}, button);
	        button = new ui_buttons.Button(option);
	        this.getButtonsContainer().appendChild(button.render());
	      });
	    }
	  }
	  render() {
	    var _this$link2;
	    if (this.target) {
	      this.getWrapper().style.setProperty('height', this.target.offsetHeight + 'px');
	      this.target.appendChild(this.getWrapper());
	      this.getWrapper().appendChild(this.getContainer());
	    }
	    if (this.items.length > 0) {
	      this.items.forEach(item => {
	        let itemNode = item;
	        itemNode = main_core.Tag.render(_t9 || (_t9 = _`
					<img class="ui-migration-bar__item">
				`));
	        this.getItemContainer().appendChild(itemNode);
	        const itemKeys = Object.keys(item);
	        for (let i = 0; i < itemKeys.length; i++) {
	          const event = itemKeys[i];
	          itemNode.setAttribute(event, item[event]);
	        }
	      });
	    }
	    if ((_this$link2 = this.link) != null && _this$link2.text) {
	      this.getItemContainer().appendChild(this.getLink());
	    }
	  }
	  remove() {
	    this.getContainer().classList.add('--close');
	    this.getContainer().addEventListener('animationend', () => {
	      this.getContainer().classList.remove('--close');
	      this.getContainer().remove();
	      this.getWrapper().remove();
	    }, {
	      once: true
	    });
	  }
	  show() {
	    this.adjustItemData();
	    this.setButtons();
	    this.render();
	  }
	}

	exports.MigrationBar = MigrationBar;

}((this.BX.UI = this.BX.UI || {}),BX,BX.UI,BX.Main));
//# sourceMappingURL=migrationbar.bundle.js.map

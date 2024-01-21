/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core,main_core_events) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2,
	  _t3;
	class BaseCard extends main_core_events.EventEmitter {
	  constructor(options = {}) {
	    super();
	    this.cache = new main_core.Cache.MemoryCache();
	    this.data = {
	      ...options
	    };
	    this.options = this.data;
	    this.id = main_core.Type.isStringFilled(this.options.id) ? this.options.id : main_core.Text.getRandom();
	    this.hidden = main_core.Text.toBoolean(this.options.hidden);
	    this.onClickHandler = main_core.Type.isFunction(this.options.onClick) ? this.options.onClick : () => {};
	    this.onClick = this.onClick.bind(this);
	    this.layout = this.getLayout();
	    this.header = this.getHeader();
	    this.body = this.getBody();
	    this.setTitle(this.options.title || '');
	    this.setHidden(this.options.hidden);
	    if (main_core.Type.isStringFilled(this.options.className)) {
	      main_core.Dom.addClass(this.layout, this.options.className);
	    }
	    if (main_core.Type.isObject(this.options.attrs)) {
	      main_core.Dom.adjust(this.layout, {
	        attrs: this.options.attrs
	      });
	    }
	    main_core.Event.bind(this.layout, 'click', this.onClick);
	  }
	  getLayout() {
	    return this.cache.remember('layout', () => {
	      return main_core.Tag.render(_t || (_t = _`
				<div class="ui-card">
					${0}
					${0}
				</div>
			`), this.getHeader(), this.getBody());
	    });
	  }
	  getHeader() {
	    return this.cache.remember('header', () => {
	      return main_core.Tag.render(_t2 || (_t2 = _`
				<div class="ui-card-header"></div>
			`));
	    });
	  }
	  getBody() {
	    return this.cache.remember('body', () => {
	      return main_core.Tag.render(_t3 || (_t3 = _`
				<div class="ui-card-body"></div>
			`));
	    });
	  }
	  setTitle(title) {
	    this.getHeader().textContent = title;
	  }
	  setHidden(hidden) {
	    main_core.Dom.attr(this.getLayout(), 'hidden', hidden || null);
	  }
	  onClick() {
	    this.onClickHandler(this);
	    this.emit('onClick');
	  }
	  show() {
	    this.setHidden(false);
	  }
	  isShown() {
	    return main_core.Dom.attr(this.getLayout(), 'hidden') === null;
	  }
	  hide() {
	    this.setHidden(true);
	  }
	  getNode() {
	    return this.getLayout();
	  }
	}

	let _$1 = t => t,
	  _t$1,
	  _t2$1,
	  _t3$1,
	  _t4,
	  _t5;
	class MessageCard extends BaseCard {
	  constructor(options) {
	    super(options);
	    main_core.Dom.addClass(this.getLayout(), 'ui-card-message');
	    this.onCloseClick = this.onCloseClick.bind(this);
	    if (this.options.angle === false) {
	      main_core.Dom.addClass(this.getLayout(), 'ui-card-message-without-angle');
	    }
	    if (main_core.Type.isStringFilled(this.options.icon)) {
	      main_core.Dom.append(this.getIcon(), this.getHeader());
	    }
	    if (!main_core.Type.isArray(this.options.actionElements)) {
	      this.options.actionElements = [];
	    }
	    main_core.Dom.append(this.getTitle(), this.getHeader());
	    main_core.Dom.append(this.getDescription(), this.getBody());
	    if (this.options.closeable !== false) {
	      main_core.Dom.append(this.getCloseButton(), this.getLayout());
	    }
	    if (this.options.hideActions !== true || this.options.more) {
	      main_core.Dom.append(this.getActionsContainer(), this.getLayout());
	    }
	    if (this.isAllowRestoreState()) {
	      const state = MessageCard.cache.get(this.options.id, {
	        shown: true
	      });
	      if (state.shown) {
	        this.show();
	      } else {
	        this.hide();
	      }
	    }
	  }
	  isAllowRestoreState() {
	    return this.options.restoreState && this.options.id;
	  }
	  getIcon() {
	    return this.cache.remember('icon', () => {
	      return main_core.Tag.render(_t$1 || (_t$1 = _$1`
				<div class="ui-card-message-icon" style="background-image: url(${0})"></div>
			`), this.options.icon);
	    });
	  }
	  getTitle() {
	    return this.cache.remember('title', () => {
	      return main_core.Tag.render(_t2$1 || (_t2$1 = _$1`
				<div class="ui-card-message-title">${0}</div>
			`), this.options.header);
	    });
	  }
	  getDescription() {
	    return this.cache.remember('description', () => {
	      return main_core.Tag.render(_t3$1 || (_t3$1 = _$1`
				<div class="ui-card-message-description">${0}</div>
			`), this.options.description);
	    });
	  }
	  getCloseButton() {
	    return this.cache.remember('closeButton', () => {
	      return main_core.Tag.render(_t4 || (_t4 = _$1`
				<div 
					class="ui-card-message-close-button" 
					onclick="${0}"
				></div>
			`), this.onCloseClick);
	    });
	  }
	  onCloseClick(event) {
	    event.preventDefault();
	    this.hide();
	    this.emit('onClose');
	    MessageCard.cache.set(this.options.id, {
	      shown: false
	    });
	  }
	  getActionsContainer() {
	    return this.cache.remember('actionsContainer', () => {
	      const actionWrapper = main_core.Tag.render(_t5 || (_t5 = _$1`
				<div class="ui-card-message-actions"></div>
			`));
	      this.options.actionElements.forEach(element => {
	        actionWrapper.appendChild(element);
	      });
	      return actionWrapper;
	    });
	  }
	  onClick() {
	    this.onClickHandler(this);
	    this.emit('onClick');
	  }
	}
	MessageCard.cache = new main_core.Cache.MemoryCache();

	exports.MessageCard = MessageCard;

}((this.BX.UI = this.BX.UI || {}),BX,BX.Event));
//# sourceMappingURL=messagecard.bundle.js.map

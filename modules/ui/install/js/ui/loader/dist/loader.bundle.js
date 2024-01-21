/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2;
	var _getContainer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getContainer");
	class Loader {
	  constructor(options) {
	    Object.defineProperty(this, _getContainer, {
	      value: _getContainer2
	    });
	    this.target = main_core.Type.isDomNode(options.target) ? options.target : null;
	    this.type = main_core.Type.isString(options.type) ? options.type : null;
	    this.size = main_core.Type.isString(options.size) ? options.size : null;
	    this.color = options.color ? options.color : null;
	    this.layout = {
	      container: null,
	      bulletContainer: null
	    };
	  }
	  bulletLoader() {
	    const color = this.color ? `background: ${this.color};` : '';
	    if (!this.layout.bulletContainer) {
	      this.layout.bulletContainer = main_core.Tag.render(_t || (_t = _`
				<div class="ui-loader__bullet">
					<div style="${0}" class="ui-loader__bullet_item"></div>
					<div style="${0}" class="ui-loader__bullet_item"></div>
					<div style="${0}" class="ui-loader__bullet_item"></div>
					<div style="${0}" class="ui-loader__bullet_item"></div>
					<div style="${0}" class="ui-loader__bullet_item"></div>
				</div>
			`), color, color, color, color, color);
	    }
	    this.layout.container = document.querySelector('.ui-loader__bullet');
	    return this.layout.bulletContainer;
	  }
	  show() {
	    this.layout.container.style.display = 'block';
	  }
	  hide() {
	    this.layout.container.style.display = '';
	  }
	  render() {
	    if (!main_core.Type.isDomNode(this.target)) {
	      console.warn('BX.LiveChatRestClient: your auth-token has expired, send query with a new token');
	      return;
	    } else {
	      main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _getContainer)[_getContainer](), this.target);
	      if (this.type === 'BULLET') {
	        if (this.size) {
	          if (this.size.toUpperCase() === 'XS') {
	            main_core.Dom.addClass(this.layout.container, 'ui-loader__bullet--xs');
	          }
	          if (this.size.toUpperCase() === 'S') {
	            main_core.Dom.addClass(this.layout.container, 'ui-loader__bullet--sm');
	          }
	          if (this.size.toUpperCase() === 'M') {
	            main_core.Dom.addClass(this.layout.container, 'ui-loader__bullet--md');
	          }
	          if (this.size.toUpperCase() === 'L') {
	            main_core.Dom.addClass(this.layout.container, 'ui-loader__bullet--lg');
	          }
	          if (this.size.toUpperCase() === 'XL') {
	            main_core.Dom.addClass(this.layout.container, 'ui-loader__bullet--xl');
	          }
	        }
	      }
	    }
	  }
	}
	function _getContainer2() {
	  if (!this.layout.container) {
	    this.layout.container = main_core.Tag.render(_t2 || (_t2 = _`
				<div class="ui-loader__container ui-loader__scope">
					${0}
				</div>
			`), this.type === 'BULLET' ? this.bulletLoader() : '');
	  }
	  return this.layout.container;
	}

	exports.Loader = Loader;

}((this.BX.UI = this.BX.UI || {}),BX));
//# sourceMappingURL=loader.bundle.js.map

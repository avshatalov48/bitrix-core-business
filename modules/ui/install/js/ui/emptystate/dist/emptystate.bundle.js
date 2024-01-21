/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core) {
	'use strict';

	let _ = t => t,
	  _t;
	class EmptyState {
	  constructor({
	    target,
	    size,
	    type
	  }) {
	    this.target = main_core.Type.isDomNode(target) ? target : null;
	    this.size = main_core.Type.isNumber(size) ? size : null;
	    this.type = main_core.Type.isString(type) ? type : null;
	    this.container = null;
	  }
	  getContainer() {
	    if (!this.container) {
	      this.container = main_core.Tag.render(_t || (_t = _`
				<div class="ui-emptystate ${0}">
					<i></i>
				</div>
			`), this.type ? '--' + this.type.toLowerCase() : '');
	      if (this.size) {
	        this.container.style.setProperty('height', this.size + 'px');
	        this.container.style.setProperty('width', this.size + 'px');
	      }
	    }
	    return this.container;
	  }
	  hide() {
	    main_core.Dom.clean(this.target);
	  }
	  show() {
	    if (this.target) {
	      main_core.Dom.clean(this.target);
	      main_core.Dom.append(this.getContainer(), this.target);
	    }
	  }
	}

	exports.EmptyState = EmptyState;

}((this.BX.UI = this.BX.UI || {}),BX));
//# sourceMappingURL=emptystate.bundle.js.map

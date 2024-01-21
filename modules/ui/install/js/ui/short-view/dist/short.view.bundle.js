/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_core,main_core_events) {
	'use strict';

	let _ = t => t,
	  _t;
	class ShortView extends main_core_events.EventEmitter {
	  constructor(params) {
	    super(params);
	    this.setEventNamespace('BX.UI.ShortView');
	    this.setShortView(params.isShortView);
	    this.node = null;
	  }
	  renderTo(container) {
	    if (!main_core.Type.isDomNode(container)) {
	      throw new Error('UI ShortView: HTMLElement not found');
	    }
	    main_core.Dom.append(this.render(), container);
	  }
	  render() {
	    const checked = this.getShortView() === 'Y' ? 'checked' : '';
	    this.node = main_core.Tag.render(_t || (_t = _`
			<div class="tasks-scrum__switcher--container tasks-scrum__scope-switcher" title="${0}">
				<label class="tasks-scrum__switcher--label">
				<div class="tasks-scrum__switcher--label-text">
					${0}
				</div>
				<input type="checkbox" class="tasks-scrum__switcher--checkbox" ${0}>
				<span class="tasks-scrum__switcher-cursor"></span>
				</label>
			</div>
		`), main_core.Loc.getMessage('UI_SHORT_VIEW_LABEL'), main_core.Loc.getMessage('UI_SHORT_VIEW_LABEL'), checked);
	    main_core.Event.bind(this.node, 'change', this.onChange.bind(this));
	    return this.node;
	  }
	  setShortView(value) {
	    this.shortView = value === 'Y' ? 'Y' : 'N';
	  }
	  getShortView() {
	    return this.shortView;
	  }
	  onChange() {
	    const checkboxNode = this.node.querySelector('input[type="checkbox"]');
	    this.setShortView(checkboxNode.checked ? 'Y' : 'N');
	    this.emit('change', this.getShortView());
	  }
	}

	exports.ShortView = ShortView;

}((this.BX.UI.ShortView = this.BX.UI.ShortView || {}),BX,BX.Event));
//# sourceMappingURL=short.view.bundle.js.map

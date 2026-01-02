/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_core,main_core_events,ui_buttons,ui_switcher) {
	'use strict';

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
	    const checked = this.getShortView() === 'Y';
	    this.node = new ui_buttons.SplitButton({
	      text: main_core.Loc.getMessage('UI_SHORT_VIEW_LABEL'),
	      round: true,
	      size: ui_buttons.ButtonSize.SMALL,
	      color: ui_buttons.ButtonColor.LIGHT_BORDER,
	      className: 'ui-btn-themes',
	      mainButton: {
	        onclick: (button, event) => {
	          event.preventDefault();
	          this.node.getSwitcher().toggle();
	        }
	      },
	      switcher: {
	        checked,
	        color: ui_switcher.SwitcherColor.primary,
	        handlers: {
	          toggled: () => this.onChange()
	        }
	      }
	    });
	    return this.node.render();
	  }
	  setShortView(value) {
	    this.shortView = value === 'Y' ? 'Y' : 'N';
	  }
	  getShortView() {
	    return this.shortView;
	  }
	  onChange() {
	    this.setShortView(this.node.getSwitcher().isChecked() ? 'Y' : 'N');
	    this.emit('change', this.getShortView());
	  }
	}

	exports.ShortView = ShortView;

}((this.BX.UI.ShortView = this.BX.UI.ShortView || {}),BX,BX.Event,BX.UI,BX.UI));
//# sourceMappingURL=short.view.bundle.js.map

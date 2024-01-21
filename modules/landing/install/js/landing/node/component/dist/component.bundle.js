this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports,landing_node_base) {
	'use strict';

	class Component extends landing_node_base.Base {
	  constructor(options) {
	    super(options);
	    this.type = 'component';
	    this.value = '';
	  }

	  /**
	   * @inheritDoc
	   * @return {BX.Landing.UI.Field.BaseField}
	   */
	  getField() {
	    return new BX.Landing.UI.Field.BaseField({
	      selector: this.selector
	    });
	  }

	  /**
	   * Gets value
	   * @return {string}
	   */
	  getValue() {
	    return this.value;
	  }

	  /**
	   * Sets value
	   * @inheritDoc
	   */
	  setValue(value, preventSave, preventHistory) {
	    this.value = value;
	  }
	}
	BX.Landing.Node.Component = Component;

	exports.Component = Component;

}((this.BX.Landing.Node = this.BX.Landing.Node || {}),BX.Landing.Node));
//# sourceMappingURL=component.bundle.js.map

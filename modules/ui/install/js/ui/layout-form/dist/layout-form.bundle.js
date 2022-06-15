this.BX = this.BX || {};
(function (exports,main_core) {
	'use strict';

	var LayoutForm = /*#__PURE__*/function () {
	  function LayoutForm() {
	    babelHelpers.classCallCheck(this, LayoutForm);
	    this.hiddenAttribute = 'data-form-row-hidden';
	    this.nodes = null;
	    this.init();
	  }

	  babelHelpers.createClass(LayoutForm, [{
	    key: "init",
	    value: function init() {
	      this.nodes = document.querySelectorAll('[' + this.hiddenAttribute + ']');

	      for (var i = 0; i < this.nodes.length; i++) {
	        main_core.Event.bind(this.nodes[i], "click", this.onClick.bind(this));
	        this.nodes[i].querySelector('.ui-ctl-element[type="checkbox"]').style.pointerEvents = 'none';
	        this.checkInitialBlockVisibility(this.nodes[i]);
	      }
	    }
	  }, {
	    key: "checkInitialBlockVisibility",
	    value: function checkInitialBlockVisibility(hiddenRow) {
	      var checkbox = hiddenRow.querySelector('.ui-ctl-element[type="checkbox"]');

	      if (checkbox.checked) {
	        var hiddenBlock = hiddenRow.nextElementSibling;

	        if (hiddenBlock.scrollHeight > 0) {
	          hiddenBlock.style.height = hiddenBlock.scrollHeight + 'px';
	          main_core.Dom.addClass(hiddenBlock, 'ui-form-row-hidden-show');
	        }
	      }
	    }
	  }, {
	    key: "onClick",
	    value: function onClick(event) {
	      // event.preventDefault();
	      var checkbox = event.currentTarget.querySelector('.ui-ctl-element[type="checkbox"]');
	      var hiddenBlock = event.currentTarget.nextElementSibling;
	      var height = hiddenBlock.scrollHeight;
	      this.toggleHiddenBLock(checkbox, hiddenBlock, height);
	    }
	  }, {
	    key: "toggleHiddenBLock",
	    value: function toggleHiddenBLock(checkbox, hiddenBlock, height) {
	      if (!checkbox.checked) {
	        checkbox.checked = true;
	        hiddenBlock.style.height = height + 'px';
	        main_core.Dom.addClass(hiddenBlock, 'ui-form-row-hidden-show');
	      } else {
	        checkbox.checked = false;
	        hiddenBlock.style.height = 0;
	        main_core.Dom.removeClass(hiddenBlock, 'ui-form-row-hidden-show');
	      }
	    }
	  }]);
	  return LayoutForm;
	}();

	exports.LayoutForm = LayoutForm;

}((this.BX.UI = this.BX.UI || {}),BX));
//# sourceMappingURL=layout-form.bundle.js.map

(function (exports) {
	'use strict';

	if (!window.DOMRect || typeof DOMRect.prototype.toJSON !== 'function' || typeof DOMRect.fromRect !== 'function') {
	  window.DOMRect = /*#__PURE__*/function () {
	    function DOMRect(x, y, width, height) {
	      babelHelpers.classCallCheck(this, DOMRect);
	      this.x = x || 0;
	      this.y = y || 0;
	      this.width = width || 0;
	      this.height = height || 0;
	    }

	    babelHelpers.createClass(DOMRect, [{
	      key: "toJSON",
	      value: function toJSON() {
	        return {
	          top: this.top,
	          left: this.left,
	          right: this.right,
	          bottom: this.bottom,
	          width: this.width,
	          height: this.height,
	          x: this.x,
	          y: this.y
	        };
	      }
	    }, {
	      key: "top",
	      get: function get() {
	        return this.y;
	      }
	    }, {
	      key: "left",
	      get: function get() {
	        return this.x;
	      }
	    }, {
	      key: "right",
	      get: function get() {
	        return this.x + this.width;
	      }
	    }, {
	      key: "bottom",
	      get: function get() {
	        return this.y + this.height;
	      }
	    }], [{
	      key: "fromRect",
	      value: function fromRect(otherRect) {
	        return new DOMRect(otherRect.x, otherRect.y, otherRect.width, otherRect.height);
	      }
	    }]);
	    return DOMRect;
	  }();
	}

}((this.window = this.window || {})));
//# sourceMappingURL=domrect.bundle.js.map

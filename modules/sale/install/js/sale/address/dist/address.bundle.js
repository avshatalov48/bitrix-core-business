this.BX = this.BX || {};
(function (exports,ui_vue,location_core,location_widget) {
	'use strict';

	var handleOutsideClick;
	var ClosableDirective = {
	  bind: function bind(el, binding, vnode) {
	    handleOutsideClick = function handleOutsideClick(e) {
	      e.stopPropagation();
	      var _binding$value = binding.value,
	          handler = _binding$value.handler,
	          exclude = _binding$value.exclude;
	      var clickedOnExcludedEl = false;
	      exclude.forEach(function (refName) {
	        if (!clickedOnExcludedEl) {
	          var excludedEl = vnode.context.$refs[refName];
	          clickedOnExcludedEl = excludedEl.contains(e.target);
	        }
	      });

	      if (!el.contains(e.target) && !clickedOnExcludedEl) {
	        vnode.context[handler]();
	      }
	    };

	    document.addEventListener('click', handleOutsideClick);
	    document.addEventListener('touchstart', handleOutsideClick);
	  },
	  unbind: function unbind() {
	    document.removeEventListener('click', handleOutsideClick);
	    document.removeEventListener('touchstart', handleOutsideClick);
	  }
	};

	function _createForOfIteratorHelper(o) { if (typeof Symbol === "undefined" || o[Symbol.iterator] == null) { if (Array.isArray(o) || (o = _unsupportedIterableToArray(o))) { var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var it, normalCompletion = true, didErr = false, err; return { s: function s() { it = o[Symbol.iterator](); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it.return != null) it.return(); } finally { if (didErr) throw err; } } }; }

	function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(n); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

	function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }
	var AddressControlConstructor = ui_vue.Vue.extend({
	  directives: {
	    closable: ClosableDirective
	  },
	  props: {
	    name: {
	      type: String,
	      required: true
	    },
	    initValue: {
	      required: false
	    },
	    onChangeCallback: {
	      type: Function,
	      required: false
	    }
	  },
	  data: function data() {
	    return {
	      id: null,
	      isEditMode: null,
	      value: null,
	      addressWidget: null
	    };
	  },
	  methods: {
	    startOver: function startOver() {
	      this.addressWidget.address = null;
	      this.changeValue(null);
	      this.closeMap();
	    },
	    changeValue: function changeValue(newValue) {
	      this.$emit('change', newValue);
	      this.value = newValue;

	      if (this.onChangeCallback) {
	        setTimeout(this.onChangeCallback, 0);
	      }
	    },
	    buildAddress: function buildAddress(value) {
	      try {
	        return new BX.Location.Core.Address(JSON.parse(value));
	      } catch (e) {
	        return null;
	      }
	    },
	    getMap: function getMap() {
	      if (!this.addressWidget) {
	        return null;
	      }

	      var _iterator = _createForOfIteratorHelper(this.addressWidget.features),
	          _step;

	      try {
	        for (_iterator.s(); !(_step = _iterator.n()).done;) {
	          var feature = _step.value;

	          if (feature instanceof BX.Location.Widget.MapFeature) {
	            return feature;
	          }
	        }
	      } catch (err) {
	        _iterator.e(err);
	      } finally {
	        _iterator.f();
	      }

	      return null;
	    },
	    showMap: function showMap() {
	      var map = this.getMap();

	      if (map) {
	        map.showMap();
	      }
	    },
	    closeMap: function closeMap() {
	      var map = this.getMap();

	      if (map) {
	        map.closeMap();
	      }

	      this.isEditMode = false;
	    },
	    onInputControlClicked: function onInputControlClicked() {
	      if (this.value) {
	        this.showMap();
	      } else {
	        this.closeMap();
	      }
	    }
	  },
	  computed: {
	    wrapperClass: function wrapperClass() {
	      return {
	        'ui-ctl': true,
	        'ui-ctl-w100': true,
	        'ui-ctl-after-icon': true
	      };
	    },
	    addressFormatted: function addressFormatted() {
	      if (!this.value || !this.addressWidget) {
	        return '';
	      }

	      var address = this.buildAddress(this.value);

	      if (!address) {
	        return '';
	      }

	      return address.toString(this.addressWidget.addressFormat, location_core.AddressStringConverter.STRATEGY_TYPE_FIELD_SORT);
	    }
	  },
	  mounted: function mounted() {
	    var _this = this;

	    if (this.initValue) {
	      this.value = this.initValue;
	    }

	    var factory = new BX.Location.Widget.Factory();
	    this.addressWidget = factory.createAddressWidget({
	      address: this.initValue ? this.buildAddress(this.initValue) : null,
	      mapBehavior: 'manual',
	      popupBindOptions: {
	        position: 'right'
	      },
	      mode: location_core.ControlMode.edit,
	      useFeatures: {
	        fields: false,
	        map: true,
	        autocomplete: true
	      }
	    });
	    this.addressWidget.subscribeOnAddressChangedEvent(function (event) {
	      var data = event.getData();
	      _this.isEditMode = true;
	      var address = data.address;

	      if (!address.latitude || !address.longitude) {
	        _this.changeValue(null);

	        _this.closeMap();
	      } else {
	        _this.changeValue(address.toJson());

	        _this.showMap();
	      }
	    });
	    this.addressWidget.subscribeOnStateChangedEvent(function (event) {
	      var data = event.getData();

	      if (data.state === location_widget.State.DATA_INPUTTING) {
	        _this.changeValue(null);

	        _this.closeMap();
	      }
	    });
	    /**
	     * Render widget
	     */

	    this.addressWidget.render({
	      inputNode: this.$refs['input-node'],
	      mapBindElement: this.$refs['input-node'],
	      controlWrapper: this.$refs['control-wrapper']
	    });
	  },
	  template: "\n\t\t<div\n\t\t\tv-closable=\"{\n\t\t\t  exclude: ['input-node'],\n\t\t\t  handler: 'closeMap'\n\t\t\t}\"\n\t\t\tclass=\"ui-ctl-w100\"\n\t\t>\n\t\t\t<div :class=\"wrapperClass\" ref=\"control-wrapper\">\n\t\t\t\t<div\n\t\t\t\t\t@click=\"startOver\"\n\t\t\t\t\tclass=\"ui-ctl-after ui-ctl-icon-btn ui-ctl-icon-clear\"\n\t\t\t\t></div>\n\t\t\t\t<input\n\t\t\t\t\t@click=\"onInputControlClicked\"\n\t\t\t\t\tref=\"input-node\"\n\t\t\t\t\ttype=\"text\"\n\t\t\t\t\tclass=\"ui-ctl-element ui-ctl-textbox\"\n\t\t\t\t\tv-html=\"addressFormatted\"\n\t\t\t\t/>\n\t\t\t\t<input v-model=\"value\" type=\"hidden\" :name=\"name\" />\n\t\t\t</div>\t\t\t\t\n\t\t</div>\n\t"
	});

	exports.AddressControlConstructor = AddressControlConstructor;

}((this.BX.Sale = this.BX.Sale || {}),BX,BX.Location.Core,BX.Location.Widget));
//# sourceMappingURL=address.bundle.js.map

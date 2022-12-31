this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,main_core,landing_ui_panel_iconpanel,landing_ui_field_image,landing_ui_card_iconoptionscard) {
	'use strict';

	/**
	 * @memberOf BX.Landing.UI.Field
	 */

	var Icon = /*#__PURE__*/function (_Image) {
	  babelHelpers.inherits(Icon, _Image);

	  function Icon(data) {
	    var _this;

	    babelHelpers.classCallCheck(this, Icon);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Icon).call(this, data));
	    _this.uploadButton.layout.innerText = BX.Landing.Loc.getMessage("LANDING_ICONS_FIELD_BUTTON_REPLACE");
	    _this.editButton.layout.hidden = true;
	    _this.clearButton.layout.hidden = true;

	    _this.dropzone.removeEventListener("dragover", _this.onDragOver);

	    _this.dropzone.removeEventListener("dragleave", _this.onDragLeave);

	    _this.dropzone.removeEventListener("drop", _this.onDrop);

	    _this.preview.removeEventListener("dragenter", _this.onImageDragEnter);

	    _this.options = new landing_ui_card_iconoptionscard.IconOptionsCard();
	    main_core.Dom.append(_this.options.getLayout(), _this.right);
	    _this.onOptionClick = _this.onOptionClick.bind(babelHelpers.assertThisInitialized(_this));

	    _this.options.subscribe('onChange', _this.onOptionClick);

	    var sourceClassList = _this.content.classList;
	    var newClassList = [];
	    landing_ui_panel_iconpanel.IconPanel.getLibraries().then(function (libraries) {
	      if (libraries.length === 0) {
	        this.uploadButton.disable();
	      } else {
	        libraries.forEach(function (library) {
	          library.categories.forEach(function (category) {
	            category.items.forEach(function (item) {
	              var itemClasses = '';

	              if (main_core.Type.isObject(item)) {
	                itemClasses = item.options.join(' ');
	              } else {
	                itemClasses = item;
	              }

	              var iconClasses = itemClasses.split(" ");
	              iconClasses.forEach(function (iconClass) {
	                if (sourceClassList.indexOf(iconClass) !== -1 && newClassList.indexOf(iconClass) === -1) {
	                  newClassList.push(iconClass);
	                }
	              });
	            });
	          });
	        });
	        this.icon.innerHTML = "<span class=\"test " + newClassList.join(" ") + "\"></span>";
	      }

	      this.options.setOptionsByItem(newClassList);
	    }.bind(babelHelpers.assertThisInitialized(_this)));
	    return _this;
	  }

	  babelHelpers.createClass(Icon, [{
	    key: "onUploadClick",
	    value: function onUploadClick(event) {
	      var _this2 = this;

	      event.preventDefault();
	      landing_ui_panel_iconpanel.IconPanel.getInstance().show().then(function (result) {
	        _this2.options.setOptions(result.iconOptions, result.iconClassName);

	        _this2.setValue({
	          type: "icon",
	          classList: result.iconClassName.split(" ")
	        });
	      });
	    }
	  }, {
	    key: "onOptionClick",
	    value: function onOptionClick(event) {
	      var classList = event.getData().option.split(' ');
	      this.setValue({
	        type: 'icon',
	        classList: classList
	      });
	    }
	  }, {
	    key: "isChanged",
	    value: function isChanged() {
	      return this.getValue().classList.some(function (className) {
	        return this.content.classList.indexOf(className) === -1;
	      }, this);
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      var classList = this.classList;

	      if (this.selector) {
	        var selectorClassname = this.selector.split("@")[0].replace(".", "");
	        classList = main_core.Runtime.clone(this.classList).concat([selectorClassname]);
	        classList = BX.Landing.Utils.arrayUnique(classList);
	      }

	      return {
	        type: "icon",
	        src: "",
	        id: -1,
	        alt: "",
	        classList: classList,
	        url: Object.assign({}, this.url.getValue(), {
	          enabled: true
	        })
	      };
	    }
	  }, {
	    key: "reset",
	    value: function reset() {
	      this.setValue({
	        type: "icon",
	        src: "",
	        id: -1,
	        alt: "",
	        classList: [],
	        url: ''
	      });
	    }
	  }]);
	  return Icon;
	}(landing_ui_field_image.Image);

	exports.Icon = Icon;

}((this.BX.Landing.UI.Field = this.BX.Landing.UI.Field || {}),BX,BX.Landing.UI.Panel,BX.Landing.UI.Field,BX.Landing.UI.Card));
//# sourceMappingURL=icon.bundle.js.map

(function (exports,main_core,bizproc_automation,main_popup) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3, _templateObject4;
	var namespace = main_core.Reflection.namespace('BX.Bizproc.Component');
	var Scheme = /*#__PURE__*/function () {
	  function Scheme(options) {
	    babelHelpers.classCallCheck(this, Scheme);
	    babelHelpers.defineProperty(this, "selectedType", null);
	    babelHelpers.defineProperty(this, "selectedCategory", null);
	    babelHelpers.defineProperty(this, "selectedStatus", null);
	    if (main_core.Type.isPlainObject(options)) {
	      this.scheme = new bizproc_automation.TemplatesScheme(options.scheme);
	      this.signedParameters = options.signedParameters;
	      this.steps = options.steps;
	      this.action = options.action;
	      this.executeButton = options.executeButton;
	      this.errorsContainer = options.errorsContainer;
	      this.stepContentTypeContainer = options.stepsContentContainers[0];
	      this.stepContentCategoryContainer = options.stepsContentContainers[1];
	      this.stepContentStatusContainer = options.stepsContentContainers[2];
	    }
	  }
	  babelHelpers.createClass(Scheme, [{
	    key: "init",
	    value: function init() {
	      this.renderStepContents();
	      main_core.Event.bind(this.executeButton, 'click', this.onExecuteButtonClick.bind(this));
	    }
	  }, {
	    key: "renderStepContents",
	    value: function renderStepContents() {
	      var _this = this;
	      var steps = [[this.selectedType, this.stepContentTypeContainer, this.onTypeSelectorClick.bind(this)], [this.selectedCategory, this.stepContentCategoryContainer, this.onCategorySelectorClick.bind(this)], [this.selectedStatus, this.stepContentStatusContainer, this.onStatusSelectorClick.bind(this)]];
	      var completedSteps = 0;
	      steps.forEach(function (_ref) {
	        var _ref2 = babelHelpers.slicedToArray(_ref, 3),
	          selected = _ref2[0],
	          container = _ref2[1],
	          onclick = _ref2[2];
	        var text = main_core.Type.isNil(selected) ? BX.message('BIZPROC_AUTOMATION_SCHEME_DROPDOWN_PLACEHOLDER') : selected.Name;
	        _this.renderDropdownStepContent(container, text, onclick);
	        if (selected) {
	          completedSteps += 1;
	        }
	      });
	      if (!main_core.Type.isNil(this.selectedType) && this.scheme.getTypeCategories(this.selectedType).length <= 0) {
	        this.renderTextStepContent(this.stepContentCategoryContainer, BX.message('BIZPROC_AUTOMATION_SCHEME_CATEGORIES_NOT_EXISTS'));
	        completedSteps += 1;
	      }
	      this.stepTo(completedSteps);
	    }
	  }, {
	    key: "renderDropdownStepContent",
	    value: function renderDropdownStepContent(target, text, onclick) {
	      main_core.Dom.clean(target);
	      var dropdownNode = main_core.Dom.create('div', {
	        attrs: {
	          "class": 'ui-ctl ui-ctl-after-icon ui-ctl-dropdown'
	        },
	        events: {
	          click: onclick
	        },
	        children: [main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-ctl-after ui-ctl-icon-angle\"></div>"]))), main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-ctl-element\">", "</div>"])), main_core.Text.encode(text))]
	      });
	      target.appendChild(main_core.Dom.create('div', {
	        attrs: {
	          "class": 'bizproc-automation-scheme__content --padding-15'
	        },
	        children: [dropdownNode]
	      }));
	    }
	  }, {
	    key: "renderTextStepContent",
	    value: function renderTextStepContent(target, text) {
	      main_core.Dom.clean(target);
	      target.appendChild(main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"bizproc-automation-scheme__content\">\n\t\t\t\t\t<div class=\"ui-alert ui-alert-success\">\n\t\t\t\t\t\t<span class=\"ui-alert-message\">", "</span>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"])), main_core.Text.encode(text)));
	    }
	  }, {
	    key: "onExecuteButtonClick",
	    value: function onExecuteButtonClick(event) {
	      var _this2 = this;
	      event.preventDefault();
	      if (!this.selectedType || !this.selectedStatus) {
	        this.showError({
	          message: BX.message("BIZPROC_AUTOMATION_SCHEME_DESTINATION_SCOPE_ERROR_ACTION_".concat(this.action))
	        });
	        main_core.Dom.removeClass(this.executeButton, 'ui-btn-wait');
	        return;
	      }
	      BX.ajax.runComponentAction('bitrix:bizproc.automation.scheme', 'copyMove', {
	        mode: 'class',
	        signedParameters: this.signedParameters,
	        data: {
	          dstScope: {
	            DocumentType: this.selectedType,
	            Category: this.selectedCategory,
	            Status: this.selectedStatus
	          }
	        }
	      }).then(function (response) {
	        if (_this2.isSlider()) {
	          var sliderData = BX.SidePanel.Instance.getTopSlider().getData();
	          Object.entries(response.data).forEach(function (_ref3) {
	            var _ref4 = babelHelpers.slicedToArray(_ref3, 2),
	              key = _ref4[0],
	              data = _ref4[1];
	            return sliderData.set(key, data);
	          });
	          sliderData.set('targetScope', {
	            documentType: _this2.selectedType,
	            category: _this2.selectedCategory,
	            status: _this2.selectedStatus
	          });
	          BX.SidePanel.Instance.close();
	        }
	        main_core.Dom.removeClass(_this2.executeButton, 'ui-btn-wait');
	      })["catch"](function (response) {
	        response.errors.forEach(function (error) {
	          return _this2.showError(error);
	        });
	        main_core.Dom.removeClass(_this2.executeButton, 'ui-btn-wait');
	      });
	    }
	  }, {
	    key: "isSlider",
	    value: function isSlider() {
	      return location.href.toString().indexOf('SIDE_SLIDER') > 0;
	    }
	  }, {
	    key: "stepTo",
	    value: function stepTo(index) {
	      this.steps.forEach(function (elem, i) {
	        if (i < index) {
	          main_core.Dom.addClass(elem, '--success');
	        } else {
	          main_core.Dom.removeClass(elem, '--success');
	        }
	      }.bind(this));
	    }
	  }, {
	    key: "onTypeSelectorClick",
	    value: function onTypeSelectorClick(event) {
	      event.preventDefault();
	      var self = this;
	      this.adjustDropdown(event.target, this.scheme.getDocumentTypes().map(function (type) {
	        return {
	          id: type.Type,
	          text: type.Name,
	          onclick: function onclick(event) {
	            event.preventDefault();
	            this.close();
	            self.selectedType = type;
	            self.selectedCategory = null;
	            self.selectedStatus = null;
	            self.renderStepContents();
	          }
	        };
	      }));
	    }
	  }, {
	    key: "onCategorySelectorClick",
	    value: function onCategorySelectorClick(event) {
	      event.preventDefault();
	      var self = this;
	      var categories = !main_core.Type.isNil(this.selectedType) ? this.scheme.getTypeCategories(this.selectedType) : [];
	      if (categories.length > 0) {
	        this.adjustDropdown(event.target, categories.map(function (category) {
	          return {
	            id: category.Id,
	            text: category.Name,
	            onclick: function onclick(event) {
	              event.preventDefault();
	              this.close();
	              self.selectedCategory = category;
	              self.selectedStatus = null;
	              self.renderStepContents();
	            }
	          };
	        }));
	      }
	    }
	  }, {
	    key: "onStatusSelectorClick",
	    value: function onStatusSelectorClick(event) {
	      event.preventDefault();
	      var self = this;
	      var statuses = [];
	      if (!main_core.Type.isNil(this.selectedType)) {
	        statuses = this.scheme.getTypeStatuses(this.selectedType, this.selectedCategory);
	      }
	      if (statuses.length > 0) {
	        this.adjustDropdown(event.target, statuses.map(function (status) {
	          return {
	            id: status.Id,
	            text: status.Name,
	            onclick: function onclick(event) {
	              event.preventDefault();
	              this.close();
	              self.selectedStatus = status;
	              self.renderStepContents();
	            }
	          };
	        }));
	      }
	    }
	  }, {
	    key: "adjustDropdown",
	    value: function adjustDropdown(target, items) {
	      var popupMenu = new main_popup.Menu({
	        autoHide: true,
	        bindElement: target,
	        width: target.offsetWidth,
	        closeByEsc: true,
	        items: items
	      });
	      popupMenu.show();
	    }
	  }, {
	    key: "showError",
	    value: function showError(error) {
	      var errorNode = main_core.Dom.create('div', {
	        props: {
	          className: 'ui-alert ui-alert-danger'
	        },
	        children: [main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["<span class=\"ui-alert-message\">", "</span>"])), error.message)]
	      });
	      this.errorsContainer.append(errorNode);
	    }
	  }]);
	  return Scheme;
	}();
	namespace.Scheme = Scheme;

}((this.window = this.window || {}),BX,BX.Bizproc.Automation,BX.Main));
//# sourceMappingURL=script.js.map

this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,ui_progressbar,main_core_events,main_popup,ui_alerts,ui_buttons,main_core) {
	'use strict';

	/**
	 * @namespace {BX.UI.StepProcessing}
	 */
	var ProcessResultStatus = {
	  progress: 'PROGRESS',
	  completed: 'COMPLETED'
	};
	var ProcessState = {
	  intermediate: 'INTERMEDIATE',
	  running: 'RUNNING',
	  completed: 'COMPLETED',
	  stopped: 'STOPPED',
	  error: 'ERROR',
	  canceling: 'CANCELING'
	};

	var _templateObject, _templateObject2, _templateObject3, _templateObject4, _templateObject5, _templateObject6, _templateObject7, _templateObject8;
	var BaseField = /*#__PURE__*/function () {
	  function BaseField(options) {
	    babelHelpers.classCallCheck(this, BaseField);
	    babelHelpers.defineProperty(this, "obligatory", false);
	    babelHelpers.defineProperty(this, "emptyMessage", '');
	    babelHelpers.defineProperty(this, "className", '');
	    babelHelpers.defineProperty(this, "disabled", false);
	    babelHelpers.defineProperty(this, "value", null);
	    this.id = 'id' in options ? options.id : 'ProcessDialogField_' + Math.random().toString().substring(2);
	    this.name = options.name;
	    this.type = options.type;
	    this.title = options.title;
	    this.obligatory = !!options.obligatory;

	    if ('value' in options) {
	      this.setValue(options.value);
	    }

	    if ('emptyMessage' in options) {
	      this.emptyMessage = options.emptyMessage;
	    } else {
	      this.emptyMessage = main_core.Loc.getMessage('UI_STEP_PROCESSING_EMPTY_ERROR') || '';
	    }
	  }

	  babelHelpers.createClass(BaseField, [{
	    key: "setValue",
	    value: function setValue(value) {
	      throw new Error('BX.UI.StepProcessing: Must be implemented by a subclass'); //this.value = value;
	      //return this;
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      throw new Error('BX.UI.StepProcessing: Must be implemented by a subclass'); //return this.value;
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      throw new Error('BX.UI.StepProcessing: Must be implemented by a subclass'); //return this.field;
	    }
	  }, {
	    key: "lock",
	    value: function lock() {
	      throw new Error('BX.UI.StepProcessing: Must be implemented by a subclass'); //this.disabled = flag;
	      //this.field.disabled = !!flag;
	      //return this;
	    }
	  }, {
	    key: "isFilled",
	    value: function isFilled() {
	      throw new Error('BX.UI.StepProcessing: Must be implemented by a subclass'); //return this.field;
	    }
	  }, {
	    key: "getInput",
	    value: function getInput() {
	      return this.field ? this.field : null;
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      if (!this.container) {
	        this.container = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<div class=\"", " ", "\"></div>"])), DialogStyle.ProcessOptionContainer, this.className);
	        this.container.appendChild(main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["<div class=\"", "\"></div>"])), DialogStyle.ProcessOptionsTitle)).appendChild(main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["<label for=\"", "_inp\">", "</label>"])), this.id, this.title));
	        this.container.appendChild(main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["<div class=\"", "\"></div>"])), DialogStyle.ProcessOptionsInput)).appendChild(this.render());

	        if (this.obligatory) {
	          var alertId = this.id + '_alert';
	          this.container.appendChild(main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["<div id=\"", "\" class=\"", "\" style=\"display:none\"></div>"])), alertId, DialogStyle.ProcessOptionsObligatory)).appendChild(main_core.Tag.render(_templateObject6 || (_templateObject6 = babelHelpers.taggedTemplateLiteral(["<span class=\"ui-alert-message\">", "</span>"])), this.emptyMessage));
	        }
	      }

	      return this.container;
	    }
	  }, {
	    key: "showWarning",
	    value: function showWarning(message) {
	      var alertId = this.id + '_alert';
	      var optionElement = this.container.querySelector('#' + alertId);

	      if (optionElement) {
	        if (main_core.Type.isStringFilled(message)) {
	          var messageElement = optionElement.querySelector('.ui-alert-message');
	          messageElement.innerHTML = message;
	        }

	        optionElement.style.display = 'block';
	      } else {
	        var _message = _message ? _message : this.emptyMessage;

	        if (main_core.Type.isStringFilled(_message)) {
	          this.container.appendChild(main_core.Tag.render(_templateObject7 || (_templateObject7 = babelHelpers.taggedTemplateLiteral(["<div id=\"", "\" class=\"", "\"></div>"])), alertId, DialogStyle.ProcessOptionsObligatory)).appendChild(main_core.Tag.render(_templateObject8 || (_templateObject8 = babelHelpers.taggedTemplateLiteral(["<span class=\"ui-alert-message\">", "</span>"])), _message));
	        }
	      }

	      return this;
	    }
	  }, {
	    key: "hideWarning",
	    value: function hideWarning() {
	      var alertId = this.id + '_alert';
	      var optionElement = this.container.querySelector('#' + alertId);

	      if (optionElement) {
	        optionElement.style.display = 'none';
	      }

	      return this;
	    }
	  }]);
	  return BaseField;
	}();

	var _templateObject$1;
	var TextField = /*#__PURE__*/function (_BaseField) {
	  babelHelpers.inherits(TextField, _BaseField);

	  function TextField(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, TextField);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(TextField).call(this, options));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "type", 'text');
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "className", DialogStyle.ProcessOptionText);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "rows", 10);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "cols", 50);

	    if (options.textSize) {
	      _this.cols = options.textSize;
	    }

	    if (options.textLine) {
	      _this.rows = options.textLine;
	    }

	    return _this;
	  }

	  babelHelpers.createClass(TextField, [{
	    key: "setValue",
	    value: function setValue(value) {
	      this.value = value;

	      if (this.field) {
	        this.field.value = this.value;
	      }

	      return this;
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      if (this.field && this.disabled !== true) {
	        if (typeof this.field.value !== 'undefined') {
	          this.value = this.field.value;
	        }
	      }

	      return this.value;
	    }
	  }, {
	    key: "isFilled",
	    value: function isFilled() {
	      if (this.field) {
	        if (typeof this.field.value !== 'undefined') {
	          return main_core.Type.isStringFilled(this.field.value);
	        }
	      }

	      return false;
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      if (!this.field) {
	        this.field = main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["<textarea id=\"", "\" name=\"", "\" cols=\"", "\" rows=\"", "\"></textarea>"])), this.id, this.name, this.cols, this.rows);
	      }

	      if (this.value) {
	        this.field.value = this.value;
	      }

	      return this.field;
	    }
	  }, {
	    key: "lock",
	    value: function lock() {
	      var flag = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;
	      this.disabled = flag;
	      this.field.disabled = !!flag;
	      return this;
	    }
	  }]);
	  return TextField;
	}(BaseField);

	var _templateObject$2;
	var FileField = /*#__PURE__*/function (_BaseField) {
	  babelHelpers.inherits(FileField, _BaseField);

	  function FileField(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, FileField);

	    if (!('emptyMessage' in options)) {
	      options.emptyMessage = main_core.Loc.getMessage('UI_STEP_PROCESSING_FILE_EMPTY_ERROR');
	    }

	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(FileField).call(this, options));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "type", 'file');
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "className", DialogStyle.ProcessOptionFile);
	    return _this;
	  }

	  babelHelpers.createClass(FileField, [{
	    key: "setValue",
	    value: function setValue(value) {
	      this.value = value;

	      if (this.field) {
	        if (value instanceof FileList) {
	          this.field.files = value;
	        } else if (value instanceof File) {
	          this.field.files[0] = value;
	        }
	      }

	      return this;
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      if (this.field && this.disabled !== true) {
	        if (typeof this.field.files[0] != "undefined") {
	          this.value = this.field.files[0];
	        }
	      }

	      return this.value;
	    }
	  }, {
	    key: "isFilled",
	    value: function isFilled() {
	      if (this.field) {
	        if (typeof this.field.files[0] != "undefined") {
	          return true;
	        }
	      }

	      return false;
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      if (!this.field) {
	        this.field = main_core.Tag.render(_templateObject$2 || (_templateObject$2 = babelHelpers.taggedTemplateLiteral(["<input type=\"file\" id=\"", "\" name=\"", "\">"])), this.id, this.name);
	      }

	      return this.field;
	    }
	  }, {
	    key: "lock",
	    value: function lock() {
	      var flag = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;
	      this.disabled = flag;
	      this.field.disabled = !!flag;
	      return this;
	    }
	  }]);
	  return FileField;
	}(BaseField);

	var _templateObject$3, _templateObject2$1, _templateObject3$1, _templateObject4$1, _templateObject5$1, _templateObject6$1, _templateObject7$1;
	var CheckboxField = /*#__PURE__*/function (_BaseField) {
	  babelHelpers.inherits(CheckboxField, _BaseField);

	  function CheckboxField(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, CheckboxField);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(CheckboxField).call(this, options));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "type", 'checkbox');
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "list", []);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "multiple", false);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "className", DialogStyle.ProcessOptionCheckbox);

	    if ('list' in options) {
	      _this.list = options.list;
	    }

	    _this.multiple = _this.list.length > 1;

	    if (_this.multiple) {
	      _this["class"] = DialogStyle.ProcessOptionMultiple;
	    }

	    return _this;
	  }

	  babelHelpers.createClass(CheckboxField, [{
	    key: "setValue",
	    value: function setValue(value) {
	      if (this.multiple) {
	        this.value = main_core.Type.isArray(value) ? value : [value];
	      } else {
	        if (value === 'Y' || value === 'N' || value === null || value === undefined) {
	          value = value === 'Y'; //Boolean
	        }

	        this.value = value;
	      }

	      if (this.field) {
	        if (this.multiple) {
	          var optionElements = this.field.querySelectorAll("input[type=checkbox]");

	          if (optionElements) {
	            for (var k = 0; k < optionElements.length; k++) {
	              optionElements[k].checked = this.value.indexOf(optionElements[k].value) !== -1;
	            }
	          }
	        } else {
	          var optionElement = this.field.querySelector("input[type=checkbox]");

	          if (optionElement) {
	            optionElement.checked = main_core.Type.isBoolean(this.value) ? this.value : optionElement.value === this.value;
	          }
	        }
	      }

	      return this;
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      if (this.field && this.disabled !== true) {
	        if (this.multiple) {
	          this.value = [];
	          var optionElements = this.field.querySelectorAll("input[type=checkbox]");

	          if (optionElements) {
	            for (var k = 0; k < optionElements.length; k++) {
	              if (optionElements[k].checked) {
	                this.value.push(optionElements[k].value);
	              }
	            }
	          }
	        } else {
	          var optionElement = this.field.querySelector("input[type=checkbox]");

	          if (optionElement) {
	            if (optionElement.value && optionElement.value !== 'Y') {
	              this.value = optionElement.checked ? optionElement.value : '';
	            } else {
	              this.value = optionElement.checked;
	            }
	          }
	        }
	      }

	      return this.value;
	    }
	  }, {
	    key: "isFilled",
	    value: function isFilled() {
	      if (this.field) {
	        var optionElements = this.field.querySelectorAll("input[type=checkbox]");

	        if (optionElements) {
	          return true;
	        }
	      }

	      return false;
	    }
	  }, {
	    key: "getInput",
	    value: function getInput() {
	      if (this.field) {
	        if (this.multiple) {
	          var optionElements = this.field.querySelectorAll("input[type=checkbox]");

	          if (optionElements) {
	            return optionElements;
	          }
	        } else {
	          var optionElement = this.field.querySelector("input[type=checkbox]");

	          if (optionElement) {
	            return optionElement;
	          }
	        }
	      }

	      return null;
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      var _this2 = this;

	      if (!this.field) {
	        this.field = main_core.Tag.render(_templateObject$3 || (_templateObject$3 = babelHelpers.taggedTemplateLiteral(["<div id=\"", "\"></div>"])), this.id);
	      }

	      if (this.multiple) {
	        Object.keys(this.list).forEach(function (itemId) {
	          if (_this2.value.indexOf(itemId) !== -1) {
	            _this2.field.appendChild(main_core.Tag.render(_templateObject2$1 || (_templateObject2$1 = babelHelpers.taggedTemplateLiteral(["<label><input type=\"checkbox\" name=\"", "[]\" value=\"", "\" checked>", "</label>"])), _this2.name, itemId, _this2.list[itemId]));
	          } else {
	            _this2.field.appendChild(main_core.Tag.render(_templateObject3$1 || (_templateObject3$1 = babelHelpers.taggedTemplateLiteral(["<label><input type=\"checkbox\" name=\"", "[]\" value=\"", "\">", "</label>"])), _this2.name, itemId, _this2.list[itemId]));
	          }
	        });
	      } else {
	        if (main_core.Type.isBoolean(this.value)) {
	          if (this.value) {
	            this.field.appendChild(main_core.Tag.render(_templateObject4$1 || (_templateObject4$1 = babelHelpers.taggedTemplateLiteral(["<input type=\"checkbox\" id=\"", "_inp\" name=\"", "\" value=\"Y\" checked>"])), this.id, this.name));
	          } else {
	            this.field.appendChild(main_core.Tag.render(_templateObject5$1 || (_templateObject5$1 = babelHelpers.taggedTemplateLiteral(["<input type=\"checkbox\" id=\"", "_inp\" name=\"", "\" value=\"Y\">"])), this.id, this.name));
	          }
	        } else {
	          if (this.value !== '') {
	            this.field.appendChild(main_core.Tag.render(_templateObject6$1 || (_templateObject6$1 = babelHelpers.taggedTemplateLiteral(["<input type=\"checkbox\" id=\"", "_inp\" name=\"", "\" value=\"", "\" checked>"])), this.id, this.name, this.value));
	          } else {
	            this.field.appendChild(main_core.Tag.render(_templateObject7$1 || (_templateObject7$1 = babelHelpers.taggedTemplateLiteral(["<input type=\"checkbox\" id=\"", "_inp\" name=\"", "\" value=\"", ">\""])), this.id, this.name, this.value));
	          }
	        }
	      }

	      return this.field;
	    }
	  }, {
	    key: "lock",
	    value: function lock() {
	      var flag = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;
	      this.disabled = flag;

	      if (this.field) {
	        var optionElements = this.field.querySelectorAll("input[type=checkbox]");

	        if (optionElements) {
	          for (var k = 0; k < optionElements.length; k++) {
	            optionElements[k].disabled = !!flag;
	          }
	        }
	      }

	      return this;
	    }
	  }]);
	  return CheckboxField;
	}(BaseField);

	var _templateObject$4, _templateObject2$2;
	var SelectField = /*#__PURE__*/function (_BaseField) {
	  babelHelpers.inherits(SelectField, _BaseField);

	  function SelectField(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, SelectField);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(SelectField).call(this, options));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "type", 'select');
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "multiple", false);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "list", []);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "className", DialogStyle.ProcessOptionSelect);

	    if ('multiple' in options) {
	      _this.multiple = main_core.Type.isBoolean(options.multiple) ? options.multiple === true : options.multiple === 'Y';
	    }

	    if (_this.multiple) {
	      if ('size' in options) {
	        _this.size = options.size;
	      }
	    }

	    if ('list' in options) {
	      _this.list = options.list;
	    }

	    return _this;
	  }

	  babelHelpers.createClass(SelectField, [{
	    key: "setValue",
	    value: function setValue(value) {
	      if (this.multiple) {
	        this.value = main_core.Type.isArray(value) ? value : [value];
	      } else {
	        this.value = value;
	      }

	      if (this.field) {
	        if (this.multiple) {
	          for (var k = 0; k < this.field.options.length; k++) {
	            this.field.options[k].selected = this.value.indexOf(this.field.options[k].value) !== -1;
	          }
	        } else {
	          this.field.value = this.value;
	        }
	      }

	      return this;
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      if (this.field && this.disabled !== true) {
	        if (this.multiple) {
	          this.value = [];

	          for (var k = 0; k < this.field.options.length; k++) {
	            if (this.field.options[k].selected) {
	              this.value.push(this.field.options[k].value);
	            }
	          }
	        } else {
	          this.value = this.field.value;
	        }
	      }

	      return this.value;
	    }
	  }, {
	    key: "isFilled",
	    value: function isFilled() {
	      if (this.field) {
	        for (var k = 0; k < this.field.options.length; k++) {
	          if (this.field.options[k].selected) {
	            return true;
	          }
	        }
	      }

	      return false;
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      var _this2 = this;

	      if (!this.field) {
	        this.field = main_core.Tag.render(_templateObject$4 || (_templateObject$4 = babelHelpers.taggedTemplateLiteral(["<select id=\"", "\" name=\"", "\"></select>"])), this.id, this.name);
	      }

	      if (this.multiple) {
	        this.field.multiple = 'multiple';

	        if (this.size) {
	          this.field.size = this.size;
	        }
	      }

	      Object.keys(this.list).forEach(function (itemId) {
	        var selected;

	        if (_this2.multiple === true) {
	          selected = _this2.value.indexOf(itemId) !== -1;
	        } else {
	          selected = itemId === _this2.value;
	        }

	        var option = _this2.field.appendChild(main_core.Tag.render(_templateObject2$2 || (_templateObject2$2 = babelHelpers.taggedTemplateLiteral(["<option value=\"", "\">", "</option>"])), itemId, _this2.list[itemId]));

	        if (selected) {
	          option.selected = 'selected';
	        }
	      });
	      return this.field;
	    }
	  }, {
	    key: "lock",
	    value: function lock() {
	      var flag = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;
	      this.disabled = flag;
	      this.field.disabled = !!flag;
	      return this;
	    }
	  }]);
	  return SelectField;
	}(BaseField);

	var _templateObject$5, _templateObject2$3, _templateObject3$2;
	var RadioField = /*#__PURE__*/function (_BaseField) {
	  babelHelpers.inherits(RadioField, _BaseField);

	  function RadioField(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, RadioField);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(RadioField).call(this, options));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "type", 'radio');
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "list", []);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "className", DialogStyle.ProcessOptionMultiple);

	    if ('list' in options) {
	      _this.list = options.list;
	    }

	    return _this;
	  }

	  babelHelpers.createClass(RadioField, [{
	    key: "setValue",
	    value: function setValue(value) {
	      this.value = value;

	      if (this.field) {
	        var optionElements = this.field.querySelectorAll("input[type=radio]");

	        if (optionElements) {
	          for (var k = 0; k < optionElements.length; k++) {
	            optionElements[k].checked = optionElements[k].value === this.value;
	          }
	        }
	      }

	      return this;
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      if (this.field) {
	        var optionElements = this.field.querySelectorAll("input[type=radio]");

	        if (optionElements) {
	          for (var k = 0; k < optionElements.length; k++) {
	            if (optionElements[k].checked) {
	              this.value = optionElements[k].value;
	              break;
	            }
	          }
	        }
	      }

	      return this.value;
	    }
	  }, {
	    key: "isFilled",
	    value: function isFilled() {
	      if (this.field) {
	        var optionElements = this.field.querySelectorAll("input[type=radio]");

	        if (optionElements) {
	          for (var k = 0; k < optionElements.length; k++) {
	            if (optionElements[k].checked) {
	              return true;
	            }
	          }
	        }
	      }

	      return false;
	    }
	  }, {
	    key: "getInput",
	    value: function getInput() {
	      if (this.field && this.disabled !== true) {
	        var optionElement = this.field.querySelector("input[type=radio]");

	        if (optionElement) {
	          return optionElement;
	        }
	      }

	      return null;
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      var _this2 = this;

	      if (!this.field) {
	        this.field = main_core.Tag.render(_templateObject$5 || (_templateObject$5 = babelHelpers.taggedTemplateLiteral(["<div id=\"", "\"></div>"])), this.id);
	      }

	      Object.keys(this.list).forEach(function (itemId) {
	        if (itemId === _this2.value) {
	          _this2.field.appendChild(main_core.Tag.render(_templateObject2$3 || (_templateObject2$3 = babelHelpers.taggedTemplateLiteral(["<label><input type=\"radio\" name=\"", "\" value=\"", "\" checked>", "</label>"])), _this2.name, itemId, _this2.list[itemId]));
	        } else {
	          _this2.field.appendChild(main_core.Tag.render(_templateObject3$2 || (_templateObject3$2 = babelHelpers.taggedTemplateLiteral(["<label><input type=\"radio\" name=\"", "\" value=\"", "\">", "</label>"])), _this2.name, itemId, _this2.list[itemId]));
	        }
	      });
	      return this.field;
	    }
	  }, {
	    key: "lock",
	    value: function lock() {
	      var flag = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;
	      this.disabled = flag;

	      if (this.field) {
	        var optionElements = this.field.querySelectorAll("input[type=radio]");

	        if (optionElements) {
	          for (var k = 0; k < optionElements.length; k++) {
	            optionElements[k].disabled = !!flag;
	          }
	        }
	      }

	      return this;
	    }
	  }]);
	  return RadioField;
	}(BaseField);

	var _templateObject$6, _templateObject2$4, _templateObject3$3, _templateObject4$2;
	/**
	 * @namespace {BX.UI.StepProcessing}
	 */

	var DialogStyle = {
	  ProcessWindow: 'bx-stepprocessing-dialog-process',
	  ProcessPopup: 'bx-stepprocessing-dialog-process-popup',
	  ProcessSummary: 'bx-stepprocessing-dialog-process-summary',
	  ProcessProgressbar: 'bx-stepprocessing-dialog-process-progressbar',
	  ProcessOptions: 'bx-stepprocessing-dialog-process-options',
	  ProcessOptionContainer: 'bx-stepprocessing-dialog-process-option-container',
	  ProcessOptionsTitle: 'bx-stepprocessing-dialog-process-options-title',
	  ProcessOptionsInput: 'bx-stepprocessing-dialog-process-options-input',
	  ProcessOptionsObligatory: 'ui-alert ui-alert-xs ui-alert-warning',
	  ProcessOptionText: 'bx-stepprocessing-dialog-process-option-text',
	  ProcessOptionCheckbox: 'bx-stepprocessing-dialog-process-option-checkbox',
	  ProcessOptionMultiple: 'bx-stepprocessing-dialog-process-option-multiple',
	  ProcessOptionFile: 'bx-stepprocessing-dialog-process-option-file',
	  ProcessOptionSelect: 'bx-stepprocessing-dialog-process-option-select',
	  ButtonStart: 'popup-window-button-accept',
	  ButtonStop: 'popup-window-button-disable',
	  ButtonCancel: 'popup-window-button-link-cancel',
	  ButtonDownload: 'popup-window-button-link-download',
	  ButtonRemove: 'popup-window-button-link-remove'
	};
	var DialogEvent = {
	  Shown: 'BX.UI.StepProcessing.Dialog.Shown',
	  Closed: 'BX.UI.StepProcessing.Dialog.Closed',
	  Start: 'BX.UI.StepProcessing.Dialog.Start',
	  Stop: 'BX.UI.StepProcessing.Dialog.Stop'
	};
	/**
	 * UI of process dialog
	 *
	 * @namespace {BX.UI.StepProcessing}
	 * @event BX.UI.StepProcessing.Dialog.Shown
	 * @event BX.UI.StepProcessing.Dialog.Closed
	 * @event BX.UI.StepProcessing.Dialog.Start
	 * @event BX.UI.StepProcessing.Dialog.Stop
	 */

	var Dialog = /*#__PURE__*/function () {
	  /**
	   * @type {DialogOptions}
	   * @private
	   */

	  /**
	   * @private
	   */

	  /**
	   * @private
	   */

	  /**
	   * @private
	   */
	  function Dialog() {
	    var settings = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, Dialog);
	    babelHelpers.defineProperty(this, "id", '');
	    babelHelpers.defineProperty(this, "_settings", {});
	    babelHelpers.defineProperty(this, "isShown", false);
	    babelHelpers.defineProperty(this, "buttons", {});
	    babelHelpers.defineProperty(this, "fields", {});
	    babelHelpers.defineProperty(this, "_messages", {});
	    babelHelpers.defineProperty(this, "_handlers", {});
	    babelHelpers.defineProperty(this, "isAdminPanel", false);
	    this._settings = settings;
	    this.id = this.getSetting('id', 'ProcessDialog_' + Math.random().toString().substring(2));
	    this._messages = this.getSetting('messages', {});
	    var optionsFields = {};
	    var fields = this.getSetting('optionsFields');

	    if (main_core.Type.isArray(fields)) {
	      fields.forEach(function (option) {
	        if (main_core.Type.isPlainObject(option) && option.hasOwnProperty('name') && option.hasOwnProperty('type') && option.hasOwnProperty('title')) {
	          optionsFields[option.name] = option;
	        }
	      });
	    } else if (main_core.Type.isPlainObject(fields)) {
	      Object.keys(fields).forEach(function (optionName) {
	        var option = fields[optionName];

	        if (main_core.Type.isPlainObject(option) && option.hasOwnProperty('name') && option.hasOwnProperty('type') && option.hasOwnProperty('title')) {
	          optionsFields[option.name] = option;
	        }
	      });
	    }

	    this.setSetting('optionsFields', optionsFields);
	    var optionsFieldsValue = this.getSetting('optionsFieldsValue');

	    if (!optionsFieldsValue) {
	      this.setSetting('optionsFieldsValue', {});
	    }

	    var showButtons = this.getSetting('showButtons');

	    if (!showButtons) {
	      this.setSetting('showButtons', {
	        'start': true,
	        'stop': true,
	        'close': true
	      });
	    }

	    this._handlers = this.getSetting('handlers', {});
	  }

	  babelHelpers.createClass(Dialog, [{
	    key: "destroy",
	    value: function destroy() {
	      if (this.popupWindow) {
	        this.popupWindow.destroy();
	        this.popupWindow = null;
	      }
	    }
	  }, {
	    key: "getId",
	    value: function getId() {
	      return this.id;
	    }
	  }, {
	    key: "getSetting",
	    value: function getSetting(name) {
	      var defaultVal = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	      return this._settings.hasOwnProperty(name) ? this._settings[name] : defaultVal;
	    }
	  }, {
	    key: "setSetting",
	    value: function setSetting(name, value) {
	      this._settings[name] = value;
	      return this;
	    }
	  }, {
	    key: "getMessage",
	    value: function getMessage(name) {
	      return this._messages && this._messages.hasOwnProperty(name) ? this._messages[name] : "";
	    }
	  }, {
	    key: "setMessage",
	    value: function setMessage(name, text) {
	      this._messages[name] = text;
	      return this;
	    } //region Event handlers

	  }, {
	    key: "setHandler",
	    value: function setHandler(type, handler) {
	      if (typeof handler == 'function') {
	        this._handlers[type] = handler;
	      }

	      return this;
	    }
	  }, {
	    key: "callHandler",
	    value: function callHandler(type, args) {
	      if (typeof this._handlers[type] == 'function') {
	        this._handlers[type].apply(this, args);
	      }
	    } //endregion
	    //region Run

	  }, {
	    key: "start",
	    value: function start() {
	      this.callHandler('start');
	      main_core_events.EventEmitter.emit(DialogEvent.Start, new main_core_events.BaseEvent({
	        dialog: this
	      }));
	    }
	  }, {
	    key: "stop",
	    value: function stop() {
	      this.callHandler('stop');
	      main_core_events.EventEmitter.emit(DialogEvent.Stop, new main_core_events.BaseEvent({
	        dialog: this
	      }));
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      if (this.isShown) {
	        return;
	      }

	      var optionElement = document.querySelector('#bx-admin-prefix');

	      if (optionElement) {
	        this.isAdminPanel = true;
	      }

	      this.progressBar = new BX.UI.ProgressBar({
	        statusType: BX.UI.ProgressBar.Status.COUNTER,
	        size: this.isAdminPanel ? BX.UI.ProgressBar.Size.LARGE : BX.UI.ProgressBar.Size.MEDIUM,
	        fill: this.isAdminPanel,
	        column: !this.isAdminPanel
	      });
	      this.error = new ui_alerts.Alert({
	        color: ui_alerts.AlertColor.DANGER,
	        icon: ui_alerts.AlertIcon.DANGER,
	        size: ui_alerts.AlertSize.SMALL
	      });
	      this.warning = new ui_alerts.Alert({
	        color: ui_alerts.AlertColor.WARNING,
	        icon: ui_alerts.AlertIcon.WARNING,
	        size: ui_alerts.AlertSize.SMALL
	      });
	      this.popupWindow = main_popup.PopupManager.create({
	        id: this.getId(),
	        cacheable: false,
	        titleBar: this.getMessage("title"),
	        autoHide: false,
	        closeByEsc: false,
	        closeIcon: true,
	        content: this._prepareDialogContent(),
	        draggable: true,
	        buttons: this._prepareDialogButtons(),
	        className: DialogStyle.ProcessWindow,
	        bindOptions: {
	          forceBindPosition: false
	        },
	        events: {
	          onClose: BX.delegate(this.onDialogClose, this)
	        },
	        overlay: true,
	        resizable: true,
	        minWidth: Number.parseInt(this.getSetting('minWidth', 500)),
	        maxWidth: Number.parseInt(this.getSetting('maxWidth', 1000))
	      });

	      if (!this.popupWindow.isShown()) {
	        this.popupWindow.show();
	      }

	      this.isShown = this.popupWindow.isShown();

	      if (this.isShown) {
	        this.callHandler('dialogShown');
	        main_core_events.EventEmitter.emit(DialogEvent.Shown, new main_core_events.BaseEvent({
	          dialog: this
	        }));
	      }

	      return this;
	    }
	  }, {
	    key: "close",
	    value: function close() {
	      if (!this.isShown) {
	        return;
	      }

	      if (this.popupWindow) {
	        this.popupWindow.close();
	      }

	      this.isShown = false;
	      this.callHandler('dialogClosed');
	      main_core_events.EventEmitter.emit(DialogEvent.Closed, new main_core_events.BaseEvent({
	        dialog: this
	      }));
	      return this;
	    } // endregion
	    //region Dialog

	    /**
	     * @private
	     */

	  }, {
	    key: "_prepareDialogContent",
	    value: function _prepareDialogContent() {
	      var _this = this;

	      this.summaryBlock = main_core.Tag.render(_templateObject$6 || (_templateObject$6 = babelHelpers.taggedTemplateLiteral(["<div class=\"", "\">", "</div>"])), DialogStyle.ProcessSummary, this.getMessage('summary'));
	      this.errorBlock = this.error.getContainer();
	      this.warningBlock = this.warning.getContainer();
	      this.errorBlock.style.display = 'none';
	      this.warningBlock.style.display = 'none';

	      if (this.progressBar) {
	        this.progressBarBlock = main_core.Tag.render(_templateObject2$4 || (_templateObject2$4 = babelHelpers.taggedTemplateLiteral(["<div class=\"", "\" style=\"display:none\"></div>"])), DialogStyle.ProcessProgressbar);
	        this.progressBarBlock.appendChild(this.progressBar.getContainer());
	      }

	      if (!this.optionsFieldsBlock) {
	        this.optionsFieldsBlock = main_core.Tag.render(_templateObject3$3 || (_templateObject3$3 = babelHelpers.taggedTemplateLiteral(["<div class=\"", "\" style=\"display:none\"></div>"])), DialogStyle.ProcessOptions);
	      } else {
	        main_core.Dom.clean(this.optionsFieldsBlock);
	      }

	      var optionsFields = this.getSetting('optionsFields', {});
	      var optionsFieldsValue = this.getSetting('optionsFieldsValue', {});
	      Object.keys(optionsFields).forEach(function (optionName) {
	        var optionValue = optionsFieldsValue[optionName] ? optionsFieldsValue[optionName] : null;

	        var optionBlock = _this._renderOption(optionsFields[optionName], optionValue);

	        if (optionBlock instanceof HTMLElement) {
	          _this.optionsFieldsBlock.appendChild(optionBlock);

	          _this.optionsFieldsBlock.style.display = 'block';
	        }
	      });
	      var dialogContent = main_core.Tag.render(_templateObject4$2 || (_templateObject4$2 = babelHelpers.taggedTemplateLiteral(["<div class=\"", "\"></div>"])), DialogStyle.ProcessPopup);
	      dialogContent.appendChild(this.summaryBlock);
	      dialogContent.appendChild(this.warningBlock);
	      dialogContent.appendChild(this.errorBlock);

	      if (this.progressBarBlock) {
	        dialogContent.appendChild(this.progressBarBlock);
	      }

	      if (this.optionsFieldsBlock) {
	        dialogContent.appendChild(this.optionsFieldsBlock);
	      }

	      return dialogContent;
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "_renderOption",
	    value: function _renderOption(option) {
	      var optionValue = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	      option.id = this.id + '_opt_' + option.name;

	      switch (option.type) {
	        case 'text':
	          this.fields[option.name] = new TextField(option);
	          break;

	        case 'file':
	          this.fields[option.name] = new FileField(option);
	          break;

	        case 'checkbox':
	          this.fields[option.name] = new CheckboxField(option);
	          break;

	        case 'select':
	          this.fields[option.name] = new SelectField(option);
	          break;

	        case 'radio':
	          this.fields[option.name] = new RadioField(option);
	          break;
	      }

	      if (optionValue !== null) {
	        this.fields[option.name].setValue(optionValue);
	      }

	      var optionBlock = this.fields[option.name].getContainer();
	      return optionBlock;
	    } //endregion
	    //region Events

	  }, {
	    key: "onDialogClose",
	    value: function onDialogClose() {
	      if (this.popupWindow) {
	        this.popupWindow.destroy();
	        this.popupWindow = null;
	      }

	      this.buttons = {};
	      this.fields = {};
	      this.summaryBlock = null;
	      this.isShown = false;
	      this.callHandler('dialogClosed');
	      main_core_events.EventEmitter.emit(DialogEvent.Closed, new main_core_events.BaseEvent({
	        dialog: this
	      }));
	    }
	  }, {
	    key: "handleStartButtonClick",
	    value: function handleStartButtonClick() {
	      var btn = this.getButton('start');

	      if (btn && btn.isDisabled()) {
	        return;
	      }

	      this.start();
	    }
	  }, {
	    key: "handleStopButtonClick",
	    value: function handleStopButtonClick() {
	      var btn = this.getButton('stop');

	      if (btn && btn.isDisabled()) {
	        return;
	      }

	      this.stop();
	    }
	  }, {
	    key: "handleCloseButtonClick",
	    value: function handleCloseButtonClick() {
	      this.popupWindow.close();
	    } //endregion
	    //region Buttons

	    /**
	     * @private
	     */

	  }, {
	    key: "_prepareDialogButtons",
	    value: function _prepareDialogButtons() {
	      var showButtons = this.getSetting('showButtons');
	      var ret = [];
	      this.buttons = {};

	      if (showButtons.start) {
	        var startButtonText = this.getMessage('startButton');
	        this.buttons.start = new ui_buttons.Button({
	          text: startButtonText || 'Start',
	          color: ui_buttons.Button.Color.SUCCESS,
	          icon: ui_buttons.Button.Icon.START,
	          //className: DialogStyle.ButtonStart,
	          events: {
	            click: BX.delegate(this.handleStartButtonClick, this)
	          }
	        });
	        ret.push(this.buttons.start);
	      }

	      if (showButtons.stop) {
	        var stopButtonText = this.getMessage('stopButton');
	        this.buttons.stop = new ui_buttons.Button({
	          text: stopButtonText || 'Stop',
	          color: ui_buttons.Button.Color.LIGHT_BORDER,
	          icon: ui_buttons.Button.Icon.STOP,
	          //className: DialogStyle.ButtonStop,
	          events: {
	            click: BX.delegate(this.handleStopButtonClick, this)
	          }
	        });
	        this.buttons.stop.setDisabled();
	        ret.push(this.buttons.stop);
	      }

	      if (showButtons.close) {
	        var closeButtonText = this.getMessage('closeButton');
	        this.buttons.close = new ui_buttons.CancelButton({
	          text: closeButtonText || 'Close',
	          color: ui_buttons.Button.Color.LIGHT_BORDER,
	          tag: ui_buttons.Button.Tag.SPAN,
	          events: {
	            click: BX.delegate(this.handleCloseButtonClick, this)
	          }
	        });
	        ret.push(this.buttons.close);
	      }

	      return ret;
	    }
	    /**
	     * @param {String} downloadLink
	     * @param {String} fileName
	     * @param {function} purgeHandler
	     * @return self
	     */

	  }, {
	    key: "setDownloadButtons",
	    value: function setDownloadButtons(downloadLink, fileName, purgeHandler) {
	      var ret = [];

	      if (downloadLink) {
	        var downloadButtonText = this.getMessage("downloadButton");
	        downloadButtonText = downloadButtonText !== "" ? downloadButtonText : "Download file";
	        var downloadButton = new ui_buttons.Button({
	          text: downloadButtonText,
	          color: ui_buttons.Button.Color.SUCCESS,
	          icon: ui_buttons.Button.Icon.DOWNLOAD,
	          className: DialogStyle.ButtonDownload,
	          tag: ui_buttons.Button.Tag.LINK,
	          link: downloadLink,
	          props: {
	            //href: downloadLink,
	            download: fileName
	          }
	        });
	        ret.push(downloadButton);
	      }

	      if (typeof purgeHandler == 'function') {
	        var clearButtonText = this.getMessage("clearButton");
	        clearButtonText = clearButtonText !== "" ? clearButtonText : "Delete file";
	        var clearButton = new ui_buttons.Button({
	          text: clearButtonText,
	          color: ui_buttons.Button.Color.LIGHT_BORDER,
	          icon: ui_buttons.Button.Icon.REMOVE,
	          className: DialogStyle.ButtonRemove,
	          events: {
	            click: purgeHandler
	          }
	        });
	        ret.push(clearButton);
	      }

	      if (this.buttons.close) {
	        ret.push(this.buttons.close);
	      }

	      if (ret.length > 0 && this.popupWindow) {
	        this.popupWindow.setButtons(ret);
	      }

	      return this;
	    }
	  }, {
	    key: "resetButtons",
	    value: function resetButtons() {
	      var showButtons = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {
	        'start': true,
	        'stop': true,
	        'close': true
	      };

	      this._prepareDialogButtons();

	      showButtons = showButtons || this.getSetting('showButtons');
	      var ret = [];

	      if (showButtons.start) {
	        ret.push(this.buttons.start);
	      }

	      if (showButtons.stop) {
	        ret.push(this.buttons.stop);
	      }

	      if (showButtons.close) {
	        ret.push(this.buttons.close);
	      }

	      if (ret.length > 0 && this.popupWindow) {
	        this.popupWindow.setButtons(ret);
	      }

	      return this;
	    }
	  }, {
	    key: "getButton",
	    value: function getButton(bid) {
	      var _this$buttons$bid;

	      return (_this$buttons$bid = this.buttons[bid]) !== null && _this$buttons$bid !== void 0 ? _this$buttons$bid : null;
	    }
	  }, {
	    key: "lockButton",
	    value: function lockButton(bid, lock, wait) {
	      var btn = this.getButton(bid);

	      if (btn) {
	        btn.setDisabled(lock);

	        if (main_core.Type.isBoolean(wait)) {
	          btn.setWaiting(wait);
	        }
	      }

	      return this;
	    }
	  }, {
	    key: "showButton",
	    value: function showButton(bid, show) {
	      var btn = this.getButton(bid);

	      if (btn) {
	        btn.getContainer().style.display = !!show ? '' : 'none';
	      }

	      if (bid === 'close') {
	        if (this.popupWindow && this.popupWindow.closeIcon) {
	          this.popupWindow.closeIcon.style.display = !!show ? '' : 'none';
	        }
	      }

	      return this;
	    } // endregion
	    //region Summary

	  }, {
	    key: "setSummary",
	    value: function setSummary(content) {
	      var isHtml = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

	      if (this.optionsFieldsBlock) {
	        BX.clean(this.optionsFieldsBlock);
	        this.optionsFieldsBlock.style.display = 'none';
	      }

	      if (main_core.Type.isStringFilled(content)) {
	        if (this.summaryBlock) {
	          if (!!isHtml) this.summaryBlock.innerHTML = content;else this.summaryBlock.innerHTML = BX.util.htmlspecialchars(content);
	          this.summaryBlock.style.display = "block";
	        }
	      } else {
	        this.summaryBlock.innerHTML = "";
	        this.summaryBlock.style.display = "none";
	      }

	      return this;
	    } //endregion
	    //region Errors

	  }, {
	    key: "setErrors",
	    value: function setErrors(errors) {
	      var _this2 = this;

	      var isHtml = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
	      errors.forEach(function (err) {
	        return _this2.setError(err, isHtml);
	      });
	      return this;
	    }
	  }, {
	    key: "setError",
	    value: function setError(content, isHtml) {
	      if (main_core.Type.isStringFilled(content)) {
	        this.setSummary('');

	        if (this.progressBar) {
	          this.progressBar.setColor(BX.UI.ProgressBar.Color.DANGER);
	        }

	        if (!!isHtml) {
	          this.error.setText(content);
	        } else {
	          this.error.setText(BX.util.htmlspecialchars(content));
	        }

	        this.errorBlock.style.display = "flex";
	      }

	      return this;
	    }
	  }, {
	    key: "clearErrors",
	    value: function clearErrors() {
	      if (this.error) {
	        this.error.setText('');
	      }

	      if (this.errorBlock) {
	        this.errorBlock.style.display = 'none';
	      }

	      return this;
	    }
	  }, {
	    key: "setWarning",
	    value: function setWarning(err) {
	      var isHtml = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

	      if (main_core.Type.isStringFilled(err)) {
	        if (!!isHtml) {
	          this.warning.setText(err);
	        } else {
	          this.warning.setText(BX.util.htmlspecialchars(err));
	        }

	        this.warningBlock.style.display = "flex";
	      }

	      return this;
	    }
	  }, {
	    key: "clearWarnings",
	    value: function clearWarnings() {
	      if (this.warning) {
	        this.warning.setText("");
	      }

	      if (this.warningBlock) {
	        this.warningBlock.style.display = 'none';
	      }

	      return this;
	    } //endregion
	    //region Progressbar

	  }, {
	    key: "setProgressBar",
	    value: function setProgressBar(totalItems, processedItems, textBefore) {
	      if (this.progressBar) {
	        if (main_core.Type.isNumber(processedItems) && main_core.Type.isNumber(totalItems) && totalItems > 0) {
	          BX.show(this.progressBarBlock);
	          this.progressBar.setColor(BX.UI.ProgressBar.Color.PRIMARY);
	          this.progressBar.setMaxValue(totalItems);
	          textBefore = textBefore || "";
	          this.progressBar.setTextBefore(textBefore);
	          this.progressBar.update(processedItems);
	        } else {
	          this.hideProgressBar();
	        }
	      }

	      return this;
	    }
	  }, {
	    key: "hideProgressBar",
	    value: function hideProgressBar() {
	      if (this.progressBar) {
	        BX.hide(this.progressBarBlock);
	      }

	      return this;
	    } //endregion
	    //region Initial options

	  }, {
	    key: "getOptionField",
	    value: function getOptionField(name) {
	      if (main_core.Type.isString(name)) {
	        if (this.fields[name] && this.fields[name] instanceof BaseField) {
	          return this.fields[name];
	        }
	      }

	      return null;
	    }
	  }, {
	    key: "getOptionFieldValues",
	    value: function getOptionFieldValues() {
	      var _this3 = this;

	      var initialOptions = {};

	      if (this.optionsFieldsBlock) {
	        Object.keys(this.fields).forEach(function (optionName) {
	          var field = _this3.getOptionField(optionName);

	          var val = field.getValue();

	          if (field.type === 'checkbox' && main_core.Type.isBoolean(val)) {
	            initialOptions[optionName] = val ? 'Y' : 'N';
	          } else if (main_core.Type.isArray(val)) {
	            if (main_core.Type.isArrayFilled(val)) {
	              initialOptions[optionName] = val;
	            }
	          } else if (val) {
	            initialOptions[optionName] = val;
	          }
	        });
	      }

	      return initialOptions;
	    }
	  }, {
	    key: "checkOptionFields",
	    value: function checkOptionFields() {
	      var _this4 = this;

	      var checked = true;

	      if (this.optionsFieldsBlock) {
	        Object.keys(this.fields).forEach(function (optionName) {
	          var field = _this4.getOptionField(optionName);

	          if (field.obligatory) {
	            if (!field.isFilled()) {
	              field.showWarning();
	              checked = false;
	            } else {
	              field.hideWarning();
	            }
	          }
	        });
	      }

	      return checked;
	    }
	  }, {
	    key: "lockOptionFields",
	    value: function lockOptionFields() {
	      var _this5 = this;

	      var flag = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;

	      if (this.optionsFieldsBlock) {
	        Object.keys(this.fields).forEach(function (optionName) {
	          var field = _this5.getOptionField(optionName);

	          if (field) {
	            field.lock(flag);
	          }
	        });
	      }

	      return this;
	    } //endregion

	  }]);
	  return Dialog;
	}();

	/**
	 * @namespace {BX.UI.StepProcessing}
	 */

	var ProcessEvent = {
	  StateChanged: 'BX.UI.StepProcessing.StateChanged',
	  BeforeRequest: 'BX.UI.StepProcessing.BeforeRequest'
	};
	/**
	 * @namespace {BX.UI.StepProcessing}
	 */

	var ProcessCallback = {
	  StateChanged: 'StateChanged',
	  RequestStart: 'RequestStart',
	  RequestStop: 'RequestStop',
	  RequestFinalize: 'RequestFinalize',
	  StepCompleted: 'StepCompleted'
	};
	var ProcessDefaultLabels = {
	  AuthError: main_core.Loc.getMessage('UI_STEP_PROCESSING_AUTH_ERROR'),
	  RequestError: main_core.Loc.getMessage('UI_STEP_PROCESSING_REQUEST_ERR'),
	  DialogStartButton: main_core.Loc.getMessage('UI_STEP_PROCESSING_BTN_START'),
	  DialogStopButton: main_core.Loc.getMessage('UI_STEP_PROCESSING_BTN_STOP'),
	  DialogCloseButton: main_core.Loc.getMessage('UI_STEP_PROCESSING_BTN_CLOSE'),
	  RequestCanceling: main_core.Loc.getMessage('UI_STEP_PROCESSING_CANCELING'),
	  RequestCanceled: main_core.Loc.getMessage('UI_STEP_PROCESSING_CANCELED'),
	  RequestCompleted: main_core.Loc.getMessage('UI_STEP_PROCESSING_COMPLETED'),
	  DialogExportDownloadButton: main_core.Loc.getMessage('UI_STEP_PROCESSING_FILE_DOWNLOAD'),
	  DialogExportClearButton: main_core.Loc.getMessage('UI_STEP_PROCESSING_FILE_DELETE'),
	  WaitingResponse: main_core.Loc.getMessage('UI_STEP_PROCESSING_WAITING')
	};
	var EndpointType = {
	  Controller: 'controller',
	  Component: 'component'
	};
	/**
	 * Long running process.
	 *
	 * @namespace {BX.UI.StepProcessing}
	 * @event BX.UI.StepProcessing.StateChanged
	 * @event BX.UI.StepProcessing.BeforeRequest
	 */

	var Process = /*#__PURE__*/function () {
	  // Ajax endpoint
	  // Queue
	  // Events
	  // Messages
	  function Process(options) {
	    babelHelpers.classCallCheck(this, Process);
	    babelHelpers.defineProperty(this, "action", '');
	    babelHelpers.defineProperty(this, "method", 'POST');
	    babelHelpers.defineProperty(this, "params", {});
	    babelHelpers.defineProperty(this, "isRequestRunning", false);
	    babelHelpers.defineProperty(this, "queue", []);
	    babelHelpers.defineProperty(this, "currentStep", -1);
	    babelHelpers.defineProperty(this, "state", ProcessState.intermediate);
	    babelHelpers.defineProperty(this, "initialOptionValues", {});
	    babelHelpers.defineProperty(this, "optionsFields", {});
	    babelHelpers.defineProperty(this, "handlers", {});
	    babelHelpers.defineProperty(this, "messages", new Map());
	    this.options = main_core.Type.isPlainObject(options) ? options : {};
	    this.id = this.getOption('id', '');

	    if (!main_core.Type.isStringFilled(this.id)) {
	      this.id = 'Process_' + main_core.Text.getRandom().toLowerCase();
	    }

	    var controller = this.getOption('controller', '');
	    var component = this.getOption('component', '');

	    if (main_core.Type.isStringFilled(controller)) {
	      this.controller = controller;
	      this.controllerDefault = controller;
	      this.endpointType = EndpointType.Controller;
	    } else if (main_core.Type.isStringFilled(component)) {
	      this.component = component;
	      this.endpointType = EndpointType.Component;
	      this.componentMode = this.getOption('componentMode', 'class');
	    }

	    if (!main_core.Type.isStringFilled(this.controller)) {
	      if (!main_core.Type.isStringFilled(this.component)) {
	        throw new TypeError("BX.UI.StepProcessing: There no any ajax endpoint was defined.");
	      }
	    }

	    this.setQueue(this.getOption('queue', [])).setParams(this.getOption('params', {})).setOptionsFields(this.getOption('optionsFields', {})).setHandlers(this.getOption('handlers', {})).setMessages(ProcessDefaultLabels).setMessages(this.getOption('messages', {}));
	  }

	  babelHelpers.createClass(Process, [{
	    key: "destroy",
	    value: function destroy() {
	      if (this.dialog instanceof Dialog) {
	        this.dialog.close().destroy();
	        this.dialog = null;
	      }

	      this._closeConnection();
	    } //region Run

	  }, {
	    key: "start",
	    value: function start() {
	      var startStep = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 1;

	      this._refreshHash();

	      startStep = startStep || 1;

	      if (this.state === ProcessState.intermediate || this.state === ProcessState.stopped || this.state === ProcessState.completed) {
	        if (!this.getDialog().checkOptionFields()) {
	          return;
	        }

	        this.getDialog().clearErrors().clearWarnings();
	        this.networkErrorCount = 0;

	        if (this.getQueueLength() > 0) {
	          this.currentStep = 0;

	          if (startStep > 1) {
	            this.currentStep = startStep - 1;
	          }

	          if (this.endpointType === EndpointType.Controller) {
	            if (main_core.Type.isStringFilled(this.queue[this.currentStep].controller)) {
	              this.setController(this.queue[this.currentStep].controller);
	            }
	          }

	          if (!main_core.Type.isStringFilled(this.queue[this.currentStep].action)) {
	            throw new Error("BX.UI.StepProcessing: Could not find controller action at the queue position.");
	          }

	          this.setAction(this.queue[this.currentStep].action);
	          this.startRequest();

	          if (this.queue[this.currentStep].title) {
	            this.getDialog().setSummary(this.queue[this.currentStep].title);
	          } else {
	            this.getDialog().setSummary(this.getMessage('WaitingResponse'));
	          }
	        } else {
	          this.startRequest();
	        }
	      }

	      return this;
	    }
	  }, {
	    key: "stop",
	    value: function stop() {
	      if (this.state === ProcessState.running) {
	        this.stopRequest();
	        this.currentStep = -1;
	      }

	      return this;
	    } //endregion
	    //region Request

	  }, {
	    key: "startRequest",
	    value: function startRequest() {
	      var _this = this;

	      if (this.isRequestRunning || this.state === ProcessState.canceling) {
	        return this.ajaxPromise;
	      }

	      this.isRequestRunning = true;
	      this.ajaxPromise = null;
	      var actionData = new FormData();

	      var appendData = function appendData(data, prefix) {
	        if (main_core.Type.isPlainObject(data)) {
	          Object.keys(data).forEach(function (name) {
	            var id = name;

	            if (prefix) {
	              id = prefix + '[' + name + ']';
	            }

	            if (main_core.Type.isArray(data[name]) || main_core.Type.isPlainObject(data[name])) {
	              appendData(data[name], id);
	            } else {
	              actionData.append(id, data[name]);
	            }
	          });
	        } else if (main_core.Type.isArray(data)) {
	          data.forEach(function (element) {
	            return actionData.append(prefix + '[]', element);
	          });
	        }
	      };

	      appendData(this.params);

	      if (this.queue[this.currentStep].params) {
	        appendData(this.queue[this.currentStep].params);
	      }

	      var initialOptions = this.getDialog().getOptionFieldValues();

	      if (BX.type.isNotEmptyObject(initialOptions)) {
	        appendData(initialOptions);
	        this.initialOptionValues = initialOptions;
	        this.storeOptionFieldValues(initialOptions);
	      } else {
	        Object.keys(this.initialOptionValues).forEach(function (name) {
	          // don't repeat file uploading
	          if (_this.initialOptionValues[name] instanceof File) {
	            delete _this.initialOptionValues[name];
	          }
	        });
	        appendData(this.initialOptionValues);
	      }

	      this.setState(ProcessState.running);

	      if (this.hasActionHandler(ProcessCallback.RequestStart)) {
	        this.callActionHandler(ProcessCallback.RequestStart, [actionData]);
	      } else if (this.hasHandler(ProcessCallback.RequestStart)) {
	        this.callHandler(ProcessCallback.RequestStart, [actionData]);
	      }

	      main_core_events.EventEmitter.emit(ProcessEvent.BeforeRequest, new main_core_events.BaseEvent({
	        data: {
	          process: this,
	          actionData: actionData
	        }
	      }));
	      var params = {
	        data: actionData,
	        method: this.method,
	        onrequeststart: this._onRequestStart.bind(this)
	      };

	      if (this.endpointType === EndpointType.Controller) {
	        this.ajaxPromise = BX.ajax.runAction(this.controller + '.' + this.getAction(), params).then(this._onRequestSuccess.bind(this), this._onRequestFailure.bind(this));
	      } else if (this.endpointType === EndpointType.Component) {
	        params.data.mode = this.componentMode;

	        if ('signedParameters' in params.data) {
	          params.signedParameters = params.data.signedParameters;
	          delete params.data.signedParameters;
	        }

	        this.ajaxPromise = BX.ajax.runComponentAction(this.component, this.getAction(), params).then(this._onRequestSuccess.bind(this), this._onRequestFailure.bind(this));
	      }

	      return this.ajaxPromise;
	    }
	  }, {
	    key: "stopRequest",
	    value: function stopRequest() {
	      if (this.state === ProcessState.canceling) {
	        return this.ajaxPromise;
	      }

	      this.setState(ProcessState.canceling);

	      this._closeConnection();

	      var actionData = BX.clone(this.params);
	      actionData.cancelingAction = this.getAction();
	      this.getDialog().setSummary(this.getMessage("RequestCanceling"));
	      var proceedAction = true;

	      if (this.hasActionHandler(ProcessCallback.RequestStop)) {
	        proceedAction = false;
	        this.callActionHandler(ProcessCallback.RequestStop, [actionData]);
	      } else if (this.hasHandler(ProcessCallback.RequestStop)) {
	        proceedAction = false;
	        this.callHandler(ProcessCallback.RequestStop, [actionData]);
	      }

	      main_core_events.EventEmitter.emit(ProcessEvent.BeforeRequest, new main_core_events.BaseEvent({
	        data: {
	          process: this,
	          actionData: actionData
	        }
	      }));
	      this.ajaxPromise = null;

	      if (proceedAction) {
	        var params = {
	          data: actionData,
	          method: this.method,
	          onrequeststart: this._onRequestStart.bind(this)
	        };

	        if (this.endpointType === EndpointType.Controller) {
	          this.setController(this.controllerDefault);
	          this.ajaxPromise = BX.ajax.runAction(this.controller + '.cancel', params).then(this._onRequestSuccess.bind(this), this._onRequestFailure.bind(this));
	        } else if (this.endpointType === EndpointType.Component) {
	          params.data.mode = this.componentMode;

	          if ('signedParameters' in params.data) {
	            params.signedParameters = params.data.signedParameters;
	            delete params.data.signedParameters;
	          }

	          this.ajaxPromise = BX.ajax.runComponentAction(this.component, 'cancel', params).then(this._onRequestSuccess.bind(this), this._onRequestFailure.bind(this));
	        }
	      }

	      return this.ajaxPromise;
	    }
	  }, {
	    key: "finalizeRequest",
	    value: function finalizeRequest() {
	      if (this.state === ProcessState.canceling) {
	        return this.ajaxPromise;
	      }

	      var actionData = BX.clone(this.params);
	      var proceedAction = true;

	      if (this.hasActionHandler(ProcessCallback.RequestFinalize)) {
	        proceedAction = false;
	        this.callActionHandler(ProcessCallback.RequestFinalize, [actionData]);
	      } else if (this.hasHandler(ProcessCallback.RequestFinalize)) {
	        proceedAction = false;
	        this.callHandler(ProcessCallback.RequestFinalize, [actionData]);
	      }

	      main_core_events.EventEmitter.emit(ProcessEvent.BeforeRequest, new main_core_events.BaseEvent({
	        data: {
	          process: this,
	          actionData: actionData
	        }
	      }));
	      this.ajaxPromise = null;

	      if (proceedAction) {
	        var params = {
	          data: actionData,
	          method: this.method,
	          onrequeststart: this._onRequestStart.bind(this)
	        };

	        if (this.endpointType === EndpointType.Controller) {
	          this.setController(this.controllerDefault);
	          this.ajaxPromise = BX.ajax.runAction(this.controller + '.finalize', params);
	        } else if (this.endpointType === EndpointType.Component) {
	          params.data.mode = this.componentMode;

	          if ('signedParameters' in params.data) {
	            params.signedParameters = params.data.signedParameters;
	            delete params.data.signedParameters;
	          }

	          this.ajaxPromise = BX.ajax.runComponentAction(this.component, 'finalize', params);
	        }
	      }

	      return this.ajaxPromise;
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "_refreshHash",
	    value: function _refreshHash() {
	      this.hash = this.id + Date.now();
	      this.setParam("PROCESS_TOKEN", this.hash);
	      return this;
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "_onRequestSuccess",
	    value: function _onRequestSuccess(response) {
	      this.isRequestRunning = false;
	      this.xhr = null;
	      this.ajaxPromise = null;

	      if (!response) {
	        this.getDialog().setError(this.getMessage('RequestError'));
	        this.setState(ProcessState.error);
	        return;
	      }

	      if (main_core.Type.isArrayFilled(response.errors)) {
	        var errors = response.errors.slice(-10);
	        var errMessages = [];
	        errors.forEach(function (err) {
	          return errMessages.push(err.message);
	        });
	        this.getDialog().setErrors(errMessages, true);
	        this.setState(ProcessState.error);
	        return;
	      }

	      this.networkErrorCount = 0;
	      var result = response.data;
	      var status = main_core.Type.isStringFilled(result.STATUS) ? result.STATUS : "";
	      var summary = "";

	      if (main_core.Type.isStringFilled(result.SUMMARY)) {
	        summary = result.SUMMARY;
	      } else if (main_core.Type.isStringFilled(result.SUMMARY_HTML)) {
	        summary = result.SUMMARY_HTML;
	      }

	      var processedItems = main_core.Type.isNumber(result.PROCESSED_ITEMS) ? result.PROCESSED_ITEMS : 0;
	      var totalItems = main_core.Type.isNumber(result.TOTAL_ITEMS) ? result.TOTAL_ITEMS : 0;
	      var finalize = !!result.FINALIZE;

	      if (this.hasActionHandler(ProcessCallback.StepCompleted)) {
	        this.callActionHandler(ProcessCallback.StepCompleted, [status, result]);
	      }

	      if (main_core.Type.isStringFilled(result.WARNING)) {
	        this.getDialog().setWarning(result.WARNING);
	      }

	      if (status === ProcessResultStatus.progress || status === ProcessResultStatus.completed) {
	        if (totalItems > 0) {
	          if (this.queue[this.currentStep].progressBarTitle) {
	            this.getDialog().setProgressBar(totalItems, processedItems, this.queue[this.currentStep].progressBarTitle);
	          } else {
	            this.getDialog().setProgressBar(totalItems, processedItems);
	          }
	        } else {
	          this.getDialog().hideProgressBar();
	        }
	      }

	      if (status === ProcessResultStatus.progress) {
	        if (summary !== "") {
	          this.getDialog().setSummary(summary, true);
	        }

	        if (this.state === ProcessState.canceling) {
	          this.setState(ProcessState.stopped);
	        } else {
	          if (this.endpointType === EndpointType.Controller) {
	            var nextController = main_core.Type.isStringFilled(result.NEXT_CONTROLLER) ? result.NEXT_CONTROLLER : "";

	            if (nextController !== "") {
	              this.setController(nextController);
	            } else if (main_core.Type.isStringFilled(this.queue[this.currentStep].controller)) {
	              this.setController(this.queue[this.currentStep].controller);
	            } else {
	              this.setController(this.controllerDefault);
	            }
	          }

	          var nextAction = main_core.Type.isStringFilled(result.NEXT_ACTION) ? result.NEXT_ACTION : "";

	          if (nextAction !== "") {
	            this.setAction(nextAction);
	          }

	          setTimeout(BX.delegate(this.startRequest, this), 100);
	        }

	        return;
	      }

	      if (this.state === ProcessState.canceling) {
	        this.getDialog().setSummary(this.getMessage("RequestCanceled"));
	        this.setState(ProcessState.completed);
	      } else if (status === ProcessResultStatus.completed) {
	        if (this.getQueueLength() > 0 && this.currentStep + 1 < this.getQueueLength()) {
	          // next
	          this.currentStep++;

	          if (this.endpointType === EndpointType.Controller) {
	            if (main_core.Type.isStringFilled(this.queue[this.currentStep].controller)) {
	              this.setController(this.queue[this.currentStep].controller);
	            } else {
	              this.setController(this.controllerDefault);
	            }
	          }

	          if (!main_core.Type.isStringFilled(this.queue[this.currentStep].action)) {
	            throw new Error("BX.UI.StepProcessing: Could not find controller action at the queue position.");
	          }

	          if ('finalize' in this.queue[this.currentStep]) {
	            finalize = true;
	            this.setAction(this.queue[this.currentStep].action);
	          } else {
	            this.setAction(this.queue[this.currentStep].action);
	            this.getDialog().setSummary(this.queue[this.currentStep].title);
	            setTimeout(BX.delegate(this.startRequest, this), 100);
	            return;
	          }
	        }

	        if (summary !== "") {
	          this.getDialog().setSummary(summary, true);
	        } else {
	          this.getDialog().setSummary(this.getMessage("RequestCompleted"));
	        }

	        if (main_core.Type.isStringFilled(result.DOWNLOAD_LINK)) {
	          if (main_core.Type.isStringFilled(result.DOWNLOAD_LINK_NAME)) {
	            this.getDialog().setMessage('downloadButton', result.DOWNLOAD_LINK_NAME);
	          }

	          if (main_core.Type.isStringFilled(result.CLEAR_LINK_NAME)) {
	            this.getDialog().setMessage('clearButton', result.CLEAR_LINK_NAME);
	          }

	          this.getDialog().setDownloadButtons(result.DOWNLOAD_LINK, result.FILE_NAME, BX.delegate(function () {
	            this.getDialog().resetButtons({
	              stop: true,
	              close: true
	            });
	            this.callAction('clear'); //.then

	            setTimeout(BX.delegate(function () {
	              this.getDialog().resetButtons({
	                close: true
	              });
	            }, this), 1000);
	          }, this));
	        }

	        this.setState(ProcessState.completed, result);

	        if (finalize) {
	          setTimeout(BX.delegate(this.finalizeRequest, this), 100);
	        }
	      } else {
	        this.getDialog().setSummary("").setError(this.getMessage("RequestError"));
	        this.setState(ProcessState.error);
	      }
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "_onRequestFailure",
	    value: function _onRequestFailure(response) {
	      var _this2 = this;

	      /*
	      // check if it's manual aborting
	      if (this.state === ProcessState.canceling)
	      {
	      	return;
	      }
	      */
	      this.isRequestRunning = false;
	      this.ajaxPromise = null; // check non auth

	      if (main_core.Type.isPlainObject(response) && 'data' in response && main_core.Type.isPlainObject(response.data) && 'ajaxRejectData' in response.data && main_core.Type.isPlainObject(response.data.ajaxRejectData) && 'reason' in response.data.ajaxRejectData && response.data.ajaxRejectData.reason === 'status' && 'data' in response.data.ajaxRejectData && response.data.ajaxRejectData.data === 401) {
	        this.getDialog().setError(this.getMessage('AuthError'));
	      } // check errors
	      else if (main_core.Type.isPlainObject(response) && 'errors' in response && main_core.Type.isArrayFilled(response.errors)) {
	        var abortingState = false;
	        var networkError = false;
	        response.errors.forEach(function (err) {
	          if (err.code === 'NETWORK_ERROR') {
	            if (_this2.state === ProcessState.canceling) {
	              abortingState = true;
	            } else {
	              networkError = true;
	            }
	          }
	        }); // ignoring error of manual aborting

	        if (abortingState) {
	          return;
	        }

	        if (networkError) {
	          this.networkErrorCount++; // Let's give it more chance to complete

	          if (this.networkErrorCount <= 2) {
	            setTimeout(BX.delegate(this.startRequest, this), 15000);
	            return;
	          }
	        }

	        var errors = response.errors.slice(-10);
	        var errMessages = [];
	        errors.forEach(function (err) {
	          if (err.code === 'NETWORK_ERROR') {
	            errMessages.push(_this2.getMessage('RequestError'));
	          } else {
	            errMessages.push(err.message);
	          }
	        });
	        this.getDialog().setErrors(errMessages, true);
	      } else {
	        this.getDialog().setError(this.getMessage('RequestError'));
	      }

	      this.xhr = null;
	      this.currentStep = -1;
	      this.setState(ProcessState.error);
	    } //endregion
	    //region Connection

	    /**
	     * @private
	     */

	  }, {
	    key: "_closeConnection",
	    value: function _closeConnection() {
	      if (this.xhr instanceof XMLHttpRequest) {
	        try {
	          this.xhr.abort();
	          this.xhr = null;
	        } catch (e) {}
	      }
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "_onRequestStart",
	    value: function _onRequestStart(xhr) {
	      this.xhr = xhr;
	    } //endregion
	    //region Set & Get

	  }, {
	    key: "setId",
	    value: function setId(id) {
	      this.id = id;
	      return this;
	    }
	  }, {
	    key: "getId",
	    value: function getId() {
	      return this.id;
	    } //region Queue actions

	  }, {
	    key: "setQueue",
	    value: function setQueue(queue) {
	      var _this3 = this;

	      queue.forEach(function (action) {
	        return _this3.addQueueAction(action);
	      });
	      return this;
	    }
	  }, {
	    key: "addQueueAction",
	    value: function addQueueAction(action) {
	      this.queue.push(action);
	      return this;
	    }
	  }, {
	    key: "getQueueLength",
	    value: function getQueueLength() {
	      return this.queue.length;
	    } //endregion
	    //region Process options

	  }, {
	    key: "setOption",
	    value: function setOption(name, value) {
	      this.options[name] = value;
	      return this;
	    }
	  }, {
	    key: "getOption",
	    value: function getOption(name) {
	      var defaultValue = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	      return this.options.hasOwnProperty(name) ? this.options[name] : defaultValue;
	    } //endregion
	    //region Initial fields

	  }, {
	    key: "setOptionsFields",
	    value: function setOptionsFields(optionsFields) {
	      var _this4 = this;

	      Object.keys(optionsFields).forEach(function (id) {
	        return _this4.addOptionsField(id, optionsFields[id]);
	      });
	      return this;
	    }
	  }, {
	    key: "addOptionsField",
	    value: function addOptionsField(id, field) {
	      this.optionsFields[id] = field;
	      return this;
	    }
	  }, {
	    key: "storeOptionFieldValues",
	    value: function storeOptionFieldValues(values) {
	      var _this5 = this;

	      if ('sessionStorage' in window) {
	        var valuesToStore = {};
	        Object.keys(this.optionsFields).forEach(function (name) {
	          var field = _this5.optionsFields[name];

	          switch (field.type) {
	            case 'checkbox':
	            case 'select':
	            case 'radio':
	              if (field.name in values) {
	                valuesToStore[field.name] = values[field.name];
	              }

	              break;
	          }
	        });
	        window.sessionStorage.setItem('bx.' + this.getId(), JSON.stringify(valuesToStore));
	      }

	      return this;
	    }
	  }, {
	    key: "restoreOptionFieldValues",
	    value: function restoreOptionFieldValues() {
	      var values = {};

	      if ('sessionStorage' in window) {
	        values = JSON.parse(window.sessionStorage.getItem('bx.' + this.getId()));

	        if (!main_core.Type.isPlainObject(values)) {
	          values = {};
	        }
	      }

	      return values;
	    } //endregion
	    //region Request parameters

	  }, {
	    key: "setParams",
	    value: function setParams(params) {
	      var _this6 = this;

	      this.params = {};
	      Object.keys(params).forEach(function (name) {
	        return _this6.setParam(name, params[name]);
	      });
	      return this;
	    }
	  }, {
	    key: "getParams",
	    value: function getParams() {
	      return this.params;
	    }
	  }, {
	    key: "setParam",
	    value: function setParam(key, value) {
	      this.params[key] = value;
	      return this;
	    }
	  }, {
	    key: "getParam",
	    value: function getParam(key) {
	      return this.params[key] ? this.params[key] : null;
	    } //endregion
	    //region Process state

	  }, {
	    key: "setState",
	    value: function setState(state) {
	      var result = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

	      if (this.state === state) {
	        return this;
	      }

	      this.state = state;

	      if (state === ProcessState.intermediate || state === ProcessState.stopped) {
	        this.getDialog().lockButton('start', false).lockButton('stop', true).showButton('close', true);
	      } else if (state === ProcessState.running) {
	        this.getDialog().lockButton('start', true, true).lockButton('stop', false).showButton('close', false);
	      } else if (state === ProcessState.canceling) {
	        this.getDialog().lockButton('start', true).lockButton('stop', true, true).showButton('close', false).hideProgressBar();
	      } else if (state === ProcessState.error) {
	        this.getDialog().lockButton('start', true).lockButton('stop', true).showButton('close', true);
	      } else if (state === ProcessState.completed) {
	        this.getDialog().lockButton('start', true).lockButton('stop', true).showButton('close', true).hideProgressBar();
	      }

	      if (this.hasActionHandler(ProcessCallback.StateChanged)) {
	        this.callActionHandler(ProcessCallback.StateChanged, [state, result]);
	      } else if (this.hasHandler(ProcessCallback.StateChanged)) {
	        this.callHandler(ProcessCallback.StateChanged, [state, result]);
	      }

	      main_core_events.EventEmitter.emit(ProcessEvent.StateChanged, new main_core_events.BaseEvent({
	        data: {
	          state: state,
	          result: result
	        }
	      }));
	      return this;
	    }
	  }, {
	    key: "getState",
	    value: function getState() {
	      return this.state;
	    } //endregion
	    //region Controller

	  }, {
	    key: "setController",
	    value: function setController(controller) {
	      this.controller = controller;
	      return this;
	    }
	  }, {
	    key: "getController",
	    value: function getController() {
	      return this.controller;
	    }
	  }, {
	    key: "setComponent",
	    value: function setComponent(component) {
	      var componentMode = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'class';
	      this.component = component;
	      this.componentMode = componentMode;
	      return this;
	    }
	  }, {
	    key: "getComponent",
	    value: function getComponent() {
	      return this.component;
	    }
	  }, {
	    key: "setAction",
	    value: function setAction(action) {
	      this.action = action;
	      return this;
	    }
	  }, {
	    key: "getAction",
	    value: function getAction() {
	      return this.action;
	    }
	  }, {
	    key: "callAction",
	    value: function callAction(action) {
	      this.setAction(action)._refreshHash();

	      return this.startRequest();
	    } //endregion
	    //region Event handlers

	  }, {
	    key: "setHandlers",
	    value: function setHandlers(handlers) {
	      var _this7 = this;

	      Object.keys(handlers).forEach(function (type) {
	        return _this7.setHandler(type, handlers[type]);
	      });
	      return this;
	    }
	  }, {
	    key: "setHandler",
	    value: function setHandler(type, handler) {
	      if (main_core.Type.isFunction(handler)) {
	        this.handlers[type] = handler;
	      }

	      return this;
	    }
	  }, {
	    key: "hasHandler",
	    value: function hasHandler(type) {
	      return main_core.Type.isFunction(this.handlers[type]);
	    }
	  }, {
	    key: "callHandler",
	    value: function callHandler(type, args) {
	      if (this.hasHandler(type)) {
	        this.handlers[type].apply(this, args);
	      }
	    }
	  }, {
	    key: "hasActionHandler",
	    value: function hasActionHandler(type) {
	      if (this.queue[this.currentStep]) {
	        if ('handlers' in this.queue[this.currentStep]) {
	          return main_core.Type.isFunction(this.queue[this.currentStep].handlers[type]);
	        }
	      }

	      return false;
	    }
	  }, {
	    key: "callActionHandler",
	    value: function callActionHandler(type, args) {
	      if (this.hasActionHandler(type)) {
	        this.queue[this.currentStep].handlers[type].apply(this, args);
	      }
	    } //endregion
	    //region lang messages

	  }, {
	    key: "setMessages",
	    value: function setMessages(messages) {
	      var _this8 = this;

	      Object.keys(messages).forEach(function (id) {
	        return _this8.setMessage(id, messages[id]);
	      });
	      return this;
	    }
	  }, {
	    key: "setMessage",
	    value: function setMessage(id, text) {
	      this.messages.set(id, text);
	      return this;
	    }
	  }, {
	    key: "getMessage",
	    value: function getMessage(id) {
	      var placeholders = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	      var phrase = this.messages.has(id) ? this.messages.get(id) : '';

	      if (main_core.Type.isStringFilled(phrase) && main_core.Type.isPlainObject(placeholders)) {
	        Object.keys(placeholders).forEach(function (placeholder) {
	          phrase = phrase.replace('#' + placeholder + '#', placeholders[placeholder]);
	        });
	      }

	      return phrase;
	    } //endregion
	    //endregion
	    //region Dialog

	  }, {
	    key: "getDialog",
	    value: function getDialog() {
	      if (!this.dialog) {
	        this.dialog = new Dialog({
	          id: this.id,
	          optionsFields: this.getOption('optionsFields', {}),
	          minWidth: Number.parseInt(this.getOption('dialogMinWidth', 500)),
	          maxWidth: Number.parseInt(this.getOption('dialogMaxWidth', 1000)),
	          optionsFieldsValue: this.restoreOptionFieldValues(),
	          messages: {
	            title: this.getMessage('DialogTitle'),
	            summary: this.getMessage('DialogSummary'),
	            startButton: this.getMessage('DialogStartButton'),
	            stopButton: this.getMessage('DialogStopButton'),
	            closeButton: this.getMessage('DialogCloseButton'),
	            downloadButton: this.getMessage('DialogExportDownloadButton'),
	            clearButton: this.getMessage('DialogExportClearButton')
	          },
	          showButtons: this.getOption('showButtons'),
	          handlers: {
	            start: BX.delegate(this.start, this),
	            stop: BX.delegate(this.stop, this),
	            dialogShown: typeof this.handlers.dialogShown == 'function' ? this.handlers.dialogShown : null,
	            dialogClosed: typeof this.handlers.dialogClosed == 'function' ? this.handlers.dialogClosed : null
	          }
	        });
	      }

	      return this.dialog;
	    }
	  }, {
	    key: "showDialog",
	    value: function showDialog() {
	      this.getDialog().setSetting('optionsFieldsValue', this.restoreOptionFieldValues()).resetButtons(this.getOption('optionsFields')).show();

	      if (!this.isRequestRunning) {
	        this.setState(ProcessState.intermediate);
	      }

	      return this;
	    }
	  }, {
	    key: "closeDialog",
	    value: function closeDialog() {
	      if (this.isRequestRunning) {
	        this.stop();
	      }

	      this.getDialog().close();
	      return this;
	    } //endregion

	  }]);
	  return Process;
	}();

	/**
	 * @namespace {BX.UI.StepProcessing}
	 */

	var ProcessManager = /*#__PURE__*/function () {
	  function ProcessManager() {
	    babelHelpers.classCallCheck(this, ProcessManager);
	  }

	  babelHelpers.createClass(ProcessManager, null, [{
	    key: "create",
	    value: function create(props) {
	      if (!this.instances) {
	        this.instances = new Map();
	      }

	      var process = new Process(props);
	      this.instances.set(process.getId(), process);
	      return process;
	    }
	  }, {
	    key: "get",
	    value: function get(id) {
	      if (this.instances) {
	        if (this.instances.has(id)) {
	          return this.instances.get(id);
	        }
	      }

	      return null;
	    }
	  }, {
	    key: "has",
	    value: function has(id) {
	      if (this.instances) {
	        return this.instances.has(id);
	      }

	      return false;
	    }
	  }, {
	    key: "delete",
	    value: function _delete(id) {
	      if (this.instances) {
	        if (this.instances.has(id)) {
	          this.instances.get(id).destroy();
	          this.instances["delete"](id);
	        }
	      }
	    }
	  }]);
	  return ProcessManager;
	}();

	exports.ProcessManager = ProcessManager;
	exports.Process = Process;
	exports.ProcessState = ProcessState;
	exports.ProcessEvent = ProcessEvent;
	exports.ProcessCallback = ProcessCallback;
	exports.ProcessResultStatus = ProcessResultStatus;
	exports.Dialog = Dialog;
	exports.DialogEvent = DialogEvent;

}((this.BX.UI.StepProcessing = this.BX.UI.StepProcessing || {}),BX.UI,BX.Event,BX.Main,BX.UI,BX.UI,BX));
//# sourceMappingURL=process.bundle.js.map

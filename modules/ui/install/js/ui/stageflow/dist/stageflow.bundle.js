this.BX = this.BX || {};
(function (exports,main_core,main_popup) {
	'use strict';

	var _templateObject, _templateObject2;
	var Stage = /*#__PURE__*/function () {
	  function Stage(_ref) {
	    var id = _ref.id,
	        name = _ref.name,
	        color = _ref.color,
	        backgroundColor = _ref.backgroundColor,
	        isFilled = _ref.isFilled,
	        events = _ref.events,
	        isSuccess = _ref.isSuccess,
	        isFail = _ref.isFail,
	        fillingColor = _ref.fillingColor;
	    babelHelpers.classCallCheck(this, Stage);
	    babelHelpers.defineProperty(this, "backgroundImage", "url('data:image/svg+xml;charset=UTF-8,%3csvg width=%27295%27 height=%2732%27 viewBox=%270 0 295 32%27 fill=%27none%27 xmlns=%27http://www.w3.org/2000/svg%27%3e%3cmask id=%27mask0_2_11%27 style=%27mask-type:alpha%27 maskUnits=%27userSpaceOnUse%27 x=%270%27 y=%270%27 width=%27295%27 height=%2732%27%3e%3cpath fill=%27#COLOR2#%27 d=%27M0 2.9961C0 1.3414 1.33554 0 2.99805 0L285.905 7.15256e-07C287.561 7.15256e-07 289.366 1.25757 289.937 2.80757L295 16.5505L290.007 29.2022C289.397 30.7474 287.567 32 285.905 32H2.99805C1.34227 32 0 30.6657 0 29.0039V2.9961Z%27/%3e%3c/mask%3e%3cg mask=%27url(%23mask0_2_11)%27%3e%3cpath fill=%27#COLOR2#%27 d=%27M0 2.9961C0 1.3414 1.33554 0 2.99805 0L285.905 7.15256e-07C287.561 7.15256e-07 289.366 1.25757 289.937 2.80757L295 16.5505L290.007 29.2022C289.397 30.7474 287.567 32 285.905 32H2.99805C1.34227 32 0 30.6657 0 29.0039V2.9961Z%27/%3e%3cpath d=%27M0 30H295V32H0V30Z%27 fill=%27#COLOR1#%27/%3e%3c/g%3e%3c/svg%3e') 3 10 3 3 fill repeat");
	    this.id = id;
	    this.name = name;
	    this.color = color;
	    this.backgroundColor = backgroundColor;
	    this.isFilled = isFilled;
	    this.events = events;
	    this.success = isSuccess;
	    this.fail = isFail;
	    this.fillingColor = fillingColor;
	  }

	  babelHelpers.createClass(Stage, [{
	    key: "getId",
	    value: function getId() {
	      return this.id;
	    }
	  }, {
	    key: "getName",
	    value: function getName() {
	      return this.name;
	    }
	  }, {
	    key: "setName",
	    value: function setName(name) {
	      this.name = name;

	      if (this.textNode) {
	        this.textNode.innerText = this.name;
	      }

	      return this;
	    }
	  }, {
	    key: "isSuccess",
	    value: function isSuccess() {
	      return this.success === true;
	    }
	  }, {
	    key: "isFail",
	    value: function isFail() {
	      return this.fail === true;
	    }
	  }, {
	    key: "isFinal",
	    value: function isFinal() {
	      return this.isFail() || this.isSuccess();
	    }
	  }, {
	    key: "getColor",
	    value: function getColor() {
	      return this.color;
	    }
	  }, {
	    key: "setColor",
	    value: function setColor(color) {
	      this.color = color;
	      return this;
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      if (this.node) {
	        this.textNode.style.backgroundImage = this.getBackgroundImage();
	      } else {
	        this.textNode = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<div style=\"border-image: ", ";\" class=\"ui-stageflow-stage-item-text\">", "</div>"])), this.getBackgroundImage(), main_core.Text.encode(this.getName()));
	        this.node = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["<div \n\t\t\t\t\tclass=\"ui-stageflow-stage\" \n\t\t\t\t\tdata-stage-id=\"", "\" \n\t\t\t\t\tonmouseenter=\"", "\" \n\t\t\t\t\tonmouseleave=\"", "\"\n\t\t\t\t\tonclick=\"", "\"\n\t\t\t\t>\n\t\t\t\t<div class=\"ui-stageflow-stage-item\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</div>"])), this.getId(), this.onMouseEnter.bind(this), this.onMouseLeave.bind(this), this.onClick.bind(this), this.textNode);
	      }

	      this.textNode.style.color = Stage.calculateTextColor('#' + (this.isFilled ? this.color : this.backgroundColor));
	      return this.node;
	    }
	  }, {
	    key: "getBackgroundImage",
	    value: function getBackgroundImage() {
	      var color = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
	      var isFilled = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;

	      if (!color) {
	        if (this.isFilled && this.fillingColor) {
	          color = this.fillingColor;
	        } else {
	          color = this.getColor();
	        }
	      }

	      if (main_core.Type.isNull(isFilled)) {
	        isFilled = this.isFilled;
	      }

	      var image = this.backgroundImage.replaceAll('#COLOR1#', encodeURIComponent('#' + color));

	      if (isFilled) {
	        image = image.replaceAll('#COLOR2#', encodeURIComponent('#' + color));
	      } else {
	        image = image.replaceAll('#COLOR2#', encodeURIComponent('#' + this.backgroundColor));
	      }

	      return image;
	    }
	  }, {
	    key: "onMouseEnter",
	    value: function onMouseEnter() {
	      if (main_core.Type.isFunction(this.events.onMouseEnter)) {
	        this.events.onMouseEnter(this);
	      }
	    }
	  }, {
	    key: "onMouseLeave",
	    value: function onMouseLeave() {
	      if (main_core.Type.isFunction(this.events.onMouseLeave)) {
	        this.events.onMouseLeave(this);
	      }
	    }
	  }, {
	    key: "onClick",
	    value: function onClick() {
	      if (main_core.Type.isFunction(this.events.onClick)) {
	        this.events.onClick(this);
	      }
	    }
	  }, {
	    key: "addBackLight",
	    value: function addBackLight(color) {
	      if (this.textNode) {
	        this.textNode.style.borderImage = this.getBackgroundImage(color, true);
	        this.textNode.style.color = Stage.calculateTextColor('#' + color);
	      }
	    }
	  }, {
	    key: "removeBackLight",
	    value: function removeBackLight() {
	      if (this.textNode) {
	        this.textNode.style.borderImage = this.getBackgroundImage();
	        this.textNode.style.color = Stage.calculateTextColor('#' + (this.isFilled ? this.fillingColor : this.backgroundColor));
	      }
	    }
	  }], [{
	    key: "create",
	    value: function create(data) {
	      if (main_core.Type.isPlainObject(data) && data.id && data.name && data.color && data.backgroundColor) {
	        data.id = main_core.Text.toInteger(data.id);
	        data.name = data.name.toString();
	        data.color = data.color.toString();
	        data.backgroundColor = data.backgroundColor.toString();

	        if (!main_core.Type.isPlainObject(data.events)) {
	          data.events = {};
	        }

	        if (!main_core.Type.isBoolean(data.isFilled)) {
	          data.isFilled = false;
	        }

	        if (data.id > 0) {
	          return new Stage(data);
	        }
	      }

	      return null;
	    }
	  }, {
	    key: "calculateTextColor",
	    value: function calculateTextColor(baseColor) {
	      var r, g, b;

	      if (baseColor.length > 7 && baseColor.indexOf('(') >= 0 && baseColor.indexOf(')') >= 0) {
	        var hexComponent = baseColor.split("(")[1].split(")")[0];
	        hexComponent = hexComponent.split(",");
	        r = parseInt(hexComponent[0]);
	        g = parseInt(hexComponent[1]);
	        b = parseInt(hexComponent[2]);
	      } else {
	        if (/^#([A-Fa-f0-9]{3}){1,2}$/.test(baseColor)) {
	          var c = baseColor.substring(1).split('');

	          if (c.length === 3) {
	            c = [c[0], c[0], c[1], c[1], c[2], c[2]];
	          }

	          c = '0x' + c.join('');
	          r = c >> 16 & 255;
	          g = c >> 8 & 255;
	          b = c & 255;
	        }
	      }

	      var y = 0.21 * r + 0.72 * g + 0.07 * b;
	      return y < 145 ? "#fff" : "#333";
	    }
	  }]);
	  return Stage;
	}();

	var _templateObject$1, _templateObject2$1, _templateObject3, _templateObject4, _templateObject5, _templateObject6;

	function _createForOfIteratorHelper(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }

	function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

	function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var semanticSelectorPopupId = 'ui-stageflow-select-semantic-popup';
	var finalStageSelectorPopupId = 'ui-stageflow-select-final-stage-popup';
	var FinalStageDefaultData = {
	  id: 'final',
	  color: '7BD500',
	  isFilled: false
	};
	var defaultFinalStageLabels = {
	  finalStageName: main_core.Loc.getMessage('UI_STAGEFLOW_FINAL_STAGE_NAME'),
	  finalStagePopupTitle: main_core.Loc.getMessage('UI_STAGEFLOW_FINAL_STAGE_POPUP_TITLE'),
	  finalStagePopupFail: main_core.Loc.getMessage('UI_STAGEFLOW_FINAL_STAGE_POPUP_FAIL'),
	  finalStageSelectorTitle: main_core.Loc.getMessage('UI_STAGEFLOW_FINAL_STAGE_SELECTOR_TITLE')
	};
	var Chart = /*#__PURE__*/function () {
	  function Chart(params) {
	    var _this = this;

	    var stages = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : [];
	    babelHelpers.classCallCheck(this, Chart);
	    babelHelpers.defineProperty(this, "currentStage", 0);
	    babelHelpers.defineProperty(this, "isActive", false);
	    this.labels = defaultFinalStageLabels;

	    if (main_core.Type.isPlainObject(params)) {
	      if (main_core.Type.isString(params.backgroundColor) && params.backgroundColor.length === 6) {
	        this.backgroundColor = params.backgroundColor;
	      }

	      if (params.currentStage) {
	        this.currentStage = main_core.Text.toInteger(params.currentStage);
	      }

	      if (main_core.Type.isBoolean(params.isActive)) {
	        this.isActive = params.isActive;
	      }

	      if (main_core.Type.isFunction(params.onStageChange)) {
	        this.onStageChange = params.onStageChange;
	      }

	      if (main_core.Type.isPlainObject(params.labels)) {
	        this.labels = _objectSpread(_objectSpread({}, this.labels), params.labels);
	      }
	    }

	    FinalStageDefaultData.name = this.labels.finalStageName;

	    if (main_core.Type.isArray(stages)) {
	      var fillingColor = null;

	      if (this.currentStage > 0) {
	        stages.forEach(function (data) {
	          if (main_core.Text.toInteger(data.id) === main_core.Text.toInteger(_this.currentStage)) {
	            fillingColor = data.color;
	          }
	        });
	      }

	      this.fillStages(stages, fillingColor);
	    }

	    if (!this.currentStage && this.stages.length > 0) {
	      this.currentStage = this.stages.keys().next().value;
	    }
	  }

	  babelHelpers.createClass(Chart, [{
	    key: "setCurrentStageId",
	    value: function setCurrentStageId(stageId) {
	      stageId = main_core.Text.toInteger(stageId);
	      var currentStage = this.getStageById(stageId);

	      if (!currentStage) {
	        return;
	      }

	      this.currentStage = stageId;
	      var finalStage = this.getFinalStage();

	      if (finalStage) {
	        if (currentStage.isFinal()) {
	          finalStage.setColor(currentStage.getColor()).setName(currentStage.getName());
	        } else {
	          finalStage.setColor(FinalStageDefaultData.color).setName(FinalStageDefaultData.name);
	        }
	      }

	      this.stages.forEach(function (stage) {
	        if (!stage.isFinal()) {
	          stage.fillingColor = currentStage.getColor();
	        }
	      });
	      this.addBackLightUpToStage();
	      return this;
	    }
	  }, {
	    key: "fillStages",
	    value: function fillStages(stages, fillingColor) {
	      var _this2 = this;

	      var isFilled = this.currentStage > 0;
	      var finalStageOptions = {};
	      this.stages = new Map();
	      stages.forEach(function (data) {
	        data.isFilled = isFilled;
	        data.backgroundColor = _this2.backgroundColor;
	        data.fillingColor = fillingColor;
	        data.events = {
	          onMouseEnter: _this2.onStageMouseHover.bind(_this2),
	          onMouseLeave: _this2.onStageMouseLeave.bind(_this2),
	          onClick: _this2.onStageClick.bind(_this2)
	        };
	        var stage = Stage.create(data);

	        if (stage) {
	          _this2.stages.set(stage.getId(), stage);
	        }

	        if (stage.isSuccess()) {
	          FinalStageDefaultData.color = stage.getColor();
	        }

	        if (stage.isFinal()) {
	          finalStageOptions.isFilled = isFilled;

	          if (stage.getId() === _this2.currentStage) {
	            finalStageOptions.name = stage.getName();
	            finalStageOptions.color = stage.getColor();
	          }
	        } else if (isFilled && stage.getId() === _this2.currentStage) {
	          isFilled = false;
	        }
	      });

	      if (this.getFailStages().length <= 0) {
	        FinalStageDefaultData.name = finalStageOptions.name = this.getSuccessStage().getName();
	      }

	      this.addFinalStage(finalStageOptions);
	    }
	  }, {
	    key: "addFinalStage",
	    value: function addFinalStage(data) {
	      this.stages.set(FinalStageDefaultData.id, new Stage(_objectSpread(_objectSpread(_objectSpread({}, {
	        backgroundColor: this.backgroundColor,
	        events: {
	          onMouseEnter: this.onStageMouseHover.bind(this),
	          onMouseLeave: this.onStageMouseLeave.bind(this),
	          onClick: this.onFinalStageClick.bind(this)
	        }
	      }), FinalStageDefaultData), data)));
	    }
	  }, {
	    key: "getFinalStage",
	    value: function getFinalStage() {
	      return this.getStageById(FinalStageDefaultData.id);
	    }
	  }, {
	    key: "getStages",
	    value: function getStages() {
	      return this.stages;
	    }
	  }, {
	    key: "getFirstFailStage",
	    value: function getFirstFailStage() {
	      var failStage = null;
	      this.stages.forEach(function (stage) {
	        if (stage.isFail() && !failStage) {
	          failStage = stage;
	        }
	      });
	      return failStage;
	    }
	  }, {
	    key: "getFailStages",
	    value: function getFailStages() {
	      var failStages = [];
	      this.stages.forEach(function (stage) {
	        if (stage.isFail()) {
	          failStages.push(stage);
	        }
	      });
	      return failStages;
	    }
	  }, {
	    key: "getSuccessStage",
	    value: function getSuccessStage() {
	      var finalStage = null;
	      this.stages.forEach(function (stage) {
	        if (stage.isSuccess()) {
	          finalStage = stage;
	        }
	      });
	      return finalStage;
	    }
	  }, {
	    key: "getStageById",
	    value: function getStageById(id) {
	      return this.stages.get(id);
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      var container = this.renderContainer();
	      this.getStages().forEach(function (stage) {
	        if (stage.isFinal()) {
	          return;
	        }

	        container.appendChild(stage.render());
	      });
	      this.addBackLightUpToStage();
	      return container;
	    }
	  }, {
	    key: "renderContainer",
	    value: function renderContainer() {
	      if (this.container) {
	        main_core.Dom.clean(this.container);
	        return this.container;
	      }

	      this.container = main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-stageflow-container\"></div>"])));
	      return this.container;
	    }
	  }, {
	    key: "onStageMouseHover",
	    value: function onStageMouseHover(stage) {
	      if (!this.isActive) {
	        return;
	      }

	      var _iterator = _createForOfIteratorHelper(this.stages),
	          _step;

	      try {
	        for (_iterator.s(); !(_step = _iterator.n()).done;) {
	          var _step$value = babelHelpers.slicedToArray(_step.value, 2),
	              id = _step$value[0],
	              currentStage = _step$value[1];

	          currentStage.addBackLight(stage.getColor());

	          if (id === stage.getId()) {
	            break;
	          }
	        }
	      } catch (err) {
	        _iterator.e(err);
	      } finally {
	        _iterator.f();
	      }
	    }
	  }, {
	    key: "onStageMouseLeave",
	    value: function onStageMouseLeave(stage) {
	      if (!this.isActive) {
	        return;
	      }

	      var _iterator2 = _createForOfIteratorHelper(this.stages),
	          _step2;

	      try {
	        for (_iterator2.s(); !(_step2 = _iterator2.n()).done;) {
	          var _step2$value = babelHelpers.slicedToArray(_step2.value, 2),
	              id = _step2$value[0],
	              currentStage = _step2$value[1];

	          currentStage.removeBackLight();

	          if (id === stage.getId()) {
	            break;
	          }
	        }
	      } catch (err) {
	        _iterator2.e(err);
	      } finally {
	        _iterator2.f();
	      }
	    }
	  }, {
	    key: "onStageClick",
	    value: function onStageClick(stage) {
	      if (!this.isActive) {
	        return;
	      }

	      if (stage.getId() !== this.currentStage && main_core.Type.isFunction(this.onStageChange)) {
	        this.onStageChange(stage);
	      }

	      var popup = this.getSemanticSelectorPopup();

	      if (popup.isShown()) {
	        popup.close();
	      }
	    }
	  }, {
	    key: "onFinalStageClick",
	    value: function onFinalStageClick(stage) {
	      if (!this.isActive) {
	        return;
	      }

	      if (this.getFailStages().length <= 0) {
	        this.onStageClick(this.getSuccessStage());
	      } else {
	        var popup = this.getSemanticSelectorPopup();
	        popup.show();
	        var currentStage = this.getStageById(this.currentStage);
	        this.isActive = false;

	        if (!currentStage.isFinal()) {
	          var finalStage = this.getStageById(FinalStageDefaultData.id);

	          if (finalStage) {
	            this.addBackLightUpToStage(finalStage.getId(), finalStage.getColor());
	          }
	        }
	      }
	    }
	  }, {
	    key: "addBackLightUpToStage",
	    value: function addBackLightUpToStage() {
	      var stageId = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
	      var color = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;

	      if (!stageId) {
	        stageId = this.currentStage;
	      }

	      var currentStage = this.getStageById(stageId);

	      if (currentStage && !color) {
	        color = currentStage.getColor();
	      }

	      var isFilled = !!stageId;
	      this.stages.forEach(function (stage) {
	        stage.isFilled = isFilled;

	        if (stage.isFilled) {
	          stage.addBackLight(color ? color : stage.getColor());
	        } else {
	          stage.removeBackLight();
	        }

	        if (!stage.isFinal() && isFilled && stage.getId() === stageId) {
	          isFilled = false;
	        }
	      });
	    }
	  }, {
	    key: "getSemanticSelectorPopup",
	    value: function getSemanticSelectorPopup() {
	      var _this3 = this;

	      var popup = main_popup.PopupManager.getPopupById(semanticSelectorPopupId);

	      if (!popup) {
	        var failSemanticText = this.getFailStageName();
	        popup = main_popup.PopupManager.create({
	          id: semanticSelectorPopupId,
	          autoHide: true,
	          closeByEsc: true,
	          closeIcon: true,
	          maxWidth: 420,
	          content: main_core.Tag.render(_templateObject2$1 || (_templateObject2$1 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-stageflow-popup-title\">", "</div>"])), this.labels.finalStagePopupTitle),
	          buttons: [new BX.UI.Button({
	            color: BX.UI.Button.Color.SUCCESS,
	            text: this.getSuccessStage().getName(),
	            onclick: function onclick() {
	              _this3.isActive = true;

	              _this3.onStageClick(_this3.getSuccessStage());
	            }
	          }), failSemanticText ? new BX.UI.Button({
	            color: BX.UI.Button.Color.DANGER,
	            text: failSemanticText,
	            onclick: function onclick() {
	              popup.close();

	              var finalStagePopup = _this3.getFinalStageSelectorPopup();

	              finalStagePopup.show();
	              _this3.isActive = false;
	            }
	          }) : null],
	          events: {
	            onClose: function onClose() {
	              _this3.setCurrentStageId(_this3.currentStage);

	              _this3.isActive = true;
	            }
	          }
	        });
	      }

	      return popup;
	    }
	  }, {
	    key: "getFinalStageSemanticSelector",
	    value: function getFinalStageSemanticSelector() {
	      var isSuccess = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;

	      if (!this.finalStageSemanticSelector) {
	        this.finalStageSemanticSelector = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-stageflow-stage-selector-option ui-stageflow-stage-selector-option-fail\" onclick=\"", "\"></div>"])), this.onSemanticSelectorClick.bind(this));
	      }

	      if (main_core.Type.isBoolean(isSuccess)) {
	        var realFinalStage = null;
	        var failStageName = this.getFailStageName();

	        if (isSuccess || !failStageName) {
	          this.finalStageSemanticSelector.classList.add('ui-stageflow-stage-selector-option-success');
	          this.finalStageSemanticSelector.classList.remove('ui-stageflow-stage-selector-option-fail');
	          this.finalStageSemanticSelector.innerText = this.getSuccessStage().getName();
	          realFinalStage = this.getSuccessStage();
	        } else {
	          this.finalStageSemanticSelector.classList.add('ui-stageflow-stage-selector-option-fail');
	          this.finalStageSemanticSelector.classList.remove('ui-stageflow-stage-selector-option-success');
	          this.finalStageSemanticSelector.innerText = failStageName;
	          realFinalStage = this.getFirstFailStage();
	        }

	        var finalStage = this.getFinalStage();

	        if (finalStage && realFinalStage) {
	          finalStage.setColor(realFinalStage.getColor()).setName(realFinalStage.getName());
	        }

	        this.addBackLightUpToStage(finalStage.getId(), finalStage.getColor());
	      }

	      return this.finalStageSemanticSelector;
	    }
	  }, {
	    key: "getFinalStageSelectorPopup",
	    value: function getFinalStageSelectorPopup() {
	      var _this4 = this;

	      var isSuccess = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;
	      var titleBar = {};
	      var content = main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-stageflow-final-fail-stage-list-wrapper\"></div>"])));

	      if (!isSuccess) {
	        var failStages = this.getFailStages();

	        if (failStages.length > 1) {
	          var isChecked = true;
	          failStages.forEach(function (stage) {
	            content.appendChild(main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-stageflow-final-fail-stage-list-section\">\n\t\t\t\t\t\t<input data-stage-id=\"", "\" id=\"ui-stageflow-final-fail-stage-", "\" name=\"ui-stageflow-final-fail-stage-input\" class=\"crm-list-fail-deal-button\" type=\"radio\" ", ">\n\t\t\t\t\t\t<label for=\"ui-stageflow-final-fail-stage-", "\">", "</label>\n\t\t\t\t\t</div>"])), stage.getId(), stage.getId(), isChecked ? 'checked="checked"' : '', stage.getId(), stage.getName()));
	            isChecked = false;
	          });
	        }
	      }

	      titleBar.content = main_core.Tag.render(_templateObject6 || (_templateObject6 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-stageflow-stage-selector-block\">\n\t\t\t<span>", " </span>\n\t\t\t", "\n\t\t</div>"])), this.labels.finalStageSelectorTitle, this.getFinalStageSemanticSelector(isSuccess));
	      var popup = main_popup.PopupManager.getPopupById(finalStageSelectorPopupId);

	      if (!popup) {
	        popup = main_popup.PopupManager.create({
	          id: finalStageSelectorPopupId,
	          autoHide: false,
	          closeByEsc: true,
	          closeIcon: true,
	          width: 420,
	          titleBar: true,
	          buttons: [new BX.UI.SaveButton({
	            onclick: function onclick() {
	              popup.close();

	              var stage = _this4.getSelectedFinalStage();

	              if (stage) {
	                _this4.onStageClick(stage);
	              }
	            }
	          }), new BX.UI.CancelButton({
	            onclick: function onclick() {
	              popup.close();
	            }
	          })],
	          events: {
	            onClose: function onClose() {
	              _this4.setCurrentStageId(_this4.currentStage);

	              _this4.isActive = true;
	            }
	          }
	        });
	      }

	      popup.setContent(content);
	      popup.setTitleBar(titleBar);
	      return popup;
	    }
	  }, {
	    key: "onSemanticSelectorClick",
	    value: function onSemanticSelectorClick() {
	      var _this5 = this;

	      var failStageName = this.getFailStageName();
	      var menu = main_popup.MenuManager.create({
	        id: 'ui-stageflow-final-stage-semantic-selector',
	        bindElement: this.getFinalStageSemanticSelector(),
	        items: [{
	          text: this.getSuccessStage().getName(),
	          onclick: function onclick() {
	            _this5.getFinalStageSelectorPopup(true);

	            menu.close();
	          }
	        }, failStageName ? {
	          text: failStageName,
	          onclick: function onclick() {
	            _this5.getFinalStageSelectorPopup(false);

	            menu.close();
	          }
	        } : null]
	      });
	      menu.show();
	    }
	  }, {
	    key: "getSelectedFinalStage",
	    value: function getSelectedFinalStage() {
	      var finalStageSemanticSelector = this.getFinalStageSemanticSelector();

	      if (finalStageSemanticSelector.classList.contains('ui-stageflow-stage-selector-option-success')) {
	        return this.getSuccessStage();
	      } else {
	        var failStages = this.getFailStages();

	        if (failStages.length > 1) {
	          var finalStageSelectorPopupContainer = document.getElementById(finalStageSelectorPopupId);

	          if (finalStageSelectorPopupContainer) {
	            var selectedInput = finalStageSelectorPopupContainer.querySelector('input:checked');

	            if (selectedInput) {
	              var failStage = this.getStageById(main_core.Text.toInteger(selectedInput.dataset.stageId));

	              if (failStage) {
	                return failStage;
	              }
	            }
	          }
	        }

	        return this.getFirstFailStage();
	      }
	    }
	  }, {
	    key: "getFailStageName",
	    value: function getFailStageName() {
	      var failStagesLength = this.getFailStages().length;

	      if (failStagesLength <= 0) {
	        return null;
	      } else if (failStagesLength === 1) {
	        return this.getFirstFailStage().getName();
	      } else {
	        return this.labels.finalStagePopupFail;
	      }
	    }
	  }]);
	  return Chart;
	}();

	var StageFlow = {
	  Chart: Chart,
	  Stage: Stage
	};

	exports.StageFlow = StageFlow;

}((this.BX.UI = this.BX.UI || {}),BX,BX.Main));
//# sourceMappingURL=stageflow.bundle.js.map

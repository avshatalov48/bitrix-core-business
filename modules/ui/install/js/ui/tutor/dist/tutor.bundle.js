this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_core,ui_tour,main_loader) {
	'use strict';

	var Step =
	/*#__PURE__*/
	function (_Event$EventEmitter) {
	  babelHelpers.inherits(Step, _Event$EventEmitter);

	  function Step(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, Step);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Step).call(this));

	    _this.setEventNamespace('BX.UI.Tutor.Step');

	    options = main_core.Type.isPlainObject(options) ? options : {};
	    _this.id = options.id || null;
	    _this.title = options.title || null;
	    _this.description = options.description || null;
	    _this.url = options.url || '';
	    _this.isCompleted = options.isCompleted || false;
	    _this.video = options.video || null;
	    _this.helpLink = options.helpLink || null;
	    _this.highlight = options.highlight || null;
	    _this.isActive = options.isActive === true;
	    _this.isShownForSlider = options.isShownForSlider || false;
	    _this.initOptions = options;
	    _this.videoObj = null;
	    return _this;
	  }
	  /**
	   * @public
	   * @returns {string}
	   */


	  babelHelpers.createClass(Step, [{
	    key: "getTitle",
	    value: function getTitle() {
	      return this.title;
	    }
	    /**
	     * @public
	     * @returns {Object}
	     */

	  }, {
	    key: "getVideoObj",
	    value: function getVideoObj() {
	      return this.videoObj;
	    }
	    /**
	     * @public
	     */

	  }, {
	    key: "getHighlightOptions",
	    value: function getHighlightOptions() {
	      return this.highlight;
	    }
	    /**
	     * @public
	     * @returns {string}
	     */

	  }, {
	    key: "getDescription",
	    value: function getDescription() {
	      return this.description;
	    }
	    /**
	     * @public
	     * @returns {string}
	     */

	  }, {
	    key: "getUrl",
	    value: function getUrl() {
	      return this.url;
	    }
	    /**
	     * @public
	     * @returns {Boolean}
	     */

	  }, {
	    key: "getCompleted",
	    value: function getCompleted() {
	      return this.isCompleted;
	    }
	  }, {
	    key: "getVideo",
	    value: function getVideo() {
	      return this.video;
	    }
	  }, {
	    key: "getHelpLink",
	    value: function getHelpLink() {
	      return this.helpLink;
	    }
	    /**
	     * @public
	     * @returns {string}
	     */

	  }, {
	    key: "getId",
	    value: function getId() {
	      return this.id;
	    }
	    /**
	     * @public
	     * @returns {Object}
	     */

	  }, {
	    key: "getInitOptions",
	    value: function getInitOptions() {
	      return this.initOptions;
	    }
	    /**
	     * @public
	     */

	  }, {
	    key: "activate",
	    value: function activate() {
	      this.isActive = true;
	    }
	    /**
	     * @public
	     */

	  }, {
	    key: "getShownForSlider",
	    value: function getShownForSlider() {
	      return this.isShownForSlider;
	    }
	    /**
	     * @public
	     */

	  }, {
	    key: "deactivate",
	    value: function deactivate() {
	      this.isActive = false;
	    }
	    /**
	     * @private
	     */

	  }], [{
	    key: "getFullEventName",
	    value: function getFullEventName(shortName) {
	      return shortName;
	    }
	  }]);
	  return Step;
	}(main_core.Event.EventEmitter);

	function _templateObject11() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"ui-tutor-popup-step-title\"></div>\n\t\t\t\t"]);

	  _templateObject11 = function _templateObject11() {
	    return data;
	  };

	  return data;
	}

	function _templateObject10() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<button class=\"ui-btn ui-btn-success ui-btn-round ui-btn-xs\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</button>\n\t\t\t\t"]);

	  _templateObject10 = function _templateObject10() {
	    return data;
	  };

	  return data;
	}

	function _templateObject9() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<button class=\"ui-btn ui-btn-primary ui-btn-round ui-btn-xs\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</button>\n\t\t\t\t"]);

	  _templateObject9 = function _templateObject9() {
	    return data;
	  };

	  return data;
	}

	function _templateObject8() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<button class=\"ui-btn ui-btn-primary ui-btn-round ui-btn-xs\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</button>\n\t\t\t\t"]);

	  _templateObject8 = function _templateObject8() {
	    return data;
	  };

	  return data;
	}

	function _templateObject7() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"ui-tutor-popup ui-tutor-popup-collapse\" onclick=\"", "\">\n\t\t\t\t\t\t<div class=\"ui-tutor-popup-content\">\n\t\t\t\t\t\t\t<div class=\"ui-tutor-popup-step-subject\">", "</div>\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t<div class=\"ui-tutor-popup-collapse-btn\">\n\t\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t"]);

	  _templateObject7 = function _templateObject7() {
	    return data;
	  };

	  return data;
	}

	function _templateObject6() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"ui-tutor-informer\" id=\"ui-tutor-informer\"></div>\n\t\t\t\t"]);

	  _templateObject6 = function _templateObject6() {
	    return data;
	  };

	  return data;
	}

	function _templateObject5() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<button class=\"ui-btn ui-btn-link\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</button>\n\t\t\t\t"]);

	  _templateObject5 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<button class=\"ui-btn ui-btn-primary ui-btn-round\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</button>\n\t\t\t\t"]);

	  _templateObject4 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"ui-tutor-popup ui-tutor-popup-start\">\n\t\t\t\t\t\t<div class=\"ui-tutor-popup-header\">\n\t\t\t\t\t\t\t<span class=\"ui-tutor-popup-header-icon\"></span>\n\t\t\t\t\t\t\t<span class=\"ui-tutor-popup-header-title-wrap\">\n\t\t\t\t\t\t\t\t<span class=\"ui-tutor-popup-header-title\">", "</span>\n\t\t\t\t\t\t\t</span>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"ui-tutor-popup-content\">\n\t\t\t\t\t\t\t<div class=\"ui-tutor-popup-title\">", "</div>\n\t\t\t\t\t\t\t<div class=\"ui-tutor-popup-text\">", "</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"ui-tutor-popup-footer\">\n\t\t\t\t\t\t\t<div class=\"ui-tutor-popup-btn\">\n\t\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"ui-tutor-popup-icon-angle\"></div>\n\t\t\t\t\t</div>\n\t\t\t\t"]);

	  _templateObject3 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"ui-tutor-popup\" onclick=\"", "\">\n\t\t\t\t\t\t<div class=\"ui-tutor-popup-header\">\n\t\t\t\t\t\t\t<span class=\"ui-tutor-popup-header-icon\"></span>\n\t\t\t\t\t\t\t<span class=\"ui-tutor-popup-header-title-wrap\">\n\t\t\t\t\t\t\t\t<span class=\"ui-tutor-popup-header-title\">", "</span> \n\t\t\t\t\t\t\t</span>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"ui-tutor-popup-content\">\n\t\t\t\t\t\t\t<div class=\"ui-tutor-popup-text\">", "</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"ui-tutor-popup-icon-angle\"></div>\n\t\t\t\t\t</div>\n\t\t\t\t"]);

	  _templateObject2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"ui-tutor-btn\"></div>\n\t\t\t\t"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var Manager =
	/*#__PURE__*/
	function (_Event$EventEmitter) {
	  babelHelpers.inherits(Manager, _Event$EventEmitter);

	  function Manager() {
	    var _this;

	    babelHelpers.classCallCheck(this, Manager);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Manager).call(this));

	    _this.setEventNamespace('BX.UI.Tutor.Manager');

	    return _this;
	  }

	  babelHelpers.createClass(Manager, [{
	    key: "setOptions",
	    value: function setOptions(options, domain, feedbackFormId) {
	      options = options || {};
	      this.tutorialData = options.tutorialData || {};
	      this.eventService = options.eventService || {};
	      this.lastCheckTime = options.lastCheckTime || 0;
	      this.domain = options.domain || '';
	      this.feedbackFormId = options.feedbackFormId || '';

	      if (main_core.Type.isString(domain) && domain.length > 0) {
	        this.domain = domain;
	      }

	      if (main_core.Type.isString(feedbackFormId) && feedbackFormId.length > 0) {
	        this.feedbackFormId = feedbackFormId;
	      }
	    }
	  }, {
	    key: "showFeedbackForm",
	    value: function showFeedbackForm() {
	      if (this.feedbackFormId) {
	        this.feedBackForm = BX.UI.Feedback.Form.getById(this.feedbackFormId);

	        if (this.feedBackForm) {
	          this.feedBackForm.openPanel();
	        }
	      }
	    }
	  }, {
	    key: "getDomain",
	    value: function getDomain() {
	      return this.domain;
	    }
	  }, {
	    key: "getCurrentTutorialData",
	    value: function getCurrentTutorialData() {
	      return this.tutorialData;
	    }
	  }, {
	    key: "getCurrentEventService",
	    value: function getCurrentEventService() {
	      return this.eventService;
	    }
	  }, {
	    key: "getCurrentLastCheckTime",
	    value: function getCurrentLastCheckTime() {
	      return this.lastCheckTime;
	    }
	    /**
	     * @return {Manager}
	     */

	  }], [{
	    key: "getInstance",
	    value: function getInstance() {
	      return this.instance;
	    }
	    /**
	     * @return {Scenario}
	     */

	  }, {
	    key: "getScenarioInstance",
	    value: function getScenarioInstance() {
	      return this.scenarioInstance;
	    }
	  }, {
	    key: "init",
	    value: function init(options, domain, feedbackFormId) {
	      var instance = this.getInstance();

	      if (!(instance instanceof Manager)) {
	        this.instance = new Manager();
	        instance = this.getInstance();
	        this.emit('onInitManager');
	      } else {
	        instance = this.getInstance();
	      }

	      instance.setOptions(options, domain, feedbackFormId);
	      return instance;
	    }
	  }, {
	    key: "initScenario",
	    value: function initScenario(options) {
	      var instance = this.getScenarioInstance();

	      if (!(instance instanceof Scenario)) {
	        this.scenarioInstance = new Scenario();
	        instance = this.getScenarioInstance();
	        this.emit('onInitScenario');
	      } else {
	        instance = this.getScenarioInstance();
	      }

	      instance.setOptions(options);
	      return instance;
	    }
	  }, {
	    key: "showButton",
	    value: function showButton(animation) {
	      return this.getImButton(animation);
	    }
	  }, {
	    key: "getRootImButton",
	    value: function getRootImButton() {
	      return document.getElementById('ui-tutor-btn-wrap');
	    }
	  }, {
	    key: "hasImButton",
	    value: function hasImButton() {
	      return !!this.getRootImButton();
	    }
	  }, {
	    key: "getImButton",
	    value: function getImButton(animation) {
	      var _this2 = this;

	      if (!this.layout.imButton) {
	        var buttonWrapper = this.getRootImButton();

	        if (buttonWrapper) {
	          var buttonInner = main_core.Tag.render(_templateObject());

	          if (animation) {
	            main_core.Dom.addClass(buttonWrapper, 'ui-tutor-btn-wrap-animate');
	          }

	          main_core.Dom.append(buttonInner, buttonWrapper);
	          main_core.Dom.addClass(buttonWrapper, 'ui-tutor-btn-wrap-show');
	          this.layout.imButton = buttonWrapper;
	          main_core.Event.bind(this.layout.imButton, "click", function () {
	            _this2.emit('clickImButton');
	          });
	          var usersPanel = document.querySelector('.bx-im-users-wrap');

	          if (document.querySelector('#bx-im-btn-call')) {
	            usersPanel.style.bottom = '175px';
	          } else {
	            usersPanel.style.bottom = '120px';
	          }
	        }
	      }

	      return this.layout.imButton;
	    }
	  }, {
	    key: "showSmallPopup",
	    value: function showSmallPopup(text) {
	      this.smallPopupText = text;
	      this.getSmallPopup().style.display = 'block';
	      this.smallPopupText = '';

	      if (main_core.Dom.hasClass(this.getSmallPopup(), 'ui-tutor-popup-welcome-hide')) {
	        main_core.Dom.removeClass(this.getSmallPopup(), 'ui-tutor-popup-welcome-hide');
	      }
	    }
	  }, {
	    key: "hideSmallPopup",
	    value: function hideSmallPopup(skipAnimation) {
	      skipAnimation = skipAnimation === true;

	      var removeHandler = function () {
	        main_core.Dom.remove(this.getSmallPopup());

	        if (this.hasOwnProperty('smallPopup')) {
	          delete this.smallPopup;
	        }

	        this.emit('onCompleteHideSmallPopup');
	      }.bind(this);

	      main_core.Dom.removeClass(this.getSmallPopup(), 'ui-tutor-popup-welcome-show');
	      main_core.Dom.addClass(this.getSmallPopup(), 'ui-tutor-popup-welcome-hide');

	      if (skipAnimation) {
	        removeHandler();
	      } else {
	        setTimeout(removeHandler, 300);
	      }
	    }
	  }, {
	    key: "showWelcomePopup",
	    value: function showWelcomePopup(text) {
	      this.emit('onShowWelcomePopup');
	      this.showSmallPopup(text);
	    }
	  }, {
	    key: "hideWelcomePopup",
	    value: function hideWelcomePopup() {
	      this.emit('onBeforeHideWelcomePopup');
	      this.hideSmallPopup();
	      this.emit('onAfterHideWelcomePopup');
	    }
	  }, {
	    key: "showNoticePopup",
	    value: function showNoticePopup(text) {
	      this.emit('onShowNoticePopup');
	      this.showSmallPopup(text);
	    }
	  }, {
	    key: "hideNoticePopup",
	    value: function hideNoticePopup() {
	      this.emit('onBeforeHideNoticePopup');
	      this.hideSmallPopup();
	      this.emit('onAfterHideNoticePopup');
	    }
	  }, {
	    key: "getSmallPopup",
	    value: function getSmallPopup() {
	      var _this3 = this;

	      var clickSmallPopupHandler = function clickSmallPopupHandler() {
	        _this3.emit('onClickSmallPopupBtn');
	      };

	      if (!this.smallPopup) {
	        this.smallPopup = main_core.Tag.render(_templateObject2(), clickSmallPopupHandler.bind(this), main_core.Loc.getMessage('JS_UI_TUTOR_TITLE'), this.smallPopupText);
	        this.emit('onCreateSmallPopupNode');
	        main_core.Dom.addClass(this.smallPopup, 'ui-tutor-popup-welcome-show');
	        this.emit('onBeforeAppendSmallPopupNode');
	        main_core.Dom.append(this.smallPopup, document.body);
	        this.emit('onAfterAppendSmallPopupNode');
	      }

	      return this.smallPopup;
	    }
	  }, {
	    key: "showStartPopup",
	    value: function showStartPopup(title, text) {
	      this.emit('onShowStartPopup');
	      this.startTitle = title;
	      this.startText = text;
	      main_core.Dom.addClass(this.getStartPopup(), 'ui-tutor-popup-show');
	      this.startPopup.style.display = 'flex';
	      this.startTitle = '';
	      this.startText = '';
	    }
	  }, {
	    key: "closeStartPopup",
	    value: function closeStartPopup() {
	      main_core.Dom.remove(this.getStartPopup());
	      delete this.startPopup;
	    }
	  }, {
	    key: "getStartPopup",
	    value: function getStartPopup() {
	      if (!this.startPopup) {
	        this.startPopup = main_core.Tag.render(_templateObject3(), main_core.Loc.getMessage('JS_UI_TUTOR_TITLE'), this.startTitle, this.startText, this.getBeginBtn(), this.getDeferBtn());
	        this.emit('onCreateStartPopupNode');
	        main_core.Dom.append(this.startPopup, document.body);
	        this.emit('onAfterAppendStartPopupNode');
	      }

	      return this.startPopup;
	    }
	  }, {
	    key: "getBeginBtn",
	    value: function getBeginBtn() {
	      var _this4 = this;

	      if (!this.beginBtn) {
	        this.beginBtn = main_core.Tag.render(_templateObject4(), main_core.Loc.getMessage('JS_UI_TUTOR_BTN_BEGIN'));
	        main_core.Event.bind(this.beginBtn, "click", function () {
	          _this4.emit('clickBeginBtn');
	        });
	      }

	      return this.beginBtn;
	    }
	  }, {
	    key: "getDeferBtn",
	    value: function getDeferBtn() {
	      var _this5 = this;

	      if (!this.deferBtn) {
	        this.deferBtn = main_core.Tag.render(_templateObject5(), main_core.Loc.getMessage('JS_UI_TUTOR_CLOSE_POPUP_BTN'));
	        main_core.Event.bind(this.deferBtn, "click", function () {
	          _this5.emit('clickDeferBtn');
	        });
	      }

	      return this.deferBtn;
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "getFullEventName",
	    value: function getFullEventName(shortName) {
	      return shortName;
	    }
	    /**
	     * @public
	     */

	  }, {
	    key: "getInformer",
	    value: function getInformer() {
	      if (!this.informer) {
	        this.informer = main_core.Tag.render(_templateObject6());
	        var informerParentNode = this.getImButton();

	        if (this.isCollapsedShow) {
	          informerParentNode = this.getCollapseBlock();
	        }

	        if (informerParentNode) {
	          main_core.Dom.append(this.informer, informerParentNode);
	        }
	      }

	      return this.informer;
	    }
	  }, {
	    key: "setCount",
	    value: function setCount(num) {
	      this.emit('onBeforeSetCount');

	      if (num < 1) {
	        this.removeInformer();
	        delete this.informer;
	        this.isInformerShow = false;
	      } else {
	        this.getInformer().textContent = num;
	        this.isInformerShow = true;
	      }

	      this.emit('onAfterSetCount');
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "removeInformer",
	    value: function removeInformer() {
	      if (this.isInformerShow) {
	        main_core.Dom.remove(this.getInformer());
	      }
	    }
	    /**
	     * @public
	     * @returns {HTMLElement}
	     */

	  }, {
	    key: "showCollapsedBlock",
	    value: function showCollapsedBlock(step, withGuide, showAfterAnimation) {
	      withGuide = withGuide !== false;
	      showAfterAnimation = showAfterAnimation !== false;
	      this.emit('onBeforeShowCollapsedBlock');

	      if (!this.isCollapsedShow) {
	        this.emit('onStartShowCollapsedBlock');

	        if (!(step instanceof Step)) {
	          step = new Step(step);
	        }

	        this.collapsedStep = step;
	        var collapsedBlock = this.getCollapseBlock();

	        var showFunction = function showFunction() {
	          collapsedBlock.style.display = 'flex';
	        };

	        if (showAfterAnimation) {
	          setTimeout(showFunction.bind(this), 300);
	        } else {
	          showFunction.call(this);
	        }

	        this.getCollapseTitle().innerHTML = step.getTitle();

	        if (this.isInformerShow) {
	          main_core.Dom.append(this.getInformer(), collapsedBlock);
	        }

	        this.isCollapsedShow = true;
	        this.emit('onShowCollapsedBlock');
	      }

	      if (withGuide) {
	        this.showGuide();
	      } else {
	        this.checkButtonsState();
	      }
	    }
	  }, {
	    key: "setCollapsedInvisible",
	    value: function setCollapsedInvisible() {
	      this.hideNode(this.getCollapseBlock());
	    }
	  }, {
	    key: "setCollapsedVisible",
	    value: function setCollapsedVisible() {
	      this.showNode(this.getCollapseBlock());
	    }
	  }, {
	    key: "checkButtonsState",
	    value: function checkButtonsState() {
	      this.emit('onCheckButtonsState');
	      var step = this.collapsedStep;

	      if (!step) {
	        return;
	      }

	      if (step.getCompleted()) {
	        if (this.activeGuide) {
	          this.hideNode(this.getRepeatBtn());
	        } else {
	          this.showNode(this.getRepeatBtn());
	        }

	        this.hideNode(this.getCompletedBtn());
	        this.hideNode(this.getStartBtn());
	      } else if (step.isActive) {
	        this.showNode(this.getCompletedBtn());

	        if (this.activeGuide || !this.isShowRepeatWithCompleted) {
	          this.hideNode(this.getRepeatBtn());
	        } else {
	          this.showNode(this.getRepeatBtn());
	        }

	        this.hideNode(this.getStartBtn());
	      } else {
	        this.showNode(this.getStartBtn());
	        this.hideNode(this.getRepeatBtn());
	        this.hideNode(this.getCompletedBtn());
	      }
	    }
	  }, {
	    key: "showGuide",
	    value: function showGuide() {
	      this.emit('onBeforeShowGuide');
	      var step = this.collapsedStep;

	      if (!this.activeGuide && step) {
	        this.emit('onStartShowGuide');
	        this.activeGuide = new ui_tour.Guide({
	          simpleMode: true,
	          steps: [step.getHighlightOptions()]
	        });
	        this.activeGuide.subscribe(ui_tour.Guide.getFullEventName("onFinish"), this.finishGuide.bind(this));
	        this.activeGuide.start();
	        main_core.Dom.remove(this.activeGuide.getPopup().closeIcon);
	        this.emit('showCollapseWithGuide');
	        this.checkButtonsState();
	      }
	    }
	  }, {
	    key: "closeGuide",
	    value: function closeGuide() {
	      if (this.activeGuide instanceof ui_tour.Guide) {
	        this.activeGuide.close();
	        this.emit('onAfterGuide');
	      }
	    }
	    /**
	     * @public
	     * @returns {HTMLElement}
	     */

	  }, {
	    key: "getCollapseBlock",
	    value: function getCollapseBlock() {
	      if (!this.layout.collapseBlock) {
	        this.layout.collapseBlock = main_core.Tag.render(_templateObject7(), this.clickCollapseBlockHandler.bind(this), main_core.Loc.getMessage('JS_UI_TUTOR_STEP_TITLE'), this.getCollapseTitle(), this.getStartBtn(), this.getRepeatBtn(), this.getCompletedBtn());
	        this.emit('onCreateCollapsedBlockNode');
	        main_core.Dom.append(this.layout.collapseBlock, document.body);
	        this.emit('onAfterAppendCollapsedBlockNode');
	      }

	      return this.layout.collapseBlock;
	    }
	    /**
	     * @public
	     * @returns {HTMLElement}
	     */

	  }, {
	    key: "getStartBtn",
	    value: function getStartBtn() {
	      var _this6 = this;

	      if (!this.startBtn) {
	        this.startBtn = main_core.Tag.render(_templateObject8(), main_core.Loc.getMessage('JS_UI_TUTOR_BTN_START'));
	        main_core.Event.bind(this.startBtn, "click", function (event) {
	          event.stopPropagation();

	          _this6.emit('clickStartBtn');
	        });
	      }

	      return this.startBtn;
	    }
	    /**
	     * @public
	     * @returns {HTMLElement}
	     */

	  }, {
	    key: "getRepeatBtn",
	    value: function getRepeatBtn() {
	      var _this7 = this;

	      if (!this.repeatBtn) {
	        this.repeatBtn = main_core.Tag.render(_templateObject9(), main_core.Loc.getMessage('JS_UI_TUTOR_BTN_REPEAT'));
	        main_core.Event.bind(this.repeatBtn, "click", function (event) {
	          event.stopPropagation();

	          _this7.emit('clickRepeatBtn');
	        });
	      }

	      return this.repeatBtn;
	    }
	    /**
	     * @public
	     * @returns {HTMLElement}
	     */

	  }, {
	    key: "getCompletedBtn",
	    value: function getCompletedBtn() {
	      var _this8 = this;

	      if (!this.completedBtn) {
	        this.completedBtn = main_core.Tag.render(_templateObject10(), main_core.Loc.getMessage('JS_UI_TUTOR_BTN_COMPLETED_SHORT'));
	        main_core.Event.bind(this.completedBtn, "click", function (event) {
	          event.stopPropagation();

	          _this8.emit('clickCompletedBtn');
	        });
	      }

	      return this.completedBtn;
	    }
	    /**
	     * @public
	     * @returns {HTMLElement}
	     */

	  }, {
	    key: "getCollapseTitle",
	    value: function getCollapseTitle() {
	      if (!this.layout.collapseTitle) {
	        this.layout.collapseTitle = main_core.Tag.render(_templateObject11());
	      }

	      return this.layout.collapseTitle;
	    }
	    /**
	     * @public
	     * @returns {HTMLElement}
	     */

	  }, {
	    key: "closeCollapsePopup",
	    value: function closeCollapsePopup(event) {
	      this.closeCollapseEntity();
	      this.emit('clickCloseCollapseBlock');
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "clickCollapseBlockHandler",
	    value: function clickCollapseBlockHandler() {
	      this.emit('clickCollapseBlock');
	    }
	  }, {
	    key: "finishGuide",
	    value: function finishGuide() {
	      delete this.activeGuide;
	      this.checkButtonsState();
	      this.emit('completeCloseGuide');
	    }
	  }, {
	    key: "closeCollapseEntity",
	    value: function closeCollapseEntity() {
	      this.emit('onBeforeHideCollapsedBlock');
	      this.getCollapseBlock().style.display = 'none';
	      this.getImButton().style.display = 'block';

	      if (this.activeGuide instanceof ui_tour.Guide) {
	        this.activeGuide.close();
	      }

	      if (this.isInformerShow) {
	        main_core.Dom.append(this.getInformer(), this.getImButton());
	      }

	      delete this.collapsedStep;
	      this.isCollapsedShow = false;
	      this.emit('onHideCollapsedBlock');
	    }
	  }, {
	    key: "showLoader",
	    value: function showLoader() {
	      this.emit('onBeforeShowLoader');
	      this.startTitle = '';
	      this.startText = '';
	      this.layout.loader = new main_loader.Loader({
	        target: this.getStartPopup(),
	        size: 85
	      });
	      this.layout.loader.show();
	      this.getStartPopup().style.display = 'flex';
	      main_core.Dom.addClass(this.getStartPopup(), "ui-tutor-popup-load");
	      this.emit('onAfterShowLoader');
	    }
	  }, {
	    key: "hideLoader",
	    value: function hideLoader() {
	      if (this.layout.loader) {
	        this.layout.loader.destroy();
	        this.getStartPopup().style.display = 'none';
	      }
	    }
	  }, {
	    key: "showCollapsedLoader",
	    value: function showCollapsedLoader() {
	      this.emit('onBeforeShowCollapsedLoader');
	      this.layout.collapseLoader = new main_loader.Loader({
	        target: this.getCollapseBlock(),
	        size: 34
	      });
	      this.layout.collapseLoader.show();
	      this.getCollapseBlock().style.display = 'flex';
	      main_core.Dom.addClass(this.getCollapseBlock(), "ui-tutor-popup-collapse-load");
	      this.emit('onAfterShowCollapsedLoader');
	    }
	  }, {
	    key: "hideCollapsedLoader",
	    value: function hideCollapsedLoader() {
	      this.emit('onBeforeHideCollapsedLoader');

	      if (this.layout.collapseLoader) {
	        this.layout.collapseLoader.destroy();
	        main_core.Dom.removeClass(this.getCollapseBlock(), "ui-tutor-popup-collapse-load");
	        this.getCollapseBlock().style.display = 'none';
	      }

	      this.emit('onAfterHideCollapsedLoader');
	    }
	  }, {
	    key: "showNode",
	    value: function showNode(node) {
	      node.style.display = 'block';
	    }
	  }, {
	    key: "hideNode",
	    value: function hideNode(node) {
	      node.style.display = 'none';
	    }
	  }, {
	    key: "checkFollowLink",
	    value: function checkFollowLink(step, scenario) {
	      this.emit('onStartCheckFollowLink');
	      step = step || this.collapsedStep;

	      if (step instanceof Step) {
	        scenario = scenario || {};

	        if (!(window.location.pathname === step.getUrl())) {
	          var beforeEvent = 'onBeforeRedirectToActionPage';

	          if (scenario instanceof Scenario) {
	            main_core.Dom.addClass(scenario.getStartBtn(), 'ui-btn-wait');
	            scenario.fireCurrentStepEvent(beforeEvent);
	          } else {
	            main_core.Dom.addClass(this.getStartBtn(), 'ui-btn-wait');
	            this.emit(beforeEvent, {
	              step: step
	            });
	          }

	          window.location = step.getUrl();
	        } else {
	          if (scenario instanceof Scenario) {
	            scenario.showCollapseBlock(step);
	          } else {
	            step.activate();
	            this.showCollapsedBlock(step);
	          }
	        }
	      }

	      this.emit('onFinishCheckFollowLink');
	    }
	  }, {
	    key: "fireEvent",
	    value: function fireEvent(eventName) {
	      this.emit(eventName);
	    }
	  }]);
	  return Manager;
	}(main_core.Event.EventEmitter);
	/**
	 * @private
	 */

	Manager.instance = null;
	Manager.scenarioInstance = null;
	Manager.activeGuide = null;
	Manager.isShowRepeatWithCompleted = true;
	Manager.layout = {
	  imButton: null,
	  collapseBlock: null,
	  collapseTitle: null
	};

	function _templateObject29() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"ui-tutor-popup-arrow ui-tutor-popup-arrow-next\"></div>\n\t\t\t\t"]);

	  _templateObject29 = function _templateObject29() {
	    return data;
	  };

	  return data;
	}

	function _templateObject28() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"ui-tutor-popup-arrow ui-tutor-popup-arrow-prev\"></div>\n\t\t\t\t"]);

	  _templateObject28 = function _templateObject28() {
	    return data;
	  };

	  return data;
	}

	function _templateObject27() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"ui-tutor-popup-new-text\">", "</div>\n\t\t\t\t"]);

	  _templateObject27 = function _templateObject27() {
	    return data;
	  };

	  return data;
	}

	function _templateObject26() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"ui-tutor-popup-finished-notice\">", "</div>\n\t\t\t\t"]);

	  _templateObject26 = function _templateObject26() {
	    return data;
	  };

	  return data;
	}

	function _templateObject25() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"ui-tutor-popup-finished\">\n\t\t\t\t\t\t<div class=\"ui-tutor-popup-finished-title\">", "</div>\n\t\t\t\t\t\t<div class=\"ui-tutor-popup-finished-icon\"></div>\n\t\t\t\t\t\t<div class=\"ui-tutor-popup-finished-text\">", "</div>\n\t\t\t\t\t</div>\n\t\t\t\t"]);

	  _templateObject25 = function _templateObject25() {
	    return data;
	  };

	  return data;
	}

	function _templateObject24() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<span class=\"ui-tutor-popup-help-link\" onclick=\"", "\">\n\t\t\t\t\t\t<span class=\"ui-tutor-popup-help-link-text\">", "</span>\n\t\t\t\t\t</span>\n\t\t\t\t"]);

	  _templateObject24 = function _templateObject24() {
	    return data;
	  };

	  return data;
	}

	function _templateObject23() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"ui-tutor-popup-video\" data-step=", "></div>\n\t\t\t\t"]);

	  _templateObject23 = function _templateObject23() {
	    return data;
	  };

	  return data;
	}

	function _templateObject22() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"ui-tutor-popup-help\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t"]);

	  _templateObject22 = function _templateObject22() {
	    return data;
	  };

	  return data;
	}

	function _templateObject21() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t<span class=\"ui-tutor-popup-step-item\" data-step=", " onclick=\"", "\">\n\t\t\t\t\t\t\t<span class=\"ui-tutor-popup-step-item-number\">", "</span>\n\t\t\t\t\t\t</span>\n\t\t\t\t\t"]);

	  _templateObject21 = function _templateObject21() {
	    return data;
	  };

	  return data;
	}

	function _templateObject20() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"ui-tutor-popup-step-list\"></div>\n\t\t\t\t"]);

	  _templateObject20 = function _templateObject20() {
	    return data;
	  };

	  return data;
	}

	function _templateObject19() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<a class=\"ui-tutor-popup-support-link\" onclick=\"", "\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</a>\n\t\t\t\t"]);

	  _templateObject19 = function _templateObject19() {
	    return data;
	  };

	  return data;
	}

	function _templateObject18() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"ui-tutor-popup-step-decs\"></div>\n\t\t\t\t"]);

	  _templateObject18 = function _templateObject18() {
	    return data;
	  };

	  return data;
	}

	function _templateObject17() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"ui-tutor-popup-step-title\"></div>\n\t\t\t\t"]);

	  _templateObject17 = function _templateObject17() {
	    return data;
	  };

	  return data;
	}

	function _templateObject16() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"ui-tutor-popup-completed\">\n\t\t\t\t\t\t<div class=\"ui-tutor-popup-completed-icon\"></div>\n\t\t\t\t\t\t<div class=\"ui-tutor-popup-completed-text\">", "</div>\n\t\t\t\t\t</div>\n\t\t\t\t"]);

	  _templateObject16 = function _templateObject16() {
	    return data;
	  };

	  return data;
	}

	function _templateObject15() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<button class=\"ui-btn ui-btn-link ui-btn-round\" onclick=\"", "\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</button>\n\t\t\t\t"]);

	  _templateObject15 = function _templateObject15() {
	    return data;
	  };

	  return data;
	}

	function _templateObject14() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<button class=\"ui-btn ui-btn-primary ui-btn-round\" onclick=\"", "\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</button>\n\t\t\t\t"]);

	  _templateObject14 = function _templateObject14() {
	    return data;
	  };

	  return data;
	}

	function _templateObject13() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<button class=\"ui-btn ui-btn-success ui-btn-round\" onclick=\"", "\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</button>\n\t\t\t\t"]);

	  _templateObject13 = function _templateObject13() {
	    return data;
	  };

	  return data;
	}

	function _templateObject12() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<button class=\"ui-btn ui-btn-primary ui-btn-round\" onclick=\"", "\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</button>\n\t\t\t\t"]);

	  _templateObject12 = function _templateObject12() {
	    return data;
	  };

	  return data;
	}

	function _templateObject11$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<span class=\"ui-tutor-popup-defer-link\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</span>\n\t\t\t\t"]);

	  _templateObject11$1 = function _templateObject11() {
	    return data;
	  };

	  return data;
	}

	function _templateObject10$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<span class=\"ui-tutor-popup-header-counter-number\"></span>\n\t\t\t\t"]);

	  _templateObject10$1 = function _templateObject10() {
	    return data;
	  };

	  return data;
	}

	function _templateObject9$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<span class=\"ui-tutor-popup-header-counter-step\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</span>\n\t\t\t\t"]);

	  _templateObject9$1 = function _templateObject9() {
	    return data;
	  };

	  return data;
	}

	function _templateObject8$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<span class=\"ui-tutor-popup-nav-item ui-tutor-popup-nav-item-next\" onclick=\"", "\"></span>\n\t\t\t\t"]);

	  _templateObject8$1 = function _templateObject8() {
	    return data;
	  };

	  return data;
	}

	function _templateObject7$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<span class=\"ui-tutor-popup-nav-item ui-tutor-popup-nav-item-prev\" onclick=\"", "\"></span>\n\t\t\t\t"]);

	  _templateObject7$1 = function _templateObject7() {
	    return data;
	  };

	  return data;
	}

	function _templateObject6$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"ui-tutor-popup-nav\"></div>\n\t\t\t\t"]);

	  _templateObject6$1 = function _templateObject6() {
	    return data;
	  };

	  return data;
	}

	function _templateObject5$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"ui-tutor-popup-btn\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t"]);

	  _templateObject5$1 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"ui-tutor-popup-content-inner\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t"]);

	  _templateObject4$1 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"ui-tutor-popup-footer\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t"]);

	  _templateObject3$1 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"ui-tutor-popup-content-block\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t"]);

	  _templateObject2$1 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"ui-tutor-popup ui-tutor-popup-step\">\n\t\t\t\t\t\t<div class=\"ui-tutor-popup-header\">\n\t\t\t\t\t\t\t<span class=\"ui-tutor-popup-header-icon\"></span>\n\t\t\t\t\t\t\t<span class=\"ui-tutor-popup-header-title\">\n\t\t\t\t\t\t\t\t<span class=\"ui-tutor-popup-header-counter\">\n\t\t\t\t\t\t\t\t\t", ".\n\t\t\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t\t</span>\n\t\t\t\t\t\t\t\t<span class=\"ui-tutor-popup-header-subtitle\">", "</span>\n\t\t\t\t\t\t\t</span>\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"ui-tutor-popup-content\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"ui-tutor-popup-step-wrap\">\n\t\t\t\t\t\t\t<div class=\"ui-tutor-popup-step-inner\">\n\t\t\t\t\t\t\t\t<div class=\"ui-tutor-popup-arrow-wrap\"></div>\n\t\t\t\t\t\t\t\t<div class=\"ui-tutor-popup-step-list-wrap\">\n\t\t\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"ui-tutor-popup-icon-close\" onclick=\"", "\"></div>\n\t\t\t\t\t\t<div class=\"ui-tutor-popup-icon-angle\"></div>\n\t\t\t\t\t</div>\n\t\t\t\t"]);

	  _templateObject$1 = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var Scenario =
	/*#__PURE__*/
	function (_Event$EventEmitter) {
	  babelHelpers.inherits(Scenario, _Event$EventEmitter);

	  function Scenario() {
	    var _this;

	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, Scenario);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Scenario).call(this));

	    _this.setEventNamespace('BX.UI.Tutor.Scenario');

	    _this.stepPopup = null;
	    _this.arrowTimer = null;
	    _this.guide = null;
	    _this.loader = null;
	    _this.arrowWrap = null;
	    _this.prevArrow = null;
	    _this.nextArrow = null;
	    _this.currentStepIndex = 0;
	    _this.currentStep = null;
	    _this.isAddedSteps = false;
	    _this.hasArrows = false;
	    _this.isLoading = true;

	    _this.setOptions(options);

	    _this.btn = document.getElementById('ui-tutor-btn-wrap');
	    _this.informer = document.getElementById('ui-tutor-informer');
	    _this.layout = {
	      stepBlock: null,
	      progress: null,
	      counter: null,
	      counterContainer: null,
	      title: null,
	      description: null,
	      collapseBlock: null,
	      collapseTitle: null,
	      collapseDescription: null,
	      content: null,
	      contentInner: null,
	      contentBlock: null,
	      url: null,
	      target: null,
	      startBtn: null,
	      nextBtn: null,
	      repeatBtn: null,
	      deferBtn: null,
	      help: null,
	      completedBtn: null,
	      completedBlock: null,
	      finishedBlock: null,
	      supportLink: null
	    };
	    _this.sections = ['settings', 'scenario', 'work'];

	    _this.loadYoutubeApiScript();

	    _this.subscribe("onYouTubeReady", function () {
	      _this.setVideoItems();
	    });

	    return _this;
	  }

	  babelHelpers.createClass(Scenario, [{
	    key: "loadYoutubeApiScript",
	    value: function loadYoutubeApiScript() {
	      var onYouTubeReadyEvent = function () {
	        this.emit("onYouTubeReady", {
	          scenario: this
	        });
	      }.bind(this);

	      if (!window.YT) {
	        var isNeedCheckYT = true;
	        var tag = document.createElement('script');
	        tag.src = "https://www.youtube.com/iframe_api";
	        var firstScriptTag = document.getElementsByTagName('script')[0];
	        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
	        var ytCheckerTimer = setInterval(function () {
	          if (isNeedCheckYT) {
	            if (window.YT && main_core.Type.isFunction(window.YT.Player)) {
	              clearInterval(ytCheckerTimer);
	              onYouTubeReadyEvent();
	            }
	          }
	        }, 200);
	        setTimeout(function () {
	          clearInterval(ytCheckerTimer);
	          isNeedCheckYT = false;
	        }, 2000);
	      } else {
	        setTimeout(function () {
	          onYouTubeReadyEvent();
	        }.bind(this), 100);
	      }
	    }
	  }, {
	    key: "setOptions",
	    value: function setOptions(options) {
	      var _this2 = this;

	      this.fireCurrentStepEvent('onBeforeSetOptions', false);
	      options = main_core.Type.isPlainObject(options) ? options : {};
	      var currentStep = this.getCurrentStep();
	      /** @var {Step[]} */

	      this.steps = [];

	      if (Array.isArray(options.steps)) {
	        options.steps.forEach(function (step) {
	          _this2.steps.push(new Step(step));
	        });
	      }

	      if (currentStep instanceof Step) {
	        var stepInList = this.findStepById(currentStep.getId());

	        if (stepInList) {
	          currentStep = stepInList;
	        }
	      } else if (main_core.Type.isString(options.currentStepId) && options.currentStepId.length > 0) {
	        var _stepInList = this.findStepById(options.currentStepId);

	        if (_stepInList) {
	          currentStep = _stepInList;

	          if (options.currentStepIsActive === true) {
	            currentStep.activate();
	          }
	        }
	      }

	      if (!currentStep) {
	        var uncompletedStep = this.getFirstUncompletedStep();

	        if (uncompletedStep) {
	          currentStep = uncompletedStep;
	        }
	      }

	      if (!currentStep && this.steps && this.steps[0]) {
	        currentStep = this.steps[0];
	      }

	      this.setCurrentStep(currentStep);

	      if (options) {
	        this.isLoading = false;
	      }

	      this.title = options.title || '';
	      this.supportLink = options.supportLink || '';
	      this.isFinished = options.isFinished || false;
	      this.fireCurrentStepEvent('onAfterSetOptions', false);
	    }
	    /**
	     * @param {Step} step
	     */

	  }, {
	    key: "setCurrentStep",
	    value: function setCurrentStep(step) {
	      if (step instanceof Step) {
	        this.currentStep = step;
	        var steps = this.steps;

	        if (main_core.Type.isArray(steps)) {
	          this.currentStepIndex = steps.indexOf(step);
	        }

	        this.fireCurrentStepEvent('onStartStep');
	      }
	    }
	    /**
	     * @public
	     */

	  }, {
	    key: "start",
	    value: function start(complexAnimation) {
	      this.emit("onStart", {
	        scenario: this
	      });

	      if (complexAnimation) // animate transition from collapsed popup to step popup
	        {
	          this.complexAnimation = true;
	        }

	      this.showPopup(this.getStepPopup());
	      this.toggleCompletedState();
	      this.toggleNavBtn();
	      this.setPopupData();

	      if (this.isAddedSteps) {
	        this.hideFinalState();
	      }

	      if (!this.hasArrows) {
	        this.initArrows();
	      }

	      this.complexAnimation = false;
	      this.fireCurrentStepEvent('onShowComplete');
	    }
	  }, {
	    key: "findStepById",
	    value: function findStepById(stepId) {
	      for (var i = 0; i < this.steps.length; i++) {
	        var step = this.steps[i];

	        if (step.getId() === stepId) {
	          return step;
	        }
	      }

	      return null;
	    }
	    /**
	     * @public
	     * @returns {HTMLElement}
	     */

	  }, {
	    key: "getStepPopup",
	    value: function getStepPopup() {
	      var _this3 = this;

	      var clickOnCloseIcon = function clickOnCloseIcon() {
	        _this3.emit("onClickOnCloseIcon", {
	          scenario: _this3
	        });
	      };

	      if (!this.stepPopup) {
	        this.stepPopup = main_core.Tag.render(_templateObject$1(), main_core.Loc.getMessage('JS_UI_TUTOR_TITLE'), this.getCounterContainer(), this.title, this.getDeferLink(), this.getContentBlock(), this.getStepBlock(), clickOnCloseIcon.bind(this));
	        this.fireCurrentStepEvent('onCreateStepPopupNode');
	        main_core.Dom.append(this.stepPopup, document.body);
	        this.fireCurrentStepEvent('onAfterAppendStepPopupNode');
	      }

	      return this.stepPopup;
	    }
	    /**
	     * @public
	     * @returns {HTMLElement}
	     */

	  }, {
	    key: "getContentBlock",
	    value: function getContentBlock() {
	      if (!this.layout.contentBlock) {
	        this.layout.contentBlock = main_core.Tag.render(_templateObject2$1(), this.getContentInner(), this.getFooter());
	      }

	      return this.layout.contentBlock;
	    }
	    /**
	     * @public
	     * @returns {HTMLElement}
	     */

	  }, {
	    key: "getFooter",
	    value: function getFooter() {
	      if (!this.layout.footer) {
	        this.layout.footer = main_core.Tag.render(_templateObject3$1(), this.getNavigation(), this.getBtnContainer());

	        if (Manager.getInstance().feedbackFormId) {
	          main_core.Dom.append(this.getSupportLink(), this.layout.footer);
	        }
	      }

	      return this.layout.footer;
	    }
	    /**
	     * @public
	     * @returns {HTMLElement}
	     */

	  }, {
	    key: "getContentInner",
	    value: function getContentInner() {
	      if (!this.layout.contentInner) {
	        this.layout.contentInner = main_core.Tag.render(_templateObject4$1(), this.getTitle(), this.getDescription(), this.getHelpBlock());
	      }

	      return this.layout.contentInner;
	    }
	    /**
	     * @public
	     * @returns {HTMLElement}
	     */

	  }, {
	    key: "getBtnContainer",
	    value: function getBtnContainer() {
	      if (!this.layout.btnContainer) {
	        this.layout.btnContainer = main_core.Tag.render(_templateObject5$1(), this.getStartBtn(), this.getRepeatBtn(), this.getCompletedBtn());
	      }

	      return this.layout.btnContainer;
	    }
	    /**
	     * @public
	     * @returns {HTMLElement}
	     */

	  }, {
	    key: "getNavigation",
	    value: function getNavigation() {
	      if (!this.layout.navigation) {
	        this.layout.navigation = main_core.Tag.render(_templateObject6$1());
	        this.layout.backBtn = main_core.Tag.render(_templateObject7$1(), this.clickOnBackBtn.bind(this));
	        this.layout.nextBtn = main_core.Tag.render(_templateObject8$1(), this.clickOnNextBtn.bind(this));
	        main_core.Dom.append(this.layout.backBtn, this.layout.navigation);
	        main_core.Dom.append(this.layout.nextBtn, this.layout.navigation);
	      }

	      return this.layout.navigation;
	    }
	    /**
	     * @private
	     * @param {HTMLElement} node
	     */

	  }, {
	    key: "setInformer",
	    value: function setInformer(node) {
	      this.setInformerCount(this.steps.length - this.getCompletedSteps());
	    }
	    /**
	     * @public
	     * @param {Number} num
	     */

	  }, {
	    key: "setInformerExternal",
	    value: function setInformerExternal(num) {
	      this.setInformerCount(num);
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "setInformerCount",
	    value: function setInformerCount(num) {
	      Manager.setCount(num);
	    }
	    /**
	     * @public
	     * @param {Event} event
	     * @param {Boolean} complexAnimation
	     */

	  }, {
	    key: "closeStepPopup",
	    value: function closeStepPopup(event, complexAnimation) {
	      if (!this.stepPopup) {
	        return;
	      }

	      if (event) {
	        event.stopPropagation();
	      }

	      this.fireCurrentStepEvent('onCloseStepPopup');

	      if (complexAnimation) // animate transition from collapsed popup to step popup
	        {
	          this.complexAnimation = true;
	        }

	      this.fadeAnimation(this.getStepPopup());
	      setTimeout(function () {
	        this.hideNode(this.getStepPopup());
	      }.bind(this), 310);
	      this.complexAnimation = false;
	    }
	    /**
	     * @public
	     * @returns {number}
	     */

	  }, {
	    key: "getCompletedSteps",
	    value: function getCompletedSteps() {
	      var total = 0;

	      for (var i = 0; i < this.steps.length; i += 1) {
	        if (this.steps[i].isCompleted) {
	          total += 1;
	        }
	      }

	      return total;
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "setStepCounter",
	    value: function setStepCounter() {
	      this.getCounter().textContent = main_core.Loc.getMessage('JS_UI_TUTOR_COUNTER_NUMBER').replace('#NUMBER#', this.steps.indexOf(this.getCurrentStep()) + 1).replace('#NUMBER_TOTAL#', this.steps.length);
	    }
	    /**
	     * @public
	     * @returns {HTMLElement}
	     */

	  }, {
	    key: "getCounterContainer",
	    value: function getCounterContainer() {
	      if (!this.layout.counterContainer) {
	        this.layout.counterContainer = main_core.Tag.render(_templateObject9$1(), this.getCounter());
	      }

	      return this.layout.counterContainer;
	    }
	    /**
	     * @public
	     * @returns {HTMLElement}
	     */

	  }, {
	    key: "getCounter",
	    value: function getCounter() {
	      if (!this.layout.counter) {
	        this.layout.counter = main_core.Tag.render(_templateObject10$1());
	      }

	      return this.layout.counter;
	    }
	    /**
	     * @public
	     * @returns {HTMLElement}
	     */

	  }, {
	    key: "getDeferLink",
	    value: function getDeferLink() {
	      if (!this.layout.deferLink) {
	        this.layout.deferLink = main_core.Tag.render(_templateObject11$1(), main_core.Loc.getMessage('JS_UI_TUTOR_BTN_DEFER'));
	        var deferMenu = new BX.PopupMenuWindow({
	          angle: true,
	          offsetLeft: 15,
	          className: 'ui-tutor-popup-defer-menu',
	          bindElement: this.layout.deferLink,
	          items: [{
	            text: main_core.Loc.getMessage('JS_UI_TUTOR_DEFER_MENU_HOUR'),
	            onclick: function () {
	              this.emit("onDeferOneHour", {
	                scenario: this
	              });
	              deferMenu.close();
	            }.bind(this)
	          }, {
	            text: main_core.Loc.getMessage('JS_UI_TUTOR_DEFER_MENU_TOMORROW'),
	            onclick: function () {
	              this.emit("onDeferTomorrow", {
	                scenario: this
	              });
	              deferMenu.close();
	            }.bind(this)
	          }, {
	            text: main_core.Loc.getMessage('JS_UI_TUTOR_DEFER_MENU_WEEK'),
	            onclick: function () {
	              this.emit("onDeferWeek", {
	                scenario: this
	              });
	              deferMenu.close();
	            }.bind(this)
	          }, {
	            text: main_core.Loc.getMessage('JS_UI_TUTOR_DEFER_MENU_FOREVER'),
	            onclick: function () {
	              this.emit("onDeferForever", {
	                scenario: this
	              });
	              deferMenu.close();
	            }.bind(this)
	          }]
	        });
	        main_core.Event.bind(this.layout.deferLink, "click", function () {
	          deferMenu.show();
	        });
	      }

	      return this.layout.deferLink;
	    }
	    /**
	     * @public
	     * @returns {HTMLElement}
	     */

	  }, {
	    key: "getStartBtn",
	    value: function getStartBtn() {
	      if (!this.layout.startBtn) {
	        this.layout.startBtn = main_core.Tag.render(_templateObject12(), this.clickStartHandler.bind(this), main_core.Loc.getMessage('JS_UI_TUTOR_BTN_START'));
	      }

	      return this.layout.startBtn;
	    }
	    /**
	     * @public
	     * @returns {HTMLElement}
	     */

	  }, {
	    key: "getCompletedBtn",
	    value: function getCompletedBtn() {
	      if (!this.layout.completedBtn) {
	        this.layout.completedBtn = main_core.Tag.render(_templateObject13(), this.showSuccessState.bind(this), main_core.Loc.getMessage('JS_UI_TUTOR_BTN_COMPLETED'));
	      }

	      return this.layout.completedBtn;
	    }
	    /**
	     * @public
	     * @returns {HTMLElement}
	     */

	  }, {
	    key: "getRepeatBtn",
	    value: function getRepeatBtn() {
	      if (!this.layout.repeatBtn) {
	        this.layout.repeatBtn = main_core.Tag.render(_templateObject14(), this.repeatStep.bind(this), main_core.Loc.getMessage('JS_UI_TUTOR_BTN_REPEAT'));
	      }

	      return this.layout.repeatBtn;
	    }
	    /**
	     * @public
	     * @returns {HTMLElement}
	     */

	  }, {
	    key: "getDeferBtn",
	    value: function getDeferBtn() {
	      if (!this.layout.deferBtn) {
	        this.layout.deferBtn = main_core.Tag.render(_templateObject15(), this.closeStepPopup.bind(this), main_core.Loc.getMessage('JS_UI_TUTOR_BTN_DEFER'));
	      }

	      return this.layout.deferBtn;
	    }
	    /**
	     * @public
	     * @returns {HTMLElement}
	     */

	  }, {
	    key: "getCompletedBLock",
	    value: function getCompletedBLock() {
	      if (!this.layout.completedBlock) {
	        this.layout.completedBlock = main_core.Tag.render(_templateObject16(), main_core.Loc.getMessage('JS_UI_TUTOR_STEP_COMPLETED'));
	      }

	      return this.layout.completedBlock;
	    }
	    /**
	     * @public
	     * @returns {HTMLElement}
	     */

	  }, {
	    key: "getTitle",
	    value: function getTitle() {
	      if (!this.layout.title) {
	        this.layout.title = main_core.Tag.render(_templateObject17());
	      }

	      return this.layout.title;
	    }
	    /**
	     * @public
	     * @returns {HTMLElement}
	     */

	  }, {
	    key: "getDescription",
	    value: function getDescription() {
	      if (!this.layout.description) {
	        this.layout.description = main_core.Tag.render(_templateObject18());
	      }

	      return this.layout.description;
	    }
	    /**
	     * @public
	     * @returns {HTMLElement}
	     */

	  }, {
	    key: "getSupportLink",
	    value: function getSupportLink() {
	      if (!this.layout.supportLink) {
	        this.layout.supportLink = main_core.Tag.render(_templateObject19(), this.supportLinkHandler.bind(this), main_core.Loc.getMessage('JS_UI_TUTOR_BTN_SUPPORT'));
	      }

	      return this.layout.supportLink;
	    }
	  }, {
	    key: "setInvisible",
	    value: function setInvisible() {
	      this.hideNode(this.getStepPopup());
	    }
	  }, {
	    key: "setVisible",
	    value: function setVisible() {
	      this.showNode(this.getStepPopup());
	    }
	  }, {
	    key: "supportLinkHandler",
	    value: function supportLinkHandler() {
	      this.emit('supportLinkClick');
	      Manager.getInstance().showFeedbackForm();
	    }
	    /**
	     * @public
	     * @returns {HTMLElement}
	     */

	  }, {
	    key: "getStepBlock",
	    value: function getStepBlock() {
	      if (!this.layout.stepBlock) {
	        this.layout.stepBlock = main_core.Tag.render(_templateObject20());
	        this.layout.stepItems = [];

	        for (var i = 0; i < this.steps.length; i += 1) {
	          var currentStepIndex = main_core.Tag.render(_templateObject21(), i, this.switchStep.bind(this), i + 1);
	          this.layout.stepItems.push(currentStepIndex);
	          main_core.Dom.append(currentStepIndex, this.layout.stepBlock);
	        }

	        this.setStepItems();
	      }

	      return this.layout.stepBlock;
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "setStepItems",
	    value: function setStepItems() {
	      if (this.layout && this.layout.stepItems) {
	        for (var i = 0; i < this.steps.length; i += 1) {
	          if (this.layout.stepItems[i]) {
	            main_core.Dom.removeClass(this.layout.stepItems[i], 'ui-tutor-popup-step-item-current');

	            if (i === this.currentStepIndex) {
	              main_core.Dom.addClass(this.layout.stepItems[i], 'ui-tutor-popup-step-item-current');
	            }

	            if (this.steps[i].isCompleted) {
	              main_core.Dom.addClass(this.layout.stepItems[i], 'ui-tutor-popup-step-item-completed');
	            }
	          }
	        }
	      }
	    }
	    /**
	     * @public
	     * @returns {HTMLElement}
	     */

	  }, {
	    key: "getHelpBlock",
	    value: function getHelpBlock() {
	      if (!this.layout.help) {
	        this.layout.help = main_core.Tag.render(_templateObject22(), this.getHelpLink());
	      }

	      return this.layout.help;
	    }
	  }, {
	    key: "setVideoItems",
	    value: function setVideoItems() {
	      for (var i = 0; i < this.steps.length; i += 1) {
	        var currentVideo = main_core.Tag.render(_templateObject23(), i);
	        main_core.Dom.prepend(currentVideo, this.getHelpBlock());

	        if (window.YT && main_core.Type.isObject(window.YT) && main_core.Type.isFunction(window.YT.Player) && this.steps[i].video !== null) {
	          var playerData = {
	            videoId: this.steps[i].video,
	            events: {
	              'onReady': function (event) {
	                event.target.mute();
	                event.target.pauseVideo();
	                event.target.setPlaybackQuality('hd720');

	                if (+event.target.getIframe().getAttribute('data-step') === this.currentStepIndex) {
	                  main_core.Dom.addClass(event.target.getIframe(), 'ui-tutor-popup-video-show');
	                  event.target.playVideo();
	                }
	              }.bind(this)
	            },
	            playerVars: {
	              cc_load_policy: 1,
	              cc_lang_pref: 'ru',
	              rel: 0
	            }
	          };
	          this.fireCurrentStepEvent('onBeforeCreateVideo', true, {
	            playerData: playerData
	          });
	          this.steps[i].videoObj = new YT.Player(currentVideo, playerData);
	          this.fireCurrentStepEvent('onAfterCreateVideo');
	        }
	      }
	    }
	  }, {
	    key: "pauseCurrentVideo",
	    value: function pauseCurrentVideo() {
	      var step = this.getCurrentStep();

	      if (window.YT && step instanceof Step) {
	        var video = step.getVideoObj();

	        if (main_core.Type.isObject(video) && video.pauseVideo) {
	          video.pauseVideo();
	        }
	      }
	    }
	  }, {
	    key: "playCurrentVideo",
	    value: function playCurrentVideo() {
	      var step = this.getCurrentStep();

	      if (window.YT && step instanceof Step) {
	        var video = step.getVideoObj();

	        if (main_core.Type.isObject(video) && video.playVideo) {
	          video.playVideo();
	        }
	      }
	    }
	  }, {
	    key: "getHelpLink",
	    value: function getHelpLink() {
	      if (!this.layout.link) {
	        this.layout.link = main_core.Tag.render(_templateObject24(), this.handleClickLinkHandler.bind(this), main_core.Loc.getMessage('JS_UI_TUTOR_ARTICLE_HELP_TOPIC'));
	      }

	      return this.layout.link;
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "handleClickLinkHandler",
	    value: function handleClickLinkHandler() {
	      this.emit('helpLinkClick');
	    }
	    /**
	     * @public
	     * @param {HTMLElement} node
	     */

	  }, {
	    key: "showPopup",
	    value: function showPopup(node) {
	      this.showAnimation(node);
	      node.style.display = 'block';
	    }
	    /**
	     * @public
	     * @param {HTMLElement} node
	     */

	  }, {
	    key: "showNode",
	    value: function showNode(node) {
	      node.style.display = 'block';
	    }
	    /**
	     * @public
	     * @param {HTMLElement} node
	     */

	  }, {
	    key: "hideNode",
	    value: function hideNode(node) {
	      node.style.display = 'none';
	    }
	    /**
	     * @public
	     * @param {HTMLElement} node
	     */

	  }, {
	    key: "removePopup",
	    value: function removePopup(node) {
	      main_core.Dom.remove(node);
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "clickOnNextBtn",
	    value: function clickOnNextBtn() {
	      this.fireCurrentStepEvent('onBeforeClickNavNextBtn');

	      if (this.getCompletedSteps() === this.steps.length && !this.isFinished) {
	        this.isAddedSteps = false;
	        main_core.Dom.remove(this.getNewStepsSection());
	        main_core.Dom.removeClass(this.getFinishedBlock(), 'ui-tutor-popup-finished-new');
	        this.showFinalState();
	        return;
	      }

	      if (this.getCompletedSteps() === this.steps.length && this.currentStepIndex + 1 === this.steps.length) {
	        this.currentStepIndex = -1;
	      }

	      main_core.Dom.removeClass(this.getStartBtn(), 'ui-btn-wait');
	      this.increaseCurrentIndex();
	      this.showStep();
	      this.toggleNavBtn();
	      this.fireCurrentStepEvent('onAfterClickNavNextBtn');
	    }
	  }, {
	    key: "clickOnBackBtn",
	    value: function clickOnBackBtn() {
	      this.fireCurrentStepEvent('onBeforeClickNavBackBtn');
	      this.reduceCurrentIndex();
	      this.toggleNavBtn();
	      this.showStep();
	      this.fireCurrentStepEvent('onAfterClickNavBackBtn');
	    }
	  }, {
	    key: "toggleNavBtn",
	    value: function toggleNavBtn() {
	      main_core.Dom.removeClass(this.layout.backBtn, 'ui-tutor-popup-nav-item-disabled');
	      main_core.Dom.removeClass(this.layout.nextBtn, 'ui-tutor-popup-nav-item-disabled');

	      if (this.currentStepIndex === 0) {
	        main_core.Dom.addClass(this.layout.backBtn, 'ui-tutor-popup-nav-item-disabled');
	      }

	      if (this.currentStepIndex + 1 === this.steps.length) {
	        main_core.Dom.addClass(this.layout.nextBtn, 'ui-tutor-popup-nav-item-disabled');
	      }
	    }
	  }, {
	    key: "showStep",
	    value: function showStep() {
	      // when last step is completed, but some steps are not
	      if (this.clickOnCompletedBtn && this.currentStepIndex === this.steps.length) {
	        var nextUncompletedStep = this.getFirstUncompletedStep();

	        if (nextUncompletedStep) {
	          this.setCurrentStep(nextUncompletedStep);
	        }
	      }

	      this.scrollToStep();
	      this.toggleCompletedState();
	      this.setPopupData();
	      this.clickOnCompletedBtn = false;
	      this.fireCurrentStepEvent('onAfterShowStep');
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "switchStep",
	    value: function switchStep() {
	      this.fireCurrentStepEvent('onBeforeSwitchStep');
	      this.setCurrentStep(this.steps[+window.event.target.getAttribute('data-step')]);
	      this.fireCurrentStepEvent('onAfterSwitchStep');

	      if (this.layout.finishedBlock) {
	        this.hideFinalState();
	      }

	      this.showStep();
	      this.toggleNavBtn();
	      this.fireCurrentStepEvent('onEndSwitchStep');
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "getFirstUncompletedStep",
	    value: function getFirstUncompletedStep() {
	      for (var i = 0; i < this.steps.length; i += 1) {
	        if (!this.steps[i].isCompleted) {
	          return this.steps[i];
	        }
	      }

	      return null;
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "toggleCompletedState",
	    value: function toggleCompletedState() {
	      var currentStep = this.getCurrentStep();

	      if (currentStep) {
	        if (currentStep.getCompleted()) {
	          this.showNode(this.getRepeatBtn());
	          this.hideNode(this.getStartBtn());
	          this.hideNode(this.getCompletedBtn());
	        } else if (currentStep.isActive) {
	          this.showNode(this.getCompletedBtn());
	          this.hideNode(this.getStartBtn());
	          this.showNode(this.getRepeatBtn());
	        } else {
	          this.showNode(this.getStartBtn());
	          this.hideNode(this.getCompletedBtn());
	          this.hideNode(this.getRepeatBtn());
	        }
	      }
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "setPopupData",
	    value: function setPopupData() {
	      this.fireCurrentStepEvent('onBeforeSetPopupData');
	      var currentStep = this.getCurrentStep();

	      if (currentStep) {
	        this.getTitle().innerHTML = currentStep.getTitle();
	        this.getDescription().innerHTML = currentStep.getDescription();
	        Manager.getCollapseTitle().innerHTML = currentStep.getTitle();

	        if (this.getCurrentStep().getVideo() && window.YT) {
	          this.setCurrentVideo();
	        }

	        this.setStepCounter();
	        this.setStepItems();
	      }

	      this.fireCurrentStepEvent('onAfterSetPopupData');
	    }
	  }, {
	    key: "setCurrentVideo",
	    value: function setCurrentVideo() {
	      this.fireCurrentStepEvent('onSetCurrentVideo');

	      for (var i = 0; i < this.steps.length; i += 1) {
	        var video = this.steps[i].getVideoObj();

	        if (window.YT && i === this.currentStepIndex && video && video.playVideo) {
	          main_core.Dom.addClass(video.getIframe(), 'ui-tutor-popup-video-show');
	          video.playVideo();
	        } else {
	          if (video) {
	            main_core.Dom.removeClass(video.getIframe(), 'ui-tutor-popup-video-show');

	            if (video.pauseVideo) {
	              video.pauseVideo();
	            }
	          }
	        }
	      }
	    }
	    /**
	     * @public
	     * @returns {Step}
	     */

	  }, {
	    key: "getCurrentStep",
	    value: function getCurrentStep() {
	      return this.currentStep;
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "increaseCurrentIndex",
	    value: function increaseCurrentIndex() {
	      if (this.currentStepIndex === this.steps.length) {
	        return;
	      }

	      this.currentStepIndex += 1;
	      this.setCurrentStep(this.steps[this.currentStepIndex]);
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "reduceCurrentIndex",
	    value: function reduceCurrentIndex() {
	      if (this.currentStepIndex === 0) {
	        return;
	      }

	      this.currentStepIndex -= 1;
	      this.setCurrentStep(this.steps[this.currentStepIndex]);
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "showCollapseBlock",
	    value: function showCollapseBlock(step, withGuide) {
	      withGuide = withGuide !== false;
	      this.closeStepPopup(null, true);
	      Manager.showCollapsedBlock(step, withGuide);
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "minimize",
	    value: function minimize() {
	      this.pauseCurrentVideo();
	      this.fireCurrentStepEvent('onMinimize');
	      this.showCollapseBlock(this.getCurrentStep(), false);
	    }
	  }, {
	    key: "repeatStep",
	    value: function repeatStep() {
	      this.followLink();
	    }
	  }, {
	    key: "clickStartHandler",
	    value: function clickStartHandler() {
	      this.followLink();
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "followLink",
	    value: function followLink(step) {
	      var currentStep = this.getCurrentStep();

	      if (step instanceof Step) {
	        currentStep = step;
	      }

	      this.pauseCurrentVideo();
	      this.setActiveStep(currentStep);
	      Manager.checkFollowLink(currentStep, this);
	    }
	  }, {
	    key: "setActiveStep",
	    value: function setActiveStep(step) {
	      this.fireCurrentStepEvent('onBeforeSetActiveStep');

	      if (this.currentActiveStep instanceof Step) {
	        this.currentActiveStep.deactivate();
	      }

	      step.activate();
	      this.currentActiveStep = step;
	      this.fireCurrentStepEvent('onAfterSetActiveStep');
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "showSuccessState",
	    value: function showSuccessState() {
	      var currentStep = this.getCurrentStep();
	      this.clickOnCompletedBtn = true;
	      currentStep.isCompleted = true;
	      this.fireCurrentStepEvent('onFinishStep');

	      if (currentStep.getCompleted()) {
	        main_core.Dom.addClass(this.layout.stepItems[this.currentStepIndex], 'ui-tutor-popup-step-item-completed');
	      }

	      main_core.Dom.addClass(this.getContentBlock(), 'ui-tutor-popup-content-block-animate');
	      setTimeout(function () {
	        main_core.Dom.replace(this.getHelpBlock(), this.getCompletedBLock());
	        this.getFooter().style.display = "none";
	        this.getDescription().style.display = "none";
	        this.getTitle().style.display = "none";
	      }.bind(this), 300);
	      setTimeout(function () {
	        main_core.Dom.addClass(this.getCompletedBLock(), 'ui-tutor-popup-completed-animate');
	      }.bind(this), 800);
	      setTimeout(function () {
	        main_core.Dom.replace(this.getCompletedBLock(), this.getHelpBlock());
	        this.getTitle().style.display = "block";
	        this.getDescription().style.display = "block";
	        this.getFooter().style.display = "flex";
	        this.clickOnNextBtn();
	      }.bind(this), 1500);
	      setTimeout(function () {
	        main_core.Dom.removeClass(this.getCompletedBLock(), 'ui-tutor-popup-completed-animate');
	        main_core.Dom.removeClass(this.getContentBlock(), 'ui-tutor-popup-content-block-animate');
	        var counter = this.stepPopup.querySelector(".ui-tutor-popup-header-counter-number");
	        counter.innerHTML = main_core.Loc.getMessage('JS_UI_TUTOR_COUNTER_NUMBER').replace('#NUMBER#', this.steps.indexOf(this.getCurrentStep()) + 1).replace('#NUMBER_TOTAL#', this.steps.length);
	        this.fireCurrentStepEvent('onAfterShowSuccessState');
	      }.bind(this), 1700);
	    }
	  }, {
	    key: "fireCurrentStepEvent",
	    value: function fireCurrentStepEvent(eventName, fireStepEvent, extra) {
	      fireStepEvent = fireStepEvent !== false;
	      var currentStep = this.getCurrentStep();
	      var data = {
	        step: currentStep,
	        scenario: this
	      };

	      if (extra) {
	        data.extra = extra;
	      }

	      if (currentStep && fireStepEvent) {
	        currentStep.emit(eventName, data);
	      }

	      this.emit(eventName, data);
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "showFinalState",
	    value: function showFinalState() {
	      this.fireCurrentStepEvent('onFinalState');

	      if (this.layout.stepItems) {
	        main_core.Dom.removeClass(this.layout.stepItems[this.currentStepIndex], 'ui-tutor-popup-step-item-current');
	      }

	      main_core.Dom.append(this.getFinishedBlock(), this.getContentInner());
	      main_core.Dom.replace(this.getStartBtn(), this.getFinishedNotice());
	      main_core.Dom.remove(this.getCompletedBtn());
	      main_core.Dom.remove(this.getSupportLink());
	      main_core.Dom.remove(this.getNavigation());
	      main_core.Dom.remove(this.getHelpBlock());
	      main_core.Dom.remove(this.getRepeatBtn());
	      main_core.Dom.remove(this.getTitle());
	      main_core.Dom.remove(this.getDescription());
	      main_core.Dom.remove(this.getDeferLink());
	      this.isFinished = true;
	      this.fireCurrentStepEvent('onAfterFinalState');
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "hideFinalState",
	    value: function hideFinalState() {
	      this.fireCurrentStepEvent('onBeforeHideFinalState');

	      if (this.getCurrentStep().getCompleted()) {
	        main_core.Dom.replace(this.getFinishedNotice(), this.getRepeatBtn());
	      } else {
	        main_core.Dom.replace(this.getFinishedNotice(), this.getStartBtn());
	      }

	      main_core.Dom.replace(this.getFinishedBlock(), this.getHelpBlock());

	      if (Manager.getInstance().feedbackFormId) {
	        main_core.Dom.append(this.getSupportLink(), this.getFooter());
	      }

	      main_core.Dom.prepend(this.getNavigation(), this.getFooter());
	      main_core.Dom.prepend(this.getDescription(), this.getContentInner());
	      main_core.Dom.prepend(this.getTitle(), this.getContentInner());

	      if (this.layout.deferBtn) {
	        main_core.Dom.remove(this.getDeferBtn());
	        main_core.Dom.prepend(this.getStartBtn(), this.getBtnContainer());
	      }

	      var header = this.getStepPopup().querySelector('.ui-tutor-popup-header');
	      main_core.Dom.append(this.getDeferLink(), header);
	      this.fireCurrentStepEvent('onAfterHideFinalState');
	    }
	    /**
	     * @public
	     * @returns {HTMLElement}
	     */

	  }, {
	    key: "getFinishedBlock",
	    value: function getFinishedBlock() {
	      if (!this.layout.finishedBlock) {
	        this.layout.finishedBlock = main_core.Tag.render(_templateObject25(), main_core.Loc.getMessage('JS_UI_TUTOR_FINAL_CONGRATULATIONS'), main_core.Loc.getMessage('JS_UI_TUTOR_FINAL_TEXT'));
	      }

	      return this.layout.finishedBlock;
	    }
	    /**
	     * @public
	     * @returns {HTMLElement}
	     */

	  }, {
	    key: "getFinishedNotice",
	    value: function getFinishedNotice() {
	      if (!this.layout.finishedNotice) {
	        this.layout.finishedNotice = main_core.Tag.render(_templateObject26(), main_core.Loc.getMessage('JS_UI_TUTOR_FINAL_NOTICE'));
	      }

	      return this.layout.finishedNotice;
	    }
	    /**
	     * @public
	     * @returns {HTMLElement}
	     */

	  }, {
	    key: "getNewStepsSection",
	    value: function getNewStepsSection() {
	      if (!this.layout.newStepsSection) {
	        this.layout.newStepsSection = main_core.Tag.render(_templateObject27(), main_core.Loc.getMessage('JS_UI_TUTOR_STEP_NEW'));
	      }

	      return this.layout.newStepsSection;
	    }
	    /**
	     * @public
	     */

	  }, {
	    key: "showNewSteps",
	    value: function showNewSteps() {
	      main_core.Dom.addClass(this.getFinishedBlock(), 'ui-tutor-popup-finished-new');
	      this.showPopup(this.getStepPopup());
	      this.showFinalState();
	      main_core.Dom.append(this.getNewStepsSection(), this.getFinishedBlock());
	      main_core.Dom.replace(this.getFinishedNotice(), Manager.getBeginBtn());
	      main_core.Dom.append(this.getDeferBtn(), this.getBtnContainer());
	      this.setStepCounter();
	      this.initArrows();
	      this.scrollToStep();
	      this.isAddedSteps = true;
	      this.isFinished = false;
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "initArrows",
	    value: function initArrows() {
	      this.stepListWrap = document.querySelector('.ui-tutor-popup-step-list-wrap');
	      this.arrowWrap = document.querySelector('.ui-tutor-popup-arrow-wrap');

	      if (this.stepListWrap && this.stepListWrap.scrollWidth > this.stepListWrap.offsetWidth) {
	        main_core.Dom.append(this.getPrevArrow(), this.arrowWrap);
	        main_core.Dom.append(this.getNextArrow(), this.arrowWrap);
	        this.stepListWrap.addEventListener('scroll', this.toggleArrows.bind(this));
	        this.prevArrow.addEventListener('mouseenter', this.scrollToLeft.bind(this));
	        this.prevArrow.addEventListener('mouseleave', this.stopAutoScroll.bind(this));
	        this.nextArrow.addEventListener('mouseenter', this.scrollToRight.bind(this));
	        this.nextArrow.addEventListener('mouseleave', this.stopAutoScroll.bind(this));
	        this.toggleNextArrow();
	        this.getStepBlock().classList.add("ui-tutor-popup-step-list-wide");
	        this.hasArrows = true;
	      }
	    }
	  }, {
	    key: "getPrevArrow",
	    value: function getPrevArrow() {
	      if (!this.prevArrow) {
	        this.prevArrow = main_core.Tag.render(_templateObject28());
	      }

	      return this.prevArrow;
	    }
	  }, {
	    key: "getNextArrow",
	    value: function getNextArrow() {
	      if (!this.nextArrow) {
	        this.nextArrow = main_core.Tag.render(_templateObject29());
	      }

	      return this.nextArrow;
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "scrollToLeft",
	    value: function scrollToLeft() {
	      this.arrowTimer = setInterval(function () {
	        this.stepListWrap.scrollLeft -= 5;
	      }.bind(this), 20);
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "scrollToRight",
	    value: function scrollToRight() {
	      this.arrowTimer = setInterval(function () {
	        this.stepListWrap.scrollLeft += 5;
	      }.bind(this), 20);
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "stopAutoScroll",
	    value: function stopAutoScroll() {
	      clearInterval(this.arrowTimer);
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "toggleArrows",
	    value: function toggleArrows() {
	      this.togglePrevArrow();
	      this.toggleNextArrow();
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "toggleNextArrow",
	    value: function toggleNextArrow() {
	      if (this.stepListWrap.scrollWidth > this.stepListWrap.offsetWidth && this.stepListWrap.offsetWidth + this.stepListWrap.scrollLeft < this.stepListWrap.scrollWidth) {
	        main_core.Dom.addClass(this.nextArrow, 'ui-tutor-popup-arrow-show');
	      } else {
	        main_core.Dom.removeClass(this.nextArrow, 'ui-tutor-popup-arrow-show');
	      }
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "togglePrevArrow",
	    value: function togglePrevArrow() {
	      if (this.stepListWrap.scrollLeft > 0) {
	        main_core.Dom.addClass(this.prevArrow, 'ui-tutor-popup-arrow-show');
	      } else {
	        main_core.Dom.removeClass(this.prevArrow, 'ui-tutor-popup-arrow-show');
	      }
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "showAnimation",
	    value: function showAnimation(popup) {
	      main_core.Dom.removeClass(popup, 'ui-tutor-popup-hide-complex');
	      main_core.Dom.removeClass(popup, 'ui-tutor-popup-hide');

	      if (this.complexAnimation) {
	        main_core.Dom.addClass(popup, 'ui-tutor-popup-show-complex');
	      } else {
	        main_core.Dom.addClass(popup, 'ui-tutor-popup-show');
	      }
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "fadeAnimation",
	    value: function fadeAnimation(popup) {
	      main_core.Dom.removeClass(popup, 'ui-tutor-popup-show-complex');
	      main_core.Dom.removeClass(popup, 'ui-tutor-popup-show');

	      if (this.complexAnimation) {
	        main_core.Dom.addClass(popup, 'ui-tutor-popup-hide-complex');
	      } else {
	        main_core.Dom.addClass(popup, 'ui-tutor-popup-hide');
	      }
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "scrollToStep",
	    value: function scrollToStep() {
	      var posList = null;
	      var posStep = null;

	      if (this.stepListWrap) {
	        posList = main_core.Dom.getPosition(this.stepListWrap);
	        posStep = main_core.Dom.getPosition(this.stepListWrap.querySelector('[data-step="' + this.currentStepIndex + '"]'));
	      }

	      var offset = 7; // padding 2px and margin 5px

	      if (!main_core.Type.isNull(posStep) && posStep.left + posStep.width > posList.left + posList.width) {
	        this.stepListWrap.scrollLeft += posStep.left - (posList.left + posList.width) + posStep.width + offset;
	      }

	      if (!main_core.Type.isNull(posStep) && posStep.left < posList.left) {
	        this.stepListWrap.scrollLeft -= posList.left - posStep.left + offset;
	      }
	    }
	    /**
	     * @private
	     */

	  }], [{
	    key: "getFullEventName",
	    value: function getFullEventName(shortName) {
	      return shortName;
	    }
	  }, {
	    key: "getInstance",
	    value: function getInstance() {
	      return Manager.getScenarioInstance();
	    }
	  }, {
	    key: "init",
	    value: function init(options) {
	      return Manager.initScenario(options);
	    }
	  }]);
	  return Scenario;
	}(main_core.Event.EventEmitter);

	exports.Scenario = Scenario;
	exports.Manager = Manager;
	exports.Step = Step;

}((this.BX.UI.Tutor = this.BX.UI.Tutor || {}),BX,BX.UI.Tour,BX));
//# sourceMappingURL=tutor.bundle.js.map

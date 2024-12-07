/* eslint-disable */
this.BX = this.BX || {};
this.BX.Socialnetwork = this.BX.Socialnetwork || {};
this.BX.Socialnetwork.Blog = this.BX.Socialnetwork.Blog || {};
(function (exports,main_core,main_core_events) {
	'use strict';

	let _ = t => t,
	  _t;
	var _params = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("params");
	var _layout = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("layout");
	var _copilotLoaded = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("copilotLoaded");
	var _copilotContextMenu = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("copilotContextMenu");
	var _copilotShown = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("copilotShown");
	var _adjustAnimation = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("adjustAnimation");
	var _render = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("render");
	var _createCopilot = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createCopilot");
	var _bindEvents = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("bindEvents");
	var _startAdjustAnimation = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("startAdjustAnimation");
	var _onButtonMouseDown = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onButtonMouseDown");
	var _onButtonClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onButtonClick");
	var _show = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("show");
	var _getBindElement = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getBindElement");
	var _hide = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hide");
	var _copyIntoComment = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("copyIntoComment");
	var _copyIntoNewPost = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("copyIntoNewPost");
	class BlogCopilotReadonly {
	  constructor(params) {
	    Object.defineProperty(this, _copyIntoNewPost, {
	      value: _copyIntoNewPost2
	    });
	    Object.defineProperty(this, _copyIntoComment, {
	      value: _copyIntoComment2
	    });
	    Object.defineProperty(this, _hide, {
	      value: _hide2
	    });
	    Object.defineProperty(this, _getBindElement, {
	      value: _getBindElement2
	    });
	    Object.defineProperty(this, _show, {
	      value: _show2
	    });
	    Object.defineProperty(this, _onButtonClick, {
	      value: _onButtonClick2
	    });
	    Object.defineProperty(this, _onButtonMouseDown, {
	      value: _onButtonMouseDown2
	    });
	    Object.defineProperty(this, _startAdjustAnimation, {
	      value: _startAdjustAnimation2
	    });
	    Object.defineProperty(this, _bindEvents, {
	      value: _bindEvents2
	    });
	    Object.defineProperty(this, _createCopilot, {
	      value: _createCopilot2
	    });
	    Object.defineProperty(this, _render, {
	      value: _render2
	    });
	    Object.defineProperty(this, _params, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _layout, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _copilotLoaded, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _copilotContextMenu, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _copilotShown, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _adjustAnimation, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _params)[_params] = params;
	    babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout] = {};
	    if (babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].enabledBySettings) {
	      void babelHelpers.classPrivateFieldLooseBase(this, _createCopilot)[_createCopilot]();
	    }
	    main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _render)[_render](), babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].container);
	  }
	}
	function _render2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].button = main_core.Tag.render(_t || (_t = _`
			<span class="feed-inform-item feed-inform-comments feed-copilot-readonly">
				<a>${0}</a>
			</span>
		`), main_core.Loc.getMessage('BLOG_POST_BUTTON_COPILOT'));
	  main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].button, 'mousedown', babelHelpers.classPrivateFieldLooseBase(this, _onButtonMouseDown)[_onButtonMouseDown].bind(this));
	  main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].button, 'click', babelHelpers.classPrivateFieldLooseBase(this, _onButtonClick)[_onButtonClick].bind(this));
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].button;
	}
	async function _createCopilot2() {
	  const {
	    CopilotContextMenu
	  } = await main_core.Runtime.loadExtension('ai.copilot');
	  const options = {
	    moduleId: babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].copilotParams.moduleId,
	    contextId: babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].copilotParams.contextId,
	    category: babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].copilotParams.category,
	    bindElement: babelHelpers.classPrivateFieldLooseBase(this, _getBindElement)[_getBindElement](),
	    angle: true,
	    extraResultMenuItems: [{
	      code: 'insert-into-comment',
	      text: main_core.Loc.getMessage('BLOG_POST_BUTTON_COPILOT_COPY_INTO_COMMENT'),
	      command: () => {
	        const resultText = babelHelpers.classPrivateFieldLooseBase(this, _copilotContextMenu)[_copilotContextMenu].getResultText();
	        babelHelpers.classPrivateFieldLooseBase(this, _copilotContextMenu)[_copilotContextMenu].hide();
	        babelHelpers.classPrivateFieldLooseBase(this, _copyIntoComment)[_copyIntoComment](resultText);
	      }
	    }, {
	      code: 'insert-into-new-post',
	      text: main_core.Loc.getMessage('BLOG_POST_BUTTON_COPILOT_COPY_INTO_NEW_POST'),
	      command: () => {
	        const resultText = babelHelpers.classPrivateFieldLooseBase(this, _copilotContextMenu)[_copilotContextMenu].getResultText();
	        babelHelpers.classPrivateFieldLooseBase(this, _copilotContextMenu)[_copilotContextMenu].hide();
	        babelHelpers.classPrivateFieldLooseBase(this, _copyIntoNewPost)[_copyIntoNewPost](resultText);
	      }
	    }]
	  };
	  babelHelpers.classPrivateFieldLooseBase(this, _copilotContextMenu)[_copilotContextMenu] = new CopilotContextMenu(options);
	  babelHelpers.classPrivateFieldLooseBase(this, _bindEvents)[_bindEvents]();
	  try {
	    await babelHelpers.classPrivateFieldLooseBase(this, _copilotContextMenu)[_copilotContextMenu].init();
	    babelHelpers.classPrivateFieldLooseBase(this, _copilotLoaded)[_copilotLoaded] = true;
	  } catch (e) {
	    console.error('Failed to init copilot', e);
	  }
	}
	function _bindEvents2() {
	  main_core_events.EventEmitter.subscribe('onPullEvent-unicomments', babelHelpers.classPrivateFieldLooseBase(this, _startAdjustAnimation)[_startAdjustAnimation].bind(this));
	}
	function _startAdjustAnimation2() {
	  var _babelHelpers$classPr;
	  (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _adjustAnimation)[_adjustAnimation]) == null ? void 0 : _babelHelpers$classPr.stop();
	  // eslint-disable-next-line new-cap
	  babelHelpers.classPrivateFieldLooseBase(this, _adjustAnimation)[_adjustAnimation] = new BX.easing({
	    duration: 1000,
	    start: {},
	    finish: {},
	    transition: BX.easing.makeEaseOut(BX.easing.transitions.linear),
	    step: () => {
	      if (babelHelpers.classPrivateFieldLooseBase(this, _copilotContextMenu)[_copilotContextMenu].isShown()) {
	        babelHelpers.classPrivateFieldLooseBase(this, _copilotContextMenu)[_copilotContextMenu].adjustPosition();
	      }
	    },
	    complete: () => {
	      babelHelpers.classPrivateFieldLooseBase(this, _adjustAnimation)[_adjustAnimation] = null;
	    }
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _adjustAnimation)[_adjustAnimation].animate();
	}
	function _onButtonMouseDown2() {
	  var _babelHelpers$classPr2;
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].enabledBySettings) {
	    return;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _copilotShown)[_copilotShown] = (_babelHelpers$classPr2 = babelHelpers.classPrivateFieldLooseBase(this, _copilotContextMenu)[_copilotContextMenu]) == null ? void 0 : _babelHelpers$classPr2.isShown();
	}
	function _onButtonClick2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].enabledBySettings) {
	    BX.UI.InfoHelper.show('limit_copilot_off');
	    return;
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _copilotShown)[_copilotShown]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _hide)[_hide]();
	  } else {
	    babelHelpers.classPrivateFieldLooseBase(this, _show)[_show]();
	  }
	}
	function _show2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _copilotLoaded)[_copilotLoaded]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _copilotContextMenu)[_copilotContextMenu].setContext(babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].blogText);
	    babelHelpers.classPrivateFieldLooseBase(this, _copilotContextMenu)[_copilotContextMenu].show({
	      bindElement: babelHelpers.classPrivateFieldLooseBase(this, _getBindElement)[_getBindElement]()
	    });
	  }
	}
	function _getBindElement2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].button;
	}
	function _hide2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _copilotContextMenu)[_copilotContextMenu].hide();
	}
	function _copyIntoComment2(text) {
	  var _lhe$oEditor;
	  const list = FCList.getInstance({
	    ENTITY_XML_ID: babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].blogId
	  });
	  const form = list.form;
	  const lhe = LHEPostForm.getHandlerByFormId(list.form.formId);
	  if ((_lhe$oEditor = lhe.oEditor) != null && _lhe$oEditor.IsShown()) {
	    lhe.oEditor.action.Exec('insertHTML', text);
	  }
	  const iframeInitHandler = () => {
	    lhe.oEditor.action.Exec('insertHTML', text);
	    BX.removeCustomEvent(lhe.oEditor, 'OnAfterIframeInit', iframeInitHandler);
	  };
	  lhe.exec(() => {
	    BX.addCustomEvent(lhe.oEditor, 'OnAfterIframeInit', iframeInitHandler);
	  });
	  form.show(list);
	}
	function _copyIntoNewPost2(text) {
	  const pathToPostCreate = main_core.Uri.addParam(babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].pathToPostCreate, {
	    getTextFromHash: 'Y'
	  });
	  location.href = `${pathToPostCreate}#${encodeURIComponent(text)}`;
	}

	exports.BlogCopilotReadonly = BlogCopilotReadonly;

}((this.BX.Socialnetwork.Blog.Post = this.BX.Socialnetwork.Blog.Post || {}),BX,BX.Event));
//# sourceMappingURL=index.js.map

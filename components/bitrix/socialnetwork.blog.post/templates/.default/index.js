this.BX = this.BX || {};
this.BX.Socialnetwork = this.BX.Socialnetwork || {};
this.BX.Socialnetwork.Blog = this.BX.Socialnetwork.Blog || {};
(function (exports,main_core) {
	'use strict';

	let _ = t => t,
	  _t;
	var _params = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("params");
	var _layout = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("layout");
	var _copilotLoaded = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("copilotLoaded");
	var _copilotReadonly = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("copilotReadonly");
	var _copilotShown = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("copilotShown");
	var _render = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("render");
	var _createCopilot = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createCopilot");
	var _onButtonMouseDown = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onButtonMouseDown");
	var _onButtonClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onButtonClick");
	var _show = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("show");
	var _hide = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hide");
	var _enabledBySettings = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("enabledBySettings");
	class BlogCopilotReadonly {
	  constructor(params) {
	    Object.defineProperty(this, _enabledBySettings, {
	      value: _enabledBySettings2
	    });
	    Object.defineProperty(this, _hide, {
	      value: _hide2
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
	    Object.defineProperty(this, _copilotReadonly, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _copilotShown, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _params)[_params] = params;
	    babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout] = {};
	    if (babelHelpers.classPrivateFieldLooseBase(this, _enabledBySettings)[_enabledBySettings]()) {
	      babelHelpers.classPrivateFieldLooseBase(this, _createCopilot)[_createCopilot]();
	    }
	    main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _render)[_render](), babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].container);
	  }
	}
	function _render2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].button = main_core.Tag.render(_t || (_t = _`
			<span
				class="feed-inform-item feed-inform-comments feed-copilot-readonly"
				data-id="blog-post-button-copilot"
			>
				<a>${0}</a>
			</span>
		`), main_core.Loc.getMessage('BLOG_POST_BUTTON_COPILOT'));
	  main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].button, 'mousedown', babelHelpers.classPrivateFieldLooseBase(this, _onButtonMouseDown)[_onButtonMouseDown].bind(this));
	  main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].button, 'click', babelHelpers.classPrivateFieldLooseBase(this, _onButtonClick)[_onButtonClick].bind(this));
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].button;
	}
	async function _createCopilot2() {
	  const {
	    Copilot
	  } = await main_core.Runtime.loadExtension('ai.copilot');
	  babelHelpers.classPrivateFieldLooseBase(this, _copilotReadonly)[_copilotReadonly] = new Copilot({
	    moduleId: babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].copilotParams.moduleId,
	    contextId: babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].copilotParams.contextId,
	    category: babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].copilotParams.category,
	    readonly: true,
	    autoHide: true
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _copilotReadonly)[_copilotReadonly].subscribe('finish-init', () => {
	    babelHelpers.classPrivateFieldLooseBase(this, _copilotLoaded)[_copilotLoaded] = true;
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _copilotReadonly)[_copilotReadonly].init();
	}
	function _onButtonMouseDown2() {
	  var _babelHelpers$classPr;
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _enabledBySettings)[_enabledBySettings]()) {
	    return;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _copilotShown)[_copilotShown] = (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _copilotReadonly)[_copilotReadonly]) == null ? void 0 : _babelHelpers$classPr.isShown();
	}
	function _onButtonClick2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _enabledBySettings)[_enabledBySettings]()) {
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
	    babelHelpers.classPrivateFieldLooseBase(this, _copilotReadonly)[_copilotReadonly].setContext(babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].blogText);
	    const buttonRect = babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].button.getBoundingClientRect();
	    babelHelpers.classPrivateFieldLooseBase(this, _copilotReadonly)[_copilotReadonly].show({
	      bindElement: {
	        left: buttonRect.left + window.scrollX,
	        top: buttonRect.bottom + window.scrollY + 10
	      }
	    });
	  }
	}
	function _hide2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _copilotReadonly)[_copilotReadonly].hide();
	}
	function _enabledBySettings2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].enabledBySettings === 'Y';
	}

	exports.BlogCopilotReadonly = BlogCopilotReadonly;

}((this.BX.Socialnetwork.Blog.Post = this.BX.Socialnetwork.Blog.Post || {}),BX));
//# sourceMappingURL=index.js.map

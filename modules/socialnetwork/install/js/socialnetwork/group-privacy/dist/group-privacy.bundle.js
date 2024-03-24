this.BX = this.BX || {};
(function (exports,main_core,main_core_events,main_popup) {
	'use strict';

	let _ = t => t,
	  _t;
	var _popup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("popup");
	var _contentNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("contentNode");
	var _privacyCode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("privacyCode");
	var _getPopup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getPopup");
	var _createPopup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createPopup");
	var _renderContent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderContent");
	var _changePrivacy = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("changePrivacy");
	class GroupPrivacy extends main_core_events.EventEmitter {
	  constructor(params) {
	    super();
	    Object.defineProperty(this, _changePrivacy, {
	      value: _changePrivacy2
	    });
	    Object.defineProperty(this, _renderContent, {
	      value: _renderContent2
	    });
	    Object.defineProperty(this, _createPopup, {
	      value: _createPopup2
	    });
	    Object.defineProperty(this, _getPopup, {
	      value: _getPopup2
	    });
	    Object.defineProperty(this, _popup, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _contentNode, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _privacyCode, {
	      writable: true,
	      value: void 0
	    });
	    this.setEventNamespace('BX.Socialnetwork.Spaces.Settings.Group.Privacy');
	    babelHelpers.classPrivateFieldLooseBase(this, _privacyCode)[_privacyCode] = params.privacyCode;
	  }
	  show(bindElement) {
	    babelHelpers.classPrivateFieldLooseBase(this, _getPopup)[_getPopup](bindElement).show();
	  }
	  setPrivacy(privacyCode) {
	    babelHelpers.classPrivateFieldLooseBase(this, _privacyCode)[_privacyCode] = privacyCode;
	    if (babelHelpers.classPrivateFieldLooseBase(this, _contentNode)[_contentNode]) {
	      main_core.Dom.removeClass(babelHelpers.classPrivateFieldLooseBase(this, _contentNode)[_contentNode].querySelector('.--active'), '--active');
	      const node = babelHelpers.classPrivateFieldLooseBase(this, _contentNode)[_contentNode].querySelector(`[data-id='spaces-group-privacy-${privacyCode}']`);
	      main_core.Dom.addClass(node, '--active');
	    }
	  }
	  getLabel() {
	    return main_core.Loc.getMessage(`SN_SPACES_GROUP_PRIVACY_${babelHelpers.classPrivateFieldLooseBase(this, _privacyCode)[_privacyCode].toUpperCase()}`);
	  }
	}
	function _getPopup2(bindElement) {
	  var _babelHelpers$classPr, _babelHelpers$classPr2;
	  (_babelHelpers$classPr2 = (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _popup))[_popup]) != null ? _babelHelpers$classPr2 : _babelHelpers$classPr[_popup] = babelHelpers.classPrivateFieldLooseBase(this, _createPopup)[_createPopup](bindElement);
	  return babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup];
	}
	function _createPopup2(bindElement) {
	  const popup = new main_popup.Popup({
	    id: 'sn-post-form',
	    bindElement,
	    content: babelHelpers.classPrivateFieldLooseBase(this, _renderContent)[_renderContent](babelHelpers.classPrivateFieldLooseBase(this, _privacyCode)[_privacyCode]),
	    autoHide: true,
	    angle: false,
	    width: 343,
	    closeIcon: false,
	    closeByEsc: true,
	    overlay: true,
	    padding: 0,
	    animation: 'fading-slide'
	  });
	  popup.subscribe('onShow', () => this.emit('onShow'));
	  popup.subscribe('onAfterClose', () => this.emit('onAfterClose'));
	  return popup;
	}
	function _renderContent2(privacyCode) {
	  const openActiveClass = privacyCode === 'open' ? '--active' : '';
	  const closedActiveClass = privacyCode === 'closed' ? '--active' : '';
	  const secretActiveClass = privacyCode === 'secret' ? '--active' : '';
	  const openId = 'spaces-group-privacy-open';
	  const closedId = 'spaces-group-privacy-closed';
	  const secretId = 'spaces-group-privacy-secret';
	  babelHelpers.classPrivateFieldLooseBase(this, _contentNode)[_contentNode] = main_core.Tag.render(_t || (_t = _`
			<div class="sn-spaces__popup-menu">
				<div data-id="${0}" class="sn-spaces__popup-menu_item ${0}">
					<div class="sn-spaces__popup-menu_item-icon --open-spaces"></div>
					<div class="sn-spaces__popup-menu_item-info">
						<div class="sn-spaces__popup-menu_item-name">
							${0}
						</div>
						<div class="sn-spaces__popup-menu_item-description">
							${0}
						</div>
					</div>
				</div>
				<div data-id="${0}" class="sn-spaces__popup-menu_item ${0}">
					<div class="sn-spaces__popup-menu_item-icon --closed-spaces"></div>
					<div class="sn-spaces__popup-menu_item-info">
						<div class="sn-spaces__popup-menu_item-name">
							${0}
						</div>
						<div class="sn-spaces__popup-menu_item-description">
							${0}
						</div>
					</div>
				</div>
				<div data-id="${0}" class="sn-spaces__popup-menu_item ${0}">
					<div class="sn-spaces__popup-menu_item-icon --secret-spaces"></div>
					<div class="sn-spaces__popup-menu_item-info">
						<div class="sn-spaces__popup-menu_item-name">
							${0}
						</div>
						<div class="sn-spaces__popup-menu_item-description">
							${0}
						</div>
					</div>
				</div>
				<div class="sn-spaces__popup-menu_hint">
					${0}
				</div>
			</div>
		`), openId, openActiveClass, main_core.Loc.getMessage('SN_SPACES_GROUP_PRIVACY_OPEN'), main_core.Loc.getMessage('SN_SPACES_GROUP_PRIVACY_OPEN_DESC'), closedId, closedActiveClass, main_core.Loc.getMessage('SN_SPACES_GROUP_PRIVACY_CLOSED'), main_core.Loc.getMessage('SN_SPACES_GROUP_PRIVACY_CLOSED_DESC'), secretId, secretActiveClass, main_core.Loc.getMessage('SN_SPACES_GROUP_PRIVACY_SECRET'), main_core.Loc.getMessage('SN_SPACES_GROUP_PRIVACY_SECRET_DESC'), main_core.Loc.getMessage('SN_SPACES_GROUP_PRIVACY_PROMPT'));
	  main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _contentNode)[_contentNode].querySelector(`[data-id='${openId}']`), 'click', () => babelHelpers.classPrivateFieldLooseBase(this, _changePrivacy)[_changePrivacy]('open'));
	  main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _contentNode)[_contentNode].querySelector(`[data-id='${closedId}']`), 'click', () => babelHelpers.classPrivateFieldLooseBase(this, _changePrivacy)[_changePrivacy]('closed'));
	  main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _contentNode)[_contentNode].querySelector(`[data-id='${secretId}']`), 'click', () => babelHelpers.classPrivateFieldLooseBase(this, _changePrivacy)[_changePrivacy]('secret'));
	  return babelHelpers.classPrivateFieldLooseBase(this, _contentNode)[_contentNode];
	}
	function _changePrivacy2(privacyCode) {
	  this.setPrivacy(privacyCode);
	  this.emit('changePrivacy', babelHelpers.classPrivateFieldLooseBase(this, _privacyCode)[_privacyCode]);
	  babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup].close();
	}

	exports.GroupPrivacy = GroupPrivacy;

}((this.BX.Socialnetwork = this.BX.Socialnetwork || {}),BX,BX.Event,BX.Main));
//# sourceMappingURL=group-privacy.bundle.js.map

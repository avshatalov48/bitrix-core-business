this.BX = this.BX || {};
(function (exports,main_core) {
	'use strict';

	let _ = t => t,
	  _t;
	var _logo = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("logo");
	var _getIconStyle = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getIconStyle");
	var _getIconClass = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getIconClass");
	class Logo {
	  constructor(logo) {
	    Object.defineProperty(this, _getIconClass, {
	      value: _getIconClass2
	    });
	    Object.defineProperty(this, _getIconStyle, {
	      value: _getIconStyle2
	    });
	    Object.defineProperty(this, _logo, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _logo)[_logo] = logo;
	  }
	  render() {
	    const iconClass = babelHelpers.classPrivateFieldLooseBase(this, _getIconClass)[_getIconClass]();
	    const iconStyle = babelHelpers.classPrivateFieldLooseBase(this, _getIconStyle)[_getIconStyle]();
	    return main_core.Tag.render(_t || (_t = _`<i class="${0}" style="${0}"></i>`), iconClass, iconStyle);
	  }
	  getClass() {
	    let result = '';
	    if (babelHelpers.classPrivateFieldLooseBase(this, _logo)[_logo].type === 'icon') {
	      if (babelHelpers.classPrivateFieldLooseBase(this, _logo)[_logo].id.length > 0) {
	        result = `sonet-common-workgroup-avatar --${babelHelpers.classPrivateFieldLooseBase(this, _logo)[_logo].id}`;
	      } else {
	        result = 'ui-icon-common-user-group ui-icon';
	      }
	    }
	    return result;
	  }
	}
	function _getIconStyle2() {
	  let result = '';
	  if (babelHelpers.classPrivateFieldLooseBase(this, _logo)[_logo].type === 'image') {
	    result = `background-image: url('${babelHelpers.classPrivateFieldLooseBase(this, _logo)[_logo].id}'); background-size: cover`;
	  }
	  return result;
	}
	function _getIconClass2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _logo)[_logo].type === 'image' ? 'sn-spaces__space-logo_img' : '';
	}

	exports.Logo = Logo;

}((this.BX.Socialnetwork = this.BX.Socialnetwork || {}),BX));
//# sourceMappingURL=logo.bundle.js.map

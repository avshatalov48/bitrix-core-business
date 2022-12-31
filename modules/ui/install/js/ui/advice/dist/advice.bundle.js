this.BX = this.BX || {};
(function (exports,main_core) {
	'use strict';

	let _ = t => t,
	    _t,
	    _t2;
	const AdviceAnglePosition = Object.freeze({
	  TOP: 'top',
	  BOTTOM: 'bottom'
	});

	var _container = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("container");

	var _avatar = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("avatar");

	var _anglePosition = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("anglePosition");

	var _content = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("content");

	var _textBoxBaseClassname = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("textBoxBaseClassname");

	var _containerBaseClassname = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("containerBaseClassname");

	var _getTextBoxClassname = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getTextBoxClassname");

	var _getContainerClassname = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getContainerClassname");

	var _getHtmlContent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getHtmlContent");

	var _createContainer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createContainer");

	var _isValidAnglePosition = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isValidAnglePosition");

	var _isValidContent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isValidContent");

	class Advice {
	  constructor(options) {
	    Object.defineProperty(this, _isValidContent, {
	      value: _isValidContent2
	    });
	    Object.defineProperty(this, _isValidAnglePosition, {
	      value: _isValidAnglePosition2
	    });
	    Object.defineProperty(this, _createContainer, {
	      value: _createContainer2
	    });
	    Object.defineProperty(this, _getHtmlContent, {
	      value: _getHtmlContent2
	    });
	    Object.defineProperty(this, _getContainerClassname, {
	      value: _getContainerClassname2
	    });
	    Object.defineProperty(this, _getTextBoxClassname, {
	      value: _getTextBoxClassname2
	    });
	    Object.defineProperty(this, _container, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _avatar, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _anglePosition, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _content, {
	      writable: true,
	      value: ''
	    });
	    Object.defineProperty(this, _textBoxBaseClassname, {
	      writable: true,
	      value: 'ui-advice__text-box'
	    });
	    Object.defineProperty(this, _containerBaseClassname, {
	      writable: true,
	      value: 'ui-advice'
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _avatar)[_avatar] = main_core.Type.isString(options.avatarImg) ? options.avatarImg : '';
	    babelHelpers.classPrivateFieldLooseBase(this, _anglePosition)[_anglePosition] = babelHelpers.classPrivateFieldLooseBase(this, _isValidAnglePosition)[_isValidAnglePosition](options.anglePosition) ? options.anglePosition : Advice.AnglePosition.TOP;
	    babelHelpers.classPrivateFieldLooseBase(this, _content)[_content] = babelHelpers.classPrivateFieldLooseBase(this, _isValidContent)[_isValidContent](options.content) ? options.content : '';

	    babelHelpers.classPrivateFieldLooseBase(this, _createContainer)[_createContainer]();
	  }

	  getAvatar() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _avatar)[_avatar];
	  }

	  setAvatar(avatarImg) {
	    babelHelpers.classPrivateFieldLooseBase(this, _avatar)[_avatar] = avatarImg;
	    return babelHelpers.classPrivateFieldLooseBase(this, _avatar)[_avatar];
	  }

	  getContent() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _content)[_content];
	  }

	  setContent(content) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isValidContent)[_isValidContent](content)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _content)[_content] = content;
	      return babelHelpers.classPrivateFieldLooseBase(this, _content)[_content];
	    }

	    return null;
	  }

	  getContainer() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _container)[_container];
	  }

	  renderTo(targetContainer) {
	    if (main_core.Type.isDomNode(targetContainer)) {
	      main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _container)[_container], targetContainer);
	      return targetContainer;
	    } else {
	      return null;
	    }
	  }

	}

	function _getTextBoxClassname2() {
	  let className = babelHelpers.classPrivateFieldLooseBase(this, _textBoxBaseClassname)[_textBoxBaseClassname];

	  if (babelHelpers.classPrivateFieldLooseBase(this, _anglePosition)[_anglePosition] === AdviceAnglePosition.BOTTOM) {
	    className += ' --angle-bottom';
	  } else if (babelHelpers.classPrivateFieldLooseBase(this, _anglePosition)[_anglePosition] === AdviceAnglePosition.TOP) {
	    className += ' --angle-top';
	  } else {
	    className += ' --angle-bottom';
	  }

	  return className;
	}

	function _getContainerClassname2() {
	  let className = babelHelpers.classPrivateFieldLooseBase(this, _containerBaseClassname)[_containerBaseClassname];

	  switch (babelHelpers.classPrivateFieldLooseBase(this, _anglePosition)[_anglePosition]) {
	    case AdviceAnglePosition.BOTTOM:
	      className += ' --angle-bottom';
	      break;

	    case AdviceAnglePosition.TOP:
	      className += ' --angle-top';
	      break;

	    default:
	      className += ' --angle-bottom';
	  }

	  return className;
	}

	function _getHtmlContent2() {
	  if (main_core.Type.isString(babelHelpers.classPrivateFieldLooseBase(this, _content)[_content])) {
	    return main_core.Tag.render(_t || (_t = _`<span>${0}</span>`), babelHelpers.classPrivateFieldLooseBase(this, _content)[_content]);
	  }

	  return babelHelpers.classPrivateFieldLooseBase(this, _content)[_content];
	}

	function _createContainer2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _container)[_container]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _container)[_container] = main_core.Tag.render(_t2 || (_t2 = _`
				<div class="${0}">
					<div class="ui-advice__avatar-box">
						<span class="ui-advice__avatar ui-icon ui-icon-common-user">
							<i style="background-image: url('${0}')"></i>
						</span>
					</div>
					<div class="${0}"></div>
				</div>
				`), babelHelpers.classPrivateFieldLooseBase(this, _getContainerClassname)[_getContainerClassname](), encodeURI(this.getAvatar()), babelHelpers.classPrivateFieldLooseBase(this, _getTextBoxClassname)[_getTextBoxClassname]());

	    const contentContainer = babelHelpers.classPrivateFieldLooseBase(this, _container)[_container].querySelector(`.${babelHelpers.classPrivateFieldLooseBase(this, _textBoxBaseClassname)[_textBoxBaseClassname]}`);

	    main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _getHtmlContent)[_getHtmlContent](), contentContainer);
	  }

	  return babelHelpers.classPrivateFieldLooseBase(this, _container)[_container];
	}

	function _isValidAnglePosition2(anglePosition) {
	  return main_core.Type.isString(anglePosition) && Object.values(AdviceAnglePosition).includes(anglePosition);
	}

	function _isValidContent2(content) {
	  return main_core.Type.isString(content) || main_core.Type.isDomNode(content);
	}

	Advice.AnglePosition = AdviceAnglePosition;

	exports.AdviceAnglePosition = AdviceAnglePosition;
	exports.Advice = Advice;

}((this.BX.Ui = this.BX.Ui || {}),BX));
//# sourceMappingURL=advice.bundle.js.map

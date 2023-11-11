/* eslint-disable */
this.BX = this.BX || {};
this.BX.IM = this.BX.IM || {};
(function (exports,main_core,main_core_events,main_popup,ui_buttons) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2;
	var _rows = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("rows");
	var _createControlNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createControlNode");
	var _onTemplateSelect = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onTemplateSelect");
	var _getTemplates = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getTemplates");
	class MessageTemplateSelector extends main_core_events.EventEmitter {
	  constructor(options) {
	    super();
	    Object.defineProperty(this, _getTemplates, {
	      value: _getTemplates2
	    });
	    Object.defineProperty(this, _onTemplateSelect, {
	      value: _onTemplateSelect2
	    });
	    Object.defineProperty(this, _createControlNode, {
	      value: _createControlNode2
	    });
	    Object.defineProperty(this, _rows, {
	      writable: true,
	      value: []
	    });
	    this.setEventNamespace('BX.IM.Robot.MessageTemplateSelector');
	  }
	  show(bindElement, selected) {
	    const popup = new main_popup.Popup({
	      bindElement: bindElement,
	      width: 431,
	      padding: 20,
	      content: babelHelpers.classPrivateFieldLooseBase(this, _createControlNode)[_createControlNode](selected),
	      closeByEsc: true,
	      events: {
	        onClose: () => {
	          babelHelpers.classPrivateFieldLooseBase(this, _rows)[_rows] = [];
	        }
	      },
	      buttons: [new ui_buttons.Button({
	        text: main_core.Loc.getMessage('BX_IM_ROBOT_MESSAGE_TEMPLATE_SELECTOR_BUTTON_OK'),
	        color: ui_buttons.Button.Color.PRIMARY,
	        events: {
	          click: () => {
	            const form = popup.getContentContainer().querySelector('form');
	            const value = new FormData(form).get('select-type-message');
	            this.emit('select', {
	              selected: value
	            });
	            popup.close();
	          }
	        }
	      }), new ui_buttons.Button({
	        text: main_core.Loc.getMessage('BX_IM_ROBOT_MESSAGE_TEMPLATE_SELECTOR_BUTTON_CANCEL'),
	        color: ui_buttons.Button.Color.LINK,
	        events: {
	          click: () => {
	            popup.close();
	          }
	        }
	      })],
	      autoHide: true,
	      closeIcon: false,
	      titleBar: false,
	      angle: true
	    });
	    popup.setCacheable(false);
	    popup.show();
	  }
	}
	function _createControlNode2(selected) {
	  const templates = babelHelpers.classPrivateFieldLooseBase(this, _getTemplates)[_getTemplates]();
	  templates.forEach(template => {
	    const isSelected = template.id === selected;
	    const {
	      root: templateRow,
	      templateRadio
	    } = main_core.Tag.render(_t || (_t = _`
				<div class="bizproc-automation-popup-settings__select-type_row ${0}">
						<label class="bizproc-automation-popup-settings__select-type_info">
							<div class="bizproc-automation-popup-settings__select-type_info-name ui-ctl ui-ctl-radio ui-ctl-wa">
								<input ref="templateRadio" type="radio" ${0} onclick="${0}" name="select-type-message" value="${0}" class="ui-ctl-element bizproc-automation-popup-settings__select-type_info-input">
								${0}
							</div>
							<div class="bizproc-automation-popup-settings__select-type_info-description">
								${0}
							</div>
						</label>
						<div class="bizproc-automation-popup-settings__select-type_images">
							<img src="/bitrix/js/im/robot/message-template-selector/images/template-${0}.svg" alt="${0}">
						</div>
					</div>
			`), isSelected ? '--active' : '', isSelected ? 'checked' : '', babelHelpers.classPrivateFieldLooseBase(this, _onTemplateSelect)[_onTemplateSelect].bind(this), main_core.Text.encode(template.id), main_core.Text.encode(template.name), main_core.Text.encode(template.description), main_core.Text.encode(template.id), main_core.Text.encode(template.name));
	    babelHelpers.classPrivateFieldLooseBase(this, _rows)[_rows].push({
	      template: templateRow,
	      radioButton: templateRadio
	    });
	  });
	  return main_core.Tag.render(_t2 || (_t2 = _`
			<form class="bizproc-automation-popup-settings__select-type">
				${0}
			</form>
		`), babelHelpers.classPrivateFieldLooseBase(this, _rows)[_rows].map(row => row.template));
	}
	function _onTemplateSelect2() {
	  for (const row of babelHelpers.classPrivateFieldLooseBase(this, _rows)[_rows]) {
	    if (row.radioButton.checked) {
	      main_core.Dom.addClass(row.template, '--active');
	    } else {
	      main_core.Dom.removeClass(row.template, '--active');
	    }
	  }
	}
	function _getTemplates2() {
	  return [{
	    id: 'plain',
	    name: main_core.Loc.getMessage('BX_IM_ROBOT_MESSAGE_TEMPLATE_SELECTOR_PLAIN_NAME'),
	    description: main_core.Loc.getMessage('BX_IM_ROBOT_MESSAGE_TEMPLATE_SELECTOR_PLAIN_DESC')
	  }, {
	    id: 'news',
	    name: main_core.Loc.getMessage('BX_IM_ROBOT_MESSAGE_TEMPLATE_SELECTOR_NEWS_NAME'),
	    description: main_core.Loc.getMessage('BX_IM_ROBOT_MESSAGE_TEMPLATE_SELECTOR_NEWS_DESC')
	  }, {
	    id: 'notify',
	    name: main_core.Loc.getMessage('BX_IM_ROBOT_MESSAGE_TEMPLATE_SELECTOR_NOTIFY_NAME'),
	    description: main_core.Loc.getMessage('BX_IM_ROBOT_MESSAGE_TEMPLATE_SELECTOR_NOTIFY_DESC')
	  }, {
	    id: 'important',
	    name: main_core.Loc.getMessage('BX_IM_ROBOT_MESSAGE_TEMPLATE_SELECTOR_IMPORTANT_NAME'),
	    description: main_core.Loc.getMessage('BX_IM_ROBOT_MESSAGE_TEMPLATE_SELECTOR_IMPORTANT_DESC')
	  }, {
	    id: 'alert',
	    name: main_core.Loc.getMessage('BX_IM_ROBOT_MESSAGE_TEMPLATE_SELECTOR_ALERT_NAME'),
	    description: main_core.Loc.getMessage('BX_IM_ROBOT_MESSAGE_TEMPLATE_SELECTOR_ALERT_DESC')
	  }];
	}

	exports.MessageTemplateSelector = MessageTemplateSelector;

}((this.BX.IM.Robot = this.BX.IM.Robot || {}),BX,BX.Event,BX.Main,BX.UI));
//# sourceMappingURL=message-template-selector.bundle.js.map

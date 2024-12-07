/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core,main_popup,ui_buttons) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4;
	class WwallPopup {
	  constructor(options) {
	    this.colorTheme = options.colorTheme || 'danger';
	    this.title = options.title || null;
	    this.subtitle = options.subtitle || null;
	    this.text = options.text || null;
	    this.isToolTipShow = main_core.Type.isBoolean(options.isToolTipShow) ? options.isToolTipShow : false;
	    this.closeIcon = main_core.Type.isBoolean(options.closeIcon) ? options.closeIcon : true;
	    this.isSuccess = main_core.Type.isBoolean(options.isSuccess) ? options.isSuccess : false;
	    this.cache = new main_core.Cache.MemoryCache();
	    this.buttons = options.buttons || null;
	  }
	  getTitleWrapper() {
	    return this.cache.remember('titleBox', () => {
	      return main_core.Tag.render(_t || (_t = _`
				<div class='adm-security-popup_title-box'>
					${0}
				</div>
			`), this.getTitle());
	    });
	  }
	  getTitle() {
	    const title = this.title || main_core.Loc.getMessage('SEC_WWALL_POPUP_TITLE');
	    const toolTip = this.isToolTipShow ? this.getTooltip() : '';
	    return main_core.Tag.render(_t2 || (_t2 = _`
			<div class='adm-security-popup_title'>
				${0}
			</div>
			${0}
		`), title, toolTip);
	  }
	  getTooltip() {
	    return main_core.Tag.render(_t3 || (_t3 = _`
			<a class='tooltip adm-security-popup_tooltip adm-security-info_link' href='https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=35&LESSON_ID=27172' target='_blank'>
				${0}
			</a>
		`), main_core.Loc.getMessage('SEC_WWALL_POPUP_TITLE_ABOUT'));
	  }
	  getContent() {
	    return this.cache.remember('popupContentWarningWrap', () => {
	      return main_core.Tag.render(_t4 || (_t4 = _`
				<div class='adm-security-popup_wrap --${0}'>
					${0}
					<div class='adm-security-popup_content'>
						<div class="adm-security-popup_icon"></div>
						<div class="adm-security-popup_inner">
							<div class="adm-security-popup_inner-title">
								${0} 
							</div>
							<div class="adm-security-popup_info">
								${0}
							</div>
						</div>
					</div>
				</div>
			`), this.colorTheme, this.getTitleWrapper(), this.subtitle || this.getSubtitle(), this.text || this.getPopupInfo());
	    });
	  }
	  getSubtitle() {
	    return this.isSuccess ? main_core.Loc.getMessage('SEC_WWALL_POPUP_ATTACK_TITLE') : this.isPortal ? main_core.Loc.getMessage('SEC_WWALL_POPUP_WARNING_TITLE_CP') : main_core.Loc.getMessage('SEC_WWALL_POPUP_WARNING_TITLE');
	  }
	  getPopupInfo() {
	    return this.isSuccess ? main_core.Loc.getMessage('SEC_WWALL_POPUP_ATTACK_CONTENT') : this.isPortal ? main_core.Loc.getMessage('SEC_WWALL_POPUP_WARNING_CONTENT_CP') : main_core.Loc.getMessage('SEC_WWALL_POPUP_WARNING_CONTENT');
	  }
	  setButtons() {
	    if (this.buttons) {
	      return this.createCustomButtons();
	    }
	    return this.createDefaultButtons();
	  }
	  createDefaultButtons() {
	    return [new ui_buttons.Button({
	      text: this.isSuccess ? main_core.Loc.getMessage('SEC_WWALL_POPUP_ACTION_OPEN') : main_core.Loc.getMessage('SEC_WWALL_POPUP_ACTION_UPDATE'),
	      className: this.isSuccess ? 'adm-security-popup-btn-accept' : 'adm-security-popup-btn-refresh',
	      events: {
	        click: () => {
	          document.location.href = '/bitrix/admin/update_system.php';
	        }
	      }
	    }), new ui_buttons.Button({
	      text: this.isSuccess ? main_core.Loc.getMessage('SEC_WWALL_POPUP_ACTION_CONTINUE') : main_core.Loc.getMessage('SEC_WWALL_POPUP_ACTION_IGNORE'),
	      className: 'adm-security-popup-btn-close',
	      events: {
	        click: () => {
	          this.close();
	        }
	      }
	    })];
	  }
	  createCustomButtons() {
	    const buttons = [];
	    if (this.buttons.primary) {
	      buttons.push(new ui_buttons.Button({
	        text: this.buttons.primary.text,
	        className: this.setButtonStyle(this.buttons.primary.type),
	        events: {
	          click: () => {
	            if (this.buttons.primary.onclick) {
	              this.buttons.primary.onclick();
	            }
	          }
	        }
	      }));
	    }
	    if (this.buttons.secondary) {
	      buttons.push(new ui_buttons.Button({
	        text: this.buttons.secondary.text,
	        className: this.setButtonStyle(this.buttons.secondary.type),
	        events: {
	          click: () => {
	            if (this.buttons.secondary.onclick) {
	              this.buttons.secondary.onclick();
	            }
	          }
	        }
	      }));
	    }
	    return buttons;
	  }
	  setButtonStyle(type) {
	    switch (type) {
	      case 'accept':
	        return 'adm-security-popup-btn-accept';
	      case 'refresh':
	        return 'adm-security-popup-btn-refresh';
	      case 'close':
	      default:
	        return 'adm-security-popup-btn-close';
	    }
	  }
	  show() {
	    this.popup = new main_popup.Popup({
	      className: 'adm-security-popup',
	      closeIcon: this.closeIcon,
	      contentBackground: 'transparent',
	      overlay: true,
	      minWidth: 500,
	      content: this.getContent(),
	      buttons: this.setButtons(),
	      events: {
	        onPopupClose() {
	          this.destroy();
	        },
	        onPopupDestroy() {
	          this.popup = null;
	        }
	      }
	    });
	    this.popup.show();
	  }
	  close() {
	    this.popup.close();
	  }
	}

	exports.WwallPopup = WwallPopup;

}((this.BX.Main = this.BX.Main || {}),BX,BX.Main,BX.UI));
//# sourceMappingURL=wwallpopup.bundle.js.map

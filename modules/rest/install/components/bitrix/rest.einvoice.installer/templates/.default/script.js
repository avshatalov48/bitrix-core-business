/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_loader,main_core,ui_popupcomponentsmaker,ui_buttons,main_core_events,rest_listener,rest_appForm) {
	'use strict';

	let _ = t => t,
	  _t;
	class BasePage extends main_core_events.EventEmitter {
	  constructor() {
	    super();
	    this.setEventNamespace('BX.Rest.EInvoiceInstaller.Page');
	  }
	  getContent() {
	    throw new Error('Must be implemented in a child class');
	  }
	  getIcon() {
	    return main_core.Tag.render(_t || (_t = _`
			<div class="bitrix-einvoice-installer-main-icon-wrapper">
				<div class="bitrix-einvoice-installer-main-icon"></div>
			</div>
		`));
	  }
	  static getType() {
	    throw new Error('Must be implemented in a child class');
	  }
	}

	let _$1 = t => t,
	  _t$1;
	var _content = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("content");
	var _button = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("button");
	var _getButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getButton");
	class ErrorPage extends BasePage {
	  constructor() {
	    super();
	    Object.defineProperty(this, _getButton, {
	      value: _getButton2
	    });
	    Object.defineProperty(this, _content, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _button, {
	      writable: true,
	      value: void 0
	    });
	    this.setEventNamespace('BX.Rest.EInvoiceInstaller.ErrorPage');
	  }
	  static getType() {
	    return 'error';
	  }
	  getContent() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _content)[_content]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _content)[_content] = main_core.Tag.render(_t$1 || (_t$1 = _$1`
				<div class="bitrix-einvoice-installer-content --error-content">
					<div class="bitrix-einvoice-installer-error-icon"></div>
					<div class="bitrix-einvoice-installer-title-install">${0}</div>
					${0}
				</div>
			`), main_core.Loc.getMessage('REST_EINVOICE_INSTALLER_ERROR_TITLE'), babelHelpers.classPrivateFieldLooseBase(this, _getButton)[_getButton]());
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _content)[_content];
	  }
	}
	function _getButton2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _button)[_button]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _button)[_button] = new ui_buttons.Button({
	      text: main_core.Loc.getMessage('REST_EINVOICE_INSTALLER_ERROR_BUTTON'),
	      size: ui_buttons.Button.Size.LARGE,
	      color: ui_buttons.Button.Color.SUCCESS,
	      className: 'bitrix-einvoice-installer-button-try-again',
	      onclick: () => {
	        this.emit('go-back');
	      }
	    }).getContainer();
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _button)[_button];
	}

	let _$2 = t => t,
	  _t$2,
	  _t2;
	var _content$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("content");
	var _title = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("title");
	var _loader = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loader");
	var _onAfterUnsuccessfulInstallApplication = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onAfterUnsuccessfulInstallApplication");
	var _getTitle = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getTitle");
	var _getLoader = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getLoader");
	class InstallPage extends BasePage {
	  static getType() {
	    return 'install';
	  }
	  constructor() {
	    super();
	    Object.defineProperty(this, _getLoader, {
	      value: _getLoader2
	    });
	    Object.defineProperty(this, _getTitle, {
	      value: _getTitle2
	    });
	    Object.defineProperty(this, _onAfterUnsuccessfulInstallApplication, {
	      value: _onAfterUnsuccessfulInstallApplication2
	    });
	    Object.defineProperty(this, _content$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _title, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _loader, {
	      writable: true,
	      value: void 0
	    });
	    this.setEventNamespace('BX.Rest.EInvoiceInstaller.InstallPage');
	    this.subscribe('install-app', event => {
	      babelHelpers.classPrivateFieldLooseBase(this, _getLoader)[_getLoader]().show();
	      const installer = event.data.source;
	      event.data.install.then(response => {
	        if (!response.status) {
	          return Promise.reject();
	        }
	        return rest_appForm.AppForm.buildByApp(response.data.id, rest_appForm.EventType.INSTALL);
	      }).then(appForm => {
	        babelHelpers.classPrivateFieldLooseBase(this, _getLoader)[_getLoader]().hide();
	        appForm.show();
	        installer.render('selection');
	      }).catch(() => {
	        babelHelpers.classPrivateFieldLooseBase(this, _getLoader)[_getLoader]().hide();
	        babelHelpers.classPrivateFieldLooseBase(this, _onAfterUnsuccessfulInstallApplication)[_onAfterUnsuccessfulInstallApplication]();
	      });
	    });
	  }
	  getContent() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _content$1)[_content$1]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _content$1)[_content$1] = main_core.Tag.render(_t$2 || (_t$2 = _$2`
				<div class="bitrix-einvoice-installer-content">
					${0}
					<div class="bitrix-einvoice-installer-loader-wrapper-install"/>
				</div>
			`), babelHelpers.classPrivateFieldLooseBase(this, _getTitle)[_getTitle]());
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _content$1)[_content$1];
	  }
	}
	function _onAfterUnsuccessfulInstallApplication2() {
	  this.emit('install-error');
	}
	function _getTitle2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _title)[_title]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _title)[_title] = main_core.Tag.render(_t2 || (_t2 = _$2`
				<div class="bitrix-einvoice-installer-title-install">
					${0}
				</div>
			`), main_core.Loc.getMessage('REST_EINVOICE_INSTALLER_INSTALL_TITLE'));
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _title)[_title];
	}
	function _getLoader2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _loader)[_loader]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _loader)[_loader] = new main_loader.Loader({
	      target: this.getContent().querySelector('.bitrix-einvoice-installer-loader-wrapper-install'),
	      size: 90,
	      color: '#2FC6F6',
	      mode: 'inline'
	    });
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _loader)[_loader];
	}

	let _$3 = t => t,
	  _t$3,
	  _t2$1;
	var _popup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("popup");
	var _apps = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("apps");
	var _popupContent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("popupContent");
	var _formConfiguration = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("formConfiguration");
	var _getPopup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getPopup");
	var _getPopupContent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getPopupContent");
	var _showFormForOffer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showFormForOffer");
	class EInvoiceAppButton extends main_core_events.EventEmitter {
	  constructor(apps, formConfiguration) {
	    super();
	    Object.defineProperty(this, _showFormForOffer, {
	      value: _showFormForOffer2
	    });
	    Object.defineProperty(this, _getPopupContent, {
	      value: _getPopupContent2
	    });
	    Object.defineProperty(this, _getPopup, {
	      value: _getPopup2
	    });
	    Object.defineProperty(this, _popup, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _apps, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _popupContent, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _formConfiguration, {
	      writable: true,
	      value: void 0
	    });
	    this.setEventNamespace('BX.Rest.EInvoiceAppButton');
	    babelHelpers.classPrivateFieldLooseBase(this, _apps)[_apps] = apps;
	    babelHelpers.classPrivateFieldLooseBase(this, _formConfiguration)[_formConfiguration] = formConfiguration;
	  }
	  getContent() {
	    const button = new ui_buttons.Button({
	      text: main_core.Loc.getMessage('REST_EINVOICE_INSTALLER_SELECTION_BUTTON'),
	      size: ui_buttons.Button.Size.LARGE,
	      color: ui_buttons.Button.Color.SUCCESS,
	      dropdown: true,
	      className: 'bitrix-einvoice-installer-button-select',
	      onclick: event => {
	        const popup = babelHelpers.classPrivateFieldLooseBase(this, _getPopup)[_getPopup](event.button);
	        popup.getPopup().setMaxWidth(event.button.offsetWidth);
	        popup.getPopup().toggle();
	      }
	    });
	    return button.getContainer();
	  }
	}
	function _getPopup2(bindElement) {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup]) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup];
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup] = new ui_popupcomponentsmaker.PopupComponentsMaker({
	    target: bindElement,
	    content: babelHelpers.classPrivateFieldLooseBase(this, _getPopupContent)[_getPopupContent](),
	    useAngle: false
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup].getPopup().setOffset({
	    offsetLeft: 0,
	    offsetTop: 0
	  });
	  this.subscribe('popup-close', () => {
	    babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup].close();
	  });
	  return babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup];
	}
	function _getPopupContent2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _popupContent)[_popupContent]) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _popupContent)[_popupContent];
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _popupContent)[_popupContent] = [];
	  babelHelpers.classPrivateFieldLooseBase(this, _apps)[_apps].forEach(app => {
	    const onclick = () => {
	      this.emit('popup-close');
	      this.emit('click-app', new main_core_events.BaseEvent({
	        data: {
	          code: app.code,
	          name: app.name
	        }
	      }));
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _popupContent)[_popupContent].push({
	      html: main_core.Tag.render(_t$3 || (_t$3 = _$3`
					<div onclick="${0}" class="bitrix-einvoice-installer-app-wrapper">
						<div class="bitrix-einvoice-installer-app-name">
							${0}
						</div>
					</div>
				`), onclick, main_core.Text.encode(app.name))
	    });
	  });
	  const showForm = () => {
	    babelHelpers.classPrivateFieldLooseBase(this, _showFormForOffer)[_showFormForOffer]();
	    this.emit('popup-close');
	  };
	  babelHelpers.classPrivateFieldLooseBase(this, _popupContent)[_popupContent].push({
	    html: main_core.Tag.render(_t2$1 || (_t2$1 = _$3`
				<div onclick="${0}" class="bitrix-einvoice-installer-app-wrapper --form">
					${0}
				</div>
			`), showForm, main_core.Loc.getMessage('REST_EINVOICE_INSTALLER_SELECTION_BUTTON_OFFER'))
	  });
	  return babelHelpers.classPrivateFieldLooseBase(this, _popupContent)[_popupContent];
	}
	function _showFormForOffer2() {
	  BX.UI.Feedback.Form.open({
	    id: 'b5309667',
	    forms: [{
	      zones: ['en'],
	      id: 674,
	      lang: 'en',
	      sec: '5iorws'
	    }, {
	      zones: ['es'],
	      id: 676,
	      lang: 'es',
	      sec: 'uthphh'
	    }, {
	      zones: ['de'],
	      id: 670,
	      lang: 'de',
	      sec: 'gk89kt'
	    }, {
	      zones: ['com.br'],
	      id: 668,
	      lang: 'br',
	      sec: 'kuelnm'
	    }],
	    presets: {
	      ...babelHelpers.classPrivateFieldLooseBase(this, _formConfiguration)[_formConfiguration],
	      sender_page: document.location.href
	    }
	  });
	}

	let _$4 = t => t,
	  _t$4,
	  _t2$2,
	  _t3,
	  _t4;
	var _content$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("content");
	var _title$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("title");
	var _description = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("description");
	var _button$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("button");
	var _apps$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("apps");
	var _formConfiguration$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("formConfiguration");
	var _moreInformation = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("moreInformation");
	var _getTitle$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getTitle");
	var _getDescription = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDescription");
	var _getButton$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getButton");
	var _getMoreInformation = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getMoreInformation");
	class SelectionPage extends BasePage {
	  constructor(apps, formConfiguration) {
	    super();
	    Object.defineProperty(this, _getMoreInformation, {
	      value: _getMoreInformation2
	    });
	    Object.defineProperty(this, _getButton$1, {
	      value: _getButton2$1
	    });
	    Object.defineProperty(this, _getDescription, {
	      value: _getDescription2
	    });
	    Object.defineProperty(this, _getTitle$1, {
	      value: _getTitle2$1
	    });
	    Object.defineProperty(this, _content$2, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _title$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _description, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _button$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _apps$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _formConfiguration$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _moreInformation, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _apps$1)[_apps$1] = apps;
	    babelHelpers.classPrivateFieldLooseBase(this, _formConfiguration$1)[_formConfiguration$1] = formConfiguration;
	    this.setEventNamespace('BX.Rest.EInvoiceInstaller.SelectionPage');
	  }
	  static getType() {
	    return 'selection';
	  }
	  getContent() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _content$2)[_content$2]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _content$2)[_content$2] = main_core.Tag.render(_t$4 || (_t$4 = _$4`
				<div class="bitrix-einvoice-installer-content">
					${0}
					${0}
					${0}
					${0}
				</div>
			`), babelHelpers.classPrivateFieldLooseBase(this, _getTitle$1)[_getTitle$1](), babelHelpers.classPrivateFieldLooseBase(this, _getDescription)[_getDescription](), babelHelpers.classPrivateFieldLooseBase(this, _getButton$1)[_getButton$1](), babelHelpers.classPrivateFieldLooseBase(this, _getMoreInformation)[_getMoreInformation]());
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _content$2)[_content$2];
	  }
	}
	function _getTitle2$1() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _title$1)[_title$1]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _title$1)[_title$1] = main_core.Tag.render(_t2$2 || (_t2$2 = _$4`
				<div class="bitrix-einvoice-installer-title__wrapper">
					<div class="bitrix-einvoice-installer-title__main-text">
						${0}
					</div>
				</div>
			`), main_core.Loc.getMessage('REST_EINVOICE_INSTALLER_SELECTION_TITLE'));
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _title$1)[_title$1];
	}
	function _getDescription2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _description)[_description]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _description)[_description] = main_core.Tag.render(_t3 || (_t3 = _$4`
				<div class="bitrix-einvoice-installer-description">
					${0}
				</div>
			`), main_core.Loc.getMessage('REST_EINVOICE_INSTALLER_SELECTION_DESCRIPTION'));
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _description)[_description];
	}
	function _getButton2$1() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _button$1)[_button$1]) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _button$1)[_button$1];
	  }
	  const buttonConstructor = new EInvoiceAppButton(babelHelpers.classPrivateFieldLooseBase(this, _apps$1)[_apps$1], babelHelpers.classPrivateFieldLooseBase(this, _formConfiguration$1)[_formConfiguration$1]);
	  babelHelpers.classPrivateFieldLooseBase(this, _button$1)[_button$1] = buttonConstructor.getContent();
	  buttonConstructor.subscribe('click-app', event => {
	    this.emit('start-install-app', event);
	  });
	  return babelHelpers.classPrivateFieldLooseBase(this, _button$1)[_button$1];
	}
	function _getMoreInformation2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _moreInformation)[_moreInformation]) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _moreInformation)[_moreInformation];
	  }
	  const onclick = () => {
	    top.BX.Helper.show('redirect=detail&code=19312840');
	  };
	  babelHelpers.classPrivateFieldLooseBase(this, _moreInformation)[_moreInformation] = main_core.Tag.render(_t4 || (_t4 = _$4`
			<div class="bitrix-einvoice-installer-more-information-wrapper">
				<div onclick="${0}" class="bitrix-einvoice-installer-more-information-link">
					${0}
				</div>
			</div>
		`), onclick, main_core.Loc.getMessage('REST_EINVOICE_INSTALLER_MORE'));
	  return babelHelpers.classPrivateFieldLooseBase(this, _moreInformation)[_moreInformation];
	}

	var _selectionPage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("selectionPage");
	var _installPage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("installPage");
	var _errorPage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("errorPage");
	var _options = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("options");
	var _types = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("types");
	var _getSelectionPage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getSelectionPage");
	var _getInstallPage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getInstallPage");
	var _getErrorPage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getErrorPage");
	var _registerPageHandlers = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("registerPageHandlers");
	class PageProvider extends main_core_events.EventEmitter {
	  constructor(options) {
	    super();
	    Object.defineProperty(this, _registerPageHandlers, {
	      value: _registerPageHandlers2
	    });
	    Object.defineProperty(this, _getErrorPage, {
	      value: _getErrorPage2
	    });
	    Object.defineProperty(this, _getInstallPage, {
	      value: _getInstallPage2
	    });
	    Object.defineProperty(this, _getSelectionPage, {
	      value: _getSelectionPage2
	    });
	    Object.defineProperty(this, _selectionPage, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _installPage, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _errorPage, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _options, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _types, {
	      writable: true,
	      value: void 0
	    });
	    this.setEventNamespace('BX.Rest.EInvoiceInstaller.PageProvider');
	    babelHelpers.classPrivateFieldLooseBase(this, _options)[_options] = options;
	    babelHelpers.classPrivateFieldLooseBase(this, _types)[_types] = [SelectionPage.getType(), InstallPage.getType(), ErrorPage.getType()];
	  }
	  exist(type) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _types)[_types].includes(type);
	  }
	  getPageByType(type) {
	    switch (type) {
	      case SelectionPage.getType():
	        return babelHelpers.classPrivateFieldLooseBase(this, _getSelectionPage)[_getSelectionPage]();
	      case InstallPage.getType():
	        return babelHelpers.classPrivateFieldLooseBase(this, _getInstallPage)[_getInstallPage]();
	      case ErrorPage.getType():
	        return babelHelpers.classPrivateFieldLooseBase(this, _getErrorPage)[_getErrorPage]();
	      default:
	        throw new Error('Incorrect page type');
	    }
	  }
	}
	function _getSelectionPage2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _selectionPage)[_selectionPage]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _selectionPage)[_selectionPage] = new SelectionPage(babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].apps, babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].formConfiguration);
	    babelHelpers.classPrivateFieldLooseBase(this, _registerPageHandlers)[_registerPageHandlers](babelHelpers.classPrivateFieldLooseBase(this, _selectionPage)[_selectionPage]);
	    babelHelpers.classPrivateFieldLooseBase(this, _selectionPage)[_selectionPage].subscribe('start-install-app', () => {
	      this.emit('render', new main_core_events.BaseEvent({
	        data: {
	          type: InstallPage.getType()
	        }
	      }));
	    });
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _selectionPage)[_selectionPage];
	}
	function _getInstallPage2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _installPage)[_installPage]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _installPage)[_installPage] = new InstallPage();
	    babelHelpers.classPrivateFieldLooseBase(this, _registerPageHandlers)[_registerPageHandlers](babelHelpers.classPrivateFieldLooseBase(this, _installPage)[_installPage]);
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _installPage)[_installPage];
	}
	function _getErrorPage2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _errorPage)[_errorPage]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _errorPage)[_errorPage] = new ErrorPage();
	    babelHelpers.classPrivateFieldLooseBase(this, _registerPageHandlers)[_registerPageHandlers](babelHelpers.classPrivateFieldLooseBase(this, _errorPage)[_errorPage]);
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _errorPage)[_errorPage];
	}
	function _registerPageHandlers2(page) {
	  page.subscribe('go-back', () => {
	    this.emit('render', new main_core_events.BaseEvent({
	      data: {
	        type: SelectionPage.getType()
	      }
	    }));
	  });
	  page.subscribe('install-error', () => {
	    this.emit('render', new main_core_events.BaseEvent({
	      data: {
	        type: ErrorPage.getType()
	      }
	    }));
	  });
	}

	var _options$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("options");
	var _pageProvider = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("pageProvider");
	var _formListener = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("formListener");
	var _installApplicationByCode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("installApplicationByCode");
	var _onStartInstall = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onStartInstall");
	var _getPageProvider = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getPageProvider");
	class EInvoiceInstaller extends main_core_events.EventEmitter {
	  constructor(options) {
	    super();
	    Object.defineProperty(this, _getPageProvider, {
	      value: _getPageProvider2
	    });
	    Object.defineProperty(this, _onStartInstall, {
	      value: _onStartInstall2
	    });
	    Object.defineProperty(this, _installApplicationByCode, {
	      value: _installApplicationByCode2
	    });
	    Object.defineProperty(this, _options$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _pageProvider, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _formListener, {
	      writable: true,
	      value: void 0
	    });
	    this.setEventNamespace('BX.Rest.EInvoiceInstaller');
	    babelHelpers.classPrivateFieldLooseBase(this, _options$1)[_options$1] = options;
	    this.render('selection');
	    babelHelpers.classPrivateFieldLooseBase(this, _getPageProvider)[_getPageProvider]().getPageByType('selection').subscribe('start-install-app', babelHelpers.classPrivateFieldLooseBase(this, _onStartInstall)[_onStartInstall].bind(this));
	  }
	  render(pageType) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _getPageProvider)[_getPageProvider]().exist(pageType)) {
	      main_core.Dom.clean(babelHelpers.classPrivateFieldLooseBase(this, _options$1)[_options$1].wrapper);
	      const page = babelHelpers.classPrivateFieldLooseBase(this, _getPageProvider)[_getPageProvider]().getPageByType(pageType);
	      main_core.Dom.append(page.getContent(), babelHelpers.classPrivateFieldLooseBase(this, _options$1)[_options$1].wrapper);
	      if (pageType !== 'error') {
	        main_core.Dom.append(page.getIcon(), babelHelpers.classPrivateFieldLooseBase(this, _options$1)[_options$1].wrapper);
	      }
	    }
	  }
	}
	function _installApplicationByCode2(code) {
	  return main_core.ajax.runComponentAction('bitrix:rest.einvoice.installer', 'installApplicationByCode', {
	    mode: 'class',
	    data: {
	      code: code
	    }
	  });
	}
	function _onStartInstall2(event) {
	  babelHelpers.classPrivateFieldLooseBase(this, _getPageProvider)[_getPageProvider]().getPageByType('install').emit('install-app', new main_core_events.BaseEvent({
	    data: {
	      source: this,
	      code: event.data.code,
	      name: event.data.name,
	      install: babelHelpers.classPrivateFieldLooseBase(this, _installApplicationByCode)[_installApplicationByCode](event.data.code)
	    }
	  }));
	}
	function _getPageProvider2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _pageProvider)[_pageProvider]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _pageProvider)[_pageProvider] = new PageProvider(babelHelpers.classPrivateFieldLooseBase(this, _options$1)[_options$1]);
	    babelHelpers.classPrivateFieldLooseBase(this, _pageProvider)[_pageProvider].subscribe('render', event => {
	      this.render(event.data.type);
	    });
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _pageProvider)[_pageProvider];
	}

	exports.EInvoiceInstaller = EInvoiceInstaller;

}((this.BX.Rest = this.BX.Rest || {}),BX,BX,BX.UI,BX.UI,BX.Event,BX.Rest,BX.Rest));
//# sourceMappingURL=script.js.map

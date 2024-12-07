/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_core) {
	'use strict';

	var _list = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("list");
	var _loadedList = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loadedList");
	var _opened = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("opened");
	var _appendFormToSlider = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("appendFormToSlider");
	var _appendFormScript = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("appendFormScript");
	var _handleB24FormInit = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleB24FormInit");
	class Form {
	  static getList() {
	    return babelHelpers.classPrivateFieldLooseBase(Form, _list)[_list];
	  }
	  static getById(id) {
	    return babelHelpers.classPrivateFieldLooseBase(Form, _list)[_list].find(form => {
	      return form.id === id;
	    }) || null;
	  }
	  static open(formOptions) {
	    if (babelHelpers.classPrivateFieldLooseBase(Form, _opened)[_opened]) {
	      return;
	    }
	    const formId = formOptions.id;
	    const loadedForm = babelHelpers.classPrivateFieldLooseBase(Form, _loadedList)[_loadedList][formId];
	    if (loadedForm) {
	      loadedForm.openPanel();
	      return;
	    }
	    const form = new Form({
	      map: formOptions
	    });
	    babelHelpers.classPrivateFieldLooseBase(Form, _loadedList)[_loadedList][formOptions.id] = form;
	    form.openPanel();
	  }
	  /**
	   * @deprecated use static method open
	   * @param formOptions
	   */
	  constructor(formOptions) {
	    Object.defineProperty(this, _handleB24FormInit, {
	      value: _handleB24FormInit2
	    });
	    Object.defineProperty(this, _appendFormScript, {
	      value: _appendFormScript2
	    });
	    Object.defineProperty(this, _appendFormToSlider, {
	      value: _appendFormToSlider2
	    });
	    this.init(formOptions);
	    babelHelpers.classPrivateFieldLooseBase(Form, _list)[_list].push(this);
	  }
	  init(formOptions) {
	    this.cached = false;
	    if (formOptions.map !== undefined) {
	      this.map = formOptions.map;
	      return;
	    }
	    this.id = formOptions.id;
	    this.portal = formOptions.portal;
	    this.presets = formOptions.presets || {};
	    this.form = formOptions.form || {};
	    this.title = formOptions.title || '';
	    if (formOptions.button) {
	      this.button = BX(formOptions.button);
	      main_core.Event.bind(this.button, 'click', this.openPanel.bind(this));
	    }
	  }
	  appendPresets(presets) {
	    Object.entries(presets).forEach(([key, value]) => {
	      this.presets[key] = value;
	    });
	  }
	  openPanel() {
	    babelHelpers.classPrivateFieldLooseBase(Form, _opened)[_opened] = true;
	    BX.SidePanel.Instance.open(`ui:feedback-form-${this.id}`, {
	      cacheable: false,
	      contentCallback: () => {
	        return Promise.resolve();
	      },
	      animationDuration: 200,
	      events: {
	        onLoad: this.checkSidePanelLoad.bind(this),
	        onBeforeCloseComplete: this.checkSidePanelClosed.bind(this)
	      },
	      width: 600
	    });
	  }
	  checkSidePanelClosed() {
	    babelHelpers.classPrivateFieldLooseBase(Form, _opened)[_opened] = false;
	  }
	  checkSidePanelLoad(event) {
	    if (this.map && this.cached === false) {
	      main_core.ajax.runAction('ui.feedback.loadData', {
	        json: {
	          title: this.map.title || null,
	          id: this.map.id || null,
	          presets: this.map.presets || null,
	          portalUri: this.map.portalUri || null,
	          forms: this.map.forms || null,
	          defaultForm: this.map.defaultForm || null
	        }
	      }).then(response => {
	        const params = response.data.params;
	        this.id = params.id;
	        this.title = params.title;
	        this.form = params.form;
	        this.presets = params.presets;
	        this.portal = params.portal;
	        this.cached = true;
	        this.onSidePanelLoad(event);
	      }).catch(response => {
	        console.error(response);
	      });
	      return;
	    }
	    this.onSidePanelLoad(event);
	  }
	  onSidePanelLoad(event) {
	    const slider = event.getSlider();
	    if (!slider) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _appendFormToSlider)[_appendFormToSlider](slider);
	    setTimeout(() => {
	      slider.showLoader();
	    }, 0);
	    this.loadForm(this.checkLoader.bind(this, slider));
	  }
	  checkLoader(slider) {
	    setTimeout(() => {
	      slider.closeLoader();
	    }, 100);
	  }
	  loadForm(callback) {
	    const form = this.form;
	    if (!form || !form.id || !form.lang || !form.sec) {
	      return;
	    }
	    if (form.presets) {
	      this.appendPresets(form.presets);
	    }
	    const objectId = `b24form${this.id}`;
	    babelHelpers.classPrivateFieldLooseBase(this, _appendFormScript)[_appendFormScript](`${this.portal}/bitrix/js/crm/form_loader.js`, objectId);
	    main_core.Event.bind(top, 'b24:form:init', babelHelpers.classPrivateFieldLooseBase(this, _handleB24FormInit)[_handleB24FormInit]);
	    top[objectId]({
	      id: form.id,
	      lang: form.lang,
	      sec: form.sec,
	      type: 'inline',
	      node: this.formNode,
	      presets: this.presets,
	      handlers: {
	        load: callback
	      }
	    });
	  }
	}
	function _appendFormToSlider2(slider) {
	  if (!slider) {
	    return;
	  }
	  this.formNode = main_core.Dom.create('div');
	  const titleNode = main_core.Dom.create('div', {
	    style: {
	      marginBottom: '25px',
	      font: '26px/26px var(--ui-font-family-primary, var(--ui-font-family-helvetica))',
	      color: 'var(--ui-color-text-primary)'
	    },
	    text: this.title
	  });
	  const containerNode = main_core.Dom.create('div', {
	    style: {
	      padding: '20px',
	      overflowY: 'auto'
	    },
	    children: [titleNode, this.formNode]
	  });
	  main_core.Dom.append(containerNode, slider.layout.content);
	}
	function _appendFormScript2(u, b) {
	  top.Bitrix24FormObject = b;
	  top[b] = top[b] || function () {
	    // eslint-disable-next-line prefer-rest-params
	    arguments[0].ref = u;
	    // eslint-disable-next-line prefer-rest-params
	    (top[b].forms = top[b].forms || []).push(arguments[0]);
	  };
	  if (top[b].forms) {
	    return;
	  }
	  const scriptElement = top.document.createElement('script');
	  const r = Date.now();
	  scriptElement.async = 1;
	  scriptElement.src = `${u}?${r}`;
	  const h = top.document.getElementsByTagName('script')[0];
	  main_core.Dom.insertBefore(scriptElement, h);
	}
	function _handleB24FormInit2(event) {
	  const eventForm = event.detail.object;
	  eventForm.design.setFont('var(--ui-font-family-primary),var(--ui-font-family-helvetica)');
	}
	Object.defineProperty(Form, _list, {
	  writable: true,
	  value: []
	});
	Object.defineProperty(Form, _loadedList, {
	  writable: true,
	  value: {}
	});
	Object.defineProperty(Form, _opened, {
	  writable: true,
	  value: false
	});

	exports.Form = Form;

}((this.BX.UI.Feedback = this.BX.UI.Feedback || {}),BX));
//# sourceMappingURL=form.bundle.js.map

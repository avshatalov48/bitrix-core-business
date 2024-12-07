/* eslint-disable */
this.BX = this.BX || {};
(function (exports,catalog_config_settings,main_core,ui_sidepanel) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2;
	var _getStubLayout = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getStubLayout");
	class ExternalCatalogStub {
	  static showCatalogStub() {
	    ui_sidepanel.SidePanel.Instance.open('catalog:external-catalog-stub', {
	      contentCallback: slider => {
	        return babelHelpers.classPrivateFieldLooseBase(ExternalCatalogStub, _getStubLayout)[_getStubLayout]({
	          title: main_core.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_EMPTY_STATE_TITLE_CATALOG'),
	          text: main_core.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_EMPTY_STATE_CATALOG_TEXT'),
	          icon: '1c'
	        });
	      },
	      width: 880
	    });
	  }
	  static showDocsStub() {
	    ui_sidepanel.SidePanel.Instance.open('catalog:external-catalog-docs-stub', {
	      contentCallback: slider => {
	        return babelHelpers.classPrivateFieldLooseBase(ExternalCatalogStub, _getStubLayout)[_getStubLayout]({
	          title: main_core.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_EMPTY_STATE_TITLE'),
	          text: main_core.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_EMPTY_STATE_DOCS_TEXT'),
	          icon: 'docs'
	        });
	      },
	      width: 880
	    });
	  }
	}
	function _getStubLayout2(params) {
	  const settingsButton = main_core.Tag.render(_t || (_t = _`
			<div class="ui-btn ui-btn-success ui-btn-round ui-btn-lg">
				${0}
			</div>
		`), main_core.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_EMPTY_STATE_BUTTON'));
	  main_core.Event.bind(settingsButton, 'click', () => {
	    catalog_config_settings.Slider.open();
	  });
	  return main_core.Tag.render(_t2 || (_t2 = _`
			<div class="inventory-management__empty-state --1c">
				<div class="inventory-management__empty-state-title">
					${0}
				</div>
				<div class="inventory-management__empty-state-text">
					${0}
				</div>
				<div class="inventory-management__empty-state-logo --${0}"></div>
				${0}
			</div>
		`), params.title, params.text, params.icon, settingsButton);
	}
	Object.defineProperty(ExternalCatalogStub, _getStubLayout, {
	  value: _getStubLayout2
	});

	exports.ExternalCatalogStub = ExternalCatalogStub;

}((this.BX.Catalog = this.BX.Catalog || {}),BX.Catalog.Config,BX,BX));
//# sourceMappingURL=external-catalog-stub.bundle.js.map

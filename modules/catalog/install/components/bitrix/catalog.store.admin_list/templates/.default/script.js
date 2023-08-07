(function (exports) {
	'use strict';

	BX.namespace('BX.Catalog.Store');
	var Grid = /*#__PURE__*/function () {
	  function Grid() {
	    babelHelpers.classCallCheck(this, Grid);
	  }
	  babelHelpers.createClass(Grid, null, [{
	    key: "init",
	    value: function init(settings) {
	      Grid.gridId = settings.gridId;
	      Grid.tariff = settings.tariff;
	    }
	  }, {
	    key: "openStoreCreation",
	    value: function openStoreCreation(event) {
	      Grid.openStoreSlider();
	    }
	  }, {
	    key: "openStoreSlider",
	    value: function openStoreSlider() {
	      var id = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 0;
	      var url = '/shop/documents-stores/details/' + parseInt(id) + '/';
	      BX.SidePanel.Instance.open(url, {
	        allowChangeHistory: true,
	        cacheable: false,
	        width: 500,
	        events: {
	          onClose: function onClose(event) {
	            var grid = BX.Main.gridManager.getInstanceById(Grid.gridId);
	            if (grid) {
	              grid.reload();
	            }
	          }
	        }
	      });
	    }
	  }, {
	    key: "openTariffHelp",
	    value: function openTariffHelp() {
	      if (Grid.tariff !== '') {
	        BX.UI.InfoHelper.show(Grid.tariff);
	      }
	    }
	  }, {
	    key: "openUfSilder",
	    value: function openUfSilder(e, item) {
	      e.preventDefault();
	      BX.SidePanel.Instance.open(item.options.href, {
	        allowChangeHistory: false,
	        cacheable: false
	      });
	    }
	  }]);
	  return Grid;
	}();
	BX.Catalog.Store.Grid = Grid;

}((this.window = this.window || {})));
//# sourceMappingURL=script.js.map

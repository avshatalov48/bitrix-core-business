this.BX = this.BX || {};
(function (exports,main_core,main_core_events) {
	'use strict';

	var barcodeScannerPool = new Map();
	var BarcodeScanner = /*#__PURE__*/function () {
	  function BarcodeScanner() {
	    babelHelpers.classCallCheck(this, BarcodeScanner);
	    this.pool = [];
	    main_core_events.EventEmitter.subscribe('onPullEvent-catalog', this.onPullEvent.bind(this));
	  }

	  babelHelpers.createClass(BarcodeScanner, [{
	    key: "onPullEvent",
	    value: function onPullEvent(event) {
	      var data = event.getData();
	      var command = data[0];
	      var params = main_core.Type.isObjectLike(data[1]) ? data[1] : {};

	      switch (command) {
	        case 'HandleBarcodeScanned':
	          if (params.hasOwnProperty('id')) {
	            var scanner = barcodeScannerPool.has(params.id);

	            if (scanner) {
	              main_core_events.EventEmitter.emit('BarcodeScanner::onScanEmit', params);
	            }
	          }

	          break;
	      }
	    }
	  }], [{
	    key: "open",
	    value: function open() {
	      var id = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 'default';

	      if (!barcodeScannerPool.has(id)) {
	        var scanner = new BarcodeScanner();
	        barcodeScannerPool.set(id, scanner);
	      }

	      main_core.ajax.runAction('catalog.barcodescanner.sendMobilePush', {
	        data: {
	          id: id
	        }
	      });
	    }
	  }]);
	  return BarcodeScanner;
	}();

	exports.BarcodeScanner = BarcodeScanner;

}((this.BX.Catalog = this.BX.Catalog || {}),BX,BX.Event));
//# sourceMappingURL=barcode-scanner.bundle.js.map

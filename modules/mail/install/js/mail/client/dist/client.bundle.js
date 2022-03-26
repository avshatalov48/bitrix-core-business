this.BX = this.BX || {};
(function (exports,mail_client_filtertoolbar,mail_client_binding,main_core_events) {
	'use strict';

	var _filter = /*#__PURE__*/new WeakMap();

	var _filterToolbar = /*#__PURE__*/new WeakMap();

	var _binding = /*#__PURE__*/new WeakMap();

	var _mailboxId = /*#__PURE__*/new WeakMap();

	var Client = /*#__PURE__*/function () {
	  function Client() {
	    var config = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {
	      filterId: '',
	      mailboxId: 0
	    };
	    babelHelpers.classCallCheck(this, Client);

	    _filter.set(this, {
	      writable: true,
	      value: void 0
	    });

	    _filterToolbar.set(this, {
	      writable: true,
	      value: void 0
	    });

	    _binding.set(this, {
	      writable: true,
	      value: void 0
	    });

	    _mailboxId.set(this, {
	      writable: true,
	      value: void 0
	    });

	    babelHelpers.classPrivateFieldSet(this, _mailboxId, config['mailboxId']);
	    babelHelpers.classPrivateFieldSet(this, _filter, BX.Main.filterManager.getById(config['filterId']));
	    var mailCounterWrapper = document.querySelector('[data-role="mail-counter-toolbar"]');
	    var filterToolbar = new mail_client_filtertoolbar.FilterToolbar({
	      wrapper: mailCounterWrapper,
	      filter: babelHelpers.classPrivateFieldGet(this, _filter)
	    });
	    filterToolbar.build();
	    babelHelpers.classPrivateFieldSet(this, _filterToolbar, filterToolbar);
	    babelHelpers.classPrivateFieldSet(this, _binding, new mail_client_binding.Binding(babelHelpers.classPrivateFieldGet(this, _mailboxId)));
	    mail_client_binding.Binding.initButtons();
	    main_core_events.EventEmitter.subscribe('Grid::updated', function (event) {
	      var _event$getCompatData = event.getCompatData(),
	          _event$getCompatData2 = babelHelpers.slicedToArray(_event$getCompatData, 1),
	          grid = _event$getCompatData2[0];

	      if (grid !== {} && grid !== undefined && BX.Mail.Home.Grid.getId() === grid.getId()) {
	        mail_client_binding.Binding.initButtons();
	      }
	    });
	  }

	  babelHelpers.createClass(Client, [{
	    key: "getFilterToolbar",
	    value: function getFilterToolbar() {
	      return babelHelpers.classPrivateFieldGet(this, _filterToolbar);
	    }
	  }]);
	  return Client;
	}();

	exports.Client = Client;

}((this.BX.Mail = this.BX.Mail || {}),BX.Mail.Client,BX.Mail.Client,BX.Event));
//# sourceMappingURL=client.bundle.js.map

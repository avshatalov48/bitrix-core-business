this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports) {
	'use strict';

	var Disk = /*#__PURE__*/function () {
	  function Disk() {
	    babelHelpers.classCallCheck(this, Disk);
	  }

	  babelHelpers.createClass(Disk, null, [{
	    key: "openDialog",
	    value: function openDialog(_ref) {
	      var onSelect = _ref.onSelect;
	      var urlSelect = '/bitrix/tools/disk/uf.php?action=selectFile&dialog2=Y&SITE_ID=' + BX.message('SITE_ID');
	      var dialogName = 'LandingDiskFile';
	      BX.ajax.get(urlSelect, 'multiselect=N&dialogName=' + dialogName, BX.delegate(function () {
	        setTimeout(BX.delegate(function () {
	          BX.DiskFileDialog.obElementBindPopup[dialogName].overlay = {
	            backgroundColor: '#cdcdcd',
	            opacity: '.1'
	          };
	          BX.DiskFileDialog.obCallback[dialogName] = {
	            saveButton: function (tab, path, selected) {
	              var selectedItem = selected[Object.keys(selected)[0]];

	              if (!selectedItem) {
	                return;
	              }

	              var fileId = selectedItem.id;

	              if (fileId[0] === 'n') {
	                fileId = fileId.substring(1);
	              }

	              if (onSelect) {
	                onSelect(fileId);
	              }
	            }.bind(this)
	          };
	          BX.DiskFileDialog.openDialog(dialogName);
	        }, this), 10);
	      }, this));
	    }
	  }]);
	  return Disk;
	}();

	exports.Disk = Disk;

}((this.BX.Landing.Connector = this.BX.Landing.Connector || {})));
//# sourceMappingURL=connector.disk.bundle.js.map

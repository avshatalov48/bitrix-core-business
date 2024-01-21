/* eslint-disable */
(function (exports) {
	'use strict';

	BX.namespace('BX.Catalog');
	BX.Catalog.productGridInit = function (grid) {
	  var dialogInstance;
	  grid.showChangePriceDialog = function () {
	    if (!dialogInstance) {
	      dialogInstance = new top.BX.CAdminDialog({
	        content_url: "/bitrix/tools/catalog/iblock_catalog_change_price.php?bxpublic=Y",
	        content_post: "sessid=".concat(BX.bitrix_sessid(), "&sTableID=").concat(this.containerId),
	        width: 800,
	        height: 415,
	        resizable: false,
	        buttons: [{
	          title: top.BX.message('JS_CORE_WINDOW_SAVE'),
	          id: 'savebtn',
	          name: 'savebtn',
	          className: top.BX.browser.IsIE() && top.BX.browser.IsDoctype() && !top.BX.browser.IsIE10() ? '' : 'adm-btn-save'
	        }, top.BX.CAdminDialog.btnCancel]
	      });
	    }
	    dialogInstance.Show();
	  };
	  grid.sendSmallPopupWithConfirm = function (action, data, confirmMessage, confirmButtonMessage, backButtonMessage) {
	    BX.UI.Dialogs.MessageBox.confirm(confirmMessage, function (messageBox) {
	      grid.sendRowAction(action, data);
	      messageBox.close();
	    }, confirmButtonMessage, function (messageBox) {
	      messageBox.close();
	    }, backButtonMessage);
	  };
	  grid.sendMediumPopupWithConfirm = function (action, data, titleMessage, confirmMessage, confirmButtonMessage, backButtonMessage) {
	    BX.UI.Dialogs.MessageBox.confirm(confirmMessage, titleMessage, function (messageBox) {
	      grid.sendRowAction(action, data);
	      messageBox.close();
	    }, confirmButtonMessage, function (messageBox) {
	      messageBox.close();
	    }, backButtonMessage);
	  };
	  return grid;
	};

}((this.window = this.window || {})));
//# sourceMappingURL=script.js.map

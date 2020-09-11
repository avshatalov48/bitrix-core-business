(function (exports,main_core,main_core_events,mail_avatar) {
	'use strict';

	var namespace = main_core.Reflection.namespace('BX.Mail.AddressBook');
	var gridId = 'MAIL_ADDRESSBOOK_LIST';
	BX.ready(function () {
	  var addContactButton = document.getElementById('mail-address-book-add-button');

	  addContactButton.onclick = function () {
	    top.BX.Runtime.loadExtension('mail.dialogeditcontact').then(function () {
	      return top.BX.Mail.AddressBook.DialogEditContact.openCreateDialog();
	    });
	  };

	  namespace.openEditDialog = function (attributes) {
	    top.BX.Runtime.loadExtension('mail.dialogeditcontact').then(function () {
	      return top.BX.Mail.AddressBook.DialogEditContact.openEditDialog(attributes);
	    });
	  };

	  namespace.openRemoveDialog = function (configContact) {
	    top.BX.Runtime.loadExtension('mail.dialogeditcontact').then(function () {
	      top.BX.Mail.AddressBook.DialogEditContact.openRemoveDialog(configContact).then(function () {
	        return reloadGrid(gridId);
	      });
	    });
	  };

	  function reloadGrid(gridID) {
	    var gridObject = BX.Main.gridManager.getById(gridID);

	    if (gridObject.hasOwnProperty('instance')) {
	      gridObject.instance.reloadTable('POST');
	    }
	  }

	  mail_avatar.Avatar.replaceTagsWithAvatars({
	    className: 'mail-ui-avatar'
	  });
	  main_core_events.EventEmitter.subscribe('SidePanel.Slider:onMessage', function (event) {
	    var _event$getCompatData = event.getCompatData(),
	        _event$getCompatData2 = babelHelpers.slicedToArray(_event$getCompatData, 1),
	        messageEvent = _event$getCompatData2[0];

	    if (messageEvent.getEventId() === 'dialogEditContact::reloadList') {
	      reloadGrid(gridId);
	    }
	  });
	  main_core_events.EventEmitter.subscribe('Grid::updated', function (event) {
	    var _event$getCompatData3 = event.getCompatData(),
	        _event$getCompatData4 = babelHelpers.slicedToArray(_event$getCompatData3, 1),
	        messageEvent = _event$getCompatData4[0];

	    if (messageEvent.containerId === 'MAIL_ADDRESSBOOK_LIST') {
	      mail_avatar.Avatar.replaceTagsWithAvatars({
	        className: 'mail-ui-avatar'
	      });
	    }
	  });
	});

}((this.window = this.window || {}),BX,BX.Event,BX.Mail));
//# sourceMappingURL=script.js.map

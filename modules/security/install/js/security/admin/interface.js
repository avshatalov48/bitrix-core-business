var JCSecurityAdminInterface = (function () {
	var AdminInterface = function() {};

	AdminInterface.prototype.initialize = function(settings) {
		if (!!settings.addableRows) {
			for (var i = 0, len = settings.addableRows.length; i < len; ++i) {
				var info = settings.addableRows[i];
				this.initializeAddableRow(info.tableId, info.buttonId);
			}
		}
	};

	AdminInterface.prototype.initializeAddableRow = function(tableId, buttonId) {
		var addFunction = addTableRow;
		if (BX.browser.IsIE() && !BX.browser.IsIE10())
			addFunction = addTableRowIE;

		BX.bind(BX(buttonId), 'click', BX.delegate(addFunction, {tableId: tableId}));
	};

	function addTableRow() {
		var lastRow = BX(this.tableId).querySelectorAll('tr.security-addable-row');
		lastRow = lastRow[lastRow.length - 1];
		if (!lastRow)
			return;

		var newRow = BX.create('tr', {
			props: {
				className: 'security-addable-row'
			}
		});
		//styles hack
		newRow.style.cssText  = 'padding-bottom:3px;';

		function increment(full, pre, value, post) {
			return pre + (parseInt(value, 10) + 1) + post;
		}

		newRow.innerHTML = lastRow.innerHTML.replace(/((?:\[|__|xx)n)(\d+)(\]|__|xx)/ig, increment);
		lastRow.parentNode.insertBefore(newRow, lastRow.nextSibling);
	}

	function addTableRowIE() {
		var tbl = BX(this.tableId);
		var cnt = tbl.rows.length;
		var oRow = tbl.insertRow(cnt-1);
		var oCell = oRow.insertCell(0);
		var sHTML = tbl.rows[cnt-2].cells[0].innerHTML;
		oCell.style.cssText  = 'padding-bottom:3px;';

		function increment(full, pre, value, post) {
			return pre + (parseInt(value, 10) + 1) + post;
		}

		oCell.innerHTML = sHTML.replace(/((?:\[|__|xx)n)(\d+)(\]|__|xx)/ig, increment);
	}

	return AdminInterface;
}());

(function initialize() {
	function initInterface() {
		var settings = BX('security-interface-settings');
		if (!settings)
			return;

		settings = JSON.parse(settings.innerHTML);
		var securityInterface = new JCSecurityAdminInterface();
		securityInterface.initialize(settings);
	}

	BX.ready(initInterface);
})();
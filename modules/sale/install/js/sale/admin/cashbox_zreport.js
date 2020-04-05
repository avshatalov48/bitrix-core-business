(function(window) {
	if (!BX.Sale)
		BX.Sale = {};

	if (BX.Sale.CashboxReport)
		return;
	
	BX.Sale.CashboxReport =
	{
		ajaxUrl: "/bitrix/admin/sale_order_ajax.php",
		onClearFilter: function()
		{
			var cashboxFilter = BX('filter_cashbox_id');
			if (cashboxFilter !== undefined && cashboxFilter !== null)
			{
				var cashboxFilterOptions = cashboxFilter.children;
				if (cashboxFilterOptions[0] !== undefined && cashboxFilterOptions[0] !== null)
					cashboxFilterOptions[0].selected = true;
			}

			this.changeCashboxBlocks();
		},
		changeCashboxBlocks: function()
		{
			var cashboxFilter = BX('filter_cashbox_id');
			if (cashboxFilter !== undefined && cashboxFilter !== null)
			{
				var data = {
					sessid : BX.bitrix_sessid(),
					cashboxId: cashboxFilter.value,
					action: 'loadCashboxCheckInfo'
				};
				BX.proxy(BX.ajax(
					{
						method: 'post',
						dataType: 'json',
						url: this.ajaxUrl,
						data: data,
						onsuccess: BX.proxy(function (result)
						{
							if (result.ERROR && result.ERROR.length > 0)
							{
								alert(result.ERROR);
							}
							else
							{
								if (result.CASH)
								{
									cashNow = BX('adm-zreport-cash-now');
									if (cashNow)
									{
										cashNow.innerHTML = result.CASH.FORMATED_SUM;
									}
									cashReturn = BX('adm-zreport-cash-return');
									if (cashReturn)
									{
										cashReturn.innerHTML = result.CASH.FORMATED_RETURN_SUM;
									}
								}

								if (result.CASHLESS)
								{
									cashlessNow = BX('adm-zreport-cashless-now');
									if (cashlessNow)
									{
										cashlessNow.innerHTML = result.CASHLESS.FORMATED_SUM;
									}
									cashlessReturn = BX('adm-zreport-cashless-return');
									if (cashlessReturn)
									{
										cashlessReturn.innerHTML = result.CASHLESS.FORMATED_RETURN_SUM;
									}
								}

								if (result.CUMULATIVE)
								{
									cumulative = BX('adm-zreport-cumulative');
									if (cumulative)
									{
										cumulative.innerHTML = result.CUMULATIVE.FORMATED_SUM;
									}
								}
							}
						},this),
						onfailure: function() {BX.debug('Set filter error. Can\'t reload cashbox info')}
					}
				),this);
			}
		},
		createZReport: function()
		{
			var cashboxFilter = BX('filter_cashbox_id');
			if (cashboxFilter == undefined || cashboxFilter == null)
			{
				return;
			}
			var cashboxFilterOptions = cashboxFilter.children;

			var content = "<div class='adm-info-message'>"+BX.message('CASHBOX_CREATE_ZREPORT_WINDOW_INFO')+"</div>";
			content += "<table><tbody><tr><td><label>"+BX.message('SALE_F_CASHBOX')+":</label></td><td>";
			content += "<select id='cashboxListAddReport' class='sale-discount-bus-select'>";
			for (option in cashboxFilterOptions)
			{
				if (cashboxFilterOptions[option].value != undefined)
				{
					content +="<option value='"+cashboxFilterOptions[option].value+"'>"+cashboxFilterOptions[option].innerHTML+"</option>";
				}
			}
			content +="</select></td></tr></tbody></table>";
			var dlg = new BX.CAdminDialog({
				'title': BX.message('CASHBOX_CREATE_ZREPORT_WINDOW_TITLE'),
				'content': content,
				'resizable': false,
				'draggable': true,
				'height': '170',
				'width': '387',
				'buttons': [
					{
						title: BX.message('JS_CORE_WINDOW_SAVE'),
						id: 'saveCheckBtn',
						name: 'savebtn',
						className: top.BX.browser.IsIE() && top.BX.browser.IsDoctype() && !top.BX.browser.IsIE10() ? '' : 'adm-btn-save'
					},
					{
						title: top.BX.message('JS_CORE_WINDOW_CANCEL'),
						id: 'cancelCheckBtn',
						name: 'cancel'
					}
				]
			});

			BX.bind(BX("cancelCheckBtn"), 'click', BX.delegate(
				function()
				{
					dlg.Close();
					dlg.DIV.parentNode.removeChild(dlg.DIV);
				}
			),this );

			BX.bind(BX("saveCheckBtn"), 'click', BX.delegate(
				function()
				{
					var sendData = {
						sessid : BX.bitrix_sessid(),
						cashboxId: BX('cashboxListAddReport').value,
						action: 'addZReport'
					};
					BX.ajax(
						{
							method: 'post',
							dataType: 'json',
							url: BX.Sale.CashboxReport.ajaxUrl,
							data: sendData,
							onsuccess: function (result)
							{
								if (result.ERROR && result.ERROR.length > 0)
								{
									alert(result.ERROR);
								}
								else
								{
									dlg.Close();
									location.reload();
								}
							},
							onfailure: function() {BX.debug('Select params error');}
						}
					);

				}
			),this);

			dlg.Show();
		}
	};

	BX.addCustomEvent(window, 'onBeforeAdminFilterSet', function(){
		BX.Sale.CashboxReport.changeCashboxBlocks();
	});

	BX.addCustomEvent(window, 'onBeforeAdminFilterClear', function(){
		BX.Sale.CashboxReport.onClearFilter();
	});
})(window);


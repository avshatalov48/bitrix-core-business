function bx_app_pass_toggle(elem)
{
	if(BX.hasClass(elem, 'open'))
	{
		BX.removeClass(elem, 'open');
		BX.addClass(elem, 'close');
	}
	else
	{
		BX.removeClass(elem, 'close');
		BX.addClass(elem, 'open');
	}
	return false;
}

function bx_app_pass_show_create_window(form)
{
	form = BX(form);

	var data = {
		'SYSCOMMENT': (form.SYSCOMMENT? form.SYSCOMMENT.value : ''),
		'COMMENT': form.COMMENT.value,
		'APPLICATION_ID': form.APPLICATION_ID.value,
		'action': 'add',
		'sessid': BX.message('bitrix_sessid')
	};

	var config = {
		'method': 'POST',
		'dataType': 'json',
		'url': '/bitrix/components/bitrix/main.app.passwords/ajax.php',
		'data': data,
		'onsuccess':
			function(result)
			{
				BX.removeClass(BX('bx_app_pass_close_button'), 'wait');
				if(result.success === true)
				{
					var elem = BX('bx_app_pass_lottery');
					BX.removeClass(elem, 'bx-otp-popup-lottery-black');
					BX.addClass(elem, 'bx-otp-popup-lottery-white');

					BX('bx_app_pass_password').innerHTML = result.password;

					var cells = [
						{
							className: 'bx-otp-access-table-param',
							content: BX.util.htmlspecialchars(data.SYSCOMMENT) + '\n' +
								'<small>' + BX.util.htmlspecialchars(data.COMMENT) + '</small>'
						},
						{
							className: 'bx-otp-access-table-value',
							content: result.date_create
						},
						{
							className: 'bx-otp-access-table-value',
							content: ''
						},
						{
							className: 'bx-otp-access-table-value',
							content: ''
						},
						{
							className: 'bx-otp-access-table-action',
							content: '<a class="bx-otp-btn big lightgray mb0" href="javascript:void(0);" onclick="bx_app_pass_show_delete_window(' + result.id + ')">' + bx_app_pass_mess.deleteButton + '</a>'
						}
					];
					var table = BX('bx_app_pass_table_' + data.APPLICATION_ID);
					var row = table.insertRow(table.rows.length-1);
					row.id = 'bx_app_pass_row_' + result.id;
					for(var i in cells)
					{
						var cell = row.insertCell(-1);
						cell.className = cells[i].className;
						cell.innerHTML = cells[i].content;
					}
				}
				else
				{
					alert(result.message);
				}
			}
	};

	var popup = BX.PopupWindowManager.create("bx_app_pass_create", null, {
		autoHide: false,
		offsetLeft: 0,
		offsetTop: 0,
		overlay : true,
		closeByEsc: true,
		closeIcon: { right : "12px", top : "10px"},
		content: BX('bx_app_pass_new_password')
	});

	BX('bx_app_pass_password').innerHTML = '';

	var elem = BX('bx_app_pass_lottery');
	BX.removeClass(elem, 'bx-otp-popup-lottery-white');
	BX.addClass(elem, 'bx-otp-popup-lottery-black');

	BX.addClass(BX('bx_app_pass_close_button'), 'wait');

	popup.show();

	BX.ajax(config);
}

function bx_app_pass_show_delete_window(id)
{
	var data = {
		'ID': id,
		'action': 'delete',
		'sessid': BX.message('bitrix_sessid')
	};

	var config = {
		'method': 'POST',
		'dataType': 'json',
		'url': '/bitrix/components/bitrix/main.app.passwords/ajax.php',
		'data': data,
		'onsuccess':
			function(result)
			{
				BX.removeClass(BX('bx_app_pass_del_button'), 'wait');
				if(result.success === true)
				{
					BX('bx_app_pass_row_'+id).style.display = 'none';
					popup.close();
				}
				else
				{
					alert(result.message);
				}
			}
	};

	BX.removeClass(BX('bx_app_pass_del_button'), 'wait');

	var popup = BX.PopupWindowManager.create("bx_app_pass_delete", null, {
		autoHide: false,
		offsetLeft: 0,
		offsetTop: 0,
		overlay : true,
		closeByEsc: true,
		closeIcon: { right : "12px", top : "10px"},
		content: BX('bx_app_pass_delete_password')
	});

	BX('bx_app_pass_del_button').onclick = function()
	{
		BX.addClass(BX('bx_app_pass_del_button'), 'wait');
		BX.ajax(config)
	};

	popup.show();
}


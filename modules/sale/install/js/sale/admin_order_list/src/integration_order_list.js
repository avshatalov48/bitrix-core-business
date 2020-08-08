import {OrderList} from "./order_list"

export class IntegrationOrderList extends OrderList
{
	dialog = null;

	initialize(settings)
	{
		this._settings = settings ? settings : {};
		this._restEntityInfo = BX.prop.getObject(this._settings, "restEntityInfo", {});
		this._form = BX.prop.getElementNode(this._settings, "form");

		this.entityId = BX.prop.getInteger(this._restEntityInfo, 'entityId', 0);
		this.entityTypeId = BX.prop.getInteger(this._restEntityInfo, 'entityTypeId', 0);
	}

	confirmToOpenNewOrder(title, message, url)
	{
		this.dialog = new BX.PopupWindow(
			'adm-sale-order-alert-dialog',
			null,
			{
				autoHide: false,
				draggable: true,
				offsetLeft: 0,
				offsetTop: 0,
				bindOptions: { forceBindPosition: false },
				closeByEsc: true,
				closeIcon: true,
				titleBar: title,
				contentColor: 'white',
				content: BX.create(
					'span',
					{
						html: BX.util.htmlspecialchars(message).replace(/\n/g, "<br>\n"),
						style: {
							backgroundColor: "white"
						}
					}
				)
			}
		);

		let buttons = [
			new BX.PopupWindowButton(
				{
					text:  BX.message('SALE_O_CONTEXT_B_IS_SYNC_B24_REGISTRY_CREATE'),
					className: "popup-window-button-accept",
					events:
						{
							click : function(){
								window.open(url);
								BX.delegate(this.onPopupClose, this);
								BX.Sale.AdminIntegrationOrderList.closeApplication();
							}
						}
				}
			),
			new BX.PopupWindowButton(
				{
					text:  BX.message('SALE_O_CONTEXT_B_IS_SYNC_B24_REGISTRY_CLOSE'),
					className: "popup-window-button-link-cancel",
					events:
						{
							click : function(){
								BX.delegate(this.onPopupClose, this);
								BX.Sale.AdminIntegrationOrderList.closeApplication();
							}
						}
				}
			)
		];

		this.dialog.setButtons(buttons);
		this.dialog.show();
	}

	popup(title, message)
	{
		this.dialog = new BX.PopupWindow(
			'adm-sale-order-alert-dialog',
			null,
			{
				autoHide: false,
				draggable: true,
				offsetLeft: 0,
				offsetTop: 0,
				bindOptions: { forceBindPosition: false },
				closeByEsc: true,
				closeIcon: true,
				titleBar: title,
				contentColor: 'white',
				content: BX.create(
					'span',
					{
						html: BX.util.htmlspecialchars(message).replace(/\n/g, "<br>\n"),
						style: {
							backgroundColor: "white"
						}
					}
				)
			}
		);

		let buttons = [
			new BX.PopupWindowButton(
				{
					text:  BX.message('SALE_O_CONTEXT_B_IS_SYNC_B24_REGISTRY_CLOSE'),
					className: "popup-window-button-link-cancel",
					events:
						{
							click : BX.delegate(this.onPopupClose, this)
						}
				}
			)];

		this.dialog.setButtons(buttons);
		this.dialog.show();
	}

	onPopupClose()
	{
		this.dialog.close();
		this.dialog.destroy()
	}

	getSendedOrders()
	{
		let orders = [];
		let checksList = [];

		checksList = this.getCheckedCheckBoxList(this._form);

		for (let i = 0; i < checksList.length; i++)
		{
			let row = BX.findParent(checksList[i], {tag: 'TR'});

			if(!!row)
			{
				let spanList = row.getElementsByTagName('span');

				if(!!spanList)
				{
					for (let n = 0; n < spanList.length; n++)
					{
						if(spanList[n].id == 'IS_SYNC_B24_'+checksList[i].value)
						{
							if(spanList[n].innerText == BX.message('SALE_O_CONTEXT_B_IS_SYNC_B24_REGISTRY_SEND_YES'))
							{
								orders.push(checksList[i].value);
							}
						}
					}
				}
			}
		}
		return orders;
	}

	getCheckedCheckBoxList(form)
	{
		let lnt = form.elements.length;
		let list = [];

		for (let i = 0; i < lnt; i++)
		{
			if(form.elements[i].tagName.toUpperCase() == "INPUT"
				&& form.elements[i].type.toUpperCase() == "CHECKBOX"
				&& form.elements[i].name.toUpperCase() == "ID[]"
				&& form.elements[i].checked == true)
			{
				list.push(form.elements[i]);
			}
		}

		return list;
	}

	sendOrdersToRestApplication()
	{
		let ordersListForm = this._form;
		let boxList = this.getCheckedCheckBoxList(this._form);

		if(BX('tbl_sale_order_check_all') && ordersListForm)
		{
			if(boxList.length == 0)
			{
				this.popup(
					BX.message('SALE_O_CONTEXT_B_IS_SYNC_B24_TITLE'),
					BX.message('SALE_O_CONTEXT_B_IS_SYNC_B24_SELECTION_NEEDED')
				);
			}
			else
			{
				let ordersSend = this.getSendedOrders();

				if(tbl_sale_order.num_checked>3)
				{
					this.popup(
						BX.message('SALE_O_CONTEXT_B_IS_SYNC_B24_TITLE'),
						BX.message('SALE_O_CONTEXT_B_IS_SYNC_B24_SELECTION_MORE_THREE')
					);
				}
				else if(ordersSend.length>0)
				{
					this.popup(
						BX.message('SALE_O_CONTEXT_B_IS_SYNC_B24_TITLE'),
						BX.message('SALE_O_CONTEXT_B_IS_SYNC_B24_REGISTRY_SENDED')+': '+ordersSend.toString()
					);
				}
				else
				{
					this.redirect();
				}
			}
		}
	}

	getValuesCheckedCheckBox(form)
	{
		let boxList = this.getCheckedCheckBoxList(form);
		let list = [];

		for (let i = 0; i < boxList.length; i++)
		{
			list.push(boxList[i].value);
		}
		return list;
	}

	redirect()
	{
		let url = 'sale_app_rest_sender.php';
		url = BX.Uri.addParam(url, {
			orderIds: this.getValuesCheckedCheckBox(this._form),
			entityId: this.entityId,
			entityTypeId: this.entityTypeId,
			IFRAME: 'Y'
		});
		document.location.href = url;
	}

	closeApplication()
	{
		BX24.closeApplication();
	}
}
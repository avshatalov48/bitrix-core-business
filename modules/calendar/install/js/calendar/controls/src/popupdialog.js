export class PopupDialog {
	constructor(params = {})
	{
		this.id = params.id || 'popup-dialog-' + Math.random();
		this.zIndex = params.zIndex || 3200;
		this.DOM = {};
		this.title = '';
	}

	create()
	{
		this.dialog = new BX.PopupWindow(this.id, null, {
			overlay: {opacity: 10},
			autoHide: true,
			closeByEsc : true,
			zIndex: this.zIndex,
			offsetLeft: 0,
			offsetTop: 0,
			draggable: true,
			bindOnResize: false,
			titleBar: this.getTitle(),
			closeIcon: { right : "12px", top : "10px"},
			className: 'bxc-popup-window',
			buttons: this.getButtons(),
			content: this.getContent(),
			events: {}
		});
	}

	getTitle()
	{
		return this.title;
	}

	getContent()
	{
		this.DOM.content = BX.create('DIV');
		return this.DOM.content;
	}

	getButtons()
	{
		this.buttons = [];
		return this.buttons;
	}

	show(params)
	{
		if (!this.dialog)
		{
			this.create();
		}
		this.dialog.show();
	}

	close()
	{
		if (this.dialog)
		{
			this.dialog.close();
		}
	}
}
import {Type} from 'main.core';

export class Waiter
{
	static instance = null;

	static getInstance()
	{
		if (Type.isNull(Waiter.instance))
		{
			Waiter.instance = new Waiter();
		}

		return Waiter.instance;
	}

	constructor()
	{
		this.waitTimeout = null;
		this.waitPopup = null;

	}

	show(timeout)
	{
		if (timeout !== 0)
		{
			return (this.waitTimeout = setTimeout(() => {
				this.show(0);
			}, 50));
		}

		if (!this.waitPopup)
		{
			this.waitPopup = new BX.PopupWindow('sonet_common_wait_popup', window, {
				autoHide: true,
				lightShadow: true,
				zIndex: 2,
				content: BX.create('DIV', {
					props: {
						className: 'sonet-wait-cont'
					},
					children: [
						BX.create('DIV', {
							props: {
								className: 'sonet-wait-icon'
							}
						}),
						BX.create('DIV', {
							props: {
								className: 'sonet-wait-text'
							},
							html: BX.message('SONET_EXT_COMMON_WAIT')
						})
					]
				})
			});
		}
		else
		{
			this.waitPopup.setBindElement(window);
		}

		this.waitPopup.show();
	}

	hide()
	{
		if (this.waitTimeout)
		{
			clearTimeout(this.waitTimeout);
			this.waitTimeout = null;
		}

		if (this.waitPopup)
		{
			this.waitPopup.close();
		}
	}
}

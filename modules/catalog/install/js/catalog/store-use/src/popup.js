import {Popup as MainPopup} from 'main.popup';

class Popup
{
	show(target, message, timer)
	{
		if (this.popup)
		{
			this.popup.destroy();
			this.popup = null;
		}

		if (!target && !message)
		{
			return;
		}

		this.popup = new MainPopup(null, target, {
			events: {
				onPopupClose: () => {
					this.popup.destroy();
					this.popup = null;
				}
			},
			darkMode: true,
			content: message,
			offsetLeft: target.offsetWidth,
		});

		if (timer)
		{
			setTimeout(() => {
				this.popup.destroy();
				this.popup = null;
			}, timer);
		}

		this.popup.show();
	}

	hide()
	{
		if (this.popup)
		{
			this.popup.destroy();
		}
	}
}

export
{
	Popup
}
import { Helper } from './helper';

export class PageConfiguration
{

	constructor()
	{
		this.helper = Helper.getCreated();
		this.targetUrlBlock = document.querySelector('.seo-ads-target-url');

		return this;
	}

	apply(applyBtn)
	{
		if(!this.validateUrl(this.targetUrlBlock.value))
		{
			this.removeWait(applyBtn);
			return;
		}

		BX.SidePanel.Instance.close();

		BX.SidePanel.Instance.postMessage(
			window,
			'seo-ads-target-post-selected',
			{
				targetUrl: this.targetUrlBlock.value
			}
		);
		this.removeWait(applyBtn);
	}

	removeWait(applyBtn)
	{
		setTimeout(() => {
			applyBtn.classList.remove('ui-btn-wait')
		}, 200);
	}

	cancel()
	{
		BX.SidePanel.Instance.close();
	}

	validateUrl(value)
	{
		return /^(?:(?:(?:https?|ftp):)?\/\/)(?:\S+(?::\S*)?@)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)(?:\.(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)*(?:\.(?:[a-z\u00a1-\uffff]{2,})))(?::\d{2,5})?(?:[/?#]\S*)?$/i.test(value);
	}
}
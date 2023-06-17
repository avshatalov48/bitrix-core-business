import SharingButton from './controls/sharingbutton';

export default class Interface
{
	constructor(options)
	{
		this.buttonWrap = options.buttonWrap;
		this.userId = options.userId;
		this.payAttentionToNewFeature = options.payAttentionToNewFeature ?? false;
	}

	showSharingButton()
	{
		this.sharingButton = new SharingButton({
			wrap: this.buttonWrap,
			userId: this.userId,
			payAttentionToNewFeature: this.payAttentionToNewFeature,
		});
		this.sharingButton.show();
	}
}
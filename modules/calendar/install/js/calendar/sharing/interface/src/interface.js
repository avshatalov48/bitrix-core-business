import SharingButton from './controls/sharingbutton';

export default class Interface
{
	constructor(options)
	{
		this.buttonWrap = options.buttonWrap;
		this.userId = options.userId;
	}

	showSharingButton()
	{
		this.sharingButton = new SharingButton({
			wrap: this.buttonWrap,
			userId: this.userId,
		});
		this.sharingButton.show();
	}
}
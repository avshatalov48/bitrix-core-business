import SharingButton from './controls/sharingbutton';

export default class Interface
{
	constructor(options)
	{
		this.buttonWrap = options.buttonWrap;
		this.userInfo = options.userInfo || null;
		this.payAttentionToNewFeature = options.payAttentionToNewFeature ?? false;
		this.sharingFeatureLimit = options.sharingFeatureLimit ?? false;
		this.sharingSettingsCollapsed = options.sharingSettingsCollapsed ?? false;
		this.sortJointLinksByFrequentUse = options.sortJointLinksByFrequentUse ?? false;
	}

	showSharingButton()
	{
		this.sharingButton = new SharingButton({
			wrap: this.buttonWrap,
			userInfo: this.userInfo,
			payAttentionToNewFeature: this.payAttentionToNewFeature,
			sharingFeatureLimit: this.sharingFeatureLimit,
			sharingSettingsCollapsed: this.sharingSettingsCollapsed,
			sortJointLinksByFrequentUse: this.sortJointLinksByFrequentUse,
		});
		this.sharingButton.show();
	}
}

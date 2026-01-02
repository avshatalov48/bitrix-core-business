import { DiskIcon } from '../js/disk-icon';
import { DiskIconType as iconType } from '../common/const';

export const DiskIconType = iconType;

// @vue/component
export const BDiskIcon = {
	props: {
		type: {
			type: String,
			required: false,
			default: 'file',
		},
		size: {
			type: Number,
			required: false,
		},
		previewUrl: {
			type: String,
			required: false,
			default: null,
		},
		alt: {
			type: String,
			required: false,
			default: '',
		},
		title: {
			type: String,
			required: false,
			default: '',
		},
		responsive: {
			type: Boolean,
			default: false,
		},
	},

	data()
	{
		return {
			diskIcon: null,
		};
	},

	watch:
	{
		type() {
			this.updateDiskIcon();
		},
		size() {
			this.updateDiskIcon();
		},
		previewUrl() {
			this.updateDiskIcon();
		},
		alt() {
			this.updateDiskIcon();
		},
		title() {
			this.updateDiskIcon();
		},
		responsive() {
			this.updateDiskIcon();
		},
	},

	mounted() {
		this.initDiskIcon();
	},

	beforeUnmount() {
		if (this.diskIcon)
		{
			this.diskIcon.destroy();
		}
	},

	methods: {
		initDiskIcon() {
			const options = {
				type: this.type,
				size: this.size,
				previewUrl: this.previewUrl || null,
				alt: this.alt,
				title: this.title,
				responsive: this.responsive,
			};

			this.diskIcon = new DiskIcon(options);
			this.diskIcon.renderOnNode(this.$el);
		},

		updateDiskIcon() {
			if (!this.diskIcon)
			{
				return;
			}

			this.diskIcon.setType(this.type);
			if (this.responsive === false)
			{
				this.diskIcon.setSize(this.size);
			}
			this.diskIcon.setPreviewUrl(this.previewUrl || null);
			this.diskIcon.setAlt(this.alt);
			this.diskIcon.setTitle(this.title);
			this.diskIcon.setResponsive(this.responsive);
		},
	},

	template: '<div></div>',
};

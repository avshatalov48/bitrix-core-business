import { Tag } from 'main.core';
import './css/style.css';

export class Avatar
{
	static stringToHashCode(string)
	{
		let hashCode = 0;

		for (let i = 0; i < string.length; i++)
		{
			hashCode = string.charCodeAt(i) + ((hashCode << 5) - hashCode);
		}

		return hashCode;
	}

	static alignChannelRangeColor(chanelCode)
	{
		if(chanelCode > 255)
		{
			return 255;
		}
		else if(chanelCode < 0)
		{
			return 0;
		}
		else
		{
			return Math.ceil(chanelCode);
		}
	}

	static hashToColor(hash)
	{
		const maxIntensityAllChannels = 255*3;
		const minIntensityAllChannels = 0;
		const differenceCoefficientForGrayDetection = 0.20;

		let r = (hash & 0xFF0000) >> 16;
		let g = (hash & 0x00FF00) >> 8;
		let b = (hash & 0x0000FF);

		const contrastRatioForPastelColors = 1.5;
		const contrastRatioForDarkColors = 2.5;
		const channelReductionCoefficientIfGray = 2;

		if(maxIntensityAllChannels - (r+g+b) < 100)
		{	//Pastel colors or white
			r/=contrastRatioForPastelColors;
			g/=contrastRatioForPastelColors;
			b/=contrastRatioForPastelColors;
		}
		else if((r+g+b) < (200 - minIntensityAllChannels))
		{
			//Very dark colors
			r*=contrastRatioForDarkColors;
			g*=contrastRatioForDarkColors;
			b*=contrastRatioForDarkColors;
		}

		let channels = [r,g,b];
		channels.sort(function(a,b){
			return a - b;
		})

		if(((channels[channels.length - 1]-channels[0])/channels[0]) < differenceCoefficientForGrayDetection)
		{
			//Shade of gray
			g/=channelReductionCoefficientIfGray;
		}

		r=this.alignChannelRangeColor(r);
		g=this.alignChannelRangeColor(g);
		b=this.alignChannelRangeColor(b);

		const color = "#" + ("0" + r.toString(16)).substr(-2) + ("0" + g.toString(16)).substr(-2) + ("0" + b.toString(16)).substr(-2);
		return color.toUpperCase();
	}

	static stringToColor(name)
	{
		return this.hashToColor(this.stringToHashCode(name));
	}

	static getInitials(string, email)
	{
		string=string.replace(/[0-9]|[-\u0026\u002f\u005c\u0023\u002c\u002b\u0028\u0029\u0024\u007e\u0025\u002e\u0027\u0022\u003a\u002a\u003f\u003c\u003e\u007b\u007d\u00ab\u00bb]/g,"");
		string=string.replace(/^\s+|\s+$/g, '');

		const names = string.split(' ');

		let initials = names[0].substring(0, 1).toUpperCase();

		if (names.length > 1)
		{
			initials += names[names.length - 1].substring(0, 1).toUpperCase();
		}

		if(initials === '')
		{
			initials = email[0].toUpperCase();
		}

		return initials;
	}

	static getAvatarData(config = {
		fullName: 'User Quest',
		email: 'info@example.com',
	})
	{
		return {
			'abbreviation': this.getInitials(config['fullName'], config['email']),
			'color': this.stringToColor(config['email']),
		};
	}

	static build(config = {
		size: 'small',
		fullName: 'User Quest',
		email: 'info@example.com',
	})
	{
		const whiteList = new Set(['small', 'big']);

		if (config['size'] === undefined || !whiteList.has(config['size']))
		{
			config['size'] = 'small';
		}

		const data = this.getAvatarData(config);

		let avatar = Tag.render`<span class="mail-ui-avatar mail-ui-avatar-${config['size']}">${data['abbreviation']}</span>`;
		avatar.style.backgroundColor = data['color'];

		return avatar;
	}

	static replaceElementWithAvatar(object, avatar)
	{
		const parent = object.parentNode;
		parent.insertBefore(avatar, object);
		parent.removeChild(object);
	}

	static replaceTagsWithAvatars(config = { className: 'mail-ui-avatar' })
	{
		const elements = document.getElementsByClassName(config['className']);
		for (let element of elements)
		{
			this.replaceElementWithAvatar(element, this.build({
				fullName: element.getAttribute('user-name'),
				email: element.getAttribute('email'),
			}));
		}
	}
}
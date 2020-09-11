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

	static numberToRGB(index)
	{
		let color = (index & 0x00FFFFFF).toString(16);
		color = color.toUpperCase();

		return '00000'.substring(0, 6 - color.length) + color;
	}

	static stringToColor(name)
	{
		return this.numberToRGB(this.stringToHashCode(name));
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

		config['fullName'] = config['fullName'].replace(/[\u0026\u005c\u002f\u005c\u005c\u0023\u002c\u002b\u0028\u0029\u0024\u007e\u0025\u002e\u0027\u0022\u003a\u002a\u003f\u003c\u003e\u007b\u007d\u00ab\u00bb]/g, '').toUpperCase();
		let brokenName = config['fullName'].split(' ');
		let abbreviation = brokenName[0][0];

		if (brokenName.length > 1)
		{
			abbreviation += brokenName[1][0];
		}

		let avatar = Tag.render`<span class="mail-ui-avatar mail-ui-avatar-${config['size']}">${abbreviation}</span>`;
		avatar.style.backgroundColor = this.stringToColor(config['email']);

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
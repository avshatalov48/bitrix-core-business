import {Type} from 'main.core';

const getFormDataSize = (formData: FormData): Array =>
{
	const entries = formData.entries();
	let entry, filesCount = 0, formSize = 0;
	while((entry = entries.next()) && entry.done === false)
	{
		const [name, value] = entry.value;

		if (value instanceof Blob)
		{
			filesCount++;
			formSize += value.size;
		}
		else
		{
			formSize += value.toString().length;
		}
		formSize += name.toString().length;
	}
	return [formSize, filesCount];
}

const convertFormDataToObject = (formData: FormData): Object => {
	const entries = formData.entries();
	let entry;
	const data = {};
	while((entry = entries.next()) && entry.done === false)
	{
		const [name, value] = entry.value;
		if (name.indexOf('[') <= 0)
		{
			data[name] = value;
		}
		else
		{
			const names = [name.substring(0, name.indexOf('['))];
			name.replace(/\[(.*?)\]/gi, (n, nn) => {
				names.push(nn.length > 0 ? nn : '');
			});
			let n;
			let pointer = data;
			while (n = names.shift())
			{
				if (n === '')
				{
					pointer.push(value);
					break;
				}
				else if (names.length <= 0)
				{
					pointer[n] = value;
					break;
				}
				else if (names[0] === '')
				{
					pointer[n] = (pointer[n] || []);
					pointer = pointer[n];
				}
				else
				{
					pointer[n] = (pointer[n] || {});
					pointer = pointer[n];
				}
			}
		}
	}
	return data;
};

const copyFormToForm = (fromData1, formData2): void => {
	const entries = fromData1.entries();
	let entry;
	while((entry = entries.next()) && entry.done === false)
	{
		const [name, value] = entry.value;

		if (value instanceof Blob)
		{
			formData2.append(name, value, value.name);
		}
		else
		{
			formData2.append(name, value);
		}
	}
}
const appendToForm = (formData, ob, prefix): void => {
	for (let ii in ob)
	{
		if (ob.hasOwnProperty(ii))
		{
			const name = (prefix ? (prefix + '[#name#]') : '#name#').replace('#name#', ii);
			if (Type.isPlainObject(ob[ii]))
			{
				appendToForm(formData, ob[ii], name);
			}
			else
			{
				if (ob[ii] instanceof Blob)
				{
					formData.append(name, ob[ii], (ob[ii]['name'] || ii));
				}
				else
				{
					formData.append(name, ob[ii]);
				}
			}
		}
	}
};
export {appendToForm, getFormDataSize, copyFormToForm, convertFormDataToObject}
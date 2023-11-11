import { Type } from 'main.core';

const assignFileToInput = (input: HTMLInputElement, file: File | File[]) => {
	try
	{
		const dataTransfer = new DataTransfer();
		const files = Type.isArray(file) ? file : [file];

		files.forEach((item) => {
			dataTransfer.items.add(item);
		});

		// eslint-disable-next-line no-param-reassign
		input.files = dataTransfer.files;
	}
	catch
	{
		return false;
	}

	return true;
};

export default assignFileToInput;

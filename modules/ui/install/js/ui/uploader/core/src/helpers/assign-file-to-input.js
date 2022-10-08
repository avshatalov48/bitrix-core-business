import { Type } from 'main.core';

const assignFileToInput = (input: HTMLInputElement, file: File | File[]) => {
	try
	{
		const dataTransfer = new DataTransfer();
		const files = Type.isArray(file) ? file : [file];

		files.forEach(file => {
			dataTransfer.items.add(file);
		});

		input.files = dataTransfer.files;
	}
	catch (error)
	{
		return false;
	}

	return true;
};

export default assignFileToInput;
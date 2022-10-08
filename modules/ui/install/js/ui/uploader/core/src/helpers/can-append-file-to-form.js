let result = null;

const canAppendFileToForm = () => {
	if (result === null)
	{
		try
		{
			const dataTransfer = new DataTransfer();
			const file = new File(['hello'], 'my.txt');
			dataTransfer.items.add(file);

			const input = document.createElement('input');
			input.setAttribute('type', 'file');
			input.files = dataTransfer.files;

			result = input.files.length === 1;
		}
		catch (err)
		{
			result = false;
		}
	}

	return result;
};

export default canAppendFileToForm;
const createBlobFromDataUri = (dataURI: string): Blob => {
	const byteString = atob(dataURI.split(',')[1]);
	const mimeString = dataURI.split(',')[0].split(':')[1].split(';')[0];

	const buffer = new ArrayBuffer(byteString.length);
	const view = new Uint8Array(buffer);

	for (let i = 0; i < byteString.length; i++)
	{
		view[i] = byteString.codePointAt(i);
	}

	return new Blob([buffer], { type: mimeString });
};

export default createBlobFromDataUri;

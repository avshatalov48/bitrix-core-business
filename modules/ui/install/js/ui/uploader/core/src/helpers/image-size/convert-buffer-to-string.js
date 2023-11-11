const convertBufferToString = (buffer: ArrayBuffer): string => {
	return String.fromCharCode.apply(null, new Uint8Array(buffer));
};

export default convertBufferToString;

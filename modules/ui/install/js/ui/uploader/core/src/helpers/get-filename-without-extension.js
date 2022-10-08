const getFilenameWithoutExtension = (name) => {
	return name.substr(0, name.lastIndexOf('.')) || name;
};

export default getFilenameWithoutExtension;
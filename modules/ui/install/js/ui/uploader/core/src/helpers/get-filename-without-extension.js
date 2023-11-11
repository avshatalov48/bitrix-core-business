const getFilenameWithoutExtension = (name) => {
	return name.slice(0, Math.max(0, name.lastIndexOf('.'))) || name;
};

export default getFilenameWithoutExtension;

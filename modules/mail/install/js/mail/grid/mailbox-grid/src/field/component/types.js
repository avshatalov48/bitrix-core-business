export type Avatar = {
	src: string,
	width: number,
	height: number,
	size: number
}

export type User = {
	id: number,
	name: string,
	avatar: Avatar,
	pathToProfile: string,
	position: string,
}

export const AddFormStore = {
	state(): Object
	{
		return {
			avatarColors: [],
			avatarColor: '29AD49',
		};
	},
	actions:
		{
			setAvatarColors: (store, colors) => {
				store.commit('setAvatarColors', colors);
			},
			setAvatarColor: (store, color) => {
				store.commit('setAvatarColor', color);
			},
		},
	mutations:
		{
			setAvatarColors: (state, colors) => {
				state.avatarColors = colors;
			},
			setAvatarColor: (state, color) => {
				state.avatarColor = color;
			},
		},
	getters:
		{
			avatarColors: (state) => {
				return state.avatarColors;
			},
			previousAvatarColor: (state) => {
				return state.avatarColor;
			},
		},
};

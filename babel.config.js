module.exports = ( api ) => {
	const isTestEnv = api.env() === 'test';

	return {
		presets: [
			[
				require.resolve( '@babel/preset-env' ),
				isTestEnv
					? {
							targets: {
								node: 'current',
							},
					  }
					: {
							bugfixes: true,
							modules: false,
					  },
			],
		],
		plugins: [
			require.resolve( '@wordpress/warning/babel-plugin' ),
			[
				require.resolve( '@babel/plugin-transform-react-jsx' ),
				{
					pragma: 'wp.element.createElement',
					pragmaFrag: 'wp.element.Fragment',
					runtime: 'classic',
				},
			],
			! isTestEnv && [
				require.resolve( '@babel/plugin-transform-runtime' ),
				{
					helpers: true,
					useESModules: false,
				},
			],
		].filter( Boolean ),
	};
};

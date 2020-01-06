const path = require('path');

module.exports = {
	entry: {
		admin: './admin/js/demovox-admin.js',
		public: './public/js/demovox-public.js',
		'public-pdf': './public/js/demovox-public-pdf.js'
	},
	output: {
		path: path.resolve(__dirname),
		filename: (chunkData) => {
			return (chunkData.chunk.name === 'admin' ? '/admin' : '/public') + '/js/demovox-[name].min.js';
		},
		// filename: 'demovox-[name].min.js'
	},
	module: {
		rules: [
			{
				test: /\.css$/,
				use: ['style-loader', 'css-loader'],
			},
		],
	},
	externals: {
		// require("jquery") is external and available
		//  on the global var jQuery
		"jquery": "jQuery"
	},
	performance: {
		maxAssetSize: 1200000,
		maxEntrypointSize: 1200000,
	},
	mode: 'production',
};
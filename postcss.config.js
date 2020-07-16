module.exports = (ctx) => ({
  plugins: {
    "postcss-easy-import": {
      extensions: ".pcss",
    },
    // "postcss-import": {},
    "postcss-inline-svg": {},
    "postcss-mixins": {},
    "postcss-simple-vars": {},
    "postcss-nested": {},
    "postcss-combine-media-query": {},
    autoprefixer: {},
    cssnano: {},
  },
});

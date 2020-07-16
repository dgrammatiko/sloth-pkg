const {
  existsSync,
  copy,
  mkdirSync,
  readFileSync,
  readlinkSync,
  symlinkSync,
  lstatSync,
  writeFileSync,
} = require("fs-extra");
const { js } = require("./processJs.js");
const { css } = require("./processCss.js");

if (!existsSync("./sloth_tmp")) {
  mkdirSync("./sloth_tmp");
}

copy("tmpl_sloth/sloth", "sloth_tmp");

js("tmpl_sloth/media_src/js");
css("tmpl_sloth/media_src/css");

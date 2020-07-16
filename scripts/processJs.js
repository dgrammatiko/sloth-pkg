const { execSync } = require("child_process");
const {
  existsSync,
  mkdirpSync,
  readFileSync,
  readlinkSync,
  symlinkSync,
  lstatSync,
  writeFileSync,
} = require("fs-extra");
const { resolve } = require("path");
const { yellow, magenta } = require("chalk");
const crypto = require("crypto");
const readdirp = require("readdirp");

const { gzipFile } = require("./compress.js");

const root = process.cwd();
const settings = require(`${root}/settings.json`);
const dest = `${settings.options.template}`;
const assets = require(`${root}/${dest}/joomla.asset.json`);

module.exports.js = (input) => {
  if (!input || input === true) {
    console.log("no path?");
    return;
  }

  // input is Directory
  if (lstatSync(resolve(`${process.cwd()}/${input}`)).isDirectory()) {
    (async function () {
      for await (const entry of readdirp(
        resolve(`${process.cwd()}/${input}`)
      )) {
        let { path } = entry;
        path = `${process.cwd()}/${input}/${path}`;
        console.log(`${JSON.stringify({ path })}`);
        if (!path.endsWith(".js")) {
          return;
        }
        const outputFile = path
          .replace(".js", ".min.js")
          .replace(`tmpl_sloth/media_src`, `${dest}`);

        execSync(
          `npx rollup --config rollup.config.js ${path} -o ${outputFile}`
        );

        for (let asset of assets.assets) {
          if (
            asset.uri ===
            outputFile.replace(`${process.cwd()}/tmpl_sloth/sloth/js/`, "")
          ) {
            const ff = readFileSync(outputFile, { encoding: "utf8" });
            const sha256 = crypto.createHash("sha256").update(ff).digest("hex");
            // Get the hash and store it in version
            asset.version = sha256;
          }
        }

        if (!path.endsWith("sw.js")) {
          gzipFile(outputFile);
        }

        writeFileSync(
          resolve(`${root}/${dest}/joomla.asset.json`),
          JSON.stringify(assets, null, 2),
          { encoding: "utf8" }
        );
      }
    })();
  }
};

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
const { async: crawl } = require("fdir");
const crypto = require("crypto");

const { gzipFile } = require("./compress.js");

const root = process.cwd();
const settings = require(`${root}/settings.json`);
const dest = `${settings.options.template}`;
const assets = require(`${root}/${dest}/joomla.asset.json`);

module.exports.css = (input) => {
  // input is Directory
  if (lstatSync(resolve(process.cwd(), input)).isDirectory()) {
    crawl(resolve(process.cwd(), input)).then((results) => {
      results.forEach((r) => {
        const x = r.split("/");
        if (!r.endsWith("css") || x[x.length - 1].startsWith("_")) {
          return;
        }

        const outputFile = r
          .replace(".css", ".min.css")
          .replace("tmpl_sloth/media_src", `${dest}`);

        execSync(`npx postcss ${r} -o ${outputFile}`);

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

        gzipFile(outputFile);
      });

      writeFileSync(
        resolve(`${root}/${dest}/joomla.asset.json`),
        JSON.stringify(assets, null, 2),
        { encoding: "utf8" }
      );
    });
  }
};

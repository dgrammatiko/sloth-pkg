const { readFile, writeFile, copy, remove } = require("fs-extra");
const { version } = require("../package.json");

(async function exec() {
  let xml = await readFile("./sloth_tmp/templateDetails.xml", {
    encoding: "utf8",
  });
  xml = xml.replace(/{{version}}/g, version);

  await writeFile("./sloth_tmp/templateDetails.xml", xml, { encoding: "utf8" });
  await copy(
    `${process.cwd()}/tmpl_sloth/media_src/images`,
    `${process.cwd()}/sloth_tmp/images`
  );

  // Package it
  const zip = new (require("adm-zip"))();
  zip.addLocalFolder("sloth_tmp", false);
  zip.writeZip(`packages/tmpl_sloth.zip`);

  await remove("sloth_tmp");
  await remove("tmpl_sloth");
})();

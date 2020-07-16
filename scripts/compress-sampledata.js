const { readFile, writeFile, copy, remove } = require("fs-extra");
const { version } = require("../package.json");

(async function exec() {
  let xml = await readFile("./plg_sampledata/sloth.xml", {
    encoding: "utf8",
  });
  xml = xml.replace(/{{version}}/g, version);

  await copy("plg_sampledata", "tmp_sampledata");
  await writeFile("./tmp_sampledata/sloth.xml", xml, { encoding: "utf8" });

  // Package it
  const zip = new (require("adm-zip"))();
  zip.addLocalFolder("tmp_sampledata", false);
  zip.writeZip(`packages/plg_sampledata_sloth.zip`);

  await remove("tmp_sampledata");
})();

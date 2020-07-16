const { readFile, writeFile, remove, unlink } = require("fs-extra");
const { version } = require("../package.json");

(async function exec() {
  let xml = await readFile("./scripts/pkg_sloth.xml", {
    encoding: "utf8",
  });
  xml = xml.replace(/{{version}}/g, version);

  await writeFile("./pkg_sloth.xml", xml, { encoding: "utf8" });

  const zip = new (require("adm-zip"))();
  zip.addLocalFolder("packages", "packages");
  zip.addLocalFile("pkg_sloth.xml", false);
  zip.addLocalFile("./scripts/script.php", false);

  zip.writeZip(`docs/packages/tmpl_sloth-v${version}.zip`);

  await unlink("./pkg_sloth.xml");
})();

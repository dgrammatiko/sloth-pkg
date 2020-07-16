const promises = require("util");
const { mkdir, exists } = require("fs").promises;
const { existsSync } = require("fs");
const { execSync } = require("child_process");

(async () => {
  // Clone Component com_frontpage https://github.com/dgrammatiko/com_frontpage
  // if (!existsSync("com_landing")) {
  //   await mkdir("com_landing");
  // }
  // execSync(
  //   "cd com_landing && npx degit --force dgrammatiko/com_frontpage/src/component"
  // );

  // Clone Plugin responsive_images https://github.com/ttc-freebies/plugin-responsive-images
  // if (!existsSync("plg_responsive")) {
  //   await mkdir("plg_responsive");
  // }
  // execSync(
  //   "cd plg_responsive && npx degit --force ttc-freebies/plugin-responsive-images/src"
  // );

  // Clone Template  Sloth https://github.com/dgrammatiko/sloth
  if (!existsSync("tmpl_sloth")) {
    await mkdir("tmpl_sloth");
  }
  execSync(
    "cd tmpl_sloth && npx degit --force https://github.com/dgrammatiko/sloth#step-5"
  );
})();

<?php
/**
 * Based on https://github.com/anibalsanchez/extly-buildfiles-for-joomla/blob/master/package/script.foo.php
 * by Anibal Sanchez: https://github.com/anibalsanchez
 */
// No direct access
defined('_JEXEC') || die('<html><head><script>location.href = location.origin</script></head></html>');

use \Joomla\CMS\Application\ApplicationHelper as CMSAppHelper;
use Joomla\CMS\Installer\Adapter\PackageAdapter;
use \Joomla\CMS\Factory as CMSFactory;
use \Joomla\CMS\Log\Log as Log;
use \Joomla\CMS\Cache\Cache as CMSCache;
use Joomla\CMS\Version;

/**
 * Installation class to perform additional changes during install/uninstall/update
 */
class Pkg_SlothInstallerScript extends \Joomla\CMS\Installer\InstallerScript {
  /**
   * A list of extensions (modules, plugins) to enable after installation. Each item has four values, in this order:
   * type (plugin, module, ...), name (of the extension), client (0=site, 1=admin), group (for plugins).
   *
   * @var array
   */
  protected $extensionsToEnable = [
    ['plugin', 'responsive', 0, 'content' ],
    ['plugin', 'sloth', 0, 'sampledata' ],
    ['component', 'com_landing', 1, '' ],
    ['template', 'sloth', 0, '' ],
  ];

  /**
   * Constructor
   *
   * @param   PackageAdapter  $adapter  The object responsible for running this script
   */
  public function __construct(PackageAdapter $adapter) {
    $this->minimumJoomla = '4.0';
    $this->minimumPhp = JOOMLA_MINIMUM_PHP;
  }

  /**
   * Tuns on installation (but not on upgrade). This happens in install and discover_install installation routes.
   *
   * @param   PackageAdapter  $parent  Parent object
   *
   * @return  boolean
   */
  public function install(PackageAdapter $parent) {
    // Enable the extensions we need to install
    $this->enableExtensions();

    return true;
  }

  /**
   * Enable modules and plugins after installing them
   *
   * @return  void
   */
  private function enableExtensions() {
    foreach ($this->extensionsToEnable as $ext)
    {
      $this->enableExtension($ext[0], $ext[1], $ext[2], $ext[3]);
    }
  }

  /**
   * Enable an extension
   *
   * @param   string   $type    The extension type.
   * @param   string   $name    The name of the extension (the element field).
   * @param   integer  $client  The application id (0: Joomla CMS site; 1: Joomla CMS administrator).
   * @param   string   $group   The extension group (for plugins).
   *
   * @return  void
   */
  private function enableExtension($type, $name, $client = 1, $group = null) {
    $db = CMSFactory::getDbo();

    $query = $db->getQuery(true)
      ->update('#__extensions')
      ->set($db->qn('enabled') . ' = ' . $db->q(1))
      ->where('type = ' . $db->quote($type))
      ->where('element = ' . $db->quote($name));

    switch ($type) {
      case 'plugin':
        // Plugins have a folder but not a client
        $query->where('folder = ' . $db->quote($group));
        break;

      case 'language':
      case 'module':
      case 'template':
        // Languages, modules and templates have a client but not a folder
        $client = CMSAppHelper::getClientInfo($client, true);
        $query->where('client_id = 0'); // . (int) $client->id);
        break;

      default:
      case 'library':
      case 'package':
      case 'component':
        // Components, packages and libraries don't have a folder or client.
        // Included for completeness.
        break;
    }

    $db->setQuery($query);

    try {
      $db->execute();
    } catch (\Exception $e) {
      // var_dump($e);
    }
  }
}

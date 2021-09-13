<?php defined('_JEXEC') || die('<html><head><script>location.href = location.origin</script></head></html>');
/**
 * @copyright Copyright (C) 2020 Dimitris Grammatikogiannis. All rights reserved.
 * @license GNU General Public License version 2 or later
 */
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class PlgSampledataSloth extends \Joomla\CMS\Plugin\CMSPlugin {
  protected $steps = 8;        /** @var integer $steps  How many steps  */
  protected $app;              /** @var Joomla\CMS\Application\AdministratorApplication $app */
  protected $db;               /** @var Joomla\Database\DatabaseDriver $db */
  protected $pluginData;       /** @var \stdClass $pluginData The Json data  */
  protected $access;           /** @var integer $access Access level  */
  protected $user;             /** @var \stdClass $user Admin user  */
  protected $year;             /** @var integer $year well  */
  protected $month;            /** @var integer $month well  */
  protected $day;              /** @var integer $day well  */
  protected $stepsData;        /** @var \stdClass $stepsData well  */
  protected $autoloadLanguage = true;     /** @var boolean $autoloadLanguage */

  public function __construct(&$subject, $config = array()) {
    if (file_exists(__DIR__ . '/data.json')) {
      try {
        $json = file_get_contents(__DIR__ . '/data.json');
        $data = json_decode($json);
        $this->pluginData = $data->plugin;
        $this->stepsData = $data->steps;
      } catch (\Exception $e) {
        new \Exception('Plugin doesn\'t have valid data');
      }
    }

    parent::__construct($subject, $config);

    $this->year   = date('Y');
    $this->month  = date('m');
    $this->day    = date('d');
    $this->user   = Factory::getUser();
    $this->access = (int) $this->app->get('access', 1);
  }

  public function onSampledataGetOverview() {
    $data              = new stdClass;
    $data->name        = $this->_name;
    $data->title       = Text::_($this->pluginData->strings->TITLE);
    $data->description = Text::_($this->pluginData->strings->DESCRIPTION);
    $data->icon        = 'chess-king';
    $data->steps       = $this->steps;

    return $data;
  }

  public function onAjaxSampledataApplyStep1() {
    if (!$this->accessCheck()) {
      return self::message(false, Text::_('JERROR'));
    }

    $currentComponent = $this->stepsData->{0}->component;
    $currentData = $this->stepsData->{0}->data;
    $totalExtensions = count($currentData);

    if ($totalExtensions === 0) {
      return self::message(true, Text::sprintf($this->pluginData->strings->STEP1_SUCCESS, 0, $currentComponent));
    }

    foreach ($currentData as $num => $data) {
      $this->updateRow($data, $currentComponent, $num);
    }

    return self::message(true, Text::sprintf($this->pluginData->strings->STEP1_SUCCESS, $totalExtensions, $currentComponent));
  }

  public function onAjaxSampledataApplyStep2() {
    if (!$this->accessCheck()) {
      return self::message(false, Text::_('JERROR'));
    }

    return $this->step(1);
  }

  public function onAjaxSampledataApplyStep3() {
    if (!$this->accessCheck()) {
      return self::message(false, Text::_('JERROR'));
    }

    return $this->step(2);
  }

  public function onAjaxSampledataApplyStep4() {
    if (!$this->accessCheck()) {
      return self::message(false, Text::_('JERROR'));
    }

    return $this->step(3);
  }

  public function onAjaxSampledataApplyStep5() {
    if (!$this->accessCheck()) {
      return self::message(false, Text::_('JERROR'));
    }

    return $this->step(4);
  }

  public function onAjaxSampledataApplyStep6() {
    if (!$this->accessCheck()) {
      return self::message(false, Text::_('JERROR'));
    }

    return $this->step(5);
  }

  public function onAjaxSampledataApplyStep7() {
    if (!$this->accessCheck()) {
      return self::message(false, Text::_('JERROR'));
    }

    return $this->copyFiles();
  }

  public function onAjaxSampledataApplyStep8() {
    if (!$this->accessCheck()) {
      return self::message(false, Text::_('JERROR'));
    }

    return self::message(true, Text::_($this->pluginData->strings->STEP7_SUCCESS));
  }

  private function accessCheck() {
    if ($this->app->input->get('type') === $this->_name) {
      return true;
    }
  }

  private static function message($success, $message) {
    return [ 'success' => $success, 'message' => $message ];
  }

  protected function createItem(int $access, $item, int $userId, $componentModel, array $catIds, $id, $sessionData) {
    if (property_exists($item, 'id')) {
      $item->id = 0;
    }

    if (property_exists($item, 'published')) {
      $item->published = 1;
    }

    if (property_exists($item, 'transition')) {
      $item->transition = 2;
    }

    if (property_exists($item, 'catid')) {
      $item->catid = $sessionData['sampledata_com_categories_Category'][$item->catid];
    }

    if (property_exists($item, 'access')) {
      $item->access = $access;
    }

    if (property_exists($item, 'created_user_id')) {
      $item->created_user_id = $userId;
    }

    if (property_exists($item, 'alias')) {
      $item->alias = ApplicationHelper::stringURLSafe($item->title);
    }

    if (property_exists($item, 'publish_up')) {
      $item->publish_up = $this->year . '-' . $this->month . '-' . $this->day . ' 01:01:01';
    }

    if (!$componentModel->save(json_decode(json_encode($item), true))) {
      new \Exception($componentModel->getError());
    }

    $catIds[$id] = $componentModel->getState($componentModel->getName() . '.id');

    return $catIds;
  }

  protected function updateRow($data, $currentComponent, $num) {
    $query = $this->db->getQuery(true);
    $fields = [
      $this->db->quoteName('enabled') . ' = ' . (int) $data->enabled,
      $this->db->quoteName('params') . ' = ' . $this->db->quote(json_encode($data->params))
    ];
    $conditions = [
      $this->db->quoteName('type') . ' = ' . $this->db->quote($data->type),
      $this->db->quoteName('name') . ' = ' . $this->db->quote($data->name)
    ];

    switch ($data->type) {
      case 'plugin':
        $conditions[] = $this->db->quoteName('folder') . '=' . $this->db->quote($data->folder);
        break;
      case 'language':
      case 'module':
      case 'template':
        $conditions[] = $this->db->quoteName('client_id') . '=' . $this->db->quote($data->client_id);
        break;
      default:
      case 'library':
      case 'package':
      case 'component':
        break;
    }

    $query->update($this->db->quoteName('#__extensions'))->set($fields)->where($conditions);
    $this->db->setQuery($query);

    try {
      $this->db->execute();
    } catch (\Exception $e) {
      return self::message(false,  Text::sprintf($this->pluginData->strings->STEP_SKIPPED, 1, $currentComponent));
    }
  }

  private function step($num) {
    $idNext = [];
    $step = $this->stepsData->{$num};
    $currentComponent = $step->component;
    $totalCategories = count($step->data);

    if ($totalCategories === 0) {
      return self::message(true, Text::sprintf($this->pluginData->strings->STEP_SKIPPED, 0, $currentComponent));
    }

    $sessionData = [];
    for ($i = 1, $l = $num; $i <= $l; $i++) {
      $compTmp = $this->stepsData->{$i}->component;
      $modelTmp = $this->stepsData->{$i}->model;
      $name = 'sampledata_' . $compTmp . '_' . $modelTmp;
      $data = $this->app->getUserState($name);
      if ($data) {
        $sessionData[$name] = $data;
      }
    }

    $componentModel = $this->app->bootComponent($currentComponent)->getMVCFactory()
      ->createModel($step->model, 'Administrator', ['ignore_request' => true]);

    if ($currentComponent === 'com_menus' && $step->model === 'Item') {
      $templateId = $this->getTemplateId(0, 'sloth');
    }

    foreach ($step->data as $id => $item) {
      if ($step->component === 'com_menus') {
        $componentModel->setState('item.id', 0);

        if (property_exists($item, 'template_style_id')) {
          $item->template_style_id = $templateId;
        }

        // Set the correct cat id/component id
        if (isset($item->link)) {
          $queryString = parse_url($item->link, PHP_URL_QUERY);
          parse_str($queryString, $queryArr);
          preg_match('/option\=(.*?)&/', $item->link, $matches);

          if (count($matches)) {
            $item->component_id = ComponentHelper::getComponent($matches[1])->id;
          }

          if (isset($queryArr['id'])) {
            preg_match('/\{\{(.*?)\}\}/', $queryArr['id'], $output_arr);

            if ($output_arr && strpos($output_arr[1], 'com_categories_Category:') !== false) {
              $newLinkId = (int)str_replace('com_categories_Category:', '', $output_arr[1]);
              $item->link = preg_replace('/\{\{.*?\}\}/', $sessionData['sampledata_com_categories_Category'][$newLinkId], $item->link);
            }
          }
        }
      }

      if ($step->component === 'com_modules') {
        if (!empty($item->params)) {
          $params = json_decode($item->params);
          if (isset($params->catid)) {
            $params->catid = [$sessionData['sampledata_com_categories_Category'][$params->catid[0]]];
          }

          $item->params = json_encode($params);
        }
      }

      $idNext = $this->createItem($this->access, $item, $this->user->id, $componentModel, $idNext, $id, $sessionData);
    }

    if (isset($idNext) && count($idNext)) {
      $this->app->setUserState('sampledata_' . $currentComponent . '_' . $step->model, $idNext);
    }

    return self::message(true, Text::sprintf($this->pluginData->strings->{'STEP' . ($num + 1) . '_SUCCESS'}, $totalCategories, $currentComponent));
  }

  private function getTemplateId($client, $name) {
    $conditions = [
      $this->db->quoteName('template') . ' = ' . $this->db->quote($name),
      $this->db->quoteName('client_id') . '=' . (int) $this->db->quote($client),
    ];

    $query = $this->db->getQuery(true);

    $query->select($this->db->quoteName('id'))
      ->from($this->db->quoteName('#__template_styles'))
      ->where($conditions);

    $this->db->setQuery($query);

    try {
      $this->db->execute();
    } catch (\Exception $e) {
      return self::message(false,  Text::sprintf($this->pluginData->strings->STEP_SKIPPED, 1, 'x'));
    }

    return $this->db->loadResult();
  }

  private function copyFiles() {
    $zip = new \ZipArchive;
    if (is_file(__DIR__ . '/zips/images.zip')) {
      if ($zip->open(__DIR__ . '/zips/images.zip') === true) {
        $zip->extractTo(JPATH_ROOT . '/images');
        $zip->close();
      } else {
        $msg[] = 'images.zip';
      }
    }
    if (is_file(__DIR__ . '/zips/cached-resp-images.zip')) {
      if ($zip->open(__DIR__ . '/zips/cached-resp-images.zip') === true) {
        $zip->extractTo(JPATH_ROOT . '/media');
        $zip->close();
      } else {
        $msg[] = 'cached-resp-images.zip';
      }
    }

    if (isset($msg) && count($msg) > 0) {
      self::message(false, Text::_('JERROR'), 0, implode(', ', $msg));
    }
    return self::message(true,  Text::_($this->pluginData->strings->FILES_COPIED, 1, 'x'));
  }
}

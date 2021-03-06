<?php
/**
 * @filesource modules/edocument/models/settings.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Edocument\Settings;

use \Kotchasan\Http\Request;
use \Gcms\Login;
use \Kotchasan\Language;
use \Kotchasan\Config;

/**
 * บันทึกการตั้งค่าโมดูล
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\KBase
{

  /**
   * รับค่าจาก settings.php
   *
   * @param Request $request
   */
  public function submit(Request $request)
  {
    $ret = array();
    // session, token, can_config
    if ($request->initSession() && $request->isSafe() && $login = Login::isMember()) {
      if ($login['username'] != 'demo' && Login::checkPermission($login, 'can_config')) {
        // รับค่าจากการ POST
        $typies = array();
        foreach (explode(',', strtolower($request->post('edocument_file_typies')->filter('a-zA-Z0-9,'))) as $typ) {
          if ($typ != '') {
            $typies[$typ] = $typ;
          }
        }
        // โหลด config
        $config = Config::load(ROOT_PATH.'settings/config.php');
        $config->edocument_format_no = $request->post('edocument_format_no')->topic();
        $config->edocument_send_mail = $request->post('edocument_send_mail')->toBoolean();
        $config->edocument_file_typies = array_keys($typies);
        $config->edocument_upload_size = $request->post('edocument_upload_size')->toInt();
        if (empty($config->edocument_file_typies)) {
          // คืนค่า input ที่ error
          $ret['ret_edocument_file_typies'] = 'this';
        } else {
          // save config
          if (Config::save($config, ROOT_PATH.'settings/config.php')) {
            // คืนค่า
            $ret['alert'] = Language::get('Saved successfully');
            $ret['location'] = 'reload';
            // เคลียร์
            $request->removeToken();
          } else {
            // ไม่สามารถบันทึก config ได้
            $ret['alert'] = sprintf(Language::get('File %s cannot be created or is read-only.'), 'settings/config.php');
          }
        }
      }
    }
    if (empty($ret)) {
      $ret['alert'] = Language::get('Unable to complete the transaction');
    }
    // คืนค่าเป็น JSON
    echo json_encode($ret);
  }
}

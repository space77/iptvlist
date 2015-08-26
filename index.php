<?php
  /*--- Настройки ---*/
  $m3u_fname                = "./stc.m3u";  // файл плейлиста *.m3u    
  
  class tv_channel {
    public $id              = 0;            // целое число идентификатор канала
    public $number          = 0;            // целое число номер канала
    public $caption         = "";           // строка название канала отображаемое в списке
    public $icon_url        = "";           // строка url иконки канала
    public $tv_categories   = array();      // целое число ид категории каналов
    public $streaming_url   = "";           // строка ссылка на url потока
    public $announce        = "";           // строка ананс канала
    public $volume_shift    = 0;            // целое число смещение громкости 
  }
  
  class tv_category {
    public $id              = 0;            // целое число идентификатор группы
    public $caption         = "";           // строка название группы отображаемое в списке
    public $icon_url        = "";           // строка url иконки группы
  }

  class tv_list {
    public $operator_name   = "STC";        // строка имя оператора
    public $epg_url         = "";           // строка
    public $sync_time       = 1430567772;   // число целое 1430567772
    public $time_zone       = "+9";         // строка +9
	  public $tv_info         = array();      // массив строк Информация в формате html
    public $tv_announces    = "";           // строка
    public $tv_categories   = array();      // массив категорий каналов 
    public $tv_channels     = array();      // массив каналов*/
  }
  
  define('RES_OK',                    0x00000000);
  define('RES_ERR_FILE_NOT_EXISTS',   0x00000001);
  define('RES_ERR_FILE_EMPTY',        0x00000002);
  define('RES_ERR_NOT_PARSE',         0x00000003);
  
  $result = RES_OK;
  $srv_root = "http://".$_SERVER["HTTP_HOST"]."/iptvlist";
    
  // 1. Проверяем наличие файлв
  if (!file_exists($m3u_fname)) {
    $result = RES_ERR_FILE_NOT_EXISTS;  
  } else {
    // 2. Читаем все содержимое файла 
    $m3u_content = file_get_contents($m3u_fname);
  
    // 3. Производим разбор файла регулярным вырожением  
    //m3u_patern = '{^#EXTINF\:\d,(?<chnum>\d+)\.\s(?<chname>[\s\w+]+$)\r(?<chaddr>udp://@\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}:\d+$)}m';
    //m3u_patern = '{^#EXTINF\:\d,(\d+)\. ([\s\w]+)$[\n|\r](udp://@\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}:\d+)$}mi';
    //$m3u_patern = '{^#EXTINF\:\d,(?<chnum>\d+)\. (?<chname>[\s\w]+)$[\n|\r](?<chaddr>udp://@\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}:\d+)$}mi';
    $m3u_patern = '{^#EXTINF\:\d,(?<chnum>\d+)\.\s(?<chname>[[:print:]]+)[^\R]$[^\R](?<chaddr>udp://@\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}:\d+)}miu'; 
    preg_match_all($m3u_patern, $m3u_content, $mathes, PREG_SET_ORDER ); // PREG_PATTERN_ORDER (def), PREG_SET_ORDER, PREG_OFFSET_CAPTURE
    
    // 4. Создаем плей лист
    $tvlist = new tv_list();
    $tvlist->operator_name          = "STC";
    $tvlist->epg_url                = "";
    $tvlist->sync_time              = (int)1430567772;
  	$tvlist->time_zone              = "+9";
  	$tvlist->tv_info                = array ();
  	$tvlist->tv_announces           = "";
  	  
    // 5. Создаем коллекцию категорий каналов
    $tvcategories = array();
    $tvcategory = new tv_category();
     
    $tvcategory->id                 = (int)1;
    $tvcategory->caption            = "TV1";
    $tvcategory->icon_url           = "$srv_root/images/categories/tv.png";
    $tvcategories[$tvcategory->id]   = $tvcategory;
    
    $tvcategory = new tv_category();
    $tvcategory->id                 = (int)2;
    $tvcategory->caption            = "Radio";
    $tvcategory->icon_url           = "$srv_root/images/categories/radio.png";
    $tvcategories[$tvcategory->id]  = $tvcategory;
    
    $tvlist->tv_categories          = $tvcategories;
    
    // 6. Создаем коллекцию каналов
    $tvchannels = array();
    foreach ($mathes as $item) {
      $tvchannel = new tv_channel();
      $tvchannel->id                = (int)$item["chnum"];
      $tvchannel->number            = (int)$item["chnum"];
      $tvchannel->caption           = $item["chname"];
      $tvchannel->icon_url          = "";
      $tvchannel->tv_categories[]    = 1;
      $tvchannel->streaming_url     = $item["chaddr"];
      $tvchannel->announce          = "";
      $tvchannel->volume_shift      = 0;
      $tvchannels[$tvchannel->id]   = $tvchannel;
    }
    $tvlist->tv_channels            = $tvchannels;
    
    $retstr = str_replace('\/', '/', json_encode($tvlist));
    
    header('Content-Type: text/html; charset=UTF-8');
    echo $retstr;
  }
?>
<?php

#
# Класс для работы с RSS-лентами сайтов
#
# Для успешной работы необходим файл 'sites.ini' в текущей директории (по умолчанию),
# либо необходимо задать путь к вашему файлу в свойстве $sites_config
#
#
# Создать новый экземпляр класса:
#   $site = new SITE(); // будет выбран случайный сайт
#   или
#   $site = new SITE('bash'); // будет выбран сайт с именем 'bash', если он будет найден в списке
#
class RSS {
  
  # Свойства конфига
  private $sites_config = 'sites.ini';
  private $sites_config_data = NULL;
  
  # Свойства сайта
  private $site_name;
  private $site_rss_url;
  private $site_title;
  private $site_link;
  
  # Свойства rss-элементов
  private $rss_data;
  private $rss_random_item;
  private $rss_item_title;
  private $rss_item_text;
  private $rss_item_date;
  private $rss_item_link;
  
  
  
  
  #
  # При создании экземпляра класса можно задать имя сайта (необязательно),
  # из RSS-ленты которого необходимо вернуть требуемый элемент(ы).
  #
  # Если имя сайта не задано, либо такого сайта нет в предопределенном 
  # списке - сайт будет выбран случайным образом.
  #
  public function __construct($site_name = NULL){
    
    // Устанавливаем путь к конфиг-файлу
    $this->setSitesConfig();
    
    // Парсим конфиг-файл и получаем массив данных
    $this->setSitesConfigData();
    
    // Устанавливаем имя сайта
    $this->setSiteName($site_name);
    
    // Устанавливаем URL-адрес RSS-ленты сайта
    $this->setSiteRssUrl();
    
    // Парсим всю RSS-ленту в виде массива
    $this->setRssData();
    
    // Устанавливаем заголовок сайта (берем из RSS-ленты)
    $this->setSiteTitle();
    
    // Устанавливаем ссылку сайта (берем из RSS-ленты)
    $this->setSiteLink();
    
    // Устанавливаем случайный элемент RSS-ленты
    $this->setRssRandomItem();
    
    // Устанавливаем заголовок элемента RSS-ленты
    $this->setRssItemTitle();
    
    // Устанавливаем ссылку на элемент RSS-ленты
    $this->setRssItemLink();
    
    // Устанавливаем текст элемента RSS-ленты
    $this->setRssItemText();
    
    // Устанавливаем дату и время публикации элемента RSS-ленты
    $this->setRssItemDate();
    
  }
  
  
  
  
  #
  # Замена классическим геттерам и сеттерам, не требующих обработки/проверки свойств.
  #
  # Получение и установка свойств объекта через вызов метода вида: 
  # $object->(get|set)PropertyName($prop); 
  #
  # @see __call 
  # @return mixed 
  # 
  public function __call($method_name, $argument) {
    
    $args = preg_split('/(?<=\w)(?=[A-Z])/', $method_name);
    $action = array_shift($args);
    $property_name = strtolower(implode('_', $args));
    
    switch ($action) {
      case 'get':
        return isset($this->$property_name) ? $this->$property_name : NULL;
         
      case 'set':
        $this->$property_name = $argument[0];
        return $this;
    }
    
  }
  
  
  
  
  #
  # Метод парсит конфиг-файл, который должен быть в формате .ini
  # Подробности: http://php.net/parse_ini_file
  #
  # В случае успеха, возвращает многомерный массив с данными.
  # 
  # @return array
  #
  public function parseConfigFile($filename) {
    
    // Пробуем распарсить ini-файл ...
    if ($ini_array = parse_ini_file($filename , true)) {
      // всё ок, возвращаем полученный массив
      return $ini_array;
    }
    
  }
  
  
  
  #
  # Метод парсит указанную RSS-ленту сайта и возвращает в виде объекта.
  # Иначе выдает ошибку.
  #
  # @return object
  #
  public static function parseRSS($rss_url) {
    
    // Подавляем все ошибки XML для последующей самостоятельной обработки
    libxml_use_internal_errors(true);
    
    $xml = simplexml_load_file($rss_url, 'SimpleXMLElement', LIBXML_NOCDATA);
    
    if ($xml) {
      return $xml;
    }
    else {
      echo "<h1>RSS loading error!</h1>";
      echo "<p>Details: <br />";
  
      foreach(libxml_get_errors() as $error) {
        echo $error->message . ".<br />";
      }
      
      echo "</p>";
    }
    
  }
  
  
  
  
  # 
  # Метод устанавливает путь к конфиг-файлу.
  #
  public function setSitesConfig($sites_config = NULL) {
    
    if (isset($sites_config) && is_readable($sites_config)) {
      $this->sites_config = $sites_config;
    }
    
    return $this;
    
  }
  
  
  
  
  #
  # Метод устанавливает свойство со всеми данными из конфиг-файла.
  #
  public function setSitesConfigData() {
    
    $this->sites_config_data = $this->parseConfigFile($this->getSitesConfig());
    
    return $this;
    
  }
  
  
  
  
  #
  # Метод получения данных всей RSS-ленты определенного сайта.
  #
  public function setRssData() {
    
    // Получаем имя сайта
    $site_name = $this->getSiteName();
    
    // Получаем URL-адрес выбранного сайта
    $site_rss_url = $this->getSiteRssUrl($site_name);
    
    // Получаем всю RSS-ленту выбранного сайта
    $this->rss_data = $this->parseRSS($site_rss_url);
    
    return $this;
    
  }
  
  
  
  
  #
  # Метод устанавливает свойство - случайный элемент RSS-ленты.
  #
  public function setRssRandomItem() {
    
    // Получаем все элементы ленты в узле item
    $items = $this->getRssData()->channel[0]->item;
    
    // Возвращаем случайный item-элемент
    $this->rss_random_item = $items[rand(0, count($items)-1)];
    
    return $this;
    
  }
  
  
  
  
  #
  # Метод возвращает значение указанного атрибута из 
  # конфиг-файла, если атрибут есть и он не пустой. 
  # Иначе берет значение атрибута из RSS-ленты.
  #
  public function getSiteAttribute($attr_name) {
    
    // Получаем из конфиг-файла массив данных
    $sites = $this->getSitesConfigData();
      
    // Получаем атрибут сайта из конфиг-файла
    $site_attr = $sites[$this->getSiteName()][$attr_name];
      
    // Проверяем, указан ли данный атрибут в конфиге...
    if ($site_attr == NULL) {
      // если не указан - берем значение атрибута из одноименного поля RSS-ленты
      $site_attr = $this->getRssData()->channel[0]->$attr_name;
    }
    
    return $site_attr;
    
  }
  
  
  
  
  #
  # Метод устанавливает имя сайта.
  #
  # Берет указанное имя сайта, если имя сайта задано (не пустое значение) и такой сайт есть в конфиге.
  # Иначе выбирает сайт из конфига случайным образом.
  #
  public function setSiteName($site_name = NULL) {
    
    // Получаем из конфиг-файла массив данных
    $sites = $this->getSitesConfigData();
    
    // Проверяем, что указанный сайт есть в списке...
    if (isset($site_name) && array_key_exists($site_name, $sites)) {
      // если есть - устанавливаем имя сайта, заменяя некоторые символы на подчеркивание
      $this->site_name = str_replace([" ","."], "_", $site_name);
    }
    else {
      // если нет - берем случайный сайт
      $this->site_name = array_rand($sites, 1);
    }
    
    return $this;
    
  }
  
  
  
  
  #
  # Метод получает URL-адрес RSS-ленты определенного сайта.
  #
  public function setSiteRssUrl() {
    
    $this->site_rss_url = $this->getSiteAttribute('rss');
    
    return $this;
    
  }
  
  
  
  
  #
  # Метод устанавливает заголовок сайта - из конфиг-файла (если указано), либо из RSS-ленты.
  #
  public function setSiteTitle($site_name = NULL) {
    
    $this->site_title = $this->getSiteAttribute('title');
    
    return $this;
  }
  
  
  
  
  #
  # Метод устанавливает ссылку на сайт - из конфиг-файла (если указано), либо из RSS-ленты
  #
  public function setSiteLink() {
    
    $this->site_link = $this->getSiteAttribute('link');
    
    return $this;
    
  }
  
  
  
  
  #
  # Метод устанавливает заголовок случайного элемента RSS-ленты.
  #
  public function setRssItemTitle() {
    
    // Получаем заголовок случайного элемента
    $title = $this->getRssRandomItem()->title;
    
    // На всякий случай, перед тем как вернуть значение - приводим к текстовому типу
    $this->rss_item_title = (string)$title;
    
    return $this;
    
  }
  
  
  
  
  #
  # Метод устанавливает ссылку случайного элемента RSS-ленты.
  #
  public function setRssItemLink() {
    
    // Получаем содержимое поля guid случайного элемента
    $guid = $this->getRssRandomItem()->guid;
    
    // Получаем содержимое поля link случайного элемента
    $link = $this->getRssRandomItem()->link;
    
    // Проверяем, содержит ли поле guid что-то, похожее на ссылку ...
    if (filter_var($guid, FILTER_VALIDATE_URL)) {
      // да, это URL - используем в качестве ссылки случайного элемента
      $url = $guid;
    }
    else {
      // точно нет - берем ссылку случайного элемента из поля link
      $url = $link;
    }
    
    // на всякий случай, перед тем как вернуть значение - приводим к текстовому типу
    $this->rss_item_link = (string)$url;
    
    return $this;
    
  }
  
  
  
  
  #
  # Метод устанавливает текст случайного элемента RSS-ленты.
  #
  public function setRssItemText($site_name = NULL) {
    
    // Получаем текст случайного элемента
    $text = $this->getRssRandomItem()->description;
    
    // На всякий случай, перед тем как вернуть значение - приводим к текстовому типу
    $this->rss_item_text = (string)$text;
    
    return $this;
    
  }
  
  
  
  
  #
  # Метод устанавливает временную метку публикации случайного 
  # элемента RSS-ленты в формате дата-время (http://php.net/date).
  #
  public function setRssItemDate($site_name = NULL) {
    
    // Получаем дату и время публикации случайного элемента
    $pubDate = $this->getRssRandomItem()->pubDate;
    
    // Проверяем переменную (должен быть объект + тип Дата/Время) ...
    if (!is_object($pubDate) || !$pubDate instanceof Datetime) {
      // похоже, что-то не то - форматируем
      $pubDate = new Datetime($pubDate);
    }
    
    $this->rss_item_date = $pubDate;
    
    return $this;
    
  }
  
  
  
  
  public function CleanUp_HTML($s) {
	  
	  if (isset($s)) {
	    $s = (string)$s;
	    
	  	$s = htmlspecialchars_decode($s, ENT_NOQUOTES); // decode characters in text, exclude quotes (it's important for JSON format)
	  	$s = str_replace("&quot;", "'", $s); // convert quotes
	  	$s = str_replace("&laquo;", "«", $s); // convert quotes
	  	$s = str_replace("&raquo;", "»", $s); // convert quotes
	  	$s = str_replace("&amp;", "&", $s); // convert ampersand
	  	$s = str_replace("&copy;", "©", $s); // convert copyright symbol
	  	$s = str_replace("&trade;", "™", $s); // convert copyright symbol
	  	$s = str_replace("&nbsp;", " ", $s); // convert space
	  	$s = str_replace('\n ', "\n", $s); // remove space in the begining of the line
	  	$s = str_replace('<p>', "\n", $s); // replace start of paragrapf -> new line
	  	$s = str_replace('</p>', ' ', $s); // replace end of paragrapf -> space
	  	$s = str_replace(array('<br>','<br/>','<br />'), "\n", $s); // replace line break -> new line
	  	$s = str_replace(array('image/seemem/','alt=""','title=""'), "", $s); // replace image prefix and meta
	  	$s = preg_replace('/<img.* src="(http.*)" .*>/i', '${1}', $s, -1); // replace <img> tag -> link to the image
	  	$s = preg_replace('/  +/', ' ', $s, -1); // replace tab(s) -> space
		
	  	$s = strip_tags($s); // remove other HTML tags
	  }
  	
  	return $s;
  	
	}
	
  
  
}

?>
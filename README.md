[![Deploy](https://www.herokucdn.com/deploy/button.svg)](https://heroku.com/deploy)

# Скрипты для интеграции чат-бота [Joker](https://joker-site.herokuapp.com/) с сервисом [API.AI](https://api.ai/)
Скрипт получает URL-адрес RSS-ленты указанного сайта (если сайт не указан, то берет рандомный сайт). Адрес RSS-ленты берется из [конфига](rss/sites.ini) в блоке, соответствующем указанному названию сайта. Далее парсит RSS-ленту и выдает требуемые поля в формате HTML/JSON.
Это необходимо для [интеграции с сервисом API.AI с помощью webhook](https://docs.api.ai/docs/webhook) (в JSON-ответе используеся [минимальное кол-во полей](https://docs.api.ai/docs/webhook#section-format-of-response-from-the-service)).

## Описание структуры:
  - [index.php](index.php) - вывод результатов. Возможно использование параметров:
    - `site` - имя сайта, указанное в [конфиге](rss/sites.ini) в качестве названия блока параметров;
    - `format` - формат вывода результата (по умолчанию - HTML-вид). На данный момент возможны варианты: `html` , `json`;
  - [rss/rss.class.php](rss/rss.class.php) - самописный класс для работы с RSS-лентой;
  - [rss/sites.ini](rss/sites.ini) - конфиг-файл с описанием сайтов, с RSS-лентами которых будет работать скрипт;

## Примеры использования: 
**Получить случайную цитату с рандомного сайта из числа перечисленных в [конфиге](rss/sites.ini):**
  - вывод в HTML-формате: https://joker-integrations.herokuapp.com/?format=html или просто: https://joker-integrations.herokuapp.com/
  - вывод в JSON-формате: https://joker-integrations.herokuapp.com/?format=json

**Получить случайную цитату с Баша:**
  - вывод в HTML-формате: https://joker-integrations.herokuapp.com/?site=bash&format=html или просто: https://joker-integrations.herokuapp.com/?site=bash
  - вывод в JSON-формате: https://joker-integrations.herokuapp.com/?site=bash&format=json

<?php

include_once('rss/rss.class.php');


$n = 0;
$limit = 10;
$LimitExceeded = FALSE;
$LimitExceeded_msg = "Я попробовал уже больше $limit раз, но так и не нешал постов без видео!";

do {
	if ($n != 0) {
		# Удаляем объект в случае, если он уже был создан
		$RSS = NULL;
	}
	
	# Используем имя сайта, если в запросе есть непустой параметр "site"
	isset($_GET['site']) ? $RSS = new RSS($_GET['site']) : $RSS = new RSS();
	
	$n++;
	
	# Если лимит попыток исчерпан...
	if ($n > $limit) {
		# ... выставляем флаг
		$LimitExceeded = TRUE;
		break;
	}
} while (stripos($RSS->getRssItemText(), '📹' ) !== FALSE);


if ($_GET['format'] == 'json') {
    header('Content-type:application/json;charset=utf-8');
    
    if ($LimitExceeded) {
    	$arr = array('displayText' => $LimitExceeded_msg);
    }
    else {
    	$button_meme_text = "Еще мем";
		$button_help_text = "Показать справку";
		
	    
	    $meme_plain_text = 
			"С сайта «".$RSS->getSiteTitle()."»... \n\n".
			$RSS->getRssItemTitle().": \n".
			$RSS->CleanUp_HTML($RSS->getRssItemText());
							
		$meme_telegram_text = 
			"С сайта «".$RSS->getSiteTitle()."»... \n\n".
			"*".$RSS->getRssItemTitle()."*: \n".
			$RSS->CleanUp_HTML($RSS->getRssItemText());
			
		$meme_slack_text = 
			"С сайта «<".$RSS->getSiteLink()."|".$RSS->getSiteTitle().">»... \n\n".
			"<".$RSS->getRssItemLink()."|".$RSS->getRssItemTitle().">: \n".
			$RSS->CleanUp_HTML($RSS->getRssItemText());
	    
	    $arr = array(
			// ответ на запрос
			'speech' => $meme_plain_text, 
			
			// текстовый ответ - на случай если его нет в расширенном ответе
			'displayText' => $meme_plain_text, 
			
			// расширенный ответ...
			'data' => array (
				
				// для Telegram
				'telegram' => array (
					'parse_mode' => 'Markdown',
					'disable_web_page_preview' => 'no',
					'text' => $meme_telegram_text,
					'reply_markup' => array (
						'keyboard' => array (
							array (
								array (
									'text' => $button_meme_text,
								),
							),
							/*
							array (
								array (
									'text' => $button_help_text,
								),
							),
							*/
						),
						'resize_keyboard' => true,
						'one_time_keyboard' => true,
					),
				),
				
				// для Slack
				'slack' => array (
					'text' => $meme_slack_text,
					'attachments' => array (
						array (
							'title' => 'Понравилось? Жми:',
							//'text' => 'какой-то текст',
							'callback_id' => 'quick_buttons',
							'color' => '#3AA3E3',
							'actions' => array (
								array (
									'name' => 'memes',
									'text' => $button_meme_text,
									'type' => 'button',
									'style' => 'primary',
									'value' => 'memes',
								),
								/*
								array (
									'name' => 'help',
									'text' => $button_help_text,
									'type' => 'button',
									'value' => 'help',
								),
								*/
							),
						),
					),
				),
			),
			
			//'contextOut' => "", 
			'source' => $RSS->getSiteRssUrl()
		);
    }
	
	print_r(json_encode($arr));
}
else {
    header('Content-type:text/html;charset=utf-8');
    
    if ($LimitExceeded) {
    	echo "<h2>Ошибка: $LimitExceeded_msg</h2>";
    } else {
    	echo "<center><h1>Preview page</h1></center><br/>";
		echo "<h2>Site info</h2>";
	    echo "<p><b>Title:</b> " . $RSS->getSiteTitle() . "</p>";
	    echo "<p><b>URL:</b> " . $RSS->getSiteLink() . "</p>";
	    echo "<p><b>RSS link:</b> " . $RSS->getSiteRssUrl() . "</p>";
	    echo "<br />";
	    echo "<h2>Item info</h2>";
	    echo "<p><b>Title:</b> " . $RSS->getRssItemTitle() . "</p>";
	    echo "<p><b>Link:</b> " . $RSS->getRssItemLink() . "</p>";
	    echo "<p><b>Date:</b> " . $RSS->getRssItemDate()->format('Y.m.d H:i:s') . "</p>";
	    echo "<p><b>Content:</b> <br />" . $RSS->getRssItemText() . "</p>";
    }
}


?>
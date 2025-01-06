<?php
return [
    'web' => [
        
    ],
    'api' => [
        'api/telegram/generate-text' => ['controller' => 'TelegramController', 'method' => 'generateText'],
	    'api/telegram/handle-webhook' => ['controller' => 'TelegramController', 'method' => 'handleWebhook'],
    ],
];
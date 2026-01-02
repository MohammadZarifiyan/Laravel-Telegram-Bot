# Introduction
This package helps you to easily use the Telegram Bot API in your Laravel project and utilize its features to build a great Telegram bot.

Please read [The Telegram API documentation](https://core.telegram.org/bots/api) to gain dipper understanding about how to work with this package.

# Installation
To install the package in your project, run the following command in your project root folder:
```shell
composer require mohammad-zarifiyan/laravel-telegram-bot:^11.0
```

# Basic configuration
If you would like to publish the configuration file, run the following command (optional):
```shell
php artisan vendor:publish --provider="MohammadZarifiyan\Telegram\Providers\InstantServiceProvider" --tag="telegram-config"
```

## Configure API Key
To use Telegram bots, you must have an API key. Obtain your API key via [@BotFather](https://t.me/BotFather). Then, set your bot's API key. By default, you should add the following code to the `config/services.php` file:
```php
<?php

return [
    // The rest of your code

    'telegram' => [
        'api-key' => env('TELEGRAM_API_KEY'),
    ],
];
```
Then add `TELEGRAM_API_KEY` to your `.env` file.

### Custom repository
If you want to obtain the API Key through another way, such as a database, you can create your own repository instead of the above method. Simple create a class and implement `MohammadZarifiyan\Telegram\Interfaces\ApiKeyRepository` in it. Then, in the `telegram.php` configuration file, set the value of `api-key-repository` to the address of your class.
#### Example
`app/Repositories/TelegramApiKeyRepository.php` file:
```php
<?php

namespace App\Repositories;

use \MohammadZarifiyan\Telegram\Interfaces\ApiKeyRepository;

class TelegramApiKeyRepository implements ApiKeyRepository
{
    public function get(): ?string
    {
        return '123456:abcdefg';// Return API Key
    }
}
```
The `telegram.php` configuration file:
```php
<?php

return [
    // The rest of the file

    'api-key-repository' => \App\Repositories\TelegramApiKeyRepository::class,// Set your own custom repository

    // The rest of the file
];
```

## Configure Endpoint
You can use an arbitrary endpoint to send HTTP requests to it instead of the default endpoint of Telegram bots (api.telegram.org).
By default, you should add the following code to the `config/services.php` file.
```php
<?php

return [
    // The rest of your code

    'telegram' => [
        'endpoint' => env('TELEGRAM_ENDPOINT', 'https://api.telegram.org'),
    ],
];
```
Then add `TELEGRAM_ENDPOINT` to your `.env` file.

### Custom repository
If you want to get the endpoint through another way, such as a database, you can create a repository for yourself instead of the above method. Just create a class and implement `MohammadZarifiyan\Telegram\Interfaces\EndpointRepository` in it. Then, in the `telegram.php` configuration file, set the value of `endpoint-repository` to the address of your class.
#### Example
`app/Repositories/TelegramEndpointRepository.php` file:
```php
<?php

namespace App\Repositories;

use \MohammadZarifiyan\Telegram\Interfaces\EndpointRepository;

class TelegramEndpointRepository implements EndpointRepository
{
    public function get(): ?string
    {
        return 'https://example.com';// Return your own custom endpoint
    }
}
```
The `telegram.php` configuration file:
```php
<?php

return [
    // The rest of the file

    'endpoint-repository' => \App\Repositories\TelegramEndpointRepository::class,// Set your own custom repository

    // The rest of the file
];
```

## Verify TLS Certificate of the endpoint
You can also set your endpoint's tls certificate to be verified when you send a request to it. For this you can set `TELEGRAM_VERIFY_ENDPOINT` in your `.env`. It is recommended that the `TELEGRAM_VERIFY_ENDPOINT` value is always `true`.

The `telegram.php` configuration file:
```php
<?php

return [
    // The rest of the file
    
    'verify-endpoint' => (bool) env('TELEGRAM_VERIFY_ENDPOINT', true),
    
    // The rest of the file
];
```

## Retry request
You can set the number of retries and the delay between each retry if the HTTP connection is lost.

The `telegram.php` configuration file:
```php
<?php

return [
    // The rest of the file
    
    'retry' => [
        'times' => 5,// How many times to retry requests in case of connection errors
        'sleep' => 200,// How long to sleep between retries in milliseconds
    ],
    
    // The rest of the file
];
```

## Configure Proxy
To use a proxy while sending requests, you need to add the following code to the `config/services.php` file by default.
```php
<?php

return [
    // The rest of your code

    'telegram' => [
        'proxies' => explode(',', env('TELEGRAM_PROXIES', '')),
    ],
];
```
Then add `TELEGRAM_PROXIES` to your `.env` file.

You need to separate your list of proxies in the `.env` file with a comma (`,`). Look at the following example:
```dotenv
TELEGRAM_PROXIES=http://username:password@127.0.0.1:8080,http://username:password@127.0.0.1:8081
```

### Custom repository
If you want to get the proxies through another way, such as a database, you can create a repository for yourself instead of the above method. Just create a class and implement `MohammadZarifiyan\Telegram\Interfaces\ProxyRepository` in it. Then, in the `telegram.php` configuration file, set the value of `proxy-repository` to the address of your class.
### Example
`app/Repositories/TelegramProxyRepository.php` file:

```php
<?php

namespace App\Repositories;

use Illuminate\Support\Collection;
use MohammadZarifiyan\Telegram\Interfaces\Proxy;
use MohammadZarifiyan\Telegram\Interfaces\ProxyRepository;
use App\Models\TelegramProxy;

class TelegramProxyRepository implements ProxyRepository
{
    public function get(): Collection
    {
        return TelegramProxy::active()// Only get active proxies
            ->orderByDesc('score')// Sort by score
            ->get()
            ->toBase()
            ->map([$this, 'mapToProxy']);// Must return a collection of Proxy objects
    }
    
    public function mapToProxy(TelegramProxy $telegramProxy): Proxy
    {
        return new class ($telegramProxy) implements Proxy {
            public function __construct(public TelegramProxy $telegramProxy)
            {
                //
            }

            public function getKey(): string
            {
                return (string) $this->telegramProxy->getKey();
            }

            public function getConfiguration(): string
            {
                return $this->telegramProxy->schema'://'.$this->telegramProxy->username.':'.$this->telegramProxy->password.'@'.$this->telegramProxy->hostname.':'.$this->telegramProxy->port;
            }
        };
    }
}
```
The `telegram.php` configuration file:
```php
<?php

return [
    // The rest of the file

    'proxy-repository' => \App\Repositories\TelegramProxyRepository::class,// Set your own custom repository

    // The rest of the file
];
```
### Events
When a request is sent to the Telegram API via a proxy, the `MohammadZarifiyan\Telegram\Events\ProxyUsed` event is dispatched.

When sending request failed due to a proxy, the `MohammadZarifiyan\Telegram\Events\ProxyFailed` event is dispatched.

# Submit request to Telegram
Use the `perform` method to send a request to the Telegram API. The first parameter is the method and the second parameter is the data you want to send to the Telegram API.

**Note: See available Telegram methods at [this link](https://core.telegram.org/bots/api#available-methods)**

## Example
In the following example, _Hello world_ is sent.
```php
use \MohammadZarifiyan\Telegram\Facades\Telegram;

Telegram::perform('sendMessage', [
    'text' => 'Hello world!',
    'chat_id' => 1234
]);
```

## Concurrent Requests
Sometimes, you may wish to make multiple HTTP requests concurrently. In other words, you want several requests to be dispatched at the same time instead of issuing the requests sequentially.

Thankfully, you may accomplish this using the `concurrent` method. The `concurrent` method accepts a closure which receives an `MohammadZarifiyan\Telegram\Interfaces\PendingRequestStack` instance, allowing you to easily add requests to the request pool for dispatching.

### Example
In the example below, three messages are sent to the user simultaneously.
```php
use \MohammadZarifiyan\Telegram\Interfaces\PendingRequestStack;
use \MohammadZarifiyan\Telegram\Facades\Telegram;
 
$responses = Telegram::concurrent(fn (PendingRequestStack $pendingRequestStack) => [
    $pendingRequestStack->add()
        ->setMethod('sendMessage')
        ->setData([
            'text' => 'Message 1',
            'chat_id' => 1234
        ]),
    $pendingRequestStack->add()
        ->setMethod('sendMessage')
        ->setData([
            'text' => 'Message 2',
            'chat_id' => 1234
        ]),
    $pendingRequestStack->add()
        ->setMethod('sendMessage')
        ->setData([
            'text' => 'Message 3',
            'chat_id' => 1234
        ]),
]);

$result = $responses[0]->json('ok')
    && $responses[1]->json('ok')
    && $responses[2]->json('ok');

if ($result) {
    // All messages have been sent successfully.
}
else {
    // There was a problem sending some messages.
}
```

As you can see, each response instance is accessible based on the order in which it was added to the response collection. If you want, you can name the requests by giving the `add` method a name, which will allow you to access the corresponding responses by name.

### Example
```php
use \MohammadZarifiyan\Telegram\Interfaces\PendingRequestStack;
use \MohammadZarifiyan\Telegram\Facades\Telegram;
 
$responses = Telegram::concurrent(fn (PendingRequestStack $pendingRequestStack) => [
    $pendingRequestStack->add('first_message')
        ->setMethod('sendMessage')
        ->setData([
            'text' => 'Message 1',
            'chat_id' => 1234
        ]),
    $pendingRequestStack->add('second_message')
        ->setMethod('sendMessage')
        ->setData([
            'text' => 'Message 2',
            'chat_id' => 1234
        ]),
    $pendingRequestStack->add('third_message')
        ->setMethod('sendMessage')
        ->setData([
            'text' => 'Message 3',
            'chat_id' => 1234
        ]),
]);

$result = $responses['first_message']->json('ok')
    && $responses['second_message']->json('ok')
    && $responses['third_message']->json('ok');

if ($result) {
    // All messages have been sent successfully.
}
else {
    // There was a problem sending some messages.
}
```

The `add` method returns an instance of `MohammadZarifiyan\Telegram\Interfaces\PendingRequestBuilder`, which provides a variety of methods that may be used to modify the request:

```php
use MohammadZarifiyan\Telegram\Interfaces\PendingRequestBuilder;

$pendingRequestBuilder->setMethod(string $method): PendingRequestBuilder;
$pendingRequestBuilder->setData(array $data = []): PendingRequestBuilder;
$pendingRequestBuilder->setReplyMarkup(ReplyMarkup|string|null $replyMarkup = null): PendingRequestBuilder;
$pendingRequestBuilder->setApiKey(?string $apikey = null): PendingRequestBuilder;
$pendingRequestBuilder->setEndpoint(?string $endpoint = null): PendingRequestBuilder;
```

# Send notification by Telegram bot
To send a notification via Telegram, define the `routeNotificationForTelegram` method on your notifiable model. This method should return an instance of `MohammadZarifiyan\Telegram\TelegramRequestOptions`, indicating how the notification should be delivered.

```php
use Illuminate\Notifications\Notification;
use MohammadZarifiyan\Telegram\TelegramRequestOptions;

public function routeNotificationForTelegram(Notification $notification): null|string|int|TelegramRequestOptions
{
    // return an instance of TelegramRequestOptions
}
```
Return the `telegram` channel from your notification's `via` method.
```php
public function via($notifiable): array
{
    return ['telegram'];
}
```
Finally, add `toTelegram` to your notification class and use `MohammadZarifiyan\Telegram\TelegramRequestContent` to specify your Telegram notification data.
```php
public function toTelegram($notifiable)
{
    // Return an instance of \MohammadZarifiyan\Telegram\TelegramRequestContent
}
```
## Example
In the example below, _Hello_ is sent to all users.
```php
use App\Notifications\HelloNotification;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

$users = User::all();

Notification::send($users, HelloNotification::class);
```
The `User.php` file:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use MohammadZarifiyan\Telegram\TelegramRequestOptions;

class User extends Model
{
    use Notifiable;

    protected $fillable = ['telegram_id'];

    protected $casts = [
        'telegram_id' => 'integer'
    ];

    public function routeNotificationForTelegram(Notification $notification): null|string|int|TelegramRequestOptions
    {
        return TelegramRequestOptions::fresh()
            ->setRecipient($this->telegram_id)
            ->setApiKey('1234:abcd')// Optional: You can not call this method if you return an API key via the API Key Repository.
    }
}
```
The `HelloNotification.php` file:
```php
<?php

namespace App\Notifications;

use \Illuminate\Notifications\Notification;
use \MohammadZarifiyan\Telegram\TelegramRequestContent;

class PaymentPaidNotification extends Notification
{
    public function via($notifiable): array
    {
        return ['telegram'];
    }

    public function toTelegram($notifiable)
    {
        $routeNotification = $notifiable->routeNotificationFor('telegram');

        return TelegramRequestContent::fresh()
            ->setMethod('sendMessage')
            ->setData([
                'text' => 'Hello',
                'chat_id' => $routeNotification->recipient
            ]);
    }
}
```

# Reply Markup
If you want to add a reusable reply markup to your request payload, simply create a **ReplyMarkup** class.
To create a ReplyMarkup class run the following command:
```shell
php artisan make:telegram-reply-markup <ReplyMarkupName>
```
## Example
```shell
php artisan make:telegram-reply-markup MyKeyboard
```
The `app\Telegram\ReplyMarkups\MyKeyboard.php` file:
```php
<?php

namespace App\Telegram\ReplyMarkups;

use \MohammadZarifiyan\Telegram\Interfaces\ReplyMarkup;

class MyKeyboard implements ReplyMarkup
{
    public function __invoke(): array
    {
        return [
            'resize_keyboard' => true,
            'keyboard' => [
                [
                    // This array contains a row
                    
                    [
                        // This array contains a column
                        'text' => 'top left button'
                    ],
                    
                    [
                        // This array contains a column
                        'text' => 'top right button'
                    ]
                ],
                [
                    // This array contains a row
                    
                    [
                        // This array contains a column
                        'text' => 'bottom left button'
                    ],
                    
                    [
                        // This array contains a column
                        'text' => 'bottom right button'
                    ]
                ]
            ]
        ];
    }
}
```
Here is the final code:
```php
use \App\Telegram\ReplyMarkups\MyKeyboard;
use \MohammadZarifiyan\Telegram\Facades\Telegram;

Telegram::perform(
    'sendMessage',
    [
        'text' => 'Hello world!',
        'chat_id' => 1234
    ],
    MyKeyboard::class
);
```
You can even use your `ReplyMarkup` class inside notifications using `setReplyMarkup` method.
```php
<?php

namespace App\Notifications;

use \App\Telegram\ReplyMarkups\MyKeyboard;
use \Illuminate\Notifications\Notification;
use \MohammadZarifiyan\Telegram\TelegramRequestContent;

class PaymentPaidNotification extends Notification
{
    public function via($notifiable): array
    {
        return ['telegram'];
    }

    public function toTelegram($notifiable)
    {
        $routeNotification = $notifiable->routeNotificationFor('telegram');

        return TelegramRequestContent::fresh()
            ->setMethod('sendMessage')
            ->setData([
                'text' => 'Hello',
                'chat_id' => $routeNotification->recipient
            ])
            ->setReplyMarkup(MyKeyboard::class); 
    }
}
```

# Attachment
Use the `MohammadZarifiyan\Telegram\Attachment` class to attach a file stored on the server to the request.

## Example
In the example below, The photo stored on the server will be sent in the chat.
```php
use MohammadZarifiyan\Telegram\Facades\Telegram;
use MohammadZarifiyan\Telegram\Attachment;

$fileContents = file_get_contents('path/to/file.png');
$fileName = 'my-file.png';

Telegram::perform('sendPhoto', [
    'photo' => new Attachment($fileContents, $fileName),
    'chat_id' => 1234
]);
```

# Manipulating requests
Sometimes, you may want to manipulate request before sending executing it.

First, create a class and implement `\MohammadZarifiyan\Telegram\Interfaces\PendingRequest`. Then, retrieve `\MohammadZarifiyan\Telegram\Interfaces\PendingRequest` in its constructor.

Next, in the `telegram.php` configuration file, set the value of `pending-request-manipulator` to the address of your class.

You can then manipulate the request received in the constructor as needed.

The `telegram.php` configuration file:
```php
<?php

return [
    // The rest of the file
    
    'pending-request-manipulator' => App\Telegram\PendingRequestManipulator::class,
    
    // The rest of the file
];
```
The `PendingRequestManipulator.php` file:
```php
<?php

namespace App\Telegram;

use MohammadZarifiyan\Telegram\Interfaces\PendingRequest as PendingRequestInterface;

class PendingRequestManipulator implements PendingRequestInterface
{
    public function __construct(public PendingRequestInterface $pendingRequest)
    {
        //
    }
    
    public function getUrl(): string
    {
        return $this->pendingRequest->getUrl();// You can change the URL of the request here.
    }
    
    public function getBody(): array
    {
        return $this->pendingRequest->getBody();// You can change the body of the request here.
    }
    
    public function getAttachments(): array
    {
        return $this->pendingRequest->getAttachments();// You can change the attachments of the request here.
    }
}
```

# Generate file url
Use the `generateFileUrl` method to create a link for a file located on Telegram's servers.

## Example
```php
use MohammadZarifiyan\Telegram\Facades\Telegram;

$response = Telegram::perform('getFile', [
    'file_id' => 'abcdefg'// Your file id
]);

if ($response->json('ok')) {
    $filePath = $response->json('result.file_path');
    $fileUrl = Telegram::generateFileUrl($filePath);
    // You can use $fileUrl to download the file.
}
else {
    $error = $response->json('description');
    // There was an error receiving file information. You can use $error to display the error.
}
```

# Parsing API Key
You can parse any Telegram API Key.

Use `Telegram::parseApikey($apiKey)` to get the `botId` and `botTokenHash` from any Telegram API Key. It returns a `MohammadZarifiyan\Telegram\TelegramBotApiKey` instance containing both values. Example:
```php
use MohammadZarifiyan\Telegram\Facades\Telegram;

$apiKey = '123456:abcdefg';
$parsedApiKey Telegram::getBotId($apiKey);

echo $parsedApiKey->botId;// Output: 123456
echo $parsedApiKey->botTokenHash;// Output: abcdefg
```

# Get bot ID
Use the `getBotId` method to get the ID of the bot whose API Key you set.
## Example
The `.env` file:
```
TELEGRAM_API_KEY=123456:abcdefg
```
```php
use MohammadZarifiyan\Telegram\Facades\Telegram;

echo Telegram::getBotId();// Output: 123456
```

# Validate Telegram Login Widget (Authorization) data
Pass the data received from the Login Widget to the `validateAuthorizationData` method. If the data is valid, it will return `true`; otherwise, it will return `false`.
## Example
The `routes/web.php` file:
```php
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use \MohammadZarifiyan\Telegram\Facades\Telegram;

Route::get('telegram/login', function (Request $request) {
    $isValid = Telegram::validateAuthorizationData($request->all());
    
    if ($isValid) {
        $user = User::firstOrCreate([
            'telegram_id' => $request->query('id')
        ]);
        
        Auth::login($user);

        return 'Success: You are logged into your account!';
    }
    else {
        return 'Error: Telegram authorization data is not valid!';
    }
});
```
The `app\Models\User.php` file:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $fillable = ['telegram_id'];

    protected $casts = [
        'telegram_id' => 'integer',
    ];
}
```
# Validate WebApp InitData
There are two ways to verify WebApp data.

## Validating data for First-Party Use
If you want to validate the WebApp initData for the bot whose API you have configured, pass the `initData` to the `validateWebAppInitData` method. This method validates the initData using the configured bot token.

It returns `true` if the data is valid, and `false` otherwise.

## Validating data for Third-Party Use
If you want to validate the WebApp initData of another bot, or a bot whose API key you do not have, use the `Telegram::validateWebAppSignature`. This method validates the data without requiring an API key, using only the Telegram bot ID and Telegram’s public key hex.

Note that the public key HEX must be set in your `config/services.php` file.

You can find the public key HEX at this [link](https://core.telegram.org/bots/webapps#validating-data-for-third-party-use).

The `config/services.php` file:
```php
<?php

return [
    // The rest of your code

    'telegram' => [
        'public-key-hex' => env('TELEGRAM_PUBLIC_KEY_HEX'),
    ],
];
```
Then add `TELEGRAM_PUBLIC_KEY_HEX` to your `.env` file.

# Configure Secret Token
It is strongly recommend to set a secret token for your bot to make sure that the updates are sent by Telegram webhook.

By default, you should add the following code to the `config/services.php` file.
```php
<?php

return [
    // The rest of your code

    'telegram' => [
        'secret-token' => env('TELEGRAM_SECRET_TOKEN'),
    ],
];
```
Then add `TELEGRAM_SECRET_TOKEN` to your `.env` file.

Note: Only characters A-Z, a-z, 0-9, _ and - are allowed.

Note: After changing the secret token, you must set your bot's webhook again.

## Custom repository
If you want to get the secret token through another way, such as a database, you can create a repository for yourself instead of the above method. Just create a class and implement `MohammadZarifiyan\Telegram\Interfaces\SecretTokenRepository` in it. Then, in the `telegram.php` configuration file, set the value of `secret-token-repository` to the address of your class.
### Example
`app/Repositories/TelegramSecretTokenRepository.php` file:

```php
<?php

namespace App\Repositories;

use MohammadZarifiyan\Telegram\Interfaces\SecretTokenRepository;

class TelegramSecretTokenRepository implements SecretTokenRepository
{
    public function get(): ?string
    {
        return 'abcdefg';// Return your secret token
    }
}
```
The `telegram.php` configuration file:
```php
<?php

return [
    // The rest of the file

    'secret-token-repository' => \App\Repositories\TelegramSecretTokenRepository::class,// Set your own custom repository

    // The rest of the file
];
```

# Handling updates
Handling an update can happen in different steps depending on your needs. This method makes you able to implement all kinds of capabilities you need without considering the obstacles.

## Start handling
After creating a route, handles request using the `handleRequest` method. You can also store requests in database and handle them later.

### Example
The `api.php` file:
```php
<?php

use MohammadZarifiyan\Telegram\Facades\Telegram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('telegram-update', function (Request $request) {
   Telegram::handleRequest($request);
});
```

## Middlewares
If you want to run some codes to **modify the processing update** or **prevent processing it**, you can use a _Telegram middleware_.

Telegram middlewares run before Command Handler and Breakers, If a Middleware returns anything other than an instance of `MohammadZarifiyan\Telegram\Update`, an instance of `MohammadZarifiyan\Telegram\TelegramMiddlewareFailedException` will be thrown and processing of the Telegram update will stop.

The method that is called in the Telegram middleware is based on the type of Telegram update that is being processed.

For example, if the update type is `callback_query`, the method called will be `handleCallbackQuery`.
If there is no specific method for handling the Telegram update based on its type, a method named `handle` is called in the Telegram middleware.

Both `handle` and `handleCallbackQuery` will receive an instance of `MohammadZarifiyan\Telegram\Update` as the first argument.

Note that `handleCallbackQuery` is used as an example, and the method name will vary with different Telegram updates based on the update type.

To generate a new Telegram middleware, run the following command.
```bash
php artisan make:telegram-middleware <MiddlewareName>
```

You should add your Telegram middleware in `telegram.php` configuration file.
```php
<?php

return [
    // The rest of the file

    'middlewares' => [
        App\Telegram\Middlewares\YourMiddleware::class,
    ],
    
    // The rest of the file
];
```

Telegram middlewares will be executed in the same order as you place them in the `telegram.php` configuration file.

## Command handler
The commands that users send to your bot must be handled by a _Command Handler_.

The Telegram Command handler will run after all Telegram middlewares have run successfully.

If no Command handler is found for the command the user submitted, an instance of `MohammadZarifiyan\Telegram\TelegramCommandNotFoundException` is thrown.

You can disable throwing exceptions by setting `allow-incognito-command` to `true` in the `telegram.php` configuration file.

Additionally, you can check with `$update->isCommand()` whether the Telegram update was created due to a bot command or not.

To generate a new Command handler, run the following command.
```bash
php artisan make:telegram-command-handler <CommandHandlerName>
```

You should add your Telegram Command handler to the `telegram.php` configuration file.
```php
<?php

return [
    // The rest of the file

    'command_handlers' => [
        App\Telegram\CommandHandlers\YourCommandHandler::class,
    ],
    
    // The rest of the file
];
```

A command handler must implement the `MohammadZarifiyan\Telegram\Interfaces\CommandHandler` interface and will look like this:
```php
<?php

namespace App\Telegram\CommandHandlers;

use MohammadZarifiyan\Telegram\Enums\Signal;
use MohammadZarifiyan\Telegram\Interfaces\CommandHandler;
use MohammadZarifiyan\Telegram\Update;

class StartCommandHandler implements CommandHandler
{
    /**
     * The signature(s) of the Telegram bot command that can be handled by current CommandHandler.
     *
     * @param Update $update
     * @return string|array
     */
    public function getSignature(Update $update): string|array
    {
        return 'start';
    }
    
    /**
     * Handles the Telegram command.
     *
     * @param Update $update
     * @return Signal
     */
    public function handle(Update $update): Signal
    {
        if (true) {// update can be handled
            // Handle command here

            return Signal::Exit;
        }
        else {
            return Signal::Continue;
        }
    }
}
```

The `getSignature` method must return the name of the command or commands that the `handle` method can handle.

Sometimes a Telegram update comes with a command parameter.

You can access Telegram bot command data by calling the `toCommand` method. Then, you will receive an instance of `MohammadZarifiyan\Telegram\Interfaces\Command`.

For example, if your Telegram bot username is `MyAwesomeBot`, then you can add a parameter to your `/start` command:
`https://t.me/MyAwesomeBot?start=abc`

```php
$command = $update->toCommand();

$command->getSignature(); // Returns 'start'
$command->getValue(); // Returns 'abc'
```

## Anonymous command handler
Sometimes you may want to create a command handler without specifying the signature. This feature is mostly used for commands that are case-insensitive or commands with dynamic signature. For this purpose, you can create a class and implement `MohammadZarifiyan\Telegram\Interfaces\AnonymousCommandHandler` in it. Then add the address of your class to `command_handlers` in the `telegram.php` configuration file.

In anonymous command handlers, there is a `matchesSignature` method, in which you should check the command match.
```php
<?php

namespace App\Telegram\CommandHandlers;

use MohammadZarifiyan\Telegram\Enums\Signal;
use MohammadZarifiyan\Telegram\Interfaces\AnonymousCommandHandler;
use MohammadZarifiyan\Telegram\Update;

class MyAnonymousCommandHandler implements AnonymousCommandHandler
{
    /**
     * Checks whether the current CommandHandler can process the command.
     *
     * @param Update $update
     * @return bool
     */
    public function matchesSignature(Update $update): bool
    {
        $signature = $update->toCommand()->getSignature();
        
        return $signature === 'start';
    }
    
    /**
     * Handles the Telegram command.
     *
     * @param Update $update
     * @return Signal
     */
    public function handle(Update $update): Signal
    {
        if (true) {// update can be handled
            // Handle command here

            return Signal::Exit;
        }
        else {
            return Signal::Continue;
        }
    }
}
```

## Breaker
If the Update is not handled by a `CommandHandler`, it continues its path and reaches the Breakers. A _Breaker_ is a class that can handle the request based on its type, but unlike Telegram Middlewares, it cannot modify the update.

The method that is called when a Breaker is executed is exactly like a Telegram middleware.

Every Breaker should return `MohammadZarifiyan\Telegram\Enums\Signal`.

If a Breaker returns `Signal::Stop`, update processing stops; If the breaker returns `Signal::Continue`, the next breakers will be executed.

**Note that having too many Telegram Middlewares and Breakers can reduce the performance of your Telegram bot.**

To generate a new Breaker, run the following command.
```bash
php artisan make:telegram-breaker <BreakerName>
```

You should add your Telegram Breaker to the `telegram.php` configuration file.
```php
<?php

return [
    // The rest of the file

    'breakers' => [
        App\Telegram\Breakers\YourBreaker::class,
    ],
    
    // The rest of the file
];
```
### Example
In the example below, we handle all callback query updates related to an inline button to cancel sending notifications to all users.

```php
<?php

namespace App\Telegram\Breakers;

use MohammadZarifiyan\Enums\Signal;
use MohammadZarifiyan\Telegram\Update;

class CancelBulkNotificationBreaker
{
   public function handleCallbackQuery(Update $update): Signal
   {
      if ($update->input('callback_query.data') === 'cancel-sending-all-notifications') {
         /*
          * Cancel sending notifications
          * 
          * Stop processing request by other Telegram breakers,
          * Because we already handled the Update and the is no need to continue processing this Update in our application
          */
         return Signal::Stop;
      }
      else {
         /*
          * Continue processing the Telegram Update using other Telegram Breakers or Telegram Stages,
          * Because this Telegram breaker could was not able to handle the Telegram update
          */
         return Signal::Continue;
      }
   }
}
```

## Gainer
You may want to access something like `Illuminate\Support\Facades\Request::user()` in your application to interact with the database regarding the current update. This functionality is provided for you in this package. Simply create a class called `GainerResolver` in `app/Telegram` and implement the `MohammadZarifiyan\Telegram\Interfaces\GainerResolver` interface in it. Then set the name of the class you created in the `telegram.php` configuration file.

The `telegram.php` configuration file:
```php
<?php

return [
    // The rest of the file

    'gainer-resolver' => \App\Telegram\GainerResolver::class,// Set your own class name

    // The rest of the file
];
```
By using `$update->gainer()`, you can access the value returned by the `handle` method in `GainerResolver.php` throughout your application.
### Example
The `app/Models/User.php` file:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $fillable = ['telegram_id'];

    protected $casts = [
        'telegram_id' => 'integer',
    ];
}
```
The `app/Telegram/GainerResolver.php` file:
```php
<?php

use App\Models\User;
use MohammadZarifiyan\Telegram\Update;
use MohammadZarifiyan\Telegram\Interfaces\GainerResolver as GainerResolverInterface;

class GainerResolver implements GainerResolverInterface
{
    public function handle(Update $update)
    {
        if ($update->type() === 'message') {
            return User::firstOrCreate([
                'telegram_id' => $update->input('message.from.id')
            ]);
        }
        
        return null;
    }
}
```
```php
use MohammadZarifiyan\Telegram\Facades\Telegram;
use App\Models\User;

$update = Telegram::getUpdate();
$gainer = $update->gainer();

if ($gainer instanceof User) {
    echo 'The user became a member of the bot on date ' . $gainer->created_at;
}
else {
    echo 'The $gainer value is null.';
}
```

## Stage
Some bots have an interactive mode where they allow users to access new features upon receiving a new Telegram update. For example, when a user clicks a button on the keyboard, a new set of buttons is displayed, which the user can interact with only after clicking the initial button. This feature is commonly seen in bots that guide users through stages of information gathering. To implement this, you need to save a _stage_ that checks the user's next update, so that when the next update arrives, the corresponding stage code executes. Fortunately, this package simplifies this process significantly. The _Stage_ class has methods similar to those in _Middleware_ and _Breaker_ classes. These methods handle bot updates; for instance, the `handleMessage` method manages updates triggered by sending messages in the bot's chat. To utilize this feature, define a model for your _Gainer_, add a stage `column` to your database table, and store the fully qualified class name that should handle the next update in the `stage` column. Finally, implement the `MohammadZarifiyan\Telegram\Interfaces\Gainer` interface in your _Gainer_ model.

To create a stage, run the following command:
```shell
php artisan make:telegram-stage <StageName>
```
The above command creates a file in the path `app/Telegram/Stages`.

### Validation and authorization of updates
You can validate updates in the update handler methods within the stage. Update validation works similar to `FormRequest` in Laravel. To validate updates, you must create a class that extends `MohammadZarifiyan\Telegram\FormUpdate` and use it as the first parameter in the update handler method within the stage class. If validation fails, an exception of type `MohammadZarifiyan\Telegram\Exceptions\TelegramValidationException` is thrown. You can catch `TelegramValidationException` in the `app/Exceptions/Handler.php` file and send validation errors to the user.

You can also use the `authorize` method. If the output of this method is `false`, an exception of type `MohammadZarifiyan\Telegram\Exceptions\TelegramAuthorizationException` will be thrown. You can catch `TelegramAuthorizationException` in the `app/Exceptions/Handler.php` file and send an authorization error to the user.

Run the following command to create a new update:
```shell
php artisan make:telegram-update <UpdateName>
```
This command creates a file at the path `app/Telegram/Updates`.

### Example
In the following example, the bot asks the user for their age, expecting a number between 1 and 100. If the user sends anything other than this number, the bot will respond with a validation error message.

Users migration:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('telegram_id');
            $table->string('stage')->nullable();
            $table->unsignedTinyInteger('age')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
```
The `app\Models\User.php` file:
```php
<?php

namespace App\Models;

use MohammadZarifiyan\Telegram\Interfaces\HasStage;
use Illuminate\Database\Eloquent\Model;

class User extends Model implements HasStage
{
    protected $fillable = [
        'telegram_id',
        'age',
        'stage',
    ];

    protected $casts = [
        'telegram_id' => 'integer',
        'age' => 'integer',
    ];

    public function getStage(): null|string|object
    {
        return $this->stage;
    }
}
```
`App\Telegram\Stages\Age.php` file:
```php
<?php

namespace App\Telegram\Stages;

use App\Telegram\Updates\AgeUpdate;
use MohammadZarifiyan\Telegram\Facades\Telegram;

class Age
{
    public function handleMessage(AgeUpdate $update): void
    {
        $update->gainer()->update([
            'stage' => null,
            'age' => $update->integer('message.text')
        ]);
        
        Telegram::perform('sendMessage', [
            'text' => 'Your age has been saved in the database.',
            'chat_id' => $update->integer('message.chat.id'),
        ]);
    }
}
```
The `app\Telegram\Updates\AgeUpdate.php` file:
```php
<?php

namespace App\Telegram\Updates;

use MohammadZarifiyan\Telegram\FormUpdate;

class AgeUpdate extends FormUpdate
{
    public function rules(): array
    {
        return [
            'message.text' => ['bail', 'required', 'integer', 'between:1,200']
        ];
    }

    public function attributes(): array
    {
        return [
            'message.text' => 'age'
        ];
    }
}
```
```php
use MohammadZarifiyan\Telegram\Facades\Telegram;
use App\Telegram\Stages\Age;

$gainer = Telegram::getUpdate()->gainer();
$gainer->update(['stage' => Age::class]);

Telegram::perform('sendMessage', [
    'chat_id' => $gainer->telegram_id,
    'text' => 'How old are you?'
]);
```
## Update flow chart
By viewing the chart below, you will better understand the update processing.

![Handling process](https://user-images.githubusercontent.com/55022827/210347380-722855a5-d681-43aa-a057-c3be6c49cca4.png)

# Creating Random Telegram Bot API Keys
Using the `telegramBotApiKey()` method, you can easily generate a random **Telegram Bot API Key**. For example:

```php
$faker = \Faker\Factory::create();
$apiKey = $faker->telegramBotApiKey();
```

This method returns an instance of the `MohammadZarifiyan\Telegram\TelegramBotApiKey` class, containing both the `botId` and `botTokenHash`.

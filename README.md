# Introduction
This package helps you to easily use Telegram bot API in your Laravel project and use Laravel features to build great Telegram bot.

Please read [Telegram API documentation](https://core.telegram.org/bots/api) to get dipper understanding about how to work with this package.

# Installation
To install package in your project run following command in your project root folder.
```shell
composer require mohammad-zarifiyan/laravel-telegram-bot:^6.1
```

# Basic configuration
If you would like to publish configuration file run the following command. (Optional)
```shell
php artisan vendor:publish --provider="MohammadZarifiyan\Telegram\Providers\TelegramServiceProvider" --tag="telegram-config"
```

## Configure API Key
To use Telegram bots, you must have an API Key. Get your API Key via [@BotFather](https://t.me/BotFather). Then you need to set your bot's API Key.
By default, you should add the following code to the `config/services.php` file.
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
If you want to get the API Key through another way, such as a database, you can create a repository for yourself instead of the above method. Just create a class and implement `MohammadZarifiyan\Telegram\Interfaces\ApiKeyRepository` in it. Then, in the `telegram.php` configuration file, set the value of `api-key-repository` equal to the address of your class.
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
        'endpoint' => env('TELEGRAM_ENDPOINT'),
    ],
];
```
Then add `TELEGRAM_ENDPOINT` to your `.env` file.

### Custom repository
If you want to get the endpoint through another way, such as a database, you can create a repository for yourself instead of the above method. Just create a class and implement `MohammadZarifiyan\Telegram\Interfaces\EndpointRepository` in it. Then, in the `telegram.php` configuration file, set the value of `endpoint-repository` equal to the address of your class.
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

# Submit request to Telegram
Use the `perform` method to send a request to the Telegram API. The first parameter is the method and the second parameter is the payload you want to send to the Telegram API.

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
    $pendingRequestStack->add('sendMessage', [
        'text' => 'Message 1',
        'chat_id' => 1234
    ]),
    $pendingRequestStack->add('sendMessage', [
        'text' => 'Message 2',
        'chat_id' => 1234
    ]),
    $pendingRequestStack->add('sendMessage', [
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

# Send notification by Telegram bot
To send a notification by Telegram add `routeNotificationForTelegram` method to your notifiable model, then you have to return the Telegram `chat_id` of the notifiable.
```php
public function routeNotificationForTelegram($notification)
{
    // return telegram id
}
```
Return `telegram` channel from your notification `via` method.
```php
public function via($notifiable): array
{
    return ['telegram'];
}
```
Finally add `toTelegram` to your notification class and use `MohammadZarifiyan\Telegram\TelegramRequestContent` to specify your Telegram notification information.
```php
public function toTelegram($notifiable): \MohammadZarifiyan\Telegram\Interfaces\TelegramRequestContent
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

class User extends Model
{
    use Notifiable;

    protected $fillable = ['telegram_id'];

    protected $casts = [
        'telegram_id' => 'integer'
    ];

    public function routeNotificationForTelegram($notification)
    {
        return $this->telegram_id;
    }
}
```
The `HelloNotification.php` file:
```php
<?php

namespace App\Notifications;

use \Illuminate\Notifications\Notification;
use \MohammadZarifiyan\Telegram\Interfaces\TelegramRequestContent as TelegramRequestContentInterface;
use \MohammadZarifiyan\Telegram\TelegramRequestContent;

class PaymentPaidNotification extends Notification
{
    public function via($notifiable): array
    {
        return ['telegram'];
    }

    public function toTelegram($notifiable): TelegramRequestContentInterface
    {
        return TelegramRequestContent::fresh()
            ->setMethod('sendMessage')
            ->setData([
                'text' => 'Hello'
            ]);
    }
}
```

# Reply Markup
If you want to add a reusable reply markup to your request payload, you need to create a **ReplyMarkup** class.
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
The final code:
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
use MohammadZarifiyan\Telegram\Interfaces\TelegramRequestContent as TelegramRequestContentInterface;
use \MohammadZarifiyan\Telegram\TelegramRequestContent;

class PaymentPaidNotification extends Notification
{
    public function via($notifiable): array
    {
        return ['telegram'];
    }

    public function toTelegram($notifiable): TelegramRequestContentInterface
    {
        return TelegramRequestContent::fresh()
            ->setMethod('sendMessage')
            ->setData([
                'text' => 'Hello'
            ])
            ->setReplyMarkup(MyKeyboard::class); 
    }
}
```

# Attachment
Use the `MohammadZarifiyan\Telegram\Attachment` class to attach a file stored on the server to request.

## Example
In the example below, The photo stored on the server will be sent in the chat.
```php
use MohammadZarifiyan\Telegram\Facades\Telegram;
use MohammadZarifiyan\Telegram\Attachment;

$file_contents = file_get_contents('path/to/file.png');
$file_name = 'my-file.png';

Telegram::perform('sendPhoto', [
    'photo' => new Attachment($file_contents, $file_name),
    'chat_id' => 1234
]);
```

# Manipulating requests
Sometimes you may want to manipulate information before sending a request to the Telegram API.

First of all create a class and implement `App\Interfaces\PendingRequest`. Then get `App\Interfaces\PendingRequest` in its constructor.

Then, in the `telegram.php` configuration file, set the value of `pending-request-manipulator` equal to the address of your class.

You can do whatever you want with the data received in the constructor.

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
        return $this->pendingRequest->getUrl();// You can change the URL of request here.
    }
    
    public function getBody(): array
    {
        return $this->pendingRequest->getBody();// You can change the body of request here.
    }
    
    public function getAttachments(): array
    {
        return $this->pendingRequest->getAttachments();// You can change the attachments of request here.
    }
}
```

# Generate file url
Use the `generateFileUrl` method to create a link for a file located on Telegram servers.

## Example
```php
use MohammadZarifiyan\Telegram\Facades\Telegram;

$response = Telegram::perform('getFile', [
    'file_id' => 'abcdefg'// Your file id
]);

if ($response->json('ok')) {
    $file_path = $response->json('result.file_path');
    $file_url = Telegram::generateFileUrl($file_path);
    // You can use $file_url to download the file.
}
else {
    $error = $response->json('description');
    // There was an error receiving file information. You can use $error to display the error.
}
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
# Webhook setup
If you would like to receive Telegram updates you have to create a route named telegram-update, Then run the following command.
```shell
php artisan telegram:set-webhook
```
You can use your custom route name instead of `telegram-update`, but you have to update `update-route` in the `telegram.php` configuration file.

**Note: Your update route URL must start with `https://` and you have to install a valid SSL/TSL certificate on your domain.**

## Configure Secure Token
It is strongly recommend to set a secure token for your bot to make sure that the updates are sent by Telegram.

By default, you should add the following code to the `config/services.php` file.
```php
<?php

return [
    // The rest of your code

    'telegram' => [
        'secure-token' => env('TELEGRAM_SECURE_TOKEN'),
    ],
];
```
Then add `TELEGRAM_SECURE_TOKEN` to your `.env` file.

Note: Only characters A-Z, a-z, 0-9, _ and - are allowed.

Note: After changing the secure token, you must set your bot's webhook again.

### Custom repository
If you want to get the secure token through another way, such as a database, you can create a repository for yourself instead of the above method. Just create a class and implement `MohammadZarifiyan\Telegram\Interfaces\SecureTokenRepository` in it. Then, in the `telegram.php` configuration file, set the value of `secure-token-repository` equal to the address of your class.
#### Example
`app/Repositories/TelegramSecureTokenRepository.php` file:
```php
<?php

namespace App\Repositories;

use MohammadZarifiyan\Telegram\Interfaces\SecureTokenRepository;

class TelegramSecureTokenRepository implements SecureTokenRepository
{
    public function get(): ?string
    {
        return 'abcdefg';// Return your secure token
    }
}
```
The `telegram.php` configuration file:
```php
<?php

return [
    // The rest of the file

    'secure-token-repository' => \App\Repositories\TelegramSecureTokenRepository::class,// Set your own custom repository

    // The rest of the file
];
```

# Handling updates
Handling an update can happen in different steps depending on your needs. This method makes you able to implement all kinds of capabilities you need without considering the obstacles.

## Start handling
After making an update route, Handles request using `handleRequest` method.
You can also store requests in database and handle them later.

### Example
The `api.php` file:
```php
<?php

use MohammadZarifiyan\Telegram\Facades\Telegram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('telegram-update', function (Request $request) {
   Telegram::handleRequest($request);
})
   ->name(
      config('telegram.update-route')
   );
```

## Middlewares
If you want to run some codes to **modify the processing update** or **prevent processing it**, you can use a _Telegram middleware_.

Telegram middlewares run before Command Handler and Breakers, If a Middleware returns anything other than an instance of `MohammadZarifiyan\Telegram\Update`, Then an instance of `MohammadZarifiyan\Telegram\TelegramMiddlewareFailedException` will be thrown and processing of the Telegram update will stop.

The method that is called in the Telegram middleware is based on the type of Telegram update that is being processed.

For example, if the update type is `callback_query`, the method called will be `handleCallbackQuery`.
If there is no specific method based on the Telegram update type related to handling the Telegram update, a method named `handle` is called in the Telegram middleware.
Also, if there is no `handle` method in the middleware, the Telegram middleware will not be executed.

Both `handle` and `handleCallbackQuery` will receive an instance of `MohammadZarifiyan\Telegram\Update` as the first argument.

Note that `handleCallbackQuery` is used as an example and the method name will be vary in different Telegram updates based on update type.

To generate a fresh Telegram middleware run the following command.
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

Telegram middlewares will be executed in the same order as you put in `telegram.php` configuration file.

## Command handler
The commands that users send to your bot must be handled by a _Command Handler_.

Telegram Command handler will be run after running successfully all Telegram middlewares.

If no Command handler is found for the command the user submitted, an instance of `MohammadZarifiyan\Telegram\TelegramCommandNotFoundException` is thrown.

You can disable of throwing exception by changing `allow-incognito-command` to `true` in `telegram.php` configuration file.

Also you can check with the `$update->isCommand()` whether the Telegram update was created because of a bot command or not.

To generate a fresh Command handler run the following command.
```bash
php artisan make:telegram-command-handler <CommandHandlerName>
```

You should add your Telegram Command handler in `telegram.php` configuration file.
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

A command handler must implement `MohammadZarifiyan\Telegram\Interfaces\CommandHandler` interface and will look like this:
```php
<?php

namespace App\Telegram\CommandHandlers;

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
     */
    public function handle(Update $update)
    {
        // Handle command here
    }
}
```

The `getSignature` method must return the name of the command or commands that the `handle` method can handle.

Sometimes Telegram update comes with command parameter.

You can access to Telegram bot command data by calling `toCommand` method, Then you will receive an instance of `MohammadZarifiyan\Telegram\Interfaces\Command`.

For example if your Telegram bot username is `MyAwesomeBot` then you can add parameter to your `/start` command:
`https://t.me/MyAwesomeBot?start=abc`

```php
$command = $update->toCommand();

$command->getSignature(); // Returns 'start'
$command->getValue(); // Returns 'abc'
```

## Breaker
If the Update is not handled by a `CommandHandler`, the update continues its path and reaches the _Breakers_. Breaker is a class that can handle the request based on its type, But a Breaker unlike Telegram Middlewares cannot modify the update.

The method that is called when Breaker is executed is exactly like Telegram middleware.

A Breaker should extend `MohammadZarifiyan\Telegram\Breaker`.

If a Breaker returns `true`, update processing stops. Otherwise, the next breakers will be executed. You can also stop processing Update by `stop` method in your Telegram breaker. If you do not want to stop processing the Update through next Telegram Breakers or stage, You should return `false` or anything other than `true`. You can also call `continue` method in your Telegram Breaker for the same result.

**Note that too many Telegram Middlewares and Breakers can reduce the performance of your Telegram bot.**

To generate a fresh Breaker run the following command.
```bash
php artisan make:telegram-breaker <BreakerName>
```

You should add your Telegram Breaker in `telegram.php` configuration file.
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

In the example below, we handle all the callback query updates related to an inline button to cancel sending notifications to all users.

```php
<?php

namespace App\Telegram\Breakers;

use MohammadZarifiyan\Telegram\Breaker;
use MohammadZarifiyan\Telegram\Update;

class CancelBulkNotificationBreaker extends Breaker
{
   public function handleCallbackQuery(Update $update): bool
   {
      if ($update->input('callback_query.data') === 'cancel-sending-all-notifications') {
         // Cancel sending notifications

         /*
          * Stop processing request by other Telegram breakers,
          * Because we already handled the Update and the is no need to continue processing this Update in our application
          */
         return $this->stop();
      }
      else {
         /*
          * Continue processing the Telegram Update using other Telegram Breakers or Telegram Stages,
          * Because this Telegram breaker could was not able to handle the Telegram update
          */
         return $this->continue();
      }
   }
}
```

## Gainer
You may want to access something like `Illuminate\Support\Facades\Request::user()` in your application to communicate with the database about the current update. Such a thing is provided for you in this package. Just create a class called `GainerResolver` in `app/Telegram` and implement `MohammadZarifiyan\Telegram\Interfaces\GainerResolver` in it. Then set the name of the class you created in the `telegram.php` configuration file.
The `telegram.php` configuration file:
```php
<?php

return [
    // The rest of the file

    'gainer-resolver' => \App\Telegram\GainerResolver::class,// Set your own class name

    // The rest of the file
];
```
By using the `$update->gainer()`, you can access the value returned in `GainerResolver.php` handle method throughout your application.
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
Some robots have an interactive mode, that is, by receiving the new Telegram update, they allow the user to use new features. For example, when the user clicks on a button from the keyboard, a set of new buttons will be displayed to the user, and the user can use the new set of buttons only when he has already clicked on the mentioned button. This feature is often seen in robots that receive information from the user in stages. To implement such a thing, you need to save a _stage_ that should check the user's next update so that when you receive the next update, the code of that stage will be executed. Fortunately, this is greatly simplified by this package, `Stage` is a class that has methods similar to _Middleware_ and _Breaker_ methods. These methods are responsible for handling the robot updates. For example, `handleMessage` method checks the updates that are created due to sending messages in the chat where the bot is present. To use this feature, just define a modal for your `Gainer` and add a column named `stage` to your table in the database, then save the name of the class that should handle the next update along with its namespace in the `stage` column in your database.
Then implement `MohammadZarifiyan\Telegram\Interfaces\Gainer` in your `Gainer` model.

To create a stage, you must run the following command:
```shell
php artisan make:telegram-stage <StageName>
```
The above command creates a file in the path `app/Telegram/Stages`.
### Validation and authorization of updates
You can validate the updates in the update handler methods in the stage. Update validation works like `FormRequest` in Laravel. To validate the updates, you must create a class that inherits `MohammadZarifiyan\Telegram\FormUpdate` and use it as the first parameter in the update handler method in the stage class. If validation fails, an exception of type `MohammadZarifiyan\Telegram\Exceptions\TelegramValidationException` is thrown.
You can catch `TelegramValidationException` in `app/Exceptions/Handler.php` file and send validation error to user.

You can also use the authorize method. If the output of this method is false, an exception of `MohammadZarifiyan\Telegram\Exceptions\TelegramAuthorizationException` type will be thrown.
You can catch `TelegramAuthorizationException` in `app/Exceptions/Handler.php` file and send authorization error to user.

Run the following command to create a new update:
```shell
php artisan make:telegram-update <UpdateName>
```
This command creates a file in the path `app/Telegram/Updates`.

### Example
In the following example, the age of the user is asked, the user can send a number between 1 and 100 to the bot. If the user sends anything other than this number to the robot, the robot will send the user a validation error message.

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

use MohammadZarifiyan\Telegram\Interfaces\Gainer as GainerInterface;
use MohammadZarifiyan\Telegram\Traits\Gainer;
use Illuminate\Database\Eloquent\Model;

class User extends Model implements GainerInterface
{
    use Gainer;
    
    protected $fillable = [
        'telegram_id',
        'age',
    ];

    protected $casts = [
        'telegram_id' => 'integer',
        'age' => 'integer',
    ];
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
By viewing the chart below, you will better understand the update processing process.

![Handling process](https://user-images.githubusercontent.com/55022827/210347380-722855a5-d681-43aa-a057-c3be6c49cca4.png)

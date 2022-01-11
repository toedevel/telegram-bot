<?php

namespace App\Conversations;
//подключили переговорщика телеграм
use BotMan\BotMan\Messages\Conversations\Conversation;
use App\Models\client;
use App\Models\ask;

/* директивы телеграм */
use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Incoming\Answer as BotManAnswer;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\OutgoingMessage;
use BotMan\BotMan\Messages\Outgoing\Question as BotManQuestion;

class mainConversation extends conversation
{

   public $response = [];
//стартовая функция, место старта любого телеграм-бота
   public function run () {
     //передаём управление в функцию, предполагая, что клиент новый
       $this->askAboutRateNeed();
   }
//функция спрашивает клиента, нужен ли ему курс доллара
//(а вдруг он попал по шибке?), и если да - передаёт управление
//функции для выдачи курса. Если нет - сразу переходит к
//завершению программы
   private function askAboutRateNeed() {
          //создание нового вопроса
          $question = BotManQuestion::create("Вам нужен курс доллара?");
          //кнопки, дабы не вводить юзера в ступор и не генерировать ошибки
          //его неаккуратными действиями
          $question->addButtons( [
            Button::create('Да')->value(1),
            Button::create('Нет')->value(2)
          ]);
          //непосредственное выполнение вопроса
          $this->ask($question, function (BotManAnswer $answer) {
          //проверка, попал ли клиент случайно или нет
          if ($this->$answer=1){
            //если клиенту нужен курс - передача управления в функцию его выдачи
            $this->sendRate();
          }
          //если нет - переход в функцию завершения
          $this->exit();
        });
       }

       //функция для отправления курса доллара. Здесь для простоты предполагается
       //наличие внешнего источника, из которой данная переменная будет подгружаться.
       //Сейчас в функции переменная определена для тестирования, в реальном проекте
       //будет выполнен запрос к нужному модулю, хранящему переменную.
    private function sendRate(){
          //объявление гипотетического курса в целях тестрирования. Значение
          //специально явно не соответствует дествительности, чтобы при вводе
          //в эксплуатацию вызвать вопросы раработчика, если вдруг эта часть
          //не будет именена (хотя должна быть)
          $dollarRate = 16;
          //создание сообщения бота
          $message = OutgoingMessage::create('Курс доллара : '+ $dollarRate + ' рублей за 1 доллар.');
          //отправка сообщения
          $this->bot->reply($message);
          //переход к функции сохранения данных о запросе и (если надо) пользователе
          $this->saveAsk();
    }
    //функция сохраняет в базу данных информацию о запросе и клиенте.
    //Если клиент не найден, то он будет добавлен в таблицу клиентов
    private function saveAsk(){
        //получение id клиента
        $id_client = $this->bot->getUser()->getId();
        //создание новой записи в таблицу ask
        $ask = new Ask;
        //фиксирование текущего курса
        $ask->current_rate = $dollarRate;
        //проверяем, есть ли клиент с таким айди, если нет - создаём
        $client = clients::firstOrCreate(["id_chat"=>$id_client]);
        //новый чат соединим через айди клиента с основной базой,
        //теперь мы уверены, что он точно существует
        $ask->client_id = $client->id;
        //записали результат в табличку
        $ask->save();
        $this->exit();

    }
    //функция для завершения программы. Вынесена отдельно, чтобы, во-первых,
    //не дублировать завершающий код, во-вторых, не забыть закрыть программу
    //и в-третьих, на случай, если после выполнения кода понадобится передать
    //управление другому боту, тогда код передачи можно будет один раз ввести в этой функции
    private function exit(){

      return true;

    }

}

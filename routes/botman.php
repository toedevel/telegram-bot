$botman->hears('/start', function ( $bot ) { $bot->startConversation ( new mainConversation ); } );
use App\Conversations\mainConversation;

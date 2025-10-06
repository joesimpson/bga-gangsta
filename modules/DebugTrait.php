<?php
namespace Bga\Games\gansgta;

use Bga\GameFramework\Actions\Types\IntParam;

/**
 * Debugging functions to be called in chat/debug window in BGA Studio
 */
trait DebugTrait
{
 
  ////////////////////////////////////////////////////

  function debug_ReSetup(bool $draft = true){
    $this->trace("debug_ReSetup - START ////////////////////////////////////////////////////");
    $players = self::loadPlayersBasicInfos();
    
    $options = ["DEBUG_SETUP"=> true,
      100 => 2,
      101 => 2,
      102 => 2,
      103 => ($draft ? 2 :1) ,
    ];
    //CLEAR DATAS
    // self::DbQuery("UPDATE `stats` set stats_value = 0 where stats_type >= 10 ");
    self::DbQuery("DELETE FROM `stats` where stats_type >= 10 ");
    self::DbQuery("DELETE FROM `card`");
    //self::DbQuery("UPDATE `global` set global_value = 0 where global_id >= 10 AND global_id < 100 ");
    self::DbQuery("DELETE FROM `global` where global_id >= 10 AND global_id < 100 or global_id = 103 ");
    self::DbQuery("INSERT INTO `global` (global_id,global_value) VALUES ( 103,".$options[103]." )"); //NOT ENOUGH because of cache

    self::DbQuery("DELETE FROM `player`");

    self::DbQuery("DELETE FROM `gamelog` where gamelog_packet_id > 1");

    $this->setupNewGame($players,$options);

    $players = self::loadPlayersBasicInfos(); 
    if($draft) $this->gamestate->jumpToState(30);//resourcesSetup
    else $this->gamestate->jumpToState(4);//playerAction
    $this->notify->all('reloadPage', "/!\ : Refresh page to see game has restarted...", []);
    $this->trace("debug_ReSetup - END ////////////////////////////////////////////////////");

  }

  
  function debug_KillByHeist(int $nbGangstersOpponent = 0, bool $notifyAll = true){
    $this->trace("debug_KillByHeist - START ////////////////////////////////////////////////////");
    $playerId = $this->getActivePlayerId();

    //Empty player hand
    $cards = $this->cards->getPlayerHand($playerId);
    foreach($cards as $card){
      if($card['type_arg'] == 1) continue;//boss
      $this->cards->moveCard($card['id'],'deckgangsters');
    }
    //Go to last phase
    self::DbQuery("UPDATE `global` set global_value = 2 where global_id = 12 ");
    //2 cards can kill : 418 & 426
    self::DbQuery("UPDATE `card` set card_location ='avheists' where card_type in (418,426) ");
    //Several gangsters are needed to attack
    self::DbQuery("UPDATE `card` set card_location ='hand', card_location_arg=$playerId, card_state=0 where card_type in (123,154, 116, 114) ");
    //Avoid score problems when assassination = loosing points
    self::DbQuery("UPDATE player SET player_score=100, public_score=100,  player_money=12");

    //Empty opponent hand:
    $opponentId = $this->getNextPlayerTable()[$playerId];
    $opponentCards = $this->cards->getPlayerHand($opponentId);
    foreach($opponentCards as $card){
      if($card['type_arg'] == 1) continue;//boss
      $this->cards->moveCard($card['id'],'deckgangsters');
    }
    $this->cards->pickCardsForLocation($nbGangstersOpponent,'deckgangsters', 'hand', $opponentId);
    
    $this->gamestate->jumpToState(4);//playerAction

    if($notifyAll){
      $this->notify->all('reloadPage', "/!\ : Refresh page to see Heist Rival Gang assassination...", []);
    }
    else {
      $this->notify->player($playerId,'reloadPage', "/!\ : Refresh page to see Heist Rival Gang assassination...", []);
    }

    $this->trace("debug_KillByHeist - END ////////////////////////////////////////////////////");
  }

  
  function debug_actSelectResource(int $cardId = 0){
    $this->actSelectResource($cardId);
  }
 
}

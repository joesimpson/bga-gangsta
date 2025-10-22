<?php
namespace Bga\Games\gansgta;

use Bga\GameFramework\Actions\Types\IntParam;
use BgaVisibleSystemException;

/**
 * Debugging functions to be called in chat/debug window in BGA Studio
 */
trait DebugTrait
{
 
  ////////////////////////////////////////////////////

  function debug_ReSetup(bool $draft = true,bool $visibleHeistScore = true){
    $this->trace("debug_ReSetup - START ////////////////////////////////////////////////////");
    $players = self::loadPlayersBasicInfos();
    
    $options = ["DEBUG_SETUP"=> true,
      100 => 2,
      101 => 2,
      102 => ($visibleHeistScore ? 2 :1) ,
      103 => ($draft ? 2 :1) ,
    ];
    //CLEAR DATAS
    // self::DbQuery("UPDATE `stats` set stats_value = 0 where stats_type >= 10 ");
    self::DbQuery("DELETE FROM `stats` where stats_type >= 10 ");
    self::DbQuery("DELETE FROM `card`");
    //self::DbQuery("UPDATE `global` set global_value = 0 where global_id >= 10 AND global_id < 100 ");
    self::DbQuery("DELETE FROM `global` where global_id >= 10 AND global_id < 100 or global_id in( 102,103) ");
    self::DbQuery("INSERT INTO `global` (global_id,global_value) VALUES ( 102,".$options[102]." )"); //NOT ENOUGH because of cache
    self::DbQuery("INSERT INTO `global` (global_id,global_value) VALUES ( 103,".$options[103]." )"); //NOT ENOUGH because of cache
    self::setGameStateValue( 'publicvariant', $options[102]);
    self::setGameStateValue( 'resourcevariant', $options[103]);

    self::DbQuery("DELETE FROM `player`");

    self::DbQuery("DELETE FROM `gamelog` where gamelog_packet_id > 1");

    $this->setupNewGame($players,$options);

    $players = self::loadPlayersBasicInfos(); 
    $this->gamestate->jumpToState(30);//resourcesSetup
    
    $this->notify->all('reloadPage', "/!\ : Refresh page to see game has restarted...", []);
    $this->trace("debug_ReSetup - END ////////////////////////////////////////////////////");

  }
  
  /**
   * Function to call to regenerate JSON from PHP 
   */
  function debug_JSON(){

    $json = json_encode($this->getStatTypes(), JSON_PRETTY_PRINT);
    //Formatting options as json -> copy the DOM of this log : \n
    $this->notify->all("jsonStats","$json",['json' => $json]);
     
  }
  ////////////////////////////////////////////////////

  
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

  //Test  all resource cards images and tooltips
  function debug_reshuffleResourcesInDraft(){
    $playerId = $this->getCurrentPlayerId();

    $state = $this->gamestate->getCurrentMainState();
    if($state->name != "resourcesSelection"){
      throw new BgaVisibleSystemException("To be run before selecting resource cards !");
    }
    
    self::DbQuery("UPDATE `card` set card_location ='rc_deck', card_location_arg =0 where card_location in ('rc_draft','rc_discard')");
    $this->cards->shuffle('rc_deck');

    $players = self::loadPlayersBasicInfos();
    /*
    $n = $this->cards->countCardInLocation('rc_draft');
    while($n < 12){
      self::DbQuery("UPDATE `card` set card_location ='rc_draft', card_location_arg ='$playerId' where card_location ='rc_deck' 
      order by card_location_arg
      LIMIT 1");
      if( self::DbAffectedRow() <1 ) break;
      $playerId = $this->getPlayerAfter($playerId);
      $n = $this->cards->countCardInLocation('rc_draft');
    }
    */

    foreach ($players as $pid => $player) {
      $this->cards->pickCardsForLocation(12/count($players),'rc_deck','rc_draft',$pid);
    }
    
    //$this->notify->player($playerId,'reloadPage', "/!\ : Refresh page to see cards...", []);
    
    $this->gamestate->jumpToState(31);
  }

  function debug_actSelectResource(int $cardId = 0){
    $this->actSelectResource($cardId);
  }

  
  function debug_scoreMedia(){
    $playerId = $this->getCurrentPlayerId();
    $resource = $this->getHandResourceCard($playerId);
    if(isset($resource)){
      $resourceId = $resource['id'];
      self::DbQuery("UPDATE `card` set card_type =906, card_location ='rc_hand', card_location_arg ='$playerId', card_state = 1 where card_id = $resourceId ");
    }
    else {
      //todo
    }
    
    self::DbQuery("UPDATE player SET player_score =player_score-resource_value, public_score =public_score-resource_value, resource_value = 0, player_money=50  WHERE player_id = $playerId");

    //several scoring heists may be performed 220, 401 with a score+ 218 no score
    self::DbQuery("UPDATE `card` set card_location ='performed', card_location_arg=$playerId where card_type in (220,401,218) ");
    
    $this->notify->player($playerId, 'reloadPage', "/!\ : Refresh page to see resource card...", []);
  }
  
  function debug_teachSkill(){
    $playerId = $this->getCurrentPlayerId();
    self::DbQuery("UPDATE `card` set card_location ='hdiscard' where card_location ='avheists' ");
    //some cards can teach : 313
    self::DbQuery("UPDATE `card` set card_location ='avheists' where card_type in (313,326,301) ");
    //Several gangsters are needed to attack
    self::DbQuery("UPDATE `card` set card_location ='hand', card_location_arg=$playerId, card_state=0 where card_type in (152,111) ");
    
    //Go to phase 2
    self::DbQuery("UPDATE `global` set global_value = 1 where global_id = 12 ");

    $this->notify->all( 'reloadPage', "/!\ : Refresh page to see resource card...", []);
  }
  
  function debug_rewardRecruit(){
    $playerId = $this->getCurrentPlayerId();
    $money = 0;//we need 3 and we will earn 3 with big heist -> we must be able to buy a gangster and not notif 'cannot recruit a gangster'
    $this->globals->set('vault_money',intval($money));

    $phase = self::getGameStateValue('activePhase');
    self::DbQuery("UPDATE `card` set card_location ='hdiscard', card_location_arg=$phase where card_location ='avheists' ");
    // cards "Inmate transfer" can reward recruits : 
    self::DbQuery("UPDATE `card` set card_location ='avheists' where card_type in (207,208,209) ");
    //Several gangsters are needed to attack
    self::DbQuery("UPDATE `card` set card_location ='hand', card_location_arg=$playerId, card_state=0 where card_type in (152,111,135) ");
    
    //Go to phase 1
    self::DbQuery("UPDATE `global` set global_value = 0 where global_id = 12 ");

    //change available recruits for next step
    self::DbQuery("UPDATE `card` set card_location ='deckgangsters' where card_location ='avgangsters'");
    self::DbQuery("UPDATE `card` set card_location ='avgangsters' where card_type in (112,146,147,155,157) ");
    //RED Boss : 
    self::DbQuery("UPDATE `card` set card_location ='hand', card_location_arg=$playerId, card_state=0 where card_type in (101) ");
    self::DbQuery("UPDATE player SET player_money=$money where player_id = $playerId");

    $this->gamestate->jumpToState(4);//playerAction

    $this->notify->all( 'reloadPage', "/!\ : Refresh page to see gangsters...", []);
  }
  
  function debug_EmptyHand(){
    $playerId = $this->getCurrentPlayerId();
    
    //empty players'hand to continue playing...
    self::DbQuery("UPDATE `card` set card_location ='deckgangsters',card_state =0,card_location_arg=0 where card_location ='hand' and card_type_arg=0"); 
    $this->gamestate->jumpToState(4);//playerAction

    $this->notify->all( 'reloadPage', "/!\ : Refresh page to see gangsters...", []);
  }
  function debug_EmptyDeck(bool $gangstersDeck = true, bool $heistDeck = true){
    $playerId = $this->getCurrentPlayerId();
    
    //empty players'hand to continue playing...
    if($gangstersDeck){
      self::DbQuery("UPDATE `card` set card_location ='gdiscard',card_state =0,card_location_arg=0 where card_location ='deckgangsters'"); 
    }
    if($heistDeck){
      self::DbQuery("UPDATE `card` set card_location ='hdiscard', card_location_arg=0 where card_location in('deckgenesis')"); 
      self::DbQuery("UPDATE `card` set card_location ='hdiscard', card_location_arg=1 where card_location in('deckgangwars')"); 
      self::DbQuery("UPDATE `card` set card_location ='hdiscard', card_location_arg=2 where card_location in('deckdomination')"); 
    }
    $this->gamestate->jumpToState(3);

    $this->notify->all( 'reloadPage', "/!\ : Refresh page to see gangsters...", []);
  }
  
  function debug_inactivateResource(){
    $playerId = $this->getCurrentPlayerId();
    $resource = $this->getHandResourceCard($playerId);
    if(isset($resource)){
      $resourceId = $resource['id'];
      self::DbQuery("UPDATE `card` set card_type =906, card_location ='rc_hand', card_location_arg ='$playerId', card_state = 0 where card_id = $resourceId ");
    }
    
    self::DbQuery("UPDATE player SET player_score =player_score-resource_value, public_score =public_score-resource_value, resource_value = 0  WHERE player_id = $playerId");
    
    $this->notify->player($playerId, 'reloadPage', "/!\ : Refresh page to see resource card...", []);
  }
  
  function debug_EndOfChapter(int $chap = 1, int $addSkills = 2){
    $playerId = $this->getCurrentPlayerId();

    //set bank
    //$cardType = 909;
    //self::DbQuery("UPDATE `card` set card_location ='rc_discard', card_location_arg = 0 where card_location ='rc_hand' and card_location_arg = $playerId ");
    //self::DbQuery("UPDATE `card` set card_location ='rc_hand', card_location_arg = $playerId, card_state = 1 where card_type = $cardType ");
    
    //Go to phase 2
    $phase = $chap -1;
    self::DbQuery("UPDATE `global` set global_value = $phase where global_id = 12 ");
    self::setGameStateValue('activePhase', $phase);
    self::setGameStateValue('GDGStatus', 0);

    $countForChap = [4,7,9][$phase];

    $players = self::loadPlayersBasicInfos();
    foreach($players as $pId => $player){
      $gangsters = $this->cards->getPlayerHand($pId);
      if(count($gangsters) < $countForChap ){
        $this->cards->pickCardsForLocation($countForChap-count($gangsters) ,'deckgangsters','hand',$pId);
      } else if(count($gangsters) > $countForChap ){
        $this->cards->pickCardsForLocation(count($gangsters) - $countForChap ,'hand','deckgangsters',$pId);
      }

      //Test with added skills
      $gangsters = $this->cards->getPlayerHand($pId);
      $k = 0;
      foreach($gangsters as $gId => $gangster){
        if($k>$addSkills){
          self::dbQuery("UPDATE card SET card_skills=0 where card_id = $gId");
        }
        else {
          $skill_id = $k;
          self::dbQuery("UPDATE card SET card_skills=$skill_id where card_id = $gId");
        }
        $k++;
      }
    }

    $this->notify->all( 'reloadPage', "/!\ : Refresh page to see resource card...", []);
    $this->gamestate->jumpToState(25);//checkPhase
  }

  function debug_SnitchToDraw(int $money = 1){
    $playerId = $this->getCurrentPlayerId();
    self::DbQuery("UPDATE player SET player_money=$money where player_id = $playerId");

    //set police station
    $cardType = 901;
    self::DbQuery("UPDATE `card` set card_location ='rc_hand', card_location_arg = $playerId, card_state = 1 where card_type = $cardType ");
    
    //Go to phase 2
    self::DbQuery("UPDATE `global` set global_value = 1 where global_id = 12 ");
    $snitchesTypes = [331,332,333];
    $snitches = self::getCollectionFromDB("SELECT card_id id, card_type type  from card WHERE card_type in (". implode(',',$snitchesTypes).")");
    foreach($snitches as $snitch){
      $onTop = true;
      if($snitch['type'] == 331) $onTop = false;
      $this->cards->insertCardOnExtremePosition($snitch['id'],'deckgangwars',$onTop);
    }

    $this->gamestate->jumpToState(5);//discard card
    $this->notify->all( 'reloadPage', "/!\ : Refresh page to see resource card...", []);
  }
  
  function debug_PrivateJetVSSociety(){
    $playerId = $this->getCurrentPlayerId();
    $cardsToTest = [905,908];
    $players = self::loadPlayersBasicInfos();
    $k = 0;
    foreach($players as $pId => $player){
      $this->removeAllActionsFromEndTurn($pId); 
    }
    foreach($cardsToTest as $cardType){//Give resource cards to players
      $nextPid = array_keys($players)[$k];

      $resource = $this->getHandResourceCard($nextPid);
      if(isset($resource)){
        $resourceId = $resource['id'];
        self::DbQuery("UPDATE `card` set card_location ='rc_discard', card_location_arg =0, card_state = 0 where card_id = $resourceId ");
      }
      
      self::DbQuery("UPDATE `card` set card_location ='rc_hand', card_location_arg =$nextPid, card_state = 1 where card_type = $cardType ");
      
      //Several gangsters are needed to attack
      if($k % 2==0) $gangstersToAdd = [124,125,152,111];
      else $gangstersToAdd = [120,121,122,123];
      self::DbQuery("UPDATE `card` set card_location ='hand', card_location_arg=$nextPid, card_state=0 where card_type in (". implode(',',$gangstersToAdd).") ");

      $k++;
    }

    //some heists can COOP : 303
    self::DbQuery("UPDATE `card` set card_location ='avheists' where card_type in (303,305) ");
    
    //Go to phase 2
    self::DbQuery("UPDATE `global` set global_value = 1 where global_id = 12 ");

    $this->gamestate->jumpToState(2);
    $this->notify->all( 'reloadPage', "/!\ : Refresh page to see resource card...", []);
  }
  
  function debug_endTurnActions(){
    $player_id = $this->getCurrentPlayerId();
    
    $players = self::loadPlayersBasicInfos();
    //$this->globals->set(GLOBAL_END_TURN_ACTIONS, []);
    foreach($players as $pId => $player){
      $this->removeAllActionsFromEndTurn($pId);
      $this->addActionToEndTurn($pId,'freeUntapGangster');
      $this->addActionToEndTurn($pId,'freeUntapLeader');

      $this->addActionToEndTurn($pId,'TEST_ABSENT');
      $this->removeActionFromEndTurn($pId,'TEST_ABSENT');
    }
    
    $endTurnActions = $this->globals->get(GLOBAL_END_TURN_ACTIONS, []);
    $this->notify->player($player_id, 'json', "endTurnActions :".json_encode($endTurnActions), ['json'=>$endTurnActions]);
    
    //Mobilize gang
    self::DbQuery("UPDATE `card` set card_state=1 where card_location ='hand' AND card_location_arg='$player_id' ");

    $this->globals->set(GLOBAL_END_TURN_ACTIONS_DONE,false);
    $this->gamestate->jumpToState(25);//checkPhase
    $this->notify->all('reloadPage', "/!\ : Refresh page to see gangsters card...", []);
  }

  //Test PRODUCTION BUG "Unexpected Error - BIGINT UNSIGNED value is out of range"  when conceding -> fixed
  function debug_scoreOnConcede(){
    $player_id = $this->getCurrentPlayerId();
    
    $sql = "UPDATE player SET player_score = 0 WHERE player_id = $player_id";
    self::DbQuery($sql);
    $sql = "UPDATE player SET player_score = 1 WHERE player_id != $player_id";
    self::DbQuery($sql);
    
    $this->notify->player($player_id,'reloadPage', "/!\ : Refresh page to see score...", []);
  }
 
  function debug_jumpToState(int $jumpState = 25){
    $this->gamestate->jumpToState($jumpState);
  }
  
  /**
   * Test what happens if a zombie was there at current state
   */
  function debug_Zombie(){
    $playerId = $this->getCurrentPlayerId();
    $state = $this->gamestate->state();
    $this->zombieTurn($state,$playerId);
  }

  /* Test BGA shuffle
  */
  function debug_shuffleDeck(){
    $bossCardsOrder = [];
    $players = self::loadPlayersBasicInfos();

    //Clean setup of boss and restart setup of bosses
    $this->DbQuery("UPDATE `card` set card_location = 'deckboss' where card_type_arg = 1 and card_location = 'hand'");
    $this->cards->shuffle("deckboss");

    foreach ($players as $player_id => $player) {
      $bosscard = $this->cards->pickCard('deckboss', $player_id);
      $bossCardsOrder[] = $bosscard['type'];
      //$this->cards->moveCard($bosscard['id'],'deckboss');
    }
    $this->notify->all("debug_shuffleDeck","bossCardsOrder=".json_encode($bossCardsOrder));

    /* 21/10/2025 2players : normal results after clicking 15 times 
      bossCardsOrder=["103","101"]
      bossCardsOrder=["103","102"]
      bossCardsOrder=["104","102"]
      bossCardsOrder=["105","104"]
      bossCardsOrder=["103","105"]
      bossCardsOrder=["104","102"]
      bossCardsOrder=["104","101"]
      bossCardsOrder=["104","105"]
      bossCardsOrder=["101","104"]
      bossCardsOrder=["104","105"]
      bossCardsOrder=["102","103"]
      bossCardsOrder=["102","105"]
      bossCardsOrder=["102","101"]
      bossCardsOrder=["101","105"]
      bossCardsOrder=["104","103"]
    */
  }
}

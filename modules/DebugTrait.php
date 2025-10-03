<?php
namespace Bga\Games\gansgta;

/**
 * Debugging functions to be called in chat/debug window in BGA Studio
 */
trait DebugTrait
{
 
  ////////////////////////////////////////////////////

  function debug_ReSetup(){
    $this->trace("debug_ReSetup - START ////////////////////////////////////////////////////");
    $players = self::loadPlayersBasicInfos();
    
    $options = ["DEBUG_SETUP"=> true,
      100 => 2,
      101 => 2,
      102 => 2,
      103 => 1,
    ];
    //CLEAR DATAS
    // self::DbQuery("UPDATE `stats` set stats_value = 0 where stats_type >= 10 ");
    self::DbQuery("DELETE FROM `stats` where stats_type >= 10 ");
    self::DbQuery("DELETE FROM `card`");
    //self::DbQuery("UPDATE `global` set global_value = 0 where global_id >= 10 AND global_id < 100 ");
    self::DbQuery("DELETE FROM `global` where global_id >= 10 AND global_id < 100 ");
    self::DbQuery("DELETE FROM `player`");

    $this->setupNewGame($players,$options);

    $players = self::loadPlayersBasicInfos(); 
    $this->gamestate->jumpToState(4);//playerAction
    self::notifyAllPlayers('debug_ReSetup', "/!\ : Refresh page to see game has restarted...", []);
    $this->trace("debug_ReSetup - END ////////////////////////////////////////////////////");

  }
 
}

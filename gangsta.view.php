<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Gangsta implementation : © Benoit Ragoen <benoit.ragoen@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * gangsta.view.php
 *
 * This is your "view" file.
 *
 * The method "build_page" below is called each time the game interface is displayed to a player, ie:
 * _ when the game starts
 * _ when a player refreshes the game page (F5)
 *
 * "build_page" method allows you to dynamically modify the HTML generated for the game interface. In
 * particular, you can set here the values of variables elements defined in gangsta_gangsta.tpl (elements
 * like {MY_VARIABLE_ELEMENT}), and insert HTML block elements (also defined in your HTML template file)
 *
 * Note: if the HTML of your game interface is always the same, you don't have to place anything here.
 *
 */

  require_once( APP_BASE_PATH."view/common/game.view.php" );

  class view_gangsta_gangsta extends game_view
  {
    function getGameName() {
        return "gangsta";
    }
  	function build_page( $viewArgs )
  	{
  	    // Get players & players number
        $players = $this->game->loadPlayersBasicInfos();
        $players_nbr = count( $players );

        /*********** Place your code below:  ************/
        $this->tpl['SNITCHES_TITLE'] = self::_('Snitches');
        $this->tpl['SNITCHES_VICTORY_CONDITION'] = '3 '.self::_('snitches').': '.self::_('end of game');

        $this->page->begin_block( "gangsta_gangsta", "player" );

        $current_player_id = $this->getCurrentPlayerId();
        $this->tpl['CURRENT_PLAYER_ID'] = $current_player_id;

        if( isset( $players[ $current_player_id ] ) )
        {
            // Place player tableau first
            $player = $players[ $current_player_id ];

            $this->page->insert_block( "player", array( "PLAYER_ID" => $player['player_id'],
                                                        "PLAYER_CATEGORY" => "current_player",
                                                        "PLAYER_NAME" => $player['player_name'],
                                                        "PLAYER_COLOR" => $player['player_color'] ) );
        }

        $counter = 0;
        if($this->game->isSpectator()){
            //count from first player 
            $player_id = array_keys($players)[0];
        }
        else {
            //count from second player 
            $player_id = $current_player_id;
            $player_id = $this->game->getPlayerAfter($player_id);
        }
        while($counter < count($players)) {
        //foreach( $players as $player_id => $player )
            $counter++;
            $player = [ 'player_id' => $player_id, 'player_name'=>$this->game->getPlayerNameById($player_id), 'player_color'=>$this->game->getPlayerColorById($player_id), ];
            if( $player_id != $current_player_id )
            {
                $this->page->insert_block( "player", array( "PLAYER_ID" => $player['player_id'],
                                                            "PLAYER_CATEGORY" => "opposing_player",
                                                            "PLAYER_NAME" => $player['player_name'],
                                                            "PLAYER_COLOR" => $player['player_color'] ) );
            }
            $player_id = $this->game->getPlayerAfter($player_id);
        }
        /*********** Do not change anything below this line  ************/
  	}
  }



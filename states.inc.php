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
 * states.inc.php
 *
 * Gangsta game states description
 *
 */

/*
   Game state machine is a tool used to facilitate game developpement by doing common stuff that can be set up
   in a very easy way from this configuration file.

   Please check the BGA Studio presentation about game state to understand this, and associated documentation.

   Summary:

   States types:
   _ activeplayer: in this type of state, we expect some action from the active player.
   _ multipleactiveplayer: in this type of state, we expect some action from multiple players (the active players)
   _ game: this is an intermediary state where we don't expect any actions from players. Your game logic must decide what is the next game state.
   _ manager: special type for initial and final state

   Arguments of game states:
   _ name: the name of the GameState, in order you can recognize it on your own code.
   _ description: the description of the current game state is always displayed in the action status bar on
                  the top of the game. Most of the time this is useless for game state with "game" type.
   _ descriptionmyturn: the description of the current game state when it's your turn.
   _ type: defines the type of game states (activeplayer / multipleactiveplayer / game / manager)
   _ action: name of the method to call when this game state become the current game state. Usually, the
             action method is prefixed by "st" (ex: "stMyGameStateName").
   _ possibleactions: array that specify possible player actions on this step. It allows you to use "checkAction"
                      method on both client side (Javacript: this.checkAction) and server side (PHP: self::checkAction).
   _ transitions: the transitions are the possible paths to go from a game state to another. You must name
                  transitions in order to use transition names in "nextState" PHP method, and use IDs to
                  specify the next game state for each transition.
   _ args: name of the method to call to retrieve arguments for this gamestate. Arguments are sent to the
           client side to be used on "onEnteringState" or to set arguments in the gamestate description.
   _ updateGameProgression: when specified, the game progression is updated (=> call to your getGameProgression
                            method).
*/

/*
    "Visual" States Diagram :

             1 SETUP
                |
                30 resourcesSetup
                |      |
                |      31 resourcesSelection
                |      |
                |      |
                v      v
    2 -> 3 -> 4 playerAction <---------------------------\
    ^                  |                                 ^
    |                  |-----------------------------    |
    |                  |           |                |    |
    |                  v           v                v    |            
    |   /------>6      5,7,9,11   10  ->12->13->14  8--->/
    |   ^       |      |           |            |   |
    |   |       v      v           v            v   v
    |   |       -------------------------------------
    |   |               |
    |   |               v
    |   |              25 checkPhase --------------->\
    |   |               |                            |
    |   |               v                            |
    |   |       /-------------------\                |
    |   |       |       |           |                |
    |   |       |       v           v                |
    |   |       |       21          27 ->22->26      |
    |   |       v       |                    |       |
    |   23 <--- 24  <------------------------/       |
    |           |                                    |
    |           v                                    |
    \<--------- 20 nextPlayer                        |
                                                     v
                        98 endOfGame  <--------------/
                        | 
                        v
                        99 END
*/

//    !! It is not a good idea to modify this file when a game is running !!

$machinestates = array(

    // The initial state. Please do not modify.
    1 => array(
        "name" => "gameSetup",
        "description" => "Game Setup",
        "type" => "manager",
        "action" => "stGameSetup",
        "transitions" => array( "" => "30" )
    ),
    
    30 => array(
        "name" => "resourcesSetup",
        'action' => 'stResourcesSetup',
        "description" => clienttranslate('Assigning resources cards to players'),
        "type" => "game",
        "transitions" => [ 
            "next" => 4,
            "draftMulti" => 31,
        ],
    ),
    31 => array(
        "name" => "resourcesSelection",
        'args' => 'argResourcesSelection',
        "description" => clienttranslate('Players must choose a resource card to play with'),
        "descriptionmyturn" => clienttranslate('${you} must choose a resource card to play with'),
        "type" => "multipleactiveplayer",
        "possibleactions" => [ 
            "actSelectResource",
        ],
        "transitions" => [ 
            "next" => 4,
        ],
    ),
    
    2 => array(
    		"name" => "checkSynchro",
    		"description" => '',
    		"type" => "game",
            "action" => "stCheckSynchro",
            "updateGameProgression" => true,
    		"transitions" => array( "mobilize" => 3, "playerAction" => 4 )
    ),

    3 => array(
        "name" => "playerMobilize",
        "description" => clienttranslate('${actplayer} is mobilizing their gangsters'),
        "descriptionmyturn" => clienttranslate('${you} must choose which gangsters to untap (${leader} free untaps)'),
        "type" => "activeplayer",
        "possibleactions" => array( "untapGangsters", "skip" ),
        "transitions" => array( "playTurn" => 4, "skip" => 4, "zombiePass"=>25 ),
        "args" => "argPlayerMobilize"
    ),

    4 => array(
        "name" => "playerAction",
        "description" => clienttranslate('${actplayer} must perform an action or pass'),
        "descriptionmyturn" => clienttranslate('${you} must recruit, perform a heist, or Pass'),
        "type" => "activeplayer",
        "possibleactions" => array( "recruitGangster", "performHeist", "pass" ),
        "transitions" => array( "discard" => 5, "checkPhase" => 25, "replay"=> 25, "zombiePass"=>25,
                                "rewardRecruit" => 7, "rewardSteal" => 8, "rewardTap" => 9, "markForKill" => 10, "rewardSkill" => 11)
    ),

    5 => array(
        "name" => "discard",
        "description" => clienttranslate('${actplayer} may discard a card from the available heists or gangsters'),
        "descriptionmyturn" => clienttranslate('${you} may discard a card from the available heists or gangsters'),
        "type" => "activeplayer",
        "possibleactions" => array( "discard", "skipDiscard" ),
        "transitions" => array( "checkPhase" => 25 )
    ),

    6 => array(
        "name" => "snitch",
        "description" => clienttranslate('Other players must deal with the snitch'),
        "descriptionmyturn" => clienttranslate('${you} need to deal with the snitch'),
        "type" => "multipleactiveplayer",
        "possibleactions" => array( "snitchKill" ),
        "transitions" => array( "checkPhase" => 25 ),
        'action' => 'stSnitchInit',
        'args' => 'argSnitchInit',
    ),

    7 => array(
        "name" => "rewardRecruit",
        "description" => clienttranslate('${actplayer} can recruit an additional gangster'),
        "descriptionmyturn" => clienttranslate('${you} may recruit an additional gangster'),
        "type" => "activeplayer",
        "possibleactions" => array( "recruitGangster", "skip" ),
        "transitions" => array( "checkPhase" => 25, "skip" => 25  ),
    ),

    8 => array(
        "name" => "rewardSteal",
        "description" => clienttranslate('${actplayer} can now steal ${amount} from anyone'),
        "descriptionmyturn" => clienttranslate('${you} may steal ${amount} from a rival gang'),
        "type" => "activeplayer",
        "possibleactions" => array( "steal", "skip" ),
        "transitions" => array( "checkPhase" => 25, "skip" => 25, "replay" => 4  ),
        "args" => "argRewardSteal"
    ),

    9 => array(
        "name" => "rewardTap",
        "description" => clienttranslate('${actplayer} may tap ${amount} rival gangsters'),
        "descriptionmyturn" => clienttranslate('${you} may tap ${amount} rival gangsters'),
        "type" => "activeplayer",
        "possibleactions" => array( "tap", "skip" ),
        "transitions" => array( "checkPhase" => 25, "skip" => 25, "zombiePass" => 25  ),
        "args" => "argRewardTap"
    ),

    10 => array(
        "name" => "markForKill",
        "description" => clienttranslate('${actplayer} is chosing a rival gang as a target'),
        "descriptionmyturn" => clienttranslate('${you} may designate a rival gang for an assassination'),
        "type" => "activeplayer",
        "possibleactions" => array( "markForKill", "skip" ),
        "transitions" => array( "markForKill" => 12, "skip" => 25, "zombiePass" => 25 ),
        "args" => "argRewardMark"
    ),

    11 => array(
        "name" => "rewardSkill",
        "description" => clienttranslate('${actplayer} is giving a skill to a gangster'),
        "descriptionmyturn" => clienttranslate('${you} may teach the skill ${skill_name} to one of your gangsters'),
        "type" => "activeplayer",
        "possibleactions" => array( "teach", "skip" ),
        "transitions" => array( "checkPhase" => 25, "skip" => 25, "zombiePass" => 25 ),
        "args" => "argRewardSkill"
    ),

    12 => array(
        "name" => "switchToKiller",
        "description" => '',
        "type" => "game",
        "action" => "stSwitchToKiller",
        "transitions" => array( "selectKiller" => 13 ),
    ),

    13 => array(
        "name" => "rewardKill",
        "description" => clienttranslate('${actplayer} is choosing a gangster to kill'),
        "descriptionmyturn" => clienttranslate('${you} must select one of your gangsters'),
        "type" => "activeplayer",
        "possibleactions" => array( "kill" ),
        "transitions" => array( "goBackToActive" => 14, "zombiePass"=>14 ),
        "args" => "argRewardKill"
    ),

    14 => array(
        "name" => "goBackToActive",
        "description" => '',
        "type" => "game",
        "action" => "stGoBackToActive",
        "transitions" => array( "checkPhase" => 25 ),
    ),

    20 => array(
        "name" => "nextPlayer",
        "description" => '',
        "type" => "game",
        "action" => "stNextPlayer",
        "updateGameProgression" => true,
        "transitions" => array( "nextPlayer" => 2) // for Debug
    ),

    21 => array(
        "name" => "startGDG",
        "description" => '',
        "type" => "game",
        "action" => "stStartGDG",
        "updateGameProgression" => true,
        "transitions" => array( "nextPlayer" => 24) // for Debug
    ),

    22 => array(
        "name" => "gdgMulti",
        "description" => clienttranslate('Other players must choose a gangster'),
        "descriptionmyturn" => clienttranslate('${you} must choose a gangster'),
        "type" => "multipleactiveplayer",
        "possibleactions" => array( "gdgKill" ),
        "transitions" => array( "startDomination" => 26 ),
        'action' => 'stGdgMulti',
        'args' => 'argGdgMulti',
    ),

    23 => array(
        "name" => "discoverSnitch",
        "description" => '',
        "type" => "game",
        "action" => "stDiscoverSnitch",
        "updateGameProgression" => true,
        "transitions" => array( "checkPhase" => 25, "snitch" => 6) // for Debug
    ),

    24 => array(
        "name" => "revealCards",
        "description" => '',
        "type" => "game",
        "action" => "stRevealCards",
        "updateGameProgression" => true,
        "transitions" => array( "endTurn" => 20, "snitch" => 23 ) // for Debug
    ),

    25 => array(
        "name" => "checkPhase",
        "description" => '',
        "type" => "game",
        "action" => "stCheckPhase",
        "updateGameProgression" => true,
        "transitions" => array( "gameEnd" => 98, "startGDG" => 21, "startDomination"=>27, "revealCards" => 24) // for Debug
    ),

    26 => array(
        "name" => "startDomination",
        "description" => '',
        "type" => "game",
        "action" => "stStartDomination",
        "updateGameProgression" => true,
        "transitions" => array( "nextPlayer" => 24) // for Debug
    ),

    27 => array(
        "name" => "computeGangWar",
        "description" => '',
        "type" => "game",
        "action" => "stComputeGDG",
        "updateGameProgression" => false,
        "transitions" => array( "GDGMulti" => 22) // for Debug
    ),

    98 => array(
        "name" => "endOfGame",
        "description" => '',
        "type" => "game",
        "action" => "stEndOfGame",
        "updateGameProgression" => true,
        "transitions" => array( "gameEnd" => 99)
    ),
    // Final state.
    // Please do not modify (and do not overload action/args methods).
    99 => array(
        "name" => "gameEnd",
        "description" => clienttranslate("The game has ended"),
        "type" => "manager",
        "action" => "stGameEnd",
        "args" => "argGameEnd"
    )

);
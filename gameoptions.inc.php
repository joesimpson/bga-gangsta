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
 * gameoptions.inc.php
 *
 * Gangsta game options description
 *
 * In this file, you can define your game options (= game variants).
 *
 * Note: If your game has no variant, you don't have to modify this file.
 *
 * Note²: All options defined in this file should have a corresponding "game state labels"
 *        with the same ID (see "initGameStateLabels" in gangsta.game.php)
 *
 * !! It is not a good idea to modify this file when a game is running !!
 *
 */

$game_options = [

    /*
    
    // note: game variant ID should start at 100 (ie: 100, 101, 102, ...). The maximum is 199.
    100 => array(
                'name' => totranslate('my game option'),    
                'values' => array(

                            // A simple value for this option:
                            1 => array( 'name' => totranslate('option 1') )

                            // A simple value for this option.
                            // If this value is chosen, the value of "tmdisplay" is displayed in the game lobby
                            2 => array( 'name' => totranslate('option 2'), 'tmdisplay' => totranslate('option 2') ),

                            // Another value, with other options:
                            //  description => this text will be displayed underneath the option when this value is selected to explain what it does
                            //  beta=true => this option is in beta version right now (there will be a warning)
                            //  alpha=true => this option is in alpha version right now (there will be a warning, and starting the game will be allowed only in training mode except for the developer)
                            //  nobeginner=true  =>  this option is not recommended for beginners
                            3 => array( 'name' => totranslate('option 3'), 'description' => totranslate('this option does X'), 'beta' => true, 'nobeginner' => true )
                        ),
                'default' => 1
            ),

    */
    100 => [
        'name' => totranslate('Clans'),
        'values' => [
            1 => ['name' => totranslate('Standard')],

            2 => ['name' => totranslate('Clans'), 'tmdisplay' => totranslate('Clans')
                  , 'description' => totranslate('$1 discount when recruiting a gangster from the same clan as your Boss. End game IP awarded per clan majority instead of most gangsters')
                  , 'nobeginner' => true],
        ],
        'default' => 1,
    ],

    101 => [
        'name' => totranslate('Money Options'),
        'values' => [
            1 => ['name' => totranslate('standard')],
            2 => [
                'name' => totranslate('Minimum 2$ when passing'),
                'tmdisplay' => totranslate('Minimum 2$ when passing'),
                'description' => totranslate('When passing the minimum amount of money you get is $2'),
            ],
            3 => [
                'name' => totranslate('Cash for not playing first'),
                'tmdisplay' => totranslate('Cash for not playing first'),
                'description' => totranslate('Extra money for 2nd, 3rd, 4th player'),
            ],
            4 => [
                'name' => totranslate('Both money adjustments'),
                'tmdisplay' => totranslate('Both money adjustments'),
                'description' => totranslate('Minimum 2$ when passing AND Cash for not playing first'),
            ],
        ],
        'default' => 2,
    ],

    102 => [
        'name' => totranslate('Heist Influence'),
        'values' => [
            1 => [
                'name' => totranslate('private'), 'description' => totranslate('Influence gained from heists is only visible to you')],
            2 => ['name' => totranslate('public'), 'tmdisplay' => 'public','description' => totranslate('Influence gained from heists is added to you public total')],
        ],
        'default' => 1,
    ],

    103 => [
        'name' => 'Resources',
        'values' => [
            1 => [
                'name' => 'Disabled', 
                'tmdisplay' => '',
                'description' => 'Resource cards are not dealt to players.'
            ],
            2 => [
                'name' => 'Resources choice', 
                'tmdisplay' => 'Resources choice',
                'description' => ('Players randomly receive two Resource Cards, selecting only one and discarding the other. Each Resource Card give a unique ability during the game.'),
                'nobeginner' => true,
                'alpha' => true,
                'beta' => true, //TODO JSA REMOVE BETA OPTION When OK
            ],
            //We may add 3 => 'Automatic choice' 
        ],
        'default' => 1,
    ],

];



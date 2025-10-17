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
 * material.inc.php
 *
 * Gangsta game material description
 *
 * Here, you can describe the material of your game with PHP variables.
 *
 * This file is loaded in your game logic class constructor, ie these variables
 * are available everywhere in your game logic code.
 *
 */

$this->gameConstants = array(
    'CARD_WIDTH' => 110,
    'CARD_HEIGHT' => 154,
    'GANGSTER_DECK_SIZE' => 35, // Float
    'GENESIS_DECK_SIZE' => 30, // Float
    'GANGWARS_DECK_SIZE' => 33, // Float
    'DOMINATION_DECK_SIZE' => 36, // Float
    'X_ORIGIN' => 25,
    'Y_ORIGIN' => 25,
    'gamePhases' => ['genesis','gangwars','domination'],
    'clanNames' => array(
        'bratva' => clienttranslate('Bratva'),
        'cartel' => clienttranslate('Cartel'),
        'gang'   => clienttranslate('Ghetto gangs'),
        'mafia'  => clienttranslate('Mafia'),
        'triad'  => clienttranslate('Triads'),  
    ),
);

$this -> Phase_Names = array(
    'genesis' => clienttranslate('Genesis'),
    'gangwars' => clienttranslate('Gang War'),
    'domination' => clienttranslate('Domination'),
);

$this -> Clan_type = array(
    1=> clienttranslate('Bratva'),
    2=> clienttranslate('Cartel'),
    3=> clienttranslate('Ghetto gangs'),
    4=> clienttranslate('Mafia'),
    5=> clienttranslate('Triads'),
);

$this -> Clan_type_name = array(
    'bratva' => 1,
    'cartel' => 2,
    'gang'   => 3,
    'mafia'  => 4,
    'triad'  => 5,
);

$this -> skill_name = array(
   0 => 'dummy',
   1 => clienttranslate('Leader'),
   2 => clienttranslate('Mercenary'),
   3 => clienttranslate('Informant'),
   4 => clienttranslate('Sniper'),
   5 => clienttranslate('Brawler'),
   6 => clienttranslate('Hacker'),
);

$this -> skill_name_invariant = array(
    0 => 'dummy',
    1 => 'leader',
    2 => 'mercenary',
    3 => 'informant',
    4 => 'sniper',
    5 => 'brawler',
    6 => 'hacker',
 );

$this -> skill_typeid = array(
    'leader'   => 1,
    'mercenary'=> 2,
    'informant'=> 3,
    'sniper'   => 4,
    'brawler'  => 5,
    'hacker'   => 6,
);

$this->heist_genesis_type= array(
    201 => array(
        'type' => 'single',
        'chapter' => 'genesis',
        'name' => clienttranslate('Armored van'),
        'reward' => array(
            'cash' => 3,
            'influence' => 0,
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 1,
            2 => 1,
            3 => 0,
            4 => 0,
            5 => 0,
            6 => 0,
        ),
    ),
    202 => array(
        'type' => 'single',
        'chapter' => 'genesis',
        'name' => clienttranslate('Armored van'),
        'reward' => array(
            'cash' => 3,
            'influence' => 1,
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 0,
            2 => 0,
            3 => 1,
            4 => 1,
            5 => 0,
            6 => 0,
        ),
    ),
    203 => array(
        'type' => 'single',
        'chapter' => 'genesis',
        'name' => clienttranslate('Armored van'),
        'reward' => array(
            'cash' => 4,
            'influence' => 0,
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 0,
            2 => 1,
            3 => 0,
            4 => 1,
            5 => 0,
            6 => 0,
        ),
    ),
    204 => array(
        'type' => 'single',
        'chapter' => 'genesis',
        'name' => clienttranslate('Casino'),
        'reward' => array(
            'cash' => 2,
            'influence' => 2,
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 2,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0,
            6 => 0,
        ),
    ),
    205 => array(
        'type' => 'single',
        'chapter' => 'genesis',
        'name' => clienttranslate('Casino'),
        'reward' => array(
            'cash' => 4,
            'influence' => 0,
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 0,
            2 => 2,
            3 => 0,
            4 => 0,
            5 => 0,
            6 => 0,
        ),
    ),
    206 => array(
        'type' => 'single',
        'chapter' => 'genesis',
        'name' => clienttranslate('Casino'),
        'reward' => array(
            'cash' => 3,
            'influence' => 0,
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 0,
            2 => 1,
            3 => 0,
            4 => 0,
            5 => 1,
            6 => 0,
        ),
    ),
    207 => array(
        'type' => 'single',
        'chapter' => 'genesis',
        'name' => clienttranslate('Inmate transfer'),
        'reward' => array(
            'cash' => 2,
            'influence' => 1,
            'action' => array(
                'diversion' => 0,
                'recruit' => 1,
                'replay' => 0,
                'steal' => 0,
                'untap' => 0,
                'kill' => 0,
            ),
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 0,
            2 => 0,
            3 => 1,
            4 => 0,
            5 => 0,
            6 => 1,
        ),
    ),
    208 => array(
        'type' => 'single',
        'chapter' => 'genesis',
        'name' => clienttranslate('Inmate transfer'),
        'reward' => array(
            'cash' => 3,
            'influence' => 0,
            'action' => array(
                'diversion' => 0,
                'recruit' => 1,
                'replay' => 0,
                'steal' => 0,
                'untap' => 0,
                'kill' => 0,
            ),
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 1,
            6 => 1,
        ),
    ),
    209 => array(
        'type' => 'single',
        'chapter' => 'genesis',
        'name' => clienttranslate('Inmate transfer'),
        'reward' => array(
            'cash' => 3,
            'influence' => 1,
            'action' => array(
                'diversion' => 0,
                'recruit' => 1,
                'replay' => 0,
                'steal' => 0,
                'untap' => 0,
                'kill' => 0,
            ),
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 0,
            2 => 0,
            3 => 2,
            4 => 1,
            5 => 0,
            6 => 0,
        ),
    ),
    210 => array(
        'type' => 'single',
        'chapter' => 'genesis',
        'name' => clienttranslate('Jewelry'),
        'reward' => array(
            'cash' => 1,
            'influence' => 1,
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 0,
            2 => 1,
            3 => 0,
            4 => 0,
            5 => 0,
            6 => 0,
        ),
    ),
    211 => array(
        'type' => 'single',
        'chapter' => 'genesis',
        'name' => clienttranslate('Jewelry'),
        'reward' => array(
            'cash' => 3,
            'influence' => 0,
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 0,
            2 => 1,
            3 => 0,
            4 => 0,
            5 => 0,
            6 => 1,
        ),
    ),
    212 => array(
        'type' => 'single',
        'chapter' => 'genesis',
        'name' => clienttranslate('Jewelry'),
        'reward' => array(
            'cash' => 4,
            'influence' => 1,
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 1,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 2,
            6 => 0,
        ),
    ),
    213 => array(
        'type' => 'single',
        'chapter' => 'genesis',
        'name' => clienttranslate('Supermarket'),
        'reward' => array(
            'cash' => 6,
            'influence' => 0,
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 0,
            2 => 1,
            3 => 0,
            4 => 1,
            5 => 1,
            6 => 0,
        ),
    ),
    214 => array(
        'type' => 'single',
        'chapter' => 'genesis',
        'name' => clienttranslate('Supermarket'),
        'reward' => array(
            'cash' => 2,
            'influence' => 1,
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 1,
            2 => 0,
            3 => 1,
            4 => 0,
            5 => 0,
            6 => 0,
        ),
    ),
    215 => array(
        'type' => 'single',
        'chapter' => 'genesis',
        'name' => clienttranslate('Supermarket'),
        'reward' => array(
            'cash' => 0,
            'influence' => 0,
            'skill' => 'leader',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 0,
            2 => 1,
            3 => 0,
            4 => 0,
            5 => 0,
            6 => 0,
        ),
    ),
    216 => array(
        'type' => 'single',
        'chapter' => 'genesis',
        'name' => clienttranslate('Rival gangs'),
        'reward' => array(
            'cash' => 4,
            'influence' => 0,
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 0,
            2 => 0,
            3 => 1,
            4 => 0,
            5 => 1,
            6 => 0,
        ),
    ),
    217 => array(
        'type' => 'single',
        'chapter' => 'genesis',
        'name' => clienttranslate('Rival gangs'),
        'reward' => array(
            'cash' => 2,
            'influence' => 2,
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 0,
            2 => 0,
            3 => 1,
            4 => 0,
            5 => 0,
            6 => 1,
        ),
    ),
    218 => array(
        'type' => 'single',
        'chapter' => 'genesis',
        'name' => clienttranslate('Rival gangs'),
        'reward' => array(
            'cash' => 3,
            'influence' => 0,
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 1,
            5 => 1,
            6 => 0,
        ),
    ),
    219 => array(
        'type' => 'single',
        'chapter' => 'genesis',
        'name' => clienttranslate('Hack attack'),
        'high_tech_target' => true,
        'reward' => array(
            'cash' => 4,
            'influence' => 0,
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 1,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0,
            6 => 1,
        ),
    ),
    220 => array(
        'type' => 'single',
        'chapter' => 'genesis',
        'name' => clienttranslate('Hack attack'),
        'high_tech_target' => true,
        'reward' => array(
            'cash' => 1,
            'influence' => 1,
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0,
            6 => 1,
        ),
    ),
    221 => array(
        'type' => 'single',
        'chapter' => 'genesis',
        'name' => clienttranslate('Hack attack'),
        'high_tech_target' => true,
        'reward' => array(
            'cash' => 2,
            'influence' => 2,
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 1,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0,
            6 => 1,
        ),
    ),
    222 => array(
        'type' => 'single',
        'chapter' => 'genesis',
        'name' => clienttranslate('Drug dealer'),
        'reward' => array(
            'cash' => 3,
            'influence' => 1,
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 1,
            2 => 0,
            3 => 0,
            4 => 1,
            5 => 0,
            6 => 0,
        ),
    ),
    223 => array(
        'type' => 'single',
        'chapter' => 'genesis',
        'name' => clienttranslate('Drug dealer'),
        'reward' => array(
            'cash' => 5,
            'influence' => 0,
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 1,
            2 => 0,
            3 => 0,
            4 => 2,
            5 => 0,
            6 => 0,
        ),
    ),
    224 => array(
        'type' => 'single',
        'chapter' => 'genesis',
        'name' => clienttranslate('Drug dealer'),
        'reward' => array(
            'cash' => 5,
            'influence' => 0,
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 0,
            2 => 2,
            3 => 1,
            4 => 0,
            5 => 0,
            6 => 0,
        ),
    ),
    225 => array(
        'type' => 'single',
        'chapter' => 'genesis',
        'name' => clienttranslate('Offshore investments'),
        'reward' => array(
            'cash' => 2,
            'influence' => 0,
            'skill' => 'informant',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 2,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0,
            6 => 0,
        ),
    ),
    226 => array(
        'type' => 'single',
        'chapter' => 'genesis',
        'name' => clienttranslate('Offshore investments'),
        'reward' => array(
            'cash' => 2,
            'influence' => 0,
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0,
            6 => 1,
        ),
    ),
    227 => array(
        'type' => 'single',
        'chapter' => 'genesis',
        'name' => clienttranslate('Offshore investments'),
        'reward' => array(
            'cash' => 2,
            'influence' => 0,
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 1,
            6 => 0,
        ),
    ),
    228 => array(
        'type' => 'single',
        'chapter' => 'genesis',
        'name' => clienttranslate('Art theft'),
        'high_tech_target' => true,
        'reward' => array(
            'cash' => 2,
            'influence' => 0,
            'skill' => 'mercenary',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 1,
            5 => 0,
            6 => 1,
        ),
    ),
    229 => array(
        'type' => 'single',
        'chapter' => 'genesis',
        'name' => clienttranslate('Art theft'),
        'high_tech_target' => true,
        'reward' => array(
            'cash' => 1,
            'influence' => 2,
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 1,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 1,
            6 => 0,
        ),
    ),
    230 => array(
        'type' => 'single',
        'chapter' => 'genesis',
        'name' => clienttranslate('Art theft'),
        'high_tech_target' => true,
        'reward' => array(
            'cash' => 3,
            'influence' => 1,
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 0,
            2 => 2,
            3 => 0,
            4 => 0,
            5 => 0,
            6 => 0,
        ),
    ),
);

$this->heist_gangwars_type = array(
    301 => array(
        'type' => 'single',
        'chapter' => 'gangwar',
        'name' => clienttranslate('Armored van'),
        'reward' => array(
            'cash' => 3,
            'influence' => 0,
            'skill' => 'hacker',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 2,
            2 => 0,
            3 => 1,
            4 => 0,
            5 => 1,
            6 => 0,
        ),
    ),
    302 => array(
        'type' => 'single',
        'chapter' => 'gangwar',
        'name' => clienttranslate('Armored van'),
        'reward' => array(
            'cash' => 3,
            'influence' => 1,
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 0,
            2 => 2,
            3 => 1,
            4 => 0,
            5 => 0,
            6 => 0,
        ),
    ),
    303 => array(
        'type' => 'coop',
        'chapter' => 'gangwar',
        'name' => clienttranslate('Armored van'),
        'reward' => array(
            'cash' => 0,
            'influence' => 3,
            'skill' => '0',
            'coopcash' => 3
        ),
        'cost' => array(
            1 => 1,
            2 => 1,
            3 => 1,
            4 => 1,
            5 => 0,
            6 => 0,
        ),
    ),
    304 => array(
        'type' => 'single',
        'chapter' => 'gangwar',
        'name' => clienttranslate('Casino'),
        'reward' => array(
            'cash' => 2,
            'influence' => 2,
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 1,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 2,
            6 => 0,
        ),
    ),
    305 => array(
        'type' => 'coop',
        'chapter' => 'gangwar',
        'name' => clienttranslate('Casino'),
        'reward' => array(
            'cash' => 0,
            'influence' => 2,
            'skill' => '0',
            'coopcash' => 2
        ),
        'cost' => array(
            1 => 1,
            2 => 1,
            3 => 0,
            4 => 0,
            5 => 0,
            6 => 1,
        ),
    ),
    306 => array(
        'type' => 'single',
        'chapter' => 'gangwar',
        'name' => clienttranslate('Casino'),
        'reward' => array(
            'cash' => 4,
            'influence' => 0,
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 0,
            2 => 1,
            3 => 0,
            4 => 0,
            5 => 1,
            6 => 1,
        ),
    ),
    307 => array(
        'type' => 'single',
        'chapter' => 'gangwar',
        'name' => clienttranslate('Inmate transfer'),
        'reward' => array(
            'cash' => 2,
            'influence' => 0,
            'action' => array(
                'diversion' => 0,
                'recruit' => 1,
                'replay' => 0,
                'steal' => 0,
                'untap' => 0,
                'kill' => 0,
            ),
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 0,
            2 => 1,
            3 => 1,
            4 => 0,
            5 => 0,
            6 => 1,
        ),
    ),
    308 => array(
        'type' => 'single',
        'chapter' => 'gangwar',
        'name' => clienttranslate('Inmate transfer'),
        'reward' => array(
            'cash' => 4,
            'influence' => 0,
            'action' => array(
                'diversion' => 0,
                'recruit' => 1,
                'replay' => 0,
                'steal' => 0,
                'untap' => 0,
                'kill' => 0,
            ),
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 0,
            2 => 2,
            3 => 1,
            4 => 1,
            5 => 0,
            6 => 0,
        ),
    ),
    309 => array(
        'type' => 'single',
        'chapter' => 'gangwar',
        'name' => clienttranslate('Inmate transfer'),
        'reward' => array(
            'cash' => 2,
            'influence' => 1,
            'action' => array(
                'diversion' => 0,
                'recruit' => 1,
                'replay' => 0,
                'steal' => 0,
                'untap' => 0,
                'kill' => 0,
            ),
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 2,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 1,
            6 => 0,
        ),
    ),
    310 => array(
        'type' => 'single',
        'chapter' => 'gangwar',
        'name' => clienttranslate('Jewelry'),
        'reward' => array(
            'cash' => 4,
            'influence' => 0,
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 0,
            2 => 1,
            3 => 0,
            4 => 0,
            5 => 2,
            6 => 0,
        ),
    ),
    311 => array(
        'type' => 'single',
        'chapter' => 'gangwar',
        'name' => clienttranslate('Jewelry'),
        'reward' => array(
            'cash' => 3,
            'influence' => 0,
            'action' => array(
                'diversion' => 2,
                'recruit' => 0,
                'replay' => 0,
                'steal' => 0,
                'untap' => 0,
                'kill' => 0,
            ),
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 0,
            2 => 2,
            3 => 0,
            4 => 0,
            5 => 1,
            6 => 0,
        ),
    ),
    312 => array(
        'type' => 'single',
        'chapter' => 'gangwar',
        'name' => clienttranslate('Jewelry'),
        'reward' => array(
            'cash' => 3,
            'influence' => 2,
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 1,
            2 => 0,
            3 => 2,
            4 => 1,
            5 => 0,
            6 => 0,
        ),
    ),
    313 => array(
        'type' => 'single',
        'chapter' => 'gangwar',
        'name' => clienttranslate('Supermarket'),
        'reward' => array(
            'cash' => 2,
            'influence' => 0,
            'skill' => 'brawler',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 0,
            2 => 2,
            3 => 0,
            4 => 1,
            5 => 0,
            6 => 0,
        ),
    ),
    314 => array(
        'type' => 'single',
        'chapter' => 'gangwar',
        'name' => clienttranslate('Supermarket'),
        'reward' => array(
            'cash' => 3,
            'influence' => 0,
            'action' => array(
                'diversion' => 0,
                'recruit' => 0,
                'replay' => 1,
                'steal' => 0,
                'untap' => 0,
                'kill' => 0,
            ),
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 2,
            2 => 1,
            3 => 1,
            4 => 0,
            5 => 0,
            6 => 0,
        ),
    ),
    315 => array(
        'type' => 'coop',
        'chapter' => 'gangwar',
        'name' => clienttranslate('Supermarket'),
        'reward' => array(
            'cash' => 2,
            'influence' => 1,
            'skill' => '0',
            'coopcash' => 4
        ),
        'cost' => array(
            1 => 0,
            2 => 1,
            3 => 0,
            4 => 1,
            5 => 1,
            6 => 0,
        ),
    ),
    316 => array(
        'type' => 'single',
        'chapter' => 'gangwar',
        'name' => clienttranslate('Rival gangs'),
        'reward' => array(
            'cash' => 3,
            'influence' => 2,
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 0,
            2 => 0,
            3 => 1,
            4 => 1,
            5 => 1,
            6 => 1,
        ),
    ),
    317 => array(
        'type' => 'single',
        'chapter' => 'gangwar',
        'name' => clienttranslate('Rival gangs'),
        'reward' => array(
            'cash' => 3,
            'influence' => 1,
            'action' => array(
                'diversion' => 0,
                'recruit' => 0,
                'replay' => 0,
                'steal' => 2,
                'untap' => 0,
                'kill' => 0,
            ),
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 1,
            2 => 1,
            3 => 0,
            4 => 2,
            5 => 1,
            6 => 0,
        ),
    ),
    318 => array(
        'type' => 'single',
        'chapter' => 'gangwar',
        'name' => clienttranslate('Rival gangs'),
        'reward' => array(
            'cash' => 0,
            'influence' => 2,
            'action' => array(
                'diversion' => 0,
                'recruit' => 0,
                'replay' => 0,
                'steal' => 2,
                'untap' => 0,
                'kill' => 0,
            ),
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 1,
            5 => 2,
            6 => 0,
        ),
    ),
    319 => array(
        'type' => 'single',
        'chapter' => 'gangwar',
        'name' => clienttranslate('Hack attack'),
        'high_tech_target' => true,
        'reward' => array(
            'cash' => 1,
            'influence' => 2,
            'action' => array(
                'diversion' => 2,
                'recruit' => 0,
                'replay' => 0,
                'steal' => 0,
                'untap' => 0,
                'kill' => 0,
            ),
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 0,
            2 => 0,
            3 => 1,
            4 => 0,
            5 => 1,
            6 => 1,
        ),
    ),
    320 => array(
        'type' => 'single',
        'chapter' => 'gangwar',
        'name' => clienttranslate('Hack attack'),
        'high_tech_target' => true,
        'reward' => array(
            'cash' => 0,
            'influence' => 0,
            'action' => array(
                'diversion' => 0,
                'recruit' => 0,
                'replay' => 0,
                'steal' => 4,
                'untap' => 0,
                'kill' => 0,
            ),
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 1,
            2 => 1,
            3 => 0,
            4 => 0,
            5 => 0,
            6 => 1,
        ),
    ),
    321 => array(
        'type' => 'single',
        'chapter' => 'gangwar',
        'name' => clienttranslate('Hack attack'),
        'high_tech_target' => true,
        'reward' => array(
            'cash' => 0,
            'influence' => 1,
            'action' => array(
                'diversion' => 0,
                'recruit' => 0,
                'replay' => 1,
                'steal' => 1,
                'untap' => 0,
                'kill' => 0,
            ),
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 0,
            2 => 1,
            3 => 0,
            4 => 0,
            5 => 0,
            6 => 2,
        ),
    ),
    322 => array(
        'type' => 'coop',
        'chapter' => 'gangwar',
        'name' => clienttranslate('Drug dealer'),
        'reward' => array(
            'cash' => 3,
            'influence' => 1,
            'skill' => '0',
            'coopcash' => 2
        ),
        'cost' => array(
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 2,
            5 => 1,
            6 => 1,
        ),
    ),
    323 => array(
        'type' => 'single',
        'chapter' => 'gangwar',
        'name' => clienttranslate('Drug dealer'),
        'reward' => array(
            'cash' => 2,
            'influence' => 0,
            'action' => array(
                'diversion' => 0,
                'recruit' => 0,
                'replay' => 1,
                'steal' => 0,
                'untap' => 0,
                'kill' => 0,
            ),
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 1,
            2 => 0,
            3 => 0,
            4 => 2,
            5 => 0,
            6 => 0,
        ),
    ),
    324 => array(
        'type' => 'single',
        'chapter' => 'gangwar',
        'name' => clienttranslate('Drug dealer'),
        'reward' => array(
            'cash' => 3,
            'influence' => 3,
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 0,
            2 => 1,
            3 => 2,
            4 => 1,
            5 => 0,
            6 => 1,
        ),
    ),
    325 => array(
        'type' => 'coop',
        'chapter' => 'gangwar',
        'name' => clienttranslate('Offshore investments'),
        'reward' => array(
            'cash' => 0,
            'influence' => 2,
            'skill' => '0',
            'coopcash' => 2
        ),
        'cost' => array(
            1 => 2,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0,
            6 => 1,
        ),
    ),
    326 => array(
        'type' => 'single',
        'chapter' => 'gangwar',
        'name' => clienttranslate('Offshore investments'),
        'reward' => array(
            'cash' => 2,
            'influence' => 0,
            'skill' => 'sniper',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 1,
            2 => 0,
            3 => 1,
            4 => 0,
            5 => 0,
            6 => 1,
        ),
    ),
    327 => array(
        'type' => 'single',
        'chapter' => 'gangwar',
        'name' => clienttranslate('Offshore investments'),
        'reward' => array(
            'cash' => 0,
            'influence' => 3,
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 1,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0,
            6 => 2,
        ),
    ),
    328 => array(
        'type' => 'coop',
        'chapter' => 'gangwar',
        'name' => clienttranslate('Art theft'),
        'high_tech_target' => true,
        'reward' => array(
            'cash' => 1,
            'influence' => 1,
            'skill' => '0',
            'coopcash' => 2
        ),
        'cost' => array(
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 1,
            5 => 0,
            6 => 2,
        ),
    ),
    329 => array(
        'type' => 'single',
        'chapter' => 'gangwar',
        'name' => clienttranslate('Art theft'),
        'high_tech_target' => true,
        'reward' => array(
            'cash' => 4,
            'influence' => 1,
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 1,
            2 => 2,
            3 => 0,
            4 => 0,
            5 => 1,
            6 => 0,
        ),
    ),
    330 => array(
        'type' => 'single',
        'chapter' => 'gangwar',
        'name' => clienttranslate('Art theft'),
        'high_tech_target' => true,
        'reward' => array(
            'cash' => 2,
            'influence' => 2,
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 1,
            2 => 1,
            3 => 0,
            4 => 1,
            5 => 0,
            6 => 0,
        ),
    ),
    331 => array(
        'type' => 'snitch',
        'name' => clienttranslate('Snitch'),
        'chapter' => 'gangwar',
        'cost' => array(
            1 => 0,
            2 => 0,
            3 => 2,
            4 => 0,
            5 => 0,
            6 => 0
        ),
    ),
    332 => array(
        'type' => 'snitch',
        'name' => clienttranslate('Snitch'),
        'chapter' => 'gangwar',
        'cost' => array(
            1 => 0,
            2 => 0,
            3 => 3,
            4 => 0,
            5 => 0,
            6 => 0
        ),
    ),
    333 => array(
        'type' => 'snitch',
        'name' => clienttranslate('Snitch'),
        'chapter' => 'gangwar',
        'cost' => array(
            1 => 0,
            2 => 0,
            3 => 4,
            4 => 0,
            5 => 0,
            6 => 0
        ),
    ),
    334 => array(
        'type' => 'coop',
        'chapter' => 'gangwar',
        'name' => clienttranslate('Casino'),
        'reward' => array(
            'cash' => 1,
            'influence' => 2,
            'skill' => '0',
            'coopcash' => 3
        ),
        'cost' => array(
            1 => 1,
            2 => 0,
            3 => 1,
            4 => 1,
            5 => 0,
            6 => 1,
        ),
    ),
    335 => array(
        'type' => 'single',
        'chapter' => 'gangwar',
        'name' => clienttranslate('Art Theft'),
        'high_tech_target' => true,
        'reward' => array(
            'cash' => 2,
            'influence' => 0,
            'action' => array(
                'diversion' => 0,
                'recruit' => 0,
                'replay' => 0,
                'steal' => 2,
                'untap' => 0,
                'kill' => 0,
            ),
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 0,
            2 => 0,
            3 => 1,
            4 => 1,
            5 => 1,
            6 => 0,
        ),
    ),
    336 => array(
        'type' => 'single',
        'chapter' => 'gangwar',
        'name' => clienttranslate('Rival Gangs'),
        'reward' => array(
            'cash' => 0,
            'influence' => 2,
            'action' => array(
                'diversion' => 3,
                'recruit' => 0,
                'replay' => 0,
                'steal' => 0,
                'untap' => 0,
                'kill' => 0,
            ),
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 1,
            2 => 1,
            3 => 0,
            4 => 0,
            5 => 1,
            6 => 1,
        ),
    ),
);

$this->heist_domination_type = array(
    401 => array(
        'type' => 'coop',
        'chapter' => 'domination',
        'name' => clienttranslate('Armored van'),
        'reward' => array(
            'cash' => 0,
            'influence' => 2,
            'skill' => '0',
            'coopcash' => 4
        ),
        'cost' => array(
            1 => 1,
            2 => 2,
            3 => 0,
            4 => 1,
            5 => 1,
            6 => 0,
        ),
    ),
    402 => array(
        'type' => 'single',
        'chapter' => 'domination',
        'name' => clienttranslate('Armored van'),
        'reward' => array(
            'cash' => 0,
            'influence' => 3,
            'action' => array(
                'diversion' => 0,
                'recruit' => 0,
                'replay' => 0,
                'steal' => 0,
                'untap' => 1,
                'kill' => 0,
            ),
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 0,
            2 => 1,
            3 => 1,
            4 => 2,
            5 => 0,
            6 => 0,
        ),
    ),
    403 => array(
        'type' => 'single',
        'chapter' => 'domination',
        'name' => clienttranslate('Armored van'),
        'reward' => array(
            'cash' => 5,
            'influence' => 0,
            'action' => array(
                'diversion' => 0,
                'recruit' => 0,
                'replay' => 0,
                'steal' => 2,
                'untap' => 0,
                'kill' => 0,
            ),
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 2,
            2 => 0,
            3 => 1,
            4 => 1,
            5 => 0,
            6 => 0,
        ),
    ),
    404 => array(
        'type' => 'single',
        'chapter' => 'domination',
        'name' => clienttranslate('Casino'),
        'reward' => array(
            'cash' => 2,
            'influence' => 2,
            'action' => array(
                'diversion' => 0,
                'recruit' => 0,
                'replay' => 1,
                'steal' => 0,
                'untap' => 0,
                'kill' => 0,
            ),
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 3,
            2 => 0,
            3 => 1,
            4 => 0,
            5 => 1,
            6 => 0,
        ),
    ),
    405 => array(
        'type' => 'coop',
        'chapter' => 'domination',
        'name' => clienttranslate('Casino'),
        'reward' => array(
            'cash' => 0,
            'influence' => 3,
            'skill' => '0',
            'coopcash' => 2
        ),
        'cost' => array(
            1 => 1,
            2 => 2,
            3 => 0,
            4 => 0,
            5 => 0,
            6 => 2,
        ),
    ),
    406 => array(
        'type' => 'single',
        'chapter' => 'domination',
        'name' => clienttranslate('Casino'),
        'reward' => array(
            'cash' => 0,
            'influence' => 3,
            'action' => array(
                'diversion' => 0,
                'recruit' => 0,
                'replay' => 0,
                'steal' => 4,
                'untap' => 0,
                'kill' => 0,
            ),
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 0,
            2 => 2,
            3 => 0,
            4 => 1,
            5 => 1,
            6 => 2,
        ),
    ),
    407 => array(
        'type' => 'single',
        'chapter' => 'domination',
        'name' => clienttranslate('Inmate transfer'),
        'reward' => array(
            'cash' => 2,
            'influence' => 1,
            'action' => array(
                'diversion' => 0,
                'recruit' => 1,
                'replay' => 0,
                'steal' => 0,
                'untap' => 0,
                'kill' => 0,
            ),
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 0,
            2 => 0,
            3 => 2,
            4 => 0,
            5 => 1,
            6 => 1,
        ),
    ),
    408 => array(
        'type' => 'single',
        'chapter' => 'domination',
        'name' => clienttranslate('Inmate transfer'),
        'reward' => array(
            'cash' => 1,
            'influence' => 2,
            'action' => array(
                'diversion' => 0,
                'recruit' => 1,
                'replay' => 0,
                'steal' => 0,
                'untap' => 0,
                'kill' => 0,
            ),
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 1,
            2 => 0,
            3 => 0,
            4 => 2,
            5 => 1,
            6 => 0,
        ),
    ),
    409 => array(
        'type' => 'single',
        'chapter' => 'domination',
        'name' => clienttranslate('Inmate transfer'),
        'reward' => array(
            'cash' => 4,
            'influence' => 0,
            'action' => array(
                'diversion' => 0,
                'recruit' => 1,
                'replay' => 0,
                'steal' => 0,
                'untap' => 0,
                'kill' => 0,
            ),
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 0,
            2 => 0,
            3 => 1,
            4 => 1,
            5 => 0,
            6 => 1,
        ),
    ),
    410 => array(
        'type' => 'single',
        'chapter' => 'domination',
        'name' => clienttranslate('Jewelry'),
        'reward' => array(
            'cash' => 1,
            'influence' => 1,
            'action' => array(
                'diversion' => 3,
                'recruit' => 0,
                'replay' => 0,
                'steal' => 0,
                'untap' => 0,
                'kill' => 0,
            ),
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 1,
            2 => 2,
            3 => 0,
            4 => 0,
            5 => 0,
            6 => 0,
        ),
    ),
    411 => array(
        'type' => 'single',
        'chapter' => 'domination',
        'name' => clienttranslate('Jewelry'),
        'reward' => array(
            'cash' => 6,
            'influence' => 1,
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 0,
            2 => 1,
            3 => 2,
            4 => 1,
            5 => 0,
            6 => 1,
        ),
    ),
    412 => array(
        'type' => 'coop',
        'chapter' => 'domination',
        'name' => clienttranslate('Jewelry'),
        'reward' => array(
            'cash' => 3,
            'influence' => 1,
            'skill' => '0',
            'coopcash' => 3
        ),
        'cost' => array(
            1 => 2,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 2,
            6 => 0,
        ),
    ),
    413 => array(
        'type' => 'single',
        'chapter' => 'domination',
        'name' => clienttranslate('Supermarket'),
        'reward' => array(
            'cash' => 5,
            'influence' => 2,
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 0,
            2 => 1,
            3 => 0,
            4 => 1,
            5 => 2,
            6 => 0,
        ),
    ),
    414 => array(
        'type' => 'coop',
        'chapter' => 'domination',
        'name' => clienttranslate('Supermarket'),
        'reward' => array(
            'cash' => 0,
            'influence' => 2,
            'skill' => '0',
            'coopcash' => 3
        ),
        'cost' => array(
            1 => 1,
            2 => 1,
            3 => 1,
            4 => 1,
            5 => 0,
            6 => 0,
        ),
    ),
    415 => array(
        'type' => 'single',
        'chapter' => 'domination',
        'name' => clienttranslate('Supermarket'),
        'reward' => array(
            'cash' => 6,
            'influence' => 0,
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 0,
            2 => 3,
            3 => 0,
            4 => 1,
            5 => 0,
            6 => 0,
        ),
    ),
    416 => array(
        'type' => 'coop',
        'chapter' => 'domination',
        'name' => clienttranslate('Rival gangs'),
        'reward' => array(
            'cash' => 1,
            'influence' => 3,
            'skill' => '0',
            'coopcash' => 3
        ),
        'cost' => array(
            1 => 0,
            2 => 0,
            3 => 2,
            4 => 2,
            5 => 1,
            6 => 0,
        ),
    ),
    417 => array(
        'type' => 'single',
        'chapter' => 'domination',
        'name' => clienttranslate('Rival gangs'),
        'reward' => array(
            'cash' => 2,
            'influence' => 4,
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 2,
            2 => 1,
            3 => 0,
            4 => 0,
            5 => 3,
            6 => 0,
        ),
    ),
    418 => array(
        'type' => 'single',
        'chapter' => 'domination',
        'name' => clienttranslate('Rival gangs'),
        'reward' => array(
            'cash' => 0,
            'influence' => 0,
            'action' => array(
                'diversion' => 0,
                'recruit' => 0,
                'replay' => 0,
                'steal' => 0,
                'untap' => 0,
                'kill' => 1,
            ),
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 1,
            5 => 2,
            6 => 0,
        ),
    ),
    419 => array(
        'type' => 'single',
        'chapter' => 'domination',
        'name' => clienttranslate('Hack attack'),
        'high_tech_target' => true,
        'reward' => array(
            'cash' => 4,
            'influence' => 2,
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 0,
            2 => 1,
            3 => 1,
            4 => 0,
            5 => 0,
            6 => 2,
        ),
    ),
    420 => array(
        'type' => 'single',
        'chapter' => 'domination',
        'name' => clienttranslate('Hack attack'),
        'high_tech_target' => true,
        'reward' => array(
            'cash' => 3,
            'influence' => 1,
            'action' => array(
                'diversion' => 0,
                'recruit' => 0,
                'replay' => 0,
                'steal' => 2,
                'untap' => 0,
                'kill' => 0,
            ),
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 1,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 1,
            6 => 2,
        ),
    ),
    421 => array(
        'type' => 'single',
        'chapter' => 'domination',
        'name' => clienttranslate('Hack attack'),
        'high_tech_target' => true,
        'reward' => array(
            'cash' => 3,
            'influence' => 2,
            'action' => array(
                'diversion' => 0,
                'recruit' => 0,
                'replay' => 0,
                'steal' => 0,
                'untap' => 1,
                'kill' => 0,
            ),
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 2,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0,
            6 => 3,
        ),
    ),
    422 => array(
        'type' => 'single',
        'chapter' => 'domination',
        'name' => clienttranslate('Drug dealer'),
        'reward' => array(
            'cash' => 2,
            'influence' => 1,
            'action' => array(
                'diversion' => 3,
                'recruit' => 0,
                'replay' => 0,
                'steal' => 0,
                'untap' => 0,
                'kill' => 0,
            ),
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 0,
            2 => 1,
            3 => 1,
            4 => 0,
            5 => 1,
            6 => 0,
        ),
    ),
    423 => array(
        'type' => 'single',
        'chapter' => 'domination',
        'name' => clienttranslate('Drug dealer'),
        'reward' => array(
            'cash' => 4,
            'influence' => 2,
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 2,
            2 => 0,
            3 => 2,
            4 => 0,
            5 => 0,
            6 => 0,
        ),
    ),
    424 => array(
        'type' => 'coop',
        'chapter' => 'domination',
        'name' => clienttranslate('Drug dealer'),
        'reward' => array(
            'cash' => 1,
            'influence' => 2,
            'skill' => '0',
            'coopcash' => 2
        ),
        'cost' => array(
            1 => 0,
            2 => 0,
            3 => 1,
            4 => 1,
            5 => 1,
            6 => 1,
        ),
    ),
    425 => array(
        'type' => 'coop',
        'chapter' => 'domination',
        'name' => clienttranslate('Offshore investments'),
        'reward' => array(
            'cash' => 0,
            'influence' => 3,
            'skill' => '0',
            'coopcash' => 2
        ),
        'cost' => array(
            1 => 1,
            2 => 1,
            3 => 0,
            4 => 0,
            5 => 1,
            6 => 2,
        ),
    ),
    426 => array(
        'type' => 'single',
        'chapter' => 'domination',
        'name' => clienttranslate('Offshore investments'),
        'reward' => array(
            'cash' => 3,
            'influence' => 1,
            'action' => array(
                'diversion' => 0,
                'recruit' => 0,
                'replay' => 0,
                'steal' => 0,
                'untap' => 0,
                'kill' => 1,
            ),
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 1,
            2 => 0,
            3 => 1,
            4 => 0,
            5 => 2,
            6 => 2,
        ),
    ),
    427 => array(
        'type' => 'single',
        'chapter' => 'domination',
        'name' => clienttranslate('Offshore investments'),
        'reward' => array(
            'cash' => 3,
            'influence' => 0,
            'action' => array(
                'diversion' => 0,
                'recruit' => 0,
                'replay' => 1,
                'steal' => 0,
                'untap' => 0,
                'kill' => 0,
            ),
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 0,
            2 => 2,
            3 => 1,
            4 => 0,
            5 => 0,
            6 => 1,
        ),
    ),
    428 => array(
        'type' => 'single',
        'chapter' => 'domination',
        'name' => clienttranslate('Art theft'),
        'high_tech_target' => true,
        'reward' => array(
            'cash' => 0,
            'influence' => 2,
            'action' => array(
                'diversion' => 0,
                'recruit' => 0,
                'replay' => 1,
                'steal' => 0,
                'untap' => 0,
                'kill' => 0,
            ),
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 1,
            2 => 0,
            3 => 0,
            4 => 3,
            5 => 0,
            6 => 1,
        ),
    ),
    429 => array(
        'type' => 'single',
        'chapter' => 'domination',
        'name' => clienttranslate('Art theft'),
        'high_tech_target' => true,
        'reward' => array(
            'cash' => 0,
            'influence' => 0,
            'action' => array(
                'diversion' => 0,
                'recruit' => 0,
                'replay' => 0,
                'steal' => 3,
                'untap' => 1,
                'kill' => 0,
            ),
            'skill' => '0',
            'coopcash' => 0
        ),
        'cost' => array(
            1 => 2,
            2 => 1,
            3 => 1,
            4 => 0,
            5 => 0,
            6 => 0,
        ),
    ),
    430 => array(
        'type' => 'coop',
        'chapter' => 'domination',
        'name' => clienttranslate('Art theft'),
        'high_tech_target' => true,
        'reward' => array(
            'cash' => 2,
            'influence' => 2,
            'skill' => '0',
            'coopcash' => 2
        ),
        'cost' => array(
            1 => 1,
            2 => 1,
            3 => 0,
            4 => 2,
            5 => 0,
            6 => 0,
        ),
    ),
    431 => array(
        'type' => 'snitch',
        'name' => clienttranslate('Snitch'),
        'chapter' => 'domination',
        'cost' => array(
            1 => 0,
            2 => 0,
            3 => 3,
            4 => 0,
            5 => 0,
            6 => 0
        ),
    ),
    432 => array(
        'type' => 'snitch',
        'name' => clienttranslate('Snitch'),
        'chapter' => 'domination',
        'cost' => array(
            1 => 0,
            2 => 0,
            3 => 3,
            4 => 0,
            5 => 0,
            6 => 0
        ),
    ),
    433 => array(
        'type' => 'snitch',
        'name' => clienttranslate('Snitch'),
        'chapter' => 'domination',
        'cost' => array(
            1 => 0,
            2 => 0,
            3 => 3,
            4 => 0,
            5 => 0,
            6 => 0
        ),
    ),
    434 => array(
        'type' => 'snitch',
        'name' => clienttranslate('Snitch'),
        'chapter' => 'domination',
        'cost' => array(
            1 => 0,
            2 => 0,
            3 => 4,
            4 => 0,
            5 => 0,
            6 => 0
        ),
    ),
    435 => array(
        'type' => 'snitch',
        'name' => clienttranslate('Snitch'),
        'chapter' => 'domination',
        'cost' => array(
            1 => 0,
            2 => 0,
            3 => 4,
            4 => 0,
            5 => 0,
            6 => 0
        ),
    ),
);

$this->gangster_type = array(
    101 => array(
        'type' => 'boss',
        'clan' => 'bratva',
        'stats' => array(
            1 => 1,
            2 => 0,
            3 => 0,
            4 => 1,
            5 => 0,
            6 => 0,
        ),
        'cost' =>8,
        'influence' =>0,
    ),
    102 => array(
        'type' => 'boss',
        'clan' => 'cartel',
        'stats' => array(
            1 => 1,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 1,
            6 => 0,
        ),
        'cost' =>8,
        'influence' =>0,
    ),
    103 => array(
        'type' => 'boss',
        'clan' => 'gang',
        'stats' => array(
            1 => 1,
            2 => 1,
            3 => 0,
            4 => 0,
            5 => 0,
            6 => 0,
        ),
        'cost' =>6,
        'influence' =>0,
    ),
    104 => array(
        'type' => 'boss',
        'clan' => 'mafia',
        'stats' => array(
            1 => 1,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0,
            6 => 1,
        ),
        'cost' =>8,
        'influence' =>0,
    ),
    105 => array(
        'type' => 'boss',
        'clan' => 'triad',
        'stats' => array(
            1 => 1,
            2 => 0,
            3 => 1,
            4 => 0,
            5 => 0,
            6 => 0,
        ),
        'cost' =>6,
        'influence' =>0,
    ),
    111 => array(
        'type' => 'gangster',
        'clan' => 'bratva',
        'stats' => array(
            1 => 0,
            2 => 1,
            3 => 0,
            4 => 1,
            5 => 0,
            6 => 0,
        ),
        'cost' =>4,
        'influence' =>1,
    ),
    112 => array(
        'type' => 'gangster',
        'clan' => 'bratva',
        'stats' => array(
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 1,
            5 => 1,
            6 => 0,
        ),
        'cost' =>4,
        'influence' =>2,
    ),
    113 => array(
        'type' => 'gangster',
        'clan' => 'bratva',
        'stats' => array(
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 1,
            5 => 0,
            6 => 1,
        ),
        'cost' =>4,
        'influence' =>2,
    ),
    114 => array(
        'type' => 'gangster',
        'clan' => 'bratva',
        'stats' => array(
            1 => 0,
            2 => 0,
            3 => 1,
            4 => 0,
            5 => 0,
            6 => 1,
        ),
        'cost' =>4,
        'influence' =>1,
    ),
    115 => array(
        'type' => 'gangster',
        'clan' => 'bratva',
        'stats' => array(
            1 => 1,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 1,
            6 => 0,
        ),
        'cost' =>5,
        'influence' =>2,
    ),
    116 => array(
        'type' => 'gangster',
        'clan' => 'bratva',
        'stats' => array(
            1 => 1,
            2 => 1,
            3 => 0,
            4 => 0,
            5 => 0,
            6 => 1,
        ),
        'cost' =>6,
        'influence' =>1,
    ),
    117 => array(
        'type' => 'gangster',
        'clan' => 'bratva',
        'stats' => array(
            1 => 0,
            2 => 2,
            3 => 0,
            4 => 0,
            5 => 1,
            6 => 0,
        ),
        'cost' =>8,
        'influence' =>3,
    ),
    121 => array(
        'type' => 'gangster',
        'clan' => 'cartel',
        'stats' => array(
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 1,
            5 => 0,
            6 => 0,
        ),
        'cost' =>2,
        'influence' =>1,
    ),
    122 => array(
        'type' => 'gangster',
        'clan' => 'cartel',
        'stats' => array(
            1 => 1,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0,
            6 => 0,
        ),
        'cost' =>3,
        'influence' =>1,
    ),
    123 => array(
        'type' => 'gangster',
        'clan' => 'cartel',
        'stats' => array(
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 2,
            6 => 0,
        ),
        'cost' =>4,
        'influence' =>2,
    ),
    124 => array(
        'type' => 'gangster',
        'clan' => 'cartel',
        'stats' => array(
            1 => 0,
            2 => 0,
            3 => 1,
            4 => 1,
            5 => 0,
            6 => 0,
        ),
        'cost' =>5,
        'influence' =>2,
    ),
    125 => array(
        'type' => 'gangster',
        'clan' => 'cartel',
        'stats' => array(
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0,
            6 => 2,
        ),
        'cost' =>5,
        'influence' =>3,
    ),
    126 => array(
        'type' => 'gangster',
        'clan' => 'cartel',
        'stats' => array(
            1 => 0,
            2 => 2,
            3 => 1,
            4 => 0,
            5 => 0,
            6 => 0,
        ),
        'cost' =>7,
        'influence' =>1,
    ),
    127 => array(
        'type' => 'gangster',
        'clan' => 'cartel',
        'stats' => array(
            1 => 1,
            2 => 1,
            3 => 0,
            4 => 1,
            5 => 0,
            6 => 0,
        ),
        'cost' =>7,
        'influence' =>2,
    ),
    131 => array(
        'type' => 'gangster',
        'clan' => 'gang',
        'stats' => array(
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 1,
            6 => 0,
        ),
        'cost' =>2,
        'influence' =>1,
    ),
    132 => array(
        'type' => 'gangster',
        'clan' => 'gang',
        'stats' => array(
            1 => 0,
            2 => 1,
            3 => 0,
            4 => 0,
            5 => 0,
            6 => 1,
        ),
        'cost' =>5,
        'influence' =>2,
    ),
    133 => array(
        'type' => 'gangster',
        'clan' => 'gang',
        'stats' => array(
            1 => 1,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0,
            6 => 1,
        ),
        'cost' =>4,
        'influence' =>1,
    ),
    134 => array(
        'type' => 'gangster',
        'clan' => 'gang',
        'stats' => array(
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 2,
            5 => 0,
            6 => 0,
        ),
        'cost' =>4,
        'influence' =>2,
    ),
    135 => array(
        'type' => 'gangster',
        'clan' => 'gang',
        'stats' => array(
            1 => 0,
            2 => 0,
            3 => 2,
            4 => 0,
            5 => 0,
            6 => 0,
        ),
        'cost' =>6,
        'influence' =>2,
    ),
    136 => array(
        'type' => 'gangster',
        'clan' => 'gang',
        'stats' => array(
            1 => 1,
            2 => 1,
            3 => 0,
            4 => 0,
            5 => 0,
            6 => 0,
        ),
        'cost' =>6,
        'influence' =>2,
    ),
    137 => array(
        'type' => 'gangster',
        'clan' => 'gang',
        'stats' => array(
            1 => 0,
            2 => 1,
            3 => 0,
            4 => 0,
            5 => 1,
            6 => 0,
        ),
        'cost' =>6,
        'influence' =>3,
    ),
    141 => array(
        'type' => 'gangster',
        'clan' => 'mafia',
        'stats' => array(
            1 => 0,
            2 => 0,
            3 => 1,
            4 => 0,
            5 => 0,
            6 => 0,
        ),
        'cost' =>3,
        'influence' =>1,
    ),
    142 => array(
        'type' => 'gangster',
        'clan' => 'mafia',
        'stats' => array(
            1 => 1,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 1,
            6 => 0,
        ),
        'cost' =>4,
        'influence' =>1,
    ),
    143 => array(
        'type' => 'gangster',
        'clan' => 'mafia',
        'stats' => array(
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 1,
            6 => 1,
        ),
        'cost' =>4,
        'influence' =>2,
    ),
    144 => array(
        'type' => 'gangster',
        'clan' => 'mafia',
        'stats' => array(
            1 => 0,
            2 => 1,
            3 => 0,
            4 => 0,
            5 => 0,
            6 => 1,
        ),
        'cost' =>4,
        'influence' =>1,
    ),
    145 => array(
        'type' => 'gangster',
        'clan' => 'mafia',
        'stats' => array(
            1 => 0,
            2 => 1,
            3 => 1,
            4 => 0,
            5 => 0,
            6 => 0,
        ),
        'cost' =>6,
        'influence' =>2,
    ),
    146 => array(
        'type' => 'gangster',
        'clan' => 'mafia',
        'stats' => array(
            1 => 1,
            2 => 0,
            3 => 1,
            4 => 0,
            5 => 0,
            6 => 0,
        ),
        'cost' =>6,
        'influence' =>2,
    ),
    147 => array(
        'type' => 'gangster',
        'clan' => 'mafia',
        'stats' => array(
            1 => 0,
            2 => 1,
            3 => 0,
            4 => 1,
            5 => 0,
            6 => 0,
        ),
        'cost' =>6,
        'influence' =>3,
    ),
    151 => array(
        'type' => 'gangster',
        'clan' => 'triad',
        'stats' => array(
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0,
            6 => 1,
        ),
        'cost' =>2,
        'influence' =>1,
    ),
    152 => array(
        'type' => 'gangster',
        'clan' => 'triad',
        'stats' => array(
            1 => 0,
            2 => 1,
            3 => 0,
            4 => 0,
            5 => 0,
            6 => 0,
        ),
        'cost' =>4,
        'influence' =>3,
    ),
    153 => array(
        'type' => 'gangster',
        'clan' => 'triad',
        'stats' => array(
            1 => 0,
            2 => 2,
            3 => 0,
            4 => 0,
            5 => 0,
            6 => 0,
        ),
        'cost' =>5,
        'influence' =>1,
    ),
    154 => array(
        'type' => 'gangster',
        'clan' => 'triad',
        'stats' => array(
            1 => 1,
            2 => 0,
            3 => 0,
            4 => 1,
            5 => 0,
            6 => 0,
        ),
        'cost' =>4,
        'influence' =>1,
    ),
    155 => array(
        'type' => 'gangster',
        'clan' => 'triad',
        'stats' => array(
            1 => 1,
            2 => 0,
            3 => 1,
            4 => 0,
            5 => 0,
            6 => 0,
        ),
        'cost' =>6,
        'influence' =>2,
    ),
    156 => array(
        'type' => 'gangster',
        'clan' => 'triad',
        'stats' => array(
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 1,
            5 => 1,
            6 => 1,
        ),
        'cost' =>6,
        'influence' =>2,
    ),
    157 => array(
        'type' => 'gangster',
        'clan' => 'triad',
        'stats' => array(
            1 => 0,
            2 => 1,
            3 => 1,
            4 => 0,
            5 => 1,
            6 => 0,
        ),
        'cost' =>7,
        'influence' =>2,
    ),
);


$this->resource_types = [
    901 => [
        'ability' => 'police_station',
        'influence' => 3,
        'name' => clienttranslate('Police station'),
        'cost' => [
            1 => 2,
            2 => 1,
            3 => 1,
            4 => 0,
            5 => 0,
            6 => 0,
        ],
    ],
    902 => [
        'ability' => 'black_market',
        'influence' => 5,
        'name' => clienttranslate('Black market'),
        'cost' => [
            1 => 0,
            2 => 1,
            3 => 0,
            4 => 1,
            5 => 1,
            6 => 0,
        ],
    ],
    903 => [
        'ability' => 'hq',
        'influence' => 2,
        'name' => clienttranslate('Headquarters'),
        'cost' => [
            1 => 0,
            2 => 1,
            3 => 1,
            4 => 0,
            5 => 1,
            6 => 0,
        ],
    ],
    904 => [
        'ability' => 'counterfeit_printing',
        'influence' => 3,
        'name' => clienttranslate('Counterfeit Printing'),
        'cost' => [
            1 => 0,
            2 => 1,
            3 => 0,
            4 => 0,
            5 => 2,
            6 => 0,
        ],
    ],
    905 => [
        'ability' => 'private_jet',
        'influence' => 4,
        'name' => clienttranslate('Private jet'),
        'cost' => [
            1 => 0,
            2 => 0,
            3 => 1,
            4 => 1,
            5 => 0,
            6 => 0,
        ],
    ],
    906 => [
        'ability' => 'media',
        'influence' => 0,
        'max_influence' => 7,
        'name' => clienttranslate('Media'),
        'cost' => [
            1 => 0,
            2 => 1,
            3 => 1,
            4 => 2,
            5 => 1,
            6 => 1,
        ],
    ],
    907 => [
        'ability' => 'high_tech_eq',
        'influence' => 4,
        'name' => clienttranslate('High tech equipment'),
        'cost' => [
            1 => 0,
            2 => 0,
            3 => 1,
            4 => 1,
            5 => 1,
            6 => 0,
        ],
    ],
    908 => [
        'ability' => 'private_society',
        'influence' => 3,
        'name' => clienttranslate('Secret society'),
        'cost' => [
            1 => 0,
            2 => 1,
            3 => 1,
            4 => 0,
            5 => 0,
            6 => 1,
        ],
    ],
    909 => [
        'ability' => 'bank',
        'influence' => 4,
        'name' => clienttranslate('Bank'),
        'cost' => [
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0,
            6 => 2,
        ],
    ],
    910 => [
        'ability' => 'bikers_gang',
        'influence' => 3,
        'name' => clienttranslate('Bikers gang'),
        'cost' => [
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 1,
            5 => 1,
            6 => 1,
        ],
    ],
    /*
    911 => [
        'ability' => 11,
        'influence' => 5,
        'name' => clienttranslate('Hospital'),
        'cost' => [
            1 => 0,
            2 => 1,
            3 => 0,
            4 => 1,
            5 => 0,
            6 => 1,
        ],
    ],
    */
    912 => [
        'ability' => 'indicator_network',
        'influence' => 2,
        'name' => clienttranslate('Indicator Network'),
        'cost' => [
            1 => 0,
            2 => 0,
            3 => 2,
            4 => 0,
            5 => 0,
            6 => 1,
        ],
    ],
];
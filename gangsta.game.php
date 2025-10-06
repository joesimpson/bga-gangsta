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
 * gangsta.game.php
 *
 * This is the main file for your game logic.
 *
 * In this PHP file, you are going to defines the rules of the game.
 *
 */

require_once (dirname(__FILE__) . '/modules/DebugTrait.php');

require_once(APP_GAMEMODULE_PATH . 'module/table/table.game.php');

const CARD_RESOURCE_LOCATION_DECK = 'deck_resources';
const CARD_RESOURCE_LOCATION_DRAFT = 'rc_draft';

//Pick 2 cards at setup
const RESOURCE_CARDS_PER_PLAYER = 2;

class Gangsta extends Table {
    use Bga\Games\gansgta\DebugTrait;
    
    private bool $isStartingDollarSetups;
    private bool $isPassCash;

    function __construct() {
        // Your global variables labels:
        //  Here, you can assign labels to global variables you are using for this game.
        //  You can use any number of global variables with IDs between 10 and 99.
        //  If your game has options (variants), you also have to associate here a label to
        //  the corresponding ID in gameoptions.inc.php.
        // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
        parent::__construct();

        self::initGameStateLabels([
                                      "firstPlayer" => 10,
                                      "activeSnitches" => 11,
                                      "activePhase" => 12,
                                      "lastSnitchID" => 13,
                                      "isReplayTurn" => 14,
                                      "lastPerformedHeist" => 15,
                                      "rewardParam" => 16,
                                      "turnCount" => 17,
                                      "nextPlayerId" => 18,
                                      "replayRewardId" => 19,
                                      "GDGWinner" => 20,
                                      "GDGStatus" => 21,
                                      "isDoubleGDG" => 22,
                                      "clanvariant" => 100,
                                      "passvariant" => 101,
                                      "publicvariant" => 102,
                                      "resourcevariant" => 103,
                                  ]);


        $this->cards = self::getNew("module.common.deck");
        $this->cards->init("card");
        $this->cards->autoreshuffle = true;
        $this->cards->autoreshuffle_custom = ['deckgangsters' => 'gdiscard'];

        $this->isClan = self::getUniqueValueFromDB('select count(*) from global where global_id=100 and global_value=2') == 1;
        $this->initializeMoneyOptions();
        $this->isPublic = self::getUniqueValueFromDB('select count(*) from global where global_id=102 and global_value=2') == 1;
        $this->isResources = self::getUniqueValueFromDB('select count(*) from global where global_id=103 and global_value=2') == 1;
    }

    private function initializeMoneyOptions() {
        if (!isset($this->isStartingDollarSetups)) {
            $moneyOptionValue = self::getUniqueValueFromDB('select global_value from global where global_id=101');
            $this->isPassCash = $moneyOptionValue == 2 || $moneyOptionValue == 4;
            $this->isStartingDollarSetups = $moneyOptionValue == 3 || $moneyOptionValue == 4;
        }
    }

    public function isStartingDollarSetups() {
        return self::getUniqueValueFromDB('select count(*) from global where global_id=101 and (global_value=3 OR global_value=4)') == 1;
    }
    
    function isVariantResourcesChoice() : bool
    { 
        $variant_value = self::getGameStateValue( 'resourcevariant', 1 );
        switch($variant_value){
            case 1: return FALSE;
            case 2: return TRUE;
            default: return FALSE;
        }
    }

    protected function getGameName() {
        // Used for translations and stuff. Please do not modify.
        return "gangsta";
    }

    /*
        setupNewGame:

        This method is called only once, when a new game is launched.
        In this method, you must setup the game according to the game rules, so that
        the game is ready to be played.
    */
    protected function setupNewGame($players,
                                    $options = []) {
        // Set the colors of the players with HTML color code
        // The default below is red/green/blue/orange/brown
        // The number of colors defined here must correspond to the maximum number of players allowed for the gams
        $gameinfos = self::getGameinfos();
        $default_colors = $gameinfos['player_colors'];

        // Create players
        // Note: if you added some extra field on "player" table in the database (dbmodel.sql), you can initialize it there.
        $sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES ";
        $values = [];
        foreach ($players as $player_id => $player) {
            $color = array_shift($default_colors);
            $values[] = "('" . $player_id . "','$color','" . $player['player_canal'] . "','" . addslashes($player['player_name']) . "','" . addslashes($player['player_avatar']) . "')";
        }
        $sql .= implode(',', $values);
        self::DbQuery($sql);
        self::reattributeColorsBasedOnPreferences($players, $gameinfos['player_colors']);
        self::reloadPlayersBasicInfos();

        /************ Start the game initialization *****/

        // Init global values with their initial values
        //self::setGameStateInitialValue( 'my_first_global_variable', 0 );

        // Init game statistics
        // (note: statistics used in this file must be defined in your stats.inc.php file)
        //self::initStat( 'table', 'table_teststat1', 0 );    // Init a table statistics
        //self::initStat( 'player', 'player_teststat1', 0 );  // Init a player statistics (for all players)
        self::initStat('player', 'gangsterRecruited', 0);
        self::initStat('player', 'scoreFromGangster', 0);
        self::initStat('player', 'heistPerformed', 0);
        self::initStat('player', 'scoreFromHeist', 0);
        self::initStat('player', 'gangsterUntapped', 0);
        self::initStat('player', 'gangsterFreeUntap', 0);
        self::initStat('player', 'moneyGained', 0);
        self::initStat('player', 'synchronizations', 0);
        self::initStat('player', 'passedForMoney', 0);
        self::initStat('player', 'lostToSnitch', 0);
        self::initStat('player', 'coopPerformed', 0);
        self::initStat('player', 'gangsterLost', 0);
        self::initStat('player', 'leaderComp', 0);
        self::initStat('player', 'mercComp', 0);
        self::initStat('player', 'infoComp', 0);
        self::initStat('table', 'snitches', 0);
        self::initStat('table', 'turns_number', 0);
        self::initStat('player', 'scoreMostGangster', 0);
        self::initStat('player', 'scoreMostMoney', 0);

        //self::incStat( 1, 'gangsterLost', $player_id );


        // TODO: setup the initial game situation here
        $deck_gangsters = [];
        $deck_genesis = [];
        $deck_gangwars = [];
        $deck_domination = [];
        $deck_boss = [];
        $deck_resources = [];

        foreach ($this->heist_genesis_type as $heist_type_id => $heist_type) {
            $deck_genesis[] = ['type' => $heist_type_id, 'type_arg' => 0, 'nbr' => 1, 'card_state' => 0];
        }

        foreach ($this->heist_gangwars_type as $heist_type_id => $heist_type) {
            $deck_gangwars[] = ['type' => $heist_type_id, 'type_arg' => 1, 'nbr' => 1, 'card_state' => 0];
        }

        foreach ($this->heist_domination_type as $heist_type_id => $heist_type) {
            $deck_domination[] = ['type' => $heist_type_id, 'type_arg' => 2, 'nbr' => 1, 'card_state' => 0];
        }

        foreach ($this->gangster_type as $gangster_type_id => $gangster_type) {
            if ($gangster_type['type'] == 'gangster') {
                $deck_gangsters[] = ['type' => $gangster_type_id, 'type_arg' => 0, 'nbr' => 1, 'card_state' => 0];
            } elseif ($gangster_type['type'] == 'boss') {
                $deck_boss[] = ['type' => $gangster_type_id, 'type_arg' => 1, 'nbr' => 1, 'card_state' => 0];
            }
        }

        foreach ($this->resource_types as $resource_type_id => $resource_type) {
            $deck_resources[] = ['type' => $resource_type_id, 'type_arg' => 5, 'nbr' => 1, 'card_state' => 0];
        }

        // Create all the decks.
        $this->cards->createCards($deck_genesis, 'deckgenesis');
        $this->cards->createCards($deck_gangwars, 'deckgangwars');
        $this->cards->createCards($deck_domination, 'deckdomination');
        $this->cards->createCards($deck_gangsters, 'deckgangsters');
        $this->cards->createCards($deck_boss, 'deckboss');
        $this->cards->createCards($deck_resources, CARD_RESOURCE_LOCATION_DECK);

        // Shuffle all the decks
        $this->cards->shuffle("deckgenesis");
        $this->cards->shuffle("deckgangwars");
        $this->cards->shuffle("deckdomination");
        $this->cards->shuffle("deckgangsters");
        $this->cards->shuffle("deckboss");
        $this->cards->shuffle(CARD_RESOURCE_LOCATION_DECK);

        // One Boss per player
        foreach ($players as $player_id => $player) {
            $bosscard = $this->cards->pickCard('deckboss', $player_id);
            $bossmoney = $this->gangster_type[$bosscard['type']]['cost'];

            if ($this->isStartingDollarSetups()) {
                $extra_money = self::getUniqueValueFromDB("SELECT player_no - 1 FROM player WHERE player_id='$player_id'");
            } else {
                $extra_money = 0;
            }

            $total_starting_money = $bossmoney + $extra_money;

            self::DbQuery("UPDATE player SET player_money='$total_starting_money' WHERE player_id='$player_id'");
            self::DbQuery("UPDATE card SET card_order=1 where card_location='hand'");
        }

        // 5 cards for the heist line
        $this->cards->pickCardsForLocation(5, 'deckgenesis', 'avheists');

        // 5 cards for the gangster line
        $this->cards->pickCardsForLocation(5, 'deckgangsters', 'avgangsters');

        // Activate first player (which is in general a good idea :) )
        if(!array_key_exists("DEBUG_SETUP",$options)){
            $first_player = $this->activeNextPlayer();
        }
        else {//In debug, we cannot change active player
            $first_player = $this->getActivePlayerId();
        }

        self::setGameStateInitialValue('firstPlayer', $first_player);
        self::setGameStateInitialValue('activeSnitches', 0);
        self::setGameStateInitialValue('activePhase', 0);
        self::setGameStateInitialValue('lastSnitchID', 0);
        self::setGameStateInitialValue('isReplayTurn', 0);
        self::setGameStateInitialValue('lastPerformedHeist', 0);
        self::setGameStateInitialValue('rewardParam', 0);
        self::setGameStateInitialValue('turnCount', 0);
        self::setGameStateInitialValue('nextPlayerId', 0);
        self::setGameStateInitialValue('replayRewardId', 0);
        self::setGameStateInitialValue('GDGWinner', 0);
        self::setGameStateInitialValue('GDGStatus', 0);
        /************ End of the game initialization *****/
    }

    /*
        getAllDatas:

        Gather all informations about current game situation (visible by the current player).

        The method is called each time the game interface is displayed to a player, ie:
        _ when the game starts
        _ when a player refreshes the game page (F5)
    */
    protected function getAllDatas(): array {
        $result = [];

        // Constants.
        $result['constants'] = $this->gameConstants;
        $result['skill_name'] = $this->skill_name;
        $result['skill_name_invariant'] = $this->skill_name_invariant;
        $result['skill_typeid'] = $this->skill_typeid;

        // $result['globals'] = self::getCollectionFromDB('SELECT * from global');
        // $result['stats'] = self::getCollectionFromDB('SELECT * from stats');
        // $result['mystats'] = array(
        //     'isClan' => $this->isClan,
        //     'isPassCash' => $this->isPassCash,
        // );

        $result['clan_variant'] = $this->isClan;
        $result['pass_variant'] = $this->isPassCash;
        $result['starting_dollars_variant'] = $this->isStartingDollarSetups;
        $result['public_variant'] = $this->isPublic;
        $result['resources_variant'] = self::getGameStateValue( 'resourcevariant', 1 );

        $current_player_id = self::getCurrentPlayerId();    // !! We must only return informations visible by this player !!

        $result['private_score'] = 0;
        if (!self::isSpectator() && !self::isCurrentPlayerZombie()) {
            $result['private_score'] = self::getUniqueValueFromDB("SELECT player_score FROM player WHERE player_id='$current_player_id'");
            $result['heist_score'] = self::getUniqueValueFromDB("SELECT player_score-public_score FROM player WHERE player_id='$current_player_id'");
        }

        // Get information about players
        // Note: you can retrieve some extra field you added for "player" table in "dbmodel.sql" if you need it.
        $sql = "SELECT player_id id, public_score score, player_score private_score, player_money money FROM player ";
        if ($this->isPublic) {
            $sql = "SELECT player_id id, player_score score, player_score private_score, player_money money FROM player ";
        }
        $result['players'] = self::getCollectionFromDb($sql);
        if (!self::isSpectator() && !self::isCurrentPlayerZombie()) {
            $result['players'][$current_player_id]['score'] = $result['private_score'];
        }

        //All Cards types
        $result['genesis_type'] = $this->heist_genesis_type;
        $result['gangwars_type'] = $this->heist_gangwars_type;
        $result['domination_type'] = $this->heist_domination_type;
        $result['gangster_type'] = $this->gangster_type;

        // Cards in locations.
        $result['avheists'] = $this->cards->getCardsInLocation('avheists');
        $result['avgangsters'] = $this->cards->getCardsInLocation('avgangsters');
        $result['activesnitch'] = $this->cards->getCardsInLocation('activesnitch');

        //$result['tableau'] = $this->cards->getCardsInLocation( 'hand' );
        $result['tableau'] = $this->getFullCardsInLocation('hand');

        $result['firstPlayer'] = self::getGameStateValue('firstPlayer');
        $result['activePhaseId'] = self::getGameStateValue('activePhase');
        $result['activePhaseName'] = $this->gameConstants['gamePhases'][self::getGameStateValue('activePhase')];
        $result['activeSnitches'] = self::getGameStateValue('activeSnitches');

        $result['counters'] = $this->getCounters();

        return $result;
    }

    /*
        getGameProgression:

        Compute and return the current game progression.
        The number returned must be an integer beween 0 (=the game just started) and
        100 (= the game is finished or almost finished).

        This method is called each time we are in a game state with the "updateGameProgression" property set to true
        (see states.inc.php)
    */
    function getGameProgression() {
        $count = $this->cards->countCardsByLocationArgs('hand');
        $max = 1;

        foreach ($count as $player_id => $pcount) {
            if ($pcount > $max) {
                $max = $pcount;
            }
        }
        $ret = ($max - 1) * 12;
        if ($max == 9) {
            $ret = 100;
        }

        return $ret;
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////

    /*
        In this space, you can put any utility methods useful for your game logic
    */
    function checkIfSameClan($playerId,
                             $gangsterclan) {
        //if($this->getGameStateValue('clanvariant') == 1){
        if ($this->isClan == false) {
            return false;
        }
        $boss = self::getObjectFromDB("SELECT card_id id, card_type 'type', card_type_arg type_arg from card WHERE card_type_arg=1 and card_location='hand' and card_location_arg=$playerId");
        $bossGang = $this->gangster_type[$boss['type']]['clan'];

        return $bossGang == $gangsterclan;
    }

    function getCounters() {
        $playerInfos = $this->getPlayersCompleteInfos();
        $cards = $this->getFullCardsInLocation('hand');
        $result = [];
        foreach ($playerInfos as $pid => $pinfo) {
            $result["panel_money_$pid"] = ["counter_name" => "panel_money_$pid", "counter_value" => $pinfo['player_money']];
            $result["playermoney_$pid"] = ["counter_name" => "playermoney_$pid", "counter_value" => $pinfo['player_money']];
            $result["skill_leader_$pid"] = ["counter_name" => "skill_leader_$pid", "counter_value" => 0];
            $result["skill_hacker_$pid"] = ["counter_name" => "skill_hacker_$pid", "counter_value" => 0];
            $result["skill_sniper_$pid"] = ["counter_name" => "skill_sniper_$pid", "counter_value" => 0];
            $result["skill_brawler_$pid"] = ["counter_name" => "skill_brawler_$pid", "counter_value" => 0];
            $result["skill_mercenary_$pid"] = ["counter_name" => "skill_mercenary_$pid", "counter_value" => 0];
            $result["skill_informant_$pid"] = ["counter_name" => "skill_informant_$pid", "counter_value" => 0];
            $result["panel_team_$pid"] = ["counter_name" => "panel_team_$pid", "counter_value" => 0];
            $result["panel_t_pts_$pid"] = ["counter_name" => "panel_t_pts_$pid", "counter_value" => $pinfo['public_score']];
            $result["family_triad_$pid"] = ["counter_name" => "family_triad_$pid", "counter_value" => 0];
            $result["family_bratva_$pid"] = ["counter_name" => "family_bratva_$pid", "counter_value" => 0];
            $result["family_mafia_$pid"] = ["counter_name" => "family_mafia_$pid", "counter_value" => 0];
            $result["family_cartel_$pid"] = ["counter_name" => "family_cartel_$pid", "counter_value" => 0];
            $result["family_gang_$pid"] = ["counter_name" => "family_gang_$pid", "counter_value" => 0];
            if ($this->isPublic) {
                $result["panel_h_pts_$pid"] = ["counter_name" => "panel_h_pts_$pid", "counter_value" => $pinfo['player_score'] - $pinfo['public_score']];
            }
        }

        foreach ($cards as $cid => $cinfo) {
            foreach ($this->skill_typeid as $sname => $sid) {
                $result["skill_{$sname}_{$cinfo['location_arg']}"]["counter_value"] += $this->gangster_type[$cinfo['type']]['stats'][$sid];
            }
            if ($cinfo['skill'] > 0) {
                $result["skill_{$this->skill_name_invariant[$cinfo['skill']]}_{$cinfo['location_arg']}"]["counter_value"] += 1;
            }
            $result["panel_team_{$cinfo['location_arg']}"]["counter_value"] += 1;
            $result["family_{$this->gangster_type[$cinfo['type']]['clan']}_{$cinfo['location_arg']}"]['counter_value'] += 1;
        }

        return $result;
    }

    function getPlayersCompleteInfos() {
        return self::getCollectionFromDB('SELECT player_id, player_name, player_score, public_score, player_no, player_money, player_snitch FROM player');
    }

    function getChapterDeckName() {
        return 'deck' . $this->gameConstants['gamePhases'][self::getGameStateValue('activePhase')];
    }

    function getRelevantHeistDeck() {
        $chapter = self::getGameStateValue('activePhase');
        $heistdeck = $this->heist_genesis_type;
        if ($chapter == 1) {
            $heistdeck = $this->heist_gangwars_type;
        }
        if ($chapter == 2) {
            $heistdeck = $this->heist_domination_type;
        }
        return $heistdeck;
    }

    function fillResourceCardsInfo(array &$cards){
        foreach($cards as $id => &$card){
            $card_type = $card['type'];
            $card['name'] = $this->resource_types[$card_type]['name'];
        }
    }
    function getFullCardInfo($cardid) { //returns an object with no key
        $fullCard = self::getObjectFromDB("SELECT card_id id, card_type 'type', card_type_arg type_arg, card_location location, card_location_arg location_arg, card_order 'order', card_state state, card_skills skill from card WHERE card_id='$cardid'");
        return $fullCard;
    }

    function getCardState($cardid) { //returns just the value
        $state = self::getUniqueValueFromDB("SELECT card_state from card WHERE card_id='$cardid'");
        return $state;
    }

    function getCardSkill($cardid) { //returns just the value
        $skill = self::getUniqueValueFromDB("SELECT card_state from card WHERE card_id='$cardid'");
        return $skill;
    }

    function getFullCardsInLocation($location) {
        $cardlist = self::getCollectionFromDB("SELECT card_id id, card_type 'type', card_type_arg type_arg, card_location location, card_location_arg location_arg, card_order 'order', card_state 'state', card_skills skill from card WHERE card_location='$location'");
        return $cardlist;
    }

    function getCardsForPlayer($player_id) { // return a phony array with the ID as key of the object.
        $cardlist = self::getCollectionFromDB("SELECT card_id id, card_type 'type', card_type_arg type_arg, card_location location, card_location_arg location_arg, card_order 'order', card_state 'state', card_skills skill from card WHERE card_location='hand' and card_location_arg=$player_id");
        return $cardlist;
    }

    function getGangsterCountForPlayer($player_id) {
        $count = self::getUniqueValueFromDB("SELECT count(*) as count FROM card WHERE card_location='hand' and card_location_arg=$player_id");
        return $count;
    }

    function getTappedGangstersForPlayer($player_id) {
        $gangsters = $this->getCardsForPlayer($player_id);
        $tapped = array_filter($gangsters, function ($item) {
            return $item['state'] > 0;
        });
        return $tapped;
    }

    function getCompetenceCount($player_id,
                                $competence,
                                $onlyUntapped) {
        $gangsters = $this->getCardsForPlayer($player_id);
        $untapped = array_filter($gangsters, function ($item) {
            return $item['state'] == 0;
        });
        if ($onlyUntapped == true) {
            $gangsters = $untapped;
        }
        $count = 0;
        foreach ($gangsters as $gid => $gCard) {
            $count += $this->gangster_type[$gCard['type']]['stats'][$competence]; //add the count from the stats
            if ($gCard['skill'] == $competence) {
                $count += 1; //adds it if it was added to the card.
            }
        }
        return $count;
    }

    function getGangsterWith2Skill($player_id) { //also not bosses
        $cardlist = $this->getFullCardsInLocation('hand');
        $result = [];
        $notmygangsters = $cardlist;
        if ($player_id) {
            $notmygangsters = array_filter($cardlist, function ($item) use
            (
                $player_id
            ) {
                return ($item['location_arg'] != $player_id) && ($item['type_arg'] == 0);
            });
        } else {
            $notmygangsters = array_filter($cardlist, function ($item) use
            (
                $player_id
            ) {
                return ($item['type_arg'] == 0);
            });
        }
        foreach ($notmygangsters as $cardid => $gCard) {
            $scount = 0 + $gCard['skill'];
            for ($i = 1; $i < 7; $i++) {
                $scount += $this->gangster_type[$gCard['type']]['stats'][$i];
            }
            if ($scount == 2) {
                $result[$cardid] = $gCard;
            }
        }
        return $result;
    }

    function getAllTeamSkill($player_id) { //return a 0 based skill array (with 0 skill as dummy skill)
        $skills = [0, 0, 0, 0, 0, 0, 0];

        $gangstersInTeam = getCardsForPlayer($player_id);

        foreach ($gangstersInTeam as $gid => $gCard) {
            for ($i = 1; $i < 7; $i++) {
                $skills[$i] += $this->gangster_type[$gCard['type']]['stats'][$i];
            }
            $skills[$gCard['skill']] += 1; //this increase dummy if they don't have any
        }
        $skills[0] = 0;
        return $skills;
    }

    function untapGangster($gangster_id,
                           $player_id) {
        $sql = "UPDATE card SET card_state=0 WHERE card_id='$gangster_id' and card_location_arg='$player_id'";
        self::DbQuery($sql);

        return self::DbAffectedRow();
    }

    function tapGangster($gangster_id,
                         $isTeam) {
        $value = 1;
        if ($isTeam) {
            $value = 2;
        }
        $sql = "UPDATE card SET card_state=$value WHERE card_id='$gangster_id'";
        self::DbQuery($sql);

        return self::DbAffectedRow();
    }

    function clearCrew($playerid) {
        $sql = "UPDATE card SET card_state=1 where card_state=2 and card_location_arg='$playerid'";
        self::DbQuery($sql);
    }

    function clearAllCrews() {
        $sql = "UPDATE card SET card_state=1 where card_state=2";
        self::DbQuery($sql);
    }

    function untapAllPlayerGangsters($player_id) {
        $sql = "UPDATE card SET card_state=0 WHERE card_location = 'hand' AND card_location_arg='$player_id'";
        self::DbQuery($sql);

        return self::DbAffectedRow();
    }

    function tapAllPlayerGangsters($player_id) {
        $sql = "UPDATE card SET card_state=1 WHERE card_location = 'hand' AND card_location_arg='$player_id'";
        self::DbQuery($sql);

        return self::DbAffectedRow();
    }

    function checkForSynchro($player_id) {
        $count = self::getUniqueValueFromDB("SELECT count(*) from card WHERE card_location = 'hand' AND card_location_arg='$player_id' AND card_state = 0");
        if ($count == 0) {
            return true;
        }

        return false;
    }

    function getTappedGangsterCount($player_id) {
        return self::getUniqueValueFromDB("SELECT count(*) from card WHERE card_location = 'hand' AND card_location_arg='$player_id' AND card_state > 0");
    }

    function getUntappedGangsterCount($player_id) {
        return self::getUniqueValueFromDB("SELECT count(*) from card WHERE card_location = 'hand' AND card_location_arg='$player_id' AND card_state = 0");
    }

    function getPlayerGangsterCount($player_id) {
        return self::getUniqueValueFromDB("SELECT count(*) from card WHERE card_location = 'hand' AND card_location_arg='$player_id'");
    }

    /*
    Card
    array(
        'id' => ..,          // the card ID
        'type' => ..,        // the card type
        'type_arg' => ..,    // the card type argument
        'location' => ..,    // the card location
        'location_arg' => .. // the card location argument
    );
    countCardsInLocations()
        array(
            'deck' => 12,
            'hand' => 21,
            'discard' => 54,
            'ontable' => 3
        );

    countCardsByLocationArgs( 'hand' );
        array(
            122345 => 5,    // player 122345 has 5 cards in hand
            123456 => 4     // and player 123456 has 4 cards in hand
        );
    */

//////////////////////////////////////////////////////////////////////////////
//////////// Player actions
////////////

    /*
        Each time a player is doing some game action, one of the methods below is called.
        (note: each method below must match an input method in gangsta.action.php)
    */

    /**
     * First user action : select a resource card
     * @param int $cardId
     */
    function actSelectResource(int $cardId){
        self::checkAction( 'actSelectResource' ); 
        self::trace("actSelectResource($cardId)");
        
        $card = $this->getFullCardInfo($cardId);
        $player_id = self::getCurrentPlayerId();
        $args = $this->argResourcesSelection();
        $selectableCards = array_keys($args['_private'][$player_id]['cards']);

        //ANTICHEAT :
        if ($card == null) {
            throw new \BgaVisibleSystemException("Invalid card");
        }
        if (!in_array($cardId,$selectableCards)) {
            throw new \BgaVisibleSystemException("This card is not available");
        }

        //TODO JSA MOVE RESOURCE CARD
        //TODO JSA NOTIFY

        // END PLAYER turn and go to next state when everyone is ready
        $this->gamestate->setPlayerNonMultiactive( $player_id, 'next');
    }

    function actionUntapGangsters($gangster_ids) {
        self::checkAction('untapGangsters');
        $player_id = self::getActivePlayerId();

        $availableMoney = self::getUniqueValueFromDB("SELECT player_money FROM player WHERE player_id='$player_id'");
        $leaders = self::getCompetenceCount($player_id, 1, true);
        $cost = count($gangster_ids) - $leaders;
        if ($leaders > 0) {
            self::incStat(min(count($gangster_ids), $leaders), 'gangsterFreeUntap', $player_id);
        }
        if ($cost < 0) {
            $cost = 0;
        }
        if ($availableMoney < $cost) {
            throw new BgaUserException(self::_("You don't have enough money for that"));
        }

        foreach ($gangster_ids as $gid) {
            if ($this->untapGangster($gid, $player_id) == 0) {
                throw new feException("trying to untap another player gangster");
            } else {
                self::incStat(1, 'gangsterUntapped', $player_id);
            }
        }

        if ($cost > 0) {
            self::dbQuery("UPDATE player SET player_money=player_money-$cost WHERE player_id='$player_id'");
        }

        self::notifyAllPlayers('mobilize',
                               clienttranslate('${player_name} makes ${count} gangster(s) available (cost: ${cost})'), [
                                   'player_id' => $player_id,
                                   'player_name' => self::getActivePlayerName(),
                                   'cost' => $cost,
                                   'count' => count($gangster_ids),
                                   'new_money' => $availableMoney - $cost,
                                   'gangsters' => $gangster_ids,
                               ]);

        $this->gamestate->nextState('playTurn');
    }

    function actionTapGangsters($gangster_ids) {
        self::checkAction('tap');
        $player_id = self::getActivePlayerId();

        $maxcount = self::getGameStateValue('rewardParam');

        if (count($gangster_ids) > $maxcount) {
            throw new BgaUserException(self::_("You are trying to divert too many gangsters"));
        }

        foreach ($gangster_ids as $gid) {
            $this->tapGangster($gid, false);
        }

        self::notifyAllPlayers('diversion',
                               clienttranslate('${player_name} creates a diversion affecting ${count} gangsters'), [
                                   'player_id' => $player_id,
                                   'player_name' => self::getActivePlayerName(),
                                   'count' => count($gangster_ids),
                                   'gangsters' => $gangster_ids,
                               ]);

        self::setGameStateValue('rewardParam', 0);

        $this->gamestate->nextState('checkPhase');
    }

    function actionKillGangster($gangster_id) {
        self::checkAction('kill');

        $player_id = self::getActivePlayerId();
        $players = $this->loadPlayersBasicInfos();

        //$targetPlayer_id = self::getGameStateValue('rewardParam');

        $gangster = $this->getFullCardInfo($gangster_id);

        if ($gangster == null) {
            throw new feException("This card does not exists");
        }
        if ($gangster['location'] != 'hand') {
            throw new feException("This gangster is not available");
        }
        if ($gangster['location_arg'] != $player_id) {
            throw new feException("It must be one of your gangsters");
        }

        $scount = 0 + $gangster['skill'];
        for ($i = 1; $i < 7; $i++) {
            $scount += $this->gangster_type[$gangster['type']]['stats'][$i];
        }
        if ($scount != 2) {
            throw new BgaUserException(self::_("You can only discard a gangster with exactly 2 skills who isn't a Boss"));
        }

        self::dbQuery("UPDATE card SET card_location='killed' WHERE card_id='$gangster_id'");

        $gscore = $this->gangster_type[$gangster['type']]['influence'];

        self::dbQuery("UPDATE player SET public_score=public_score-$gscore, player_score=player_score-$gscore WHERE player_id='$player_id'");

        self::notifyAllPlayers('kill', clienttranslate('${player_name} discards a gangster'), [
            'player_id' => $player_id,
            'player_name' => $players[$player_id]['player_name'],
            'score_loss' => $gscore,
            'gangster' => $gangster_id,
        ]);

        self::incStat(1, 'gangsterLost', $player_id);
        self::incStat(-1 * $gscore, 'scoreFromGangster', $player_id);
        $this->gamestate->nextState('goBackToActive');
    }

    function actionSnitchKill($gangster_id) {
        self::checkAction('snitchKill');

        $player_id = self::getCurrentPlayerId();
        $players = $this->loadPlayersBasicInfos();
        $gangster = $this->getFullCardInfo($gangster_id);


        if ($gangster == null) {
            throw new feException("This card does not exists");
        }
        if ($gangster['location'] != 'hand') {
            throw new feException("This gangster is not available");
        }
        if ($gangster['location_arg'] != $player_id) {
            throw new feException("It must be one of your gangsters");
        }

        $owner_id = $gangster['location_arg'];

        self::dbQuery("UPDATE card SET card_location='killed' WHERE card_id='$gangster_id'");

        $gscore = $this->gangster_type[$gangster['type']]['influence'];

        self::dbQuery("UPDATE player SET public_score=public_score-$gscore, player_score=player_score-$gscore WHERE player_id='$player_id'");

        self::notifyAllPlayers('kill', clienttranslate('${player_name} gangster was caught by the police'), [
            'player_id' => $owner_id,
            'player_name' => $players[$owner_id]['player_name'],
            'gangster' => $gangster_id,
            'score_loss' => $gscore,
        ]);

        self::DbQuery("UPDATE player SET player_snitch = 0 WHERE player_id = $player_id");

        self::incStat(1, 'gangsterLost', $owner_id);
        self::incStat(-1 * $gscore, 'scoreFromGangster', $owner_id);
        $this->gamestate->setPlayerNonMultiactive($player_id, "checkPhase");
    }

    function actionGdgKill($gangster_id) {
        self::checkAction('gdgKill');
        $isDoubleGDG = self::getGameStateValue('GDGStatus') == 2;
        $player_id = self::getCurrentPlayerId();
        $gangster = $this->getFullCardInfo($gangster_id);
        $players = $this->loadPlayersBasicInfos();

        if ($gangster == null) {
            throw new feException("This card does not exists");
        }
        if ($gangster['location'] != 'hand') {
            throw new feException("This gangster is not available");
        }
        if ($isDoubleGDG) {
            if ($gangster['location_arg'] == $player_id) {
                throw new feException("This gangster is not available");
            }
        } else {
            if ($gangster['location_arg'] != $player_id) {
                throw new feException("This gangster is not available");
            }
        }

        $owner_id = $gangster['location_arg'];

        self::dbQuery("UPDATE card SET card_location='killed' WHERE card_id='$gangster_id'");

        $gscore = $this->gangster_type[$gangster['type']]['influence'];

        self::dbQuery("UPDATE player SET public_score=public_score-$gscore, player_score=player_score-$gscore WHERE player_id='$owner_id'");

        if ($isDoubleGDG) {
            self::notifyAllPlayers('kill', clienttranslate('${player_name2} discards a gangster of ${player_name}'), [
                'player_id' => $owner_id,
                'player_name' => $players[$owner_id]['player_name'],
                'player_name2' => $players[$player_id]['player_name'],
                'gangster' => $gangster_id,
                'score_loss' => $gscore,
            ]);
        } else {
            self::notifyAllPlayers('kill', clienttranslate('${player_name} discards a gangster'), [
                'player_id' => $player_id,
                'player_name' => $players[$owner_id]['player_name'],
                'gangster' => $gangster_id,
                'score_loss' => $gscore,
            ]);
        }

        self::incStat(1, 'gangsterLost', $owner_id);
        self::incStat(-1 * $gscore, 'scoreFromGangster', $owner_id);
        $this->gamestate->setPlayerNonMultiactive($player_id, "startDomination");
    }

    function actionTeachGangster($gangster_id) {
        self::checkAction('teach');
        $player_id = self::getActivePlayerId();
        $gangster = $this->getFullCardInfo($gangster_id);

        if ($gangster == null) {
            throw new feException("This card does not exists");
        }
        if ($gangster['location'] != 'hand') {
            throw new feException("This gangster is not available");
        }

        if ($gangster['skill'] > 0) {
            throw new BgaUserException(clienttranslate("A gangster can only learn one additional skill"));
        }

        $skill_id = self::getGameStateValue('rewardParam');
        self::dbQuery("UPDATE card SET card_skills=$skill_id where card_id = $gangster_id");

        self::notifyAllPlayers('teach',
                               clienttranslate('${player_name} teaches ${skill_name} to one of their gangster'), [
                                   'i18n' => ['skill_name'],
                                   'player_id' => $player_id,
                                   'player_name' => self::getActivePlayerName(),
                                   'skill' => $skill_id,
                                   'skill_name' => $this->skill_name[$skill_id],
                                   'gangster' => $gangster_id,
                               ]);

        self::setGameStateValue('rewardParam', 0);
        self::setGameStateValue('lastPerformedHeist', 0);
        $this->gamestate->nextState('checkPhase');
    }


    function recruitGangster($gangster_id) {
        self::checkAction('recruitGangster');

        $player_id = self::getActivePlayerId();

        $gangster = $this->getFullCardInfo($gangster_id);

        if ($gangster == null) {
            throw new feException("This card does not exists");
        }
        if ($gangster['location'] != 'avgangsters') {
            throw new feException("This gangster is not available");
        }

        $cost = $this->gangster_type[$gangster['type']]['cost'];
        $score = $this->gangster_type[$gangster['type']]['influence'];
        $rebate = false;

        if ($this->checkIfSameClan($player_id, $this->gangster_type[$gangster['type']]['clan'])) {
            $cost = $cost - 1;
            $rebate = true;
        }

        //Check if you have enough money
        $old_values = self::getCollectionFromDB("SELECT player_id, player_score, public_score, player_money FROM player  WHERE player_id='$player_id'");
        $current_money = $old_values[$player_id]['player_money'];

        if ($cost > $current_money) {
            throw new feException(sprintf(self::_("You need %s <span class=\"money\" style=\"z-index:10\"></span> to pay this gangster"),
                                          $cost), true);
        }

        self::incStat(1, 'gangsterRecruited', $player_id);
        self::incStat($score, 'scoreFromGangster', $player_id);

        $sql = "UPDATE player SET player_score=player_score+$score, public_score=public_score+$score, player_money=player_money-$cost WHERE player_id='$player_id'";
        self::DbQuery($sql);

        $current_score = $old_values[$player_id]['public_score'] + $score;
        $team_score = $current_score;
        if ($this->isPublic) {
            $current_score = $old_values[$player_id]['player_score'] + $score;
        }

        $current_money -= $cost;

        // Okay, move it to player hand
        $this->cards->moveCard($gangster_id, 'hand', $player_id);
        $gansterorder = self::getUniqueValueFromDB("SELECT max(card_order) FROM card WHERE card_location = 'hand' AND card_location_arg='$player_id'") + 1;
        self::dbQuery("UPDATE card SET card_order =$gansterorder where card_id=$gangster_id");

        if ($rebate) {
            self::notifyAllPlayers('message',
                                   clienttranslate('${player_name} saves 1$ for recruiting from the same clan'), [
                                       'player_id' => $player_id,
                                       'player_name' => self::getActivePlayerName(),
                                   ]);
        }


        self::notifyAllPlayers('recruitGangster',
                               clienttranslate('${player_name} recruits and gains ${influence} influence'), [
                                   'player_id' => $player_id,
                                   'player_name' => self::getActivePlayerName(),
                                   'gangster_id' => $gangster_id,
                                   'gangster_type' => $gangster['type'],
                                   'cost' => $cost,
                                   'new_money' => $current_money,
                                   'influence' => $score,
                                   'new_influence' => $current_score,
                                   'team_score' => $team_score,
                                   'order' => $gansterorder,
                               ]);

        $heistScore = $old_values[$player_id]['player_score'] - $old_values[$player_id]['public_score'];
        if (!$this->isPublic) {
            self::notifyPlayer($player_id, 'scoreUpdate', "", [
                'player_id' => $player_id,
                'new_influence' => $old_values[$player_id]['player_score'] + $score,
                'heist_influence' => $heistScore,
                'gangster_influence' => $old_values[$player_id]['public_score'] + $score,
            ]);
        }
        // else{
        //     self::notifyAllPlayers( 'scoreUpdate', "",array(
        //         'player_id' => $player_id,
        //         'new_influence' => $old_values[$player_id]['player_score']+$score,
        //         'heist_influence' => $heistScore,
        //         'gangster_influence' => $old_values[$player_id]['public_score']+$score,
        //     ));
        // }
        $this->gamestate->nextState('checkPhase');
    }

    function passForMoney() {
        self::checkAction('pass');

        $player_id = self::getActivePlayerId();

        $gangstersInTeam = $this->getCardsForPlayer($player_id);
        self::incStat(1, 'passedForMoney', $player_id);
        $leaderComps = 0;
        foreach ($gangstersInTeam as $gid => $gCard) {
            if ($gCard['skill'] == 1) {
                $leaderComps += 1;
            }
            $leaderComps += $this->gangster_type[$gCard['type']]['stats'][1];
        }
        $current_money = self::getUniqueValueFromDB("SELECT player_money FROM player WHERE player_id='$player_id'");

        if ($leaderComps == 1 && $this->isPassCash) {
            $leaderComps = 2;
        }

        $current_money += $leaderComps;
        self::DbQuery("UPDATE player SET player_money='$current_money' WHERE player_id='$player_id'");

        self::notifyAllPlayers('pass', clienttranslate('${player_name} passes and receive $${money}'), [
            'player_id' => $player_id,
            'player_name' => self::getActivePlayerName(),
            'new_money' => $current_money,
            'money' => $leaderComps,
        ]);

        $this->gamestate->nextState('discard');
    }

    function performHeist($heist_id,
                          $gangster_ids) {
        self::checkAction('performHeist');
        $heistdeck = $this->getRelevantHeistDeck();

        $player_id = self::getActivePlayerId();

        $heistCard = $this->cards->getCard($heist_id);

        if ($heistCard == null) {
            throw new feException("This card does not exist");
        }
        if ($heistCard['location'] != 'avheists') {
            throw new feException("This heist is not available");
        }

        $heistType = $heistdeck[$heistCard['type']];
        $isCoopHeist = $heistType['reward']['coopcash'] > 0;

        //Get the sum of all the skills of recruited gangsters.
        // making this array 7 to be able to use 0 based index like the materials object for skill
        $availableSkills = [0, 0, 0, 0, 0, 0, 0];

        if ($isCoopHeist) {
            $gangsters = $this->getFullCardsInLocation('hand');
            $gangstersInTeam = array_filter($gangsters, function ($item) use
            (
                $gangster_ids
            ) {
                return $item['state'] == 0 && in_array($item['id'], $gangster_ids);
            });
        } else {
            $gangsters = $this->getCardsForPlayer($player_id);
            $gangstersInTeam = array_filter($gangsters, function ($item) use
            (
                $gangster_ids
            ) {
                return $item['state'] == 0 && in_array($item['id'], $gangster_ids);
            });
        }

        foreach ($gangstersInTeam as $gid => $gCard) {
            for ($i = 1; $i < 7; $i++) {
                $availableSkills[$i] += $this->gangster_type[$gCard['type']]['stats'][$i];
            }
            $availableSkills[$gCard['skill']] += 1; //this add 1 to dummy if they don't have an added skill
        }

        //Check if that's enough to perform the selected heist.
        $enoughSkillsToPerform = 1;
        foreach ($heistType['cost'] as $sId => $sValue) {
            if ($sValue > $availableSkills[$sId]) {
                $enoughSkillsToPerform = 0;
            }
        }

        if ($enoughSkillsToPerform == 0) {
            throw new BgaUserException(self::_("You do not have enough skill to perform this heist"));
        }

        self::incStat(1, 'heistPerformed', $player_id);

        $this->clearAllCrews();

        foreach ($gangster_ids as $gid) {
            $this->tapGangster($gid, true);
        }

        // Okay, move it to player performed section and pick a new heist.
        $this->cards->moveCard($heist_id, 'performed', $player_id);

        self::setGameStateValue('lastPerformedHeist', $heist_id);

        //Add Reward
        $workdone = self::applyRewards($player_id, $heistType, $heist_id, $gangster_ids);

        $this->gamestate->nextState($workdone);
    }

    function applyRewards($player_id,
                          $heistType,
                          $heist_id,
                          $gangster_ids) {
        $heistreward = $heistType['reward'];
        $score = 0;
        $money = 0;

        //apply cashreward
        if ($heistreward['cash'] > 0) {
            $money = $heistreward['cash'];
            self::incStat($money, 'moneyGained', $player_id);
        }

        if ($heistreward['influence'] > 0) {
            $score = $heistreward['influence'];
            self::incStat($score, 'scoreFromHeist', $player_id);
        }

        $sql = "UPDATE player SET player_score=player_score+$score, player_money=player_money+$money WHERE player_id='$player_id'";
        self::DbQuery($sql);

        $newvalues = self::getCollectionFromDB("SELECT player_id, player_score, public_score, player_money FROM player  WHERE player_id='$player_id'");

        $newInfluence = $newvalues[$player_id]['public_score'];
        $heist_score = 0;
        if ($this->isPublic) {
            $newInfluence = $newvalues[$player_id]['player_score'];
            $heist_score = $newvalues[$player_id]['player_score'] - $newvalues[$player_id]['public_score'];
        }

        //now send notification.
        self::notifyAllPlayers('gainReward',
                               clienttranslate('${player_name} performs ${heist_name} and receives $${money} and ${influence} influence'),
                               [
                                    'i18n'=>['heist_name'],
                                   'player_id' => $player_id,
                                   'heist_id' => $heist_id,
                                   'heist_name' => $heistType['name'],
                                   'player_name' => self::getActivePlayerName(),
                                   'influence' => $score,
                                   'new_influence' => $newInfluence,
                                   'heist_score' => $heist_score,
                                   'money' => $money,
                                   'new_money' => $newvalues[$player_id]['player_money'],
                                   'gangsters' => $gangster_ids,
                               ]);

        if (!$this->isPublic) {
            self::notifyPlayer($player_id, 'scoreUpdate', "", [
                'player_id' => $player_id,
                'new_influence' => $newvalues[$player_id]['player_score'],
                'heist_influence' => $newvalues[$player_id]['player_score'] - $newvalues[$player_id]['public_score'],
                'gangster_influence' => $newvalues[$player_id]['public_score'],
            ]);
        }


        if ($heistreward['coopcash'] > 0) {
            self::incStat(1, 'coopPerformed', $player_id);
            $coopReward = $heistreward['coopcash'];
            $allGangsters = $this->getFullCardsInLocation('hand');
            $playersInvolved = array_unique(array_map(function ($item) use
            (
                $allGangsters
            ) {
                return ($allGangsters[$item]['location_arg']);
            }, $gangster_ids));
            if (count($playersInvolved) > 1) {
                $players = $this->getPlayersCompleteInfos();
                foreach ($playersInvolved as $pid) {
                    self::incStat($coopReward, 'moneyGained', $pid);
                    self::DbQuery("UPDATE player SET player_money = player_money+$coopReward WHERE player_id = $pid");
                    self::notifyAllPlayers('gainCoop',
                                           clienttranslate('${player_name} gains $${money} as a cooperation reward'), [
                                               'player_id' => $pid,
                                               'player_name' => $players[$pid]['player_name'],
                                               'money' => $coopReward,
                                               'new_money' => $players[$pid]['player_money'] + $coopReward,
                                           ]);
                }
            }
        }

        // now handle special rewards.
        // skill
        $result = 'checkPhase';
        if (strlen($heistreward['skill']) > 1) {
            $targetCount = self::getUniqueValueFromDB("SELECT count(card_id) FROM card WHERE card_location_arg = '$player_id' AND card_skills = 0");
            if ($targetCount == 0) {
                self::notifyAllPlayers('noSkillTarget',
                                       clienttranslate('${player_name} gangster(s) cannot learn any new skill'), [
                                           'player_id' => $player_id,
                                           'player_name' => self::getActivePlayerName(),
                                       ]);
            } else {
                self::setGameStateValue('rewardParam', $this->skill_typeid[$heistreward['skill']]);
                $result = 'rewardSkill';
            }
        } elseif (array_key_exists('action', $heistreward)) {
            //playagain
            if ($heistreward['action']['replay'] > 0) {
                //probably needs a notification if it is skipped because double replay.
                $replay = self::getGameStateValue('isReplayTurn');
                if ($replay != $player_id) {
                    self::setGameStateValue('isReplayTurn', 0);
                }
                if ($replay < 1) {
                    self::setGameStateValue('replayRewardId', $player_id);
                    self::setGameStateValue('isReplayTurn', $player_id);
                    $result = 'replay';
                }
                if ($replay == $player_id) {
                    self::notifyAllPlayers('message', clienttranslate('${player_name} can\'t replay twice in a row'), [
                        'player_name' => self::getActivePlayerName(),
                        'player_id' => $player_id,
                    ]);
                }
            }
            //Rally
            if ($heistreward['action']['untap'] > 0) {
                $this->untapAllPlayerGangsters($player_id);
                self::notifyAllPlayers('rally', clienttranslate('${player_name} gangsters rally'), [
                    'player_id' => $player_id,
                    'player_name' => self::getActivePlayerName(),
                ]);
            }
            //fastrecruit
            if ($heistreward['action']['recruit'] > 0) {
                // check if any gangster can be bought
                // $result='rewardRecruit';
                // return $result;
                $avgangsters = $this->cards->getCardsInLocation('avgangsters');
                $money = (int)($newvalues[$player_id]['player_money']);
                $count = 0;
                foreach ($avgangsters as $gid => $gcard) {
                    if ($this->gangster_type[$gcard['type']]['cost'] <= $money) {
                        $count += 1;
                    }
                }
                if ($count > 0) {
                    $result = 'rewardRecruit';
                } else {
                    self::notifyAllPlayers('notEnoughMoney',
                                           clienttranslate('${player_name} cannot recruit a gangster'), [
                                               'player_id' => $player_id,
                                               'player_name' => self::getActivePlayerName(),
                                           ]);
                }
            }
            //Theft
            if ($heistreward['action']['steal'] > 0) {
                $playerWithMoney = self::getUniqueValueFromDB("select count(player_id) from player where player_money > 0 and player_id != $player_id");
                if ($playerWithMoney > 0) {
                    self::setGameStateValue('rewardParam', $heistreward['action']['steal']);
                    $result = 'rewardSteal';
                } else {
                    self::notifyAllPlayers('everyoneBroke', clienttranslate('Nobody has any money to steal'), [
                    ]);
                }
            }
            //Diversion
            if ($heistreward['action']['diversion'] > 0) {
                $gangsterToTap = self::getUniqueValueFromDB("select count(card_id) from card where card_state = 0 and card_location ='hand' and card_location_arg != '$player_id'");
                if ($gangsterToTap > 0) {
                    self::setGameStateValue('rewardParam', $heistreward['action']['diversion']);
                    $result = 'rewardTap';
                } else {
                    self::notifyAllPlayers('everyoneTapped',
                                           clienttranslate('There are no gangster left for ${player_name} to tap'), [
                                               'player_id' => $player_id,
                                               'player_name' => self::getActivePlayerName(),
                                           ]);
                }
            }
            //assassination
            if ($heistreward['action']['kill'] > 0) {
                $killable = $this->getGangsterWith2Skill($player_id);
                if (count($killable) > 0) {
                    $result = 'markForKill';
                } else {
                    self::notifyAllPlayers('nobodyKillable',
                                           clienttranslate('No gangster is eligible to be assassinated by ${player_name}'),
                                           [
                                               'player_id' => $player_id,
                                               'player_name' => self::getActivePlayerName(),
                                           ]);
                }
            }
        }
        return $result;
    }

    function skipDiscard() {
        self::checkAction('skipDiscard');

        $player_id = self::getActivePlayerId();

        self::notifyAllPlayers('skipDiscard', clienttranslate('${player_name} chooses not to discard'), [
            'player_id' => $player_id,
            'player_name' => self::getActivePlayerName(),
        ]);

        $this->gamestate->nextState('checkPhase');
    }

    function skip($forced) {
        self::checkAction('skip');

        self::setGameStateValue('rewardParam', 0);

        $player_id = self::getActivePlayerId();

        $message = clienttranslate('${player_name} chooses to skip');
        if ($forced) {
            $message = clienttranslate('${player_name} cannot apply the reward');
        }

        self::notifyAllPlayers('skip', $message, [
            'player_id' => $player_id,
            'player_name' => self::getActivePlayerName(),
        ]);

        $this->gamestate->nextState('skip');
    }

    function selectAssasinationTarget($target_id) {
        self::checkAction('markForKill');
        //make that other player active etc.
        $this->gamestate->nextState('markForKill');
    }

    function steal($target_id) {
        self::checkAction('steal');
        $player_id = self::getActivePlayerId();

        if (!($target_id > 0 && $target_id != $player_id)) {
            throw new feException("Invalid player");
        }

        $stealAmount = self::getGameStateValue('rewardParam');

        $sql = "SELECT player_id id, player_money money FROM player ";
        $players = self::getCollectionFromDb($sql);

        if (!array_key_exists($target_id, $players)) {
            throw new feException("Invalid player");
        }

        $targetMoney = $players[$target_id]['money'];
        if ($stealAmount > $targetMoney) {
            $stealAmount = $targetMoney;
        }

        self::incStat($stealAmount, 'moneyGained', $player_id);

        self::DbQuery("UPDATE player SET player_money=player_money+'$stealAmount' WHERE player_id='$player_id'");
        self::DbQuery("UPDATE player SET player_money=player_money-'$stealAmount' WHERE player_id='$target_id'");

        $new_amount_player = self::getUniqueValueFromDB("SELECT player_money FROM player WHERE player_id='$player_id'");
        $new_amount_target = self::getUniqueValueFromDB("SELECT player_money FROM player WHERE player_id='$target_id'");
        self::setGameStateValue('rewardParam', 0);

        self::notifyAllPlayers('stealMoney', clienttranslate('${player_name} steals ${amount} from ${player_name2}'), [
            'player_id' => $player_id,
            'target_id' => $target_id,
            'player_name' => self::getActivePlayerName(),
            'player_name2' => self::getPlayerNameById($target_id),
            'amount' => $stealAmount,
            'new_amount_player' => $new_amount_player,
            'new_amount_target' => $new_amount_target,
        ]);

        $this->gamestate->nextState('checkPhase');
    }

    function actionMark($target_id) {
        self::checkAction('markForKill');
        self::notifyAllPlayers('message',
                               clienttranslate('${player_name} has chosen ${player_name2} as target of their assassination reward'),
                               [
                                   'player_name' => self::getActivePlayerName(),
                                   'player_name2' => self::getPlayerNameById($target_id),
                               ]);
        self::setGameStateValue('rewardParam', 0);
        self::setGameStateValue('nextPlayerId', $target_id);
        $this->gamestate->nextState('markForKill');
    }

    function discard($gangster_id,
                     $heist_id) {
        self::checkAction('discard');
        //$heistdeck = $this->getRelevantHeistDeck();
        $player_id = self::getActivePlayerId();

        if ($gangster_id > 0) { //gangster was discarded
            $Card = $this->cards->getCard($gangster_id);
            if ($Card == null) {
                throw new feException("This card does not exist");
            }
            if ($Card['location'] != 'avgangsters') {
                throw new feException("This gangster is not available");
            }

            $this->cards->moveCard($gangster_id, 'gdiscard');

            self::notifyAllPlayers('discardGangster', clienttranslate('${player_name} discards a gangster'), [
                'gangster_id' => $gangster_id,
                'player_id' => $player_id,
                'player_name' => self::getActivePlayerName(),
            ]);
        } elseif ($heist_id > 0) { //heist was discarded
            $Card = $this->cards->getCard($heist_id);
            if ($Card == null) {
                throw new feException("This card does not exist");
            }
            if ($Card['location'] != 'avheists') {
                throw new feException("This heist is not available");
            }

            $this->cards->moveCard($heist_id, 'hdiscard', self::getGameStateValue('activePhase'));


            self::notifyAllPlayers('discardHeist', clienttranslate('${player_name} discards a heist'), [
                'heist_id' => $heist_id,
                'player_id' => $player_id,
                'player_name' => self::getActivePlayerName(),
            ]);
        } else {
            throw new feException("Tried to actively discard nothing.");
        }

        $this->gamestate->nextState('checkPhase');
    }
//////////////////////////////////////////////////////////////////////////////
//////////// Game state arguments
////////////

    /*
        Here, you can create methods defined as "game state arguments" (see "args" property in states.inc.php).
        These methods function is to return some additional information that is specific to the current
        game state.
    */
            
    public function argResourcesSelection()
    { 
        $privateDatas = array ();
        $players = $players = self::loadPlayersBasicInfos();

        foreach($players as $player_id => $player){
            $cardsInfo = $this->cards->getCardsInLocation(CARD_RESOURCE_LOCATION_DRAFT,$player_id);
            $this->fillResourceCardsInfo($cardsInfo);
            $privateDatas[$player_id] = [
                'cards' => $cardsInfo,
            ];
        }

        $args = [
            '_private' => $privateDatas,
        ];
        return $args;
    }

    function argPlayerMobilize() {
        $player_id = self::getActivePlayerId();
        $leaders = $this->getCompetenceCount($player_id, 1, true); //get leader count for untapped gangsters
        $money = self::getUniqueValueFromDB("SELECT player_money FROM player WHERE player_id='$player_id'");
        $tapped = $this->getTappedGangstersForPlayer($player_id);
        return [
            'player_id' => $player_id,
            'leader' => $leaders,
            'money' => $money,
            'tapped' => $tapped,
        ];
    }

    function argRewardTap() {
        $amount = self::getGameStateValue('rewardParam');
        $player_id = self::getActivePlayerId();
        $allgangster = $this->getFullCardsInLocation('hand');
        $tappedGangsters = array_filter($allgangster, function ($item) use
        (
            $player_id
        ) {
            return ($item['location_arg'] != $player_id) && ($item['state'] == 0);
        });
        return [
            'amount' => $amount,
            'selectable' => $tappedGangsters,
        ];
    }

    function argRewardKill() {
        $player_id = self::getActivePlayerId();
        $killable = $this->getGangsterWith2Skill(null);
        $justMine = array_filter($killable, function ($item) use
        (
            $player_id
        ) {
            return ($item['location_arg'] == $player_id);
        });

        return ['selectable' => $justMine];
    }

    function argRewardMark() {
        $player_id = self::getActivePlayerId();
        $killable = [];
        foreach ($this->getGangsterWith2Skill($player_id) as $gid => $ginfo) {
            $killable[] = $ginfo['location_arg'];
        }
        $selectable = array_unique($killable);

        return ['selectable' => $selectable];
    }

    function argRewardSkill() {
        $player_id = self::getActivePlayerId();
        $skill = self::getGameStateValue('rewardParam');
        $allgangsters = $this->getCardsForPlayer($player_id);
        $possibleGangsters = array_filter($allgangsters, function ($item) {
            return ($item['skill'] == 0 && $item['state'] == 2);
        });
        if (count($possibleGangsters) == 0) {
            $possibleGangsters = array_filter($allgangsters, function ($item) {
                return ($item['skill'] == 0 && $item['state'] > 0);
            });
        }

        if ($skill == 1) {
            $allgangsters = $possibleGangsters;
            $possibleGangsters = array_filter($allgangsters, function ($item) {
                return $this->gangster_type[$item['type']]['stats'][1] == 0;
            });
        }

        return [
            'skill_id' => $skill,
            'skill_name' => $this->skill_name[$skill],
            'selectable' => $possibleGangsters,
        ];
    }

    function argRewardSteal() {
        $amount = self::getGameStateValue('rewardParam');
        $playermoney = self::getCollectionFromDB("SELECT player_id 'id', player_name 'name', player_money 'money' FROM player");

        return [
            'amount' => $amount,
            'player_money' => $playermoney,
        ];
    }

    function argSnitchInit() {
        $playersnitch = self::getCollectionFromDB("SELECT player_id, player_snitch FROM player WHERE player_snitch != 0");

        return [
            'snitch_effect' => $playersnitch,
        ];
    }

    function argGdgMulti() {
        $isDouble = (2 == self::getGameStateValue('GDGStatus'));

        return ['is_double' => $isDouble];
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Game state actions
////////////

    /*
        Here, you can create methods defined as "game state actions" (see "action" property in states.inc.php).
        The action method of state X is called everytime the current game state is set to X.
    */

    /*

    Example for game state "MyGameState":

    function stMyGameState()
    {
        // Do some stuff ...

        // (very often) go to another gamestate
        $this->gamestate->nextState( 'some_gamestate_transition' );
    }
    */

    public function stResourcesSetup()
    { 
        self::trace("stResourcesSetup()");

        if($this->isVariantResourcesChoice()){
            $players = $this->loadPlayersBasicInfos();
            foreach ($players as $pid => $player) {
                $drawnCards = $this->cards->pickCardsForLocation(RESOURCE_CARDS_PER_PLAYER,CARD_RESOURCE_LOCATION_DECK,CARD_RESOURCE_LOCATION_DRAFT,$pid);
                //$this->notify->player($pid,'draftCards',clienttranslate('${player_name} draws 2 resource cards'),[
                //    'player_name' => $player['player_name'],
                //    'cards'=> $drawnCards,
                //]);
            }

            $this->gamestate->setAllPlayersMultiactive(); 
            foreach ($players as $pid => $player) {
                self::giveExtraTime($pid);
            }
            $this->gamestate->nextState('draftMulti');
            return;
        }

        $this->gamestate->nextState('next');
    }

    function stNextPlayer() {
        $player_id = self::getGameStateValue('replayRewardId');
        if ($player_id > 0) {
            self::setGameStateValue('replayRewardId', 0);
            self::notifyAllPlayers('message', clienttranslate('${player_name} gets to play again'), [
                'player_name' => self::getActivePlayerName(),
                'player_id' => $player_id,
            ]);
        } else {
            self::setGameStateValue('isReplayTurn', 0);
            self::incStat(1, 'turns_number');
            $player_id = self::activeNextPlayer();
        }

        self::giveExtraTime($player_id);

        $this->gamestate->nextState('nextPlayer');
    }

    function stCheckSynchro() {
        if (self::isCurrentPlayerZombie()) {
            $this->gamestate->nextState("zombiePass");
            //TODO RETURN ?
        }

        $player_id = self::getActivePlayerId();

        $nextState = 'mobilize';

        if ($this->checkForSynchro($player_id)) {
            self::incStat(1, 'synchronizations', $player_id);
            $this->untapAllPlayerGangsters($player_id);
            self::notifyAllPlayers('synchronize', clienttranslate('${player_name} gang is synchronized!'), [
                'player_name' => self::getActivePlayerName(),
                'player_id' => $player_id,
            ]);
            $nextState = 'playerAction';
        } else {
            $count = $this->getTappedGangsterCount($player_id);
            if ($count == 0) {
                $nextState = 'playerAction';
            } else {
                $leaders = $this->getCompetenceCount($player_id, 1, true); //get leader count for untapped gangsters
                $money = self::getUniqueValueFromDB("SELECT player_money FROM player WHERE player_id='$player_id'");
                if ($leaders == 0 && $money == 0) {
                    $nextState = 'playerAction';
                    self::notifyAllPlayers('message', clienttranslate('${player_name} cannot untap any gangster'), [
                        'player_name' => self::getActivePlayerName(),
                        'player_id' => $player_id,
                    ]);
                }
            }
        }
        $this->gamestate->nextState($nextState);
    }

    function stRevealCards() {
        $snitch = false;
        $new_gangster = ['id' => 0];
        $new_heist = ['id' => 0];

        // Place a new gangster on available gangsters
        // count cards in location gangster, shouldn't ever be more than one
        $count = $this->cards->countCardInLocation('avgangsters');
        if ($count < 5) {
            if ($this->cards->countCardInLocation('deckgangsters') < 1) {
                $this->cards->moveAllCardsInLocation('gdiscard', 'deckgangsters');
                $this->cards->shuffle('deckgangsters');
            }
            $new_gangster = $this->cards->pickCardForLocation('deckgangsters', 'avgangsters');
        }

        // count cards in location heists, shouldn't ever be more than one.
        $count = $this->cards->countCardInLocation('avheists');
        if ($count < 5) {
            $deckPhase = 'deck' . $this->gameConstants['gamePhases'][self::getGameStateValue('activePhase')];
            if ($this->cards->countCardInLocation($deckPhase) < 1) {
                $this->cards->moveAllCardsInLocation('hdiscard', $deckPhase, self::getGameStateValue('activePhase'));
                $this->cards->shuffle($deckPhase);
            }
            $new_heist = $this->cards->pickCardForLocation($deckPhase, 'avheists');

            $heistdeck = $this->getRelevantHeistDeck();
            if ($heistdeck[$new_heist['type']]['type'] === "snitch") {
                $snitch = true;
                self::setGameStateValue('lastSnitchID', $new_heist['id']);
            }
        }

        self::notifyAllPlayers('endOfTurn', "", [
            'new_avheist' => $new_heist,
            'new_avgang' => $new_gangster,
        ]);

        if ($snitch) {
            self::incStat(1, 'snitches');
            $this->gamestate->nextState('snitch');
        } else {
            $this->gamestate->nextState('endTurn');
        }
    }

    function stCheckPhase() {
        $player_id = self::getActivePlayerId();

        // need to check if there is a phase change or End game
        $recruited = $this->cards->countCardsByLocationArgs('hand');
        $maxcount = 0;

        foreach ($recruited as $player_id => $count) {
            if ($count > $maxcount) {
                $maxcount = $count;
            }
        }
        $currentphase = self::getGameStateValue('activePhase');

        $snitches = self::getGameStateValue('activeSnitches');
        if ($currentphase == 2 && $snitches == 3) {
            $this->gamestate->nextState('gameEnd');
        }
        //criteria for game end: 9 gangsters or 3 snitches
        else if ($maxcount == 9 && ($currentphase == 2)) {
            $this->gamestate->nextState('gameEnd');
        } elseif ($maxcount == 7 && $currentphase == 1) {
            //criteria for Domination: 7 gangsters
            self::setGameStateValue('nextPlayerId', self::getActivePlayerId());
            $this->gamestate->nextState('startDomination');
        } elseif ($maxcount == 4 && $currentphase == 0) {
            //criteria for GDG: 4 gangsters
            $this->gamestate->nextState('startGDG');
        } else {
            //let's just reveal new cards.
            $this->gamestate->nextState('revealCards');
        }
    }

    function stStartGDG() {
        //compute additional revenue (additional cash for players with lower skill count)
        $players = $this->loadPlayersBasicInfos();
        $maxskills = 0;
        $skillCount = [];
        foreach ($players as $pid => $pinfo) {
            $skillCount[$pid] = 0;
            $gangstersInTeam = $this->getCardsForPlayer($pid);
            foreach ($gangstersInTeam as $gid => $gCard) {
                for ($i = 1; $i < 7; $i++) {
                    $skillCount[$pid] += $this->gangster_type[$gCard['type']]['stats'][$i];
                }
                if ($gCard['skill'] > 0) {
                    $skillCount[$pid] += 1;
                }
            }
            if ($skillCount[$pid] > $maxskills) {
                $maxskills = $skillCount[$pid];
            }
        }
        $table = [["", 
                [ 'str' => clienttranslate("Gangster Skills"), 'args' => [] ], 
                [ 'str' => clienttranslate("Bonus Cash"), 'args' => [] ],
            ]];
        //send cash to each player who has less than the max, giving them the difference
        foreach ($players as $pid => $pinfo) {
            $bonuscash = $maxskills - $skillCount[$pid];
            if ($bonuscash > 0) {
                $current_money = self::getUniqueValueFromDB("SELECT player_money FROM player WHERE player_id='$pid'");
                self::incStat($bonuscash, 'moneyGained', $pid);
                self::DbQuery("UPDATE player SET player_money=player_money+'$bonuscash' WHERE player_id='$pid'");
                self::notifyAllPlayers('receiveGDGRevenue',
                                       clienttranslate('${player_name} receives $${money} as opportunity income'), [
                                           'skillcount' => $skillCount,
                                           'max' => $maxskills,
                                           'player_id' => $pid,
                                           'player_name' => $pinfo['player_name'],
                                           'money' => $bonuscash,
                                           'new_money' => $current_money + $bonuscash,
                                       ]);
            } else {
                $bonuscash = 0;
            }
            $table[] = [['str' => '${player_name}', 'args' => ['player_name' => $pinfo['player_name']], 'type' => 'header'],
                        $skillCount[$pid], $bonuscash];
        }

        //change avdecks
        $this->cards->moveAllCardsInLocation('avheists', 'deckgenesis');
        $newcards = 0;
        while ($newcards < 5) {
            $card = $this->cards->pickCardForLocation('deckgangwars', 'avheists');
            if ($this->heist_gangwars_type[$card['type']]['type'] === "snitch") {
                $this->cards->moveCard($card['id'], 'phasediscard');
            } else {
                $newcards += 1;
            }
        }
        $this->cards->moveAllCardsInLocation('phasediscard', 'deckgangwars');
        $this->cards->shuffle('deckgangwars');

        $newphase = self::incGameStateValue('activePhase', 1);
        self::notifyAllPlayers('changeChapter', clienttranslate('A new chapter has started, ${chaptername}'), [
            'i18n' => ['chaptername'],
            'chaptername' => $this->Phase_Names['gangwars'],
            'phase' => $newphase,
            'heists' => $this->cards->getCardsInLocation('avheists'),
        ]);

        self::notifyAllPlayers('tableWindow', '', [
            "id" => 'genesisRevenue',
            "title" => clienttranslate('Chapter has ended'),
            "table" => $table,
            "closing" => clienttranslate('Close'),
        ]);

        $this->gamestate->nextState('nextPlayer');
    }

    function stStartDomination() {
        $player_id = self::getGameStateValue('nextPlayerId');
        self::setGameStateValue('nextPlayerId', 0);
        $this->gamestate->changeActivePlayer($player_id);
        $this->cards->moveAllCardsInLocation('avheists', 'deckgangwars');
        $newcards = 0;
        while ($newcards < 5) {
            $card = $this->cards->pickCardForLocation('deckdomination', 'avheists');
            if ($this->heist_domination_type[$card['type']]['type'] == 'snitch') {
                $this->cards->moveCard($card['id'], 'phasediscard');
            } else {
                $newcards += 1;
            }
        }
        $this->cards->moveAllCardsInLocation('phasediscard', 'deckdomination');
        $this->cards->shuffle('deckdomination');

        $newphase = self::incGameStateValue('activePhase', 1);
        self::notifyAllPlayers('changeChapter', clienttranslate('A new chapter has started, ${chaptername}'), [
            'i18n' => ['chaptername'],
            'chaptername' => $this->Phase_Names['domination'],
            'phase' => $newphase,
            'heists' => $this->cards->getCardsInLocation('avheists'),
        ]);

        $this->cards->moveAllCardsInLocation('activesnitch', 'hdiscard');
        self::setGameStateValue('activeSnitches', 0);

        $this->gamestate->nextState('nextPlayer');
    }

    function stDiscoverSnitch() {
        //ohlala we discovered a snitch, compute what happens.
        $heistdeck = $this->getRelevantHeistDeck();
        //$chapter = self::getGameStateValue('activePhase');
        $snitch_id = self::getGameStateValue('lastSnitchID');
        $snitch = $this->cards->getCard($snitch_id);
        self::incGameStateValue('activeSnitches', 1);

        self::notifyAllPlayers('snitchRevealed', clienttranslate('A snitch is revealed'), [
            'snitch_id' => $snitch['id'],
            'snitch_type' => $snitch['type'],
        ]);

        $this->cards->moveCard($snitch['id'], 'activesnitch');

        //for each player
        //count how many informant
        //snitch effect - informant = how many gangster to tap
        //if not enough gangsters, do the rest with money
        //if not enough money that player need to discard
        //then send them to next end turn
        $snitchType = $heistdeck[$snitch['type']];
        $snitchValue = $snitchType['cost'][3];
        $players = $this->getPlayersCompleteInfos();
        $dealtWith = true;
        foreach ($players as $pid => $pinfo) {
            $skillCount = $this->getCompetenceCount($pid, 3, false);
            $difference = $snitchValue - $skillCount;
            if ($difference > 0) { //player doesn't have enough Informants
                $deduction = $difference;
                if ($pinfo['player_money'] < $deduction) { //player doesn't have nough money to compensate
                    self::DbQuery("UPDATE player SET player_money = 0  WHERE player_id = $pid");
                    //ok this guy must kill a gangster
                    self::notifyAllPlayers('snitchHandling',
                                           clienttranslate('${player_name} loses $${amount} to deal with the snitch but it\'s not enough'),
                                           [
                                               'snitch_id' => $snitch['id'],
                                               'player_name' => $pinfo['player_name'],
                                               'player_id' => $pinfo['player_id'],
                                               'amount' => $pinfo['player_money'],
                                               'new_value' => 0,
                                           ]);
                    if (self::getUniqueValueFromDB("SELECT count(*) from player join card on player.player_id = card.card_location_arg where player_id = $pid and card_location='hand'") > 1) {
                        self::DbQuery("UPDATE player SET player_snitch = -1 WHERE player_id = $pid");
                        $dealtWith = false;
                    }
                    self::incStat($pinfo['player_money'], 'lostToSnitch', $pid);
                } else { //player has enough money
                    $v = $pinfo['player_money'] - $deduction;
                    self::DbQuery("UPDATE player SET player_money = player_money - $deduction  WHERE player_id = $pid");
                    self::notifyAllPlayers('snitchHandling',
                                           clienttranslate('${player_name} loses $${amount} to deal with the snitch'), [
                                               'snitch_id' => $snitch['id'],
                                               'player_name' => $pinfo['player_name'],
                                               'player_id' => $pid,
                                               'amount' => $deduction,
                                               'new_value' => $v,
                                           ]);
                    self::incStat($deduction, 'lostToSnitch', $pid);
                }
            } else {
                //player is unaffected because of informant skill
                self::notifyAllPlayers('message',
                                       clienttranslate('${player_name} has enough informants and is unaffected by the snitch'),
                                       [
                                           'player_name' => $pinfo['player_name'],
                                           'player_id' => $pid,
                                       ]);
            }
        }
        if ($dealtWith) {
            $this->cards->moveCard($snitch['id'], 'activesnitch');
            $this->setGameStateValue('lastSnitchID', 0);

            $this->gamestate->nextState('checkPhase');
        } else {
            $this->gamestate->nextState('snitch');
        }
    }

    function stSwitchToKiller() {
        $player_id = self::getGameStateValue('nextPlayerId');
        $current_player = self::getActivePlayerId();
        self::setGameStateValue('nextPlayerId', $current_player);
        $this->gamestate->changeActivePlayer($player_id);
        $this->gamestate->nextState("selectKiller");
    }

    function stGoBackToActive() {
        $old_active_id = self::getGameStateValue("nextPlayerId");
        if ($old_active_id > 0) {
            $this->gamestate->changeActivePlayer($old_active_id);
            self::setGameStateValue("nextPlayerId", 0);
        }
        $this->gamestate->nextState("checkPhase");
    }

    function stSnitchInit() {
        $sql = "SELECT player_id FROM player WHERE player_snitch != 0";
        $activeplayers = self::getObjectListFromDB($sql, true);

        $this->gamestate->setPlayersMultiactive($activeplayers, "checkPhase", true);
    }

    function stComputeGDG() {
        $p_infos = $this->loadPlayersBasicInfos();
        $allGangsters = $this->getFullCardsInLocation('hand');
        $maxskills = 0;
        $skillCount = [];
        $isTie = false;
        $is2players = $this->getPlayersNumber() == 2;
        $isDouble = false;
        $table = [["", [ 'str' => clienttranslate("Mercenary Skill"), 'args' => [] ]]];

        foreach ($allGangsters as $gid => $ginfo) {
            if (!isset($skillCount[$ginfo['location_arg']])) {
                $skillCount[$ginfo['location_arg']] = 0;
            }
            $skillCount[$ginfo['location_arg']] += $this->gangster_type[$ginfo['type']]['stats'][$this->skill_typeid['mercenary']];
            if ($ginfo['skill'] == $this->skill_typeid['mercenary']) {
                $skillCount[$ginfo['location_arg']] += 1;
            }
        }
        $mid = 0;
        foreach ($skillCount as $sid => $sinfo) {
            if ($sinfo > $maxskills) {
                if ($is2players) {
                    $isDouble = $sinfo >= $maxskills * 2;
                }
                $maxskills = $sinfo;
                $mid = $sid;
                $isTie = false;
            } else {
                if ($sinfo == $maxskills) {
                    $isTie = true;
                }
                if ($is2players) {
                    $isDouble = $maxskills >= $sinfo * 2;
                }
            }
            $table[] = [['str' => '${player_name}', 'args' => ['player_name' => $p_infos[$sid]['player_name']], 'type' => 'header'],
                        $sinfo];
        }
        self::setGameStateValue('GDGWinner', $mid);

        if ($isTie == true) {
            self::setGameStateValue('GDGStatus', 1);
        } else {
            if ($is2players && $isDouble) {
                self::setGameStateValue('GDGStatus', 2);
            }
        }

        self::notifyAllPlayers('tableWindow', '', [
            "id" => 'gangwarScoring',
            "title" => clienttranslate('Chapter has ended'),
            "header" => clienttranslate('The gangs have gone at war with each other, everyone <br>will discard a gangster except the player with <br>the most mercernary skill (if there is one)'),
            "table" => $table,
            "closing" => clienttranslate('Close'),
        ]);


        $this->gamestate->nextState("GDGMulti");
    }

    function stGdgMulti() {
        $gdgWinner = self::getGameStateValue('GDGWinner');
        $gdgStatus = self::getGameStateValue('GDGStatus');
        $isTie = $gdgStatus == 1;
        $isDouble = $gdgStatus == 2;

        if ($isTie == true) {
            $this->gamestate->setAllPlayersMultiactive();
            self::notifyAllPlayers('gdgEvent',
                                   clienttranslate('There is a tie for most mercenary skill, everyone must discard a gangster.'),
                                   [
                                   ]);
        } else {
            $pname = $this->loadPlayersBasicInfos();
            $players = [];
            if ($isDouble) {
                self::notifyAllPlayers('gdgEvent',
                                       clienttranslate('${player_name} has double the Mercernary skill, they will choose the discarded gangster.'),
                                       [
                                           'player_name' => $pname[$gdgWinner]['player_name'],
                                           'player_id' => $gdgWinner,
                                       ]);
                array_push($players, $gdgWinner);
            } else {
                self::notifyAllPlayers('gdgEvent',
                                       clienttranslate('${player_name} has the most Mercenary, everyone else must discard a gangster.'),
                                       [
                                           'player_name' => $pname[$gdgWinner]['player_name'],
                                           'player_id' => $gdgWinner,
                                       ]);
                foreach ($pname as $pid => $scount) {
                    if ($pid != $gdgWinner) {
                        if (self::getGangsterCountForPlayer($pid) > 1) {
                            array_push($players, $pid);
                        }
                    }
                }
            }
            $this->gamestate->setPlayersMultiactive($players, "startDomination", true);
        }
    }

    function stEndOfGame() {
        $allGangsters = $this->getFullCardsInLocation('hand');
        $gCount = [];
        $clans = ['bratva' => [], 'cartel' => [], 'gang' => [], 'triad' => [], 'mafia' => []];
        $pbInfos = $this->loadPlayersBasicInfos();
        foreach ($clans as $cname => $cplayers) { //init clan counter
            foreach ($pbInfos as $pid => $pinfo) {
                $clans[$cname][$pid] = 0;
            }
        }
        foreach ($allGangsters as $gid => $ginfo) {
            if (!isset($gCount[$ginfo['location_arg']])) {
                $gCount[$ginfo['location_arg']] = 0;
            }
            $gCount[$ginfo['location_arg']] += 1;
            $clans[$this->gangster_type[$ginfo['type']]['clan']][$ginfo['location_arg']] += 1;
        }
        $max = ["count" => 0, "players" => []];
        foreach ($gCount as $pid => $pcount) {
            self::dbQuery("UPDATE player SET player_score_aux = $pcount WHERE player_id = $pid");
            if ($pcount > $max["count"]) {
                $max["count"] = $pcount;
                $max["players"] = [$pid];
            } elseif ($pcount == $max["count"]) {
                $max["players"][] = $pid;
            }
        }

        $playersInfo = $this->getPlayersCompleteInfos();

        foreach ($playersInfo as $pid => $pinfo) {
            $leaders = $this->getCompetenceCount($pid, 1, false);
            $mercs = $this->getCompetenceCount($pid, 2, false);
            $infos = $this->getCompetenceCount($pid, 3, false);
            self::setStat($leaders, 'leaderComp', $pid);
            self::setStat($mercs, 'mercComp', $pid);
            self::setStat($infos, 'infoComp', $pid);
        }

        //if($this->getGameStateValue('clanvariant') == 1){
        if ($this->isClan == false) {
            if (count($max["players"]) == 1) {
                //one player received 2
                $pid = $max["players"][0];
                $vpoints = $playersInfo[$pid]['player_score'];
                self::dbQuery("UPDATE player SET player_score = player_score+2,public_score=public_score+2 WHERE player_id = $pid");
                self::notifyAllPlayers('endPoints',
                                       clienttranslate('${player_name} receives 2 influence for having the most gangsters'),
                                       [
                                           'player_name' => $playersInfo[$pid]['player_name'],
                                           'player_id' => $pid,
                                           'amount' => 2,
                                           'new_amount' => $vpoints + 2,
                                       ]);
                self::incStat(2, 'scoreMostGangster', $pid);
            } else {
                //all players receive 1
                foreach ($max["players"] as $pid) {
                    $vpoints = $playersInfo[$pid]['player_score'];
                    self::dbQuery("UPDATE player SET player_score = player_score+1,public_score=public_score+1 WHERE player_id = $pid");
                    self::notifyAllPlayers('endPoints',
                                           clienttranslate('${player_name} receives 1 influence for being tied for most gangsters'),
                                           [
                                               'player_name' => $playersInfo[$pid]['player_name'],
                                               'player_id' => $pid,
                                               'amount' => 1,
                                               'new_amount' => $vpoints + 1,
                                           ]);
                    self::incStat(1, 'scoreMostGangster', $pid);
                }
            }
        }
        //if($this->getGameStateValue('clanvariant')==2){
        if ($this->isClan == true) {
            $headerRow = [""];
            $table = [];
            foreach ($pbInfos as $pid => $pinfo) {
                $headerRow[] = ['str' => '${player_name}', 'args' => ['player_name' => $pinfo['player_name']], 'type' => 'header'];
            }
            $table[] = $headerRow;
            $cmax = ['bratva' => ['count' => 0, 'players' => []],
                     'cartel' => ['count' => 0, 'players' => []],
                     'gang' => ['count' => 0, 'players' => []],
                     'triad' => ['count' => 0, 'players' => []],
                     'mafia' => ['count' => 0, 'players' => []]];
            foreach ($clans as $cname => $c) {
                foreach ($c as $pid => $pcount) {
                    if ($pcount > $cmax[$cname]['count']) {
                        $cmax[$cname]["count"] = $pcount;
                        $cmax[$cname]["players"] = [$pid];
                    } elseif ($pcount == $cmax[$cname]['count']) {
                        $cmax[$cname]["players"][] = $pid;
                    }
                }
                $row = [[ 'str' => $this->Clan_type[$this->Clan_type_name[$cname]], 'args' => [] ]];
                foreach ($pbInfos as $plid => $plinfos) {
                    $row[] = $c[$plid];
                }
                $table[] = $row;
            }
            foreach ($cmax as $cname => $maxInfo) {
                if ($maxInfo["count"] > 0 && count($maxInfo["players"]) == 1) {
                    //one player received 2
                    $pid = $maxInfo["players"][0];
                    $vpoints = $playersInfo[$pid]['player_score'];
                    self::dbQuery("UPDATE player SET player_score = player_score+2,public_score=public_score+2 WHERE player_id = $pid");
                    self::notifyAllPlayers('endPoints',
                                           clienttranslate('${player_name} receives 2 influence for having the most gangsters of the ${clan} clan'),
                                           [
                                                'i18n'=>['clan'],
                                               'player_name' => $playersInfo[$pid]['player_name'],
                                               'player_id' => $pid,
                                               'amount' => 2,
                                               'new_amount' => $vpoints + 2,
                                               'clan' => $this->gameConstants['clanNames'][$cname],
                                           ]);
                    self::incStat(2, 'scoreMostGangster', $pid);
                }
                if ($maxInfo["count"] > 0 && count($maxInfo["players"]) > 1) {
                    //all players receive 1
                    foreach ($maxInfo["players"] as $pid) {
                        $vpoints = $playersInfo[$pid]['player_score'];
                        self::dbQuery("UPDATE player SET player_score = player_score+1,public_score=public_score+1 WHERE player_id = $pid");
                        self::notifyAllPlayers('endPoints',
                                               clienttranslate('${player_name} receives 1 influence for being tied for most gangsters of the ${clan} clan'),
                                               [
                                                    'i18n'=>['clan'],
                                                   'player_name' => $playersInfo[$pid]['player_name'],
                                                   'player_id' => $pid,
                                                   'amount' => 1,
                                                   'new_amount' => $vpoints + 1,
                                                   'clan' => $this->gameConstants['clanNames'][$cname],
                                               ]);
                        self::incStat(1, 'scoreMostGangster', $pid);
                    }
                }
            }
            self::notifyAllPlayers('tableWindow', '', [
                "id" => 'clanScoring',
                "title" => clienttranslate('Clan Scoring'),
                "header" => clienttranslate('Details of the clan scoring'),
                "table" => $table,
                "closing" => clienttranslate('Close'),
            ]);
        }

        $max = ["count" => 0, "players" => []];
        foreach ($playersInfo as $pid => $pinfo) {
            if ($pinfo['player_money'] > $max['count']) {
                $max["count"] = $pinfo['player_money'];
                $max["players"] = [$pid];
            } elseif ($pinfo['player_money'] == $max['count']) {
                $max["players"][] = $pid;
            }
        }

        if (count($max["players"]) == 1) {
            //one player received 2
            $pid = $max["players"][0];
            $vpoints = $playersInfo[$pid]['player_score'];
            self::dbQuery("UPDATE player SET player_score = player_score+2,public_score=public_score+2 WHERE player_id = $pid");
            self::notifyAllPlayers('endPoints',
                                   clienttranslate('${player_name} receives 2 influence for being the richest'), [
                                       'player_name' => $playersInfo[$pid]['player_name'],
                                       'player_id' => $pid,
                                       'amount' => 2,
                                       'new_amount' => $vpoints + 2,
                                   ]);
            self::setStat(2, 'scoreMostMoney', $pid);
        } else {
            //all players receive 1
            foreach ($max["players"] as $pid) {
                $vpoints = $playersInfo[$pid]['player_score'];
                self::dbQuery("UPDATE player SET player_score = player_score+1,public_score=public_score+1 WHERE player_id = $pid");
                self::notifyAllPlayers('endPoints',
                                       clienttranslate('${player_name} receives 1 influence for being tied for richest'),
                                       [
                                           'player_name' => $playersInfo[$pid]['player_name'],
                                           'player_id' => $pid,
                                           'amount' => 1,
                                           'new_amount' => $vpoints + 1,
                                       ]);
                self::setStat(1, 'scoreMostMoney', $pid);
            }
        }
        $this->gamestate->nextState("gameEnd");
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Zombie
////////////

    /*
        zombieTurn:

        This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
        You can do whatever you want in order to make sure the turn of this player ends appropriately
        (ex: pass).

        Important: your zombie code will be called when the player leaves the game. This action is triggered
        from the main site and propagated to the gameserver from a server, not from a browser.
        As a consequence, there is no current player associated to this action. In your zombieTurn function,
        you must _never_ use getCurrentPlayerId() or getCurrentPlayerName(), otherwise it will fail with a "Not logged" error message.
    */

    function zombieTurn($state,
                        $active_player) {
        $statename = $state['name'];
        $stType = $state['type'];

        if ($stType == "activeplayer") {
            switch ($statename) {
                default:
                    $this->gamestate->nextState("zombiePass");
                    break;
            }

            return;
        }

        if ($stType == "multipleactiveplayer") {
            // Make sure player is in a non blocking status for role turn
            $sql = "
                UPDATE  player
                SET     player_is_multiactive = 0
                WHERE   player_id = $active_player
            ";
            self::DbQuery($sql);

            $this->gamestate->updateMultiactiveOrNextState('');
            //$this->gamestate->setPlayerNonMultiactive( $active_player, 'zombiePass' );

            return;
        }

        throw new feException("Zombie mode not supported at this game state: " . $statename);
    }

///////////////////////////////////////////////////////////////////////////////////:
////////// DB upgrade
//////////

    /*
        upgradeTableDb:

        You don't have to care about this until your game has been published on BGA.
        Once your game is on BGA, this method is called everytime the system detects a game running with your old
        Database scheme.
        In this case, if you change your Database scheme, you just have to apply the needed changes in order to
        update the game database and allow the game to continue to run with your new version.

    */

    function upgradeTableDb($from_version) {
        // $from_version is the current version of this game database, in numerical form.
        // For example, if the game was running with a release of your game named "140430-1345",
        // $from_version is equal to 1404301345

        // Example:
//        if( $from_version <= 1404301345 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "ALTER TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        if( $from_version <= 1405061421 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "CREATE TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        // Please add your future database scheme changes here
//
//
        if ($from_version <= 2206011421) {
            // ! important ! Use DBPREFIX_<table_name> for all tables

            $sql = "UPDATE DBPREFIX_card SET card_order = card_type where card_order = 0 and card_location = 'hand'";
            self::applyDbUpgradeToAllDB($sql);
        }
    }
}

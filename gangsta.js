/**
 *------
 * BGA framework: Â© Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Gangsta implementation : : © Benoit Ragoen <benoit.ragoen@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * gangsta.js
 *
 * Gangsta user interface script
 *
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

var isDebug = window.location.host == 'studio.boardgamearena.com' || window.location.hash.indexOf('debug') > -1;
var debug = isDebug ? console.info.bind(window.console) : function () {};

define([
        "dojo", "dojo/_base/declare",
        getLibUrl('bga-animations', '1.x'),
        "ebg/core/gamegui",
        "ebg/counter",
        "ebg/stock",
        g_gamethemeurl + "modules/bga-help/bga-help.js",
    ],
    function (dojo, declare, BgaAnimations) {
        return declare("bgagame.gangsta", ebg.core.gamegui, {
            constructor: function () {
                //console.log('gangsta constructor');

                // Here, you can init the global variables of your user interface
                this.avheists = null;
                this.avgangsters = null;
                this.playerTableau = {};
                this._counters = {};

                this.genesisdecksize = 30;
                this.gangwarsdecksize = 33;
                this.dominationdecksize = 36;
                this.gangsterdecksize = 35;
                this.card_height = 154;
                this.card_width = 110;
                this.connexions = [];
                this.clientStateArgs = {};
                this.selectedDeck = null;

                this.neededHeistSkills = {};
                this.currentMobilize = {};
                this.currentTap = 0;
                this.possibleTargets = [];
                this.currentHeist = null;
                this.possible_heists = [];
                this.isPublicVariant = false;
                
                this._connectionsGangsters = [];
                this._connections = [];
                this._selectableNodes = [];
                this.skillCount = class {
                    needed = null;
                    ignoreSkills = 0;
                    gangsters = [];
                    original = []

                    isComplete() {
                        let sumMissing = 0;
                        this.needed.forEach(element => {
                            if (element > 0) {
                                sumMissing += element;
                            }
                        });
                        if(sumMissing> this.ignoreSkills) return false;
                        return true;
                    }

                    hideSkill(skill) {
                        dojo.query('#skillcounter_text .skill_' + skill).style("display", "none");
                    }

                    showSkill(skill) {
                        dojo.query('#skillcounter_text .skill_' + skill).style("display", "");
                    }

                    updateText() {
                        //console.log("updateText");
                        this.needed.forEach((element, index) => {
                            dojo.query("#skillcounter_text .skill_" + index + "_value").text(element);
                        });
                    }

                    clearGangsters() {
                        this.gangsters = [];
                        this.needed = this.original.slice();
                        this.updateText();
                        this.updateVisibility();
                    }

                    updateVisibility() {
                        //console.log("updateVisibility");
                        this.needed.forEach((element, index) => {
                            if (element > 0) {
                                this.showSkill(index);
                            } else {
                                this.hideSkill(index);
                            }
                        });
                    }

                    involveGangster(gangster, gangsterid, tableau) {
                        //console.log("involveGangster");
                        //console.log(gangster);
                        //console.log(this.needed);
                        var possible = tableau[gangsterid]['skill'] > 0 && this.needed[tableau[gangsterid]['skill']] > 0;
                        for (var skill in gangster.stats) {
                            possible = possible || (this.needed[skill] > 0 && gangster.stats[skill] > 0);
                        }
                        if (!possible) {
                            return false;
                        }
                        for (var skill in gangster.stats) {
                            this.needed[skill] -= gangster.stats[skill];
                        }
                        if (tableau[gangsterid]['skill'] > 0) {
                            this.needed[tableau[gangsterid]['skill']] -= 1;
                        }
                        this.gangsters.push(gangsterid);
                        this.updateText();
                        this.updateVisibility();
                        return true;
                    }

                    removeGangster(gangster, gangsterid, tableau) {
                        //console.log("removeGangster");
                        //console.log(gangster);
                        //console.log(this.needed);
                        if (tableau[gangsterid]['skill'] > 0) {
                            this.needed[tableau[gangsterid]['skill']] += 1;
                        }
                        for (var skill in gangster.stats) {
                            this.needed[skill] += gangster.stats[skill];
                        }
                        this.gangsters.splice(this.gangsters.indexOf(gangsterid), 1)
                        this.updateText();
                        this.updateVisibility();
                    }

                    constructor(skillcost) {
                        //console.log(skillcost);
                        this.needed = [];

                        for (var skill in skillcost) {
                            this.needed[skill] = skillcost[skill];
                            this.original[skill] = skillcost[skill];
                        }
                        this.updateText();
                        this.updateVisibility();
                        //console.log(this.isComplete());
                    }

                };
            },

            /*
                setup:

                This method must set up the game user interface according to current game situation specified
                in parameters.

                The method is called each time the game interface is displayed to a player, ie:
                _ when the game starts
                _ when a player refreshes the game page (F5)

                "gamedatas" argument contains all datas retrieved by your "getAllDatas" PHP method.
            */

            setup: function (gamedatas) {
                //console.log( "Starting game setup" );
                console.log(gamedatas);

                // create the animation manager, and bind it to the `game.bgaAnimationsActive()` function
                this.animationManager = new BgaAnimations.Manager({
                    animationsActive: () => this.bgaAnimationsActive(),
                });
                // Setting up player boards
                var nb_players = 0;

                for (var player_id in gamedatas.players) {
                    var player = gamedatas.players[player_id];
                    player['heistpoints'] = _("Heist Points");
                    player['teampoints'] = _("Team Points");
                    player['resourcepoints'] = _("Resource Points");
                    player['fname_bratva'] = _(this.gamedatas.constants.clanNames['bratva']);
                    player['fname_cartel'] = _(this.gamedatas.constants.clanNames['cartel']);
                    player['fname_gang'] = _(this.gamedatas.constants.clanNames['gang']);
                    player['fname_mafia'] = _(this.gamedatas.constants.clanNames['mafia']);
                    player['fname_triad'] = _(this.gamedatas.constants.clanNames['triad']);
                    
                    this._counters[player_id] = {};

                    for(let k=1; k<this.gamedatas.skill_name.length;k++){
                        player['skill_name_'+k] = _("Skill: ") + _(this.gamedatas.skill_name[k]);
                    }

                    $('playermoney_' + player_id).innerHTML = player.money;
                    player['vault_style'] = "no_display";

                    // Setting up players boards if needed
                    var player_board_div = $('player_board_' + player_id);
                    dojo.place(this.format_block('jstpl_player_board', player), player_board_div);

                    nb_players++;
                    if (player_id == this.player_id || gamedatas['public_variant'] == true) {
                        this.changePlayerHeistScore(player_id, gamedatas.heist_score);
                    }
                }

                this.updateCounters(this.gamedatas.counters);
                this.addTooltipToClass('player_vault',_('Bank vault'),'');

                if (this.gamedatas.activePhaseName == 'gangwars') {
                    dojo.removeClass('heists', 'genesis');
                    dojo.addClass('heists', 'gangwar');
                }
                if (this.gamedatas.activePhaseName == 'domination') {
                    dojo.removeClass('heists', 'genesis');
                    dojo.addClass('heists', 'domination');
                    $('victoryCond').style.removeProperty('display');
                }

                this.snitchlist = ebg.stock();
                this.snitchlist.create(this, $('snitchlist'), this.card_width, this.card_height);
                this.snitchlist.image_items_per_row = 9;
                this.snitchlist.setOverlap(15, 0);
                this.snitchlist.setSelectionMode(0); //no selection possible

                this.avheists = ebg.stock();
                this.avheists.order_items = 0;
                this.avheists.create(this, $('avheists'), this.card_width, this.card_height);
                this.avheists.image_items_per_row = 9;
                this.avheists.item_margin = 10;
                this.avheists.setSelectionMode(1); //maximum of 1 item selected
                this.avheists.setSelectionAppearance("class");//--> .stockitem_selected
                //this.avheist.onItemCreate = dojo.hitch( this, 'setupNewHeist' );
                dojo.connect(this.avheists, 'onChangeSelection', this, 'onPickAvHeist');

                //console.log('Adding available Heists');
                for (var heist_genesis_type in this.gamedatas.genesis_type) {
                    var card_type_id = this.getHeistUniqueId(heist_genesis_type);
                    this.avheists.addItemType(heist_genesis_type, 1, g_gamethemeurl + 'img/heist_sprite_chap1.jpg', card_type_id);
                }
                for (var heist_gangwar_type in this.gamedatas.gangwars_type) {
                    var card_type_id = this.getHeistUniqueId(heist_gangwar_type);
                    this.avheists.addItemType(heist_gangwar_type, 1, g_gamethemeurl + 'img/heist_sprite_chap2.jpg', card_type_id);
                    if (this.gamedatas.gangwars_type[heist_gangwar_type].type == "snitch") {
                        this.snitchlist.addItemType(heist_gangwar_type, 1, g_gamethemeurl + 'img/heist_sprite_chap2.jpg', card_type_id);
                    }
                }
                for (var heist_domination_type in this.gamedatas.domination_type) {
                    var card_type_id = this.getHeistUniqueId(heist_domination_type);
                    this.avheists.addItemType(heist_domination_type, 1, g_gamethemeurl + 'img/heist_sprite_chap3.jpg', card_type_id);
                    if (this.gamedatas.domination_type[heist_domination_type].type == "snitch") {
                        this.snitchlist.addItemType(heist_domination_type, 1, g_gamethemeurl + 'img/heist_sprite_chap3.jpg', card_type_id);
                    }
                }

                this.displayAvailableHeists(this.gamedatas.avheists);
                this.displayPlayerHeists(this.gamedatas.playerheists);

                for (let i in this.gamedatas.activesnitch) {
                    var s = this.gamedatas.activesnitch[i];
                    this.snitchlist.addToStockWithId(s.type, s.id);
                    this.addGangstaTip(s.type, 'heist', this.snitchlist.getItemDivId(s.id));
                }

                this.avgangsters = ebg.stock();
                this.avgangsters.create(this, $('avgangsters'), this.card_width, this.card_height);
                this.avgangsters.image_items_per_row = 8;
                this.avgangsters.item_margin = 10;
                this.avgangsters.setSelectionMode(1); //maximum of 1 item selected
                this.avgangsters.setSelectionAppearance("class");//--> .stockitem_selected
                //this.avgangsters.onItemCreate = dojo.hitch( this, 'setupNewGangster' );
                dojo.connect(this.avgangsters, 'onChangeSelection', this, 'onPickAvGangster');

                for (var gangster_type in this.gamedatas.gangster_type) {
                    var card_type_id = this.getGangsterUniqueId(gangster_type);
                    this.avgangsters.addItemType(gangster_type, 1, g_gamethemeurl + 'img/gangsters_sprite.jpg', card_type_id);
                }

                this.displayAvailableGangsters(this.gamedatas.avgangsters);

                //console.log('  Starting to fill Tableau for players');
                var sortedTableau = [];
                for (let i in this.gamedatas.tableau) {
                    sortedTableau.push(this.gamedatas.tableau[i]);
                }

                sortedTableau.sort((a, b) => a.order - b.order);
                for (var card of sortedTableau) {
                    //for now location_arg is playerid because it's in the deck location of the php Deck component.
                    this.addGangster(card.location_arg, card.type, card.id, card.state, card);
                }
                
                Object.values(this.gamedatas.resources).forEach((card) => {
                    if(card.location == 'rc_hand' || card.location == 'rc_hand_future'){
                        let cardDiv = this.addResourceCardInHand(card);
                    }
                });
                
                Object.values(this.gamedatas.wounded).forEach((card) => {
                    this.addGangsterInPlayerHospital(card.location_arg,card);
                });

                if (!this.isSpectator) {
                    dojo.place(this.format_block('jstpl_skillcounter', {
                        "LABEL_REQUIRED_SKILLS": _("Required Skills :"),
                        'skill_1_value': 0,
                        'skill_2_value': 0,
                        'skill_3_value': 0,
                        'skill_4_value': 0,
                        'skill_5_value': 0,
                        'skill_6_value': 0,
                    }), dojo.query('.current_player .playertableau')[0], "before");
                }
                //adding buttons on heist interface.
                if (!this.isSpectator) {
                    this.addActionButton('commit_heist_button', _('Confirm'), 'onConfirmHeist', null, false, 'blue');
                    this.addActionButton('cancel_heist_button', _('Cancel'), 'onCancelHeist', null, false, 'blue');
                    dojo.place('commit_heist_button', 'skill_confirm_holder');
                    dojo.place('cancel_heist_button', 'skill_cancel_holder');
                    
                }
                // Create a new div for multi active buttons to avoid BGA auto clearing it
                dojo.place("<div id='customActions' style='display:inline-block'></div>", $('generalactions'), 'after');

                if (this.gamedatas['clan_variant'] == true) {
                    dojo.query('.board-family').removeClass('no_display');
                }

                if (this.gamedatas['public_variant'] == true) {
                    this.isPublicVariant = true;
                }
                $('ebd-body').dataset.clan_variant = this.gamedatas.clan_variant;
                $('ebd-body').dataset.resources_variant = this.gamedatas.resources_variant;
                $('gangstainterface').dataset.resources_variant = this.gamedatas.resources_variant;

                dojo.place('<a href="#" onclick="return false;" id="game_help_btn" class="action-button bgabutton bgabutton_blue">'+_('Player aid')+'</a>', "synchronous_notif_icon", "before");
                dojo.connect($('game_help_btn'), 'onclick', this, 'createPlayerAid');
                
                if(this.gamedatas.resources_variant != 1){
                    this.helpManager = new HelpManager(ebg.core.gamegui, {
                        buttons: [
                            new BgaHelpPopinButton({
                                title: _("Player aid"),
                                html: `<div id='gng_resources_help_popin'>
                                    ${this.formatResourcesHelp()}
                                </div>`,
                            })
                        ],
                    });
                }

                if( this.gamedatas.endScoring && Object.keys(this.gamedatas.endScoring).length ) this.displayFinalScoringTable(this.gamedatas.endScoring);

                // Setup game notifications to handle (see "setupNotifications" method below)
                this.setupNotifications();

                //console.log( "Ending game setup" );
            },
            
            formatResourceCardHelp: function(cardDatas)
            {
                let tplCardDatas = this.prepareTplDatasForResourceCard(cardDatas);
                let tooltipHtml = this.format_block('jstpl_tooltip_resource', tplCardDatas);
                return tooltipHtml;
            },
            formatResourcesHelp: function()
            {
                let list = "";
                let k = 0;
                Object.entries(this.gamedatas.resources_type).forEach(([type,resource_type]) => {
                    let resourceDatas = resource_type;
                    resourceDatas.id = 0;
                    resourceDatas.state = 0;
                    resourceDatas.type = type;
                    if(k>0){
                        list = list.concat('<hr>');
                    }
                    list = list.concat( this.formatResourceCardHelp(resourceDatas));
                    k++; 
                });
                return `
                    <h1 class='gng_resourcesHelp_title'>${_("Resource cards") }</h1>
                    <div class='gng_resourcesHelp'>
                        ${list}
                    </div>
                `;
            },

            createPlayerAid: function (evt) {
                this.myPlayerAid = new ebg.popindialog();
                this.myPlayerAid.create('playeraid');
                this.myPlayerAid.setTitle(_("Reward Aid"));
                this.myPlayerAid.setMaxWidth(700); // Optional

                let html = this.format_block('jstpl_game_help', {
                    "income": _("Income"),
                    "income_t": _("You receive the indicated amount of $."),
                    "influence": _("Influence Points (IP)"),
                    "influence_t": _("You receive the indicated amount of Influence."),
                    "recruit": _("Fast recruitment"),
                    "recruit_t": _("You may immediately recruit a gangster, you must still pay their cost."),
                    "teach": _("Learning"),
                    "teach_t": _("You may add the indicated skill to one of the gangsters who performed the heist and who hasn't yet learned a skill. You can't have the Leader skill twice."),
                    "replay": _("Play again"),
                    "replay_t": _("You can immediately play another turn. You cannot benefit from this twice in a row."),
                    "steal": _("Theft"),
                    "steal_t": _("You may steal the indicated amount of $ from one single rival gang."),
                    "diversion": _("Diversion"),
                    "diversion_t": _("You may engage up to the indicated amount of opponent(s) gangster(s)."),
                    "kill": _("Assassination"),
                    "kill_t": _("You may designate an opponent, they must discard a gangster with exactly 2 skills (no Bosses)"),
                    "rally": _("Rallying"),
                    "rally_t": _("Make all of your gangsters available."),
                    "coop": _("Cooperative Heists (Chapters II & III)"),
                    "coop_t": _("First engage one of your gangsters, then you can engage your or other player's available gangsters as if they were yours. The initiator gains the reward on the left. If 2 or more players are involved, everyone involved gains the reward on the right."),
                });

                // Show the dialog
                this.myPlayerAid.setContent(html); // Must be set before calling show() so that the size of the content is defined before positioning the dialog
                this.myPlayerAid.show();
            },

            ///////////////////////////////////////////////////
            //// Game & client states

            // onEnteringState: this method is called each time we are entering into a new game state.
            //                  You can use this method to perform some user interface changes at this moment.
            //
            onEnteringState: function (stateName, args) {
                debug( 'Entering state: ',stateName, args );

                switch (stateName) {
                    case 'resourcesSelection':
                        this.enteringResourcesSelection(args.args);
                        break;
                    case 'playerAction':
                        this.enteringPlayerAction(args.args);
                        break;
                    case 'rewardRecruit':
                        this.enteringRewardRecruit(args.args);
                        break;
                    case 'rewardTap':
                        this.enteringRewardTap(args.args);
                        break;
                    case 'rewardKill':
                        this.enteringRewardKill(args.args);
                        break;
                    case 'rewardSkill':
                        this.enteringRewardSkill(args.args);
                        break;
                    case 'playerMobilize':
                        this.enteringPlayerMobilize(args.args);
                        break;
                    case 'recovering':
                        this.enteringRecovering(args.args);
                        break;
                    case 'endTurnActions':
                        this.enteringEndTurnActions(args.args);
                        break;
                        
                    //Client states :
                    case 'client_untapGangsters':
                        this.enteringClientUntapGangsters(args.args);
                        break;

                    case 'dummmy':
                        break;
                }
                this.updateCounters(this.gamedatas.counters)
            },

            enteringResourcesSelection: function (args) {
                document.getElementById("avdecks").classList.add("no_display");
                if(this.isSpectator) return;

                this.possibleCards = [];
                if(args._private !=undefined){
                    if(args._private.cards!=undefined){
                        this.possibleCards = args._private.cards;
                    }
                } 
                
                this.selectedResource = null;

                Object.values(this.possibleCards).forEach((card) => {
                    let cardDiv = this.addResourceCardInAvailable(card);
                    cardDiv.classList.add('selectable');
                    this.onClick(cardDiv.id, () => {
                        if (this.isCurrentPlayerActive()) {
                            if (this.selectedResource){
                                $(`resource_card_${this.selectedResource}`).classList.remove('selected');
                            }
                            this.selectedResource = card.id;
                            cardDiv.classList.add('selected');
                            this.confirmMessage = this.format_string(_('Take ${name}'), { name: _(card.name) });

                            if($(`btnConfirmResource`)){
                                $('btnConfirmResource').innerHTML = this.confirmMessage;
                            }
                            else {
                                this.statusBar.addActionButton(this.confirmMessage, () => this.onConfirmResource(),{
                                    id:'btnConfirmResource',
                                    destination:$('customActions'),
                                    disabled: false,
                                });
                            }
                        }
                    });
                });
            },

            enteringPlayerAction: function (args) {
                this.possible_heists = args.pHeists;
                Object.entries(this.possible_heists).forEach(([card_id,card]) => {
                    let divCard = document.getElementById(`avheists_item_${card_id}`);
                    if(divCard) divCard.classList.add("selectable");
                });
                this.possible_gangsters = args.pRecruits;
                Object.entries(this.possible_gangsters).forEach(([card_id,card]) => {
                    let divCard = document.getElementById(`avgangsters_item_${card_id}`);
                    if(divCard) divCard.classList.add("selectable");
                });
                this.displayTopDeckGangster(args._private, true);
                this.displayTopDeckHeist(args._private, true);
            },
            enteringRewardRecruit: function (args) {
                this.possible_gangsters = args.pRecruits;
                Object.entries(this.possible_gangsters).forEach(([card_id,card]) => {
                    let divCard = document.getElementById(`avgangsters_item_${card_id}`);
                    if(divCard) divCard.classList.add("selectable");
                });
                this.displayTopDeckGangster(args._private, true);
            },
            
            enteringRecovering: function (args) {
                this.statusBar.addActionButton(_('Recover all'), () => {
                        this.onConfirmRecoverNGangsters(true);
                    },{
                        id:'btnConfirmRecoverAll',
                        destination:$('customActions'),
                    });

                this.confirmMessage = this.format_string(_('Recover ${n} selected gangsters'), { n: 0 });
                this.statusBar.addActionButton(this.confirmMessage, () => this.onConfirmRecoverNGangsters(false),{
                            id:'btnConfirmRecoverN',
                            destination:$('customActions'),
                            disabled: true,
                        });
                this.statusBar.addActionButton(_('Skip'), () => {
                        this.bgaPerformAction('actSkipRecover' );
                    },{
                        id:'btnSkipRecover',
                        destination:$('customActions'),
                    });

                this.possibleCards = args.g_ids;
                this.selectedDeadGangsters = [];
                if (!this.isCurrentPlayerActive()) return;

                Object.values(this.possibleCards).forEach((cardId) => {
                    let cardDiv = document.getElementById(`gangster_${cardId}`);
                    cardDiv.classList.add('selectable');
                    this.onClick(cardDiv.id, () => {
                        debug("click recovering",cardId);
                        if(cardDiv.classList.contains('selected')){
                            cardDiv.classList.remove('selected');
                            cardDiv.classList.add('dead');
                            let index = this.selectedDeadGangsters.indexOf(cardId);
                            if (index > -1) {
                                this.selectedDeadGangsters.splice(index, 1);
                            }
                        }
                        else {
                            cardDiv.classList.add('selected');
                            cardDiv.classList.remove('dead');
                            this.selectedDeadGangsters.push(cardId);
                        }
                        this.confirmMessage = this.format_string(_('Recover ${n} selected gangsters'), { n: this.selectedDeadGangsters.length });
                        if($(`btnConfirmRecoverN`)){
                            $('btnConfirmRecoverN').innerHTML = this.confirmMessage;
                            if(this.selectedDeadGangsters.length>0) $('btnConfirmRecoverN').disabled = false;
                            else $('btnConfirmRecoverN').disabled = true;
                        }
                    });
                });
            },

            enteringPlayerMobilize: function (args) {
                // 'player_id' => $player_id,
                // 'leaders' => $leaders,
                // 'money' => $money,
                // 'tapped' => $tapped,
                this.gangstersInLastHeist = [];
                this.possibleTargets = [];
                this.currentHeist = null;
                this.currentTap = 0;
                this.neededHeistSkills = {};
                if (!this.isCurrentPlayerActive()) {
                    return;
                }

                //console.log(args);

                dojo.query('.current_player .playertableau .gangster').removeClass('mobilized');
                for (var gangster in args.tapped) {
                    dojo.addClass($('gangster_' + gangster), 'mobilized selectable');
                }
                this.currentMobilize = {free: args.leaders, gangsters: [], money: 0};
                this.displayTopDeckGangster(args._private, false);
                this.displayTopDeckHeist(args._private, false);
            },

            enteringRewardTap: function (args) {
                if (!this.isCurrentPlayerActive()) {
                    return;
                }
                //console.log(args);
                this.currentTap = args.amount;
                this.possibleTargets = [];
                for (var gangster in args.selectable) {
                    this.possibleTargets.push(gangster);
                    dojo.addClass($('gangster_' + gangster), 'selectable');
                    dojo.removeClass($('gangster_' + gangster), 'mobilized'); //making sure they're not tapped
                }
            },

            enteringRewardKill: function (args) {
                if (!this.isCurrentPlayerActive()) {
                    return;
                }
                this.possibleTargets = [];
                for (var gangster in args.selectable) {
                    this.possibleTargets.push(gangster);
                    dojo.addClass($('gangster_' + gangster), 'selectable');
                }
                dojo.query('.boss').removeClass('selectable');
            },

            enteringRewardSteal: function (args) {
                if (!this.isCurrentPlayerActive()) {
                    return;
                }
            },
            enteringEndTurnActions: function (args) {
                if (!this.isCurrentPlayerActive()) {
                    return;
                }
                let actions = args.pactions;
                Object.entries(actions).forEach(([actionName,actionDatas]) => {

                    switch(actionName){
                        case 'freeUntapLeader':
                            this.statusBar.addActionButton(this.format_string(_("Untap ${n} leader(s)"), {n:  actionDatas['args'].amount}), () => {
                                this.setClientState('client_untapGangsters', {
                                   descriptionmyturn : this.format_string(_('You must choose ${n} leaders to untap for free'), { n: actionDatas['args'].amount  } ),
                                   args : actionDatas['args'],
                                });
                            },{
                                id:'btnEndTurn'+actionName,
                                destination:$('customActions'),
                            });
                            break;
                        case 'freeUntapGangster':
                            this.statusBar.addActionButton(this.format_string(_("Untap ${n} gangster(s)"), {n:  actionDatas['args'].amount}), () => {
                                this.setClientState('client_untapGangsters', {
                                   descriptionmyturn : this.format_string(_('You must choose ${n} gangsters to untap for free'), { n: actionDatas['args'].amount } ),
                                   args : actionDatas['args'],
                                });
                            },{
                                id:'btnEndTurn'+actionName,
                                destination:$('customActions'),
                            });
                            break;
                        default: break;
                    }

                    
                });
            },

            enteringClientUntapGangsters: function (args) {
                if (!this.isCurrentPlayerActive()) {
                    return;
                }

                let gangsters_ids = args.g_ids;

                this.statusBar.addActionButton("Cancel", () => {
                        dojo.query('.selected').addClass('mobilized');
                        this.restoreServerGameState();
                    },{
                        id:'btnCancel',
                        destination:$('customActions'),
                        color: 'secondary',
                    });
                this.statusBar.addActionButton(_("Confirm"), () => {
                        if(this.currentMobilize.gangsters.length > args.amount) return;

                        this.bgaPerformAction('actEndUntapGangsters', { 'actionType':args.actionType, g_ids: this.currentMobilize.gangsters.join(";") });
                    },{
                        id:'btnEndTurnConfirm',
                        destination:$('customActions'),
                    });
                //From enteringPlayerMobilize..., useful infos for onClickGangsterForMobilization
                this.currentMobilize = {free: args.amount, gangsters: [], money: 0};
                Object.values(gangsters_ids).forEach((gangster_id) => {
                    let div = $('gangster_' + gangster_id);
                    div.classList.add('selectable');
                });
            },

            enteringRewardSkill: function (args) {
                if (!this.isCurrentPlayerActive()) {
                    return;
                }
                //this.currentLearn={skill_id: args['skill_id'], skill_name: args['skill_name']};
                this.possibleTargets = [];
                for (var gangster in args.selectable) {
                    if (this.gangstersInLastHeist == undefined || this.gangstersInLastHeist.includes(gangster)) {
                        this.possibleTargets.push(gangster);
                        dojo.addClass($('gangster_' + gangster), 'selectable');
                    }
                }
                //Replaced by server side control
                //if (this.possibleTargets.length == 0) {
                //    this.onSkip(true);
                //}
            },

            enteringSnitch: function (args) { //this is called from UpdateActionButtons.
                if (!this.isCurrentPlayerActive()) {
                    return;
                }
                //console.log(args);
                var thisSnitch = args['snitch_effect'][this.player_id] && args['snitch_effect'][this.player_id]['player_snitch'];
                if (thisSnitch === undefined) {
                    return;
                }
                this.snitchAction = thisSnitch;

                if (thisSnitch > 0) { // you need to tap gangsters
                    dojo.query('.current_player .playertableau .gangster:not(.mobilized)').addClass('selectable');
                    this.gamedatas.gamestate.args['count'] = thisSnitch;
                    this.currentTap = thisSnitch;
                    this.setDescriptionOnMyTurn(_("${you} must tap ${count} gangster(s)"));
                    //this.addActionButton('confSnitch_button', _('Confirm'), 'onConfirmSnitch');
                } else { // you need to kill a gangster
                    dojo.query('.current_player .playertableau .gangster:not(.boss)').addClass('selectable');
                    this.setDescriptionOnMyTurn(_("${you} must discard a gangster"));
                    //this.addActionButton('confTap_button', _('Confirm'), 'onConfirmTap');
                }
                this.addActionButton('confSnitch_button', _('Confirm'), 'onConfirmSnitch');
                dojo.query('boss').removeClass('selectable');
            },

            enteringGdg: function (args) {
                if (!this.isCurrentPlayerActive()) {
                    return;
                }
                if (args['is_double']) {
                    this.setDescriptionOnMyTurn(_("${you} must discard a gangster of your opponent."));
                    this.addActionButton('confGdgKill_button', _('Confirm'), 'onConfirmGdg');
                    dojo.query('.opposing_player .playertableau .gangster:not(.boss)').addClass('selectable');
                } else {
                    this.setDescriptionOnMyTurn(_("${you} must discard a gangster"));
                    this.addActionButton('confGdgKill_button', _('Confirm'), 'onConfirmGdg');
                    dojo.query('.current_player .playertableau .gangster:not(.boss)').addClass('selectable');
                }
            },

            // onLeavingState: this method is called each time we are leaving a game state.
            //                 You can use this method to perform some user interface changes at this moment.
            //
            onLeavingState: function (stateName) {
                debug( 'Leaving state: ',stateName );
                this.hideSkillCounter();
                switch (stateName) {

                    case 'resourcesSelection':
                        dojo.empty('av_resources');
                        break;
                    case 'resourcesSetup':
                        document.getElementById("avdecks").classList.remove("no_display");
                        break;
                    case 'rewardTap':
                        break;
                    case 'rewardKill':
                        break;
                    case 'rewardSkill':
                        break;
                    case 'playerMobilize':
                        dojo.query('.selected').addClass('mobilized');
                        break;
                    case 'client_untapGangsters':
                        dojo.query('.selected').addClass('mobilized');
                        break;
                    case 'snitch':
                        this.snitchAction = 0;
                        break;
                    case 'dummmy':
                        break;
                }
                this.clearPossibleSelection();
                this.avheists.unselectAll();
                this.avgangsters.unselectAll();
            },

            // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
            //                        action status bar (ie: the HTML links in the status bar).
            //
            onUpdateActionButtons: function (stateName, args) {
                //console.log( 'onUpdateActionButtons: '+stateName );

                if (this.isCurrentPlayerActive()) {
                    switch (stateName) {
                        case 'resourcesSelection':
                            //!\\ Multi active state
                            break;
                        case 'playerAction':
                            this.addActionButton('confRecruit_button', _('Recruit'), 'onConfRecruit');
                            this.statusBar.addActionButton( "<span>"+_('Pass for Money')+"</span>"+`<i class='fa6 fa-arrow-right'></i> <span>${args.passMoney}</span> ${this.formatIconMoney()}`,  () => this.onPassForMoney(),{id:'pass_button',destination:$('customActions')});
                            dojo.addClass('confRecruit_button', 'disabled');
                            break;
                        case 'discard':
                            this.addActionButton('skip_button', _('Skip discard'), 'onSkipDiscard');
                            break;
                        case 'rewardRecruit':
                            this.addActionButton('confRecruit_button', _('Recruit'), 'onConfRecruit');
                            this.addActionButton('skRecr_button', _('Skip'), 'onSkip');
                            dojo.addClass('confRecruit_button', 'disabled');
                            break;
                        case 'rewardSteal':
                            this.addAllPlayerButtons(args);
                            this.addActionButton('skSteal_button', _('Skip'), 'onSkip');
                            break;
                        case 'markForKill':
                            this.addMarkForKillButtons(args);
                            this.addActionButton('skMark_button', _('Skip'), 'onSkip');
                            break;
                        case 'rewardTap':
                            this.addActionButton('confTap_button', _('Confirm'), 'onConfirmTap');
                            this.addActionButton('skTap_button', _('Skip'), 'onSkip');
                            break;
                        case 'rewardKill':
                            this.addActionButton('confKill_button', _('Confirm'), 'onConfirmKill');
                            break;
                        case 'rewardSkill':
                            let iconSkill = this.formatIconSkill(args.skill_id);
                            this.statusBar.addActionButton( "<span>"+_('Confirm')+"</span>"+iconSkill,  () => this.onConfirmTeach(),{id:'confTeach_button',destination:$('customActions')});
                            this.statusBar.addActionButton( _('Skip'), () => this.onSkip(),{id:'skTeach_button',destination:$('customActions')});
                            
                            break;
                        case 'playerMobilize':
                            this.addActionButton('confMob_button', _('Confirm'), 'onMobilize');
                            this.statusBar.addActionButton( _('Skip'), () => {
                                    dojo.query('.selected').addClass('mobilized');
                                    this.onSkip();
                                },
                                {id:'skMob_button',destination:$('customActions')}
                            );
                            break;
                        case 'snitch':
                            this.enteringSnitch(args);
                            break;
                        case 'endTurnActions':
                            this.statusBar.addActionButton( _('Skip'), () => this.bgaPerformAction('actSkipEndTurn'),{destination:$('customActions')});
                            break;
                        case 'gdgMulti':
                            //console.log(args);
                            this.enteringGdg(args);
                            break;
                        /*               Example:
                                         case 'myGameState':

                                            // Add 3 action buttons in the action status bar:

                                            this.addActionButton( 'button_1_id', _('Button 1 label'), 'onMyMethodToCall1' );
                                            this.addActionButton( 'button_2_id', _('Button 2 label'), 'onMyMethodToCall2' );
                                            this.addActionButton( 'button_3_id', _('Button 3 label'), 'onMyMethodToCall3' );
                                            break;
                        */
                    }
                } else {
                    dojo.empty('customActions');
                }
            },

            ///////////////////////////////////////////////////
            //// Utility methods

            // Here, you can defines some utility methods that you can use everywhere in your javascript script.

            clearPossibleSelection() {
                debug('clearPossibleSelection()' );
                dojo.empty('customActions');

                this._connections.forEach(dojo.disconnect);
                this._connections = [];
                this._selectableNodes.forEach((node) => {
                    if ($(node)) dojo.removeClass(node, 'selectable selected');
                });
                this._selectableNodes = [];
                dojo.query('.selectable').removeClass('selectable');
                dojo.query('.selected').removeClass('selected');
                dojo.empty('gangsters_deck_topcard');
                dojo.empty('heists_deck_topcard');
                document.getElementById("gangsters_deck_topcard").classList.add('no_display');
                document.getElementById("heists_deck_topcard").classList.add('no_display');
            },
            /*
            * Custom connect that keep track of the connections
            */
            connect(node, action, callback) {
                this._connections.push(dojo.connect($(node), action, callback));
            },
            onClick(node, callback, temporary = true) {
                let safeCallback = (evt) => {
                    evt.stopPropagation();
                    if (this.isInterfaceLocked()) return false;
                    callback(evt);
                };

                if (temporary) {
                    this.connect($(node), 'click', safeCallback);
                    dojo.addClass(node, 'selectable');
                    this._selectableNodes.push(node);
                } else {
                    dojo.connect($(node), 'click', safeCallback);
                }
            },

            /** Declare this function to inject html into log items. */
            bgaFormatText : function(log, args) {
                debug("bgaFormatText",log, args);
                try {
                    if (log && args && !args.processed) {
                        args.processed = true;

                        ///
                        let clan = 'clan';
                        let clan_id = 'clan_id';
                        if(clan in args && clan_id in args) {
                            args.clan = _(args.clan) + this.formatIconClan(args.clan_id);
                        }

                        if('tableWindowDatas' in args){
                            //we store it in title in order to have at least a title in game logs before opening the game replay
                            args['title'] = this.formatTableWindowDatasForLogs(args);
                        }
                            

                    }
                } catch (e) {
                    console.error(log,args,"Exception thrown", e.stack);
                }
                return this.inherited(arguments);
            },
            // Returns true for spectators, instant replay (during game), archive mode (after game end)
            addGangstaTip: function (cardTypeId, type, divId) {
                //this.addGangstaTip(gangster.type, 'gangster',this.avgangsters.getItemDivId(gangster.id));
                var phaseId = this.gamedatas.activePhaseId + 1;
                var imgurl = g_themeurl + "img/heist_sprite_chap" + phaseId;
                var imgsize = "1980px 1232px";
                var tpl = {
                    classes: "htip " + this.gamedatas.activePhaseName,
                    backx: this.getHeistHorizontalOffset(cardTypeId) * 100,
                    backy: this.getHeistVerticalOffset(cardTypeId) * 100,
                    title: "",
                };

                if (type === 'gangster') {
                    tpl.classes = "gtip";
                    tpl.backx = this.getGangsterHorizontalOffset(cardTypeId) * 100;
                    tpl.backy = this.getGangsterVerticalOffset(cardTypeId) * 100;
                    if(this.gamedatas.gangster_type[cardTypeId].type == 'boss'){
                        tpl.title = _("BOSS");
                    }
                } 
                else if(type === 'heist') {
                    if (this.gamedatas.genesis_type[cardTypeId]) {
                        tpl.title = _(this.gamedatas.genesis_type[cardTypeId].name);
                    } else  if (this.gamedatas.gangwars_type[cardTypeId]) {
                        tpl.title = _(this.gamedatas.gangwars_type[cardTypeId].name);
                    } else  if (this.gamedatas.domination_type[cardTypeId]) {
                        tpl.title = _(this.gamedatas.domination_type[cardTypeId].name);
                    }
                }
                var tipHtml = this.format_block('jstpl_tooltip', tpl);
                this.addTooltipHtml(divId, tipHtml);
            },

            displayAvailableHeists: function (datas) {
                debug("displayAvailableHeists",datas);
                for (let i in datas) {
                    var heist = datas[i];
                    this.avheists.addToStockWithId(heist.type, heist.id);
                    this.addGangstaTip(heist.type, 'heist', this.avheists.getItemDivId(heist.id));
                }
                this.avheists.updateDisplay();
            },
            /**
             * 
             * @param {*} datas these datas should be received at the end when performed heists are public information
             */
            displayPlayerHeists: function (datas) {
                debug("displayPlayerHeists",datas);
                for (let i in datas) {
                    let heist = datas[i];
                    let pid = heist.location_arg;
                    this.addHeist(pid, heist, `performed_heists_${pid}`,true);
                }
            },
            displayAvailableGangsters: function (datas) {
                debug("displayAvailableGangsters",datas);
                for (let i in datas) {
                    var gangster = datas[i];
                    this.avgangsters.addToStockWithId(gangster.type, gangster.id);
                    this.addGangstaTip(gangster.type, 'gangster', this.avgangsters.getItemDivId(gangster.id));
                }
                this.avgangsters.updateDisplay();
            },
            displayTopDeckGangster: function (privatesDatas, withClickHandler = true) {
                debug("displayTopDeckGangster",privatesDatas,withClickHandler);
                this.possible_topdeck_gangster = null;
                if(!privatesDatas || !privatesDatas.top_deck){ 
                    return;
                }
                if(privatesDatas.top_deck.gangster){
                    let topDeckGangster = privatesDatas.top_deck.gangster.card;
                    this.possible_topdeck_gangster = topDeckGangster;
                    let topDeckGangsterDiv = document.getElementById("gangsters_deck_topcard");
                    topDeckGangsterDiv.classList.remove('no_display');
                    //this.avgangsters.addToStockWithId(topDeckGangster.type, topDeckGangster.id, 'gangstersdeck');
                    let divGangster = this.addGangster(this.player_id, topDeckGangster.type, topDeckGangster.id, topDeckGangster.state, {'skill': "0"}, 'gangsters_deck_topcard');
                    if(privatesDatas.top_deck.gangster.possible && withClickHandler) divGangster.classList.add("selectable");
                    divGangster.dataset.id = topDeckGangster.id;
                    dojo.disconnect(this._connectionsGangsters[divGangster.id]);
                    if(withClickHandler) dojo.connect(divGangster, 'onclick', this, 'onPickAvGangster');
                    

                }
            },
            displayTopDeckHeist: function (privatesDatas, withClickHandler = true) {
                debug("displayTopDeckHeist",privatesDatas,withClickHandler);
                this.possible_topdeck_heist = null;
                if(!privatesDatas || !privatesDatas.top_deck){ 
                    return;
                }
                if(privatesDatas.top_deck.heist){
                    let top_deck_datas = privatesDatas.top_deck.heist;
                    let topDeckHeist = top_deck_datas.card;
                    this.possible_topdeck_heist = topDeckHeist;
                    let topDeckHeistDiv = document.getElementById("heists_deck_topcard");
                    topDeckHeistDiv.classList.remove('no_display');
                    let divHeist = this.addHeist(this.player_id, topDeckHeist, 'heists_deck_topcard');
                    if(top_deck_datas.possible && withClickHandler){
                        divHeist.classList.add("selectable");
                        this._connections[divHeist.id] = dojo.connect(divHeist, 'onclick', this, 'onPickAvHeist');
                        this.possible_heists[topDeckHeist.id] = topDeckHeist;
                        this.possible_heists[topDeckHeist.id].ignore_skills = 0;
                    }
                }
            },

            isReadOnly: function () {
                return this.isSpectator || typeof g_replayFrom != 'undefined';// || g_archive_mode;
            },

            divYou: function () {
                var color = this.gamedatas.players[this.player_id].color;
                var color_bg = "";
                if (this.gamedatas.players[this.player_id] && this.gamedatas.players[this.player_id].color_back) {
                    color_bg = "background-color:#" + this.gamedatas.players[this.player_id].color_back + ";";
                }
                var you = "<span style=\"font-weight:bold;color:#" + color + ";" + color_bg + "\">" + __("lang_mainsite", "You") + "</span>";
                return you;
            },

            setMainTitle: function (text) {
                $('pagemaintitletext').innerHTML = text;
            },

            setDescriptionOnMyTurn: function (text) {
                this.gamedatas.gamestate.descriptionmyturn = text;
                var tpl = dojo.clone(this.gamedatas.gamestate.args);
                if (tpl === null) {
                    tpl = {};
                }
                var title = "";
                if (this.isCurrentPlayerActive() && text !== null) {
                    tpl.you = this.divYou();
                }
                title = this.format_string_recursive(text, tpl);

                if (!title) {
                    this.setMainTitle(" ");
                } else {
                    this.setMainTitle(title);
                }
            },
            
            /**
             * try to do what is done by framework displayTableWindow(), but the table will be kept in the log text
             * 
             * @returns string html content
             * */
            formatTableWindowDatasForLogs(args){
                debug("formatTableWindowDatasForLogs() : reformat tableWindow datas to display here a table", args);
                let formattedHeader = "";
                formattedHeader += (args['title'] ) ? "<div class='tableWindowRecap_title'>"+_(args['title']) + "</div><hr>" : "";
                formattedHeader += (args['header'] ) ? "<div class='tableWindowRecap_header'>"+ _(args['header']) +"</div><hr>" : "";
                let formattedTable = "<table class='tableWindowRecap'>";
                Object.values(args.tableWindowDatas).forEach((row) => {
                    formattedTable += "<tr>";
                    Object.values(row).forEach((col) => {
                        let cellType =  "td";
                        let cellContent = "";
                        if ("object" == typeof col) {
                            if(col['type'] == 'header') cellType = "th";
                            //Special arg treatment for player_names
                            Object.entries(col['args']).forEach(([key,value]) => { 
                                if(key.startsWith("player_name")){
                                    col['args'][key] = this.formatColoredPlayerNameByName(value); 
                                }
                            }); 

                            cellContent = (col['str'] ) ? this.format_string(_(col['str']), col['args']) : col;
                        }
                        else {
                            cellContent = col;
                        }
                        formattedTable += `<${cellType}>${cellContent}</${cellType}>`;
                    });
                    formattedTable += "</tr>";
                });
                formattedTable += "</table>";

                return formattedHeader + formattedTable;
            },

            formatColoredPlayerNameByName(player_name){
                const player = Object.values(this.gamedatas.players).find((player) => player.name == player_name);
                if (player == undefined) return player_name;

                const color = player.color;
                const color_bg = player.color_back ? 'background-color:#' + player.color_back + ';' : '';
                return `<!--PNS--><span class="playername playername_wrapper_${color}" style="color:#${color};${color_bg}">${player_name}</span><!--PNE-->`;
            },

            addAllPlayerButtons: function (args) {
                var activeplayer = this.getActivePlayerId();
                for (let player_id in args.player_money) {
                    if (player_id != activeplayer) {
                        let name = args.player_money[player_id].name;
                        let player_color = this.gamedatas.players[player_id].color;
                        let formattedName = `<span class='button_player_name' style='color:#${player_color};'>${name}</span>`;
                        let money = args.player_money[player_id].money;
                        if (money >= 0) {
                            this.statusBar.addActionButton( "<span>"+formattedName+"</span>"+` ( <span>${money}</span> ${this.formatIconMoney()} )`,  () => this.onSteal(player_id),{id:'st_Button' + player_id,destination:$('customActions')});
                        }
                    }
                }
            },

            addMarkForKillButtons: function (args) {
                //console.log("addMarkForKillButtons");
                //console.log(args);
                var activeplayer = this.getActivePlayerId();
                Object.keys(args.selectable).forEach(element => {
                    var player_id = args.selectable[element];
                    if (player_id != activeplayer) {
                        var name = this.gamedatas.players[player_id].name;
                        let player_color = this.gamedatas.players[player_id].color;
                        let formattedName = `<span class='button_player_name' style='color:#${player_color};'>${name}</span>`;
                        this.addActionButton('st_Button' + player_id, formattedName, dojo.hitch(this, "onMark", player_id));
                    }

                })

            },

            addToTableau: function (type, card_id, type_arg, player_id, order) {
                var gangster = {
                    "id": card_id,
                    "type": type,
                    "type_arg": type_arg,
                    "location": "hand",
                    "location_arg": player_id,
                    "state": 0,
                    "order": order,
                    "skill": 0,
                }
                this.gamedatas.tableau[card_id] = gangster;
                this.gamedatas.counters['panel_team_' + player_id].counter_value += 1;

                this.gamedatas.counters['skill_' + this.gamedatas.skill_name_invariant[1] + '_' + player_id].counter_value += this.gamedatas.gangster_type[type].stats[1];
                this.gamedatas.counters['skill_' + this.gamedatas.skill_name_invariant[2] + '_' + player_id].counter_value += this.gamedatas.gangster_type[type].stats[2];
                this.gamedatas.counters['skill_' + this.gamedatas.skill_name_invariant[3] + '_' + player_id].counter_value += this.gamedatas.gangster_type[type].stats[3];
                this.gamedatas.counters['skill_' + this.gamedatas.skill_name_invariant[4] + '_' + player_id].counter_value += this.gamedatas.gangster_type[type].stats[4];
                this.gamedatas.counters['skill_' + this.gamedatas.skill_name_invariant[5] + '_' + player_id].counter_value += this.gamedatas.gangster_type[type].stats[5];
                this.gamedatas.counters['skill_' + this.gamedatas.skill_name_invariant[6] + '_' + player_id].counter_value += this.gamedatas.gangster_type[type].stats[6];

                this.gamedatas.counters['family_' + this.gamedatas.gangster_type[type].clan + '_' + player_id].counter_value += 1;
            },

            removeFromTableau(cardid, player_id) {
                var type = this.gamedatas.tableau[cardid]["type"];
                this.gamedatas.counters['panel_team_' + player_id].counter_value -= 1;
                this.gamedatas.counters['skill_' + this.gamedatas.skill_name_invariant[1] + '_' + player_id].counter_value -= this.gamedatas.gangster_type[type].stats[1];
                this.gamedatas.counters['skill_' + this.gamedatas.skill_name_invariant[2] + '_' + player_id].counter_value -= this.gamedatas.gangster_type[type].stats[2];
                this.gamedatas.counters['skill_' + this.gamedatas.skill_name_invariant[3] + '_' + player_id].counter_value -= this.gamedatas.gangster_type[type].stats[3];
                this.gamedatas.counters['skill_' + this.gamedatas.skill_name_invariant[4] + '_' + player_id].counter_value -= this.gamedatas.gangster_type[type].stats[4];
                this.gamedatas.counters['skill_' + this.gamedatas.skill_name_invariant[5] + '_' + player_id].counter_value -= this.gamedatas.gangster_type[type].stats[5];
                this.gamedatas.counters['skill_' + this.gamedatas.skill_name_invariant[6] + '_' + player_id].counter_value -= this.gamedatas.gangster_type[type].stats[6];

                this.gamedatas.counters['family_' + this.gamedatas.gangster_type[type].clan + '_' + player_id].counter_value -= 1;

                var learnedskill = this.gamedatas.tableau[cardid].skill;
                if (learnedskill > 0) {
                    this.gamedatas.counters['skill_' + this.gamedatas.skill_name_invariant[learnedskill] + '_' + player_id].counter_value -= 1;
                }

                delete this.gamedatas.tableau[cardid];
            },

            formatIconMoney: function(iconSize = ''){
                return `<span class="custom_icon money ${iconSize}Icon"></span>`;
            },
            formatIconTeam: function(){
                return `<span class="custom_icon team largeIcon"></span>`;
            },
            formatIconSkill: function(skill_id){
                return `<span data-type="${skill_id}" class="custom_icon skill"></span>`;
            },
            formatIconClan: function(clan_id){
                return `<span data-type="${clan_id}" class="custom_icon familyIconLarge"></span>`;
            },
            
            formatResourceCardAbilityText: function (abilityValue) {
                let descriptionMap = new Map([
                    ['police_station', this.format_string(_('Never pay more than $${n} to the bank when a Snitch is revealed. The usual rules apply if you are unable to shell out $${n}.'),{n:1 })],
                    ['black_market', this.format_string(_('Receive + $${n} when you Pass your Turn.'),{n:2 })],
                    ['hq', this.format_string(_('Pay $${n} to make your entire gang available'),{ n:1})],
                    ['counterfeit_printing', this.format_string(_('Receive $${n} at the beginning of each of your turns. Discard the Counterfeit printing card during the gang war and place it with your stored Heist Cards.'),{n:1 })],
                    ['private_jet', this.format_string(_('If you just performed or participated in a Cooperative Heist ${icon_coop}, make any of your gangsters Available.'),{ icon_coop:'<span class="reward-icon reward-coop"></span>' })],
                    ['media', this.format_string(_('Receive ${n} influence ${icon_influence} at the end of the game for each stored heist wich includes Influence Points (with a maximum of ${max}).'),{n:1,max:7, icon_influence: '<span class="reward-icon reward-influence"></span>'})],
                    ['high_tech_eq', this.format_string(_('Ignore one requirement of your choice if you perform ${a} or ${b}.'),{ a:_('Art theft'), b: _('Hack attack')})],
                    ['private_society', this.format_string(_('At the end of your turn, make one of your leaders ${icon_leader} available for free.'),{icon_leader: `<span class="skill leader"></span>` })],
                    ['bank', this.format_string(_('Store up to $${n} in the bank vault ${icon_vault}. This money cannot be targeted by a Theft ${icon_theft}. Money will be transferred or withdrawn to or from the bank at any time during your turn.'),{n:10, icon_theft: '<span class="reward-icon reward-theft"></span>', icon_vault:'<span><i class="icon_vault fa6-solid fa6-vault fa6-lg"></i></span>' })],
                    ['bikers_gang', this.format_string(_('At the beginning of your turn, you can look at the first Heist Card from the stack. You will be allowed to perform the heist during this phase by paying $${n} in advance.'),{n:1 })],
                    ['hospital', this.format_string(_('All your gangsters who were eliminated during the game are placed on the Hospital card. You can recover them ( in Available position) when you choose to pass your turn. This does not apply to gangsters who are arrested when a Snitch is revealed.'),{ })],
                    ['indicator_network', this.format_string(_('At the beginning of your turn, you can look at the first gangster from the stack. You can recruit them during this turn. Should they belong to the same family as your Boss, they will cost you $ ${n} less.'),{n:1 })],
                ]);
                let description = descriptionMap.get(abilityValue);
                return description;
            },
            prepareTplDatasForResourceCard: function (cardDatas) {
                let tplCardDatas = {
                    'id': cardDatas.id,
                    'type': cardDatas.type,
                    'name': _(cardDatas.name),
                    'state': cardDatas.state,
                    'ability': this.format_string( 
                        bga_format(_('Ability *when active* : ${ability}'), {
                            '*': (t) => '<b><i>' + t + '</i></b>',
                        })
                        , {
                            'ability': '<span class="resource_ability_value">'+this.formatResourceCardAbilityText(cardDatas.ability)+'</span>'
                        }
                    ),
                    'required_skills_label': _('Required Skills :'),
                    'skill_1_value': cardDatas.cost[1],
                    'skill_2_value': cardDatas.cost[2],
                    'skill_3_value': cardDatas.cost[3],
                    'skill_4_value': cardDatas.cost[4],
                    'skill_5_value': cardDatas.cost[5],
                    'skill_6_value': cardDatas.cost[6],
                    'LABEL_INFLUENCE': this.format_string(
                        bga_format(_('End game influence *when active* : ${influence}'), {
                            '*': (t) => '<b><i>' + t + '</i></b>',
                        })
                        , {
                            'influence': '<span class="resource_influence_value">'+cardDatas.influence+'</span>'
                        }
                    ),
                };
                return tplCardDatas;
            },
            
            addResourceCardInAvailable: function (cardDatas) {
                debug('addResourceCardInAvailable',cardDatas);
                let cardDiv = $('resource_card_' + cardDatas.id);
                if (cardDiv) return cardDiv;

                let tplCardDatas = this.prepareTplDatasForResourceCard(cardDatas);
                dojo.place(this.format_block('jstpl_resource_card', tplCardDatas), 'av_resources');

                cardDiv = $('resource_card_' + cardDatas.id);
                let tipHtml = this.format_block('jstpl_tooltip_resource', tplCardDatas);
                this.addTooltipHtml(cardDiv.id, tipHtml);
                return cardDiv;
            },

            addResourceCardInHand: async function (cardDatas) {
                debug('addResourceCardInHand',cardDatas);
                let card_owner = cardDatas.location_arg;
                if(cardDatas.ability == 'bank'){
                    this.displayPlayerVault(card_owner);
                }
                let cardDiv = $('resource_card_' + cardDatas.id);
                if (cardDiv) return cardDiv;

                let tplCardDatas = this.prepareTplDatasForResourceCard(cardDatas);
                await dojo.place(this.format_block('jstpl_resource_card',tplCardDatas ), `player_resource_cards_${card_owner}`);
                
                cardDiv = $('resource_card_' + cardDatas.id);

                let tipHtml = this.format_block('jstpl_tooltip_resource', tplCardDatas);
                this.addTooltipHtml(cardDiv.id, tipHtml);

                return cardDiv;
            },
            displayPlayerVault: function (player_id) {
                debug('displayPlayerVault',player_id);
            
                let vault = document.getElementById(`player_vault_${player_id}`);
                vault.classList.remove("no_display");
                vault = document.getElementById(`playerboard_vault_wrap_${player_id}`);
                vault.classList.remove("no_display");
            },

            cancelRecruitSelection: function () {
                debug("cancelRecruitSelection");
                this.avgangsters.unselectAll();
                dojo.addClass('confRecruit_button', 'disabled');
                this.cancelTopDeckRecruitSelection();
            },
            cancelTopDeckRecruitSelection: function () {
                debug("cancelTopDeckRecruitSelection");
                let topDeckGangsterDiv = document.getElementById("gangsters_deck_topcard").querySelector(".gangster") ;
                if(topDeckGangsterDiv) topDeckGangsterDiv.classList.remove('selected');
            },
            cancelTopDeckHeistSelection: function () {
                debug("cancelTopDeckHeistSelection");
                let targetHeistDiv = document.getElementById("heists_deck_topcard").querySelector(".heist") ;
                if(targetHeistDiv) targetHeistDiv.classList.remove('selected');
            },

            addHeist: function (player_id, heistDatas, targetPlace, considerAllChapters = false ) {
                debug("addHeist",player_id, heistDatas, targetPlace,considerAllChapters);
                let cardTypeId = heistDatas.type;
                let chapter_phase = this.gamedatas.activePhaseName;
                if(considerAllChapters) {
                    if (this.gamedatas.genesis_type[cardTypeId]) {
                        chapter_phase = this.gamedatas.constants.gamePhases[0];
                    } else  if (this.gamedatas.gangwars_type[cardTypeId]) {
                        chapter_phase = this.gamedatas.constants.gamePhases[1];
                    } else  if (this.gamedatas.domination_type[cardTypeId]) {
                        chapter_phase = this.gamedatas.constants.gamePhases[2];
                    }
                }
                let tpl = {
                    id: heistDatas.id,
                    type: cardTypeId,
                    chapter_phase: chapter_phase,
                    backx: this.getHeistHorizontalOffset(cardTypeId) * 100,
                    backy: this.getHeistVerticalOffset(cardTypeId) * 100,
                };
                let heistId = 'heist_' + heistDatas.id;

                dojo.place(this.format_block('jstpl_heist', tpl), targetPlace);

                this.addGangstaTip(cardTypeId, 'heist', heistId);
                return $(heistId);
            },

            addGangsterInPlayerHospital(player_id, gangsterCard) {
                debug("addGangsterInPlayerHospital",player_id, gangsterCard);
                let divGangster = this.addGangster(
                    player_id, 
                    gangsterCard.type, 
                    gangsterCard.id, 
                    gangsterCard.state, 
                    gangsterCard, 
                    'dead_gangsters_'+player_id,
                );
                divGangster.classList.add("dead");
                return divGangster;
            },

            addGangster: function (player_id, type, id, gangsterState, fullCard, forceTarget = null) {
                // Standard case
                var tpl = {
                    id: id,
                    backx: this.getGangsterHorizontalOffset(type) * 100,
                    backy: this.getGangsterVerticalOffset(type) * 100,
                    boss: "",
                    extra: "",
                };
                var gangsterid = 'gangster_' + id;

                if (this.gamedatas.gangster_type[type]['type'] === 'boss') {
                    tpl.boss = 'boss';
                }
                if (parseInt(fullCard['skill']) > 0) {
                    tpl.extra = 'extra_skill';
                }
                //console.log('Add gangster type:'+type+' id:'+id+'boss: '+tpl.boss+' extra: '+tpl.extra) ;

                let targetPlace = 'recruited_gangsters_' + player_id;
                if(forceTarget) targetPlace = forceTarget;
                dojo.place(this.format_block('jstpl_gangster', tpl), targetPlace);

                if (parseInt(fullCard['skill']) > 0) {
                    var theSkill = this.gamedatas.skill_name_invariant[fullCard['skill']];
                    dojo.place(this.format_block('jstpl_extraskill', {skill: theSkill}), 'gangster_' + id, "first");
                }


                // if( player_id == this.player_id )
                // {
                //     //dojo.addClass( 'gangster_'+id, 'selectable' );
                //     dojo.connect( $(gangsterid), 'onclick', this, 'onSelectGangster' );
                // }
                this._connectionsGangsters[gangsterid] = dojo.connect($(gangsterid), 'onclick', this, 'onSelectGangster');
                if ($('avgangsters_item_' + id)) {
                    this.placeOnObject(gangsterid, 'avgangsters_item_' + id);
                    this.slideToObject(gangsterid, 'card_wrap_' + id).play();
                }
                if (gangsterState >= 1) {
                    dojo.addClass(gangsterid, 'mobilized');
                }
                this.addGangstaTip(type, 'gangster', gangsterid);
                return $(gangsterid);
            },

            getGangsterVerticalOffset(typeid) {
                if (typeid < 110) {
                    return typeid - 100;
                } // 103 = 3
                return Math.floor((typeid - 100) / 10); // floor((111-100)/10) = 1
            },
            getGangsterHorizontalOffset(typeid) {
                if (typeid < 110) {
                    return 7;
                }
                return (typeid % 10) - 1; // 111 % 10-1 = 0
            },
            getGangsterUniqueId: function (typeid) { //111=>8
                if (typeid < 110) { //101 = 16, 103=32
                    return (typeid - 100 + 1) * 8;
                }
                var family = Math.floor((typeid - 100) / 10); // floor((111-100)/10) = 1
                var gangster = typeid - 100 - family * 10; // 111-100-1*10=1

                return family * 8 + gangster - 1; // 111 = 1*8+1=9
            },

            getHeistUniqueId: function (typeid) {
                //offset is 36 for new chapter
                //that's for genesis
                if (typeid < 300) { //genesis
                    return typeid - 200;
                }
                if (typeid < 400) { //gdg
                    //return typeid-300+36;
                    return typeid - 300;
                }
                //domination
                //return typeid-400+72;
                return typeid - 400;
            },

            getHeistVerticalOffset(typeid) {
                var offset = typeid % 100; // 209 = 9
                return Math.floor((offset) / 9); // floor(9/9) = 1
            },
            getHeistHorizontalOffset(typeid) {
                var offset = typeid % 100; // 209 = 9
                return offset % 9;        // 9 = 0
            },

            changePlayerMoney: function (playerid, money) {
                this.gamedatas.players[playerid].money = money;
                this.gamedatas.counters['panel_money_' + playerid]['counter_value'] = money
                this.gamedatas.counters['playermoney_' + playerid]['counter_value'] = money
            },

            changePlayerScore: function (playerid, score) {
                debug("changePlayerScore",playerid, score);
                $('player_score_' + playerid).innerHTML = score;
                this.gamedatas.players[playerid].score = score;
                //this.gamedatas.counters['panel_t_pts_'+playerid].counter_value = score;
                //this takes value from this.scoreCtl which is populated from the player_score and would this be the private score.
                //this.scoreCtrl[notif.args.player_id].setValue(notif.args.player_score);

                if(this._counters[playerid].scoringRecap !=undefined){
                    //IF end scoring recap displayed
                    this._counters[playerid].scoringRecap.total.toValue(score);
                }
            },
            looseScoreFromGangster: function (player_id, score_loss) {
                debug("looseScoreFromGangster",player_id, score_loss);
                if (score_loss > 0) {
                    var score = this.gamedatas.players[player_id].score;
                    var tscore = this.gamedatas.counters['panel_t_pts_' + player_id].counter_value;
                    this.changePlayerScore(player_id, score - score_loss);
                    this.changePlayerTeamScore(player_id, tscore - score_loss);
                }
                this.updateCounters(this.gamedatas.counters);
            },

            changePlayerTeamScore: function (playerid, score) {
                debug("changePlayerTeamScore",playerid, score);
                this.gamedatas.counters['panel_t_pts_' + playerid].counter_value = score;
                this.updateCounters(this.gamedatas.counters);
                
                if(this._counters[playerid].scoringRecap !=undefined){
                    //IF end scoring recap displayed
                    this._counters[playerid].scoringRecap.teamScore.toValue(score);
                }
            },
            changePlayerHeistScore: function (playerid, score) {
                if (this.isPublicVariant == false) {
                    $('panel_h_pts_' + playerid).innerHTML = score;
                } else {
                    this.gamedatas.counters['panel_h_pts_' + playerid].counter_value = score;
                    this.updateCounters(this.gamedatas.counters);
                }
                if(this._counters[playerid].scoringRecap !=undefined){
                    //IF end scoring recap displayed
                    this._counters[playerid].scoringRecap.heists.toValue(score);
                }
            },
            changePlayerResourceScore: function (playerid, score) {
                this.gamedatas.counters['panel_r_pts_' + playerid].counter_value = score;
                this.updateCounters(this.gamedatas.counters);
                if(this._counters[playerid].scoringRecap !=undefined){
                    //IF end scoring recap displayed
                    this._counters[playerid].scoringRecap.resources.toValue(score);
                }
            },


            scrollToHeists: function(player_id){
                debug("scrollToHeists",player_id);
                let div = document.getElementById(`performed_heists_${player_id}`);
                if(div) div.scrollIntoView({block: "center", inline: "nearest"});
            },
            
            prepareEndScoreCounter(endScoreDatas, playerId, divId, counterName, arrayKeyName, arrayKey2 = undefined){
                if(!arrayKey2){
                    this._counters[playerId].scoringRecap[counterName] = new ebg.counter();
                    this._counters[playerId].scoringRecap[counterName].create(divId);
                }
                else {
                    this._counters[playerId].scoringRecap[counterName][arrayKey2] = new ebg.counter();
                    this._counters[playerId].scoringRecap[counterName][arrayKey2].create(divId);
                }
                if(endScoreDatas && endScoreDatas[arrayKeyName] ){
                    let counterVal = endScoreDatas[arrayKeyName];
                    let counterToUpdate = this._counters[playerId].scoringRecap[counterName];
                    if(arrayKey2) {
                        if( endScoreDatas[arrayKeyName][arrayKey2]) counterVal = endScoreDatas[arrayKeyName][arrayKey2];
                        else counterVal = 0;
                        counterToUpdate = this._counters[playerId].scoringRecap[counterName][arrayKey2];
                    }
                    counterToUpdate.setValue(counterVal);
                    if(counterToUpdate.getValue()>0) document.getElementById(divId).classList.remove("counter_empty");
                }
            },
            /**
             * 
             * @param {array} datas : if we already have computed datas
             */
            displayFinalScoringTable(datas = null){
                debug("displayFinalScoringTable()",datas);
                
                dojo.empty('end_score_recap');
                dojo.place(this.tplFinalScoringTable(),'end_score_recap');
                
                Object.values(this.gamedatas.players).forEach((player) => {
                    let pId = player.id;
                    let endScoreDatas = datas ? datas[pId] : undefined;
                    this._counters[pId].scoringRecap = [];
                    
                    this.prepareEndScoreCounter(endScoreDatas,pId,`recap_teamScore_${pId}`,'teamScore','SCORING_TEAM');
                    this.prepareEndScoreCounter(endScoreDatas,pId,`recap_heists_${pId}`,'heists','SCORING_HEIST');
                    this.prepareEndScoreCounter(endScoreDatas,pId,`recap_resources_${pId}`,'resources','SCORING_RESOURCE');
                    this.prepareEndScoreCounter(endScoreDatas,pId,`recap_mostMoney_${pId}`,'mostMoney','SCORING_MONEY');
                    this.prepareEndScoreCounter(endScoreDatas,pId,`recap_mostGangsters_${pId}`,'mostGangsters','SCORING_MAJORITY');
                    this.prepareEndScoreCounter(endScoreDatas,pId,`recap_total_${pId}`,'total','SCORING_FINAL');
                    
                    this._counters[pId].scoringRecap.clans = [];
                    Object.values([1,2,3,4,5]).forEach((clanId) =>{
                        this.prepareEndScoreCounter(endScoreDatas,pId,`recap_clanInfluence_${clanId}_${pId}`,'clans','SCORING_CLANS', clanId);
                    });

                    //update player heists score (in case of previously private info or not sync info)
                    this.changePlayerHeistScore(player.id, this._counters[player.id].scoringRecap.heists.getValue());
                });
            },
            tplFinalScoringTable(){
                debug("tplFinalScoringTable()");
                let playersNames = '';
                let playersTeamScore = '';
                let playersHeists = '';
                let playersResources = '';
                let playersTotal = '';
                let clansInfluence = '';
                let playersMostMoney = '';
                let playersMostGangsters = '';
                Object.values([1,4,5,2,3]).forEach((clanId) =>{ // LOOP CLANS
                    let playersClansInfluence = '';
                    Object.values(this.gamedatas.players).forEach((player) => {
                        playersClansInfluence +=`<td><div id='recap_clanInfluence_${clanId}_${player.id}' class="counter_empty"></div></td>`;
                    });
                    let clanIcon = this.formatIconClan(clanId);
                    clansInfluence += `<tr class="row_recap_clans">
                        <th>${this.format_string(_('Influence: most Gangsters'),{}) + clanIcon}</th>
                        ${playersClansInfluence}
                    </tr>`;
                });
                let moneyIcon = this.formatIconMoney('large');
                let teamIcon = this.formatIconTeam();
                Object.values(this.gamedatas.players).forEach((player) => {//LOOP PLAYERS
                    let formattedName = `<span class='button_player_name' style='color:#${player.color};'>${player.name}</span>`;
                    playersNames +=`<th>${formattedName}</th>`;
                    playersTeamScore +=`<td><div id='recap_teamScore_${player.id}' class="counter_empty"></div></td>`;
                    playersHeists +=`<td><div id='recap_heists_${player.id}' class="counter_empty"></div><button id='view_heists_${player.id}' class="view_heists_link" onclick="gameui.scrollToHeists(${player.id});"><i class="fa6 fa6-eye"></i></button></td>`;
                    playersResources += `<td><div id='recap_resources_${player.id}' class="counter_empty"></div></td>`;
                    playersMostMoney += `<td><div id='recap_mostMoney_${player.id}' class="counter_empty"></div></td>`;
                    playersMostGangsters += `<td><div id='recap_mostGangsters_${player.id}' class="counter_empty"></div></td>`;
                    playersTotal +=`<td><div id='recap_total_${player.id}' class="counter_empty"></div></td>`;
                });
                let html = `<table id="end_score_recap_table">
                        <thead>
                            <th></th>
                            ${playersNames}
                        </thead>
                        <tbody>
                            <tr>
                                <th>${_('Team Points')}</th>
                                ${playersTeamScore}
                            </tr>
                            <tr class="row_recap_heists">
                                <th>${_('Heist Points')}</div></th>
                                ${playersHeists}
                            </tr>
                            <tr class="row_recap_resources">
                                <th>${_('Resource Points')}</th>
                                ${playersResources}
                            </tr>
                            <tr class="row_recap_mostGangsters">
                                <th>${_('Influence: most Gangsters')}${teamIcon}</th>
                                ${playersMostGangsters}
                            </tr>
                            ${clansInfluence}}
                            <tr>
                                <th>${_('Influence: most money')}${moneyIcon}</th>
                                ${playersMostMoney}
                            </tr>
                            <tr class ='final_score'>
                                <th>${_('Final score')}</th>
                                ${playersTotal}
                            </tr>
                        </tbody>
                    </table>
                `;
                return html;
            },

            /* @Override BGA standard error handling (for specific errors only) */
            showMessage: function (msg, type) {
                let indexOfIconMoney = msg.indexOf("&lt;span class=");
                if (type == "error" && msg && indexOfIconMoney >= 0) {
                    //let iconMoney = '<span class=\"money\"></span>';
                    //msg = msg.substring(0, indexOfIconMoney) + iconMoney + msg.substring(indexOfIconMoney + iconMoney.length);
                    msg = msg.replace('&lt;span class=&quot;money&quot; style=&quot;z-index:10&quot;&gt;&lt;/span&gt;', '<span class=\"money\"></span>');
                }
                this.inherited(arguments);
            },

            ///////////////////////////////////////////////////
            //// Player's action

            /*

                Here, you are defining methods to handle player's action (ex: results of mouse click on
                game objects).

                Most of the time, these methods:
                _ check the action is possible at this game state.
                _ make a call to the game server

            */

            onPickAvGangster: function (evt) {
                debug("onPickAvGangster",evt);
                if (this.isReadOnly()) {
                    return;
                }
                let selectedTopDeck = false;
                if(this.possible_topdeck_gangster && evt != "avgangsters"){
                    this.avgangsters.unselectAll();
                    //If we select the top deck card
                    let targetGangsterDiv = evt.currentTarget;
                    let targetGangsterId = targetGangsterDiv.id.split("_")[1];
                    if(this.possible_topdeck_gangster.id == targetGangsterId){
                        if(targetGangsterDiv.classList.contains("selected")){
                            targetGangsterDiv.classList.remove("selected");
                        }
                        else {
                            selectedTopDeck = true;
                            targetGangsterDiv.classList.add("selected");
                        }
                    }
                }
                else if(evt == "avgangsters"){
                    this.cancelTopDeckRecruitSelection();
                }
                if (this.avgangsters.getSelectedItems().length == 0 && !selectedTopDeck) {
                    let button = document.getElementById('confRecruit_button');
                    if(button) button.classList.add('disabled');
                    return;
                }

                if (this.checkAction('discard', true)) {
                    this.onPickForDiscard('gangster');
                    this.avgangsters.unselectAll();
                    return;
                }

                if (!this.checkAction('recruitGangster')) {
                    this.avgangsters.unselectAll();
                    return;
                }

                this.onCancelHeist();
                if (this.avgangsters.getSelectedItems().length == 1) {
                    dojo.removeClass('confRecruit_button', 'disabled');
                } else if(selectedTopDeck){
                    dojo.removeClass('confRecruit_button', 'disabled');
                }
            },

            onPickAvHeist: function (evt) {
                debug("onPickAvHeist",evt);
                if (this.isReadOnly()) {
                    return;
                }
                let selectedTopDeck = false;
                if(this.possible_topdeck_heist && evt != "avheists"){
                    this.avheists.unselectAll();
                    //If we select the top deck card
                    let targetHeistDiv = evt.currentTarget;
                    let targetHeistId = targetHeistDiv.dataset.id;
                    if(this.possible_topdeck_heist.id == targetHeistId){
                        if(targetHeistDiv.classList.contains("selected")){
                            targetHeistDiv.classList.remove("selected");
                        }
                        else {
                            selectedTopDeck = true;
                            targetHeistDiv.classList.add("selected");
                        }
                    }
                }
                else if(evt == "avheists"){
                    this.cancelTopDeckHeistSelection();
                }
                if (this.avheists.getSelectedItems().length == 0 && !selectedTopDeck) {
                    this.onCancelHeist();
                    return;
                }

                if (this.checkAction('discard', true)) {
                    this.onPickForDiscard('heist');
                    this.avheists.unselectAll();
                    return;
                }

                if (!this.checkAction('performHeist')) {
                    this.avheists.unselectAll();
                    return;
                }

                this.cancelRecruitSelection();
                var selected = this.avheists.getSelectedItems();
                if (selected.length == 1) {
                    this.onSelectHeist(selected[0]);
                    // var heist_id = selected[0].id;
                    // this.ajaxcall( "/gangsta/gangsta/performHeist.html", { id: heist_id, lock: true }, this, function(result){} );
                }  else if(selectedTopDeck){
                    this.onSelectHeist(this.possible_topdeck_heist, true);
                } else {
                    this.avheists.unselectAll();
                }
            },

            onPickForDiscard: function (type) {
                debug('on pick for Discard ',type);
                if (!this.checkAction('discard')) {
                    this.avgangsters.unselectAll();
                    this.avheists.unselectAll();
                    return;
                }

                var gangster_id = 0;
                var heist_id = 0;
                if (type === 'gangster') {
                    var selected = this.avgangsters.getSelectedItems();
                    if (selected.length == 1) {
                        var gangster_id = selected[0].id;
                        this.avgangsters.unselectAll();
                    }
                }
                if (type === 'heist') {
                    var selected = this.avheists.getSelectedItems();
                    if (selected.length == 1) {
                        var heist_id = selected[0].id;
                        this.avheists.unselectAll();
                    }
                }
                this.bgaPerformAction('discard', {
                    gangster: gangster_id,
                    heist: heist_id,
                }, {} );
            },

            onPickPlayerMoney: function (playerid, event) {
                debug('onPickPlayerMoney',playerid, event);

                this.bgaPerformAction('steal', { target: playerid,} );
            },

            onPassForMoney: function () {
                this.bgaPerformAction('pass' );
            }, 

            onConfirmResource: function () {
                if (!this.checkPossibleActions('actSelectResource')) {
                    return;
                }

                this.bgaPerformAction('actSelectResource', { c: this.selectedResource },
                    {
                        //checkAction:false,//allow user to replay while waiting for others
                    }
                );
            }, 

            onConfirmRecoverNGangsters: function (all) {
                debug("onConfirmRecoverNGangsters",all,this.selectedDeadGangsters);
                if(!all && this.selectedDeadGangsters.length <1) return;

                this.bgaPerformAction('actRecover', { 
                        'all': all, 
                        'g_ids': this.selectedDeadGangsters.join(";"),
                    }, {
                    }
                );
            }, 

            onConfRecruit: function () {
                debug("onConfRecruit");
                let selected = this.avgangsters.getSelectedItems();
                if (selected.length == 1) {
                    var gangster_id = selected[0].id;
                    this.bgaPerformAction('recruitGangster',{'id': gangster_id});
                } else {
                    let topDeckGangsterDiv =  document.getElementById("gangsters_deck_topcard").querySelector(".gangster") ;
                    if(topDeckGangsterDiv && topDeckGangsterDiv.classList.contains("selected")) {
                        this.bgaPerformAction('recruitGangster',{'id': topDeckGangsterDiv.dataset.id});
                    }
                    else{
                        this.cancelRecruitSelection();
                    }
                }
            },

            onSelectHeist: function (heistCard, fromDeck = false) {
                debug("onSelectHeist",heistCard,fromDeck);
                if (this.isReadOnly()) {
                    return;
                }
                if(!fromDeck){
                    let avheistDiv = document.getElementById(`avheists_item_${heistCard.id}`);
                    if(!avheistDiv.classList.contains("selectable")){
                        this.avheists.unselectItem(heistCard.id);
                        return;
                    }
                }
                var heist = this.gamedatas[this.gamedatas.activePhaseName + '_type'][heistCard.type]
                var costs = heist.cost;
                this.currentHeist = heist;
                this.neededHeistSkills = new this.skillCount(costs);
                this.neededHeistSkills.ignoreSkills = this.possible_heists[ heistCard.id ].ignore_skills ;

                dojo.query('#skillcounter').style("display", "");
                dojo.addClass('commit_heist_button', 'disabled');
                dojo.query('.playertableau .gangster.selected').removeClass('selected');
                dojo.query('.current_player .playertableau .gangster').addClass('selectable');
                dojo.query('.current_player .playertableau .gangster:not(.mobilized)').addClass('selectable');
                
                if (this.neededHeistSkills.isComplete()) {
                    //Rare case of 1 skill needed and ignored by ability
                    dojo.removeClass('commit_heist_button', 'disabled');
                }
            },

            hideSkillCounter: function () {
                this.neededHeistSkills = {};
                this.currentHeist = null;
                dojo.query('.playertableau .gangster.selected').removeClass('selected');
                dojo.query('#skillcounter').style('display', 'none');
            },

            onSelectGangster: function (evt) {
                debug("onSelectGangster",evt);
                if (this.isReadOnly()) {
                    return;
                }
                //console.log("onSelectGangster");
                if (this.snitchAction > 0) {
                    this.onClickGangsterForTap(evt);
                    return;
                }
                if (this.snitchAction < 0) {
                    this.onClickGangsterForKill(evt);
                    return;
                }
                if (this.checkAction("performHeist", true)) {
                    this.onClickGangsterForHeist(evt);
                    //console.log(this.neededHeistSkills.needed);
                    return;
                }
                if (this.checkAction("untapGangsters", true) || this.checkAction("actEndUntapGangsters", true)) {
                    this.onClickGangsterForMobilization(evt);
                    return;
                }
                if (this.checkAction("teach", true)) {
                    this.onClickGangsterForTeach(evt);
                    return;
                }
                if (this.checkAction("tap", true)) {
                    this.onClickGangsterForTap(evt);
                    return;
                }
                if (this.checkAction("kill", true)) {
                    this.onClickGangsterForKill(evt);
                    return;
                }
                if (this.checkAction("snitchKill", true)) {
                    this.onClickGangsterForKill(evt);
                    return;
                }
                if (this.checkAction("gdgKill", true)) {
                    this.onClickGangsterForKill(evt);
                    return;
                }
            },

            onClickGangsterForMobilization: function (evt) {
                if (!this.isCurrentPlayerActive()) {
                    return;
                }
                var activeplayer = this.getActivePlayerId();
                var targetGangsterId = evt.currentTarget.id.split("_")[1];
                if (dojo.hasClass(evt.currentTarget.id, 'selectable')) {
                    if (dojo.hasClass(evt.currentTarget.id, 'selected')) {
                        if (this.currentMobilize.money > 0) {
                            this.currentMobilize.money -= 1;
                        }
                        this.currentMobilize.gangsters.splice(this.currentMobilize.gangsters.indexOf(targetGangsterId), 1);
                        dojo.removeClass(evt.currentTarget.id, 'selected');
                        dojo.addClass(evt.currentTarget.id, 'mobilized');
                    } else {
                        if (this.currentMobilize.gangsters.length >= this.currentMobilize.free && this.currentMobilize.money >= this.gamedatas.players[activeplayer].money) {
                            return;
                        }
                        if (this.currentMobilize.gangsters.length >= this.currentMobilize.free) {
                            this.currentMobilize.money += 1;
                        }
                        this.currentMobilize.gangsters.push(targetGangsterId);
                        dojo.removeClass(evt.currentTarget.id, 'mobilized');
                        dojo.addClass(evt.currentTarget.id, 'selected');
                    }
                }
            },

            onClickGangsterForHeist: function (evt) {
                debug("onClickGangsterForHeist",evt);
                if (!this.isCurrentPlayerActive()) {
                    return;
                }
                if (this.avheists.getSelectedItems().length == 0 && !this.possible_topdeck_heist) {
                    return;
                }
                //console.log("onClickGangsterForHeist");
                //console.log(this.neededHeistSkills.needed);
                if (dojo.hasClass(evt.currentTarget.id, 'mobilized')) {
                    return;
                }
                if (!dojo.hasClass(evt.currentTarget.id, 'selectable')) {
                    return;
                }

                var gangsterid = evt.currentTarget.id.split("_")[1];

                if (dojo.hasClass(evt.currentTarget.id, 'selected')) {
                    dojo.removeClass(evt.currentTarget.id, 'selected');
                    if (this.currentHeist['reward']['coopcash'] > 0 && this.neededHeistSkills.gangsters.indexOf(gangsterid) === 0) {
                        this.neededHeistSkills.clearGangsters();
                        dojo.query('.playertableau .gangster').removeClass('selected selectable');
                        dojo.query('.current_player .playertableau .gangster:not(.mobilized)').addClass('selectable');
                    } else {
                        this.neededHeistSkills.removeGangster(
                            this.gamedatas.gangster_type[this.gamedatas.tableau[gangsterid].type], gangsterid, this.gamedatas.tableau);
                    }
                } else {
                    var success = this.neededHeistSkills.involveGangster(
                        this.gamedatas.gangster_type[this.gamedatas.tableau[gangsterid].type], gangsterid, this.gamedatas.tableau);
                    if (!success) {
                        return;
                    }
                    dojo.addClass(evt.currentTarget.id, 'selected');
                    if (this.currentHeist['reward']['coopcash'] > 0 && this.neededHeistSkills.gangsters.length === 1) {
                        dojo.query('.playertableau .gangster:not(.mobilized)').addClass('selectable');
                    }
                }
                if (this.neededHeistSkills.isComplete()) {
                    dojo.removeClass('commit_heist_button', 'disabled');
                } else {
                    dojo.addClass('commit_heist_button', 'disabled');
                }
            },

            onClickGangsterForTeach: function (evt) {
                //console.log("onClickGangsterForTeach");
                if (!this.isCurrentPlayerActive()) {
                    return;
                }
                if (!dojo.hasClass(evt.currentTarget.id, 'selectable')) {
                    return;
                }
                dojo.query('.playertableau .gangster.selected').removeClass('selected');
                dojo.toggleClass(evt.currentTarget.id, 'selected');
            },

            onClickGangsterForTap: function (evt) {
                //console.log("onClickGangsterForTap");
                if (!this.isCurrentPlayerActive()) {
                    return;
                }
                if (!dojo.hasClass(evt.currentTarget.id, 'selectable')) {
                    return;
                }

                if (dojo.hasClass(evt.currentTarget.id, 'selected')) {
                    dojo.removeClass(evt.currentTarget.id, 'selected');
                } else {
                    if (dojo.query('.gangster.selectable.selected').length < this.currentTap) {
                        dojo.addClass(evt.currentTarget.id, 'selected');
                    }
                }
            },

            onClickGangsterForKill: function (evt) {
                //console.log("onClickGangsterForKill");
                if (!this.isCurrentPlayerActive()) {
                    return;
                }
                if (!dojo.hasClass(evt.currentTarget.id, 'selectable')) {
                    return;
                }

                dojo.query('.gangster.selected').removeClass('selected');
                dojo.addClass(evt.currentTarget.id, 'selected');
            },

            onConfirmHeist: function () {
                //console.log("Confirm Heist");
                var selected = this.avheists.getSelectedItems();

                if (selected.length == 0 && !this.possible_topdeck_heist) {
                    return;
                }

                if (!this.checkAction('performHeist')) {
                    onCancelHeist();
                    return;
                }

                //dojo.query(".gangster.selected").forEach(function(element){console.log(element.id);});
                var selectedGangsters = dojo.query(".gangster.selected").map(function (node) {
                    return node.id.split("_")[1];
                });
                //console.log(selectedGangsters.join(";"));
                if (selected.length == 1) {
                    var heist_id = selected[0].id;
                    this.bgaPerformAction('performHeist',{'id': heist_id,gangsters: selectedGangsters.join(";"),});
                } else {
                    let topDeckHeistDiv =  document.getElementById("heists_deck_topcard").querySelector(".heist") ;
                    if(topDeckHeistDiv && topDeckHeistDiv.classList.contains("selected")) {
                        this.bgaPerformAction('performHeist',{'id': topDeckHeistDiv.dataset.id,gangsters: selectedGangsters.join(";"),});
                    }
                }
            },

            onConfirmTap: function () {
                debug("confirm Tap");

                if (!this.checkAction('tap')) {
                    dojo.query('.gangster').removeClass('selected selectable');
                    return;
                }

                var selected = dojo.query('.gangster.selectable.selected');
                if (selected.length > 0 && selected.length <= this.currentTap) {
                    var selectedGangsters = dojo.query(".gangster.selectable.selected").map(function (node) {
                        return node.id.split("_")[1];
                    });
                    this.bgaPerformAction('tapGangsters',{
                        gangsters: selectedGangsters.join(";"),
                    }, {checkAction: false});//check false because of different action name
                }
            },

            onConfirmKill: function () {
                debug("onConfirmKill");

                if (!this.checkAction('kill')) {
                    dojo.query('.gangster').removeClass('selected selectable');
                    return;
                }

                var selected = dojo.query('.gangster.selectable.selected');
                if (selected.length == 1) {
                    var theId = selected[0].id.split("_")[1]
                    if (this.possibleTargets.indexOf(theId) > -1) {
                        //check false because of different action name
                        this.bgaPerformAction('killGangster',{gangster: theId,},{checkAction: false});
                    }
                }
            },

            onConfirmTeach: function () {
                debug("onConfirmTeach");

                if (!this.checkAction('teach')) {
                    dojo.query('.playertableau .gangster').removeClass('selected selectable');
                    return;
                }

                var selected = dojo.query('.playertableau .gangster.selectable.selected');

                if (selected.length == 1) {
                    var theId = selected[0].id.split("_")[1];
                    if (this.possibleTargets.indexOf(theId) > -1) {
                        this.bgaPerformAction('teachGangster',{
                            gangster: theId,
                        }, {checkAction: false});//check false because of different action name
                    }
                }
            },

            onConfirmSnitch() {
                debug("confirm Snitch");

                if (!this.checkAction('snitchKill')) {
                    return;
                }
                var selected = dojo.query('.playertableau .gangster.selectable.selected');
                if (selected.length == 1) {
                    var theId = selected[0].id.split("_")[1]
                    this.bgaPerformAction('snitchKill',{
                        gangster: theId,
                    });
                }
            },

            onConfirmGdg() {
                debug("confirm Gdg");

                var selected = dojo.query('.playertableau .gangster.selectable.selected');
                if (selected.length == 1) {
                    var theId = selected[0].id.split("_")[1];
                    //check false because of different action name
                    this.bgaPerformAction('gdgKill',{gangster: theId,},{checkAction: false});
                }
            },

            onMobilize() {
                debug("Confirm Mobilize");
                if (this.checkAction('untapGangsters')) {
                    if (this.currentMobilize && this.currentMobilize.gangsters.length === 0) {
                        return;
                    } //misclick protection we do nothing.
                    this.bgaPerformAction('untapGangsters',{
                        gangsters: this.currentMobilize.gangsters.join(";"),
                    });
                }
            },

            onCancelHeist: function () {
                debug("Cancel Heist");
                this.avheists.unselectAll();
                this.hideSkillCounter();
                dojo.query('.playertableau .gangster').removeClass('selectable selected');
                this.cancelTopDeckHeistSelection();
            },

            onSkipDiscard: function () {
                debug("onSkipDiscard");
                if (!this.checkAction('discard')) {
                    return;
                }
                //check false because of different action name
                this.bgaPerformAction('skipDiscard', {}, {checkAction: false} );
            },

            onSkip: function (isForced) {
                debug("onSkip",isForced);
                this.bgaPerformAction('skip', {forced: isForced == true} );
            },

            onSteal: function (player_id) {
                debug("onSteal",player_id);
                this.bgaPerformAction('steal',{
                    target: player_id,
                });
            },

            onMark: function (player_id) {
                debug("onMark",player_id);
                if (!this.checkAction('markForKill')) {
                    return;
                }
                //check false because of different action name
                this.bgaPerformAction('mark',{target: player_id},{checkAction: false});
            },

            ///////////////////////////////////////////////////
            //// Reaction to cometD notifications

            /*
                setupNotifications:

                In this method, you associate each of your game notifications with your local method to handle it.

                Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                      your gangsta.game.php file.

            */
            setupNotifications: function () {
                debug( 'notifications subscriptions setup' );

                // TODO: here, associate your game notifications with local methods

                //notifs without methods because nothing to do:
                // notEnoughMoney (can't fast recruit a gangster)
                // everyoneBroke (nobody has any money to steal)
                // everyoneTapped (no gangster to tap)
                // nobodyKillable (nobody has exactly 2 skills)

                dojo.subscribe('selectedResource', this, "notif_selectedResource");
                this.notifqueue.setSynchronous('selectedResource', 800);
                dojo.subscribe('selectedResourcePublic', this, "notif_selectedResourcePublic");
                this.notifqueue.setSynchronous('selectedResourcePublic', 800);
                
                dojo.subscribe('resourceState', this, "notif_resourceState");
                this.notifqueue.setSynchronous('resourceState', 800);

                dojo.subscribe('setupAvailableCards', this, "notif_setupAvailableCards");
                this.notifqueue.setSynchronous('setupAvailableCards', 500);

                dojo.subscribe('recruitGangster', this, "notif_recruitGangster");
                this.notifqueue.setSynchronous('recruitGangster', 1000);
                dojo.subscribe('recoverGangsters', this, "notif_recoverGangsters");
                this.notifqueue.setSynchronous('recoverGangsters', 1200);
                //this.notifqueue.setSynchronous( 'recruitGangster', 1000 );
                //this.notifqueue.setSynchronous( 'performHeist', 1000 );
                dojo.subscribe('pass', this, "notif_pass");
                dojo.subscribe('gainMoney', this, "notif_gainMoney");
                dojo.subscribe('gainReward', this, "notif_gainReward");
                this.notifqueue.setSynchronous('gainReward', 500);
                dojo.subscribe('performedHeistFromDeck', this, "notif_performedHeistFromDeck");
                this.notifqueue.setSynchronous('performedHeistFromDeck', 1000);
                dojo.subscribe('discardHeist', this, "notif_discardHeist");
                this.notifqueue.setSynchronous('discardHeist', 1000);
                dojo.subscribe('discardGangster', this, "notif_discardGangster");
                this.notifqueue.setSynchronous('discardGangster', 500);
                dojo.subscribe('receiveGDGRevenue', this, "notif_receiveGDGRevenue");
                dojo.subscribe('changeChapter', this, "notif_changeChapter");
                this.notifqueue.setSynchronous('changeChapter', 1000);
                dojo.subscribe('snitchRevealed', this, "notif_snitchRevealed");
                this.notifqueue.setSynchronous('snitchRevealed', 1000);
                dojo.subscribe('snitchGone', this, "notif_snitchGone");
                this.notifqueue.setSynchronous('snitchGone', 1000);
                dojo.subscribe('rally', this, "notif_rally");
                dojo.subscribe('endOfTurn', this, "notif_endOfTurn");
                this.notifqueue.setSynchronous('endOfTurn', 500);
                dojo.subscribe('notEnoughMoney', this, "notif_nothing");
                dojo.subscribe('stealMoney', this, "notif_steal");
                dojo.subscribe('synchronize', this, "notif_synchro");
                dojo.subscribe('mobilize', this, "notif_mobilize");
                dojo.subscribe('teach', this, "notif_teach");
                this.notifqueue.setSynchronous('teach', 500);
                dojo.subscribe('kill', this, "notif_kill");
                this.notifqueue.setSynchronous('kill', 500);
                dojo.subscribe('movedToHospital', this, "notif_movedToHospital");
                this.notifqueue.setSynchronous('movedToHospital', 1200);
                dojo.subscribe('diversion', this, "notif_diversion");
                this.notifqueue.setSynchronous('diversion', 500);
                dojo.subscribe('gainCoop', this, "notif_gainCoop");
                dojo.subscribe('snitchHandling', this, "notif_snitchHandling");
                dojo.subscribe('endPointsMostGangsters', this, "notif_endPointsMostGangsters");
                this.notifqueue.setSynchronous('endPointsMostGangsters', 500);
                dojo.subscribe('endPointsMoney', this, "notif_endPointsMoney");
                this.notifqueue.setSynchronous('endPointsMoney', 500);
                dojo.subscribe('endPointsClan', this, "notif_endPointsClan");
                this.notifqueue.setSynchronous('endPointsClan', 1000);
                dojo.subscribe('scoreUpdate', this, "notif_scoreUpdate");
                dojo.subscribe('scoreResource', this, "notif_scoreResource");
                this.notifqueue.setSynchronous('scoreResource', 300);
                
                dojo.subscribe('vaultUpdate', this, "notif_vaultUpdate");
                this.notifqueue.setSynchronous('vaultUpdate', 300);
                
                dojo.subscribe('revealPlayerHeists', this, "notif_revealPlayerHeists");
                this.notifqueue.setSynchronous('revealPlayerHeists', 1000);
                dojo.subscribe('computeFinalScore', this, "notif_computeFinalScore");
                this.notifqueue.setSynchronous('computeFinalScore', 1000);
                
                dojo.subscribe('reloadPage', this, "notif_reloadPage");
            },

            notif_scoreUpdate: function (notif) {
                debug( 'notif_scoreUpdate (private)', notif );

                this.changePlayerScore(notif.args.player_id, notif.args.new_influence);
                if(notif.args.heist_influence !=undefined) this.changePlayerHeistScore(notif.args.player_id, notif.args.heist_influence);
                if(notif.args.gangster_influence !=undefined) this.changePlayerTeamScore(notif.args.player_id, notif.args.gangster_influence);
            },
            notif_scoreResource: function (notif) {
                debug( 'notif_scoreResource : increase +n', notif );

                this.changePlayerResourceScore(notif.args.player_id, notif.args.new_res_score);
                this.changePlayerScore(notif.args.player_id, notif.args.new_influence);
            },
            notif_vaultUpdate: function (notif) {
                debug( 'notif_vaultUpdate : update vault content', notif );

                let totalVault = notif.args.vault;
                let playerid = notif.args.player_id;
                this.gamedatas.counters['panel_vault_' + playerid]['counter_value'] = totalVault;
                this.gamedatas.counters['board_vault_' + playerid]['counter_value'] = totalVault;

                this.displayPlayerVault(playerid);
            },

            notif_endPointsMostGangsters: async function (notif) {
                debug( 'notif_endPointsMostGangsters' , notif);
                let player_id = notif.args.player_id;
                let player_color = this.gamedatas.players[player_id].color;
                //animate scoring on panel icons
                this.displayScoring( `playerteamicon_${player_id}`, player_color, notif.args.amount,200);
                await this.wait(200); 
                this._counters[player_id].scoringRecap.mostGangsters.incValue(notif.args.amount);
                document.getElementById(`recap_mostGangsters_${player_id}`).classList.remove("counter_empty");
                this.changePlayerScore(player_id, notif.args.new_amount);
            },
            notif_endPointsMoney: async function (notif) {
                debug( 'notif_endPointsMoney' , notif);
                let player_id = notif.args.player_id;
                let player_color = this.gamedatas.players[player_id].color;
                //animate scoring on panel icons
                this.displayScoring( `player_cash_${player_id}`, player_color, notif.args.amount,200);
                await this.wait(200); 
                this._counters[player_id].scoringRecap.mostMoney.incValue(notif.args.amount);
                document.getElementById(`recap_mostMoney_${player_id}`).classList.remove("counter_empty");
                this.changePlayerScore(player_id, notif.args.new_amount);
            },
            notif_endPointsClan: async function (notif) {
                debug( 'notif_endPointsClan' , notif);
                let player_id = notif.args.player_id;
                let player_color = this.gamedatas.players[player_id].color;
                let clan_id = notif.args.clan_id;
                //animate scoring on panel icons
                this.displayScoring( `family-container-${player_id}-${clan_id}`, player_color, notif.args.amount,200);
                await this.wait(200); 
                this._counters[player_id].scoringRecap.clans[clan_id].incValue(notif.args.amount);
                document.getElementById(`recap_clanInfluence_${clan_id}_${player_id}`).classList.remove("counter_empty");
                this.changePlayerScore(player_id, notif.args.new_amount);
                
            },
            
            notif_computeFinalScore(notif) {
                debug('notif_computeFinalScore', notif);
                let datas = notif.args.datas;
                this.displayFinalScoringTable(datas);
            },

            notif_revealPlayerHeists: function (notif) {
                debug('notif_revealPlayerHeists', notif);
                this.gamedatas.playerheists = notif.args.playerheists;
                this.displayPlayerHeists(this.gamedatas.playerheists);
            },
            notif_nothing: function (notif) {
                //console.log( 'notif_nothing' );
                //console.log( notif );
            },

            notif_setupAvailableCards: async function (notif) {
                debug( 'notif_setupAvailableCards', notif );
                this.gamedatas.avheists = notif.args.avheists;
                this.gamedatas.avgangsters = notif.args.avgangsters;
                document.getElementById("avdecks").classList.remove("no_display");
                this.displayAvailableHeists(this.gamedatas.avheists);
                this.displayAvailableGangsters(this.gamedatas.avgangsters);
            },


            notif_selectedResource: async function (notif) {
                debug( 'notif_selectedResource ... private card',notif );

                let card = notif.args.card;
                let cardDiv = $('card_wrap_' + card.id);
                await this.addResourceCardInHand(card);
                let destinationDiv = document.getElementById(`player_resource_cards_${this.player_id}`);
                await this.animationManager.slideAndAttach(cardDiv, destinationDiv);

                dojo.empty('av_resources');
            },
            
            notif_selectedResourcePublic: async function (notif) {
                debug( 'notif_selectedResourcePublic ... public card',notif );

                let card = notif.args.card;
                let player_id = notif.args.player_id;
                let cardDiv = $('card_wrap_' + card.id);
                await this.addResourceCardInHand(card);
                cardDiv = $('card_wrap_' + card.id);
                await this.animationManager.fadeIn(cardDiv, $('av_resources'));
            },
            
            notif_resourceState: async function (notif) {
                debug( 'notif_resourceState ...',notif );

                let card = notif.args.card;
                let player_id = notif.args.player_id;
                let cardDiv = $('card_wrap_' + card.id);

                cardDiv.dataset.state = card.state;
            },

            notif_recruitGangster: async function (notif) {
                debug("notif_recruitGangster",notif);

                // var delay = 0;
                // for( let i = toint( $('playermoney_'+notif.args.player_id).innerHTML ); i> toint( notif.args.new_money ); i-- )
                // {
                //     delay += 100;
                //     var anim = this.slideTemporaryObject( this.format_block( 'jstpl_money', {} ), 'building_static_'+notif.args.building_card.id, 'playermoneyicon_'+notif.args.player_id, 'gangster_'+notif.args.worker_card.id , 500, delay );
                //     dojo.connect( anim, 'onBegin', function() {
                //         $('playercoin_'+notif.args.player_id).innerHTML = toint( $('playercoin_'+notif.args.player_id).innerHTML ) - 1;
                //     } );
                //     anim.play();
                // }
                let player_id = notif.args.player_id;
                let player_color = this.gamedatas.players[player_id].color;

                this.changePlayerMoney(notif.args.player_id, notif.args.new_money);
                this.changePlayerScore(notif.args.player_id, notif.args.new_influence);
                this.changePlayerTeamScore(notif.args.player_id, notif.args.team_score)

                if(notif.args.fromDeck){
                    let topDeckGangsterDiv = document.getElementById("gangsters_deck_topcard").querySelector(".card_wrap");
                    if(!topDeckGangsterDiv){
                        topDeckGangsterDiv = this.addGangster(this.player_id, notif.args.gangster_type, notif.args.gangster_id, 0, {'skill': "0"}, 'gangsters_deck_topcard')
                                            .parentNode;
                    }
                    topDeckGangsterDiv.querySelector(".gangster").classList.remove("selected");
                    await this.animationManager.slideAndAttach(topDeckGangsterDiv, $(`recruited_gangsters_${notif.args.player_id}`));
                    //Destroy after animation in order to use same method and clickHandler as other case
                    dojo.destroy(topDeckGangsterDiv);
                }
                else {
                    this.avgangsters.removeFromStockById(notif.args.gangster_id);
                }
                this.addGangster(notif.args.player_id, notif.args.gangster_type, notif.args.gangster_id, "0", {'skill': "0"});
                //this.displayScoring(`gangster_${notif.args.gangster_id}`, player_color, notif.args.influence);
                this.addToTableau(notif.args.gangster_type, notif.args.gangster_id, 0, notif.args.player_id, notif.args.order);
            },
            notif_recoverGangsters: async function (notif) {
                debug("notif_recoverGangsters (public)",notif);

                let player_id = notif.args.player_id;
                if(player_id != this.player_id){
                    //in this case, current player will receives a private score update right before this notif
                    this.changePlayerScore(player_id, notif.args.new_influence);
                }
                this.changePlayerTeamScore(player_id, notif.args.team_score);

                // move all recovered gangsters at once
                let pcards = Object.values(notif.args.gangsters);
                pcards.sort((a, b) => a.order - b.order);
                await Promise.all(
                    pcards.map(async (card, i) => {
                        let gangsterDiv = $("card_wrap_"+card.id);
                        gangsterDiv.querySelector(".gangster").classList.remove("dead");
                        this.addToTableau(card.type, card.id, 0, player_id, card.order);
                        await this.wait(300 * i).then(async () => 
                            await this.animationManager.slideAndAttach(gangsterDiv, $(`recruited_gangsters_${player_id}`))
                        );
                    })
                );
                
            },

            notif_endOfTurn: function (notif) {
                debug("notif_endOfTurn",notif);

                //add the new available heist from the deck
                if (notif.args.new_avheist != null && notif.args.new_avheist.id > 0) {
                    this.avheists.addToStockWithId(notif.args.new_avheist.type, notif.args.new_avheist.id, 'heistsdeck');
                    this.addGangstaTip(notif.args.new_avheist.type, 'heist', this.avheists.getItemDivId(notif.args.new_avheist.id));
                }

                if (notif.args.new_avgang != null && notif.args.new_avgang.id > 0) {
                    this.avgangsters.addToStockWithId(notif.args.new_avgang.type, notif.args.new_avgang.id, 'gangstersdeck');
                    this.addGangstaTip(notif.args.new_avgang.type, 'gangster', this.avgangsters.getItemDivId(notif.args.new_avgang.id));
                }
            },

            notif_pass: function (notif) {
                debug( 'notif_pass',notif );
                this.changePlayerMoney(notif.args.player_id, notif.args.new_money);
            },

            notif_gainMoney: function (notif) {
                debug( 'notif_gainMoney',notif );
                this.changePlayerMoney(notif.args.player_id, notif.args.new_money);
            },

            notif_performedHeistFromDeck: async function (notif) {
                debug("notif_performedHeistFromDeck", notif);
                let topDeckHeistDiv = document.getElementById("heists_deck_topcard").querySelector(".heist_wrap");
                if(!topDeckHeistDiv){
                    topDeckHeistDiv = this.addHeist(this.player_id, notif.args.heist, 'heists_deck_topcard').parentNode;
                }
                topDeckHeistDiv.querySelector(".heist").classList.remove("selected");
                await this.animationManager.slideAndAttach(topDeckHeistDiv, $(`panel_h_pts_${notif.args.player_id}`));
                
                this.changePlayerMoney(notif.args.player_id, notif.args.new_money);
            },
            notif_gainReward: async function (notif) {
                debug("notif_gainReward", notif);

                this.avheists.unselectAll();
                if(notif.args.fromDeck){
                    //see notif_performedHeistFromDeck
                }
                else {
                    this.avheists.removeFromStockById(notif.args.heist_id);
                }
                dojo.query('.playertableau .gangster.selected').removeClass('selected');
                notif.args.gangsters.forEach(element => {
                    dojo.addClass('gangster_' + element, 'mobilized');
                });

                this.gangstersInLastHeist = notif.args.gangsters;

                this.hideSkillCounter();

                this.changePlayerMoney(notif.args.player_id, notif.args.new_money);
                this.changePlayerScore(notif.args.player_id, notif.args.new_influence);
                if (notif.args.heist_score > 0 && this.isPublicVariant) {
                    this.changePlayerHeistScore(notif.args.player_id, notif.args.heist_score);
                }
            },

            notif_discardGangster: function (notif) {
                //console.log('notif_discardGangster');
                //console.log(notif);
                //remove the dicarded gangster
                this.avgangsters.removeFromStockById(notif.args.gangster_id);
                //add the newly available one
                if (notif.args.new_available != null)
                    this.avgangsters.addToStockWithId(notif.args.new_available.type, notif.args.new_available.id, 'gangstersdeck');
            },

            notif_discardHeist: function (notif) {
                //console.log('notif_discardHeist');
                //console.log(notif);
                //remove the discarded heist
                this.avheists.removeFromStockById(notif.args.heist_id);
                //add the new available one from the deck
                if (notif.args.new_available != null)
                    this.avheists.addToStockWithId(notif.args.new_available.type, notif.args.new_available.id, 'heistsdeck');
            },

            notif_receiveGDGRevenue: function (notif) {
                //console.log( 'notif_gainReward' );
                //console.log( notif );

                this.changePlayerMoney(notif.args.player_id, notif.args.new_money);
            },

            notif_changeChapter: function (notif) {
                //console.log( 'notif_changeChapter' );
                //console.log( notif );

                this.avheists.removeAll();
                this.snitchlist.removeAll();

                if (notif.args.phase == 1) {
                    dojo.removeClass('heists', 'genesis');
                    dojo.addClass('heists', 'gangwar');
                }
                if (notif.args.phase == 2) {
                    dojo.removeClass('heists', 'gangwar')
                    dojo.addClass('heists', 'domination');
                    $('victoryCond').style.removeProperty('display');
                }

                this.gamedatas.activePhaseName = this.gamedatas.constants.gamePhases[notif.args.phase];
                this.gamedatas.activePhaseId = notif.args.phase;

                for (let i in notif.args.heists) {
                    var heist = notif.args.heists[i];
                    this.avheists.addToStockWithId(heist.type, heist.id, 'heistsdeck');
                    this.addGangstaTip(heist.type, 'heist', this.avheists.getItemDivId(heist.id));
                }
            },

            notif_snitchRevealed(notif) {
                //console.log('notif_snitchRevealed');
                //console.log(notif);

                this.snitchlist.addToStockWithId(notif.args.snitch_type, notif.args.snitch_id, 'avheists_item_' + notif.args.snitch_id);
                this.avheists.removeFromStockById(notif.args.snitch_id);
            },

            notif_snitchGone: function (notif) {
                //console.log('notif_snitchGone');
                //console.log(notif);
                //remove the discarded heist
                this.snitchlist.addToStockWithId(notif.args.snitch_type, notif.args.snitch_id, 'avheists_item_' + notif.args.snitch_id);
                this.avheists.removeFromStockById(notif.args.snitch_id);
            },

            notif_rally(notif) {
                //console.log('notif_rally');
                //console.log(notif);

                dojo.query('#tableau_' + notif.args.player_id + ' .gangster').removeClass('mobilized');
            },

            notif_steal(notif) {
                //console.log('notif_steal');
                //console.log(notif);

                this.changePlayerMoney(notif.args.player_id, notif.args.new_amount_player);
                this.changePlayerMoney(notif.args.target_id, notif.args.new_amount_target);
            },

            notif_synchro(notif) {
                //console.log('notif_synchro');
                //console.log(notif);

                dojo.query('#tableau_' + notif.args.player_id + ' .gangster').removeClass('mobilized');
                for (var gangster in this.gamedatas.tableau) {
                    if (this.gamedatas.tableau[gangster].location_arg == notif.args.player_id) {
                        this.gamedatas.tableau[gangster].state = 0;
                    }
                }
            },

            notif_mobilize(notif) {
                //console.log('notif_mobilize');
                //console.log(notif);
                // 'player_id' => $player_id,
                // 'player_name' => self::getActivePlayerName(),
                // 'cost' => $cost,
                // 'count' => count($gangster_ids),
                // 'new_money' => $current_money-$cost,
                // 'gangsters' => $gangster_ids,
                this.currentMobilize = {};
                dojo.query('#tableau_' + notif.args['player_id'] + ' .gangster').removeClass('selectable selected');
                for (var gangster of notif.args.gangsters) {
                    dojo.removeClass($('gangster_' + gangster), 'mobilized');
                    this.gamedatas.tableau[gangster].state = 0;
                }

                this.changePlayerMoney(notif.args.player_id, notif.args.new_money);
            },

            notif_teach(notif) {
                //console.log('notif_teach');
                //console.log(notif);
                // 'player_id' => $player_id,
                // 'player_name' => self::getActivePlayerName(),
                // 'skill' => $skill_id,
                // 'skill_name' => $this->skill_name[$skill_id],
                // 'gangster' => $gangster_id,
                this.possibleTargets = [];
                dojo.query('.playertableau .gangster').removeClass('selectable selected');
                var theSkill = this.gamedatas.skill_name_invariant[notif.args.skill];
                var gangster = notif.args.gangster;
                this.gamedatas.tableau[gangster]['skill'] = notif.args.skill;
                this.gamedatas.counters['skill_' + this.gamedatas.skill_name_invariant[notif.args.skill] + '_' + notif.args.player_id].counter_value += 1;
                dojo.addClass($('gangster_' + gangster), 'extra_skill');
                dojo.place(this.format_block('jstpl_extraskill', {skill: theSkill}), 'gangster_' + gangster, "first");
            },

            notif_kill(notif) {
                debug('notif_kill',notif);
                // 'player_id' => $player_id,
                // 'player_name' => self::getActivePlayerName(),
                // 'gangster' => $gangster_id,
                var gangster = $('card_wrap_' + notif.args.gangster);

                dojo.destroy(gangster);
                this.removeFromTableau(notif.args.gangster, notif.args.player_id);

                this.looseScoreFromGangster(notif.args.player_id,notif.args.score_loss);
            },
            notif_movedToHospital(notif) {
                debug('notif_movedToHospital',notif);
                let player_id = notif.args.player_id;
                let gangster = notif.args.gangster;
                //let gangsterID = gangster.id;
                //let gangsterDiv = $('card_wrap_' + gangsterID);
                //gangsterDiv will be deleted by previous notif 'kill' with no animation

                this.looseScoreFromGangster(player_id,notif.args.score_loss);
                this.addGangsterInPlayerHospital(player_id,gangster);
            },

            notif_diversion(notif) {
                //console.log('notif_diversion');
                //console.log(notif);
                // 'player_id' => $player_id,
                // 'player_name' => self::getActivePlayerName(),
                // 'count' => count($gangster_ids),
                // 'gangsters' => $gangster_ids,
                this.currentTap = 0;

                dojo.query('.selectable').removeClass('selectable');
                dojo.query('.selected').removeClass('selected');
                for (var gangster of notif.args.gangsters) {
                    dojo.addClass($('gangster_' + gangster), 'mobilized');
                    this.gamedatas.tableau[gangster].state = 1;
                }
            },

            notif_gainCoop(notif) {
                //console.log('notif_gainCoop');
                //console.log(notif);
                //     'player_id' => $pid,
                //     'player_name' => $players[$pid]['player_name'],
                //     'money' => $coopReward,
                //     'new_money' => $players[$pid]['player_money']+$coopReward,

                this.changePlayerMoney(notif.args.player_id, notif.args.new_money);
            },

            notif_snitchHandling(notif) {
                //console.log('notif_snitchHandling');
                //console.log(notif);
                //     'snitch_id' => $snitch['id'],
                //     'player_name' => $pinfo['player_name'],
                //     'player_id' => $pinfo['player_id'],
                //     'tap_all' => true,
                //     'amount' => $deduction,
                //     'new_value' => $pinfo['player-money'] - $deduction,
                //modify the money

                this.changePlayerMoney(notif.args.player_id, notif.args.new_value);
            },
            
            //For debugging :
            notif_reloadPage(notif) {
                window.location.reload(true);
            },
        });
    });

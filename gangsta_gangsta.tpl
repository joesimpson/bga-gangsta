{OVERALL_GAME_HEADER}
<div id="gangstainterface">
  <div id="end_score_recap"></div>
  <div id="av_resources"></div>
  <div id="avdecks">
    <div id="twodecks">
      <div id="heists" class="whiteblock genesis">
        <div id="heistsdeck" class="deck">
          <div id="heists_deck_topcard" class="heist card_sized deck_topcard no_display"></div>
        </div>
        <div class="cardlist_wrapper"><div id="avheists"></div></div>
      </div>
      <div id="gangsters" class="whiteblock">
        <div id="gangstersdeck" class="deck">
          <div id="gangsters_deck_topcard" class="gangster card_sized deck_topcard no_display"></div>
        </div>
        <div class="cardlist_wrapper"><div id="avgangsters"></div></div>
      </div>
    </div>
    <div id="snitches">
      <div class="whiteblock">
        <h2>{SNITCHES_TITLE}</h2>
        <div class="cardlist_wrapper"><div id="snitchlist"></div></div>        
        <p id="victoryCond" style="display: none;">{SNITCHES_VICTORY_CONDITION}</p>
      </div>
    </div>
  </div>

  <!-- BEGIN player -->
  <div class="{PLAYER_CATEGORY}">
    <div class="whiteblock">
      <div class="chapters_separator"></div>
      <h3 class="player_title" style="color: #{PLAYER_COLOR}" id="playertitle_{PLAYER_ID}">
        {PLAYER_NAME}
        <div class="money" id="playermoneyicon_{PLAYER_ID}"></div>
        <span class="playermoney">
          x<span id="playermoney_{PLAYER_ID}">10</span></span
        >
        <div class="player_vault playerboard_vault no_display" id="playerboard_vault_wrap_{PLAYER_ID}">
          <i class="icon_vault fa6-solid fa6-vault fa6-lg"></i>
          x<span class="playerboard_vault" id="board_vault_{PLAYER_ID}">0</span>
        </div>
        
        <div class="firstplayerwrap">
          <div id="firstplayer_{PLAYER_ID}" class="firstplayer"></div>
        </div>
      </h3>

      <div id="tableau_{PLAYER_ID}" class="playertableau">
          <div id="player_resource_cards_{PLAYER_ID}" class="player_resource_cards"></div>
          <div id="recruited_gangsters_{PLAYER_ID}" class="cardlist_wrapper"></div>
      </div>
      <div id="dead_gangsters_{PLAYER_ID}" class="dead_gangsters cardlist_wrapper"></div>

      <div class="clear"></div>
    </div>
  </div>
  <!-- END player -->
</div>

<script type="text/javascript">
   var jstpl_resource_card = '<div class="card_wrap card_sized resource_card_wrap" id="card_wrap_${id}"  data-state="${state}" >\
                             <div class="resource_card card_sized" id="resource_card_${id}" data-type="${type}" data-state="${state}" title="${name}"></div>\
                         </div>';
   var jstpl_gangster = '<div class="card_wrap card_sized" id="card_wrap_${id}">\
                             <div class="gangster card_sized ${boss} ${extra}" id="gangster_${id}" style="background-position: -${backx}% -${backy}%"></div>\
                         </div>';

  var jstpl_heist =
    '<div class="heist_wrap card_sized" id="heist_wrap_${id}" data-id="${id}">\
      <div class="heist card_sized ${chapter_phase}" id="heist_${id}" data-id="${id}" data-type="${type}" style="background-position: -${backx}% -${backy}%"></div>\
    </div>';

  var jstpl_extraskill = '<div class="contain_skill"><span class="skill ${skill}"></span></div>';

  var jstpl_boardblock = "";

  var jstpl_required_skills = '\
          <span class="required_skill skill_1" data-value="${skill_1_value}">\
            <span class="skill leader"></span>\
            <span class="skill_1_value">${skill_1_value}</span>\
          </span>\
          <span class="required_skill skill_2" data-value="${skill_2_value}">\
            <span class="skill mercenary"></span>\
            <span class="skill_2_value">${skill_2_value}</span>\
          </span>\
          <span class="required_skill skill_3" data-value="${skill_3_value}">\
            <span class="skill informant"></span>\
            <span class="skill_3_value">${skill_3_value}</span>\
          </span>\
          <span class="required_skill skill_4" data-value="${skill_4_value}">\
            <span class="skill sniper"></span>\
            <span class="skill_4_value">${skill_4_value}</span>\
          </span>\
          <span class="required_skill skill_5" data-value="${skill_5_value}">\
            <span class="skill brawler"></span>\
            <span class="skill_5_value">${skill_5_value}</span>\
          </span>\
          <span class="required_skill skill_6" data-value="${skill_6_value}">\
            <span class="skill hacker"></span>\
            <span class="skill_6_value">${skill_6_value}</span>\
          </span>\
      </span>';
  var jstpl_tooltip_resource = '<div class="resource_card_tooltip">\
    <div class="resource_card_tooltip_block1"><h1 class="resource_card_title">${name}</h1></div>\
    <div class="resource_card_tooltip_block2">\
      <div class="resource_card_img_wrap">'+jstpl_resource_card+'</div>\
      <div class="resource_card_text">\
        <div class="resource_card_details">\
          <span class="resource_required_skills">\
            ${required_skills_label}\
            '+jstpl_required_skills+'\
          <hr>\
          <span class="resource_influence_label">${LABEL_INFLUENCE}</span>\
          <hr>\
          <span class="resource_ability">${ability}</span>\
        </div>\
      </div>\
    </div>\
    </div>';
  var jstpl_tooltip = '<div class="card_tooltip">\
      <h1 class="card_title">${title}</h1>\
      <div class="cardTip ${classes}" style="background-position: -${backx}% -${backy}%;"></div>\
    </div>';

  var jstpl_coin = '<div class="coin" style="z-index:10"></div>';
  var jstpl_money = '<div class="money" style="z-index:10"></div>';

  var jstpl_skillcounter =
  '<div id="skillcounter" class="roundedbox" style="text-align:center;display:none;">\
    <span id="skillcounter_text">${LABEL_REQUIRED_SKILLS}\
        '+jstpl_required_skills+'\
      <span id="skill_confirm_holder"></span>\
      <span id="skill_cancel_holder"></span>\
    </span>\
  </div>';

  //var jstpl_player_board = '\<div class="cp_board">\
  //    <div id="stoneicon_p${id}" class="gmk_stoneicon gmk_stoneicon_${color}"></div><span id="stonecount_p${id}">0</span>\
  //</div>';
  var jstpl_player_board = '\<div class="player-items" id="player-items-${id}">\
                    <div class="board-items" id="board-items-${id}">\
                      <div class="board-skills">\
                        <div class="skill-container">\
                          <div class="skill leader" title="${skill_name_1}"></div><span id="skill_leader_${id}">1</span>\
                        </div>\
                        <div class="skill-container">\
                          <div class="skill mercenary" title="${skill_name_2}"></div><span id="skill_mercenary_${id}">0</span>\
                        </div>\
                        <div class="skill-container">\
                          <div class="skill informant" title="${skill_name_3}"></div><span id="skill_informant_${id}">3</span>\
                        </div>\
                        <div class="skill-container">\
                          <div class="skill sniper" title="${skill_name_4}"></div><span id="skill_sniper_${id}">4</span>\
                        </div>\
                        <div class="skill-container">\
                          <div class="skill brawler" title="${skill_name_5}"></div><span id="skill_brawler_${id}">2</span>\
                        </div>\
                        <div class="skill-container">\
                          <div class="skill hacker" title="${skill_name_6}"></div><span id="skill_hacker_${id}">5</span>\
                        </div>\
                      </div>\
                      <div class="player_cash" id="player_cash_${id}">\
                          <div class="money" id="playermoneyicon_${id}"></div><span class="playermoney">&nbsp;<span class="boardValue" id="panel_money_${id}">10</span></span>\
                          <div class="player_vault ${vault_style}" id="player_vault_${id}">(<i class="icon_vault fa6-solid fa6-vault fa6-lg"></i><span class="boardValue panel_vault" id="panel_vault_${id}">0</span>)</div>\
                          <div class="team" id="playerteamicon_${id}"></div><span class="playerteam">&nbsp;<span class="boardValue" id="panel_team_${id}">0</span></span>\
                      </div>\
                      <div class="player_points">\
                          <span class="player_points_team">${teampoints}:&nbsp;<span class="boardValue" id="panel_t_pts_${id}">0</span></span> &nbsp;/&nbsp;\
                          <span class="player_points_heists">${heistpoints}:&nbsp;<span class="boardValue" id="panel_h_pts_${id}">?</span></span>\
                          <span class="player_points_resources_separator">&nbsp;/&nbsp;</span><span  class="player_points_resources">${resourcepoints}:&nbsp;<span class="boardValue" id="panel_r_pts_${id}">0</span></span>\
                      </div>\
                      <div id="board-family-${id}" class="board-family displaynone">\
                        <div id="family-container-${id}-1" class="family-container">\
                          <div class="family bratva" title="${fname_bratva}"></div><span class="boardValue" id="family_bratva_${id}">1</span>\
                        </div>\
                        <div id="family-container-${id}-4" class="family-container">\
                          <div class="family mafia" title="${fname_mafia}"></div><span class="boardValue" id="family_mafia_${id}">2</span>\
                        </div>\
                        <div id="family-container-${id}-5" class="family-container">\
                          <div class="family triads" title="${fname_triad}"></div><span class="boardValue" id="family_triad_${id}">3</span>\
                        </div>\
                        <div id="family-container-${id}-2" class="family-container">\
                          <div class="family cartel" title="${fname_cartel}"></div><span class="boardValue" id="family_cartel_${id}">4</span>\
                        </div>\
                        <div id="family-container-${id}-3" class="family-container">\
                          <div class="family ghetto" title="${fname_gang}"></div><span class="boardValue" id="family_gang_${id}">5</span>\
                        </div>\
                      </div>\
                    </div>\
                  </div>';

 var jstpl_game_help = '<div id="game-help">\
          <table cellspacing="0" cellpadding="0" border="0" width="100%">\
              <tbody>\
                  <tr>\
                      <th>\
                          <span class="reward-icon reward-income"></span>\
                      </th>\
                      <td>\
                          <h3>${income}</h3>\
                          <p>${income_t}</p>\
                      </td>\
                  </tr>\
                  <tr>\
                      <th>\
                          <span class="reward-icon reward-influence"></span>\
                      </th>\
                      <td>\
                          <h3>${influence}</h3>\
                          <p>${influence_t}</p>\
                      </td>\
                  </tr>\
                  <tr>\
                      <th>\
                          <span class="reward-icon reward-recruitement"></span>\
                      </th>\
                      <td>\
                          <h3>${recruit}</h3>\
                          <p>${recruit_t}</p>\
                      </td>\
                  </tr>\
                  <tr>\
                      <th>\
                          <span class="reward-icon reward-learning learning-leader"></span>\
                          <span class="reward-icon reward-learning learning-hacker"></span>\
                          <span class="reward-icon reward-learning learning-brawler"></span>\
                          <span class="reward-icon reward-learning learning-sniper"></span>\
                          <span class="reward-icon reward-learning learning-informant"></span>\
                          <span class="reward-icon reward-learning learning-mercenary"></span>\
                      </th>\
                      <td>\
                          <h3>${teach}</h3>\
                          <p>${teach_t}</p>\
                      </td>\
                  </tr>\
                  <tr>\
                      <th>\
                          <span class="reward-icon reward-play"></span>\
                      </th>\
                      <td>\
                          <h3>${replay}</h3>\
                          <p>${replay_t}</p>\
                      </td>\
                  </tr>\
                  <tr>\
                      <th>\
                          <span class="reward-icon reward-theft"></span>\
                      </th>\
                      <td>\
                          <h3>${steal}</h3>\
                          <p>${steal_t}</p>\
                      </td>\
                  </tr>\
                  <tr>\
                      <th>\
                          <span class="reward-icon reward-diversion"></span>\
                      </th>\
                      <td>\
                          <h3>${diversion}</h3>\
                          <p>${diversion_t}</p>\
                      </td>\
                  </tr>\
                  <tr>\
                      <th>\
                          <span class="reward-icon reward-assassination"></span>\
                      </th>\
                      <td>\
                          <h3>${kill}</h3>\
                          <p>${kill_t}</p>\
                      </td>\
                  </tr>\
                  <tr>\
                      <th>\
                          <span class="reward-icon reward-rallying"></span>\
                      </th>\
                      <td>\
                          <h3>${rally}</h3>\
                          <p>${rally_t}</p>\
                      </td>\
                  </tr>\
                  <tr>\
                      <th>\
                          <span class="reward-icon reward-coop"></span>\
                      </th>\
                      <td>\
                          <h3>${coop}</h3>\
                          <p>${coop_t}</p>\
                      </td>\
                  </tr>\
              </tbody>\
          </table>\
        </div>';
</script>

{OVERALL_GAME_FOOTER}



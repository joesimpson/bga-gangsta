{OVERALL_GAME_HEADER}
<div id="gangstainterface">
  <div id="avdecks">
    <div id="twodecks">
      <div id="heists" class="whiteblock genesis">
        <div id="heistsdeck" class="deck"></div>
        <div class="cardlist_wrapper"><div id="avheists"></div></div>
      </div>
      <div id="gangsters" class="whiteblock">
        <div id="gangstersdeck" class="deck"></div>
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
      <h3 class="player_title" style="color: #{PLAYER_COLOR}" id="playertitle_{PLAYER_ID}">
        {PLAYER_NAME}
        <div class="money" id="playermoneyicon_{PLAYER_ID}"></div>
        <span class="playermoney">
          x<span id="playermoney_{PLAYER_ID}">10</span></span
        >
        <div class="firstplayerwrap">
          <div id="firstplayer_{PLAYER_ID}" class="firstplayer"></div>
        </div>
      </h3>

      <div id="tableau_{PLAYER_ID}" class="playertableau">
          <div id="recruited_gangsters_{PLAYER_ID}" class="cardlist_wrapper"></div>
      </div>
      <div class="clear"></div>
    </div>
  </div>
  <!-- END player -->
</div>

<script type="text/javascript">
   var jstpl_gangster = '<div class="card_wrap card_sized" id="card_wrap_${id}">\
                             <div class="gangster card_sized ${boss} ${extra}" id="gangster_${id}" style="background-position: -${backx}% -${backy}%"></div>\
                         </div>';

  var jstpl_heist =
    '<div class="heist_space justplaced heist_${type}" id="heist_space_${id}">\
                        <div class="heist_wrap" id="heist_wrap_${id}">\
                            <div class="heist heist_${type}" id="heist_${id}" style="background-position: -${backx}% -${backy}%"></div>\
                        </div>\
                    </div>';

  var jstpl_extraskill = '<div class="contain_skill"><span class="skill ${skill}"></span></div>';

  var jstpl_boardblock = "";

  var jstpl_tooltip = '<div class="cardTip ${classes}" style="background-position: -${backx}% -${backy}%;"></div>';

  var jstpl_coin = '<div class="coin" style="z-index:10"></div>';
  var jstpl_money = '<div class="money" style="z-index:10"></div>';

  var jstpl_skillcounter =
  '<div id="skillcounter" class="roundedbox" style="text-align:center;display:none;">\
    <span id="skillcounter_text">${LABEL_REQUIRED_SKILLS}\
      <span id="skill_1">\
        <span class="skill leader"></span>\
        <span id="skill_1_value">0</span>\
      </span>\
      <span id="skill_2">\
        <span class="skill mercenary"></span>\
        <span id="skill_2_value">0</span>\
      </span>\
      <span id="skill_3">\
        <span class="skill informant"></span>\
        <span id="skill_3_value">0</span>\
      </span>\
      <span id="skill_4">\
        <span class="skill sniper"></span>\
        <span id="skill_4_value">0</span>\
      </span>\
      <span id="skill_5">\
        <span class="skill brawler"></span>\
        <span id="skill_5_value">0</span>\
      </span>\
      <span id="skill_6">\
        <span class="skill hacker"></span>\
        <span id="skill_6_value">0</span>\
      </span>\
      <span id="skill_confirm_holder"></span>\
      <span id="skill_cancel_holder"></span>\
    </span>\
  </div>';

  //var jstpl_player_board = '\<div class="cp_board">\
  //    <div id="stoneicon_p${id}" class="gmk_stoneicon gmk_stoneicon_${color}"></div><span id="stonecount_p${id}">0</span>\
  //</div>';
  var jstpl_player_board = '\<div class="player-items">\
                    <div class="board-items">\
                      <div class="board-skills">\
                        <div class="skill-container">\
                          <div class="skill leader" title="${skill_name_1}"></div><span id="skill_leader_${id}">1</span>\
                        </div>\
                        <div class="skill-container">\
                          <div class="skill hacker" title="${skill_name_6}"></div><span id="skill_hacker_${id}">5</span>\
                        </div>\
                        <div class="skill-container">\
                          <div class="skill sniper" title="${skill_name_4}"></div><span id="skill_sniper_${id}">4</span>\
                        </div>\
                        <div class="skill-container">\
                          <div class="skill brawler" title="${skill_name_5}"></div><span id="skill_brawler_${id}">2</span>\
                        </div>\
                        <div class="skill-container">\
                          <div class="skill informant" title="${skill_name_3}"></div><span id="skill_informant_${id}">3</span>\
                        </div>\
                        <div class="skill-container">\
                          <div class="skill mercenary" title="${skill_name_2}"></div><span id="skill_mercenary_${id}">0</span>\
                        </div>\
                      </div>\
                      <div class="player_cash">\
                          <div class="money" id="playermoneyicon_{PLAYER_ID}"></div><span class="playermoney">&nbsp;<span class="boardValue" id="panel_money_${id}">10</span></span>\
                          <div class="team" id="playerteamicon_{PLAYER_ID}"></div><span class="playerteam">&nbsp;<span class="boardValue" id="panel_team_${id}">0</span></span>\
                      </div>\
                      <div class="player_points">\
                          <span class="playerheist">${teampoints}:&nbsp;<span class="boardValue" id="panel_t_pts_${id}">0</span></span> &nbsp;/&nbsp;\
                          <span class="playerheist">${heistpoints}:&nbsp;<span class="boardValue" id="panel_h_pts_${id}">?</span></span>\
                      </div>\
                      <div class="board-family displaynone">\
                        <div class="family-container">\
                          <div class="family bratva" title="${fname_bratva}"></div><span class="boardValue" id="family_bratva_${id}">1</span>\
                        </div>\
                        <div class="family-container">\
                          <div class="family mafia" title="${fname_mafia}"></div><span class="boardValue" id="family_mafia_${id}">2</span>\
                        </div>\
                        <div class="family-container">\
                          <div class="family triads" title="${fname_triad}"></div><span class="boardValue" id="family_triad_${id}">3</span>\
                        </div>\
                        <div class="family-container">\
                          <div class="family cartel" title="${fname_cartel}"></div><span class="boardValue" id="family_cartel_${id}">4</span>\
                        </div>\
                        <div class="family-container">\
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



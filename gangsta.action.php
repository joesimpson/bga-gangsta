<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Gangsta implementation : © Benoit Ragoen <benoit.ragoen@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on https://boardgamearena.com.
 * See http://en.doc.boardgamearena.com/Studio for more information.
 * -----
 *
 * gangsta.action.php
 *
 * Gangsta main action entry point
 *
 *
 * In this file, you are describing all the methods that can be called from your
 * user interface logic (javascript).
 *
 * If you define a method "myAction" here, then you can call it from your javascript code with:
 * this.ajaxcall( "/gangsta/gangsta/myAction.html", ...)
 *
 */


  class action_gangsta extends APP_GameAction
  {
    // Constructor: please do not modify
   	public function __default()
  	{
  	    if( self::isArg( 'notifwindow') )
  	    {
            $this->view = "common_notifwindow";
  	        $this->viewArgs['table'] = self::getArg( "table", AT_posint, true );
  	    }
  	    else
  	    {
            $this->view = "gangsta_gangsta";
            self::trace( "Complete reinitialization of board game" );
      }
  	}

  	// TODO: defines your action entry points there
    public function recruitGangster()
    {
      self::setAjaxMode();

      $gangster_id= self::getArg( "id", AT_posint, true );

      $this->game->recruitGangster( $gangster_id );

      self::ajaxResponse( );
    }

    public function performHeist()
    {
      self::setAjaxMode();

      $heist_id= self::getArg( "id", AT_posint, true );

      $gangster_ids_raw = self::getArg( "gangsters", AT_numberlist, true );

      // Removing last ';' if exists
      if( substr( $gangster_ids_raw, -1 ) == ';' )
          $gangster_ids_raw = substr( $gangster_ids_raw, 0, -1 );
      if( $gangster_ids_raw == '' )
          $gangster_ids = array();
      else
          $gangster_ids = explode( ';', $gangster_ids_raw );

      $this->game->performHeist( $heist_id, $gangster_ids );

      self::ajaxResponse( );
    }

    public function untapGangsters()
    {
      self::setAjaxMode();

      $gangster_ids_raw = self::getArg( "gangsters", AT_numberlist, true );

      // Removing last ';' if exists
      if( substr( $gangster_ids_raw, -1 ) == ';' )
          $gangster_ids_raw = substr( $gangster_ids_raw, 0, -1 );
      if( $gangster_ids_raw == '' )
          $gangster_ids = array();
      else
          $gangster_ids = explode( ';', $gangster_ids_raw );

      $this->game->actionUntapGangsters( $gangster_ids );

      self::ajaxResponse( );
    }

    public function tapGangsters(){
      self::setAjaxMode();

      $gangster_ids_raw = self::getArg( "gangsters", AT_numberlist, true );

      // Removing last ';' if exists
      if( substr( $gangster_ids_raw, -1 ) == ';' )
          $gangster_ids_raw = substr( $gangster_ids_raw, 0, -1 );
      if( $gangster_ids_raw == '' )
          $gangster_ids = array();
      else
          $gangster_ids = explode( ';', $gangster_ids_raw );

      $this->game->actionTapGangsters( $gangster_ids );

      self::ajaxResponse( );
    }

    public function killGangster(){
      self::setAjaxMode();

      $gangster_id= self::getArg( "gangster", AT_posint, true );

      $this->game->actionKillGangster( $gangster_id );

      self::ajaxResponse( );
    }

    public function snitchKill(){
      self::setAjaxMode();

      $gangster_id= self::getArg( "gangster", AT_posint, true );

      $this->game->actionSnitchKill( $gangster_id );

      self::ajaxResponse( );
    }

    public function gdgKill(){
      self::setAjaxMode();

      $gangster_id= self::getArg( "gangster", AT_posint, true );

      $this->game->actionGdgKill( $gangster_id );

      self::ajaxResponse( );
    }

    public function teachGangster(){
      self::setAjaxMode();

      $gangster_id= self::getArg( "gangster", AT_posint, true );

      $this->game->actionTeachGangster( $gangster_id );

      self::ajaxResponse( );
    }


    public function pass()
    {
      self::setAjaxMode();

      $this->game->passForMoney(false);
      self::ajaxResponse();
    }

    public function discard()
    {
      self::setAjaxMode();

      $gangster_id= self::getArg( "gangster", AT_posint, true );
      $heist_id= self::getArg( "heist", AT_posint, true );

      $this->game->discard($gangster_id, $heist_id);

      self::ajaxResponse();
    }

    public function skipDiscard()
    {
      self::setAjaxMode();

      $this->game->skipDiscard();
      self::ajaxResponse();
    }

    public function skip()
    {
      self::setAjaxMode();

      $forced = self::getArg("forced", AT_bool, false, false);

      $this->game->skip($forced);
      self::ajaxResponse();
    }

    public function steal()
    {
      self::setAjaxMode();
      $playerid = self::getArg( "target", AT_posint, true );
      $this->game->steal($playerid);
      self::ajaxResponse();
    }

    public function mark()
    {
      self::setAjaxMode();
      $playerid = self::getArg( "target", AT_posint, true );
      $this->game->actionMark($playerid);
      self::ajaxResponse();
    }

  }



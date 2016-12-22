<?php
/**
 * Plugin Name: Dude GitHub feed
 * Plugin URL: https://www.dude.fi
 * Description: Fetches the user and repository activity from GitHub
 * Version: 1.0.0
 * Author: Timi Wahalahti / DUDE
 * Author URL: http://dude.fi
 * Requires at least: 4.4.2
 * Tested up to: 4.4.2
 *
 * Text Domain: dude-github-feed
 * Domain Path: /languages
 */

if( !defined( 'ABSPATH' )  )
	exit();

Class Dude_Github_Feed {
  private static $_instance = null;

  /**
   * Construct everything and begin the magic!
   *
   * @since   0.1.0
   * @version 0.1.0
   */
  public function __construct() {
    // Add actions to make magic happen
    add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
  } // end function __construct

  /**
   *  Prevent cloning
   *
   *  @since   0.1.0
   *  @version 0.1.0
   */
  public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'dude-github-feed' ) );
	} // end function __clone

  /**
   *  Prevent unserializing instances of this class
   *
   *  @since   0.1.0
   *  @version 0.1.0
   */
  public function __wakeup() {
    _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'dude-github-feed' ) );
  } // end function __wakeup

  /**
   *  Ensure that only one instance of this class is loaded and can be loaded
   *
   *  @since   0.1.0
   *  @version 0.1.0
	 *  @return  Main instance
   */
  public static function instance() {
    if( is_null( self::$_instance ) ) {
      self::$_instance = new self();
    }

    return self::$_instance;
  } // end function instance

  /**
   *  Load plugin localisation
   *
   *  @since   0.1.0
   *  @version 0.1.0
   */
  public function load_plugin_textdomain() {
    load_plugin_textdomain( 'dude-github-feed', false, dirname( plugin_basename( __FILE__ ) ).'/languages/' );
  } // end function load_plugin_textdomain

	public function get_user_activity( $username = '' ) {
		if( empty( $username ) )
			return;

		$transient_name = apply_filters( 'dude-github-feed/user_activity_transient', 'dude-github-user-'.$username, $username );
		$activity = get_transient( $transient_name );
	  if( !empty( $activity ) || false != $activity )
	    return $activity;

		$response = self::_call_api( 'users/'.$username );
		if( $response === FALSE )
			return;

		$i = 0;
		$activity = array();
		$event_types = apply_filters( 'dude-github-feed/user_activity_event_types', array( 'IssuesEvent', 'ForkEvent', 'ForkApplyEvent', 'GistEvent', 'IssueCommentEvent', 'PullRequestEvent', 'PushEvent', 'ReleaseEvent', 'WatchEvent' ) );
		$response = apply_filters( 'dude-github-feed/user_activity', json_decode( $response['body'], true ) );

		foreach( $response as $key => $event ) {
			if( $i >= apply_filters( 'dude-github-feed/user_activity_count', '5' ) ) {
				break;
			} elseif( !in_array( $event['type'], $event_types ) ) {
				continue;
			} else {
				$activity[] = $response[$key];
				$i++;
			}
		}

		set_transient( $transient_name, $activity, apply_filters( 'dude-github-feed/user_activity_lifetime', '600' ) );
		return $activity;
	} // end function get_users_activity

	public function get_repository_activity( $owner = '', $repository = '' ) {
		if( empty( $owner ) )
			return;

		if( empty( $repository ) )
			return;

		$transient_name = apply_filters( 'dude-github-feed/repository_activity_transient', 'dude-github-repo-'.$repository );
		$activity = get_transient( $transient_name );
	  if( !empty( $activity ) || false != $activity )
	    return $activity;

		$response = self::_call_api( 'repos/'.$owner.'/'.$repository );
		if( $response === FALSE )
			return;

		$i = 0;
		$activity = array();
		$event_types = apply_filters( 'dude-github-feed/repository_activity_event_types', array( 'ForkApplyEvent', 'IssueCommentEvent', 'PushEvent', 'ReleaseEvent' ) );
		$response = apply_filters( 'dude-github-feed/repository_activity', json_decode( $response['body'], true ) );

		foreach( $response as $key => $event ) {
			if( $i >= apply_filters( 'dude-github-feed/repository_activity_count', '5' ) ) {
				break;
			} elseif( !in_array( $event['type'], $event_types ) ) {
				continue;
			} else {
				$activity[] = $response[$key];
				$i++;
			}
		}

		set_transient( $transient_name, $activity, apply_filters( 'dude-github-feed/repository_activity_lifetime', '600' ) );
		return $activity;
	} // end function get_repository_activity

	public function get_repository_details( $owner = '', $repository = '' ) {
		if( empty( $owner ) )
			return;

		if( empty( $repository ) )
			return;

		$transient_name = apply_filters( 'dude-github-feed/repository_details_transient', 'dude-github-repo-details-'.$repository );
		$activity = get_transient( $transient_name );
	  	if( !empty( $activity ) || false != $activity )
	    		return $activity;

		$response = self::_call_api( 'repos/'.$owner.'/'.$repository, false );
		if( $response === FALSE )
			return;

		$i = 0;
		$activity = array();
		$response = apply_filters( 'dude-github-feed/repository_details', json_decode( $response['body'], true ) );

		set_transient( $transient_name, $response, apply_filters( 'dude-github-feed/repository_details_lifetime', '600' ) );
		return $response;
	} // end function get_repository_details

	private function _call_api( $endpoint = '', $events = true ) {
		if( empty( $endpoint ) )
			return false;

    		$url = 'https://api.github.com/'.$endpoint;
    		if( $events )
      			$url .= '/events';

		$response = wp_remote_get( $url );

		if( $response['response']['code'] !== 200 ) {
			self::_write_log( 'response status code not 200 OK, endpoint: '.$url );
			return false;
		}

		return $response;
	} // end function _call_api

	private function _write_log ( $log )  {
    if( true === WP_DEBUG ) {
      if( is_array( $log ) || is_object( $log ) ) {
        error_log( print_r( $log, true ) );
      } else {
        error_log( $log );
      }
    }
  } // end _write_log
} // end class Dude_Github_Feed

function dude_github_feed() {
  return new Dude_Github_Feed();
} // end function dude_github_feed

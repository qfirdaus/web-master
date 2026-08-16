<?php
/**
 * @version: $Id$
 * @package: SobiPro Entries Module Application
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * @copyright Copyright (C) 2017 - 2014 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license GNU/GPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3
 * as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/gpl.html and http://sobipro.sigsiu.net/licenses.
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * $Date$
 * $Revision$
 * $Author$
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadController( 'section' );
require_once( dirname( __FILE__ ) . '/view.php' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 06-Sep-2011 12:43:13
 */
class SPEntriesDJMTCtrl extends SPSectionCtrl
{
	
	public function getSourceEntries( $params ) {
		
		$entries = $this->entries($params);
		
		$entries = $this->entries( $params );
		$view = new SPEntriesDJMTView();
		$view->assign( $this->_model, 'section' );
		$view->assign( SPFactory::user()->getCurrent(), 'visitor' );
		$view->assign( $entries, 'entries' );
		$view->assign( $params, 'params' );
		
		return $view->getEntires();
	}
	
	/**
	 * @param $params
	 * @param bool $count
	 * @return array
	 */
	protected function entries( $params, $count = false )
	{
		if ( $params->get( 'fieldOrder' ) ) {
			$eOrder = $params->get( 'fieldOrder' );
		}
		else {
			$eOrder = $params->get( 'spOrder' );
		}
		$entriesRecursive = true;
		$conditions = array();
		$db = SPFactory::db();
		$limits = $params->get( 'spLimit' );
		if ( $limits ) {
			$limits = explode( '::', $limits );
			$fid = $limits[ 0 ];
			$value = $limits[ 1 ] == 'group' ? $limits[ 2 ] : $limits[ 1 ];
			$condition = array( 'fid' => $fid, 'optValue' => $value );
			if ( $limits[ 1 ] == 'group' ) {
				$condition[ 'optValue' ] = $db
						->select( 'optValue', 'spdb_field_option', array( 'optParent' => $value, 'fid' => $fid ) )
						->loadResultArray();
			}
			$conditions[ 'spo.id' ] = $db
					->select( 'sid', 'spdb_field_option_selected', $condition )
					->loadResultArray();
			if ( !( count( $conditions[ 'spo.id' ] ) ) ) {
				return array();
			}
		}
		$eDir = $params->get( 'spOrderDir' );
		$oPrefix = null;

		/* get the site to display */
		if ( $params->get( 'engine' ) != 'static' ) {
			$site = SPRequest::int( 'site', 1 );
		}
		else {
			$site = 1;
		}
		$eLimit = $params->get( 'entriesLimit' );
		$eLimStart = ( ( $site - 1 ) * $eLimit );

		/* get the ordering and the direction */
		if ( strstr( $eOrder, '.' ) ) {
			$eOrder = explode( '.', $eOrder );
			$eDir = $eOrder[ 1 ];
			$eOrder = $eOrder[ 0 ];
		}
		$sid = $params->get( 'sid' );
		$section = $params->get( 'section' );
		$this->setModel( $sid == $section ? 'section' : 'category' );
		$this->_model->init( $sid );
		$catId = SPRequest::int( 'pid' );
		$catId = $catId ? $catId : SPRequest::sid();
		if ( $params->get( 'autoListing', false ) && $catId && $catId != Sobi::Section() ) {
			$entries = Sobi::GetUserData( 'currently-displayed-entries', array() );
			if ( !( count( $entries ) ) && $catId ) {
				$entries = SPFactory::Category( $catId )
						->getChilds( 'entry', true, 1 );
				$entries = array_unique( $entries );
			}

			if ( count( $entries ) ) {
				$conditions[ 'spo.id' ] = $entries;
			}
		}
		else {
			if ( $entriesRecursive ) {
				$pids = $this->_model->getChilds( 'category', true );
				// getChilds doesn't includes the category id itself
				$pids[ $this->_model->get( 'id' ) ] = $this->_model->get( 'id' );
				if ( is_array( $pids ) ) {
					$pids = array_keys( $pids );
				}
				$conditions[ 'sprl.pid' ] = $pids;
			}
			else {
				$conditions[ 'sprl.pid' ] = $sid;
			}
			if ( $sid == -1 ) {
				unset( $conditions[ 'sprl.pid' ] );
			}
		}
		if ( count( $conditions ) ) {
			/* sort by field */
			if ( is_numeric( $eOrder ) ) {
				static $fields = array();
				$specificMethod = false;
				$field = isset( $fields[ $sid ] ) ? $fields[ $sid ] : null;
				if ( !$field ) {
					try {
						$fType = $db
								->select( 'fieldType', 'spdb_field', array( 'fid' => $eOrder ) )
								->loadResult();
					} catch ( SPException $x ) {
						Sobi::Error( $this->name(), SPLang::e( 'CANNOT_DETERMINE_FIELD_TYPE', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
					}
					if ( $fType ) {
						$field = SPLoader::loadClass( 'opt.fields.' . $fType );
					}
					$fields[ $sid ] = $field;
				}
				if ( $field && method_exists( $field, 'sortBy' ) ) {
					$table = null;
					$oPrefix = null;
					$specificMethod = call_user_func_array( array( $field, 'sortBy' ), array( &$table, &$conditions, &$oPrefix, &$eOrder, &$eDir ) );
				}
				if ( !( $specificMethod ) ) {
					$table = $db->join(
							array(
									array( 'table' => 'spdb_field', 'as' => 'fdef', 'key' => 'fid' ),
									array( 'table' => 'spdb_field_data', 'as' => 'fdata', 'key' => 'fid' ),
									array( 'table' => 'spdb_object', 'as' => 'spo', 'key' => array( 'fdata.sid', 'spo.id' ) ),
									array( 'table' => 'spdb_relations', 'as' => 'sprl', 'key' => array( 'fdata.sid', 'sprl.id' ) ),
							)
					);
					$oPrefix = 'spo.';
					$conditions[ 'spo.oType' ] = 'entry';
					$conditions[ 'fdef.fid' ] = $eOrder;
					$eOrder = 'baseData.' . $eDir;
				}
			}
			else {
				$table = $db->join(
						array(
								array( 'table' => 'spdb_relations', 'as' => 'sprl', 'key' => 'id' ),
								array( 'table' => 'spdb_object', 'as' => 'spo', 'key' => 'id' )
						)
				);
				$conditions[ 'spo.oType' ] = 'entry';
				if ( $eOrder == 'validUntil' ) {
					$eOrder = 'spo.validUntil';
				}
				$eOrder = $eOrder . '.' . $eDir;
				$oPrefix = 'spo.';
			}

			/* check user permissions for the visibility */
			if ( Sobi::My( 'id' ) ) {
				$this->userPermissionsQuery( $conditions, $oPrefix );
			}
			else {
				$conditions = array_merge( $conditions, array( $oPrefix . 'state' => '1', '@VALID' => $db->valid( $oPrefix . 'validUntil', $oPrefix . 'validSince' ) ) );
			}
			$conditions[ 'sprl.copy' ] = '0';
			try {
				if ( !( $count ) ) {
					$results = $db
							->select( $oPrefix . 'id', $table, $conditions, $eOrder, $eLimit, $eLimStart, true )
							->loadResultArray();
				}
				else {
					$results = $db
							->select( "COUNT( DISTINCT {$oPrefix}id )", $table, $conditions, $eOrder )
							->loadResult();
				}
			} catch ( SPException $x ) {
				Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
			}
			if ( $count ) {
				return $results;
			}
			$entries = array();
			if ( count( $results ) ) {
				foreach ( $results as $i => $sid ) {
					$entries[ $i ] = $sid;
				}
			}
			return $entries;
		}
		else {
			return array();
		}
	}
}

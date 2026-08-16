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

defined( '_JEXEC' ) or die();
define( 'SOBI_ROOT', JPATH_ROOT );
define( 'SOBI_PATH', SOBI_ROOT . '/components/com_sobipro' );
require_once( SOBI_PATH . '/lib/cms/joomla_common/elements/spsection.php' );

class JElementSPElements extends JElementSPSection
{
	protected $settings = null;
	protected $sections = array();

	public function setSettings( $setting )
	{
		$this->settings = $setting;
	}

	protected function determineTask()
	{
		try {
			$this->sections = SPFactory::db()
				->select( '*', 'spdb_object', array( 'oType' => 'section' ), 'id' )
				->loadObjectList( 'id' );
		} catch ( SPException $x ) {
		}
	}

	public static function getInstance()
	{
		static $instance = null;
		if ( !( $instance instanceof self ) ) {
			$instance = new self();
		}
		return $instance;
	}

	protected function field( &$data )
	{
		$f = SPConfig::fields( $this->settings()->get( 'section' ), array( 'inbox', 'select', 'calendar' ) );
		if ( count( $f ) ) {
			foreach ( $f as $field => $label ) {
				$data[ $field ] = $label;
			}
		}
	}

	private function limits( $selected )
	{
		$f = SPConfig::fields( $this->settings()->get( 'section' ), array( 'chbxgroup', 'select', 'radio' ) );
		$fields = array( '' => null );
		if ( count( $f ) ) {
			foreach ( $f as $id => $type ) {
				$labels = SPFactory::db()
					->select( array( 'sValue', 'language', 'sKey' ), 'spdb_language', array( 'fid' => $id, 'oType' => 'field_option' ) )
					->loadAssocList();
				/** @var SPField $field */
				$field = SPFactory::Model( 'field' );
				$field
					->init( $id )
					->loadType();
				$options = $field->get( 'options' );
				if ( count( $options ) ) {
					foreach ( $options as $value ) {
						$label = $value[ 'label' ];
						foreach ( $labels as $l ) {
							if ( $l[ 'sKey' ] == $value[ 'label' ] ) {
								$label = $l[ 'sValue' ];
							}
						}
						if ( isset( $value[ 'options' ] ) ) {
							$fields[ $type ][ $id . '::group::' . $value[ 'id' ] ] = '<b>' . $label . '</b>';
							foreach ( $value[ 'options' ] as $subOption ) {
								$fields[ $type ][ $id . '::' . $subOption[ 'id' ] ] = $label . ' &gt; ' . $subOption[ 'label' ];
							}
						}
						else {
							$fields[ $type ][ $id . '::' . $value[ 'id' ] ] = $label;
						}
					}
				}
			}
		}
		return SPHtml_Input::select( 'jform[params][spLimit]', $fields, $selected, false, array( 'style' => 'width: 300px' ) );
	}

	private function ordering( $selected = null )
	{
		$fData = array(
			'counter.asc' => Sobi::Txt( 'SEC.CFG.ENTRY_ORDER_BY_POPULARITY_ASCENDING' ),
			'counter.desc' => Sobi::Txt( 'SEC.CFG.ENTRY_ORDER_BY_POPULARITY_DESCENDING' ),
			'createdTime.asc' => Sobi::Txt( 'SEC.CFG.ENTRY_ORDER_BY_CREATION_DATE_ASC' ),
			'createdTime.desc' => Sobi::Txt( 'SEC.CFG.ENTRY_ORDER_BY_CREATION_DATE_DESC' ),
			'updatedTime.asc' => Sobi::Txt( 'SEC.CFG.ENTRY_ORDER_BY_UPDATE_DATE_ASC' ),
			'updatedTime.desc' => Sobi::Txt( 'SEC.CFG.ENTRY_ORDER_BY_UPDATE_DATE_DESC' ),
			'validUntil.asc' => Sobi::Txt( 'SEC.CFG.ENTRY_ORDER_BY_EXPIRATION_DATE_ASC' ),
			'validUntil.desc' => Sobi::Txt( 'SEC.CFG.ENTRY_ORDER_BY_EXPIRATION_DATE_DESC' ),
			'RAND()' => JText::_( 'SOBI_MOD_RANDOM' ),
		);
		$this->field( $fData[ JText::_( 'SOBI_ORDER_BY_FIELDS' ) ] );
		return SPHtml_Input::select( 'jform[params][spOrder]', $fData, $selected );
	}

	private function tplFile( $selected = null, $modal = false )
	{
		$dir = SOBI_PATH . '/usr/templates/front/modules/entries/' . ( $modal ? 'modal/' : null );
		if ( !( file_exists( $dir ) ) || ( count( scandir( $dir ) ) < 3 ) ) {
//			$this->installTpl( $modal );
		}
		if ( !( file_exists( SOBI_PATH . '/usr/templates/front/modules/entries/default2.xsl' ) ) ) {
//			$this->installTpl( false );
		}
		$files = scandir( SOBI_PATH . '/usr/templates/front/modules/entries/' . ( $modal ? 'modal/' : null ) );
		$tpls = array();
		if ( count( $files ) ) {
			foreach ( $files as $file ) {
				$stack = explode( '.', $file );
				if ( array_pop( $stack ) == 'xsl' ) {
					$tpls[ $file ] = $file;
				}
			}
		}
		if ( !( $modal ) ) {
			return SPHtml_Input::select( 'jform[params][tplFile]', $tpls, $selected );
		}
		else {
			return SPHtml_Input::select( 'jform[params][modalTemplate]', $tpls, $selected );
		}
	}

	// moved to the installer script
//	private function installTpl( $modal = false )
//	{
//		$dir = SOBI_PATH . '/usr/templates/front/modules/entries/' . ( $modal ? 'modal/' : null );
//		SPFs::mkDir( $dir );
//		$files = array(
//				'entries-mod.js' => 'components/com_sobipro/lib/js/entries-mod.js',
//				'emod.php' => 'components/com_sobipro/lib/ctrl/emod.php',
//				'vemod.php' => 'components/com_sobipro/lib/views/emod.php',
//		);
//		foreach ( $files as $from => $to ) {
//			if ( SPFs::exists( SOBI_ROOT . '/' . $to ) ) {
//				SPFs::delete( SOBI_ROOT . '/' . $to );
//			}
//			SPFs::copy( dirname( __FILE__ ) . '/install/' . $from, SOBI_ROOT . '/' . $to );
//		}
//		if ( !( $modal ) ) {
//			$files = scandir( dirname( __FILE__ ) . '/install/tmpl/' );
//			if ( count( $files ) ) {
//				foreach ( $files as $file ) {
//					if ( strstr( $file, '.xsl' ) ) {
//						SPFs::copy( dirname( __FILE__ ) . '/install/tmpl/' . $file, SOBI_PATH . '/usr/templates/front/modules/entries/' . $file );
//					}
//				}
//			}
//		}
//		else {
//			$files = scandir( dirname( __FILE__ ) . '/install/tmpl/modal/' );
//			if ( count( $files ) ) {
//				foreach ( $files as $file ) {
//					if ( strstr( $file, '.xsl' ) ) {
//						SPFs::copy( dirname( __FILE__ ) . '/install/tmpl/modal/' . $file, SOBI_PATH . '/usr/templates/front/modules/entries/modal/' . $file );
//					}
//				}
//			}
//		}
//	}

	protected function settings()
	{
		static $settings = null;
		if ( !( $settings ) ) {
			$settings = new JRegistry( $this->settings );
		}
		return $settings;
	}


	public function fetchElement( $name, &$label )
	{
		$sid = $this->settings()->get( 'sid' );
		$this->oType = 'section';
		switch ( $name ) {
			case 'sid':
				$params = array( 'id' => 'sid', 'size' => 5, 'class' => 'text_area', 'style' => 'text-align: center;', 'readonly' => 'readonly' );
				return SPHtml_Input::text( 'jform[params][sid]', $sid, $params );
				break;
			case 'tplFile':
			case 'modalTemplate':
				return $this->tplFile( $this->settings()->get( 'tplFile' ), $name == 'modalTemplate' );
				break;
			case 'spOrder':
				return $this->ordering( $this->settings()->get( 'spOrder' ) );
				break;
			case 'spLimit':
				return $this->limits( $this->settings()->get( 'spLimit' ) );
				break;
			case 'cid':
				if ( !( in_array( $sid, array_keys( $this->sections ) ) ) ) {
					$catName = SPLang::translateObject( $sid, array( 'name' ) );
					if ( isset( $catName[ $sid ][ 'value' ] ) ) {
						$this->oName = $catName[ $sid ][ 'value' ];
						$this->oType = 'category';
					}
				}
				return $this->getCat();
				break;
			default:
				$sections = array();
				if ( count( $this->sections ) ) {
					$sections[ ] = Sobi::Txt( 'SELECT_SECTION' );
					foreach ( $this->sections as $section ) {
						if ( Sobi::Can( 'section', 'access', 'valid', $section->id ) ) {
							$s = SPFactory::Model( 'section' );
							$s->extend( $section );
							$sections[ $s->get( 'id' ) ] = $s->get( 'name' );
						}
					}
				}
				$params = array( 'id' => 'spsection', 'class' => 'text_area required' );
				return SPHtml_Input::select( 'jform[params][section]', $sections, $this->settings()->get( 'section' ), false, $params );
				break;
		}
	}
}

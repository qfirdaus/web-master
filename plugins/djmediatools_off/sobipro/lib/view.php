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

defined( '_JEXEC' ) || die( 'Direct Access to this location is not allowed.' );
SPLoader::loadView( 'section' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 04-Apr-2011 10:13:08
 */
class SPEntriesDJMTView extends SPSectionView
{
	public function getEntires() {
		
		$data = array();
		$entries = $this->get( 'entries' );
		$params = $this->get( 'params' );

		if ( count( $entries ) ) {
			$this->loadNonStaticData( $entries );
			foreach ( $entries as $eid ) {
				$en = $this->entry( $eid, false, true );
				$data[] = $en;
			}
		}
		
		return $data;
	}
}

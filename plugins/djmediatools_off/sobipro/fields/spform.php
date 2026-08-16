<?php
/**
 * @version: $Id$
 * @package: SobiPro Entries Module Application
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * @copyright Copyright (C) 2017 - 2013 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
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
JLoader::import( 'joomla.html.parameter.element' );
require_once dirname( __FILE__ ) . '/spelements.php';

class JFormFieldSPForm extends JFormField
{
	protected $spElement = null;

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		$this->spElement->setSettings( $this->form->getValue( 'params' ) );
		return $this->spElement ->fetchElement( $this->fieldname, $this->label );
	}

	public function __construct( $jform = null )
	{
		parent::__construct( $jform );
		$this->spElement  = JElementSPElements::getInstance();
	}
}

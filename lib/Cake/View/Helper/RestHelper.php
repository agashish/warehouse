<?php
/**
 * Html Helper class file.
 *
 * Simplifies the construction of HTML elements.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       Cake.View.Helper
 * @since         CakePHP(tm) v 0.9.1
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('AppHelper', 'View/Helper');
App::uses('CakeResponse', 'Network');

/**
 * Html Helper class for easy use of HTML widgets.
 *
 * HtmlHelper encloses all methods needed while working with HTML pages.
 *
 * @package       Cake.View.Helper
 * @link http://book.cakephp.org/2.0/en/core-libraries/helpers/html.html
 */
class RestHelper extends AppHelper
{
	public $base;
	public $current;
	public $price;
	
	/*public function __construct()
	{
		
        RestHelper::getRates( $base , $current , $price );
    }*/
	
	 public function getRates( $base , $current , $price )
	 {
		 //echo $base .' , '. $current .' , '. $price;
		 //$base = explode('==',$base);
		 App::import('Controller', 'Currents');
		 $currentControl = new CurrentsController();
	 return $currentControl->getCurrentRate($base , $current , $price);
	 }
	 
}

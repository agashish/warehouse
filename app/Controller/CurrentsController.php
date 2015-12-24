<?php
error_reporting(0);
//ini_set('memory_limit', '-1');

class CurrentsController extends AppController
{	
	
	private $fxRate;	
	public function setParam($currencyBase, $currencyForeign)
	{
		$url = 'http://download.finance.yahoo.com/d/quotes.csv?s='
			.$currencyBase .$currencyForeign .'=X&f=l1';
		$c = curl_init($url);
		curl_setopt($c, CURLOPT_HEADER, 0);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		$this->fxRate = doubleval(curl_exec($c));
		curl_close($c);
	}

	public function toBase($amount)
	{
		if($this->fxRate == 0)
			return 0;
			
		return  $amount / $this->fxRate;
	}
	
	public function toForeign($amount)
	{
		if($this->fxRate == 0)
			return 0;
			
		return $amount * $this->fxRate;
	}
	
	
	public function index()
	{
		$this->layout = 'index';
		$this->autoRender = false;
		
		$auPrice = 16.04;
		$this->setParam('USD', 'GBP');
		echo '<p>Your price is AU$'. $this->fmtMoney($auPrice)
        .' which is approximately &euro;'. $this->fmtMoney($this->toForeign($auPrice)) .'</p>';
		exit;
	}
	
	public function getCurrentRate( $baseCurrency , $current , $price )
	{
		//echo $baseCurrency .','. $current .','. $price; exit;
		$this->setParam( $baseCurrency,$current );		
		return $this->fmtMoney($this->toForeign($price));
	}
	
// This function formats a value with 2 decimal places.
    function fmtMoney($amount)
    {
        return sprintf('%.2f', $amount);
    }
}

?>

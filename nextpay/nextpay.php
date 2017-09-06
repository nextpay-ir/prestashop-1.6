<?php
/**d
 * @package    Nextpay payment module
 * @author     Nextpay
 * @copyright  2015
 * @version    1.00
 */

if (!defined('_PS_VERSION_'))
	exit ;

include_once dirname(__FILE__).'/include/nextpay_payment.php';

class nextpay extends PaymentModule {

	private $_html = '';
	private $_postErrors = array();

	public function __construct() {

		$this->name = 'nextpay';
		$this->tab = 'payments_gateways';
		$this->version = '1.0';
		$this->author = 'Nextpay';
		$this->currencies = true;
		$this->currencies_mode = 'radio';
		parent::__construct();
		$this->displayName = $this->l('Nextpay Payment Modlue');
		$this->description = $this->l('Online Payment With Nextpay');
		$this->confirmUninstall = $this->l('Are you sure you want to delete your details?');
		if (!sizeof(Currency::checkPaymentCurrencies($this->id)))
			$this->warning = $this->l('No currency has been set for this module');
		$config = Configuration::getMultiple(array('nextpay_API'));
		if (!isset($config['nextpay_API']))
			$this->warning = $this->l('You have to enter your nextpay Api key to use nextpay for your online payments.');

	}

	public function install() {
		if (!parent::install() || !Configuration::updateValue('nextpay_API', '') || !Configuration::updateValue('nextpay_LOGO', '') || !Configuration::updateValue('nextpay_HASH_KEY', $this->hash_key()) || !$this->registerHook('payment') || !$this->registerHook('paymentReturn'))
			return false;
		else
			return true;
	}

	public function uninstall() {
		if (!Configuration::deleteByName('nextpay_API') || !Configuration::deleteByName('nextpay_LOGO') || !Configuration::deleteByName('nextpay_HASH_KEY') || !parent::uninstall())
			return false;
		else
			return true;
	}

	public function hash_key() {
		$en = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');
		$one = rand(1, 26);
		$two = rand(1, 26);
		$three = rand(1, 26);
		return $hash = $en[$one] . rand(0, 9) . rand(0, 9) . $en[$two] . $en[$tree] . rand(0, 9) . rand(10, 99);
	}

	public function getContent() {

		if (Tools::isSubmit('nextpay_setting')) {

			Configuration::updateValue('nextpay_API', $_POST['nx_API']);
			Configuration::updateValue('nextpay_LOGO', $_POST['nx_LOGO']);
			$this->_html .= '<div class="conf confirm">' . $this->l('Settings updated') . '</div>';
		}

		$this->_generateForm();
		return $this->_html;
	}

	private function _generateForm() {
		$this->_html .= '<div align="center"><form action="' . $_SERVER['REQUEST_URI'] . '" method="post">';
		$this->_html .= $this->l('Please Enter Your Api Key :') . '<br/><br/>';
		$this->_html .= '<input type="text" name="nx_API" value="' . Configuration::get('nextpay_API') . '" ><br/><br/>';
		$this->_html .= '<input type="submit" name="nextpay_setting"';
		$this->_html .= 'value="' . $this->l('Save it!') . '" class="button" />';
		$this->_html .= '</form><br/></div>';
	}

	public function do_payment($cart) {
		
		$Nextpay_API_Key = Configuration::get('nextpay_API');
		$amount = floatval(number_format($cart ->getOrderTotal(true, 3), 2, '.', ''));
		$callbackUrl = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . __PS_BASE_URI__ . 'modules/nextpay/nx.php?do=call_back&amount=' . $amount ;
		$orderId = $cart ->id;

        $parameters = array (
            "api_key"=> $Nextpay_API_Key,
            "order_id"=> $orderId,
            "amount"=> $amount,
            "callback_uri"=> $callbackUrl
        );

        $nextpay = new Nextpay_Payment($parameters);
        $res = $nextpay->token();
		
		$hash = Configuration::get('nextpay_HASH');
		$_SESSION['order' . $orderId] = md5($orderId . $amount . $hash);


		if(intval($res->code) == -1){
		    echo $this->success($this->l('Redirecting...'));
			echo '<script>window.location=("http://api.nextpay.org/gateway/payment/' .  $res->trans_id . '");</script>';
		} else {
            echo $this->error($this->l('There is a problem.') . ' (' . $res->code . ')');
		}
	}

	public function error($str) {
		return '<div class="alert error">' . $str . '</div>';
	}

	public function success($str) {
		echo '<div class="conf confirm">' . $str . '</div>';
	}

	public function hookPayment($params) {
		global $smarty;
		$smarty ->assign('nextpay_logo', Configuration::get('nextpay_LOGO'));
		if ($this->active)
			return $this->display(__FILE__, 'nextpay_pay.tpl');
	}

	public function hookPaymentReturn($params) {
		if ($this->active)
			return $this->display(__FILE__, 'zpconfirmation.tpl');
	}

}

// End of: nextpay.php
?>

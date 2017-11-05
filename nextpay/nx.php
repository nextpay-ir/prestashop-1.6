<?php
/**
 * @package    Nextpay payment module
 * @author     Nextpay
 * @copyright  2014
 * @version    1.00
 */
@session_start();
if (isset($_GET['do'])) {
	include (dirname(__FILE__) . '/../../config/config.inc.php');
	include (dirname(__FILE__) . '/../../header.php');
	include_once (dirname(__FILE__).'/nextpay.php');
	include_once (dirname(__FILE__).'/include/nextpay_payment.php');
	$nextpay = new nextpay;
//	if (!$cookie -> isLogged())
//		Tools::redirect('authentication.php?back=order.php');
	if ($_GET['do'] == 'payment') {
		//if (isset($_POST['id'])) {
			$nextpay -> do_payment($cart);
	//	} else {
		//	echo $nextpay -> error($nextpay -> l('There is a problem.'));
		//}
	} else {
		if (isset($_POST['order_id']) && isset($_POST['trans_id']) && isset($_GET['amount'])) {
            error_reporting(E_ALL);
			$amount = $_GET['amount'];
			$orderId = $_POST['order_id'];
			$trans_id = $_POST['trans_id'] ;
			if (isset($_SESSION['order' . $orderId])) {
				$hash = Configuration::get('nextpay_HASH');
				$hash = md5($orderId . $amount . $hash);
				if ($hash == $_SESSION['order' . $orderId]) {
					$api_key = Configuration::get('nextpay_API');

				    $parameters = array
                    (
                        'api_key'	=> $api_key,
                        'order_id'	=> $orderId ,
                        'trans_id' 	=> $trans_id,
                        'amount'	=> $amount
                    );

                    $nextpay_payment = new Nextpay_Payment();
                    $result = $nextpay_payment->verify_request($parameters);

					if (intval($result) == 0) {
						$nextpay -> validateOrder($orderId, _PS_OS_PAYMENT_, $amount, $nextpay -> displayName, "سفارش تایید شده / کد تراکنش {$trans_id}", array(), $cookie -> id_currency);
						$_SESSION['order' . $orderId] = '';
						Tools::redirect('history.php');
					} else {
						echo $nextpay -> error($nextpay_payment->code_error($result) . ' (' . $result . ')<br/>' . $nextpay -> l('Transaction ID') . ' : ' . $trans_id);
					}

				} else {
					echo "hash session in not valid";
					echo $nextpay -> error($nextpay -> l('There is a problem.'));
				}
			} else {
				echo "session not found";
				echo $nextpay -> error($nextpay -> l('There is a problem.'));
			}
		} else {
			echo "params not send";
			echo $nextpay -> error($nextpay -> l('There is a problem.'));
		}
	}
	include_once (dirname(__FILE__) . '/../../footer.php');
} else {
	_403();
}
function _403() {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
}

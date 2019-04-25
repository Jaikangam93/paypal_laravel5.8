<?php

namespace App\Http\Controllers;

use PayPal\Api\Item;
use PayPal\Api\Payer;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Payment;
use PayPal\Api\ItemList;
use PayPal\Api\Transaction;
use Illuminate\Http\Request;
use PayPal\Api\RedirectUrls;
use PayPal\Api\PaymentExecution;


class PaymentController extends Controller
{
	
	public function create() {

		$apiContext = new \PayPal\Rest\ApiContext(
            new \PayPal\Auth\OAuthTokenCredential(
                'AcVQRamXal0Izrci-lgNefwpZ81hh-OusAJ8_xAqoMwzMUtEFW-kJoSOsc_keVkyBwUaYC4Y7KR54s1X',   //Client ID of your Paypal sandbox accunt
                'EJFnXVRloUMo9nVRHS-VrbLs769pNiUQ5QEHmQqrsvV-aOntcCRdIPRNY_KdpluIiOw4oNtA22SrqkQa',  //Client Secret of your Paypal sandbox accunt  
		));
	
			
		$payer = new Payer();
		$payer->setPaymentMethod("paypal");

		$item1 = new Item();
		$item1->setName('Ground Coffee 40 oz')
				->setCurrency('USD')
				->setQuantity(1)
				->setSku("123123") // Similar to `item_number` in Classic API
				->setPrice(7.5);
		$item2 = new Item();
		$item2->setName('Granola bars')
				->setCurrency('USD')
				->setQuantity(5)
				->setSku("321321") // Similar to `item_number` in Classic API
				->setPrice(2);

		$itemList = new ItemList();
		$itemList->setItems(array($item1, $item2));

		$details = new Details();
		$details->setShipping(1.2)
				->setTax(1.3)
				->setSubtotal(17.50);

		$amount = new Amount();
		$amount->setCurrency("USD")
			->setTotal(20)
			->setDetails($details);

		$transaction = new Transaction();
		$transaction->setAmount($amount)
			->setItemList($itemList)
			->setDescription("Payment description")
			->setInvoiceNumber(uniqid());
		
		
		$redirectUrls = new RedirectUrls();
		$redirectUrls->setReturnUrl("http://localhost:8000/execute-payment")
					->setCancelUrl("http://localhost:8000/cancel");

		$payment = new Payment();
		$payment->setIntent("sale")
			->setPayer($payer)
			->setRedirectUrls($redirectUrls)
			->setTransactions(array($transaction));

		$payment->create($apiContext);

		return redirect($approvalUrl = $payment->getApprovalLink());

		
	
	}
	

	public function execute()
    {

		$apiContext = new \PayPal\Rest\ApiContext(
		        new \PayPal\Auth\OAuthTokenCredential(
		            'AcVQRamXal0Izrci-lgNefwpZ81hh-OusAJ8_xAqoMwzMUtEFW-kJoSOsc_keVkyBwUaYC4Y7KR54s1X',  
		            'EJFnXVRloUMo9nVRHS-VrbLs769pNiUQ5QEHmQqrsvV-aOntcCRdIPRNY_KdpluIiOw4oNtA22SrqkQa'   
		));

		$paymentId = request('paymentId');
    	$payment = Payment::get($paymentId, $apiContext);

    	$execution = new PaymentExecution();
		$execution->setPayerId(request('PayerID'));
		   
		$transaction = new Transaction();
		$amount = new Amount();
		$details = new Details();

		$details->setShipping(1.2)
				->setTax(1.3)
				->setSubtotal(17.5);
		
		$amount->setCurrency('USD');
    	$amount->setTotal(20);
    	$amount->setDetails($details);
		$transaction->setAmount($amount);

		$execution->addTransaction($transaction);
		
		$result = $payment->execute($execution, $apiContext);

    	return $result;

    }
}

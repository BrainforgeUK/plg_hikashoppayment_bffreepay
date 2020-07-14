<?php
/**
 * @package    HikaShop for Joomla!
 * @version    4.3.0
 * @author    hikashop.com
 * @copyright    (C) 2010-2020 HIKARI SOFTWARE. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php

class plgHikashoppaymentBF_Freepay extends hikashopPaymentPlugin
{
	var $name = 'bf_freepay';
	var $multiple = true;
	var $pluginConfig = array(
		'order_status' => array('ORDER_STATUS', 'orderstatus'),
		'order_finalstatus' => array('Final order status', 'radio', array('cancelled' => 'CANCELLED', '*' => 'ORDER_STATUS')),
		'status_notif_email' => array('ORDER_STATUS_NOTIFICATION', 'boolean', '0'),
		'return_url' => array('RETURN_URL', 'input'),
		'information' => array('ADDITIONAL_INFORMATION', 'big-textarea')
	);

	function onBeforeOrderCreate(&$order, &$do)
	{
	}

	function onAfterOrderCreate(&$order, &$send_email)
	{
		if(empty($order->order_payment_method) || $order->order_payment_method != $this->name)
		{
			return true;
		}

		if (empty($order->cart->payment->payment_params->status_notif_email))
		{
			$send_email = false;
		}
	}

	function onAfterOrderConfirm(&$order, &$methods, $method_id)
	{
		parent::onAfterOrderConfirm($order, $methods, $method_id);
		$method =& $methods[$method_id];
		$this->modifyOrder($order->order_id,
			$method->payment_params->order_status, !empty($method->payment_params->status_notif_email), false);
		if ($method->payment_params->order_finalstatus != '*')
		{
			$this->modifyOrder($order->order_id, 'cancelled', @$method->payment_params->status_notif_email, false);
		}
		$this->removeCart = true;

		$this->information = $method->payment_params->information;
		if (preg_match('#^[a-z0-9_]*$#i', $this->information))
		{
			$this->information = JText::_($this->information);
		}
		$currencyClass = hikashop_get('class.currency');
		$this->amount = $currencyClass->format($order->order_full_price, $order->order_currency_id);
		$this->order_number = $order->order_number;

		return $this->showPage('end');
	}

	function getPaymentDefaultValues(&$element)
	{
		$element->payment_name = 'Freepay';
		$element->payment_description = 'You can buy without paying us anything!';
		$element->payment_params->order_status = 'confirmed';
		$element->payment_params->order_finalstatus = 'cancelled';
	}
}

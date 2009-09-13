<?php
/**
 *                    Jojo CMS
 *                ================
 *
 * Copyright 2008 Harvey Kane <code@ragepank.com>
 * Copyright 2008 Michael Holt <code@gardyneholt.co.nz>
 *
 * See the enclosed file license.txt for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @author  Harvey Kane <code@ragepank.com>
 * @license http://www.fsf.org/copyleft/lgpl.html GNU Lesser General Public License
 * @link    http://www.jojocms.org JojoCMS
 */

/* Define the class for the cart */
if (!defined('Jojo_Cart_Class')) {
    define('Jojo_Cart_Class', Jojo::getOption('jojo_cart_class', 'jojo_plugin_jojo_cart'));
}

if (class_exists(Jojo_Cart_Class)) {
    call_user_func(array(Jojo_Cart_Class, 'setPaymentHandler'), 'jojo_plugin_jojo_cart_securepaytech');
}

$_options[] = array(
    'id'          => 'securepaytech_card_types',
    'category'    => 'Cart',
    'label'       => 'SecurePayTech Card types',
    'description' => 'A comma separated list of card types that are accepted (visa, mastercard, amex, diners)',
    'type'        => 'text',
    'default'     => 'visa,mastercard',
    'options'     => '',
    'plugin'      => 'jojo_cart_securepaytech'
);

$_options[] = array(
    'id'          => 'securepaytech_merchant_id',
    'category'    => 'Cart',
    'label'       => 'SecurePayTech merchant ID',
    'description' => 'The merchant ID provided by SecurePayTech',
    'type'        => 'text',
    'default'     => '',
    'options'     => '',
    'plugin'      => 'jojo_cart_securepaytech'
);

$_options[] = array(
    'id'          => 'securepaytech_test_merchant_id',
    'category'    => 'Cart',
    'label'       => 'SecurePayTech TEST merchant ID',
    'description' => 'The test merchant ID provided by SecurePayTech, used when debugging the payment system',
    'type'        => 'text',
    'default'     => '',
    'options'     => '',
    'plugin'      => 'jojo_cart_securepaytech'
);

$_options[] = array(
    'id'          => 'securepaytech_password',
    'category'    => 'Cart',
    'label'       => 'SecurePayTech password',
    'description' => 'The password provided by SecurePayTech',
    'type'        => 'text',
    'default'     => '',
    'options'     => '',
    'plugin'      => 'jojo_cart_securepaytech'
);


$_options[] = array(
    'id'          => 'securepaytech_test_password',
    'category'    => 'Cart',
    'label'       => 'SecurePayTech TEST password',
    'description' => 'The test password provided by SecurePayTech, used when debugging the payment system',
    'type'        => 'text',
    'default'     => '',
    'options'     => '',
    'plugin'      => 'jojo_cart_securepaytech'
);
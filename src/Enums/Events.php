<?php


namespace App\Enums;


class Events
{
    static public $USER_REGISTER = 'user/register';
    static public $USER_LOGIN = 'user/login';
    static public $ORDER_BASKET_ADD = 'order/basket_add';
    static public $ORDER_PAID = 'order/paid';
    static public $USER_REVIEW = 'user/review';

    static public function getEvents()
    {
        return [
            self::$USER_LOGIN,
            self::$USER_REGISTER,
            self::$ORDER_BASKET_ADD,
            self::$ORDER_PAID,
            self::$USER_REVIEW
        ];
    }
}
<?php namespace Xakfull\Tools\Classes;

use Session;

/**
 * Class SessionManager
 * @package Xakfull\Tools
 * @author XAKFULL
 */
class SessionManager
{
    static $session;

    public function __construct(string $session)
    {
         self::$session = $session;
    }

    /**
     * Remove item from cart
     * @param $key
     * @return bool
     */
    public function remove($keys){
        if (!is_array($keys))
            $keys = [$keys];

        $deleted = false;

        foreach ($keys as $key)
            if (Session::has(self::$session . '.' . $key)) {
                Session::forget(self::$session . '.' . $key);
                session()->forget(self::$session . '.' . $key);
                $deleted = true;
            }

        return $deleted;
    }

    /**
     * Remove items from cart
     * @param array $items
     * @param int $qty
     * @return bool
     */
    public function removeMany($items)
    {
        foreach ($items as $key)
            if (Session::has(self::$session . '.' . $key))
                Session::forget(self::$session . '.' . $key);

        return true;
    }

    /**
     * Add items to cart
     * @param array $items
     * @param int $qty
     * @return bool
     */
    public function addMany($items)
    {
        foreach ($items as $key => $quantity)
            if (Session::has(self::$session . '.' . $key))
                self::change($key, Session::get(self::$session . '.' . $key)+$quantity);
            else
                Session::put(self::$session . '.' . $key, $quantity);

        return true;
    }


    /**
     * Add item to cart
     * @param string $key
     * @param int $qty
     * @return bool
     */
    public function add($key, $qty = 1, $data = [])
    {
        if (Session::has(self::$session . '.' . $key))
            self::change($key, Session::get(self::$session . '.' . $key)+$qty);
        else
            Session::put(self::$session . '.' . $key, $qty);

        return true;
    }

    /**
     * Update item quantity in cart
     * @param $key
     * @param $qty
     * @return bool
     */
    public function change($key, $qty)
    {
        if (Session::has(self::$session . '.' . $key)) {
            if ($qty > 0)
                Session::put(self::$session . '.' . $key, (int)$qty);
            else
                self::remove($key);

            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isEmpty(){
        return Session::get(self::$session) == [];
    }

    /**
     * Return cart content in collection
     * @return \Illuminate\Support\Collection|\October\Rain\Support\Collection
     */
    public function getContent(){
        return collect(Session::get(self::$session));
    }

    /**
     * Return item quantity in cart
     * @param $key
     * @return int
     */
    public function getQuantity($key){
        try {
            return Session::get(self::$session.'.'.$key);
        } catch (\Exception $exception){
            return 0;
        }
    }

    /**
     * @return bool
     */
    public function clear(){
//        Session::forget(self::$session);
        session()->forget(self::$session);
        return true;
    }
}

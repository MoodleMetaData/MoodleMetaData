<?php
/**
 * Created by IntelliJ IDEA.
 * User: tdjones
 * Date: 11-03-28
 * Time: 1:43 PM
 * Eclass Cache Object.
 */

/**
 * Options: to disable Caching set $CFG->ECLASS_CACHE_DISABLED = TRUE in config.php
 */

define("ECLASS_CACHE_EXPIRED", "cache_expired");
define("ECLASS_CACHE_HIT",2);
define("ECLASS_CACHE_DEFAULT_KEY", "ECLASS_CACHE");

defined('MOODLE_INTERNAL') || die;


 
class EclassCache {

    private $key;

    /**
     * Initialize Cache object with namespaced key
     * @throws Exception
     * @param string $key
     */
    public function __construct($key = ECLASS_CACHE_DEFAULT_KEY){
        $this->key = $key;
        if(!isset($_SESSION)) {
            throw new Exception('Missing session object, cannot initialiaze eclass cache.');
        }
        if(!isset($_SESSION[$key])){
            $_SESSION[$key] = array();
        }
    }

    /**
     * Check is the cache item referenced by datakey is expired or not
     * @param datakey
     * @return boolean true if expired
     **/
    public function isExpired($datakey){
        global $CFG;
        if(isset($CFG->ECLASS_CACHE_DISABLED) && $CFG->ECLASS_CACHE_DISABLED == TRUE){
            return TRUE;
        }
        if(isset($_SESSION[$this->key][$datakey])){
            $now = new DateTime('now');
            if($now >= $_SESSION[$this->key][$datakey]['timestamp']){
                $this->expire($datakey);
                return TRUE;
            } else {
                return FALSE;
            }
        } else{
            return TRUE;
        }
    }

    /**
     *  Sets the Data for the cached key
     *  @param datakey (string) Key to access cached data
     *  @param minutes_until_expired (int) number of minutes until cache expires this item
     *  @param data data object to be stored
     **/
    public function setData($datakey, $mintues_until_expired, $data){

        $expire_time = date_modify(new DateTime("now"), "+$mintues_until_expired minutes");
        $_SESSION[$this->key][$datakey] = array('timestamp'=>$expire_time, 'data'=>$data);
    }

    /*
     * Retrieves data from cache referenced by datakey.
     * @param datakey
     *
     * @return data object or ECLASS_CACHE_EXPIRED;
     *
     */
    public function getData($datakey){
        if(!$this->isExpired($datakey)){
            return $_SESSION[$this->key][$datakey]['data'];
        } else {
            return ECLASS_CACHE_EXPIRED;
        }
    }

    /*
     * Expires the datakey in the cache
     * @param datakey datakey of item to expire
     * @return returns the expired data
     */
    public function expire($datakey){
        if(isset($_SESSION[$this->key][$datakey])){
            $data = $_SESSION[$this->key][$datakey]['data'];
            unset($_SESSION[$this->key][$datakey]);
            return $data;
        } else {
            return null;
        }
    }
}

<?php
/**
* Output Cache extension of base caching class
*/
class OutputCache extends Cache
{
    /**
    * Group of currently being recorded data
    * @var string
    */
    private static $group;
    
    /**
    * ID of currently being recorded data
    * @var string
    */
    private static $id;
    
    /**
    * Ttl of currently being recorded data
    * @var int
    */
    private static $ttl;

    /**
    * Starts caching off. Returns true if cached, and dumps
    * the output. False if not cached and start output buffering.
    * 
    * @param  string $group Group to store data under
    * @param  string $id    Unique ID of this data
    * @param  int    $ttl   How long to cache for (in seconds)
    * @return bool          True if cached, false if not
    */
    public static function Start($group, $id, $ttl)
    {
        if (self::isCached($group, $id)) {
            echo self::read($group, $id);
            return true;
        
        } else {
            
            ob_start();
            
            self::$group = $group;
            self::$id    = $id;
            self::$ttl   = $ttl;
            
            return false;
        }
    }
    
    /**
    * Ends caching. Writes data to disk.
    */
    public static function End()
    {
        $data = ob_get_contents();
        ob_end_flush();
        
        self::write(self::$group, self::$id, self::$ttl, $data);
    }
}
?>
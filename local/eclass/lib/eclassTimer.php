<?php
/**
 * Created by IntelliJ IDEA.
 * User: tdjones
 * Date: 11-04-04
 * Time: 3:58 PM
 * To change this template use File | Settings | File Templates.
 */

class EclassTimer
{

    public $starttime;
    public $endtime;

    public function __construct(){
        $this->starttime = 0;
        $this->endtime = 0;
    }

    public function start() {
        $mtime = microtime();
        $mtime = explode(' ', $mtime);
        $mtime = $mtime[1] + $mtime[0];
        $this->starttime = $mtime;

    }

    public function stop() {
        $mtime = microtime();
        $mtime = explode(" ", $mtime);
        $mtime = $mtime[1] + $mtime[0];
        $this->endtime = $mtime;
        return ($this->endtime - $this->starttime);
    }

    public function totalTime(){
        if($this->starttime > 0 && $this->endtime > 0){
            return $this->endtime - $this->starttime;
        }
        return -1;
    }


}

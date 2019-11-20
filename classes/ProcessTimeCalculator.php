<?php
/**
 * @author Soumen Pasari
 * @package process-time-calculator
 * @license soumen-pasari MIT open-source 2019-2020
 */
class ProcessTimeCalculator
{
    /**
     * class varibles
     */
    protected static $startTime = null;
    protected static $endTime = null;
    /**
     * record the starting time of any process
     * @return null
     */
    public static function startTimer()
    {
        self::$startTime = self::getMicroTime();
        return null;
    }
    /**
     * record the ending time of process
     * to calculate
     * @return null
     */
    public static function endTimer()
    {
        self::$endTime = self::getMicroTime();
        return self::calculate();
    }
    /**
     * get time in micro seconds
     */
    protected static function getMicroTime()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }
    /**
     * calculate the time difference in seconds
     * between startTime and endTime
     * @return array['status',['data','message' optional]]
     */
    public static function calculate()
    {
        try
        {
            # checking mendatory params to calculate time diff
            if(self::$startTime != null && self::$endTime != null)
            {
                return [
                    'status'=>true,
                    'data'=>self::$endTime - self::$startTime
                ];
            }
            else
            {
                throw new Exception('timer is either not started or have not been ended yet!');
            }
        }
        catch(Exception $e)
        {
            return [
                'status'=>false,
                'message'=>$e->getMessage()
            ];
        }
    }
}
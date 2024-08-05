<?php

class ReceivingRecord
{
    /**
     * @var string
     */
    private $hsf_id;

    /**
     * @var string
     */
    private $threshing_flag;

    /**
     * @var string
     */
    private $transportation_flag;

    /**
     * @var string
     */
    private $total_field_size_threshed;

    /**
     * @var string
     */
    private $bags_received;

    /**
     * @return string|null
     */
    public function getHsfId()
    {
        return $this->hsf_id;
    }

    /**
     * @return string|null
     */
    public function getThreshingFlag()
    {
        return $this->threshing_flag;
    }

    /**
     * @return string|null
     */
    public function getTransportationFlag()
    {
        return $this->transportation_flag;
    }

    /**
     * @return string|null
     */
    public function getTotalFieldSizeThreshed()
    {
        return $this->total_field_size_threshed;
    }

    /**
     * @return string|null
     */
    public function getBagsReceived()
    {
        return $this->bags_received;
    }
}


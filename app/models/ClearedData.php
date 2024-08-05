<?php

class ClearedData
{
    /**
     * @var string
     */
    private $hsf_id;

    /**
     * @var string
     */
    private $collection_center_id;

    /**
     * @var string
     */
    private $hub;

    /**
     * @var string
     */
    private $new_hsf;

    /**
     * @var string
     */
    private $verifier_id;

    /**
     * @var string
     */
    private $unique_member_id;

    /**
     * @var string
     */
    private $ik_number;

    /**
     * @var string
     */
    private $empty_bag_weight;

    /**
     * @var string
     */
    private $average_weight;


    /**
     * @var string
     */
    private $total_weight;

    /**
     * @var string
     */
    private $prorated_total_weight;

    /**
     * @var string
     */
    private $net_weight;

    /**
     * @var string
     */
    private $product_type;

    /**
     * @var string
     */
    private $variety;

    /**
     * @var string
     */
    private $thresher_id;

    /**
     * @var string
     */
    private $threshing_date;

    /**
     * @var string
     */
    private $threshing_cost;

    /**
     * @var string
     */
    private $transporter_id;

    /**
     * @var string
     */
    private $transporter_cost;

    /**
     * @var string
     */
    private $cc_processing_cost;

    /**
     * @var string
     */
    private $costs;

    /**
     * @var int
     */
    private $bags_marketed;

    /**
     * @var int
     */
    private $bags_transported;

    /**
     * @var int
     */
    private $moldy_grains_count_flag;

    /**
     * @var int
     */
    private $transport_voucher_alteration_flag;

    /**
     * @var int
     */
    private $transporter_payment_flag;

    /**
     * @var int
     */
    private $verifier_flag;

    /**
     * @var string
     */
    private $verifier_comment;

    /**
     * @var string
     */
    private $voucher_edit_comment;

    /**
     * @var int
     */
    private $cleared_flag;

    /**
     * @var string
     */
    private $created_at;

    /**
     * @var string
     */
    private $updated_at;

    /**
     * @var string
     */
    private $transaction_date;

    /**
     * @var string
     */
    private $moisture_percentage;

    /**
     * @var string
     */
    private $cleanliness_percentage;

    /**
     * @var int
     */
    private $moldy_grains_count;

    /**
     * @var int
     */
    private $update_flag;


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
    public function getCollectionCenterid()
    {
        return $this->collection_center_id;
    }

    /**
     * @param string|null $collection_center_id
     */
    public function setCollectionCenterid($collection_center_id)
    {
        $this->collection_center_id = $collection_center_id;
    }

    /**
     * @return string|null
     */
    public function getHub()
    {
        return $this->hub;
    }


    /**
     * @return string|null
     */
    public function getNewHsf()
    {
        return $this->new_hsf;
    }


    /**
     * @return string|null
     */
    public function getVerifierId()
    {
        return $this->verifier_id;
    }


    /**
     * @return string|null
     */
    public function getUniqueMemberid()
    {
        return $this->unique_member_id;
    }


    /**
     * @return string|null
     */
    public function getIkNumber()
    {
        return $this->ik_number;
    }

    /**
     * @return string|null
     */
    public function getEmptyBagweight()
    {
        return $this->empty_bag_weight;
    }

    /**
     * @param string|null $empty_bag_weight
     */
    public function setEmptyBagweight($empty_bag_weight)
    {
        $this->empty_bag_weight = $empty_bag_weight;
    }

    /**
     * @return string|null
     */
    public function getTotalWeight()
    {
        return $this->total_weight;
    }

    /**
     * @return string|null
     */
    public function getProratedTotalweight()
    {
        return $this->prorated_total_weight;
    }

    /**
     * @param string|null $prorated_total_weight
     */
    public function setProratedTotalweight($prorated_total_weight)
    {
        $this->prorated_total_weight = $prorated_total_weight;
    }

    /**
     * @return string|null
     */
    public function getNetWeight()
    {
        return $this->net_weight;
    }

    /**
     * @param string|null $net_weight
     */
    public function setNetWeight($net_weight)
    {
        $this->net_weight = $net_weight;
    }

    /**
     * @return string|null
     */
    public function getProductType()
    {
        return $this->product_type;
    }

    /**
     * @return string|null
     */
    public function getVariety()
    {
        return $this->variety;
    }


    /**
     * @return string|null
     */
    public function getThresherId()
    {
        return $this->thresher_id;
    }

    /**
     * @return string|null
     */
    public function getThreshingDate()
    {
        return $this->threshing_date;
    }

    /**
     * @return string|null
     */
    public function getThreshingCost()
    {
        return $this->threshing_cost;
    }

    /**
     * @param string|null $threshing_cost
     */
    public function setThreshingCost($threshing_cost)
    {
        $this->threshing_cost = $threshing_cost;
    }

    /**
     * @return string|null
     */
    public function getTransporterId()
    {
        return $this->transporter_id;
    }

    /**
     * @param string|null $transporter_id
     */
    public function setTransporterId($transporter_id)
    {
        $this->transporter_id = $transporter_id;
    }

    /**
     * @return string|null
     */
    public function getTransporterCost()
    {
        return $this->transporter_cost;
    }

    /**
     * @param string|null $threshing_cost
     */
    public function setTransporterCost($transporter_cost)
    {
        $this->transporter_cost = $transporter_cost;
    }


    /**
     * @return string|null
     */
    public function getCcProcessingcost()
    {
        return $this->cc_processing_cost;
    }

    /**
     * @param string|null $threshing_cost
     */
    public function setCcProcessingcost($cc_processing_cost)
    {
        $this->cc_processing_cost = $cc_processing_cost;
    }

    /**
     * @return string|null
     */
    public function getCosts()
    {
        return $this->costs;
    }

    /**
     * @param string|null $costs
     */
    public function setCosts($costs)
    {
        $this->costs = $costs;
    }

    /**
     * @return int|null
     */
    public function getBagsMarketed()
    {
        return $this->bags_marketed;
    }

    /**
     * @return int|null
     */
    public function getBagsTransported()
    {
        return $this->bags_transported;
    }
    /**
     * @return int|null
     */
    public function getMoldyGrainscountflag()
    {
        return $this->moldy_grains_count_flag;
    }

    /**
     * @param int|null $moldy_grains_count_flag
     */
    public function setMoldyGrainscountflag($moldy_grains_count_flag)
    {
        $this->moldy_grains_count_flag = $moldy_grains_count_flag;
    }

    /**
     * @return int|null
     */
    public function getTransportVoucheralterationflag()
    {
        return $this->transport_voucher_alteration_flag;
    }

    /**
     * @return int|null
     */
    public function getTransporterPaymentflag()
    {
        return $this->transporter_payment_flag;
    }

    /**
     * @return int|null
     */
    public function getVerifierFlag()
    {
        return $this->verifier_flag;
    }


    /**
     * @return string|null
     */
    public function getVerifierComment()
    {
        return $this->verifier_comment;
    }


    /**
     * @return string|null
     */
    public function getVoucherEditcomment()
    {
        return $this->voucher_edit_comment;
    }


    /**
     * @return int|null
     */
    public function getClearedFlag()
    {
        return $this->cleared_flag;
    }


    /**
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }


    /**
     * @return string|null
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }


    /**
     * @return string|null
     */
    public function getTransactionDate()
    {
        return $this->transaction_date;
    }

    /**
     * @return string|null
     */
    public function getMoisturePercentage()
    {
        return $this->moisture_percentage;
    }

    /**
     * @return string|null
     */
    public function getCleanlinessPercentage()
    {
        return $this->cleanliness_percentage;
    }

    /**
     * @return int|null
     */
    public function getMoldyGrainscount()
    {
        return $this->moldy_grains_count;
    }

    /**
     * @return int|null
     */
    public function getUpdateFlag()
    {
        return $this->update_flag;
    }

    /**
     * @return string|null
     */
    public function getAverageWeight()
    {
        return $this->average_weight;
    }

    /**
     * @param string|null $average_weight
     */
    public function setAverageWeight($average_weight)
    {
        $this->average_weight = $average_weight;
    }
}


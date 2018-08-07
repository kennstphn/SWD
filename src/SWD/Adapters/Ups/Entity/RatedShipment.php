<?php

namespace SWD\Adapters\Ups\Entity;


use Ups\Entity\RatedPackage;

class RatedShipment implements \JsonSerializable
{
    public $service;
    public $provider;
    public $cost;
    public $code;

    public $warning;
    public $totalWeight;
    public $transportationCharges;
    public $serviceOptionsCharges;
    public $serviceOptionsDescription;
    public $guaranteedDaysToDelivery;
    public $scheduledDeliveryTime;
    public $ratedPackageList = [];
    public $surCharges;
    public $timeInTransit;

    /**
     * @throws \Exception
     */
    function validate(){
        foreach( [
            'service','cost', 'code'
        ] as $required){
            if ( is_null($this->$required)){
                throw new \Exception('required value ('.$required.') can not be null');
            }
        }
    }

    function createFromUpsRatedShipment(\Ups\Entity\RatedShipment $shipment)
    {
        $this->service = $shipment->getServiceName();
        $this->code = $shipment->Service->getCode();
        $this->cost = $shipment->TotalCharges->MonetaryValue;

        $this->warning = $shipment->RateShipmentWarning ? $shipment->RateShipmentWarning : null;
        $this->totalWeight = $shipment->BillingWeight->Weight;
        $this->transportationCharges = $shipment->TransportationCharges->MonetaryValue;
        $this->serviceOptionsCharges = $shipment->ServiceOptionsCharges->MonetaryValue;
        $this->serviceOptionsDescription = $shipment->ServiceOptionsCharges->Description;
        $this->guaranteedDaysToDelivery = $shipment->GuaranteedDaysToDelivery;
        $this->scheduledDeliveryTime = $shipment->ScheduledDeliveryTime;

        foreach($shipment->RatedPackage as $ratedPackage){
            /** @var RatedPackage $ratedPackage */
            $package = new \stdClass();
            $package->weight = $ratedPackage->Weight;
            $package->charge = $ratedPackage->TotalCharges->MonetaryValue;
            $package->billingWeight = $ratedPackage->BillingWeight->Weight;
            $package->serviceOptionsCharges = $ratedPackage->ServiceOptionsCharges->MonetaryValue;
            $package->transportationCharges = $ratedPackage->TransportationCharges->MonetaryValue;

            array_push($this->ratedPackageList, $package);
        }
    }

    /**
     * @return array
     *
     * Used to eliminate optional null-value properties from json data
     */
    function jsonSerialize()
    {
        $this->validate();
        $data = [];
        foreach(get_object_vars($this) as $prop => $val){
            if ( ! is_null($val) ){
                $data[$prop] = $val;
            }

        }
        return $data;
    }


}
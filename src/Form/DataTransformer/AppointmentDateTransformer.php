<?php


namespace App\Form\DataTransformer;


use Symfony\Component\Form\DataTransformerInterface;

class AppointmentDateTransformer implements DataTransformerInterface
{
    public function transform($value)
    {
        if ($value instanceof \DateTime) {
            return $value->format("d/m/Y");
        }
    }

    public function reverseTransform($value)
    {
        try {
            return \DateTime::createFromFormat("d/m/Y", $value);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
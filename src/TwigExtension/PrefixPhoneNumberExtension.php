<?php


namespace App\TwigExtension;


use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class PrefixPhoneNumberExtension extends AbstractExtension
{
    const PHONE_PREFIX = [
        'fr' => '+33 ',
        'be' => '+32 ',
        'lu' => '+352 ',
        'ch' => '+41 '
    ];

    public function getFilters()
    {
        return [
            new TwigFilter('phone_prefix', [$this, 'showPrefixPhoneNumber']),
        ];
    }

    public function showPrefixPhoneNumber(string $phoneNumber, string $country = 'fr')
    {
        return self::PHONE_PREFIX[$country] . $phoneNumber;
    }
}
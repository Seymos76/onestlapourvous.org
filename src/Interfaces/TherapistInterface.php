<?php


namespace App\Interfaces;


interface TherapistInterface
{
    public function getEthicEntityCodeLabel(): ?string ;
    public function getSchoolEntityLabel(): ?string ;
    public function isOwningCertification(): ?bool ;
    public function isSupervised(): ?bool ;
    public function isRespectingEthicalFrameWork(): ?bool ;
}
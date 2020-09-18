<?php


namespace App\Search;


use Symfony\Component\HttpFoundation\Request;

class RequestParameters
{
    public function getParameters(Request $request): array
    {
        $params = [];
        foreach ($request->query as $key => $value) {
            if ($value !== "") {
                $params[$key] = $value;
            }
        }
        return $params;
    }
}
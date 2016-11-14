<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cc\Mvc;

use Cc\UrlManager;

/**
 * 
 */
class JsonLD extends \Cc\Json
{

    public function __construct($type = NULL)
    {
        parent::__construct();

        if (!is_null($type))
            $this["@type"] = $type;
    }

    public function NoEmpty()
    {
        return count($this->__debugInfo()) != 0;
    }

    public function SitieSearch($url, $urlSearch, $StrSerach = 'search_term_string')
    {
        $this["@type"] = 'WebSite';
        $this["url"] = $url;
        $this["potentialAction"] = [
            "@type" => "SearchAction",
            "target" => $url . $urlSearch,
            "query-input" => "required name=$StrSerach"
        ];
    }

}

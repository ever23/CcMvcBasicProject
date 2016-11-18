<?php

/*
 * Copyright (C) 2016 Enyerber Franco
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
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
